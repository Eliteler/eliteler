<?php

namespace App\Services\Admin\Nfc;

use App\AppliedCoupon;
use App\Classes\InvoiceNumber;
use App\EmailTemplate;
use App\NfcCardDesign;
use App\NfcCardOrder;
use App\NfcCardOrderTransaction;
use App\User;
use App\Support\SmartProducts\OrderHistory;
use App\Support\Zapier;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

/**
 * NFC card orders and their payments.
 *
 * nfc_card_orders.order_status: pending (awaiting payment), processing,
 * printing process begun, shipped, out for delivery, delivered, hold, cancelled.
 * nfc_card_orders.order_details (JSON) holds the item, prices, quantity,
 * unique_key (array of activation keys) and tracking info.
 * nfc_card_order_transactions.payment_status: pending, success, failed.
 *
 * Stock rule: a design's stock is reduced when the payment becomes "success"
 * (as the online checkout does) and given back only if a successful payment
 * is later moved to pending or failed.
 */
class NfcOrderService
{
    public const DELIVERY_STATUSES = [
        'pending', 'processing', 'printing process begun', 'shipped', 'out for delivery', 'delivered', 'hold', 'cancelled',
    ];

    private const DELIVERY_EMAIL_TEMPLATE = '584922675210';

    public function __construct(private NfcKeyService $keys)
    {
    }

    public static function details(NfcCardOrder $order): array
    {
        return json_decode($order->order_details ?? '', true) ?: [];
    }

    public static function quantity(NfcCardOrder $order): int
    {
        return max((int) (self::details($order)['order_quantity'] ?? 1), 1);
    }

    /** @return array<int, string> */
    public static function orderKeys(NfcCardOrder $order): array
    {
        $keys = self::details($order)['unique_key'] ?? [];

        return array_values(array_filter(is_array($keys) ? $keys : [$keys]));
    }

    /** Products without a chip (generates_keys off) never get keys. */
    public static function generatesKeys(NfcCardOrder $order): bool
    {
        $design = NfcCardDesign::where('nfc_card_id', $order->nfc_card_id)->first();

        return $design === null || $design->generates_keys !== false;
    }

    /**
     * Create activation keys until the order has one per item ordered.
     * New keys start with the product's default link type.
     *
     * @return int number of keys created
     */
    public function ensureKeys(NfcCardOrder $order): int
    {
        $design  = NfcCardDesign::where('nfc_card_id', $order->nfc_card_id)->first();
        $details = self::details($order);
        $keys    = self::orderKeys($order);
        $missing = self::quantity($order) - count($keys);

        if ($missing <= 0 || $design?->generates_keys === false) {
            return 0;
        }

        $linkType = $design?->default_link_type ?: null;

        DB::transaction(function () use ($order, &$details, &$keys, $missing, $linkType) {
            for ($i = 0; $i < $missing; $i++) {
                $keys[] = $this->keys->newKey('online', null, null, $linkType)->unqiue_key;
            }
            $details['unique_key'] = $keys;
            $order->forceFill(['order_details' => json_encode($details)])->save();

            // The customer owns the new keys (ChipLinks)
            if (\Illuminate\Support\Facades\Schema::hasColumn('nfc_card_keys', 'user_id')) {
                \App\NfcCardKey::whereIn('unqiue_key', $keys)->whereNull('user_id')->update(['user_id' => $order->user_id]);
            }
        });

        return $missing;
    }

    /**
     * Update delivery status and tracking, email the customer and add a
     * timeline entry when anything changed.
     * Returns false when the email could not be sent.
     */
    public function updateDelivery(NfcCardOrder $order, array $data): bool
    {
        $details = self::details($order);
        $before  = [$order->order_status, $details['tracking_number'] ?? '', $details['courier_partner'] ?? '', $details['delivery_message'] ?? ''];

        $details['tracking_number']  = $data['tracking_number'] ?? '';
        $details['courier_partner']  = $data['courier_partner'] ?? '';
        $details['delivery_message'] = $data['delivery_message'] ?? '';

        $order->forceFill([
            'order_details' => json_encode($details),
            'order_status'  => $data['status'],
        ])->save();

        $changed = $before !== [$data['status'], $details['tracking_number'], $details['courier_partner'], $details['delivery_message']];
        $emailed = ($data['notify'] ?? true) ? $this->emailDelivery($order, $details, $data['status']) : null;

        if ($changed || $emailed) {
            OrderHistory::record($order, $data['status'], [
                'message'         => $details['delivery_message'],
                'tracking_number' => $details['tracking_number'],
                'courier_partner' => $details['courier_partner'],
                'notified'        => (bool) $emailed,
            ]);
        }

        return $emailed !== false;
    }

    /** Delivery update email (template 584922675210). */
    private function emailDelivery(NfcCardOrder $order, array $details, string $status): bool
    {
        $transaction = NfcCardOrderTransaction::where('nfc_card_order_id', $order->nfc_card_order_id)->first();
        $invoice     = json_decode($transaction->invoice_details ?? '', true) ?: [];
        $template    = EmailTemplate::where('email_template_id', self::DELIVERY_EMAIL_TEMPLATE)->first();
        $email       = $invoice['to_billing_email'] ?? User::find($order->user_id)?->email;

        if (! $template || ! $email) {
            return false;
        }

        try {
            Mail::to($email)->send(new \App\Mail\AppointmentMail([
                'emailSubject'   => $template->email_template_subject,
                'emailContent'   => $template->email_template_content,
                'orderid'        => $order->nfc_card_order_id,
                'cardname'       => $details['order_item'] ?? '',
                'cardprice'      => $details['price'] ?? '',
                'paymentstatus'  => $transaction->payment_status ?? '',
                'deliverystatus' => $status,
                'quantity'       => self::quantity($order),
                'trackingnumber' => $details['tracking_number'] ?: '-',
                'courierpartner' => $details['courier_partner'] ?: '-',
                'orderpageurl'   => route('user.order.nfc.card.view', $order->nfc_card_order_id),
                'totalprice'     => formatMoney($invoice['invoice_amount'] ?? $transaction->amount ?? 0, $transaction->currency ?? null),
                'supportemail'   => $invoice['from_billing_email'] ?? '',
                'supportphone'   => $invoice['from_billing_phone'] ?? '',
            ]));

            return true;
        } catch (\Throwable $e) {
            report($e);

            return false;
        }
    }

    /**
     * Change a payment's status by hand (e.g. confirming an offline payment).
     *
     * success: invoice number, order to processing, coupon redeemed, stock
     *          reduced, activation keys created, invoice emailed, Zapier event.
     * failed:  order cancelled. pending: order back to pending.
     * Leaving success gives the stock back.
     */
    public function setPaymentStatus(NfcCardOrderTransaction $transaction, string $status): void
    {
        $order = NfcCardOrder::where('nfc_card_order_id', $transaction->nfc_card_order_id)->firstOrFail();
        $wasPaid  = $transaction->payment_status === 'success';
        $quantity = self::quantity($order);

        if ($status === $transaction->payment_status) {
            return;
        }

        DB::transaction(function () use ($transaction, $order, $status, $wasPaid, $quantity) {
            $design = NfcCardDesign::where('nfc_card_id', $order->nfc_card_id)->first();

            if ($status === 'success') {
                $prefix = DB::table('config')->where('config_key', 'invoice_prefix')->value('config_value');

                if (! $transaction->invoice_number) {
                    $transaction->invoice_prefix = $prefix;
                    $transaction->invoice_number = InvoiceNumber::next('nfc_card_order_transactions', $prefix);
                }
                $transaction->payment_status = 'success';
                $transaction->save();

                $order->forceFill(['order_status' => 'processing'])->save();
                AppliedCoupon::where('transaction_id', $transaction->payment_transaction_id)->update(['status' => 1]);
                $design?->decrement('available_stocks', $quantity);
                $this->ensureKeys($order->fresh());
                OrderHistory::record($order, 'processing', ['message' => __('Payment confirmed.')]);

                return;
            }

            $transaction->forceFill(['payment_status' => $status])->save();
            $order->forceFill(['order_status' => $status === 'failed' ? 'cancelled' : 'pending'])->save();
            OrderHistory::record($order, $status === 'failed' ? 'cancelled' : 'pending', [
                'message' => $status === 'failed' ? __('Payment failed.') : __('Payment marked as pending.'),
            ]);

            if ($wasPaid) {
                $design?->increment('available_stocks', $quantity);
            }
        });

        if ($status === 'success') {
            $this->emailInvoice($transaction->fresh(), $order->fresh());
            $this->dispatchZapier($transaction->fresh(), $order->fresh(), $quantity);
        }
    }

    private function emailInvoice(NfcCardOrderTransaction $transaction, NfcCardOrder $order): void
    {
        $invoice = json_decode($transaction->invoice_details ?? '', true) ?: [];
        $config  = DB::table('config')->whereIn('config_key', ['email_heading', 'email_footer'])->pluck('config_value', 'config_key');

        if (empty($invoice['to_billing_email'])) {
            return;
        }

        try {
            Mail::to($invoice['to_billing_email'])->send(new \App\Mail\SendEmailInvoice([
                'from_billing_name'    => $invoice['from_billing_name'] ?? '',
                'from_billing_email'   => $invoice['from_billing_email'] ?? '',
                'from_billing_address' => $invoice['from_billing_address'] ?? '',
                'from_billing_city'    => $invoice['from_billing_city'] ?? '',
                'from_billing_state'   => $invoice['from_billing_state'] ?? '',
                'from_billing_country' => $invoice['from_billing_country'] ?? '',
                'from_billing_zipcode' => $invoice['from_billing_zipcode'] ?? '',
                'gobiz_transaction_id' => $transaction->payment_transaction_id,
                'to_billing_name'      => $invoice['to_billing_name'] ?? '',
                'invoice_currency'     => $transaction->currency,
                'subtotal'             => $invoice['subtotal'] ?? 0,
                'tax_amount'           => $invoice['tax_amount'] ?? 0,
                'applied_coupon'       => $invoice['applied_coupon'] ?? null,
                'discounted_price'     => $invoice['discounted_price'] ?? 0,
                'shipping_amount'      => $invoice['shipping_amount'] ?? 0,
                'invoice_amount'       => $invoice['invoice_amount'] ?? $transaction->amount,
                'invoice_id'           => $transaction->invoice_prefix . $transaction->invoice_number,
                'invoice_date'         => $transaction->created_at,
                'description'          => self::details($order)['order_item'] ?? '',
                'email_heading'        => $config['email_heading'] ?? '',
                'email_footer'         => $config['email_footer'] ?? '',
            ]));
        } catch (\Throwable $e) {
            report($e);
        }
    }

    private function dispatchZapier(NfcCardOrderTransaction $transaction, NfcCardOrder $order, int $quantity): void
    {
        Zapier::nfcOrder(User::find($order->user_id), [
            'transaction_id' => $order->nfc_card_order_id,
            'invoice_id'     => $transaction->invoice_prefix . $transaction->invoice_number,
            'nfc_card_name'  => self::details($order)['order_item'] ?? 'NFC Card',
            'quantity'       => $quantity,
            'currency'       => $transaction->currency,
            'amount'         => $transaction->amount,
            'payment_method' => $transaction->payment_method ?? 'Offline',
        ], json_decode($transaction->invoice_details ?? '', true) ?: []);
    }
}
