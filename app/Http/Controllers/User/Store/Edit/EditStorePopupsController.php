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

namespace App\Http\Controllers\User\Store\Edit;

use App\User;
use App\Setting;
use App\BusinessCard;
use App\InformationPop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class EditStorePopupsController extends Controller
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

    // Edit Store Popups
    public function editStorePopups(Request $request, $id)
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

            // Get information popup details from information pops table
            $informationPopUpDetails = InformationPop::where('card_id', $id)->first();

            return view('user.pages.edit-store.edit-popups', compact('business_card', 'plan_details', 'informationPopUpDetails', 'settings', 'config'));
        }
    }

    public function updateStorePopups(Request $request)
    {
        // Store ID
        $id = $request->store_id;

        // Queries
        $business_card = BusinessCard::where('user_id', Auth::user()->user_id)->where('card_id', $id)->first();

        // Check business card
        $plan = DB::table('users')->where('user_id', Auth::user()->user_id)->where('status', 1)->first();
        $plan_details = json_decode($plan->plan_details);

        // Check business card
        if ($business_card == null) {
            return redirect()->route('user.cards')->with('failed', trans('Card not found!'));
        } else {
            // Check is newsletter popup is "enabled"
            $is_newsletter_pop_active = 0;
            if ($request->is_newsletter_pop_active == "1") {
                $is_newsletter_pop_active = 1;
            }

            // Check is information popup is "enabled"
            $is_info_pop_active = 0;
            if ($request->is_info_pop_active == "1") {
                $is_info_pop_active = 1;
            }

            // Check information popup is "enabled"

            if ($is_info_pop_active == 1) {
                if ($request->hasFile('info_pop_image')) {
                    $allowedExtensions = ['jpeg', 'png', 'jpg', 'gif', 'svg', 'webp'];
                    $extension = $request->file('info_pop_image')->extension();

                    if (in_array($extension, $allowedExtensions)) {
                        // Generate unique file name
                        $filename = 'IMG-' . uniqid() . '-' . time() . '.' . $extension;

                        // Save to storage/app/public/information_pop_images
                        Storage::disk('public')->putFileAs('information_pop_images', $request->file('info_pop_image'), $filename);

                        // Generate the public URL: /storage/information_pop_images/IMG-...
                        $info_pop_image = Storage::url('information_pop_images/' . $filename);
                    }
                }

                // Confetti effect
                $confetti_effect = 0;
                if ($request->confetti_effect == "1") {
                    $confetti_effect = 1;
                }

                // Check card_id is exists in information pops table
                if (InformationPop::where('card_id', $id)->exists()) {
                    // Update information popup
                    InformationPop::where('card_id', $id)->update([
                        'confetti_effect' => $confetti_effect,
                        'info_pop_image' => $info_pop_image,
                        'info_pop_title' => $request->info_pop_title,
                        'info_pop_desc' => $request->info_pop_desc,
                        'info_pop_button_text' => $request->info_pop_button_text,
                        'info_pop_button_url' => $request->info_pop_button_url,
                    ]);
                } else {
                    // Create information popup
                    $saveInfoPop = new InformationPop();
                    $saveInfoPop->information_pop_id = uniqid();
                    $saveInfoPop->card_id = $id;
                    $saveInfoPop->confetti_effect = $confetti_effect;
                    $saveInfoPop->info_pop_image = $info_pop_image;
                    $saveInfoPop->info_pop_title = $request->info_pop_title;
                    $saveInfoPop->info_pop_desc = $request->info_pop_desc;
                    $saveInfoPop->info_pop_button_text = $request->info_pop_button_text;
                    $saveInfoPop->info_pop_button_url = $request->info_pop_button_url;
                    $saveInfoPop->save();
                }
            }

            // Update popups
            BusinessCard::where('user_id', Auth::user()->user_id)->where('card_id', $id)->update([
                'is_newsletter_pop_active' => $is_newsletter_pop_active,
                'is_info_pop_active' => $is_info_pop_active,
            ]);

            return redirect()->route('user.edit.store.popups', $id)->with('success', trans('Popups are updated.'));
        }
    }
}
