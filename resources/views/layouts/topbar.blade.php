@php($user = auth()->user())
@php($unreadCount = $user->receivedNotifications()->whereNull('read_at')->count())
@php($notificationsRoute = $user->isLeader() ? route('leader.notifications.index') : route('student.notifications.index'))
@php($dashboardRoute = $user->hasRole(\App\Enums\RoleName::Administrator) ? route('leader.dashboard') : route('community'))

<header class="daruso-topbar">
    <div class="d-flex align-items-center gap-2 min-w-0">
        <button class="daruso-menu-button d-lg-none" id="sidebar-toggle" type="button" aria-label="Open navigation">
            <i class="bi bi-list"></i>
        </button>
        <a href="{{ $dashboardRoute }}" class="daruso-mobile-brand d-lg-none">DARUSO</a>
        <div class="daruso-topbar-context d-none d-lg-block">
            <span>{{ $user->hasRole(\App\Enums\RoleName::Administrator) ? 'Administration workspace' : 'DARUSO Community' }}</span>
            <strong>@yield('heading', 'Dashboard')</strong>
        </div>
    </div>

    <div class="daruso-topbar-actions">
        <form method="GET" action="{{ route('search') }}" class="d-none d-md-flex align-items-center">
            <label class="visually-hidden" for="daruso-global-search">Search DARUSO</label>
            <div class="input-group input-group-sm">
                <span class="input-group-text border-end-0"><i class="bi bi-search"></i></span>
                <input id="daruso-global-search" type="search" name="q" value="{{ request('q') }}" class="form-control border-start-0" placeholder="Search DARUSO..." style="width:190px">
            </div>
        </form>
        <a href="{{ route('notification-preferences.edit') }}" class="daruso-icon-button" aria-label="Notification preferences"><i class="bi bi-sliders"></i></a>
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
