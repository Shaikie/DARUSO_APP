<x-app-layout>
    @section('title', 'Leadership')
    @section('heading', 'Leadership assignments')
    @section('subheading', $selectedTerm
        ? 'Who holds which position in '.$selectedTerm->name
        : 'No leadership term has been created yet.')

    @section('actions')
        @can('create', App\Models\LeaderAssignment::class)
            <a href="{{ route('leader.leadership.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i>New assignment
            </a>
        @endcan
    @endsection

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-12 col-md-4">
                    <label for="term" class="form-label small fw-semibold">Leadership term</label>
                    <select id="term" name="term" class="form-select form-select-sm">
                        <option value="">Active term</option>
                        @foreach ($terms as $term)
                            <option value="{{ $term->id }}"
                                @selected($selectedTerm && $selectedTerm->id === $term->id)>
                                {{ $term->name }}{{ $term->is_active ? ' (active)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-4">
                    <label for="ministry" class="form-label small fw-semibold">Ministry</label>
                    <select id="ministry" name="ministry" class="form-select form-select-sm">
                        <option value="">All ministries</option>
                        @foreach ($ministries as $ministry)
                            <option value="{{ $ministry->id }}" @selected((int) request('ministry') === $ministry->id)>
                                {{ $ministry->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-4">
                    <label for="position" class="form-label small fw-semibold">Position</label>
                    <select id="position" name="position" class="form-select form-select-sm">
                        <option value="">All positions</option>
                        @foreach ($positions as $position)
                            <option value="{{ $position->id }}" @selected((int) request('position') === $position->id)>
                                {{ $position->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 d-flex gap-2">
                    <button class="btn btn-sm btn-outline-primary" type="submit">
                        <i class="bi bi-funnel me-1"></i>Filter
                    </button>
                    @if (request()->hasAny(['term', 'ministry', 'position']))
                        <a href="{{ route('leader.leadership.index') }}" class="btn btn-sm btn-link">Reset</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <x-page-card icon="person-badge" title="Assignments">
        @if ($assignments->isEmpty())
            <x-empty-state icon="person-badge"
                           title="No assignments in this term"
                           description="Assign a leader to a position, optionally within a ministry.">
                @can('create', App\Models\LeaderAssignment::class)
                    <x-slot:action>
                        <a href="{{ route('leader.leadership.create') }}" class="btn btn-sm btn-primary">
                            New assignment
                        </a>
                    </x-slot:action>
                @endcan
            </x-empty-state>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">Leader</th>
                            <th scope="col">Position</th>
                            <th scope="col">Ministry</th>
                            <th scope="col">Term</th>
                            <th scope="col" class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($assignments as $assignment)
                            <tr>
                                <td>
                                    <a href="{{ route('leader.leadership.show', $assignment) }}"
                                       class="fw-semibold text-decoration-none">
                                        {{ $assignment->user?->name ?? '—' }}
                                    </a>
                                </td>
                                <td class="small">{{ $assignment->position?->name ?? '—' }}</td>
                                <td class="small">{{ $assignment->ministry?->name ?? '—' }}</td>
                                <td class="small">
                                    {{ $assignment->term?->name ?? '—' }}
                                    @if ($assignment->term?->is_active)
                                        <span class="badge text-bg-success ms-1">Active</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        @can('update', $assignment)
                                            <a href="{{ route('leader.leadership.edit', $assignment) }}"
                                               class="btn btn-outline-primary" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                        @endcan

                                        @can('delete', $assignment)
                                            <form method="POST"
                                                  action="{{ route('leader.leadership.destroy', $assignment) }}"
                                                  onsubmit="return confirm('Remove this assignment?');">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-outline-danger" title="Remove">
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

            @if ($assignments->hasPages())
                <div class="mt-3">{{ $assignments->links() }}</div>
            @endif
        @endif
    </x-page-card>
</x-app-layout>