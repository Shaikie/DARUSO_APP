<?php

namespace App\Policies;

use App\Enums\PermissionName;
use App\Models\Position;
use App\Models\User;

class PositionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isLeader();
    }

    public function view(User $user, Position $position): bool
    {
        return $user->isLeader();
    }

    public function create(User $user): bool
    {
        return $user->hasPermission(PermissionName::PositionManage->value);
    }

    public function update(User $user, Position $position): bool
    {
        return $user->hasPermission(PermissionName::PositionManage->value);
    }

    public function delete(User $user, Position $position): bool
    {
        return $user->hasPermission(PermissionName::PositionManage->value);
    }
}
