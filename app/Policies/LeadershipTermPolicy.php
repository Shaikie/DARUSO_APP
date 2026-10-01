<?php

namespace App\Policies;

use App\Enums\PermissionName;
use App\Models\LeadershipTerm;
use App\Models\User;

class LeadershipTermPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isLeader();
    }

    public function view(User $user, LeadershipTerm $term): bool
    {
        return $user->isLeader();
    }

    public function create(User $user): bool
    {
        return $user->hasPermission(PermissionName::TermManage->value);
    }

    public function update(User $user, LeadershipTerm $term): bool
    {
        return $user->hasPermission(PermissionName::TermManage->value);
    }

    public function delete(User $user, LeadershipTerm $term): bool
    {
        // The active term is never deletable: representatives depend on it.
        return $user->hasPermission(PermissionName::TermManage->value) && ! $term->is_active;
    }

    /**
     * Activating a term implicitly deactivates the previous one.
     */
    public function activate(User $user, LeadershipTerm $term): bool
    {
        return $user->hasPermission(PermissionName::TermManage->value);
    }
}
