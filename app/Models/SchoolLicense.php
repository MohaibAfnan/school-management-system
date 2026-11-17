<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SchoolLicense extends Model
{
    use BelongsToSchool;

    protected $fillable = [
        'license_id',
        'installation_id',
        'plan',
        'features',
        'token_digest',
        'issued_at',
        'expires_at',
        'activated_at',
        'revoked_at',
    ];

    protected function casts(): array
    {
        return [
            'features' => 'array',
            'issued_at' => 'datetime',
            'expires_at' => 'datetime',
            'activated_at' => 'datetime',
            'revoked_at' => 'datetime',
        ];
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }
}
