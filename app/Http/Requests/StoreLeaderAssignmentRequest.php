<?php

namespace App\Http\Requests;

use App\Models\LeaderAssignment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Leadership assignment validation.
 *
 * The composite unique index (user, position, term) is mirrored here so the
 * error is a friendly validation message rather than a database exception.
 */
class StoreLeaderAssignmentRequest extends FormRequest
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
            'position_id' => ['required', 'integer', Rule::exists('positions', 'id')],
            'ministry_id' => ['nullable', 'integer', Rule::exists('ministries', 'id')],
            'leadership_term_id' => ['required', 'integer', Rule::exists('leadership_terms', 'id')],

            // The composite unique index is (user, position, term). It is checked here
            // rather than with `Rule::unique` because that rule compares against
            // the value of the attribute it is attached to — which would be the
            // missing `unique_combination` key rather than the user id.
            'user_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id'),
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $exists = LeaderAssignment::where('user_id', $value)
                        ->where('position_id', $this->input('position_id'))
                        ->where('leadership_term_id', $this->input('leadership_term_id'))
                        ->when($this->route('leader_assignment'), fn ($query, $id) => $query->whereKeyNot($id))
                        ->exists();

                    if ($exists) {
                        $fail('That person already holds this position in the selected term.');
                    }
                },
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'user_id.exists' => 'The selected user does not exist.',
        ];
    }
}
