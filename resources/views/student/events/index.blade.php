<x-app-layout>
    @section('title', 'Events')
    @section('heading', 'Events')
    @section('subheading', 'Events open to you and your groups.')

    <div class="d-flex flex-wrap gap-2 mb-4">
        <a href="{{ route('student.events.index') }}"
           class="btn btn-sm {{ request()->boolean('upcoming') ? 'btn-outline-secondary' : 'btn-primary' }}">
            All events
        </a>
        <a href="{{ route('student.events.index', ['upcoming' => 1]) }}"
           class="btn btn-sm {{ request()->boolean('upcoming') ? 'btn-primary' : 'btn-outline-secondary' }}">
            Upcoming only
        </a>
    </div>

    <x-page-card icon="calendar-check" title="Your events">
        @if ($events->isEmpty())
            <x-empty-state icon="calendar-check"
                           title="No events"
                           description="Events addressed to your college, programme or year will appear here." />
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">Event</th>
                            <th scope="col">Date</th>
                            <th scope="col">Venue</th>
                            <th scope="col">Status</th>
                            <th scope="col" class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($events as $event)
                            <tr>
                                <td>
                                    <a href="{{ route('student.events.show', $event) }}"
                                       class="fw-semibold text-decoration-none">
                                        {{ $event->title }}
                                    </a>
                                    <div class="small text-muted">
                                        Organised by {{ $event->organizer?->name ?? 'DARUSO' }}
                                    </div>
                                </td>
                                <td class="small text-nowrap">{{ $event->event_date->format('D, d M Y') }}</td>
                                <td class="small">{{ $event->venue }}</td>
                                <td><x-status-badge :status="$event->status" /></td>
                                <td class="text-end">
                                    <a href="{{ route('student.events.show', $event) }}"
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye me-1"></i>Details
                                    </a>
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