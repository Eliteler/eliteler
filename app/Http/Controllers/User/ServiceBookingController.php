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

namespace App\Http\Controllers\User;

use App\User;
use App\Setting;
use Carbon\Carbon;
use App\BusinessCard;
use App\EmailTemplate;
use Illuminate\Http\Request;
use App\ServiceBookingConfirmed;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class ServiceBookingController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the service booking.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */

    // Service Booking
    public function serviceBooking(Request $request, $id)
    {
        // Queries
        $settings = Setting::where('status', 1)->first();

        // Get plan details
        $plan = User::where('user_id', Auth::user()->user_id)->where('status', 1)->first();
        $plan_details = json_decode($plan->plan_details);

        // Check active plan
        if ($plan_details->service_booking == 0) {
            return redirect()->route('user.cards')->with('failed', trans('Service booking is not enabled for this plan.'));
        }

        // Service Booking
        $serviceBooking = ServiceBookingConfirmed::where('vcard_id', $id)->orderBy('id', 'desc')->get()->map(function ($service) {
            return (object)[
                'service_booking_id' => $service->service_booking_confirmed_id,
                'name'   => $service->fullname,
                'email'  => $service->email,
                'phone'  => $service->mobile_number,
                'address' => $service->address,
                'checkin' => formatDateForUser($service->checkin_date . ' ' . $service->checkin_time),
                'checkout' => formatDateForUser($service->checkout_date . ' ' . $service->checkout_time),
                'number_of_guests' => $service->number_of_guests,
                'notes' => $service->notes,
                'booking_status' => $service->service_booking_confirmed_status
            ];
        });

        return view('user.pages.cards.confirmed-service-booking', compact('serviceBooking', 'settings', 'plan_details'));
    }

    // Accept Service Booking
    public function acceptServiceBooking(Request $request)
    {
        // Params
        $id = $request->query('id');

        // Get service booking confirmed
        $service = ServiceBookingConfirmed::where('service_booking_confirmed_id', $id)->first();

        // Check service booking
        if ($service == null) {
            return redirect()->route('user.all.service.booking', $service->vcard_id)->with('failed', trans('Service booking not found!'));
        }

        // Update service booking
        $service->update([
            'service_booking_confirmed_status' => 'confirmed'
        ]);

        // Business name with vcard URL from business_cards table
        $businessDetails = BusinessCard::where('card_id', $service->vcard_id)->first();
        $businessName = $businessDetails->title;
        $businessVcardUrl = url($businessDetails->card_url);

        // Appointment Date
        $checkInDate = Carbon::parse($service->checkin_date)->format('d M Y'); // e.g., '2024-10-18'
        $checkInTime = Carbon::parse($service->checkin_time)->format('H:i'); // e.g., '14:00'

        // CheckOut Date
        $checkOutDate = Carbon::parse($service->checkout_date)->format('d M Y'); // e.g., '2024-10-19'
        $checkOutTime = Carbon::parse($service->checkout_time)->format('H:i'); // e.g., '14:00'

        // Combine date and time for start and end in ISO 8601 format
        $startDateTime = Carbon::parse("{$checkInDate} {$checkInTime}")->format('Ymd\THis');
        $endDateTime = Carbon::parse("{$checkOutDate} {$checkOutTime}")->format('Ymd\THis');

        // Text
        $appointmentMessage = trans("Service Booking with " . $businessName . " (" . $businessVcardUrl . ")");

        // Service booking status
        $appointmentStatus = trans("Your service booking is confirmed");

        // Google Calendar
        $googleCalendarUrl = "https://www.google.com/calendar/render?action=TEMPLATE" .
            "&text=" . urlencode($appointmentMessage) .
            "&dates={$startDateTime}/{$endDateTime}" .
            "&details=" . urlencode($appointmentStatus);

        // Get service booking accepted email template content
        $emailTemplateDetails = EmailTemplate::where('email_template_id', '584922675216')->first();

        // Booking mail sent to customer
        if ($emailTemplateDetails->is_enabled == 1) {

            // Booking mail sent to customer
            $details = [
                'status' => "Confirmed",
                'emailSubject' => $emailTemplateDetails->email_template_subject,
                'emailContent' => $emailTemplateDetails->email_template_content,
                'checkindate' => Carbon::parse($service->checkin_date)->format('d M Y'), // e.g., '2024-10-18'
                'checkintime' => Carbon::parse($service->checkin_time)->format('H:i'), // e.g., '14:00'
                'checkoutdate' => Carbon::parse($service->checkout_date)->format('d M Y'), // e.g., '2024-10-18'
                'checkouttime' => Carbon::parse($service->checkout_time)->format('H:i'), // e.g., '14:00'
                'servicebookingpageurl' => route('user.all.service.booking', $service->vcard_id),
                'googleCalendarUrl' => $googleCalendarUrl,
                'customerName' => "",
                'vcardName' => $businessName,
                'vcardUrl' => $businessVcardUrl,
                "cardId" => $service->vcard_id
            ];
        }

        try {
            Mail::to($service->email)->send(new \App\Mail\AppointmentMail($details));
        } catch (\Exception $e) {
        }

        return redirect()->route('user.all.service.booking', $service->vcard_id)->with('success', trans('Service booking accepted successfully!'));
    }

    // Reject Service Booking
    public function rejectServiceBooking(Request $request)
    {
        // Params
        $id = $request->query('id');

        // Get service booking confirmed
        $service = ServiceBookingConfirmed::where('service_booking_confirmed_id', $id)->first();

        // Check service booking
        if ($service == null) {
            return redirect()->route('user.all.service.booking', $service->vcard_id)->with('failed', trans('Service booking not found!'));
        }

        // Update service booking
        $service->update([
            'service_booking_confirmed_status' => 'rejected'
        ]);

        // Business name with vcard URL from business_cards table
        $businessDetails = BusinessCard::where('card_id', $service->vcard_id)->first();
        $businessName = $businessDetails->title;
        $businessVcardUrl = url($businessDetails->card_url);

        // Get service booking rejected email template content
        $emailTemplateDetails = EmailTemplate::where('email_template_id', '584922675217')->first();

        // Booking mail sent to customer
        if ($emailTemplateDetails->is_enabled == 1) {

            // Booking mail sent to customer
            $details = [
                'status' => "Rejected",
                'emailSubject' => $emailTemplateDetails->email_template_subject,
                'emailContent' => $emailTemplateDetails->email_template_content,
                'checkindate' => Carbon::parse($service->checkin_date)->format('d M Y'), // e.g., '2024-10-18'
                'checkintime' => Carbon::parse($service->checkin_time)->format('H:i'), // e.g., '14:00'
                'checkoutdate' => Carbon::parse($service->checkout_date)->format('d M Y'), // e.g., '2024-10-18'
                'checkouttime' => Carbon::parse($service->checkout_time)->format('H:i'), // e.g., '14:00'
                'servicebookingpageurl' => route('user.all.service.booking', $service->vcard_id),
                'googleCalendarUrl' => "",
                'customerName' => "",
                'vcardName' => $businessName,
                'vcardUrl' => $businessVcardUrl,
                "cardId" => $service->vcard_id
            ];
        }

        try {
            Mail::to($service->email)->send(new \App\Mail\AppointmentMail($details));
        } catch (\Exception $e) {
        }

        return redirect()->route('user.all.service.booking', $service->vcard_id)->with('success', trans('Service booking rejected successfully!'));
    }

    // Complete Service Booking
    public function completeServiceBooking(Request $request)
    {
        // Params
        $id = $request->query('id');

        // Get service booking confirmed
        $service = ServiceBookingConfirmed::where('service_booking_confirmed_id', $id)->first();

        // Check service booking
        if ($service == null) {
            return redirect()->route('user.all.service.booking', $service->vcard_id)->with('failed', trans('Service booking not found!'));
        }

        // Update service booking
        $service->update([
            'service_booking_confirmed_status' => 'completed'
        ]);

        // Business name with vcard URL from business_cards table
        $businessDetails = BusinessCard::where('card_id', $service->vcard_id)->first();
        $businessName = $businessDetails->title;
        $businessVcardUrl = url($businessDetails->card_url);

        // Get service booking completed email template content
        $emailTemplateDetails = EmailTemplate::where('email_template_id', '584922675218')->first();

        // Booking mail sent to customer
        if ($emailTemplateDetails->is_enabled == 1) {

            // Booking mail sent to customer
            $details = [
                'status' => "Completed",
                'emailSubject' => $emailTemplateDetails->email_template_subject,
                'emailContent' => $emailTemplateDetails->email_template_content,
                'checkindate' => Carbon::parse($service->checkin_date)->format('d M Y'), // e.g., '2024-10-18'
                'checkintime' => Carbon::parse($service->checkin_time)->format('H:i'), // e.g., '14:00'
                'checkoutdate' => Carbon::parse($service->checkout_date)->format('d M Y'), // e.g., '2024-10-18'
                'checkouttime' => Carbon::parse($service->checkout_time)->format('H:i'), // e.g., '14:00'
                'servicebookingpageurl' => route('user.all.service.booking', $service->vcard_id),
                'googleCalendarUrl' => "",
                'customerName' => "",
                'vcardName' => $businessName,
                'vcardUrl' => $businessVcardUrl,
                "cardId" => $service->vcard_id
            ];
        }

        try {
            Mail::to($service->email)->send(new \App\Mail\AppointmentMail($details));
        } catch (\Exception $e) {
        }

        return redirect()->route('user.all.service.booking', $service->vcard_id)->with('success', trans('Service booking completed successfully!'));
    }

    // Add My Google Calendar
    public function ServiceBookingAddMyGoogleCalendar(Request $request)
    {
        // Params
        $id = $request->query('id');

        // Get service booking confirmed
        $service = ServiceBookingConfirmed::where('service_booking_confirmed_id', $id)->first();

        // Check service booking
        if ($service == null) {
            return redirect()->route('user.all.service.booking', $service->vcard_id)->with('failed', trans('Service booking not found!'));
        }

        // Business name with vcard URL from business_cards table
        $businessDetails = BusinessCard::where('card_id', $service->vcard_id)->first();
        if (!$businessDetails) {
            return redirect()->back()->with('failed', trans('Business details not found.'));
        }

        $businessName = $businessDetails->title;
        $businessVcardUrl = url($businessDetails->card_url);

        // Prepare details for the email and Google Calendar
        $checkInDate = $service->checkin_date; // e.g., '2024-10-18'
        $checkInTime = $service->checkin_time; // e.g., '14:00'

        // CheckOut Date
        $checkOutDate = $service->checkout_date; // e.g., '2024-10-18'
        $checkOutTime = $service->checkout_time; // e.g., '14:00'

        // Combine date and time for start and end in ISO 8601 format
        $startDateTime = Carbon::parse("{$checkInDate} {$checkInTime}")->format('Ymd\THis');
        $endDateTime = Carbon::parse("{$checkOutDate} {$checkOutTime}")->format('Ymd\THis');

        // Text
        $appointmentMessage = trans("Service Booking with " . $businessName . " (" . $businessVcardUrl . ")");

        // Service booking status
        $appointmentStatus = trans("Your service booking is ");
        if ($service->service_booking_confirmed_status == 'pending') {
            $appointmentStatus .= "pending";
        } elseif ($service->service_booking_confirmed_status == 'confirmed') {
            $appointmentStatus .= "confirmed";
        } elseif ($service->service_booking_confirmed_status == 'rejected') {
            $appointmentStatus .= "rejected";
        } elseif ($service->service_booking_confirmed_status == 'completed') {
            $appointmentStatus .= "completed";
        }

        // Generate Google Calendar URL for the service booking
        $googleCalendarUrl = "https://www.google.com/calendar/render?action=TEMPLATE" .
            "&text=" . urlencode($appointmentMessage) .
            "&dates={$startDateTime}/{$endDateTime}" .
            "&details=" . urlencode($appointmentStatus);

        // Redirect to the Google Calendar URL
        return redirect()->to($googleCalendarUrl);
    }
}
