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

use App\Plan;
use App\User;
use App\Coupon;
use App\Gateway;
use App\Setting;
use App\Currency;
use Carbon\Carbon;
use App\Transaction;
use App\BusinessCard;
use App\AppliedCoupon;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class CheckOutController extends Controller
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

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */

    // Checkout Page
    public function index(Request $request, $id)
    {
        // Selected plan
        $selected_plan = Plan::where('plan_id', $id)->where('status', 1)->first();
        $applied = false; 
        $coupon_code = '';

        // check recurring payment gateways
        if (Schema::hasTable(('recurring_payment_gateways'))) {
            // get latest transaction
            $latest_transaction = Transaction::where('user_id', Auth::user()->id)->latest()->first();

            // if transaction exists
            if ($latest_transaction) {
                // get rucurring payment gateways
                $payment_gateway = DB::table('recurring_payment_gateways')->where('payment_gateway_name', $latest_transaction->payment_gateway_name)->first();

                // check latest transaction is subscription and still active
                if ($payment_gateway && $latest_transaction->payment_status == 'SUCCESS') {
                    // retrieve controller namespace
                    $controllerNamespace = "Plugins\\" . $payment_gateway->plugin_id . "\\Controllers\\" . $payment_gateway->plugin_id . "UserController";

                    // check if controller exists
                    if (class_exists($controllerNamespace)) {
                        $controller = app($controllerNamespace);

                        if (method_exists($controller, 'checkPaymentStatus')) {
                            // call checkPaymentStatus method
                            $status = $controller->checkPaymentStatus($latest_transaction->transaction_id);

                            // check if status is not null and authorized
                            if ($status !== null && $status === '1') {
                                return redirect()->route('user.plans')->with('failed', trans('You already have an active subscription. Please cancel it before making a new one.'));
                            }
                        }
                    }
                }
            }
        }

        // Check selected plan
        if ($selected_plan == null) {
            return redirect()->route('user.plans')->with('failed', trans('Your current plan is not available. Please choose a different plan.'));
        } else {
            // Queries
            $config = DB::table('config')->get();

            // Check selected plan
            if ($selected_plan == null) {
                return redirect()->route('user.plans')->with('failed', trans('Plan not found!'));
            } else {
                // Check selected plan is "0"
                if ((int) $selected_plan->plan_price == 0) {

                    // Check billing details is filled
                    if (Auth::user()->billing_name == "") {
                        return redirect()->route('user.billing', $id);
                    } else {

                        // check already subscribed
                        $is_already_subscribed = Transaction::where('user_id', Auth::user()->id)
                            ->where('plan_id', $selected_plan->plan_id)
                            ->exists();

                        if ($is_already_subscribed) {
                            return redirect()->route('user.plans')->with('failed', trans('Sorry, you have already used this free plan.'));
                        }

                        // Generate invoice details
                        $invoice_details = [];

                        $invoice_details['from_billing_name'] = $config[16]->config_value;
                        $invoice_details['from_billing_address'] = $config[19]->config_value;
                        $invoice_details['from_billing_city'] = $config[20]->config_value;
                        $invoice_details['from_billing_state'] = $config[21]->config_value;
                        $invoice_details['from_billing_zipcode'] = $config[22]->config_value;
                        $invoice_details['from_billing_country'] = $config[23]->config_value;
                        $invoice_details['from_vat_number'] = $config[26]->config_value;
                        $invoice_details['from_billing_phone'] = $config[18]->config_value;
                        $invoice_details['from_billing_email'] = $config[17]->config_value;
                        $invoice_details['to_billing_name'] = $request->billing_name;
                        $invoice_details['to_billing_address'] = $request->billing_address;
                        $invoice_details['to_billing_city'] = $request->billing_city;
                        $invoice_details['to_billing_state'] = $request->billing_state;
                        $invoice_details['to_billing_zipcode'] = $request->billing_zipcode;
                        $invoice_details['to_billing_country'] = $request->billing_country;
                        $invoice_details['to_billing_phone'] = $request->billing_phone;
                        $invoice_details['to_billing_email'] = $request->billing_email;
                        $invoice_details['to_vat_number'] = $request->vat_number;
                        $invoice_details['tax_name'] = $config[24]->config_value;
                        $invoice_details['tax_type'] = $config[14]->config_value;
                        $invoice_details['tax_value'] = $config[25]->config_value;
                        $invoice_details['invoice_amount'] = 0;
                        $invoice_details['subtotal'] = 0;
                        $invoice_details['tax_amount'] = 0;

                        // Save new transaction
                        $transaction = new Transaction();
                        $transaction->gobiz_transaction_id = uniqid();
                        $transaction->transaction_date = now();
                        $transaction->transaction_id = uniqid();
                        $transaction->user_id = Auth::user()->id;
                        $transaction->plan_id = $selected_plan->plan_id;
                        $transaction->desciption = $selected_plan->plan_name . " Plan";
                        $transaction->payment_gateway_name = "FREE";
                        $transaction->transaction_amount = $selected_plan->plan_price;
                        $transaction->transaction_currency = $config[1]->config_value;
                        $transaction->invoice_details = json_encode($invoice_details);
                        $transaction->payment_status = "SUCCESS"; 
                        $transaction->save();

                        // Update user plan
                        $selected_plan_validity = (int) $selected_plan->validity;

                        // Add new plan validity
                        $plan_validity = Carbon::now();
                        $plan_validity->addDays($selected_plan_validity);

                        // Update validity in user
                        $user = User::where('user_id', Auth::user()->user_id)->first();
                        $user->plan_id = $id;
                        $user->term = "9999";
                        $user->plan_validity = $plan_validity;
                        $user->plan_activation_date = now();
                        $user->plan_details = $selected_plan;
                        $user->save();

                        // Making all cards inactive, For Plan change
                        BusinessCard::where('user_id', Auth::user()->user_id)->update([
                            'card_status' => 'inactive',
                        ]);

                        return redirect()->route('user.plans')->with('success', trans('Hurray! Your FREE plan has been activated.'));
                    }
                } elseif ($selected_plan->trial != 0 && Auth::user()->trial != 1) {
                    // Generate invoice details
                    $invoice_details = [];

                    $invoice_details['from_billing_name'] = $config[16]->config_value;
                    $invoice_details['from_billing_address'] = $config[19]->config_value;
                    $invoice_details['from_billing_city'] = $config[20]->config_value;
                    $invoice_details['from_billing_state'] = $config[21]->config_value;
                    $invoice_details['from_billing_zipcode'] = $config[22]->config_value;
                    $invoice_details['from_billing_country'] = $config[23]->config_value;
                    $invoice_details['from_vat_number'] = $config[26]->config_value;
                    $invoice_details['from_billing_phone'] = $config[18]->config_value;
                    $invoice_details['from_billing_email'] = $config[17]->config_value;
                    $invoice_details['to_billing_name'] = $request->billing_name;
                    $invoice_details['to_billing_address'] = $request->billing_address;
                    $invoice_details['to_billing_city'] = $request->billing_city;
                    $invoice_details['to_billing_state'] = $request->billing_state;
                    $invoice_details['to_billing_zipcode'] = $request->billing_zipcode;
                    $invoice_details['to_billing_country'] = $request->billing_country;
                    $invoice_details['to_billing_phone'] = $request->billing_phone;
                    $invoice_details['to_billing_email'] = $request->billing_email;
                    $invoice_details['to_vat_number'] = $request->vat_number;
                    $invoice_details['tax_name'] = $config[24]->config_value;
                    $invoice_details['tax_type'] = $config[14]->config_value;
                    $invoice_details['tax_value'] = $config[25]->config_value;
                    $invoice_details['invoice_amount'] = 0;
                    $invoice_details['subtotal'] = 0;
                    $invoice_details['tax_amount'] = 0;

                    // Save new transaction
                    $transaction = new Transaction();
                    $transaction->gobiz_transaction_id = uniqid();
                    $transaction->transaction_date = now();
                    $transaction->transaction_id = uniqid();
                    $transaction->user_id = Auth::user()->id;
                    $transaction->plan_id = $selected_plan->plan_id;
                    $transaction->desciption = $selected_plan->plan_name . " Plan";
                    $transaction->payment_gateway_name = "TRIAL";
                    $transaction->transaction_amount = $selected_plan->plan_price;
                    $transaction->transaction_currency = $config[1]->config_value;
                    $transaction->invoice_details = json_encode($invoice_details);
                    $transaction->payment_status = "SUCCESS";
                    $transaction->save();

                    // Update user plan
                    $selected_plan_validity = (int) $selected_plan->validity;
                    $trialDays = (int) $selected_plan->trial;

                    // Add new plan validity
                    $plan_validity = Carbon::now();
                    $plan_validity->addDays($trialDays);

                    // Update validity in user
                    $user = User::where('user_id', Auth::user()->user_id)->first();
                    $user->plan_id = $id;
                    $user->term = $selected_plan_validity;
                    $user->plan_validity = $plan_validity;
                    $user->plan_activation_date = now();
                    $user->plan_details = $selected_plan;
                    $user->trial = 1;
                    $user->save();

                    // Making all cards inactive, For Plan change
                    BusinessCard::where('user_id', Auth::user()->user_id)->update([
                        'card_status' => 'inactive',
                    ]);

                    return redirect()->route('user.plans')->with('success', trans($selected_plan->plan_name) . ' ' . trans('Trial Plan Activated!'));
                } else {
                    // Queries
                    $settings = Setting::where('status', 1)->first();
                    $config = DB::table('config')->get();
                    $currency = Currency::where('iso_code', $config[1]->config_value)->first();
                    $gateways = Gateway::where('is_status', 'enabled')->where('status', "1")->get();
                    $plan_price = $selected_plan->plan_price;
                    $tax = $config[25]->config_value;
                    $total = ((float) ($plan_price) * (float) ($tax) / 100) + (float) ($plan_price);

                    // recurring payment gateways
                    $recurring_payment_gateways = null;

                    // check plugin file exists
                    if (Schema::hasTable('recurring_payment_gateways') && $selected_plan->validity < 9998) {
                        $recurring_payment_gateways = DB::table('recurring_payment_gateways')->where('status', 'enabled')->get();

                        $recurring_payment_gateways = $recurring_payment_gateways->filter(function ($gateway) {
                            if (empty($gateway->plugin_id)) {
                                return false;
                            }

                            $pluginPath = base_path('plugins/' . $gateway->plugin_id);
                            return File::exists($pluginPath);
                        })->values();
                    }

                    return view('user.pages.checkout.checkout', compact('settings', 'config', 'currency', 'selected_plan', 'gateways', 'total', 'coupon_code', 'applied', 'recurring_payment_gateways'));
                }
            }
        }
    }

    // Checkout coupon
    public function checkoutCoupon(Request $request, $planId)
    {
        // Queries
        $config = DB::table('config')->get();
        $tax = (float) $config[25]->config_value;
        $total = 0;
        $applied = false;

        // Coupon code
        $coupon_code = Str::upper($request->coupon_code);

        // Get plan details
        $selected_plan = Plan::where('plan_id', $planId)->where('status', 1)->first();

        // Check plan exists
        if ($selected_plan == null) {
            return response()->json(['success' => false, 'message' => trans('Plan not found!')]);
        }

        // Get coupon details
        $couponDetails = Coupon::where('used_for', 'plan')->where('coupon_code', $coupon_code)->where('status', 1)->first();

        // Check coupon exists
        if ($couponDetails == null) {
            return response()->json(['success' => false, 'message' => trans('Coupon not vaild!')]);
        }

        // Check coupon validity
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
            $appliedTaxInTotal = ($selected_plan->plan_price * $tax) / 100;
            $discountPrice = $couponDetails->coupon_amount;
            $total = ($selected_plan->plan_price + $appliedTaxInTotal) - $discountPrice;
        } else {
            // Applied tax in total
            $appliedTaxInTotal = ($selected_plan->plan_price * $tax) / 100;
            // Get discount in plan price
            $discountPrice = ($selected_plan->plan_price * $couponDetails->coupon_amount) / 100;

            // Total
            $total = ($selected_plan->plan_price + $appliedTaxInTotal) - $discountPrice;
        }

        // Change applied status
        $applied = true;

        return response()->json(['success' => true, 'applied' => $applied, 'coupon_code' => $coupon_code, 'coupon_id' => $couponDetails->coupon_id, 'discountPrice' => $discountPrice, 'total' => $total]);
    }
}
