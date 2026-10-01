<x-app-layout>
    @section('title', 'Dashboard')
    @section('heading', 'Welcome, '.\Illuminate\Support\Str::before($user->name, ' '))
    @section('subheading', $profile ? $profile->programme.' · Year '.$profile->year_of_study : 'Student dashboard')

    @section('actions')
        @can('create', App\Models\Complaint::class)
            <a href="{{ route('student.complaints.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i>Submit complaint
            </a>
        @endcan
    @endsection

    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <x-stat-card icon="bell" label="Unread notifications" :value="$unreadNotifications"
                         :href="route('student.notifications.index')" tone="danger" />
        </div>
        <div class="col-6 col-lg-3">
            <x-stat-card icon="hourglass-split" label="Open complaints" :value="$openComplaints"
                         :href="route('student.complaints.index')" tone="warning" />
        </div>
        <div class="col-6 col-lg-3">
            <x-stat-card icon="file-earmark-text" label="Documents" :value="$documentCount"
                         :href="route('student.documents.index')" tone="info" />
        </div>
        <div class="col-6 col-lg-3">
            <x-stat-card icon="megaphone" label="Announcements" :value="$announcements->count()"
                         :href="route('student.announcements.index')" tone="primary" />
        </div>
    </div>

    <div class="row g-4">
        <div class="col-12 col-lg-7">
            <x-page-card icon="megaphone" title="Latest announcements">
                <x-slot:actions>
                    <a href="{{ route('student.announcements.index') }}" class="btn btn-sm btn-link">View all</a>
                </x-slot:actions>

                @forelse ($announcements as $announcement)
                    <div class="border-bottom pb-3 mb-3">
                        <div class="d-flex justify-content-between align-items-start gap-2">
                            <a href="{{ route('student.announcements.show', $announcement) }}"
                               class="fw-semibold text-decoration-none">
                                {{ $announcement->title }}
                            </a>
                            <x-priority-badge :priority="$announcement->priority" />
                        </div>
                        <p class="small text-muted mb-1">
                            {{ \Illuminate\Support\Str::limit($announcement->content, 160) }}
                        </p>
                        <div class="small text-muted">
                            {{ $announcement->author?->name }}
                            · {{ $announcement->published_at?->diffForHumans() }}
                        </div>
                    </div>
                @empty
                    <x-empty-state icon="megaphone" title="No announcements yet"
                                   description="Announcements addressed to you will appear here." />
                @endforelse
            </x-page-card>

            <x-page-card class="mt-4" icon="calendar-check" title="Upcoming events">
                <x-slot:actions>
                    <a href="{{ route('student.events.index') }}" class="btn btn-sm btn-link">View all</a>
                </x-slot:actions>

                @forelse ($events as $event)
                    <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                        <div>
                            <a href="{{ route('student.events.show', $event) }}" class="text-decoration-none fw-semibold">
                                {{ $event->title }}
                            </a>
                            <div class="small text-muted">
                                {{ $event->event_date->format('D, d M Y') }} · {{ $event->venue }}
                            </div>
                        </div>
                        <x-status-badge :status="$event->status" />
                    </div>
                @empty
                    <x-empty-state icon="calendar-check" title="No upcoming events"
                                   description="Events addressed to you will appear here." />
                @endforelse
            </x-page-card>

            <x-page-card class="mt-4" icon="calendar-event" title="Upcoming meetings">
                <x-slot:actions>
                    <a href="{{ route('student.meetings.index') }}" class="btn btn-sm btn-link">View all</a>
                </x-slot:actions>

                @forelse ($meetings as $meeting)
                    <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                        <div>
                            <a href="{{ route('student.meetings.show', $meeting) }}" class="text-decoration-none fw-semibold">
                                {{ $meeting->title }}
                            </a>
                            <div class="small text-muted">
                                {{ $meeting->meeting_date->format('D, d M Y') }}
                                at {{ $meeting->meeting_time->format('H:i') }} · {{ $meeting->venue }}
                            </div>
                        </div>
                        <x-status-badge :status="$meeting->status" />
                    </div>
                @empty
                    <x-empty-state icon="calendar-event" title="No upcoming meetings"
                                   description="Meetings you are invited to will appear here." />
                @endforelse
            </x-page-card>
        </div>

        <div class="col-12 col-lg-5">
            <x-page-card icon="bell" title="Recent notifications">
                <x-slot:actions>
                    <a href="{{ route('student.notifications.index') }}" class="btn btn-sm btn-link">View all</a>
                </x-slot:actions>

                @forelse ($recentNotifications as $notification)
                    <a href="{{ route('student.notifications.show', $notification) }}"
                       class="d-block text-decoration-none border-bottom py-2">
                        <div class="d-flex justify-content-between align-items-start gap-2">
                            <span class="fw-semibold small {{ $notification->isRead() ? 'text-muted' : '' }}">
                                {{ $notification->title }}
                            </span>
                            @unless ($notification->isRead())
                                <span class="badge text-bg-danger">New</span>
                            @endunless
                        </div>
                        <div class="small text-muted">
                            {{ \Illuminate\Support\Str::limit($notification->message, 70) }}
                        </div>
                    </a>
                @empty
                    <x-empty-state icon="bell" title="No notifications"
                                   description="Notifications from leadership will appear here." />
                @endforelse
            </x-page-card>

            <x-page-card class="mt-4" icon="exclamation-triangle" title="My complaints">
                <x-slot:actions>
                    <a href="{{ route('student.complaints.index') }}" class="btn btn-sm btn-link">View all</a>
                </x-slot:actions>

                @forelse ($complaintStats as $status => $count)
                    <div class="d-flex justify-content-between align-items-center py-1">
                        <x-status-badge :status="\App\Enums\ComplaintStatus::from($status)" />
                        <span class="fw-semibold">{{ $count }}</span>
                    </div>
                @empty
                    <x-empty-state icon="inbox" title="No complaints submitted"
                                   description="Raise a complaint and track its progress here.">
                        @can('create', App\Models\Complaint::class)
                            <x-slot:action>
                                <a href="{{ route('student.complaints.create') }}" class="btn btn-sm btn-primary">
                                    Submit a complaint
                                </a>
                            </x-slot:action>
                        @endcan
                    </x-empty-state>
                @endforelse
            </x-page-card>
        </div>
    </div>

    <x-page-card class="mt-4" icon="newspaper" title="Daily posts">
        <x-slot:actions><a href="{{ route('student.posts.index') }}" class="btn btn-sm btn-link">View all</a></x-slot:actions>
        @forelse($recentPosts as $post)
            <div class="d-flex justify-content-between align-items-start gap-3 border-bottom py-3">
                <div>
                    <a href="{{ route('student.posts.show', $post) }}" class="fw-semibold text-decoration-none">{{ $post->title }}</a>
                    <div class="small text-muted">{{ $post->author?->name }} · {{ $post->published_at?->diffForHumans() }}</div>
                    <div class="small text-muted mt-1">{{ \Illuminate\Support\Str::limit($post->excerpt ?: strip_tags($post->content), 100) }}</div>
                </div>
                <div class="small text-muted text-nowrap">{{ $post->likes_count }} likes · {{ $post->comments_count }} comments</div>
            </div>
        @empty
            <x-empty-state icon="newspaper" title="No daily posts" description="Leadership stories and community updates will appear here." />
        @endforelse
    </x-page-card>
</x-app-layout>