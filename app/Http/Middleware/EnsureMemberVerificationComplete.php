<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureMemberVerificationComplete
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user->is_admin && $request->routeIs('admin.*') && app()->environment('local')) {
            return $next($request);
        }

        if (! $user->hasVerifiedEmail()) {
            return redirect()->route('verification.notice');
        }

        if (! $user->phone) {
            return redirect()->route('mypage.profile');
        }

        if (! $user->phone_verified_at) {
            return redirect()->route('phone-verification.notice');
        }

        return $next($request);
    }
}
