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

namespace App\Http\Controllers\Admin;

use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

class CreateStoreController extends Controller
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
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function CreateStore(Request $request, $id)
    {
        $user = null;

        DB::transaction(function () use (&$user) {

            // Find or create admin user
            $user = User::where('email', Auth::user()->email)->first();

            // Assign plan only if not already assigned
            $user->update([
                'plan_id'        => '606732cb4ec9b',
                'plan_validity'  => Carbon::now()->addDays(18980),
                'plan_details'   => json_encode([
                    'id' => 3,
                    'plan_id' => '606732cb4ec9b',
                    'dodo_payments_plan_price_id' => null,
                    'paddle_plan_price_id' => null,
                    'in_app_purchase_id' => null,
                    'plan_type' => 'BOTH',
                    'plan_name' => 'Professional',
                    'plan_description' => 'Nullam diam arcu, sodales quis convallis sit amet, sagittis varius ligula.',
                    'plan_price' => 48,
                    'validity' => 9999,
                    'trial' => 0,
                    'no_of_vcards' => 999,
                    'no_of_services' => 999,
                    'no_of_vcard_products' => 999,
                    'no_of_galleries' => 999,
                    'no_of_links' => 999,
                    'no_of_payments' => 999,
                    'no_testimonials' => 999,
                    'business_hours' => 1,
                    'contact_form' => 1,
                    'appointment' => 1,
                    'service_booking' => 1,
                    'custom_domain' => 1,
                    'nfc_card' => 1,
                    'google_wallet' => 1,
                    'no_of_google_wallets' => 999,
                    'no_of_enquires' => 999,
                    'no_of_stores' => 999,
                    'no_of_categories' => 999,
                    'no_of_store_products' => 999,
                    'pwa' => 1,
                    'password_protected' => 1,
                    'advanced_settings' => 1,
                    'storage' => 999,
                    'additional_tools' => 0,
                    'personalized_link' => 1,
                    'hide_branding' => 1,
                    'free_setup' => 1,
                    'free_support' => 1,
                    'recommended' => 0,
                    'is_private' => 0,
                    'status' => 1,
                    'created_at' => '2021-08-06T16:34:40.000000Z',
                    'updated_at' => '2025-11-24T06:54:13.000000Z'
                ]),
            ]);
        });

        // Set session impersonate
        Session::put('impersonate', true);

        // Redirect to user cards target="blank"
        return redirect()->route('user.' . $id);
    }

    // Logout impersonate
    public function impersonateLogout()
    {
        // Remove impersonate
        Session::forget('impersonate');

        return redirect()->route('admin.dashboard');
    }
}
