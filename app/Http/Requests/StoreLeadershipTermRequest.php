<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLeadershipTermRequest extends FormRequest
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
        $term = $this->route('leadership_term');

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('leadership_terms', 'name')->ignore($term?->getKey()),
            ],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'end_date.after' => 'The term end date must be after the start date.',
            'name.unique' => 'A leadership term with that name already exists.',
        ];
    }
}
