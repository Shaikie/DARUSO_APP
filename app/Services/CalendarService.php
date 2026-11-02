<?php

namespace App\Services;

use App\Enums\PollStatus;
use App\Models\CalendarEntry;
use App\Models\Event;
use App\Models\LeaderTask;
use App\Models\Meeting;
use App\Models\Poll;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

/**
 * Aggregates every dated object into one calendar view.
 *
 * The calendar is a projection, not a duplicate store: meetings, events, task
 * deadlines and poll closing dates remain in their own tables, while custom
 * entries live in `calendar_entries`.
 */
class CalendarService
{
    public function __construct(
        private readonly AudienceResolver $audience,
    ) {}

    /**
     * @return Collection<int, array{date: string, title: string, subtitle: string, type: string, badge: string, url: ?string, id?: int}>
     */
    public function forUser(User $user, CarbonInterface $from, CarbonInterface $to): Collection
    {
        $fromDate = $from->toDateString();
        $toDate = $to->toDateString();

        $events = $this->audience->constrainSubjectForUser(
            Event::query()
                ->whereBetween('event_date', [$fromDate, $toDate]),
            Event::class,
            $user,
        )->get()->map(fn (Event $event): array => [
            'date' => $event->event_date->toDateString(),
            'title' => $event->title,
            'subtitle' => $event->venue,
            'type' => 'Event',
            'badge' => 'text-bg-primary',
            'url' => route($user->isLeader() ? 'leader.events.show' : 'student.events.show', $event),
        ]);

        $meetings = $this->audience->constrainSubjectForUser(
            Meeting::query()
                ->whereBetween('meeting_date', [$fromDate, $toDate]),
            Meeting::class,
            $user,
        )->get()->map(fn (Meeting $meeting): array => [
            'date' => $meeting->meeting_date->toDateString(),
            'title' => $meeting->title,
            'subtitle' => trim(($meeting->meeting_time?->format('H:i') ?? '').' · '.$meeting->venue, ' ·'),
            'type' => 'Meeting',
            'badge' => 'text-bg-info',
            'url' => route($user->isLeader() ? 'leader.meetings.show' : 'student.meetings.show', $meeting),
        ]);

        $tasks = $user->hasPermission('task.view')
            ? LeaderTask::query()
                ->with(['assignee'])
                ->where(fn ($query) => $query->where('assigned_to', $user->getKey())->orWhere('assigned_by', $user->getKey()))
                ->whereBetween('due_date', [$fromDate, $toDate])
                ->get()->map(fn (LeaderTask $task): array => [
                    'date' => $task->due_date->toDateString(),
                    'title' => 'Task due: '.$task->title,
                    'subtitle' => $task->assigned_to === $user->getKey() ? 'Assigned to you' : 'Assigned to '.$task->assignee?->name,
                    'type' => 'Task',
                    'badge' => 'text-bg-warning',
                    'url' => route('leader.tasks.show', $task),
                ])
            : collect();

        $polls = Poll::query()
            ->whereIn('status', [PollStatus::Open->value, PollStatus::Closed->value])
            ->whereBetween('ends_at', [$fromDate.' 00:00:00', $toDate.' 23:59:59'])
            ->get()->map(fn (Poll $poll): array => [
                'date' => $poll->ends_at->toDateString(),
                'title' => 'Poll closes: '.$poll->title,
                'subtitle' => 'Voting closes',
                'type' => 'Poll',
                'badge' => 'text-bg-success',
                'url' => $user->isLeader() ? route('leader.polls.show', $poll) : route('student.polls.show', $poll),
            ]);

        $entries = CalendarEntry::query()
            ->between($fromDate, $toDate)
            ->where(function ($query) use ($user): void {
                $query->where('audience', 'all');

                if ($user->isStudent()) {
                    $query->orWhere('audience', 'students');
                }

                if ($user->isLeader()) {
                    $query->orWhere('audience', 'leaders');
                }
            })
            ->get()->map(fn (CalendarEntry $entry): array => [
                'date' => $entry->start_date->toDateString(),
                'title' => $entry->title,
                'subtitle' => trim(($entry->start_time ?? '').' '.($entry->location ?? ''), ' '),
                'type' => $entry->type->label(),
                'badge' => $entry->type->badgeClass(),
                'url' => null,
                'id' => $entry->getKey(),
            ]);

        return $events
            ->concat($meetings)
            ->concat($tasks)
            ->concat($polls)
            ->concat($entries)
            ->sortBy('date')
            ->values();
    }

    /**
     * @return Collection<int, array{date: string, title: string, subtitle: string, type: string, badge: string, url: ?string}>
     */
    public function upcomingFor(User $user, int $days = 30): Collection
    {
        return $this->forUser($user, now()->startOfDay(), now()->addDays($days)->endOfDay())
            ->filter(fn (array $entry): bool => $entry['date'] >= now()->toDateString())
            ->values();
    }
}
