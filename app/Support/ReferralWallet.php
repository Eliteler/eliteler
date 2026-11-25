<?php

namespace App\Support;

use App\Referral;
use App\ReferralWithdrawRequest;

/**
 * A referrer's wallet, shared by the Blade panel, the React panel and the
 * Mobile App API.
 *
 * Only amounts in the current base currency count (see BaseCurrency):
 * earnings and withdrawals from before a base currency change stay in the
 * history with their own currency but aren't added to or taken from the
 * balance, since there are no exchange rates.
 */
class ReferralWallet
{
    /** Commission earned: subscribed referrals. */
    public static function earned(string $userId): float
    {
        $earned = 0.0;

        $earnings = BaseCurrency::only(Referral::query(), BaseCurrency::REFERRAL_CURRENCY)
            ->where('is_registered', 1)->where('is_subscribed', 1)->where('referred_user_id', $userId)
            ->get(['referral_scheme']);

        foreach ($earnings as $earning) {
            $scheme = ! empty($earning->referral_scheme) ? json_decode($earning->referral_scheme) : null;
            $earned += (float) ($scheme->referral_amount ?? 0);
        }

        return round($earned, 2);
    }

    /** Withdrawals already paid out (transferred). */
    public static function transferred(string $userId): float
    {
        return round((float) BaseCurrency::only(ReferralWithdrawRequest::query(), 'currency')
            ->where('payment_status', 2)->where('user_id', $userId)->sum('amount'), 2);
    }

    /** Withdrawals asked for that aren't rejected (pending, accepted, transferred). */
    public static function requested(string $userId): float
    {
        return round((float) BaseCurrency::only(ReferralWithdrawRequest::query(), 'currency')
            ->where('payment_status', '!=', '-1')->where('user_id', $userId)->sum('amount'), 2);
    }

    /** What can still be withdrawn: earnings minus requests that aren't rejected. */
    public static function available(string $userId): float
    {
        return round(self::earned($userId) - self::requested($userId), 2);
    }

    /** Currency of a referral earning (scheme JSON string, array or object). */
    public static function earningCurrency(mixed $scheme): ?string
    {
        if (is_string($scheme)) {
            $scheme = json_decode($scheme, true);
        }

        $code = is_array($scheme) ? ($scheme['currency'] ?? null) : ($scheme->currency ?? null);

        return $code ? (string) $code : null;
    }
}
