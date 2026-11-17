<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SchoolSmtpSetting extends Model
{
    use BelongsToSchool;

    protected $fillable = [
        'host',
        'port',
        'scheme',
        'username',
        'password',
        'from_email',
        'from_name',
        'last_tested_at',
        'last_test_status',
    ];

    protected function casts(): array
    {
        return [
            'username' => 'encrypted',
            'password' => 'encrypted',
            'last_tested_at' => 'datetime',
        ];
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function mailerConfiguration(): array
    {
        return [
            'transport' => 'smtp',
            'scheme' => $this->scheme === 'smtps' ? 'smtps' : 'smtp',
            'host' => $this->host,
            'port' => $this->port,
            'username' => $this->username,
            'password' => $this->password,
            'timeout' => 10,
            'local_domain' => parse_url((string) config('app.url'), PHP_URL_HOST),
        ];
    }
}
