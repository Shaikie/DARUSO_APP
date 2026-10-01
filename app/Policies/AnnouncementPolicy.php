<?php

namespace App\Policies;

use App\Enums\AnnouncementStatus;
use App\Enums\PermissionName;
use App\Models\Announcement;
use App\Models\User;
use App\Services\AudienceResolver;

/**
 * Announcement authorization.
 *
 * A user may read an announcement only if its audience resolves to them. That
 * check lives in the policy (not the view) so a guessed URL is rejected.
 */
class AnnouncementPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission(PermissionName::AnnouncementCreate->value)
            || $user->isLeader();
    }

    public function view(User $user, Announcement $announcement): bool
    {
        if ($announcement->author_id === $user->getKey()) {
            return true;
        }

        if ($user->hasPermission(PermissionName::AnnouncementPublish->value)) {
            return true;
        }

        return app(AudienceResolver::class)
            ->includes($announcement->audienceRules()->get(), $user);
    }

    public function create(User $user): bool
    {
        return $user->hasPermission(PermissionName::AnnouncementCreate->value);
    }

    /**
     * Authors may edit their own drafts; `announcement.edit_any` covers the rest.
     */
    public function update(User $user, Announcement $announcement): bool
    {
        if ($announcement->status === AnnouncementStatus::Published) {
            return $user->hasPermission(PermissionName::AnnouncementEditAny->value);
        }

        return $announcement->author_id === $user->getKey()
                && $user->hasPermission(PermissionName::AnnouncementEdit->value)
            || $user->hasPermission(PermissionName::AnnouncementEditAny->value);
    }

    public function delete(User $user, Announcement $announcement): bool
    {
        return $user->hasPermission(PermissionName::AnnouncementDelete->value)
            || $announcement->author_id === $user->getKey()
                && $user->hasPermission(PermissionName::AnnouncementEdit->value);
    }

    /**
     * Publishing requires an explicit permission, never authorship alone.
     *
     * The announcement is optional so the check can also be used as a general
     * capability test (`@can('publish', Announcement::class)`) on the create
     * form, where no record exists yet.
     */
    public function publish(User $user, ?Announcement $announcement = null): bool
    {
        return $user->hasPermission(PermissionName::AnnouncementPublish->value);
    }

    public function approve(User $user, ?Announcement $announcement = null): bool
    {
        return $user->hasPermission(PermissionName::AnnouncementPublish->value)
            && ($announcement === null || $announcement->author_id !== $user->getKey());
    }

    public function archive(User $user, ?Announcement $announcement = null): bool
    {
        return $user->hasPermission(PermissionName::AnnouncementPublish->value);
    }

    public function submitForReview(User $user, Announcement $announcement): bool
    {
        return $announcement->author_id === $user->getKey()
            && $announcement->status === AnnouncementStatus::Draft;
    }
}
