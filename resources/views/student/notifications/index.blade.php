<x-app-layout>
    @section('title', 'Notifications')
    @section('heading', 'Notifications')
    @section('subheading', $unreadCount > 0
        ? $unreadCount.' unread notification'.($unreadCount === 1 ? '' : 's')
        : 'You are all caught up.')

    @section('actions')
        @if ($unreadCount > 0)
            <form method="POST" action="{{ route('student.notifications.read-all') }}">
                @csrf
                <button class="btn btn-outline-primary">
                    <i class="bi bi-check2-all me-1"></i>Mark all as read
                </button>
            </form>
        @endif
    @endsection

    <div class="d-flex flex-wrap gap-2 mb-4">
        <a href="{{ route('student.notifications.index') }}"
           class="btn btn-sm {{ request()->boolean('unread') ? 'btn-outline-secondary' : 'btn-primary' }}">
            All
        </a>
        <a href="{{ route('student.notifications.index', ['unread' => 1]) }}"
           class="btn btn-sm {{ request()->boolean('unread') ? 'btn-primary' : 'btn-outline-secondary' }}">
            Unread only
        </a>
    </div>

    <x-page-card icon="bell" title="Your inbox">
        @if ($notifications->isEmpty())
            <x-empty-state icon="bell"
                           title="No notifications"
                           description="Messages sent to you by DARUSO leadership will appear here." />
        @else
            <div class="list-group list-group-flush">
                @foreach ($notifications as $notification)
                    <div class="list-group-item px-0 py-3 {{ $notification->isRead() ? '' : 'bg-primary-subtle' }}">
                        <div class="d-flex justify-content-between align-items-start gap-2 mb-1">
                            <a href="{{ route('student.notifications.show', $notification) }}"
                               class="fw-semibold text-decoration-none">
                                {{ $notification->title }}
                                @unless ($notification->isRead())
                                    <span class="badge text-bg-danger ms-1">New</span>
                                @endunless
                            </a>
                            <x-priority-badge :priority="$notification->priority" />
                        </div>

                        <p class="mb-1 small text-muted">{{ $notification->message }}</p>

                        <div class="small text-muted">
                            {{ $notification->sender?->name ?? 'DARUSO' }}
                            · {{ $notification->created_at->diffForHumans() }}
                        </div>

                        @unless ($notification->isRead())
                            <form method="POST" class="mt-2"
                                  action="{{ route('student.notifications.markAsRead', $notification) }}">
                                @csrf
                                <button class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-check2 me-1"></i>Mark as read
                                </button>
                            </form>
                        @endif
                    </div>
                @endforeach
            </div>

            @if ($notifications->hasPages())
                <div class="mt-3">{{ $notifications->links() }}</div>
            @endif
        @endif
    </x-page-card>
</x-app-layout>