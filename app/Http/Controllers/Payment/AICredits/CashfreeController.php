<?php

namespace App\Http\Controllers\Payment\AICredits;

use App\AiCredit;
use App\AiCreditsPlan;
use App\AiCreditsTransaction;
use App\AppliedCoupon;
use App\Coupon;
use App\Http\Controllers\Controller;
use App\Setting;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class CashfreeController extends Controller
{
    protected $appId;
    protected $secretKey;
    protected $baseUrl;

    public function __construct()
    {
        $config = DB::table('config')->get();

        $this->appId = $config[85]->config_value;
        $this->secretKey = $config[86]->config_value;

        $this->baseUrl = $config[84]->config_value === 'test'
            ? 'https://sandbox.cashfree.com/pg'
            : 'https://api.cashfree.com/pg';
    }

    /**
     * Generate Payment Link
     */
    public function generatePaymentLink(Request $request, $planId, $couponId)
    {
        // Login Check
        if (!Auth::user()) {
            return redirect()->route('login');
        }

        // Config
        $config = DB::table('config')->get();

        // User
        $userData = User::where('id', Auth::user()->id)->first();

        // Settings
        $settings = Setting::where('status', 1)->first();

        // Plan
        $plan_details = AiCreditsPlan::where('ai_credits_plan_id', $planId)
            ->where('status', 'active')
            ->first();

        // Validation
        if (!$plan_details) {

            return redirect()
                ->route('user.ai.credits.plans')
                ->with('failed', trans('Invalid plan!'));
        }

        // IDs
        $aiCreditsTransactionId =
            "TX" . preg_replace('/\D/', '', Str::uuid());

        $aiCreditsOrderId =
            "OD" . preg_replace('/\D/', '', Str::uuid());

        // Coupon
        $couponDetails = Coupon::where('used_for', 'ai_credits')
            ->where('coupon_id', $couponId)
            ->first();

        // Tax
        $appliedTaxInTotal = 0;

        // Discount
        $discountPrice = 0;

        // Applied Coupon
        $appliedCoupon = null;

        // Coupon Logic
        if ($couponDetails != null) {

            if ($couponDetails->coupon_type == 'fixed') {

                // Tax
                $appliedTaxInTotal =
                    ((float) $plan_details->plan_price *
                        (float) $config[25]->config_value / 100);

                // Discount
                $discountPrice =
                    $couponDetails->coupon_amount;

                // Total
                $amountToBePaid =
                    ($plan_details->plan_price +
                        $appliedTaxInTotal) -
                    $discountPrice;

                $amountToBePaid =
                    (float) number_format(
                        $amountToBePaid,
                        2,
                        '.',
                        ''
                    );

                // Coupon
                $appliedCoupon =
                    $couponDetails->coupon_code;
            } else {

                // Tax
                $appliedTaxInTotal =
                    ((float) $plan_details->plan_price *
                        (float) $config[25]->config_value / 100);

                // Discount
                $discountPrice =
                    $plan_details->plan_price *
                    $couponDetails->coupon_amount / 100;

                // Total
                $amountToBePaid =
                    ($plan_details->plan_price +
                        $appliedTaxInTotal) -
                    $discountPrice;

                $amountToBePaid =
                    (float) number_format(
                        $amountToBePaid,
                        2,
                        '.',
                        ''
                    );

                // Coupon
                $appliedCoupon =
                    $couponDetails->coupon_code;
            }
        } else {

            // Tax
            $appliedTaxInTotal =
                ((float) $plan_details->plan_price *
                    (float) $config[25]->config_value / 100);

            // Total
            $amountToBePaid =
                $plan_details->plan_price +
                $appliedTaxInTotal;
        }

        // Cashfree Payload
        $data = [
            'order_id' => $aiCreditsTransactionId,
            'order_amount' => (float) $amountToBePaid,
            'order_currency' => $config[1]->config_value,

            'customer_details' => [
                'customer_id' => Auth::user()->user_id,
                'customer_name' => Auth::user()->name,
                'customer_email' => Auth::user()->email,
                'customer_phone' => Auth::user()->billing_phone,
            ],

            'order_meta' => [
                'return_url' =>
                route('ai.credits.cashfree.payment.status') .
                    '?order_id={order_id}',
            ]
        ];

        try {

            // Create Order
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'x-api-version' => '2022-01-01',
                'x-client-id' => $this->appId,
                'x-client-secret' => $this->secretKey,
            ])->post(
                "{$this->baseUrl}/orders",
                $data
            );

            $responseBody = $response->json();

            // Validation
            if (
                isset($responseBody['order_status']) &&
                $responseBody['order_status'] === 'ACTIVE'
            ) {

                // Invoice Details
                $invoice_details = [
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
                    'subtotal' => $plan_details->plan_price,
                    'tax_name' => $config[24]->config_value,
                    'tax_type' => $config[14]->config_value,
                    'tax_value' => $config[25]->config_value,
                    'tax_amount' => $appliedTaxInTotal,
                    'applied_coupon' => $appliedCoupon,
                    'discounted_price' => $discountPrice,
                    'invoice_amount' => $amountToBePaid,
                ];

                // Save Transaction
                $transaction =
                    new AiCreditsTransaction();

                $transaction->ai_credits_transaction_id =
                    $aiCreditsTransactionId;

                $transaction->ai_credits_order_id =
                    $aiCreditsOrderId;

                $transaction->payment_transaction_id =
                    $aiCreditsTransactionId;

                $transaction->user_id =
                    Auth::user()->id;

                $transaction->ai_credits_plan_id =
                    $plan_details->ai_credits_plan_id;

                $transaction->purchase_details =
                    $plan_details->plan_name .
                    " AI Credits Plan";

                $transaction->payment_method =
                    "Cashfree";

                $transaction->currency =
                    $config[1]->config_value;

                $transaction->amount =
                    $amountToBePaid;

                $transaction->invoice_details =
                    json_encode($invoice_details);

                $transaction->payment_status =
                    "pending";

                $transaction->save();

                // Save Coupon
                if ($couponId != " ") {

                    $appliedCouponRecord =
                        new AppliedCoupon();

                    $appliedCouponRecord->applied_coupon_id =
                        uniqid();

                    $appliedCouponRecord->transaction_id =
                        $aiCreditsTransactionId;

                    $appliedCouponRecord->user_id =
                        Auth::user()->id;

                    $appliedCouponRecord->coupon_id =
                        $couponId;

                    $appliedCouponRecord->status = 0;

                    $appliedCouponRecord->save();
                }

                // Redirect
                if (isset($responseBody['payment_link'])) {

                    return redirect()->to(
                        $responseBody['payment_link']
                    );
                }
            }

            return redirect()
                ->route('user.ai.credits.plans')
                ->with(
                    'failed',
                    trans('Payment initiation failed')
                );
        } catch (\Exception $e) {

            return redirect()
                ->route('user.ai.credits.plans')
                ->with(
                    'failed',
                    trans('Failed to initiate payment.')
                );
        }
    }

    /**
     * Cashfree Payment Status
     */
    public function cashfreePaymentStatus(Request $request)
    {
        // Order ID
        $order_id =
            $request->query('order_id');

        // Transaction
        $transactionDetails =
            AiCreditsTransaction::where(
                'payment_method',
                'Cashfree'
            )
            ->where(
                'payment_transaction_id',
                $order_id
            )
            ->orderBy('id', 'desc')
            ->first();

        // Validation
        if (!$transactionDetails) {

            return redirect()
                ->route('user.ai.credits.plans')
                ->with(
                    'failed',
                    trans('Transaction not found.')
                );
        }

        // Config
        $config =
            DB::table('config')->get();

        $appId =
            $config[85]->config_value;

        $secretKey =
            $config[86]->config_value;

        $mode =
            $config[84]->config_value;

        // URL
        $baseUrl =
            $mode === 'test'
            ? 'https://sandbox.cashfree.com/pg/orders/' . $order_id . '/payments'
            : 'https://api.cashfree.com/pg/orders/' . $order_id . '/payments';

        // Request
        $response =
            Http::withHeaders([
                'x-client-id' => $appId,
                'x-client-secret' => $secretKey,
                'Content-Type' => 'application/json',
                'x-api-version' => '2022-09-01',
            ])->get($baseUrl);

        // Success
        if ($response->successful()) {

            $paymentDetailsArray =
                json_decode(
                    $response->body(),
                    true
                );

            // Validation
            if (
                !is_array($paymentDetailsArray) ||
                empty($paymentDetailsArray[0])
            ) {

                return redirect()
                    ->route('user.ai.credits.plans')
                    ->with(
                        'failed',
                        trans('Invalid payment response.')
                    );
            }

            $paymentDetails =
                (object) $paymentDetailsArray[0];

            // Payment Status
            if (
                isset($paymentDetails->payment_status) &&
                $paymentDetails->payment_status == "SUCCESS"
            ) {

                // Plan Details
                $planDetails =
                    AiCreditsPlan::where(
                        'ai_credits_plan_id',
                        $transactionDetails->ai_credits_plan_id
                    )->first();

                // Invoice Count
                $invoice_count =
                    AiCreditsTransaction::where(
                        'invoice_prefix',
                        $config[15]->config_value
                    )->count();

                $invoice_number =
                    $invoice_count + 1;

                // Update Transaction
                AiCreditsTransaction::where(
                    'payment_transaction_id',
                    $transactionDetails->payment_transaction_id
                )->update([
                    'invoice_prefix' => $config[15]->config_value,
                    'invoice_number' => $invoice_number,
                    'payment_status' => 'paid',
                ]);

                // Update AI credits
                $aiCredit = AiCredit::where('user_id', Auth::user()->user_id)->first();

                if ($aiCredit) {

                    $aiCredit->credits += $planDetails->no_of_ai_credits;
                    $aiCredit->save();
                } else {

                    $aiCredit = new AiCredit();
                    $aiCredit->user_id = $transactionDetails->user_id;
                    $aiCredit->credits = $planDetails->no_of_ai_credits;
                    $aiCredit->save();
                }

                // Send invoice email
                $encode = json_decode($transactionDetails->invoice_details, true);

                $details = [
                    'from_billing_name' => $encode['from_billing_name'],
                    'from_billing_email' => $encode['from_billing_email'],
                    'from_billing_address' => $encode['from_billing_address'],
                    'from_billing_city' => $encode['from_billing_city'],
                    'from_billing_state' => $encode['from_billing_state'],
                    'from_billing_country' => $encode['from_billing_country'],
                    'from_billing_zipcode' => $encode['from_billing_zipcode'],
                    'gobiz_transaction_id' => $transactionDetails->ai_credits_transaction_id,
                    'to_billing_name' => $encode['to_billing_name'],
                    'invoice_currency' => $transactionDetails->currency,
                    'subtotal' => $encode['subtotal'],
                    'tax_amount' => $encode['tax_amount'],
                    'applied_coupon' => $encode['applied_coupon'],
                    'discounted_price' => $encode['discounted_price'],
                    'invoice_amount' => $encode['invoice_amount'],
                    'invoice_id' => $config[15]->config_value . $invoice_number,
                    'invoice_date' => $transactionDetails->created_at,
                    'description' => $transactionDetails->purchase_details,
                    'email_heading' => $config[27]->config_value,
                    'email_footer' => $config[28]->config_value,
                ];

                try {
                    Mail::to($encode['to_billing_email'])
                        ->send(new \App\Mail\SendEmailInvoice($details));
                } catch (\Exception $e) {
                }

                // Update Coupon
                AppliedCoupon::where(
                    'transaction_id',
                    $transactionDetails->payment_transaction_id
                )->update([
                    'status' => 1
                ]);

                return redirect()
                    ->route('user.ai.credits.plans')
                    ->with(
                        'success',
                        trans('AI Credits activated!')
                    );
            } else {

                // Failed
                AiCreditsTransaction::where(
                    'payment_transaction_id',
                    $transactionDetails->payment_transaction_id
                )->update([
                    'payment_status' => 'failed'
                ]);

                return redirect()
                    ->route('user.ai.credits.plans')
                    ->with(
                        'failed',
                        trans('Payment failed.')
                    );
            }
        }

        // Failed
        AiCreditsTransaction::where(
            'payment_transaction_id',
            $transactionDetails->payment_transaction_id
        )->update([
            'payment_status' => 'failed'
        ]);

        return redirect()
            ->route('user.ai.credits.plans')
            ->with(
                'failed',
                trans('Payment failed.')
            );
    }
}
