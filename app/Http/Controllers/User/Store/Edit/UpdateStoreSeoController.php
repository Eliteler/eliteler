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

use App\Setting;
use App\BusinessCard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class UpdateStoreSeoController extends Controller
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

    // Edit store seo
    public function editSeo(Request $request, $id)
    {
        // Queries
        $business_card = BusinessCard::where('user_id', Auth::user()->user_id)->where('card_id', $id)->first();

        // Check business card
        if ($business_card == null) {
            return redirect()->route('user.stores')->with('failed', trans('Store not found!'));
        }

        $config   = DB::table('config')->get();
        $settings = Setting::where('status', 1)->first();

        return view('user.pages.edit-store.seo', compact('business_card', 'settings', 'config'));
    }

    // Update store seo
    public function updateSeo(Request $request)
    {
        // Validate
        $this->validate($request, [
            'store_id' => 'required',
            'meta_title' => 'required',
            'meta_description' => 'required',
            'meta_keywords' => 'required',
        ]);

        // Update store seo
        $business_card = BusinessCard::where('user_id', Auth::user()->user_id)->where('card_id', $request->store_id)->first();

        // Check business card
        if ($business_card == null) {
            return redirect()->route('user.stores')->with('failed', trans('Store not found!'));
        }

        // Meta title
            if (strlen($request->meta_title) > 70) {
                return redirect()->route('user.edit.advanced.setting', $request->store_id)
                    ->with('failed', trans('Meta title must not exceed 70 characters.'));
            }

            // Meta description
            if (strlen($request->meta_description) > 160) {
                return redirect()->route('user.edit.advanced.setting', $request->store_id)
                    ->with('failed', trans('Meta description must not exceed 160 characters.'));
            }

            // Meta keywords
            if (strlen($request->meta_keywords) > 70) {
                return redirect()->route('user.edit.store.advanced.setting', $request->store_id)
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
        BusinessCard::where('card_id', $request->store_id)->update([
            'seo_configurations' => $seoConfig,
        ]);

        return redirect()->route('user.edit.store.seo', $request->store_id)->with('success', __('Updated!'));
    }
}
