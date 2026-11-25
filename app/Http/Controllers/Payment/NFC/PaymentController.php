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
use App\Services\SmartProducts\OrderQuote;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    /**
     * First version of each payment plugin whose NFC checkout charges the
     * shipping fee (uses OrderQuote). Older versions are refused while a
     * shipping fee applies, so they can't undercharge.
     */
    private const SHIPPING_READY_PLUGINS = [
        'eSewa'      => '1.5.0',
        'Moneroo'    => '1.5.0',
        'Coinbase'   => '1.7.0',
        'SSLCommerz' => '1.3.0',
        'AmwalPay'   => '1.4.0',
    ];

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
        $nfcCard = NfcCardDesign::where('nfc_card_id', $nfcId)->where('status', 1)->first();

        if (! $nfcCard) {
            return redirect()->route('user.order.nfc.cards')->with('failed', __('NFC Card not found.'));
        }

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
                | Coupon
                |--------------------------------------------------------------------------
                */

                $couponDetails = !empty(trim($request->applied_coupon ?? ''))
                    ? Coupon::where('used_for', 'nfc')
                        ->where(function ($query) use ($request) {
                            $code = trim($request->applied_coupon);
                            $query->where('coupon_id', $code)
                                ->orWhere('coupon_code', $code);
                        })
                        ->first()
                    : null;

                // A coupon that is no longer valid (expired, used up, other product) is dropped
                if ($couponDetails && OrderQuote::couponError($couponDetails, $nfcCard, Auth::user())) {
                    $couponDetails = null;
                }

                $couponId = $couponDetails?->coupon_code ?: ' ';

                // The price is always worked out here, never taken from the form
                $quote = OrderQuote::make($nfcCard, $quantity, $couponDetails, Auth::user());

                /*
                |--------------------------------------------------------------------------
                | Zero Order
                |--------------------------------------------------------------------------
                */

                if ($quote->total <= 0) {

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
                    $minimum = self::SHIPPING_READY_PLUGINS[$plugin['plugin_id']] ?? null;

                    if ($quote->shipping > 0 && $minimum && version_compare($plugin['version'] ?? '0', $minimum, '<')) {
                        return redirect()->route('user.order.nfc.card.checkout', $nfcId)->with('failed', __('This payment method needs an update before it can be used. Please choose another payment method.'));
                    }

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
        // Coupon code
        $couponCode = Str::upper($request->coupon_code);

        // Quantity
        $quantity = max((int) $request->quantity, 1);

        // Get NFC Card
        $nfcCardDetails = NfcCardDesign::where('nfc_card_id', $designId)->where('status', 1)->first();

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

        // Expiry, product, usage limits
        if ($error = OrderQuote::couponError($couponDetails, $nfcCardDetails, Auth::user())) {
            return response()->json([
                'success' => false,
                'message' => $error,
            ]);
        }

        // Same calculation as the payment gateways
        $quote = OrderQuote::make($nfcCardDetails, $quantity, $couponDetails, Auth::user());

        return response()->json([
            'success' => true,
            'applied' => true,
            'coupon_code' => $couponCode,
            'coupon_id' => $couponDetails->coupon_id,
            'quantity' => $quantity,
            'subtotal' => $quote->subtotal,
            'taxAmount' => $quote->tax,
            'discountPrice' => $quote->discount,
            'shipping' => $quote->shipping,
            'total' => $quote->total,
        ]);
    }
}
