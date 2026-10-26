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

use App\BusinessCard;
use App\Currency;
use App\Helpers\StorageHelper;
use App\Http\Controllers\Controller;
use App\Setting;
use App\StoreBusinessHour;
use App\Theme;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

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
        $stores = BusinessCard::where('user_id', Auth::user()->user_id)->where('card_type', 'store')->where('card_status', 'activated')->where('status', '1')->count();

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

        // Generate card ID
        $cardId = uniqid();

        // Set session
        session(['cardId' => $cardId]);

        // Check number of stores
        if ($validity == 1) {
            if ($stores < $no_of_stores) {
                return view('user.pages.store.create-store', compact('themes', 'settings', 'plan_details', 'currencies', 'cardId'));
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
            'theme_id'      => 'required',
            'card_lang'     => 'required',
            'banner'        => 'required',
            'logo'          => 'required',
            'title'         => 'required',
            'currency'      => 'required',
            'subtitle'      => 'required',
            'country_code'  => 'required',
            'whatsapp_no'   => 'required',
            'whatsapp_msg'  => 'required',
        ]);

        // Validate alert message
        if ($validator->fails()) {
            return back()
                ->with('failed', $validator->messages()->all()[0])
                ->withInput();
        }

        // Check theme_id is store
        $themeExists = Theme::where('theme_id', $request->theme_id)
            ->where('theme_description', 'WhatsApp Store')
            ->where('status', 1)
            ->count();

        if ($themeExists == 0) {
            return back()->with('failed', trans('Invalid Theme'));
        }

        // Queries
        $user_details = User::where('user_id', Auth::user()->user_id)->first();
        $plan_details = json_decode($user_details->plan_details, true);

        // Generate Unique Card ID
        $cardId = session('cardId');

        // Personalized Link
        // Only use the requested link if the plan supports Personalized Link.
        if (!empty($plan_details['personalized_link'])) {
            $personalized_link = $request->link ?: $cardId;
        } else {
            $personalized_link = $cardId;
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

        // Queries
        $cards = BusinessCard::where('user_id', Auth::user()->user_id)
            ->where('card_type', 'store')
            ->where('card_status', 'activated')
            ->where('status', '1')
            ->count();

        // App Base URLs to remove
        $appUrls = array_filter([
            url('/'),
            config('app.url'),
            env('APP_URL'),
        ]);

        // -------------------------------------------------------------
        // 1. CLEAN & FORMAT LOGO PATH
        // -------------------------------------------------------------

        $rawLogo = $request->logo;

        $cleanedLogo = str_replace(
            ['\\', '[', ']', '"', "'"],
            '',
            $rawLogo
        );

        $logo = trim($cleanedLogo);
        $logo = str_replace($appUrls, '', $logo);
        $logo = '/' . ltrim($logo, '/');

        // -------------------------------------------------------------
        // 2. CLEAN & FORMAT BANNER ARRAY
        // -------------------------------------------------------------

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

        $cleanBanners = array_values(array_unique($cleanBanners));

        // Store details
        $store_details = [
            'whatsapp_no'  => $request->country_code . $request->whatsapp_no,
            'whatsapp_msg' => $request->whatsapp_msg,
            'currency'     => $request->currency,
        ];

        // Unique Store URL
        $card_url = $personalized_link;

        // Check existing card URL
        $current_card = BusinessCard::where('card_url', $card_url)
            ->where('card_status', '!=', 'deleted')
            ->count();

        $no_of_stores = ($plan_details['no_of_stores'] == 999)
            ? 999999
            : $plan_details['no_of_stores'];

        if ($current_card == 0) {

            if ($cards < $no_of_stores) {

                try {
                    $card_id = $cardId;

                    // Save Card
                    $card = new BusinessCard();

                    $card->card_id = $card_id;
                    $card->user_id = Auth::user()->user_id;
                    $card->theme_id = $request->theme_id;
                    $card->card_lang = $request->card_lang;

                    // Save banner paths
                    $card->cover = json_encode(
                        $cleanBanners,
                        JSON_UNESCAPED_SLASHES
                    );

                    $card->profile = $logo;
                    $card->card_url = $card_url;
                    $card->card_type = 'store';
                    $card->title = $request->title;
                    $card->sub_title = $request->subtitle;
                    $card->description = json_encode($store_details);

                    $card->save();

                    // Set default business hours
                    $business_hours = [
                        'monday'    => ['start' => '00:01', 'end' => '23:59'],
                        'tuesday'   => ['start' => '00:01', 'end' => '23:59'],
                        'wednesday' => ['start' => '00:01', 'end' => '23:59'],
                        'thursday'  => ['start' => '00:01', 'end' => '23:59'],
                        'friday'    => ['start' => '00:01', 'end' => '23:59'],
                        'saturday'  => ['start' => '00:01', 'end' => '23:59'],
                        'sunday'    => ['start' => '00:01', 'end' => '23:59'],
                    ];

                    $businessHour = new StoreBusinessHour();
                    $businessHour->user_id = Auth::user()->user_id;
                    $businessHour->store_id = $card_id;
                    $businessHour->business_hours_id = uniqid();
                    $businessHour->business_hours = json_encode($business_hours);
                    $businessHour->status = 1;
                    $businessHour->save();

                    // Set default delivery options
                    $delivery_options = [
                        'order_for_delivery' => 1,
                        'take_away'          => 0,
                        'dine_in'             => 0,
                    ];

                    $business_card = BusinessCard::where(
                        'card_id',
                        $card_id
                    )->first();

                    $business_card->delivery_options = json_encode(
                        $delivery_options
                    );

                    $business_card->save();

                    return redirect()
                        ->route('user.edit.products', $card_id)
                        ->with(
                            'success',
                            trans('New WhatsApp Store Created Successfully!')
                        );
                } catch (\Exception $th) {
                    return redirect()
                        ->route('user.create.store')
                        ->with(
                            'failed',
                            trans('Sorry, the personalized link was already registered.')
                        );
                }
            } else {
                return redirect()
                    ->route('user.create.store')
                    ->with(
                        'failed',
                        trans('Maximum store creation limit is exceeded, Please upgrade your plan to add more store(s).')
                    );
            }
        } else {
            return redirect()
                ->route('user.create.store')
                ->with(
                    'failed',
                    trans('Sorry, the personalized link was already registered.')
                );
        }
    }


    // Cropping image
    public function storeCroppedImage(Request $request)
    {
        // Validate image 
        $request->validate([
            'croppedImage' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:' . env('SIZE_LIMIT', 2048),
        ]);

        // Upload cropped image using StorageHelper and get relative path
        $imagePath = StorageHelper::uploadImage(
            $request->file('croppedImage'),
            'store/images',
            Str::random(20)
        );

        return response()->json([
            'success'  => true,
            'imagePath' => $imagePath,
            'imageUrl' => asset($imagePath),
        ]);
    }
}
