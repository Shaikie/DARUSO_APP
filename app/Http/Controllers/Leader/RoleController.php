<?php

namespace App\Http\Controllers\Leader;

use App\Enums\RoleName;
use App\Http\Controllers\Controller;
use App\Http\Requests\AssignRoleRequest;
use App\Http\Requests\StoreRoleRequest;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Role and permission administration.
 *
 * Roles are only bundles of permissions, so editing a role here is the supported
 * way to change what a group of users can do. Privilege escalation is prevented
 * in AssignRoleRequest, which requires the actor to already hold every
 * permission the role confers.
 */
class RoleController extends Controller
{
    public function __construct(
        private readonly AuditLogger $audit,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Role::class);

        $roles = Role::query()
            ->withCount(['users', 'permissions'])
            ->with('permissions')
            ->orderBy('name')
            ->paginate(20);

        return view('leader.roles.index', [
            'roles' => $roles,
            'permissions' => Permission::orderBy('name')->get()->groupBy(
                fn (Permission $permission): string => explode('.', $permission->name)[0]
            ),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Role::class);

        return view('leader.roles.create', [
            'permissions' => Permission::orderBy('name')->get(),
        ]);
    }

    public function store(StoreRoleRequest $request): RedirectResponse
    {
        $this->authorize('create', Role::class);

        $role = Role::create($request->safe()->only(['name', 'description']));
        $role->permissions()->sync($request->permissionIds());

        $this->audit->log('created', $role, null, [
            'name' => $role->name,
            'permissions' => $request->permissionIds(),
        ], $request);

        return redirect()
            ->route('leader.roles.index')
            ->with('success', 'Role created.');
    }

    public function show(Role $role): View
    {
        $this->authorize('view', $role);

        return view('leader.roles.show', [
            'role' => $role->load('permissions'),
            'users' => $role->users()->with('studentProfile')->paginate(20),
            'candidates' => User::query()->orderBy('name')->limit(500)->get(),
        ]);
    }

    /**
     * Assign this role to a user from the role page.
     */
    public function addUser(AssignRoleRequest $request, Role $role): RedirectResponse
    {
        $this->authorize('update', $role);

        $user = User::findOrFail($request->integer('user_id'));

        $this->authorize('update', $user);

        if (! $request->canGrant()) {
            return back()->with('error', 'You cannot grant a role that holds permissions you do not have.');
        }

        if ($user->hasRole($role->name)) {
            return back()->with('error', "{$user->name} already holds the {$role->name} role.");
        }

        $user->roles()->syncWithoutDetaching([$role->getKey()]);

        $this->audit->log('role_assigned', $user, null, [
            'role' => $role->name,
            'user_id' => $user->getKey(),
        ], $request);

        return back()->with('success', "Role '{$role->name}' assigned to {$user->name}.");
    }

    public function edit(Role $role): View
    {
        $this->authorize('update', $role);

        return view('leader.roles.edit', [
            'role' => $role->load('permissions'),
            'permissions' => Permission::orderBy('name')->get(),
            'selected' => $role->permissions->pluck('id')->all(),
        ]);
    }

    public function update(StoreRoleRequest $request, Role $role): RedirectResponse
    {
        $this->authorize('update', $role);

        $old = ['permissions' => $role->permissions()->pluck('name')->all()];

        $role->update($request->safe()->only(['name', 'description']));
        $role->permissions()->sync($request->permissionIds());

        $this->audit->log('updated', $role, $old, [
            'name' => $role->name,
            'permissions' => $request->permissionIds(),
        ], $request);

        return redirect()
            ->route('leader.roles.index')
            ->with('success', 'Role updated.');
    }

    public function destroy(Role $role): RedirectResponse
    {
        $this->authorize('delete', $role);

        $this->audit->log('deleted', $role, ['name' => $role->name], null, request());

        $role->delete();

        return redirect()
            ->route('leader.roles.index')
            ->with('success', 'Role deleted.');
    }

    /**
     * Assign a role to a user.
     */
    public function assign(AssignRoleRequest $request, User $user): RedirectResponse
    {
        $this->authorize('update', $user);

        if (! $request->canGrant()) {
            return back()->with('error', 'You cannot grant a role that holds permissions you do not have.');
        }

        $role = Role::findOrFail($request->integer('role_id'));

        if ($user->hasRole($role->name)) {
            return back()->with('error', "{$user->name} already holds the {$role->name} role.");
        }

        $user->roles()->syncWithoutDetaching([$role->getKey()]);

        $this->audit->log('role_assigned', $user, null, [
            'role' => $role->name,
            'user_id' => $user->getKey(),
        ], $request);

        return back()->with('success', "Role '{$role->name}' assigned to {$user->name}.");
    }

    public function revoke(AssignRoleRequest $request, User $user, Role $role): RedirectResponse
    {
        $this->authorize('update', $user);

        if (! $user->hasRole($role->name)) {
            return back()->with('error', 'That user does not hold the selected role.');
        }

        // Guard against removing the last administrator and locking everyone out.
        if ($role->isRole(RoleName::Administrator)
            && $role->users()->count() <= 1) {
            return back()->with('error', 'At least one system administrator must remain.');
        }

        $user->roles()->detach($role->getKey());

        $this->audit->log('role_removed', $user, ['role' => $role->name], null, $request);

        return back()->with('success', "Role '{$role->name}' removed from {$user->name}.");
    }
}
