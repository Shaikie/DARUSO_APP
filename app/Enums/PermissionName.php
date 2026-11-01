<?php

namespace App\Enums;

/**
 * Granular permission identifiers. Seeded into the `permissions` table and
 * referenced through Gates/policies, so the database stays the source of truth
 * while the code keeps compile-time safety over the vocabulary.
 */
enum PermissionName: string
{
    // Announcements
    case AnnouncementCreate = 'announcement.create';
    case AnnouncementEdit = 'announcement.edit';
    case AnnouncementEditAny = 'announcement.edit_any';
    case AnnouncementPublish = 'announcement.publish';
    case AnnouncementDelete = 'announcement.delete';

    // Notifications
    case NotificationCreate = 'notification.create';
    case NotificationSend = 'notification.send';

    // Posts / social feed
    case PostCreate = 'post.create';
    case PostEdit = 'post.edit';
    case PostPublish = 'post.publish';
    case PostDelete = 'post.delete';
    case PostComment = 'post.comment';
    case PostModerate = 'post.moderate';

    // Students
    case StudentView = 'student.view';
    case StudentViewSensitive = 'student.view_sensitive';
    case StudentViewHostel = 'student.view_hostel';
    case StudentVerify = 'student.verify';
    case StudentDirectory = 'student.directory';

    // Complaints
    case ComplaintView = 'complaint.view';
    case ComplaintAssign = 'complaint.assign';
    case ComplaintUpdate = 'complaint.update';
    case ComplaintResolve = 'complaint.resolve';

    // Meetings
    case MeetingCreate = 'meeting.create';
    case MeetingEdit = 'meeting.edit';
    case MeetingManage = 'meeting.manage';

    // Events
    case EventCreate = 'event.create';
    case EventEdit = 'event.edit';
    case EventManage = 'event.manage';

    // Documents
    case DocumentCreate = 'document.create';
    case DocumentPublish = 'document.publish';
    case DocumentDelete = 'document.delete';

    // Feedback and polls
    case FeedbackView = 'feedback.view';
    case FeedbackRespond = 'feedback.respond';
    case PollManage = 'poll.manage';

    // Leadership operations
    case LeaderInvite = 'leader.invite';
    case TaskView = 'task.view';
    case TaskAssign = 'task.assign';
    case TaskManage = 'task.manage';
    case HandoverManage = 'handover.manage';
    case ActivityReportSubmit = 'activity_report.submit';
    case ActivityReportReview = 'activity_report.review';
    case CalendarManage = 'calendar.manage';
    case PostReportManage = 'post.report_manage';

    // Administration
    case LeaderManage = 'leader.manage';
    case CommitteeManage = 'committee.manage';
    case MinistryManage = 'ministry.manage';
    case TermManage = 'term.manage';
    case PositionManage = 'position.manage';
    case UserManage = 'user.manage';
    case RoleManage = 'role.manage';
    case SystemSettings = 'system.settings';
    case AuditView = 'audit.view';

    // Reports
    case ReportView = 'report.view';

    public function label(): string
    {
        return ucwords(str_replace(['.', '_'], [' ', ' '], $this->value));
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
