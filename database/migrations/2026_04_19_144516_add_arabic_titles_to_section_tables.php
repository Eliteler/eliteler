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
        Schema::table('business_fields', function (Blueprint $table) { clone $table; if (!Schema::hasColumn('business_fields', 'title_ar')) $table->string('title_ar')->nullable(); });
        Schema::table('payments', function (Blueprint $table) { clone $table; if (!Schema::hasColumn('payments', 'title_ar')) $table->string('title_ar')->nullable(); });
        Schema::table('services', function (Blueprint $table) { clone $table; if (!Schema::hasColumn('services', 'title_ar')) $table->string('title_ar')->nullable(); });
        Schema::table('vcard_products', function (Blueprint $table) { clone $table; if (!Schema::hasColumn('vcard_products', 'title_ar')) $table->string('title_ar')->nullable(); });
        Schema::table('galleries', function (Blueprint $table) { clone $table; if (!Schema::hasColumn('galleries', 'title_ar')) $table->string('title_ar')->nullable(); });
        Schema::table('testimonials', function (Blueprint $table) { clone $table; if (!Schema::hasColumn('testimonials', 'title_ar')) $table->string('title_ar')->nullable(); });
        Schema::table('business_hours', function (Blueprint $table) { clone $table; if (!Schema::hasColumn('business_hours', 'title_ar')) $table->string('title_ar')->nullable(); });
        Schema::table('card_appointment_times', function (Blueprint $table) { clone $table; if (!Schema::hasColumn('card_appointment_times', 'title_ar')) $table->string('title_ar')->nullable(); });
        Schema::table('business_cards', function (Blueprint $table) { clone $table; if (!Schema::hasColumn('business_cards', 'contact_form_title_ar')) $table->string('contact_form_title_ar')->nullable(); });
        Schema::table('service_bookings', function (Blueprint $table) { clone $table; if (!Schema::hasColumn('service_bookings', 'title_ar')) $table->string('title_ar')->nullable(); });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('business_fields', function (Blueprint $table) { if (Schema::hasColumn('business_fields', 'title_ar')) $table->dropColumn('title_ar'); });
        Schema::table('payments', function (Blueprint $table) { if (Schema::hasColumn('payments', 'title_ar')) $table->dropColumn('title_ar'); });
        Schema::table('services', function (Blueprint $table) { if (Schema::hasColumn('services', 'title_ar')) $table->dropColumn('title_ar'); });
        Schema::table('vcard_products', function (Blueprint $table) { if (Schema::hasColumn('vcard_products', 'title_ar')) $table->dropColumn('title_ar'); });
        Schema::table('galleries', function (Blueprint $table) { if (Schema::hasColumn('galleries', 'title_ar')) $table->dropColumn('title_ar'); });
        Schema::table('testimonials', function (Blueprint $table) { if (Schema::hasColumn('testimonials', 'title_ar')) $table->dropColumn('title_ar'); });
        Schema::table('business_hours', function (Blueprint $table) { if (Schema::hasColumn('business_hours', 'title_ar')) $table->dropColumn('title_ar'); });
        Schema::table('card_appointment_times', function (Blueprint $table) { if (Schema::hasColumn('card_appointment_times', 'title_ar')) $table->dropColumn('title_ar'); });
        Schema::table('business_cards', function (Blueprint $table) { if (Schema::hasColumn('business_cards', 'contact_form_title_ar')) $table->dropColumn('contact_form_title_ar'); });
        Schema::table('service_bookings', function (Blueprint $table) { if (Schema::hasColumn('service_bookings', 'title_ar')) $table->dropColumn('title_ar'); });
    }
};
