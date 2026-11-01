<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Restricts leadership routes to users with leadership membership or
 * explicitly granted leadership permissions.
 */
class EnsureLeader
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null) {
            return redirect()->route('login');
        }

        abort_unless(
            $user->isLeader() || $user->grantedPermissions()->isNotEmpty(),
            403,
            'You are not authorised to access the leadership area.',
        );

        return $next($request);
    }
}
