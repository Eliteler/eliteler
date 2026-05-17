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
use App\Classes\OrderAICredits;
use App\Coupon;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ZeroAICreditsOrder
{
    public function zero($couponId, $planDetails, $planId, $payment_mode)
    {
        // Queries
        $config = DB::table('config')->get();

        // Check applied coupon
        $couponDetails = Coupon::where('used_for', 'ai_credits')->where('coupon_id', $couponId)->first();

        // User details
        $userDetails = User::where('id', Auth::user()->id)->first();

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
                $amountToBePaid = (float)number_format($amountToBePaid, 2, '.', '');

                // Coupon is applied
                $appliedCoupon = $couponDetails->coupon_code;
            } else {
                // Applied tax in total
                $appliedTaxInTotal = ((float)($planDetails->plan_price) * (float)($config[25]->config_value) / 100);

                // Get discount in plan price
                $discountPrice = $planDetails->plan_price * $couponDetails->coupon_amount / 100;

                // Total
                $amountToBePaid = ($planDetails->plan_price + $appliedTaxInTotal) - $discountPrice;
                $amountToBePaid = (float)number_format($amountToBePaid, 2, '.', '');

                // Coupon is applied
                $appliedCoupon = $couponDetails->coupon_code;
            }
        } else {
            // Applied tax in total
            $appliedTaxInTotal = ((float)($planDetails->plan_price) * (float)($config[25]->config_value) / 100);

            // Total
            $amountToBePaid = ($planDetails->plan_price + $appliedTaxInTotal);
        }

        // Get invoice number
        $latestTransaction = AiCreditsTransaction::where('invoice_prefix', $config[15]->config_value)->count();
        $invoiceNumber = $latestTransaction + 1;

        // Store transaction details in ai_credits_transactions
        $aiCreditsTransaction = new AiCreditsTransaction();
        $aiCreditsTransaction->ai_credits_transaction_id = $aiCreditsTransactionId;
        $aiCreditsTransaction->ai_credits_order_id = $aiCreditsOrderId;
        $aiCreditsTransaction->payment_transaction_id = $aiCreditsTransactionId;
        $aiCreditsTransaction->user_id = Auth::user()->id;
        $aiCreditsTransaction->ai_credits_plan_id = $planId;
        $aiCreditsTransaction->purchase_details = $planDetails->plan_name . " AI Credits Plan";
        $aiCreditsTransaction->payment_method = "FREE";
        $aiCreditsTransaction->currency = $config[1]->config_value;
        $aiCreditsTransaction->amount = $amountToBePaid;
        $aiCreditsTransaction->invoice_number = $invoiceNumber;
        $aiCreditsTransaction->invoice_prefix = $config[15]->config_value;
        $aiCreditsTransaction->invoice_details = json_encode($this->prepareInvoiceDetails($config, $userDetails, $amountToBePaid, $planDetails, $appliedCoupon, $discountPrice));
        $aiCreditsTransaction->payment_status = "paid";
        $aiCreditsTransaction->save();

        // Coupon is not applied
        if ($couponId != " ") { 
            // Save applied coupon
            $appliedCoupon = new AppliedCoupon;
            $appliedCoupon->applied_coupon_id = uniqid();
            $appliedCoupon->transaction_id = $aiCreditsTransactionId;
            $appliedCoupon->user_id = Auth::user()->id;
            $appliedCoupon->coupon_id = $couponId;
            $appliedCoupon->status = 1;
            $appliedCoupon->save();
        }

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
