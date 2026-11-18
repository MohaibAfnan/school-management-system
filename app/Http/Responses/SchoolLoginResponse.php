<?php

namespace App\Http\Responses;

use Illuminate\Http\RedirectResponse;
use Laravel\Fortify\Contracts\LoginResponse;
use Laravel\Fortify\Fortify;

class SchoolLoginResponse implements LoginResponse
{
    public function toResponse($request): RedirectResponse
    {
        $user = $request->user('web');

        if ($user?->must_change_password) {
            return redirect()->route('security.edit');
        }

        return redirect()->intended(Fortify::redirects('login'));
    }
}
