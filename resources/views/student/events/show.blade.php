<x-app-layout>
    @section('title', $event->title)
    @section('heading', $event->title)
    @section('subheading', $event->event_date->format('l, d F Y'))

    @section('actions')
        <a href="{{ route('student.events.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Back
        </a>
        @if ($event->attachments->isNotEmpty())
            <a href="{{ route('student.events.download', $event) }}" class="btn btn-primary">
                <i class="bi bi-download me-1"></i>Download flyer
            </a>
        @endif
    @endsection

    <div class="row g-4">
        <div class="col-12 col-lg-8">
            <x-page-card icon="calendar-check" title="Event details">
                <x-status-badge :status="$event->status" class="mb-3" />

                <div class="communication-body">{{ $event->description }}</div>
            </x-page-card>
        </div>

        <div class="col-12 col-lg-4">
            <x-page-card icon="info-circle" title="When and where">
                <dl class="row mb-0 small">
                    <dt class="col-5 text-muted fw-normal">Date</dt>
                    <dd class="col-7">{{ $event->event_date->format('d M Y') }}</dd>

                    <dt class="col-5 text-muted fw-normal">Venue</dt>
                    <dd class="col-7">{{ $event->venue }}</dd>

                    <dt class="col-5 text-muted fw-normal">Organiser</dt>
                    <dd class="col-7">{{ $event->organizer?->name ?? 'DARUSO' }}</dd>
                </dl>
            </x-page-card>
        </div>
    </div>
</x-app-layout>