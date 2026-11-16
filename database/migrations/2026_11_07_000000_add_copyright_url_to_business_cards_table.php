<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('business_cards', 'copyright_url')) {
            Schema::table('business_cards', function (Blueprint $table) {
                $table->string('copyright_url')->nullable()->after('copyright');
            });
        }
    }

    public function down(): void
    {
        Schema::table('business_cards', function (Blueprint $table) {
            $table->dropColumn('copyright_url');
        });
    }
};
