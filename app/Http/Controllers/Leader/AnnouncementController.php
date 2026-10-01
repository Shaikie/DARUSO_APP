<?php

namespace App\Http\Controllers\Leader;

use App\Enums\AnnouncementStatus;
use App\Enums\AuditAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAnnouncementRequest;
use App\Models\Announcement;
use App\Services\AnnouncementPublisher;
use App\Services\AttachmentService;
use App\Services\AudienceRuleRepository;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Announcement management for leaders.
 *
 * Thin by design: lifecycle transitions delegate to AnnouncementPublisher and
 * every action is authorized against the policy before it runs.
 */
class AnnouncementController extends Controller
{
    public function __construct(
        private readonly AnnouncementPublisher $publisher,
        private readonly AudienceRuleRepository $audienceRules,
        private readonly AttachmentService $attachments,
        private readonly AuditLogger $audit,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Announcement::class);

        $announcements = Announcement::query()
            ->with(['author', 'audienceRules'])
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->when($request->filled('priority'), fn ($query) => $query->where('priority', $request->string('priority')))
            ->search($request->input('search'), ['title', 'content'])
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('leader.announcements.index', [
            'announcements' => $announcements,
            'statuses' => AnnouncementStatus::cases(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Announcement::class);

        return view('leader.announcements.create', [
            'audienceOptions' => $this->audienceRules->options(),
        ]);
    }

    public function store(StoreAnnouncementRequest $request): RedirectResponse
    {
        $this->authorize('create', Announcement::class);

        $announcement = Announcement::create([
            ...$request->safe()->only(['title', 'content', 'priority', 'status', 'expires_at', 'requires_approval']),
            'author_id' => $request->user()->getKey(),
            'status' => $request->string('status')->value === AnnouncementStatus::Published->value
                && ! $request->user()->can('publish', Announcement::class)
                    ? AnnouncementStatus::Draft->value
                    : $request->string('status')->value,
        ]);

        $rules = $this->audienceRules->persist($this->audienceRules->build($request->audiencePayload()));
        $announcement->audienceRules()->sync($rules->pluck('id'));

        if ($request->hasFile('attachment')) {
            $this->attachments->storeFor($announcement, $request->file('attachment'), $request->user()->getKey());
        }

        if ($announcement->isPublished()) {
            $this->publisher->publish($announcement, $request->user(), null, $request);
        } else {
            $this->audit->log(AuditAction::Created, $announcement, null, [
                'title' => $announcement->title,
            ], $request);
        }

        return redirect()
            ->route('leader.announcements.index')
            ->with('success', 'Announcement created successfully.');
    }

    public function show(Announcement $announcement): View
    {
        $this->authorize('view', $announcement);

        return view('leader.announcements.show', [
            'announcement' => $announcement->load(['author', 'approver', 'audienceRules', 'attachments']),
            'audienceSummary' => $this->audienceRules->describe($announcement->audienceRules),
        ]);
    }

    public function edit(Announcement $announcement): View
    {
        $this->authorize('update', $announcement);

        return view('leader.announcements.edit', [
            'announcement' => $announcement->load('audienceRules'),
            'audienceOptions' => $this->audienceRules->options(),
            'audienceSummary' => $this->audienceRules->describe($announcement->audienceRules),
        ]);
    }

    public function update(StoreAnnouncementRequest $request, Announcement $announcement): RedirectResponse
    {
        $this->authorize('update', $announcement);

        $old = $announcement->only(['title', 'status', 'priority']);

        $announcement->update($request->safe()->only([
            'title', 'content', 'priority', 'expires_at', 'requires_approval',
        ]));

        $rules = $this->audienceRules->persist($this->audienceRules->build($request->audiencePayload()));
        $announcement->audienceRules()->sync($rules->pluck('id'));

        if ($request->hasFile('attachment')) {
            $existing = $announcement->attachments()->first();

            if ($existing !== null) {
                $this->attachments->replace($existing, $request->file('attachment'), $request->user()->getKey());
            } else {
                $this->attachments->storeFor($announcement, $request->file('attachment'), $request->user()->getKey());
            }
        }

        $this->audit->log('updated', $announcement, $old, $announcement->only(['title', 'status', 'priority']), $request);

        return redirect()
            ->route('leader.announcements.show', $announcement)
            ->with('success', 'Announcement updated.');
    }

    /**
     * Submit a draft for review.
     */
    public function submitForReview(Request $request, Announcement $announcement): RedirectResponse
    {
        $this->authorize('submitForReview', $announcement);

        $this->publisher->submitForReview($announcement, $request->user(), $request);

        return back()->with('success', 'Announcement submitted for review.');
    }

    /**
     * Publish an announcement.
     */
    public function publish(Request $request, Announcement $announcement): RedirectResponse
    {
        $this->authorize('publish', $announcement);

        $this->publisher->publish($announcement, $request->user(), null, $request);

        return back()->with('success', 'Announcement published.');
    }

    public function archive(Request $request, Announcement $announcement): RedirectResponse
    {
        $this->authorize('archive', $announcement);

        $this->publisher->archive($announcement, $request->user(), $request);

        return back()->with('success', 'Announcement archived.');
    }

    public function revertToDraft(Request $request, Announcement $announcement): RedirectResponse
    {
        $this->authorize('update', $announcement);

        $this->publisher->revertToDraft($announcement, $request->user(), $request);

        return back()->with('success', 'Announcement returned to draft.');
    }

    public function destroy(Request $request, Announcement $announcement): RedirectResponse
    {
        $this->authorize('delete', $announcement);

        $this->audit->log('deleted', $announcement, $announcement->only(['title', 'status']), null, $request);

        $announcement->delete();

        return redirect()
            ->route('leader.announcements.index')
            ->with('success', 'Announcement deleted.');
    }
}
