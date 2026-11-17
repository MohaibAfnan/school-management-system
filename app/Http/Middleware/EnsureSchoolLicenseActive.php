<?php

namespace App\Http\Middleware;

use App\Models\SchoolLicense;
use App\Tenancy\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSchoolLicenseActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $school = app(TenantContext::class)->school();

        if (! $school || $school->deployment_mode !== 'local' || blank(config('school.license_public_key'))) {
            return $next($request);
        }

        $license = SchoolLicense::query()
            ->whereNull('revoked_at')
            ->latest('activated_at')
            ->first();

        if ($license && (! $license->expires_at || $license->expires_at->isFuture())) {
            return $next($request);
        }

        if ($request->routeIs('school-activation.*', 'logout')) {
            return $next($request);
        }

        abort_unless($request->user()?->isAdmin(), 403, 'This installation requires activation.');

        return redirect()->route('school-activation.edit');
    }
}
