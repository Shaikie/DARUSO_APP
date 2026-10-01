<x-app-layout>
    @section('title', 'Leadership terms')
    @section('heading', 'Leadership terms')
    @section('subheading', 'Historical terms preserve the structure of past leadership.')

    @section('actions')
        @can('create', App\Models\LeadershipTerm::class)
            <a href="{{ route('leader.leadership-terms.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i>New term
            </a>
        @endcan
    @endsection

    <div class="alert alert-info d-flex gap-2" role="alert">
        <i class="bi bi-info-circle-fill mt-1"></i>
        <div class="small">
            Exactly one term is active at a time. Activating a term deactivates the
            previous one, so representatives and assignments always resolve unambiguously.
        </div>
    </div>

    <x-page-card icon="calendar-range" title="All terms">
        @if ($terms->isEmpty())
            <x-empty-state icon="calendar-range"
                           title="No leadership terms"
                           description="Create a term such as 2026/2027 before assigning leaders." />
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">Term</th>
                            <th scope="col">Dates</th>
                            <th scope="col">Assignments</th>
                            <th scope="col">Committee members</th>
                            <th scope="col">Status</th>
                            <th scope="col" class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($terms as $term)
                            <tr>
                                <td>
                                    <a href="{{ route('leader.leadership-terms.show', $term) }}"
                                       class="fw-semibold text-decoration-none">
                                        {{ $term->name }}
                                    </a>
                                </td>
                                <td class="small text-nowrap">
                                    {{ $term->start_date->format('d M Y') }} –
                                    {{ $term->end_date->format('d M Y') }}
                                </td>
                                <td class="small">{{ $term->assignments_count }}</td>
                                <td class="small">{{ $term->committee_members_count }}</td>
                                <td>
                                    @if ($term->is_active)
                                        <span class="badge text-bg-success">Active</span>
                                    @else
                                        <span class="badge text-bg-secondary">Historical</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        @can('activate', $term)
                                            @unless ($term->is_active)
                                                <form method="POST"
                                                      action="{{ route('leader.leadership-terms.activate', $term) }}">
                                                    @csrf
                                                    <button class="btn btn-outline-success" title="Activate">
                                                        <i class="bi bi-check2-circle"></i>
                                                    </button>
                                                </form>
                                            @endunless
                                        @endcan

                                        @can('update', $term)
                                            <a href="{{ route('leader.leadership-terms.edit', $term) }}"
                                               class="btn btn-outline-primary" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                        @endcan

                                        @can('delete', $term)
                                            <form method="POST"
                                                  action="{{ route('leader.leadership-terms.destroy', $term) }}"
                                                  onsubmit="return confirm('Delete this term?');">
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

            @if ($terms->hasPages())
                <div class="mt-3">{{ $terms->links() }}</div>
            @endif
        @endif
    </x-page-card>
</x-app-layout>