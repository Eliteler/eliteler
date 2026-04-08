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
        Schema::create('service_booking_confirmeds', function (Blueprint $table) {
            $table->increments('id');
            $table->string('service_booking_confirmed_id')->unique();
            $table->string('user_id');
            $table->string('vcard_id');
            $table->string('fullname');
            $table->string('email');
            $table->string('mobile_number');
            $table->text('address');
            $table->date('checkin_date');
            $table->time('checkin_time');
            $table->date('checkout_date');
            $table->time('checkout_time');
            $table->integer('number_of_guests')->default(1);
            $table->text('notes')->nullable();
            $table->enum('service_booking_confirmed_status', ['pending', 'confirmed', 'rejected', 'completed'])->default('pending');
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
        Schema::dropIfExists('service_booking_confirmeds');
    }
};
