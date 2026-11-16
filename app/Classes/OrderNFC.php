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
use App\Currency;
use App\EmailTemplate;
use App\NfcCardDesign;
use App\NfcCardKey;
use App\NfcCardOrder;
use App\NfcCardOrderTransaction;
use App\Support\Zapier;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

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

        if (!$transactionDetails) {
            return;
        }

        // Update nfc card order transaction details.
        // A gateway can report the same payment twice (return page + webhook), so the status
        // is checked under a row lock and an already processed payment is skipped.
        $invoice_number = DB::transaction(function () use ($transactionDetails, $paymentId, $config) {
            // Takes the invoice lock first (same lock order as the admin approval path)
            $invoice_number = InvoiceNumber::next('nfc_card_order_transactions', $config[15]->config_value);

            $locked = NfcCardOrderTransaction::whereKey($transactionDetails->getKey())->lockForUpdate()->first();
            if ($locked->payment_status === 'success') {
                return null;
            }

            $transactionDetails->payment_transaction_id = $paymentId;
            $transactionDetails->invoice_prefix = $config[15]->config_value;
            $transactionDetails->invoice_number = $invoice_number;
            $transactionDetails->payment_status = 'success';
            $transactionDetails->save();

            return $invoice_number;
        });

        // Already processed
        if ($invoice_number === null) {
            return;
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

        // Quantity
        $quantity = max((int) json_decode($nfcCardOrderDetails->order_details, true)['order_quantity'], 1);

        // Reduce NFC card stock by the ordered quantity
        NfcCardDesign::where('nfc_card_id', $nfcCardOrderDetails->nfc_card_id)->decrement('available_stocks', $quantity);

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
            Mail::to(config('mail.from.address'))->send(new \App\Mail\AppointmentMail($ownerDetails));
        } catch (\Throwable $e) {
            report($e);
        }

        // Zapier: NFC card order
        Zapier::nfcOrder($customerDetails, [
            'transaction_id' => $transactionDetails->nfc_card_order_id ?? $paymentId,
            'invoice_id'     => ($config[15]->config_value ?? 'INV-') . $invoice_number,
            'nfc_card_name'  => $itemDetails['order_item'] ?? 'Custom Metal NFC Business Card',
            'quantity'       => (int) $quantity,
            'currency'       => $transactionDetails->currency ?? ($config[1]->config_value ?? 'USD'),
            'amount'         => $transactionDetails->amount ?? ($encode['invoice_amount'] ?? 0),
            'payment_method' => $transactionDetails->payment_method ?? 'Online',
            'payment_status' => $nfcCardOrderDetails->payment_status ?? 'paid',
        ], $encode ?? []);
    }

    // Failed
    public function paymentFailed($paymentId)
    {
        NfcCardOrderTransaction::where('payment_transaction_id', $paymentId)->update([
            'payment_status' => 'failed',
        ]);
    }
}
