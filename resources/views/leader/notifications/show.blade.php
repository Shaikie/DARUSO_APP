<x-app-layout>
    @section('title', $notification->title)
    @section('heading', $notification->title)
    @section('subheading', 'Notification to '.($notification->recipient?->name ?? 'recipient'))

    @section('actions')
        <a href="{{ route('leader.notifications.index') }}" class="btn btn-outline-secondary">
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
                        <span class="badge text-bg-secondary">Unread</span>
                    @endif
                </div>

                <div class="communication-body">{{ $notification->message }}</div>

                @if ($notification->related)
                    <hr>
                    <p class="mb-0 small">
                        <i class="bi bi-link-45deg me-1"></i>Related:
                        {{ class_basename($notification->related_type) }} #{{ $notification->related_id }}
                    </p>
                @endif
            </x-page-card>

            <x-page-card class="mt-4" icon="bullseye" title="Targeting">
                @forelse ($notification->audienceRules as $rule)
                    <div class="mb-1">
                        <i class="bi bi-dot me-1"></i>{{ $rule->type()->label() }}{{ $rule->audience_value ? ': '.$rule->audience_value : '' }}
                    </div>
                @empty
                    <p class="text-muted small mb-0">
                        This notification was sent to an individual recipient, so no audience
                        rules were recorded.
                    </p>
                @endforelse
            </x-page-card>
        </div>

        <div class="col-12 col-lg-4">
            <x-page-card icon="info-circle" title="Details">
                <dl class="row mb-0 small">
                    <dt class="col-5 text-muted fw-normal">Recipient</dt>
                    <dd class="col-7">{{ $notification->recipient?->name ?? '—' }}</dd>

                    <dt class="col-5 text-muted fw-normal">Sent</dt>
                    <dd class="col-7">{{ $notification->created_at->format('d M Y H:i') }}</dd>

                    <dt class="col-5 text-muted fw-normal">Read at</dt>
                    <dd class="col-7">{{ $notification->read_at?->format('d M Y H:i') ?? 'Not yet' }}</dd>
                </dl>
            </x-page-card>

            <x-page-card class="mt-4" icon="info-circle" title="Editing">
                <p class="small text-muted mb-0">
                    Delivered notifications cannot be edited or deleted — recipients may
                    already have acted on the original wording.
                </p>
            </x-page-card>
        </div>
    </div>
</x-app-layout>