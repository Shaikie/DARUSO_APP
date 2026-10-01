<?php

namespace App\Http\Requests;

use App\Models\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Assigning a role to a user.
 *
 * Two entry points exist: the role page submits `user_id` for a known role in
 * the route, and the user-facing flow submits `role_id`. Exactly one is required.
 *
 * Privilege escalation is guarded here: a grantor must already hold every
 * permission the role confers, so `role.manage` alone can never hand out an
 * administrator role.
 */
class AssignRoleRequest extends FormRequest
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
            'role_id' => ['required_without:user_id', 'nullable', 'integer', Rule::exists('roles', 'id')],
            'user_id' => ['required_without:role_id', 'nullable', 'integer', Rule::exists('users', 'id')],
        ];
    }

    /**
     * The role being granted, whether submitted directly or taken from the route.
     */
    public function targetRole(): ?Role
    {
        return Role::find($this->integer('role_id')) ?? $this->route('role');
    }

    /**
     * Whether the acting user may grant the requested role.
     */
    public function canGrant(): bool
    {
        $role = $this->targetRole();

        if ($role === null) {
            return false;
        }

        $actorPermissions = $this->user()->grantedPermissions();

        // Granting requires holding every permission the role confers.
        return $role->permissions()
            ->pluck('name')
            ->diff($actorPermissions)
            ->isEmpty();
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'role_id.required_without' => 'Select a role to assign.',
            'user_id.required_without' => 'Select a user to assign the role to.',
        ];
    }
}
