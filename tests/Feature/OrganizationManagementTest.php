<?php

namespace Tests\Feature;

use App\Enums\PermissionName;
use App\Models\Committee;
use App\Models\CommitteeMember;
use App\Models\LeaderAssignment;
use App\Models\LeaderProfile;
use App\Models\LeadershipTerm;
use App\Models\Ministry;
use App\Models\Position;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The organisation is entirely database-driven; these tests cover the
 * management surface and the "exactly one active term" invariant.
 */
class OrganizationManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_ministries_require_the_manage_permission(): void
    {
        $leader = $this->userWithRole('observer');

        $this->actingAs($leader)
            ->get(route('leader.ministries.create'))
            ->assertForbidden();

        $this->actingAs($leader)
            ->post(route('leader.ministries.store'), [
                'name' => 'Rogue Ministry',
                'description' => 'Should not be created.',
            ])
            ->assertForbidden();

        $this->assertDatabaseCount('ministries', 0);
    }

    public function test_a_permitted_leader_can_manage_ministries(): void
    {
        $leader = $this->userWithRole('organiser', [
            PermissionName::MinistryManage->value,
        ]);

        $this->actingAs($leader)
            ->post(route('leader.ministries.store'), [
                'name' => 'Academic Ministry',
                'description' => 'Academic affairs.',
            ])
            ->assertRedirect();

        $ministry = Ministry::firstWhere('name', 'Academic Ministry');
        $this->assertNotNull($ministry);

        $this->actingAs($leader)
            ->put(route('leader.ministries.update', $ministry), [
                'name' => 'Academic Affairs Ministry',
                'description' => 'Updated description.',
            ])
            ->assertRedirect();

        $this->assertSame('Academic Affairs Ministry', $ministry->fresh()->name);
    }

    public function test_ministry_names_must_be_unique(): void
    {
        $leader = $this->userWithRole('organiser', [
            PermissionName::MinistryManage->value,
        ]);

        Ministry::create(['name' => 'Welfare Ministry', 'description' => 'x']);

        $this->actingAs($leader)
            ->post(route('leader.ministries.store'), [
                'name' => 'Welfare Ministry',
                'description' => 'Duplicate.',
            ])
            ->assertSessionHasErrors('name');
    }

    public function test_ministry_changes_are_audited(): void
    {
        $leader = $this->userWithRole('organiser', [
            PermissionName::MinistryManage->value,
        ]);

        $this->actingAs($leader)
            ->post(route('leader.ministries.store'), [
                'name' => 'Health Ministry',
                'description' => 'Health outreach.',
            ]);

        $ministry = Ministry::firstWhere('name', 'Health Ministry');

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'created',
            'target_type' => Ministry::class,
            'target_id' => $ministry->getKey(),
            'actor_id' => $leader->getKey(),
        ]);
    }

    public function test_positions_require_a_hierarchy_level(): void
    {
        $leader = $this->userWithRole('organiser', [
            PermissionName::PositionManage->value,
        ]);

        $this->actingAs($leader)
            ->post(route('leader.positions.store'), [
                'name' => 'President',
                'hierarchy_level' => '',
            ])
            ->assertSessionHasErrors('hierarchy_level');
    }

    public function test_positions_are_ordered_by_hierarchy(): void
    {
        $leader = $this->userWithRole('organiser');

        Position::create(['name' => 'Secretary General', 'hierarchy_level' => 3]);
        Position::create(['name' => 'President', 'hierarchy_level' => 1]);
        Position::create(['name' => 'Treasurer', 'hierarchy_level' => 5]);

        $order = Position::orderBy('hierarchy_level')->pluck('name')->all();

        $this->assertSame(['President', 'Secretary General', 'Treasurer'], $order);
    }

    public function test_committee_membership_requires_the_manage_permission(): void
    {
        $committee = Committee::create(['name' => 'Finance Committee', 'description' => 'x']);
        $leader = $this->userWithRole('observer');

        $this->actingAs($leader)
            ->post(route('leader.committees.members.store', $committee), [
                'user_id' => $this->userWithRole('someone')->getKey(),
                'committee_id' => $committee->getKey(),
                'leadership_term_id' => LeadershipTerm::factory()->create()->getKey(),
            ])
            ->assertForbidden();

        $this->assertDatabaseCount('committee_members', 0);
    }

    public function test_a_permitted_leader_can_add_and_remove_committee_members(): void
    {
        $leader = $this->userWithRole('organiser', [
            PermissionName::CommitteeManage->value,
        ]);

        $committee = Committee::create(['name' => 'Finance Committee', 'description' => 'x']);
        $term = LeadershipTerm::factory()->active()->create();
        $member = $this->userWithRole('member');

        $this->actingAs($leader)
            ->post(route('leader.committees.members.store', $committee), [
                'user_id' => $member->getKey(),
                'committee_id' => $committee->getKey(),
                'leadership_term_id' => $term->getKey(),
                'role_in_committee' => 'Chairperson',
            ])
            ->assertRedirect();

        $membership = CommitteeMember::firstWhere('user_id', $member->getKey());
        $this->assertNotNull($membership);
        $this->assertSame('Chairperson', $membership->role_in_committee);

        $this->actingAs($leader)
            ->delete(route('leader.committees.members.destroy', [$committee, $membership]))
            ->assertRedirect();

        $this->assertDatabaseCount('committee_members', 0);
    }

    public function test_a_person_cannot_join_the_same_committee_twice_in_one_term(): void
    {
        $leader = $this->userWithRole('organiser', [
            PermissionName::CommitteeManage->value,
        ]);

        $committee = Committee::create(['name' => 'Finance Committee', 'description' => 'x']);
        $term = LeadershipTerm::factory()->active()->create();
        $member = $this->userWithRole('member');

        $payload = [
            'user_id' => $member->getKey(),
            'committee_id' => $committee->getKey(),
            'leadership_term_id' => $term->getKey(),
        ];

        $this->actingAs($leader)
            ->post(route('leader.committees.members.store', $committee), $payload)
            ->assertRedirect();

        $this->actingAs($leader)
            ->post(route('leader.committees.members.store', $committee), $payload)
            ->assertSessionHasErrors('user_id');
    }

    public function test_leadership_assignments_require_the_manage_permission(): void
    {
        $leader = $this->userWithRole('observer');

        $this->actingAs($leader)
            ->get(route('leader.leadership.create'))
            ->assertForbidden();

        $this->assertDatabaseCount('leader_assignments', 0);
    }

    public function test_a_permitted_leader_can_assign_a_position(): void
    {
        $leader = $this->userWithRole('organiser', [
            PermissionName::LeaderManage->value,
        ]);

        $position = Position::create(['name' => 'President', 'hierarchy_level' => 1]);
        $term = LeadershipTerm::factory()->active()->create();
        $user = $this->student();

        $this->actingAs($leader)
            ->post(route('leader.leadership.store'), [
                'user_id' => $user->getKey(),
                'position_id' => $position->getKey(),
                'leadership_term_id' => $term->getKey(),
            ])
            ->assertRedirect();

        $assignment = LeaderAssignment::firstWhere('user_id', $user->getKey());
        $this->assertNotNull($assignment);

        // Holding a position makes the holder a leader.
        $this->assertNotNull($assignment->user->leaderProfile);
    }

    public function test_a_duplicate_assignment_is_rejected(): void
    {
        $leader = $this->userWithRole('organiser', [
            PermissionName::LeaderManage->value,
        ]);

        $position = Position::create(['name' => 'President', 'hierarchy_level' => 1]);
        $term = LeadershipTerm::factory()->active()->create();
        $user = $this->student();

        $payload = [
            'user_id' => $user->getKey(),
            'position_id' => $position->getKey(),
            'leadership_term_id' => $term->getKey(),
        ];

        $this->actingAs($leader)->post(route('leader.leadership.store'), $payload)->assertRedirect();

        $this->actingAs($leader)
            ->post(route('leader.leadership.store'), $payload)
            ->assertSessionHasErrors('user_id');
    }

    public function test_activating_a_term_deactivates_the_previous_one(): void
    {
        $leader = $this->userWithRole('organiser', [
            PermissionName::TermManage->value,
        ]);

        $current = LeadershipTerm::factory()->active()->create(['name' => '2025/2026']);
        $next = LeadershipTerm::factory()->create(['name' => '2026/2027']);

        $this->actingAs($leader)
            ->post(route('leader.leadership-terms.activate', $next))
            ->assertRedirect();

        $this->assertFalse($current->fresh()->is_active);
        $this->assertTrue($next->fresh()->is_active);

        // The invariant: exactly one term is ever active.
        $this->assertSame(1, LeadershipTerm::where('is_active', true)->count());
    }

    public function test_creating_an_active_term_deactivates_the_existing_one(): void
    {
        $leader = $this->userWithRole('organiser', [
            PermissionName::TermManage->value,
        ]);

        $current = LeadershipTerm::factory()->active()->create(['name' => '2025/2026']);

        $this->actingAs($leader)
            ->post(route('leader.leadership-terms.store'), [
                'name' => '2026/2027',
                'start_date' => now()->addYear()->startOfYear()->toDateString(),
                'end_date' => now()->addYears(2)->endOfYear()->toDateString(),
                'is_active' => '1',
            ])
            ->assertRedirect();

        $this->assertFalse($current->fresh()->is_active);
        $this->assertSame(1, LeadershipTerm::where('is_active', true)->count());
    }

    public function test_the_active_term_cannot_be_deleted(): void
    {
        $leader = $this->userWithRole('organiser', [
            PermissionName::TermManage->value,
        ]);

        $active = LeadershipTerm::factory()->active()->create();

        $this->actingAs($leader)
            ->delete(route('leader.leadership-terms.destroy', $active))
            ->assertForbidden();

        $this->assertDatabaseHas('leadership_terms', ['id' => $active->getKey()]);
    }

    public function test_term_dates_are_validated(): void
    {
        $leader = $this->userWithRole('organiser', [
            PermissionName::TermManage->value,
        ]);

        $this->actingAs($leader)
            ->post(route('leader.leadership-terms.store'), [
                'name' => '2026/2027',
                'start_date' => '2027-01-01',
                'end_date' => '2026-01-01',
            ])
            ->assertSessionHasErrors('end_date');
    }

    public function test_ministry_assignment_makes_a_user_a_ministry_leader(): void
    {
        $term = LeadershipTerm::factory()->active()->create();
        $position = Position::create(['name' => 'Minister', 'hierarchy_level' => 8]);
        $ministry = Ministry::create(['name' => 'Welfare Ministry', 'description' => 'x']);
        $user = $this->student();

        LeaderAssignment::create([
            'user_id' => $user->getKey(),
            'position_id' => $position->getKey(),
            'ministry_id' => $ministry->getKey(),
            'leadership_term_id' => $term->getKey(),
        ]);

        $this->assertTrue($user->fresh()->ministryIds()->contains($ministry->getKey()));
    }

    public function test_a_leader_sees_the_ministry_listing(): void
    {
        $leader = $this->userWithRole('organiser');

        // A leader profile is what marks the holder as leadership, independent
        // of which role name they carry.
        LeaderProfile::create(['user_id' => $leader->getKey(), 'bio' => null]);

        Ministry::create(['name' => 'Academic Ministry', 'description' => 'x']);

        $this->actingAs($leader->fresh())
            ->get(route('leader.ministries.index'))
            ->assertOk()
            ->assertSee('Academic Ministry');
    }
}
