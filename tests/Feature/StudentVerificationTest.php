<?php

namespace Tests\Feature;

use App\Enums\StudentStatus;
use App\Enums\VerificationStatus;
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
            'verification_status' => VerificationStatus::Pending,
        ]);

        $this->actingAs($user)
            ->get(route('student.dashboard'))
            ->assertOk()
            ->assertSee('Your student record is under review');
    }

    public function test_account_without_a_student_profile_cannot_access_student_services(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('student.dashboard'))
            ->assertForbidden();
    }

    public function test_suspended_student_sees_the_correct_account_status_message(): void
    {
        $user = User::factory()->create();

        StudentProfile::factory()->suspended()->create([
            'user_id' => $user->getKey(),
        ]);

        $this->actingAs($user)
            ->get(route('student.dashboard'))
            ->assertOk()
            ->assertSee('Your student access has been suspended')
            ->assertDontSee('Verification pending');
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
            'verification_status' => VerificationStatus::Pending->value,
        ]);
    }

    public function test_authorised_leader_can_approve_a_student_and_the_student_is_notified(): void
    {
        $leader = $this->administrator();
        $studentUser = User::factory()->create();

        $profile = StudentProfile::factory()->create([
            'user_id' => $studentUser->getKey(),
            'status' => StudentStatus::PendingVerification,
        ]);

        $this->actingAs($leader)->put(route('leader.students.update', $profile), [
            'registration_number' => $profile->registration_number,
            'college' => $profile->college,
            'school_faculty' => $profile->school_faculty,
            'programme' => $profile->programme,
            'year_of_study' => $profile->year_of_study,
            'status' => StudentStatus::Active->value,
        ])->assertRedirect(route('leader.students.show', $profile));

        $this->assertDatabaseHas('student_profiles', [
            'id' => $profile->getKey(),
            'status' => StudentStatus::Active->value,
            'verification_status' => VerificationStatus::Verified->value,
        ]);

        $this->assertTrue($studentUser->fresh()->hasRole(\App\Enums\RoleName::Student));

        $this->assertDatabaseHas('notifications', [
            'recipient_id' => $studentUser->getKey(),
            'title' => 'Student record verified',
        ]);
    }
}
