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

namespace App\Http\Controllers\User\Store\Edit;

use App\BusinessCard;
use App\Http\Controllers\Controller;
use App\Setting;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use PSpell\Config;

class EditStoreAdvancedSettingController extends Controller
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

    // Edit Store Advanced Settings
    public function editStoreAdvancedSetting(Request $request, $id)
    {
        // Queries
        $business_card = BusinessCard::where('user_id', Auth::user()->user_id)->where('card_id', $id)->first();

        // Check business card
        if ($business_card == null) {
            return redirect()->route('user.stores')->with('failed', trans('Store not found!'));
        } else {
            // Queries
            $settings = Setting::where('status', 1)->first();
            $config = DB::table('config')->get();

            // Plan details
            $plan = User::where('user_id', Auth::user()->user_id)->where('status', 1)->first();
            $plan_details = json_decode($plan->plan_details);

            return view('user.pages.edit-store.edit-advanced-settings', compact('business_card', 'plan_details', 'settings', 'config'));
        }
    }

    // Update Store Advanced Settings
    public function updateStoreAdvancedSetting(Request $request)
    { 
        // Validate request inputs
        $validator = Validator::make($request->all(), [
            'store_id' => 'required',
            'custom_css' => 'nullable|string|max:50000',
            'custom_js' => 'nullable|string|max:50000',
        ]);

        if ($validator->fails()) {
            return back()->with('failed', $validator->messages()->all()[0])->withInput();
        }

        // Find the store belonging to the authenticated user
        $business_card = BusinessCard::where('user_id', Auth::user()->user_id)
            ->where('card_id', $request->store_id)
            ->first();

        // Guard clause: check if store exists
        if (!$business_card) {
            return redirect()->route('user.stores')->with('failed', trans('Store not found!'));
            }

        // Safely evaluate boolean states (handles '1', 1, 'on', true, or unchecked null)
        $isEnablePwa = $request->boolean('is_enable_pwa') ? 1 : 0;
        $isEnableLanguageSwitcher = $request->boolean('is_enable_language_switcher') ? 1 : 0;
        $directoryListing = $request->boolean('directory_listing') ? 1 : 0;

        // Update store advanced settings
        $business_card->is_enable_pwa = $isEnablePwa;
        $business_card->is_enable_language_switcher = $isEnableLanguageSwitcher;
        $business_card->directory_listing = $directoryListing;
            $business_card->custom_css = $request->custom_css;
            $business_card->custom_js = $request->custom_js;
            $business_card->save();

        return redirect()->route('user.edit.store.advanced.setting', $request->store_id)
            ->with('success', trans('Store advanced setting updated successfully!'));
    }
}
