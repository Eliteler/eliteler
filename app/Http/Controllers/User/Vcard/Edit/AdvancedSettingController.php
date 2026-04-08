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

namespace App\Http\Controllers\User\Vcard\Edit;

use App\Setting;
use App\BusinessCard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AdvancedSettingController extends Controller
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

    // Edit Advanced settings
    public function editAdvancedSetting(Request $request, $id)
    {
        // Queries
        $business_card = BusinessCard::where('card_id', $id)->first();
        $settings = Setting::where('status', 1)->first();

        // Check business card
        $plan = DB::table('users')->where('user_id', Auth::user()->user_id)->where('status', 1)->first();
        $plan_details = json_decode($plan->plan_details);

        // Check business card
        if ($business_card == null) {
            return redirect()->route('user.cards')->with('failed', trans('Card not found!'));
        } else {
            if ($plan_details->password_protected == 1 || $plan_details->advanced_settings == 1) {
                return view('user.pages.edit-cards.edit-advanced-settings', compact('plan_details', 'business_card', 'settings'));
            } else {
                return redirect()->route('user.cards')->with('success', trans('Your virtual business card is ready.'));
            }
        }
    }

    // Update Advanced settings
    public function updateAdvancedSetting(Request $request, $id)
    {
        // Queries
        $business_card = BusinessCard::where('card_id', $id)->first();

        // Check business card
        if ($business_card == null) {
            return redirect()->route('user.cards')->with('failed', trans('Card not found!'));
        } else {
            // Set password
            $password = $request->password;
            if ($request->password_protected == "on") {
                $password = null;
            }

            // Check pwa enable/disable
            if ($request->is_enable_pwa == "on") {
                $request->is_enable_pwa = 1;
            } else {
                $request->is_enable_pwa = 0;
            }

            // Check language switcher enable/disable
            if ($request->is_enable_language_switcher == "on") {
                $request->is_enable_language_switcher = 1;
            } else {
                $request->is_enable_language_switcher = 0;
            }

            // Check directory listing enable/disable
            if ($request->directory_listing == "on") {
                $request->directory_listing = 1;
            } else {
                $request->directory_listing = 0;
            }

            // Meta title
            if (strlen($request->meta_title) > 70) {
                return redirect()->route('user.edit.advanced.setting', $id)
                    ->with('failed', trans('Meta title must not exceed 70 characters.'));
            }

            // Meta description
            if (strlen($request->meta_description) > 160) {
                return redirect()->route('user.edit.advanced.setting', $id)
                    ->with('failed', trans('Meta description must not exceed 160 characters.'));
            }

            // Meta keywords
            if (strlen($request->meta_keywords) > 70) {
                return redirect()->route('user.edit.advanced.setting', $id)
                    ->with('failed', trans('Meta keywords must not exceed 70 characters.'));
            }

            // Favicon
            $favicon = null;
            if ($request->hasFile('favicon')) {
                $fileName = uniqid() . '.png';
                Storage::disk('public')->putFileAs('vcard/favicons', $request->file('favicon'), $fileName);
                $favicon = 'storage/vcard/favicons/' . $fileName;
            }

            // JSON encode
            $seoConfig = json_encode([
                'favicon' => $favicon,
                'meta_title' => $request->meta_title,
                'meta_description' => $request->meta_description,
                'meta_keywords' => $request->meta_keywords,
            ]);

            // Update
            BusinessCard::where('card_id', $id)->update([
                'password' => $password,
                'custom_css' => $request->custom_css,
                'custom_js' => $request->custom_js,
                'seo_configurations' => $seoConfig,
                'is_enable_pwa' => $request->is_enable_pwa,
                'is_enable_language_switcher' => $request->is_enable_language_switcher,
                'directory_listing' => $request->directory_listing,
            ]);

            return redirect()->route('user.edit.section.title', $id)->with('success', trans('Advanced settings are updated.'));
        }
    }
}
