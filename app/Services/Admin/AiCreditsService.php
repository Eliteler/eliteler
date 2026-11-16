<?php

namespace App\Services\Admin;

use App\AiCredit;
use App\AiCreditsPlan;
use App\AiCreditsTransaction;
use App\Classes\InvoiceNumber;
use App\User;
use App\Support\Zapier;
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
                $transaction->invoice_number = InvoiceNumber::next('ai_credits_transactions', $prefix);
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
        Zapier::aiCredits($user, [
            'transaction_id' => $transaction->ai_credits_transaction_id,
            'invoice_id'     => $transaction->invoice_prefix . $transaction->invoice_number,
            'plan_name'      => $transaction->purchase_details,
            'currency'       => $transaction->currency,
            'amount'         => $transaction->amount,
            'payment_method' => $transaction->payment_method ?? 'Offline',
        ], json_decode($transaction->invoice_details ?? '', true) ?: []);
    }
}
