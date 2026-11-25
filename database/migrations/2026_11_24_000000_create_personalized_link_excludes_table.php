<?php

use Database\Seeders\PersonalizedLinkExcludeSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('personalized_link_excludes')) {
            Schema::create('personalized_link_excludes', function (Blueprint $table) {
                $table->id();
                $table->string('keyword')->unique();
                $table->boolean('is_default')->default(false);
                $table->timestamps();
            });
        }

        // Seed existing installations even when the new seeder is absent from Composer's classmap.
        require_once database_path('seeds/PersonalizedLinkExcludeSeeder.php');

        (new PersonalizedLinkExcludeSeeder())->run();
    }

    public function down(): void
    {
        Schema::dropIfExists('personalized_link_excludes');
    }
};
