<?php

namespace App\Http\Controllers\Developer;

use App\Http\Controllers\Controller;
use App\Models\PlatformAuditLog;
use App\Models\School;
use App\Models\SchoolPlan;
use App\Models\SchoolSubscription;
use App\Models\User;
use App\Tenancy\SchoolScope;
use Inertia\Inertia;
use Inertia\Response;

class DeveloperDashboardController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('developer/Dashboard', [
            'stats' => [
                'schools' => School::withTrashed()->count(),
                'activeSchools' => School::query()->where('status', 'active')->count(),
                'disabledSchools' => School::query()->where('status', '!=', 'active')->count(),
                'students' => User::withoutGlobalScope(SchoolScope::class)->whereHas('role', fn ($query) => $query->where('slug', 'student'))->count(),
                'plans' => SchoolPlan::query()->count(),
                'subscriptions' => SchoolSubscription::query()->whereIn('status', ['active', 'trialing'])->count(),
            ],
            'recentActivity' => PlatformAuditLog::query()
                ->with('school:id,name,code')
                ->latest('created_at')
                ->limit(10)
                ->get(['id', 'platform_admin_email', 'school_id', 'action', 'target_type', 'target_id', 'created_at']),
        ]);
    }
}
