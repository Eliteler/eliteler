<?php

/*
 |--------------------------------------------------------------------------
 | Eliteler vCard SaaS
 |--------------------------------------------------------------------------
 | Developed by NativeCode © 2021 - https://nativecode.in
 | All rights reserved
 | Unauthorized distribution is prohibited
 |--------------------------------------------------------------------------
*/

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\BusinessCard;
use App\BusinessField;
use App\EmailTemplate;
use GuzzleHttp\Client;
use App\BookedAppointment;
use App\CardAppointmentTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class BookAppointmentController extends Controller
{
    // Book appointment
    public function bookAppointment(Request $request)
    {
        /*
    |--------------------------------------------------------------------------
    | Validate request
    |--------------------------------------------------------------------------
    */

        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'date' => 'required|date',
            'time_slot' => 'required|string',
        ];

        // Add Recaptcha validation if enabled
        if (env('RECAPTCHA_ENABLE') == 'on') {
            $rules['g_recaptcha_response'] = 'required';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {

            return response()->json([
                'success' => false,
                'message' => trans('Please fill out all the fields.')
            ], 422);
        }

        /*
    |--------------------------------------------------------------------------
    | Verify Recaptcha
    |--------------------------------------------------------------------------
    */

        if (env('RECAPTCHA_ENABLE') == 'on') {

            $recaptcha = Http::asForm()->post(
                'https://www.google.com/recaptcha/api/siteverify',
                [
                'secret' => env('RECAPTCHA_SECRET_KEY'),
                'response' => $request->input('g_recaptcha_response'),
                'remoteip' => $request->ip(),
                ]
            );

            $result = $recaptcha->json();

            if (!($result['success'] ?? false)) {

                return response()->json([
                    'success' => false,
                    'message' => trans('reCAPTCHA verification failed.')
                ], 422);
            }
        }

        /*
    |--------------------------------------------------------------------------
    | Parse booking date
    |--------------------------------------------------------------------------
    */

        $parsedDate = Carbon::parse($request->date)
            ->format('Y-m-d');

        /*
    |--------------------------------------------------------------------------
    | Check already booked
    |--------------------------------------------------------------------------
    |
    */

        $alreadyBooked = BookedAppointment::where('card_id', $request->card)
            ->whereDate('booking_date', $parsedDate)
            ->where('booking_time', $request->time_slot)
            ->where(function ($query) {

                $query->whereIn('booking_status', [0, 1])
                    ->orWhereNull('booking_status');
            })
            ->exists();

        if ($alreadyBooked) {
            return response()->json([
                'success' => false,
                'message' => trans('Booking date and time is already booked')
            ], 422);
        }

        /*
    |--------------------------------------------------------------------------
    | Save appointment
    |--------------------------------------------------------------------------
    */

        $bookAppointment = new BookedAppointment();

        $bookAppointment->booked_appointment_id = uniqid();
        $bookAppointment->card_id = $request->card;
        $bookAppointment->name = $request->name;
        $bookAppointment->email = $request->email;
        $bookAppointment->phone = $request->phone;
        $bookAppointment->notes = $request->notes;
        $bookAppointment->booking_date = $parsedDate;
        $bookAppointment->booking_time = $request->time_slot;
        $bookAppointment->total_price = $request->price ?? 0;

        // Important
        $bookAppointment->booking_status = 0;

        $bookAppointment->save();

        /*
    |--------------------------------------------------------------------------
    | Get vCard details
    |--------------------------------------------------------------------------
    */

        $vcardOwner = BusinessCard::where(
            'card_id',
            $request->card
        )->first();

        $businessName = $vcardOwner->title ?? '';
        $businessVcardUrl = url($vcardOwner->card_url ?? '');
        $vcardOwnerEmail = $vcardOwner->appointment_receive_email ?? '';

        /*
    |--------------------------------------------------------------------------
    | Default WhatsApp URL
    |--------------------------------------------------------------------------
    */

        $whatsapp_url = "#";

        try {

            /*
        |--------------------------------------------------------------------------
        | Email templates
        |--------------------------------------------------------------------------
        */

            $customerTemplate = EmailTemplate::where(
                'email_template_id',
                '584922675196'
            )->first();

            $ownerTemplate = EmailTemplate::where(
                'email_template_id',
                '584922675201'
            )->first();

            /*
        |--------------------------------------------------------------------------
        | Send customer email
        |--------------------------------------------------------------------------
        */

            if ($customerTemplate?->is_enabled) {

                $details = [
                    'status' => "Pending",
                    'emailSubject' => $customerTemplate->email_template_subject,
                    'emailContent' => $customerTemplate->email_template_content,
                    'appointmentDate' => $parsedDate,
                    'appointmentTime' => $request->time_slot,
                    'vcardName' => $businessName,
                    'vcardUrl' => $businessVcardUrl,
                    'googleCalendarUrl' => "",
                    'customerName' => "",
                    'cardId' => $request->card,
                ];

                // Dynamically set from address for this mail
                if ($vcardOwner && !empty($vcardOwner->email_from_address)) {
                    config(['mail.from.address' => $vcardOwner->email_from_address]);
                }
                if ($vcardOwner && !empty($vcardOwner->email_from_name)) {
                    config(['mail.from.name' => $vcardOwner->email_from_name]);
                }

                Mail::to($request->email)->send(new \App\Mail\AppointmentMail($details));
            }

            /*
        |--------------------------------------------------------------------------
        | Send owner email
        |--------------------------------------------------------------------------
        */

            if ($ownerTemplate?->is_enabled) {

                $ownerDetails = [
                    'status' => "",
                    'emailSubject' => $ownerTemplate->email_template_subject,
                    'emailContent' => $ownerTemplate->email_template_content,
                    'appointmentDate' => $parsedDate,
                    'appointmentTime' => $request->time_slot,
                    'vcardName' => $businessName,
                    'vcardUrl' => $businessVcardUrl,
                    'googleCalendarUrl' => "",
                    'customerName' => $request->name,
                    'cardId' => $request->card,
                ];

                // Dynamically set from address for this mail
                if ($vcardOwner && !empty($vcardOwner->email_from_address)) {
                    config(['mail.from.address' => $vcardOwner->email_from_address]);
                }
                if ($vcardOwner && !empty($vcardOwner->email_from_name)) {
                    config(['mail.from.name' => $vcardOwner->email_from_name]);
                }

                Mail::to($vcardOwnerEmail)->send(new \App\Mail\AppointmentMail($ownerDetails));
            }

            /*
        |--------------------------------------------------------------------------
        | WhatsApp notification
        |--------------------------------------------------------------------------
        */

            $whatsAppExists = false;

            if (
                !plugin_active('MSG91WhatsappNotification') &&
                !plugin_active('TwilioWhatsappNotification')
            ) {

                $whatsAppExists = BusinessField::where('card_id', $request->card)
                    ->where('type', 'wa')
                    ->exists();
            }

            if ($whatsAppExists) {

                $whatsAppData = BusinessField::where('card_id', $request->card)
                    ->where('type', 'wa')
                    ->first();

                $whatsAppNumber = $whatsAppData->content ?? '';

                $vcard = BusinessCard::where(
                    'card_id',
                    $request->card
                )->first();

                $vcardLanguage = $vcard->card_lang ?? 'en';

                // Apply locale
                app()->setLocale($vcardLanguage);

                /*
            |--------------------------------------------------------------------------
            | Build WhatsApp message
            |--------------------------------------------------------------------------
            */

                $appointmentMessage  = __("New Appointment") . "\n\n";
                $appointmentMessage .= __("vCard Name") . ": " . $businessName . "\n";
                $appointmentMessage .= __("Appointment Date") . ": " . $parsedDate . "\n";
                $appointmentMessage .= __("Appointment Time") . ": " . $request->time_slot . "\n";
                $appointmentMessage .= __("Customer Name") . ": " . $request->name . "\n";
                $appointmentMessage .= __("Customer Email") . ": " . $request->email . "\n";
                $appointmentMessage .= __("Customer Phone") . ": " . $request->phone . "\n";
                $appointmentMessage .= __("Notes") . ": " . $request->notes;

                /*
            |--------------------------------------------------------------------------
            | Format number
            |--------------------------------------------------------------------------
            */

                $whatsAppNumber = preg_replace(
                    '/[^0-9]/',
                    '',
                    $whatsAppNumber
                );

                $whatsAppUrl = "https://api.whatsapp.com/send?phone={$whatsAppNumber}&text="
                    . urlencode($appointmentMessage);

                return response()->json([
                    'success' => true,
                    'message' => trans('Appointment booked successfully!'),
                    'whatsapp_url' => $whatsAppUrl
                ]);
            }

            /*
        |--------------------------------------------------------------------------
        | Success response
        |--------------------------------------------------------------------------
        */

            return response()->json([
                'success' => true,
                'message' => trans('Appointment booked successfully!'),
                'whatsapp_url' => $whatsapp_url
            ]);
        } catch (\Exception $e) {

            // Optional log
            // \Log::error($e->getMessage());

            return response()->json([
                'success' => false,
                'message' => trans('Failed to send confirmation email.'),
                'whatsapp_url' => $whatsapp_url
            ]);
        }
    }

    // Get day wise available time slots
    public function getAvailableTimeSlots(Request $request)
    {
        // Parse the input day into a Carbon date object
        $cardId = $request->card;

        // Add one day
        $addOneDay = $request->choose_date;

        // Format the new date
        $Date = Carbon::parse($addOneDay)->addDay();
        $choosedDate = $Date->format('Y-m-d');

        $day = Carbon::parse($request->day);

        /*
    |--------------------------------------------------------------------------
    | Retrieve already booked appointments
    |--------------------------------------------------------------------------
    | Includes:
    | - Pending bookings
    | - Confirmed bookings
    | - Old NULL status bookings
    */

        $bookedAppointments = BookedAppointment::where('card_id', $cardId)
            ->whereDate('booking_date', $choosedDate)
            ->where(function ($query) {

                $query->whereIn('booking_status', [0, 1])
                    ->orWhereNull('booking_status');
            })
            ->pluck('booking_time');

        // Convert booked appointments to array
        $excludedTimeSlots = $bookedAppointments->toArray();

        /*
    |--------------------------------------------------------------------------
    | Retrieve available time slots
    |--------------------------------------------------------------------------
    */

        $availableTimeSlots = CardAppointmentTime::where('card_id', $cardId)
            ->where('day', strtolower($day->format('l')))
            ->pluck('time_slots');

        /*
    |--------------------------------------------------------------------------
    | No slots available
    |--------------------------------------------------------------------------
    */

        if ($availableTimeSlots->isEmpty()) {

            return response()->json([
                'success' => false,
                'message' => __('No available time slots for this day.')
            ]);
        }

        /*
    |--------------------------------------------------------------------------
    | Decode JSON time slots
    |--------------------------------------------------------------------------
    */

        $availableTimeSlots = json_decode(
            $availableTimeSlots->first(),
            true
        ) ?? [];

        /*
    |--------------------------------------------------------------------------
    | Ensure excluded slots exist
    |--------------------------------------------------------------------------
    */

        $excludedTimeSlots = $excludedTimeSlots ?? [];

        /*
    |--------------------------------------------------------------------------
    | Remove booked slots
    |--------------------------------------------------------------------------
    */

        $availableTimeSlots = array_diff(
            $availableTimeSlots,
            $excludedTimeSlots
        );

        /*
    |--------------------------------------------------------------------------
    | Re-index array
    |--------------------------------------------------------------------------
    */

        $availableTimeSlots = array_values($availableTimeSlots);

        /*
    |--------------------------------------------------------------------------
    | Convert back to JSON
    |--------------------------------------------------------------------------
    */

        $availableTimeSlotsJson = json_encode($availableTimeSlots);

        /*
    |--------------------------------------------------------------------------
    | Get price safely
    |--------------------------------------------------------------------------
    */

        $price = optional(
            CardAppointmentTime::where('card_id', $cardId)->first()
        );

        /*
    |--------------------------------------------------------------------------
    | Return response
    |--------------------------------------------------------------------------
    */

        return response()->json([
            'success' => true,
            'available_time_slots' => $availableTimeSlotsJson,
            'price' => $price->price
        ]);
    }
}
