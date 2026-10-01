<?php

namespace App\Http\Controllers\Student;

use App\Enums\EventStatus;
use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Services\AudienceResolver;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Events as seen by an attendee.
 */
class EventController extends Controller
{
    public function __construct(
        private readonly AudienceResolver $audience,
    ) {}

    public function index(Request $request): View
    {
        $user = $request->user();

        // Visible if the event's audience covers this student, or if they
        // organised it.
        $events = Event::query()
            ->with('organizer')
            ->where(function (Builder $query) use ($user): void {
                $this->audience->constrainSubjectForUser($query, Event::class, $user);
                $query->orWhere('organizer_id', $user->getKey());
            })
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->when($request->boolean('upcoming'), fn ($query) => $query->upcoming())
            ->orderByDesc('event_date')
            ->paginate(10)
            ->withQueryString();

        return view('student.events.index', [
            'events' => $events,
            'statuses' => EventStatus::cases(),
        ]);
    }

    public function show(Request $request, Event $event): View
    {
        $this->authorize('view', $event);

        return view('student.events.show', [
            'event' => $event->load(['organizer', 'attachments']),
        ]);
    }

    public function download(Request $request, Event $event): StreamedResponse
    {
        $this->authorize('download', $event);

        $attachment = $event->attachments()->firstOrFail();

        if (! Storage::disk($attachment->disk)->exists($attachment->file_path)) {
            abort(404, 'The stored file is missing.');
        }

        return Storage::disk($attachment->disk)->download(
            $attachment->file_path,
            $attachment->file_name,
            ['Content-Type' => 'application/octet-stream'],
        );
    }
}
