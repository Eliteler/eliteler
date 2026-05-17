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

use App\AiCredit;
use App\AiCreditsPlan;
use App\AiCreditsTransaction;
use App\AppliedCoupon;
use App\Classes\OrderAICredits;
use App\Classes\OrderPaidAICredits;
use App\Coupon;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\ProductionEnvironment;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use PayPalCheckoutSdk\Orders\OrdersCaptureRequest;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;
use PayPalHttp\HttpException;

class PaypalController extends Controller
{
    protected $apiContext;

    public function __construct()
    {
        // Fetch PayPal configuration from database
        $paypalConfiguration = DB::table('config')->get();

        // Set up PayPal environment
        $clientId = $paypalConfiguration[4]->config_value;
        $clientSecret = $paypalConfiguration[5]->config_value;
        $mode = $paypalConfiguration[3]->config_value;

        if ($mode == "sandbox") {
            $environment = new SandboxEnvironment($clientId, $clientSecret);
        } else {
            $environment = new ProductionEnvironment($clientId, $clientSecret);
        }
        $this->apiContext = new PayPalHttpClient($environment);
    }

    public function payWithPayPal(Request $request, $planId, $couponId)
    {
        if (Auth::check()) {
            // Get the ai credits plan details
            $planDetails = AiCreditsPlan::where('ai_credits_plan_id', $planId)->where('status', 'active')->first();

            // Check if the plan exists
            if (!$planDetails) {
                return redirect()->route('user.ai.credits.plans')->with('failed', __('This AI Credits plan does not exist.'));
            }

            $config = DB::table('config')->get();
            $userData = Auth::user();

            // Check applied coupon
            $couponDetails = Coupon::where('used_for', 'ai_credits')->where('coupon_id', $couponId)->first();

            // Applied tax in total
            $appliedTaxInTotal = 0;

            // Discount price
            $discountPrice = 0;

            // Applied coupon
            $appliedCoupon = null;

            // AI Credits Order ID
            $aiCreditsOrderId = "OD" . preg_replace('/\D/', '', Str::uuid());
            $aiCreditsTransactionId = "TX" . preg_replace('/\D/', '', Str::uuid());

            // Check coupon type
            if ($couponDetails != null) {
                if ($couponDetails->coupon_type == 'fixed') {
                    // Applied tax in total
                    $appliedTaxInTotal = ((float)($planDetails->plan_price) * (float)($config[25]->config_value) / 100);

                    // Get discount in plan price
                    $discountPrice = $couponDetails->coupon_amount;

                    // Total
                    $amountToBePaid = ($planDetails->plan_price + $appliedTaxInTotal) - $discountPrice;
                    $amountToBePaid = number_format($amountToBePaid, 2, '.', '');

                    // Coupon is applied
                    $appliedCoupon = $couponDetails->coupon_code;
                } else {
                    // Applied tax in total
                    $appliedTaxInTotal = ((float)($planDetails->plan_price) * (float)($config[25]->config_value) / 100);

                    // Get discount in plan price
                    $discountPrice = $planDetails->plan_price * $couponDetails->coupon_amount / 100;

                    // Total
                    $amountToBePaid = ($planDetails->plan_price + $appliedTaxInTotal) - $discountPrice;
                    $amountToBePaid = number_format($amountToBePaid, 2, '.', '');

                    // Coupon is applied
                    $appliedCoupon = $couponDetails->coupon_code;
                }
            } else {
                // Applied tax in total
                $appliedTaxInTotal = ((float)($planDetails->plan_price) * (float)($config[25]->config_value) / 100);

                // Total
                $amountToBePaid = ($planDetails->plan_price + $appliedTaxInTotal) - $discountPrice;
                $amountToBePaid = number_format($amountToBePaid, 2, '.', '');
            }

            // Construct PayPal order request
            $request = new OrdersCreateRequest();
            $request->prefer('return=representation');
            $request->body = [
                'intent' => 'CAPTURE',
                'purchase_units' => [[
                    'amount' => [
                        'currency_code' => $config[1]->config_value,
                        'value' => $amountToBePaid,
                    ]
                ]],
                'application_context' => [
                    'cancel_url' => route('ai.credits.paypal.payment.status'),
                    'return_url' => route('ai.credits.paypal.payment.status'),
                ]
            ];

            try {
                // Create PayPal order
                $response = $this->apiContext->execute($request);
                foreach ($response->result->links as $link) {
                    if ($link->rel == 'approve') {
                        $redirectUrl = $link->href;
                        break;
                    }
                }

                // Store transaction details in ai_credits_transactions
                $aiCreditsTransaction = new AiCreditsTransaction();
                $aiCreditsTransaction->ai_credits_transaction_id = $aiCreditsTransactionId;
                $aiCreditsTransaction->ai_credits_order_id = $aiCreditsOrderId;
                $aiCreditsTransaction->payment_transaction_id = $response->result->id;
                $aiCreditsTransaction->user_id = Auth::user()->id;
                $aiCreditsTransaction->ai_credits_plan_id = $planId;
                $aiCreditsTransaction->purchase_details = $planDetails->plan_name . " AI Credits Plan";
                $aiCreditsTransaction->payment_method = "Paypal";
                $aiCreditsTransaction->currency = $config[1]->config_value;
                $aiCreditsTransaction->amount = $amountToBePaid;
                $aiCreditsTransaction->invoice_details = json_encode($this->prepareInvoiceDetails($config, $userData, $amountToBePaid, $planDetails, $appliedCoupon, $discountPrice));
                $aiCreditsTransaction->payment_status = "pending";
                $aiCreditsTransaction->save();

                // Coupon is not applied
                if ($couponId != " ") {
                    // Save applied coupon
                    $appliedCoupon = new AppliedCoupon;
                    $appliedCoupon->applied_coupon_id = uniqid();
                    $appliedCoupon->transaction_id = $aiCreditsTransactionId;
                    $appliedCoupon->user_id = Auth::user()->id;
                    $appliedCoupon->coupon_id = $couponId;
                    $appliedCoupon->status = 0;
                    $appliedCoupon->save();
                }

                // Redirect to PayPal for payment
                return Redirect::away($redirectUrl);
            } catch (\Exception $ex) {
                if (config('app.debug')) {
                    return redirect()->route('user.ai.credits.plans')->with('failed', trans('Payment failed, Something went wrong!'));
                } else {
                    return redirect()->route('user.ai.credits.plans')->with('failed', trans('Payment failed, Something went wrong!'));
                }
            }
        } else {
            return redirect()->route('login');
        }
    }

    // Update transaction status
    public function paypalPaymentStatus(Request $request)
    {
        if (empty($request->PayerID) || empty($request->token)) {
            Session::put('error', 'Payment cancelled!');
            return redirect()->route('user.ai.credits.plans');
        }

        try {
            // Get the payment ID from the request
            $paymentId = $request->token;
            $orderId = $paymentId;
            $transactionDetails = AiCreditsTransaction::where('payment_transaction_id', $paymentId)->first();

            $request = new OrdersCaptureRequest($paymentId);
            $response = $this->apiContext->execute($request);

            if ($response->statusCode == 201) {

                // AI Credits Order Placed
                $order = new OrderPaidAICredits();
                $order->order($orderId, $response);

                return redirect()->route('user.ai.credits.plans')->with('success', trans('Payment successful!'));
            } else {
                // Update transaction status in ai_credits_transactions
                $transactionDetails->payment_status = "failed";
                $transactionDetails->save();

                return redirect()->route('user.ai.credits.plans')->with('failed', trans('Payment failed!'));
            }
        } catch (HttpException $ex) {
            // Handle the HTTP exception
            // Log the error or display an error message
            // Example: Log::error('PayPal HTTP Exception: ' . $e->getMessage());

            // Set an error message for the user
            Session::flash('failed', trans('An error occurred while communicating with PayPal. Please try again later.'));

            // Redirect back to the user plans page or any other appropriate page
            return redirect()->route('user.ai.credits.plans')->with('failed', trans('An error occurred while communicating with PayPal. Please try again later.'));
        }
    }

    // Prepare invoice details
    private function prepareInvoiceDetails($config, $userData, $amountToBePaid, $planDetails, $appliedCoupon, $discountPrice)
    {
        // Prepare invoice details
        $invoiceDetails = [
            'from_billing_name' => $config[16]->config_value,
            'from_billing_address' => $config[19]->config_value,
            'from_billing_city' => $config[20]->config_value,
            'from_billing_state' => $config[21]->config_value,
            'from_billing_zipcode' => $config[22]->config_value,
            'from_billing_country' => $config[23]->config_value,
            'from_vat_number' => $config[26]->config_value,
            'from_billing_phone' => $config[18]->config_value,
            'from_billing_email' => $config[17]->config_value,
            'to_billing_name' => $userData->billing_name,
            'to_billing_address' => $userData->billing_address,
            'to_billing_city' => $userData->billing_city,
            'to_billing_state' => $userData->billing_state,
            'to_billing_zipcode' => $userData->billing_zipcode,
            'to_billing_country' => $userData->billing_country,
            'to_billing_phone' => $userData->billing_phone,
            'to_billing_email' => $userData->billing_email,
            'to_vat_number' => $userData->vat_number,
            'tax_name' => $config[24]->config_value,
            'tax_type' => $config[14]->config_value,
            'tax_value' => $config[25]->config_value,
            'applied_coupon' => $appliedCoupon,
            'discounted_price' => $discountPrice,
            'invoice_amount' => $amountToBePaid,
            'subtotal' => $planDetails->plan_price,
            'tax_amount' => (float)($planDetails->plan_price) * (float)($config[25]->config_value) / 100
        ];

        return $invoiceDetails;
    }
}
