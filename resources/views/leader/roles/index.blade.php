<x-app-layout>
    @section('title', 'Roles and permissions')
    @section('heading', 'Roles and permissions')
    @section('subheading', 'Roles are bundles of permissions. There is no bypass role.')

    @section('actions')
        @can('create', App\Models\Role::class)
            <a href="{{ route('leader.roles.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i>New role
            </a>
        @endcan
    @endsection

    <div class="alert alert-info d-flex gap-2" role="alert">
        <i class="bi bi-info-circle-fill mt-1"></i>
        <div class="small">
            Authority comes only from permission rows attached to roles. Revoking a permission
            from a role immediately narrows what every holder of that role can do, and the
            change is recorded in the audit log.
        </div>
    </div>

    <x-page-card icon="shield-lock" title="Roles">
        @if ($roles->isEmpty())
            <x-empty-state icon="shield-lock" title="No roles defined"
                           description="Create a role and attach the permissions it grants." />
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">Role</th>
                            <th scope="col">Users</th>
                            <th scope="col">Permissions</th>
                            <th scope="col" class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($roles as $role)
                            <tr>
                                <td>
                                    <span class="fw-semibold">{{ $role->name }}</span>
                                    <div class="small text-muted">{{ $role->description }}</div>
                                </td>
                                <td class="small">{{ $role->users_count }}</td>
                                <td class="small">{{ $role->permissions_count }}</td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        @can('update', $role)
                                            <a href="{{ route('leader.roles.edit', $role) }}"
                                               class="btn btn-outline-primary" title="Edit permissions">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                        @endcan

                                        @can('delete', $role)
                                            <form method="POST" action="{{ route('leader.roles.destroy', $role) }}"
                                                  onsubmit="return confirm('Delete this role?');">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-outline-danger" title="Delete">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($roles->hasPages())
                <div class="mt-3">{{ $roles->links() }}</div>
            @endif
        @endif
    </x-page-card>

    <x-page-card class="mt-4" icon="key" title="Available permissions">
        @foreach ($permissions as $group => $groupPermissions)
            <h3 class="h6 fw-semibold text-capitalize mb-2">{{ str_replace('_', ' ', $group) }}</h3>
            <div class="d-flex flex-wrap gap-1 mb-3">
                @foreach ($groupPermissions as $permission)
                    <span class="badge text-bg-light border" title="{{ $permission->description }}">
                        {{ $permission->name }}
                    </span>
                @endforeach
            </div>
        @endforeach
    </x-page-card>
</x-app-layout>