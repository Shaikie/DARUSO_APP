<x-app-layout>
    @section('title', 'Notification')
    @section('heading', 'Delivered notification')
    @section('subheading', $notification->title)

    @section('actions')
        <a href="{{ route('leader.notifications.show', $notification) }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Back
        </a>
    @endsection

    {{--
        A delivered notification is read-only by design: recipients may already
        have acted on the wording that was sent. This route therefore shows the
        message rather than an edit form, and the controller rejects any attempt
        to modify it.
    --}}
    <div class="alert alert-info d-flex gap-2" role="alert">
        <i class="bi bi-lock mt-1"></i>
        <div class="small">
            Notifications are immutable once delivered, and are never deleted. Read state is
            recorded instead so the history stays intact.
        </div>
    </div>

    <x-page-card icon="bell" title="Message">
        <div class="d-flex flex-wrap gap-2 mb-3">
            <x-priority-badge :priority="$notification->priority" />
            @if ($notification->isRead())
                <span class="badge text-bg-success">Read</span>
            @else
                <span class="badge text-bg-secondary">Unread</span>
            @endif
        </div>

        <div class="communication-body">{{ $notification->message }}</div>
    </x-page-card>

    <x-page-card class="mt-4" icon="info-circle" title="Details">
        <dl class="row mb-0 small">
            <dt class="col-5 text-muted fw-normal">Recipient</dt>
            <dd class="col-7">{{ $notification->recipient?->name ?? '—' }}</dd>

            <dt class="col-5 text-muted fw-normal">Sent</dt>
            <dd class="col-7">{{ $notification->created_at->format('d M Y H:i') }}</dd>

            <dt class="col-5 text-muted fw-normal">Read at</dt>
            <dd class="col-7">{{ $notification->read_at?->format('d M Y H:i') ?? 'Not yet' }}</dd>
        </dl>
    </x-page-card>
</x-app-layout>