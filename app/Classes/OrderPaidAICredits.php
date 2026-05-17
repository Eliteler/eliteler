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

namespace App\Classes;

use App\AiCredit;
use App\AiCreditsPlan;
use App\AiCreditsTransaction;
use App\AppliedCoupon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class OrderPaidAICredits
{
    public function order($paymentId, $payment_mode)
    {
        // Queries
        $config = DB::table('config')->get();

        // Get ai transaction details
        $transactionDetails = AiCreditsTransaction::where('payment_transaction_id', $paymentId)->first();

        // Get the ai credits plan details
        $planDetails = AiCreditsPlan::where('ai_credits_plan_id', $transactionDetails->ai_credits_plan_id)->first();

        // Get invoice number
        $invoice_count = AiCreditsTransaction::where("invoice_prefix", $config[15]->config_value)->count();
        $invoice_number = $invoice_count + 1;

        // Update transaction status in ai_credits_transactions
        $transactionDetails->payment_transaction_id = $paymentId;
        $transactionDetails->invoice_prefix = $config[15]->config_value;
        $transactionDetails->invoice_number = $invoice_number;
        $transactionDetails->payment_status = "paid";
        $transactionDetails->save();

        // Check user_id already exists or not in the ai_credits table
        $aiCreditsOrder = AiCredit::where('user_id', Auth::user()->user_id)->first();

        // If user_id already exists in the ai_credits table
        if ($aiCreditsOrder) {
            // Update credits
            $aiCreditsOrder->credits = $aiCreditsOrder->credits + $planDetails->no_of_ai_credits;
            $aiCreditsOrder->save();
        } else {
            // Insert ai credits in the ai_credits table
            $aiCreditsOrder = new AiCredit();
            $aiCreditsOrder->user_id = Auth::user()->user_id;
            $aiCreditsOrder->credits = $planDetails->no_of_ai_credits;
            $aiCreditsOrder->save();
        }

        // Update status in applied_coupons table if coupon is applied
        AppliedCoupon::where('transaction_id', $transactionDetails->ai_credits_transaction_id)->update([
            'status' => 1
        ]);

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
            'invoice_id' => $config[15]->config_value . $invoice_number,
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
    }
}
