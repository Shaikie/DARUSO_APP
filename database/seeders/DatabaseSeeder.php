<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Full development dataset.
 *
 * Ordering reflects real dependencies: authorization vocabulary, organisation
 * structure, accounts, leadership assignments, then content that references them.
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            PermissionSeeder::class,
            RoleSeeder::class,
            OrganizationSeeder::class,
            UserSeeder::class,
            LeadershipSeeder::class,
            SettingSeeder::class,
            CommunicationSeeder::class,
            ComplaintSeeder::class,
            DocumentSeeder::class,
            NotificationSeeder::class,
            AuditSeeder::class,
        ]);
    }
}
