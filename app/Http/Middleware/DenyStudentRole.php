<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Keeps a user out of an area when they hold one of the listed roles.
 *
 * Used for the leader area: a student who has also been given a student role
 * must not fall through into leadership pages by holding the wrong role.
 */
class DenyStudentRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if ($user === null) {
            return redirect()->route('login');
        }

        if ($user->hasAnyRole($roles)) {
            abort(403, 'You are not authorised to access this area.');
        }

        return $next($request);
    }
}
