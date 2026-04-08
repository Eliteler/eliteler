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

namespace App\Classes;

use App\Plan;
use Illuminate\Support\Facades\Validator;

class UpdatePlan
{
    public function create($request)
    {
        // Default
        $this->result = 0;

        // Check plan type
        switch ($request->plan_type) {
            case 'VCARD':

                // Validate
                $validator = Validator::make($request->all(), [
                    'plan_id' => 'required',
                    'plan_type' => 'required',
                    'plan_name' => 'required',
                    'plan_description' => 'required',
                    'plan_price' => 'required',
                    'validity' => 'required',
                    'trial_period' => 'required',
                    'ai_credits' => 'required',
                    'no_of_vcards' => 'required',
                    'no_of_services' => 'required',
                    'no_of_vcard_products' => 'required',
                    'no_of_links' => 'required',
                    'no_of_payments' => 'required',
                    'no_of_galleries' => 'required',
                    'no_testimonials' => 'required',
                    'business_hours' => 'required',
                    'contact_form' => 'required',
                    'appointment' => 'required',
                    'service_booking' => 'required',
                    'no_of_enquires' => 'required',
                    'storage' => 'required',
                ]);

                if ($validator->fails()) {
                    return back()->with('failed', $validator->messages()->all()[0])->withInput();
                }

                // List of fields to check
                $fields = [
                    'business_hours',
                    'contact_form',
                    'custom_domain',
                    'appointment',
                    'service_booking',
                    'pwa',
                    'password_protected',
                    'advanced_settings',
                    'personalized_link',
                    'hide_branding',
                    'is_private',
                    'free_setup',
                    'free_support',
                    'recommended',
                    'nfc_card'
                ];

                // Push google wallet if it's installed
                $google_wallet = 0;
                $no_of_google_wallets = 0;
                if (is_dir(base_path('plugins/GoogleWallet'))) {
                    $google_wallet = $request->google_wallet == null || $request->google_wallet == "off" ? 0 : 1;
                    $no_of_google_wallets = $request->no_of_google_wallets;
                }

                // push mobile app plan details
                $in_app_purchase_id = null;
                if (is_dir(base_path('plugins/MobileAppAPI'))) {
                    $in_app_purchase_id = $request->in_app_purchase_id;
                }

                // push paddle plan price id
                $paddle_plan_price_id = null;
                if (is_dir(base_path('plugins/PaddleRecurring'))) {
                    $paddle_plan_price_id = $request->paddle_plan_price_id;
                }

                // push dodo payments plan price id
                $dodo_payments_plan_price_id = null;
                if (is_dir(base_path('plugins/DodoPaymentsRecurring'))) {
                    $dodo_payments_plan_price_id = $request->dodo_payments_plan_price_id;
                }

                // Initialize an empty array to store the values
                $settings = [];

                foreach ($fields as $field) {
                    // Set the value to 0 if the field is "off", else set it to 1
                    $settings[$field] = $request->$field == null || $request->$field == "off" ? 0 : 1;
                }

                // Prepare the update data
                $updateData = [
                    'plan_type' => $request->plan_type,
                    'plan_name' => ucfirst($request->plan_name),
                    'plan_description' => ucfirst($request->plan_description),
                    'recommended' => $settings['recommended'],
                    'plan_price' => $request->plan_price,
                    'validity' => $request->validity,
                    'trial' => $request->trial_period,
                    'ai_credits' => $request->ai_credits,
                    'no_of_vcards' => $request->no_of_vcards,
                    'no_of_services' => $request->no_of_services,
                    'no_of_vcard_products' => $request->no_of_vcard_products,
                    'no_of_galleries' => $request->no_of_galleries,
                    'no_of_links' => $request->no_of_links,
                    'no_of_payments' => $request->no_of_payments,
                    'no_testimonials' => $request->no_testimonials,
                    'business_hours' => $settings['business_hours'],
                    'contact_form' => $settings['contact_form'],
                    'appointment' => $settings['appointment'],
                    'service_booking' => $settings['service_booking'],
                    'custom_domain' => $settings['custom_domain'],
                    'no_of_enquires' => $request->no_of_enquires,
                    'google_wallet' => $google_wallet,
                    'no_of_google_wallets' => $no_of_google_wallets,
                    'pwa' => $settings['pwa'],
                    'password_protected' => $settings['password_protected'],
                    'advanced_settings' => $settings['advanced_settings'],
                    'storage' => $request->storage,
                    'additional_tools' => 0,
                    'personalized_link' => $settings['personalized_link'],
                    'hide_branding' => $settings['hide_branding'],
                    'free_setup' => $settings['free_setup'],
                    'free_support' => $settings['free_support'],
                    'is_private' => $settings['is_private'],
                    'nfc_card' => $settings['nfc_card'],
                    'in_app_purchase_id' => $in_app_purchase_id,
                    'paddle_plan_price_id' => $paddle_plan_price_id,
                    'dodo_payments_plan_price_id' => $dodo_payments_plan_price_id,
                ];

                // Update the plan
                Plan::where('plan_id', $request->plan_id)->update($updateData);

                return $this->result = 1;
                break;

            case 'STORE':

                // Validate
                $validator = Validator::make($request->all(), [
                    'plan_id' => 'required',
                    'plan_type' => 'required',
                    'plan_name' => 'required',
                    'plan_description' => 'required',
                    'plan_price' => 'required',
                    'validity' => 'required',
                    'trial_period' => 'required',
                    'no_of_stores' => 'required',
                    'no_of_categories' => 'required',
                    'no_of_store_products' => 'required',
                    'storage' => 'required',
                ]);

                if ($validator->fails()) {
                    return back()->with('failed', $validator->messages()->all()[0])->withInput();
                }

                // List of fields to check
                $fields = [
                    'custom_domain',
                    'pwa',
                    'advanced_settings',
                    'personalized_link',
                    'hide_branding',
                    'is_private',
                    'free_setup',
                    'free_support',
                    'recommended',
                    'nfc_card'
                ];

                // push mobile app plan details
                $in_app_purchase_id = null;
                if (is_dir(base_path('plugins/MobileAppAPI'))) {
                    $in_app_purchase_id = $request->in_app_purchase_id;
                }

                // push paddle plan price id
                $paddle_plan_price_id = null;
                if (is_dir(base_path('plugins/PaddleRecurring'))) {
                    $paddle_plan_price_id = $request->paddle_plan_price_id;
                }

                // push dodo payments plan price id
                $dodo_payments_plan_price_id = null;
                if (is_dir(base_path('plugins/DodoPaymentsRecurring'))) {
                    $dodo_payments_plan_price_id = $request->dodo_payments_plan_price_id;
                }

                // Initialize an empty array to store the values
                $settings = [];

                foreach ($fields as $field) {
                    // If the field is "off", set it to 0, else set it to 1
                    $settings[$field] = $request->$field == null || $request->$field == "off" ? 0 : 1;
                }

                // Prepare the update data
                $updateData = [
                    'plan_type' => $request->plan_type,
                    'plan_name' => ucfirst($request->plan_name),
                    'plan_description' => ucfirst($request->plan_description),
                    'recommended' => $settings['recommended'],
                    'plan_price' => $request->plan_price,
                    'validity' => $request->validity,
                    'trial' => $request->trial_period,
                    'no_of_stores' => $request->no_of_stores,
                    'no_of_categories' => $request->no_of_categories,
                    'no_of_store_products' => $request->no_of_store_products,
                    'custom_domain' => $settings['custom_domain'],
                    'pwa' => $settings['pwa'],
                    'advanced_settings' => $settings['advanced_settings'],
                    'storage' => $request->storage,
                    'additional_tools' => 0,
                    'personalized_link' => $settings['personalized_link'],
                    'hide_branding' => $settings['hide_branding'],
                    'free_setup' => $settings['free_setup'],
                    'free_support' => $settings['free_support'],
                    'is_private' => $settings['is_private'],
                    'nfc_card' => $settings['nfc_card'],
                    'in_app_purchase_id' => $in_app_purchase_id,
                    'paddle_plan_price_id' => $paddle_plan_price_id,
                    'dodo_payments_plan_price_id' => $dodo_payments_plan_price_id,
                ];

                // Update the plan
                Plan::where('plan_id', $request->plan_id)->update($updateData);

                return $this->result = 1;
                break;

            default:

                // Validate
                $validator = Validator::make($request->all(), [
                    'plan_id' => 'required',
                    'plan_type' => 'required',
                    'plan_name' => 'required',
                    'plan_description' => 'required',
                    'plan_price' => 'required',
                    'validity' => 'required',
                    'trial_period' => 'required',
                    'ai_credits' => 'required',
                    'no_of_vcards' => 'required',
                    'no_of_services' => 'required',
                    'no_of_vcard_products' => 'required',
                    'no_of_links' => 'required',
                    'no_of_payments' => 'required',
                    'no_of_galleries' => 'required',
                    'no_testimonials' => 'required',
                    'business_hours' => 'required',
                    'contact_form' => 'required',
                    'appointment' => 'required',
                    'service_booking' => 'required',
                    'no_of_enquires' => 'required',
                    'no_of_stores' => 'required',
                    'no_of_categories' => 'required',
                    'no_of_store_products' => 'required',
                    'storage' => 'required',
                ]);

                if ($validator->fails()) {
                    return back()->with('failed', $validator->messages()->all()[0])->withInput();
                }

                // List of fields to check
                $fields = [
                    'business_hours',
                    'contact_form',
                    'custom_domain',
                    'appointment',
                    'service_booking',
                    'pwa',
                    'password_protected',
                    'advanced_settings',
                    'personalized_link',
                    'hide_branding',
                    'is_private',
                    'free_setup',
                    'free_support',
                    'recommended',
                    'nfc_card'
                ];

                // Push google wallet if it's installed
                $google_wallet = 0;
                $no_of_google_wallets = 0;
                if (is_dir(base_path('plugins/GoogleWallet'))) {
                    $google_wallet = $request->google_wallet == null || $request->google_wallet == "off" ? 0 : 1;
                    $no_of_google_wallets = $request->no_of_google_wallets;
                }

                // push mobile app plan details
                $in_app_purchase_id = null;
                if (is_dir(base_path('plugins/MobileAppAPI'))) {
                    $in_app_purchase_id = $request->in_app_purchase_id;
                }

                // push paddle plan price id
                $paddle_plan_price_id = null;
                if (is_dir(base_path('plugins/PaddleRecurring'))) {
                    $paddle_plan_price_id = $request->paddle_plan_price_id;
                }

                // push dodo payments plan price id
                $dodo_payments_plan_price_id = null;
                if (is_dir(base_path('plugins/DodoPaymentsRecurring'))) {
                    $dodo_payments_plan_price_id = $request->dodo_payments_plan_price_id;
                }

                // Initialize an empty array to store the values
                $settings = [];

                // Loop through each field and check the value in the request
                foreach ($fields as $field) {
                    // If the field is "off", set it to 0, else set it to 1
                    $settings[$field] = $request->$field == null || $request->$field == "off" ? 0 : 1;
                }

                // Prepare the update data
                $updateData = [
                    'plan_type' => $request->plan_type,
                    'plan_name' => ucfirst($request->plan_name),
                    'plan_description' => ucfirst($request->plan_description),
                    'recommended' => $settings['recommended'],  // Dynamic value
                    'plan_price' => $request->plan_price,
                    'validity' => $request->validity,
                    'trial' => $request->trial_period,
                    'ai_credits' => $request->ai_credits,
                    'no_of_vcards' => $request->no_of_vcards,
                    'no_of_services' => $request->no_of_services,
                    'no_of_vcard_products' => $request->no_of_vcard_products,
                    'no_of_galleries' => $request->no_of_galleries,
                    'no_of_links' => $request->no_of_links,
                    'no_of_payments' => $request->no_of_payments,
                    'no_testimonials' => $request->no_testimonials,
                    'business_hours' => $settings['business_hours'],  // Dynamic value
                    'contact_form' => $settings['contact_form'],  // Dynamic value
                    'appointment' => $settings['appointment'],  // Dynamic value
                    'service_booking' => $settings['service_booking'],  // Dynamic value
                    'custom_domain' => $settings['custom_domain'],  // Dynamic value
                    'no_of_enquires' => $request->no_of_enquires,
                    'google_wallet' => $google_wallet,  // Dynamic value
                    'no_of_google_wallets' => $no_of_google_wallets,  // Dynamic value
                    'no_of_stores' => $request->no_of_stores,
                    'no_of_categories' => $request->no_of_categories,
                    'no_of_store_products' => $request->no_of_store_products,
                    'pwa' => $settings['pwa'],  // Dynamic value
                    'password_protected' => $settings['password_protected'],  // Dynamic value
                    'advanced_settings' => $settings['advanced_settings'],  // Dynamic value
                    'storage' => $request->storage,  // Dynamic value
                    'additional_tools' => 0,  // Dynamic value
                    'personalized_link' => $settings['personalized_link'],  // Dynamic value
                    'hide_branding' => $settings['hide_branding'],  // Dynamic value
                    'free_setup' => $settings['free_setup'],  // Dynamic value
                    'free_support' => $settings['free_support'],  // Dynamic value
                    'is_private' => $settings['is_private'],  // Dynamic value
                    'nfc_card' => $settings['nfc_card'],  // Dynamic value
                    'in_app_purchase_id' => $in_app_purchase_id,
                    'paddle_plan_price_id' => $paddle_plan_price_id,
                    'dodo_payments_plan_price_id' => $dodo_payments_plan_price_id,
                ];

                // Update the plan
                Plan::where('plan_id', $request->plan_id)->update($updateData);

                return $this->result = 1;
                break;
        }
    }
}
