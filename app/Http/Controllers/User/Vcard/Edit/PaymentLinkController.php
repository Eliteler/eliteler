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

use App\Payment;
use App\Setting;
use App\BusinessCard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

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
    public function paymentLinks(Request $request, $id)
    {
        // Queries
        $business_card = BusinessCard::where('card_id', $id)->first();

        // Check business card
        if ($business_card == null) {
            return redirect()->route('user.cards')->with('failed', trans('Card not found!'));
        } else {
            // Queries
            $payments = Payment::where('card_id', $id)->orderBy('position', 'asc')->get();
            $plan = DB::table('users')->where('user_id', Auth::user()->user_id)->where('status', 1)->first();
            $plan_details = json_decode($plan->plan_details);
            $settings = Setting::where('status', 1)->first();

            if ($plan_details->no_of_payments > 0) {
                return view('user.pages.edit-cards.edit-payment-links', compact('payments', 'plan_details', 'settings'));
            } else if ($plan_details->no_of_services > 0) {
                return redirect()->route('user.edit.services', request()->segment(3));
            } else if ($plan_details->no_of_vcard_products > 0) {
                return redirect()->route('user.edit.vproducts', request()->segment(3));
            } else if ($plan_details->no_of_galleries > 0) {
                return redirect()->route('user.edit.galleries', request()->segment(3));
            } else if ($plan_details->no_testimonials > 0) {
                return redirect()->route('user.edit.testimonials', request()->segment(3));
            } else {
                return redirect()->route('user.edit.popups', request()->segment(3));
            }
        }
    }

    // Update payment links
    public function updatePaymentLinks(Request $request, $id)
    {
        // Find business card
        $business_card = BusinessCard::where('card_id', $id)->first();
        if (!$business_card) {
            return redirect()->route('user.cards')->with('failed', trans('Card not found!'));
        }

        // If no icons are submitted or empty array, delete all existing payment links
        if (!$request->has('icon') || !is_array($request->icon) || empty($request->icon)) {
            Payment::where('card_id', $id)->delete();

            return redirect()->route('user.edit.services', $id)
                ->with('success', trans('All payment links have been removed.'));
        }

        // Fetch user plan
        $plan = DB::table('users')
            ->where('user_id', Auth::user()->user_id)
            ->where('status', 1)
            ->first();

        $plan_details = json_decode($plan->plan_details ?? '{}');
        $max_allowed = (int) ($plan_details->no_of_payments ?? 0);

        // Validate limit - Reject if count is more than allowed
        if (count($request->icon) > $max_allowed) {
            return redirect()->route('user.edit.payment.links', $id)
                ->with('failed', trans('Maximum links limit exceeded.'));
        }

        // Validate all required fields before deleting existing links
        foreach ($request->icon as $i => $icon) {
            if (
                !isset($request->type[$i]) ||
                !isset($request->label[$i]) ||
                (!isset($request->value[$i]) && !$request->hasFile("value.$i"))
            ) {
                return redirect()->route('user.edit.payment.links', $id)
                    ->with('failed', trans('Please fill out all required fields.'));
            }
        }

        // Get temporary title before delete
        $temp_title = Payment::where('card_id', $id)->first();
        $tempTitle  = $temp_title?->title ?? 'Payment Links';

        // All fields validated, now delete old links
        Payment::where('card_id', $id)->delete();

        // Save each new payment link
        foreach ($request->icon as $i => $icon) {
            $payment = new Payment();
            $payment->card_id = $id;
            $payment->title = $tempTitle;
            $payment->type = $request->type[$i];
            $payment->icon = $icon;
            $payment->label = $request->label[$i];
            $payment->position = $i + 1;

            if ($request->type[$i] === 'image' && $request->hasFile("value.$i")) {
                $file = $request->file("value.$i");
                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $directory = 'payments';

                // Store using putFileAs
                Storage::disk('public')->putFileAs($directory, $file, $filename);

                // Set the public path
                $payment->content = Storage::url("$directory/$filename");
            } else {
                // Handle text/UPI/link/etc
                $payment->content = $request->value[$i];
            }

            $payment->save();
        }

        return redirect()->route('user.edit.services', $id)
            ->with('success', trans('Payment links are updated.'));
    }
}
