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

namespace App\Http\Controllers\User\Store\Create;

use App\Medias;
use App\Setting;
use App\Category;
use App\BusinessCard;
use App\StoreProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
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

    // Products
    public function products()
    {
        // Get plan details
        $plan = DB::table('users')->where('user_id', Auth::user()->user_id)->where('status', 1)->first();
        $plan_details = json_decode($plan->plan_details);

        // Queries
        $categories = Category::where('user_id', Auth::user()->user_id)->where('status', 1)->get();
        $media = Medias::where('user_id', Auth::user()->user_id)->orderBy('id', 'desc')->get();
        $settings = Setting::where('status', 1)->first();

        return view('user.pages.store.products', compact('plan_details', 'media', 'settings', 'categories'));
    }

    // Save Products
    public function saveProducts(Request $request, $id)
    {
        // Queries (only the user's own store)
        $business_card = BusinessCard::where('user_id', Auth::user()->user_id)->where('card_id', $id)->first();

        // Check business card
        if ($business_card == null) {
            return redirect()->route('user.stores')->with('failed', trans('Store not found!'));
        } else {

            // Get plan details
            $plan = DB::table('users')->where('user_id', Auth::user()->user_id)->where('status', 1)->first();
            $plan_details = !empty($plan->plan_details) ? json_decode($plan->plan_details) : null;

            // Check product image
            if (is_array($request->badge)) {

                // Check products limit
                if (count($request->badge) <= ($plan_details->no_of_store_products ?? 0)) {

                    // Delete previous products and their variations
                    DB::table('store_product_variants')->whereIn('product_id', StoreProduct::where('card_id', $id)->select('product_id'))->delete();
                    StoreProduct::where('card_id', $id)->delete();

                    // Check dynamic fields
                    for ($i = 0; $i < count($request->badge); $i++) {

                        // Save
                        $service = new StoreProduct();
                        $service->card_id = $id;
                        $service->product_id = uniqid();
                        $service->category_id = $request->categories[$i];
                        $service->badge = $request->badge[$i];
                        $service->product_image = $request->product_image[$i];
                        $service->product_name = $request->product_name[$i];
                        $service->product_description = $request->product_description[$i];
                        $service->regular_price = $request->regular_price[$i];
                        $service->sales_price = $request->sales_price[$i];
                        $service->product_status = $request->product_status[$i];
                        $service->save();
                    }

                    return redirect()->route('user.stores')->with('success', trans('Your WhatsApp store link is ready!'));
                } else {
                    return redirect()->route('user.products', $id)->with('failed', trans('You have reached the plan limit!'));
                }
            } else {
                return redirect()->route('user.products', $id)->with('failed', trans('You must add atleast one product.'));
            }
        }
    }
}
