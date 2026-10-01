<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Sign in') &middot; {{ config('app.name', 'DARUSO') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-light">
    <div class="container min-vh-100 d-flex flex-column justify-content-center py-5">
        <div class="row justify-content-center">
            <div class="col-12 col-sm-10 col-md-7 col-lg-5">
                <div class="text-center mb-4">
                    <i class="bi bi-megaphone-fill fs-1 text-primary"></i>
                    <h1 class="h4 fw-bold mb-0">{{ config('app.name', 'DARUSO') }}</h1>
                    <p class="text-muted small mb-0">Digital Communication &amp; Information Management</p>
                </div>

                <x-flash-messages />

                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        {{ $slot }}
                    </div>
                </div>

                <p class="text-center text-muted small mt-3 mb-0">
                    <a href="{{ route('home') }}" class="text-decoration-none">
                        <i class="bi bi-arrow-left me-1"></i>Back to home
                    </a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>