<?php

namespace Database\Seeders;

use App\Enums\PermissionName;
use App\Enums\RoleName;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

/**
 * Seeds roles and attaches their permissions.
 *
 * Each role is only a bundle of permission rows: there is no bypass flag, so
 * revoking a permission from a role immediately narrows what its holders can do.
 */
class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $definitions = [
            RoleName::Administrator->value => [
                'description' => RoleName::Administrator->label(),
                'permissions' => PermissionName::values(),
            ],

            RoleName::SecretaryGeneral->value => [
                'description' => RoleName::SecretaryGeneral->label(),
                'permissions' => [
                    PermissionName::AnnouncementCreate,
                    PermissionName::AnnouncementEdit,
                    PermissionName::AnnouncementEditAny,
                    PermissionName::AnnouncementPublish,
                    PermissionName::AnnouncementDelete,
                    PermissionName::NotificationCreate,
                    PermissionName::NotificationSend,
                    PermissionName::StudentView,
                    PermissionName::StudentViewSensitive,
                    PermissionName::ComplaintView,
                    PermissionName::ComplaintAssign,
                    PermissionName::ComplaintUpdate,
                    PermissionName::ComplaintResolve,
                    PermissionName::MeetingCreate,
                    PermissionName::MeetingEdit,
                    PermissionName::MeetingManage,
                    PermissionName::EventCreate,
                    PermissionName::EventEdit,
                    PermissionName::EventManage,
                    PermissionName::DocumentCreate,
                    PermissionName::DocumentPublish,
                    PermissionName::DocumentDelete,
                    PermissionName::LeaderManage,
                    PermissionName::CommitteeManage,
                    PermissionName::MinistryManage,
                    PermissionName::TermManage,
                    PermissionName::PositionManage,
                    PermissionName::ReportView,
                    PermissionName::AuditView,
                ],
            ],

            RoleName::MinistryLeader->value => [
                'description' => RoleName::MinistryLeader->label(),
                'permissions' => [
                    PermissionName::AnnouncementCreate,
                    PermissionName::AnnouncementEdit,
                    PermissionName::AnnouncementPublish,
                    PermissionName::NotificationCreate,
                    PermissionName::NotificationSend,
                    PermissionName::StudentView,
                    PermissionName::StudentViewHostel,
                    PermissionName::ComplaintView,
                    PermissionName::ComplaintUpdate,
                    PermissionName::MeetingCreate,
                    PermissionName::MeetingEdit,
                    PermissionName::EventCreate,
                    PermissionName::EventEdit,
                    PermissionName::DocumentCreate,
                    PermissionName::DocumentPublish,
                    PermissionName::ReportView,
                ],
            ],

            RoleName::CommitteeLeader->value => [
                'description' => RoleName::CommitteeLeader->label(),
                'permissions' => [
                    PermissionName::AnnouncementCreate,
                    PermissionName::AnnouncementEdit,
                    PermissionName::NotificationCreate,
                    PermissionName::NotificationSend,
                    PermissionName::MeetingCreate,
                    PermissionName::MeetingEdit,
                    PermissionName::EventCreate,
                    PermissionName::EventEdit,
                    PermissionName::DocumentCreate,
                    PermissionName::DocumentPublish,
                    PermissionName::ReportView,
                ],
            ],

            RoleName::Student->value => [
                'description' => RoleName::Student->label(),
                'permissions' => [],
            ],
        ];

        foreach ($definitions as $name => $definition) {
            $role = Role::updateOrCreate(
                ['name' => $name],
                ['description' => $definition['description']],
            );

            $permissionIds = Permission::whereIn(
                'name',
                array_map(
                    static fn (PermissionName|string $permission): string => $permission instanceof PermissionName
                        ? $permission->value
                        : $permission,
                    $definition['permissions'],
                ),
            )->pluck('id');

            $role->permissions()->sync($permissionIds);
        }
    }
}
