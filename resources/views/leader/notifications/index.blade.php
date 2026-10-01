<x-app-layout>
    @section('title', 'Notifications sent')
    @section('heading', 'Notifications')
    @section('subheading', 'Direct messages you have sent to students and leaders.')

    @section('actions')
        @can('create', App\Models\Notification::class)
            <a href="{{ route('leader.notifications.create') }}" class="btn btn-primary">
                <i class="bi bi-send me-1"></i>Send notification
            </a>
        @endcan
    @endsection

    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <x-stat-card icon="send" label="Sent" :value="$stats['total']" tone="primary" />
        </div>
        <div class="col-6 col-lg-3">
            <x-stat-card icon="check2-circle" label="Read" :value="$stats['read']" tone="success" />
        </div>
        <div class="col-6 col-lg-3">
            <x-stat-card icon="envelope" label="Unread"
                         :value="$stats['total'] - $stats['read']" tone="warning" />
        </div>
    </div>

    <x-page-card icon="bell" title="Sent notifications">
        @if ($notifications->isEmpty())
            <x-empty-state icon="send"
                           title="You have not sent any notifications"
                           description="Send a targeted message to specific people or to an audience.">
                @can('create', App\Models\Notification::class)
                    <x-slot:action>
                        <a href="{{ route('leader.notifications.create') }}" class="btn btn-sm btn-primary">
                            Send a notification
                        </a>
                    </x-slot:action>
                @endcan
            </x-empty-state>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">Message</th>
                            <th scope="col">Recipient</th>
                            <th scope="col">Priority</th>
                            <th scope="col">State</th>
                            <th scope="col">Sent</th>
                            <th scope="col" class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($notifications as $notification)
                            <tr>
                                <td>
                                    <a href="{{ route('leader.notifications.show', $notification) }}"
                                       class="fw-semibold text-decoration-none">
                                        {{ $notification->title }}
                                    </a>
                                    <div class="small text-muted">
                                        {{ \Illuminate\Support\Str::limit($notification->message, 80) }}
                                    </div>
                                </td>
                                <td class="small">{{ $notification->recipient?->name ?? '—' }}</td>
                                <td><x-priority-badge :priority="$notification->priority" /></td>
                                <td>
                                    @if ($notification->isRead())
                                        <span class="badge text-bg-success">Read</span>
                                    @else
                                        <span class="badge text-bg-secondary">Unread</span>
                                    @endif
                                </td>
                                <td class="small text-nowrap">{{ $notification->created_at->format('d M Y H:i') }}</td>
                                <td class="text-end">
                                    <a href="{{ route('leader.notifications.show', $notification) }}"
                                       class="btn btn-sm btn-outline-secondary">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($notifications->hasPages())
                <div class="mt-3">{{ $notifications->links() }}</div>
            @endif
        @endif
    </x-page-card>
</x-app-layout>