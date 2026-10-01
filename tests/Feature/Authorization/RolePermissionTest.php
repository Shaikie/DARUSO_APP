<?php

namespace Tests\Feature\Authorization;

use App\Enums\PermissionName;
use App\Enums\RoleName;
use App\Models\AuditLog;
use App\Policies\AnnouncementPolicy;
use App\Policies\AuditLogPolicy;
use App\Policies\ComplaintPolicy;
use App\Policies\NotificationPolicy;
use App\Policies\StudentProfilePolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

/**
 * Authorization is enforced by policies and permissions on the server, not by
 * hiding navigation. These tests exercise the gates and policies directly so a
 * regression is caught even if no view renders.
 */
class RolePermissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_permissions_are_resolved_from_roles(): void
    {
        $student = $this->student();
        $leader = $this->userWithRole(RoleName::MinistryLeader);

        $this->assertFalse($student->hasPermission(PermissionName::AnnouncementCreate->value));
        $this->assertTrue($leader->hasPermission(PermissionName::AnnouncementCreate->value));
    }

    public function test_gates_mirror_permission_checks(): void
    {
        $student = $this->student();
        $leader = $this->userWithRole(RoleName::MinistryLeader);

        $this->assertFalse(Gate::forUser($student)->allows(PermissionName::ComplaintView->value));
        $this->assertTrue(Gate::forUser($leader)->allows(PermissionName::ComplaintView->value));
    }

    public function test_no_role_grants_everything_implicitly(): void
    {
        // Even the administrator role grants only explicit permission rows.
        $admin = $this->administrator();

        $this->assertTrue($admin->hasPermission(PermissionName::SystemSettings->value));
        $this->assertSame(
            count(PermissionName::values()),
            $admin->permissions()->count()
        );
    }

    public function test_students_cannot_access_leader_routes(): void
    {
        $student = $this->student();

        $this->actingAs($student)
            ->get('/leader/dashboard')
            ->assertForbidden();
    }

    public function test_leader_area_rejects_a_user_holding_only_the_student_role(): void
    {
        $user = $this->userWithRole(RoleName::Student);

        $this->actingAs($user)
            ->get('/leader/announcements')
            ->assertForbidden();
    }

    public function test_audit_logs_require_the_audit_permission(): void
    {
        $leader = $this->userWithRole(RoleName::MinistryLeader);

        $this->actingAs($leader)
            ->get(route('leader.audit-logs.index'))
            ->assertForbidden();
    }

    public function test_announcement_policy_distinguishes_authorship_from_publishing(): void
    {
        // A role holding only the create/edit permissions, but not publish.
        $author = $this->userWithRole('announcement_author', [
            PermissionName::AnnouncementCreate->value,
            PermissionName::AnnouncementEdit->value,
        ]);

        $announcement = $author->announcements()->create([
            'title' => 'Draft notice',
            'content' => 'A draft announcement body long enough to pass validation.',
            'priority' => 'normal',
            'status' => 'draft',
        ]);

        $policy = new AnnouncementPolicy;

        $this->assertTrue($policy->create($author));
        $this->assertTrue($policy->submitForReview($author, $announcement));

        // No publish permission was granted, so publishing must be denied even
        // though the user is the author.
        $this->assertFalse($policy->publish($author, $announcement));

        $announcement->delete();
    }

    public function test_notification_policy_only_allows_the_recipient_to_read_state(): void
    {
        $sender = $this->userWithRole(RoleName::MinistryLeader, [
            PermissionName::NotificationCreate->value,
        ]);
        $recipient = $this->student();

        $notification = $sender->sentNotifications()->create([
            'title' => 'Private note',
            'message' => 'Only the recipient should read this.',
            'recipient_id' => $recipient->getKey(),
            'priority' => 'normal',
        ]);

        $policy = new NotificationPolicy;

        $this->assertTrue($policy->view($recipient, $notification));
        $this->assertTrue($policy->markAsRead($recipient, $notification));

        // The sender may view what they sent, but must not alter read state.
        $this->assertTrue($policy->view($sender, $notification));
        $this->assertFalse($policy->markAsRead($sender, $notification));

        $this->assertFalse($policy->delete($recipient, $notification));
    }

    public function test_audit_log_policy_never_allows_mutation(): void
    {
        $admin = $this->administrator();
        $log = AuditLog::create([
            'action' => 'created',
            'target_type' => 'Test',
            'target_id' => 1,
            'created_at' => now(),
        ]);

        $policy = new AuditLogPolicy;

        $this->assertTrue($policy->view($admin, $log));
        $this->assertFalse($policy->update($admin, $log));
        $this->assertFalse($policy->delete($admin, $log));
    }

    public function test_audit_log_entries_are_immutable(): void
    {
        $log = AuditLog::create([
            'action' => 'created',
            'created_at' => now(),
        ]);

        $this->expectException(\LogicException::class);
        $log->update(['action' => 'tampered']);
    }

    public function test_audit_log_entries_cannot_be_deleted(): void
    {
        $log = AuditLog::create([
            'action' => 'created',
            'created_at' => now(),
        ]);

        $this->expectException(\LogicException::class);
        $log->delete();
    }

    public function test_student_profile_sensitive_fields_require_permission(): void
    {
        $profile = $this->student()->studentProfile;
        $policy = new StudentProfilePolicy;

        $hostelLeader = $this->userWithRole(RoleName::MinistryLeader);
        $fullAdmin = $this->administrator();

        $this->assertTrue($policy->view($hostelLeader, $profile));

        // Ministry leaders may see hostel data but not full sensitive records.
        $this->assertTrue($policy->viewHostel($hostelLeader, $profile));
        $this->assertFalse($policy->viewSensitive($hostelLeader, $profile));

        $this->assertTrue($policy->viewSensitive($fullAdmin, $profile));
    }

    public function test_complaint_policy_blocks_leaders_outside_their_remit(): void
    {
        $student = $this->student();
        $complaint = $student->complaints()->create([
            'title' => 'Private complaint',
            'description' => 'A description that clearly belongs to this student only.',
            'category' => 'academic',
            'status' => 'submitted',
        ]);

        $leader = $this->userWithRole(RoleName::MinistryLeader);
        $policy = new ComplaintPolicy;

        // Assigned to nobody the leader knows, and they lack complaint.assign.
        $this->assertFalse($policy->view($leader, $complaint));

        // The creator always sees their own complaint.
        $this->assertTrue($policy->view($student, $complaint));
    }
}
