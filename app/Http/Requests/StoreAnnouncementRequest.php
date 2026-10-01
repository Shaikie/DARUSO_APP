<?php

namespace App\Http\Requests;

use App\Enums\AnnouncementStatus;
use App\Enums\AudienceType;
use App\Enums\Priority;
use App\Models\Announcement;
use App\Rules\SafeUpload;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Announcement creation/update validation.
 *
 * Authorization is not done here on purpose: policies own that decision, so a
 * request can be authorised from the controller with the correct subject.
 */
class StoreAnnouncementRequest extends FormRequest
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
        $announcement = $this->route('announcement');

        return [
            'title' => ['required', 'string', 'min:5', 'max:255'],
            'content' => ['required', 'string', 'min:10'],
            'priority' => ['required', Rule::in(Priority::values())],
            'status' => ['required', Rule::in([
                AnnouncementStatus::Draft->value,
                AnnouncementStatus::Review->value,
                AnnouncementStatus::Published->value,
            ])],
            'expires_at' => ['nullable', 'date', 'after:now'],
            'requires_approval' => ['nullable', 'boolean'],

            'attachment' => $this->attachmentRules(),

            // Audience targeting. Every supplied value must correspond to a real
            // record so a typo cannot silently address nobody.
            'audience' => ['required', 'array', 'min:1'],
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
            'audience.individual' => ['nullable', 'array', 'max:500'],
            'audience.individual.*' => ['integer', Rule::exists('users', 'id')],
        ];
    }

    /**
     * The audience array is validated structurally above; normalise it so the
     * controller receives a predictable shape.
     *
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

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'audience.required' => 'Select at least one target audience for this announcement.',
            'content.min' => 'The announcement content must be at least 10 characters.',
        ];
    }

    /**
     * Attachments are optional for announcements.
     *
     * @return array<int, string>
     */
    private function attachmentRules(): array
    {
        return [
            'nullable',
            'file',
            'mimes:'.implode(',', config('daruso.uploads.mimes')),
            new SafeUpload((int) config('daruso.uploads.max_size_kb')),
        ];
    }
}
