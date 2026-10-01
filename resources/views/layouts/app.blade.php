<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <meta name="theme-color" content="#e8ecf3">

    <title>@yield('title', 'Dashboard') &middot; {{ app(\App\Services\SettingService::class)->get('system_name', 'DARUSO') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div class="d-flex align-items-stretch" id="wrapper">
        @auth
            @include('layouts.sidebar')
        @endauth

        <div id="page-content-wrapper" class="flex-grow-1 d-flex flex-column">
            @auth
                @include('layouts.topbar')
            @endauth

            <main class="container-fluid p-3 p-lg-4 flex-grow-1">
                <x-flash-messages />

                <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-4">
                    <div>
                        <h1 class="h3 mb-1 fw-bold">@yield('heading', 'Dashboard')</h1>
                        @hasSection('subheading')
                            <p class="text-muted mb-0">@yield('subheading')</p>
                        @endif
                    </div>
                    @hasSection('actions')
                        <div class="d-flex flex-wrap gap-2">@yield('actions')</div>
                    @endif
                </div>

                {{ $slot ?? '' }}
                @yield('content')
            </main>

            <footer class="container-fluid px-4 py-3 text-muted small border-top bg-white">
                &copy; {{ now()->year }} {{ app(\App\Services\SettingService::class)->get('organisation_name', 'Daruso Students Organisation') }}
            </footer>
        </div>
    </div>

    @stack('scripts')
</body>
</html>