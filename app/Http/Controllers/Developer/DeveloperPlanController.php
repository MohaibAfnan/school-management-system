<?php

namespace App\Http\Controllers\Developer;

use App\Http\Controllers\Controller;
use App\Models\SchoolPlan;
use App\Services\PlatformAudit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class DeveloperPlanController extends Controller
{
    public function index(): Response
    {
        $plans = SchoolPlan::query()
            ->withCount('subscriptions')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return Inertia::render('developer/Plans', ['plans' => $plans]);
    }

    public function store(Request $request, PlatformAudit $audit): RedirectResponse
    {
        $data = $this->validated($request);
        $plan = SchoolPlan::query()->create($this->attributes($data));
        $audit->record($request, $request->user('platform'), 'plan.created', targetType: 'school_plan', targetId: $plan->id, after: $plan->only(['name', 'slug', 'price', 'currency', 'billing_interval', 'is_active']));

        return back()->with('status', 'Package created.');
    }

    public function update(Request $request, SchoolPlan $plan, PlatformAudit $audit): RedirectResponse
    {
        $data = $this->validated($request, $plan);
        $before = $plan->only(['name', 'slug', 'price', 'currency', 'billing_interval', 'student_limit', 'staff_limit', 'features', 'is_active']);
        $plan->fill($this->attributes($data))->save();
        $audit->record($request, $request->user('platform'), 'plan.updated', targetType: 'school_plan', targetId: $plan->id, before: $before, after: $plan->only(array_keys($before)));

        return back()->with('status', 'Package saved.');
    }

    public function toggle(Request $request, SchoolPlan $plan, PlatformAudit $audit): RedirectResponse
    {
        $before = ['is_active' => $plan->is_active];
        $plan->forceFill(['is_active' => ! $plan->is_active])->save();
        $audit->record($request, $request->user('platform'), 'plan.status_changed', targetType: 'school_plan', targetId: $plan->id, before: $before, after: ['is_active' => $plan->is_active]);

        return back()->with('status', $plan->is_active ? 'Package enabled.' : 'Package disabled. Existing subscriptions are retained.');
    }

    public function destroy(Request $request, SchoolPlan $plan, PlatformAudit $audit): RedirectResponse
    {
        if ($plan->subscriptions()->exists()) {
            return back()->withErrors(['plan' => 'This package is assigned to a school. Disable it to prevent new assignments while preserving subscription history.']);
        }

        $before = $plan->only(['name', 'slug', 'price', 'currency']);
        $plan->delete();
        $audit->record($request, $request->user('platform'), 'plan.archived', targetType: 'school_plan', targetId: $plan->id, before: $before);

        return back()->with('status', 'Package archived.');
    }

    private function validated(Request $request, ?SchoolPlan $plan = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:191'],
            'slug' => ['nullable', 'string', 'max:191', 'alpha_dash', Rule::unique('school_plans', 'slug')->ignore($plan?->id)],
            'description' => ['nullable', 'string', 'max:2000'],
            'price' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'currency' => ['required', 'string', 'size:3', 'alpha'],
            'billing_interval' => ['required', Rule::in(['monthly', 'yearly', 'lifetime'])],
            'student_limit' => ['nullable', 'integer', 'min:1', 'max:10000000'],
            'staff_limit' => ['nullable', 'integer', 'min:1', 'max:1000000'],
            'features_text' => ['nullable', 'string', 'max:4000'],
            'sort_order' => ['nullable', 'integer', 'between:0,65535'],
            'is_active' => ['nullable', 'boolean'],
        ]);
    }

    private function attributes(array $data): array
    {
        $features = preg_split('/\R/', (string) ($data['features_text'] ?? ''), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $slug = trim((string) ($data['slug'] ?? ''));

        return [
            'name' => trim($data['name']),
            'slug' => $slug !== '' ? Str::slug($slug) : Str::slug($data['name']),
            'description' => $data['description'] ?? null,
            'price' => $data['price'],
            'currency' => strtoupper($data['currency']),
            'billing_interval' => $data['billing_interval'],
            'student_limit' => $data['student_limit'] ?? null,
            'staff_limit' => $data['staff_limit'] ?? null,
            'features' => array_values(array_unique(array_map('trim', $features))),
            'sort_order' => $data['sort_order'] ?? 0,
            'is_active' => (bool) ($data['is_active'] ?? false),
        ];
    }
}
