<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('business_cards', function (Blueprint $table) {
            if (! Schema::hasColumn('business_cards', 'title_align')) {
                $table->string('title_align', 10)->nullable()->after('subtitle2');
            }
            if (! Schema::hasColumn('business_cards', 'title2_align')) {
                $table->string('title2_align', 10)->nullable()->after('title_align');
            }
        });
    }

    public function down(): void
    {
        Schema::table('business_cards', function (Blueprint $table) {
            $table->dropColumn(['title_align', 'title2_align']);
        });
    }
};
