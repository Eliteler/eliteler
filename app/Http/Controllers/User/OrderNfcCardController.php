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

use App\Services\SmartProducts\OrderQuote;
use App\Support\SmartProducts\SmartProductCatalog;
use App\Plan;
use App\User;
use App\Gateway;
use App\Setting;
use App\Currency;
use App\NfcCardDesign;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class OrderNfcCardController extends Controller
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

    // Show all nfc cards
    public function index()
    {
        // check modern_dashboard_enabled
        if (Schema::hasTable('modern_dashboard_settings')) {
            $is_modern_dashboard_enabled = DB::table('modern_dashboard_settings')->first()->modern_dashboard_enabled;
            if (plugin_active('ModernDashboard') && $is_modern_dashboard_enabled == 1) {
                if (session()->has('success') || session()->has('failed')) {
                    session()->reflash();
                }
                return redirect()->route('user.dashboard.nfc-orders');
            }
        }

        // Queries
        $settings = Setting::where('status', 1)->first();
        $config = DB::table('config')->get();
        $currency = Currency::where('iso_code', $config[1]->config_value)->first();

        // Check enable nfc card order system
        if ($config[76]->config_value == '0') {
            return abort(404);
        }

        // Check if the user has a plan
        $active_plan = Plan::where('plan_id', Auth::user()->plan_id)->first();
        $plan = User::where('user_id', Auth::user()->user_id)->first();
        $active_plan = json_decode($plan->plan_details);

        if ($active_plan != null) {
            // Available NFC Cards
            $availableNfcCards = NfcCardDesign::where('status', 1)->orderBy('sort_order')->orderBy('id')->get();

            // Types that have products, for the filter
            $productTypes = SmartProductCatalog::typeLabels()
                ->only($availableNfcCards->pluck('product_type')->map(SmartProductCatalog::typeCode(...))->unique()->all())
                ->all();

            return view('user.pages.order.nfc-card.index', compact('availableNfcCards', 'settings', 'config', 'currency', 'productTypes'));
        } else {
            return redirect()->route('user.plans');
        }
    }

    // Select a nfc card
    public function nfcCardCheckout(Request $request, $designId)
    {
        // React panel has its own checkout (place-order errors come back here too)
        if (\App\Support\Plugins\UserPluginPage::reactPanel()) {
            session()->reflash();

            return redirect()->route('user.dashboard.smart-products.checkout', $designId);
        }

        // Default values
        $coupon_code = '';

        // Queries
        $settings = Setting::where('status', 1)->first();
        $config = DB::table('config')->get();
        $currency = Currency::where('iso_code', $config[1]->config_value)->first();
        $gateways = Gateway::where('is_status', 'enabled')->where('status', "1")->get();

        // Check enable nfc card order system
        if ($config[76]->config_value == '0') {
            return abort(404);
        }

        // Get NFC Card details
        $nfcCardDetails = NfcCardDesign::where('nfc_card_id', $designId)->where('status', 1)->first();

        // NFC Card details existing in the database
        if (!$nfcCardDetails) {
            return redirect()->route('user.order.nfc.cards')->with('failed', __('NFC Card not found.'));
        }

        // Check available stock
        if ($nfcCardDetails->available_stocks <= 0) {
            return redirect()->route('user.order.nfc.cards')->with('failed', __('This NFC card is out of stock.'));
        }

        // Price for one item (the page recalculates when the quantity changes)
        $quote = OrderQuote::make($nfcCardDetails, 1);
        $total = number_format($quote->total, 2, '.', '');
        $shippingFee       = (float) DB::table('config')->where('config_key', 'smart_product_shipping_fee')->value('config_value');
        $freeShippingAbove = (float) DB::table('config')->where('config_key', 'smart_product_free_shipping_above')->value('config_value');

        return view('user.pages.order.nfc-card.checkout', compact('nfcCardDetails', 'settings', 'config', 'currency', 'total', 'coupon_code', 'gateways', 'quote', 'shippingFee', 'freeShippingAbove'));
    }
}
