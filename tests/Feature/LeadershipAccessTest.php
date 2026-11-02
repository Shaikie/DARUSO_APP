<?php

namespace Tests\Feature;

use App\Enums\PermissionName;
use App\Enums\RoleName;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeadershipAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_verified_account_without_leadership_membership_cannot_access_leader_area(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('leader.dashboard'))
            ->assertForbidden();
    }

    public function test_leadership_navigation_only_shows_authorized_administration_links(): void
    {
        $leader = $this->userWithRole(RoleName::MinistryLeader, [
            PermissionName::AnnouncementCreate->value,
        ]);

        $response = $this->actingAs($leader)->get(route('leader.dashboard'));

        $response->assertOk()
            ->assertSee('Announcements')
            ->assertDontSee('href="'.route('leader.ministries.index').'"', false)
            ->assertDontSee('href="'.route('leader.settings.index').'"', false)
            ->assertDontSee('href="'.route('leader.audit-logs.index').'"', false);
    }
}
