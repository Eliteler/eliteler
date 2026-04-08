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
use App\Newsletter;
use App\ContactForm;
use App\BookedAppointment;
use Illuminate\Http\Request;
use App\ServiceBookingConfirmed;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class ContactController extends Controller
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
     * Show the contact list.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */

    // Contacts
    public function index(Request $request, $id)
    {
        // Queries
        $settings = Setting::where('status', 1)->first();

        // Get plan details
        $plan = User::where('user_id', Auth::user()->user_id)->where('status', 1)->first();
        $plan_details = json_decode($plan->plan_details);

        // Check active plan
        if ($plan_details->contact_form == 0) {
            return redirect()->route('user.cards')->with('failed', trans('Contact form is not enabled for this plan.'));
        }

        // Contact Forms
        $contactForms = ContactForm::where('card_id', $id)->orderBy('id', 'desc')->get()->map(function ($contact) {
            return (object)[
                'name'   => $contact->name,
                'email'  => $contact->email,
                'phone'  => $contact->phone,
                'source' => trans("Contact Form")
            ];
        });

        // Booked Appointments
        $bookedAppointments = BookedAppointment::where('card_id', $id)->orderBy('id', 'desc')->get()->map(function ($appointment) {
            return (object)[
                'name'   => $appointment->name,
                'email'  => $appointment->email,
                'phone'  => $appointment->phone,
                'source' => trans("Appointment")
            ];
        });

        // Service Booking
        $serviceBooking = ServiceBookingConfirmed::where('vcard_id', $id)->orderBy('id', 'desc')->get()->map(function ($service) {
            return (object)[
                'name'   => $service->fullname,
                'email'  => $service->email,
                'phone'  => $service->mobile_number,
                'source' => trans("Service Booking")
            ];
        });

        // Newsletters
        $newsletters = Newsletter::where('card_id', $id)->orderBy('id', 'desc')->get()->map(function ($newsletter) {
            return (object)[
                'name'   => '',
                'email'  => $newsletter->email,
                'phone'  => '',
                'source' => trans("Newsletter")
            ];
        });

        // Merge and sort
        $contacts = collect()
            ->concat($contactForms)
            ->concat($bookedAppointments)
            ->concat($newsletters)
            ->sortByDesc('created_at')
            ->values();

        return view('user.pages.cards.contacts', compact('contacts', 'settings', 'plan_details'));
    }
}
