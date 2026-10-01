<?php

namespace App\Policies;

use App\Enums\PermissionName;
use App\Models\Meeting;
use App\Models\User;
use App\Services\AudienceResolver;

class MeetingPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Meetings are visible to their audience and to meeting managers.
     */
    public function view(User $user, Meeting $meeting): bool
    {
        if ($meeting->organizer_id === $user->getKey()) {
            return true;
        }

        if ($user->hasPermission(PermissionName::MeetingManage->value)) {
            return true;
        }

        return app(AudienceResolver::class)->includes($meeting->audienceRules()->get(), $user);
    }

    public function create(User $user): bool
    {
        return $user->hasPermission(PermissionName::MeetingCreate->value);
    }

    public function update(User $user, Meeting $meeting): bool
    {
        return $user->hasPermission(PermissionName::MeetingManage->value)
            || $meeting->organizer_id === $user->getKey()
                && $user->hasPermission(PermissionName::MeetingEdit->value);
    }

    public function delete(User $user, Meeting $meeting): bool
    {
        return $user->hasPermission(PermissionName::MeetingManage->value);
    }

    public function download(User $user, Meeting $meeting): bool
    {
        return $this->view($user, $meeting);
    }
}
