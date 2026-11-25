<?php

use App\Support\ReferralCurrencySchema;
use Illuminate\Database\Migrations\Migration;

/**
 * Multi-currency history: referral_withdraw_requests.currency, the currency
 * of existing referral earnings / requests, and payment rows that saved a
 * config key instead of a currency code.
 * Raw SQL for manual updates: referral_currency_migration.sql (project root).
 */
return new class extends Migration
{
    public function up(): void
    {
        ReferralCurrencySchema::up();
    }

    public function down(): void
    {
        // Additive; not rolled back automatically.
    }
};
