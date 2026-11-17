<?php

namespace App\Tenancy;

use App\Models\School;

class TenantContext
{
    private ?School $school = null;

    public function set(?School $school): void
    {
        $this->school = $school;
    }

    public function school(): ?School
    {
        return $this->school;
    }

    public function id(): ?int
    {
        return $this->school?->getKey();
    }
}
