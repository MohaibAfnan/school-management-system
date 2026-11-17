<?php

namespace App\Http\Controllers\Developer;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\School;
use App\Models\SchoolPlan;
use App\Models\SchoolSmtpSetting;
use App\Models\SchoolSubscription;
use App\Models\User;
use App\Services\PlatformAudit;
use App\Tenancy\SchoolScope;
use App\Tenancy\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class DeveloperSchoolController extends Controller
{
    public function index(Request $request, TenantContext $tenant): Response
    {
        $search = trim((string) $request->query('q', ''));
        $query = School::withTrashed()
            ->with('subscription.plan')
            ->orderByDesc('created_at');

        if ($search !== '') {
            $query->where(function ($builder) use ($search): void {
                $like = '%'.$search.'%';
                $builder->where('name', 'like', $like)
                    ->orWhere('code', 'like', $like)
                    ->orWhere('slug', 'like', $like)
                    ->orWhereExists(fn ($subquery) => $subquery
                        ->selectRaw('1')
                        ->from('users')
                        ->whereColumn('users.school_id', 'schools.id')
                        ->where('users.email', 'like', $like));
            });
        }

        $schools = $query->paginate(25)->withQueryString();

        $schools->setCollection($schools->getCollection()->map(function (School $school) use ($tenant): array {
            $tenant->set($school);
            $admin = User::query()
                ->whereHas('role', fn ($query) => $query->where('slug', 'admin'))
                ->orderBy('id')
                ->first(['id', 'school_id', 'name', 'email']);

            return [
                'id' => $school->id,
                'name' => $school->name,
                'slug' => $school->slug,
                'code' => $school->code,
                'status' => $school->status,
                'deploymentMode' => $school->deployment_mode,
                'createdAt' => $school->created_at?->toDateString(),
                'archived' => $school->trashed(),
                'admin' => $admin ? ['id' => $admin->id, 'name' => $admin->name, 'email' => $admin->email] : null,
                'subscription' => $school->subscription ? [
                    'status' => $school->subscription->status,
                    'planId' => $school->subscription->school_plan_id,
                    'planName' => $school->subscription->plan?->name,
                    'trialEndsAt' => $school->subscription->trial_ends_at?->toDateString(),
                    'endsAt' => $school->subscription->ends_at?->toDateString(),
                ] : null,
            ];
        }));
        $tenant->set(null);

        return Inertia::render('developer/Schools', [
            'schools' => $schools,
            'filters' => ['q' => $search],
            'plans' => SchoolPlan::query()->where('is_active', true)->orderBy('sort_order')->orderBy('name')->get(['id', 'name', 'price', 'currency']),
        ]);
    }

    public function store(Request $request, TenantContext $tenant, PlatformAudit $audit): RedirectResponse
    {
        $request->merge([
            'code' => strtoupper(trim((string) $request->input('code'))),
            'admin_email' => mb_strtolower(trim((string) $request->input('admin_email'))),
        ]);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:191'],
            'code' => ['required', 'string', 'min:3', 'max:32', 'regex:/^[A-Za-z0-9-]+$/', 'unique:schools,code'],
            'admin_name' => ['required', 'string', 'max:191'],
            'admin_email' => ['required', 'email', 'max:191', 'unique:users,email'],
            'admin_password' => ['nullable', 'string', PasswordRule::min(14)->mixedCase()->numbers()->symbols(), 'confirmed'],
            'school_plan_id' => ['nullable', 'integer', Rule::exists('school_plans', 'id')->where('is_active', true)],
        ]);

        $school = DB::transaction(function () use ($data, $tenant): School {
            $code = strtoupper($data['code']);
            $slug = Str::slug($data['name']).'-'.strtolower($code);
            $school = School::query()->create([
                'name' => $data['name'],
                'slug' => $slug,
                'code' => $code,
                'installation_id' => (string) Str::uuid(),
                'status' => 'active',
                'deployment_mode' => 'hosted',
            ]);

            $tenant->set($school);
            $role = Role::query()->firstOrCreate(
                ['slug' => 'admin'],
                ['name' => 'Administrator', 'permissions' => ['all']],
            );

            $admin = User::query()->create([
                'name' => $data['admin_name'],
                'email' => mb_strtolower($data['admin_email']),
                'password' => Hash::make($data['admin_password'] ?? Str::password(24)),
                'role_id' => $role->id,
            ]);
            $admin->forceFill(['must_change_password' => filled($data['admin_password'] ?? null)])->save();

            if (! empty($data['school_plan_id'])) {
                SchoolSubscription::query()->create([
                    'school_id' => $school->id,
                    'school_plan_id' => $data['school_plan_id'],
                    'status' => 'trialing',
                    'starts_at' => now(),
                    'trial_ends_at' => now()->addDays(14),
                ]);
            }

            return $school;
        });

        $tenant->set($school);
        $mailConfigured = $this->configureMailForSchool($school);
        $resetStatus = filled($data['admin_password'] ?? null)
            ? 'temporary_password_set'
            : ($mailConfigured ? $this->sendResetLink(mb_strtolower($data['admin_email'])) : 'delivery_unconfigured');
        if (filled($data['admin_password'] ?? null)) {
            $request->session()->flash('developer_temporary_password', Crypt::encryptString($data['admin_password']));
        }
        $tenant->set(null);
        $audit->record($request, $request->user('platform'), 'school.created', $school, 'school', $school->id, after: [
            'name' => $school->name,
            'code' => $school->code,
            'admin_email' => mb_strtolower($data['admin_email']),
            'plan_id' => $data['school_plan_id'] ?? null,
        ]);

        $message = match ($resetStatus) {
            'temporary_password_set' => 'School created. The temporary password is shown once below; the administrator must change it after signing in.',
            Password::RESET_LINK_SENT => 'School created. An account setup link was sent to the school administrator.',
            default => 'School created, but email delivery is not configured or the setup link could not be sent. Configure platform email before using Reset access.',
        };

        return to_route('developer.schools.index')->with('status', $message);
    }

    public function update(Request $request, School $school, TenantContext $tenant, PlatformAudit $audit): RedirectResponse
    {
        abort_if($school->trashed(), 404);

        $tenant->set($school);
        $admin = $this->primaryAdmin();
        $request->merge(['code' => strtoupper(trim((string) $request->input('code'))), 'admin_email' => mb_strtolower(trim((string) $request->input('admin_email')))]);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:191'],
            'code' => ['required', 'string', 'min:3', 'max:32', 'regex:/^[A-Za-z0-9-]+$/', Rule::unique('schools', 'code')->ignore($school->id)],
            'admin_name' => ['required', 'string', 'max:191'],
            'admin_email' => ['required', 'email', 'max:191', Rule::unique('users', 'email')->ignore($admin?->id)],
            'admin_password' => ['nullable', 'string', PasswordRule::min(14)->mixedCase()->numbers()->symbols(), 'confirmed'],
        ]);

        $before = ['name' => $school->name, 'code' => $school->code, 'admin_email' => $admin?->email];
        $school->fill([
            'name' => $data['name'],
            'code' => strtoupper($data['code']),
            'slug' => Str::slug($data['name']).'-'.strtolower($data['code']),
        ])->save();

        $emailChanged = false;
        if ($admin) {
            $emailChanged = $admin->email !== mb_strtolower($data['admin_email']);
            $admin->fill(['name' => $data['admin_name'], 'email' => mb_strtolower($data['admin_email'])])->save();

            if (filled($data['admin_password'] ?? null)) {
                $admin->forceFill([
                    'password' => Hash::make($data['admin_password']),
                    'must_change_password' => true,
                ])->save();
                $request->session()->flash('developer_temporary_password', Crypt::encryptString($data['admin_password']));
            }

            if ($emailChanged) {
                $admin->forceFill(['email_verified_at' => null])->save();
                if ($this->configureMailForSchool($school)) {
                    try {
                        $admin->sendEmailVerificationNotification();
                    } catch (\Throwable) {
                        // Keep the account update; the administrator can retry delivery from the school mail settings.
                    }
                }
            }
        }

        $tenant->set(null);
        $audit->record($request, $request->user('platform'), 'school.updated', $school, 'school', $school->id, $before, [
            'name' => $school->name,
            'code' => $school->code,
            'admin_email' => mb_strtolower($data['admin_email']),
        ]);

        $message = filled($data['admin_password'] ?? null)
            ? 'School details saved. The temporary password is shown once below and must be changed at first sign-in.'
            : ($emailChanged
                ? 'School details saved. Send a new access link to the updated administrator email.'
                : 'School details saved.');

        return back()->with('status', $message);
    }

    public function toggle(Request $request, School $school, PlatformAudit $audit): RedirectResponse
    {
        abort_if($school->trashed(), 404);
        $before = ['status' => $school->status];
        $school->forceFill(['status' => $school->status === 'active' ? 'disabled' : 'active'])->save();
        $audit->record($request, $request->user('platform'), 'school.status_changed', $school, 'school', $school->id, $before, ['status' => $school->status]);

        return back()->with('status', $school->status === 'active' ? 'School access enabled.' : 'School access disabled.');
    }

    public function archive(Request $request, School $school, PlatformAudit $audit): RedirectResponse
    {
        $before = ['name' => $school->name, 'code' => $school->code, 'status' => $school->status];
        $school->delete();
        $audit->record($request, $request->user('platform'), 'school.archived', $school, 'school', $school->id, $before);

        return back()->with('status', 'School archived. Its records are retained and can be restored.');
    }

    public function restore(Request $request, int $school, PlatformAudit $audit): RedirectResponse
    {
        $record = School::withTrashed()->findOrFail($school);
        $record->restore();
        $audit->record($request, $request->user('platform'), 'school.restored', $record, 'school', $record->id, ['archived' => true], ['archived' => false]);

        return back()->with('status', 'School restored. Review its access status before enabling sign-in.');
    }

    public function subscription(Request $request, School $school, PlatformAudit $audit): RedirectResponse
    {
        abort_if($school->trashed(), 404);
        $data = $request->validate([
            'school_plan_id' => ['nullable', 'integer', Rule::exists('school_plans', 'id')],
            'status' => ['required', Rule::in(['trialing', 'active', 'past_due', 'cancelled'])],
            'trial_ends_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date'],
        ]);

        $subscription = SchoolSubscription::query()->firstOrNew(['school_id' => $school->id]);
        $before = $subscription->exists ? $subscription->only(['school_plan_id', 'status', 'trial_ends_at', 'ends_at']) : null;
        $subscription->fill([
            'school_plan_id' => $data['school_plan_id'] ?: null,
            'status' => $data['status'],
            'starts_at' => $subscription->starts_at ?? now(),
            'trial_ends_at' => $data['trial_ends_at'] ?? null,
            'ends_at' => $data['ends_at'] ?? null,
            'cancelled_at' => $data['status'] === 'cancelled' ? ($subscription->cancelled_at ?? now()) : null,
        ])->save();

        $audit->record($request, $request->user('platform'), 'school.subscription_changed', $school, 'school_subscription', $subscription->id, $before, $subscription->only(['school_plan_id', 'status', 'trial_ends_at', 'ends_at']));

        return back()->with('status', 'School package and subscription state saved.');
    }

    public function sendReset(Request $request, School $school, TenantContext $tenant, PlatformAudit $audit): RedirectResponse
    {
        abort_if($school->trashed(), 404);
        $tenant->set($school);
        $admin = $this->primaryAdmin();

        if (! $admin) {
            $tenant->set(null);

            return back()->withErrors(['account' => 'This school does not have an administrator account.']);
        }

        $status = $this->configureMailForSchool($school)
            ? $this->sendResetLink($admin->email)
            : 'delivery_unconfigured';
        $tenant->set(null);
        $audit->record($request, $request->user('platform'), 'school.admin_reset_requested', $school, 'user', $admin->id, after: ['admin_email' => $admin->email, 'delivery_status' => $status]);

        if ($status !== Password::RESET_LINK_SENT) {
            return back()->withErrors(['account' => 'The reset link could not be sent. Check the school SMTP settings and delivery status.']);
        }

        return back()->with('status', 'A password reset link was sent to the school administrator.');
    }

    public function setTemporaryPassword(Request $request, School $school, TenantContext $tenant, PlatformAudit $audit): RedirectResponse
    {
        abort_if($school->trashed(), 404);
        $tenant->set($school);
        $admin = $this->primaryAdmin();

        if (! $admin) {
            $tenant->set(null);

            return back()->withErrors(['account' => 'This school does not have an administrator account.']);
        }

        $temporaryPassword = Str::password(24);
        $admin->forceFill([
            'password' => Hash::make($temporaryPassword),
            'must_change_password' => true,
        ])->save();
        $tenant->set(null);

        $request->session()->flash('developer_temporary_password', Crypt::encryptString($temporaryPassword));
        $audit->record($request, $request->user('platform'), 'school.admin_temporary_password_set', $school, 'user', $admin->id, after: ['admin_email' => $admin->email, 'must_change_password' => true]);

        return back()->with('status', 'A temporary password was created and is shown once below. Share it securely; the administrator must change it after signing in.');
    }

    private function primaryAdmin(): ?User
    {
        return User::query()
            ->whereHas('role', fn ($query) => $query->where('slug', 'admin'))
            ->orderBy('id')
            ->first();
    }

    private function configureMailForSchool(School $school): bool
    {
        $smtp = SchoolSmtpSetting::query()->where('school_id', $school->id)->first();

        if (! $smtp) {
            config([
                'mail.default' => config('mail.platform_default', 'log'),
                'mail.from.address' => config('mail.platform_from_address'),
                'mail.from.name' => config('mail.platform_from_name'),
            ]);
            app('mail.manager')->purge();

            return ! in_array(config('mail.platform_default', 'log'), ['log', 'array'], true);
        }

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
        app('mail.manager')->purge();

        return true;
    }

    private function sendResetLink(string $email): string
    {
        try {
            return Password::broker('users')->sendResetLink(['email' => $email]);
        } catch (\Throwable) {
            return 'delivery_failed';
        }
    }
}
