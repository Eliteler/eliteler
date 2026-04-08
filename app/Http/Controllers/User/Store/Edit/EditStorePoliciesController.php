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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class EditStorePoliciesController extends Controller
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
     * Show the store policies.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */

    // Edit Store Policies
    public function editStorePolicies(Request $request, $id)
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
            $plan = User::where('user_id', Auth::user()->user_id)
                ->where('status', 1)
                ->first();
            $plan_details = json_decode($plan->plan_details, true);

            return view('user.pages.edit-store.edit-policies', compact('business_card', 'plan_details', 'settings', 'config'));
        }
    }

    public function updateStorePolicies(Request $request)
    { 
        // Queries
        $business_card = BusinessCard::where('user_id', Auth::user()->user_id)->where('card_id', $request->store_id)->first();

        // Check business card
        if ($business_card == null) {
            return redirect()->route('user.stores')->with('failed', trans('Store not found!'));
        } else {
            // Update policies
            $business_card->terms_and_conditions = $request->terms_and_conditions_textarea;
            $business_card->privacy_policy = $request->privacy_policy_textarea;
            $business_card->refund_policy = $request->refund_policy_textarea;
            $business_card->shipping_policy = $request->shipping_policy_textarea;
            $business_card->cookie_policy = $request->cookie_policy_textarea;
            $business_card->customer_support_policy = $request->customer_support_policy_textarea;
            $business_card->save();

            return redirect()->route('user.edit.store.policies', $request->store_id)->with('success', trans('Store advanced setting updated successfully!'));
        }
    }
}
