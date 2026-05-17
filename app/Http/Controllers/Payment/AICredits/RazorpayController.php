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
use Razorpay\Api\Api;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class RazorpayController extends Controller
{
    // RazorPay
    public function prepareRazorpay(Request $request, $planId, $couponId)
    {
        if (Auth::check()) {

            $settings = Setting::where('status', 1)->first();

            // Get AI Credits Plan
            $planDetails = AiCreditsPlan::where('ai_credits_plan_id', $planId)
                ->where('status', 'active')
                ->first();

            $userData = Auth::user();
            $config = DB::table('config')->get();

            if (!$planDetails) {
                return view('errors.404');
            }

            // AI Credits Order ID
            $aiCreditsOrderId = "OD" . preg_replace('/\D/', '', Str::uuid());
            $aiCreditsTransactionId = "TX" . preg_replace('/\D/', '', Str::uuid());

            $RAZOR_KEY = $config[6]->config_value;
            $RAZOR_SECRET = $config[7]->config_value;

            $api = new Api($RAZOR_KEY, $RAZOR_SECRET);

            // Check applied coupon
            $couponDetails = Coupon::where('used_for', 'ai_credits')
                ->where('coupon_id', $couponId)
                ->first();

            // Applied tax in total
            $appliedTaxInTotal = 0;

            // Discount price
            $discountPrice = 0;

            // Applied coupon
            $appliedCoupon = null;

            // Check coupon type
            if ($couponDetails != null) {

                if ($couponDetails->coupon_type == 'fixed') {

                    // Applied tax
                    $appliedTaxInTotal = ((float)($planDetails->plan_price) * (float)($config[25]->config_value) / 100);

                    // Discount
                    $discountPrice = $couponDetails->coupon_amount;

                    // Total
                    $amountToBePaid = ($planDetails->plan_price + $appliedTaxInTotal) - $discountPrice;
                    $amountToBePaid = (float) number_format($amountToBePaid, 2, '.', '');

                    // Coupon applied
                    $appliedCoupon = $couponDetails->coupon_code;
                } else {

                    // Applied tax
                    $appliedTaxInTotal = ((float)($planDetails->plan_price) * (float)($config[25]->config_value) / 100);

                    // Discount
                    $discountPrice = $planDetails->plan_price * $couponDetails->coupon_amount / 100;

                    // Total
                    $amountToBePaid = ($planDetails->plan_price + $appliedTaxInTotal) - $discountPrice;
                    $amountToBePaid = (float) number_format($amountToBePaid, 2, '.', '');

                    // Coupon applied
                    $appliedCoupon = $couponDetails->coupon_code;
                }
            } else {

                // Applied tax
                $appliedTaxInTotal = ((float)($planDetails->plan_price) * (float)($config[25]->config_value) / 100);

                // Total
                $amountToBePaid = ($planDetails->plan_price + $appliedTaxInTotal);
            }

            $amountToBePaidPaise = $amountToBePaid * 100;

            try {
                $order = $api->order->create([
                    'receipt' => $aiCreditsOrderId,
                    'amount' => (int) $amountToBePaidPaise,
                    'currency' => $config[1]->config_value
                ]);
            } catch (\Exception $e) {
                return redirect()->route('user.ai.credits.plans')
                    ->with('failed', trans('Payment method not supported!'));
            }

            // Invoice Details
            $invoiceDetails = [];

            $invoiceDetails['from_billing_name'] = $config[16]->config_value;
            $invoiceDetails['from_billing_address'] = $config[19]->config_value;
            $invoiceDetails['from_billing_city'] = $config[20]->config_value;
            $invoiceDetails['from_billing_state'] = $config[21]->config_value;
            $invoiceDetails['from_billing_zipcode'] = $config[22]->config_value;
            $invoiceDetails['from_billing_country'] = $config[23]->config_value;
            $invoiceDetails['from_vat_number'] = $config[26]->config_value;
            $invoiceDetails['from_billing_phone'] = $config[18]->config_value;
            $invoiceDetails['from_billing_email'] = $config[17]->config_value;
            $invoiceDetails['to_billing_name'] = $userData->billing_name;
            $invoiceDetails['to_billing_address'] = $userData->billing_address;
            $invoiceDetails['to_billing_city'] = $userData->billing_city;
            $invoiceDetails['to_billing_state'] = $userData->billing_state;
            $invoiceDetails['to_billing_zipcode'] = $userData->billing_zipcode;
            $invoiceDetails['to_billing_country'] = $userData->billing_country;
            $invoiceDetails['to_billing_phone'] = $userData->billing_phone;
            $invoiceDetails['to_billing_email'] = $userData->billing_email;
            $invoiceDetails['to_vat_number'] = $userData->vat_number;
            $invoiceDetails['subtotal'] = $planDetails->plan_price;
            $invoiceDetails['tax_name'] = $config[24]->config_value;
            $invoiceDetails['tax_type'] = $config[14]->config_value;
            $invoiceDetails['tax_value'] = $config[25]->config_value;
            $invoiceDetails['tax_amount'] = $appliedTaxInTotal;
            $invoiceDetails['applied_coupon'] = $appliedCoupon;
            $invoiceDetails['discounted_price'] = $discountPrice;
            $invoiceDetails['invoice_amount'] = $amountToBePaid;

            // Save transaction
            $transaction = new AiCreditsTransaction();
            $transaction->ai_credits_transaction_id = $aiCreditsTransactionId;
            $transaction->ai_credits_order_id = $aiCreditsOrderId;
            $transaction->payment_transaction_id = $order->id;
            $transaction->user_id = Auth::user()->id;
            $transaction->ai_credits_plan_id = $planDetails->ai_credits_plan_id;
            $transaction->purchase_details = $planDetails->plan_name . " AI Credits Plan";
            $transaction->payment_method = "Razorpay";
            $transaction->currency = $config[1]->config_value;
            $transaction->amount = $amountToBePaid;
            $transaction->invoice_details = json_encode($invoiceDetails);
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

            return view('user.pages.ai-credits.pay-with-razorpay', compact(
                'settings',
                'planDetails',
                'order',
                'config',
                'aiCreditsTransactionId',
                'amountToBePaid'
            ));
        }

        return redirect()->route('login');
    }

    // Razorpay Payment Status
    public function razorpayPaymentStatus(Request $request, $orderId, $paymentId)
    {
        if ($orderId == "" || $paymentId == "") {

            AiCreditsTransaction::where('payment_transaction_id', $orderId)->update([
                'payment_transaction_id' => $paymentId,
                'payment_status' => 'failed',
            ]);

            return redirect()->route('user.ai.credits.plans')
                ->with('failed', trans('Something went wrong!'));
        }

        $config = DB::table('config')->get();

        $RAZOR_KEY = $config[6]->config_value;
        $RAZOR_SECRET = $config[7]->config_value;

        $api = new Api($RAZOR_KEY, $RAZOR_SECRET);

        try {
            $payment = $api->payment->fetch($paymentId);
        } catch (\Exception $e) {
            return redirect()->route('user.ai.credits.plans')
                ->with('failed', trans('Something went wrong!'));
        }

        if ($payment->status == "authorized" || $payment->status == "captured") {

            $paymentOrderId = $payment->order_id;

            $transactionDetails = AiCreditsTransaction::where('payment_transaction_id', $paymentOrderId)
                ->orWhere('payment_transaction_id', $paymentId)
                ->first();

            // Update payment details
            $transactionDetails->payment_transaction_id = $paymentId;
            $transactionDetails->payment_status = 'paid';
            $transactionDetails->save();

            // Get plan details
            $planDetails = AiCreditsPlan::where('ai_credits_plan_id', $transactionDetails->ai_credits_plan_id)
                ->first();

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

            // Update applied coupon
            AppliedCoupon::where('transaction_id', $transactionDetails->ai_credits_transaction_id)
                ->update([
                    'status' => 1
                ]);

            // Generate invoice
            $invoiceCount = AiCreditsTransaction::where('invoice_prefix', $config[15]->config_value)
                ->count();

            $invoiceNumber = $invoiceCount + 1;

            $transactionDetails->invoice_prefix = $config[15]->config_value;
            $transactionDetails->invoice_number = $invoiceNumber;
            $transactionDetails->save();

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
                'invoice_id' => $config[15]->config_value . $invoiceNumber,
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

            return redirect()->route('user.ai.credits.plans')
                ->with('success', trans('Payment successful!'));
        }

        AiCreditsTransaction::where('payment_transaction_id', $orderId)->update([
            'payment_transaction_id' => $paymentId,
            'payment_status' => 'failed',
        ]);

        return redirect()->route('user.ai.credits.plans')
            ->with('failed', trans('Something went wrong!'));
    }
}
