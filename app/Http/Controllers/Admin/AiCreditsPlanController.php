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

use App\AiCreditsPlan;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

/**
 * AI credit packs customers can buy. status: active, inactive, deleted.
 */
class AiCreditsPlanController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $sales = DB::table('ai_credits_transactions')
            ->where('payment_status', 'paid')
            ->selectRaw('ai_credits_plan_id, COUNT(*) as orders, SUM(amount) as revenue')
            ->groupBy('ai_credits_plan_id')
            ->get()
            ->keyBy('ai_credits_plan_id');

        $plans = AiCreditsPlan::where('status', '!=', 'deleted')->orderByRaw("status = 'active' DESC")->orderBy('plan_price')->get()
            ->map(fn (AiCreditsPlan $plan) => [
                'id'          => $plan->ai_credits_plan_id,
                'name'        => $plan->plan_name,
                'description' => $plan->plan_description,
                'price'       => (float) $plan->plan_price,
                'credits'     => (int) $plan->no_of_ai_credits,
                'inAppId'     => $plan->in_app_product_id,
                'active'      => $plan->status === 'active',
                'orders'      => (int) ($sales[$plan->ai_credits_plan_id]->orders ?? 0),
                'revenue'     => round((float) ($sales[$plan->ai_credits_plan_id]->revenue ?? 0), 2),
            ]);

        return Inertia::render('ai-credits/plans', [
            'plans' => $plans,
            'inApp' => plugin_active('MobileAppAPI'),
            'open'  => $request->query('edit') ?: ($request->boolean('new') ? 'new' : null),
        ]);
    }

    // Old pages: open the dialog on the plans page
    public function createPlan()
    {
        return redirect()->route('admin.ai.credits.plans', ['new' => 1]);
    }

    public function editAiCreditsPlan($id)
    {
        return redirect()->route('admin.ai.credits.plans', ['edit' => $id]);
    }

    public function savePlan(Request $request)
    {
        $data = $request->validate($this->rules(), [], $this->attributes());

        $plan                     = new AiCreditsPlan();
        $plan->ai_credits_plan_id = uniqid('aicp_');
        $plan->status             = 'active';
        $this->fill($plan, $data)->save();

        return back()->with('success', __('Credit pack “:name” created.', ['name' => $plan->plan_name]));
    }

    public function updateAiCreditsPlan(Request $request, $id)
    {
        $plan = AiCreditsPlan::where('ai_credits_plan_id', $id)->firstOrFail();
        $data = $request->validate($this->rules(), [], $this->attributes());

        // Status is kept: editing no longer re-activates a deactivated pack.
        $this->fill($plan, $data)->save();

        return back()->with('success', __('Credit pack updated.'));
    }

    public function statusAiCreditsPlan(Request $request)
    {
        $data = $request->validate(['id' => 'required|string', 'action' => 'required|in:activate,deactivate']);

        AiCreditsPlan::where('ai_credits_plan_id', $data['id'])->update(['status' => $data['action'] === 'activate' ? 'active' : 'inactive']);

        return back()->with('success', $data['action'] === 'activate' ? __('Credit pack is now on sale.') : __('Credit pack hidden from customers.'));
    }

    public function deleteAiCreditsPlan(Request $request)
    {
        $data = $request->validate(['id' => 'required|string']);

        AiCreditsPlan::where('ai_credits_plan_id', $data['id'])->update(['status' => 'deleted']);

        return back()->with('success', __('Credit pack deleted.'));
    }

    private function rules(): array
    {
        return [
            'plan_name'         => 'required|string|max:191',
            'plan_description'  => 'required|string|max:1000',
            'plan_price'        => 'required|numeric|min:0',
            'no_of_ai_credits'  => 'required|integer|min:1',
            'in_app_product_id' => 'nullable|string|max:255',
        ];
    }

    private function attributes(): array
    {
        return [
            'plan_name'         => __('name'),
            'plan_description'  => __('description'),
            'plan_price'        => __('price'),
            'no_of_ai_credits'  => __('credits'),
            'in_app_product_id' => __('in-app product ID'),
        ];
    }

    private function fill(AiCreditsPlan $plan, array $data): AiCreditsPlan
    {
        $plan->plan_name         = $data['plan_name'];
        $plan->plan_description  = $data['plan_description'];
        $plan->plan_price        = $data['plan_price'];
        $plan->no_of_ai_credits  = $data['no_of_ai_credits'];
        $plan->in_app_product_id = $data['in_app_product_id'] ?? null;

        return $plan;
    }
}
