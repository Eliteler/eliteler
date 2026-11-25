<?php

use App\Support\SmartProducts\SmartProductsSchema;
use Illuminate\Database\Migrations\Migration;

/**
 * NFC Writer: nfc_card_orders.is_locked (card made read-only when written).
 * Runs the same guarded schema routine again, so it only adds what is missing.
 * Raw SQL for manual updates: nfc_order_is_locked_migration.sql (project root).
 */
return new class extends Migration
{
    public function up(): void
    {
        SmartProductsSchema::up();
    }

    public function down(): void
    {
        // Additive; not rolled back automatically.
    }
};
