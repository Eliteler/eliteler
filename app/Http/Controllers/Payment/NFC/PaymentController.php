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

namespace App\Http\Controllers\Payment\NFC;

use App\AppliedCoupon;
use App\Classes\ZeroOrderNFC;
use App\Coupon;
use App\Gateway;
use App\Http\Controllers\Controller;
use App\NfcCardDesign;
use App\Plan;
use App\Services\PluginManager;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    protected $pluginManager;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(PluginManager $pluginManager)
    {
        $this->pluginManager = $pluginManager;
        $this->middleware('auth');
    }

    // Payment Gateway
    public function placeOrder(Request $request, $nfcId)
    {
        // Queries
        $config = DB::table('config')->get();

        // Check enable nfc card order system
        if ($config[76]->config_value == '0') {
            return abort(404);
        }

        // NFC Card
        $nfcCard = NfcCardDesign::where('nfc_card_id', $nfcId)->first();

        // Check stock
        if ($nfcCard->available_stocks <= 0) {
            return redirect()->route('user.order.nfc.cards')->with('failed', __('This NFC card is out of stock.'));
        }

        /*
        |--------------------------------------------------------------------------
        | Quantity
        |--------------------------------------------------------------------------
        */

        $quantity = (int) $request->quantity;

        if ($quantity <= 0) {
            $quantity = 1;
        }

        // Check stock limit
        if ($quantity > $nfcCard->available_stocks) {
            return redirect()->route('user.order.nfc.card.checkout', $nfcId)->with('failed', __('Requested quantity exceeds available stock.'));
        }

        /*
        |--------------------------------------------------------------------------
        | Plan
        |--------------------------------------------------------------------------
        */

        $active_plan = Plan::where('plan_id', Auth::user()->plan_id)->first();

        $plan = User::where('user_id', Auth::user()->user_id)->first();

        $active_plan = json_decode($plan->plan_details);

        /*
        |--------------------------------------------------------------------------
        | Check NFC Card Access
        |--------------------------------------------------------------------------
        */

        if ($active_plan->nfc_card == 1) {
            if ($request->payment_gateway_id != null) {
                // Payment Gateway
                $payment_mode = Gateway::where('payment_gateway_id', $request->payment_gateway_id)->first();

                // Plugins
                $plugins = $this->pluginManager->active();

                // Plugin
                $plugin = collect($plugins)->firstWhere('plugin_id', $payment_mode->plugin_id);

                // Payment mode check
                if ($payment_mode == null) {
                    return redirect()->route('user.order.nfc.card.checkout')->with('failed', trans('Select a payment method not available. Please choose another payment method.'));
                }

                /*
                |--------------------------------------------------------------------------
                | Validation
                |--------------------------------------------------------------------------
                */

                $validator = Validator::make(
                    $request->all(),
                    [
                        'shipping_name' => 'required',
                        'shipping_email' => 'required',
                        'country_code' => 'required',
                        'mobile_number' => 'required',
                        'whatsapp_country_code' => 'required',
                        'whatsapp_number' => 'required',
                        'shipping_address' => 'required',
                        'shipping_city' => 'required',
                        'billing_country' => 'required',
                        'shipping_state' => 'required',
                        'type' => 'required',
                        'quantity' => 'required|integer|min:1',
                    ]
                );

                if ($validator->fails()) {
                    return redirect()->route('user.order.nfc.card.checkout', $nfcId)->with('failed', $validator->messages()->all()[0])->withInput();
                }

                /*
                |--------------------------------------------------------------------------
                | Save User Billing Data
                |--------------------------------------------------------------------------
                */

                User::where('user_id', Auth::user()->user_id)->update([
                    'billing_name' => $request->shipping_name,
                    'billing_address' => $request->shipping_address,
                    'billing_city' => $request->shipping_city,
                    'billing_state' => $request->shipping_state,
                    'billing_zipcode' => $request->shipping_zipcode ?? '',
                    'billing_country' => $request->billing_country,
                    'country_code' => $request->country_code,
                    'mobile_number' => $request->mobile_number ?? '',
                    'whatsapp_country_code' => $request->whatsapp_country_code,
                    'whatsapp_number' => $request->whatsapp_number ?? '',
                    'billing_country_code' => $request->country_code,
                    'billing_phone' => $request->mobile_number ?? '',
                    'billing_email' => $request->shipping_email,
                    'type' => $request->type ?? '',
                    'vat_number' => $request->vat_number ?? '',
                ]);

                /*
                |--------------------------------------------------------------------------
                | Plugin Provider
                |--------------------------------------------------------------------------
                */

                $configPath = config_path('app.php');
                $configContent = file_get_contents($configPath);

                if (strpos($configContent, 'App\Providers\PluginServiceProvider::class,') === false) {
                    $updatedConfig = preg_replace(
                        "/('providers' => \[)(.*?)(\n\s*\],)/s",
                        "$1$2\n        App\Providers\PluginServiceProvider::class,$3",
                        $configContent
                    );

                    file_put_contents($configPath, $updatedConfig);

                    Artisan::call('config:clear');
                }

                /*
                |--------------------------------------------------------------------------
                | Coupon
                |--------------------------------------------------------------------------
                */

                $couponDetails = Coupon::where('used_for', 'nfc')->where('coupon_id', $request->applied_coupon)->first();

                $couponId = $couponDetails->coupon_code;

                if ($couponId == '') {
                    $couponId = ' ';
                }

                /*
                |--------------------------------------------------------------------------
                | Zero Order
                |--------------------------------------------------------------------------
                */

                if ((float) $request->payment_gateway_amount <= 0) {

                    $zeroOrder = new ZeroOrderNFC();
                    $zeroOrder->zero($couponId, $nfcCard, $nfcId, $plan, $payment_mode, $quantity);

                    return redirect()->route('user.order.nfc.cards')->with('success', trans('Order has been successfully placed!'));
                }

                /*
                |--------------------------------------------------------------------------
                | Plugin Payments
                |--------------------------------------------------------------------------
                */

                if (!empty($plugin) && ($plugin['plugin_id'] == $payment_mode->plugin_id)) {
                    $route = Str::lower('nfc.' . $plugin['plugin_id']) . '.checkout';
                    return redirect()->route($route, compact('nfcId', 'couponId', 'quantity'));
                }

                /*
                |--------------------------------------------------------------------------
                | Payment Gateways
                |--------------------------------------------------------------------------
                */

                return redirect()->route(
                    match ($payment_mode->payment_gateway_id) {
                        '60964401751ab' => 'nfcpaywithpaypal',
                        '60964410731d9' => 'nfcpaywithrazorpay',
                        '60964410732t9' => 'nfcpaywithstripe',
                        '60964410736592' => 'nfcpaywithpaystack',
                        '6096441071589632' => 'nfcpaywithmollie',
                        '659644107y2g5' => 'nfcpaywithoffline',
                        '19065566166715' => 'nfcpaywithphonepe',
                        '776111730465' => 'nfcpaywithmercadopago',
                        '767510608137' => 'nfc.prepare.toyyibpay',
                        '754201940107' => 'nfc.prepare.flutterwave',
                        '5992737427969' => 'nfc.prepare.paddle',
                        '5992737427970' => 'nfc.prepare.paytr',
                        '278523098674' => 'nfc.prepare.xendit',
                        '278523098675' => 'nfc.prepare.cashfree',
                        '278523098676' => 'nfc.prepare.iyzipay',
                        default => 'user.order.nfc.card.checkout',
                    },
                    compact('nfcId', 'couponId', 'quantity')
                );
            }

            return redirect()->route('user.order.nfc.cards')->with('failed', __('Payment gateway not selected.'));
        }

        return redirect()->route('user.order.nfc.cards')->with('failed', __('This feature is not available on your current plan.'));
    }

    // Checkout coupon
    public function coupon(Request $request, $designId)
    {
        // Config
        $config = DB::table('config')->get();

        $taxPercentage = (float) $config[25]->config_value;

        // Coupon code
        $couponCode = Str::upper($request->coupon_code);

        // Quantity
        $quantity = max((int) $request->quantity, 1);

        // Get NFC Card
        $nfcCardDetails = NfcCardDesign::where('nfc_card_id', $designId)->first();

        // Check NFC Card exists
        if (!$nfcCardDetails) {
            return response()->json([
                'success' => false,
                'message' => trans('NFC Card not found!'),
            ]);
        }

        // Get Coupon
        $couponDetails = Coupon::where('used_for', 'nfc')
            ->where('coupon_code', $couponCode)
            ->where('status', 1)
            ->first();

        // Check coupon exists
        if (!$couponDetails) {
            return response()->json([
                'success' => false,
                'message' => trans('Coupon not valid!'),
            ]);
        }

        // Check coupon expiry
        if ($couponDetails->coupon_expired_on < Carbon::now()) {
            return response()->json([
                'success' => false,
                'message' => trans('Coupon expired!'),
            ]);
        }

        // Check if coupon is applicable for this specific NFC Card or all NFC Cards
        $allowedCards = json_decode($couponDetails->plan_id, true);
        if (!is_array($allowedCards)) {
            $allowedCards = $couponDetails->plan_id ? [$couponDetails->plan_id] : ['all'];
        }

        if (!in_array('all', $allowedCards) && !in_array($nfcCardDetails->nfc_card_id, $allowedCards)) {
            return response()->json([
                'success' => false,
                'message' => trans('This coupon is not applicable for the selected NFC Card!'),
            ]);
        }

        // User usage limit
        $userCouponCount = AppliedCoupon::where('user_id', Auth::user()->id)
            ->where('coupon_id', $couponDetails->coupon_id)
            ->where('status', 1)
            ->count();

        if ($userCouponCount >= $couponDetails->coupon_user_usage_limit) {
            return response()->json([
                'success' => false,
                'message' => trans('Coupon already used.'),
            ]);
        }

        // Total usage limit
        $totalCouponCount = AppliedCoupon::where('coupon_id', $couponDetails->coupon_id)
            ->where('status', 1)
            ->count();

        if ($totalCouponCount >= $couponDetails->coupon_total_usage_limit) {
            return response()->json([
                'success' => false,
                'message' => trans('Total number of users already reached.'),
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Price Calculation
        |--------------------------------------------------------------------------
        */

        // Unit price
        $unitPrice = (float) $nfcCardDetails->nfc_card_price;

        // Subtotal
        $subtotal = $unitPrice * $quantity;

        // Tax
        $taxAmount = ($subtotal * $taxPercentage) / 100;

        // Subtotal including tax
        $subtotalWithTax = $subtotal + $taxAmount;

        // Discount
        $discountPrice = 0;

        if ($couponDetails->coupon_type == 'fixed') {
            $discountPrice = (float) $couponDetails->coupon_amount;
        } else {
            // Percentage coupon is applied to subtotal + GST
            $discountPrice = ($subtotalWithTax * (float) $couponDetails->coupon_amount) / 100;
        }

        // Prevent discount from exceeding subtotal + tax
        $discountPrice = min($discountPrice, $subtotalWithTax);

        // Grand Total
        $total = $subtotalWithTax - $discountPrice;

        // Prevent negative total
        $total = max($total, 0);

        return response()->json([
            'success' => true,
            'applied' => true,
            'coupon_code' => $couponCode,
            'coupon_id' => $couponDetails->coupon_id,
            'quantity' => $quantity,
            'subtotal' => round($subtotal, 2),
            'taxAmount' => round($taxAmount, 2),
            'discountPrice' => round($discountPrice, 2),
            'total' => round($total, 2),
        ]);
    }
}
