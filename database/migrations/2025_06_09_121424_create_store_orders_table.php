<?php

/*
 |--------------------------------------------------------------------------
 | GoBiz vCard SaaS
 |--------------------------------------------------------------------------
 | Developed by NativeCode © 2021 - https://nativecode.in
 | All rights reserved
 | Unauthorized distribution is prohibited
 |--------------------------------------------------------------------------
*/

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
        Schema::create('store_orders', function (Blueprint $table) {
            $table->id();
            $table->string('user_id');
            $table->string('store_id');
            $table->string('order_number');
            $table->text('order_item');
            $table->enum('delivery_method', ['order for delivery', 'take away', 'dine in'])->default('order for delivery');
            $table->text('delivery_details');
            $table->string('payment_method')->default('cash');
            $table->string('payment_trans_id')->nullable();
            $table->double('order_total', 15, 2);
            $table->string('invoice_prefix')->nullable();
            $table->string('invoice_number')->nullable();
            $table->text('invoice_details')->nullable();
            $table->enum('payment_status', ['pending','processing','paid','failed','cancelled','refunded','partially_refunded'])->default('pending');
            $table->enum('order_status', ['pending', 'processing', 'shipped', 'out for delivery', 'delivered', 'cancelled', 'failed'])->default('pending');
            $table->string('order_notes')->nullable();
            $table->string('status')->default(1);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('store_orders');
    }
};
