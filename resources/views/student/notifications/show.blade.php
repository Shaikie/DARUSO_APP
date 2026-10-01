<x-app-layout>
    @section('title', $notification->title)
    @section('heading', $notification->title)
    @section('subheading', 'Notification from '.($notification->sender?->name ?? 'DARUSO'))

    @section('actions')
        <a href="{{ route('student.notifications.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Back
        </a>
    @endsection

    <div class="row g-4">
        <div class="col-12 col-lg-8">
            <x-page-card icon="bell" title="Message">
                <div class="d-flex flex-wrap gap-2 mb-3">
                    <x-priority-badge :priority="$notification->priority" />
                    @if ($notification->isRead())
                        <span class="badge text-bg-success">Read</span>
                    @else
                        <span class="badge text-bg-danger">Unread</span>
                    @endif
                </div>

                <div class="communication-body fs-6">{{ $notification->message }}</div>

                @if ($notification->related)
                    <hr>
                    <p class="mb-0">
                        <i class="bi bi-link-45deg me-1"></i>Related:
                        <span class="text-capitalize">{{ class_basename($notification->related_type) }}</span>
                        #{{ $notification->related_id }}
                    </p>
                @endif
            </x-page-card>
        </div>

        <div class="col-12 col-lg-4">
            <x-page-card icon="info-circle" title="Details">
                <dl class="row mb-0 small">
                    <dt class="col-5 text-muted fw-normal">From</dt>
                    <dd class="col-7">{{ $notification->sender?->name ?? 'DARUSO' }}</dd>

                    <dt class="col-5 text-muted fw-normal">Received</dt>
                    <dd class="col-7">{{ $notification->created_at->format('d M Y H:i') }}</dd>

                    <dt class="col-5 text-muted fw-normal">Read at</dt>
                    <dd class="col-7">{{ $notification->read_at?->format('d M Y H:i') ?? 'Not yet' }}</dd>
                </dl>
            </x-page-card>
        </div>
    </div>
</x-app-layout>