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
        Schema::create('ai_credits_plans', function (Blueprint $table) {
            $table->increments('id');
            $table->string('ai_credits_plan_id');
            $table->string('plan_name');
            $table->text('plan_description');
            $table->decimal('plan_price', 15, 2);
            $table->integer('no_of_ai_credits')->default(0);
            $table->enum('status', ['active', 'inactive', 'deleted'])->default('active');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_credits_plans');
    }
};
