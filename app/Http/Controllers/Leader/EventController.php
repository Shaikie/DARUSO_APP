<?php

namespace App\Http\Controllers\Leader;

use App\Enums\EventStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEventRequest;
use App\Models\Event;
use App\Services\AttachmentService;
use App\Services\AudienceRuleRepository;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EventController extends Controller
{
    public function __construct(
        private readonly AudienceRuleRepository $audienceRules,
        private readonly AttachmentService $attachments,
        private readonly AuditLogger $audit,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Event::class);

        $events = Event::query()
            ->with(['organizer', 'audienceRules'])
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->search($request->input('search'), ['title', 'venue'])
            ->orderByDesc('event_date')
            ->paginate(15)
            ->withQueryString();

        return view('leader.events.index', [
            'events' => $events,
            'statuses' => EventStatus::cases(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Event::class);

        return view('leader.events.create', [
            'audienceOptions' => $this->audienceRules->options(),
        ]);
    }

    public function store(StoreEventRequest $request): RedirectResponse
    {
        $this->authorize('create', Event::class);

        $event = Event::create([
            ...$request->safe()->only(['title', 'description', 'event_date', 'venue', 'status']),
            'organizer_id' => $request->user()->getKey(),
        ]);

        $rules = $this->audienceRules->persist($this->audienceRules->build($request->audiencePayload()));
        $event->audienceRules()->sync($rules->pluck('id'));

        if ($request->hasFile('attachment')) {
            $this->attachments->storeFor($event, $request->file('attachment'), $request->user()->getKey());
        }

        $this->audit->log('created', $event, null, ['title' => $event->title], $request);

        return redirect()
            ->route('leader.events.show', $event)
            ->with('success', 'Event created.');
    }

    public function show(Event $event): View
    {
        $this->authorize('view', $event);

        return view('leader.events.show', [
            'event' => $event->load(['organizer', 'audienceRules', 'attachments']),
            'audienceSummary' => $this->audienceRules->describe($event->audienceRules),
        ]);
    }

    public function edit(Event $event): View
    {
        $this->authorize('update', $event);

        return view('leader.events.edit', [
            'event' => $event->load('audienceRules'),
            'audienceOptions' => $this->audienceRules->options(),
            'audienceSummary' => $this->audienceRules->describe($event->audienceRules),
        ]);
    }

    public function update(StoreEventRequest $request, Event $event): RedirectResponse
    {
        $this->authorize('update', $event);

        $old = $event->only(['title', 'status', 'event_date']);

        $event->update($request->safe()->only(['title', 'description', 'event_date', 'venue', 'status']));

        $rules = $this->audienceRules->persist($this->audienceRules->build($request->audiencePayload()));
        $event->audienceRules()->sync($rules->pluck('id'));

        if ($request->hasFile('attachment')) {
            $existing = $event->attachments()->first();

            if ($existing !== null) {
                $this->attachments->replace($existing, $request->file('attachment'), $request->user()->getKey());
            } else {
                $this->attachments->storeFor($event, $request->file('attachment'), $request->user()->getKey());
            }
        }

        $this->audit->log('updated', $event, $old, $event->only(['title', 'status', 'event_date']), $request);

        return redirect()
            ->route('leader.events.show', $event)
            ->with('success', 'Event updated.');
    }

    public function destroy(Request $request, Event $event): RedirectResponse
    {
        $this->authorize('delete', $event);

        $this->audit->log('deleted', $event, $event->only(['title', 'status']), null, $request);

        $event->delete();

        return redirect()
            ->route('leader.events.index')
            ->with('success', 'Event deleted.');
    }
}
