<?php

use App\Http\Middleware\CheckRole;
use App\Http\Middleware\DenyStudentRole;
use App\Http\Middleware\EnsurePermission;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => CheckRole::class,
            'role.except' => DenyStudentRole::class,
            'permission' => EnsurePermission::class,
        ]);

        // Authenticated pages are session-backed and must never be cached by a
        // shared proxy; the session cookie itself is handled by Laravel.
        $middleware->encryptCookies(except: ['locale']);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
