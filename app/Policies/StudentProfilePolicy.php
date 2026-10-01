<?php

namespace App\Policies;

use App\Enums\PermissionName;
use App\Models\StudentProfile;
use App\Models\User;

/**
 * Student data is permission-aware field by field.
 *
 * Access to a student record does not imply access to sensitive fields: the
 * view template asks this policy per field group rather than hiding values with
 * CSS, and hostel data requires its own permission.
 */
class StudentProfilePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission(PermissionName::StudentView->value);
    }

    public function view(User $user, StudentProfile $profile): bool
    {
        return $profile->user_id === $user->getKey()
            || $user->hasPermission(PermissionName::StudentView->value);
    }

    /**
     * Registration number, contact details and full academic record.
     */
    public function viewSensitive(User $user, StudentProfile $profile): bool
    {
        return $profile->user_id === $user->getKey()
            || $user->hasPermission(PermissionName::StudentViewSensitive->value);
    }

    /**
     * Hostel allocation is sensitive and gated separately.
     */
    public function viewHostel(User $user, StudentProfile $profile): bool
    {
        return $profile->user_id === $user->getKey()
            || $user->hasPermission(PermissionName::StudentViewHostel->value)
            || $user->hasPermission(PermissionName::StudentViewSensitive->value);
    }

    public function create(User $user): bool
    {
        return $user->hasPermission(PermissionName::StudentViewSensitive->value);
    }

    public function update(User $user, StudentProfile $profile): bool
    {
        return $profile->user_id === $user->getKey()
            || $user->hasPermission(PermissionName::StudentViewSensitive->value);
    }

    public function delete(User $user, StudentProfile $profile): bool
    {
        return $user->hasPermission(PermissionName::StudentViewSensitive->value);
    }
}
