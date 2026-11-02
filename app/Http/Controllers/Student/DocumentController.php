<?php

namespace App\Http\Controllers\Student;

use App\Enums\DocumentCategory;
use App\Http\Controllers\Controller;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Documents available to a student.
 *
 * Uses the same visibility SQL as the leader listing, so a student never sees
 * a ministry- or committee-restricted document in the list *or* on direct URL.
 */
class DocumentController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $documents = Document::query()
            ->with('uploader')
            ->visibleTo($user)
            ->when($request->filled('category'), fn ($query) => $query->where('category', $request->string('category')))
            ->search($request->input('search'), ['title', 'description'])
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('student.documents.index', [
            'documents' => $documents,
            'categories' => DocumentCategory::cases(),
        ]);
    }

    public function show(Document $document): View
    {
        $this->authorize('view', $document);

        return view('student.documents.show', [
            'document' => $document->load('uploader'),
        ]);
    }

    /**
     * Authorized download. Policy is the single gate for file access.
     */
    public function download(Request $request, Document $document): StreamedResponse
    {
        $this->authorize('download', $document);

        $disk = Storage::disk(config('daruso.uploads.disk'));

        if (! $disk->exists($document->file_path)) {
            abort(404, 'The stored file is missing.');
        }

        return $disk->download($document->file_path, $document->file_name, [
            'Content-Type' => 'application/octet-stream',
        ]);
    }
}
