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

use App\BusinessCard;
use App\Helpers\StorageHelper;
use App\Http\Controllers\Controller;
use App\Payment;
use App\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class PaymentLinkController extends Controller
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


    // Payment links
    public function paymentLinks()
    {
        // Queries
        $plan = DB::table('users')->where('user_id', Auth::user()->user_id)->where('status', 1)->first();
        $settings = Setting::where('status', 1)->first();
        $plan_details = json_decode($plan->plan_details);

        if ($plan_details->no_of_payments > 0) {
            return view('user.pages.cards.payment-links', compact('plan_details', 'settings'));
        } else if ($plan_details->no_of_services > 0) {
            return redirect()->route('user.services', request()->segment(3));
        } else if ($plan_details->no_of_vcard_products > 0) {
            return redirect()->route('user.vproducts', request()->segment(3));
        } else if ($plan_details->no_of_galleries > 0) {
            return redirect()->route('user.galleries', request()->segment(3));
        } else if ($plan_details->no_testimonials > 0) {
            return redirect()->route('user.testimonials', request()->segment(3));
        } else {
            return redirect()->route('user.popups', request()->segment(3));
        }
    }

    // Save payment links
    public function savePaymentLinks(Request $request, $id)
    {
        // Find the business card
        $business_card = BusinessCard::where('card_id', $id)->where('user_id', Auth::user()->user_id)->first();
        if (!$business_card) {
            return redirect()->route('user.cards')->with('failed', trans('Card not found!'));
        }

        // Ensure icons are provided and are in array format
        if ($request->has('icon') && is_array($request->icon)) {

            // Fetch user plan
            $plan = DB::table('users')->where('user_id', Auth::user()->user_id)->where('status', 1)->first();
            $plan_details = json_decode($plan->plan_details ?? '{}');

            // Check payment limit
            if (count($request->icon) > ($plan_details->no_of_payments ?? 0)) {
                return redirect()->route('user.payment.links', $id)->with('failed', trans('You have reached the plan limit!'));
            }

            // Validate uploaded payment images
            $fileRules = [];
            $customAttributes = [];
            if ($request->has('type') && is_array($request->type)) {
                foreach ($request->type as $i => $type) {
                    if ($type === 'image' && $request->hasFile("value.$i")) {
                        $fileRules["value.$i"] = 'required|file|image|mimes:jpeg,png,jpg,gif,svg,webp|max:' . env('SIZE_LIMIT', 2048);
                        $customAttributes["value.$i"] = trans('Payment image');
                    }
                }
            }

            if (!empty($fileRules)) {
                $validator = Validator::make($request->all(), $fileRules, [], $customAttributes);
                if ($validator->fails()) {
                    return redirect()->route('user.payment.links', $id)->with('failed', $validator->errors()->first());
                }
            }

            // Delete previous payment links
            Payment::where('card_id', $id)->delete();

            // Loop through and save each payment method
            foreach ($request->icon as $i => $icon) {
                // Validate required fields
                if (
                    isset($request->type[$i]) &&
                    isset($request->label[$i]) &&
                    array_key_exists($i, $request->value) // Avoid undefined key
                ) {
                    $payment = new Payment();
                    $payment->card_id = $id;
                    $payment->type = $request->type[$i];
                    $payment->icon = $icon;
                    $payment->label = $request->label[$i];
                    $payment->position = $i + 1;

                    if ($request->type[$i] === 'image' && $request->hasFile("value.$i")) {
                        // Upload payment image using StorageHelper and get relative path
                        $payment->content = StorageHelper::uploadImage($request->file("value.$i"), 'payments');
                    } else {
                        // For text, link, or UPI
                        $payment->content = $request->value[$i];
                    }

                    $payment->save();
                } else {
                    return redirect()->route('user.payment.links', $id)->with('failed', trans('Please fill out all required fields.'));
                }
            }

            return redirect()->route('user.services', $id)->with('success', trans('Payment links are updated.'));
        }

        // If no icons are submitted, just redirect
        return redirect()->route('user.services', $id)->with('success', trans('Payment links are updated.'));
    }
}
