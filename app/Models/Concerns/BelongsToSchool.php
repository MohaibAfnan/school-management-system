<?php

namespace App\Models\Concerns;

use App\Tenancy\SchoolScope;
use App\Tenancy\TenantContext;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use LogicException;

trait BelongsToSchool
{
    protected static function bootBelongsToSchool(): void
    {
        static::addGlobalScope(new SchoolScope);

        static::creating(function (Model $model): void {
            $tenantId = app(TenantContext::class)->id();

            if ($tenantId === null) {
                if (! App::runningInConsole()) {
                    throw new LogicException('A school context is required to create school data.');
                }

                return;
            }

            if ($model->getAttribute('school_id') !== null && (int) $model->getAttribute('school_id') !== $tenantId) {
                throw new LogicException('School data cannot be created outside the active school context.');
            }

            $model->setAttribute('school_id', $tenantId);
        });

        static::updating(function (Model $model): void {
            $tenantId = app(TenantContext::class)->id();

            if ($tenantId !== null && (int) $model->getOriginal('school_id') !== $tenantId) {
                throw new LogicException('School data cannot be changed outside the active school context.');
            }

            if ($model->isDirty('school_id')) {
                throw new LogicException('School ownership cannot be changed after record creation.');
            }
        });
    }
}
