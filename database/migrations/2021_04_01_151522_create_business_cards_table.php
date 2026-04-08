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

class CreateBusinessCardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('business_cards', function (Blueprint $table) {
            $table->increments('id');
            $table->string('card_id');
            $table->string('user_id');
            $table->string('type')->default('business');
            $table->string('theme_id')->nullable();
            $table->string('card_lang')->default('EN');
            $table->string('cover')->nullable();
            $table->string('cover_type')->default('photo');
            $table->string('profile')->nullable();
            $table->string('card_url');
            $table->text('custom_domain')->nullable();
            $table->string('card_type');
            $table->string('title');
            $table->longText('sub_title');
            $table->longText('description')->nullable();
            $table->text('enquiry_email')->nullable();
            $table->text('appointment_receive_email')->nullable();
            $table->boolean('is_newsletter_pop_active')->default(false);
            $table->boolean('is_info_pop_active')->default(false);
            $table->boolean('is_pwa_enabled')->default(0);
            $table->json('custom_styles')->nullable();
            $table->text('custom_css')->nullable();
            $table->text('custom_js')->nullable();
            $table->text('password')->nullable();
            $table->timestamp('expiry_time')->nullable();
            $table->text('delivery_options')->nullable();
            $table->text('seo_configurations')->nullable();
            $table->boolean('directory_listing')->default(1);
            $table->string('card_status')->default('activated');
            $table->string('status')->default(1);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('business_cards');
    }
}
