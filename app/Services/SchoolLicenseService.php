<?php

namespace App\Services;

use App\Models\School;
use App\Models\SchoolLicense;
use App\Tenancy\SchoolScope;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

class SchoolLicenseService
{
    public function activate(School $school, string $licenseKey): SchoolLicense
    {
        $publicKey = $this->decodeKey((string) config('school.license_public_key'));
        $parts = explode('.', trim($licenseKey));

        if (! function_exists('sodium_crypto_sign_verify_detached') || strlen($publicKey) !== SODIUM_CRYPTO_SIGN_PUBLICKEYBYTES || count($parts) !== 2) {
            throw ValidationException::withMessages(['license_key' => 'License verification is not configured for this installation.']);
        }

        $payloadBytes = $this->decodeKey($parts[0]);
        $signature = $this->decodeKey($parts[1]);

        if (strlen($signature) !== SODIUM_CRYPTO_SIGN_BYTES || $payloadBytes === '') {
            throw ValidationException::withMessages(['license_key' => 'This license key is invalid or was not issued for this installation.']);
        }

        try {
            $payload = json_decode($payloadBytes, true, 512, JSON_THROW_ON_ERROR);
        } catch (Throwable) {
            throw ValidationException::withMessages(['license_key' => 'This license key is invalid or was not issued for this installation.']);
        }

        if (! is_array($payload)) {
            throw ValidationException::withMessages(['license_key' => 'This license key is invalid or was not issued for this installation.']);
        }

        $validSignature = sodium_crypto_sign_verify_detached($signature, $payloadBytes, $publicKey);
        $issuedAt = isset($payload['issued_at']) ? (int) $payload['issued_at'] : 0;
        $expiresAt = isset($payload['expires_at']) ? (int) $payload['expires_at'] : null;
        $licenseId = $payload['license_id'] ?? null;
        $features = $payload['features'] ?? [];

        if (
            ! $validSignature
            || ! is_string($licenseId)
            || ! preg_match('/^[0-9a-f-]{36}$/i', $licenseId)
            || ($payload['installation_id'] ?? null) !== $school->installation_id
            || strtoupper((string) ($payload['school_code'] ?? '')) !== strtoupper($school->code)
            || blank($payload['plan'] ?? null)
            || ! is_array($features)
            || count(array_filter($features, 'is_string')) !== count($features)
            || $issuedAt <= 0
            || $issuedAt > now()->addMinutes(10)->timestamp
            || ($expiresAt !== null && $expiresAt <= now()->timestamp)
        ) {
            throw ValidationException::withMessages(['license_key' => 'This license key is invalid, expired, or was not issued for this installation.']);
        }

        $digest = hash('sha256', $licenseKey);
        $usedLicense = SchoolLicense::withoutGlobalScope(SchoolScope::class)
            ->where('license_id', $licenseId)
            ->first();

        if ($usedLicense) {
            if ($usedLicense->school_id === $school->id && hash_equals($usedLicense->token_digest, $digest)) {
                if ($usedLicense->revoked_at === null && ($usedLicense->expires_at === null || $usedLicense->expires_at->isFuture())) {
                    return $usedLicense;
                }

                throw ValidationException::withMessages(['license_key' => 'This license has expired or been revoked. Request a new activation key.']);
            }

            throw ValidationException::withMessages(['license_key' => 'This one-time license key has already been used.']);
        }

        return DB::transaction(fn () => SchoolLicense::query()->create([
            'license_id' => $licenseId,
            'installation_id' => $school->installation_id,
            'plan' => (string) $payload['plan'],
            'features' => array_values(array_unique(array_map('strval', $features))),
            'token_digest' => $digest,
            'issued_at' => now()->setTimestamp($issuedAt),
            'expires_at' => $expiresAt ? now()->setTimestamp($expiresAt) : null,
            'activated_at' => now(),
        ]));
    }

    private function decodeKey(string $value): string
    {
        $value = strtr($value, '-_', '+/');
        $value .= str_repeat('=', (4 - strlen($value) % 4) % 4);

        return base64_decode($value, true) ?: '';
    }
}
