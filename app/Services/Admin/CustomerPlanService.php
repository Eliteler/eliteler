<?php

namespace App\Services\Admin;

use App\BusinessCard;
use App\Plan;
use App\Referral;
use App\Transaction;
use App\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

/**
 * Plans assigned to customers by an admin.
 *
 * An admin assignment is recorded like an offline payment: a SUCCESS
 * transaction with gateway "Offline" and an invoice built from the
 * invoice/tax settings in the config table.
 */
class CustomerPlanService
{
    /**
     * Give a customer a plan.
     *
     * - No current plan: valid from now for the plan's validity.
     * - Same plan, not expired: validity is extended from the current end date.
     * - Same plan, expired: valid from now.
     * - Different plan: valid from now, and all the customer's business cards
     *   are set to inactive (the customer re-activates them within the new limits).
     *
     * @return array{validUntil: Carbon, cardsDeactivated: bool}
     */
    public function assign(User $user, Plan $plan): array
    {
        $config = $this->config();

        return DB::transaction(function () use ($user, $plan, $config) {
            $result = $this->activatePlan($user, $plan, (float) $plan->plan_price * (1 + (float) $config->get('tax_value', 0) / 100));

            $transaction = $this->recordPayment($user, $plan, $config, [
                'to_billing_name'    => $user->billing_name,
                'to_billing_address' => $user->billing_address,
                'to_billing_city'    => $user->billing_city,
                'to_billing_state'   => $user->billing_state,
                'to_billing_zipcode' => $user->billing_zipcode,
                'to_billing_country' => $user->billing_country,
                'to_billing_phone'   => $user->billing_country_code . $user->billing_phone,
                'to_billing_email'   => $user->billing_email,
                'to_vat_number'      => $user->vat_number,
            ]);

            $this->dispatchZapier($user, $plan, $config, $transaction);
            $this->emailInvoice($user, $plan, $config, $transaction);

            return $result;
        });
    }

    /**
     * Confirm a pending/failed plan payment (e.g. an offline bank transfer):
     * gives it an invoice number, activates the plan, redeems the coupon and
     * emails the invoice.
     *
     * @return array{validUntil: Carbon, cardsDeactivated: bool}|null null when the plan no longer exists
     */
    public function confirmPayment(Transaction $transaction): ?array
    {
        $user = User::find($transaction->user_id);
        $plan = Plan::where('plan_id', $transaction->plan_id)->first();

        if (! $user || ! $plan) {
            return null;
        }

        $config = $this->config();

        return DB::transaction(function () use ($transaction, $user, $plan, $config) {
            $result = $this->activatePlan($user, $plan, (float) $transaction->transaction_amount);

            if (! $transaction->invoice_number) {
                $prefix = $config->get('invoice_prefix');
                $transaction->invoice_prefix = $prefix;
                $transaction->invoice_number = Transaction::where('invoice_prefix', $prefix)->count() + 1;
            }
            $transaction->payment_status = 'SUCCESS';
            $transaction->save();

            \App\AppliedCoupon::where('transaction_id', $transaction->transaction_id)->update(['status' => 1]);

            $this->dispatchZapier($user, $plan, $config, $transaction);
            $this->emailInvoice($user, $plan, $config, $transaction);

            return $result;
        });
    }

    /**
     * Put a customer on a plan (validity, cards, referral) without any payment record.
     *
     * @return array{validUntil: Carbon, cardsDeactivated: bool}
     */
    private function activatePlan(User $user, Plan $plan, float $amountPaid): array
    {
        $config = $this->config();
        $days   = (int) $plan->validity;

        $hadPlan  = ! empty($user->plan_validity);
        $samePlan = $hadPlan && $user->plan_id === $plan->plan_id;
        $current  = $hadPlan ? Carbon::parse($user->plan_validity) : null;

        $validUntil = $samePlan && $current->isFuture()
            ? $current->copy()->addDays($days)
            : Carbon::now()->addDays($days);

        $cardsDeactivated = $hadPlan && ! $samePlan;

        $this->markReferralSubscribed($user, $amountPaid, $config);

        if ($cardsDeactivated) {
            BusinessCard::where('user_id', $user->user_id)->update(['card_status' => 'inactive']);
        }

        $user->plan_id              = $plan->plan_id;
        $user->term                 = $days;
        $user->plan_validity        = $validUntil;
        $user->plan_activation_date = now();
        $user->plan_details         = $plan;
        $user->save();

        return compact('validUntil', 'cardsDeactivated');
    }

    /**
     * Plan fields for a customer created by an admin (no invoice email).
     */
    public function applyToNewCustomer(User $user, Plan $plan): void
    {
        $user->plan_id              = $plan->plan_id;
        $user->term                 = $plan->validity;
        $user->plan_validity        = Carbon::now()->addDays((int) $plan->validity);
        $user->plan_details         = json_encode($plan);
        $user->plan_activation_date = now();
    }

    /**
     * Invoice transaction for a customer created with a plan.
     */
    public function recordNewCustomerPayment(User $user, Plan $plan): Transaction
    {
        return $this->recordPayment($user, $plan, $this->config(), [
            'to_billing_name'  => $user->name,
            'to_billing_email' => $user->email,
        ]);
    }

    private function recordPayment(User $user, Plan $plan, Collection $config, array $billTo): Transaction
    {
        $taxRate   = (float) $config->get('tax_value', 0);
        $price     = (float) $plan->plan_price;
        $taxAmount = $price * $taxRate / 100;
        $prefix    = $config->get('invoice_prefix');

        $invoiceDetails = [
            'from_billing_name'    => $config->get('invoice_name'),
            'from_billing_address' => $config->get('invoice_address'),
            'from_billing_city'    => $config->get('invoice_city'),
            'from_billing_state'   => $config->get('invoice_state'),
            'from_billing_zipcode' => $config->get('invoice_zipcode'),
            'from_billing_country' => $config->get('invoice_country'),
            'from_vat_number'      => $config->get('tax_number'),
            'from_billing_phone'   => $config->get('invoice_phone'),
            'from_billing_email'   => $config->get('invoice_email'),
            ...$billTo,
            'tax_name'             => $config->get('tax_name'),
            'tax_type'             => $config->get('tax_type'),
            'tax_value'            => $config->get('tax_value'),
            'invoice_amount'       => $price + $taxAmount,
            'subtotal'             => $plan->plan_price,
            'tax_amount'           => $taxAmount,
        ];

        $transaction                       = new Transaction();
        $transaction->gobiz_transaction_id = uniqid();
        $transaction->transaction_date     = now();
        $transaction->transaction_id       = '';
        $transaction->user_id              = $user->id;
        $transaction->plan_id              = $plan->plan_id;
        $transaction->desciption           = $plan->plan_name . ' Plan';
        $transaction->payment_gateway_name = 'Offline';
        $transaction->transaction_amount   = $price + $taxAmount;
        $transaction->invoice_prefix       = $prefix;
        $transaction->invoice_number       = Transaction::where('invoice_prefix', $prefix)->count() + 1;
        $transaction->transaction_currency = $config->get('currency');
        $transaction->invoice_details      = json_encode($invoiceDetails);
        $transaction->payment_status       = 'SUCCESS';
        $transaction->save();

        return $transaction;
    }

    private function markReferralSubscribed(User $user, float $amount, Collection $config): void
    {
        if ($config->get('referral_system') != '1') {
            return;
        }
        $type   = $config->get('referral_commission_type');
        $value  = $config->get('referral_commission_amount');

        $scheme = [
            'referral_type'   => $type,
            'referral_value'  => $value,
            // type 0 = percentage of the amount paid, otherwise a fixed amount
            'referral_amount' => $type == '0' ? ($amount * (float) $value) / 100 : $value,
        ];

        // referrals.user_id is the referred customer
        Referral::where('user_id', $user->user_id)->where('is_subscribed', 0)->update([
            'is_subscribed'   => 1,
            'referral_scheme' => json_encode($scheme),
            'updated_at'      => now(),
        ]);
    }

    private function dispatchZapier(User $user, Plan $plan, Collection $config, Transaction $transaction): void
    {
        if (! function_exists('zapier_dispatch')) {
            return;
        }

        zapier_dispatch('plan_purchase', [
            'transaction_id'  => $transaction->gobiz_transaction_id,
            'invoice_id'      => ($transaction->invoice_prefix ?? 'INV-') . $transaction->invoice_number,
            'user_name'       => $user->name ?? $user->billing_name ?? 'Customer',
            'user_address'    => $user->billing_address ?? '',
            'user_city'       => $user->billing_city ?? '',
            'user_state'      => $user->billing_state ?? '',
            'user_country'    => $user->billing_country ?? '',
            'user_zip'        => $user->billing_zipcode ?? '',
            'user_email'      => $user->email ?? '',
            'user_phone'      => ($user->billing_country_code ?? $user->country_code ?? '') . ($user->billing_phone ?? $user->mobile_number ?? ''),
            'user_tax_number' => $user->vat_number ?? '',
            'plan_name'       => $plan->plan_name,
            'quantity'        => 1,
            'currency'        => $config->get('currency', 'USD'),
            'amount'          => (float) $transaction->transaction_amount,
            'payment_method'  => 'Offline (Admin Upgrade)',
            'payment_status'  => 'paid',
            'invoice_date'    => now()->toDateString(),
            'purchased_at'    => now()->toDateTimeString(),
        ]);
    }

    private function emailInvoice(User $user, Plan $plan, Collection $config, Transaction $transaction): void
    {
        $invoice = json_decode($transaction->invoice_details, true);

        $details = [
            'from_billing_name'    => $config->get('invoice_name'),
            'from_billing_email'   => $config->get('invoice_email'),
            'from_billing_address' => $config->get('invoice_address'),
            'from_billing_city'    => $config->get('invoice_city'),
            'from_billing_state'   => $config->get('invoice_state'),
            'from_billing_country' => $config->get('invoice_country'),
            'from_billing_zipcode' => $config->get('invoice_zipcode'),
            'gobiz_transaction_id' => $transaction->gobiz_transaction_id,
            'to_billing_name'      => $user->billing_name,
            'invoice_currency'     => $config->get('currency'),
            'subtotal'             => round((float) $invoice['subtotal'], 2),
            'tax_amount'           => round((float) $invoice['tax_amount'], 2),
            'applied_coupon'       => null,
            'discounted_price'     => 0,
            'invoice_amount'       => round((float) $invoice['invoice_amount'], 2),
            'invoice_id'           => $transaction->invoice_prefix . $transaction->invoice_number,
            'invoice_date'         => Carbon::now(),
            'description'          => $plan->plan_name . ' plan Upgrade',
            'email_heading'        => $config->get('email_heading'),
            'email_footer'         => $config->get('email_footer'),
        ];

        try {
            Mail::to($user->email)->send(new \App\Mail\SendEmailInvoice($details));
        } catch (\Throwable $e) {
            report($e);
        }
    }

    private function config(): Collection
    {
        return DB::table('config')->pluck('config_value', 'config_key');
    }
}
