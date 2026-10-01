<?php

namespace Database\Seeders;

use App\Enums\AnnouncementStatus;
use App\Enums\EventStatus;
use App\Enums\MeetingStatus;
use App\Enums\Priority;
use App\Models\Announcement;
use App\Models\AudienceRule;
use App\Models\Event;
use App\Models\Meeting;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Seeds announcements, meetings and events with a range of audience targets.
 *
 * Demonstrates that broad targeting (all students) is a single rule while
 * specific targeting uses a value.
 */
class CommunicationSeeder extends Seeder
{
    public function run(): void
    {
        $secretary = User::where('email', 'secretary@daruso.local')->first();
        $committeeLeader = User::where('email', 'committee@daruso.local')->first();

        if ($secretary === null) {
            return;
        }

        $rule = static fn (string $type, ?string $value = null): AudienceRule => AudienceRule::firstOrCreate([
            'audience_type' => $type,
            'audience_value' => $value,
        ]);

        // --- Announcements ---------------------------------------------------
        $announcements = [
            [
                'title' => 'Welcome to the DARUSO digital platform',
                'content' => 'This platform is the official channel for announcements, notifications, complaints and student services. Watch this space for regular updates from your leadership.',
                'priority' => Priority::High,
                'days_ago' => 20,
                'rules' => [['all_students', null]],
            ],
            [
                'title' => 'Registration number verification',
                'content' => 'All students are required to confirm their registration number on their profile page. Students with mismatched records should raise a complaint with the Academic Ministry.',
                'priority' => Priority::Urgent,
                'days_ago' => 5,
                'rules' => [['all_students', null]],
            ],
            [
                'title' => 'Hostel allocation review: Hostel A',
                'content' => 'The hostel allocation list for Hostel A is under review. Residents should submit objections through the complaint portal before the end of the month.',
                'priority' => Priority::Normal,
                'days_ago' => 2,
                'rules' => [['hostel', 'Hostel A']],
            ],
            [
                'title' => 'Welfare ministry outreach',
                'content' => 'The Welfare Ministry will hold an outreach session for students experiencing financial hardship. Contact the ministry through the complaint portal to be included.',
                'priority' => Priority::Normal,
                'days_ago' => 1,
                'rules' => [['ministry', 'Welfare Ministry']],
            ],
            [
                'title' => 'Faculty orientation (College of Science)',
                'content' => 'Orientation for all first and second year students of the College of Science will take place as scheduled. All students are to attend.',
                'priority' => Priority::High,
                'days_ago' => 3,
                'rules' => [['college', 'College of Science'], ['programme', 'Computer Science']],
            ],
            [
                'title' => 'Finance committee audit notice',
                'content' => 'The Finance Committee has published its audit schedule. Committee members should confirm availability with the Secretary General.',
                'priority' => Priority::Low,
                'days_ago' => 8,
                'rules' => [['committee', 'Finance Committee']],
            ],
        ];

        foreach ($announcements as $data) {
            $announcement = Announcement::firstOrCreate(
                ['title' => $data['title']],
                [
                    'content' => $data['content'],
                    'author_id' => $secretary->getKey(),
                    'priority' => $data['priority']->value,
                    'status' => AnnouncementStatus::Published->value,
                    'published_at' => now()->subDays($data['days_ago']),
                    'requires_approval' => false,
                ],
            );

            $announcement->audienceRules()->sync(
                collect($data['rules'])
                    ->map(fn (array $audience): AudienceRule => $rule($audience[0], $audience[1]))
                    ->pluck('id')
            );
        }

        // A draft that has not been published, to exercise the review lifecycle.
        Announcement::firstOrCreate(
            ['title' => 'Draft: proposed constitutional amendment'],
            [
                'content' => 'This draft outlines a proposed amendment to the DARUSO constitution regarding the election timeline. It is still under internal review.',
                'author_id' => $secretary->getKey(),
                'priority' => Priority::Normal->value,
                'status' => AnnouncementStatus::Draft->value,
                'requires_approval' => true,
            ],
        );

        // --- Meetings --------------------------------------------------------
        if ($committeeLeader !== null) {
            Meeting::firstOrCreate(
                ['title' => 'Finance committee quarterly review'],
                [
                    'description' => 'Quarterly review of income, expenditure and outstanding disbursements.',
                    'organizer_id' => $committeeLeader->getKey(),
                    'meeting_date' => now()->addDays(7)->toDateString(),
                    'meeting_time' => '10:00:00',
                    'venue' => 'DARUSO Boardroom',
                    'status' => MeetingStatus::Scheduled->value,
                    'agenda' => "1. Financial statements\n2. Outstanding requests\n3. Next quarter budget",
                ],
            )->audienceRules()->sync([
                $rule('committee', 'Finance Committee')->getKey(),
            ]);

            Meeting::firstOrCreate(
                ['title' => 'General student assembly'],
                [
                    'description' => 'Open assembly for all students to hear current reports and raise concerns.',
                    'organizer_id' => $secretary->getKey(),
                    'meeting_date' => now()->addDays(14)->toDateString(),
                    'meeting_time' => '14:00:00',
                    'venue' => 'Main Auditorium',
                    'status' => MeetingStatus::Scheduled->value,
                    'agenda' => "1. President's address\n2. Ministry reports\n3. Open forum",
                ],
            )->audienceRules()->sync([
                $rule('all_students')->getKey(),
            ]);

            Meeting::firstOrCreate(
                ['title' => 'Academic ministry planning'],
                [
                    'description' => 'Planning session for the academic ministry portfolio.',
                    'organizer_id' => $secretary->getKey(),
                    'meeting_date' => now()->subDays(10)->toDateString(),
                    'meeting_time' => '09:30:00',
                    'venue' => 'Ministry Room',
                    'status' => MeetingStatus::Completed->value,
                    'agenda' => "1. Examination support\n2. Academic complaints backlog",
                ],
            )->audienceRules()->sync([
                $rule('ministry', 'Academic Ministry')->getKey(),
            ]);
        }

        // --- Events ----------------------------------------------------------
        Event::firstOrCreate(
            ['title' => 'Freshers welcome week'],
            [
                'description' => 'A week of activities welcoming new students to the university and to DARUSO.',
                'organizer_id' => $secretary->getKey(),
                'event_date' => now()->addDays(21)->toDateString(),
                'venue' => 'University Grounds',
                'status' => EventStatus::Upcoming->value,
            ],
        )->audienceRules()->sync([
            $rule('all_students')->getKey(),
        ]);

        Event::firstOrCreate(
            ['title' => 'Community health outreach'],
            [
                'description' => 'Free health screening and wellbeing talk delivered by the Health Ministry.',
                'organizer_id' => $secretary->getKey(),
                'event_date' => now()->addDays(10)->toDateString(),
                'venue' => 'Faculty Hall',
                'status' => EventStatus::Upcoming->value,
            ],
        )->audienceRules()->sync([
            $rule('all_students')->getKey(),
        ]);

        unset($committeeLeader);
    }
}
