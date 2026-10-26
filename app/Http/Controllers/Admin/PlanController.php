<?php

/*
 |--------------------------------------------------------------------------
 | GoBiz vCard SaaS
 |--------------------------------------------------------------------------
 | Developed by NativeCode © 2021 - https://nativecode.in
 | All rights reserved
 | Unauthorized distribution is prohibited
 |--------------------------------------------------------------------------
*/

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Plan;
use App\Services\Admin\PlanService;
use App\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PlanController extends Controller
{
    private const TABS = ['all' => null, 'active' => 1, 'disabled' => 0];

    public function __construct(private PlanService $plans)
    {
        $this->middleware('auth');
    }

    // Plans
    public function plans(Request $request)
    {
        $tab    = array_key_exists($request->query('status'), self::TABS) ? $request->query('status') : 'all';
        $search = trim((string) $request->query('search', ''));

        $subscribers = $this->plans->activeSubscribers();

        $plans = Plan::query()
            ->when($tab === 'all', fn($q) => $q->where('status', '!=', 2))
            ->when($tab !== 'all', fn($q) => $q->where('status', self::TABS[$tab]))
            ->when($search !== '', fn($q) => $q->where('plan_name', 'like', "%{$search}%"))
            ->orderByRaw('status = 1 DESC')
            ->orderBy('plan_price')
            ->get()
            ->map(fn(Plan $plan) => $this->plans->present($plan, $subscribers[$plan->plan_id] ?? 0));

        $counts = Plan::query()
            ->where('status', '!=', 2)
            ->when($search !== '', fn($q) =>
                $q->where('plan_name', 'like', "%{$search}%")
            )
            ->selectRaw('
                COUNT(*) as `all`,
                SUM(status = 1) as active,
                SUM(status = 0) as disabled
            ')
            ->first();

        return Inertia::render('plans/index', [
            'plans'   => $plans,
            'tab'     => $tab,
            'search'  => $search,
            'counts'  => collect(array_keys(self::TABS))->mapWithKeys(fn($key) => [$key => (int) $counts->{$key} ?? 0]),
            'plugins' => $this->plans->plugins(),
        ]);
    }

    // New plan
    public function addPlan()
    {
        return Inertia::render('plans/form', [
            'plan'    => null,
            'plugins' => $this->plans->plugins(),
        ]);
    }

    // Save new plan
    public function savePlan(Request $request)
    {
        $data = $request->validate($this->plans->rules((string) $request->input('plan_type')), [], $this->plans->attributes());

        $plan          = new Plan();
        $plan->plan_id = uniqid();
        $this->plans->fill($plan, $data)->save();

        return redirect()->route('admin.plans')->with('success', __('Plan “:name” created.', ['name' => $plan->plan_name]));
    }

    // Edit plan
    public function editPlan($id)
    {
        $plan = Plan::where('plan_id', $id)->first();

        if (! $plan) {
            return redirect()->route('admin.plans')->with('failed', __('Not Found!'));
        }

        return Inertia::render('plans/form', [
            'plan'    => $this->plans->present($plan, $this->plans->activeSubscribers()[$plan->plan_id] ?? 0),
            'plugins' => $this->plans->plugins(),
        ]);
    }

    // Update plan
    public function updatePlan(Request $request)
    {
        $plan = Plan::where('plan_id', (string) $request->input('plan_id'))->first();

        if (! $plan) {
            return redirect()->route('admin.plans')->with('failed', __('Not Found!'));
        }

        $data = $request->validate($this->plans->rules((string) $request->input('plan_type')), [], $this->plans->attributes());
        $this->plans->fill($plan, $data)->save();

        return redirect()->route('admin.edit.plan', $plan->plan_id)->with('success', __('Plan updated.'));
    }

    // Enable / disable
    public function statusPlan(Request $request)
    {
        $data = $request->validate([
            'id'     => 'required|string',
            'status' => 'required|in:active,disabled',
        ]);

        $plan = Plan::where('plan_id', $data['id'])->first();

        if (! $plan) {
            return back()->with('failed', __('Not Found!'));
        }

        $plan->status = $data['status'] === 'active' ? 1 : 0;
        $plan->save();

        return back()->with('success', match (true) {
            $data['status'] === 'active' => __('Plan enabled.'),
            default                      => __('Plan disabled.'),
        });
    }

    // Delete
    public function deletePlan(Request $request)
    {
        $data = $request->validate(['id' => 'required|string']);

        if (User::where('plan_id', $data['id'])->exists()) {
            return back()->with('failed', __('This plan is in use by customers and cannot be deleted.'));
        }

        $updated = Plan::where('plan_id', $data['id'])->delete();

        return $updated
            ? back()->with('success', __('Plan deleted.'))
            : back()->with('failed', __('Not Found!'));
    }
}
