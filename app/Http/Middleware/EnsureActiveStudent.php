<?php

namespace App\Http\Middleware;

use App\Enums\StudentStatus;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveStudent
{
    public function handle(Request $request, Closure $next): Response
    {
        $profile = $request->user()?->studentProfile;

        if (! $profile) {
            abort(403, 'An active student record is required to access this service.');
        }

        if ($profile->status !== StudentStatus::Active) {
            return response()->view('student.pending-verification', [
                'status' => $profile->status,
            ]);
        }

        return $next($request);
    }
}
