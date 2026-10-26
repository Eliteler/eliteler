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
use App\Currency;
use App\Http\Controllers\Controller;
use App\Plan;
use App\Setting;
use App\Theme;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class UpdateController extends Controller
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

    // Edit Store
    public function editStore(Request $request, $id)
    {
        // Queries
        $themes = Theme::where('theme_description', 'WhatsApp Store')->where('status', 1)->orderBy('id', 'asc')->get();
        $business_card = BusinessCard::where('user_id', Auth::user()->user_id)->where('card_id', $id)->first();

        // Check business card
        if ($business_card == null) {
            return redirect()->route('user.stores')->with('failed', trans('Store not found!'));
        } else {

            // Check card type is "STORE"
            if ($business_card->card_type == "store") {

                // Queries
                $settings = Setting::where('status', 1)->first();
                $currencies = Currency::where('status', 1)->get();
                $plan_details = Plan::where('plan_id', Auth::user()->plan_id)->first();
                $store_details = json_decode($business_card->description);

                return view('user.pages.edit-store.edit-store', compact('themes', 'business_card', 'settings', 'plan_details', 'store_details', 'currencies'));
            } else {
                return redirect()->route('user.edit.card', $id);
            }
        }
    }

    // Update store
    public function updateStore(Request $request, $id)
    {
        // Check theme_id is store
        $themeExists = Theme::where('theme_id', $request->theme_id)
            ->where('theme_description', 'WhatsApp Store')
            ->where('status', 1)
            ->count();

        if ($themeExists == 0) {
            return back()->with('failed', trans('Invalid Theme'))->withInput();
        }

        // Get business card
        $business_card = BusinessCard::where('user_id', Auth::user()->user_id)
            ->where('card_id', $id)
            ->first();

        // Check business card
        if ($business_card == null) {
            return redirect()
                ->route('user.stores')
                ->with('failed', trans('Store not found!'));
        }

        // Get user plan details
        $user_details = User::where('user_id', Auth::user()->user_id)->first();
        $plan_details = json_decode($user_details->plan_details, true);

        // Personalized Link
        if (!empty($plan_details['personalized_link'])) {
            // Personalized Link is available in the plan
            $personalized_link = $request->link ?: $business_card->card_url;
        } else {
            // Personalized Link is not available in the plan
            // Always keep the existing Store URL
            $personalized_link = $business_card->card_url;
        }

        // Clean Personalized Link
        $personalized_link = trim($personalized_link);

        // Remove https://, http:// and www.
        $personalized_link = str_replace(
            ['https://', 'http://', 'www.'],
            '',
            $personalized_link
        );

        // Convert accented characters to ASCII
        $personalized_link = iconv(
            'UTF-8',
            'ASCII//TRANSLIT//IGNORE',
            $personalized_link
        );

        // Remove all characters except letters, numbers, and hyphens
        $personalized_link = preg_replace(
            '/[^a-zA-Z0-9-]/',
            '',
            $personalized_link
        );

        // Convert to lowercase
        $personalized_link = strtolower($personalized_link);

        // Check if Store URL already exists
        // Exclude current store from duplicate check
        $current_card = BusinessCard::where('card_url', $personalized_link)
            ->where('card_id', '!=', $id)
            ->where('card_status', '!=', 'deleted')
            ->count();

        if ($current_card > 0) {
            return redirect()
                ->route('user.edit.store', $id)
                ->with(
                    'failed',
                    trans('Sorry, the personalized link was already registered.')
                )
                ->withInput();
        }

        // App Base URLs to remove
        $appUrls = array_filter([
            url('/'),
            config('app.url'),
            env('APP_URL'),
        ]);

        // Generate JSON/array for store details
        $store_details = [
            'whatsapp_no'  => $request->country_code . $request->whatsapp_no,
            'whatsapp_msg' => $request->whatsapp_msg,
            'currency'     => $request->currency,
        ];

        // Setup common data for update
        $updateData = [
            'theme_id'    => $request->theme_id,
            'card_lang'   => $request->card_lang,
            'card_url'    => $personalized_link,
            'title'       => $request->title,
            'title2'      => $request->title2,
            'sub_title'   => $request->subtitle,
            'subtitle2'   => $request->subtitle2,
            'copyright'   => $request->copyright,
            'description' => $store_details,
        ];

        // 1. Case: Both Banner and Logo are NOT updated
        if ($request->banner == null && $request->logo == null) {

            // Keep old assets
        }

        // 2. Case: Only Banner is updated
        else if ($request->logo == null) {

            $validator = Validator::make($request->all(), [
                'banner' => 'required',
            ]);

            if ($validator->fails()) {
                return back()
                    ->with('failed', $validator->messages()->all()[0])
                    ->withInput();
            }

            $rawBanners = $request->banner;

            if (is_array($rawBanners)) {
                $rawBanners = json_encode($rawBanners);
            }

            $cleanedBannersStr = str_replace(
                ['\\', '[', ']', '"', "'"],
                '',
                $rawBanners
            );

            $bannerItems = explode(',', $cleanedBannersStr);
            $cleanBanners = [];

            foreach ($bannerItems as $item) {
                $item = trim($item);

                if (!empty($item)) {
                    $cleanPath = str_replace($appUrls, '', $item);
                    $cleanPath = '/' . ltrim($cleanPath, '/');

                    if (!empty($cleanPath) && $cleanPath !== '/') {
                        $cleanBanners[] = $cleanPath;
                    }
                }
            }

            $updateData['cover'] = json_encode(
                array_values(array_unique($cleanBanners)),
                JSON_UNESCAPED_SLASHES
            );
        }

        // 3. Case: Only Logo is updated
        else if ($request->banner == null) {

            $validator = Validator::make($request->all(), [
                'logo' => 'required',
            ]);

            if ($validator->fails()) {
                return back()
                    ->with('failed', $validator->messages()->all()[0])
                    ->withInput();
            }

            $rawLogo = $request->logo;

            $cleanedLogo = str_replace(
                ['\\', '[', ']', '"', "'"],
                '',
                $rawLogo
            );

            $logo = trim($cleanedLogo);
            $logo = str_replace($appUrls, '', $logo);

            $updateData['profile'] = '/' . ltrim($logo, '/');
        }

        // 4. Case: Both Banner and Logo are updated
        else {

            $validator = Validator::make($request->all(), [
                'banner' => 'required',
                'logo'   => 'required',
            ]);

            if ($validator->fails()) {
                return back()
                    ->with('failed', $validator->messages()->all()[0])
                    ->withInput();
            }

            // Clean Logo
            $rawLogo = $request->logo;

            $cleanedLogo = str_replace(
                ['\\', '[', ']', '"', "'"],
                '',
                $rawLogo
            );

            $logo = trim($cleanedLogo);
            $logo = str_replace($appUrls, '', $logo);

            $updateData['profile'] = '/' . ltrim($logo, '/');

            // Clean Banners
            $rawBanners = $request->banner;

            if (is_array($rawBanners)) {
                $rawBanners = json_encode($rawBanners);
            }

            $cleanedBannersStr = str_replace(
                ['\\', '[', ']', '"', "'"],
                '',
                $rawBanners
            );

            $bannerItems = explode(',', $cleanedBannersStr);
            $cleanBanners = [];

            foreach ($bannerItems as $item) {
                $item = trim($item);

                if (!empty($item)) {
                    $cleanPath = str_replace($appUrls, '', $item);
                    $cleanPath = '/' . ltrim($cleanPath, '/');

                    if (!empty($cleanPath) && $cleanPath !== '/') {
                        $cleanBanners[] = $cleanPath;
                    }
                }
            }

            $updateData['cover'] = json_encode(
                array_values(array_unique($cleanBanners)),
                JSON_UNESCAPED_SLASHES
            );
        }

        // Perform Update
        BusinessCard::where('user_id', Auth::user()->user_id)
            ->where('card_id', $id)
            ->update($updateData);

        return redirect()
            ->route('user.edit.store', $id)
            ->with('success', trans('Updated!'));
    }
}
