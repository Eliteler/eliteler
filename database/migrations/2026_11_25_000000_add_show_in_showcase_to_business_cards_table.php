<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('business_cards', 'show_in_showcase')) {
            Schema::table('business_cards', function (Blueprint $table) {
                $table->boolean('show_in_showcase')->default(false);
            });
        }
    }

    public function down(): void
    {
        Schema::table('business_cards', function (Blueprint $table) {
            $table->dropColumn('show_in_showcase');
        });
    }
};
