@php($user = auth()->user())
@php($unreadCount = $user->receivedNotifications()->whereNull('read_at')->count())
@php($notificationsRoute = $user->isLeader() ? route('leader.notifications.index') : route('student.notifications.index'))
@php($dashboardRoute = $user->isLeader() ? route('leader.dashboard') : route('student.dashboard'))

<header class="daruso-topbar">
    <div class="d-flex align-items-center gap-2 min-w-0">
        <button class="daruso-menu-button d-lg-none" id="sidebar-toggle" type="button" aria-label="Open navigation">
            <i class="bi bi-list"></i>
        </button>
        <a href="{{ $dashboardRoute }}" class="daruso-mobile-brand d-lg-none">DARUSO</a>
        <div class="daruso-topbar-context d-none d-lg-block">
            <span>{{ $user->isLeader() ? 'Leadership workspace' : 'Student community' }}</span>
            <strong>@yield('heading', 'Dashboard')</strong>
        </div>
    </div>

    <div class="daruso-topbar-actions">
        <a href="{{ $notificationsRoute }}" class="daruso-icon-button position-relative" aria-label="Notifications">
            <i class="bi bi-bell"></i>
            @if ($unreadCount > 0)
                <span class="daruso-notification-dot">{{ $unreadCount > 99 ? '99+' : $unreadCount }}</span>
            @endif
        </a>

        <div class="dropdown">
            <button class="daruso-user-button dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                <span class="daruso-avatar daruso-avatar-sm">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($user->name, 0, 1)) }}</span>
                <span class="d-none d-md-flex flex-column text-start">
                    <strong>{{ $user->name }}</strong>
                    <small>{{ $user->isLeader() ? 'Leader' : 'Student' }}</small>
                </span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="bi bi-person me-2"></i>Profile</a></li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="dropdown-item text-danger"><i class="bi bi-box-arrow-right me-2"></i>Logout</button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</header>