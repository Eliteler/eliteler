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

namespace App\Http\Controllers\Payment;

use App\Plan;
use App\User;
use App\Coupon;
use App\Gateway;
use Carbon\Carbon;
use App\Transaction;
use App\BusinessCard;
use App\AppliedCoupon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Services\PluginManager;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
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

    public function preparePaymentGateway(Request $request, $planId)
    {
        $config = DB::table('config')->get();
        if ($request->payment_gateway_id != null) {
            $payment_mode = Gateway::where('payment_gateway_id', $request->payment_gateway_id)->first() ?? DB::table('recurring_payment_gateways')->where('payment_gateway_id', $request->payment_gateway_id)->first();

            // Get all plugins
            $plugins = $this->pluginManager->getPlugins();

            // get plugin
            $plugin = collect($plugins)->firstWhere('plugin_id', $payment_mode->plugin_id);

            // plugin path
            $pluginFolder = base_path('plugins/' . $payment_mode->plugin_id);

            if ($payment_mode == null) {
                return redirect()->route('user.plans')->with('failed', trans('Please choose valid payment method!'));
            } else {
                $validator = Validator::make($request->all(), [
                    'billing_name' => 'required',
                    'billing_email' => 'required',
                    'billing_address' => 'required',
                    'billing_city' => 'required',
                    'billing_state' => 'required',
                    'billing_country' => 'required',
                    'type' => 'required',
                ]);

                if ($validator->fails()) {
                    return back()->with('failed', $validator->messages()->all()[0])->withInput();
                }

                User::where('user_id', Auth::user()->user_id)->update([
                    'mobile_number' => $request->billing_phone ?? "",
                    'billing_name' => $request->billing_name,
                    'billing_email' => $request->billing_email,
                    'billing_phone' => $request->billing_phone ?? "",
                    'whatsapp_number' => $request->billing_whatsapp ?? "",
                    'billing_address' => $request->billing_address,
                    'billing_city' => $request->billing_city,
                    'billing_state' => $request->billing_state,
                    'billing_zipcode' => $request->billing_zipcode ?? "",
                    'billing_country' => $request->billing_country,
                    'type' => $request->type ?? "",
                    'vat_number' => $request->vat_number ?? ""
                ]);

                // Coupon ID
                $couponId = $request->applied_coupon;

                if ($couponId == "") {
                    $couponId = " ";
                }

                // Check if payment_gateway_amount is 0
                if (floatval($request->payment_gateway_amount ?? 0) <= 0) {

                    $result = $this->zeroPricePlan(
                        $request,
                        $planId,
                        $payment_mode->payment_gateway_name,
                        floatval($request->payment_gateway_amount ?? 0),
                        $request->applied_coupon
                    );

                    // Otherwise, redirect to plans with success
                    return redirect()->route('user.plans')->with('success', trans('Your plan has been activated.'));
                } else {
                    // check payment gateways
                    if (Str::startsWith($payment_mode->payment_gateway_id, 'rec')) {

                        // if plugin not found return redirect
                        if (!$plugin || !File::exists($pluginFolder)) {
                            return redirect()->route('user.plans')->with('failed', trans('Invalid payment gateway.'));
                        }

                        // get main route
                        if ($plugin && isset($plugin['main_route'])) {
                            // get main route
                            $mainRoute = $plugin['main_route'];

                            // check if main route is valid
                            if (!$mainRoute) {
                                return redirect()->route('user.plans')->with('failed', trans('Invalid payment gateway.'));
                            }

                            // reassign main route
                            $paymentRoute = Str::replaceFirst('admin.', '', $mainRoute) . '.payment';

                            // redirect to payment gateway
                            return redirect()->route($paymentRoute, compact('planId'));
                        } else {
                            // redirect to plans with failed
                            return redirect()->route('user.plans')->with('failed', trans('Invalid payment gateway.'));
                        }
                    } else if (!empty($plugin) && ($plugin['plugin_id'] == $payment_mode->plugin_id)) {
                        // Convert to small letters
                        $route = Str::lower($plugin['plugin_id']) . '.checkout';

                        // Redirect to payment gateway
                        return redirect()->route($route, compact('planId', 'couponId'));
                    } else if ($payment_mode->payment_gateway_id == "60964401751ab") {
                        // Check key and secret
                        if ($config[4]->config_value != "YOUR_PAYPAL_CLIENT_ID" || $config[5]->config_value != "YOUR_PAYPAL_SECRET") {
                            return redirect()->route('paywithpaypal', compact('planId', 'couponId'));
                        } else {
                            return redirect()->route('user.plans')->with('failed', trans('You can not use PayPal payment gateway!. For more information, please contact us.'));
                        }
                    } else if ($payment_mode->payment_gateway_id == "60964410731d9") {
                        // Check key and secret
                        if ($config[6]->config_value != "YOUR_RAZORPAY_KEY" || $config[7]->config_value != "YOUR_RAZORPAY_SECRET") {
                            return redirect()->route('paywithrazorpay', compact('planId', 'couponId'));
                        } else {
                            return redirect()->route('user.plans')->with('failed', trans('You can not use Razorpay payment gateway!. For more information, please contact us.'));
                        }
                    } else if ($payment_mode->payment_gateway_id == "60964410732t9") {
                        // Check key and secret
                        if ($config[9]->config_value != "YOUR_STRIPE_PUB_KEY" || $config[10]->config_value != "YOUR_STRIPE_SECRET") {
                            return redirect()->route('paywithstripe', compact('planId', 'couponId'));
                        } else {
                            return redirect()->route('user.plans')->with('failed', trans('You can not use Stripe payment gateway!. For more information, please contact us.'));
                        }
                    } else if ($payment_mode->payment_gateway_id == "60964410736592") {
                        // Check key and secret
                        if ($config[33]->config_value != "PAYSTACK_PUBLIC_KEY" || $config[34]->config_value != "PAYSTACK_SECRET_KEY") {
                            return redirect()->route('paywithpaystack', compact('planId', 'couponId'));
                        } else {
                            return redirect()->route('user.plans')->with('failed', trans('You can not use Paystack payment gateway!. For more information, please contact us.'));
                        }
                    } else if ($payment_mode->payment_gateway_id == "6096441071589632") {
                        // Check key and secret
                        if ($config[37]->config_value != "YOUR_MOLLIE_KEY") {
                            return redirect()->route('paywithmollie', compact('planId', 'couponId'));
                        } else {
                            return redirect()->route('user.plans')->with('failed', trans('You can not use Mollie payment gateway!. For more information, please contact us.'));
                        }
                    } else if ($payment_mode->payment_gateway_id == "659644107y2g5") {
                        // Check key and secret
                        if ($config[31]->config_value != "") {
                            return redirect()->route('paywithoffline', compact('planId', 'couponId'));
                        } else {
                            return redirect()->route('user.plans')->with('failed', trans('You can not use Offline payment gateway!. For more information, please contact us.'));
                        }
                    } else if ($payment_mode->payment_gateway_id == "19065566166715") {
                        // Check key and secret
                        if ($config[77]->config_value != "YOUR_PHONEPE_CLIENT_ID" || $config[78]->config_value != "YOUR_PHONEPE_CLIENT_VERSION" || $config[79]->config_value != "YOUR_PHONEPE_CLIENT_SECRET") {
                            return redirect()->route('paywithphonepe', compact('planId', 'couponId'));
                        } else {
                            return redirect()->route('user.plans')->with('failed', trans('You can not use PhonePe payment gateway!. For more information, please contact us.'));
                        }
                    } else if ($payment_mode->payment_gateway_id == "776111730465") {
                        // Check key and secret
                        if ($config[47]->config_value != "YOUR_MERCADO_PAGO_PUBLIC_KEY" || $config[48]->config_value != "YOUR_MERCADO_PAGO_ACCESS_TOKEN") {
                            return redirect()->route('paywithmercadopago', compact('planId', 'couponId'));
                        } else {
                            return redirect()->route('user.plans')->with('failed', trans('You can not use Mercado Pago payment gateway!. For more information, please contact us.'));
                        }
                    } else if ($payment_mode->payment_gateway_id == "767510608137") {
                        // Check key and secret
                        if ($config[49]->config_value != "YOUR_TOYYIBPAY_API_KEY" || $config[50]->config_value != "YOUR_TOYYIBPAY_CATEGORY_CODE") {
                            return redirect()->route('prepare.toyyibpay', compact('planId', 'couponId'));
                        } else {
                            return redirect()->route('user.plans')->with('failed', trans('You can not use Toyyibpay payment gateway!. For more information, please contact us.'));
                        }
                    } else if ($payment_mode->payment_gateway_id == "754201940107") {
                        // Check key, secret and encryption key
                        if ($config[51]->config_value != "YOUR_FLW_PUBLIC_KEY" || $config[52]->config_value != "YOUR_FLW_SECRET_KEY" || $config[53]->config_value != "YOUR_FLW_ENCRYPTION_KEY") {
                            return redirect()->route('prepare.flutterwave', compact('planId', 'couponId'));
                        } else {
                            return redirect()->route('user.plans')->with('failed', trans('You can not use Flutterwave payment gateway!. For more information, please contact us.'));
                        }
                    } else if ($payment_mode->payment_gateway_id == "5992737427969") {
                        // Check key, secret and encryption key
                        if ($config[65]->config_value != "YOUR_PADDLE_SELLER_ID" || $config[66]->config_value != "YOUR_PADDLE_API_KEY" || $config[67]->config_value != "YOUR_PADDLE_CLIENT_SIDE_TOKEN") {
                            return redirect()->route('prepare.paddle', compact('planId', 'couponId'));
                        } else {
                            return redirect()->route('user.plans')->with('failed', trans('You can not use Paddle payment gateway!. For more information, please contact us.'));
                        }
                    } else if ($payment_mode->payment_gateway_id == "5992737427970") {
                        // Check key, secret and encryption key
                        if ($config[68]->config_value != "YOUR_PAYTR_MERCHANT_ID" || $config[69]->config_value != "YOUR_PAYTR_MERCHANT_KEY" || $config[70]->config_value != "YOUR_PAYTR_MERCHANT_SALT") {
                            return redirect()->route('prepare.paytr', compact('planId', 'couponId'));
                        } else {
                            return redirect()->route('user.plans')->with('failed', trans('You can not use PayTR payment gateway!. For more information, please contact us.'));
                        }
                    } else if ($payment_mode->payment_gateway_id == "278523098674") {
                        // Check key, secret and encryption key
                        if ($config[72]->config_value != "YOUR_XENDIT_SECRET_KEY") {
                            return redirect()->route('prepare.xendit', compact('planId', 'couponId'));
                        } else {
                            return redirect()->route('user.plans')->with('failed', trans('You can not use Xendit payment gateway!. For more information, please contact us.'));
                        }
                    } else if ($payment_mode->payment_gateway_id == "278523098675") {
                        // Check key, secret and encryption key
                        if ($config[85]->config_value != "YOUR_CASHFREE_APP_ID" || $config[86]->config_value != "YOUR_CASHFREE_SECRET_KEY") {
                            return redirect()->route('prepare.cashfree', compact('planId', 'couponId'));
                        } else {
                            return redirect()->route('user.plans')->with('failed', trans('You can not use Cashfree payment gateway!. For more information, please contact us.'));
                        }
                    } else if ($payment_mode->payment_gateway_id == "278523098676") {
                        // Check key, secret and encryption key
                        if ($config[90]->config_value != "YOUR_IYZICO_API_KEY" || $config[91]->config_value != "YOUR_IYZICO_SECRET_KEY") {
                            return redirect()->route('prepare.iyzipay', compact('planId', 'couponId'));
                        } else {
                            return redirect()->route('user.plans')->with('failed', trans('You can not use Cashfree payment gateway!. For more information, please contact us.'));
                        }
                    } else {
                        return redirect()->route('user.plans')->with('failed', trans('You can not use this payment gateway!. For more information, please contact us.'));
                    }
                }
            }
        } else {
            return redirect()->route('user.plans')->with('failed', __('Payment gateway not selected.'));
        }
    }

    // Set zero price plan
    public function zeroPricePlan($request, $planId, $gatewayName, $amount, $couponId)
    {
        // Get configuration (keyed by config_key for safety)
        $config = DB::table('config')->get();

        $user = Auth::user();

        // Get plan
        $plan = Plan::where('plan_id', $planId)->where('status', 1)->firstOrFail();
        $termDays = $plan->validity;

        // Get coupon if applied
        $coupon = $couponId ? Coupon::where('used_for', 'plan')->where('coupon_id', $couponId)->first() : null;

        // Transaction ID
        $transactionId = uniqid();

        // Calculate discount
        $discountPrice = 0;
        $appliedCouponCode = null;
        if ($coupon) {
            $discountPrice = $coupon->coupon_type == 'fixed'
                ? $coupon->coupon_amount
                : ($plan->plan_price * $coupon->coupon_amount / 100);
            $appliedCouponCode = $coupon->coupon_code;
        }

        // Total amount for zero-price plan
        $amountToBePaid = 0;

        // Tax rate from config
        $taxRate = isset($config[25]->config_value) ? (float)$config[25]->config_value : 0;
        $appliedTax = ($amountToBePaid * $taxRate) / 100; // Tax on final amount

        // Invoice details
        $invoiceDetails = [
            'from_billing_name' => $config[16]->config_value ?? '',
            'from_billing_address' => $config[19]->config_value ?? '',
            'from_billing_city' => $config[20]->config_value ?? '',
            'from_billing_state' => $config[21]->config_value ?? '',
            'from_billing_zipcode' => $config[22]->config_value ?? '',
            'from_billing_country' => $config[23]->config_value ?? '',
            'from_vat_number' => $config[26]->config_value ?? '',
            'from_billing_phone' => $config[18]->config_value ?? '',
            'from_billing_email' => $config[17]->config_value ?? '',
            'to_billing_name' => $request->billing_name,
            'to_billing_address' => $request->billing_address,
            'to_billing_city' => $request->billing_city,
            'to_billing_state' => $request->billing_state,
            'to_billing_zipcode' => $request->billing_zipcode,
            'to_billing_country' => $request->billing_country,
            'to_billing_phone' => $request->billing_phone,
            'to_billing_email' => $request->billing_email,
            'to_vat_number' => $request->vat_number,
            'subtotal' => $plan->plan_price,
            'tax_name' => $config[24]->config_value ?? '',
            'tax_type' => $config[14]->config_value ?? '',
            'tax_value' => $taxRate,
            'tax_amount' => $appliedTax,
            'applied_coupon' => $appliedCouponCode,
            'discounted_price' => $discountPrice,
            'invoice_amount' => $amountToBePaid,
        ];

        // Save applied coupon
        if ($coupon) {
            // Save applied coupon
            $appliedCoupon = new AppliedCoupon;
            $appliedCoupon->applied_coupon_id = uniqid();
            $appliedCoupon->transaction_id = $transactionId;
            $appliedCoupon->user_id = Auth::user()->id;
            $appliedCoupon->coupon_id = $couponId;
            $appliedCoupon->status = 1;
            $appliedCoupon->save();
        }

        // Save transaction
        $transaction = new Transaction();
        $transaction->gobiz_transaction_id = uniqid();
        $transaction->transaction_date = now();
        $transaction->transaction_id = $transactionId;
        $transaction->user_id = Auth::user()->id;
        $transaction->plan_id = $plan->plan_id;
        $transaction->desciption = $plan->plan_name . " Plan"; // fixed typo
        $transaction->payment_gateway_name = $gatewayName;
        $transaction->transaction_amount = $amountToBePaid;
        $transaction->transaction_currency = $config[1]->config_value ?? 'USD';
        $transaction->invoice_details = json_encode($invoiceDetails);
        $transaction->payment_status = "SUCCESS";
        $transaction->save();

        // Update plan validity
        $currentValidity = $user->plan_id == $plan->plan_id && $user->plan_validity
            ? Carbon::parse($user->plan_validity)
            : Carbon::now();

        if ($termDays != "9999") {
            $currentValidity->addDays($termDays);
        } else {
            $currentValidity = Carbon::create(2050, 12, 30, 23, 23, 59);
        }

        // Make all cards inactive
        BusinessCard::where('user_id', $user->user_id)->update(['card_status' => 'inactive']);

        // Update validity in user
        $user = User::where('user_id', $user->user_id)->first();
        $user->plan_id = $planId;
        $user->term = $plan->validity;
        $user->plan_validity = $currentValidity;
        $user->plan_activation_date = now();
        $user->plan_details = $plan;
        $user->save();

        // Invoice email
        $invoiceCount = Transaction::where("invoice_prefix", $config[15]->config_value ?? '')->count();
        $invoiceNumber = ($config[15]->config_value ?? '') . ($invoiceCount + 1);

        $details = [
            'from_billing_name' => $invoiceDetails['from_billing_name'],
            'from_billing_email' => $invoiceDetails['from_billing_email'],
            'from_billing_address' => $invoiceDetails['from_billing_address'],
            'from_billing_city' => $invoiceDetails['from_billing_city'],
            'from_billing_state' => $invoiceDetails['from_billing_state'],
            'from_billing_country' => $invoiceDetails['from_billing_country'],
            'from_billing_zipcode' => $invoiceDetails['from_billing_zipcode'],
            'gobiz_transaction_id' => $transaction->gobiz_transaction_id,
            'to_billing_name' => $invoiceDetails['to_billing_name'],
            'invoice_currency' => $transaction->transaction_currency,
            'subtotal' => $invoiceDetails['subtotal'],
            'tax_amount' => $appliedTax,
            'applied_coupon' => $invoiceDetails['applied_coupon'],
            'discounted_price' => $invoiceDetails['discounted_price'],
            'invoice_amount' => $invoiceDetails['invoice_amount'],
            'invoice_id' => $invoiceNumber,
            'invoice_date' => $transaction->created_at,
            'description' => $transaction->description,
            'email_heading' => $config[27]->config_value ?? '',
            'email_footer' => $config[28]->config_value ?? '',
        ];

        try {
            Mail::to($invoiceDetails['to_billing_email'])->send(new \App\Mail\SendEmailInvoice($details));
        } catch (\Exception $e) {
        }

        // Redirect to plans with success
        return redirect()->route('user.plans')->with('success', __('Your plan has been activated.'));
    }
}
