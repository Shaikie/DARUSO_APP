<?php

namespace App\Http\Requests;

use App\Enums\AudienceType;
use App\Enums\Priority;
use App\Models\Announcement;
use App\Models\Event;
use App\Models\Meeting;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Notification creation/update validation.
 *
 * A notification must address either explicit recipients or an audience. Sending
 * requires the distinct `notification.send` permission, enforced in the policy.
 */
class StoreNotificationRequest extends FormRequest
{
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
            'title' => ['required', 'string', 'min:5', 'max:255'],
            'message' => ['required', 'string', 'min:5'],
            'priority' => ['required', Rule::in(Priority::values())],

            // Individual delivery.
            'recipients' => ['nullable', 'array', 'max:500'],
            'recipients.*' => ['integer', Rule::exists('users', 'id')],

            // Broadcast targeting. A broadcast must resolve to a bounded audience
            // so a university-wide rule is never materialised row by row.
            'audience' => ['nullable', 'array'],
            'audience.all_students' => ['nullable', 'boolean'],
            'audience.all_leaders' => ['nullable', 'boolean'],
            'audience.college' => ['nullable', 'array', 'max:20'],
            'audience.college.*' => ['string', 'max:255'],
            'audience.school_faculty' => ['nullable', 'array', 'max:20'],
            'audience.school_faculty.*' => ['string', 'max:255'],
            'audience.programme' => ['nullable', 'array', 'max:50'],
            'audience.programme.*' => ['string', 'max:255'],
            'audience.year_of_study' => ['nullable', 'array', 'max:10'],
            'audience.year_of_study.*' => ['integer', 'between:1,10'],
            'audience.hostel' => ['nullable', 'array', 'max:50'],
            'audience.hostel.*' => ['string', 'max:255'],
            'audience.ministry' => ['nullable', 'array', 'max:50'],
            'audience.ministry.*' => ['string', 'max:255'],
            'audience.committee' => ['nullable', 'array', 'max:50'],
            'audience.committee.*' => ['string', 'max:255'],
            'audience.position' => ['nullable', 'array', 'max:50'],
            'audience.position.*' => ['string', 'max:255'],

            // Link back to the subject of the message.
            'related_type' => ['nullable', 'string', Rule::in([
                Announcement::class,
                Meeting::class,
                Event::class,
            ])],
            'related_id' => ['nullable', 'integer'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'recipients.required_without' => 'Select at least one recipient or choose a target audience.',
            'title.required' => 'A notification title is required.',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function audiencePayload(): array
    {
        $audience = $this->validated('audience', []);

        return collect(AudienceType::values())
            ->mapWithKeys(fn (string $type): array => [
                $type => $audience[$type] ?? (AudienceType::from($type)->requiresValue() ? [] : false),
            ])
            ->filter(fn (mixed $value): bool => $value !== false && $value !== [] && $value !== null)
            ->all();
    }
}
