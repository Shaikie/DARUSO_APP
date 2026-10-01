<?php

namespace Tests\Feature;

use App\Enums\AuditAction;
use App\Enums\PermissionName;
use App\Enums\RoleName;
use App\Models\AuditLog;
use App\Models\Ministry;
use App\Models\Permission;
use App\Models\Role;
use App\Services\AuditLogger;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class AuditTrailTest extends TestCase
{
    use RefreshDatabase;

    public function test_sensitive_actions_are_recorded(): void
    {
        $leader = $this->userWithRole('organiser', [
            PermissionName::MinistryManage->value,
        ]);

        $this->actingAs($leader)
            ->post(route('leader.ministries.store'), [
                'name' => 'Infrastructure Ministry',
                'description' => 'Facilities and maintenance.',
            ]);

        $ministry = Ministry::firstWhere('name', 'Infrastructure Ministry');

        $log = AuditLog::where('target_id', $ministry->getKey())->firstOrFail();

        $this->assertSame(AuditAction::Created->value, $log->action);
        $this->assertSame(Ministry::class, $log->target_type);
        $this->assertSame($leader->getKey(), $log->actor_id);
        $this->assertNotNull($log->created_at);
    }

    public function test_audit_records_capture_the_request_context(): void
    {
        $leader = $this->userWithRole('organiser', [
            PermissionName::MinistryManage->value,
        ]);

        $this->actingAs($leader)
            ->withServerVariables(['REMOTE_ADDR' => '203.0.113.10'])
            ->post(route('leader.ministries.store'), [
                'name' => 'Publicity Ministry',
                'description' => 'Communication.',
            ]);

        $log = AuditLog::latest('id')->firstOrFail();

        $this->assertSame('203.0.113.10', $log->ip_address);
        $this->assertNotNull($log->user_agent);
    }

    public function test_old_and_new_values_are_recorded_for_updates(): void
    {
        $leader = $this->userWithRole('organiser', [
            PermissionName::MinistryManage->value,
        ]);

        $ministry = Ministry::create(['name' => 'Old Name', 'description' => 'Original']);

        $this->actingAs($leader)
            ->put(route('leader.ministries.update', $ministry), [
                'name' => 'New Name',
                'description' => 'Revised',
            ]);

        $log = AuditLog::where('action', AuditAction::Updated->value)
            ->where('target_id', $ministry->getKey())
            ->firstOrFail();

        $this->assertSame('Old Name', $log->old_values['name']);
        $this->assertSame('New Name', $log->new_values['name']);
    }

    public function test_role_changes_are_audited(): void
    {
        $admin = $this->administrator();

        $target = $this->student();
        $role = Role::firstOrCreate(['name' => RoleName::MinistryLeader->value]);

        $this->actingAs($admin)
            ->post(route('leader.roles.assign', $target), ['role_id' => $role->getKey()])
            ->assertRedirect();

        $this->assertDatabaseHas('audit_logs', [
            'action' => AuditAction::RoleAssigned->value,
            'target_id' => $target->getKey(),
            'actor_id' => $admin->getKey(),
        ]);
    }

    public function test_a_grantor_cannot_assign_a_role_that_exceeds_their_own_permissions(): void
    {
        // A leader who holds role.manage but not system.settings.
        $grantor = $this->userWithRole('limited_grantor', [
            PermissionName::RoleManage->value,
        ]);

        $target = $this->student();

        $adminRole = Role::firstOrCreate(['name' => RoleName::Administrator->value]);
        foreach (PermissionName::cases() as $permission) {
            Permission::firstOrCreate(['name' => $permission->value]);
        }
        $adminRole->permissions()->sync(Permission::pluck('id'));

        $this->actingAs($grantor)
            ->post(route('leader.roles.assign', $target), ['role_id' => $adminRole->getKey()])
            ->assertRedirect();

        $this->assertFalse($target->fresh()->hasRole(RoleName::Administrator->value));
    }

    public function test_audit_logs_are_not_reachable_without_permission(): void
    {
        $leader = $this->userWithRole('organiser', [
            PermissionName::MinistryManage->value,
        ]);

        AuditLog::create(['action' => 'created', 'created_at' => now()]);

        $this->actingAs($leader)
            ->get(route('leader.audit-logs.index'))
            ->assertForbidden();
    }

    public function test_an_authorised_user_can_read_the_audit_log(): void
    {
        $admin = $this->administrator();

        AuditLog::create([
            'action' => 'published',
            'target_type' => 'Announcement',
            'target_id' => 1,
            'created_at' => now(),
        ]);

        $this->actingAs($admin)
            ->get(route('leader.audit-logs.index'))
            ->assertOk()
            ->assertSee('published');
    }

    public function test_there_is_no_route_to_modify_or_delete_an_audit_log(): void
    {
        foreach (['update', 'destroy'] as $method) {
            $this->assertFalse(
                Route::has("leader.audit-logs.{$method}"),
                "leader.audit-logs.{$method} must not be routable."
            );
        }
    }

    public function test_the_audit_logger_never_breaks_the_caller(): void
    {
        $logger = app(AuditLogger::class);

        // A target id that does not exist must not throw: audit logging is
        // best-effort and must never roll back the action that triggered it.
        $logger->log('created', new AuditLog(['action' => 'x']), null, null);

        $this->assertDatabaseCount('audit_logs', 1);
    }

    public function test_audit_entries_are_filterable(): void
    {
        $admin = $this->administrator();

        $published = AuditLog::create([
            'action' => 'published',
            'target_type' => 'Announcement',
            'target_id' => 7,
            'created_at' => now(),
        ]);

        $deleted = AuditLog::create([
            'action' => 'deleted',
            'target_type' => 'Announcement',
            'target_id' => 9,
            'created_at' => now(),
        ]);

        $response = $this->actingAs($admin)
            ->get(route('leader.audit-logs.index', ['action' => 'published']));

        $response->assertOk();

        // Filter by target id, which appears only in the table body, so the
        // assertion is not satisfied by the filter dropdown.
        $response->assertSee('#'.$published->target_id);
        $response->assertDontSee('#'.$deleted->target_id);
    }
}
