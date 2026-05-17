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

namespace App\Http\Controllers;

use Exception;
use App\Setting;
use App\Visitor;
use App\Currency;
use Carbon\Carbon;
use App\ContactForm;
use App\Testimonial;
use App\BusinessCard;
use App\StoreProduct;
use App\BusinessField;
use App\EmailTemplate;
use App\StoreCategory;
use App\ServiceBooking;
use App\StoreBusinessHour;
use Jenssegers\Agent\Agent;
use App\CardAppointmentTime;
use Illuminate\Http\Request;
use App\Mail\AppointmentMail;
use App\Classes\ServiceWorker;
use JeroenDesloovere\VCard\VCard;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\URL;
use App\Services\GoBizCommonService;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Artesaos\SEOTools\Facades\JsonLd;
use Illuminate\Support\Facades\Cache;
use Artesaos\SEOTools\Facades\SEOMeta;
use Artesaos\SEOTools\Facades\SEOTools;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Artesaos\SEOTools\Facades\OpenGraph;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    // View Card Profile
    public function profile(Request $request, $id)
    {
        $card_details = DB::table('business_cards')->where('card_url', $id)->where('card_status', 'activated')->first();

        if (!$card_details) {
            return abort(404);
        }

        // Check theme_id is null
        if (is_null($card_details->theme_id)) {
            return redirect()
                ->route('user.cards')
                ->with('failed', trans('Theme not selected'));
        }

        // Check invalid theme for store card
        if (
            $card_details->theme_id === '588969111160' &&
            $card_details->card_type === 'store'
        ) {
            return redirect()
                ->route('user.cards')
                ->with('failed', trans('Invalid Theme'));
        }

        // Check if card is activated
        if (!$card_details) {
            return view('errors.404');
        }

        $currentUser = 0;

        // Check storage folder
        if (! File::isDirectory('storage')) {
            File::link(storage_path('app/public'), public_path('storage'));
        }

        if (isset($card_details)) {
            $currentUser = DB::table('users')->where('user_id', $card_details->user_id)->where('status', 1)->where('plan_validity', '>=', Carbon::now())->count();
        }

        if ($currentUser == 1) {

            // Check whatsapp number exists89
            $whatsAppNumberExists = BusinessField::where('card_id', $card_details->card_id)->where('type', 'wa')->exists();

            // Save visitor
            $clientIP = \Request::getClientIp(true);

            $agent     = new Agent();
            $userAgent = $request->header('user_agent');
            $agent->setUserAgent($userAgent);

            // Device
            $device = $agent->device();
            if ($device == "" || $device == "0") {
                $device = "Others";
            }

            // Language
            $language = "en";
            if ($agent->languages()) {
                $language = $agent->languages()[0];
            }

            $visitor             = new Visitor();
            $visitor->card_id    = $card_details->card_url;
            $visitor->type       = $card_details->card_type;
            $visitor->ip_address = $clientIP;
            $visitor->platform   = $agent->platform();
            $visitor->device     = $agent->device();
            $visitor->language   = $language;
            $visitor->user_agent = $userAgent;
            $visitor->save();

            if (isset($card_details)) {
                if ($card_details->card_type == "store") {
                    $enquiry_button = '#';

                    $business_card_details = DB::table('business_cards')->where('business_cards.card_id', $card_details->card_id)
                        ->where('business_cards.card_type', "store")
                        ->join('users', 'business_cards.user_id', '=', 'users.user_id')
                        ->join('themes', 'business_cards.theme_id', '=', 'themes.theme_id')
                        ->select('business_cards.*', 'users.plan_details', 'themes.theme_code', 'themes.theme_css', 'themes.theme_js')
                        ->first();

                    if ($business_card_details) {
                        $products = StoreProduct::join('store_categories', 'store_products.category_id', '=', 'store_categories.category_id')
                            ->where('store_products.card_id', $card_details->card_id)
                            ->where('store_categories.user_id', $business_card_details->user_id)
                            ->where('store_products.product_status', 'instock')
                            ->where('store_categories.status', 1)
                            ->select(
                                'store_products.id',
                                'store_products.product_id',
                                'store_products.product_name',
                                'store_products.product_image',
                                'store_products.product_short_description',
                                'store_products.regular_price',
                                'store_products.sales_price',
                                'store_products.badge',
                                'store_products.product_status',
                                'store_categories.category_name',
                                'store_categories.thumbnail',
                                'store_categories.category_id'
                            )
                            ->groupBy(
                                'store_products.id',
                                'store_products.product_id',
                                'store_products.product_name',
                                'store_products.product_image',
                                'store_products.product_short_description',
                                'store_products.regular_price',
                                'store_products.sales_price',
                                'store_products.badge',
                                'store_products.product_status',
                                'store_categories.category_name',
                                'store_categories.thumbnail',
                                'store_categories.category_id'
                            );

                        // Filter: Price Range
                        if ($request->filled('min') && $request->filled('max')) {
                            $min = (float) $request->input('min');
                            $max = (float) $request->input('max');
                            $products->whereBetween('store_products.sales_price', [$min, $max]);
                        }

                        // Filter: Search Query
                        if ($request->filled('query')) {
                            $products->where('store_products.product_name', 'like', '%' . $request->get('query') . '%');
                        }

                        // Filter: Category
                        if ($request->filled('category')) {
                            $products->where('store_categories.category_name', ucfirst($request->get('category')));
                        }

                        // Sorting
                        switch ($request->get('sort')) {
                            case 'price_asc':
                                $products->orderBy('store_products.sales_price', 'asc');
                                break;
                            case 'price_desc':
                                $products->orderBy('store_products.sales_price', 'desc');
                                break;
                            case 'name_asc':
                                $products->orderBy('store_products.product_name', 'asc');
                                break;
                            case 'name_desc':
                                $products->orderBy('store_products.product_name', 'desc');
                                break;
                            default:
                                $products->orderBy('store_products.id', 'asc');
                        }

                        // Delivery Options
                        $deliveryOptions = json_decode($business_card_details->delivery_options);

                        // Business Hours
                        $businessHours = StoreBusinessHour::where('store_id', $business_card_details->card_id)->first();

                        // Paginate (consistent)
                        $products = $products->paginate($request->filled('category') ? 9 : 8)->withQueryString();

                        // Get categories
                        $getCategories = DB::table('store_products')->select('category_id')->groupBy('category_id')->where('card_id', $card_details->card_id)->where('user_id', $business_card_details->user_id);
                        $categories    = StoreCategory::where('store_id', $card_details->card_id)->get();

                        $settings = GoBizCommonService::settings();
                        $config   = GoBizCommonService::config();

                        // Decode plan and SEO config only once
                        $planDetails = json_decode($business_card_details->plan_details, true);
                        $seoConfig = json_decode($business_card_details->seo_configurations ?? '{}');

                        // Meta Title Logic (branding)
                        $siteTitle = $config[0]->config_value ?? '';
                        $baseTitle = $seoConfig->meta_title ?? $business_card_details->title;
                        $metaTitle = isset($planDetails['hide_branding']) && $planDetails['hide_branding'] == "1"
                            ? $baseTitle
                            : $baseTitle . ' - ' . $siteTitle;

                        // Set Meta Titles
                        SEOTools::setTitle($metaTitle);
                        SEOMeta::setTitle($metaTitle);
                        OpenGraph::setTitle($metaTitle);
                        JsonLd::setTitle($metaTitle);
                        SEOMeta::addMeta('article:section', $metaTitle, 'property');

                        // Set Description
                        $description = $seoConfig->meta_description ?? $business_card_details->sub_title;
                        SEOTools::setDescription($description);
                        SEOMeta::setDescription($description);
                        OpenGraph::setDescription($description);
                        JsonLd::setDescription($description);

                        // Set Keywords
                        if (!empty($seoConfig->meta_keywords)) {
                            SEOMeta::addKeyword($seoConfig->meta_keywords);
                        } else {
                            SEOMeta::addKeyword([$metaTitle, "$metaTitle vcard online"]);
                        }

                        // Add Canonical URL
                        SEOMeta::setCanonical(url()->current());

                        // Set Favicon or Profile Image
                        $imageUrl = !empty($seoConfig->favicon) ? url($seoConfig->favicon) : url($business_card_details->profile);
                        SEOTools::addImages([$imageUrl]);
                        OpenGraph::addImage([$imageUrl]);
                        JsonLd::addImage([$imageUrl]);

                        // Set OpenGraph URL
                        OpenGraph::setUrl(url($business_card_details->card_url));

                        // PWA
                        $icons = [
                            '512x512' => [
                                'path'    => url($business_card_details->profile),
                                'purpose' => 'any',
                            ],
                        ];

                        $splash = [
                            '640x1136'  => url($business_card_details->profile),
                            '750x1334'  => url($business_card_details->profile),
                            '828x1792'  => url($business_card_details->profile),
                            '1125x2436' => url($business_card_details->profile),
                            '1242x2208' => url($business_card_details->profile),
                            '1242x2688' => url($business_card_details->profile),
                            '1536x2048' => url($business_card_details->profile),
                            '1668x2224' => url($business_card_details->profile),
                            '1668x2388' => url($business_card_details->profile),
                            '2048x2732' => url($business_card_details->profile),
                        ];

                        $shortcuts = [
                            [
                                'name'        => $business_card_details->title,
                                'description' => $business_card_details->sub_title,
                                'url'         => asset($business_card_details->card_url),
                                'icons'       => [
                                    "src"     => url($business_card_details->profile),
                                    "purpose" => "any",
                                ],
                            ],
                        ];

                        $fill = [
                            "name"        => $business_card_details->title,
                            "short_name"  => $business_card_details->title,
                            "start_url"   => asset($business_card_details->card_url),
                            "theme_color" => "#ffffff",
                            "icons"       => $icons,
                            "splash"      => $splash,
                            "shortcuts"   => $shortcuts,
                        ];

                        $out = $this->generateNew($fill);

                        Storage::disk('public')->put("manifest/" . $business_card_details->card_id . '.json', json_encode($out));

                        $manifest = url("storage/manifest/" . $business_card_details->card_id . '.json');

                        // Generate service worker
                        $generateServiceWorker = new ServiceWorker();
                        $generateServiceWorker->generateServiceWorker($business_card_details->card_id, $business_card_details->card_url);

                        $plan_details  = json_decode($business_card_details->plan_details, true);
                        $store_details = json_decode($business_card_details->description, true);

                        if ($store_details['whatsapp_no'] != null) {
                            $enquiry_button = $store_details['whatsapp_no'];
                        }

                        $whatsapp_msg = $store_details['whatsapp_msg'];
                        $currency     = $store_details['currency'];

                        // Get currency symbol
                        $currency = Currency::where('iso_code', $currency)->first();
                        $currency = $currency->symbol;

                        $url           = URL::to('/') . "/" . strtolower(preg_replace('/\s+/', '-', $card_details->card_url));
                        $business_name = $card_details->title;
                        $profile       = URL::to('/') . "/" . $business_card_details->cover;

                        $shareContent = $config[30]->config_value;
                        $shareContent = str_replace("{ business_name }", $business_name, $shareContent);
                        $shareContent = str_replace("{ business_url }", $url, $shareContent);
                        $shareContent = str_replace("{ appName }", $config[0]->config_value, $shareContent);

                        // If branding enabled, then show app name.
                        if ($plan_details['hide_branding'] == "1") {
                            $shareContent = str_replace("{ appName }", $business_name, $shareContent);
                        } else {
                            $shareContent = str_replace("{ appName }", $config[0]->config_value, $shareContent);
                        }

                        $url          = urlencode($url);
                        $shareContent = urlencode($shareContent);

                        // Set locale if not same as business card locale.
                        $locale = Session::get('locale') ?? $business_card_details->card_lang ?? config('app.locale');
                        App::setLocale($locale);

                        $qr_url = "https://chart.googleapis.com/chart?chs=250x250&cht=qr&chl=" . $url;

                        $shareComponent['facebook'] = "https://www.facebook.com/sharer/sharer.php?u=$url&quote=$shareContent";
                        $shareComponent['twitter']  = "https://twitter.com/intent/tweet?text=$shareContent";
                        $shareComponent['linkedin'] = "https://www.linkedin.com/shareArticle?mini=true&url=$url";
                        $shareComponent['telegram'] = "https://telegram.me/share/url?text=$shareContent&url=$url";
                        $shareComponent['whatsapp'] = "https://api.whatsapp.com/send/?phone&text=$shareContent";

                        // Check password protected is enabled in user purchased plan
                        if ($plan_details['password_protected'] != 1) {
                            // Update business card password
                            BusinessCard::where('card_id', $business_card_details->card_id)->update(['password' => null]);

                            // Session password protected
                            Session::get('password_protected') == false;
                        }

                        $datas = compact('card_details', 'plan_details', 'store_details', 'categories', 'business_card_details', 'products', 'settings', 'shareComponent', 'shareContent', 'config', 'enquiry_button', 'whatsapp_msg', 'currency', 'manifest', 'whatsAppNumberExists', 'deliveryOptions', 'businessHours');
                        return view('templates.store.' . $business_card_details->theme_code . '.index', $datas);
                    } else {
                        return redirect()->route('user.edit.card', $id)->with('failed', trans('Please fill out the basic business details.'));
                    }
                } else {
                    $enquiry_button = "#";

                    $business_card_details = DB::table('business_cards')->where('business_cards.card_id', $card_details->card_id)
                        ->join('users', 'business_cards.user_id', '=', 'users.user_id')
                        ->join('themes', 'business_cards.theme_id', '=', 'themes.theme_id')
                        ->select('business_cards.*', 'users.plan_details', 'themes.theme_code', 'themes.theme_css', 'themes.theme_js')
                        ->first();

                    if ($business_card_details) {
                        $feature_details   = DB::table('business_fields')->where('card_id', $card_details->card_id)->orderBy('id', 'asc')->get();
                        $service_details   = DB::table('services')->where('card_id', $card_details->card_id)->orderBy('id', 'asc')->get();
                        $product_details   = DB::table('vcard_products')->where('card_id', $card_details->card_id)->orderBy('id', 'asc')->get();
                        $galleries_details = DB::table('galleries')->where('card_id', $card_details->card_id)->orderBy('id', 'asc')->get();
                        $testimonials      = Testimonial::where('card_id', $card_details->card_id)->orderBy('id', 'asc')->get();
                        $payment_details   = DB::table('payments')->where('card_id', $card_details->card_id)->get();
                        $business_hours    = DB::table('business_hours')->where('card_id', $card_details->card_id)->first();
                        $make_enquiry      = DB::table('business_fields')->where('card_id', $card_details->card_id)->where('type', 'wa')->first();
                        $iframes           = DB::table('business_fields')->where('type', 'iframe')->where('card_id', $card_details->card_id)->orderBy('id', 'asc')->get();
                        $customTexts       = DB::table('business_fields')->where('type', 'text')->where('card_id', $card_details->card_id)->orderBy('id', 'asc')->get();

                        // Appointment slots for the card
                        $appointmentSlots = CardAppointmentTime::where('card_id', $card_details->card_id)->orderBy('id', 'asc')->get();

                        // Initialize the time slots array
                        $appointmentEnabled = false;
                        $appointment_slots  = [
                            'monday'    => [],
                            'tuesday'   => [],
                            'wednesday' => [],
                            'thursday'  => [],
                            'friday'    => [],
                            'saturday'  => [],
                            'sunday'    => [],
                        ];

                        // Iterate through the appointment slots and categorize them by day
                        foreach ($appointmentSlots as $slot) {
                            // Assuming your `CardAppointmentTime` model has a `day` attribute and a `time` attribute
                            $day  = strtolower($slot->day); // Convert to lowercase to match array keys
                            $time = $slot->time_slots;      // Assuming this contains the time range string like "16:00 - 17:00"

                            // Check if the day exists in the time_slots array
                            if (array_key_exists($day, $appointment_slots)) {
                                $appointment_slots[$day][] = $time; // Add the time to the appropriate day
                                // Get price
                                $appointment_slots[$day][] = $slot->price;
                            }

                            // Add appointment title to array
                            $appointment_slots['title'] = $appointmentSlots[0]->title;

                            $appointmentEnabled = true;
                        }

                        $appointment_slots = json_encode($appointment_slots); // Convert the array to JSON

                        // service bookings
                        $service_booking_details = ServiceBooking::where('vcard_id', $card_details->card_id)->first();

                        if ($make_enquiry != null) {
                            $enquiry_button = $make_enquiry->content;
                        }

                        $settings = GoBizCommonService::settings();
                        $config   = GoBizCommonService::config();

                        // Decode plan and SEO config only once
                        $planDetails = json_decode($business_card_details->plan_details, true);
                        $seoConfig = json_decode($business_card_details->seo_configurations ?? '{}');

                        // Meta Title Logic (branding)
                        $siteTitle = $config[0]->config_value ?? '';
                        $baseTitle = $seoConfig->meta_title ?? $business_card_details->title;
                        $metaTitle = isset($planDetails['hide_branding']) && $planDetails['hide_branding'] == "1"
                            ? $baseTitle
                            : $baseTitle . ' - ' . $siteTitle;

                        // Set Meta Titles
                        SEOTools::setTitle($metaTitle);
                        SEOMeta::setTitle($metaTitle);
                        OpenGraph::setTitle($metaTitle);
                        JsonLd::setTitle($metaTitle);
                        SEOMeta::addMeta('article:section', $metaTitle, 'property');

                        // Set Description
                        $description = $seoConfig->meta_description ?? $business_card_details->sub_title;
                        SEOTools::setDescription($description);
                        SEOMeta::setDescription($description);
                        OpenGraph::setDescription($description);
                        JsonLd::setDescription($description);

                        // Set Keywords
                        if (!empty($seoConfig->meta_keywords)) {
                            SEOMeta::addKeyword($seoConfig->meta_keywords);
                        } else {
                            SEOMeta::addKeyword([$metaTitle, "$metaTitle vcard online"]);
                        }

                        // Add Canonical URL
                        SEOMeta::setCanonical(url()->current());

                        // Set Favicon or Profile Image
                        $imageUrl = !empty($seoConfig->favicon) ? url($seoConfig->favicon) : url($business_card_details->profile);
                        SEOTools::addImages([$imageUrl]);
                        OpenGraph::addImage([$imageUrl]);
                        JsonLd::addImage([$imageUrl]);

                        // Set OpenGraph URL
                        OpenGraph::setUrl(url($business_card_details->card_url));

                        // PWA
                        $icons = [
                            '512x512' => [
                                'path'    => url($business_card_details->profile),
                                'purpose' => 'any',
                            ],
                        ];

                        $splash = [
                            '640x1136'  => url($business_card_details->profile),
                            '750x1334'  => url($business_card_details->profile),
                            '828x1792'  => url($business_card_details->profile),
                            '1125x2436' => url($business_card_details->profile),
                            '1242x2208' => url($business_card_details->profile),
                            '1242x2688' => url($business_card_details->profile),
                            '1536x2048' => url($business_card_details->profile),
                            '1668x2224' => url($business_card_details->profile),
                            '1668x2388' => url($business_card_details->profile),
                            '2048x2732' => url($business_card_details->profile),
                        ];

                        $shortcuts = [
                            [
                                'name'        => $business_card_details->title,
                                'description' => $business_card_details->sub_title,
                                'url'         => asset($business_card_details->card_url),
                                'icons'       => [
                                    "src"     => url($business_card_details->profile),
                                    "purpose" => "any",
                                ],
                            ],
                        ];

                        $fill = [
                            "name"        => $business_card_details->title,
                            "short_name"  => $business_card_details->title,
                            "start_url"   => asset($business_card_details->card_url),
                            "theme_color" => "#ffffff",
                            "icons"       => $icons,
                            "splash"      => $splash,
                            "shortcuts"   => $shortcuts,
                        ];

                        $out = $this->generateNew($fill);

                        Storage::disk('public')->put("manifest/" . $business_card_details->card_id . '.json', json_encode($out));

                        $manifest = url("storage/manifest/" . $business_card_details->card_id . '.json');

                        // Generate service worker
                        $generateServiceWorker = new ServiceWorker();
                        $generateServiceWorker->generateServiceWorker($business_card_details->card_id, $business_card_details->card_url);

                        $plan_details = json_decode($business_card_details->plan_details, true);

                        $url           = URL::to('/') . "/" . strtolower(preg_replace('/\s+/', '-', $card_details->card_url));
                        $business_name = $card_details->title;
                        $profile       = URL::to('/') . "/" . $business_card_details->cover;

                        $shareContent = $config[30]->config_value;
                        $shareContent = str_replace("{ business_name }", $business_name, $shareContent);
                        $shareContent = str_replace("{ business_url }", $url, $shareContent);

                        // If branding enabled, then show app name.
                        if ($plan_details['hide_branding'] == "1") {
                            $shareContent = str_replace("{ appName }", $business_name, $shareContent);
                        } else {
                            $shareContent = str_replace("{ appName }", $config[0]->config_value, $shareContent);
                        }

                        $url          = urlencode($url);
                        $shareContent = urlencode($shareContent);

                        $locale = Session::get('locale') ?? $business_card_details->card_lang ?? config('app.locale');
                        App::setLocale($locale);

                        $qr_url = "https://chart.googleapis.com/chart?chs=250x250&cht=qr&chl=" . $url;

                        $shareComponent['facebook'] = "https://www.facebook.com/sharer/sharer.php?u=$url&quote=$shareContent";
                        $shareComponent['twitter']  = "https://twitter.com/intent/tweet?text=$shareContent";
                        $shareComponent['linkedin'] = "https://www.linkedin.com/shareArticle?mini=true&url=$url";
                        $shareComponent['telegram'] = "https://telegram.me/share/url?text=$shareContent&url=$url";
                        $shareComponent['whatsapp'] = "https://api.whatsapp.com/send/?phone&text=$shareContent";

                        // Datas
                        $datas = compact('card_details', 'plan_details', 'business_card_details', 'feature_details', 'service_details', 'product_details', 'galleries_details', 'testimonials', 'payment_details', 'appointmentEnabled', 'appointment_slots', 'business_hours', 'settings', 'shareComponent', 'shareContent', 'config', 'enquiry_button', 'iframes', 'customTexts', 'manifest', 'whatsAppNumberExists', 'service_booking_details');

                        try {
                            // Google Wallet Details
                            $google_wallet_details = DB::table('google_wallets_vcard')->where('card_id', $business_card_details->card_id)->first();

                            if ($google_wallet_details) {
                                $wallet_url = url()->to('/') . "/user/google-wallet/redirect/wallet?id=" . $google_wallet_details->google_wallet_id;
                                $google_wallet_details->wallet_link = $wallet_url;
                            }

                            // Push google wallet details in datas
                            $datas['google_wallet_details'] = $google_wallet_details;
                        } catch (\Throwable $th) {
                        }

                        // Check password protected is enabled in user purchased plan
                        if ($plan_details['password_protected'] != 1) {
                            // Update business card password
                            BusinessCard::where('card_id', $business_card_details->card_id)->update(['password' => null]);

                            // Session password protected
                            Session::get('password_protected') == false;
                        }

                        return view('templates.' . $business_card_details->theme_code, $datas);
                    } else {
                        return redirect()->route('user.edit.card', $id)->with('failed', trans('Please fill out the basic business details.'));
                    }
                }
            } else {
                http_response_code(404);
                return view('errors.404');
            }
        } else {
            return view('errors.404');
        }
    }

    // single product view
    public function profileSingleProduct(Request $request, $id, $product_id)
    {
        $card_details = DB::table('business_cards')->where('card_url', $id)->where('card_status', 'activated')->first();

        // Check if card is activated
        if (!$card_details) {
            return view('errors.404');
        }

        $whatsAppNumberExists = BusinessField::where('card_id', $card_details->card_id)->where('type', 'wa')->exists();

        $enquiry_button = '#';

        $business_card_details = DB::table('business_cards')->where('business_cards.card_id', $card_details->card_id)
            ->join('users', 'business_cards.user_id', '=', 'users.user_id')
            ->join('themes', 'business_cards.theme_id', '=', 'themes.theme_id')
            ->select('business_cards.*', 'users.plan_details', 'themes.theme_code', 'themes.theme_css', 'themes.theme_js')
            ->first();

        if ($business_card_details) {
            // Product Details
            $product_details = StoreProduct::where('id', $product_id)->where('card_id', $card_details->card_id)->first();

            // Check product details
            if (!$product_details) {
                return view('errors.404');
            }

            $related_products = StoreProduct::where('card_id', $card_details->card_id)
                ->where('category_id', $product_details->category_id)
                ->where('product_status', 'instock')
                ->where('status', 1)
                ->get();

            $settings = GoBizCommonService::settings();
            $config   = GoBizCommonService::config();

            // Delivery Options
            $deliveryOptions = json_decode($business_card_details->delivery_options);

            // Business Hours
            $businessHours = StoreBusinessHour::where('store_id', $business_card_details->card_id)->first();

            SEOTools::setTitle($product_details->product_name . ' - ' . $business_card_details->title);
            SEOTools::setDescription($product_details->product_short_description);
            SEOTools::addImages([url($product_details->product_image)]);

            SEOMeta::setTitle($product_details->product_name . ' - ' . $business_card_details->title);
            SEOMeta::setDescription($product_details->product_short_description);
            SEOMeta::addMeta('article:section', $product_details->product_name . ' - ' . $business_card_details->title, 'property');
            SEOMeta::addKeyword(["'" . $product_details->product_name . ' - ' . $business_card_details->title . "'", "'" . $product_details->product_name . ' - ' . $business_card_details->title . " vcard online'"]);

            // Add Canonical URL
            SEOMeta::setCanonical(url()->current());

            OpenGraph::setTitle($product_details->product_name . ' - ' . $business_card_details->title);
            OpenGraph::setDescription($product_details->product_short_description);
            OpenGraph::setUrl(url()->current());
            OpenGraph::addImage([url($product_details->product_image)]);

            JsonLd::setTitle($product_details->product_name . ' - ' . $business_card_details->title);
            JsonLd::setDescription($product_details->product_short_description);
            JsonLd::addImage([url($product_details->product_image)]);

            $plan_details  = json_decode($business_card_details->plan_details, true);
            $store_details = json_decode($business_card_details->description, true);

            if ($store_details['whatsapp_no'] != null) {
                $enquiry_button = $store_details['whatsapp_no'];
            }

            $whatsapp_msg = $store_details['whatsapp_msg'];
            $currency     = $store_details['currency'];

            // Get currency symbol
            $currency = Currency::where('iso_code', $currency)->first();
            $currency = $currency->symbol;

            $url           = URL::to('/') . "/" . strtolower(preg_replace('/\s+/', '-', $card_details->card_url));
            $business_name = $card_details->title;
            $profile       = URL::to('/') . "/" . $business_card_details->cover;

            $shareContent = $config[30]->config_value;
            $shareContent = str_replace("{ business_name }", $business_name, $shareContent);
            $shareContent = str_replace("{ business_url }", $url, $shareContent);
            $shareContent = str_replace("{ appName }", $config[0]->config_value, $shareContent);

            // If branding enabled, then show app name.
            if ($plan_details['hide_branding'] == "1") {
                $shareContent = str_replace("{ appName }", $business_name, $shareContent);
            } else {
                $shareContent = str_replace("{ appName }", $config[0]->config_value, $shareContent);
            }

            $url          = urlencode($url);
            $shareContent = urlencode($shareContent);

            // Set locale if not same as business card locale.
            $locale = Session::get('locale') ?? $business_card_details->card_lang ?? config('app.locale');
            App::setLocale($locale);

            $qr_url = "https://chart.googleapis.com/chart?chs=250x250&cht=qr&chl=" . $url;

            $shareComponent['facebook'] = "https://www.facebook.com/sharer/sharer.php?u=$url&quote=$shareContent";
            $shareComponent['twitter']  = "https://twitter.com/intent/tweet?text=$shareContent";
            $shareComponent['linkedin'] = "https://www.linkedin.com/shareArticle?mini=true&url=$url";
            $shareComponent['telegram'] = "https://telegram.me/share/url?text=$shareContent&url=$url";
            $shareComponent['whatsapp'] = "https://api.whatsapp.com/send/?phone&text=$shareContent";

            $datas = compact('card_details', 'plan_details', 'store_details', 'business_card_details', 'related_products', 'product_details', 'settings', 'shareComponent', 'shareContent', 'config', 'enquiry_button', 'whatsapp_msg', 'currency', 'whatsAppNumberExists', 'deliveryOptions', 'businessHours');
            return view('templates.store.' . $business_card_details->theme_code . '.single-product', $datas);
        }
    }

    // categories
    public function profileCategories(Request $request, $id)
    {
        $card_details = DB::table('business_cards')->where('card_url', $id)->where('card_status', 'activated')->first();

        // Check if card is activated
        if (!$card_details) {
            return view('errors.404');
        }

        $whatsAppNumberExists = BusinessField::where('card_id', $card_details->card_id)->where('type', 'wa')->exists();

        $enquiry_button = '#';

        $business_card_details = DB::table('business_cards')->where('business_cards.card_id', $card_details->card_id)
            ->join('users', 'business_cards.user_id', '=', 'users.user_id')
            ->join('themes', 'business_cards.theme_id', '=', 'themes.theme_id')
            ->select('business_cards.*', 'users.plan_details', 'themes.theme_code', 'themes.theme_css', 'themes.theme_js')
            ->first();

        if ($business_card_details) {
            // Categories
            $categories = StoreCategory::where('store_id', $card_details->card_id)->where('user_id', $business_card_details->user_id)->where('status', 1)->get();

            $settings = GoBizCommonService::settings();
            $config   = GoBizCommonService::config();

            // Delivery Options
            $deliveryOptions = json_decode($business_card_details->delivery_options);

            // Business Hours
            $businessHours = StoreBusinessHour::where('store_id', $business_card_details->card_id)->first();

            SEOTools::setTitle($business_card_details->title);
            SEOTools::setDescription($business_card_details->sub_title);
            SEOTools::addImages([url($business_card_details->profile)]);

            SEOMeta::setTitle($business_card_details->title);
            SEOMeta::setDescription($business_card_details->sub_title);
            SEOMeta::addMeta('article:section', $business_card_details->title, 'property');
            SEOMeta::addKeyword(["'" . $business_card_details->title . "'", "'" . $business_card_details->title . " vcard online'"]);

            // Add Canonical URL
            SEOMeta::setCanonical(url()->current());

            OpenGraph::setTitle($business_card_details->title);
            OpenGraph::setDescription($business_card_details->sub_title);
            OpenGraph::setUrl(url($business_card_details->card_url));
            OpenGraph::addImage([url($business_card_details->profile)]);

            JsonLd::setTitle($business_card_details->title);
            JsonLd::setDescription($business_card_details->sub_title);
            JsonLd::addImage([url($business_card_details->profile)]);

            $plan_details  = json_decode($business_card_details->plan_details, true);
            $store_details = json_decode($business_card_details->description, true);

            if ($store_details['whatsapp_no'] != null) {
                $enquiry_button = $store_details['whatsapp_no'];
            }

            $whatsapp_msg = $store_details['whatsapp_msg'];
            $currency     = $store_details['currency'];

            // Get currency symbol
            $currency = Currency::where('iso_code', $currency)->first();
            $currency = $currency->symbol;

            $url           = URL::to('/') . "/" . strtolower(preg_replace('/\s+/', '-', $card_details->card_url));
            $business_name = $card_details->title;
            $profile       = URL::to('/') . "/" . $business_card_details->cover;

            $shareContent = $config[30]->config_value;
            $shareContent = str_replace("{ business_name }", $business_name, $shareContent);
            $shareContent = str_replace("{ business_url }", $url, $shareContent);
            $shareContent = str_replace("{ appName }", $config[0]->config_value, $shareContent);

            // If branding enabled, then show app name.
            if ($plan_details['hide_branding'] == "1") {
                $shareContent = str_replace("{ appName }", $business_name, $shareContent);
            } else {
                $shareContent = str_replace("{ appName }", $config[0]->config_value, $shareContent);
            }

            $url          = urlencode($url);
            $shareContent = urlencode($shareContent);

            // Set locale if not same as business card locale.
            $locale = Session::get('locale') ?? $business_card_details->card_lang ?? config('app.locale');
            App::setLocale($locale);

            $qr_url = "https://chart.googleapis.com/chart?chs=250x250&cht=qr&chl=" . $url;

            $shareComponent['facebook'] = "https://www.facebook.com/sharer/sharer.php?u=$url&quote=$shareContent";
            $shareComponent['twitter']  = "https://twitter.com/intent/tweet?text=$shareContent";
            $shareComponent['linkedin'] = "https://www.linkedin.com/shareArticle?mini=true&url=$url";
            $shareComponent['telegram'] = "https://telegram.me/share/url?text=$shareContent&url=$url";
            $shareComponent['whatsapp'] = "https://api.whatsapp.com/send/?phone&text=$shareContent";

            $datas = compact('card_details', 'plan_details', 'store_details', 'business_card_details', 'categories', 'settings', 'shareComponent', 'shareContent', 'config', 'enquiry_button', 'whatsapp_msg', 'currency', 'whatsAppNumberExists', 'deliveryOptions', 'businessHours');
            return view('templates.store.' . $business_card_details->theme_code . '.categories', $datas);
        }
    }

    // Check password
    public function checkPwd(Request $request, $id)
    {
        $business_card = BusinessCard::where('card_id', $id)->first();

        if ($business_card) {
            // Check password
            if ($business_card->password == $request->password) {
                Session::put('password_protected', true);

                return redirect()->route('profile', $business_card->card_url);
            } else {

                Session::flash('message', trans('Incorrect vcard password.'));
                return redirect()->route('profile', $business_card->card_url);
            }
        } else {
            return redirect()->route('profile', $business_card->card_url);
        }
    }

    // Download vcard file
    public function downloadVcard(Request $request, $id)
    {
        $config = GoBizCommonService::config()->pluck('config_value', 'config_key')->toArray();

        $business_card = BusinessCard::where('business_cards.card_id', $id)
            ->join('users', 'business_cards.user_id', '=', 'users.user_id')
            ->select('business_cards.*')
            ->first();

        if (!$business_card) {
            abort(404);
        }

        $features = BusinessField::where('card_id', $id)->get();

        if ($business_card->custom_domain) {
            $vcard_url = $business_card->custom_domain;
        } elseif ($config['enable_subdomain'] == '1') {
            $vcard_url = 'https://' . $business_card->card_url . '.' . env('MAIN_DOMAIN');
        } else {
            $vcard_url = env('APP_URL') . '/' . $business_card->card_url;
        }

        $vcard = new VCard();

        $vcard->addName('', $business_card->title ?? '', '', '', '');

        $socialTypes = [
            "facebook",
            "instagram",
            "x-twitter",
            "linkedin",
            "pinterest",
            "reddit",
            "tiktok",
            "threads",
            "snapchat",
            "wechat",
            "telegram",
            "tumblr",
            "qq",
            "discord",
            "quora",
            "url"
        ];

        foreach ($features as $feature) {

            if (!$feature->content) {
                continue;
            }

            switch ($feature->type) {

                case 'email':
                    $vcard->addEmail($feature->content);
                    break;

                case 'tel':
                    $vcard->addPhoneNumber($feature->content, 'WORK');
                    break;

                case 'wa':
                    $vcard->addPhoneNumber($feature->content, 'CELL');
                    break;

                case 'address':
                    $vcard->addAddress('', '', $feature->content, '', '', '', 'WORK');
                    break;

                default:
                    if (in_array($feature->type, $socialTypes)) {
                        $vcard->addURL($feature->content);
                    }
                    break;
            }
        }

        if ($business_card->sub_title) {
            $vcard->addJobtitle($business_card->sub_title);
        }

        $profilePath = public_path(ltrim($business_card->profile, '/'));
        $defaultProfile = public_path('images/default-profile.jpg');

        if ($business_card->profile && file_exists($profilePath)) {
            $vcard->addPhoto($profilePath);
        } elseif (file_exists($defaultProfile)) {
            $vcard->addPhoto($defaultProfile);
        }

        $vcard->addURL($vcard_url);

        return response($vcard->getOutput(), 200, $vcard->getHeaders(true));
    }

    // Sent email form vcard
    public function sentEnquiry(Request $request)
    {
        $business_card = BusinessCard::where('card_id', $request->card_id)->first();

        if ($business_card == null) {
            return view('errors.404');
        } else {
            $business_card_details = DB::table('business_cards')->where('business_cards.card_id', $request->card_id)
                ->join('users', 'business_cards.user_id', '=', 'users.user_id')
                ->select('business_cards.*', 'users.*')
                ->first();

            // Validate Recaptcha
            if (env('RECAPTCHA_ENABLE') == 'on') {
                $validator = Validator::make($request->all(), [
                    'name'                 => 'required|string',
                    'email'                => 'required|string|email|max:255',
                    'phone'                => 'required|string|max:255',
                    'message'              => 'required|string',
                    'g-recaptcha-response' => 'required',
                ]);
            } else {
                $validator = Validator::make($request->all(), [
                    'name'    => 'required|string',
                    'email'   => 'required|string|email|max:255',
                    'phone'   => 'required|string|max:255',
                    'message' => 'required|string',
                ]);
            }

            if ($validator->fails()) {
                Session::flash('message', trans('Please fill out all the fields.'));
                return redirect()->back();
            }

            // Save enquiry
            $contactForm                  = new ContactForm();
            $contactForm->contact_form_id = rand(1, 9999999999999999);
            $contactForm->card_id         = $request->card_id;
            $contactForm->user_id         = $business_card_details->user_id;
            $contactForm->name            = $request->name ?? '';
            $contactForm->email           = $request->email ?? '';
            $contactForm->phone           = $request->phone ?? '';
            $contactForm->message         = $request->message ?? '';
            $contactForm->save();

            $enquiryMailDetails = EmailTemplate::where('email_template_id', '584922675205')->first();

            // Booking mail sent to customer
            if ($enquiryMailDetails->is_enabled == 1) {

                // The email sending is done using the to method on the Mail facade
                try {
                    // Contact Details Array
                    $enquiryDetails = [
                        'status'          => '',
                        'emailSubject'    => $enquiryMailDetails->email_template_subject,
                        'emailContent'    => $enquiryMailDetails->email_template_content,
                        'vcardName'       => $business_card_details->title,
                        'receiverName'    => $request->name,
                        'receiverEmail'   => $request->email,
                        'receiverPhone'   => $request->phone,
                        'receiverMessage' => $request->message,
                    ];

                    // Send mail
                    Mail::to($business_card_details->enquiry_email)->send(new AppointmentMail($enquiryDetails));

                    // Send appointment booked message to vcard owner's WhatsApp
                    $whatsAppNumberExists = BusinessField::where('card_id', $request->card_id)->where('type', 'wa')->exists();

                    if ($whatsAppNumberExists) {
                        $whatsAppNumber = BusinessField::where('card_id', $request->card_id)->where('type', 'wa')->first()->content;

                        $vcard = BusinessCard::where('card_id', $request->card_id)->first();
                        $vcardLanguage = $vcard->card_lang;

                        // Apply locale 
                        app()->setLocale($vcardLanguage);

                        // Appointment message add customer name, email, phone, notes and add some break lines
                        $appointmentMessage = trans('New Enquiry') . "\n\n";
                        $appointmentMessage .= trans('vCard Name') . ': ' . $business_card_details->title . "\n";
                        $appointmentMessage .= trans('Customer Name') . ': ' . $request->name . "\n";
                        $appointmentMessage .= trans('Customer Email') . ': ' . $request->email . "\n";
                        $appointmentMessage .= trans('Customer Phone') . ': ' . $request->phone . "\n";
                        $appointmentMessage .= trans('Message') . ': ' . $request->message;

                        // Format number (e.g., remove + and dashes)
                        $whatsAppNumber = preg_replace('/[^0-9]/', '', $whatsAppNumber);

                        $whatsAppUrl = "https://api.whatsapp.com/send?phone={$whatsAppNumber}&text=" . urlencode($appointmentMessage);

                        // Set the title and message
                        $title = "WhatsApp Confirmation" . " - " . $business_card_details->title;
                        $messageOne = "Your enquiry was submitted!";
                        $messageTwo = "Click the button below to notify the business via WhatsApp:";
                        $messageThree = "Send via WhatsApp";

                        return view('templates.includes.open-whatsapp', ['whatsapp_url' => $whatsAppUrl, 'title' => $title, 'messageOne' => $messageOne, 'messageTwo' => $messageTwo, 'messageThree' => $messageThree]);
                    }

                    Session::flash('message', trans('Email sent.',));
                    return redirect()->back();
                } catch (Exception $e) {
                    Session::flash('message', trans('Email service not available.'));
                    return redirect()->back();
                }
            }
        }

        Session::flash('message', trans('Email sent.'));
        return redirect()->back();
    }

    // Generate manifest json
    public function generateNew($fill)
    {
        $basicManifest = [
            'name'             => $fill['name'],
            'short_name'       => $fill['short_name'],
            'start_url'        => $fill['start_url'],
            'background_color' => '#ffffff',
            'theme_color'      => '#000000',
            'display'          => 'standalone',
            'status_bar'       => "black",
            'splash'           => $fill['splash'],
        ];

        foreach ($fill['icons'] as $size => $file) {
            $fileInfo = pathinfo($file['path']);
            $basicManifest['icons'][] = [
                'src'     => $file['path'],
                'type'    => 'image/' . $fileInfo['extension'],
                'sizes'   => $size,
                'purpose' => $file['purpose'],
            ];
        }

        if ($fill['shortcuts']) {
            foreach ($fill['shortcuts'] as $shortcut) {

                if (array_key_exists("icons", $shortcut)) {
                    $fileInfo = pathinfo($shortcut['icons']['src']);
                    $icon     = [
                        'src'     => $shortcut['icons']['src'],
                        'type'    => 'image/' . $fileInfo['extension'],
                        'sizes'   => $size,
                        'purpose' => $shortcut['icons']['purpose'],
                    ];
                } else {
                    $icon = [];
                }

                $basicManifest['shortcuts'][] = [
                    'name'        => trans($shortcut['name']),
                    'description' => trans($shortcut['description']),
                    'url'         => $shortcut['url'],
                    'icons'       => [
                        $icon,
                    ],
                ];
            }
        }
        return $basicManifest;
    }

    // Vcard and Store Locale
    public function setLocale(Request $request)
    {
        $request->validate([
            'locale' => 'required|string',
        ]);

        $locale = strtolower($request->locale);

        // Store locale in session
        Session::put('locale', $locale);

        // Apply locale immediately
        App::setLocale($locale);

        // Update locale in business card
        BusinessCard::where('card_id', $request->card_id)->update([
            'card_lang' => $locale
        ]);

        // Clear cache
        try {
            // Clear application cache
            Cache::flush();

            // Delete all files in bootstrap/cache except .gitignore
            $cachePath = base_path('bootstrap/cache');
            $cacheFiles = File::files($cachePath);
            foreach ($cacheFiles as $file) {
                if ($file->getFilename() !== '.gitignore') {
                    File::delete($file);
                }
            }

            // Delete all files in storage/framework/cache except .gitignore
            $cachePath  = base_path('storage/framework/cache');
            $cacheFiles = File::files($cachePath);
            foreach ($cacheFiles as $file) {
                if ($file->getFilename() !== '.gitignore') {
                    File::delete($file);
                }
            }

            // Delete all files in storage/framework/views except .gitignore
            $cachePath  = base_path('storage/framework/views');
            $cacheFiles = File::files($cachePath);
            foreach ($cacheFiles as $file) {
                if ($file->getFilename() !== '.gitignore') {
                    File::delete($file);
                }
            }
        } catch (\Exception $e) {
        }

        return response()->json([
            'status'  => true,
            'message' => __('Locale updated!')
        ]);
    }
}
