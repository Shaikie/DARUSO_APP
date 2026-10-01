<!-- Sidebar -->
<div class="bg-white border-end" id="sidebar-wrapper" style="min-height: 100vh; width: 250px;">
    <div class="p-3 border-bottom">
        <h4 class="mb-0 fw-bold text-primary">
            <i class="bi bi-megaphone-fill me-2"></i>DARUSO
        </h4>
    </div>
    <div class="list-group list-group-flush">
        @if(auth()->user()->isLeader())
            <a href="{{ route('leader.dashboard') }}" class="list-group-item list-group-item-action {{ request()->routeIs('leader.dashboard') ? 'active' : '' }}">
                <i class="bi bi-speedometer2 me-2"></i>Dashboard
            </a>
            <a href="{{ route('leader.announcements.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('leader.announcements.*') ? 'active' : '' }}">
                <i class="bi bi-megaphone me-2"></i>Announcements
            </a>
            <a href="{{ route('leader.notifications.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('leader.notifications.*') ? 'active' : '' }}">
                <i class="bi bi-bell me-2"></i>Notifications
            </a>
            <a href="{{ route('leader.students.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('leader.students.*') ? 'active' : '' }}">
                <i class="bi bi-people me-2"></i>Students
            </a>
            <a href="{{ route('leader.complaints.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('leader.complaints.*') ? 'active' : '' }}">
                <i class="bi bi-exclamation-triangle me-2"></i>Complaints
            </a>
            <a href="{{ route('leader.meetings.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('leader.meetings.*') ? 'active' : '' }}">
                <i class="bi bi-calendar-event me-2"></i>Meetings
            </a>
            <a href="{{ route('leader.events.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('leader.events.*') ? 'active' : '' }}">
                <i class="bi bi-calendar-check me-2"></i>Events
            </a>
            <a href="{{ route('leader.documents.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('leader.documents.*') ? 'active' : '' }}">
                <i class="bi bi-file-earmark-text me-2"></i>Documents
            </a>
            <a href="{{ route('leader.ministries.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('leader.ministries.*') ? 'active' : '' }}">
                <i class="bi bi-building me-2"></i>Ministries
            </a>
            <a href="{{ route('leader.committees.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('leader.committees.*') ? 'active' : '' }}">
                <i class="bi bi-people-fill me-2"></i>Committees
            </a>
            <a href="{{ route('leader.leadership.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('leader.leadership.*') ? 'active' : '' }}">
                <i class="bi bi-person-badge me-2"></i>Leadership
            </a>
            <a href="{{ route('leader.reports.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('leader.reports.*') ? 'active' : '' }}">
                <i class="bi bi-bar-chart me-2"></i>Reports
            </a>
            <a href="{{ route('leader.audit-logs.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('leader.audit-logs.*') ? 'active' : '' }}">
                <i class="bi bi-journal-text me-2"></i>Audit Logs
            </a>
        @else
            <a href="{{ route('student.dashboard') }}" class="list-group-item list-group-item-action {{ request()->routeIs('student.dashboard') ? 'active' : '' }}">
                <i class="bi bi-speedometer2 me-2"></i>Dashboard
            </a>
            <a href="{{ route('student.announcements.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('student.announcements.*') ? 'active' : '' }}">
                <i class="bi bi-megaphone me-2"></i>Announcements
            </a>
            <a href="{{ route('student.notifications.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('student.notifications.*') ? 'active' : '' }}">
                <i class="bi bi-bell me-2"></i>Notifications
            </a>
            <a href="{{ route('student.events.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('student.events.*') ? 'active' : '' }}">
                <i class="bi bi-calendar-check me-2"></i>Events
            </a>
            <a href="{{ route('student.meetings.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('student.meetings.*') ? 'active' : '' }}">
                <i class="bi bi-calendar-event me-2"></i>Meetings
            </a>
            <a href="{{ route('student.complaints.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('student.complaints.*') ? 'active' : '' }}">
                <i class="bi bi-exclamation-triangle me-2"></i>Complaints
            </a>
            <a href="{{ route('student.documents.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('student.documents.*') ? 'active' : '' }}">
                <i class="bi bi-file-earmark-text me-2"></i>Documents
            </a>
            <a href="{{ route('student.representatives.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('student.representatives.*') ? 'active' : '' }}">
                <i class="bi bi-person-badge me-2"></i>Representatives
            </a>
            <a href="{{ route('student.profile') }}" class="list-group-item list-group-item-action {{ request()->routeIs('student.profile') ? 'active' : '' }}">
                <i class="bi bi-person me-2"></i>Profile
            </a>
        @endif
    </div>
</div>
