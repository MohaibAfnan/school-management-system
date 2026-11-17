<?php

namespace App\Console\Commands;

use App\Models\PlatformAdmin;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreatePlatformAdmin extends Command
{
    protected $signature = 'platform:admin:create {name} {email}';

    protected $description = 'Create a developer portal administrator.';

    public function handle(): int
    {
        $email = mb_strtolower(trim((string) $this->argument('email')));

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('Enter a valid email address.');

            return self::FAILURE;
        }

        if (PlatformAdmin::query()->where('email', $email)->exists()) {
            $this->error('A platform administrator with this email already exists.');

            return self::FAILURE;
        }

        $password = $this->secret('Platform administrator password');
        $confirmation = $this->secret('Confirm password');

        if (strlen($password) < 14 || ! hash_equals($password, $confirmation)) {
            $this->error('Passwords must match and be at least 14 characters long.');

            return self::FAILURE;
        }

        PlatformAdmin::query()->create([
            'name' => trim((string) $this->argument('name')),
            'email' => $email,
            'password' => Hash::make($password),
        ]);

        $this->info('Developer portal administrator created.');
        $this->line('Sign in at /developer/login.');

        return self::SUCCESS;
    }
}
