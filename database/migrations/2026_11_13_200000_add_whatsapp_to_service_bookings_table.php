<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('service_bookings', 'service_booking_whatsapp')) {
            Schema::table('service_bookings', function (Blueprint $table) {
                $table->string('service_booking_whatsapp', 20)->nullable()->after('service_booking_receive_email');
            });
        }
    }

    public function down(): void
    {
        Schema::table('service_bookings', function (Blueprint $table) {
            $table->dropColumn('service_booking_whatsapp');
        });
    }
};
