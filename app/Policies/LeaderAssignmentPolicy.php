<?php

namespace App\Policies;

use App\Enums\PermissionName;
use App\Models\LeaderAssignment;
use App\Models\User;

class LeaderAssignmentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isLeader();
    }

    public function view(User $user, LeaderAssignment $assignment): bool
    {
        return $user->isLeader();
    }

    public function create(User $user): bool
    {
        return $user->hasPermission(PermissionName::LeaderManage->value);
    }

    public function update(User $user, LeaderAssignment $assignment): bool
    {
        return $user->hasPermission(PermissionName::LeaderManage->value);
    }

    public function delete(User $user, LeaderAssignment $assignment): bool
    {
        return $user->hasPermission(PermissionName::LeaderManage->value);
    }
}
