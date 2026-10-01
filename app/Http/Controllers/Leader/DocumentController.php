<?php

namespace App\Http\Controllers\Leader;

use App\Enums\DocumentCategory;
use App\Enums\DocumentVisibility;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDocumentRequest;
use App\Models\Committee;
use App\Models\Document;
use App\Models\Ministry;
use App\Models\User;
use App\Services\AttachmentService;
use App\Services\AuditLogger;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Document management with per-record visibility.
 *
 * Files are stored on the private disk; downloads go through `download()` which
 * re-checks DocumentPolicy, so a leaked path grants nothing.
 */
class DocumentController extends Controller
{
    public function __construct(
        private readonly AttachmentService $attachments,
        private readonly AuditLogger $audit,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Document::class);

        $documents = Document::query()
            ->with(['uploader', 'ministry', 'committee'])
            // Visibility is applied in SQL so unrelated documents are never loaded.
            ->tap(fn ($query) => $this->scopeToVisible($query, $request->user()))
            ->when($request->filled('category'), fn ($query) => $query->where('category', $request->string('category')))
            ->search($request->input('search'), ['title', 'description'])
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('leader.documents.index', [
            'documents' => $documents,
            'categories' => DocumentCategory::cases(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Document::class);

        return view('leader.documents.create', [
            'categories' => DocumentCategory::cases(),
            'visibilities' => DocumentVisibility::cases(),
            'ministries' => Ministry::orderBy('name')->get(),
            'committees' => Committee::orderBy('name')->get(),
        ]);
    }

    public function store(StoreDocumentRequest $request): RedirectResponse
    {
        $this->authorize('create', Document::class);

        $file = $request->file('file');

        // A new version supersedes the previous one rather than overwriting it.
        $supersedes = null;
        $version = 1;

        if ($request->filled('supersedes_id')) {
            $supersedes = Document::find($request->integer('supersedes_id'));
            $version = $supersedes !== null ? $supersedes->version + 1 : 1;
        }

        $disk = config('daruso.uploads.disk');
        $path = $file->store('documents/'.$request->string('category')->value, $disk);

        $document = Document::create([
            ...$request->safe()->only(['title', 'description', 'category', 'visibility', 'ministry_id', 'committee_id', 'group_id']),
            'uploader_id' => $request->user()->getKey(),
            'file_path' => $path,
            'file_name' => $file->getClientOriginalName(),
            'file_mime_type' => $file->getClientMimeType(),
            'file_size' => $file->getSize() ?: 0,
            'version' => $version,
            'supersedes_id' => $supersedes?->getKey(),
        ]);

        $this->audit->log('document_uploaded', $document, null, [
            'title' => $document->title,
            'version' => $document->version,
        ], $request);

        return redirect()
            ->route('leader.documents.show', $document)
            ->with('success', 'Document uploaded.');
    }

    public function show(Document $document): View
    {
        $this->authorize('view', $document);

        return view('leader.documents.show', [
            'document' => $document->load(['uploader', 'ministry', 'committee', 'supersedes']),
        ]);
    }

    public function edit(Document $document): View
    {
        $this->authorize('update', $document);

        return view('leader.documents.edit', [
            'document' => $document,
            'categories' => DocumentCategory::cases(),
            'visibilities' => DocumentVisibility::cases(),
            'ministries' => Ministry::orderBy('name')->get(),
            'committees' => Committee::orderBy('name')->get(),
        ]);
    }

    /**
     * Update document metadata.
     *
     * Replacing the file does not overwrite the previous version. A new row is
     * created that supersedes the old one, so the version history stays intact
     * and a reader can always be shown which revision they downloaded.
     */
    public function update(StoreDocumentRequest $request, Document $document): RedirectResponse
    {
        $this->authorize('update', $document);

        $old = $document->only(['title', 'visibility', 'category']);

        $document->update($request->safe()->only([
            'title', 'description', 'category', 'visibility', 'ministry_id', 'committee_id', 'group_id',
        ]));

        if (! $request->hasFile('file')) {
            $this->audit->log('updated', $document, $old, $document->only(['title', 'visibility', 'category']), $request);

            return redirect()
                ->route('leader.documents.show', $document)
                ->with('success', 'Document updated.');
        }

        $file = $request->file('file');
        $disk = config('daruso.uploads.disk');

        $newPath = $file->store('documents/'.$document->category->value, $disk);

        $newVersion = Document::create([
            'title' => $document->title,
            'description' => $document->description,
            'category' => $document->category->value,
            'visibility' => $document->visibility->value,
            'ministry_id' => $document->ministry_id,
            'committee_id' => $document->committee_id,
            'group_id' => $document->group_id,
            'uploader_id' => $request->user()->getKey(),
            'file_path' => $newPath,
            'file_name' => $file->getClientOriginalName(),
            'file_mime_type' => $file->getClientMimeType(),
            'file_size' => $file->getSize() ?: 0,
            'version' => $document->version + 1,
            'supersedes_id' => $document->getKey(),
        ]);

        $this->audit->log('document_uploaded', $newVersion, $old, [
            'title' => $newVersion->title,
            'version' => $newVersion->version,
        ], $request);

        return redirect()
            ->route('leader.documents.show', $newVersion)
            ->with('success', 'A new version of the document has been uploaded.');
    }

    /**
     * Authorized download.
     */
    public function download(Request $request, Document $document): StreamedResponse
    {
        $this->authorize('download', $document);

        $disk = Storage::disk(config('daruso.uploads.disk'));

        if (! $disk->exists($document->file_path)) {
            abort(404, 'The stored file is missing.');
        }

        $this->audit->log('downloaded', $document, null, null, $request);

        // `attachment` forces a download rather than inline rendering, so an
        // uploaded HTML/SVG file cannot execute in the application's origin.
        return $disk->download($document->file_path, $document->file_name, [
            'Content-Type' => 'application/octet-stream',
        ]);
    }

    public function destroy(Request $request, Document $document): RedirectResponse
    {
        $this->authorize('delete', $document);

        $this->audit->log('deleted', $document, $document->only(['title', 'visibility']), null, $request);

        Storage::disk(config('daruso.uploads.disk'))->delete($document->file_path);

        $document->delete();

        return redirect()
            ->route('leader.documents.index')
            ->with('success', 'Document deleted.');
    }

    /**
     * Restrict a document query to what the user is permitted to see.
     *
     * Mirrors DocumentPolicy::view in SQL so inaccessible rows are never loaded.
     */
    private function scopeToVisible(Builder $query, User $user): void
    {
        $ministryIds = $user->ministryIds()->all();
        $committeeIds = $user->committeeIds()->all();
        $isLeader = $user->isLeader();

        $query->where(function (Builder $inner) use ($user, $ministryIds, $committeeIds, $isLeader): void {
            $inner->whereIn('visibility', [
                DocumentVisibility::Public->value,
                DocumentVisibility::Students->value,
            ]);

            if ($isLeader) {
                $inner->orWhere('visibility', DocumentVisibility::Leaders->value);
            }

            if ($ministryIds !== []) {
                $inner->orWhere(function (Builder $ministry) use ($ministryIds): void {
                    $ministry->where('visibility', DocumentVisibility::Ministry->value)
                        ->whereIn('ministry_id', $ministryIds);
                });
            }

            if ($committeeIds !== []) {
                $inner->orWhere(function (Builder $committee) use ($committeeIds): void {
                    $committee->where('visibility', DocumentVisibility::Committee->value)
                        ->whereIn('committee_id', $committeeIds);
                });
            }

            $inner->orWhere(function (Builder $group) use ($user): void {
                $group->where('visibility', DocumentVisibility::Group->value)
                    ->where('group_id', $user->getKey());
            });

            // Uploaders always retain access to their own documents.
            $inner->orWhere('uploader_id', $user->getKey());
        });
    }
}
