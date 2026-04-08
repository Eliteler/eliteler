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

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\EmailTemplate;
use App\ServiceBooking;
use Illuminate\Http\Request;
use App\ServiceBookingConfirmed;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class BookServiceController extends Controller
{
    public function bookService(Request $request)
    {
        // validate fields

        $validator = Validator::make($request->all(), [
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
            'customer_phone' => 'required|string|max:20|regex:/^[0-9]+$/',
            'no_of_persons' => 'required',
            'customer_address' => 'required|string',
            'service_start_date' => 'required|date',
            'service_start_time' => 'required|string',
            'service_end_date' => 'required|date',
            'service_end_time' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => trans($validator->errors()->first())]);
        }

        // check start time is less than end time
        if ($request->service_start_date == $request->service_end_date) {
            if ($request->service_start_time >= $request->service_end_time) {
                return response()->json([
                    'success' => false,
                    'message' => trans('The selected start time must be before the selected end time.')
                ]);
            }
        }

        // check if service booking is enabled
        $serviceDetails = ServiceBooking::where('vcard_id', $request->card)->first();

        if ($serviceDetails && $serviceDetails->service_booking == 1) {
            // Convert times to timestamps for comparison
            $requestedStartTime = strtotime($request->service_start_time);
            $serviceStartTime = strtotime($serviceDetails->service_booking_start_time);
            $serviceEndTime = strtotime($serviceDetails->service_booking_end_time); 

            // Validate if requested start time falls within allowed service time
            if ($requestedStartTime < $serviceStartTime || $requestedStartTime > $serviceEndTime) {
                return response()->json([
                    'success' => false,
                    'message' => trans('The selected start time is outside of the service hours. Please choose a time between ') .
                        date('H:i', $serviceStartTime) . ' and ' . date('H:i', $serviceEndTime) . '.'
                ]);
            }

            // Check checkout date greater than checkin date
            if ($request->service_end_date < $request->service_start_date) {
                return response()->json([
                    'success' => false,
                    'message' => trans('Checkout date must be greater than checkin date.')
                ]);
            }

            $bookService = new ServiceBookingConfirmed();
            $bookService->service_booking_confirmed_id = uniqid();
            $bookService->user_id = $serviceDetails->user_id;
            $bookService->vcard_id = $serviceDetails->vcard_id;
            $bookService->fullname = $request->customer_name;
            $bookService->email = $request->customer_email;
            $bookService->mobile_number = $request->customer_phone;
            $bookService->address = $request->customer_address;
            $bookService->checkin_date = $request->service_start_date;
            $bookService->checkin_time = $request->service_start_time;
            $bookService->checkout_date = $request->service_end_date;
            $bookService->checkout_time = $request->service_end_time;
            $bookService->number_of_guests = $request->no_of_persons;
            $bookService->notes = $request->customer_notes;
            $bookService->service_booking_confirmed_status = 'pending';
            $bookService->status = 1;
            $bookService->save();

            // Get service booking accepted email template content
            $emailTemplateDetails = EmailTemplate::where('email_template_id', '584922675215')->first();

            // Booking mail sent to customer
            if ($emailTemplateDetails->is_enabled == 1) {
                // Booking mail sent to customer
                $details = [
                    'status' => "Pending",
                    'emailSubject' => $emailTemplateDetails->email_template_subject,
                    'emailContent' => $emailTemplateDetails->email_template_content,
                    'checkindate' => Carbon::parse($request->service_start_date)->format('d M Y'), // e.g., '2024-10-18'
                    'checkintime' => Carbon::parse($request->service_start_time)->format('H:i'), // e.g., '14:00'
                    'checkoutdate' => Carbon::parse($request->service_end_date)->format('d M Y'), // e.g., '2024-10-18'
                    'checkouttime' => Carbon::parse($request->service_end_time)->format('H:i'), // e.g., '14:00'
                    'servicebookingpageurl' => "",
                    'googleCalendarUrl' => "",
                    'customerName' => $request->customer_name,
                    'vcardName' => "",
                    'vcardUrl' => "",
                    "cardId" =>  $serviceDetails->vcard_id
                ];
            }

            try {
                Mail::to($request->customer_email)->send(new \App\Mail\AppointmentMail($details));
            } catch (\Exception $e) {
            }

            // Booking mail sent to business card owner
            $businessEmailTemplateDetails = EmailTemplate::where('email_template_id', '584922675219')->first();

            if ($businessEmailTemplateDetails->is_enabled == 1) {
                // Booking mail sent to customer
                $serviceBookingDetails = [
                    'status' => "Pending",
                    'emailSubject' => $businessEmailTemplateDetails->email_template_subject,
                    'emailContent' => $businessEmailTemplateDetails->email_template_content,
                    'checkindate' => Carbon::parse($request->service_start_date)->format('d M Y'), // e.g., '2024-10-18'
                    'checkintime' => Carbon::parse($request->service_start_time)->format('H:i'), // e.g., '14:00'
                    'checkoutdate' => Carbon::parse($request->service_end_date)->format('d M Y'), // e.g., '2024-10-18'
                    'checkouttime' => Carbon::parse($request->service_end_time)->format('H:i'), // e.g., '14:00'
                    'servicebookingpageurl' => route('user.all.service.booking', $serviceDetails->vcard_id),
                    'googleCalendarUrl' => "",
                    'customerName' => $request->customer_name,
                    'vcardName' => "",
                    'vcardUrl' => "",
                    "cardId" =>  $serviceDetails->vcard_id
                ];
            }

            // Get business card owner email 
            $businessEmail = $serviceDetails->service_booking_receive_email;

            try {
                Mail::to($businessEmail)->send(new \App\Mail\AppointmentMail($serviceBookingDetails));
            } catch (\Exception $e) {
            }

            if ($bookService) {
                return response()->json(['success' => true, 'message' => trans('Your service has been successfully booked!')]);
            } else {
                return response()->json(['success' => false, 'message' => trans('Booking failed. Please check your details and try again.')]);
            }
        } else {
            return response()->json(['success' => false, 'message' => trans('Service booking is not enabled for this vCard.')]);
        }
    }
}
