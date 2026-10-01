<?php

namespace App\Policies;

use App\Enums\DocumentVisibility;
use App\Enums\PermissionName;
use App\Models\Document;
use App\Models\User;

/**
 * Document visibility is enforced per record, not per page.
 *
 * Possession of a URL is never sufficient: `ministry` and `committee`
 * visibility additionally require membership of that specific ministry or
 * committee, so one leader cannot read another ministry's documents.
 */
class DocumentPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Document $document): bool
    {
        if ($user->hasPermission(PermissionName::DocumentPublish->value)
            && $document->uploader_id === $user->getKey()) {
            return true;
        }

        return match ($document->visibility) {
            DocumentVisibility::Public => true,
            DocumentVisibility::Students => $user->isStudent() || $user->isLeader(),
            DocumentVisibility::Leaders => $user->isLeader(),
            DocumentVisibility::Ministry => $document->ministry_id !== null
                && $user->ministryIds()->contains($document->ministry_id),
            DocumentVisibility::Committee => $document->committee_id !== null
                && $user->committeeIds()->contains($document->committee_id),
            DocumentVisibility::Group => $document->group_id !== null
                && (int) $document->group_id === $user->getKey(),
        };
    }

    /**
     * Viewing and downloading are the same decision.
     */
    public function download(User $user, Document $document): bool
    {
        return $this->view($user, $document);
    }

    public function create(User $user): bool
    {
        return $user->hasPermission(PermissionName::DocumentCreate->value);
    }

    public function update(User $user, Document $document): bool
    {
        return $user->hasPermission(PermissionName::DocumentPublish->value)
            && $document->uploader_id === $user->getKey();
    }

    public function delete(User $user, Document $document): bool
    {
        return $user->hasPermission(PermissionName::DocumentDelete->value);
    }
}
