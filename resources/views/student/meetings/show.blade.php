<x-app-layout>
    @section('title', $meeting->title)
    @section('heading', $meeting->title)
    @section('subheading', $meeting->meeting_date->format('l, d F Y').' at '.$meeting->meeting_time->format('H:i'))

    @section('actions')
        <a href="{{ route('student.meetings.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Back
        </a>
        @if ($meeting->attachments->isNotEmpty())
            <a href="{{ route('student.meetings.download', $meeting) }}" class="btn btn-primary">
                <i class="bi bi-download me-1"></i>Download agenda
            </a>
        @endif
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
            </x-page-card>
        </div>

        <div class="col-12 col-lg-4">
            <x-page-card icon="info-circle" title="When and where">
                <dl class="row mb-0 small">
                    <dt class="col-5 text-muted fw-normal">Date</dt>
                    <dd class="col-7">{{ $meeting->meeting_date->format('d M Y') }}</dd>

                    <dt class="col-5 text-muted fw-normal">Time</dt>
                    <dd class="col-7">{{ $meeting->meeting_time->format('H:i') }}</dd>

                    <dt class="col-5 text-muted fw-normal">Venue</dt>
                    <dd class="col-7">{{ $meeting->venue }}</dd>

                    <dt class="col-5 text-muted fw-normal">Organiser</dt>
                    <dd class="col-7">{{ $meeting->organizer?->name ?? 'DARUSO' }}</dd>
                </dl>
            </x-page-card>
        </div>
    </div>
</x-app-layout>