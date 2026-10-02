<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#ffffff">
    <title>@yield('title', 'Dashboard') · {{ app(\App\Services\SettingService::class)->get('system_name', 'DARUSO') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div class="daruso-app-shell" id="daruso-app">
        @auth
            @include('layouts.sidebar')
        @endauth

        <div class="daruso-main">
            @auth
                @include('layouts.topbar')
            @endauth

            <main class="daruso-page">
                <x-flash-messages />

                <div class="daruso-page-header">
                    <div>
                        <div class="daruso-eyebrow">@yield('eyebrow')</div>
                        <h1 class="daruso-page-title">@yield('heading', 'Dashboard')</h1>
                        @hasSection('subheading')
                            <p class="daruso-page-subtitle">@yield('subheading')</p>
                        @endif
                    </div>
                    @hasSection('actions')
                        <div class="daruso-page-actions">@yield('actions')</div>
                    @endif
                </div>

                {{ $slot ?? '' }}
                @yield('content')
            </main>

            <footer class="daruso-footer">
                <span>© {{ now()->year }} {{ app(\App\Services\SettingService::class)->get('organisation_name', 'Daruso Students Organisation') }}</span>
                <span class="d-none d-md-inline">Built for better student representation.</span>
            </footer>
        </div>

        @auth
            <div class="daruso-mobile-overlay" id="daruso-mobile-overlay"></div>
        @endauth
    </div>
</body>
</html>