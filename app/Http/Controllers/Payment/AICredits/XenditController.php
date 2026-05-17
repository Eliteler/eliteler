<?php

namespace App\Http\Controllers\Payment\AICredits;

use App\AiCredit;
use App\AiCreditsPlan;
use App\AiCreditsTransaction;
use App\AppliedCoupon;
use App\Coupon;
use App\Http\Controllers\Controller;
use App\Services\XenditService;
use App\Setting;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class XenditController extends Controller
{
    protected $xenditService;

    public function __construct(XenditService $xenditService)
    {
        $this->xenditService = $xenditService;
    }

    /**
     * Generate Payment Link
     */
    public function generatePaymentLink(Request $request, $planId, $couponId)
    {
        if (!Auth::user()) {
            return redirect()->route('login');
        }

        $config = DB::table('config')->get();

        $userData = User::where('id', Auth::user()->id)->first();

        $settings = Setting::where('status', 1)->first();

        $plan_details = AiCreditsPlan::where('ai_credits_plan_id', $planId)
            ->where('status', 'active')
            ->first();

        if (!$plan_details) {
            return redirect()
                ->route('user.ai.credits.plans')
                ->with('failed', trans('Invalid plan!'));
        }

        if ($config[72]->config_value == null) {
            return redirect()
                ->route('user.ai.credits.plans')
                ->with('failed', trans('Something went wrong!'));
        }

        $aiCreditsTransactionId = "TX" . preg_replace('/\D/', '', Str::uuid());

        $aiCreditsOrderId = "OD" . preg_replace('/\D/', '', Str::uuid());

        $couponDetails = Coupon::where('used_for', 'ai_credits')
            ->where('coupon_id', $couponId)
            ->first();

        $appliedTaxInTotal = 0;

        $discountPrice = 0;

        $appliedCoupon = null;

        if ($couponDetails != null) {

            if ($couponDetails->coupon_type == 'fixed') {

                $appliedTaxInTotal = ((float) $plan_details->plan_price * (float) $config[25]->config_value / 100);

                $discountPrice = $couponDetails->coupon_amount;

                $amountToBePaid = ($plan_details->plan_price + $appliedTaxInTotal) - $discountPrice;

                $amountToBePaid = (float) number_format($amountToBePaid, 2, '.', '');

                $appliedCoupon = $couponDetails->coupon_code;
            } else {

                $appliedTaxInTotal = ((float) $plan_details->plan_price * (float) $config[25]->config_value / 100);

                $discountPrice = $plan_details->plan_price * $couponDetails->coupon_amount / 100;

                $amountToBePaid = ($plan_details->plan_price + $appliedTaxInTotal) - $discountPrice;

                $amountToBePaid = (float) number_format($amountToBePaid, 2, '.', '');

                $appliedCoupon = $couponDetails->coupon_code;
            }
        } else {

            $appliedTaxInTotal = ((float) $plan_details->plan_price * (float) $config[25]->config_value / 100);

            $amountToBePaid = ($plan_details->plan_price + $appliedTaxInTotal);
        }

        $successRedirectUrl = route('ai.credits.xendit.payment.status', [
            'transactionId' => $aiCreditsTransactionId
        ]);

        $response = $this->xenditService->createInvoice(
            $aiCreditsTransactionId,
            $amountToBePaid,
            $userData->email,
            'AI Credits Purchase',
            $successRedirectUrl
        );

        if ($response['status'] != 'PENDING') {
            return redirect()
                ->route('user.ai.credits.plans')
                ->with('failed', trans('Unable to create payment'));
        }

        $transaction_id = $response['id'];

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

        $transaction = new AiCreditsTransaction();

        $transaction->ai_credits_transaction_id = $aiCreditsTransactionId;
        $transaction->ai_credits_order_id = $aiCreditsOrderId;
        $transaction->payment_transaction_id = $transaction_id;
        $transaction->user_id = Auth::user()->id;
        $transaction->ai_credits_plan_id = $plan_details->ai_credits_plan_id;
        $transaction->purchase_details = $plan_details->plan_name . " AI Credits Plan";
        $transaction->payment_method = "Xendit";
        $transaction->currency = $config[1]->config_value;
        $transaction->amount = $amountToBePaid;
        $transaction->invoice_details = json_encode($invoice_details);
        $transaction->payment_status = "pending";

        $transaction->save();

        if ($couponId != " ") {

            $appliedCouponRecord = new AppliedCoupon();

            $appliedCouponRecord->applied_coupon_id = uniqid();
            $appliedCouponRecord->transaction_id = $transaction_id;
            $appliedCouponRecord->user_id = Auth::user()->id;
            $appliedCouponRecord->coupon_id = $couponId;
            $appliedCouponRecord->status = 0;

            $appliedCouponRecord->save();
        }

        return redirect($response['invoice_url']);
    }

    /**
     * Xendit Payment Status
     */
    public function xenditPaymentStatus(Request $request, $transactionId)
    {
        // Transaction Details
        $transaction_details = AiCreditsTransaction::where(
            'ai_credits_transaction_id',
            $transactionId
        )->first();

        // Validation
        if (!$transaction_details) {

            return redirect()
                ->route('user.ai.credits.plans')
                ->with(
                    'failed',
                    trans('Transaction not found or already processed.')
                );
        }

        // Payment Status
        $paymentStatus = $this->xenditService->getInvoiceById(
            $transaction_details->payment_transaction_id
        );

        // Validation
        if (!isset($paymentStatus['status'])) {

            return redirect()
                ->route('user.ai.credits.plans')
                ->with(
                    'failed',
                    trans('Unable to retrieve payment status.')
                );
        }

        // Check Status
        switch ($paymentStatus['status']) {

            case 'PAID':

            case 'SETTLED':

                // Plan Details
                $planDetails = AiCreditsPlan::where(
                    'ai_credits_plan_id',
                    $transaction_details->ai_credits_plan_id
                )->first();

                // Config
                $config = DB::table('config')->get();

                // Invoice Count
                $invoice_count = AiCreditsTransaction::where(
                    'invoice_prefix',
                    $config[15]->config_value
                )->count();

                $invoice_number = $invoice_count + 1;

                // Update Transaction
                AiCreditsTransaction::where(
                    'ai_credits_transaction_id',
                    $transactionId
                )->update([
                    'invoice_prefix' => $config[15]->config_value,
                    'invoice_number' => $invoice_number,
                    'payment_status' => 'paid',
                ]);

                // Send invoice
                $encode = json_decode($transaction_details->invoice_details, true);

                $details = [
                    'from_billing_name' => $encode['from_billing_name'],
                    'from_billing_email' => $encode['from_billing_email'],
                    'from_billing_address' => $encode['from_billing_address'],
                    'from_billing_city' => $encode['from_billing_city'],
                    'from_billing_state' => $encode['from_billing_state'],
                    'from_billing_country' => $encode['from_billing_country'],
                    'from_billing_zipcode' => $encode['from_billing_zipcode'],
                    'gobiz_transaction_id' => $transaction_details->ai_credits_transaction_id,
                    'to_billing_name' => $encode['to_billing_name'],
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

                try {

                    Mail::to($encode['to_billing_email'])
                        ->send(new \App\Mail\SendEmailInvoice($details));
                } catch (\Exception $e) {
                }

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
                    $transaction_details->payment_transaction_id
                )->update([
                    'status' => 1
                ]);

                return redirect()
                    ->route('user.ai.credits.plans')
                    ->with(
                        'success',
                        trans('Your payment has been successful.')
                    );

            case 'FAILED':

                return redirect()
                    ->route('user.ai.credits.plans')
                    ->with(
                        'failed',
                        trans('Your payment has failed.')
                    );

            case 'PENDING':

                return redirect()
                    ->route('user.ai.credits.plans')
                    ->with(
                        'pending',
                        trans('Your payment is pending.')
                    );

            case 'CANCELED':

                return redirect()
                    ->route('user.ai.credits.plans')
                    ->with(
                        'failed',
                        trans('Your payment has failed.')
                    );

            case 'EXPIRED':

                return redirect()
                    ->route('user.ai.credits.plans')
                    ->with(
                        'failed',
                        trans('Your payment has failed.')
                    );

            default:

                return redirect()
                    ->route('user.ai.credits.plans')
                    ->with(
                        'failed',
                        trans('Unable to determine payment status.')
                    );
        }
    }

    /**
     * Xendit Payment Webhook
     */
    public function xenditPaymentWebhook(Request $request)
    {
        // Transaction Details
        $transaction_details = AiCreditsTransaction::where(
            'ai_credits_transaction_id',
            $request->transactionId
        )->first();

        // Validation
        if (!$transaction_details) {

            return redirect()
                ->route('user.ai.credits.plans')
                ->with(
                    'failed',
                    trans('Transaction not found or already processed.')
                );
        }

        // Payment Status
        $paymentStatus = $this->xenditService->getInvoiceById(
            $transaction_details->payment_transaction_id
        );

        // Validation
        if (!isset($paymentStatus['status'])) {

            return redirect()
                ->route('user.ai.credits.plans')
                ->with(
                    'failed',
                    trans('Unable to retrieve payment status.')
                );
        }

        // Check Status
        switch ($paymentStatus['status']) {

            case 'PAID':

            case 'SETTLED':

                // Plan Details
                $planDetails = AiCreditsPlan::where(
                    'ai_credits_plan_id',
                    $transaction_details->ai_credits_plan_id
                )->first();

                // Config
                $config = DB::table('config')->get();

                // Invoice Count
                $invoice_count = AiCreditsTransaction::where(
                    'invoice_prefix',
                    $config[15]->config_value
                )->count();

                $invoice_number = $invoice_count + 1;

                // Update Transaction
                AiCreditsTransaction::where(
                    'ai_credits_transaction_id',
                    $request->transactionId
                )->update([
                    'invoice_prefix' => $config[15]->config_value,
                    'invoice_number' => $invoice_number,
                    'payment_status' => 'paid',
                ]);

                // Send invoice
                $encode = json_decode($transaction_details->invoice_details, true);

                $details = [
                    'from_billing_name' => $encode['from_billing_name'],
                    'from_billing_email' => $encode['from_billing_email'],
                    'from_billing_address' => $encode['from_billing_address'],
                    'from_billing_city' => $encode['from_billing_city'],
                    'from_billing_state' => $encode['from_billing_state'],
                    'from_billing_country' => $encode['from_billing_country'],
                    'from_billing_zipcode' => $encode['from_billing_zipcode'],
                    'gobiz_transaction_id' => $transaction_details->ai_credits_transaction_id,
                    'to_billing_name' => $encode['to_billing_name'],
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

                try {

                    Mail::to($encode['to_billing_email'])
                        ->send(new \App\Mail\SendEmailInvoice($details));
                } catch (\Exception $e) {
                }

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
                    $transaction_details->payment_transaction_id
                )->update([
                    'status' => 1
                ]);

                return redirect()
                    ->route('user.ai.credits.plans')
                    ->with(
                        'success',
                        trans('Your payment has been successful.')
                    );

            case 'FAILED':

                AiCreditsTransaction::where(
                    'payment_transaction_id',
                    $transaction_details->payment_transaction_id
                )->update([
                    'payment_status' => 'failed',
                ]);

                return redirect()
                    ->route('user.ai.credits.plans')
                    ->with(
                        'failed',
                        trans('Your payment has failed.')
                    );

            case 'PENDING':

                AiCreditsTransaction::where(
                    'payment_transaction_id',
                    $transaction_details->payment_transaction_id
                )->update([
                    'payment_status' => 'pending',
                ]);

                return redirect()
                    ->route('user.ai.credits.plans')
                    ->with(
                        'pending',
                        trans('Your payment is pending.')
                    );

            case 'CANCELED':

            case 'EXPIRED':

            default:

                AiCreditsTransaction::where(
                    'payment_transaction_id',
                    $transaction_details->payment_transaction_id
                )->update([
                    'payment_status' => 'failed',
                ]);

                return redirect()
                    ->route('user.ai.credits.plans')
                    ->with(
                        'failed',
                        trans('Your payment has failed.')
                    );
        }
    }
}
