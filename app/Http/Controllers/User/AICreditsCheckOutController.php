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

        // Get AI Credits plan details
        $plan = AiCreditsPlan::where('ai_credits_plan_id', $planId)->where('status', 'active')->first();

        // Check if AI Credits plan is active
        if (!$plan) {
            return response()->json([
                'status' => 'failed',
                'message' => __('This AI Credits plan does not exist.')
            ]);
        }

        // Get coupon details
        $couponDetails = Coupon::where('used_for', 'ai_credits')
            ->where('coupon_code', $coupon_code)
            ->where('status', 1)
            ->first();

        // Check coupon exists
        if (!$couponDetails) {
            return response()->json([
                'success' => false,
                'message' => __('Coupon not vaild!')
            ]);
        }

        // Check coupon expiry
        if ($couponDetails->coupon_expired_on < Carbon::now()) {
            return response()->json([
                'success' => false,
                'message' => __('Coupon not vaild!')
            ]);
        }

        // Check if coupon is applicable for this specific AI Credits plan or all plans
        $allowedPlans = json_decode($couponDetails->plan_id, true);
        if (!is_array($allowedPlans)) {
            $allowedPlans = $couponDetails->plan_id ? [$couponDetails->plan_id] : ['all'];
        }

        if (!in_array('all', $allowedPlans) && !in_array($plan->ai_credits_plan_id, $allowedPlans)) {
            return response()->json([
                'success' => false,
                'message' => __('This coupon is not applicable for the selected AI Credits plan!')
            ]);
        }

        // Check user already has this coupon
        $userCouponCount = AppliedCoupon::where('user_id', Auth::user()->id)
            ->where('coupon_id', $couponDetails->coupon_id)
            ->where('status', 1)
            ->count();

        if ($userCouponCount >= $couponDetails->coupon_user_usage_limit) {
            return response()->json([
                'success' => false,
                'message' => __('Coupon already used.')
            ]);
        }

        // Check total already has this coupon
        $totalCouponCount = AppliedCoupon::where('coupon_id', $couponDetails->coupon_id)
            ->where('status', 1)
            ->count();

        if ($totalCouponCount >= $couponDetails->coupon_total_usage_limit) {
            return response()->json([
                'success' => false,
                'message' => __('Total number of users already reached.')
            ]);
        }

        // AI Credits plan price
        $planPrice = (float) $plan->plan_price;

        // Calculate GST on plan price
        $appliedTaxInTotal = ($planPrice * $tax) / 100;

        // Calculate coupon discount
        if ($couponDetails->coupon_type == 'fixed') {
            $discountPrice = (float) $couponDetails->coupon_amount;
        } else {
            // Percentage coupon is applied to plan price + GST
            $discountPrice = (($planPrice + $appliedTaxInTotal) * (float) $couponDetails->coupon_amount) / 100;
        }

        // Prevent discount from exceeding plan price + GST
        $discountPrice = min($discountPrice, $planPrice + $appliedTaxInTotal);

        // Calculate final total
        $total = ($planPrice + $appliedTaxInTotal) - $discountPrice;

        // Prevent negative total
        $total = max($total, 0);

        // Change applied status
        $applied = true;

        return response()->json([
            'success' => true,
            'applied' => $applied,
            'coupon_code' => $coupon_code,
            'coupon_id' => $couponDetails->coupon_id,
            'discountPrice' => round($discountPrice, 2),
            'tax' => round($appliedTaxInTotal, 2),
            'total' => round($total, 2)
        ]);
    }
}
