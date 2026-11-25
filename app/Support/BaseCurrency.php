<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The admin's base currency and the "paid in the base currency" rule.
 *
 * Every payment keeps the currency it was made in; nothing is converted.
 * Totals and balances only add up amounts in the current base currency. A
 * row with an empty or unknown currency code counts as base, the same as
 * formatMoney() in app/helpers.php.
 *
 * Referral earnings (referrals.referral_scheme JSON) and withdrawal requests
 * may have no currency recorded: those are in the current base currency.
 * stampReferrals() keeps that true when the base currency changes.
 */
class BaseCurrency
{
    /** Currency of a referral earning, stored inside referrals.referral_scheme. */
    public const REFERRAL_CURRENCY = "JSON_UNQUOTE(JSON_EXTRACT(referral_scheme, '$.currency'))";

    /** Current base currency (ISO code, upper case). */
    public static function code(): string
    {
        return strtoupper((string) (DB::table('config')->where('config_key', 'currency')->value('config_value') ?: 'USD'));
    }

    /**
     * SQL condition for "in the base currency" on a column or expression.
     * Self-contained (no bindings): the base code and the known ISO codes are
     * inlined as quoted literals. Comparing with literals instead of a
     * subquery on `currencies` avoids "Illegal mix of collations" errors
     * when tables use different collations (common on older installs).
     */
    public static function sql(string $column): string
    {
        $pdo   = DB::getPdo();
        $known = implode(', ', array_map(fn ($code) => $pdo->quote($code), self::knownCodes()));

        return "(UPPER({$column}) = " . $pdo->quote(self::code()) . " OR {$column} IS NULL OR {$column} = ''"
            . ($known !== '' ? " OR UPPER({$column}) NOT IN ({$known})" : '') . ')';
    }

    /** @return array<int, string> every ISO code in `currencies` (disabled ones too), upper case */
    public static function knownCodes(): array
    {
        static $codes;

        return $codes ??= DB::table('currencies')->whereNotNull('iso_code')->pluck('iso_code')
            ->map(fn ($code) => strtoupper((string) $code))->unique()->values()->all();
    }

    /**
     * Limit a query (Eloquent or query builder) to rows in the base currency.
     *
     * @template T
     * @param  T  $query
     * @return T
     */
    public static function only($query, string $column)
    {
        return $query->whereRaw(self::sql($column));
    }

    /** Is this code the base currency (empty and unknown codes count as base)? */
    public static function is(?string $code): bool
    {
        $code = strtoupper(trim((string) $code));

        return $code === '' || $code === self::code() || ! DB::table('currencies')->where('iso_code', $code)->exists();
    }

    /**
     * Call BEFORE the base currency changes, with the outgoing code: referral
     * earnings and withdrawal requests that don't record a currency were made
     * in it, so they get it written down and keep it after the switch.
     */
    public static function stampReferrals(string $outgoing): void
    {
        $outgoing = strtoupper(trim($outgoing));

        if ($outgoing === '') {
            return;
        }

        if (Schema::hasTable('referrals')) {
            DB::table('referrals')
                ->whereRaw('JSON_VALID(referral_scheme)')
                ->whereRaw("JSON_TYPE(referral_scheme) = 'OBJECT'")
                ->whereRaw('(' . self::REFERRAL_CURRENCY . " IS NULL OR " . self::REFERRAL_CURRENCY . " = '')")
                ->update(['referral_scheme' => DB::raw("JSON_SET(referral_scheme, '$.currency', " . DB::getPdo()->quote($outgoing) . ')')]);
        }

        if (Schema::hasColumn('referral_withdraw_requests', 'currency')) {
            DB::table('referral_withdraw_requests')
                ->where(fn ($q) => $q->whereNull('currency')->orWhere('currency', ''))
                ->update(['currency' => $outgoing]);
        }
    }
}
