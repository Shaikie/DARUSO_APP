<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#151936">
    <title>@yield('title', 'Sign in') · {{ config('app.name', 'DARUSO') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="daruso-guest-body">
    <main class="container auth-shell min-vh-100 d-flex flex-column justify-content-center">
        <div class="text-center">
            <a href="{{ route('home') }}" class="auth-brand">
                <span class="auth-logo-mark"><i class="bi bi-broadcast-pin"></i></span>
                <span class="text-start"><strong>DARUSO</strong><small>Student community platform</small></span>
            </a>
            <p class="auth-intro">Your campus updates, conversations and student services, together in one place.</p>
        </div>

        <x-flash-messages />

        <section class="card auth-card border-0">
            <div class="card-body">
                {{ $slot }}
            </div>
        </section>

        <div class="text-center mt-4">
            <a href="{{ route('home') }}" class="auth-back-link text-decoration-none">
                <i class="bi bi-arrow-left me-1"></i>Back to DARUSO
            </a>
        </div>
    </main>
</body>
</html>
