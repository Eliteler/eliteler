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

use App\Setting;
use App\BusinessCard;
use App\BusinessCardIntro;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class IntroScreenController extends Controller
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
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */

    // Intro Screen
    public function introScreen(Request $request, $id)
    {
        // Queries
        $business_card = BusinessCard::where('card_id', $id)->first();
        $intro_screens = BusinessCardIntro::where('status', 1)->get();

        // Check business card
        if ($business_card == null) {
            return redirect()->route('user.cards')->with('failed', trans('Card not found!'));
        } else {
            $settings = Setting::where('status', 1)->first();
            return view('user.pages.edit-cards.edit-intro-screen', compact('business_card', 'settings', 'intro_screens'));
        }
    }

    // Update Intro Screen
    public function updateIntroScreen(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'intro_screen' => 'required|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('failed', trans($validator->errors()->first()));
        }

        // Queries
        $business_card = BusinessCard::where('card_id', $id)->first();

        // Check business card
        if ($business_card == null) {
            return redirect()->route('user.cards')->with('failed', trans('Card not found!'));
        } else {

            // Update business card
            if ($request->intro_screen == 'none') {
                $business_card->intro_screen = null;
            } else {
                $business_card->intro_screen = $request->intro_screen;
            }
            $business_card->save();

            return redirect()->back()->with('success', trans('Updated!'));
        }
    }
}
