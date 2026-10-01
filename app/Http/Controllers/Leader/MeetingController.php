<?php

namespace App\Http\Controllers\Leader;

use App\Enums\MeetingStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMeetingRequest;
use App\Models\Meeting;
use App\Services\AttachmentService;
use App\Services\AudienceRuleRepository;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MeetingController extends Controller
{
    public function __construct(
        private readonly AudienceRuleRepository $audienceRules,
        private readonly AttachmentService $attachments,
        private readonly AuditLogger $audit,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Meeting::class);

        $meetings = Meeting::query()
            ->with(['organizer', 'audienceRules'])
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->search($request->input('search'), ['title', 'venue'])
            ->orderByDesc('meeting_date')
            ->paginate(15)
            ->withQueryString();

        return view('leader.meetings.index', [
            'meetings' => $meetings,
            'statuses' => MeetingStatus::cases(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Meeting::class);

        return view('leader.meetings.create', [
            'audienceOptions' => $this->audienceRules->options(),
        ]);
    }

    public function store(StoreMeetingRequest $request): RedirectResponse
    {
        $this->authorize('create', Meeting::class);

        $meeting = Meeting::create([
            ...$request->safe()->only([
                'title', 'description', 'meeting_date', 'meeting_time', 'venue', 'status', 'agenda',
            ]),
            'organizer_id' => $request->user()->getKey(),
        ]);

        $rules = $this->audienceRules->persist($this->audienceRules->build($request->audiencePayload()));
        $meeting->audienceRules()->sync($rules->pluck('id'));

        if ($request->hasFile('attachment')) {
            $this->attachments->storeFor($meeting, $request->file('attachment'), $request->user()->getKey());
        }

        $this->audit->log('created', $meeting, null, ['title' => $meeting->title], $request);

        return redirect()
            ->route('leader.meetings.show', $meeting)
            ->with('success', 'Meeting scheduled.');
    }

    public function show(Meeting $meeting): View
    {
        $this->authorize('view', $meeting);

        return view('leader.meetings.show', [
            'meeting' => $meeting->load(['organizer', 'audienceRules', 'attachments']),
            'audienceSummary' => $this->audienceRules->describe($meeting->audienceRules),
        ]);
    }

    public function edit(Meeting $meeting): View
    {
        $this->authorize('update', $meeting);

        return view('leader.meetings.edit', [
            'meeting' => $meeting->load('audienceRules'),
            'audienceOptions' => $this->audienceRules->options(),
            'audienceSummary' => $this->audienceRules->describe($meeting->audienceRules),
        ]);
    }

    public function update(StoreMeetingRequest $request, Meeting $meeting): RedirectResponse
    {
        $this->authorize('update', $meeting);

        $old = $meeting->only(['title', 'status', 'meeting_date']);

        $meeting->update($request->safe()->only([
            'title', 'description', 'meeting_date', 'meeting_time', 'venue', 'status', 'agenda',
        ]));

        $rules = $this->audienceRules->persist($this->audienceRules->build($request->audiencePayload()));
        $meeting->audienceRules()->sync($rules->pluck('id'));

        if ($request->hasFile('attachment')) {
            $existing = $meeting->attachments()->first();

            if ($existing !== null) {
                $this->attachments->replace($existing, $request->file('attachment'), $request->user()->getKey());
            } else {
                $this->attachments->storeFor($meeting, $request->file('attachment'), $request->user()->getKey());
            }
        }

        $this->audit->log('updated', $meeting, $old, $meeting->only(['title', 'status', 'meeting_date']), $request);

        return redirect()
            ->route('leader.meetings.show', $meeting)
            ->with('success', 'Meeting updated.');
    }

    public function destroy(Request $request, Meeting $meeting): RedirectResponse
    {
        $this->authorize('delete', $meeting);

        $this->audit->log('deleted', $meeting, $meeting->only(['title', 'status']), null, $request);

        $meeting->delete();

        return redirect()
            ->route('leader.meetings.index')
            ->with('success', 'Meeting deleted.');
    }
}
