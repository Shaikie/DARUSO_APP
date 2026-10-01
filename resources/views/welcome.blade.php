<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'DARUSO') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand bg-white border-bottom">
        <div class="container">
            <a href="{{ route('home') }}" class="navbar-brand fw-bold text-primary">
                <i class="bi bi-megaphone-fill me-2"></i>DARUSO
            </a>
            <div class="ms-auto">
                @auth
                    <a href="{{ route('dashboard') }}" class="btn btn-primary btn-sm">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="btn btn-outline-secondary btn-sm me-2">Sign in</a>
                    <a href="{{ route('register') }}" class="btn btn-primary btn-sm">Register</a>
                @endauth
            </div>
        </div>
    </nav>

    <main class="container py-5">
        <div class="row justify-content-center text-center">
            <div class="col-12 col-lg-8">
                <h1 class="display-5 fw-bold text-primary mb-3">DARUSO</h1>
                <p class="lead text-muted">
                    The digital communication, information management and student engagement
                    platform for the Daruso Students Organisation.
                </p>
            </div>
        </div>

        <div class="row g-3 mt-4">
            <div class="col-12 col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <i class="bi bi-megaphone fs-2 text-primary"></i>
                        <h5 class="mt-3">Announcements</h5>
                        <p class="text-muted small mb-0">
                            Notices published to you and to the groups you belong to — your
                            college, programme, hostel, year or ministry.
                        </p>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <i class="bi bi-calendar-event fs-2 text-success"></i>
                        <h5 class="mt-3">Events &amp; meetings</h5>
                        <p class="text-muted small mb-0">
                            See what is scheduled for you, with dates, venues and agendas.
                        </p>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <i class="bi bi-chat-left-text fs-2 text-warning"></i>
                        <h5 class="mt-3">Complaints</h5>
                        <p class="text-muted small mb-0">
                            Raise an issue privately and follow its progress from submission to
                            resolution.
                        </p>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <i class="bi bi-bell fs-2 text-danger"></i>
                        <h5 class="mt-3">Notifications</h5>
                        <p class="text-muted small mb-0">
                            Direct messages from leadership, with read tracking so nothing is
                            missed.
                        </p>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <i class="bi bi-file-earmark-text fs-2 text-info"></i>
                        <h5 class="mt-3">Documents</h5>
                        <p class="text-muted small mb-0">
                            The constitution, policies, minutes and forms, available according
                            to your access level.
                        </p>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <i class="bi bi-person-badge fs-2 text-secondary"></i>
                        <h5 class="mt-3">Representatives</h5>
                        <p class="text-muted small mb-0">
                            Find out who represents you, resolved from the current leadership
                            term.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        @guest
            <div class="text-center mt-5">
                <a href="{{ route('login') }}" class="btn btn-primary btn-lg me-2">
                    <i class="bi bi-box-arrow-in-right me-1"></i>Sign in
                </a>
                <a href="{{ route('register') }}" class="btn btn-outline-primary btn-lg">
                    <i class="bi bi-person-plus me-1"></i>Register
                </a>
            </div>
        @endguest
    </main>

    <footer class="border-top bg-white py-3 mt-5">
        <div class="container text-center text-muted small">
            &copy; {{ now()->year }} Daruso Students Organisation
        </div>
    </footer>
</body>
</html>