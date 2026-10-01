<?php

namespace Tests;

use App\Enums\PermissionName;
use App\Enums\RoleName;
use App\Models\Permission;
use App\Models\Role;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Create a user holding the given role, with its full permission set.
     *
     * Tests seed roles on demand so they do not depend on the development
     * seeders having been run.
     *
     * @param  array<int, string>  $permissions  Extra permissions for the role.
     */
    protected function userWithRole(RoleName|string $role, array $permissions = [], array $attributes = []): User
    {
        $name = $role instanceof RoleName ? $role->value : $role;

        $permissions = array_values(array_unique(array_merge(
            $this->defaultPermissionsFor($role),
            $permissions,
        )));

        $model = Role::firstOrCreate(['name' => $name], ['description' => ucfirst($name)]);

        // Ensure the permission rows exist so tests never depend on the
        // development seeders having run.
        $permissionIds = collect($permissions)->map(function (string $permission): int {
            return Permission::firstOrCreate(
                ['name' => $permission],
                ['description' => $permission],
            )->getKey();
        });

        $model->permissions()->syncWithoutDetaching($permissionIds->all());

        $user = User::factory()->create($attributes);
        $user->roles()->attach($model);

        // Fresh permission state after attaching the role.
        $user->unsetRelation('permissions')->unsetRelation('roles');

        return $user->fresh();
    }

    /**
     * Create a student user with a student profile.
     */
    protected function student(array $profileAttributes = [], array $userAttributes = []): User
    {
        $user = $this->userWithRole(RoleName::Student, [], $userAttributes);

        StudentProfile::factory()->create([
            'user_id' => $user->getKey(),
        ] + $profileAttributes);

        return $user->fresh();
    }

    /**
     * Create a user holding every permission, for tests that assert an action is
     * reachable as well as asserting that it is not reachable for others.
     */
    protected function administrator(array $attributes = []): User
    {
        return $this->userWithRole(RoleName::Administrator, PermissionName::values(), $attributes);
    }

    /**
     * A representative permission set per role, mirroring RoleSeeder.
     *
     * @return array<int, string>
     */
    protected function defaultPermissionsFor(RoleName|string $role): array
    {
        $enum = $role instanceof RoleName ? $role : RoleName::tryFrom($role);

        return match ($enum) {
            RoleName::Administrator => PermissionName::values(),

            RoleName::SecretaryGeneral => [
                PermissionName::AnnouncementCreate->value,
                PermissionName::AnnouncementEdit->value,
                PermissionName::AnnouncementEditAny->value,
                PermissionName::AnnouncementPublish->value,
                PermissionName::AnnouncementDelete->value,
                PermissionName::NotificationCreate->value,
                PermissionName::NotificationSend->value,
                PermissionName::StudentView->value,
                PermissionName::StudentViewSensitive->value,
                PermissionName::ComplaintView->value,
                PermissionName::ComplaintAssign->value,
                PermissionName::ComplaintUpdate->value,
                PermissionName::ComplaintResolve->value,
                PermissionName::MeetingCreate->value,
                PermissionName::MeetingEdit->value,
                PermissionName::MeetingManage->value,
                PermissionName::EventCreate->value,
                PermissionName::EventEdit->value,
                PermissionName::EventManage->value,
                PermissionName::DocumentCreate->value,
                PermissionName::DocumentPublish->value,
                PermissionName::DocumentDelete->value,
                PermissionName::LeaderManage->value,
                PermissionName::MinistryManage->value,
                PermissionName::CommitteeManage->value,
                PermissionName::TermManage->value,
                PermissionName::PositionManage->value,
                PermissionName::ReportView->value,
                PermissionName::AuditView->value,
            ],

            RoleName::MinistryLeader => [
                PermissionName::AnnouncementCreate->value,
                PermissionName::AnnouncementEdit->value,
                PermissionName::AnnouncementPublish->value,
                PermissionName::NotificationCreate->value,
                PermissionName::NotificationSend->value,
                PermissionName::StudentView->value,
                PermissionName::StudentViewHostel->value,
                PermissionName::ComplaintView->value,
                PermissionName::ComplaintUpdate->value,
                PermissionName::MeetingCreate->value,
                PermissionName::EventCreate->value,
                PermissionName::DocumentCreate->value,
                PermissionName::DocumentPublish->value,
            ],

            RoleName::CommitteeLeader => [
                PermissionName::AnnouncementCreate->value,
                PermissionName::NotificationCreate->value,
                PermissionName::MeetingCreate->value,
                PermissionName::EventCreate->value,
                PermissionName::DocumentCreate->value,
            ],

            default => [],
        };
    }
}
