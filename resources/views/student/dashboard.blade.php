<x-app-layout>
    @section('title', 'Home')
    @section('eyebrow', 'DARUSO COMMUNITY')
    @section('heading', 'Welcome back, '.\Illuminate\Support\Str::before($user->name, ' '))
    @section('subheading', 'Stay informed, follow community stories and keep up with what is happening around you.')

    @section('actions')
        @can('create', App\Models\Complaint::class)
            <a href="{{ route('student.complaints.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i>Raise a complaint
            </a>
        @endcan
    @endsection

    <section class="daruso-welcome-banner mb-4">
        <div>
            <span class="daruso-kicker">YOUR CAMPUS, YOUR VOICE</span>
            <h2>Everything important, in one place.</h2>
            <p>Read leadership updates, discover community posts, follow events and track the issues you have raised.</p>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('student.posts.index') }}" class="btn btn-light"><i class="bi bi-newspaper me-1"></i>Explore community</a>
                <a href="{{ route('student.announcements.index') }}" class="btn btn-outline-light">View announcements</a>
            </div>
        </div>
        <div class="daruso-welcome-art"><i class="bi bi-broadcast-pin"></i></div>
    </section>

    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-3"><x-stat-card icon="bell" label="Unread notifications" :value="$unreadNotifications" :href="route('student.notifications.index')" tone="danger" /></div>
        <div class="col-6 col-xl-3"><x-stat-card icon="hourglass-split" label="Open complaints" :value="$openComplaints" :href="route('student.complaints.index')" tone="warning" /></div>
        <div class="col-6 col-xl-3"><x-stat-card icon="calendar-event" label="Upcoming events" :value="$events->count()" :href="route('student.events.index')" tone="info" /></div>
        <div class="col-6 col-xl-3"><x-stat-card icon="file-earmark-text" label="Available documents" :value="$documentCount" :href="route('student.documents.index')" tone="primary" /></div>
    </div>

    <div class="daruso-section-heading">
        <div><span class="daruso-eyebrow">COMMUNITY</span><h2>Latest from DARUSO</h2></div>
        <a href="{{ route('student.posts.index') }}">View all <i class="bi bi-arrow-right"></i></a>
    </div>

    <div class="row g-4">
        <div class="col-12 col-xl-8">
            <div class="daruso-feed">
                @forelse ($recentPosts as $post)
                    <article class="daruso-post-card">
                        <div class="daruso-post-head">
                            <div class="daruso-avatar">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($post->author?->name ?? 'D', 0, 1)) }}</div>
                            <div class="min-w-0">
                                <strong>{{ $post->author?->name ?? 'DARUSO' }}</strong>
                                <small>Published {{ $post->published_at?->diffForHumans() }}</small>
                            </div>
                            <span class="daruso-post-label ms-auto">COMMUNITY</span>
                        </div>

                        @if($post->cover_image_url)
                            <a href="{{ route('student.posts.show', $post) }}" class="daruso-post-cover">
                                <img src="{{ $post->cover_image_url }}" alt="{{ $post->title }}" loading="lazy">
                            </a>
                        @endif

                        <div class="daruso-post-body">
                            <h3><a href="{{ route('student.posts.show', $post) }}">{{ $post->title }}</a></h3>
                            <p>{{ $post->excerpt ?: \Illuminate\Support\Str::limit(strip_tags($post->content), 190) }}</p>
                            <div class="daruso-post-meta">
                                <span><i class="bi bi-heart"></i>{{ $post->likes_count }} likes</span>
                                <span><i class="bi bi-chat"></i>{{ $post->comments_count }} comments</span>
                                <a href="{{ route('student.posts.show', $post) }}">Read story <i class="bi bi-arrow-up-right"></i></a>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="daruso-empty"><i class="bi bi-newspaper"></i><h3>No community posts yet</h3><p>Leadership stories and useful updates will appear here.</p></div>
                @endforelse
            </div>
        </div>

        <div class="col-12 col-xl-4">
            <div class="daruso-side-stack">
                <section class="daruso-panel">
                    <div class="daruso-panel-head"><h3><i class="bi bi-megaphone"></i> Announcements</h3><a href="{{ route('student.announcements.index') }}">See all</a></div>
                    @forelse($announcements->take(4) as $announcement)
                        <a class="daruso-list-item" href="{{ route('student.announcements.show', $announcement) }}">
                            <span class="daruso-list-icon"><i class="bi bi-megaphone"></i></span>
                            <span><strong>{{ $announcement->title }}</strong><small>{{ $announcement->published_at?->diffForHumans() }}</small></span>
                        </a>
                    @empty
                        <p class="text-muted small mb-0">No announcements yet.</p>
                    @endforelse
                </section>

                <section class="daruso-panel">
                    <div class="daruso-panel-head"><h3><i class="bi bi-calendar-check"></i> Coming up</h3><a href="{{ route('student.events.index') }}">See all</a></div>
                    @forelse($events->take(3) as $event)
                        <a class="daruso-event-item" href="{{ route('student.events.show', $event) }}">
                            <span class="daruso-date-chip"><strong>{{ $event->event_date->format('d') }}</strong><small>{{ $event->event_date->format('M') }}</small></span>
                            <span><strong>{{ $event->title }}</strong><small>{{ $event->venue }}</small></span>
                        </a>
                    @empty
                        <p class="text-muted small mb-0">No upcoming events.</p>
                    @endforelse
                </section>

                <section class="daruso-panel">
                    <div class="daruso-panel-head"><h3><i class="bi bi-bell"></i> Notifications</h3><a href="{{ route('student.notifications.index') }}">See all</a></div>
                    @forelse($recentNotifications->take(3) as $notification)
                        <a class="daruso-list-item" href="{{ route('student.notifications.show', $notification) }}">
                            <span class="daruso-list-icon {{ $notification->isRead() ? '' : 'unread' }}"><i class="bi bi-dot"></i></span>
                            <span><strong>{{ $notification->title }}</strong><small>{{ \Illuminate\Support\Str::limit($notification->message, 60) }}</small></span>
                        </a>
                    @empty
                        <p class="text-muted small mb-0">You are all caught up.</p>
                    @endforelse
                </section>
            </div>
        </div>
    </div>

    <div class="daruso-section-heading mt-5">
        <div><span class="daruso-eyebrow">YOUR ACTIVITY</span><h2>Stay on top of things</h2></div>
    </div>
    <div class="row g-3">
        <div class="col-12 col-md-4"><a class="daruso-action-card" href="{{ route('student.complaints.index') }}"><span><i class="bi bi-life-preserver"></i></span><div><strong>My complaints</strong><small>Track issues you have submitted.</small></div><i class="bi bi-arrow-up-right ms-auto"></i></a></div>
        <div class="col-12 col-md-4"><a class="daruso-action-card" href="{{ route('student.meetings.index') }}"><span><i class="bi bi-calendar-event"></i></span><div><strong>Meetings</strong><small>See meetings you are invited to.</small></div><i class="bi bi-arrow-up-right ms-auto"></i></a></div>
        <div class="col-12 col-md-4"><a class="daruso-action-card" href="{{ route('student.documents.index') }}"><span><i class="bi bi-folder2-open"></i></span><div><strong>Documents</strong><small>Access shared DARUSO documents.</small></div><i class="bi bi-arrow-up-right ms-auto"></i></a></div>
    </div>
</x-app-layout>