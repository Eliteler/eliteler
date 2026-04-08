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

use App\User;
use App\Setting;
use App\Currency;
use Carbon\Carbon;
use App\Transaction;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class PlanController extends Controller
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

    // Plans
    public function index()
    {
        // check modern_dashboard_enabled
        if (Schema::hasTable('modern_dashboard_settings')) {
            $is_modern_dashboard_enabled = DB::table('modern_dashboard_settings')->first()->modern_dashboard_enabled;
            if (is_dir(base_path('plugins/ModernDashboard')) && $is_modern_dashboard_enabled == 1) {
                if (session()->has('success') || session()->has('failed')) {
                    session()->reflash();
                }
                return redirect()->route('user.dashboard.subscriptions');
            }
        }

        // Queries
        $plans = DB::table('plans')->where('is_private', 0)->where('status', 1)->get();
        $config = DB::table('config')->get();
        $free_plan = Transaction::where('user_id', Auth::user()->id)->where('transaction_amount', '0.00')->orWhere('transaction_amount', '0')->count();
        $plan = User::where('user_id', Auth::user()->user_id)->first();
        $active_plan = json_decode($plan->plan_details);
        $settings = Setting::where('status', 1)->first();
        $currency = Currency::where('iso_code', $config[1]->config_value)->first();
        $remaining_days = 0;

        $cancelSubscription = 0;
        $subscriptionId = null;

        // Check active plan in user
        if (isset($active_plan)) {
            // Customer plan validity
            $plan_validity = Carbon::createFromFormat('Y-m-d H:i:s', Auth::user()->plan_validity);

            $current_date = Carbon::now();

            // Calculate remaining days including time
            $remaining_days = $current_date->diffInDays($plan_validity, false);

            // Always round up for future dates
            $remaining_days = ceil($remaining_days);

            // Convert to integer
            $remaining_days = (int) $remaining_days;

            // check subscription is active
            $subscription = Transaction::where('user_id', Auth::user()->id)->where('payment_status', 'SUCCESS')->latest()->first();
            if ($subscription) {
                $subscriptionId = $subscription->transaction_id;
            }

            // check subscription is available
            if (Schema::hasTable('recurring_payment_gateways') && $subscription) {
                // recurring payment gateways
                $payment_gateway = DB::table('recurring_payment_gateways')->where('payment_gateway_name', $subscription->payment_gateway_name)->first();

                // chack payment gateway is recurring
                if (isset($payment_gateway) && Str::startsWith($payment_gateway->payment_gateway_id, 'rec')) {

                    // retrieve controller namespace
                    $controllerNamespace = "Plugins\\" . $payment_gateway->plugin_id . "\\Controllers\\" . $payment_gateway->plugin_id . "UserController";

                    // check if controller exists
                    if (class_exists($controllerNamespace)) {
                        $controller = app($controllerNamespace);

                        if (method_exists($controller, 'checkPaymentStatus')) {

                            // call checkPaymentStatus method
                            $status = $controller->checkPaymentStatus($subscription->transaction_id);

                            // check if status is not null and authorized
                            if ($status !== null && $status === '1') {
                                $cancelSubscription = 1;
                            }
                        }
                    } else {
                        $cancelSubscription = 0;
                    }
                }
            }
        }

        // return view
        return view('user.pages.plans.plans', compact('plans', 'settings', 'currency', 'active_plan', 'remaining_days', 'config', 'free_plan', 'cancelSubscription', 'subscriptionId'));
    }

    // cancel subscription
    public function subscriptionCancel($subscriptionId)
    {
        // check subscription id
        if (empty($subscriptionId)) {
            return back()->with('failed', 'Invalid subscription id.');
        }

        // cancel subscription
        $subscription = Transaction::where('transaction_id', $subscriptionId)->first();

        if ($subscription) {
            // recurring payment gateways
            $payment_gateway = DB::table('recurring_payment_gateways')->where('payment_gateway_name', $subscription->payment_gateway_name)->first();

            // chack payment gateway is recurring
            if ($payment_gateway) {
                // retrieve controller namespace
                $controllerNamespace = "Plugins\\" . $payment_gateway->plugin_id . "\\Controllers\\" . $payment_gateway->plugin_id . "UserController";

                // check if controller exists
                if (class_exists($controllerNamespace)) {
                    $controller = app($controllerNamespace);

                    if (method_exists($controller, 'cancelSubscription')) {
                        // call cancelSubscription method
                        $status = $controller->cancelSubscription($subscriptionId);

                        if ($status) {
                            return back()->with('success', 'Subscription cancelled successfully.');
                        } else {
                            return back()->with('failed', 'Something went wrong while cancelling.');
                        }
                    }
                } else {
                    return back()->with('failed', 'Something went wrong.');
                }
            }
        }

        // return error
        return back()->with('failed', 'Invalid subscription id.');
    }
}
