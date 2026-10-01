<?php

namespace App\Policies;

use App\Enums\PermissionName;
use App\Models\Complaint;
use App\Models\User;

/**
 * Complaint privacy.
 *
 * A complaint is readable by its creator and by leaders explicitly permitted to
 * act on it. A student never gains access by changing an id in the URL, because
 * the ownership check runs here on every show/update request.
 */
class ComplaintPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Complaint $complaint): bool
    {
        if ($complaint->creator_id === $user->getKey()) {
            return true;
        }

        if (! $user->hasPermission(PermissionName::ComplaintView->value)) {
            return false;
        }

        // Holders of `complaint.assign` have organisation-wide remit: they route
        // work, so they must be able to see any complaint, including one already
        // assigned to a ministry they do not personally belong to.
        if ($user->hasPermission(PermissionName::ComplaintAssign->value)) {
            return true;
        }

        // Otherwise a leader only sees what is assigned to them or to their
        // own ministry.
        if ($complaint->assigned_leader_id === $user->getKey()) {
            return true;
        }

        return $complaint->assigned_ministry_id !== null
            && $user->ministryIds()->contains($complaint->assigned_ministry_id);
    }

    public function create(User $user): bool
    {
        return $user->isStudent();
    }

    /**
     * Only the creator may edit the content of their own complaint.
     */
    public function update(User $user, Complaint $complaint): bool
    {
        return $complaint->creator_id === $user->getKey();
    }

    /**
     * A student may withdraw their own complaint, but only while it is still
     * open: once a leader has started work on it, only they may close it.
     */
    public function delete(User $user, Complaint $complaint): bool
    {
        if ($user->hasPermission(PermissionName::ComplaintUpdate->value)
            && $this->view($user, $complaint)) {
            return true;
        }

        return $complaint->creator_id === $user->getKey() && ! $complaint->status->isTerminal();
    }

    /**
     * Leaders change status; the workflow service validates the transition.
     */
    public function transition(User $user, Complaint $complaint): bool
    {
        if (! $this->view($user, $complaint)) {
            return false;
        }

        return $user->hasPermission(PermissionName::ComplaintUpdate->value);
    }

    public function assign(User $user, Complaint $complaint): bool
    {
        return $user->hasPermission(PermissionName::ComplaintAssign->value)
            && $this->view($user, $complaint);
    }

    public function resolve(User $user, Complaint $complaint): bool
    {
        return $user->hasPermission(PermissionName::ComplaintResolve->value)
            && $this->view($user, $complaint);
    }

    public function forward(User $user, Complaint $complaint): bool
    {
        return $this->assign($user, $complaint);
    }
}
