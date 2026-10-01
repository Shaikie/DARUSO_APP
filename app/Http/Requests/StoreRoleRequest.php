<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRoleRequest extends FormRequest
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
        $role = $this->route('role');

        return [
            'name' => [
                'required',
                'string',
                'max:100',
                'regex:/^[a-z0-9_]+$/',
                Rule::unique('roles', 'name')->ignore($role?->getKey()),
            ],
            'description' => ['nullable', 'string', 'max:500'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['integer', Rule::exists('permissions', 'id')],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.regex' => 'Role names may only contain lowercase letters, numbers and underscores.',
            'name.unique' => 'A role with that name already exists.',
            'permissions.*.exists' => 'Unknown permission selected.',
        ];
    }

    /**
     * @return array<int, int>
     */
    public function permissionIds(): array
    {
        return array_map('intval', $this->validated('permissions', []));
    }
}
