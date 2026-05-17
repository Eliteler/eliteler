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
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Mollie\Laravel\Facades\Mollie;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;

class MollieController extends Controller
{
    /**
     * Mollie Configuration
     */

    public function __construct()
    {
        $mollie_configuration = DB::table('config')->get();

        Config::set("mollie.key", $mollie_configuration[37]->config_value);
    }

    /**
     * Prepare Mollie Payment
     */

    public function prepareMollie($planId, $couponId)
    {
        if (Auth::check()) {

            // Queries
            $config = DB::table('config')->get();

            $userData = User::where('id', Auth::user()->id)->first();

            // AI Credits Plan
            $plan_details = AiCreditsPlan::where('ai_credits_plan_id', $planId)
                ->where('status', 'active')
                ->first();

            // Check plan
            if ($plan_details == null) {
                return view('errors.404');
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

            // Coupon logic
            if ($couponDetails != null) {

                if ($couponDetails->coupon_type == 'fixed') {

                    // Tax
                    $appliedTaxInTotal = ((float) ($plan_details->plan_price) * (float) ($config[25]->config_value) / 100);

                    // Discount
                    $discountPrice = $couponDetails->coupon_amount;

                    // Total
                    $amountToBePaid = ($plan_details->plan_price + $appliedTaxInTotal) - $discountPrice;

                    $amountToBePaid = (float) number_format($amountToBePaid, 2, '.', '');

                    // Coupon
                    $appliedCoupon = $couponDetails->coupon_code;
                } else {

                    // Tax
                    $appliedTaxInTotal = ((float) ($plan_details->plan_price) * (float) ($config[25]->config_value) / 100);

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
                $appliedTaxInTotal = ((float) ($plan_details->plan_price) * (float) ($config[25]->config_value) / 100);

                // Total
                $amountToBePaid = ($plan_details->plan_price + $appliedTaxInTotal);
            }

            $amountToBePaidPaise = number_format($amountToBePaid, 2);

            // Transaction ID
            $transactionId = uniqid();

            // Mollie Payment
            $payment = Mollie::api()->payments->create([

                'amount' => [
                    "currency" => $config[1]->config_value,
                    "value" => $amountToBePaidPaise
                ],

                'description' => $plan_details->plan_name . " AI Credits Plan",

                'redirectUrl' => route('ai.credits.mollie.payment.status'),

                "metadata" => [
                    "transactionId" => $transactionId,
                    "userId" => Auth::user()->id,
                ],

            ]);

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
            $transaction->payment_transaction_id = $payment->id;
            $transaction->user_id = Auth::user()->id;
            $transaction->ai_credits_plan_id = $plan_details->ai_credits_plan_id;
            $transaction->purchase_details = $plan_details->plan_name . " AI Credits Plan";
            $transaction->payment_method = "Mollie";
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

            try {

                return redirect($payment->getCheckoutUrl(), 303);
            } catch (\Exception $e) {

                return redirect()->route('user.ai.credits.plans')
                    ->with('failed', trans('The mollie token has expired. Please refresh the page and try again.'));
            }
        } else {

            return redirect()->route('login');
        }
    }

    /**
     * Mollie Payment Status
     */

    public function molliePaymentStatus()
    {
        // Latest Transaction
        $transactionDetails = AiCreditsTransaction::where(
            'user_id',
            Auth::user()->id
        )->latest()->first();

        // Payment Details
        $paymentDetails = Mollie::api()->payments->get(
            $transactionDetails->payment_transaction_id
        );

        // Check payment
        if (!$paymentDetails) {
            return view('errors.404');
        }

        // Paid
        if ($paymentDetails->isPaid()) {

            $config = DB::table('config')->get();

            // Update transaction
            $transactionDetails->payment_status = 'paid';
            $transactionDetails->save();

            // Get Plan
            $planDetails = AiCreditsPlan::where(
                'ai_credits_plan_id',
                $transactionDetails->ai_credits_plan_id
            )->first();

            // Update AI Credits
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

            // Update coupon
            AppliedCoupon::where(
                'transaction_id',
                $transactionDetails->ai_credits_transaction_id
            )->update([
                'status' => 1
            ]);

            // Invoice
            $invoiceCount = AiCreditsTransaction::where(
                'invoice_prefix',
                $config[15]->config_value
            )->count();

            $invoiceNumber = $invoiceCount + 1;

            $transactionDetails->invoice_prefix = $config[15]->config_value;
            $transactionDetails->invoice_number = $invoiceNumber;

            $transactionDetails->save();

            // Invoice Data
            $encode = json_decode($transactionDetails['invoice_details'], true);

            $details = [
                'from_billing_name' => $encode['from_billing_name'],
                'from_billing_email' => $encode['from_billing_email'],
                'from_billing_address' => $encode['from_billing_address'],
                'from_billing_city' => $encode['from_billing_city'],
                'from_billing_state' => $encode['from_billing_state'],
                'from_billing_country' => $encode['from_billing_country'],
                'from_billing_zipcode' => $encode['from_billing_zipcode'],
                'transaction_id' => $transactionDetails->payment_transaction_id,
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

                Mail::to($encode['to_billing_email'])
                    ->send(new \App\Mail\SendEmailInvoice($details));
            } catch (\Exception $e) {
            }

            return redirect()->route('user.ai.credits.plans')
                ->with('success', trans('Payment successful!'));
        } elseif ($paymentDetails->isCanceled() || $paymentDetails->isExpired()) {

            // Failed
            $transactionDetails->payment_status = 'failed';

            $transactionDetails->save();

            return redirect()->route('user.ai.credits.plans')
                ->with('failed', trans('Payment cancelled!'));
        }

        return redirect()->route('user.ai.credits.plans')
            ->with('failed', trans('Payment cancelled!'));
    }
}
