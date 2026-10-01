<?php

namespace App\Policies;

use App\Enums\PermissionName;
use App\Models\Ministry;
use App\Models\User;

class MinistryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isLeader();
    }

    public function view(User $user, Ministry $ministry): bool
    {
        return $user->isLeader();
    }

    public function create(User $user): bool
    {
        return $user->hasPermission(PermissionName::MinistryManage->value);
    }

    public function update(User $user, Ministry $ministry): bool
    {
        return $user->hasPermission(PermissionName::MinistryManage->value);
    }

    public function delete(User $user, Ministry $ministry): bool
    {
        return $user->hasPermission(PermissionName::MinistryManage->value);
    }
}
