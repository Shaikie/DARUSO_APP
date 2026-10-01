<x-app-layout>
    @section('title', 'Role: '.$role->name)
    @section('heading', $role->name)
    @section('subheading', $role->description)

    @section('actions')
        <a href="{{ route('leader.roles.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Back
        </a>
        @can('update', $role)
            <a href="{{ route('leader.roles.edit', $role) }}" class="btn btn-primary">
                <i class="bi bi-pencil me-1"></i>Edit permissions
            </a>
        @endcan
    @endsection

    <div class="row g-4">
        <div class="col-12 col-lg-7">
            <x-page-card icon="people" title="Users holding this role">
                @if ($users->isEmpty())
                    <x-empty-state icon="person-plus" title="Nobody holds this role"
                                   description="Assign the role from a user's record." />
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col">User</th>
                                    <th scope="col">Type</th>
                                    <th scope="col" class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($users as $user)
                                    <tr>
                                        <td>{{ $user->name }}</td>
                                        <td class="small text-muted">
                                            {{ $user->studentProfile ? 'Student' : 'Leader' }}
                                        </td>
                                        <td class="text-end">
                                            @can('update', $user)
                                                <form method="POST"
                                                      action="{{ route('leader.roles.revoke', [$user, $role]) }}"
                                                      onsubmit="return confirm('Remove this role from {{ $user->name }}?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="btn btn-sm btn-outline-danger">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            @endcan
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if ($users->hasPages())
                        <div class="mt-3">{{ $users->links() }}</div>
                    @endif
                @endif
            </x-page-card>

            @can('update', $role)
                <x-page-card class="mt-4" icon="person-plus" title="Assign this role">
                    <form method="POST" action="{{ route('leader.roles.users.store', $role) }}">
                        @csrf

                        <div class="mb-3">
                            <label for="user_id" class="form-label small fw-semibold">User</label>
                            <select id="user_id" name="user_id" required
                                    class="form-select @error('user_id') is-invalid @enderror">
                                <option value="">Choose a person…</option>
                                @foreach ($candidates as $candidate)
                                    <option value="{{ $candidate->id }}" @selected(old('user_id') == $candidate->id)>
                                        {{ $candidate->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('user_id') <div class="invalid-feedback">{{ $message }}</div> @enderror>
                        </div>

                        <button class="btn btn-primary btn-sm w-100">
                            <i class="bi bi-plus-lg me-1"></i>Assign role
                        </button>

                        <p class="form-text mt-2 mb-0">
                            You can only grant roles whose permissions you already hold.
                        </p>
                    </form>
                </x-page-card>
            @endcan
        </div>

        <div class="col-12 col-lg-5">
            <x-page-card icon="key" title="Granted permissions">
                @forelse ($role->permissions as $permission)
                    <div class="border-bottom py-1 small">{{ $permission->name }}</div>
                @empty
                    <p class="text-muted small mb-0">
                        This role grants nothing. Holders may sign in but cannot act.
                    </p>
                @endforelse
            </x-page-card>
        </div>
    </div>
</x-app-layout>