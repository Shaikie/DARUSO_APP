<?php

namespace App\Policies;

use App\Enums\PermissionName;
use App\Models\User;

/**
 * User administration is separate from student-data access.
 */
class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission(PermissionName::UserManage->value);
    }

    public function view(User $user, User $model): bool
    {
        return $model->is($user) || $this->viewAny($user);
    }

    /**
     * Changing a user's account or roles.
     *
     * A grantor must additionally hold `role.manage`, which is checked in
     * RoleController alongside the escalation guard in AssignRoleRequest.
     */
    public function update(User $user, User $model): bool
    {
        return $user->hasPermission(PermissionName::UserManage->value)
            || $user->hasPermission(PermissionName::RoleManage->value);
    }

    public function delete(User $user, User $model): bool
    {
        return $user->hasPermission(PermissionName::UserManage->value) && ! $model->is($user);
    }
}
