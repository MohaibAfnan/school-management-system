<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GenerateSchoolLicenseKeys extends Command
{
    protected $signature = 'school:license:keypair';

    protected $description = 'Generate the offline-license signing keypair in the local issuer environment.';

    public function handle(): int
    {
        if (! app()->environment('local')) {
            $this->error('Signing keys may only be generated in the trusted local issuer environment.');

            return self::FAILURE;
        }

        if (! function_exists('sodium_crypto_sign_keypair')) {
            $this->error('The PHP Sodium extension is required.');

            return self::FAILURE;
        }

        $path = storage_path('app/private/school-license-signing.key');

        if (is_file($path)) {
            $this->error('A signing key already exists. It will not be overwritten.');

            return self::FAILURE;
        }

        if (! is_dir(dirname($path))) {
            mkdir(dirname($path), 0700, true);
        }

        $keyPair = sodium_crypto_sign_keypair();
        $secretKey = sodium_crypto_sign_secretkey($keyPair);
        $publicKey = sodium_crypto_sign_publickey($keyPair);

        if (file_put_contents($path, base64_encode($secretKey), LOCK_EX) === false) {
            $this->error('The signing key could not be saved.');

            return self::FAILURE;
        }

        @chmod($path, 0600);

        $this->info('Signing keypair generated. Back up the private signing key securely.');
        $this->line('Private key file: '.$path);
        $this->line('Public verification key (safe to distribute): '.base64_encode($publicKey));
        $this->warn('Never include the private signing key in Git, a customer installation, or an email.');

        return self::SUCCESS;
    }
}
