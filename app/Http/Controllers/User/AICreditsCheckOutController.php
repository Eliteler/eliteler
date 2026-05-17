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

namespace App\Http\Controllers\User;

use App\AiCreditsPlan;
use App\AppliedCoupon;
use App\Coupon;
use App\Currency;
use App\Gateway;
use App\Http\Controllers\Controller;
use App\Setting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AICreditsCheckOutController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    // AI Credits Checkout
    public function checkout(Request $request, $planId)
    {
        // Get the plan details
        $plan = AiCreditsPlan::where('ai_credits_plan_id', $planId)->where('status', 'active')->first();

        // Check if the plan exists
        if (!$plan) {
            return redirect()->route('user.ai.credits.plans')->with('failed', __('This AI Credits plan does not exist.'));
        }

        $coupon_code = '';

        // Queries
        $settings = Setting::where('status', 1)->first();
        $config = DB::table('config')->get()->toArray();
        $currency = Currency::where('iso_code', $config[1]->config_value)->first();
        $gateways = Gateway::where('is_status', 'enabled')->where('status', "1")->get();

        // Get total price
        $total = ((float) ($plan->plan_price) * (float) ($config[25]->config_value) / 100) + (float) ($plan->plan_price);
        $total = number_format($total, 2, '.', '');

        return view('user.pages.ai-credits-plans.checkout', compact('plan', 'settings', 'currency', 'gateways', 'total', 'coupon_code', 'config'));
    }

    // Checkout coupon
    public function aiCreditsCheckoutCoupon(Request $request, $planId)
    {
        // Queries
        $config = DB::table('config')->get();
        $tax = (float) $config[25]->config_value;
        $total = 0;
        $applied = false;

        // Coupon code
        $coupon_code = Str::upper($request->coupon_code);

        // Get ai credits plan details
        $plan = AiCreditsPlan::where('ai_credits_plan_id', $planId)->where('status', 'active')->first();

        // Check if ai credits plan is active
        if (!$plan) {
            return response()->json(['status' => 'failed', 'message' => __('This AI Credits plan does not exist.')]);
        }

        // Get coupon details
        $couponDetails = Coupon::where('used_for', 'ai_credits')->where('coupon_code', $coupon_code)->where('status', 1)->first();

        // Check coupon exists
        if (!$couponDetails) {
            return response()->json(['status' => 'failed', 'message' => __('Coupon not vaild!')]);
        }

        // Check coupon expiry
        if ($couponDetails->coupon_expired_on < Carbon::now()) {
            return response()->json(['success' => false, 'message' => trans('Coupon not vaild!')]);
        }

        // Check user already has this coupon
        $userCouponCount = AppliedCoupon::where('user_id', Auth::user()->id)->where('coupon_id', $couponDetails->coupon_id)->where('status', 1)->count();
        if ($userCouponCount >= $couponDetails->coupon_user_usage_limit) {
            return response()->json(['success' => false, 'message' => trans('Coupon already used.')]);
        }

        // Check total already has this coupon
        $totalCouponCount = AppliedCoupon::where('coupon_id', $couponDetails->coupon_id)->where('status', 1)->count();
        if ($totalCouponCount >= $couponDetails->coupon_total_usage_limit) {
            return response()->json(['success' => false, 'message' => trans('Total number of users already reached.')]);
        }

        // Check coupon type
        if ($couponDetails->coupon_type == 'fixed') {
            $appliedTaxInTotal = ($plan->plan_price * $tax) / 100;
            $discountPrice = $couponDetails->coupon_amount;
            $total = ($plan->plan_price + $appliedTaxInTotal) - $discountPrice;
        } else {
            // Applied tax in total
            $appliedTaxInTotal = ($plan->plan_price * $tax) / 100;
            // Get discount in plan price
            $discountPrice = ($plan->plan_price * $couponDetails->coupon_amount) / 100;

            // Total
            $total = ($plan->plan_price + $appliedTaxInTotal) - $discountPrice;
        }

        // Change applied status
        $applied = true;

        return response()->json(['success' => true, 'applied' => $applied, 'coupon_code' => $coupon_code, 'coupon_id' => $couponDetails->coupon_id, 'discountPrice' => $discountPrice, 'total' => $total]);
    }
}
