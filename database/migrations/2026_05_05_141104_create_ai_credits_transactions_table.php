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
        Schema::create('ai_credits_transactions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('ai_credits_transaction_id');
            $table->string('ai_credits_order_id');
            $table->string('payment_transaction_id')->nullable();
            $table->string('user_id');
            $table->string('ai_credits_plan_id');
            $table->text('purchase_details');
            $table->string('payment_method');
            $table->string('currency');
            $table->decimal('amount', 15, 2);
            $table->string('invoice_number')->nullable();
            $table->string('invoice_prefix')->nullable();
            $table->longText('invoice_details')->nullable();
            $table->enum('payment_status', ['pending', 'processing', 'paid', 'failed', 'cancelled', 'refunded', 'partially_refunded'])->default('pending');
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
        Schema::dropIfExists('ai_credits_transactions');
    }
};
