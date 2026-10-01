<?php

namespace App\Services;

use App\Enums\AnnouncementStatus;
use App\Enums\ComplaintStatus;
use App\Models\Announcement;
use App\Models\AuditLog;
use App\Models\Complaint;
use App\Models\Document;
use App\Models\Event;
use App\Models\Meeting;
use App\Models\Notification;
use App\Models\StudentProfile;
use Illuminate\Support\Facades\DB;

/**
 * Aggregate reporting.
 *
 * Every figure is produced by a COUNT/GROUP BY in the database. Nothing here
 * loads full tables into PHP just to count rows, which matters once the student
 * population grows.
 */
class ReportService
{
    /**
     * @return array<string, mixed>
     */
    public function overview(): array
    {
        return [
            'students' => $this->studentCounts(),
            'complaints' => $this->complaintStatistics(),
            'announcements' => $this->announcementStatistics(),
            'notifications' => $this->notificationStatistics(),
            'meetings' => $this->meetingStatistics(),
            'events' => $this->eventStatistics(),
            'documents' => $this->documentStatistics(),
            'leadership' => $this->leadershipStatistics(),
        ];
    }

    /**
     * @return array<string, int>
     */
    public function studentCounts(): array
    {
        $rows = StudentProfile::query()
            ->select('status', DB::raw('count(*) as aggregate'))
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $byYear = StudentProfile::query()
            ->select('year_of_study', DB::raw('count(*) as aggregate'))
            ->groupBy('year_of_study')
            ->orderBy('year_of_study')
            ->pluck('aggregate', 'year_of_study');

        $byCollege = StudentProfile::query()
            ->select('college', DB::raw('count(*) as aggregate'))
            ->groupBy('college')
            ->orderByDesc('aggregate')
            ->limit(10)
            ->pluck('aggregate', 'college');

        return [
            'total' => (int) $rows->sum(),
            'by_status' => $rows->map(fn ($v): int => (int) $v)->all(),
            'by_year' => $byYear->map(fn ($v): int => (int) $v)->all(),
            'by_college' => $byCollege->map(fn ($v): int => (int) $v)->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function complaintStatistics(): array
    {
        $byStatus = Complaint::query()
            ->select('status', DB::raw('count(*) as aggregate'))
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $byCategory = Complaint::query()
            ->select('category', DB::raw('count(*) as aggregate'))
            ->groupBy('category')
            ->orderByDesc('aggregate')
            ->pluck('aggregate', 'category');

        $unassigned = Complaint::query()
            ->whereNull('assigned_ministry_id')
            ->whereNull('assigned_leader_id')
            ->where('status', '!=', ComplaintStatus::Rejected->value)
            ->count();

        return [
            'total' => (int) $byStatus->sum(),
            'open' => Complaint::query()->open()->count(),
            'by_status' => $byStatus->map(fn ($v): int => (int) $v)->all(),
            'by_category' => $byCategory->map(fn ($v): int => (int) $v)->all(),
            'unassigned' => $unassigned,
            'resolved' => Complaint::query()->whereNotNull('resolved_at')->count(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function announcementStatistics(): array
    {
        $byStatus = Announcement::query()
            ->select('status', DB::raw('count(*) as aggregate'))
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $byPriority = Announcement::query()
            ->where('status', AnnouncementStatus::Published->value)
            ->select('priority', DB::raw('count(*) as aggregate'))
            ->groupBy('priority')
            ->pluck('aggregate', 'priority');

        return [
            'total' => (int) $byStatus->sum(),
            'by_status' => $byStatus->map(fn ($v): int => (int) $v)->all(),
            'by_priority' => $byPriority->map(fn ($v): int => (int) $v)->all(),
            'published_last_30_days' => Announcement::query()
                ->where('published_at', '>=', now()->subDays(30))
                ->count(),
        ];
    }

    /**
     * @return array<string, int>
     */
    public function notificationStatistics(): array
    {
        return [
            'total' => Notification::query()->count(),
            'unread' => Notification::query()->whereNull('read_at')->count(),
            'sent_last_30_days' => Notification::query()->where('created_at', '>=', now()->subDays(30))->count(),
            'senders' => Notification::query()->distinct()->count('sender_id'),
        ];
    }

    /**
     * @return array<string, int>
     */
    public function meetingStatistics(): array
    {
        return [
            'total' => Meeting::query()->count(),
            'upcoming' => Meeting::query()->where('status', 'scheduled')->where('meeting_date', '>=', today())->count(),
            'completed' => Meeting::query()->where('status', 'completed')->count(),
        ];
    }

    /**
     * @return array<string, int>
     */
    public function eventStatistics(): array
    {
        return [
            'total' => Event::query()->count(),
            'upcoming' => Event::query()->where('status', 'upcoming')->where('event_date', '>=', today())->count(),
            'completed' => Event::query()->where('status', 'completed')->count(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function documentStatistics(): array
    {
        return [
            'total' => Document::query()->count(),
            'total_size' => (int) Document::query()->sum('file_size'),
            'by_category' => Document::query()
                ->select('category', DB::raw('count(*) as aggregate'))
                ->groupBy('category')
                ->pluck('aggregate', 'category')
                ->map(fn ($v): int => (int) $v)
                ->all(),
        ];
    }

    /**
     * @return array<string, int>
     */
    public function leadershipStatistics(): array
    {
        return [
            'terms' => DB::table('leadership_terms')->count(),
            'active_terms' => DB::table('leadership_terms')->where('is_active', true)->count(),
            'positions' => DB::table('positions')->count(),
            'ministries' => DB::table('ministries')->count(),
            'committees' => DB::table('committees')->count(),
            'assignments' => DB::table('leader_assignments')->count(),
            'leaders' => DB::table('leader_profiles')->count(),
        ];
    }

    /**
     * Daily audit activity, used for the recent-activity chart.
     *
     * @return array<string, int>
     */
    public function auditActivity(int $days = 14): array
    {
        return AuditLog::query()
            ->where('created_at', '>=', now()->subDays($days))
            ->select(DB::raw('date(created_at) as day'), DB::raw('count(*) as aggregate'))
            ->groupBy('day')
            ->orderBy('day')
            ->pluck('aggregate', 'day')
            ->map(fn ($v): int => (int) $v)
            ->all();
    }
}
