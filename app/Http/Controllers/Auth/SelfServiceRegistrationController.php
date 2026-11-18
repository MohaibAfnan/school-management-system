<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\EmailOtp;
use App\Models\Role;
use App\Models\School;
use App\Models\SchoolSubscription;
use App\Models\User;
use App\Notifications\EmailOtpNotification;
use App\Tenancy\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class SelfServiceRegistrationController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('auth/Register');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'school_name' => ['required', 'string', 'max:191'],
            'name' => ['required', 'string', 'max:191'],
            'email' => ['required', 'email', 'max:191', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(12)->mixedCase()->numbers()->symbols()],
            'terms' => ['accepted'],
        ]);
        $email = mb_strtolower(trim($data['email']));
        $request->merge(['email' => $email]);

        $owner = DB::transaction(function () use ($data, $email): User {
            $base = Str::slug($data['school_name']) ?: 'school';
            $code = strtoupper(Str::random(8));
            $school = School::query()->create([
                'name' => trim($data['school_name']),
                'slug' => $base.'-'.Str::lower($code),
                'code' => $code,
                'installation_id' => (string) Str::uuid(),
                'status' => 'active',
                'deployment_mode' => 'hosted',
            ]);
            app(TenantContext::class)->set($school);
            $role = Role::query()->firstOrCreate(['slug' => 'admin'], ['name' => 'Administrator', 'permissions' => ['all']]);
            $owner = User::query()->create([
                'school_id' => $school->id,
                'name' => trim($data['name']),
                'email' => $email,
                'password' => Hash::make($data['password']),
                'role_id' => $role->id,
            ]);
            $owner->forceFill(['email_verified_at' => null, 'must_change_password' => false])->save();
            SchoolSubscription::query()->create([
                'school_id' => $school->id,
                'status' => 'pending_verification',
                'starts_at' => null,
                'trial_ends_at' => null,
            ]);
            return $owner;
        });

        $request->session()->put('pending_owner_id', $owner->id);
        $request->session()->put('pending_owner_email', $owner->email);
        if (! $this->sendOtp($owner)) {
            return to_route('register.verify')->with('status', 'Email delivery is not configured yet. Ask the platform administrator to configure system email, then request a new code.');
        }

        return to_route('register.verify')->with('status', 'We sent a six-digit verification code to your email.');
    }

    public function prompt(Request $request): Response|RedirectResponse
    {
        if (! $request->session()->has('pending_owner_id')) {
            return to_route('register');
        }

        return Inertia::render('auth/VerifyOtp', ['email' => $request->session()->get('pending_owner_email')]);
    }

    public function verify(Request $request, TenantContext $tenant): RedirectResponse
    {
        $data = $request->validate(['code' => ['required', 'digits:6']]);
        $ownerId = $request->session()->get('pending_owner_id');
        $owner = User::withoutGlobalScopes()->find($ownerId);
        abort_unless($owner, 404);
        $tenant->set(School::query()->findOrFail($owner->school_id));
        $otp = EmailOtp::query()->where('user_id', $owner->id)->first();

        if (! $otp || $otp->expires_at->isPast() || $otp->attempts >= 5 || ! Hash::check($data['code'], $otp->code_hash)) {
            if ($otp && $otp->attempts < 5) $otp->increment('attempts');
            return back()->withErrors(['code' => 'That code is invalid or expired. Request a new code and try again.']);
        }

        DB::transaction(function () use ($owner, $otp): void {
            $owner->forceFill(['email_verified_at' => now()])->save();
            $otp->delete();
            $owner->school->subscription()->update([
                'status' => 'trialing',
                'starts_at' => now(),
                'trial_ends_at' => now()->addDays(7),
            ]);
        });

        $request->session()->forget(['pending_owner_id', 'pending_owner_email']);
        $request->session()->put('active_school_id', $owner->school_id);
        $tenant->set($owner->school);
        Auth::guard('web')->login($owner);
        $request->session()->regenerate();

        return to_route('dashboard')->with('status', 'Your email is verified. Your seven-day school trial has started.');
    }

    public function resend(Request $request): RedirectResponse
    {
        $owner = User::withoutGlobalScopes()->find($request->session()->get('pending_owner_id'));
        abort_unless($owner, 404);
        $otp = EmailOtp::query()->where('user_id', $owner->id)->first();
        if ($otp && $otp->last_sent_at->addMinute()->isFuture()) {
            return back()->withErrors(['code' => 'Please wait one minute before requesting another code.']);
        }
        if (! $this->sendOtp($owner)) {
            return back()->withErrors(['code' => 'Email delivery is not configured. Ask the platform administrator to configure system email.']);
        }
        return back()->with('status', 'A new verification code was sent.');
    }

    private function sendOtp(User $owner): bool
    {
        if (! app()->environment('testing') && in_array(config('mail.default'), ['log', 'array'], true)) {
            return false;
        }

        $code = (string) random_int(100000, 999999);
        EmailOtp::query()->updateOrCreate(['user_id' => $owner->id], [
            'code_hash' => Hash::make($code),
            'expires_at' => now()->addMinutes(10),
            'attempts' => 0,
            'last_sent_at' => now(),
        ]);
        $owner->notify(new EmailOtpNotification($code));

        return true;
    }
}
