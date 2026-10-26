<?php

namespace App\Services\Admin;

use App\ReferralWithdrawRequest;
use App\ReferralWithdrawTransaction;
use App\User;
use Illuminate\Support\Facades\DB;

/**
 * Referral program.
 *
 * referrals.user_id is the new customer, referrals.referred_user_id the
 * referrer who earns the commission (referral_scheme.referral_amount) once the
 * new customer subscribes (is_subscribed = 1).
 * referral_withdraw_requests.payment_status: 0 pending, 1 accepted,
 * 2 transferred (paid out), -1 rejected.
 */
class ReferralService
{
    public const PENDING = 0;

    public const ACCEPTED = 1;

    public const TRANSFERRED = 2;

    public const REJECTED = -1;

    /** @return array<string, float> referrer user_id => commission earned */
    public function earnings(?array $userIds = null): array
    {
        return DB::table('referrals')
            ->where('status', 1)->where('is_registered', 1)->where('is_subscribed', 1)
            ->when($userIds, fn ($q) => $q->whereIn('referred_user_id', $userIds))
            ->selectRaw("referred_user_id, SUM(CAST(JSON_UNQUOTE(JSON_EXTRACT(referral_scheme, '$.referral_amount')) AS DECIMAL(15,2))) as total")
            ->groupBy('referred_user_id')
            ->pluck('total', 'referred_user_id')
            ->map(fn ($v) => round((float) $v, 2))
            ->all();
    }

    /** @return array<string, float> user_id => amount already paid out */
    public function paidOut(?array $userIds = null): array
    {
        return DB::table('referral_withdraw_requests')
            ->where('status', 1)->where('payment_status', self::TRANSFERRED)
            ->when($userIds, fn ($q) => $q->whereIn('user_id', $userIds))
            ->selectRaw('user_id, SUM(amount) as total')
            ->groupBy('user_id')
            ->pluck('total', 'user_id')
            ->map(fn ($v) => round((float) $v, 2))
            ->all();
    }

    /** Payout details as plain text (stored as a JSON-encoded HTML string). */
    public static function payoutDetails(?string $raw): string
    {
        $value = $raw ?? '';
        $decoded = json_decode($value, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            $value = is_string($decoded) ? $decoded : (is_array($decoded) ? implode("\n", array_map(fn ($k, $v) => "{$k}: {$v}", array_keys($decoded), $decoded)) : $value);
        }

        $text = html_entity_decode(strip_tags(preg_replace('/<(br|\/p|\/div|\/li)\s*\/?>/i', "\n", $value)));

        return trim(preg_replace("/\n{2,}/", "\n", $text));
    }

    public function setStatus(ReferralWithdrawRequest $request, int $status, ?string $transferId = null, ?string $notes = null): void
    {
        DB::transaction(function () use ($request, $status, $transferId, $notes) {
            $request->payment_status = $status;
            if ($notes !== null) {
                $request->notes = $notes;
            }
            $request->save();

            if ($status === self::TRANSFERRED) {
                $transaction                               = new ReferralWithdrawTransaction();
                $transaction->referral_withdraw_request_id = $request->referral_withdraw_request_id;
                $transaction->transfer_id                  = $transferId;
                $transaction->notes                        = $notes;
                $transaction->payment_status               = self::TRANSFERRED;
                $transaction->save();
            }
        });

        $this->dispatchZapier($request->fresh(), $status);
    }

    private function dispatchZapier(ReferralWithdrawRequest $request, int $status): void
    {
        if (! function_exists('zapier_dispatch')) {
            return;
        }

        $user = User::where('user_id', $request->user_id)->first();

        zapier_dispatch('referral_withdraw', [
            'requested_at'        => now()->toDateTimeString(),
            'withdraw_id'         => $request->referral_withdraw_request_id,
            'user_name'           => $user->name ?? 'Affiliate User',
            'user_address'        => $user->billing_address ?? '',
            'user_city'           => $user->billing_city ?? '',
            'user_state'          => $user->billing_state ?? '',
            'user_country'        => $user->billing_country ?? '',
            'user_zip'            => $user->billing_zipcode ?? '',
            'user_email'          => $user->email ?? '',
            'user_phone'          => ($user->country_code ?? '') . ($user->mobile_number ?? ''),
            'user_tax_number'     => $user->vat_number ?? '',
            'withdrawal_currency' => DB::table('config')->where('config_key', 'currency')->value('config_value') ?? 'USD',
            'amount'              => (float) $request->amount,
            'bank_details'        => self::payoutDetails($request->bank_details),
            'transfer_status'     => [self::ACCEPTED => 'Accepted', self::REJECTED => 'Rejected', self::TRANSFERRED => 'Transferred'][$status] ?? 'Processed',
        ]);
    }
}
