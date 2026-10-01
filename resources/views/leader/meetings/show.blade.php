<x-app-layout>
    @section('title', $meeting->title)
    @section('heading', $meeting->title)
    @section('subheading', $meeting->meeting_date->format('l, d F Y').' at '.$meeting->meeting_time->format('H:i'))

    @section('actions')
        <a href="{{ route('leader.meetings.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Back
        </a>
        @can('update', $meeting)
            <a href="{{ route('leader.meetings.edit', $meeting) }}" class="btn btn-primary">
                <i class="bi bi-pencil me-1"></i>Edit
            </a>
        @endcan
        @can('delete', $meeting)
            <form method="POST" action="{{ route('leader.meetings.destroy', $meeting) }}"
                  onsubmit="return confirm('Delete this meeting?');">
                @csrf
                @method('DELETE')
                <button class="btn btn-outline-danger">
                    <i class="bi bi-trash me-1"></i>Delete
                </button>
            </form>
        @endcan
    @endsection

    <div class="row g-4">
        <div class="col-12 col-lg-8">
            <x-page-card icon="calendar-event" title="Meeting details">
                <x-status-badge :status="$meeting->status" class="mb-3" />

                <div class="communication-body">{{ $meeting->description }}</div>

                @if ($meeting->agenda)
                    <hr>
                    <h2 class="h6 fw-semibold">Agenda</h2>
                    <div class="communication-body small">{{ $meeting->agenda }}</div>
                @endif

                @if ($meeting->attachments->isNotEmpty())
                    <hr>
                    <h2 class="h6 fw-semibold">Attachments</h2>
                    <ul class="list-unstyled mb-0">
                        @foreach ($meeting->attachments as $attachment)
                            <li class="mb-1">
                                <a href="{{ route('attachments.download', $attachment) }}" class="text-decoration-none">
                                    <i class="bi bi-paperclip me-1"></i>{{ $attachment->file_name }}
                                </a>
                                <span class="text-muted small">({{ $attachment->humanReadableSize() }})</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </x-page-card>
        </div>

        <div class="col-12 col-lg-4">
            <x-page-card icon="info-circle" title="Details">
                <dl class="row mb-0 small">
                    <dt class="col-5 text-muted fw-normal">Date</dt>
                    <dd class="col-7">{{ $meeting->meeting_date->format('d M Y') }}</dd>

                    <dt class="col-5 text-muted fw-normal">Time</dt>
                    <dd class="col-7">{{ $meeting->meeting_time->format('H:i') }}</dd>

                    <dt class="col-5 text-muted fw-normal">Venue</dt>
                    <dd class="col-7">{{ $meeting->venue }}</dd>

                    <dt class="col-5 text-muted fw-normal">Organiser</dt>
                    <dd class="col-7">{{ $meeting->organizer?->name ?? '—' }}</dd>
                </dl>
            </x-page-card>

            <x-page-card class="mt-4" icon="bullseye" title="Target audience">
                @forelse ($audienceSummary as $line)
                    <div class="mb-1"><i class="bi bi-dot me-1"></i>{{ $line }}</div>
                @empty
                    <p class="text-muted small mb-0">No audience rules attached.</p>
                @endforelse
            </x-page-card>
        </div>
    </div>
</x-app-layout>