<?php

use App\Support\SmartProducts\SmartProductsSchema;
use Illuminate\Database\Migrations\Migration;

/**
 * Smart Products (NFC module evolution). All steps are guarded, so this is
 * safe on installs that already have some of these columns.
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
        // Additive and data-bearing; not rolled back automatically.
    }
};
