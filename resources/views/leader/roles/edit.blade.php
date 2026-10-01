<x-app-layout>
    @section('title', 'Edit role')
    @section('heading', 'Edit role')
    @section('subheading', $role->name)

    @section('actions')
        <a href="{{ route('leader.roles.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Back
        </a>
    @endsection

    <div class="row">
        <div class="col-12 col-lg-8">
            <form method="POST" action="{{ route('leader.roles.update', $role) }}">
                @csrf
                @method('PUT')

                <x-page-card icon="shield-lock" title="Role details">
                    <div class="mb-3">
                        <label for="name" class="form-label fw-semibold">Name</label>
                        <input type="text" id="name" name="name" required maxlength="100"
                               class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name', $role->name) }}">
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-4">
                        <label for="description" class="form-label fw-semibold">Description</label>
                        <input type="text" id="description" name="description" maxlength="500"
                               class="form-control @error('description') is-invalid @enderror"
                               value="{{ old('description', $role->description) }}">
                        @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror>
                    </div>

                    <h3 class="h6 fw-semibold mb-2">Permissions</h3>
                    <p class="small text-muted">
                        Removing a permission takes effect immediately for every user holding
                        this role.
                    </p>

                    @foreach ($permissions->groupBy(fn ($permission) => explode('.', $permission->name)[0]) as $group => $groupPermissions)
                        <h4 class="h6 fw-semibold text-capitalize mt-3 mb-2">
                            {{ str_replace('_', ' ', $group) }}
                        </h4>
                        <div class="row g-2">
                            @foreach ($groupPermissions as $permission)
                                <div class="col-12 col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox"
                                               name="permissions[]" value="{{ $permission->id }}"
                                               id="perm_{{ $permission->id }}"
                                               @checked(in_array($permission->id, old('permissions', $selected)))>
                                        <label class="form-check-label small" for="perm_{{ $permission->id }}">
                                            {{ $permission->name }}
                                            <span class="text-muted d-block">{{ $permission->description }}</span>
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endforeach

                    @error('permissions.*')
                        <div class="text-danger small mt-2">{{ $message }}</div>
                    @enderror

                    <button class="btn btn-primary mt-4">
                        <i class="bi bi-check-lg me-1"></i>Save changes
                    </button>
                </x-page-card>
            </form>
        </div>

        <div class="col-12 col-lg-4 mt-4 mt-lg-0">
            <x-page-card icon="people" title="Users holding this role">
                @if ($role->users->isEmpty())
                    <p class="text-muted small mb-0">Nobody holds this role yet.</p>
                @else
                    <ul class="list-unstyled mb-0 small">
                        @foreach ($role->users as $user)
                            <li class="border-bottom py-1">{{ $user->name }}</li>
                        @endforeach
                    </ul>
                @endif
            </x-page-card>
        </div>
    </div>
</x-app-layout>