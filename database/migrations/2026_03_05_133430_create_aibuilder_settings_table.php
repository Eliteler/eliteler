<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations. 
     */
    public function up(): void
    {
        Schema::create('aibuilder_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('aibuilder')->default(0);
            $table->string('provider')->default('openai');
            $table->string('model')->default('gpt-5.2');
            $table->boolean('generate_image')->default(1);
            $table->string('key_1')->nullable();
            $table->string('key_2')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aibuilder_settings');
    }
};
