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
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class CustomizationController extends Controller
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
     * Show the customization page
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */

    public function customization(Request $request, $id)
    {
        // Queries
        $business_card = BusinessCard::where('card_id', $id)->first();
        $plan          = User::where('user_id', Auth::user()->user_id)->where('status', 1)->first();
        $plan_details  = json_decode($plan->plan_details);
        $settings      = Setting::where('status', 1)->first();

        // Check business card
        if ($business_card == null) {
            return redirect()->route('user.cards')->with('failed', trans('Card not found!'));
        } else {
            if ($business_card->type == 'custom') {
                return view('user.pages.cards.customization', compact('plan_details', 'business_card', 'settings'));
            } else if ($plan_details->password_protected == 1 || $plan_details->advanced_settings == 1) {
                return redirect()->route('user.advanced.setting', request()->segment(3));
            } else {
                return redirect()->route('user.cards')->with('success', trans('Your virtual business card is ready.'));
            }
        }
    }
}
