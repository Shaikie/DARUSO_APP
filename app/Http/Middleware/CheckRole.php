<?php

namespace App\Http\Middleware;

use App\Enums\RoleName;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Route-level role gate.
 *
 * A coarse first filter only: policies still enforce per-record authorization,
 * so passing this middleware never grants access to a specific resource.
 */
class CheckRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        foreach ($roles as $role) {
            if ($user->hasRole($role)) {
                return $next($request);
            }
        }

        abort(403, 'You are not authorised to access this area.');
    }

    /**
     * Deny the route when the user holds none of the listed roles.
     */
    public function handleExcept(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if ($user !== null && ! $user->hasAnyRole($roles)) {
            return $next($request);
        }

        abort(403, 'You are not authorised to access this area.');
    }

    /**
     * Allow the route only for leadership roles.
     */
    public function handleLeadership(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if (! $user->hasAnyRole(RoleName::leadershipValues()) && ! $user->isLeader()) {
            abort(403, 'You are not authorised to access this area.');
        }

        return $next($request);
    }
}
