<?php

namespace App\Http\Requests;

use App\Enums\StudentStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule as ValidationRule;

/**
 * Student profile validation, used by both self-service and leader editing.
 *
 * The registration number is unique system-wide, so the rule ignores the
 * current profile when editing.
 */
class StoreStudentProfileRequest extends FormRequest
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
        $profile = $this->route('student_profile') ?? $this->route('student');
        $profileId = $profile?->getKey();

        return [
            'registration_number' => [
                'required',
                'string',
                'max:50',
                ValidationRule::unique('student_profiles', 'registration_number')->ignore($profileId),
            ],
            'college' => ['required', 'string', 'max:255'],
            'school_faculty' => ['required', 'string', 'max:255'],
            'programme' => ['required', 'string', 'max:255'],
            'year_of_study' => ['required', 'integer', 'between:1,10'],
            'hostel' => ['nullable', 'string', 'max:255'],
            'gender' => ['nullable', 'string', 'max:20'],
            'status' => ['nullable', ValidationRule::in(StudentStatus::values())],

            // Account details, editable only by those permitted to manage users.
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => [
                'sometimes',
                'email',
                'max:255',
                ValidationRule::unique('users', 'email')->ignore($profile?->user_id),
            ],
            'phone' => ['nullable', 'string', 'max:32'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'registration_number.unique' => 'That registration number is already recorded for another student.',
        ];
    }

    /**
     * Academic fields, separated from account details so a student editing their
     * own profile cannot escalate their status.
     *
     * @return array<string, mixed>
     */
    public function academicPayload(): array
    {
        return $this->safe()->only([
            'registration_number',
            'college',
            'school_faculty',
            'programme',
            'year_of_study',
            'hostel',
            'gender',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function accountPayload(): array
    {
        return $this->safe()->only(['name', 'email', 'phone']);
    }
}
