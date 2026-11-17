<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Throwable;

class IssueSchoolLicense extends Command
{
    protected $signature = 'school:license:issue {schoolCode} {installationId} {--plan=standard} {--features=students,teachers,attendance,results} {--expires=}';

    protected $description = 'Issue an installation-scoped signed license key from the trusted local issuer environment.';

    public function handle(): int
    {
        if (! app()->environment('local')) {
            $this->error('License keys may only be issued from the trusted local issuer environment.');

            return self::FAILURE;
        }

        $privateKeyPath = storage_path('app/private/school-license-signing.key');
        $secret = is_file($privateKeyPath) ? $this->decode((string) file_get_contents($privateKeyPath)) : '';

        if (! function_exists('sodium_crypto_sign_detached') || strlen($secret) !== SODIUM_CRYPTO_SIGN_SECRETKEYBYTES) {
            $this->error('The issuer private key is missing or invalid.');

            return self::FAILURE;
        }

        $schoolCode = strtoupper(trim((string) $this->argument('schoolCode')));
        $installationId = strtolower(trim((string) $this->argument('installationId')));
        $plan = trim((string) $this->option('plan'));

        if (! preg_match('/^[A-Z0-9-]{3,32}$/', $schoolCode) || ! Str::isUuid($installationId) || $plan === '') {
            $this->error('The workspace code, installation ID, or plan is invalid.');

            return self::FAILURE;
        }

        $expiresAt = null;
        if ($this->option('expires')) {
            try {
            $expiresAt = \Illuminate\Support\Carbon::parse((string) $this->option('expires'))->timestamp;
            } catch (Throwable) {
                $this->error('The expiry must be a valid date/time.');

                return self::FAILURE;
            }
        }

        $payload = json_encode([
            'license_id' => (string) Str::uuid(),
            'installation_id' => $installationId,
            'school_code' => $schoolCode,
            'plan' => $plan,
            'features' => array_values(array_filter(array_map('trim', explode(',', (string) $this->option('features'))))),
            'issued_at' => now()->timestamp,
            'expires_at' => $expiresAt,
        ], JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);

        $signature = sodium_crypto_sign_detached($payload, $secret);

        $this->newLine();
        $this->line('Signed one-time activation key (share securely with this school):');
        $this->line($this->encode($payload).'.'.$this->encode($signature));
        $this->warn('Never copy the issuer private key to a customer installation or include it in an email.');

        return self::SUCCESS;
    }

    private function encode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }

    private function decode(string $value): string
    {
        $value = strtr($value, '-_', '+/');
        $value .= str_repeat('=', (4 - strlen($value) % 4) % 4);

        return base64_decode($value, true) ?: '';
    }
}
