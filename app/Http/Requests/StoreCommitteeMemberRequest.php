<?php

namespace App\Http\Requests;

use App\Models\CommitteeMember;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCommitteeMemberRequest extends FormRequest
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
            'committee_id' => ['required', 'integer', Rule::exists('committees', 'id')],
            'leadership_term_id' => ['required', 'integer', Rule::exists('leadership_terms', 'id')],
            'role_in_committee' => ['nullable', 'string', 'max:100'],

            // The composite unique index is (user, committee, term). It is checked here
            // rather than with `Rule::unique` because that rule compares against
            // the value of the attribute it is attached to — which would be the
            // missing `unique_combination` key rather than the user id.
            'user_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id'),
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $exists = CommitteeMember::where('user_id', $value)
                        ->where('committee_id', $this->input('committee_id'))
                        ->where('leadership_term_id', $this->input('leadership_term_id'))
                        ->when($this->route('committee_member'), fn ($query, $id) => $query->whereKeyNot($id))
                        ->exists();

                    if ($exists) {
                        $fail('That person is already a member of this committee for the selected term.');
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
