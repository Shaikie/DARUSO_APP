<?php

namespace App\Http\Controllers\Leader;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\AuditLog;
use App\Models\Complaint;
use App\Models\Event;
use App\Models\LeaderAssignment;
use App\Models\Meeting;
use App\Models\Notification;
use App\Models\StudentProfile;
use App\Services\NotificationDispatcher;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Leader dashboard.
 *
 * All figures are real aggregates; nothing is a placeholder. Scoping respects the
 * leader's own remit: a ministry leader sees their ministry's complaints rather
 * than every complaint in the organisation.
 */
class DashboardController extends Controller
{
    public function __construct(
        private readonly NotificationDispatcher $notifications,
        private readonly ReportService $reports,
    ) {}

    public function index(Request $request): View
    {
        $user = $request->user();
        $ministryIds = $user->ministryIds();

        // Complaints within this leader's remit.
        $complaintScope = fn () => Complaint::query()
            ->where(function ($query) use ($user, $ministryIds): void {
                $query->where('assigned_leader_id', $user->getKey())
                    ->orWhereIn('assigned_ministry_id', $ministryIds->all());

                if ($ministryIds->isEmpty()) {
                    $query->orWhereNull('assigned_ministry_id');
                }
            });

        return view('leader.dashboard', [
            'user' => $user,
            'unreadNotifications' => $this->notifications->unreadCount($user),
            'stats' => [
                'open_complaints' => (clone $complaintScope())->open()->count(),
                'unassigned_complaints' => (clone $complaintScope())
                    ->whereNull('assigned_ministry_id')
                    ->whereNull('assigned_leader_id')
                    ->count(),
                'total_students' => StudentProfile::query()->count(),
                'active_leaders' => LeaderAssignment::query()
                    ->whereHas('term', fn ($query) => $query->where('is_active', true))
                    ->distinct('user_id')
                    ->count('user_id'),
                'upcoming_meetings' => Meeting::query()->upcoming()->count(),
                'upcoming_events' => Event::query()->upcoming()->count(),
                'notifications_sent' => Notification::where('sender_id', $user->getKey())->count(),
            ],
            'recentComplaints' => (clone $complaintScope())
                ->with(['creator', 'assignedMinistry'])
                ->latest()
                ->limit(6)
                ->get(),
            'complaintsByStatus' => (clone $complaintScope())
                ->selectRaw('status, count(*) as aggregate')
                ->groupBy('status')
                ->pluck('aggregate', 'status'),
            'upcomingMeetings' => Meeting::query()
                ->with('organizer')
                ->upcoming()
                ->limit(5)
                ->get(),
            'upcomingEvents' => Event::query()->upcoming()->limit(5)->get(),
            'recentAnnouncements' => Announcement::query()
                ->with('author')
                ->latest()
                ->limit(5)
                ->get(),
            'recentActivity' => AuditLog::query()
                ->with('actor')
                ->latest()
                ->limit(10)
                ->get(),
            'sentNotifications' => Notification::query()
                ->with('recipient')
                ->where('sender_id', $user->getKey())
                ->latest()
                ->limit(5)
                ->get(),
            'auditActivity' => $this->reports->auditActivity(7),
        ]);
    }
}
