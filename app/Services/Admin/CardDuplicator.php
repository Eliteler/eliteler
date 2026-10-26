<?php

namespace App\Services\Admin;

use App\BusinessCard;
use App\BusinessField;
use App\BusinessHour;
use App\CardAppointmentTime;
use App\Gallery;
use App\InformationPop;
use App\Newsletter;
use App\Payment;
use App\Service;
use App\ServiceBooking;
use App\StoreBusinessHour;
use App\StoreCategory;
use App\StoreProduct;
use App\Testimonial;
use App\VcardProduct;
use Illuminate\Support\Str;

/**
 * Copy a business card or store (with its content) to another customer.
 * Moved unchanged from Admin\CustomerController::assignCard.
 */
class CardDuplicator
{
    /**
     * @return string the new card_id
     */
    public function duplicate(BusinessCard $source, string $targetUserId): string
    {
        $sourceCardId = $source->card_id;
        $type = $source->card_type;

        // Generate card ID
        $generateCardId = uniqid();

        // Create duplicate
        $duplicateCard = new BusinessCard();
        $duplicateCard->card_id = $generateCardId;
        $duplicateCard->user_id = $targetUserId;
        $duplicateCard->type = $source->type;
        $duplicateCard->theme_id = $source->theme_id;
        $duplicateCard->card_lang = $source->card_lang;
        $duplicateCard->cover_type = $source->cover_type;
        $duplicateCard->cover = $source->cover;
        $duplicateCard->profile = $source->profile;
        $duplicateCard->card_url = $source->card_url . '-' . Str::random(5);
        $duplicateCard->card_type = $source->card_type;
        $duplicateCard->title = $source->title . ' (Duplicate)';
        $duplicateCard->sub_title = $source->sub_title;
        $duplicateCard->description = $source->description;
        $duplicateCard->enquiry_email = $source->enquiry_email;
        $duplicateCard->appointment_receive_email = $source->appointment_receive_email;
        $duplicateCard->is_newsletter_pop_active = $source->is_newsletter_pop_active;
        $duplicateCard->is_info_pop_active = $source->is_info_pop_active;
        $duplicateCard->custom_css = $source->custom_css;
        $duplicateCard->custom_js = $source->custom_js;
        $duplicateCard->password = $source->password;
        $duplicateCard->expiry_time = $source->expiry_time;
        $duplicateCard->card_status = 'activated';
        $duplicateCard->status = 1;
        $duplicateCard->save();

        // Check type
        if ($type == 'vcard') {
            // Duplicate social links
            $socialLinks = BusinessField::where('card_id', $sourceCardId)->get();
            foreach ($socialLinks as $socialLink) {
                try {
                    // Save social link
                    $duplicateSocialLink = new BusinessField();
                    $duplicateSocialLink->card_id = $generateCardId;
                    $duplicateSocialLink->type = $socialLink->type;
                    $duplicateSocialLink->icon = $socialLink->icon;
                    $duplicateSocialLink->label = $socialLink->label;
                    $duplicateSocialLink->content = $socialLink->content;
                    $duplicateSocialLink->position = $socialLink->position;
                    $duplicateSocialLink->status = 1;
                    $duplicateSocialLink->save();
                } catch (\Exception $e) {
                }
            }

            // Duplicate payment links
            $paymentLinks = Payment::where('card_id', $sourceCardId)->get();
            foreach ($paymentLinks as $paymentLink) {
                try {
                    // Save payment link
                    $duplicatePaymentLink = new Payment();
                    $duplicatePaymentLink->card_id = $generateCardId;
                    $duplicatePaymentLink->type = $paymentLink->type;
                    $duplicatePaymentLink->icon = $paymentLink->icon;
                    $duplicatePaymentLink->label = $paymentLink->label;
                    $duplicatePaymentLink->content = $paymentLink->content;
                    $duplicatePaymentLink->position = $paymentLink->position;
                    $duplicatePaymentLink->status = 1;
                    $duplicatePaymentLink->save();
                } catch (\Exception $e) {
                }
            }

            // Duplicate services
            $services = Service::where('card_id', $sourceCardId)->get();
            foreach ($services as $service) {
                try {
                    // Save service
                    $duplicateService = new Service();
                    $duplicateService->card_id = $generateCardId;
                    $duplicateService->service_name = $service->service_name;
                    $duplicateService->service_image = $service->service_image;
                    $duplicateService->service_description = $service->service_description;
                    $duplicateService->enable_enquiry = $service->enable_enquiry;
                    $duplicateService->status = 1;
                    $duplicateService->save();
                } catch (\Exception $e) {
                }
            }

            // Duplicate products
            $products = VcardProduct::where('card_id', $sourceCardId)->get();
            foreach ($products as $product) {
                try {
                    // Save product
                    $duplicateProduct = new VcardProduct();
                    $duplicateProduct->card_id = $generateCardId;
                    $duplicateProduct->product_id = uniqid();
                    $duplicateProduct->badge = $product->badge;
                    $duplicateProduct->currency = $product->currency;
                    $duplicateProduct->product_image = $product->product_image;
                    $duplicateProduct->product_name = $product->product_name;
                    $duplicateProduct->product_description = $product->product_description;
                    $duplicateProduct->regular_price = $product->regular_price;
                    $duplicateProduct->sales_price = $product->sales_price;
                    $duplicateProduct->product_status = $product->product_status;
                    $duplicateProduct->status = 1;
                    $duplicateProduct->save();
                } catch (\Exception $e) {
                }
            }

            // Duplicate galleries
            $galleries = Gallery::where('card_id', $sourceCardId)->get();
            foreach ($galleries as $gallery) {
                try {
                    $duplicateGallery = new Gallery();
                    $duplicateGallery->card_id = $generateCardId;
                    $duplicateGallery->caption = $gallery->caption;
                    $duplicateGallery->gallery_image = $gallery->gallery_image;
                    $duplicateGallery->status = 1;
                    $duplicateGallery->save();
                } catch (\Exception $e) {
                }
            }

            // Duplicate testimonials
            $testimonials = Testimonial::where('card_id', $sourceCardId)->get();
            foreach ($testimonials as $testimonial) {
                try {
                    // Save testimonial
                    $duplicateTestimonial = new Testimonial();
                    $duplicateTestimonial->card_id = $generateCardId;
                    $duplicateTestimonial->reviewer_image = $testimonial->reviewer_image;
                    $duplicateTestimonial->reviewer_name = $testimonial->reviewer_name;
                    $duplicateTestimonial->reviewer_subtext = $testimonial->reviewer_subtext;
                    $duplicateTestimonial->review = $testimonial->review;
                    $duplicateTestimonial->status = 1;
                    $duplicateTestimonial->save();
                } catch (\Exception $e) {
                }
            }

            // Duplicate newsletter
            $newsletter = Newsletter::where('card_id', $sourceCardId)->first();
            if ($newsletter) {
                try {
                    // Save newsletter
                    $duplicateNewsletter = new Newsletter();
                    $duplicateNewsletter->newsletter_id = $newsletter->newsletter_id;
                    $duplicateNewsletter->card_id = $generateCardId;
                    $duplicateNewsletter->email = $newsletter->email;
                    $duplicateNewsletter->status = 1;
                    $duplicateNewsletter->save();
                } catch (\Exception $e) {
                }
            }

            // Duplicate  information_pops
            $popups = InformationPop::where('card_id', $sourceCardId)->first();
            if ($popups) {
                try {
                    // Save information_pop
                    $duplicateInformationPop = new InformationPop();
                    $duplicateInformationPop->information_pop_id = $popups->information_pop_id;
                    $duplicateInformationPop->card_id = $generateCardId;
                    $duplicateInformationPop->confetti_effect = $popups->confetti_effect;
                    $duplicateInformationPop->info_pop_image = $popups->info_pop_image;
                    $duplicateInformationPop->info_pop_title = $popups->info_pop_title;
                    $duplicateInformationPop->info_pop_desc = $popups->info_pop_desc;
                    $duplicateInformationPop->info_pop_button_text = $popups->info_pop_button_text;
                    $duplicateInformationPop->info_pop_button_url = $popups->info_pop_button_url;
                    $duplicateInformationPop->status = 1;
                    $duplicateInformationPop->save();
                } catch (\Exception $e) {
                }
            }

            // Duplicate business hours
            $businessHours = BusinessHour::where('card_id', $sourceCardId)->get();
            foreach ($businessHours as $businessHour) {
                try {
                    // Save business hour
                    $duplicateBusinessHour = new BusinessHour();
                    $duplicateBusinessHour->card_id = $generateCardId;
                    $duplicateBusinessHour->monday = $businessHour->monday;
                    $duplicateBusinessHour->tuesday = $businessHour->tuesday;
                    $duplicateBusinessHour->wednesday = $businessHour->wednesday;
                    $duplicateBusinessHour->thursday = $businessHour->thursday;
                    $duplicateBusinessHour->friday = $businessHour->friday;
                    $duplicateBusinessHour->saturday = $businessHour->saturday;
                    $duplicateBusinessHour->sunday = $businessHour->sunday;
                    $duplicateBusinessHour->is_always_open = $businessHour->is_always_open;
                    $duplicateBusinessHour->is_display = $businessHour->is_display;
                    $duplicateBusinessHour->status = 1;
                    $duplicateBusinessHour->save();
                } catch (\Exception $e) {
                }
            }

            // Duplicate card appointments
            $appointments = CardAppointmentTime::where('card_id', $sourceCardId)->get();
            foreach ($appointments as $appointment) {
                try {
                    // Save appointment
                    $duplicateAppointment = new CardAppointmentTime();
                    $duplicateAppointment->card_appointment_time_id = uniqid();
                    $duplicateAppointment->card_id = $generateCardId;
                    $duplicateAppointment->day = $appointment->day;
                    $duplicateAppointment->slot_duration = $appointment->slot_duration;
                    $duplicateAppointment->time_slots = $appointment->time_slots;
                    $duplicateAppointment->price = $appointment->price;
                    $duplicateAppointment->status = 1;
                    $duplicateAppointment->save();
                } catch (\Exception $e) {
                }
            }

            // Service booking
            $serviceBookings = ServiceBooking::where('vcard_id', $sourceCardId)->get();
            foreach ($serviceBookings as $serviceBooking) {
                try {
                    // Save service booking
                    $duplicateServiceBooking = new ServiceBooking();
                    $duplicateServiceBooking->service_booking_id = uniqid();
                    $duplicateServiceBooking->user_id = $targetUserId;
                    $duplicateServiceBooking->vcard_id = $generateCardId;
                    $duplicateServiceBooking->service_booking = $serviceBooking->service_booking;
                    $duplicateServiceBooking->service_booking_available_days = $serviceBooking->service_booking_available_days;
                    $duplicateServiceBooking->service_booking_amount = $serviceBooking->service_booking_amount;
                    $duplicateServiceBooking->service_booking_start_time = $serviceBooking->service_booking_start_time;
                    $duplicateServiceBooking->service_booking_end_time = $serviceBooking->service_booking_end_time;
                    $duplicateServiceBooking->service_booking_receive_email = $serviceBooking->service_booking_receive_email;
                    $duplicateServiceBooking->save();
                } catch (\Exception $e) {
                }
            }
        } else {
            // Product store category
            $categories = StoreCategory::where('store_id', $sourceCardId)->get();
            $categoryIdMap = []; // Store original -> new category_id

            foreach ($categories as $category) {
                try {
                    // Save store category
                    $newCategoryId = uniqid(); // Generate new ID once
                    $duplicateCategory = new StoreCategory();
                    $duplicateCategory->user_id = $targetUserId ?? '';
                    $duplicateCategory->store_id = $generateCardId;
                    $duplicateCategory->category_id = $newCategoryId;
                    $duplicateCategory->thumbnail = $category->thumbnail ?? '';
                    $duplicateCategory->category_name = $category->category_name ?? '';
                    $duplicateCategory->status = 1;
                    $duplicateCategory->save();

                    // Store mapping for use in products
                    $categoryIdMap[$category->category_id] = $newCategoryId;
                } catch (\Exception $e) {
                    // Optionally log the error
                }
            }

            // Save products
            $products = StoreProduct::where('card_id', $sourceCardId)->get();

            foreach ($products as $product) {
                try {
                    $duplicateStoreProduct = new StoreProduct();
                    $duplicateStoreProduct->card_id = $generateCardId;
                    $duplicateStoreProduct->product_id = uniqid();

                    // Replace old category_id with new one using the map
                    $duplicateStoreProduct->category_id = $categoryIdMap[$product->category_id] ?? null;

                    $duplicateStoreProduct->badge = $product->badge;
                    $duplicateStoreProduct->product_image = $product->product_image;
                    $duplicateStoreProduct->product_name = $product->product_name;
                    $duplicateStoreProduct->product_description = $product->product_description;
                    $duplicateStoreProduct->regular_price = $product->regular_price;
                    $duplicateStoreProduct->sales_price = $product->sales_price;
                    $duplicateStoreProduct->product_status = $product->product_status;
                    $duplicateStoreProduct->status = 1;
                    $duplicateStoreProduct->save();
                } catch (\Exception $e) {
                    // Optionally log the error
                }
            }

            // Store Business Hours
            $businessHours = StoreBusinessHour::where('store_id', $sourceCardId)->get();
            foreach ($businessHours as $businessHour) {
                try {
                    // Save store business hour
                    $duplicateBusinessHour = new StoreBusinessHour();
                    $duplicateBusinessHour->user_id = $targetUserId;
                    $duplicateBusinessHour->store_id = $generateCardId;
                    $duplicateBusinessHour->business_hours_id = uniqid();
                    $duplicateBusinessHour->business_hours = $businessHour->business_hours;
                    $duplicateBusinessHour->save();
                } catch (\Exception $e) {
                }
            }

            // Duplicate newsletter
            $newsletter = Newsletter::where('card_id', $sourceCardId)->first();
            if ($newsletter) {
                try {
                    // Save newsletter
                    $duplicateNewsletter = new Newsletter();
                    $duplicateNewsletter->newsletter_id = $newsletter->newsletter_id;
                    $duplicateNewsletter->card_id = $generateCardId;
                    $duplicateNewsletter->email = $newsletter->email;
                    $duplicateNewsletter->status = 1;
                    $duplicateNewsletter->save();
                } catch (\Exception $e) {
                }
            }

            // Duplicate  information_pops
            $popups = InformationPop::where('card_id', $sourceCardId)->get();
            foreach ($popups as $popup) {
                try {
                    // Save information_pop
                    $duplicateInformationPop = new InformationPop();
                    $duplicateInformationPop->information_pop_id = $popup->information_pop_id;
                    $duplicateInformationPop->card_id = $generateCardId;
                    $duplicateInformationPop->confetti_effect = $popup->confetti_effect;
                    $duplicateInformationPop->info_pop_image = $popup->info_pop_image;
                    $duplicateInformationPop->info_pop_title = $popup->info_pop_title;
                    $duplicateInformationPop->info_pop_desc = $popup->info_pop_desc;
                    $duplicateInformationPop->info_pop_button_text = $popup->info_pop_button_text;
                    $duplicateInformationPop->info_pop_button_url = $popup->info_pop_button_url;
                    $duplicateInformationPop->status = 1;
                    $duplicateInformationPop->save();
                } catch (\Exception $e) {
                }
            }
        }

        return $generateCardId;
    }
}
