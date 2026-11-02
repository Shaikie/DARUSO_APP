<?php

namespace App\Http\Controllers;

use App\Enums\CalendarEntryType;
use App\Enums\FeedbackStatus;
use App\Enums\PollStatus;
use App\Enums\Priority;
use App\Enums\RoleName;
use App\Enums\TaskStatus;
use App\Models\CalendarEntry;
use App\Models\Committee;
use App\Models\Feedback;
use App\Models\Handover;
use App\Models\LeadershipInvitation;
use App\Models\LeadershipTerm;
use App\Models\LeaderTask;
use App\Models\Ministry;
use App\Models\MinistryActivityReport;
use App\Models\Poll;
use App\Models\PollOption;
use App\Models\Position;
use App\Models\StudentProfile;
use App\Models\User;
use App\Services\ActivityReportService;
use App\Services\CalendarService;
use App\Services\FeedbackService;
use App\Services\GlobalSearchService;
use App\Services\HandoverService;
use App\Services\LeadershipInvitationService;
use App\Services\NotificationPreferenceService;
use App\Services\PollService;
use App\Services\TaskService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PlatformController extends Controller
{
    public function __construct(
        private readonly FeedbackService $feedback,
        private readonly PollService $polls,
        private readonly CalendarService $calendar,
        private readonly TaskService $tasks,
        private readonly HandoverService $handovers,
        private readonly ActivityReportService $reports,
        private readonly LeadershipInvitationService $invitations,
        private readonly NotificationPreferenceService $preferences,
        private readonly GlobalSearchService $search,
    ) {}

    private function requirePermission(Request $request, string $permission): User
    {
        $user = $request->user();
        abort_unless($user && $user->hasPermission($permission), 403);

        return $user;
    }

    public function search(Request $request): View
    {
        $user = $request->user();
        $results = $this->search->search($user, (string) $request->query('q', ''));

        return view('platform.search', compact('results'));
    }

    public function preferences(Request $request): View
    {
        return view('platform.preferences', [
            'matrix' => $this->preferences->matrix($request->user()),
        ]);
    }

    public function updatePreferences(Request $request): RedirectResponse
    {
        $data = $request->validate(['preferences' => ['nullable', 'array']]);
        $this->preferences->update($request->user(), $data['preferences'] ?? []);

        return back()->with('success', 'Notification preferences saved.');
    }

    public function feedbackIndex(Request $request): View
    {
        $items = Feedback::query()->where('user_id', $request->user()->getKey())
            ->latest()->paginate(12);

        return view('platform.feedback.index', compact('items'));
    }

    public function feedbackCreate(): View
    {
        return view('platform.feedback.create');
    }

    public function feedbackStore(Request $request): RedirectResponse
    {
        abort_unless($request->user()->canParticipate(), 403, 'Student verification is required to submit feedback.');
        $data = $request->validate([
            'subject' => ['required', 'string', 'max:180'],
            'message' => ['required', 'string', 'max:10000'],
            'category' => ['required', Rule::in(['general', 'academic', 'welfare', 'hostel', 'other'])],
            'is_anonymous' => ['nullable', 'boolean'],
        ]);
        $item = $this->feedback->create($data, $request->user(), $request);

        return redirect()->route('student.feedback.show', $item)->with('success', 'Your feedback was submitted.');
    }

    public function feedbackShow(Request $request, Feedback $feedback): View
    {
        abort_unless($feedback->user_id === $request->user()->getKey(), 403);

        return view('platform.feedback.show', ['item' => $feedback->load(['author', 'responder'])]);
    }

    public function leaderFeedbackIndex(Request $request): View
    {
        $this->requirePermission($request, 'feedback.view');
        $items = Feedback::query()->with(['author', 'responder'])->latest()->paginate(15);

        return view('platform.leader-feedback', compact('items'));
    }

    public function leaderFeedbackShow(Request $request, Feedback $feedback): View
    {
        $this->requirePermission($request, 'feedback.view');

        return view('platform.leader-feedback-show', [
            'item' => $feedback->load(['author', 'responder']),
        ]);
    }

    public function leaderFeedbackRespond(Request $request, Feedback $feedback): RedirectResponse
    {
        $user = $this->requirePermission($request, 'feedback.respond');
        $data = $request->validate([
            'response' => ['required', 'string', 'max:10000'],
            'status' => ['required', Rule::enum(FeedbackStatus::class)],
        ]);
        try {
            $this->feedback->respond($feedback, $user, $data['response'], FeedbackStatus::from($data['status']), $request);
        } catch (\DomainException $exception) {
            return back()->withErrors(['feedback' => $exception->getMessage()]);
        }

        return back()->with('success', 'Feedback response sent.');
    }

    public function pollsIndex(): View
    {
        $items = Poll::query()->withCount('votes')->with('options')->visible()
            ->latest()->paginate(12);

        return view('platform.polls.index', compact('items'));
    }

    public function pollShow(Request $request, Poll $poll): View
    {
        abort_unless($poll->status !== PollStatus::Draft || $poll->created_by === $request->user()->getKey() || $request->user()->hasPermission('poll.manage'), 404);
        $poll->load('options');
        $hasVoted = $poll->votes()->where('user_id', $request->user()->getKey())->exists();

        return view('platform.polls.show', [
            'poll' => $poll,
            'hasVoted' => $hasVoted,
            'canVote' => $request->user()->isStudent() && $request->user()->canParticipate(),
            'results' => $poll->hasEnded() || $hasVoted || $request->user()->hasPermission('poll.manage') ? $this->polls->results($poll) : [],
        ]);
    }

    public function pollVote(Request $request, Poll $poll): RedirectResponse
    {
        abort_unless($request->user()->canParticipate(), 403, 'Only verified students can vote.');
        $data = $request->validate(['option_id' => ['required', 'integer', 'exists:poll_options,id']]);
        $option = PollOption::query()->findOrFail($data['option_id']);
        try {
            $this->polls->vote($poll, $option, $request->user(), $request);
        } catch (\DomainException $exception) {
            return back()->withErrors(['vote' => $exception->getMessage()]);
        }

        return back()->with('success', 'Your vote has been recorded.');
    }

    public function leaderPolls(Request $request): View
    {
        $this->requirePermission($request, 'poll.manage');
        $items = Poll::query()->withCount('votes')->with('options')->latest()->paginate(15);

        return view('platform.leader-polls', compact('items'));
    }

    public function pollCreate(Request $request): View
    {
        $this->requirePermission($request, 'poll.manage');
        return view('platform.poll-form', ['poll' => null]);
    }

    public function pollStore(Request $request): RedirectResponse
    {
        $user = $this->requirePermission($request, 'poll.manage');
        $data = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string', 'max:5000'],
            'options' => ['required', 'array', 'min:2', 'max:12'],
            'options.*' => ['required', 'string', 'max:180'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after:starts_at'],
            'is_anonymous' => ['nullable', 'boolean'],
        ]);
        $options = array_values(array_filter(array_map('trim', $data['options'])));
        abort_if(count($options) < 2, 422, 'A poll needs at least two non-empty options.');
        $poll = $this->polls->create($data, $options, $user, $request);

        return redirect()->route('leader.polls.index')->with('success', 'Poll created.');
    }

    public function pollEdit(Request $request, Poll $poll): View
    {
        $this->requirePermission($request, 'poll.manage');
        abort_if($poll->votes()->exists(), 403, 'A poll with recorded votes cannot be edited.');

        return view('platform.poll-form', ['poll' => $poll->load('options')]);
    }

    public function pollUpdate(Request $request, Poll $poll): RedirectResponse
    {
        $user = $this->requirePermission($request, 'poll.manage');
        $data = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string', 'max:5000'],
            'options' => ['required', 'array', 'min:2', 'max:12'],
            'options.*' => ['required', 'string', 'max:180'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after:starts_at'],
            'is_anonymous' => ['nullable', 'boolean'],
        ]);
        try {
            $this->polls->update($poll, $data, array_values(array_filter(array_map('trim', $data['options']))), $user, $request);
        } catch (\DomainException $exception) {
            return back()->withInput()->withErrors(['poll' => $exception->getMessage()]);
        }

        return redirect()->route('leader.polls.index')->with('success', 'Poll updated.');
    }

    public function pollOpen(Request $request, Poll $poll): RedirectResponse
    {
        $this->requirePermission($request, 'poll.manage');
        try {
            $this->polls->open($poll, $request->user(), $request);
        } catch (\DomainException $exception) {
            return back()->withErrors(['poll' => $exception->getMessage()]);
        }

        return back()->with('success', 'Poll opened.');
    }

    public function pollClose(Request $request, Poll $poll): RedirectResponse
    {
        $this->requirePermission($request, 'poll.manage');
        try {
            $this->polls->close($poll, $request->user(), $request);
        } catch (\DomainException $exception) {
            return back()->withErrors(['poll' => $exception->getMessage()]);
        }

        return back()->with('success', 'Poll closed.');
    }

    public function calendar(Request $request): View
    {
        $dates = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
        ]);
        $from = Carbon::parse($dates['from'] ?? now()->startOfMonth()->toDateString())->startOfDay();
        $to = Carbon::parse($dates['to'] ?? $from->copy()->addMonth()->endOfMonth()->toDateString())->endOfDay();
        abort_if($to->lt($from) || $from->diffInDays($to) > 93, 422, 'Choose a calendar range of up to 93 days.');

        return view('platform.calendar', [
            'entries' => $this->calendar->forUser($request->user(), $from, $to),
            'from' => $from,
            'to' => $to,
            'canManage' => $request->user()->hasPermission('calendar.manage'),
        ]);
    }

    public function calendarStore(Request $request): RedirectResponse
    {
        $user = $this->requirePermission($request, 'calendar.manage');
        $data = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string', 'max:5000'],
            'type' => ['required', Rule::enum(CalendarEntryType::class)],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'location' => ['nullable', 'string', 'max:180'],
            'audience' => ['required', Rule::in(['all', 'students', 'leaders'])],
            'is_all_day' => ['nullable', 'boolean'],
        ]);
        CalendarEntry::create([...$data, 'created_by' => $user->getKey(), 'is_all_day' => $request->boolean('is_all_day')]);

        return back()->with('success', 'Calendar entry created.');
    }

    public function calendarEdit(Request $request, CalendarEntry $entry): View
    {
        $this->requirePermission($request, 'calendar.manage');

        return view('platform.calendar-edit', ['entry' => $entry]);
    }

    public function calendarUpdate(Request $request, CalendarEntry $entry): RedirectResponse
    {
        $user = $this->requirePermission($request, 'calendar.manage');
        $data = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string', 'max:5000'],
            'type' => ['required', Rule::enum(CalendarEntryType::class)],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'location' => ['nullable', 'string', 'max:180'],
            'audience' => ['required', Rule::in(['all', 'students', 'leaders'])],
            'is_all_day' => ['nullable', 'boolean'],
        ]);
        $entry->update([...$data, 'is_all_day' => $request->boolean('is_all_day')]);

        return redirect()->route('leader.calendar.index')->with('success', 'Calendar entry updated.');
    }

    public function calendarDelete(Request $request, CalendarEntry $entry): RedirectResponse
    {
        $this->requirePermission($request, 'calendar.manage');
        $entry->delete();

        return back()->with('success', 'Calendar entry removed.');
    }

    public function tasksIndex(Request $request): View
    {
        $user = $this->requirePermission($request, 'task.view');
        $items = LeaderTask::query()->with(['assignee', 'assigner', 'ministry', 'committee'])
            ->where(fn ($query) => $query->where('assigned_to', $user->getKey())->orWhere('assigned_by', $user->getKey()))
            ->latest()->paginate(15);

        return view('platform.tasks.index', compact('items'));
    }

    public function taskCreate(Request $request): View
    {
        $this->requirePermission($request, 'task.assign');
        return view('platform.tasks.create', [
            'leaders' => User::query()->whereHas('roles', fn ($q) => $q->where('name', '!=', RoleName::Student->value))->orderBy('name')->get(),
            'ministries' => Ministry::orderBy('name')->get(),
            'committees' => Committee::orderBy('name')->get(),
        ]);
    }

    public function taskStore(Request $request): RedirectResponse
    {
        $user = $this->requirePermission($request, 'task.assign');
        $data = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string', 'max:5000'],
            'assigned_to' => ['required', 'exists:users,id'],
            'ministry_id' => ['nullable', 'exists:ministries,id'],
            'committee_id' => ['nullable', 'exists:committees,id'],
            'priority' => ['required', Rule::enum(Priority::class)],
            'due_date' => ['nullable', 'date'],
        ]);
        $assignee = User::findOrFail($data['assigned_to']);
        abort_unless($assignee->isLeader(), 422, 'Tasks can only be assigned to leaders.');
        if (! $user->isAdministrator() && ! $user->hasRole(RoleName::SecretaryGeneral)) {
            $sharesMinistry = ($data['ministry_id'] ?? null) !== null
                && $user->ministryIds()->contains((int) ($data['ministry_id'] ?? 0))
                && $assignee->ministryIds()->contains((int) ($data['ministry_id'] ?? 0));
            $sharesCommittee = ($data['committee_id'] ?? null) !== null
                && $user->committeeIds()->contains((int) ($data['committee_id'] ?? 0))
                && $assignee->committeeIds()->contains((int) ($data['committee_id'] ?? 0));
            abort_unless($sharesMinistry || $sharesCommittee, 403, 'You can only assign tasks within your active ministry or committee.');
        }
        $this->tasks->create($data, $user, $request);

        return redirect()->route('leader.tasks.index')->with('success', 'Task assigned.');
    }

    public function taskShow(Request $request, LeaderTask $task): View
    {
        $user = $this->requirePermission($request, 'task.view');
        abort_unless($task->assigned_to === $user->getKey() || $task->assigned_by === $user->getKey() || $user->hasPermission('task.manage'), 403);

        return view('platform.tasks.show', ['task' => $task->load(['assignee', 'assigner', 'ministry', 'committee'])]);
    }

    public function taskStatus(Request $request, LeaderTask $task): RedirectResponse
    {
        $user = $this->requirePermission($request, 'task.view');
        abort_unless($task->assigned_to === $user->getKey() || $task->assigned_by === $user->getKey() || $user->hasPermission('task.manage'), 403);
        $data = $request->validate(['status' => ['required', Rule::enum(TaskStatus::class)]]);
        $this->tasks->updateStatus($task, TaskStatus::from($data['status']), $user, $request);

        return back()->with('success', 'Task status updated.');
    }

    public function handoversIndex(Request $request): View
    {
        $user = $this->requirePermission($request, 'handover.manage');
        $items = Handover::query()->with(['fromUser', 'toUser', 'ministry', 'committee'])
            ->where(fn ($q) => $q->where('from_user_id', $user->getKey())->orWhere('to_user_id', $user->getKey()))
            ->latest()->paginate(15);
        if ($user->isAdministrator() || $user->hasRole(RoleName::SecretaryGeneral)) {
            $items = Handover::query()->with(['fromUser', 'toUser'])->latest()->paginate(15);
        }

        return view('platform.handovers.index', compact('items'));
    }

    public function handoverCreate(Request $request): View
    {
        $this->requirePermission($request, 'handover.manage');
        return view('platform.handovers.create', [
            'leaders' => User::query()->whereHas('roles', fn ($q) => $q->where('name', '!=', RoleName::Student->value))->orderBy('name')->get(),
            'ministries' => Ministry::orderBy('name')->get(),
            'committees' => Committee::orderBy('name')->get(),
            'terms' => LeadershipTerm::orderByDesc('start_date')->get(),
        ]);
    }

    public function handoverStore(Request $request): RedirectResponse
    {
        $user = $this->requirePermission($request, 'handover.manage');
        $data = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'summary' => ['required', 'string', 'max:10000'],
            'outstanding_items' => ['nullable', 'string', 'max:10000'],
            'recommendations' => ['nullable', 'string', 'max:10000'],
            'to_user_id' => ['required', 'exists:users,id', Rule::notIn([$user->getKey()])],
            'ministry_id' => ['nullable', 'exists:ministries,id'],
            'committee_id' => ['nullable', 'exists:committees,id'],
            'leadership_term_id' => ['nullable', 'exists:leadership_terms,id'],
        ]);
        $recipient = User::findOrFail($data['to_user_id']);
        abort_unless($recipient->isLeader(), 422, 'A handover recipient must be a leader.');
        if (! $user->isAdministrator() && ! $user->hasRole(RoleName::SecretaryGeneral)) {
            $sharesMinistry = ($data['ministry_id'] ?? null) !== null
                && $user->ministryIds()->contains((int) $data['ministry_id'])
                && $recipient->ministryIds()->contains((int) $data['ministry_id']);
            $sharesCommittee = ($data['committee_id'] ?? null) !== null
                && $user->committeeIds()->contains((int) $data['committee_id'])
                && $recipient->committeeIds()->contains((int) $data['committee_id']);
            abort_unless($sharesMinistry || $sharesCommittee, 403, 'You can only hand over work within your active ministry or committee.');
        }
        $this->handovers->create($data, $user, $request);

        return redirect()->route('leader.handovers.index')->with('success', 'Handover drafted.');
    }

    public function handoverShow(Request $request, Handover $handover): View
    {
        $user = $this->requirePermission($request, 'handover.manage');
        abort_unless($handover->from_user_id === $user->getKey() || $handover->to_user_id === $user->getKey() || $user->isAdministrator() || $user->hasRole(RoleName::SecretaryGeneral), 403);

        return view('platform.handovers.show', ['handover' => $handover->load(['fromUser', 'toUser', 'ministry', 'committee', 'term'])]);
    }

    public function handoverEdit(Request $request, Handover $handover): View
    {
        $user = $this->requirePermission($request, 'handover.manage');
        abort_unless($handover->from_user_id === $user->getKey() && $handover->status->value === 'draft', 403);

        return view('platform.handovers.edit', [
            'handover' => $handover,
            'leaders' => User::query()->whereHas('roles', fn ($q) => $q->where('name', '!=', RoleName::Student->value))->orderBy('name')->get(),
            'ministries' => Ministry::orderBy('name')->get(),
            'committees' => Committee::orderBy('name')->get(),
            'terms' => LeadershipTerm::orderByDesc('start_date')->get(),
        ]);
    }

    public function handoverUpdate(Request $request, Handover $handover): RedirectResponse
    {
        $user = $this->requirePermission($request, 'handover.manage');
        abort_unless($handover->from_user_id === $user->getKey(), 403);
        $data = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'summary' => ['required', 'string', 'max:10000'],
            'outstanding_items' => ['nullable', 'string', 'max:10000'],
            'recommendations' => ['nullable', 'string', 'max:10000'],
            'to_user_id' => ['required', 'exists:users,id', Rule::notIn([$user->getKey()])],
            'ministry_id' => ['nullable', 'exists:ministries,id'],
            'committee_id' => ['nullable', 'exists:committees,id'],
            'leadership_term_id' => ['nullable', 'exists:leadership_terms,id'],
        ]);
        $recipient = User::findOrFail($data['to_user_id']);
        abort_unless($recipient->isLeader(), 422, 'A handover recipient must be a leader.');
        if (! $user->isAdministrator() && ! $user->hasRole(RoleName::SecretaryGeneral)) {
            $sharesMinistry = ($data['ministry_id'] ?? null) !== null
                && $user->ministryIds()->contains((int) ($data['ministry_id'] ?? 0))
                && $recipient->ministryIds()->contains((int) ($data['ministry_id'] ?? 0));
            $sharesCommittee = ($data['committee_id'] ?? null) !== null
                && $user->committeeIds()->contains((int) ($data['committee_id'] ?? 0))
                && $recipient->committeeIds()->contains((int) ($data['committee_id'] ?? 0));
            abort_unless($sharesMinistry || $sharesCommittee, 403, 'You can only hand over work within your active ministry or committee.');
        }

        try {
            $this->handovers->update($handover, $data, $user, $request);
        } catch (\DomainException $exception) {
            return back()->withInput()->withErrors(['handover' => $exception->getMessage()]);
        }

        return redirect()->route('leader.handovers.show', $handover)->with('success', 'Handover draft updated.');
    }

    public function handoverSubmit(Request $request, Handover $handover): RedirectResponse
    {
        $user = $this->requirePermission($request, 'handover.manage');
        abort_unless($handover->from_user_id === $user->getKey() || $user->isAdministrator() || $user->hasRole(RoleName::SecretaryGeneral), 403);
        try {
            $this->handovers->submit($handover, $user, $request);
        } catch (\DomainException $exception) {
            return back()->withErrors(['handover' => $exception->getMessage()]);
        }

        return back()->with('success', 'Handover submitted.');
    }

    public function handoverAcknowledge(Request $request, Handover $handover): RedirectResponse
    {
        $user = $this->requirePermission($request, 'handover.manage');
        try {
            $this->handovers->acknowledge($handover, $user, $request);
        } catch (\DomainException $exception) {
            return back()->withErrors(['handover' => $exception->getMessage()]);
        }

        return back()->with('success', 'Handover acknowledged.');
    }

    public function activityReportsIndex(Request $request): View
    {
        $user = $request->user();
        abort_unless($user->hasPermission('activity_report.submit') || $user->hasPermission('activity_report.review'), 403);
        $query = MinistryActivityReport::query()->with(['ministry', 'term', 'submitter', 'reviewer']);
        if (! $user->hasPermission('activity_report.review')) {
            $query->whereIn('ministry_id', $user->ministryIds())->where('submitted_by', $user->getKey());
        }
        $items = $query->latest()->paginate(15);

        return view('platform.activity-reports.index', compact('items'));
    }

    public function activityReportCreate(Request $request): View
    {
        $user = $this->requirePermission($request, 'activity_report.submit');
        $ministries = Ministry::query()->whereIn('id', $user->ministryIds())->orderBy('name')->get();
        abort_if($ministries->isEmpty() && ! $user->isAdministrator(), 403, 'You need an active ministry assignment to submit a report.');

        return view('platform.activity-reports.create', [
            'ministries' => $user->isAdministrator() ? Ministry::orderBy('name')->get() : $ministries,
            'terms' => LeadershipTerm::orderByDesc('start_date')->get(),
        ]);
    }

    public function activityReportStore(Request $request): RedirectResponse
    {
        $user = $this->requirePermission($request, 'activity_report.submit');
        $data = $request->validate([
            'ministry_id' => ['required', 'exists:ministries,id'],
            'leadership_term_id' => ['nullable', 'exists:leadership_terms,id'],
            'period_start' => ['required', 'date'],
            'period_end' => ['required', 'date', 'after_or_equal:period_start'],
            'summary' => ['required', 'string', 'max:10000'],
            'achievements' => ['nullable', 'string', 'max:10000'],
            'challenges' => ['nullable', 'string', 'max:10000'],
            'recommendations' => ['nullable', 'string', 'max:10000'],
        ]);
        abort_unless($user->isAdministrator() || $user->ministryIds()->contains((int) $data['ministry_id']), 403);
        $this->reports->create($data, $user, $request);

        return redirect()->route('leader.activity-reports.index')->with('success', 'Activity report saved as a draft.');
    }

    public function activityReportShow(Request $request, MinistryActivityReport $report): View
    {
        $user = $request->user();
        abort_unless($user->hasPermission('activity_report.review') || ($report->submitted_by === $user->getKey() && $user->hasPermission('activity_report.submit')), 403);

        return view('platform.activity-reports.show', ['report' => $report->load(['ministry', 'term', 'submitter', 'reviewer'])]);
    }

    public function activityReportEdit(Request $request, MinistryActivityReport $report): View
    {
        $user = $this->requirePermission($request, 'activity_report.submit');
        abort_unless($report->submitted_by === $user->getKey() || $user->isAdministrator(), 403);
        abort_if($report->status->value === 'reviewed', 403);

        $ministries = $user->isAdministrator()
            ? Ministry::orderBy('name')->get()
            : Ministry::query()->whereIn('id', $user->ministryIds())->orderBy('name')->get();

        return view('platform.activity-reports.edit', [
            'report' => $report,
            'ministries' => $ministries,
            'terms' => LeadershipTerm::orderByDesc('start_date')->get(),
        ]);
    }

    public function activityReportUpdate(Request $request, MinistryActivityReport $report): RedirectResponse
    {
        $user = $this->requirePermission($request, 'activity_report.submit');
        abort_unless($report->submitted_by === $user->getKey() || $user->isAdministrator(), 403);
        $data = $request->validate([
            'ministry_id' => ['required', 'exists:ministries,id'],
            'leadership_term_id' => ['nullable', 'exists:leadership_terms,id'],
            'period_start' => ['required', 'date'],
            'period_end' => ['required', 'date', 'after_or_equal:period_start'],
            'summary' => ['required', 'string', 'max:10000'],
            'achievements' => ['nullable', 'string', 'max:10000'],
            'challenges' => ['nullable', 'string', 'max:10000'],
            'recommendations' => ['nullable', 'string', 'max:10000'],
        ]);
        abort_unless($user->isAdministrator() || $user->ministryIds()->contains((int) $data['ministry_id']), 403);
        try {
            $this->reports->update($report, $data, $user, $request);
        } catch (\DomainException $exception) {
            return back()->withInput()->withErrors(['report' => $exception->getMessage()]);
        }

        return redirect()->route('leader.activity-reports.show', $report)->with('success', 'Report updated.');
    }

    public function activityReportSubmit(Request $request, MinistryActivityReport $report): RedirectResponse
    {
        $user = $this->requirePermission($request, 'activity_report.submit');
        abort_unless($report->submitted_by === $user->getKey() || $user->isAdministrator(), 403);
        try {
            $this->reports->submit($report, $user, $request);
        } catch (\DomainException $exception) {
            return back()->withErrors(['report' => $exception->getMessage()]);
        }

        return back()->with('success', 'Report submitted for review.');
    }

    public function activityReportReview(Request $request, MinistryActivityReport $report): RedirectResponse
    {
        $user = $this->requirePermission($request, 'activity_report.review');
        $data = $request->validate(['review_notes' => ['required', 'string', 'max:5000']]);
        try {
            $this->reports->review($report, $user, $data['review_notes'], $request);
        } catch (\DomainException $exception) {
            return back()->withErrors(['report' => $exception->getMessage()]);
        }

        return back()->with('success', 'Report reviewed.');
    }

    public function invitationsIndex(Request $request): View
    {
        $user = $this->requirePermission($request, 'leader.invite');
        $items = LeadershipInvitation::query()->with(['inviter', 'acceptedBy', 'ministry', 'committee', 'position', 'term'])
            ->when(! $user->isAdministrator(), fn ($q) => $q->where('invited_by', $user->getKey()))
            ->latest()->paginate(15);

        return view('platform.invitations.index', compact('items'));
    }

    public function invitationCreate(Request $request): View
    {
        $this->requirePermission($request, 'leader.invite');
        return view('platform.invitations.create', [
            'roles' => collect(RoleName::cases())->reject(fn ($role) => $role === RoleName::Student)->values(),
            'ministries' => Ministry::orderBy('name')->get(),
            'committees' => Committee::orderBy('name')->get(),
            'positions' => Position::orderBy('hierarchy_level')->get(),
            'terms' => LeadershipTerm::orderByDesc('start_date')->get(),
        ]);
    }

    public function invitationStore(Request $request): RedirectResponse
    {
        $user = $this->requirePermission($request, 'leader.invite');
        $data = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'role_name' => ['required', Rule::in(RoleName::leadershipValues())],
            'ministry_id' => ['nullable', 'exists:ministries,id'],
            'committee_id' => ['nullable', 'exists:committees,id'],
            'position_id' => ['nullable', 'exists:positions,id'],
            'leadership_term_id' => ['nullable', 'exists:leadership_terms,id'],
            'expires_in_days' => ['required', 'integer', 'min:1', 'max:30'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);
        if (! $user->isAdministrator() && ! $user->hasRole(RoleName::SecretaryGeneral)) {
            abort_unless($data['role_name'] === RoleName::MinistryLeader->value, 403, 'Ministry leaders may only invite another leader into their own ministry.');
            abort_unless(($data['ministry_id'] ?? null) !== null && $user->ministryIds()->contains((int) ($data['ministry_id'] ?? 0)), 403);
        }

        try {
            $issued = $this->invitations->invite($data, $user, $request);
        } catch (\DomainException $exception) {
            return back()->withInput()->withErrors(['invitation' => $exception->getMessage()]);
        }

        try {
            $this->invitations->send($issued['invitation'], $issued['token']);
        } catch (\Throwable $exception) {
            $this->invitations->revoke($issued['invitation'], $user, $request);
            report($exception);

            return back()->withInput()->withErrors(['invitation' => 'The invitation could not be emailed. Check the mail transport and try again.']);
        }

        return redirect()->route('leader.invitations.index')->with('success', 'Invitation sent to '.$issued['invitation']->email.'.');
    }

    public function invitationRevoke(Request $request, LeadershipInvitation $invitation): RedirectResponse
    {
        $user = $this->requirePermission($request, 'leader.invite');
        abort_unless($user->isAdministrator() || $invitation->invited_by === $user->getKey(), 403);
        try {
            $this->invitations->revoke($invitation, $user, $request);
        } catch (\DomainException $exception) {
            return back()->withErrors(['invitation' => $exception->getMessage()]);
        }

        return back()->with('success', 'Invitation revoked.');
    }

    public function invitationAccept(Request $request, LeadershipInvitation $invitation): RedirectResponse
    {
        abort_unless($request->hasValidSignature(), 403);
        $data = $request->validate(['token' => ['required', 'string', 'min:20', 'max:200']]);
        try {
            $this->invitations->accept($invitation, $data['token'], $request->user(), $request);
        } catch (\DomainException $exception) {
            return redirect()->route('dashboard')->withErrors(['invitation' => $exception->getMessage()]);
        }

        return redirect()->route('dashboard')->with('success', 'Your leadership role is now active.');
    }

    public function directoryIndex(Request $request): View
    {
        $user = $this->requirePermission($request, 'student.directory');
        $profiles = StudentProfile::query()->with('user')->verified()->where('status', 'active')
            ->when($request->filled('q'), fn ($q) => $q->search((string) $request->query('q'), ['programme', 'college']))
            ->when($request->filled('college'), fn ($q) => $q->where('college', $request->query('college')))
            ->orderBy('college')->orderBy('programme')->paginate(24);

        return view('platform.directory.index', compact('profiles'));
    }

    public function directoryShow(Request $request, StudentProfile $profile): View
    {
        $this->requirePermission($request, 'student.directory');
        abort_unless($profile->isVerified() && $profile->status->value === 'active', 404);

        return view('platform.directory.show', compact('profile'));
    }
}
