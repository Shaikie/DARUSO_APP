<x-app-layout>
    @section('title', 'Dashboard')
    @section('heading', 'Leadership dashboard')
    @section('subheading', 'Communication, complaints and organisational activity')

    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <x-stat-card icon="inbox" label="Open complaints" :value="$stats['open_complaints']"
                         :href="route('leader.complaints.index')" tone="warning" />
        </div>
        <div class="col-6 col-lg-3">
            <x-stat-card icon="person-exclamation" label="Unassigned" :value="$stats['unassigned_complaints']"
                         :href="route('leader.complaints.index')" tone="danger" />
        </div>
        <div class="col-6 col-lg-3">
            <x-stat-card icon="people" label="Students" :value="$stats['total_students']"
                         :href="route('leader.students.index')" tone="primary" />
        </div>
        <div class="col-6 col-lg-3">
            <x-stat-card icon="person-badge" label="Active leaders" :value="$stats['active_leaders']"
                         :href="route('leader.leadership.index')" tone="info" />
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <x-stat-card icon="calendar-event" label="Upcoming meetings" :value="$stats['upcoming_meetings']"
                         :href="route('leader.meetings.index')" tone="primary" />
        </div>
        <div class="col-6 col-lg-3">
            <x-stat-card icon="calendar-check" label="Upcoming events" :value="$stats['upcoming_events']"
                         :href="route('leader.events.index')" tone="success" />
        </div>
        <div class="col-6 col-lg-3">
            <x-stat-card icon="bell" label="Notifications sent" :value="$stats['notifications_sent']"
                         :href="route('leader.notifications.index')" tone="secondary" />
        </div>
        <div class="col-6 col-lg-3">
            <x-stat-card icon="bell-fill" label="My unread" :value="$unreadNotifications"
                         :href="route('leader.notifications.index')" tone="danger" />
        </div>
    </div>

    <div class="row g-4">
        <div class="col-12 col-lg-7">
            <x-page-card icon="exclamation-triangle" title="Recent complaints">
                <x-slot:actions>
                    <a href="{{ route('leader.complaints.index') }}" class="btn btn-sm btn-link">View all</a>
                </x-slot:actions>

                @forelse ($recentComplaints as $complaint)
                    <div class="d-flex justify-content-between align-items-start border-bottom py-2 gap-2">
                        <div>
                            <a href="{{ route('leader.complaints.show', $complaint) }}"
                               class="text-decoration-none fw-semibold">
                                {{ $complaint->title }}
                            </a>
                            <div class="small text-muted">
                                {{ $complaint->creator?->name }}
                                · {{ $complaint->assignedMinistry?->name ?? 'Unassigned' }}
                                · {{ $complaint->created_at->diffForHumans() }}
                            </div>
                        </div>
                        <x-status-badge :status="$complaint->status" />
                    </div>
                @empty
                    <x-empty-state icon="inbox" title="No complaints in your remit"
                                   description="Complaints routed to your ministry appear here." />
                @endforelse

                @if ($complaintsByStatus->isNotEmpty())
                    <hr>
                    <h3 class="h6 fw-semibold mb-2">By status</h3>
                    <div class="row g-2">
                        @foreach ($complaintsByStatus as $status => $count)
                            <div class="col-6 col-md-4 d-flex justify-content-between align-items-center">
                                <x-status-badge :status="\App\Enums\ComplaintStatus::from($status)" />
                                <span class="fw-semibold">{{ $count }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </x-page-card>

            <x-page-card class="mt-4" icon="megaphone" title="Recent announcements">
                <x-slot:actions>
                    @can('create', App\Models\Announcement::class)
                        <a href="{{ route('leader.announcements.create') }}" class="btn btn-sm btn-primary">
                            <i class="bi bi-plus-lg me-1"></i>New
                        </a>
                    @endcan
                </x-slot:actions>

                @forelse ($recentAnnouncements as $announcement)
                    <div class="d-flex justify-content-between align-items-start border-bottom py-2 gap-2">
                        <div>
                            <a href="{{ route('leader.announcements.show', $announcement) }}"
                               class="text-decoration-none fw-semibold">
                                {{ $announcement->title }}
                            </a>
                            <div class="small text-muted">
                                {{ $announcement->author?->name }}
                                @if ($announcement->published_at)
                                    · published {{ $announcement->published_at->diffForHumans() }}
                                @endif
                            </div>
                        </div>
                        <div class="d-flex gap-1">
                            <x-priority-badge :priority="$announcement->priority" />
                            <x-status-badge :status="$announcement->status" />
                        </div>
                    </div>
                @empty
                    <x-empty-state icon="megaphone" title="No announcements"
                                   description="Create the first announcement for your audience." />
                @endforelse
            </x-page-card>
        </div>

        <div class="col-12 col-lg-5">
            <x-page-card icon="calendar-event" title="Upcoming meetings">
                <x-slot:actions>
                    <a href="{{ route('leader.meetings.index') }}" class="btn btn-sm btn-link">View all</a>
                </x-slot:actions>

                @forelse ($upcomingMeetings as $meeting)
                    <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                        <div>
                            <a href="{{ route('leader.meetings.show', $meeting) }}" class="text-decoration-none fw-semibold">
                                {{ $meeting->title }}
                            </a>
                            <div class="small text-muted">
                                {{ $meeting->meeting_date->format('d M Y') }}
                                at {{ $meeting->meeting_time->format('H:i') }}
                            </div>
                        </div>
                        <x-status-badge :status="$meeting->status" />
                    </div>
                @empty
                    <x-empty-state icon="calendar-event" title="No upcoming meetings"
                                   description="Schedule the next leadership meeting." />
                @endforelse
            </x-page-card>

            <x-page-card class="mt-4" icon="send" title="Recently sent notifications">
                <x-slot:actions>
                    <a href="{{ route('leader.notifications.index') }}" class="btn btn-sm btn-link">View all</a>
                </x-slot:actions>

                @forelse ($sentNotifications as $notification)
                    <div class="d-flex justify-content-between align-items-start border-bottom py-2 gap-2">
                        <div>
                            <span class="fw-semibold small">{{ $notification->title }}</span>
                            <div class="small text-muted">
                                to {{ $notification->recipient?->name }}
                                · {{ $notification->created_at->diffForHumans() }}
                            </div>
                        </div>
                        @if ($notification->isRead())
                            <span class="badge text-bg-success">Read</span>
                        @else
                            <span class="badge text-bg-secondary">Unread</span>
                        @endif
                    </div>
                @empty
                    <x-empty-state icon="send" title="No notifications sent"
                                   description="Send a targeted notification to students or leaders." />
                @endforelse
            </x-page-card>

            <x-page-card class="mt-4" icon="journal-text" title="Recent activity">
                <x-slot:actions>
                    @can('audit.view')
                        <a href="{{ route('leader.audit-logs.index') }}" class="btn btn-sm btn-link">Audit log</a>
                    @endcan
                </x-slot:actions>

                @forelse ($recentActivity as $entry)
                    <div class="d-flex justify-content-between align-items-start border-bottom py-2 gap-2">
                        <div>
                            <span class="small fw-semibold text-capitalize">{{ str_replace('_', ' ', $entry->action) }}</span>
                            <div class="small text-muted">
                                {{ $entry->target_type ? \Illuminate\Support\Str::headline(class_basename($entry->target_type)).' '.$entry->target_id : 'System' }}
                                @if ($entry->actor)
                                    · by {{ $entry->actor->name }}
                                @endif
                            </div>
                        </div>
                        <span class="small text-muted text-nowrap">{{ $entry->created_at?->diffForHumans() }}</span>
                    </div>
                @empty
                    <x-empty-state icon="journal-text" title="No recorded activity yet"
                                   description="Sensitive actions are logged here automatically." />
                @endforelse
            </x-page-card>
        </div>
    </div>
</x-app-layout>