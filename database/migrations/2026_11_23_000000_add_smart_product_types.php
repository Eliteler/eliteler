<?php

use App\Support\SmartProducts\SmartProductsSchema;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        SmartProductsSchema::productTypes();
    }

    public function down(): void
    {
        // Keep administrator-managed types and product assignments on rollback.
    }
};
