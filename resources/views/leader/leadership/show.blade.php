<x-app-layout>
    @section('title', 'Leadership assignment')
    @section('heading', $assignment->position?->name ?? 'Assignment')
    @section('subheading', $assignment->user?->name ?? '')

    @section('actions')
        <a href="{{ route('leader.leadership.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Back
        </a>
        @can('update', $assignment)
            <a href="{{ route('leader.leadership.edit', $assignment) }}" class="btn btn-primary">
                <i class="bi bi-pencil me-1"></i>Edit
            </a>
        @endcan
        @can('delete', $assignment)
            <form method="POST" action="{{ route('leader.leadership.destroy', $assignment) }}"
                  onsubmit="return confirm('Remove this assignment?');">
                @csrf
                @method('DELETE')
                <button class="btn btn-outline-danger">
                    <i class="bi bi-trash me-1"></i>Remove
                </button>
            </form>
        @endcan
    @endsection

    <div class="row g-4">
        <div class="col-12 col-lg-8">
            <x-page-card icon="person-badge" title="Assignment">
                <dl class="row mb-0">
                    <dt class="col-4 text-muted fw-normal">Leader</dt>
                    <dd class="col-8">{{ $assignment->user?->name ?? '—' }}</dd>

                    <dt class="col-4 text-muted fw-normal">Position</dt>
                    <dd class="col-8">{{ $assignment->position?->name ?? '—' }}</dd>

                    <dt class="col-4 text-muted fw-normal">Ministry</dt>
                    <dd class="col-8">{{ $assignment->ministry?->name ?? 'No ministry' }}</dd>

                    <dt class="col-4 text-muted fw-normal">Term</dt>
                    <dd class="col-8">
                        {{ $assignment->term?->name ?? '—' }}
                        @if ($assignment->term?->is_active)
                            <span class="badge text-bg-success ms-1">Active</span>
                        @endif
                    </dd>
                </dl>
            </x-page-card>
        </div>

        <div class="col-12 col-lg-4">
            <x-page-card icon="info-circle" title="Recorded">
                <dl class="row mb-0 small">
                    <dt class="col-5 text-muted fw-normal">Assigned</dt>
                    <dd class="col-7">{{ $assignment->created_at->format('d M Y') }}</dd>

                    <dt class="col-5 text-muted fw-normal">Updated</dt>
                    <dd class="col-7">{{ $assignment->updated_at->format('d M Y') }}</dd>
                </dl>
            </x-page-card>
        </div>
    </div>
</x-app-layout>