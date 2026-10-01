<?php

namespace Tests\Feature;

use App\Enums\AudienceType;
use App\Enums\PermissionName;
use App\Models\AudienceRule;
use App\Models\Notification;
use App\Services\NotificationDispatcher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    private function dispatcher(): NotificationDispatcher
    {
        return app(NotificationDispatcher::class);
    }

    public function test_a_leader_without_create_permission_cannot_open_the_create_form(): void
    {
        $leader = $this->userWithRole('observer');

        $this->actingAs($leader)
            ->get(route('leader.notifications.create'))
            ->assertForbidden();
    }

    public function test_a_leader_can_send_a_notification_to_an_individual(): void
    {
        $leader = $this->userWithRole('sender', [
            PermissionName::NotificationCreate->value,
            PermissionName::NotificationSend->value,
        ]);
        $student = $this->student();

        $this->actingAs($leader)
            ->post(route('leader.notifications.store'), [
                'title' => 'Reminder',
                'message' => 'Please confirm your registration number.',
                'priority' => 'high',
                'recipients' => [$student->getKey()],
            ])
            ->assertRedirect(route('leader.notifications.index'));

        $this->assertDatabaseHas('notifications', [
            'recipient_id' => $student->getKey(),
            'sender_id' => $leader->getKey(),
            'title' => 'Reminder',
        ]);
    }

    public function test_sending_requires_the_send_permission(): void
    {
        // Create but not send.
        $leader = $this->userWithRole('creator_only', [
            PermissionName::NotificationCreate->value,
        ]);
        $student = $this->student();

        $this->actingAs($leader)
            ->post(route('leader.notifications.store'), [
                'title' => 'Should not send',
                'message' => 'This leader lacks the send permission.',
                'priority' => 'normal',
                'recipients' => [$student->getKey()],
            ])
            ->assertForbidden();

        $this->assertDatabaseCount('notifications', 0);
    }

    public function test_a_broadcast_creates_one_row_per_recipient_within_the_limit(): void
    {
        $leader = $this->userWithRole('sender', [
            PermissionName::NotificationCreate->value,
            PermissionName::NotificationSend->value,
        ]);

        $students = collect(range(1, 3))->map(fn (): mixed => $this->student());

        $rule = AudienceRule::factory()->type(AudienceType::AllStudents)->create();

        $created = $this->dispatcher()->broadcast(
            rules: collect([$rule]),
            sender: $leader,
            title: 'Campus notice',
            message: 'The library will close early.',
        );

        $this->assertSame($students->count(), $created);
        $this->assertDatabaseCount('notifications', $students->count());
    }

    public function test_a_broadcast_above_the_limit_is_refused_with_guidance(): void
    {
        config(['daruso.audience.notification_materialisation_limit' => 2]);

        $leader = $this->userWithRole('sender', [
            PermissionName::NotificationCreate->value,
            PermissionName::NotificationSend->value,
        ]);

        collect(range(1, 4))->each(fn () => $this->student());

        $response = $this->actingAs($leader)
            ->post(route('leader.notifications.store'), [
                'title' => 'Too broad',
                'message' => 'This audience is above the materialisation limit.',
                'priority' => 'normal',
                'audience' => ['all_students' => '1'],
            ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseCount('notifications', 0);
    }

    public function test_marking_a_notification_read_does_not_delete_it(): void
    {
        $student = $this->student();
        $leader = $this->userWithRole('sender', [PermissionName::NotificationCreate->value]);

        $notification = $leader->sentNotifications()->create([
            'title' => 'Please read',
            'message' => 'This notification must survive being read.',
            'recipient_id' => $student->getKey(),
            'priority' => 'normal',
        ]);

        $this->actingAs($student)
            ->patch(route('student.notifications.markAsRead', $notification))
            ->assertRedirect();

        $notification->refresh();

        $this->assertNotNull($notification->read_at);
        $this->assertDatabaseHas('notifications', ['id' => $notification->getKey()]);
    }

    public function test_a_student_cannot_mark_another_students_notification_read(): void
    {
        $owner = $this->student();
        $intruder = $this->student();

        $notification = Notification::factory()->create([
            'recipient_id' => $owner->getKey(),
        ]);

        $this->actingAs($intruder)
            ->patch(route('student.notifications.markAsRead', $notification))
            ->assertForbidden();

        $this->assertNull($notification->fresh()->read_at);
    }

    public function test_a_student_cannot_open_another_students_notification(): void
    {
        $owner = $this->student();
        $intruder = $this->student();

        $notification = Notification::factory()->create([
            'recipient_id' => $owner->getKey(),
        ]);

        $this->actingAs($intruder)
            ->get(route('student.notifications.show', $notification))
            ->assertForbidden();
    }

    public function test_mark_all_as_read_only_touches_the_recipients_own_notifications(): void
    {
        $student = $this->student();
        $other = $this->student();
        $sender = $this->userWithRole('sender', [PermissionName::NotificationCreate->value]);

        Notification::factory()->count(2)->create([
            'recipient_id' => $student->getKey(),
            'sender_id' => $sender->getKey(),
        ]);

        $untouched = Notification::factory()->create([
            'recipient_id' => $other->getKey(),
            'sender_id' => $sender->getKey(),
        ]);

        $this->actingAs($student)
            ->patch(route('student.notifications.read-all'))
            ->assertRedirect();

        $this->assertSame(0, Notification::forRecipient($student)->unread()->count());
        $this->assertNull($untouched->fresh()->read_at);
        $this->assertDatabaseCount('notifications', 3);
    }

    public function test_the_unread_count_reflects_only_unread_notifications(): void
    {
        $student = $this->student();

        Notification::factory()->count(2)->create(['recipient_id' => $student->getKey()]);
        Notification::factory()->read()->create(['recipient_id' => $student->getKey()]);

        $this->assertSame(2, $this->dispatcher()->unreadCount($student));
    }

    public function test_the_inbox_only_lists_the_recipients_notifications(): void
    {
        $student = $this->student();
        $other = $this->student();

        Notification::factory()->create([
            'recipient_id' => $student->getKey(),
            'title' => 'For me only',
        ]);

        Notification::factory()->create([
            'recipient_id' => $other->getKey(),
            'title' => 'Not my notification',
        ]);

        $this->actingAs($student)
            ->get(route('student.notifications.index'))
            ->assertOk()
            ->assertSee('For me only')
            ->assertDontSee('Not my notification');
    }

    public function test_opening_a_notification_marks_it_read(): void
    {
        $student = $this->student();
        $notification = Notification::factory()->create([
            'recipient_id' => $student->getKey(),
        ]);

        $this->actingAs($student)
            ->get(route('student.notifications.show', $notification))
            ->assertOk();

        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_notifications_cannot_be_deleted(): void
    {
        $student = $this->student();
        $notification = Notification::factory()->create([
            'recipient_id' => $student->getKey(),
        ]);

        $this->actingAs($student)
            ->delete(route('student.notifications.destroy', $notification))
            ->assertForbidden();

        $this->assertDatabaseHas('notifications', ['id' => $notification->getKey()]);
    }

    public function test_sending_is_audited(): void
    {
        $leader = $this->userWithRole('sender', [
            PermissionName::NotificationCreate->value,
            PermissionName::NotificationSend->value,
        ]);
        $student = $this->student();

        $this->actingAs($leader)
            ->post(route('leader.notifications.store'), [
                'title' => 'Audited notice',
                'message' => 'This send should be recorded in the audit log.',
                'priority' => 'normal',
                'recipients' => [$student->getKey()],
            ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'notification_sent',
            'actor_id' => $leader->getKey(),
        ]);
    }
}
