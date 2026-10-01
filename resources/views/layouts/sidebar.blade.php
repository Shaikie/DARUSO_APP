{{--
    Permission-aware sidebar navigation.

    Every entry is hidden when the user lacks the permission, but hiding is a
    usability measure only: the matching routes are still protected by policies
    and middleware, so a direct request cannot bypass this.
--}}
@php($user = auth()->user())
@php($isLeader = $user->isLeader())

<div class="bg-white border-end d-flex flex-column" id="sidebar-wrapper">
    <div class="p-3 border-bottom">
        <a href="{{ $isLeader ? route('leader.dashboard') : route('student.dashboard') }}"
           class="text-decoration-none d-flex align-items-center gap-2">
            <i class="bi bi-megaphone-fill fs-4 text-primary"></i>
            <span class="fw-bold fs-5 text-dark">DARUSO</span>
        </a>
    </div>

    <nav class="nav flex-column py-2 flex-grow-1 overflow-auto">
        @if ($isLeader)
            {{-- Each entry: [href route, active pattern, icon, label, permission]. --}}
            @php($leaderLinks = [
                ['leader.dashboard', 'leader.dashboard', 'speedometer2', 'Dashboard', null],
                ['leader.announcements.index', 'leader.announcements.*', 'megaphone', 'Announcements', 'announcement.create'],
                ['leader.notifications.index', 'leader.notifications.*', 'bell', 'Notifications', 'notification.create'],
                ['leader.students.index', 'leader.students.*', 'people', 'Students', 'student.view'],
                ['leader.complaints.index', 'leader.complaints.*', 'exclamation-triangle', 'Complaints', 'complaint.view'],
                ['leader.meetings.index', 'leader.meetings.*', 'calendar-event', 'Meetings', 'meeting.create'],
                ['leader.events.index', 'leader.events.*', 'calendar-check', 'Events', 'event.create'],
                ['leader.documents.index', 'leader.documents.*', 'file-earmark-text', 'Documents', null],
                ['leader.ministries.index', 'leader.ministries.*', 'building', 'Ministries', null],
                ['leader.committees.index', 'leader.committees.*', 'people-fill', 'Committees', null],
                ['leader.leadership.index', 'leader.leadership.*', 'person-badge', 'Leadership', null],
                ['leader.leadership-terms.index', 'leader.leadership-terms.*', 'calendar-range', 'Leadership Terms', 'term.manage'],
                ['leader.positions.index', 'leader.positions.*', 'diagram-3', 'Positions', 'position.manage'],
                ['leader.reports.index', 'leader.reports.*', 'bar-chart', 'Reports', 'report.view'],
                ['leader.audit-logs.index', 'leader.audit-logs.*', 'journal-text', 'Audit Logs', 'audit.view'],
                ['leader.roles.index', 'leader.roles.*', 'shield-lock', 'Roles & Permissions', 'role.manage'],
                ['leader.settings.index', 'leader.settings.*', 'gear', 'Settings', 'system.settings'],
            ])

            @foreach ($leaderLinks as [$href, $pattern, $icon, $label, $permission])
                @if (! $permission || $user->can($permission))
                    <a href="{{ route($href) }}"
                       class="nav-link daruso-sidebar-link {{ request()->routeIs($pattern) ? 'active fw-semibold bg-primary-subtle' : 'text-body' }}">
                        <i class="bi bi-{{ $icon }}"></i>{{ $label }}
                    </a>
                @endif
            @endforeach
        @else
            @php($studentLinks = [
                ['student.dashboard', 'student.dashboard', 'speedometer2', 'Dashboard'],
                ['student.announcements.index', 'student.announcements.*', 'megaphone', 'Announcements'],
                ['student.notifications.index', 'student.notifications.*', 'bell', 'Notifications'],
                ['student.events.index', 'student.events.*', 'calendar-check', 'Events'],
                ['student.meetings.index', 'student.meetings.*', 'calendar-event', 'Meetings'],
                ['student.complaints.index', 'student.complaints.*', 'exclamation-triangle', 'Complaints'],
                ['student.documents.index', 'student.documents.*', 'file-earmark-text', 'Documents'],
                ['student.representatives.index', 'student.representatives.*', 'person-badge', 'Representatives'],
                ['profile.edit', 'profile.edit', 'person', 'Profile'],
            ])

            @foreach ($studentLinks as [$href, $pattern, $icon, $label])
                <a href="{{ route($href) }}"
                   class="nav-link daruso-sidebar-link {{ request()->routeIs($pattern) ? 'active fw-semibold bg-primary-subtle' : 'text-body' }}">
                    <i class="bi bi-{{ $icon }}"></i>{{ $label }}
                </a>
            @endforeach
        @endif
    </nav>

    <div class="p-3 border-top small text-muted">
        @if ($isLeader)
            <i class="bi bi-shield-check me-1"></i>Leadership access
        @else
            <i class="bi bi-mortarboard me-1"></i>Student access
        @endif
    </div>
</div>