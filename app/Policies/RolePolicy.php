<?php

namespace App\Policies;

use App\Enums\PermissionName;
use App\Enums\RoleName;
use App\Models\Role;
use App\Models\User;

/**
 * Role administration.
 *
 * Role editing is restricted to `role.manage` and the seeded Student role is
 * protected, since removing it would lock out the default student experience.
 */
class RolePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission(PermissionName::RoleManage->value);
    }

    public function view(User $user, Role $role): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->hasPermission(PermissionName::RoleManage->value);
    }

    public function update(User $user, Role $role): bool
    {
        return $user->hasPermission(PermissionName::RoleManage->value);
    }

    public function delete(User $user, Role $role): bool
    {
        return $user->hasPermission(PermissionName::RoleManage->value)
            && ! in_array($role->name, RoleName::values(), true);
    }
}
