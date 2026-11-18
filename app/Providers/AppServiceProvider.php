<?php

namespace App\Providers;

use App\Models\School;
use App\Models\User;
use App\Tenancy\TenantContext;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Fortify\Fortify;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->scoped(TenantContext::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        config([
            'mail.platform_default' => config('mail.default'),
            'mail.platform_from_address' => config('mail.from.address'),
            'mail.platform_from_name' => config('mail.from.name'),
        ]);

        $this->configureDefaults();
        Schema::defaultStringLength(191);

        Fortify::authenticateUsing(function (Request $request): ?User {
            $schoolCode = strtoupper(trim((string) $request->input('school_code')));
            $school = School::query()
                ->where(function ($query) use ($schoolCode): void {
                    $query->whereRaw('UPPER(code) = ?', [$schoolCode])
                        ->orWhere('slug', strtolower($schoolCode));
                })
                ->where('status', 'active')
                ->first();

            if (! $school) {
                throw ValidationException::withMessages([
                    'school_code' => 'Enter the active school workspace code provided by your school.',
                ]);
            }

            app(TenantContext::class)->set($school);

            $user = User::query()->where('email', Str::lower(trim((string) $request->input('email'))))->first();

            if (! $user || ! Hash::check((string) $request->input('password'), $user->password)) {
                return null;
            }

            $request->session()->put('active_school_id', $school->id);

            return $user;
        });
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
