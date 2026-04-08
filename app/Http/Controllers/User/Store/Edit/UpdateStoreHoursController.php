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
use App\BusinessHour;
use App\StoreBusinessHour;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\Cache\Store;

class UpdateStoreHoursController extends Controller
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

    // Edit store hours
    public function editHours(Request $request, $id)
    {
        // Queries
        $business_card = BusinessCard::where('user_id', Auth::user()->user_id)->where('card_id', $id)->first();

        // Check business card
        if($business_card == null) {
            return redirect()->route('user.stores')->with('failed', trans('Store not found!'));
        }

        $storeHours = StoreBusinessHour::where('store_id', $id)->first();
        $config   = DB::table('config')->get();
        $settings = Setting::where('status', 1)->first();

        return view('user.pages.edit-store.business-hours', compact('business_card', 'storeHours', 'settings', 'config'));
    }

    // Update store hours
    public function updateHours(Request $request)
    {
        // Delete business hours
        StoreBusinessHour::where('store_id', $request->store_id)->delete();

        // Save new business hours
        $businessHour = new StoreBusinessHour();
        $businessHour->user_id = Auth::user()->user_id;
        $businessHour->store_id = $request->store_id;
        $businessHour->business_hours_id = uniqid();
        $businessHour->business_hours = json_encode($request->business_hours);
        $businessHour->status = 1;
        $businessHour->save();

        return redirect()->route('user.edit.store.hours', $request->store_id)->with('success', __('Saved!'));
    }
}
