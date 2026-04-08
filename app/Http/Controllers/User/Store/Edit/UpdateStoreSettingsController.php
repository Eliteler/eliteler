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

class UpdateStoreSettingsController extends Controller
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

    // Edit store settings
    public function editSettings(Request $request, $id)
    {
        // Queries
        $business_card = BusinessCard::where('user_id', Auth::user()->user_id)->where('card_id', $id)->first();

        // Check business card
        if($business_card == null) {
            return redirect()->route('user.stores')->with('failed', trans('Store not found!'));
        }
        
        $config   = DB::table('config')->get();
        $settings = Setting::where('status', 1)->first();

        // Decode description JSON safely
        $description = json_decode($business_card->description, true);

        // Ensure it's an array
        if (!is_array($description)) {
            $description = [];
        }

        // Set default if invoice_prefix is missing
        $description['invoice_prefix'] = $description['invoice_prefix'] ?? 'INV-';

        // Assign back to model (optional, if needed later)
        $business_card->description = $description;

        return view('user.pages.edit-store.settings', compact('business_card', 'settings', 'config'));
    }

    // Update store settings
    public function updateSettings(Request $request)
    {
        // Validate
        $this->validate($request, [
            'store_id' => 'required',
        ]);

        // Check order_for_delivery
        $orderForDelivery = 0;
        if ($request->order_for_delivery == 1) {
            $orderForDelivery = 1;
        } 

        // Check take_away
        $takeAway = 0;
        if ($request->take_away == 1) {
            $takeAway = 1;
        }

        // Check dine_in
        $dineIn = 0;
        if ($request->dine_in == 1) {
            $dineIn = 1;
        }

        // Check minimum delivery option selected
        if ($orderForDelivery == 0 && $takeAway == 0 && $dineIn == 0) {
            return redirect()->route('user.edit.store.settings', $request->store_id)->with('failed', __('Please select at least one delivery option.'));
        }

        // Update delivery options
        $request->delivery_options = json_encode([
            'order_for_delivery' => $orderForDelivery,
            'take_away' => $takeAway,
            'dine_in' => $dineIn,
        ]);

        // Get description from business card
        $business_card = BusinessCard::where('user_id', Auth::user()->user_id)->where('card_id', $request->store_id)->first();
        $description = json_decode($business_card->description, true);

        // Update invoice prefix
        $description['invoice_prefix'] = $request->invoice_prefix;
        $business_card->description = json_encode($description);

        // Update store settings
        $business_card = BusinessCard::where('user_id', Auth::user()->user_id)->where('card_id', $request->store_id)->first();
        $business_card->delivery_options = $request->delivery_options;
        $business_card->description = json_encode($description);
        $business_card->save();

        return redirect()->route('user.edit.store.settings', $request->store_id)->with('success', __('Updated!'));
    }
}
