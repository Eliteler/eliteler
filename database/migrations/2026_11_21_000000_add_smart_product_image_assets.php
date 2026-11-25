<?php

use App\Support\SmartProducts\SmartProductsSchema;
use Illuminate\Database\Migrations\Migration;

/**
 * Smart Products: image assets (nfc_card_designs.image_assets and the
 * nfc_card_order_assets table). Runs the guarded schema routine again, so
 * it only adds what is missing. Raw SQL: smart_products_migration.sql.
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
