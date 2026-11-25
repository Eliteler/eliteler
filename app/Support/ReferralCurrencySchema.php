<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Multi-currency history: referral currency + currency code clean-up.
 *
 * Same steps as referral_currency_migration.sql (project root). Every step
 * checks first, so it is safe to run more than once. Used by the migration
 * 2026_11_22_000000_add_referral_currency.php and GoBizUpdater2910.
 *
 * Self-contained on purpose: the updater loads this file from the staging
 * folder before the new app files are in place.
 */
class ReferralCurrencySchema
{
    public static function up(): void
    {
        if (! Schema::hasTable('config')) {
            return;
        }

        $base = strtoupper((string) (DB::table('config')->where('config_key', 'currency')->value('config_value') ?: 'USD'));

        // 1. Withdrawal requests record the currency they were made in
        if (Schema::hasTable('referral_withdraw_requests') && ! Schema::hasColumn('referral_withdraw_requests', 'currency')) {
            Schema::table('referral_withdraw_requests', function ($table) {
                $table->string('currency', 10)->nullable()->after('amount');
            });
        }

        // 2. Existing requests and earnings were made in today's base currency
        if (Schema::hasTable('referral_withdraw_requests') && Schema::hasColumn('referral_withdraw_requests', 'currency')) {
            DB::table('referral_withdraw_requests')
                ->where(fn ($q) => $q->whereNull('currency')->orWhere('currency', ''))
                ->update(['currency' => $base]);
        }

        if (Schema::hasTable('referrals')) {
            DB::table('referrals')
                ->whereRaw('JSON_VALID(referral_scheme)')
                ->whereRaw("JSON_TYPE(referral_scheme) = 'OBJECT'")
                ->whereRaw("(JSON_EXTRACT(referral_scheme, '$.currency') IS NULL OR JSON_UNQUOTE(JSON_EXTRACT(referral_scheme, '$.currency')) = '')")
                ->update(['referral_scheme' => DB::raw("JSON_SET(referral_scheme, '$.currency', " . DB::getPdo()->quote($base) . ')')]);
        }

        // 3. Some older payments saved a config key (e.g. "enable_webtools")
        //    instead of a currency code: they were made in the base currency
        //    (a list of values, not a subquery: tables may use different collations)
        $configKeys = DB::table('config')->pluck('config_key')->filter()->values()->all();

        foreach (['transactions' => 'transaction_currency', 'nfc_card_order_transactions' => 'currency', 'ai_credits_transactions' => 'currency'] as $table => $column) {
            if ($configKeys && Schema::hasTable($table) && Schema::hasColumn($table, $column)) {
                DB::table($table)
                    ->whereIn($column, $configKeys)
                    ->update([$column => $base]);
            }
        }
    }
}
