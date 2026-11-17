<?php

namespace App\Services;

use App\Models\PlatformAdmin;
use App\Models\PlatformAuditLog;
use App\Models\School;
use Illuminate\Http\Request;

class PlatformAudit
{
    public function record(
        Request $request,
        ?PlatformAdmin $admin,
        string $action,
        ?School $school = null,
        ?string $targetType = null,
        int|string|null $targetId = null,
        ?array $before = null,
        ?array $after = null,
    ): void {
        PlatformAuditLog::query()->create([
            'platform_admin_id' => $admin?->id,
            'platform_admin_email' => $admin?->email ?? 'unknown',
            'school_id' => $school?->id,
            'action' => $action,
            'target_type' => $targetType,
            'target_id' => $targetId ? (int) $targetId : null,
            'before' => $before,
            'after' => $after,
            'ip_address' => $request->ip(),
            'user_agent' => mb_substr((string) $request->userAgent(), 0, 500),
            'created_at' => now(),
        ]);
    }
}
