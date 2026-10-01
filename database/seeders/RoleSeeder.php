<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'announcement.create',
            'announcement.edit',
            'announcement.edit_any',
            'announcement.publish',
            'announcement.delete',
            'notification.create',
            'notification.send',
            'student.view',
            'student.view_sensitive',
            'student.view_hostel',
            'complaint.view',
            'complaint.assign',
            'complaint.update',
            'complaint.resolve',
            'meeting.create',
            'meeting.edit',
            'meeting.manage',
            'event.create',
            'event.edit',
            'event.manage',
            'document.create',
            'document.publish',
            'document.delete',
            'leader.manage',
            'committee.manage',
            'ministry.manage',
            'system.settings',
            'audit.view',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $roles = [
            'admin' => [
                'description' => 'System Administrator',
                'permissions' => $permissions,
            ],
            'secretary_general' => [
                'description' => 'Secretary General',
                'permissions' => [
                    'announcement.create',
                    'announcement.edit_any',
                    'announcement.publish',
                    'notification.create',
                    'notification.send',
                    'student.view',
                    'complaint.view',
                    'complaint.assign',
                    'meeting.create',
                    'event.create',
                    'document.create',
                    'document.publish',
                    'leader.manage',
                    'audit.view',
                ],
            ],
            'ministry_leader' => [
                'description' => 'Ministry Leader',
                'permissions' => [
                    'announcement.create',
                    'announcement.edit',
                    'announcement.publish',
                    'notification.create',
                    'notification.send',
                    'student.view',
                    'student.view_hostel',
                    'complaint.view',
                    'complaint.update',
                    'meeting.create',
                    'event.create',
                    'document.create',
                ],
            ],
            'committee_leader' => [
                'description' => 'Committee Leader',
                'permissions' => [
                    'announcement.create',
                    'announcement.edit',
                    'notification.create',
                    'meeting.create',
                    'event.create',
                    'document.create',
                ],
            ],
            'student' => [
                'description' => 'Student',
                'permissions' => [],
            ],
        ];

        foreach ($roles as $roleName => $roleData) {
            $role = Role::firstOrCreate(
                ['name' => $roleName],
                ['description' => $roleData['description']]
            );

            $permissionIds = Permission::whereIn('name', $roleData['permissions'])->pluck('id');
            $role->permissions()->sync($permissionIds);
        }
    }
}
