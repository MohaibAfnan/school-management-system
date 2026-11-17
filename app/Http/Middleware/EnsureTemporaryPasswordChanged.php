<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTemporaryPasswordChanged
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->must_change_password) {
            return $next($request);
        }

        if ($request->is(
            'settings/security',
            'settings/password',
            'logout',
            'confirm-password',
            'user/confirm-password',
            'email/verify',
            'email/verify/*',
            'email/verification-notification',
        )) {
            return $next($request);
        }

        return redirect('/settings/security')->with('status', 'For your security, change the temporary password before continuing.');
    }
}
