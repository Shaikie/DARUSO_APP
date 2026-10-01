<?php

namespace Database\Seeders;

use App\Services\SettingService;
use Illuminate\Database\Seeder;

/**
 * Seeds configurable system settings.
 */
class SettingSeeder extends Seeder
{
    public function run(): void
    {
        app(SettingService::class)->put([
            'system_name' => 'DARUSO',
            'system_tagline' => 'Digital Communication & Information Management System',
            'organisation_name' => 'Daruso Students Organisation',
            'institution_name' => 'University of Dar es Salaam',
            'default_announcement_priority' => 'normal',
            'allow_public_announcements' => true,
            'allow_student_registration' => true,
            'notification_email_enabled' => false,
            'notification_sms_enabled' => false,
            'complaint_response_days' => 14,
        ]);
    }
}
