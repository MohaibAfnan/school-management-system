<?php

namespace App\Http\Controllers\Developer;

use App\Http\Controllers\Controller;
use App\Models\PlatformAdmin;
use App\Services\PlatformAudit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class DeveloperAuthController extends Controller
{
    public function create(): Response|RedirectResponse
    {
        if (Auth::guard('platform')->check()) {
            return to_route('developer.dashboard');
        }

        return Inertia::render('developer/Login');
    }

    public function store(Request $request, PlatformAudit $audit): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', 'max:255'],
        ]);

        $credentials['email'] = mb_strtolower(trim($credentials['email']));

        if (! Auth::guard('platform')->attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => 'These developer portal credentials do not match our records.',
            ]);
        }

        $request->session()->regenerate();
        $admin = Auth::guard('platform')->user();
        $admin->forceFill(['last_login_at' => now()])->save();
        $audit->record($request, $admin, 'platform.login', targetType: 'platform_admin', targetId: $admin->id);

        return to_route('developer.dashboard');
    }

    public function destroy(Request $request, PlatformAudit $audit): RedirectResponse
    {
        $admin = Auth::guard('platform')->user();
        $audit->record($request, $admin, 'platform.logout', targetType: 'platform_admin', targetId: $admin?->id);
        Auth::guard('platform')->logout();
        $request->session()->forget('active_school_id');
        $request->session()->regenerateToken();

        return to_route('developer.login');
    }
}
