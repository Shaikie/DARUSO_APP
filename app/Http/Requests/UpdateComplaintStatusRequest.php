<?php

namespace App\Http\Requests;

use App\Enums\ComplaintStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateComplaintStatusRequest extends FormRequest
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
            'status' => ['required', Rule::in(ComplaintStatus::values())],
            'notes' => ['nullable', 'string', 'max:2000'],

            // Assignment is optional but both fields are validated together so a
            // leader cannot set a leader without a ministry to act.
            'assigned_ministry_id' => ['nullable', 'integer', Rule::exists('ministries', 'id')],
            'assigned_leader_id' => ['nullable', 'integer', Rule::exists('users', 'id')],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'status.required' => 'Select the new status for this complaint.',
        ];
    }
}
