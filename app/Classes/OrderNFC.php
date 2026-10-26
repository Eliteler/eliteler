<?php

/*
 |--------------------------------------------------------------------------
 | Eliteler vCard SaaS
 |--------------------------------------------------------------------------
 | Developed by NativeCode © 2021 - https://nativecode.in
 | All rights reserved
 | Unauthorized distribution is prohibited
 |--------------------------------------------------------------------------
*/

namespace App\Classes;

use App\AppliedCoupon;
use App\Currency;
use App\EmailTemplate;
use App\NfcCardDesign;
use App\NfcCardKey;
use App\NfcCardOrder;
use App\NfcCardOrderTransaction;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail; 
use Illuminate\Support\Str;
use Plugins\Zapier\Helpers\ZapierHelper;

class OrderNFC
{
    public function order($paymentId, $res)
    {
        // Queries
        $config = DB::table('config')->get();

        // Currency symbol
        $currencies = Currency::where('status', 1)->pluck('symbol', 'iso_code')->toArray();
        $symbol = $currencies[$config[1]->config_value] ?? '';

        $transactionDetails = NfcCardOrderTransaction::where('payment_transaction_id', $paymentId)->where('status', 1)->first();

        $invoice_count = NfcCardOrderTransaction::where("invoice_prefix", $config[15]->config_value)->count();
        $invoice_number = $invoice_count + 1;

        // Update nfc card order transaction details
        if ($transactionDetails) {
            $transactionDetails->payment_transaction_id = $paymentId;
            $transactionDetails->invoice_prefix = $config[15]->config_value;
            $transactionDetails->invoice_number = $invoice_number;
            $transactionDetails->payment_status = 'success';
            $transactionDetails->save();
        }

        // Update nfc card order details
        NfcCardOrder::where('nfc_card_order_id', $transactionDetails->nfc_card_order_id)->update([
            'order_status' => 'processing',
        ]);

        // Save applied coupon
        AppliedCoupon::where('transaction_id', $paymentId)->update([
            'status' => 1
        ]);

        // Get NFC card order details
        $nfcCardOrderDetails = NfcCardOrder::where('nfc_card_order_id', $transactionDetails->nfc_card_order_id)->first();

        // Reduce NFC card stock
        $nfcCardDesign = NfcCardDesign::where('nfc_card_id', $nfcCardOrderDetails->nfc_card_id)->first();
        $nfcCardDesign->available_stocks = (int) $nfcCardDesign->available_stocks - 1;
        $nfcCardDesign->save();

        // Quantity
        $quantity = max((int) json_decode($nfcCardOrderDetails->order_details, true)['order_quantity'], 1);

        // Generate quantity wise NFC card key
        $orderDetails = json_decode($nfcCardOrderDetails->order_details, true) ?? [];

        // Ensure unique_key is always an array
        $orderDetails['unique_key'] = $orderDetails['unique_key'] ?? [];

        for ($i = 1; $i <= $quantity; $i++) {

        // Generate NFC card key
        $characterLimit = (int) $config[92]->config_value;
        $unqiueKey = Str::random($characterLimit);

            // Save NFC card key
        $nfcCardKey = new NfcCardKey();
        $nfcCardKey->nfc_card_key_id = uniqid();
        $nfcCardKey->key_type = 'online';
        $nfcCardKey->unqiue_key = $unqiueKey;
        $nfcCardKey->save();

            // Push unique key into array
            $orderDetails['unique_key'][] = $unqiueKey;
        }

        // Update order once after loop
        NfcCardOrder::where('nfc_card_order_id', $transactionDetails->nfc_card_order_id)->update([
            'order_details' => json_encode($orderDetails),
            'updated_at' => now(),
        ]);

        // Get customer details
        $customerDetails = User::where('id', $nfcCardOrderDetails->user_id)->first();


        // Email Message details
        $encode = json_decode($transactionDetails['invoice_details'], true);
        $itemDetails = json_decode($nfcCardOrderDetails['order_details'], true);

        $MailTemplateDetails = EmailTemplate::where('email_template_id', '584922675209')->first();
        $ownerMailTemplateDetails = EmailTemplate::where('email_template_id', '584922675211')->first();

        $details = [
            'emailSubject' => $MailTemplateDetails->email_template_subject,
            'emailContent' => $MailTemplateDetails->email_template_content,
            'orderid' => $transactionDetails->nfc_card_order_id,
            'cardname' => $itemDetails['order_item'],
            'cardprice' => $itemDetails['price'],
            'paymentstatus' => $transactionDetails->payment_status,
            'deliverystatus' => 'processing',
            'quantity' => 1,
            'trackingnumber' => $itemDetails['tracking_number'] ?? '-',
            'courierpartner' => $itemDetails['courier_partner'] ?? '-',
            'orderpageurl' => route('user.order.nfc.card.view', $transactionDetails->nfc_card_order_id),
            'totalprice' => formatCurrency($encode['invoice_amount']),
            'supportemail' => $encode['from_billing_email'],
            'supportphone' => $encode['from_billing_phone'],
        ];

        $ownerDetails = [
            'emailSubject' => $ownerMailTemplateDetails->email_template_subject,
            'emailContent' => $ownerMailTemplateDetails->email_template_content,
            'orderid' => $nfcCardOrderDetails->nfc_card_order_id,
            'cardname' => $itemDetails['order_item'],
            'cardprice' => $itemDetails['price'],
            'paymentstatus' => $nfcCardOrderDetails->payment_status,
            'deliverystatus' => 'processing',
            'quantity' => 1,
            'trackingnumber' => $itemDetails['tracking_number'] ?? '-',
            'courierpartner' => $itemDetails['courier_partner'] ?? '-',
            'orderpageurl' => route('admin.order.show', $nfcCardOrderDetails->nfc_card_order_id),
            'totalprice' => formatCurrency($encode['invoice_amount']),
            'supportemail' => $encode['from_billing_email'],
            'supportphone' => $encode['from_billing_phone'],
            'customerName' => $customerDetails->name,
            'customeremail' => $customerDetails->email,
        ];

        try {
            Mail::to($encode['to_billing_email'])->send(new \App\Mail\AppointmentMail($details));
            Mail::to(env('MAIL_FROM_ADDRESS'))->send(new \App\Mail\AppointmentMail($ownerDetails));
        } catch (\Exception $e) {
        }

        // Safely format user billing address
        $userAddress = $customerDetails->billing_address ?? '';
        $userCity = $customerDetails->billing_city ?? '';
        $userState = $customerDetails->billing_state ?? '';
        $userCountry = $customerDetails->billing_country ?? '';
        $userZip = $customerDetails->billing_zipcode ?? '';

        // Dispatch NFC card order event to Zapier
        if (function_exists('zapier_dispatch')) {
            zapier_dispatch('nfc_order', [
                'transaction_id'  => $transactionDetails->nfc_card_order_id ?? $paymentId,
                'invoice_id'      => ($config[15]->config_value ?? 'INV-') . $invoice_number,
                'user_name'       => $customerDetails->name ?? ($encode['to_billing_name'] ?? 'Customer'),
                'user_address'    => !empty($userAddress) ? $userAddress : ($encode['to_billing_address'] ?? ''),
                'user_city'       => !empty($userCity) ? $userCity : ($encode['to_billing_city'] ?? ''),
                'user_state'      => !empty($userState) ? $userState : ($encode['to_billing_state'] ?? ''),
                'user_country'    => !empty($userCountry) ? $userCountry : ($encode['to_billing_country'] ?? ''),
                'user_zip'        => !empty($userZip) ? $userZip : ($encode['to_billing_zipcode'] ?? ''),
                'user_email'      => $customerDetails->email ?? ($encode['to_billing_email'] ?? ''),
                'user_phone'      => ($customerDetails->country_code ?? '') . ($customerDetails->mobile_number ?? ($encode['to_billing_phone'] ?? '')),
                'user_tax_number' => $customerDetails->vat_number ?? $customerDetails->tax_number ?? ($encode['to_vat_number'] ?? ''),
                'nfc_card_name'   => $itemDetails['order_item'] ?? 'Custom Metal NFC Business Card',
                'quantity'        => (int) $quantity,
                'currency'        => $transactionDetails->currency ?? ($config[1]->config_value ?? 'USD'),
                'amount'          => (float) ($transactionDetails->amount ?? ($encode['invoice_amount'] ?? 0)),
                'order_status'    => 'processing',
                'payment_method'  => $transactionDetails->payment_method ?? 'Online',
                'payment_status'  => $nfcCardOrderDetails->payment_status ?? 'paid',
                'invoice_date'    => now()->toDateString(),
                'purchased_at'    => now()->toDateTimeString(),
            ]);
        }
    }

    // Failed
    public function paymentFailed($paymentId)
    {
        NfcCardOrderTransaction::where('payment_transaction_id', $paymentId)->update([
            'payment_status' => 'failed',
        ]);
    }
}
