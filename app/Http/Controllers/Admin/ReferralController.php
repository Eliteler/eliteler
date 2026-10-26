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
use App\Referral;
use App\Services\Admin\ReferralService;
use App\Support\Admin\AdminPermissions;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ReferralController extends Controller
{
    public function __construct(private ReferralService $referrals)
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            abort_unless(\App\Support\Admin\AdminSharedProps::features()['referral'], 404);

            return $next($request);
        });
    }

    public function index(Request $request)
    {
        $tab    = in_array($request->query('tab'), ['subscribed', 'signed_up'], true) ? $request->query('tab') : 'all';
        $search = trim((string) $request->query('search', ''));

        $base = fn () => Referral::where('referrals.status', 1)
            ->leftJoin('users as customer', 'customer.user_id', '=', 'referrals.user_id')
            ->leftJoin('users as referrer', 'referrer.user_id', '=', 'referrals.referred_user_id')
            ->when($search !== '', fn ($q) => $q->where(fn ($q) => $q
                ->where('customer.name', 'like', "%{$search}%")->orWhere('customer.email', 'like', "%{$search}%")
                ->orWhere('referrer.name', 'like', "%{$search}%")->orWhere('referrer.email', 'like', "%{$search}%")));

        $rows = $base()
            ->when($tab === 'subscribed', fn ($q) => $q->where('referrals.is_subscribed', 1))
            ->when($tab === 'signed_up', fn ($q) => $q->where('referrals.is_subscribed', 0))
            ->select('referrals.*', 'customer.name as customer_name', 'customer.email as customer_email', 'referrer.name as referrer_name', 'referrer.email as referrer_email')
            ->orderByDesc('referrals.created_at')
            ->paginate(20)
            ->withQueryString()
            ->through(function ($row) {
                $scheme = json_decode($row->referral_scheme ?? '', true) ?: [];

                return [
                    'id'         => $row->id,
                    'customer'   => $row->customer_name ? ['id' => $row->user_id, 'name' => $row->customer_name, 'email' => $row->customer_email] : null,
                    'referrer'   => $row->referrer_name ? ['id' => $row->referred_user_id, 'name' => $row->referrer_name, 'email' => $row->referrer_email] : null,
                    'subscribed' => (int) $row->is_subscribed === 1,
                    'commission' => (int) $row->is_subscribed === 1 ? AdminPermissions::amount((float) ($scheme['referral_amount'] ?? 0)) : null,
                    'date'       => $row->created_at ? Carbon::parse($row->created_at)->toIso8601String() : null,
                ];
            });

        $hidesRevenue = AdminPermissions::hidesRevenue($request->user());

        $counts = $base()->selectRaw('COUNT(*) as `all`, SUM(referrals.is_subscribed = 1) as subscribed, SUM(referrals.is_subscribed = 0) as signed_up')->first();
        $earned = $hidesRevenue ? 0 : array_sum($this->referrals->earnings());
        $paid   = $hidesRevenue ? 0 : array_sum($this->referrals->paidOut());

        return Inertia::render('referrals/index', [
            'referrals' => $rows,
            'tab'       => $tab,
            'search'    => $search,
            'counts'    => ['all' => (int) $counts->all, 'subscribed' => (int) $counts->subscribed, 'signed_up' => (int) $counts->signed_up],
            'stats'     => [
                'referrals'  => Referral::where('status', 1)->count(),
                'subscribed' => Referral::where('status', 1)->where('is_subscribed', 1)->count(),
            ] + ($hidesRevenue ? [] : [
                // Commission and payouts are money: left out when revenue is hidden
                'earned'     => $earned,
                'paidOut'    => $paid,
                'owed'       => round(max(0, $earned - $paid), 2),
                'requests'   => DB::table('referral_withdraw_requests')->where('status', 1)->where('payment_status', ReferralService::PENDING)->count(),
            ]),
        ]);
    }
}
