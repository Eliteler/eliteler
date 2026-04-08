<?php

namespace App\Http\Controllers;

use App\Setting;
use App\Currency;
use App\BusinessCard;
use App\BusinessField;
use App\StoreBusinessHour;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\URL;
use App\Services\GoBizCommonService;
use Artesaos\SEOTools\Facades\JsonLd;
use Artesaos\SEOTools\Facades\SEOMeta;
use Artesaos\SEOTools\Facades\SEOTools;
use Illuminate\Support\Facades\Session;
use Artesaos\SEOTools\Facades\OpenGraph;

class StorePolicyController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function policy(Request $request, $storeId)
    {
        // Get page title
        $route = $request->segment(2);

        // Get store details
        $card_details = BusinessCard::where('card_url', $storeId)->where('card_status', 'activated')->first();

        // Check if card is activated
        if (!$card_details) {
            return view('errors.404');
        }

        $whatsAppNumberExists = BusinessField::where('card_id', $card_details->card_id)->where('type', 'wa')->exists();

        $enquiry_button = '#';

        $business_card_details = DB::table('business_cards')->where('business_cards.card_id', $card_details->card_id)
            ->join('users', 'business_cards.user_id', '=', 'users.user_id')
            ->join('themes', 'business_cards.theme_id', '=', 'themes.theme_id')
            ->select('business_cards.*', 'users.plan_details', 'themes.theme_code')
            ->first();

        if ($business_card_details) {
            $settings = GoBizCommonService::settings();
            $config   = GoBizCommonService::config();

            // Delivery Options
            $deliveryOptions = json_decode($business_card_details->delivery_options);

            // Business Hours
            $businessHours = StoreBusinessHour::where('store_id', $business_card_details->card_id)->first();

            switch ($route) {
                case 'privacy-policy':
                    $pageTitle = 'Privacy Policy';
                    $pageContent = $business_card_details->privacy_policy;
                    break;
                case 'terms-and-conditions':
                    $pageTitle = 'Terms & Conditions';
                    $pageContent = $business_card_details->terms_and_conditions;
                    break;
                case 'return-policy':
                    $pageTitle = 'Return & Refund Policy';
                    $pageContent = $business_card_details->refund_policy;
                    break;
                case 'shipping-policy':
                    $pageTitle = 'Shipping Policy';
                    $pageContent = $business_card_details->shipping_policy;
                    break;
                case 'cookie-policy':
                    $pageTitle = 'Cookie Policy';
                    $pageContent = $business_card_details->cookie_policy;
                    break;
                case 'contact':
                    $pageTitle = 'Contact Information / Customer Support Policy';
                    $pageContent = $business_card_details->customer_support_policy;
                    break;
                default:
                    $pageTitle = 'Privacy Policy';
                    $pageContent = $business_card_details->privacy_policy;
                    break;
            }

            SEOTools::setTitle(trans($pageTitle) . ' - ' . $business_card_details->title);
            SEOTools::setDescription($business_card_details->sub_title);
            SEOTools::addImages([url($business_card_details->profile)]);

            SEOMeta::setTitle(trans($pageTitle) . ' - ' . $business_card_details->title);
            SEOMeta::setDescription($business_card_details->sub_title);
            SEOMeta::addMeta('article:section', trans($pageTitle) . ' - ' . $business_card_details->title, 'property');
            SEOMeta::addKeyword(["'" . trans($pageTitle) . ' - ' . $business_card_details->title . "'", "'" . trans($pageTitle) . ' - ' . $business_card_details->title . " vcard online'"]);

            // Add Canonical URL
            SEOMeta::setCanonical(url()->current());

            OpenGraph::setTitle(trans($pageTitle) . ' - ' . $business_card_details->title);
            OpenGraph::setDescription($business_card_details->sub_title);
            OpenGraph::setUrl(url($business_card_details->card_url));
            OpenGraph::addImage([url($business_card_details->profile)]);

            JsonLd::setTitle(trans($pageTitle) . ' - ' . $business_card_details->title);
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
            $currency = GoBizCommonService::currencies()->where('iso_code', $currency)->first();
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

            $datas = compact('card_details', 'plan_details', 'store_details', 'business_card_details', 'settings', 'shareComponent', 'shareContent', 'config', 'enquiry_button', 'whatsapp_msg', 'currency', 'whatsAppNumberExists', 'deliveryOptions', 'businessHours', 'pageTitle', 'pageContent');

            return view('templates.store.' . $business_card_details->theme_code . '.policy', $datas);
        }
    }
}
