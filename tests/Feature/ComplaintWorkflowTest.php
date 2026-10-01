<?php

namespace Tests\Feature;

use App\Enums\ComplaintStatus;
use App\Enums\PermissionName;
use App\Enums\RoleName;
use App\Models\Complaint;
use App\Models\Ministry;
use App\Models\User;
use App\Services\ComplaintWorkflow;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ComplaintWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private function workflow(): ComplaintWorkflow
    {
        return app(ComplaintWorkflow::class);
    }

    private function complainer(): User
    {
        $user = $this->student();

        $user->complaints()->create([
            'title' => 'Hostel water supply',
            'description' => 'Water has been unavailable in the evenings for a week now.',
            'category' => 'hostel',
            'status' => ComplaintStatus::Submitted->value,
        ]);

        return $user;
    }

    public function test_a_student_can_submit_a_complaint(): void
    {
        $student = $this->student();

        $this->actingAs($student)
            ->post(route('student.complaints.store'), [
                'title' => 'Broken lecture hall projector',
                'description' => 'The projector in lecture hall 3 has not worked for two weeks.',
                'category' => 'infrastructure',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('complaints', [
            'title' => 'Broken lecture hall projector',
            'creator_id' => $student->getKey(),
            'status' => ComplaintStatus::Submitted->value,
        ]);

        // The submission itself is recorded in history.
        $complaint = Complaint::firstWhere('title', 'Broken lecture hall projector');
        $this->assertDatabaseHas('complaint_history', [
            'complaint_id' => $complaint->getKey(),
            'actor_id' => $student->getKey(),
            'new_status' => ComplaintStatus::Submitted->value,
        ]);
    }

    public function test_complaint_submission_is_validated(): void
    {
        $student = $this->student();

        $this->actingAs($student)
            ->post(route('student.complaints.store'), [
                'title' => 'x',
                'description' => 'short',
                'category' => 'unknown',
            ])
            ->assertSessionHasErrors(['title', 'description', 'category']);

        $this->assertDatabaseCount('complaints', 0);
    }

    public function test_a_student_cannot_view_another_students_complaint(): void
    {
        $owner = $this->complainer();
        $other = $this->student();

        $complaint = $owner->complaints()->first();

        $this->actingAs($other)
            ->get(route('student.complaints.show', $complaint))
            ->assertForbidden();
    }

    public function test_a_student_cannot_update_or_delete_another_students_complaint(): void
    {
        $owner = $this->complainer();
        $other = $this->student();

        $complaint = $owner->complaints()->first();

        $this->actingAs($other)
            ->put(route('student.complaints.update', $complaint), [
                'title' => 'Hijacked title',
                'description' => 'Attempting to modify a complaint that is not mine at all.',
                'category' => 'academic',
            ])
            ->assertForbidden();

        $this->actingAs($other)
            ->delete(route('student.complaints.destroy', $complaint))
            ->assertForbidden();

        $this->assertDatabaseHas('complaints', ['id' => $complaint->getKey()]);
    }

    public function test_the_creator_can_view_their_own_complaint(): void
    {
        $owner = $this->complainer();
        $complaint = $owner->complaints()->first();

        $this->actingAs($owner)
            ->get(route('student.complaints.show', $complaint))
            ->assertOk()
            ->assertSee($complaint->title);
    }

    public function test_a_leader_outside_the_remit_cannot_view_the_complaint(): void
    {
        $owner = $this->complainer();
        $complaint = $owner->complaints()->first();

        // complaint.view but no complaint.assign, and nothing assigned to them.
        $leader = $this->userWithRole('limited_leader', [
            PermissionName::ComplaintView->value,
        ]);

        $this->actingAs($leader)
            ->get(route('leader.complaints.show', $complaint))
            ->assertForbidden();
    }

    public function test_the_assigned_leader_can_view_the_complaint(): void
    {
        $owner = $this->complainer();
        $leader = $this->userWithRole('limited_leader', [
            PermissionName::ComplaintView->value,
            PermissionName::ComplaintUpdate->value,
        ]);

        $complaint = $owner->complaints()->first();
        $complaint->update(['assigned_leader_id' => $leader->getKey()]);

        $this->actingAs($leader)
            ->get(route('leader.complaints.show', $complaint))
            ->assertOk();
    }

    public function test_transitioning_status_writes_history_and_notifies_the_student(): void
    {
        $student = $this->complainer();
        $complaint = $student->complaints()->first();

        $leader = $this->userWithRole('limited_leader', [
            PermissionName::ComplaintView->value,
            PermissionName::ComplaintUpdate->value,
        ]);

        $this->workflow()->transitionTo(
            $complaint,
            ComplaintStatus::UnderReview,
            $leader,
            'Acknowledged and assigned for review.'
        );

        $complaint->refresh();

        $this->assertSame(ComplaintStatus::UnderReview, $complaint->status);

        $this->assertDatabaseHas('complaint_history', [
            'complaint_id' => $complaint->getKey(),
            'actor_id' => $leader->getKey(),
            'old_status' => ComplaintStatus::Submitted->value,
            'new_status' => ComplaintStatus::UnderReview->value,
        ]);

        $this->assertDatabaseHas('notifications', [
            'recipient_id' => $student->getKey(),
            'related_id' => $complaint->getKey(),
        ]);
    }

    public function test_an_illegal_transition_is_rejected(): void
    {
        $student = $this->complainer();
        $complaint = $student->complaints()->first();

        $leader = $this->userWithRole('limited_leader', [
            PermissionName::ComplaintUpdate->value,
        ]);

        // submitted → in_progress skips the review steps and is not allowed.
        $this->expectException(\DomainException::class);

        $this->workflow()->transitionTo($complaint, ComplaintStatus::InProgress, $leader);
    }

    public function test_forwarding_records_the_ministry_and_moves_to_forwarded(): void
    {
        $student = $this->complainer();
        $complaint = $student->complaints()->first();

        $ministry = Ministry::create(['name' => 'Welfare Ministry', 'description' => 'Welfare']);

        $leader = $this->userWithRole('limited_leader', [
            PermissionName::ComplaintView->value,
            PermissionName::ComplaintAssign->value,
            PermissionName::ComplaintUpdate->value,
        ]);

        $this->workflow()->forward($complaint, $ministry, $leader, 'This belongs with welfare.');

        $complaint->refresh();

        $this->assertSame(ComplaintStatus::Forwarded, $complaint->status);
        $this->assertSame($ministry->getKey(), $complaint->assigned_ministry_id);

        $this->assertDatabaseHas('complaint_history', [
            'complaint_id' => $complaint->getKey(),
            'action' => 'forwarded',
        ]);
    }

    public function test_resolving_requires_the_resolve_permission(): void
    {
        $student = $this->complainer();
        $complaint = $student->complaints()->first();
        $complaint->update(['status' => ComplaintStatus::InProgress->value]);

        // Has complaint.update but not complaint.resolve.
        $leader = $this->userWithRole('limited_leader', [
            PermissionName::ComplaintView->value,
            PermissionName::ComplaintUpdate->value,
        ]);

        // Assigned personally, so the leader can see the complaint at all; the
        // denial therefore comes from the missing resolve permission alone.
        $complaint->update(['assigned_leader_id' => $leader->getKey()]);

        $this->actingAs($leader)
            ->put(route('leader.complaints.update', $complaint), [
                'status' => ComplaintStatus::Resolved->value,
            ])
            ->assertForbidden();

        $this->assertSame(ComplaintStatus::InProgress, $complaint->fresh()->status);
    }

    public function test_a_leader_with_resolve_permission_can_resolve(): void
    {
        $student = $this->complainer();
        $complaint = $student->complaints()->first();
        $complaint->update(['status' => ComplaintStatus::InProgress->value]);

        $leader = $this->userWithRole('limited_leader', [
            PermissionName::ComplaintView->value,
            PermissionName::ComplaintUpdate->value,
            PermissionName::ComplaintResolve->value,
        ]);

        $complaint->update(['assigned_leader_id' => $leader->getKey()]);

        $this->actingAs($leader)
            ->put(route('leader.complaints.update', $complaint), [
                'status' => ComplaintStatus::Resolved->value,
                'notes' => 'Water supply has been restored.',
            ])
            ->assertRedirect();

        $complaint->refresh();

        $this->assertSame(ComplaintStatus::Resolved, $complaint->status);
        $this->assertNotNull($complaint->resolved_at);
    }

    public function test_a_status_change_is_audited(): void
    {
        $student = $this->complainer();
        $complaint = $student->complaints()->first();

        $leader = $this->userWithRole('limited_leader', [
            PermissionName::ComplaintView->value,
            PermissionName::ComplaintUpdate->value,
        ]);

        $complaint->update(['assigned_leader_id' => $leader->getKey()]);

        $this->actingAs($leader)
            ->put(route('leader.complaints.update', $complaint), [
                'status' => ComplaintStatus::UnderReview->value,
            ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'status_changed',
            'target_id' => $complaint->getKey(),
            'actor_id' => $leader->getKey(),
        ]);
    }

    public function test_leaders_cannot_create_complaints(): void
    {
        $leader = $this->userWithRole(RoleName::MinistryLeader, [
            PermissionName::ComplaintView->value,
        ]);

        // The resource route for create/store is not registered at all.
        $this->assertFalse(
            Route::has('leader.complaints.create')
        );
    }

    public function test_a_student_can_withdraw_an_open_complaint(): void
    {
        $student = $this->complainer();
        $complaint = $student->complaints()->first();

        $this->actingAs($student)
            ->delete(route('student.complaints.destroy', $complaint))
            ->assertRedirect(route('student.complaints.index'));

        $this->assertDatabaseMissing('complaints', ['id' => $complaint->getKey()]);
    }

    public function test_a_resolved_complaint_cannot_be_withdrawn_by_its_creator(): void
    {
        $student = $this->complainer();
        $complaint = $student->complaints()->first();
        $complaint->update([
            'status' => ComplaintStatus::Resolved->value,
            'resolved_at' => now(),
        ]);

        $this->actingAs($student)
            ->delete(route('student.complaints.destroy', $complaint))
            ->assertForbidden();

        $this->assertDatabaseHas('complaints', ['id' => $complaint->getKey()]);
    }

    public function test_history_records_the_actor_for_each_transition(): void
    {
        $student = $this->complainer();
        $complaint = $student->complaints()->first();

        $leader = $this->userWithRole('limited_leader', [
            PermissionName::ComplaintUpdate->value,
        ]);

        $workflow = $this->workflow();

        $workflow->transitionTo($complaint, ComplaintStatus::UnderReview, $leader);
        $workflow->transitionTo($complaint, ComplaintStatus::InProgress, $leader);

        $entries = $complaint->history()->get();

        // The complaint was created directly here, so only the two transitions
        // are journalled. The submission entry is covered by the store test.
        // `history()` is ordered newest-first, so `last()` is the first transition.
        $this->assertCount(2, $entries);
        $this->assertSame($leader->getKey(), $entries->first()->actor_id);
        $this->assertSame(ComplaintStatus::UnderReview, $entries->last()->old_status);
        $this->assertSame(ComplaintStatus::InProgress, $entries->last()->new_status);
    }
}
