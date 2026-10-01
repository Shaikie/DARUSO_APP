<x-app-layout>
    @section('title', $position->name)
    @section('heading', $position->name)
    @section('subheading', 'Hierarchy level '.$position->hierarchy_level)

    @section('actions')
        <a href="{{ route('leader.positions.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Back
        </a>
        @can('update', $position)
            <a href="{{ route('leader.positions.edit', $position) }}" class="btn btn-primary">
                <i class="bi bi-pencil me-1"></i>Edit
            </a>
        @endcan
    @endsection

    <div class="row g-4">
        <div class="col-12 col-lg-8">
            <x-page-card icon="people" title="Holders across terms">
                @if ($assignments->isEmpty())
                    <x-empty-state icon="person-badge"
                                   title="Nobody holds this position yet"
                                   description="Assign a leader from the leadership section." />
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col">Leader</th>
                                    <th scope="col">Ministry</th>
                                    <th scope="col">Term</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($assignments as $assignment)
                                    <tr>
                                        <td>{{ $assignment->user?->name ?? '—' }}</td>
                                        <td class="small">{{ $assignment->ministry?->name ?? '—' }}</td>
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
                <p class="small mb-0">{{ $position->description ?: 'No description provided.' }}</p>
            </x-page-card>
        </div>
    </div>
</x-app-layout>