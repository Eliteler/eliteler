<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('business_cards', function (Blueprint $table) {
            foreach (['title_font_size', 'subtitle_font_size', 'title2_font_size', 'subtitle2_font_size'] as $column) {
                if (! Schema::hasColumn('business_cards', $column)) {
                    $table->unsignedSmallInteger($column)->nullable()->after('title2_align');
                }
            }
            if (! Schema::hasColumn('business_cards', 'title2_font')) {
                $table->string('title2_font', 100)->nullable()->after('subtitle2_font_size');
            }
        });
    }

    public function down(): void
    {
        Schema::table('business_cards', function (Blueprint $table) {
            $table->dropColumn(['title_font_size', 'subtitle_font_size', 'title2_font_size', 'subtitle2_font_size', 'title2_font']);
        });
    }
};
