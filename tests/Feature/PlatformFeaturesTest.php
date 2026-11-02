<?php

namespace Tests\Feature;

use App\Enums\PollStatus;
use App\Enums\RoleName;
use App\Mail\NotificationMail;
use App\Models\Feedback;
use App\Models\Poll;
use App\Models\PollOption;
use App\Models\User;
use App\Services\NotificationDispatcher;
use App\Services\NotificationPreferenceService;
use App\Services\LeadershipInvitationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PlatformFeaturesTest extends TestCase
{
    use RefreshDatabase;

    public function test_verified_student_can_submit_and_track_feedback(): void
    {
        $this->seed();
        $student = User::query()->where('email', 'student@daruso.local')->firstOrFail();

        $response = $this->actingAs($student)->post(route('student.feedback.store'), [
            'subject' => 'Improve campus Wi-Fi',
            'message' => 'The connection is unreliable in the evenings.',
            'category' => 'welfare',
            'is_anonymous' => '1',
        ]);

        $feedback = Feedback::query()->where('subject', 'Improve campus Wi-Fi')->firstOrFail();

        $response->assertRedirect(route('student.feedback.show', $feedback));
        $this->assertDatabaseHas('feedback', [
            'id' => $feedback->getKey(),
            'user_id' => $student->getKey(),
            'is_anonymous' => true,
            'status' => 'open',
        ]);

        $this->actingAs($student)
            ->get(route('student.feedback.show', $feedback))
            ->assertOk()
            ->assertSee('Improve campus Wi-Fi');
    }

    public function test_verified_student_can_vote_only_once_in_a_poll(): void
    {
        $this->seed();
        $student = User::query()->where('email', 'student@daruso.local')->firstOrFail();
        $poll = Poll::create([
            'title' => 'Preferred meeting time?',
            'created_by' => User::query()->where('email', 'admin@daruso.local')->value('id'),
            'status' => PollStatus::Open,
            'starts_at' => now()->subMinute(),
            'ends_at' => now()->addDay(),
        ]);
        $morning = PollOption::create(['poll_id' => $poll->getKey(), 'label' => 'Morning']);
        PollOption::create(['poll_id' => $poll->getKey(), 'label' => 'Afternoon']);

        $this->actingAs($student)
            ->post(route('student.polls.vote', $poll), ['option_id' => $morning->getKey()])
            ->assertRedirect();

        $this->assertDatabaseHas('poll_votes', [
            'poll_id' => $poll->getKey(),
            'poll_option_id' => $morning->getKey(),
            'user_id' => $student->getKey(),
        ]);

        $this->actingAs($student)
            ->post(route('student.polls.vote', $poll), ['option_id' => $morning->getKey()])
            ->assertRedirect();

        $this->assertDatabaseCount('poll_votes', 1);
    }

    public function test_seeded_leadership_roles_receive_their_scoped_platform_permissions(): void
    {
        $this->seed();
        $secretary = User::query()->where('email', 'secretary@daruso.local')->firstOrFail();
        $ministryLeader = User::query()->where('email', 'ministry@daruso.local')->firstOrFail();
        $committeeLeader = User::query()->where('email', 'committee@daruso.local')->firstOrFail();

        $this->assertTrue($secretary->hasPermission('leader.invite'));
        $this->assertTrue($secretary->hasPermission('poll.manage'));
        $this->assertTrue($secretary->hasPermission('activity_report.review'));
        $this->assertTrue($ministryLeader->hasPermission('activity_report.submit'));
        $this->assertTrue($ministryLeader->hasPermission('task.assign'));
        $this->assertFalse($ministryLeader->hasPermission('poll.manage'));
        $this->assertTrue($committeeLeader->hasPermission('handover.manage'));
        $this->assertTrue($committeeLeader->hasPermission('task.view'));
    }

    public function test_notification_preferences_control_unread_state_and_email_delivery(): void
    {
        Mail::fake();
        $sender = User::factory()->create();
        $recipient = User::factory()->create();
        $preferences = app(NotificationPreferenceService::class);
        $preferences->update($recipient, [
            'community' => ['in_app' => false, 'email' => true],
        ]);

        $feedback = Feedback::create([
            'user_id' => $recipient->getKey(),
            'subject' => 'A suggestion',
            'message' => 'A message',
            'category' => 'general',
            'status' => 'open',
        ]);

        $notification = app(NotificationDispatcher::class)->sendToUser(
            recipient: $recipient,
            sender: $sender,
            title: 'A response',
            message: 'We received your suggestion.',
            related: $feedback,
        );

        $this->assertNotNull($notification->read_at);
        Mail::assertSent(NotificationMail::class);
    }
    public function test_leadership_invitation_promotes_an_existing_verified_student_without_losing_the_student_profile(): void
    {
        $this->seed();
        $secretary = User::query()->where('email', 'secretary@daruso.local')->firstOrFail();
        $student = User::query()->where('email', 'student@daruso.local')->firstOrFail();
        $profileId = $student->studentProfile->getKey();

        $issued = app(LeadershipInvitationService::class)->invite([
            'email' => $student->email,
            'role_name' => RoleName::MinistryLeader->value,
            'expires_in_days' => 7,
        ], $secretary);

        app(LeadershipInvitationService::class)->accept(
            $issued['invitation'],
            $issued['token'],
            $student,
        );

        $student->refresh()->forgetAuthorizationCache();

        $this->assertTrue($student->hasRole(RoleName::MinistryLeader));
        $this->assertFalse($student->hasRole(RoleName::Student));
        $this->assertTrue($student->isLeader());
        $this->assertTrue($student->isVerifiedStudent());
        $this->assertSame($profileId, $student->studentProfile->getKey());

        $this->actingAs($student)->get(route('leader.dashboard'))->assertOk();
        $this->actingAs($student)->get(route('student.dashboard'))->assertOk();
    }

}
