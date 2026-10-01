<x-app-layout>
    @section('title', 'Ministries')
    @section('heading', 'Ministries')
    @section('subheading', 'Ministry portfolio and current leadership.')

    @section('actions')
        @can('create', App\Models\Ministry::class)
            <a href="{{ route('leader.ministries.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i>New ministry
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
                        <a href="{{ route('leader.ministries.index') }}" class="btn btn-sm btn-link">Reset</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <x-page-card icon="building" title="All ministries">
        @if ($ministries->isEmpty())
            <x-empty-state icon="building"
                           title="No ministries yet"
                           description="Ministries group leadership portfolios and route complaints.">
                @can('create', App\Models\Ministry::class)
                    <x-slot:action>
                        <a href="{{ route('leader.ministries.create') }}" class="btn btn-sm btn-primary">
                            Create the first ministry
                        </a>
                    </x-slot:action>
                @endcan
            </x-empty-state>
        @else
            <div class="row g-3">
                @foreach ($ministries as $ministry)
                    <div class="col-12 col-md-6 col-lg-4">
                        <div class="border rounded p-3 h-100 d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-start gap-2 mb-1">
                                <a href="{{ route('leader.ministries.show', $ministry) }}"
                                   class="fw-semibold text-decoration-none">
                                    {{ $ministry->name }}
                                </a>
                                <span class="badge text-bg-light border">
                                    {{ $ministry->assignments_count }} assigned
                                </span>
                            </div>

                            <p class="small text-muted mb-2">
                                {{ \Illuminate\Support\Str::limit($ministry->description, 100) }}
                            </p>

                            <div class="small text-muted mt-auto">
                                {{ $ministry->complaints_count }} complaint(s) routed
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            @if ($ministries->hasPages())
                <div class="mt-3">{{ $ministries->links() }}</div>
            @endif
        @endif
    </x-page-card>
</x-app-layout>