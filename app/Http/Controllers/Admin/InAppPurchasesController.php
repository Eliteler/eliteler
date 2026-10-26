<?php

/*
 |--------------------------------------------------------------------------
 | Eliteler vCard SaaS
 |--------------------------------------------------------------------------
 | Developed by NativeCode © 2021 - https://nativecode.in
 | All rights reserved
 | Unauthorized distribution is prohibited
 |--------------------------------------------------------------------------
*/

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

/**
 * Purchases made in the mobile app (MobileAppAPI plugin) through the App
 * Store / Google Play. Read-only; the stores are the source of truth.
 * in_app_purchases.plan_id is a plan or an AI credit pack.
 */
class InAppPurchasesController extends Controller
{
    private const TABS = [
        'all'      => null,
        'active'   => ['SUCCESS', 'ACTIVE'],
        'pending'  => ['PENDING'],
        'ended'    => ['EXPIRED', 'CANCELED', 'CANCELLED'],
    ];

    public function inAppPurchases(Request $request)
    {
        $tab      = array_key_exists($request->query('tab'), self::TABS) ? $request->query('tab') : 'all';
        $platform = in_array($request->query('platform'), ['android', 'ios'], true) ? $request->query('platform') : 'all';
        $search   = trim((string) $request->query('search', ''));

        $base = fn () => DB::table('in_app_purchases')
            ->leftJoin('users', 'users.user_id', '=', 'in_app_purchases.user_id')
            ->leftJoin('plans', 'plans.plan_id', '=', 'in_app_purchases.plan_id')
            ->leftJoin('ai_credits_plans', 'ai_credits_plans.ai_credits_plan_id', '=', 'in_app_purchases.plan_id')
            ->when($platform !== 'all', fn ($q) => $q->where('in_app_purchases.platform', $platform))
            ->when($search !== '', fn ($q) => $q->where(fn ($q) => $q
                ->where('in_app_purchases.order_id', 'like', "%{$search}%")
                ->orWhere('in_app_purchases.transaction_id', 'like', "%{$search}%")
                ->orWhere('users.name', 'like', "%{$search}%")
                ->orWhere('users.email', 'like', "%{$search}%")));

        $purchases = $base()
            ->when(self::TABS[$tab], fn ($q, $statuses) => $q->whereIn(DB::raw('UPPER(in_app_purchases.transaction_status)'), $statuses))
            ->select([
                'in_app_purchases.id', 'in_app_purchases.order_id', 'in_app_purchases.transaction_id', 'in_app_purchases.platform',
                'in_app_purchases.purchase_type', 'in_app_purchases.transaction_currency', 'in_app_purchases.transaction_amount',
                'in_app_purchases.transaction_status', 'in_app_purchases.created_at',
                'users.user_id as customer_id', 'users.name', 'users.email',
                DB::raw('COALESCE(plans.plan_name, ai_credits_plans.plan_name) as item'),
                DB::raw('CASE WHEN ai_credits_plans.id IS NOT NULL THEN "ai" ELSE "plan" END as kind'),
            ])
            ->orderByDesc('in_app_purchases.id')
            ->paginate(25)
            ->withQueryString()
            ->through(fn ($row) => [
                'id'        => $row->id,
                'order'     => $row->order_id ?: $row->transaction_id,
                'platform'  => $row->platform,
                'type'      => $row->purchase_type === 'one_time' ? 'one_time' : 'subscription',
                'item'      => $row->item,
                'kind'      => $row->kind,
                'currency'  => $row->transaction_currency,
                'amount'    => (float) $row->transaction_amount,
                'status'    => strtolower((string) $row->transaction_status) ?: 'pending',
                'date'      => $row->created_at ? Carbon::parse($row->created_at)->toIso8601String() : null,
                'customer'  => $row->customer_id ? ['id' => $row->customer_id, 'name' => $row->name, 'email' => $row->email] : null,
            ]);

        $counts = $base()->selectRaw("COUNT(*) as `all`,
            SUM(UPPER(in_app_purchases.transaction_status) IN ('SUCCESS','ACTIVE')) as active,
            SUM(UPPER(in_app_purchases.transaction_status) = 'PENDING') as pending,
            SUM(UPPER(in_app_purchases.transaction_status) IN ('EXPIRED','CANCELED','CANCELLED')) as ended")->first();

        return Inertia::render('in-app-purchases/index', [
            'purchases' => $purchases,
            'filters'   => compact('tab', 'platform', 'search'),
            'counts'    => collect(array_keys(self::TABS))->mapWithKeys(fn ($k) => [$k => (int) $counts->{$k}]),
        ]);
    }
}
