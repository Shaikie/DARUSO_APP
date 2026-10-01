<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * System settings validation.
 *
 * Keys are restricted to the known configuration surface so an unexpected key
 * cannot be injected into the settings table through this form.
 */
class UpdateSystemSettingsRequest extends FormRequest
{
    /**
     * @var list<string>
     */
    private const ALLOWED = [
        'system_name',
        'system_tagline',
        'organisation_name',
        'institution_name',
        'default_announcement_priority',
        'allow_public_announcements',
        'allow_student_registration',
        'notification_email_enabled',
        'notification_sms_enabled',
        'complaint_response_days',
    ];

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'system_name' => ['required', 'string', 'max:100'],
            'system_tagline' => ['nullable', 'string', 'max:255'],
            'organisation_name' => ['nullable', 'string', 'max:255'],
            'institution_name' => ['nullable', 'string', 'max:255'],
            'default_announcement_priority' => ['nullable', 'in:low,normal,high,urgent'],
            'allow_public_announcements' => ['nullable', 'boolean'],
            'allow_student_registration' => ['nullable', 'boolean'],
            'notification_email_enabled' => ['nullable', 'boolean'],
            'notification_sms_enabled' => ['nullable', 'boolean'],
            'complaint_response_days' => ['nullable', 'integer', 'between:1,365'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function settingsPayload(): array
    {
        return $this->safe()->only(self::ALLOWED);
    }
}
