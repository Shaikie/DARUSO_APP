<?php

namespace App\Services;

use App\Enums\AnnouncementStatus;
use App\Enums\PermissionName;
use App\Models\Announcement;
use App\Models\CalendarEntry;
use App\Models\Complaint;
use App\Models\Document;
use App\Models\Event;
use App\Models\Feedback;
use App\Models\Handover;
use App\Models\LeaderTask;
use App\Models\Meeting;
use App\Models\Poll;
use App\Models\PaymentRequest;
use App\Models\Post;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Support\Str;

/**
 * Authorization-aware global search.
 *
 * Every group reuses the same policy, permission or audience rule as its normal
 * listing, so search cannot be used to discover a record the user could not open
 * directly. Each group is independently limited; results are never combined into
 * one unbounded OR query.
 */
class GlobalSearchService
{
    private const PER_GROUP = 6;

    public function __construct(
        private readonly AudienceResolver $audience,
    ) {}

    /**
     * @return array{term: string, groups: array<int, array{label: string, icon: string, results: array<int, array{title: string, meta: string, url: string}>}>, total: int}
     */
    public function search(User $user, string $term): array
    {
        $term = trim($term);
        $groups = [];

        if (mb_strlen($term) < 2) {
            return ['term' => $term, 'groups' => [], 'total' => 0];
        }

        $groups = array_values(array_filter([
            $this->announcements($user, $term),
            $this->posts($term),
            $this->documents($user, $term),
            $this->events($user, $term),
            $this->meetings($user, $term),
            $this->students($user, $term),
            $this->complaints($user, $term),
            $this->feedback($user, $term),
            $this->tasks($user, $term),
            $this->handovers($user, $term),
            $this->polls($user, $term),
            $this->payments($user, $term),
            $this->calendarEntries($user, $term),
        ]));

        return [
            'term' => $term,
            'groups' => $groups,
            'total' => array_sum(array_map(fn (array $group): int => count($group['results']), $groups)),
        ];
    }

    /**
     * @return array{label: string, icon: string, results: array<int, array{title: string, meta: string, url: string}>}|null
     */
    private function announcements(User $user, string $term): ?array
    {
        $query = Announcement::query()->search($term, ['title', 'content']);

        if (! $user->hasPermission(PermissionName::AnnouncementPublish->value)) {
            $query->where('status', AnnouncementStatus::Published->value);
            $this->audience->constrainSubjectForUser($query, Announcement::class, $user);
        }

        $results = $query->latest()->limit(self::PER_GROUP)->get()
            ->map(fn (Announcement $item): array => [
                'title' => $item->title,
                'meta' => $item->published_at?->format('d M Y') ?? $item->status->label(),
                'url' => route($user->isLeader() ? 'leader.announcements.show' : 'student.announcements.show', $item),
            ]);

        return $this->group('Announcements', 'megaphone', $results);
    }

    /**
     * @return array{label: string, icon: string, results: array<int, array{title: string, meta: string, url: string}>}|null
     */
    private function posts(string $term): ?array
    {
        $results = Post::query()->published()->search($term, ['title', 'excerpt', 'content'])
            ->latest('published_at')->limit(self::PER_GROUP)->get()
            ->map(fn (Post $item): array => [
                'title' => $item->title,
                'meta' => 'Community post · '.$item->published_at?->diffForHumans(),
                'url' => route('student.posts.show', $item),
            ]);

        return $this->group('Community posts', 'newspaper', $results);
    }

    /**
     * @return array{label: string, icon: string, results: array<int, array{title: string, meta: string, url: string}>}|null
     */
    private function documents(User $user, string $term): ?array
    {
        $results = Document::query()->visibleTo($user)->search($term, ['title', 'description'])
            ->latest()->limit(self::PER_GROUP)->get()
            ->map(fn (Document $item): array => [
                'title' => $item->title,
                'meta' => $item->category->label().' · v'.$item->version,
                'url' => route($user->isLeader() ? 'leader.documents.show' : 'student.documents.show', $item),
            ]);

        return $this->group('Documents', 'folder2-open', $results);
    }

    /**
     * @return array{label: string, icon: string, results: array<int, array{title: string, meta: string, url: string}>}|null
     */
    private function events(User $user, string $term): ?array
    {
        $query = Event::query()->search($term, ['title', 'description', 'venue']);
        $this->audience->constrainSubjectForUser($query, Event::class, $user);

        $results = $query->latest('event_date')->limit(self::PER_GROUP)->get()
            ->map(fn (Event $item): array => [
                'title' => $item->title,
                'meta' => $item->event_date->format('d M Y').' · '.$item->venue,
                'url' => route($user->isLeader() ? 'leader.events.show' : 'student.events.show', $item),
            ]);

        return $this->group('Events', 'calendar-check', $results);
    }

    /**
     * @return array{label: string, icon: string, results: array<int, array{title: string, meta: string, url: string}>}|null
     */
    private function meetings(User $user, string $term): ?array
    {
        $query = Meeting::query()->search($term, ['title', 'description', 'venue']);
        $this->audience->constrainSubjectForUser($query, Meeting::class, $user);

        $results = $query->latest('meeting_date')->limit(self::PER_GROUP)->get()
            ->map(fn (Meeting $item): array => [
                'title' => $item->title,
                'meta' => $item->meeting_date->format('d M Y').' · '.$item->venue,
                'url' => route($user->isLeader() ? 'leader.meetings.show' : 'student.meetings.show', $item),
            ]);

        return $this->group('Meetings', 'calendar-event', $results);
    }

    /**
     * @return array{label: string, icon: string, results: array<int, array{title: string, meta: string, url: string}>}|null
     */
    private function students(User $user, string $term): ?array
    {
        if (! $user->hasPermission(PermissionName::StudentView->value)
            && ! $user->hasPermission(PermissionName::StudentDirectory->value)) {
            return null;
        }

        $columns = $user->hasPermission(PermissionName::StudentViewSensitive->value)
            ? ['registration_number', 'programme', 'college']
            : ['programme', 'college'];

        $canSearchSensitive = $user->can('viewSensitive', new StudentProfile);

        $results = StudentProfile::query()
            ->verified()
            ->active()
            ->with('user')
            ->where(function ($query) use ($term, $canSearchSensitive): void {
                $query->whereRaw('LOWER(programme) LIKE ?', ["%{$term}%"])
                    ->orWhereHas('user', fn ($userQuery) => $userQuery->whereRaw('LOWER(users.name) LIKE ?', ["%{$term}%"]));

                if ($canSearchSensitive) {
                    $query->orWhereRaw('LOWER(registration_number) LIKE ?', ["%{$term}%"])
                        ->orWhereHas('user', fn ($userQuery) => $userQuery->whereRaw('LOWER(users.email) LIKE ?', ["%{$term}%"]));
                }
            })
            ->limit(self::PER_GROUP)
            ->get()
            ->map(fn (StudentProfile $item): array => [
                'title' => $item->user?->name ?? $item->registration_number,
                'meta' => $item->programme.' · Year '.$item->year_of_study,
                'url' => $user->hasPermission(PermissionName::StudentView->value)
                    ? route('leader.students.show', $item)
                    : route('leader.directory.show', $item),
            ]);

        return $this->group('Students', 'people', $results);
    }

    /**
     * @return array{label: string, icon: string, results: array<int, array{title: string, meta: string, url: string}>}|null
     */
    private function complaints(User $user, string $term): ?array
    {
        $query = Complaint::query()->search($term, ['title', 'description']);

        if ($user->hasPermission(PermissionName::ComplaintAssign->value)) {
            // Organisation-wide remit.
        } elseif ($user->hasPermission(PermissionName::ComplaintView->value)) {
            $ministryIds = $user->ministryIds();

            $query->where(function ($inner) use ($user, $ministryIds): void {
                $inner->where('creator_id', $user->getKey())
                    ->orWhere('assigned_leader_id', $user->getKey())
                    ->orWhereIn('assigned_ministry_id', $ministryIds->all());
            });
        } else {
            $query->where('creator_id', $user->getKey());
        }

        $results = $query->latest()->limit(self::PER_GROUP)->get()
            ->map(fn (Complaint $item): array => [
                'title' => $item->title,
                'meta' => $item->status->label().' · '.$item->category,
                'url' => $item->creator_id === $user->getKey()
                    ? route($user->isLeader() ? 'leader.complaints.show' : 'student.complaints.show', $item)
                    : route('leader.complaints.show', $item),
            ]);

        return $this->group('Complaints', 'life-preserver', $results);
    }

    /**
     * @return array{label: string, icon: string, results: array<int, array{title: string, meta: string, url: string}>}|null
     */
    private function feedback(User $user, string $term): ?array
    {
        $query = Feedback::query()->search($term, ['subject', 'message']);

        if (! $user->hasPermission(PermissionName::FeedbackView->value)) {
            $query->where('user_id', $user->getKey());
        }

        $results = $query->latest()->limit(self::PER_GROUP)->get()
            ->map(fn (Feedback $item): array => [
                'title' => $item->subject,
                'meta' => $item->status->label().' · '.$item->displayAuthor($user),
                'url' => $user->hasPermission(PermissionName::FeedbackView->value)
                    ? route('leader.feedback.show', $item)
                    : route('student.feedback.show', $item),
            ]);

        return $this->group('Feedback', 'chat-left-text', $results);
    }

    /**
     * @return array{label: string, icon: string, results: array<int, array{title: string, meta: string, url: string}>}|null
     */
    private function tasks(User $user, string $term): ?array
    {
        if (! $user->isLeader() || ! $user->hasPermission(PermissionName::TaskView->value)) {
            return null;
        }

        $results = LeaderTask::query()
            ->with(['assignee'])
            ->where(fn ($query) => $query->where('assigned_to', $user->getKey())->orWhere('assigned_by', $user->getKey()))
            ->search($term, ['title', 'description'])
            ->latest()
            ->limit(self::PER_GROUP)
            ->get()
            ->map(fn (LeaderTask $item): array => [
                'title' => $item->title,
                'meta' => $item->status->label().' · '.($item->assignee?->name ?? 'Unassigned'),
                'url' => route('leader.tasks.show', $item),
            ]);

        return $this->group('Tasks', 'list-task', $results);
    }

    /**
     * @return array{label: string, icon: string, results: array<int, array{title: string, meta: string, url: string}>}|null
     */
    private function handovers(User $user, string $term): ?array
    {
        if (! $user->isLeader() || ! $user->hasPermission(PermissionName::HandoverManage->value)) {
            return null;
        }

        $query = Handover::query()->search($term, ['title', 'summary']);
        $query->where(function ($inner) use ($user): void {
            $inner->where('from_user_id', $user->getKey())
                ->orWhere('to_user_id', $user->getKey());

            if ($user->isAdministrator() || $user->hasRole(\App\Enums\RoleName::SecretaryGeneral)) {
                $inner->orWhereRaw('1 = 1');
            }
        });

        $results = $query->latest()->limit(self::PER_GROUP)->get()
            ->map(fn (Handover $item): array => [
                'title' => $item->title,
                'meta' => $item->status->label().' · '.$item->fromUser?->name.' → '.$item->toUser?->name,
                'url' => route('leader.handovers.show', $item),
            ]);

        return $this->group('Handovers', 'arrow-left-right', $results);
    }

    /**
     * @return array{label: string, icon: string, results: array<int, array{title: string, meta: string, url: string}>}|null
     */
    private function polls(User $user, string $term): ?array
    {
        $results = Poll::query()->visible()->search($term, ['title', 'description'])
            ->latest()->limit(self::PER_GROUP)->get()
            ->map(fn (Poll $item): array => [
                'title' => $item->title,
                'meta' => $item->status->label().' · '.($item->ends_at?->diffForHumans() ?? 'No deadline'),
                'url' => $user->isLeader() ? route('leader.polls.show', $item) : route('student.polls.show', $item),
            ]);

        return $this->group('Polls', 'bar-chart', $results);
    }

    /**
     * Payment search is scoped to the signed-in student's eligible requests or
     * the payment manager's private administration area.
     *
     * @return array{label: string, icon: string, results: array<int, array{title: string, meta: string, url: string}>}|null
     */
    private function payments(User $user, string $term): ?array
    {
        if ($user->hasPermission(PermissionName::PaymentManage->value)) {
            $results = PaymentRequest::query()->search($term, ['title', 'description'])
                ->latest()->limit(self::PER_GROUP)->get()
                ->map(fn (PaymentRequest $item): array => [
                    'title' => $item->title,
                    'meta' => 'Payment request · TZS '.number_format((float) $item->amount, 2),
                    'url' => route('leader.payments.show', $item),
                ]);
        } elseif ($user->canParticipate() && $user->studentProfile !== null) {
            $results = PaymentRequest::query()
                ->whereIn('status', ['open', 'closed'])
                ->search($term, ['title', 'description'])
                ->latest()->limit(self::PER_GROUP)->get()
                ->filter(fn (PaymentRequest $item): bool => $item->matchesStudent($user->studentProfile))
                ->map(fn (PaymentRequest $item): array => [
                    'title' => $item->title,
                    'meta' => 'Payment request · TZS '.number_format((float) $item->amount, 2),
                    'url' => route('student.payments.show', $item),
                ]);
        } else {
            return null;
        }

        return $this->group('Payments', 'wallet2', $results);
    }

    /**
     * @return array{label: string, icon: string, results: array<int, array{title: string, meta: string, url: string}>}|null
     */
    private function calendarEntries(User $user, string $term): ?array
    {
        $results = CalendarEntry::query()
            ->where(function ($query) use ($user): void {
                $query->where('audience', 'all');

                if ($user->isStudent()) {
                    $query->orWhere('audience', 'students');
                }

                if ($user->isLeader()) {
                    $query->orWhere('audience', 'leaders');
                }
            })
            ->search($term, ['title', 'description', 'location'])
            ->orderBy('start_date')
            ->limit(self::PER_GROUP)
            ->get()
            ->map(fn (CalendarEntry $item): array => [
                'title' => $item->title,
                'meta' => $item->start_date->format('d M Y').' · '.$item->type->label(),
                'url' => route($user->isLeader() ? 'leader.calendar.index' : 'student.calendar.index'),
            ]);

        return $this->group('Calendar', 'calendar3', $results);
    }

    /**
     * @param  \Illuminate\Support\Collection<int, array{title: string, meta: string, url: string}>  $results
     * @return array{label: string, icon: string, results: array<int, array{title: string, meta: string, url: string}>}|null
     */
    private function group(string $label, string $icon, $results): ?array
    {
        if ($results->isEmpty()) {
            return null;
        }

        return [
            'label' => $label,
            'icon' => $icon,
            'results' => $results->map(fn (array $result): array => [
                'title' => Str::limit($result['title'], 120),
                'meta' => $result['meta'],
                'url' => $result['url'],
            ])->all(),
        ];
    }
}
