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

use App\User;
use App\Theme;
use App\Setting;
use App\Currency;
use Carbon\Carbon;
use App\BusinessCard;
use App\StoreBusinessHour;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class CreateController extends Controller
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
 
    // Create Store
    public function CreateStore()
    {
        // Queries
        $themes = Theme::where('theme_description', 'WhatsApp Store')->where('status', 1)->orderBy('id', 'asc')->get();
        $settings = Setting::where('status', 1)->first();
        $stores = BusinessCard::where('user_id', Auth::user()->user_id)->where('card_type', 'store')->where('card_status', 'activated')->count();

        // Get plan details
        $plan = DB::table('users')->where('user_id', Auth::user()->user_id)->where('status', 1)->first();
        $currencies = Currency::where('status', 1)->get();
        $plan_details = json_decode($plan->plan_details);

        // Check validity
        $validity = User::where('user_id', Auth::user()->user_id)->where('status', 1)->where('plan_validity', '>=', Carbon::now())->count();

        // Get number of stores
        if ($plan_details->no_of_stores == 999) {
            $no_of_stores = 999999;
        } else {
            $no_of_stores = $plan_details->no_of_stores;
        }

        // Check number of stores
        if ($validity == 1) {
            if ($stores < $no_of_stores) {
                return view('user.pages.store.create-store', compact('themes', 'settings', 'plan_details', 'currencies'));
            } else {
                return redirect()->route('user.stores')->with('failed', trans('The maximum limit has been exceeded. Please upgrade your plan.'));
            }
        } else {
            // Redirect
            return redirect()->route('user.stores')->with('failed', trans('Your plan is over. Choose your plan renewal or new package and use it.'));
        }
    }

    // Save Store
    public function saveStore(Request $request)
    {
        // Validate
        $validator = Validator::make($request->all(), [
            'theme_id' => 'required',
            'card_lang' => 'required',
            'banner' => 'required',
            'logo' => 'required',
            'title' => 'required',
            'currency' => 'required',
            'subtitle' => 'required',
            'country_code' => 'required',
            'whatsapp_no' => 'required',
            'whatsapp_msg' => 'required',
        ]);

        // Validate alert message 
        if ($validator->fails()) {
            return back()->with('failed', $validator->messages()->all()[0])->withInput();
        }

        // Check theme_id is store
        $themeExists = Theme::where('theme_id', $request->theme_id)->where('theme_description', 'WhatsApp Store')->where('status', 1)->count();

        if ($themeExists == 0) {
            return back()->with('failed', trans('Invalid Theme'));
        }

        // Unique card ID (personalized_link)
        $cardId = uniqid();

        if ($request->link) {
            $personalized_link = $request->link;
        } else {
            $personalized_link = $cardId;
        }

        // Remove https:// or http://
        $personalized_link = str_replace('https://', 'http-', $personalized_link);
        $personalized_link = str_replace('http://', 'http-', $personalized_link);

        // Remove www.
        $personalized_link = str_replace('www.', 'www-', $personalized_link);

        // Convert accented characters to ASCII
        $personalized_link = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $personalized_link);

        // Remove all characters except letters, numbers, and hyphens
        $personalized_link = preg_replace('/[^a-zA-Z0-9-]/', '', $personalized_link);

        // Optionally, convert to lowercase
        $personalized_link = strtolower($personalized_link);

        // Queries
        $cards = BusinessCard::where('user_id', Auth::user()->user_id)->where('card_type', 'store')->where('card_status', 'activated')->count();
        $user_details = User::where('user_id', Auth::user()->user_id)->first();
        $plan_details = json_decode($user_details->plan_details, true);

        // Upload store logo
        $logo = $request->logo;
        $arrayBanners = explode(",", $request->banner);

        // Upload store banner
        $banner = [];
        for ($i = 0; $i < count($arrayBanners); $i++) {
            $banner[$i] = $arrayBanners[$i];
        }

        // Remove base url
        $banner = str_replace(env('APP_URL'), '', $banner);

        // Store details
        $store_details = [];

        $store_details['whatsapp_no'] = $request->country_code . "" . $request->whatsapp_no;
        $store_details['whatsapp_msg'] = $request->whatsapp_msg;
        $store_details['currency'] = $request->currency;

        // Unique Store URL
        $card_url = strtolower(preg_replace('/\s+/', '-', $personalized_link));

        // Get current store count
        $current_card = BusinessCard::where('card_url', $card_url)->where('card_status', '!=', 'deleted')->count();

        // Get store count
        if ($plan_details['no_of_stores'] == 999) {
            $no_of_stores = 999999;
        } else {
            $no_of_stores = $plan_details['no_of_stores'];
        }

        // Check persionalize link
        if ($current_card == 0) {

            // Checking, If the user plan allowed card creation is less than created card.
            if ($cards < $no_of_stores) {
                try {

                    // Card ID
                    $card_id = $cardId;

                    // // Save
                    $card = new BusinessCard();
                    $card->card_id = $card_id;
                    $card->user_id = Auth::user()->user_id;
                    $card->theme_id = $request->theme_id;
                    $card->card_lang = $request->card_lang;
                    $card->cover = json_encode($banner);
                    $card->profile = $logo;
                    $card->card_url = $card_url;
                    $card->card_type = 'store';
                    $card->title = $request->title;
                    $card->sub_title = $request->subtitle;
                    $card->description = json_encode($store_details);
                    $card->save();

                    // Set default business hours for store
                    $business_hours = [];
                    $business_hours['monday'] = ['start' => '00:01', 'end' => '23:59'];
                    $business_hours['tuesday'] = ['start' => '00:01', 'end' => '23:59'];
                    $business_hours['wednesday'] = ['start' => '00:01', 'end' => '23:59'];
                    $business_hours['thursday'] = ['start' => '00:01', 'end' => '23:59'];
                    $business_hours['friday'] = ['start' => '00:01', 'end' => '23:59'];
                    $business_hours['saturday'] = ['start' => '00:01', 'end' => '23:59'];
                    $business_hours['sunday'] = ['start' => '00:01', 'end' => '23:59'];

                    $request->business_hours = $business_hours;

                    $businessHour = new StoreBusinessHour();
                    $businessHour->user_id = Auth::user()->user_id;
                    $businessHour->store_id = $card_id;
                    $businessHour->business_hours_id = uniqid();
                    $businessHour->business_hours = json_encode($request->business_hours);
                    $businessHour->status = 1;
                    $businessHour->save();

                    // Set default delivery options for store
                    $delivery_options = [];
                    $delivery_options['order_for_delivery'] = 1;
                    $delivery_options['take_away'] = 0;
                    $delivery_options['dine_in'] = 0;
                    $request->delivery_options = $delivery_options;

                    $business_card = BusinessCard::where('card_id', $card_id)->first();
                    $business_card->delivery_options = json_encode($request->delivery_options);
                    $business_card->save();

                    return redirect()->route('user.edit.products', $card_id)->with('success', trans('New WhatsApp Store Created Successfully!'));
                } catch (\Exception $th) {

                    // Alert (Personalized link was already registered)
                    return redirect()->route('user.create.store')->with('failed', trans('Sorry, the personalized link was already registered.'));
                }
            } else {

                // Alert (Maximum store creation limit is exceeded,)
                return redirect()->route('user.create.store')->with('failed', trans('Maximum store creation limit is exceeded, Please upgrade your plan to add more store(s).'));
            }
        } else {

            // Alert (Personalized link was already registered)
            return redirect()->route('user.create.store')->with('failed', trans('Sorry, the personalized link was already registered.'));
        }
    }

    // Cropping image
    public function storeCroppedImage(Request $request)
    {
        $croppedImage = $request->file('croppedImage');

        // Validate image (optional but good practice)
        $validated = $request->validate([
            'croppedImage' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:' . env('SIZE_LIMIT', 2048),
        ]);

        // Generate unique name
        $imageName = Str::random(20) . '.' . $croppedImage->getClientOriginalExtension();

        // Save to storage/app/public/store/images
        Storage::disk('public')->putFileAs('store/images', $croppedImage, $imageName);

        // Generate accessible URL
        $imageUrl = asset('storage/store/images/' . $imageName);

        return response()->json([
            'success' => true,
            'imageUrl' => $imageUrl,
        ]);
    }
}
