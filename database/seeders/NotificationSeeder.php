<?php

namespace Database\Seeders;

use App\Enums\Priority;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Seeds a representative set of notifications, including unread ones so the
 * unread badge and mark-all-read behaviour are visible in development.
 */
class NotificationSeeder extends Seeder
{
    public function run(): void
    {
        $secretary = User::where('email', 'secretary@daruso.local')->first();
        $student = User::where('email', 'student@daruso.local')->first();

        if ($secretary === null || $student === null) {
            return;
        }

        $samples = [
            [
                'title' => 'Registration number verification required',
                'message' => 'Please confirm your registration number on your profile page.',
                'priority' => Priority::High,
                'read' => false,
            ],
            [
                'title' => 'General assembly scheduled',
                'message' => 'The general student assembly will be held in two weeks. Your attendance is expected.',
                'priority' => Priority::Normal,
                'read' => false,
            ],
            [
                'title' => 'Welcome to DARUSO',
                'message' => 'Your account is active. Explore announcements, events and the complaint portal.',
                'priority' => Priority::Low,
                'read' => true,
            ],
        ];

        foreach ($samples as $sample) {
            Notification::firstOrCreate(
                ['title' => $sample['title'], 'recipient_id' => $student->getKey()],
                [
                    'message' => $sample['message'],
                    'sender_id' => $secretary->getKey(),
                    'priority' => $sample['priority']->value,
                    'read_at' => $sample['read'] ? now()->subDays(1) : null,
                ],
            );
        }
    }
}
