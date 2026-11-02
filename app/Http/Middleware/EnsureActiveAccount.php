<?php

namespace App\Http\Middleware;

use App\Enums\AccountStatus;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveAccount
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        // A pending account whose email is already verified may be safely
        // activated. Suspended and rejected accounts are never auto-activated.
        if ($user->account_status === AccountStatus::Pending && $user->hasVerifiedEmail()) {
            $user->forceFill([
                'account_status' => AccountStatus::Active,
                'status_changed_at' => now(),
            ])->save();
        }

        if (! $user->isActiveAccount()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'email' => 'Your account is not active. Contact the DARUSO office for assistance.',
            ]);
        }

        return $next($request);
    }
}
