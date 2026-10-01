<?php

namespace App\Policies;

use App\Enums\PermissionName;
use App\Models\Event;
use App\Models\User;
use App\Services\AudienceResolver;

class EventPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Event $event): bool
    {
        if ($event->organizer_id === $user->getKey()) {
            return true;
        }

        if ($user->hasPermission(PermissionName::EventManage->value)) {
            return true;
        }

        return app(AudienceResolver::class)->includes($event->audienceRules()->get(), $user);
    }

    public function create(User $user): bool
    {
        return $user->hasPermission(PermissionName::EventCreate->value);
    }

    public function update(User $user, Event $event): bool
    {
        return $user->hasPermission(PermissionName::EventManage->value)
            || $event->organizer_id === $user->getKey()
                && $user->hasPermission(PermissionName::EventEdit->value);
    }

    public function delete(User $user, Event $event): bool
    {
        return $user->hasPermission(PermissionName::EventManage->value);
    }

    public function download(User $user, Event $event): bool
    {
        return $this->view($user, $event);
    }
}
