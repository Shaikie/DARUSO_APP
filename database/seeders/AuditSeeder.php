<?php

namespace Database\Seeders;

use App\Enums\AuditAction;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Seeds a short audit history so the audit log page is not empty on a fresh
 * development install. Real audit entries are produced by AuditLogger at runtime.
 */
class AuditSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@daruso.local')->first();

        if ($admin === null) {
            return;
        }

        $entries = [
            [AuditAction::Created, 'System', 'Development environment seeded', 28],
            [AuditAction::SettingsUpdated, 'System', 'Default system settings applied', 27],
            [AuditAction::RoleAssigned, 'System', 'Leadership roles assigned to seeded accounts', 26],
            [AuditAction::Published, 'System', 'Initial announcements published', 20],
        ];

        foreach ($entries as [$action, $targetType, $targetId, $daysAgo]) {
            $exists = AuditLog::where('action', $action->value)
                ->where('created_at', '>=', now()->subDays($daysAgo + 1))
                ->where('created_at', '<=', now()->subDays($daysAgo - 1))
                ->exists();

            if ($exists) {
                continue;
            }

            AuditLog::create([
                'actor_id' => $admin->getKey(),
                'action' => $action->value,
                'target_type' => $targetType,
                'target_id' => crc32($targetType),
                'new_values' => ['description' => $targetId],
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Daruso seeder',
                'created_at' => now()->subDays($daysAgo),
            ]);
        }
    }
}
