<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\School;
use App\Models\User;
use App\Tenancy\TenantContext;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ProvisionSchool extends Command
{
    protected $signature = 'school:provision {name} {code} {adminName} {adminEmail} {--mode=hosted}';

    protected $description = 'Create an isolated school workspace and its first administrator.';

    public function handle(): int
    {
        $name = trim((string) $this->argument('name'));
        $code = strtoupper(trim((string) $this->argument('code')));
        $email = strtolower(trim((string) $this->argument('adminEmail')));
        $mode = (string) $this->option('mode');

        if ($name === '' || ! preg_match('/^[A-Z0-9-]{3,32}$/', $code) || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('Use a school name, a 3–32 character workspace code, and a valid administrator email.');

            return self::FAILURE;
        }

        if (! in_array($mode, ['hosted', 'local'], true)) {
            $this->error('Mode must be hosted or local.');

            return self::FAILURE;
        }

        if (School::query()->where('code', $code)->exists()) {
            $this->error('That workspace code is already in use.');

            return self::FAILURE;
        }

        $password = $this->secret('Initial administrator password');
        $confirmation = $this->secret('Confirm administrator password');

        if (strlen($password) < 12 || ! hash_equals($password, $confirmation)) {
            $this->error('Passwords must match and be at least 12 characters.');

            return self::FAILURE;
        }

        $school = DB::transaction(function () use ($name, $code, $email, $mode, $password): School {
            $slugBase = Str::slug($name);
            $slug = $slugBase;
            $suffix = 2;

            while (School::query()->where('slug', $slug)->exists()) {
                $slug = $slugBase.'-'.$suffix++;
            }

            $school = School::query()->create([
                'name' => $name,
                'slug' => $slug,
                'code' => $code,
                'installation_id' => (string) Str::uuid(),
                'status' => 'active',
                'deployment_mode' => $mode,
            ]);

            app(TenantContext::class)->set($school);

            $adminRole = Role::query()->firstOrCreate(
                ['slug' => 'admin'],
                ['name' => 'Administrator', 'permissions' => ['all']],
            );

            $user = User::query()->create([
                'school_id' => $school->id,
                'name' => (string) $this->argument('adminName'),
                'email' => $email,
                'password' => Hash::make($password),
                'role_id' => $adminRole->id,
            ]);
            $user->forceFill(['email_verified_at' => now()])->save();

            return $school;
        });

        app(TenantContext::class)->set(null);
        $this->info('School workspace created.');
        $this->line('Workspace code: '.$school->code);
        $this->line('Administrator email: '.$email);
        $this->line('Sign in at /login. Share the temporary password with the administrator securely.');

        return self::SUCCESS;
    }
}
