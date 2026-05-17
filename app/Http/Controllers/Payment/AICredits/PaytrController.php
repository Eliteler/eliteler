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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class PaytrController extends Controller
{
    private $merchant_id;
    private $merchant_key;
    private $merchant_salt;
    private $mode;

    public function __construct()
    {
        $paytr_configuration = DB::table('config')->get();

        $this->merchant_id   = $paytr_configuration[68]->config_value;
        $this->merchant_key  = $paytr_configuration[69]->config_value;
        $this->merchant_salt = $paytr_configuration[70]->config_value;
        $this->mode          = $paytr_configuration[71]->config_value;
    }

    /**
     * Generate Payment Link
     */
    public function generatePaymentLink(Request $request, $planId, $couponId)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $config    = DB::table('config')->get();
        $userData  = User::where('id', Auth::user()->id)->first();
        $settings  = Setting::where('status', 1)->first();

        $plan_details = AiCreditsPlan::where('ai_credits_plan_id', $planId)
            ->where('status', 'active')
            ->first();

        if (!$plan_details) {
            return redirect()
                ->route('user.ai.credits.plans')
                ->with('failed', trans('Plan not found'));
        }

        $aiCreditsTransactionId = "TX" . preg_replace('/\D/', '', Str::uuid());
        $aiCreditsOrderId       = "OD" . preg_replace('/\D/', '', Str::uuid());

        $couponDetails     = Coupon::where('used_for', 'ai_credits')
            ->where('coupon_id', $couponId)
            ->first();

        $appliedTaxInTotal = 0;
        $discountPrice     = 0;
        $appliedCoupon     = null;

        if ($couponDetails != null) {
            $appliedTaxInTotal = (float)$plan_details->plan_price * (float)$config[25]->config_value / 100;

            $discountPrice = $couponDetails->coupon_type == 'fixed'
                ? $couponDetails->coupon_amount
                : $plan_details->plan_price * $couponDetails->coupon_amount / 100;

            $amountToBePaid = (float)number_format(
                ($plan_details->plan_price + $appliedTaxInTotal) - $discountPrice,
                2,
                '.',
                ''
            );
            $appliedCoupon = $couponDetails->coupon_code;
        } else {
            $appliedTaxInTotal = (float)$plan_details->plan_price * (float)$config[25]->config_value / 100;
            $amountToBePaid    = (float)($plan_details->plan_price + $appliedTaxInTotal);
        }

        $amountToBePaidPaise = $amountToBePaid * 100;
        $transactionId       = uniqid();

        $invoice_details = [
            'from_billing_name'    => $config[16]->config_value,
            'from_billing_address' => $config[19]->config_value,
            'from_billing_city'    => $config[20]->config_value,
            'from_billing_state'   => $config[21]->config_value,
            'from_billing_zipcode' => $config[22]->config_value,
            'from_billing_country' => $config[23]->config_value,
            'from_vat_number'      => $config[26]->config_value,
            'from_billing_phone'   => $config[18]->config_value,
            'from_billing_email'   => $config[17]->config_value,
            'to_billing_name'      => $userData->billing_name,
            'to_billing_address'   => $userData->billing_address,
            'to_billing_city'      => $userData->billing_city,
            'to_billing_state'     => $userData->billing_state,
            'to_billing_zipcode'   => $userData->billing_zipcode,
            'to_billing_country'   => $userData->billing_country,
            'to_billing_phone'     => $userData->billing_phone,
            'to_billing_email'     => $userData->billing_email,
            'to_vat_number'        => $userData->vat_number,
            'subtotal'             => $plan_details->plan_price,
            'tax_name'             => $config[24]->config_value,
            'tax_type'             => $config[14]->config_value,
            'tax_value'            => $config[25]->config_value,
            'tax_amount'           => $appliedTaxInTotal,
            'applied_coupon'       => $appliedCoupon,
            'discounted_price'     => $discountPrice,
            'invoice_amount'       => $amountToBePaid,
        ];

        $transaction = new AiCreditsTransaction();
        $transaction->ai_credits_transaction_id = $aiCreditsTransactionId;
        $transaction->ai_credits_order_id       = $aiCreditsOrderId;
        $transaction->payment_transaction_id    = $transactionId;
        $transaction->user_id                   = Auth::user()->id;
        $transaction->ai_credits_plan_id        = $plan_details->ai_credits_plan_id;
        $transaction->purchase_details          = $plan_details->plan_name . " AI Credits Plan";
        $transaction->payment_method            = "PayTR";
        $transaction->currency                  = $config[1]->config_value;
        $transaction->amount                    = $amountToBePaid;
        $transaction->invoice_details           = json_encode($invoice_details);
        $transaction->payment_status            = "pending";
        $transaction->save();

        if ($couponId != " ") {
            $appliedCouponRecord                  = new AppliedCoupon();
            $appliedCouponRecord->applied_coupon_id = uniqid();
            $appliedCouponRecord->transaction_id    = $transactionId;
            $appliedCouponRecord->user_id           = Auth::user()->id;
            $appliedCouponRecord->coupon_id         = $couponId;
            $appliedCouponRecord->status            = 0;
            $appliedCouponRecord->save();
        }

        $paymentData = [
            'merchant_id'           => $this->merchant_id,
            'user_ip'               => $request->ip(),
            'merchant_oid'          => $transactionId,
            'email'                 => $userData->billing_email,
            'payment_amount'        => $amountToBePaidPaise,
            'user_basket'           => base64_encode(json_encode([
                ['AI Credits Purchase', $plan_details->plan_price, 1],
            ])),
            'debug_on'              => 1,
            'no_installment'        => 0,
            'max_installment'       => 0,
            'currency'              => 'TL',
            'test_mode'             => $this->mode,
            'user_name'             => Auth::user()->billing_name,
            'user_address'          => Auth::user()->billing_address,
            'user_phone'            => Auth::user()->billing_phone,
            'merchant_ok_url'       => route('ai.credits.paytr.payment.status'),
            'merchant_fail_url'     => route('ai.credits.paytr.payment.failure'),
            'merchant_callback_url' => route('ai.credits.paytr.payment.webhook'),
        ];

        $hash_str = $this->merchant_id .
            $paymentData['user_ip'] .
            $paymentData['merchant_oid'] .
            $paymentData['email'] .
            $paymentData['payment_amount'] .
            $paymentData['user_basket'] .
            $paymentData['no_installment'] .
            $paymentData['max_installment'] .
            $paymentData['currency'] .
            $paymentData['test_mode'];

        $paymentData['paytr_token'] = base64_encode(
            hash_hmac('sha256', $hash_str . $this->merchant_salt, $this->merchant_key, true)
        );

        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "https://www.paytr.com/odeme/api/get-token");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($paymentData));
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

            $response = curl_exec($ch);

            if (curl_errno($ch)) {
                return back()->withErrors(['failed' => curl_error($ch)]);
            }

            curl_close($ch);

            $result = json_decode($response, true);

            if ($result['status'] === 'success') {
                return view('user.pages.ai-credits.pay-with-paytr', [
                    'settings'     => $settings,
                    'iframe_token' => $result['token'],
                ]);
            }
        } catch (\Exception $e) {
            return back()->with(['failed' => trans('Something went wrong. Please try again later.')]);
        }

        return back()->with(['failed' => $result['reason'] ?? trans('Payment initiation failed')]);
    }

    /**
     * Payment Success Redirect (merchant_ok_url)
     * 
     * NOTE: PayTR only redirects the user here — it does NOT send
     * hash/merchant_oid in GET params. The actual payment confirmation
     * is handled by the webhook (merchant_callback_url).
     * This page should simply show status based on DB state.
     */
    public function paytrPaymentStatus(Request $request)
    {
        if (!Auth::check()) {
            return redirect()
                ->route('user.ai.credits.plans')
                ->with('failed', trans('Payment failed'));
        }

        $user = Auth::user();

        // PayTR may pass merchant_oid as a GET param on the ok_url
        // If not, fall back to the latest transaction for this user
        $transactionId = $request->input('merchant_oid');

        if ($transactionId) {
            $paymentDetails = AiCreditsTransaction::where('user_id', $user->id)
                ->where('payment_transaction_id', $transactionId)
                ->first();
        } else {
            // Fallback: get the most recent transaction for this user
            $paymentDetails = AiCreditsTransaction::where('user_id', $user->id)
                ->latest()
                ->first();
        }

        if (!$paymentDetails) {
            return redirect()
                ->route('user.ai.credits.plans')
                ->with('failed', trans('Payment failed'));
        }

        // The webhook updates the DB to 'paid' — trust the DB status
        if ($paymentDetails->payment_status === 'paid') {
            return redirect()
                ->route('user.ai.credits.plans')
                ->with('success', trans('Payment successful!'));
        }

        // Webhook may not have fired yet — show a pending message
        return redirect()
            ->route('user.ai.credits.plans')
            ->with('success', trans('Your payment is being processed. Your credits will appear shortly.'));
    }

    /**
     * Payment Failure Redirect (merchant_fail_url)
     */
    public function paytrPaymentFailure(Request $request)
    {
        $transactionId = $request->input('merchant_oid');

        if ($transactionId) {
            AiCreditsTransaction::where('payment_transaction_id', $transactionId)
                ->update(['payment_status' => 'failed']);
        }

        return redirect()
            ->route('user.ai.credits.plans')
            ->with('failed', trans('Payment failed'));
    }

    /**
     * PayTR Webhook (merchant_callback_url)
     * Server-to-server POST — no Auth session available here
     */
    public function paytrPaymentWebhook(Request $request)
    {
        $request->validate([
            'merchant_oid'  => 'required|string',
            'status'        => 'required|string',
            'total_amount'  => 'required',
            'hash'          => 'required|string',
        ]);

        $transactionId = $request->input('merchant_oid');
        $status        = $request->input('status');
        $totalAmount   = $request->input('total_amount');
        $receivedHash  = $request->input('hash');

        // Verify hash
        $expectedHash = base64_encode(hash_hmac(
            'sha256',
            $transactionId . $this->merchant_salt . $status . $totalAmount,
            $this->merchant_key,
            true
        ));

        if (!hash_equals($expectedHash, $receivedHash)) {
            return response('Invalid hash', 400);
        }

        $transaction_details = AiCreditsTransaction::where(
            'payment_transaction_id',
            $transactionId
        )->first();

        if (!$transaction_details) {
            return response('Transaction not found', 404);
        }

        // Failed payment
        if ($status !== 'success') {
            AiCreditsTransaction::where('payment_transaction_id', $transactionId)
                ->update(['payment_status' => 'failed']);

            return response('OK', 200);
        }

        // Prevent double-processing
        if ($transaction_details->payment_status === 'paid') {
            return response('OK', 200);
        }

        $planDetails = AiCreditsPlan::where(
            'ai_credits_plan_id',
            $transaction_details->ai_credits_plan_id
        )->first();

        $config = DB::table('config')->get();

        $invoice_count  = AiCreditsTransaction::where('invoice_prefix', $config[15]->config_value)->count();
        $invoice_number = $invoice_count + 1;

        AiCreditsTransaction::where('payment_transaction_id', $transactionId)
            ->update([
                'invoice_prefix' => $config[15]->config_value,
                'invoice_number' => $invoice_number,
                'payment_status' => 'paid',
            ]);

        // ✅ FIX: Use transaction's user_id — Auth::user() is NULL in webhooks
        $aiCredit = AiCredit::where('user_id', $transaction_details->user_id)->first();

        if ($aiCredit) {
            $aiCredit->credits += $planDetails->no_of_ai_credits;
            $aiCredit->save();
        } else {
            $aiCredit          = new AiCredit();
            $aiCredit->user_id = $transaction_details->user_id;
            $aiCredit->credits = $planDetails->no_of_ai_credits;
            $aiCredit->save();
        }

        AppliedCoupon::where('transaction_id', $transactionId)
            ->update(['status' => 1]);

        $encode  = json_decode($transaction_details->invoice_details, true);
        $details = [
            'from_billing_name'    => $encode['from_billing_name'],
            'from_billing_email'   => $encode['from_billing_email'],
            'from_billing_address' => $encode['from_billing_address'],
            'from_billing_city'    => $encode['from_billing_city'],
            'from_billing_state'   => $encode['from_billing_state'],
            'from_billing_country' => $encode['from_billing_country'],
            'from_billing_zipcode' => $encode['from_billing_zipcode'],
            'transaction_id'       => $transactionId,
            'to_billing_name'      => $encode['to_billing_name'],
            'invoice_currency'     => $transaction_details->currency,
            'subtotal'             => $encode['subtotal'],
            'tax_amount'           => $encode['tax_amount'],
            'applied_coupon'       => $encode['applied_coupon'],
            'discounted_price'     => $encode['discounted_price'],
            'invoice_amount'       => $encode['invoice_amount'],
            'invoice_id'           => $config[15]->config_value . $invoice_number,
            'invoice_date'         => $transaction_details->created_at,
            'description'          => $transaction_details->purchase_details,
            'email_heading'        => $config[27]->config_value,
            'email_footer'         => $config[28]->config_value,
        ];

        try {
            Mail::to($encode['to_billing_email'])
                ->send(new \App\Mail\SendEmailInvoice($details));
        } catch (\Exception $e) {
            Log::error('Invoice email sending failed: ' . $e->getMessage());
        }

        return response('OK', 200);
    }
}
