<?php

namespace App\Policies;

use App\Enums\PermissionName;
use App\Models\Notification;
use App\Models\User;

/**
 * Notifications are private to their recipient.
 *
 * Senders may see what they sent (auditable), but only the recipient can read or
 * mark a notification, so a guessed id cannot expose private correspondence.
 */
class NotificationPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Notification $notification): bool
    {
        return $notification->recipient_id === $user->getKey()
            || $notification->sender_id === $user->getKey();
    }

    /**
     * Only the recipient may change read state.
     */
    public function markAsRead(User $user, Notification $notification): bool
    {
        return $notification->recipient_id === $user->getKey();
    }

    public function create(User $user): bool
    {
        return $user->hasPermission(PermissionName::NotificationCreate->value);
    }

    public function delete(User $user, Notification $notification): bool
    {
        return false;
    }
}
