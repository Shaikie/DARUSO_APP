<?php

namespace Database\Seeders;

use App\Enums\ComplaintStatus;
use App\Models\Complaint;
use App\Models\ComplaintHistory;
use App\Models\Ministry;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Seeds complaints across the workflow with matching history entries.
 */
class ComplaintSeeder extends Seeder
{
    public function run(): void
    {
        $students = User::whereHas('studentProfile')->limit(6)->get();

        if ($students->isEmpty()) {
            return;
        }

        $secretary = User::where('email', 'secretary@daruso.local')->first();
        $welfare = Ministry::where('name', 'Welfare Ministry')->first();
        $academic = Ministry::where('name', 'Academic Ministry')->first();

        $complaints = [
            [
                'student' => 0,
                'title' => 'Hostel water supply unreliable',
                'description' => 'Water supply to Hostel A has been unreliable for the past two weeks, particularly in the evenings.',
                'category' => 'hostel',
                'status' => ComplaintStatus::InProgress,
                'ministry' => 'welfare',
            ],
            [
                'student' => 1,
                'title' => 'Examination timetable not posted',
                'description' => 'The examination timetable has not been posted on notice boards nor shared with students.',
                'category' => 'academic',
                'status' => ComplaintStatus::UnderReview,
                'ministry' => 'academic',
            ],
            [
                'student' => 2,
                'title' => 'Laboratory equipment out of order',
                'description' => 'Several laboratory benches and fittings in the engineering faculty are not working.',
                'category' => 'infrastructure',
                'status' => ComplaintStatus::Submitted,
                'ministry' => null,
            ],
            [
                'student' => 3,
                'title' => 'Delay in scholarship disbursement',
                'description' => 'The bursary disbursement promised last semester has not yet reached eligible students.',
                'category' => 'finance',
                'status' => ComplaintStatus::Resolved,
                'ministry' => 'welfare',
            ],
            [
                'student' => 4,
                'title' => 'Overcrowding in the faculty library',
                'description' => 'The library reading space is heavily overcrowded during examination periods.',
                'category' => 'academic',
                'status' => ComplaintStatus::AwaitingStudent,
                'ministry' => 'academic',
            ],
        ];

        foreach ($complaints as $index => $data) {
            $student = $students[$data['student'] % $students->count()];

            $complaint = Complaint::firstOrCreate(
                ['title' => $data['title'], 'creator_id' => $student->getKey()],
                [
                    'description' => $data['description'],
                    'category' => $data['category'],
                    'status' => $data['status']->value,
                    'assigned_ministry_id' => $data['ministry'] === 'welfare' ? $welfare?->getKey()
                        : ($data['ministry'] === 'academic' ? $academic?->getKey() : null),
                    'assigned_leader_id' => $secretary?->getKey(),
                    'resolved_at' => $data['status'] === ComplaintStatus::Resolved ? now()->subDays(2) : null,
                ],
            );

            if ($complaint->history()->exists()) {
                continue;
            }

            $chain = [
                [null, ComplaintStatus::Submitted, 'Complaint submitted by the student.'],
                [ComplaintStatus::Submitted, ComplaintStatus::UnderReview, 'Complaint received and acknowledged.'],
            ];

            if ($data['status'] !== ComplaintStatus::Submitted) {
                $chain[] = [ComplaintStatus::UnderReview, $data['status'], 'Status updated by the handling leader.'];
            }

            foreach ($chain as $step => [$old, $new, $notes]) {
                ComplaintHistory::create([
                    'complaint_id' => $complaint->getKey(),
                    'actor_id' => $step === 0 ? $student->getKey() : $secretary?->getKey(),
                    'action' => $new === ComplaintStatus::Submitted ? 'submitted' : 'status_changed',
                    'old_status' => $old?->value,
                    'new_status' => $new->value,
                    'notes' => $notes,
                    'created_at' => now()->subDays(count($complaints) - $index)->addHours($step),
                ]);
            }
        }
    }
}
