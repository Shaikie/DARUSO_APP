<?php

namespace Tests\Feature;

use App\Enums\AudienceType;
use App\Enums\PermissionName;
use App\Models\AudienceRule;
use App\Models\Event;
use App\Models\Meeting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MeetingEventTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_leader_without_create_permission_cannot_schedule_a_meeting(): void
    {
        $leader = $this->userWithRole('observer');

        $this->actingAs($leader)
            ->get(route('leader.meetings.create'))
            ->assertForbidden();
    }

    public function test_a_leader_can_schedule_a_targeted_meeting(): void
    {
        $leader = $this->userWithRole('scheduler', [
            PermissionName::MeetingCreate->value,
        ]);

        $this->actingAs($leader)
            ->post(route('leader.meetings.store'), [
                'title' => 'Finance committee review',
                'description' => 'Quarterly review of income and expenditure.',
                'meeting_date' => now()->addWeek()->toDateString(),
                'meeting_time' => '10:00',
                'venue' => 'Boardroom',
                'status' => 'scheduled',
                'agenda' => 'Financial statements',
                'audience' => ['committee' => ['Finance Committee']],
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('meetings', [
            'title' => 'Finance committee review',
            'organizer_id' => $leader->getKey(),
        ]);

        $meeting = Meeting::firstWhere('title', 'Finance committee review');
        $this->assertSame(1, $meeting->audienceRules()->count());
        $this->assertSame(
            AudienceType::Committee,
            $meeting->audienceRules()->first()->audience_type
        );
    }

    public function test_a_meeting_requires_an_audience(): void
    {
        $leader = $this->userWithRole('scheduler', [
            PermissionName::MeetingCreate->value,
        ]);

        $this->actingAs($leader)
            ->post(route('leader.meetings.store'), [
                'title' => 'No audience meeting',
                'description' => 'This meeting has no target audience configured.',
                'meeting_date' => now()->addWeek()->toDateString(),
                'meeting_time' => '10:00',
                'venue' => 'Boardroom',
                'status' => 'scheduled',
            ])
            ->assertSessionHasErrors('audience');
    }

    public function test_meeting_creation_is_validated(): void
    {
        $leader = $this->userWithRole('scheduler', [
            PermissionName::MeetingCreate->value,
        ]);

        $this->actingAs($leader)
            ->post(route('leader.meetings.store'), [
                'title' => 'x',
                'description' => 'short',
                'meeting_date' => now()->addWeek()->toDateString(),
                'meeting_time' => '99:99',
                'venue' => '',
                'status' => 'invalid',
                'audience' => ['all_students' => '1'],
            ])
            ->assertSessionHasErrors(['title', 'description', 'meeting_time', 'venue', 'status']);
    }

    public function test_a_student_only_sees_meetings_addressed_to_them(): void
    {
        $student = $this->student(['hostel' => 'Hostel A']);

        $mine = Meeting::factory()->create(['title' => 'Hostel A assembly']);
        $mine->audienceRules()->attach(
            AudienceRule::factory()->type(AudienceType::Hostel, 'Hostel A')->create()->getKey()
        );

        $theirs = Meeting::factory()->create(['title' => 'Hostel B assembly']);
        $theirs->audienceRules()->attach(
            AudienceRule::factory()->type(AudienceType::Hostel, 'Hostel B')->create()->getKey()
        );

        $this->actingAs($student)
            ->get(route('student.meetings.index'))
            ->assertOk()
            ->assertSee('Hostel A assembly')
            ->assertDontSee('Hostel B assembly');
    }

    public function test_a_student_cannot_open_a_meeting_addressed_elsewhere(): void
    {
        $student = $this->student(['hostel' => 'Hostel A']);

        $meeting = Meeting::factory()->create();
        $meeting->audienceRules()->attach(
            AudienceRule::factory()->type(AudienceType::Hostel, 'Hostel B')->create()->getKey()
        );

        $this->actingAs($student)
            ->get(route('student.meetings.show', $meeting))
            ->assertForbidden();
    }

    public function test_a_leader_without_manage_permission_cannot_delete_a_meeting(): void
    {
        $organizer = $this->userWithRole('scheduler', [
            PermissionName::MeetingCreate->value,
            PermissionName::MeetingEdit->value,
        ]);

        $meeting = Meeting::factory()->create(['organizer_id' => $organizer->getKey()]);

        $this->actingAs($organizer)
            ->delete(route('leader.meetings.destroy', $meeting))
            ->assertForbidden();

        $this->assertDatabaseHas('meetings', ['id' => $meeting->getKey()]);
    }

    public function test_the_organizer_can_update_their_own_meeting(): void
    {
        $organizer = $this->userWithRole('scheduler', [
            PermissionName::MeetingCreate->value,
            PermissionName::MeetingEdit->value,
        ]);

        $meeting = Meeting::factory()->create(['organizer_id' => $organizer->getKey()]);

        $this->actingAs($organizer)
            ->put(route('leader.meetings.update', $meeting), [
                'title' => 'Rescheduled review',
                'description' => 'The meeting has moved to a new date.',
                'meeting_date' => now()->addWeeks(2)->toDateString(),
                'meeting_time' => '11:00',
                'venue' => 'Main Auditorium',
                'status' => 'scheduled',
                'audience' => ['all_students' => '1'],
            ])
            ->assertRedirect();

        $this->assertSame('Rescheduled review', $meeting->fresh()->title);
    }

    public function test_another_leader_without_manage_cannot_update_someone_elses_meeting(): void
    {
        $organizer = $this->userWithRole('scheduler', [
            PermissionName::MeetingCreate->value,
            PermissionName::MeetingEdit->value,
        ]);

        $other = $this->userWithRole('scheduler');

        $meeting = Meeting::factory()->create(['organizer_id' => $organizer->getKey()]);

        $this->actingAs($other)
            ->get(route('leader.meetings.edit', $meeting))
            ->assertForbidden();
    }

    public function test_a_leader_can_create_an_event(): void
    {
        $leader = $this->userWithRole('scheduler', [
            PermissionName::EventCreate->value,
        ]);

        $this->actingAs($leader)
            ->post(route('leader.events.store'), [
                'title' => 'Health outreach',
                'description' => 'Free health screening for all students.',
                'event_date' => now()->addDays(10)->toDateString(),
                'venue' => 'Faculty Hall',
                'status' => 'upcoming',
                'audience' => ['all_students' => '1'],
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('events', [
            'title' => 'Health outreach',
            'organizer_id' => $leader->getKey(),
        ]);
    }

    public function test_a_student_only_sees_events_addressed_to_them(): void
    {
        $student = $this->student(['programme' => 'Computer Science']);

        $mine = Event::factory()->create(['title' => 'CS career day']);
        $mine->audienceRules()->attach(
            AudienceRule::factory()->type(AudienceType::Programme, 'Computer Science')->create()->getKey()
        );

        $theirs = Event::factory()->create(['title' => 'Civil engineering expo']);
        $theirs->audienceRules()->attach(
            AudienceRule::factory()->type(AudienceType::Programme, 'Civil Engineering')->create()->getKey()
        );

        $this->actingAs($student)
            ->get(route('student.events.index'))
            ->assertOk()
            ->assertSee('CS career day')
            ->assertDontSee('Civil engineering expo');
    }

    public function test_a_student_cannot_open_an_event_addressed_elsewhere(): void
    {
        $student = $this->student(['programme' => 'Computer Science']);

        $event = Event::factory()->create();
        $event->audienceRules()->attach(
            AudienceRule::factory()->type(AudienceType::Programme, 'Civil Engineering')->create()->getKey()
        );

        $this->actingAs($student)
            ->get(route('student.events.show', $event))
            ->assertForbidden();
    }

    public function test_event_creation_is_validated(): void
    {
        $leader = $this->userWithRole('scheduler', [
            PermissionName::EventCreate->value,
        ]);

        $this->actingAs($leader)
            ->post(route('leader.events.store'), [
                'title' => '',
                'description' => '',
                'event_date' => 'not-a-date',
                'venue' => '',
                'status' => 'nope',
                'audience' => [],
            ])
            ->assertSessionHasErrors(['title', 'description', 'event_date', 'venue', 'status', 'audience']);
    }

    public function test_upcoming_filter_only_returns_future_scheduled_records(): void
    {
        $leader = $this->userWithRole('scheduler', [
            PermissionName::MeetingCreate->value,
        ]);

        // The organizer is set explicitly, so the organizer policy admits all
        // three records and the `upcoming` filter is what is under test.
        Meeting::factory()->create([
            'title' => 'Future meeting',
            'meeting_date' => now()->addDays(5),
            'organizer_id' => $leader->getKey(),
        ]);

        Meeting::factory()->create([
            'title' => 'Past meeting',
            'meeting_date' => now()->subDays(5),
            'organizer_id' => $leader->getKey(),
        ]);

        Meeting::factory()->cancelled()->create([
            'title' => 'Cancelled meeting',
            'meeting_date' => now()->addDays(2),
            'organizer_id' => $leader->getKey(),
        ]);
        $this->actingAs($leader)
            ->get(route('student.meetings.index', ['upcoming' => 1]))
            ->assertOk()
            ->assertSee('Future meeting')
            ->assertDontSee('Past meeting')
            ->assertDontSee('Cancelled meeting');
    }
}
