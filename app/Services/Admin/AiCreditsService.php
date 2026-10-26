<?php

namespace App\Services\Admin;

use App\AiCredit;
use App\AiCreditsPlan;
use App\AiCreditsTransaction;
use App\User;
use Illuminate\Support\Facades\DB;

/**
 * AI credit purchases (ai_credits_transactions) and balances (ai_credits).
 *
 * payment_status: pending, processing, paid, failed, cancelled, refunded,
 * partially_refunded. ai_credits.credits of -1 means unlimited (from a plan).
 * Credits are granted once, when a purchase becomes "paid", and taken back
 * (never below 0) if a paid purchase is moved to another status.
 */
class AiCreditsService
{
    public const STATUSES = ['pending', 'processing', 'paid', 'failed', 'cancelled', 'refunded', 'partially_refunded'];

    public function setPaymentStatus(AiCreditsTransaction $transaction, string $status): void
    {
        if ($status === $transaction->payment_status) {
            return;
        }

        $wasPaid = $transaction->payment_status === 'paid';
        $user    = User::find($transaction->user_id);
        $plan    = AiCreditsPlan::where('ai_credits_plan_id', $transaction->ai_credits_plan_id)->first();
        $credits = (int) ($plan->no_of_ai_credits ?? 0);

        DB::transaction(function () use ($transaction, $status, $wasPaid, $user, $credits) {
            if ($status === 'paid' && ! $transaction->invoice_number) {
                $prefix = DB::table('config')->where('config_key', 'invoice_prefix')->value('config_value');
                $transaction->invoice_prefix = $prefix;
                $transaction->invoice_number = AiCreditsTransaction::where('invoice_prefix', $prefix)->count() + 1;
            }

            $transaction->payment_status = $status;
            $transaction->save();

            if (! $user || $credits <= 0) {
                return;
            }

            $balance = AiCredit::firstOrNew(['user_id' => $user->user_id], ['credits' => 0]);

            if ((int) $balance->credits === -1) {
                return; // unlimited from the customer's plan
            }

            if ($status === 'paid') {
                $balance->credits = (int) $balance->credits + $credits;
                $balance->save();
            } elseif ($wasPaid && $balance->exists) {
                $balance->credits = max(0, (int) $balance->credits - $credits);
                $balance->save();
            }
        });

        if ($status === 'paid') {
            $this->dispatchZapier($transaction->fresh(), $user);
        }
    }

    private function dispatchZapier(AiCreditsTransaction $transaction, ?User $user): void
    {
        if (! function_exists('zapier_dispatch')) {
            return;
        }

        $invoice = json_decode($transaction->invoice_details ?? '', true) ?: [];

        zapier_dispatch('ai_credits', [
            'transaction_id'  => $transaction->ai_credits_transaction_id,
            'invoice_id'      => $transaction->invoice_prefix . $transaction->invoice_number,
            'user_name'       => $user->name ?? ($invoice['to_billing_name'] ?? 'Customer'),
            'user_address'    => $user->billing_address ?? ($invoice['to_billing_address'] ?? ''),
            'user_city'       => $user->billing_city ?? ($invoice['to_billing_city'] ?? ''),
            'user_state'      => $user->billing_state ?? ($invoice['to_billing_state'] ?? ''),
            'user_country'    => $user->billing_country ?? ($invoice['to_billing_country'] ?? ''),
            'user_zip'        => $user->billing_zipcode ?? ($invoice['to_billing_zipcode'] ?? ''),
            'user_email'      => $user->email ?? ($invoice['to_billing_email'] ?? ''),
            'user_phone'      => ($user->country_code ?? '') . ($user->mobile_number ?? ($invoice['to_billing_phone'] ?? '')),
            'user_tax_number' => $user->vat_number ?? ($invoice['to_vat_number'] ?? ''),
            'plan_name'       => $transaction->purchase_details,
            'quantity'        => 1,
            'currency'        => $transaction->currency,
            'amount'          => (float) $transaction->amount,
            'payment_method'  => $transaction->payment_method ?? 'Offline',
            'payment_status'  => 'paid',
            'invoice_date'    => now()->toDateString(),
            'purchased_at'    => now()->toDateTimeString(),
        ]);
    }
}
