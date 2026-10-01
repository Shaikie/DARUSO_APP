<?php

namespace Tests\Feature;

use App\Enums\AnnouncementStatus;
use App\Enums\AudienceType;
use App\Enums\PermissionName;
use App\Enums\RoleName;
use App\Models\Announcement;
use App\Models\AudienceRule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnnouncementTest extends TestCase
{
    use RefreshDatabase;

    private function author(array $extra = []): User
    {
        return $this->userWithRole(RoleName::MinistryLeader, $extra);
    }

    public function test_a_leader_without_create_permission_cannot_open_the_create_form(): void
    {
        $leader = $this->userWithRole('announcement_author');

        $this->actingAs($leader)
            ->get(route('leader.announcements.create'))
            ->assertForbidden();
    }

    public function test_a_leader_with_create_permission_can_create_an_announcement(): void
    {
        $leader = $this->author([PermissionName::AnnouncementCreate->value]);

        $this->actingAs($leader)
            ->post(route('leader.announcements.store'), [
                'title' => 'Orientation week',
                'content' => 'Orientation for all new students takes place next week.',
                'priority' => 'high',
                'status' => 'draft',
                'audience' => ['all_students' => '1'],
            ])
            ->assertRedirect(route('leader.announcements.index'));

        $this->assertDatabaseHas('announcements', [
            'title' => 'Orientation week',
            'author_id' => $leader->getKey(),
            'status' => 'draft',
        ]);

        $announcement = Announcement::firstWhere('title', 'Orientation week');
        $this->assertSame(1, $announcement->audienceRules()->count());

        // `audience_type` is cast to the enum, which is what the app relies on.
        $this->assertSame(
            AudienceType::AllStudents,
            $announcement->audienceRules()->first()->audience_type
        );
    }

    public function test_an_announcement_requires_an_audience(): void
    {
        $leader = $this->author([PermissionName::AnnouncementCreate->value]);

        $this->actingAs($leader)
            ->post(route('leader.announcements.store'), [
                'title' => 'No audience',
                'content' => 'This announcement has no target audience at all.',
                'priority' => 'normal',
                'status' => 'draft',
            ])
            ->assertSessionHasErrors('audience');

        $this->assertDatabaseMissing('announcements', ['title' => 'No audience']);
    }

    public function test_content_and_title_are_validated(): void
    {
        $leader = $this->author([PermissionName::AnnouncementCreate->value]);

        $this->actingAs($leader)
            ->post(route('leader.announcements.store'), [
                'title' => 'x',
                'content' => 'short',
                'priority' => 'invalid',
                'status' => 'draft',
                'audience' => ['all_students' => '1'],
            ])
            ->assertSessionHasErrors(['title', 'content', 'priority']);
    }

    public function test_a_leader_without_publish_permission_cannot_publish(): void
    {
        // A role with create/edit but not publish.
        $leader = $this->userWithRole('announcement_author', [
            PermissionName::AnnouncementCreate->value,
            PermissionName::AnnouncementEdit->value,
        ]);

        $announcement = Announcement::factory()->create(['author_id' => $leader->getKey()]);

        $this->actingAs($leader)
            ->post(route('leader.announcements.publish', $announcement))
            ->assertForbidden();

        $this->assertSame(AnnouncementStatus::Draft, $announcement->fresh()->status);
    }

    public function test_publishing_requires_the_publish_permission(): void
    {
        $leader = $this->userWithRole('announcement_author', [
            PermissionName::AnnouncementCreate->value,
            PermissionName::AnnouncementEdit->value,
            PermissionName::AnnouncementPublish->value,
        ]);

        $announcement = Announcement::factory()->create(['author_id' => $leader->getKey()]);

        $this->actingAs($leader)
            ->post(route('leader.announcements.publish', $announcement))
            ->assertRedirect();

        $announcement->refresh();

        $this->assertSame(AnnouncementStatus::Published, $announcement->status);
        $this->assertNotNull($announcement->published_at);
    }

    public function test_publishing_is_audited(): void
    {
        $leader = $this->userWithRole('announcement_author', [
            PermissionName::AnnouncementCreate->value,
            PermissionName::AnnouncementPublish->value,
        ]);

        $announcement = Announcement::factory()->create(['author_id' => $leader->getKey()]);

        $this->actingAs($leader)
            ->post(route('leader.announcements.publish', $announcement));

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'published',
            'target_id' => $announcement->getKey(),
            'actor_id' => $leader->getKey(),
        ]);
    }

    public function test_draft_can_be_submitted_for_review(): void
    {
        $leader = $this->author([
            PermissionName::AnnouncementCreate->value,
            PermissionName::AnnouncementEdit->value,
        ]);

        $announcement = Announcement::factory()->create(['author_id' => $leader->getKey()]);

        $this->actingAs($leader)
            ->post(route('leader.announcements.submit', $announcement))
            ->assertRedirect();

        $this->assertSame(AnnouncementStatus::Review, $announcement->fresh()->status);
    }

    public function test_another_leader_cannot_submit_someone_elses_draft(): void
    {
        $author = $this->author([PermissionName::AnnouncementCreate->value]);
        $other = $this->author([PermissionName::AnnouncementCreate->value]);

        $announcement = Announcement::factory()->create(['author_id' => $author->getKey()]);

        $this->actingAs($other)
            ->post(route('leader.announcements.submit', $announcement))
            ->assertForbidden();
    }

    public function test_published_announcement_can_be_archived(): void
    {
        $leader = $this->author([PermissionName::AnnouncementPublish->value]);

        $announcement = Announcement::factory()->published()->create(['author_id' => $leader->getKey()]);

        $this->actingAs($leader)
            ->post(route('leader.announcements.archive', $announcement))
            ->assertRedirect();

        $this->assertSame(AnnouncementStatus::Archived, $announcement->fresh()->status);
    }

    public function test_a_student_only_sees_announcements_addressed_to_them(): void
    {
        $student = $this->student(['hostel' => 'Hostel A']);
        $otherStudent = $this->student(['hostel' => 'Hostel B']);

        $mine = Announcement::factory()->published()->create(['title' => 'For Hostel A']);
        $mine->audienceRules()->attach(
            AudienceRule::factory()->type(AudienceType::Hostel, 'Hostel A')->create()->getKey()
        );

        $theirs = Announcement::factory()->published()->create(['title' => 'For Hostel B']);
        $theirs->audienceRules()->attach(
            AudienceRule::factory()->type(AudienceType::Hostel, 'Hostel B')->create()->getKey()
        );

        $response = $this->actingAs($student)->get(route('student.announcements.index'));

        $response->assertOk();
        $response->assertSee('For Hostel A');
        $response->assertDontSee('For Hostel B');
    }

    public function test_a_student_cannot_open_an_announcement_addressed_elsewhere(): void
    {
        $student = $this->student(['hostel' => 'Hostel A']);

        $announcement = Announcement::factory()->published()->create();
        $announcement->audienceRules()->attach(
            AudienceRule::factory()->type(AudienceType::Hostel, 'Hostel B')->create()->getKey()
        );

        $this->actingAs($student)
            ->get(route('student.announcements.show', $announcement))
            ->assertForbidden();
    }

    public function test_drafts_are_invisible_to_students_even_when_targeted(): void
    {
        $student = $this->student();

        $draft = Announcement::factory()->create(['author_id' => $student->getKey()]);
        $draft->audienceRules()->attach(
            AudienceRule::factory()->type(AudienceType::AllStudents)->create()->getKey()
        );

        $this->actingAs($student)
            ->get(route('student.announcements.index'))
            ->assertOk()
            ->assertDontSee($draft->title);
    }

    public function test_expired_announcements_are_hidden_from_students(): void
    {
        $student = $this->student();

        $expired = Announcement::factory()->expired()->create();
        $expired->audienceRules()->attach(
            AudienceRule::factory()->type(AudienceType::AllStudents)->create()->getKey()
        );

        $this->actingAs($student)
            ->get(route('student.announcements.index'))
            ->assertOk()
            ->assertDontSee($expired->title);
    }

    public function test_an_author_can_edit_their_own_draft(): void
    {
        $leader = $this->author([
            PermissionName::AnnouncementCreate->value,
            PermissionName::AnnouncementEdit->value,
        ]);

        $announcement = Announcement::factory()->create(['author_id' => $leader->getKey()]);

        $this->actingAs($leader)
            ->get(route('leader.announcements.edit', $announcement))
            ->assertOk();

        $this->actingAs($leader)
            ->put(route('leader.announcements.update', $announcement), [
                'title' => 'Revised title',
                'content' => 'Revised content that is long enough to satisfy validation.',
                'priority' => 'normal',
                'status' => 'draft',
                'audience' => ['all_students' => '1'],
            ])
            ->assertRedirect(route('leader.announcements.show', $announcement));

        $this->assertSame('Revised title', $announcement->fresh()->title);
    }

    public function test_deleting_an_announcement_is_audited(): void
    {
        $leader = $this->userWithRole('announcement_author', [
            PermissionName::AnnouncementDelete->value,
        ]);

        $announcement = Announcement::factory()->create(['author_id' => $leader->getKey()]);

        $this->actingAs($leader)
            ->delete(route('leader.announcements.destroy', $announcement))
            ->assertRedirect(route('leader.announcements.index'));

        $this->assertDatabaseMissing('announcements', ['id' => $announcement->getKey()]);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'deleted',
            'target_id' => $announcement->getKey(),
        ]);
    }

    public function test_leader_listing_can_filter_by_status_and_search(): void
    {
        $leader = $this->author([PermissionName::AnnouncementCreate->value]);

        Announcement::factory()->published()->create([
            'author_id' => $leader->getKey(),
            'title' => 'Published notice',
        ]);
        Announcement::factory()->create([
            'author_id' => $leader->getKey(),
            'title' => 'Unpublished draft',
        ]);

        $this->actingAs($leader)
            ->get(route('leader.announcements.index', ['status' => 'published']))
            ->assertOk()
            ->assertSee('Published notice')
            ->assertDontSee('Unpublished draft');

        $this->actingAs($leader)
            ->get(route('leader.announcements.index', ['search' => 'draft']))
            ->assertOk()
            ->assertSee('Unpublished draft');
    }
}
