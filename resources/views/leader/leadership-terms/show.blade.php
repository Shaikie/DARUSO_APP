<x-app-layout>
    @section('title', $term->name)
    @section('heading', $term->name)
    @section('subheading', 'Leadership term '.($term->is_active ? '(active)' : '(historical)'))

    @section('actions')
        <a href="{{ route('leader.leadership-terms.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Back
        </a>
        @can('activate', $term)
            @unless ($term->is_active)
                <form method="POST" action="{{ route('leader.leadership-terms.activate', $term) }}">
                    @csrf
                    <button class="btn btn-success">
                        <i class="bi bi-check2-circle me-1"></i>Activate term
                    </button>
                </form>
            @endunless
        @endcan
    @endsection

    <div class="row g-4">
        <div class="col-12 col-lg-7">
            <x-page-card icon="person-badge" title="Assignments">
                @if ($assignments->isEmpty())
                    <x-empty-state icon="person-badge" title="No assignments in this term"
                                   description="Assign leaders from the leadership section." />
                @else
                    <ul class="list-unstyled mb-0">
                        @foreach ($assignments as $assignment)
                            <li class="d-flex justify-content-between align-items-center border-bottom py-2">
                                <div>
                                    <span class="fw-semibold">{{ $assignment->user?->name ?? '—' }}</span>
                                    <div class="small text-muted">
                                        {{ $assignment->position?->name }}
                                        @if ($assignment->ministry)
                                            · {{ $assignment->ministry->name }}
                                        @endif
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </x-page-card>
        </div>

        <div class="col-12 col-lg-5">
            <x-page-card icon="people-fill" title="Committee members">
                @if ($members->isEmpty())
                    <p class="text-muted small mb-0">No committee members in this term.</p>
                @else
                    <ul class="list-unstyled mb-0">
                        @foreach ($members as $member)
                            <li class="border-bottom py-2">
                                <span class="fw-semibold">{{ $member->user?->name ?? '—' }}</span>
                                <div class="small text-muted">
                                    {{ $member->committee?->name }}
                                    @if ($member->role_in_committee)
                                        · {{ $member->role_in_committee }}
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </x-page-card>

            <x-page-card class="mt-4" icon="calendar-range" title="Dates">
                <dl class="row mb-0 small">
                    <dt class="col-5 text-muted fw-normal">Start</dt>
                    <dd class="col-7">{{ $term->start_date->format('d M Y') }}</dd>

                    <dt class="col-5 text-muted fw-normal">End</dt>
                    <dd class="col-7">{{ $term->end_date->format('d M Y') }}</dd>

                    <dt class="col-5 text-muted fw-normal">Status</dt>
                    <dd class="col-7">{{ $term->is_active ? 'Active' : 'Historical' }}</dd>
                </dl>
            </x-page-card>
        </div>
    </div>
</x-app-layout>