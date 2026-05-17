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
use App\User;
use App\Http\Controllers\Controller;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class FlutterwaveController extends Controller
{
    protected $secretKey;
    protected $hashKey;
    protected $baseUrl;

    public function __construct()
    {
        $config = DB::table('config')->get();

        $this->secretKey = $config[52]->config_value;
        $this->hashKey = $config[96]->config_value;
        $this->baseUrl = "https://api.flutterwave.com/v3";
    }

    /**
     * Prepare Flutterwave Payment
     */
    public function prepareFlutterwave(Request $request, $planId, $couponId)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $config = DB::table('config')->get();

        // User Details
        $userData = User::where('id', Auth::user()->id)->first();

        // AI Credits Plan
        $plan_details = AiCreditsPlan::where('ai_credits_plan_id', $planId)
            ->where('status', 'active')
            ->first();

        if (!$plan_details) {
            return redirect()
                ->route('user.ai.credits.plans')
                ->with('failed', trans('Invalid plan!'));
        }

        // IDs
        $aiCreditsTransactionId = "TX" . preg_replace('/\D/', '', Str::uuid());
        $aiCreditsOrderId = "OD" . preg_replace('/\D/', '', Str::uuid());

        // Coupon
        $couponDetails = Coupon::where('used_for', 'ai_credits')
            ->where('coupon_id', $couponId)
            ->first();

        // Applied tax
        $appliedTaxInTotal = 0;

        // Discount
        $discountPrice = 0;

        // Applied coupon
        $appliedCoupon = null;

        // Coupon Logic
        if ($couponDetails != null) {

            if ($couponDetails->coupon_type == 'fixed') {

                // Tax
                $appliedTaxInTotal = ((float) $plan_details->plan_price * (float) $config[25]->config_value / 100);

                // Discount
                $discountPrice = $couponDetails->coupon_amount;

                // Total
                $amountToBePaid = ($plan_details->plan_price + $appliedTaxInTotal) - $discountPrice;

                $amountToBePaid = (float) number_format($amountToBePaid, 2, '.', '');

                // Coupon
                $appliedCoupon = $couponDetails->coupon_code;
            } else {

                // Tax
                $appliedTaxInTotal = ((float) $plan_details->plan_price * (float) $config[25]->config_value / 100);

                // Discount
                $discountPrice = $plan_details->plan_price * $couponDetails->coupon_amount / 100;

                // Total
                $amountToBePaid = ($plan_details->plan_price + $appliedTaxInTotal) - $discountPrice;

                $amountToBePaid = (float) number_format($amountToBePaid, 2, '.', '');

                // Coupon
                $appliedCoupon = $couponDetails->coupon_code;
            }
        } else {

            // Tax
            $appliedTaxInTotal = ((float) $plan_details->plan_price * (float) $config[25]->config_value / 100);

            // Total
            $amountToBePaid = ($plan_details->plan_price + $appliedTaxInTotal);
        }

        $amountToBePaidPaise = $amountToBePaid;

        $client = new Client();

        $data = [
            'tx_ref' => $aiCreditsTransactionId,
            'amount' => $amountToBePaidPaise,
            'currency' => $config[1]->config_value,
            'redirect_url' => route('ai.credits.flutterwave.payment.status'),

            'customer' => [
                'email' => Auth::user()->email,
                'name' => Auth::user()->name,
                'phone_number' => Auth::user()->billing_phone == null
                    ? '9876543210'
                    : Auth::user()->billing_phone,
            ],

            'customizations' => [
                'title' => config('app.name'),
                'logo' => asset('img/favicon.png'),
            ]
        ];

        try {

            $response = $client->post("{$this->baseUrl}/payments", [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->secretKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => $data
            ]);

            $responseBody = json_decode($response->getBody(), true);

            if ($responseBody['status'] === 'success') {

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
                $transaction->payment_transaction_id = $aiCreditsTransactionId;
                $transaction->user_id = Auth::user()->id;
                $transaction->ai_credits_plan_id = $plan_details->ai_credits_plan_id;
                $transaction->purchase_details = $plan_details->plan_name . " AI Credits Plan";
                $transaction->payment_method = "Flutterwave";
                $transaction->currency = $config[1]->config_value;
                $transaction->amount = $amountToBePaid;
                $transaction->invoice_details = json_encode($invoice_details);
                $transaction->payment_status = "pending";

                $transaction->save();

                // Save Coupon
                if ($couponId != " ") {

                    $appliedCoupon = new AppliedCoupon();

                    $appliedCoupon->applied_coupon_id = uniqid();
                    $appliedCoupon->transaction_id = $aiCreditsTransactionId;
                    $appliedCoupon->user_id = Auth::user()->id;
                    $appliedCoupon->coupon_id = $couponId;
                    $appliedCoupon->status = 0;

                    $appliedCoupon->save();
                }

                return redirect($responseBody['data']['link']);
            }

            return redirect()
                ->route('user.ai.credits.plans')
                ->with('failed', trans('Payment initiation failed'));
        } catch (\Exception $e) {

            return redirect()
                ->route('user.ai.credits.plans')
                ->with('failed', trans('Failed to initiate payment.'));
        }
    }

    /**
     * Flutterwave Payment Status
     */
    public function flutterwavePaymentStatus(Request $request)
    {
        // Transaction Details
        $txRef = $request->query('tx_ref');
        $status = $request->query('status');

        // Success
        if ($status == "successful") {

            $transactionId = $request->query('transaction_id');

            if ($transactionId) {

                $client = new Client();

                try {

                    $response = $client->get(
                        "{$this->baseUrl}/transactions/{$transactionId}/verify",
                        [
                            'headers' => [
                                'Authorization' => 'Bearer ' . $this->secretKey,
                                'Content-Type' => 'application/json',
                            ]
                        ]
                    );

                    $verificationResponse = json_decode($response->getBody(), true);

                    // Get tx_ref and flw_ref
                    $tx_ref = $verificationResponse['data']['tx_ref'];
                    $flw_ref = $verificationResponse['data']['flw_ref'];

                    if ($verificationResponse['status'] === 'success') {

                        // Static Function
                        $updatedData = $this->paymentSuccessStatic(
                            $tx_ref,
                            $flw_ref
                        );

                        return redirect()
                            ->route('user.ai.credits.plans')
                            ->with($updatedData);
                    }

                    return redirect()
                        ->route('user.ai.credits.plans')
                        ->with('failed', trans('Payment failed.'));
                } catch (\Exception $e) {

                    return redirect()
                        ->route('user.ai.credits.plans')
                        ->with('failed', trans('Payment verification failed.'));
                }
            } else {

                return redirect()
                    ->route('user.ai.credits.plans')
                    ->with('failed', trans('Transaction not found.'));
            }
        } elseif ($status === 'failed') {

            AiCreditsTransaction::where(
                'ai_credits_transaction_id',
                $txRef
            )->update([
                'payment_status' => 'failed',
            ]);

            return redirect()
                ->route('user.ai.credits.plans')
                ->with('failed', trans('Transaction failed.'));
        } elseif ($status === 'cancelled') {

            AiCreditsTransaction::where(
                'ai_credits_transaction_id',
                $txRef
            )->update([
                'payment_status' => 'cancelled',
            ]);

            return redirect()
                ->route('user.ai.credits.plans')
                ->with('failed', trans('Transaction cancelled.'));
        }

        return redirect()
            ->route('user.ai.credits.plans')
            ->with('failed', trans('Invalid transaction status.'));
    }

    /**
     * Callback
     */
    public function flutterwaveCallback(Request $request)
    {
        // Verify Flutterwave signature
        $secretHash = $this->hashKey;

        $signature = $request->header('flutterwave-signature');

        if (!$signature || $signature !== $secretHash) {
            abort(401);
        }

        Log::info('Flutterwave Webhook:', $request->all());

        $payload = $request->all();

        // Webhook Event
        if (
            isset($payload['event']) &&
            $payload['event'] === 'charge.completed'
        ) {

            $data = $payload['data'];

            $tx_ref = $data['tx_ref'] ?? null;
            $flw_ref = $data['flw_ref'] ?? null;
            $status = $data['status'] ?? null;
            $processor_response = $data['processor_response'] ?? null;

            if (
                $status === 'successful' &&
                $processor_response === 'Transaction completed'
            ) {

                // Static Function
                $this->paymentSuccessStatic($tx_ref, $flw_ref);

                AiCreditsTransaction::where(
                    'ai_credits_transaction_id',
                    $tx_ref
                )->update([
                    'payment_status' => 'paid'
                ]);

                return response('OK', 200);
            }

            if ($status === 'failed') {

                AiCreditsTransaction::where(
                    'ai_credits_transaction_id',
                    $tx_ref
                )->update([
                    'payment_status' => 'failed'
                ]);

                return response('OK', 200);
            }
        }

        return response('OK', 200);
    }

    /**
     * Payment Success Static
     */
    public function paymentSuccessStatic($txRef, $flwRef)
    {
        // Validation
        if ($txRef == null && $flwRef == null) {
            AiCreditsTransaction::where(
                'payment_transaction_id',
                $txRef
            )->update([
                'payment_status' => 'failed'
            ]);

            return [
                'failed' => trans('Transaction not found.'),
            ];
        }

        // Config
        $config = DB::table('config')->get();

        // Transaction
        $transaction_details = AiCreditsTransaction::where(
            'payment_transaction_id',
            $txRef
        )->where(
            'payment_status',
            '!=',
            'paid'
        )->first();

        if (!$transaction_details) {
            return [
                'failed' => trans('Transaction not found.'),
            ];
        }

        // Plan Details
        $planDetails = AiCreditsPlan::where(
            'ai_credits_plan_id',
            $transaction_details->ai_credits_plan_id
        )->first();

        // Invoice
        $invoice_count = AiCreditsTransaction::where(
            "invoice_prefix",
            $config[15]->config_value
        )->count();

        $invoice_number = $invoice_count + 1;

        // Update Transaction
        AiCreditsTransaction::where(
            'payment_transaction_id',
            $transaction_details->payment_transaction_id
        )->update([
            'payment_transaction_id' => $flwRef,
            'invoice_prefix' => $config[15]->config_value,
            'invoice_number' => $invoice_number,
            'payment_status' => 'paid',
        ]);

        // Update AI Credits
        $aiCredit = AiCredit::where('user_id', Auth::user()->user_id)->first();

        if ($aiCredit) {
            $aiCredit->credits += $planDetails->no_of_ai_credits;
            $aiCredit->save();
        } else {
            $aiCredit = new AiCredit();
            $aiCredit->user_id = $transaction_details->user_id;
            $aiCredit->credits = $planDetails->no_of_ai_credits;
            $aiCredit->save();
        }

        // Update Coupon
        AppliedCoupon::where(
            'transaction_id',
            $txRef
        )->update([
            'status' => 1
        ]);

        // Invoice Details
        $encode = json_decode(
            $transaction_details->invoice_details,
            true
        );

        $details = [
            'from_billing_name' => $encode['from_billing_name'],
            'from_billing_email' => $encode['from_billing_email'],
            'from_billing_address' => $encode['from_billing_address'],
            'from_billing_city' => $encode['from_billing_city'],
            'from_billing_state' => $encode['from_billing_state'],
            'from_billing_country' => $encode['from_billing_country'],
            'from_billing_zipcode' => $encode['from_billing_zipcode'],
            'gobiz_transaction_id' => $flwRef,
            'to_billing_name' => $encode['to_billing_name'],
            'to_vat_number' => $encode['to_vat_number'],
            'invoice_currency' => $transaction_details->currency,
            'subtotal' => $encode['subtotal'],
            'tax_amount' => $encode['tax_amount'],
            'applied_coupon' => $encode['applied_coupon'],
            'discounted_price' => $encode['discounted_price'],
            'invoice_amount' => $encode['invoice_amount'],
            'invoice_id' => $config[15]->config_value . $invoice_number,
            'invoice_date' => $transaction_details->created_at,
            'description' => $transaction_details->purchase_details,
            'email_heading' => $config[27]->config_value,
            'email_footer' => $config[28]->config_value,
        ];

        // Send Invoice
        try {

            Mail::to($encode['to_billing_email'])
                ->send(new \App\Mail\SendEmailInvoice($details));
        } catch (\Exception $e) {
        }

        return [
            'success' => trans('Payment successful!'),
        ];
    }
}
