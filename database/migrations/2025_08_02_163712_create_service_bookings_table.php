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
        Schema::create('service_bookings', function (Blueprint $table) {
            $table->increments('id');
            $table->string('service_booking_id')->unique();
            $table->string('user_id');
            $table->string('vcard_id');
            $table->boolean('service_booking')->default(0);
            $table->text('service_booking_available_days');
            $table->double('service_booking_amount', 15, 2)->default(0);
            $table->time('service_booking_start_time');
            $table->time('service_booking_end_time');
            $table->string('service_booking_receive_email');
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
        Schema::dropIfExists('service_bookings');
    }
};
