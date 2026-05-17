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
use App\Coupon;
use App\Setting;
use App\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;

class PhonePeController extends Controller
{
    /**
     * Prepare PhonePe Payment
     */

    public function preparePhonpe($planId, $couponId)
    {
        if (Auth::check()) {
            // Queries
            $settings = Setting::where('status', 1)->first();
            $config = DB::table('config')->get();
            $userData = User::where('id', Auth::user()->id)
                ->first();

            // AI Credits Plan
            $plan_details = AiCreditsPlan::where('ai_credits_plan_id', $planId)->where('status', 'active')
                ->first();

            // Check plan
            if (!$plan_details) {
                return view('errors.404');
            }

            // Auth Token
            $authToken = $this->getPhonePeAuthToken();

            if (!$authToken) {
                return redirect()->route('user.ai.credits.plans')
                    ->with('failed', trans('Failed to fetch PhonePe authentication token.'));
            }

            // Coupon
            $couponDetails = Coupon::where('used_for', 'ai_credits')->where('coupon_id', $couponId)->first();

            // Applied tax
            $appliedTaxInTotal = 0;

            // Discount
            $discountPrice = 0;

            // Applied coupon
            $appliedCoupon = null;

            // Coupon logic
            if ($couponDetails != null) {

                if ($couponDetails->coupon_type == 'fixed') {

                    // Tax
                    $appliedTaxInTotal = ((float)($plan_details->plan_price) * (float)($config[25]->config_value) / 100);
                    // Discount
                    $discountPrice = $couponDetails->coupon_amount;
                    // Total
                    $amountToBePaid = ($plan_details->plan_price + $appliedTaxInTotal) - $discountPrice;
                    $amountToBePaid = (float)number_format($amountToBePaid, 2, '.', '');

                    // Coupon
                    $appliedCoupon = $couponDetails->coupon_code;
                } else {

                    // Tax
                    $appliedTaxInTotal = ((float)($plan_details->plan_price) * (float)($config[25]->config_value) / 100);
                    // Discount
                    $discountPrice = $plan_details->plan_price * $couponDetails->coupon_amount / 100;
                    // Total
                    $amountToBePaid = ($plan_details->plan_price + $appliedTaxInTotal) - $discountPrice;
                    $amountToBePaid = (float)number_format($amountToBePaid, 2, '.', '');

                    // Coupon
                    $appliedCoupon = $couponDetails->coupon_code;
                }
            } else {

                // Tax
                $appliedTaxInTotal = ((float)($plan_details->plan_price) * (float)($config[25]->config_value) / 100);
                // Total
                $amountToBePaid = ($plan_details->plan_price + $appliedTaxInTotal);
            }

            $amountToBePaidPaise = $amountToBePaid * 100;

            // IDs
            $aiCreditsTransactionId = "TX" . preg_replace('/\D/', '', Str::uuid());
            $aiCreditsOrderId = "OD" . preg_replace('/\D/', '', Str::uuid());

            try {

                $data = [

                    'merchantOrderId' => $aiCreditsTransactionId,
                    'amount' => $amountToBePaidPaise,
                    'paymentFlow' => [
                        'type' => 'PG_CHECKOUT',
                        'merchantUrls' => [
                            'redirectUrl' => route('ai.credits.phonepe.payment.status')
                        ]
                    ]
                ];

                $response = Http::withHeaders([
                    'Content-Type' => 'application/json',
                    'Authorization' => "O-Bearer " . $authToken
                ])->post('https://api.phonepe.com/apis/pg/checkout/v2/pay', $data);

                // Response
                $rData = $response->json();

                // Payment Pending
                if (!empty($rData['state']) && $rData['state'] == "PENDING") {

                    // Invoice Details
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
                    $invoice_details['to_billing_name'] = $userData->billing_name;
                    $invoice_details['to_billing_address'] = $userData->billing_address;
                    $invoice_details['to_billing_city'] = $userData->billing_city;
                    $invoice_details['to_billing_state'] = $userData->billing_state;
                    $invoice_details['to_billing_zipcode'] = $userData->billing_zipcode;
                    $invoice_details['to_billing_country'] = $userData->billing_country;
                    $invoice_details['to_billing_phone'] = $userData->billing_phone;
                    $invoice_details['to_billing_email'] = $userData->billing_email;
                    $invoice_details['to_vat_number'] = $userData->vat_number;
                    $invoice_details['subtotal'] = $plan_details->plan_price;
                    $invoice_details['tax_name'] = $config[24]->config_value;
                    $invoice_details['tax_type'] = $config[14]->config_value;
                    $invoice_details['tax_value'] = $config[25]->config_value;
                    $invoice_details['tax_amount'] = $appliedTaxInTotal;
                    $invoice_details['applied_coupon'] = $appliedCoupon;
                    $invoice_details['discounted_price'] = $discountPrice;
                    $invoice_details['invoice_amount'] = $amountToBePaid;

                    // Save Transaction
                    $transaction = new AiCreditsTransaction();
                    $transaction->ai_credits_transaction_id = $aiCreditsTransactionId;
                    $transaction->ai_credits_order_id = $aiCreditsOrderId;
                    $transaction->payment_transaction_id = $rData['orderId'];
                    $transaction->user_id = Auth::user()->id;
                    $transaction->ai_credits_plan_id = $plan_details->ai_credits_plan_id;
                    $transaction->purchase_details = $plan_details->plan_name . " AI Credits Plan";
                    $transaction->payment_method = "PhonePe";
                    $transaction->currency = $config[1]->config_value;
                    $transaction->amount = $amountToBePaid;
                    $transaction->invoice_details = json_encode($invoice_details);
                    $transaction->payment_status = "pending";
                    $transaction->save();

                    // Save coupon
                    if ($couponId != " ") {

                        $appliedCoupon = new AppliedCoupon();
                        $appliedCoupon->applied_coupon_id = uniqid();
                        $appliedCoupon->transaction_id = $aiCreditsTransactionId;
                        $appliedCoupon->user_id = Auth::user()->id;
                        $appliedCoupon->coupon_id = $couponId;
                        $appliedCoupon->status = 0;
                        $appliedCoupon->save();
                    }

                    return redirect()
                        ->to($rData['redirectUrl']);
                } else {
                    return redirect()->route('user.ai.credits.plans')
                        ->with('failed', trans('Payment failed.'));
                }
            } catch (\Exception $e) {
                return redirect()->route('user.ai.credits.plans')
                    ->with('failed', trans('Payment failed.'));
            }
        } else {
            return redirect()
                ->route('login');
        }
    }

    /**
     * PhonePe Auth Token
     */

    private function getPhonePeAuthToken()
    {
        // Cache
        if (Cache::has('phonepe_auth_token')) {
            return Cache::get('phonepe_auth_token');
        }

        // Config
        $config = DB::table('config')->get();

        // Validation
        if ($config[77]->config_value == 'YOUR_PHONEPE_CLIENT_ID' || $config[78]->config_value == 'YOUR_PHONEPE_CLIENT_VERSION' || $config[79]->config_value == 'YOUR_PHONEPE_CLIENT_SECRET') {
            return trans("Something went wrong!");
        }

        // URL
        $authUrl = "https://api.phonepe.com/apis/identity-manager/v1/oauth/token";

        // Payload
        $payload = [
            "client_id" => $config[77]->config_value,
            "client_version" => $config[78]->config_value,
            "client_secret" => $config[79]->config_value,
            "grant_type" => "client_credentials"
        ];

        // Request
        $response = Http::asForm()->post($authUrl, $payload);

        // Decode
        $responseData = $response->json();

        // Token
        if (isset($responseData['access_token'])) {
            Cache::put('phonepe_auth_token', $responseData['access_token'], now()->addMinutes(55));
            return $responseData['access_token'];
        }

        return trans("Failed to retrieve token");
    }

    /**
     * PhonePe Payment Status
     */

    public function phonepePaymentStatus(Request $request)
    {
        // Latest Transaction
        $transactionDetails = AiCreditsTransaction::where('payment_method', 'PhonePe')->where('user_id', Auth::user()
            ->id)
            ->latest()
            ->first();

        // Check transaction
        if (!$transactionDetails) {
            return redirect()->route('user.ai.credits.plans')
                ->with('failed', trans('Payment failed.'));
        }

        // Auth Token
        $authToken = $this->getPhonePeAuthToken();

        if (!$authToken) {
            return redirect()->route('user.ai.credits.plans')
                ->with('failed', trans('Failed to fetch PhonePe authentication token.'));
        }

        // Status URL
        $statusUrl = "https://api.phonepe.com/apis/pg/checkout/v2/order/" . $transactionDetails->ai_credits_transaction_id . "/status?details=false&errorContext=true";

        // Request
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => "O-Bearer " . $authToken
        ])->get($statusUrl);

        // Response
        $res = json_decode($response->body());

        try {

            // Failed
            if ($res->success == false) {
                return redirect()
                    ->route('user.ai.credits.plans')
                    ->with('failed', trans($res->message));
            }
        } catch (\Exception $e) {
        }

        // Completed
        if ($res->state == "COMPLETED") {

            $config = DB::table('config')->get();

            // Transaction ID
            $paymentId = $res->paymentDetails[0]->transactionId;
            // Update transaction
            $transactionDetails->payment_transaction_id = $paymentId;
            $transactionDetails->payment_status = 'paid';
            $transactionDetails->save();

            // Plan Details
            $planDetails = AiCreditsPlan::where('ai_credits_plan_id', $transactionDetails->ai_credits_plan_id)
                ->first();

            // Update AI Credits
            $aiCredit = AiCredit::where('user_id', Auth::user()->user_id)
                ->first();

            if ($aiCredit) {
                $aiCredit->credits += $planDetails->no_of_ai_credits;
                $aiCredit->save();
            } else {
                $aiCredit = new AiCredit();
                $aiCredit->user_id = $transactionDetails->user_id;
                $aiCredit->credits = $planDetails->no_of_ai_credits;
                $aiCredit->save();
            }

            // Update coupon
            AppliedCoupon::where('transaction_id', $transactionDetails->ai_credits_transaction_id)
                ->update(['status' => 1]);

            // Invoice
            $invoiceCount = AiCreditsTransaction::where('invoice_prefix', $config[15]->config_value)
                ->count();
            $invoiceNumber = $invoiceCount + 1;

            // Update invoice
            $transactionDetails->invoice_prefix = $config[15]->config_value;
            $transactionDetails->invoice_number = $invoiceNumber;
            $transactionDetails->save();

            // Invoice Details
            $encode = json_decode($transactionDetails->invoice_details, true);

            $details = [
                'from_billing_name' => $encode['from_billing_name'],
                'from_billing_email' => $encode['from_billing_email'],
                'from_billing_address' => $encode['from_billing_address'],
                'from_billing_city' => $encode['from_billing_city'],
                'from_billing_state' => $encode['from_billing_state'],
                'from_billing_country' => $encode['from_billing_country'],
                'from_billing_zipcode' => $encode['from_billing_zipcode'],
                'transaction_id' => $paymentId,
                'to_billing_name' => $encode['to_billing_name'],
                'invoice_currency' => $transactionDetails->currency,
                'subtotal' => $encode['subtotal'],
                'tax_amount' => $encode['tax_amount'],
                'applied_coupon' => $encode['applied_coupon'],
                'discounted_price' => $encode['discounted_price'],
                'invoice_amount' => $encode['invoice_amount'],
                'invoice_id' => $config[15]->config_value . $invoiceNumber,
                'invoice_date' => $transactionDetails->created_at,
                'description' => $transactionDetails->purchase_details,
                'email_heading' => $config[27]->config_value,
                'email_footer' => $config[28]->config_value,
            ];

            // Send Invoice
            try {
                Mail::to($encode['to_billing_email'])->send(new \App\Mail\SendEmailInvoice($details));
            } catch (\Exception $e) {
            }

            return redirect()->route('user.ai.credits.plans')
                ->with('success', trans('Payment successful!'));
        } else {
            // Failed
            $transactionDetails->payment_status = 'failed';
            $transactionDetails->save();

            return redirect()
                ->route('user.ai.credits.plans')
                ->with('failed', trans('Payment failed.'));
        }
    }
}
