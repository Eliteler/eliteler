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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ReferralSystemConfigurationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            abort_unless(\App\Support\Admin\AdminSharedProps::features()['referral'], 404);

            return $next($request);
        });
    }

    public function referralSystemConfiguration()
    {
        $config = DB::table('config')->whereIn('config_key', ['referral_commission_type', 'referral_commission_amount', 'referral_minimum_withdraw_amount'])->pluck('config_value', 'config_key');

        return Inertia::render('referrals/settings', [
            'settings' => [
                'type'    => $config['referral_commission_type'] == '0' ? 'percentage' : 'fixed',
                'amount'  => (float) $config['referral_commission_amount'],
                'minimum' => (float) $config['referral_minimum_withdraw_amount'],
            ],
        ]);
    }

    public function updateReferralSystemConfiguration(Request $request)
    {
        $data = $request->validate([
            'referral_commission_type'   => 'required|in:percentage,fixed',
            'referral_commission_amount' => 'required|numeric|min:0' . ($request->input('referral_commission_type') === 'percentage' ? '|max:100' : ''),
            'minimum_withdrawal_amount'  => 'required|numeric|min:0',
        ], [], [
            'referral_commission_amount' => __('commission'),
            'minimum_withdrawal_amount'  => __('minimum payout'),
        ]);

        // Stored as 0 = percentage of the amount paid, 1 = fixed amount
        foreach ([
            'referral_commission_type'         => $data['referral_commission_type'] === 'percentage' ? '0' : '1',
            'referral_commission_amount'       => $data['referral_commission_amount'],
            'referral_minimum_withdraw_amount' => $data['minimum_withdrawal_amount'],
        ] as $key => $value) {
            DB::table('config')->where('config_key', $key)->update(['config_value' => $value]);
        }

        return back()->with('success', __('Referral settings saved. They apply to new referrals.'));
    }
}
