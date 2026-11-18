<?php

namespace App\Http\Middleware;

use App\Models\School;
use App\Models\User;
use App\Tenancy\SchoolScope;
use App\Tenancy\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveSchool
{
    public function handle(Request $request, Closure $next): Response
    {
        $context = app(TenantContext::class);

        if ($request->is('developer', 'developer/*')) {
            $context->set(null);
            config([
                'mail.default' => config('mail.platform_default', 'log'),
                'mail.from.address' => config('mail.platform_from_address'),
                'mail.from.name' => config('mail.platform_from_name'),
            ]);
            app('mail.manager')->purge();

            return $next($request);
        }

        $school = null;
        $sessionSchoolId = $request->session()->get('active_school_id');

        if ($sessionSchoolId) {
            $school = School::query()->find($sessionSchoolId);
        }

        if (! $school && $request->filled('school_code')) {
            $code = strtoupper(trim((string) $request->input('school_code')));
            $school = School::query()
                ->where(function ($query) use ($code): void {
                    $query->where('code', $code)
                        ->orWhere('slug', strtolower($code));
                })
                ->first();
        }

        if (! $school && $request->filled('email')) {
            $user = User::withoutGlobalScope(SchoolScope::class)
                ->where('email', mb_strtolower(trim((string) $request->input('email'))))
                ->first(['school_id']);
            if ($user) $school = School::query()->find($user->school_id);
        }

        if (! $school && ($user = $request->user()) && $user->school_id) {
            $school = School::query()->find($user->school_id);

            if ($school) {
                $request->session()->put('active_school_id', $school->id);
            }
        }

        if ($school?->status !== null && $school->status !== 'active') {
            abort(403, 'This school workspace is currently unavailable.');
        }

        $context->set($school);

        if ($school && ($smtp = $school->smtpSettings()->first())) {
            $mailerName = 'school_'.$school->id;
            config([
                "mail.mailers.{$mailerName}" => [
                    ...$smtp->mailerConfiguration(),
                    'from' => ['address' => $smtp->from_email, 'name' => $smtp->from_name],
                ],
                'mail.default' => $mailerName,
                'mail.from.address' => $smtp->from_email,
                'mail.from.name' => $smtp->from_name,
            ]);
        } else {
            config([
                'mail.default' => config('mail.platform_default', 'log'),
                'mail.from.address' => config('mail.platform_from_address'),
                'mail.from.name' => config('mail.platform_from_name'),
            ]);
        }

        app('mail.manager')->purge();

        if ($school && ! $sessionSchoolId && $request->isMethod('GET') && $request->filled('school_code')) {
            $request->session()->put('active_school_id', $school->id);
        }

        return $next($request);
    }
}
