@php($user = auth()->user())
@php($isLeader = $user->isLeader())
@php($isAdmin = $user->hasRole(\App\Enums\RoleName::Administrator))

<aside class="daruso-sidebar" id="sidebar-wrapper" aria-label="Main navigation">
    <div class="daruso-brand">
        <a href="{{ $isAdmin ? route('leader.dashboard') : route('student.posts.index') }}" class="daruso-brand-link">
            <span class="daruso-brand-mark"><i class="bi bi-broadcast-pin"></i></span>
            <span>
                <strong>DARUSO</strong>
                <small>Student community</small>
            </span>
        </a>
        <button class="daruso-mobile-close d-lg-none" id="sidebar-close" type="button" aria-label="Close navigation">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>

    <div class="daruso-sidebar-profile">
        <div class="daruso-avatar">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($user->name, 0, 1)) }}</div>
        <div class="min-w-0">
            <div class="fw-semibold text-truncate">{{ $user->name }}</div>
            <small>{{ $isLeader ? 'DARUSO leadership' : 'Student' }}</small>
        </div>
    </div>

    <nav class="daruso-nav">
        <div class="daruso-nav-label">Workspace</div>

        @if ($isLeader)
            @php($leaderLinks = [
                ['leader.dashboard', 'leader.dashboard', 'speedometer2', 'Dashboard', null],
                ['leader.posts.index', 'leader.posts.*', 'newspaper', 'Community posts', 'post.create'],
                ['leader.announcements.index', 'leader.announcements.*', 'megaphone', 'Announcements', 'announcement.create'],
                ['leader.notifications.index', 'leader.notifications.*', 'bell', 'Notifications', 'notification.create'],
                ['leader.students.index', 'leader.students.*', 'people', 'Students', 'student.view'],
                ['leader.complaints.index', 'leader.complaints.*', 'life-preserver', 'Complaints', 'complaint.view'],
                ['leader.meetings.index', 'leader.meetings.*', 'calendar-event', 'Meetings', 'meeting.create'],
                ['leader.events.index', 'leader.events.*', 'calendar-check', 'Events', 'event.create'],
                ['leader.documents.index', 'leader.documents.*', 'folder2-open', 'Documents', null],
            ])
            @foreach ($leaderLinks as [$href, $pattern, $icon, $label, $permission])
                @if (! $permission || $user->can($permission))
                    <a href="{{ route($href) }}" class="daruso-nav-link {{ request()->routeIs($pattern) ? 'active' : '' }}">
                        <i class="bi bi-{{ $icon }}"></i><span>{{ $label }}</span>
                    </a>
                @endif
            @endforeach

            <div class="daruso-nav-label mt-3">Administration</div>
            @php($adminLinks = [
                ['leader.ministries.index', 'leader.ministries.*', 'building', 'Ministries'],
                ['leader.committees.index', 'leader.committees.*', 'diagram-3', 'Committees'],
                ['leader.leadership.index', 'leader.leadership.*', 'person-badge', 'Leadership'],
                ['leader.reports.index', 'leader.reports.*', 'bar-chart', 'Reports'],
                ['leader.audit-logs.index', 'leader.audit-logs.*', 'journal-text', 'Audit logs'],
                ['leader.settings.index', 'leader.settings.*', 'gear', 'Settings'],
            ])
            @foreach ($adminLinks as [$href, $pattern, $icon, $label])
                <a href="{{ route($href) }}" class="daruso-nav-link {{ request()->routeIs($pattern) ? 'active' : '' }}">
                    <i class="bi bi-{{ $icon }}"></i><span>{{ $label }}</span>
                </a>
            @endforeach
        @else
            @php($studentLinks = [
                ['student.dashboard', 'student.dashboard', 'house', 'Home'],
                ['student.posts.index', 'student.posts.*', 'newspaper', 'Community'],
                ['student.announcements.index', 'student.announcements.*', 'megaphone', 'Announcements'],
                ['student.notifications.index', 'student.notifications.*', 'bell', 'Notifications'],
                ['student.events.index', 'student.events.*', 'calendar-check', 'Events'],
                ['student.meetings.index', 'student.meetings.*', 'calendar-event', 'Meetings'],
                ['student.complaints.index', 'student.complaints.*', 'life-preserver', 'Complaints'],
                ['student.documents.index', 'student.documents.*', 'folder2-open', 'Documents'],
                ['student.representatives.index', 'student.representatives.*', 'people', 'Representatives'],
            ])
            @foreach ($studentLinks as [$href, $pattern, $icon, $label])
                <a href="{{ route($href) }}" class="daruso-nav-link {{ request()->routeIs($pattern) ? 'active' : '' }}">
                    <i class="bi bi-{{ $icon }}"></i><span>{{ $label }}</span>
                </a>
            @endforeach

            <div class="daruso-nav-label mt-3">Account</div>
            <a href="{{ route('profile.edit') }}" class="daruso-nav-link {{ request()->routeIs('profile.edit') ? 'active' : '' }}">
                <i class="bi bi-person-circle"></i><span>Profile</span>
            </a>
        @endif
    </nav>

    <div class="daruso-sidebar-footer">
        <div class="daruso-status-dot"></div>
        <span>{{ $isLeader ? 'Leadership workspace' : 'Connected to DARUSO' }}</span>
    </div>
</aside>