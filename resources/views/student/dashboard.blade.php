<x-app-layout>
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold">Student Dashboard</h2>
            <span class="text-muted">{{ now()->format('l, F j, Y') }}</span>
        </div>

        <!-- Welcome Section -->
        <div class="card mb-4 border-0 shadow-sm">
            <div class="card-body">
                <h4 class="card-title">Welcome back, {{ auth()->user()->name }}!</h4>
                <p class="card-text text-muted">Stay updated with the latest announcements, events, and activities from DARUSO.</p>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <i class="bi bi-bell-fill fs-1 text-primary"></i>
                        <h5 class="mt-2 mb-1">{{ auth()->user()->notifications()->whereNull('read_at')->count() }}</h5>
                        <p class="text-muted mb-0">Unread Notifications</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <i class="bi bi-megaphone-fill fs-1 text-success"></i>
                        <h5 class="mt-2 mb-1">{{ \App\Models\Announcement::where('status', 'published')->count() }}</h5>
                        <p class="text-muted mb-0">Announcements</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <i class="bi bi-calendar-check-fill fs-1 text-info"></i>
                        <h5 class="mt-2 mb-1">{{ \App\Models\Event::where('status', 'upcoming')->count() }}</h5>
                        <p class="text-muted mb-0">Upcoming Events</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <i class="bi bi-exclamation-triangle-fill fs-1 text-warning"></i>
                        <h5 class="mt-2 mb-1">{{ auth()->user()->complaints()->count() }}</h5>
                        <p class="text-muted mb-0">My Complaints</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row mb-4">
            <div class="col-12">
                <h5 class="mb-3">Quick Actions</h5>
            </div>
            <div class="col-md-4 mb-3">
                <a href="{{ route('student.complaints.create') }}" class="text-decoration-none">
                    <div class="card border-0 shadow-sm h-100 hover-shadow">
                        <div class="card-body text-center">
                            <i class="bi bi-plus-circle fs-1 text-primary"></i>
                            <h6 class="mt-2 mb-0 text-dark">Submit a Complaint</h6>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-4 mb-3">
                <a href="{{ route('student.announcements.index') }}" class="text-decoration-none">
                    <div class="card border-0 shadow-sm h-100 hover-shadow">
                        <div class="card-body text-center">
                            <i class="bi bi-megaphone fs-1 text-success"></i>
                            <h6 class="mt-2 mb-0 text-dark">View Announcements</h6>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-4 mb-3">
                <a href="{{ route('student.representatives.index') }}" class="text-decoration-none">
                    <div class="card border-0 shadow-sm h-100 hover-shadow">
                        <div class="card-body text-center">
                            <i class="bi bi-people fs-1 text-info"></i>
                            <h6 class="mt-2 mb-0 text-dark">View Representatives</h6>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white">
                        <h6 class="mb-0 fw-bold">Recent Announcements</h6>
                    </div>
                    <div class="card-body">
                        @php
                            $announcements = \App\Models\Announcement::where('status', 'published')
                                ->latest()
                                ->limit(5)
                                ->get();
                        @endphp
                        @forelse($announcements as $announcement)
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h6 class="mb-1">{{ $announcement->title }}</h6>
                                    <small class="text-muted">{{ $announcement->published_at?->diffForHumans() }}</small>
                                </div>
                                <span class="badge bg-{{ $announcement->priority === 'urgent' ? 'danger' : ($announcement->priority === 'high' ? 'warning' : 'info') }}">
                                    {{ ucfirst($announcement->priority) }}
                                </span>
                            </div>
                        @empty
                            <p class="text-muted text-center mb-0">No announcements yet.</p>
                        @endforelse
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white">
                        <h6 class="mb-0 fw-bold">Upcoming Events</h6>
                    </div>
                    <div class="card-body">
                        @php
                            $events = \App\Models\Event::where('status', 'upcoming')
                                ->where('event_date', '>=', now())
                                ->orderBy('event_date')
                                ->limit(5)
                                ->get();
                        @endphp
                        @forelse($events as $event)
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h6 class="mb-1">{{ $event->title }}</h6>
                                    <small class="text-muted">{{ $event->event_date->format('M j, Y') }} at {{ $event->venue }}</small>
                                </div>
                            </div>
                        @empty
                            <p class="text-muted text-center mb-0">No upcoming events.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
