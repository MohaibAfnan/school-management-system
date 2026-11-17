<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\ActivationRequest;
use App\Models\School;
use App\Models\SchoolLicense;
use App\Models\SchoolSmtpSetting;
use App\Services\SchoolLicenseService;
use App\Tenancy\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class SchoolActivationController extends Controller
{
    public function edit(Request $request): Response
    {
        $school = $this->authorizedSchool($request);
        $license = SchoolLicense::query()->latest('activated_at')->first();
        $latestRequest = ActivationRequest::query()->latest()->first();

        return Inertia::render('settings/Activation', [
            'school' => ['name' => $school->name, 'code' => $school->code],
            'deploymentMode' => $school->deployment_mode,
            'license' => $license ? [
                'plan' => $license->plan,
                'features' => $license->features ?? [],
                'expiresAt' => $license->expires_at?->toIso8601String(),
                'active' => ! $license->revoked_at && (! $license->expires_at || $license->expires_at->isFuture()),
            ] : null,
            'latestRequest' => $latestRequest ? [
                'requestId' => $latestRequest->request_id,
                'status' => $latestRequest->status,
                'createdAt' => $latestRequest->created_at?->toIso8601String(),
            ] : null,
            'activationConfigured' => filled(config('school.activation_contact_email')),
            'smtpConfigured' => SchoolSmtpSetting::query()->exists(),
        ]);
    }

    public function requestActivation(Request $request): RedirectResponse
    {
        $school = $this->authorizedSchool($request);

        abort_unless($school->deployment_mode === 'local', 404);

        $contactEmail = (string) config('school.activation_contact_email');
        $smtp = SchoolSmtpSetting::query()->first();

        if (blank($contactEmail)) {
            return back()->withErrors(['activation' => 'Activation contact is not configured. Contact your software provider.']);
        }

        if (! $smtp || blank($smtp->host)) {
            return back()->withErrors(['activation' => 'Configure and test this school’s SMTP settings before requesting activation.']);
        }

        $activationRequest = ActivationRequest::query()->create([
            'request_id' => (string) Str::uuid(),
            'installation_id' => $school->installation_id,
            'requested_by_email' => $request->user()->email,
            'status' => 'pending',
        ]);

        $body = implode("\n", [
            'School software activation request',
            'Request ID: '.$activationRequest->request_id,
            'School: '.$school->name,
            'Workspace code: '.$school->code,
            'Installation ID: '.$school->installation_id,
            'Requested by: '.$request->user()->email,
            'Request time (UTC): '.now('UTC')->toDateTimeString(),
            'Observed IP (audit hint only): '.($request->ip() ?? 'unavailable'),
        ]);

        try {
            Mail::raw($body, function ($message) use ($contactEmail, $activationRequest, $school): void {
                $message->to($contactEmail)
                    ->subject('Activation request '.$activationRequest->request_id.' — '.$school->name);
            });

            $activationRequest->forceFill(['status' => 'sent', 'sent_at' => now()])->save();
        } catch (Throwable) {
            $activationRequest->forceFill(['status' => 'failed'])->save();

            return back()->withErrors(['activation' => 'The request could not be emailed. Check this school’s SMTP settings and try again.']);
        }

        return back()->with('status', 'Activation request sent. Please allow up to 5 minutes; if it has not been activated, contact your software provider.');
    }

    public function activate(Request $request, SchoolLicenseService $licenses): RedirectResponse
    {
        $school = $this->authorizedSchool($request);

        abort_unless($school->deployment_mode === 'local', 404);

        $validated = $request->validate([
            'license_key' => ['required', 'string', 'max:10000'],
        ]);

        $licenses->activate($school, trim($validated['license_key']));

        return back()->with('status', 'This school installation is now activated.');
    }

    private function authorizedSchool(Request $request): School
    {
        abort_unless($request->user()?->isAdmin(), 403);

        $school = app(TenantContext::class)->school();
        abort_unless($school && $school->id === $request->user()->school_id, 403);

        return $school;
    }
}
