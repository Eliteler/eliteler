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

namespace App\Http\Controllers\Payment\AICredits;

use App\AiCreditsPlan;
use App\Classes\ZeroAICreditsOrder;
use App\Gateway;
use App\Http\Controllers\Controller;
use App\Services\PluginManager;
use App\User;
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

    // Prepare AI Credits payment
    public function preparePayment(Request $request, $planId)
    {
        // Queries
        $config = DB::table('config')->get();

        if ($request->payment_gateway_id != null) {

            // Get ai credits plan details
            $planDetails = AiCreditsPlan::where('ai_credits_plan_id', $planId)->first();

            // Get payment gateway details
            $payment_mode = Gateway::where('payment_gateway_id', $request->payment_gateway_id)->first();

            // Get all plugins
            $plugins = $this->pluginManager->getPlugins();

            // get plugin
            $plugin = collect($plugins)->firstWhere('plugin_id', $payment_mode->plugin_id);

            // plugin path
            $pluginFolder = base_path('plugins/' . $payment_mode->plugin_id);

            // Check payment mode
            if ($payment_mode == null) {
                return redirect()->route('user.ai.credits.checkout')->with('failed', trans('Select a payment method not available. Please choose another payment method.'));
            } else {
                // Validate request
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
                    return redirect()->route('user.ai.credits.checkout', $planId)->with('failed', $validator->messages()->all()[0])->withInput();
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

                // Insert plugins provider in config file
                $configPath = config_path('app.php');

                // Read the current config file
                $configContent = file_get_contents($configPath);

                // Check if the provider already exists to avoid duplication
                if (strpos($configContent, 'App\Providers\PluginServiceProvider::class,') === false) {
                    // Find the last occurrence of the providers array and insert before the closing bracket ]
                    $updatedConfig = preg_replace(
                        "/('providers' => \[)(.*?)(\n\s*\],)/s",
                        "$1$2\n        App\Providers\PluginServiceProvider::class,$3",
                        $configContent
                    );

                    // Write back the updated config file
                    file_put_contents($configPath, $updatedConfig);

                    // Clear config cache to apply changes
                    Artisan::call('config:clear');
                }

                // Coupon ID
                $couponId = $request->applied_coupon;

                if ($couponId == "") {
                    $couponId = " ";
                }

                // Check NFC card price is 0 or not
                if ((float)$request->payment_gateway_amount <= 0) {

                    // Zero Place order
                    $zeroOrder = new ZeroAICreditsOrder();
                    $zeroOrder->zero($couponId, $planDetails, $planId, $payment_mode);

                    return redirect()->route('user.ai.credits.plans')->with('success', trans('Your ai credits order has been placed successfully!'));
                } else if (!empty($plugin) && ($plugin['plugin_id'] == $payment_mode->plugin_id)) {
                    // Convert to small letters
                    $route = Str::lower('ai.credits.' . $plugin['plugin_id']) . '.checkout';

                    // Redirect to payment gateway
                    return redirect()->route($route, compact('planId', 'couponId'));
                } else {

                    if ($payment_mode->payment_gateway_id == "60964401751ab") {
                        // Check key and secret
                        if ($config[4]->config_value != "YOUR_PAYPAL_CLIENT_ID" || $config[5]->config_value != "YOUR_PAYPAL_SECRET") {
                            return redirect()->route('ai.credits.paywithpaypal', compact('planId', 'couponId'));
                        } else {
                            return redirect()->route('user.ai.credits.checkout', $planId)->with('failed', trans('Something went wrong!'));
                        }
                    } else if ($payment_mode->payment_gateway_id == "60964410731d9") {
                        // Check key and secret
                        if ($config[6]->config_value != "YOUR_RAZORPAY_KEY" || $config[7]->config_value != "YOUR_RAZORPAY_SECRET") {
                            return redirect()->route('ai.credits.prepare.razorpay', compact('planId', 'couponId'));
                        } else {
                            return redirect()->route('user.ai.credits.checkout', $planId)->with('failed', trans('Something went wrong!'));
                        }
                    } else if ($payment_mode->payment_gateway_id == "60964410732t9") {
                        // Check key and secret
                        if ($config[9]->config_value != "YOUR_STRIPE_PUB_KEY" || $config[10]->config_value != "YOUR_STRIPE_SECRET") {
                            return redirect()->route('ai.credits.prepare.stripe', compact('planId', 'couponId'));
                        } else {
                            return redirect()->route('user.ai.credits.checkout', $planId)->with('failed', trans('Something went wrong!'));
                        }
                    } else if ($payment_mode->payment_gateway_id == "60964410736592") {
                        // Check key and secret
                        if ($config[33]->config_value != "PAYSTACK_PUBLIC_KEY" || $config[34]->config_value != "PAYSTACK_SECRET_KEY") {
                            return redirect()->route('ai.credits.prepare.paystack', compact('planId', 'couponId'));
                        } else {
                            return redirect()->route('user.ai.credits.checkout', $planId)->with('failed', trans('Something went wrong!'));
                        }
                    } else if ($payment_mode->payment_gateway_id == "6096441071589632") {
                        // Check key and secret
                        if ($config[37]->config_value != "mollie_key") {
                            return redirect()->route('ai.credits.prepare.mollie', compact('planId', 'couponId'));
                        } else {
                            return redirect()->route('user.ai.credits.checkout', $planId)->with('failed', trans('Something went wrong!'));
                        }
                    } else if ($payment_mode->payment_gateway_id == "659644107y2g5") {
                        // Check key and secret
                        if ($config[31]->config_value != "") {
                            return redirect()->route('ai.credits.prepare.offline', compact('planId', 'couponId'));
                        } else {
                            return redirect()->route('user.ai.credits.checkout', $planId)->with('failed', trans('Something went wrong!'));
                        }
                    } else if ($payment_mode->payment_gateway_id == "19065566166715") {
                        // Check key and secret
                        if ($config[77]->config_value != "YOUR_PHONEPE_CLIENT_ID" || $config[78]->config_value != "YOUR_PHONEPE_CLIENT_VERSION" || $config[79]->config_value != "YOUR_PHONEPE_CLIENT_SECRET") {
                            return redirect()->route('ai.credits.prepare.phonepe', compact('planId', 'couponId'));
                        } else {
                            return redirect()->route('user.ai.credits.checkout', $planId)->with('failed', trans('Something went wrong!'));
                        }
                    } else if ($payment_mode->payment_gateway_id == "776111730465") {
                        // Check key and secret
                        if ($config[47]->config_value != "YOUR_MERCADO_PAGO_PUBLIC_KEY" || $config[48]->config_value != "YOUR_MERCADO_PAGO_ACCESS_TOKEN") {
                            return redirect()->route('ai.credits.prepare.mercadopago', compact('planId', 'couponId'));
                        } else {
                            return redirect()->route('user.ai.credits.checkout', $planId)->with('failed', trans('Something went wrong!'));
                        }
                    } else if ($payment_mode->payment_gateway_id == "767510608137") {
                        // Check key and secret
                        if ($config[49]->config_value != "YOUR_TOYYIBPAY_API_KEY" || $config[50]->config_value != "YOUR_TOYYIBPAY_CATEGORY_CODE") {
                            return redirect()->route('ai.credits.prepare.toyyibpay', compact('planId', 'couponId'));
                        } else {
                            return redirect()->route('user.ai.credits.checkout', $planId)->with('failed', trans('Something went wrong!'));
                        }
                    } else if ($payment_mode->payment_gateway_id == "754201940107") {
                        // Check key, secret and encryption key
                        if ($config[51]->config_value != "YOUR_FLW_PUBLIC_KEY" || $config[52]->config_value != "YOUR_FLW_SECRET_KEY" || $config[53]->config_value != "YOUR_FLW_ENCRYPTION_KEY") {
                            return redirect()->route('ai.credits.prepare.flutterwave', compact('planId', 'couponId'));
                        } else {
                            return redirect()->route('user.ai.credits.checkout', $planId)->with('failed', trans('Something went wrong!'));
                        }
                    } else if ($payment_mode->payment_gateway_id == "5992737427969") {
                        // Check key, secret and encryption key
                        if ($config[65]->config_value != "YOUR_PADDLE_SELLER_ID" || $config[66]->config_value != "YOUR_PADDLE_API_KEY" || $config[67]->config_value != "YOUR_PADDLE_CLIENT_SIDE_TOKEN") {
                            return redirect()->route('ai.credits.prepare.paddle', compact('planId', 'couponId'));
                        } else {
                            return redirect()->route('user.ai.credits.checkout', $planId)->with('failed', trans('Something went wrong!'));
                        }
                    } else if ($payment_mode->payment_gateway_id == "5992737427970") {
                        // Check key, secret and encryption key
                        if ($config[68]->config_value != "YOUR_PAYTR_MERCHANT_ID" || $config[69]->config_value != "YOUR_PAYTR_MERCHANT_KEY" || $config[70]->config_value != "YOUR_PAYTR_MERCHANT_SALT") {
                            return redirect()->route('ai.credits.prepare.paytr', compact('planId', 'couponId'));
                        } else {
                            return redirect()->route('user.ai.credits.checkout', $planId)->with('failed', trans('Something went wrong!'));
                        }
                    } else if ($payment_mode->payment_gateway_id == "278523098674") {
                        // Check key, secret and encryption key
                        if ($config[72]->config_value != "YOUR_XENDIT_SECRET_KEY") {
                            return redirect()->route('ai.credits.prepare.xendit', compact('planId', 'couponId'));
                        } else {
                            return redirect()->route('user.ai.credits.checkout', $planId)->with('failed', trans('Something went wrong!'));
                        }
                    } else if ($payment_mode->payment_gateway_id == "278523098675") {
                        // Check key, secret and encryption key
                        if ($config[85]->config_value != "YOUR_CASHFREE_APP_ID" || $config[86]->config_value != "YOUR_CASHFREE_SECRET_KEY") {
                            return redirect()->route('ai.credits.prepare.cashfree', compact('planId', 'couponId'));
                        } else {
                            return redirect()->route('user.ai.credits.checkout', $planId)->with('failed', trans('Something went wrong!'));
                        }
                    } else if ($payment_mode->payment_gateway_id == "278523098676") {
                        // Check key, secret and encryption key
                        if ($config[90]->config_value != "YOUR_IYZICO_API_KEY" || $config[91]->config_value != "YOUR_IYZICO_SECRET_KEY") {
                            return redirect()->route('ai.credits.prepare.iyzipay', compact('planId', 'couponId'));
                        } else {
                            return redirect()->route('user.ai.credits.checkout', $planId)->with('failed', trans('Something went wrong!'));
                        }
                    } else {
                        return redirect()->route('user.ai.credits.checkout', $planId)->with('failed', trans('Something went wrong!'));
                    }
                }
            }
        } else {
            return redirect()->route('user.ai.credits.plans')->with('failed', __('Payment gateway not selected.'));
        }
    }
}
