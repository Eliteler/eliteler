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

    // Advanced settings
    public function advancedSetting(Request $request, $id)
    {
        // Queries
        $settings = Setting::where('status', 1)->first();

        $plan = DB::table('users')->where('user_id', Auth::user()->user_id)->where('status', 1)->first();
        $plan_details = json_decode($plan->plan_details);

        $business_card = BusinessCard::where('card_id', $id)->first();

        if ($plan_details->advanced_settings == 1 || $plan_details->password_protected == 1) {
            return view('user.pages.cards.advanced-settings', compact('plan_details', 'settings', 'business_card'));
        } else {
            return redirect()->route('user.cards')->with('success', trans('Your virtual business card is updated!'));
        }
    }

    // Save Advanced settings
    public function saveAdvancedSetting(Request $request, $id)
    {
        // Queries
        $business_card = BusinessCard::where('card_id', $id)->first();

        // Check business card
        if ($business_card == null) {
            return redirect()->route('user.cards')->with('failed', trans('Card not found!'));
        } else {
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

            // Update
            BusinessCard::where('card_id', $id)->update([
                'password' => $request->password,
                'custom_css' => $request->custom_css,
                'custom_js' => $request->custom_js,
                'seo_configurations' => $seoConfig,
                'is_enable_pwa' => $request->is_enable_pwa,
                'is_enable_language_switcher' => $request->is_enable_language_switcher,
            ]);

            return redirect()->route('user.cards')->with('success', trans('Your virtual business card is updated!'));
        }
    }
}
