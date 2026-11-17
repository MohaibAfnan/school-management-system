<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\SchoolSmtpSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class SchoolSmtpSettingsController extends Controller
{
    public function edit(Request $request): Response
    {
        $this->authorizeSchoolAdministrator($request);

        $settings = SchoolSmtpSetting::query()->first();

        return Inertia::render('settings/Mail', [
            'settings' => [
                'host' => $settings?->host ?? '',
                'port' => $settings?->port ?? 587,
                'scheme' => $settings?->scheme ?? 'smtp',
                'username' => $settings?->username ?? '',
                'from_email' => $settings?->from_email ?? '',
                'from_name' => $settings?->from_name ?? '',
            ],
            'passwordConfigured' => filled($settings?->password),
            'lastTestedAt' => $settings?->last_tested_at?->toIso8601String(),
            'lastTestStatus' => $settings?->last_test_status,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $this->authorizeSchoolAdministrator($request);

        $validated = $request->validate([
            'host' => ['required', 'string', 'max:255'],
            'port' => ['required', 'integer', 'between:1,65535'],
            'scheme' => ['required', Rule::in(['smtp', 'smtps'])],
            'username' => ['nullable', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'max:2000'],
            'from_email' => ['required', 'email', 'max:255'],
            'from_name' => ['required', 'string', 'max:255'],
        ]);

        $settings = SchoolSmtpSetting::query()->firstOrNew();
        $newPassword = trim((string) ($validated['password'] ?? ''));
        unset($validated['password']);

        $settings->fill($validated);

        if ($newPassword !== '') {
            $settings->password = $newPassword;
        }

        $settings->last_test_status = null;
        $settings->last_tested_at = null;
        $settings->save();

        return back()->with('status', 'School email settings saved. Send a test email to verify the connection.');
    }

    public function test(Request $request): RedirectResponse
    {
        $this->authorizeSchoolAdministrator($request);

        $settings = SchoolSmtpSetting::query()->first();

        if (! $settings || blank($settings->host) || blank($settings->from_email)) {
            return back()->withErrors(['smtp' => 'Save the school email settings before sending a test.']);
        }

        try {
            $mailer = Mail::build([
                'name' => 'school_smtp_test_'.$settings->school_id,
                ...$settings->mailerConfiguration(),
            ]);

            $mailer->raw(
                'Your school email connection is working. This is a configuration test from '.config('app.name').'.',
                function ($message) use ($settings, $request): void {
                    $message->to($request->user()->email)
                        ->from($settings->from_email, $settings->from_name)
                        ->subject('School email connection test');
                },
            );

            $settings->forceFill([
                'last_tested_at' => now(),
                'last_test_status' => 'success',
            ])->save();

            return back()->with('status', 'Test email sent to your administrator email address.');
        } catch (Throwable $exception) {
            report($exception);

            $settings->forceFill([
                'last_tested_at' => now(),
                'last_test_status' => 'failed',
            ])->save();

            return back()->withErrors(['smtp' => 'The test email could not be sent. Check the host, port, security mode, username, and password.']);
        }
    }

    private function authorizeSchoolAdministrator(Request $request): void
    {
        abort_unless($request->user()?->isAdmin(), 403);
        abort_unless(app(\App\Tenancy\TenantContext::class)->id() === $request->user()->school_id, 403);
    }
}
