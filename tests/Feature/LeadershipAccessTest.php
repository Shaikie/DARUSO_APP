<?php

namespace Tests\Feature;

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
}
