<x-app-layout>
    @section('title', 'Positions')
    @section('heading', 'Positions')
    @section('subheading', 'Configurable leadership positions, ordered by hierarchy.')

    @section('actions')
        @can('create', App\Models\Position::class)
            <a href="{{ route('leader.positions.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i>New position
            </a>
        @endcan
    @endsection

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-12 col-md-8">
                    <label for="search" class="form-label small fw-semibold">Search</label>
                    <input type="search" id="search" name="search" class="form-control form-control-sm"
                           value="{{ request('search') }}" placeholder="Position name">
                </div>
                <div class="col-12 col-md-4 d-flex gap-2">
                    <button class="btn btn-sm btn-outline-primary flex-grow-1" type="submit">
                        <i class="bi bi-funnel me-1"></i>Search
                    </button>
                    @if (request()->filled('search'))
                        <a href="{{ route('leader.positions.index') }}" class="btn btn-sm btn-link">Reset</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <x-page-card icon="diagram-3" title="All positions">
        @if ($positions->isEmpty())
            <x-empty-state icon="diagram-3"
                           title="No positions defined"
                           description="Positions describe leadership roles and drive the representatives directory.">
                @can('create', App\Models\Position::class)
                    <x-slot:action>
                        <a href="{{ route('leader.positions.create') }}" class="btn btn-sm btn-primary">
                            Create the first position
                        </a>
                    </x-slot:action>
                @endcan
            </x-empty-state>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">Hierarchy</th>
                            <th scope="col">Position</th>
                            <th scope="col">Description</th>
                            <th scope="col">Assignments</th>
                            <th scope="col" class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($positions as $position)
                            <tr>
                                <td class="small text-nowrap">{{ $position->hierarchy_level }}</td>
                                <td>
                                    <a href="{{ route('leader.positions.show', $position) }}"
                                       class="fw-semibold text-decoration-none">
                                        {{ $position->name }}
                                    </a>
                                </td>
                                <td class="small text-muted">
                                    {{ \Illuminate\Support\Str::limit($position->description, 80) }}
                                </td>
                                <td class="small">{{ $position->assignments_count }}</td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        @can('update', $position)
                                            <a href="{{ route('leader.positions.edit', $position) }}"
                                               class="btn btn-outline-primary" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                        @endcan

                                        @can('delete', $position)
                                            <form method="POST"
                                                  action="{{ route('leader.positions.destroy', $position) }}"
                                                  onsubmit="return confirm('Delete this position?');">
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

            @if ($positions->hasPages())
                <div class="mt-3">{{ $positions->links() }}</div>
            @endif
        @endif
    </x-page-card>
</x-app-layout>