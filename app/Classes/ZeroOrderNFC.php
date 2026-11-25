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

use App\Services\SmartProducts\OrderQuote;
use App\AppliedCoupon;
use App\Coupon;
use App\NfcCardOrder;
use App\NfcCardOrderTransaction;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ZeroOrderNFC
{
    public function zero($couponId, $nfcCard, $nfcId, $plan, $payment_mode, $quantity = 1)
    {
        // Config
        $config = DB::table('config')->get();

        // Coupon
        $couponDetails = Coupon::where('used_for', 'nfc')
            ->where('coupon_code', $couponId)
            ->first();

        // User
        $userData = User::where('id', Auth::user()->id)->first();

        // Tax
        $appliedTaxInTotal = 0;

        // Discount
        $discountPrice = 0;

        // Applied coupon
        $appliedCoupon = null;

        // Price
        $subtotal = $nfcCard->nfc_card_price * $quantity;

        // IDs
        $nfcCardOrderId = 'OD' . preg_replace('/\D/', '', Str::uuid());
        $nfcTransactionId = 'TX' . preg_replace('/\D/', '', Str::uuid());

        /*
|--------------------------------------------------------------------------
| Coupon Calculation
|--------------------------------------------------------------------------
*/

        // Price: same calculation as the checkout page (App\Services\SmartProducts\OrderQuote)
        $quote             = OrderQuote::make($nfcCard, $quantity, $couponDetails);
        $subtotal          = $quote->subtotal;
        $appliedTaxInTotal = $quote->tax;
        $discountPrice     = $quote->discount;
        $appliedCoupon     = $quote->couponCode;
        $amountToBePaid    = $quote->total;

        /*
        |--------------------------------------------------------------------------
        | ORDER
        |--------------------------------------------------------------------------
        */

        $nfcCardOrder = new NfcCardOrder();

        $nfcCardOrder->nfc_card_order_id = $nfcCardOrderId;
        $nfcCardOrder->user_id = Auth::id();
        $nfcCardOrder->nfc_card_id = $nfcId;
        $nfcCardOrder->nfc_card_order_transaction_id = $nfcTransactionId;
        $nfcCardOrder->order_details = json_encode(
            $quote->orderDetails()
        );
        $nfcCardOrder->delivery_address = json_encode(
            OrderQuote::deliveryAddress($userData)
        );
        $nfcCardOrder->delivery_note = '-';
        $nfcCardOrder->order_status = 'pending';
        $nfcCardOrder->status = 1;

        $nfcCardOrder->save();

        /*
        |--------------------------------------------------------------------------
        | TRANSACTION
        |--------------------------------------------------------------------------
        */

        $transaction = new NfcCardOrderTransaction();

        $transaction->nfc_card_order_transaction_id = $nfcTransactionId;
        $transaction->nfc_card_order_id = $nfcCardOrderId;
        $transaction->payment_transaction_id = $nfcTransactionId;
        $transaction->payment_method = $payment_mode->payment_gateway_name ?? '-';
        $transaction->currency = $config[1]->config_value;
        $transaction->amount = 0;
        $transaction->invoice_details = json_encode(
            $quote->invoiceDetails($userData)
        );
        $transaction->payment_status = 'pending';

        $transaction->save();

        /*
        |--------------------------------------------------------------------------
        | Coupon Save
        |--------------------------------------------------------------------------
        */

        if (!empty(trim($couponId ?? ''))) {
            $appliedCouponData = new AppliedCoupon();

            $appliedCouponData->applied_coupon_id = uniqid();
            $appliedCouponData->transaction_id = $nfcTransactionId;
            $appliedCouponData->user_id = Auth::user()->id;
            $appliedCouponData->coupon_id = $couponId;
            $appliedCouponData->status = 0;

            $appliedCouponData->save();
        }

        /*
        |--------------------------------------------------------------------------
        | Order Place
        |--------------------------------------------------------------------------
        */

        $order = new OrderNFC();

        $order->order($nfcTransactionId, $nfcCardOrderId);
    }
}
