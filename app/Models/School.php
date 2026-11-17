<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class School extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'code',
        'installation_id',
        'status',
        'deployment_mode',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function smtpSettings(): HasOne
    {
        return $this->hasOne(SchoolSmtpSetting::class);
    }

    public function activationRequests(): HasMany
    {
        return $this->hasMany(ActivationRequest::class);
    }

    public function licenses(): HasMany
    {
        return $this->hasMany(SchoolLicense::class);
    }

    public function subscription(): HasOne
    {
        return $this->hasOne(SchoolSubscription::class);
    }
}
