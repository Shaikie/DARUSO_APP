<?php

namespace App\Policies;

use App\Enums\PermissionName;
use App\Models\Setting;
use App\Models\User;

class SettingPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission(PermissionName::SystemSettings->value);
    }

    public function view(User $user, Setting $setting): bool
    {
        return $this->viewAny($user);
    }

    public function update(User $user, Setting $setting): bool
    {
        return $user->hasPermission(PermissionName::SystemSettings->value);
    }

    public function delete(User $user, Setting $setting): bool
    {
        return false;
    }
}
