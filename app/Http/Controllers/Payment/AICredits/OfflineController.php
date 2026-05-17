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

use App\AiCreditsPlan;
use App\AiCreditsTransaction;
use App\AppliedCoupon;
use App\Coupon;
use App\Setting;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class OfflineController extends Controller
{
    /**
     * Offline Checkout
     */

    public function offlineCheckout(Request $request, $planId, $couponId)
    {
        $config = DB::table('config')->get();

        // Bank details check
        if ($config[31]->config_value == null) {

            return redirect()
                ->route('user.ai.credits.plans')
                ->with('failed', trans('No Bank Transfer details found!'));
        }

        $settings = Setting::where('status', 1)->first();

        // AI Credits Plan
        $plan_details = AiCreditsPlan::where('ai_credits_plan_id', $planId)
            ->where('status', 'active')
            ->first();

        return view(
            'user.pages.ai-credits.pay-with-offline',
            compact(
                'settings',
                'plan_details',
                'config',
                'couponId'
            )
        );
    }

    /**
     * Mark Offline Payment
     */

    public function markOfflinePayment(Request $request)
    {
        if (Auth::check()) {

            $config = DB::table('config')->get();

            $userData = User::where('id', Auth::user()->id)->first();

            // AI Credits Plan
            $plan_details = AiCreditsPlan::where(
                'ai_credits_plan_id',
                $request->plan_id
            )->where('status', 'active')->first();

            // Transaction ID validation
            if ($request->transaction_id == null) {

                return redirect()
                    ->route('user.ai.credits.plans')
                    ->with('failed', trans('Transaction ID is required.'));
            }

            // Coupon ID
            $couponId = $request->coupon_id;

            // Plan validation
            if ($plan_details == null) {
                return view('errors.404');
            }

            // Existing transaction
            $transaction = AiCreditsTransaction::where(
                'payment_transaction_id',
                $request->transaction_id
            )->count();

            if ($transaction > 0) {

                return redirect()
                    ->route('user.ai.credits.plans')
                    ->with(
                        'failed',
                        trans('This transaction ID is already processed.')
                    );
            }

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

            // IDs
            $aiCreditsTransactionId = "TX" . preg_replace('/\D/', '', Str::uuid());

            $aiCreditsOrderId = "OD" . preg_replace('/\D/', '', Str::uuid());

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
            $transaction->payment_transaction_id = $request->transaction_id;
            $transaction->user_id = Auth::user()->id;
            $transaction->ai_credits_plan_id = $plan_details->ai_credits_plan_id;
            $transaction->purchase_details = $plan_details->plan_name . " AI Credits Plan";
            $transaction->payment_method = "Offline";
            $transaction->currency = $config[1]->config_value;
            $transaction->amount = $amountToBePaid;
            $transaction->invoice_details = json_encode($invoice_details);
            $transaction->payment_status = "processing";

            $transaction->save();

            // Save coupon
            if ($couponId != null) {

                $appliedCoupon = new AppliedCoupon();

                $appliedCoupon->applied_coupon_id = uniqid();
                $appliedCoupon->transaction_id = $aiCreditsTransactionId;
                $appliedCoupon->user_id = Auth::user()->id;
                $appliedCoupon->coupon_id = $couponId;
                $appliedCoupon->status = 0;

                $appliedCoupon->save();
            }

            return redirect()
                ->route('user.ai.credits.plans')
                ->with(
                    'success',
                    trans('Hold on some time! Your transaction details will be sent for verification. After verification, your AI Credits will be added.')
                );
        } else {

            return redirect()->route('login');
        }
    }
}
