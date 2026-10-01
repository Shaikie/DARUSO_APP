<!-- Top Navigation -->
<nav class="navbar navbar-expand navbar-light bg-white border-bottom">
    <div class="container-fluid">
        <button class="btn btn-link" id="sidebar-toggle">
            <i class="bi bi-list fs-4"></i>
        </button>

        <div class="d-flex align-items-center ms-auto">
            <div class="dropdown me-3">
                <a class="nav-link position-relative" href="#" id="notificationDropdown" data-bs-toggle="dropdown">
                    <i class="bi bi-bell fs-5"></i>
                    @php
                        $unreadCount = auth()->user()->notifications()->whereNull('read_at')->count();
                    @endphp
                    @if($unreadCount > 0)
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                            {{ $unreadCount > 99 ? '99+' : $unreadCount }}
                        </span>
                    @endif
                </a>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="notificationDropdown">
                    <li><h6 class="dropdown-header">Notifications</h6></li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item text-center" href="{{ auth()->user()->isLeader() ? route('leader.notifications.index') : route('student.notifications.index') }}">
                            View all notifications
                        </a>
                    </li>
                </ul>
            </div>

            <div class="dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="userDropdown" data-bs-toggle="dropdown">
                    <i class="bi bi-person-circle fs-5"></i>
                    <span class="ms-1 d-none d-md-inline">{{ auth()->user()->name }}</span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                    <li>
                        <a class="dropdown-item" href="{{ auth()->user()->isLeader() ? route('leader.profile') : route('student.profile') }}">
                            <i class="bi bi-person me-2"></i>Profile
                        </a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item">
                                <i class="bi bi-box-arrow-right me-2"></i>Logout
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</nav>

<script>
    document.getElementById('sidebar-toggle').addEventListener('click', function() {
        document.getElementById('sidebar-wrapper').classList.toggle('d-none');
    });
</script>
