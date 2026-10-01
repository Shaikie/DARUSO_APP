<x-app-layout>
    @section('title', 'Meetings')
    @section('heading', 'Meetings')
    @section('subheading', 'Meetings you have been invited to attend.')

    <div class="d-flex flex-wrap gap-2 mb-4">
        <a href="{{ route('student.meetings.index') }}"
           class="btn btn-sm {{ request()->boolean('upcoming') ? 'btn-outline-secondary' : 'btn-primary' }}">
            All meetings
        </a>
        <a href="{{ route('student.meetings.index', ['upcoming' => 1]) }}"
           class="btn btn-sm {{ request()->boolean('upcoming') ? 'btn-primary' : 'btn-outline-secondary' }}">
            Upcoming only
        </a>
    </div>

    <x-page-card icon="calendar-event" title="Your meetings">
        @if ($meetings->isEmpty())
            <x-empty-state icon="calendar-event"
                           title="No meetings"
                           description="Meetings addressed to your groups will appear here with the date, time and venue." />
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">Meeting</th>
                            <th scope="col">Date</th>
                            <th scope="col">Time</th>
                            <th scope="col">Venue</th>
                            <th scope="col">Status</th>
                            <th scope="col" class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($meetings as $meeting)
                            <tr>
                                <td>
                                    <a href="{{ route('student.meetings.show', $meeting) }}"
                                       class="fw-semibold text-decoration-none">
                                        {{ $meeting->title }}
                                    </a>
                                    <div class="small text-muted">
                                        Organised by {{ $meeting->organizer?->name ?? 'DARUSO' }}
                                    </div>
                                </td>
                                <td class="small text-nowrap">{{ $meeting->meeting_date->format('D, d M Y') }}</td>
                                <td class="small text-nowrap">{{ $meeting->meeting_time->format('H:i') }}</td>
                                <td class="small">{{ $meeting->venue }}</td>
                                <td><x-status-badge :status="$meeting->status" /></td>
                                <td class="text-end">
                                    <a href="{{ route('student.meetings.show', $meeting) }}"
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye me-1"></i>Details
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($meetings->hasPages())
                <div class="mt-3">{{ $meetings->links() }}</div>
            @endif
        @endif
    </x-page-card>
</x-app-layout>