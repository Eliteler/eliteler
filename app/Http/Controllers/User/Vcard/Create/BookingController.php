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

namespace App\Http\Controllers\User\Vcard\Create;

use App\User;
use App\Setting;
use App\BusinessCard;
use App\ServiceBooking;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class BookingController extends Controller
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
     * Show the service booking page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */

    // Service Booking
    public function serviceBooking()
    {
        // Queries
        $plan = User::where('user_id', Auth::user()->user_id)->where('status', 1)->first();
        $settings = Setting::where('status', 1)->first();
        $plan_details = json_decode($plan->plan_details);

        if($plan_details->service_booking == 1) {
            return view('user.pages.cards.service-booking', compact('settings', 'plan_details'));
        } else if($plan_details->password_protected == 1 || $plan_details->advanced_settings == 1) {
            return redirect()->route('user.advanced.setting', request()->segment(3));
        } else {
            return redirect()->route('user.cards')->with('success', trans('Your virtual business card is ready.'));
        }
    }

    // Update service booking
    public function saveServiceBooking(Request $request, $id)
    {
        $allDays = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
        $selectedDays = $request->input('service_booking_available_days', []);
        $availableDays = [];
        foreach ($allDays as $day) {
            $availableDays[$day] = in_array($day, $selectedDays) ? 1 : 0;
        }

        $serviceBooking = ServiceBooking::where('vcard_id', $id)->first();

        if ($request->service_booking == '1') {
            // Delete existing service booking
            if ($serviceBooking) {
                $serviceBooking->delete();
            }

            if (!$serviceBooking) {
                $serviceBooking = new ServiceBooking([
                    'service_booking_id' => uniqid(),
                    'user_id' => Auth::user()->user_id,
                    'vcard_id' => $id,
                    'title' => 'Service Booking',
                ]);
            }

            $serviceBooking->fill([
                'service_booking' => 1,
                'service_booking_available_days' => json_encode($availableDays),
                'service_booking_start_time' => $request->service_booking_start_time ?? '00:00',
                'service_booking_end_time' => $request->service_booking_end_time ?? '00:00',
                'service_booking_receive_email' => $request->service_booking_receive_email ?? null,
                'status' => 1
            ]);

            $serviceBooking->save();
        } elseif ($serviceBooking) {
            $serviceBooking->delete();
        }

        // Queries
        $business_card = BusinessCard::where('card_id', $id)->first();

        // Check business card
        $plan = User::where('user_id', Auth::user()->user_id)->where('status', 1)->first();
        $plan_details = json_decode($plan->plan_details);

        if (($plan_details->password_protected == 1 || $plan_details->advanced_settings == 1) && $business_card->type != 'custom') {
            // Check advanced settings is "ENABLED"
            return redirect()->route('user.advanced.setting', $id)->with('success', trans('Service Booking saved successfully.'));
        } else if ($plan_details->password_protected == 1 && $business_card->type == 'custom') {
            // Customization
            return redirect()->route('user.customization', $id)->with('success', trans('Service Booking saved successfully.'));
        } else {
            return redirect()->route('user.cards')->with('success', trans('Your virtual business card is ready.'));
        }
    }
}
