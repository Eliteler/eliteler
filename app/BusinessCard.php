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

namespace App;

use Illuminate\Database\Eloquent\Model;

class BusinessCard extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'card_id', 'user_id', 'type', 'theme_id', 'card_lang', 'cover', 'cover_type', 'profile', 'card_url', 'custom_domain', 'card_type', 'title', 'sub_title', 'title2', 'subtitle2', 'description', 'copyright', 'contact_form_title', 'enquiry_email', 'appointment_receive_email', 'is_newsletter_pop_active', 'is_info_pop_active', 'is_google_wallet_hidden', 'is_enable_pwa', 'is_enable_language_switcher', 'custom_styles', 'custom_css', 'custom_js', 'password', 'expiry_time', 'delivery_options', 'seo_configurations', 'privacy_policy', 'terms_and_conditions', 'refund_policy', 'shipping_policy', 'cookie_policy', 'customer_support_policy', 'directory_listing', 'intro_screen', 'card_status', 'status', 'contact_form_title_ar', 'email_from_name', 'email_from_address'
    ];

    public function business_card_details()
    {
        return $this->hasMany(BusinessCardDetail::class, '', 'card_id');
    }
}
