<?php

namespace App\Policies;

use App\Enums\PermissionName;
use App\Models\AuditLog;
use App\Models\User;

/**
 * Audit logs are read-only and permission-gated.
 *
 * No ability ever returns true for mutation: the trail cannot be rewritten or
 * removed from application code.
 */
class AuditLogPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission(PermissionName::AuditView->value);
    }

    public function view(User $user, AuditLog $auditLog): bool
    {
        return $user->hasPermission(PermissionName::AuditView->value);
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, AuditLog $auditLog): bool
    {
        return false;
    }

    public function delete(User $user, AuditLog $auditLog): bool
    {
        return false;
    }
}
