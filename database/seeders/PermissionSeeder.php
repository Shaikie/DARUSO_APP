<?php

namespace Database\Seeders;

use App\Enums\PermissionName;
use App\Models\Permission;
use Illuminate\Database\Seeder;

/**
 * Seeds the permission vocabulary.
 *
 * The enum is the code-side contract; this seeder mirrors it into the database
 * so policies and Gates can resolve names at runtime.
 */
class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        foreach (PermissionName::cases() as $permission) {
            Permission::updateOrCreate(
                ['name' => $permission->value],
                ['description' => $permission->label()],
            );
        }
    }
}
