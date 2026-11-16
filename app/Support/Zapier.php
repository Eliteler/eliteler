<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Zapier events, sent through the Zapier plugin (plugins/Zapier) when it is installed.
 * Any controller can call one line, e.g. Zapier::customDomain($user, ...), and gets the
 * same payload as every other place that sends that event.
 *
 * Webhooks are posted after the response has gone to the browser (app()->terminating,
 * no cron or queue needed), so a slow webhook never delays a page. In the console
 * (artisan, queue workers) they are posted right away.
 *
 * Events and their Zapier settings column:
 *   new_user           user_registration_webhook
 *   plan_purchase      plan_purchase_webhook
 *   ai_credits         ai_credits_webhook
 *   nfc_order          nfc_order_webhook
 *   referral_withdraw  referral_withdraw_webhook
 *   custom_domain      custom_domain_webhook
 */
class Zapier
{
    private const HELPER = 'Plugins\\Zapier\\Helpers\\ZapierHelper';

    /** Send an event with a ready-made payload. Never throws. */
    public static function send(string $event, array $data): void
    {
        try {
            if (! self::available()) {
                return;
            }

            if (app()->runningInConsole()) {
                self::post($event, $data);

                return;
            }

            // Terminating callbacks stay registered in long-running processes (Octane)
            // and run again on later requests, so each one posts only once
            $sent = false;
            app()->terminating(function () use ($event, $data, &$sent) {
                if ($sent) {
                    return;
                }
                $sent = true;
                self::post($event, $data);
            });
        } catch (Throwable $e) {
            Log::error("Zapier [{$event}] not sent: " . $e->getMessage());
        }
    }

    /**
     * The customer fields every purchase/request event carries. Billing details of the
     * account first, then the invoice's "to_billing_*" values, then the profile.
     *
     * @param  object|null  $user     users row or App\User
     * @param  array        $invoice  decoded invoice_details of the transaction
     */
    public static function customer(?object $user, array $invoice = []): array
    {
        $pick = fn (string $column, string $invoiceKey) => filled($user->{$column} ?? null) ? $user->{$column} : ($invoice[$invoiceKey] ?? '');

        return [
            'user_name'       => $user->name ?? ($invoice['to_billing_name'] ?? 'Customer'),
            'user_address'    => $pick('billing_address', 'to_billing_address'),
            'user_city'       => $pick('billing_city', 'to_billing_city'),
            'user_state'      => $pick('billing_state', 'to_billing_state'),
            'user_country'    => $pick('billing_country', 'to_billing_country'),
            'user_zip'        => $pick('billing_zipcode', 'to_billing_zipcode'),
            'user_email'      => $user->email ?? ($invoice['to_billing_email'] ?? ''),
            'user_phone'      => ($user->country_code ?? '') . ($user->mobile_number ?? ($invoice['to_billing_phone'] ?? '')),
            'user_tax_number' => $user->vat_number ?? $user->tax_number ?? $user->billing_tax_number ?? ($invoice['to_vat_number'] ?? ''),
        ];
    }

    /** A new account. $registeredBy: "Customer (Frontend)", "Customer (Google)", "Admin", … */
    public static function newUser(object $user, string $registeredBy): void
    {
        self::send('new_user', [
            'registered_at'   => now()->toDateTimeString(),
            'user_id'         => $user->user_id ?? $user->id,
            'name'            => $user->name,
            'email'           => $user->email,
            'mobile_number'   => ($user->country_code ?? '') . ($user->mobile_number ?? ''),
            'address'         => $user->billing_address ?? '',
            'city'            => $user->billing_city ?? '',
            'state'           => $user->billing_state ?? '',
            'country'         => $user->billing_country ?? '',
            'zipcode'         => $user->billing_zipcode ?? '',
            'user_tax_number' => $user->billing_tax_number ?? $user->tax_number ?? $user->vat_number ?? '',
            'registered_by'   => $registeredBy,
        ]);
    }

    /**
     * A paid (or activated) plan.
     *
     * @param  array  $purchase  transaction_id, invoice_id, plan_name, currency, amount, payment_method
     * @param  array  $invoice   decoded invoice_details, for customer fallbacks
     */
    public static function planPurchase(?object $user, array $purchase, array $invoice = []): void
    {
        self::send('plan_purchase', self::purchase($user, $invoice, $purchase, [
            'plan_name' => $purchase['plan_name'] ?? 'Subscription Plan',
            'quantity'  => 1,
        ]));
    }

    /**
     * Bought AI credits.
     *
     * @param  array  $purchase  transaction_id, invoice_id, plan_name, currency, amount, payment_method
     */
    public static function aiCredits(?object $user, array $purchase, array $invoice = []): void
    {
        self::send('ai_credits', self::purchase($user, $invoice, $purchase, [
            'plan_name' => $purchase['plan_name'] ?? 'AI Credits Plan',
            'quantity'  => 1,
        ]));
    }

    /**
     * A paid NFC card order.
     *
     * @param  array  $purchase  transaction_id, invoice_id, nfc_card_name, quantity, currency, amount,
     *                           payment_method, payment_status, order_status
     */
    public static function nfcOrder(?object $user, array $purchase, array $invoice = []): void
    {
        self::send('nfc_order', self::purchase($user, $invoice, $purchase, [
            'nfc_card_name' => $purchase['nfc_card_name'] ?? 'NFC Card',
            'quantity'      => (int) ($purchase['quantity'] ?? 1),
            'order_status'  => $purchase['order_status'] ?? 'processing',
        ]));
    }

    /**
     * A referral payout request, or a change of its status by the admin.
     *
     * @param  mixed  $bankDetails  as stored (JSON or text); sent as plain text
     */
    public static function referralWithdraw(?object $user, string $withdrawId, float $amount, mixed $bankDetails, string $status = 'Pending Approval'): void
    {
        self::send('referral_withdraw', [
            'requested_at'        => now()->toDateTimeString(),
            'withdraw_id'         => $withdrawId,
            ...self::customer($user),
            'user_name'           => $user->name ?? 'Affiliate User',
            'withdrawal_currency' => self::currency(),
            'amount'              => $amount,
            'bank_details'        => self::plainText($bankDetails),
            'transfer_status'     => $status,
        ]);
    }

    /** A custom domain request, or a change of its status by the admin. */
    public static function customDomain(?object $user, string $requestId, ?string $cardName, ?string $previousDomain, string $currentDomain, string $status = 'Pending Approval'): void
    {
        self::send('custom_domain', [
            'request_id'      => $requestId,
            'user_name'       => $user->name ?? 'Customer',
            'user_email'      => $user->email ?? '',
            'card_name'       => $cardName ?: 'Business Card',
            'previous_domain' => $previousDomain ?? '',
            'current_domain'  => $currentDomain,
            'status'          => $status,
            'requested_at'    => now()->toDateTimeString(),
        ]);
    }

    /** Fields shared by plan, AI credit and NFC purchases, in the order Zapier has always received them. */
    private static function purchase(?object $user, array $invoice, array $purchase, array $item): array
    {
        return [
            'transaction_id' => $purchase['transaction_id'] ?? '',
            'invoice_id'     => $purchase['invoice_id'] ?? '',
            ...self::customer($user, $invoice),
            ...$item,
            'currency'       => $purchase['currency'] ?? self::currency(),
            'amount'         => (float) ($purchase['amount'] ?? ($invoice['invoice_amount'] ?? 0)),
            'payment_method' => $purchase['payment_method'] ?? 'Online',
            'payment_status' => $purchase['payment_status'] ?? 'paid',
            'invoice_date'   => now()->toDateString(),
            'purchased_at'   => now()->toDateTimeString(),
        ];
    }

    private static function available(): bool
    {
        return function_exists('plugin_active')
            && plugin_active('Zapier')
            && file_exists(base_path('plugins/Zapier/Helpers/ZapierHelper.php'))
            && class_exists(self::HELPER);
    }

    private static function post(string $event, array $data): void
    {
        try {
            (self::HELPER)::dispatch($event, $data);
        } catch (Throwable $e) {
            Log::error("Zapier [{$event}] not sent: " . $e->getMessage());
        }
    }

    private static function currency(): string
    {
        return (string) (DB::table('config')->where('config_key', 'currency')->value('config_value') ?: 'USD');
    }

    /** Bank details as readable text (JSON objects become "key: value" lines). */
    private static function plainText(mixed $value): string
    {
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            $value   = json_last_error() === JSON_ERROR_NONE && $decoded !== null ? $decoded : $value;
        }

        if (is_array($value) || is_object($value)) {
            $value = collect((array) $value)
                ->map(fn ($item, $key) => is_int($key) ? (string) (is_scalar($item) ? $item : json_encode($item)) : $key . ': ' . (is_scalar($item) ? $item : json_encode($item)))
                ->implode("\n");
        }

        // Paragraphs and line breaks from rich text editors become new lines
        $text = preg_replace('#<br\s*/?>|</(p|div|li|h[1-6])>#i', "\n", (string) $value);

        return trim(preg_replace("/\n{2,}/", "\n", html_entity_decode(strip_tags($text), ENT_QUOTES | ENT_HTML5, 'UTF-8')));
    }
}
