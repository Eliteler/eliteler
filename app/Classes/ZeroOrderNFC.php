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

        if ($couponDetails != null) {

            // TAX
            $appliedTaxInTotal = (float) (($subtotal * (float) $config[25]->config_value) / 100);

            // SUBTOTAL INCLUDING TAX
            $subtotalWithTax = $subtotal + $appliedTaxInTotal;

            // FIXED
            if ($couponDetails->coupon_type == 'fixed') {
                $discountPrice = (float) $couponDetails->coupon_amount;
            } else {
                // PERCENTAGE
                $discountPrice = ($subtotalWithTax * (float) $couponDetails->coupon_amount) / 100;
            }

            // PREVENT DISCOUNT FROM EXCEEDING TOTAL
            $discountPrice = min($discountPrice, $subtotalWithTax);

            // TOTAL
            $amountToBePaid = $subtotalWithTax - $discountPrice;
            $amountToBePaid = max($amountToBePaid, 0);
            $amountToBePaid = (float) number_format($amountToBePaid, 2, '.', '');

            // FORMAT DISCOUNT
            $discountPrice = (float) number_format($discountPrice, 2, '.', '');

            // Applied
            $appliedCoupon = $couponDetails->coupon_code;
        } else {

            // WITHOUT COUPON
            $appliedTaxInTotal = ($subtotal * (float) $config[25]->config_value) / 100;

            $amountToBePaid = $subtotal + $appliedTaxInTotal;
            $amountToBePaid = (float) number_format($amountToBePaid, 2, '.', '');
        }

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
            $this->prepareOrderDetails(
                $config,
                $amountToBePaid,
                $nfcCard,
                $appliedCoupon,
                $discountPrice,
                $quantity
            )
        );
        $nfcCardOrder->delivery_address = json_encode(
            $this->prepareDeliveryAddress($userData)
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
            $this->prepareInvoiceDetails(
                $config,
                $userData,
                $amountToBePaid,
                $nfcCard,
                $appliedCoupon,
                $discountPrice,
                $quantity
            )
        );
        $transaction->payment_status = 'pending';

        $transaction->save();

        /*
        |--------------------------------------------------------------------------
        | Coupon Save
        |--------------------------------------------------------------------------
        */

        if ($couponId != ' ') {
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

    /*
    |--------------------------------------------------------------------------
    | Invoice Details
    |--------------------------------------------------------------------------
    */

    private function prepareInvoiceDetails($config, $userData, $amountToBePaid, $nfcDetails, $appliedCoupon, $discountPrice, $quantity)
    {
        $subtotal = $nfcDetails->nfc_card_price * $quantity;

        return [
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
            'to_billing_phone' => $userData->billing_country_code . $userData->billing_phone,
            'to_billing_email' => $userData->billing_email,
            'to_vat_number' => $userData->vat_number,
            'tax_name' => $config[24]->config_value,
            'tax_type' => $config[14]->config_value,
            'tax_value' => $config[25]->config_value,
            'applied_coupon' => $appliedCoupon,
            'discounted_price' => $discountPrice,
            'invoice_amount' => 0,
            'subtotal' => $subtotal,
            'tax_amount' => ($subtotal * (float) $config[25]->config_value) / 100,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Order Details
    |--------------------------------------------------------------------------
    */

    private function prepareOrderDetails($config, $amountToBePaid, $nfcDetails, $appliedCoupon, $discountPrice, $quantity)
    {
        $subtotal = $nfcDetails->nfc_card_price * $quantity;

        return [
            'nfc_card_id' => $nfcDetails->nfc_card_id,
            'order_item' => $nfcDetails->nfc_card_name,
            'order_description' => $nfcDetails->nfc_card_description,
            'order_quantity' => $quantity,
            'price' => $nfcDetails->nfc_card_price,
            'invoice_amount' => $amountToBePaid,
            'tax_name' => $config[24]->config_value,
            'tax_type' => $config[14]->config_value,
            'tax_value' => $config[25]->config_value,
            'tax_amount' => ($subtotal * (float) $config[25]->config_value) / 100,
            'applied_coupon' => $appliedCoupon,
            'discounted_price' => $discountPrice,
            'subtotal' => $subtotal,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Delivery Address
    |--------------------------------------------------------------------------
    */

    private function prepareDeliveryAddress($userData)
    {
        return [
            'billing_name' => $userData->billing_name,
            'billing_address' => $userData->billing_address,
            'billing_city' => $userData->billing_city,
            'billing_state' => $userData->billing_state,
            'billing_zipcode' => $userData->billing_zipcode,
            'billing_country' => $userData->billing_country,
            'billing_phone' => $userData->billing_country_code . $userData->billing_phone,
            'billing_email' => $userData->billing_email,
            'shipping_name' => $userData->billing_name,
            'shipping_address' => $userData->billing_address,
            'shipping_city' => $userData->billing_city,
            'shipping_state' => $userData->billing_state,
            'shipping_zipcode' => $userData->billing_zipcode,
            'shipping_country' => $userData->billing_country,
            'shipping_phone' => $userData->billing_country_code . $userData->billing_phone,
            'shipping_email' => $userData->billing_email,
            'type' => $userData->type,
            'vat_number' => $userData->vat_number,
        ];
    }
}
