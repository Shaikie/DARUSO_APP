<x-app-layout>
    @section('title', 'Representatives')
    @section('heading', 'Your representatives')
    @section('subheading', $term
        ? 'DARUSO leadership for '.$term->name
        : 'No leadership term has been set up yet.')

    <div class="alert alert-info d-flex gap-2" role="alert">
        <i class="bi bi-info-circle-fill mt-1"></i>
        <div class="small">
            Representatives are resolved from the active leadership term, positions and
            assignments. Contact details are not published here — raise a complaint through
            the portal to reach a leader directly.
        </div>
    </div>

    <div class="row g-4">
        <div class="col-12 col-lg-7">
            <x-page-card icon="person-badge" title="Leadership">
                @forelse ($assignments as $assignment)
                    <div class="d-flex justify-content-between align-items-start border-bottom py-2 gap-2">
                        <div>
                            <span class="fw-semibold">{{ $assignment->user?->name ?? '—' }}</span>
                            <div class="small text-muted">
                                {{ $assignment->position?->name ?? '—' }}
                                @if ($assignment->ministry)
                                    · {{ $assignment->ministry->name }}
                                @endif
                            </div>
                        </div>
                        <span class="badge text-bg-light border">
                            {{ $assignment->position?->hierarchy_level ?? '—' }}
                        </span>
                    </div>
                @empty
                    <x-empty-state icon="person-badge"
                                   title="No representatives published"
                                   description="Leadership assignments for the active term will appear here." />
                @endforelse
            </x-page-card>

            <x-page-card class="mt-4" icon="people-fill" title="Committee membership">
                @forelse ($committees as $membership)
                    <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                        <div>
                            <span class="fw-semibold">{{ $membership->user?->name ?? '—' }}</span>
                            <div class="small text-muted">{{ $membership->committee?->name ?? '—' }}</div>
                        </div>
                        @if ($membership->role_in_committee)
                            <span class="badge text-bg-light border">{{ $membership->role_in_committee }}</span>
                        @endif
                    </div>
                @empty
                    <p class="text-muted small mb-0">No committee members in this term.</p>
                @endforelse
            </x-page-card>
        </div>

        <div class="col-12 col-lg-5">
            <x-page-card icon="building" title="Ministries">
                @forelse ($ministries as $ministry)
                    <div class="border-bottom py-2">
                        <span class="fw-semibold">{{ $ministry->name }}</span>
                        <div class="small text-muted">{{ $ministry->description }}</div>
                    </div>
                @empty
                    <p class="text-muted small mb-0">No ministries have been configured.</p>
                @endforelse
            </x-page-card>

            <x-page-card class="mt-4" icon="chat-left-text" title="Need to reach someone?">
                <p class="small text-muted">
                    Submit a complaint and it will be routed to the responsible ministry. You
                    will be notified as it progresses.
                </p>
                @can('create', App\Models\Complaint::class)
                    <a href="{{ route('student.complaints.create') }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-plus-lg me-1"></i>Submit a complaint
                    </a>
                @endcan
            </x-page-card>
        </div>
    </div>
</x-app-layout>