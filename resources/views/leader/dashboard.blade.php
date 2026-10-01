<x-app-layout>
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold">Leader Dashboard</h2>
            <span class="text-muted">{{ now()->format('l, F j, Y') }}</span>
        </div>

        <!-- Welcome Section -->
        <div class="card mb-4 border-0 shadow-sm">
            <div class="card-body">
                <h4 class="card-title">Welcome, {{ auth()->user()->name }}!</h4>
                <p class="card-text text-muted">Manage communication, complaints, and coordination from your dashboard.</p>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <i class="bi bi-megaphone-fill fs-1 text-primary"></i>
                        <h5 class="mt-2 mb-1">{{ \App\Models\Announcement::where('status', 'published')->count() }}</h5>
                        <p class="text-muted mb-0">Published Announcements</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <i class="bi bi-exclamation-triangle-fill fs-1 text-warning"></i>
                        <h5 class="mt-2 mb-1">{{ \App\Models\Complaint::whereIn('status', ['submitted', 'under_review', 'in_progress'])->count() }}</h5>
                        <p class="text-muted mb-0">Pending Complaints</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <i class="bi bi-calendar-event-fill fs-1 text-info"></i>
                        <h5 class="mt-2 mb-1">{{ \App\Models\Meeting::where('status', 'scheduled')->count() }}</h5>
                        <p class="text-muted mb-0">Scheduled Meetings</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <i class="bi bi-people-fill fs-1 text-success"></i>
                        <h5 class="mt-2 mb-1">{{ \App\Models\StudentProfile::where('status', 'active')->count() }}</h5>
                        <p class="text-muted mb-0">Active Students</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row mb-4">
            <div class="col-12">
                <h5 class="mb-3">Quick Actions</h5>
            </div>
            <div class="col-md-3 mb-3">
                <a href="{{ route('leader.announcements.create') }}" class="text-decoration-none">
                    <div class="card border-0 shadow-sm h-100 hover-shadow">
                        <div class="card-body text-center">
                            <i class="bi bi-plus-circle fs-1 text-primary"></i>
                            <h6 class="mt-2 mb-0 text-dark">New Announcement</h6>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-3 mb-3">
                <a href="{{ route('leader.complaints.index') }}" class="text-decoration-none">
                    <div class="card border-0 shadow-sm h-100 hover-shadow">
                        <div class="card-body text-center">
                            <i class="bi bi-exclamation-triangle fs-1 text-warning"></i>
                            <h6 class="mt-2 mb-0 text-dark">Manage Complaints</h6>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-3 mb-3">
                <a href="{{ route('leader.meetings.create') }}" class="text-decoration-none">
                    <div class="card border-0 shadow-sm h-100 hover-shadow">
                        <div class="card-body text-center">
                            <i class="bi bi-calendar-plus fs-1 text-info"></i>
                            <h6 class="mt-2 mb-0 text-dark">Schedule Meeting</h6>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-3 mb-3">
                <a href="{{ route('leader.notifications.create') }}" class="text-decoration-none">
                    <div class="card border-0 shadow-sm h-100 hover-shadow">
                        <div class="card-body text-center">
                            <i class="bi bi-bell fs-1 text-success"></i>
                            <h6 class="mt-2 mb-0 text-dark">Send Notification</h6>
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
                            $announcements = \App\Models\Announcement::latest()->limit(5)->get();
                        @endphp
                        @forelse($announcements as $announcement)
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h6 class="mb-1">{{ $announcement->title }}</h6>
                                    <small class="text-muted">{{ $announcement->created_at->diffForHumans() }}</small>
                                </div>
                                <span class="badge bg-{{ $announcement->status === 'published' ? 'success' : ($announcement->status === 'draft' ? 'secondary' : 'warning') }}">
                                    {{ ucfirst(str_replace('_', ' ', $announcement->status)) }}
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
                        <h6 class="mb-0 fw-bold">Pending Complaints</h6>
                    </div>
                    <div class="card-body">
                        @php
                            $complaints = \App\Models\Complaint::whereIn('status', ['submitted', 'under_review', 'in_progress'])
                                ->latest()
                                ->limit(5)
                                ->get();
                        @endphp
                        @forelse($complaints as $complaint)
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h6 class="mb-1">{{ $complaint->title }}</h6>
                                    <small class="text-muted">{{ $complaint->created_at->diffForHumans() }}</small>
                                </div>
                                <span class="badge bg-{{ $complaint->status === 'resolved' ? 'success' : 'warning' }}">
                                    {{ ucfirst(str_replace('_', ' ', $complaint->status)) }}
                                </span>
                            </div>
                        @empty
                            <p class="text-muted text-center mb-0">No pending complaints.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
