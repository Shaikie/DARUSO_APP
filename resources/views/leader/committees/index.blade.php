<x-app-layout>
    @section('title', 'Committees')
    @section('heading', 'Committees')
    @section('subheading', 'Committee membership within leadership terms.')

    @section('actions')
        @can('create', App\Models\Committee::class)
            <a href="{{ route('leader.committees.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i>New committee
            </a>
        @endcan
    @endsection

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-12 col-md-8">
                    <label for="search" class="form-label small fw-semibold">Search</label>
                    <input type="search" id="search" name="search" class="form-control form-control-sm"
                           value="{{ request('search') }}" placeholder="Name or description">
                </div>
                <div class="col-12 col-md-4 d-flex gap-2">
                    <button class="btn btn-sm btn-outline-primary flex-grow-1" type="submit">
                        <i class="bi bi-funnel me-1"></i>Search
                    </button>
                    @if (request()->filled('search'))
                        <a href="{{ route('leader.committees.index') }}" class="btn btn-sm btn-link">Reset</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <x-page-card icon="people-fill" title="All committees">
        @if ($committees->isEmpty())
            <x-empty-state icon="people-fill"
                           title="No committees yet"
                           description="Committees group members to oversee specific functions.">
                @can('create', App\Models\Committee::class)
                    <x-slot:action>
                        <a href="{{ route('leader.committees.create') }}" class="btn btn-sm btn-primary">
                            Create the first committee
                        </a>
                    </x-slot:action>
                @endcan
            </x-empty-state>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">Committee</th>
                            <th scope="col">Description</th>
                            <th scope="col">Members</th>
                            <th scope="col" class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($committees as $committee)
                            <tr>
                                <td>
                                    <a href="{{ route('leader.committees.show', $committee) }}"
                                       class="fw-semibold text-decoration-none">
                                        {{ $committee->name }}
                                    </a>
                                </td>
                                <td class="small text-muted">
                                    {{ \Illuminate\Support\Str::limit($committee->description, 90) }}
                                </td>
                                <td class="small">{{ $committee->members_count }}</td>
                                <td class="text-end">
                                    <a href="{{ route('leader.committees.show', $committee) }}"
                                       class="btn btn-sm btn-outline-secondary">
                                        <i class="bi bi-eye me-1"></i>Members
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($committees->hasPages())
                <div class="mt-3">{{ $committees->links() }}</div>
            @endif
        @endif
    </x-page-card>
</x-app-layout>