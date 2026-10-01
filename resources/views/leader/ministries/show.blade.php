<x-app-layout>
    @section('title', $ministry->name)
    @section('heading', $ministry->name)
    @section('subheading', 'Ministry portfolio and leadership')

    @section('actions')
        <a href="{{ route('leader.ministries.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Back
        </a>
        @can('update', $ministry)
            <a href="{{ route('leader.ministries.edit', $ministry) }}" class="btn btn-primary">
                <i class="bi bi-pencil me-1"></i>Edit
            </a>
        @endcan
        @can('delete', $ministry)
            <form method="POST" action="{{ route('leader.ministries.destroy', $ministry) }}"
                  onsubmit="return confirm('Delete this ministry?');">
                @csrf
                @method('DELETE')
                <button class="btn btn-outline-danger">
                    <i class="bi bi-trash me-1"></i>Delete
                </button>
            </form>
        @endcan
    @endsection

    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <x-stat-card icon="people" label="Assignments (all terms)" :value="$ministry->assignments()->count()" tone="primary" />
        </div>
        <div class="col-6 col-lg-3">
            <x-stat-card icon="exclamation-triangle" label="Open complaints" :value="$openComplaints" tone="warning" />
        </div>
    </div>

    <div class="row g-4">
        <div class="col-12 col-lg-8">
            <x-page-card icon="people" title="Leadership">
                @if ($assignments->isEmpty())
                    <x-empty-state icon="person-badge"
                                   title="Nobody is assigned to this ministry"
                                   description="Assign a position within this ministry from the leadership section." />
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col">Leader</th>
                                    <th scope="col">Position</th>
                                    <th scope="col">Term</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($assignments as $assignment)
                                    <tr>
                                        <td>{{ $assignment->user?->name ?? '—' }}</td>
                                        <td class="small">{{ $assignment->position?->name ?? '—' }}</td>
                                        <td class="small">
                                            {{ $assignment->term?->name ?? '—' }}
                                            @if ($assignment->term?->is_active)
                                                <span class="badge text-bg-success ms-1">Active</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </x-page-card>
        </div>

        <div class="col-12 col-lg-4">
            <x-page-card icon="info-circle" title="About">
                <p class="small mb-3">{{ $ministry->description ?: 'No description provided.' }}</p>
                <dl class="row mb-0 small">
                    <dt class="col-5 text-muted fw-normal">Created</dt>
                    <dd class="col-7">{{ $ministry->created_at->format('d M Y') }}</dd>
                </dl>
            </x-page-card>
        </div>
    </div>
</x-app-layout>