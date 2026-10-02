<x-app-layout>
    @section('title', 'Leadership dashboard')
    @section('eyebrow', 'LEADERSHIP')
    @section('heading', 'Good morning, '.\Illuminate\Support\Str::before(auth()->user()->name, ' '))
    @section('subheading', 'A clear operational view of student issues, communication and community activity.')

    <div class="daruso-leader-hero mb-4">
        <div><span class="daruso-kicker">LEADERSHIP CONTROL CENTRE</span><h2>Keep the student community informed.</h2><p>Monitor issues, publish useful information and stay on top of organisational activity from one workspace.</p></div>
        <div class="d-flex flex-wrap gap-2">
            @can('create', App\Models\Post::class)<a href="{{ route('leader.posts.create') }}" class="btn btn-light"><i class="bi bi-pencil-square me-1"></i>Write a post</a>@endcan
            @can('create', App\Models\Announcement::class)<a href="{{ route('leader.announcements.create') }}" class="btn btn-outline-light"><i class="bi bi-megaphone me-1"></i>Announcement</a>@endcan
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-3"><x-stat-card icon="inbox" label="Open complaints" :value="$stats['open_complaints']" :href="route('leader.complaints.index')" tone="warning" /></div>
        <div class="col-6 col-xl-3"><x-stat-card icon="person-exclamation" label="Unassigned" :value="$stats['unassigned_complaints']" :href="route('leader.complaints.index')" tone="danger" /></div>
        <div class="col-6 col-xl-3"><x-stat-card icon="people" label="Students" :value="$stats['total_students']" :href="route('leader.students.index')" tone="primary" /></div>
        <div class="col-6 col-xl-3"><x-stat-card icon="person-badge" label="Active leaders" :value="$stats['active_leaders']" :href="route('leader.leadership.index')" tone="info" /></div>
    </div>

    <div class="daruso-section-heading"><div><span class="daruso-eyebrow">OPERATIONS</span><h2>What needs attention</h2></div></div>
    <div class="row g-4">
        <div class="col-12 col-xl-7">
            <section class="daruso-panel">
                <div class="daruso-panel-head"><h3><i class="bi bi-life-preserver"></i> Recent complaints</h3><a href="{{ route('leader.complaints.index') }}">View all</a></div>
                @forelse($recentComplaints as $complaint)
                    <a class="daruso-list-item" href="{{ route('leader.complaints.show',$complaint) }}">
                        <span class="daruso-list-icon warning"><i class="bi bi-exclamation-triangle"></i></span>
                        <span><strong>{{ $complaint->title }}</strong><small>{{ $complaint->creator?->name }} · {{ $complaint->assignedMinistry?->name ?? 'Unassigned' }} · {{ $complaint->created_at->diffForHumans() }}</small></span>
                        <x-status-badge :status="$complaint->status" />
                    </a>
                @empty
                    <div class="daruso-empty py-4"><i class="bi bi-check2-circle"></i><h3>No complaints in your remit</h3><p>You're all clear for now.</p></div>
                @endforelse
            </section>

            <section class="daruso-panel mt-4">
                <div class="daruso-panel-head"><h3><i class="bi bi-megaphone"></i> Recent announcements</h3><a href="{{ route('leader.announcements.index') }}">Manage</a></div>
                @forelse($recentAnnouncements as $announcement)
                    <a class="daruso-list-item" href="{{ route('leader.announcements.show',$announcement) }}">
                        <span class="daruso-list-icon"><i class="bi bi-megaphone"></i></span>
                        <span><strong>{{ $announcement->title }}</strong><small>{{ $announcement->author?->name }} · {{ $announcement->published_at?->diffForHumans() ?? 'Draft' }}</small></span>
                        <x-status-badge :status="$announcement->status" />
                    </a>
                @empty
                    <p class="text-muted small mb-0">No announcements yet.</p>
                @endforelse
            </section>
        </div>

        <div class="col-12 col-xl-5">
            <section class="daruso-panel">
                <div class="daruso-panel-head"><h3><i class="bi bi-calendar-event"></i> Upcoming meetings</h3><a href="{{ route('leader.meetings.index') }}">View all</a></div>
                @forelse($upcomingMeetings as $meeting)
                    <a class="daruso-event-item" href="{{ route('leader.meetings.show',$meeting) }}">
                        <span class="daruso-date-chip"><strong>{{ $meeting->meeting_date->format('d') }}</strong><small>{{ $meeting->meeting_date->format('M') }}</small></span>
                        <span><strong>{{ $meeting->title }}</strong><small>{{ $meeting->meeting_time->format('H:i') }} · {{ $meeting->venue }}</small></span>
                    </a>
                @empty
                    <p class="text-muted small mb-0">No upcoming meetings.</p>
                @endforelse
            </section>

            <section class="daruso-panel mt-4">
                <div class="daruso-panel-head"><h3><i class="bi bi-activity"></i> Recent activity</h3>@can('audit.view')<a href="{{ route('leader.audit-logs.index') }}">Audit log</a>@endcan</div>
                @forelse($recentActivity as $entry)
                    <div class="daruso-activity-item"><span class="daruso-activity-dot"></span><div><strong>{{ str_replace('_',' ', $entry->action) }}</strong><small>{{ $entry->target_type ? \Illuminate\Support\Str::headline(class_basename($entry->target_type)).' '.$entry->target_id : 'System' }} @if($entry->actor) · {{ $entry->actor->name }} @endif</small></div><time>{{ $entry->created_at?->diffForHumans() }}</time></div>
                @empty
                    <p class="text-muted small mb-0">No recorded activity yet.</p>
                @endforelse
            </section>
        </div>
    </div>

    <section class="daruso-panel mt-4">
        <div class="daruso-panel-head"><h3><i class="bi bi-newspaper"></i> Community posts</h3><a href="{{ route('leader.posts.index') }}">Manage posts</a></div>
        <div class="row g-3">
            @forelse($recentPosts as $post)
                <div class="col-12 col-md-6 col-xl-3">
                    <a class="daruso-mini-post" href="{{ route('student.posts.show',$post) }}">
                        @if($post->cover_image_url)<img src="{{ $post->cover_image_url }}" alt="" loading="lazy">@else<div class="daruso-mini-post-placeholder"><i class="bi bi-newspaper"></i></div>@endif
                        <div><strong>{{ $post->title }}</strong><small>{{ $post->likes_count }} likes · {{ $post->comments_count }} comments</small></div>
                    </a>
                </div>
            @empty
                <p class="text-muted small mb-0">No posts yet.</p>
            @endforelse
        </div>
    </section>
</x-app-layout>