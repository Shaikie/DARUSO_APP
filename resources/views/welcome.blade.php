<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'DARUSO') }}</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center min-vh-100 align-items-center">
            <div class="col-md-8 text-center">
                <h1 class="display-4 fw-bold text-primary mb-3">
                    <i class="bi bi-megaphone-fill me-2"></i>DARUSO
                </h1>
                <p class="lead text-muted mb-4">Digital Communication & Information Management System</p>

                <div class="row justify-content-center mb-4">
                    <div class="col-md-4 mb-3">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <i class="bi bi-megaphone fs-1 text-primary"></i>
                                <h5 class="mt-2">Announcements</h5>
                                <p class="text-muted small">Stay updated with the latest news and notices</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <i class="bi bi-calendar-event fs-1 text-success"></i>
                                <h5 class="mt-2">Events & Meetings</h5>
                                <p class="text-muted small">View upcoming events and scheduled meetings</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <i class="bi bi-exclamation-triangle fs-1 text-warning"></i>
                                <h5 class="mt-2">Complaints</h5>
                                <p class="text-muted small">Submit and track your complaints and requests</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-center gap-3">
                    <a href="{{ route('login') }}" class="btn btn-primary btn-lg">
                        <i class="bi bi-box-arrow-in-right me-2"></i>Login
                    </a>
                    <a href="{{ route('register') }}" class="btn btn-outline-primary btn-lg">
                        <i class="bi bi-person-plus me-2"></i>Register
                    </a>
                </div>

                <p class="text-muted small mt-4">
                    &copy; {{ date('Y') }} DARUSO. All rights reserved.
                </p>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
