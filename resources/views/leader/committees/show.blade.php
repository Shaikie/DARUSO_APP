<x-app-layout>
    @section('title', $committee->name)
    @section('heading', $committee->name)
    @section('subheading', 'Committee membership by leadership term')

    @section('actions')
        <a href="{{ route('leader.committees.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Back
        </a>
        @can('update', $committee)
            <a href="{{ route('leader.committees.edit', $committee) }}" class="btn btn-primary">
                <i class="bi bi-pencil me-1"></i>Edit
            </a>
        @endcan
        @can('delete', $committee)
            <form method="POST" action="{{ route('leader.committees.destroy', $committee) }}"
                  onsubmit="return confirm('Delete this committee?');">
                @csrf
                @method('DELETE')
                <button class="btn btn-outline-danger">
                    <i class="bi bi-trash me-1"></i>Delete
                </button>
            </form>
        @endcan
    @endsection

    <div class="row g-4">
        <div class="col-12 col-lg-7">
            <x-page-card icon="people" title="Members">
                @if ($members->isEmpty())
                    <x-empty-state icon="person-plus"
                                   title="No members yet"
                                   description="Add a member using the form alongside." />
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col">Member</th>
                                    <th scope="col">Role in committee</th>
                                    <th scope="col">Term</th>
                                    <th scope="col" class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($members as $member)
                                    <tr>
                                        <td>{{ $member->user?->name ?? '—' }}</td>
                                        <td class="small">{{ $member->role_in_committee ?? '—' }}</td>
                                        <td class="small">
                                            {{ $member->term?->name ?? '—' }}
                                            @if ($member->term?->is_active)
                                                <span class="badge text-bg-success ms-1">Active</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            @can('manageMembers', $committee)
                                                <form method="POST"
                                                      action="{{ route('leader.committees.members.destroy', [$committee, $member]) }}"
                                                      onsubmit="return confirm('Remove this member?');">
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
                @endif
            </x-page-card>
        </div>

        <div class="col-12 col-lg-5">
            @can('manageMembers', $committee)
                <x-page-card icon="person-plus" title="Add a member">
                    <form method="POST" action="{{ route('leader.committees.members.store', $committee) }}">
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
                            @error('user_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label for="leadership_term_id" class="form-label small fw-semibold">Term</label>
                            <select id="leadership_term_id" name="leadership_term_id" required
                                    class="form-select @error('leadership_term_id') is-invalid @enderror">
                                @foreach ($terms as $term)
                                    <option value="{{ $term->id }}"
                                        @selected(old('leadership_term_id', $defaultTermId) == $term->id)>
                                        {{ $term->name }}{{ $term->is_active ? ' (active)' : '' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('leadership_term_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label for="role_in_committee" class="form-label small fw-semibold">Role</label>
                            <input type="text" id="role_in_committee" name="role_in_committee" maxlength="100"
                                   class="form-control @error('role_in_committee') is-invalid @enderror"
                                   value="{{ old('role_in_committee') }}"
                                   placeholder="e.g. Chairperson or Member">
                            @error('role_in_committee') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        {{-- Fixed to the committee in the route. --}}
                        <input type="hidden" name="committee_id" value="{{ $committee->id }}">

                        <button class="btn btn-primary w-100">
                            <i class="bi bi-plus-lg me-1"></i>Add member
                        </button>
                    </form>
                </x-page-card>
            @endcan

            <x-page-card class="mt-4" icon="info-circle" title="About">
                <p class="small mb-0">{{ $committee->description ?: 'No description provided.' }}</p>
            </x-page-card>
        </div>
    </div>
</x-app-layout>