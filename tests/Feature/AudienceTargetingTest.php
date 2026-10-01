<?php

namespace Tests\Feature;

use App\Enums\AudienceType;
use App\Models\Announcement;
use App\Models\AudienceRule;
use App\Models\LeaderAssignment;
use App\Models\Ministry;
use App\Models\Position;
use App\Services\AudienceResolver;
use Database\Factories\LeadershipTermFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The targeting engine is the core of the communication design, so it is tested
 * directly: "all students" must resolve without materialising recipient rows.
 */
class AudienceTargetingTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_students_rule_resolves_to_every_student(): void
    {
        $resolver = app(AudienceResolver::class);
        $rule = AudienceRule::factory()->type(AudienceType::AllStudents)->create();

        $students = $this->student();
        $leader = $this->administrator();

        $this->assertTrue($resolver->includes([$rule], $students));
        $this->assertFalse($resolver->includes([$rule], $leader));

        $this->assertSame(1, $resolver->countRecipients([$rule]));
        $this->assertTrue($resolver->recipientIds([$rule])->contains($students->getKey()));
    }

    public function test_all_leaders_rule_does_not_reach_students(): void
    {
        $resolver = app(AudienceResolver::class);
        $rule = AudienceRule::factory()->type(AudienceType::AllLeaders)->create();

        $this->assertTrue($resolver->includes([$rule], $this->administrator()));
        $this->assertFalse($resolver->includes([$rule], $this->student()));
    }

    public function test_hostel_rule_matches_only_students_in_that_hostel(): void
    {
        $resolver = app(AudienceResolver::class);
        $rule = AudienceRule::factory()->type(AudienceType::Hostel, 'Hostel A')->create();

        $inHostel = $this->student(['hostel' => 'Hostel A']);
        $otherHostel = $this->student(['hostel' => 'Hostel B']);

        $this->assertTrue($resolver->includes([$rule], $inHostel));
        $this->assertFalse($resolver->includes([$rule], $otherHostel));
    }

    public function test_year_of_study_rule_matches_only_that_year(): void
    {
        $resolver = app(AudienceResolver::class);
        $rule = AudienceRule::factory()->type(AudienceType::YearOfStudy, '2')->create();

        $secondYear = $this->student(['year_of_study' => 2]);
        $thirdYear = $this->student(['year_of_study' => 3]);

        $this->assertTrue($resolver->includes([$rule], $secondYear));
        $this->assertFalse($resolver->includes([$rule], $thirdYear));
    }

    public function test_college_and_programme_rules_respect_student_attributes(): void
    {
        $resolver = app(AudienceResolver::class);

        $collegeRule = AudienceRule::factory()->type(AudienceType::College, 'College of Science')->create();
        $programmeRule = AudienceRule::factory()->type(AudienceType::Programme, 'Computer Science')->create();

        $student = $this->student([
            'college' => 'College of Science',
            'programme' => 'Computer Science',
        ]);

        $this->assertTrue($resolver->includes([$collegeRule], $student));
        $this->assertTrue($resolver->includes([$programmeRule], $student));
    }

    public function test_individual_rule_matches_only_the_named_user(): void
    {
        $resolver = app(AudienceResolver::class);
        $target = $this->student();
        $other = $this->student();

        $rule = AudienceRule::factory()
            ->type(AudienceType::Individual, (string) $target->getKey())
            ->create();

        $this->assertTrue($resolver->includes([$rule], $target));
        $this->assertFalse($resolver->includes([$rule], $other));
    }

    public function test_multiple_rules_are_unioned(): void
    {
        $resolver = app(AudienceResolver::class);

        $hostelRule = AudienceRule::factory()->type(AudienceType::Hostel, 'Hostel B')->create();
        $programmeRule = AudienceRule::factory()->type(AudienceType::Programme, 'Civil Engineering')->create();

        $inHostel = $this->student(['hostel' => 'Hostel B', 'programme' => 'Mechanical Engineering']);
        $inProgramme = $this->student(['hostel' => 'Hostel A', 'programme' => 'Civil Engineering']);
        $unrelated = $this->student(['hostel' => 'Hostel A', 'programme' => 'Computer Science']);

        $rules = [$hostelRule, $programmeRule];

        $this->assertTrue($resolver->includes($rules, $inHostel));
        $this->assertTrue($resolver->includes($rules, $inProgramme));
        $this->assertFalse($resolver->includes($rules, $unrelated));

        $this->assertSame(2, $resolver->countRecipients($rules));
    }

    public function test_a_rule_without_a_matching_record_resolves_to_nobody(): void
    {
        $resolver = app(AudienceResolver::class);
        $rule = AudienceRule::factory()->type(AudienceType::Hostel, 'Hostel Z')->create();

        $this->assertSame(0, $resolver->countRecipients([$rule]));
    }

    public function test_ministry_rule_matches_a_leader_assigned_in_the_active_term(): void
    {
        $resolver = app(AudienceResolver::class);

        $term = LeadershipTermFactory::new()->active()->create();
        $ministry = Ministry::factory()->create(['name' => 'Welfare Ministry']);
        $position = Position::factory()->create(['name' => 'Minister']);
        $leader = $this->administrator();

        LeaderAssignment::create([
            'user_id' => $leader->getKey(),
            'position_id' => $position->getKey(),
            'ministry_id' => $ministry->getKey(),
            'leadership_term_id' => $term->getKey(),
        ]);

        $leader = $leader->fresh();

        $this->assertTrue($resolver->includes(
            [AudienceRule::factory()->type(AudienceType::Ministry, (string) $ministry->getKey())->create()],
            $leader
        ));
    }

    public function test_subject_query_is_filtered_by_audience(): void
    {
        $user = $this->student(['hostel' => 'Hostel A']);
        $otherUser = $this->student(['hostel' => 'Hostel B']);

        $mine = Announcement::factory()->published()->create(['author_id' => $user->getKey()]);
        $theirs = Announcement::factory()->published()->create(['author_id' => $otherUser->getKey()]);

        $mine->audienceRules()->attach(
            AudienceRule::factory()->type(AudienceType::Hostel, 'Hostel A')->create()->getKey()
        );
        $theirs->audienceRules()->attach(
            AudienceRule::factory()->type(AudienceType::Hostel, 'Hostel B')->create()->getKey()
        );

        $visible = app(AudienceResolver::class)->constrainSubjectForUser(
            Announcement::query(),
            Announcement::class,
            $user,
        )->pluck('id');

        $this->assertTrue($visible->contains($mine->getKey()));
        $this->assertFalse($visible->contains($theirs->getKey()));
    }

    public function test_subject_query_excludes_unaddressed_records(): void
    {
        $user = $this->student();
        $announcement = Announcement::factory()->published()->create();

        $visible = app(AudienceResolver::class)->constrainSubjectForUser(
            Announcement::query(),
            Announcement::class,
            $user,
        )->pluck('id');

        // No audience rules means the message is addressed to nobody in particular.
        $this->assertFalse($visible->contains($announcement->getKey()));
    }
}
