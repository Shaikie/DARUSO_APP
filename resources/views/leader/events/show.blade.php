<x-app-layout>
    @section('title', $event->title)
    @section('heading', $event->title)
    @section('subheading', $event->event_date->format('l, d F Y'))

    @section('actions')
        <a href="{{ route('leader.events.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Back
        </a>
        @can('update', $event)
            <a href="{{ route('leader.events.edit', $event) }}" class="btn btn-primary">
                <i class="bi bi-pencil me-1"></i>Edit
            </a>
        @endcan
        @can('delete', $event)
            <form method="POST" action="{{ route('leader.events.destroy', $event) }}"
                  onsubmit="return confirm('Delete this event?');">
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
            <x-page-card icon="calendar-check" title="Event details">
                <x-status-badge :status="$event->status" class="mb-3" />

                <div class="communication-body">{{ $event->description }}</div>

                @if ($event->attachments->isNotEmpty())
                    <hr>
                    <h2 class="h6 fw-semibold">Attachments</h2>
                    <ul class="list-unstyled mb-0">
                        @foreach ($event->attachments as $attachment)
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
                    <dd class="col-7">{{ $event->event_date->format('d M Y') }}</dd>

                    <dt class="col-5 text-muted fw-normal">Venue</dt>
                    <dd class="col-7">{{ $event->venue }}</dd>

                    <dt class="col-5 text-muted fw-normal">Organiser</dt>
                    <dd class="col-7">{{ $event->organizer?->name ?? '—' }}</dd>
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