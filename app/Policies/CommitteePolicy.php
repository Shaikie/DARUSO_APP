<?php

namespace App\Policies;

use App\Enums\PermissionName;
use App\Models\Committee;
use App\Models\User;

class CommitteePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isLeader();
    }

    public function view(User $user, Committee $committee): bool
    {
        return $user->isLeader();
    }

    public function create(User $user): bool
    {
        return $user->hasPermission(PermissionName::CommitteeManage->value);
    }

    public function update(User $user, Committee $committee): bool
    {
        return $user->hasPermission(PermissionName::CommitteeManage->value);
    }

    public function delete(User $user, Committee $committee): bool
    {
        return $user->hasPermission(PermissionName::CommitteeManage->value);
    }

    /**
     * Adding or removing members requires the same committee management right.
     */
    public function manageMembers(User $user, Committee $committee): bool
    {
        return $user->hasPermission(PermissionName::CommitteeManage->value);
    }
}
