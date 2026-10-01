<x-app-layout>
    @section('title', 'Events')
    @section('heading', 'Events')
    @section('subheading', 'Organise and target DARUSO events.')

    @section('actions')
        @can('create', App\Models\Event::class)
            <a href="{{ route('leader.events.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i>New event
            </a>
        @endcan
    @endsection

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-12 col-md-6">
                    <label for="search" class="form-label small fw-semibold">Search</label>
                    <input type="search" id="search" name="search" class="form-control form-control-sm"
                           value="{{ request('search') }}" placeholder="Title or venue">
                </div>
                <div class="col-6 col-md-3">
                    <label for="status" class="form-label small fw-semibold">Status</label>
                    <select id="status" name="status" class="form-select form-select-sm">
                        <option value="">All statuses</option>
                        @foreach ($statuses as $status)
                            <option value="{{ $status->value }}" @selected(request('status') === $status->value)>
                                {{ $status->label() }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-3 d-flex gap-2">
                    <button class="btn btn-sm btn-outline-primary flex-grow-1" type="submit">
                        <i class="bi bi-funnel me-1"></i>Filter
                    </button>
                    @if (request()->hasAny(['search', 'status']))
                        <a href="{{ route('leader.events.index') }}" class="btn btn-sm btn-link">Reset</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <x-page-card icon="calendar-check" title="All events">
        @if ($events->isEmpty())
            <x-empty-state icon="calendar-check"
                           title="No events"
                           description="Create an event and target the students it is open to.">
                @can('create', App\Models\Event::class)
                    <x-slot:action>
                        <a href="{{ route('leader.events.create') }}" class="btn btn-sm btn-primary">
                            Create an event
                        </a>
                    </x-slot:action>
                @endcan
            </x-empty-state>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">Event</th>
                            <th scope="col">Date</th>
                            <th scope="col">Venue</th>
                            <th scope="col">Audience</th>
                            <th scope="col">Status</th>
                            <th scope="col" class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($events as $event)
                            <tr>
                                <td>
                                    <a href="{{ route('leader.events.show', $event) }}"
                                       class="fw-semibold text-decoration-none">
                                        {{ $event->title }}
                                    </a>
                                    <div class="small text-muted">
                                        Organised by {{ $event->organizer?->name ?? '—' }}
                                    </div>
                                </td>
                                <td class="small text-nowrap">{{ $event->event_date->format('d M Y') }}</td>
                                <td class="small">{{ $event->venue }}</td>
                                <td class="small">
                                    @foreach ($event->audienceRules as $rule)
                                        <span class="badge text-bg-light border me-1">
                                            {{ $rule->type()->label() }}
                                        </span>
                                    @endforeach
                                </td>
                                <td><x-status-badge :status="$event->status" /></td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('leader.events.show', $event) }}"
                                           class="btn btn-outline-secondary" title="View">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        @can('update', $event)
                                            <a href="{{ route('leader.events.edit', $event) }}"
                                               class="btn btn-outline-primary" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                        @endcan
                                        @can('delete', $event)
                                            <form method="POST"
                                                  action="{{ route('leader.events.destroy', $event) }}"
                                                  onsubmit="return confirm('Delete this event?');">
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

            @if ($events->hasPages())
                <div class="mt-3">{{ $events->links() }}</div>
            @endif
        @endif
    </x-page-card>
</x-app-layout>