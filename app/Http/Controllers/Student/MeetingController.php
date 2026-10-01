<?php

namespace App\Http\Controllers\Student;

use App\Enums\MeetingStatus;
use App\Http\Controllers\Controller;
use App\Models\Meeting;
use App\Services\AudienceResolver;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Meetings as seen by an attendee.
 */
class MeetingController extends Controller
{
    public function __construct(
        private readonly AudienceResolver $audience,
    ) {}

    public function index(Request $request): View
    {
        $user = $request->user();

        // Visible if the meeting's audience covers this student, or if they
        // organised it. Both conditions live in one `where` group so the
        // audience check is a single correlated EXISTS.
        $meetings = Meeting::query()
            ->with('organizer')
            ->where(function (Builder $query) use ($user): void {
                $this->audience->constrainSubjectForUser($query, Meeting::class, $user);
                $query->orWhere('organizer_id', $user->getKey());
            })
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->when($request->boolean('upcoming'), fn ($query) => $query->upcoming())
            ->orderByDesc('meeting_date')
            ->orderByDesc('meeting_time')
            ->paginate(10)
            ->withQueryString();

        return view('student.meetings.index', [
            'meetings' => $meetings,
            'statuses' => MeetingStatus::cases(),
        ]);
    }

    public function show(Request $request, Meeting $meeting): View
    {
        $this->authorize('view', $meeting);

        return view('student.meetings.show', [
            'meeting' => $meeting->load(['organizer', 'attachments']),
        ]);
    }

    /**
     * Authorized attachment download for a meeting.
     */
    public function download(Request $request, Meeting $meeting): StreamedResponse
    {
        $this->authorize('download', $meeting);

        $attachment = $meeting->attachments()->firstOrFail();

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
