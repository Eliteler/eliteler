<?php

use App\Support\SmartProducts\SmartProductsSchema;
use Illuminate\Database\Migrations\Migration;

/**
 * Smart Products, Phase 3: nfc_card_keys.user_id (key owner). Runs the same
 * guarded schema routine again, so it only adds what is missing.
 * Raw SQL for manual updates: smart_products_migration.sql (project root).
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
