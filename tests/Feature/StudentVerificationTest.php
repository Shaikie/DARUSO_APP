<?php

namespace Tests\Feature;

use App\Enums\StudentStatus;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_pending_student_cannot_access_student_services(): void
    {
        $user = User::factory()->create();

        StudentProfile::factory()->create([
            'user_id' => $user->getKey(),
            'status' => StudentStatus::PendingVerification,
        ]);

        $this->actingAs($user)
            ->get(route('student.dashboard'))
            ->assertOk()
            ->assertSee('Your student record is under review');
    }

    public function test_student_profile_status_is_pending_after_onboarding(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('student.profile.store'), [
            'registration_number' => 'UDSM-2026-00001',
            'college' => 'CoICT',
            'school_faculty' => 'College of Information and Communication Technologies',
            'programme' => 'Computer Science',
            'year_of_study' => 3,
        ])->assertRedirect(route('student.dashboard'));

        $this->assertDatabaseHas('student_profiles', [
            'user_id' => $user->getKey(),
            'status' => StudentStatus::PendingVerification->value,
        ]);
    }
}
