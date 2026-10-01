{{--
    Top navigation bar: sidebar toggle, notification bell, account menu.

    The unread count is computed in a single aggregate query rather than by
    loading notifications into memory.
--}}
@php($user = auth()->user())
@php($unreadCount = $user->receivedNotifications()->whereNull('read_at')->count())
@php($notificationsRoute = $user->isLeader() ? route('leader.notifications.index') : route('student.notifications.index'))

<nav class="navbar navbar-expand bg-white border-bottom sticky-top">
    <div class="container-fluid">
        <button class="btn btn-link text-body p-0 me-3" id="sidebar-toggle" type="button" aria-label="Toggle navigation">
            <i class="bi bi-list fs-3"></i>
        </button>

        <span class="navbar-text d-none d-md-inline text-muted small">
            {{ app(\App\Services\SettingService::class)->get('organisation_name', 'Daruso Students Organisation') }}
        </span>

        <div class="d-flex align-items-center gap-2 ms-auto">
            <div class="dropdown">
                <a href="{{ $notificationsRoute }}" class="btn btn-light position-relative" aria-label="Notifications">
                    <i class="bi bi-bell fs-5"></i>
                    @if ($unreadCount > 0)
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                            {{ $unreadCount > 99 ? '99+' : $unreadCount }}
                        </span>
                    @endif
                </a>
            </div>

            <div class="dropdown">
                <button class="btn btn-light dropdown-toggle d-flex align-items-center gap-2" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-person-circle fs-5"></i>
                    <span class="d-none d-md-inline">{{ $user->name }}</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        <a class="dropdown-item" href="{{ route('profile.edit') }}">
                            <i class="bi bi-person me-2"></i>Profile
                        </a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger">
                                <i class="bi bi-box-arrow-right me-2"></i>Logout
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</nav>