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

use App\Setting;
use App\BusinessCard;
use App\BusinessField;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Google\Service\MyBusinessLodging\Business;
use Illuminate\Support\Facades\Auth;

class SocialLinkController extends Controller
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

    // Social Links
    public function socialLinks(Request $request)
    {
        // Queries
        $plan = DB::table('users')->where('user_id', Auth::user()->user_id)->where('status', 1)->first();
        $settings = Setting::where('status', 1)->first();
        $plan_details = json_decode($plan->plan_details);

        // Check plan details
        if($plan_details->no_of_links > 0) {
            // Get business card details
            $card_details = BusinessCard::where('card_id', $request->segment(3))->first();

            return view('user.pages.cards.social-links', compact('card_details', 'plan_details', 'settings'));
        } else if($plan_details->no_of_payments > 0) {
            return redirect()->route('user.payment.links', request()->segment(3));
        } else if($plan_details->no_of_services > 0) {
            return redirect()->route('user.services', request()->segment(3));
        } else if($plan_details->no_of_vcard_products > 0) {
            return redirect()->route('user.vproducts', request()->segment(3));
        } else if($plan_details->no_of_galleries > 0) {
            return redirect()->route('user.galleries', request()->segment(3));
        } else if($plan_details->no_testimonials > 0) {
            return redirect()->route('user.testimonials', request()->segment(3));
        } else {
            return redirect()->route('user.popups', request()->segment(3));
        }
    }

    // Save social links
    public function saveSocialLinks(Request $request, $id)
    {
        // Queries
        $business_card = BusinessCard::where('card_id', $id)->first();

        // Check business card
        if ($business_card == null) {
            return redirect()->route('user.cards')->with('failed', trans('Card not found!'));
        } else {
            // Check icon
            if ($request->icon != null) {

                // Delete previous links
                BusinessField::where('card_id', $id)->delete();

                // Get plan details
                $plan = DB::table('users')->where('user_id', Auth::user()->user_id)->where('status', 1)->first();
                $plan_details = json_decode($plan->plan_details);

                // Check social links limit
                if (count($request->icon) <= $plan_details->no_of_links) {

                    // Check dynamic fields foreach
                    for ($i = 0; $i < count($request->icon); $i++) {

                        // Check dynamic fields
                        if (isset($request->icon[$i]) && isset($request->label[$i]) && isset($request->value[$i])) {

                            $customContent = $request->value[$i];
                            // Replace http with https
                            $customContent = str_replace('http://', 'https://', $customContent);

                            // YouTube (standard, short, shorts)
                            if ($request->type[$i] == 'youtube') {
                                $url = $request->value[$i];
                                $parsedUrl = parse_url($url);
                                $customContent = null;

                                // Handle query string (e.g., ?v=VIDEO_ID)
                                if (!empty($parsedUrl['query'])) {
                                    parse_str($parsedUrl['query'], $queryParams);
                                    if (isset($queryParams['v'])) {
                                        $customContent = $queryParams['v'];
                                    }
                                }

                                // Handle youtu.be short links
                                if (!$customContent && isset($parsedUrl['host']) && $parsedUrl['host'] === 'youtu.be') {
                                    $customContent = ltrim($parsedUrl['path'], '/');
                                }

                                // Handle /shorts/VIDEO_ID
                                if (!$customContent && isset($parsedUrl['path']) && strpos($parsedUrl['path'], '/shorts/') === 0) {
                                    $customContent = str_replace('/shorts/', '', $parsedUrl['path']);
                                }

                                // Fallback to full URL if no ID extracted
                                if (!$customContent) {
                                    $customContent = $url;
                                }
                            }

                            // Google Map
                            if ($request->type[$i] == 'map') {
                                $value = $request->value[$i];
                                $customContent = null;
                            
                                // Normalize common URL variants
                                $value = str_replace([
                                    'https://maps.app.goo.gl',
                                    'https://maps.google.com',
                                    'https://www.google.co.in/maps',
                                    'https://www.google.com/maps',
                                ], 'https://www.google.com/maps', $value);
                            
                                if (substr($value, 0, 3) === 'pb=') {
                                    // Already a Google Maps embed query string
                                    $customContent = $value;
                            
                                } elseif (strpos($value, '<iframe') !== false) {
                                    // Extract src attribute from iframe
                                    preg_match('/<iframe[^>]+src="([^"]+)"/', $value, $matches);
                                    if (!empty($matches[1])) {
                                        $embedUrl = $matches[1];
                                        $customContent = str_replace('https://www.google.com/maps/embed?', '', $embedUrl);
                                    }
                            
                                } elseif (strpos($value, 'https://www.google.com/maps/embed?') !== false) {
                                    // Direct embed link
                                    $customContent = str_replace('https://www.google.com/maps/embed?', '', $value);
                            
                                } elseif (strpos($value, 'data=') !== false) {
                                    // Extract only the data=... part
                                    $queryStr = parse_url($value, PHP_URL_PATH) . '?' . parse_url($value, PHP_URL_QUERY);
                                    if (preg_match('/data=([^&]+)/', $queryStr, $matches)) {
                                        $customContent = $matches[1];
                                    }
                            
                                } else {
                                    // Fallback
                                    $customContent = $value;
                                }
                            
                                // Final embed iframe output
                                if ($customContent && strpos($customContent, '<iframe') === false && strpos($customContent, 'maps/embed') === false) {
                                    $customContent = $customContent;
                                }
                            }

                            // Save
                            $field = new BusinessField();
                            $field->card_id = $id;
                            $field->title = 'Social Links';
                            $field->type = $request->type[$i];
                            $field->icon = $request->icon[$i];
                            $field->label = $request->label[$i];
                            $field->content = $customContent;
                            $field->position = $i + 1;
                            $field->save();
                        } else {
                            return redirect()->route('user.social.links', $id)->with('failed', trans('At least add one bio link.'));
                        }
                    }

                    // Check type
                    if ($business_card->type == 'personal') {
                        if ($plan_details->appointment == 1) {
                            return redirect()->route('user.appointment', $id)->with('success', trans('Bio links are updated.'));
                        } else {
                            return redirect()->route('user.cards',)->with('success', trans('Your virtual business card is ready.'));
                        }
                    } else {
                        return redirect()->route('user.payment.links', $id)->with('success', trans('Bio links are updated.'));
                    }
                } else {
                    return redirect()->route('user.social.links', $id)->with('failed', trans('The maximum limit was exceeded.'));
                }
            } else {
                return redirect()->route('user.social.links', $id)->with('failed', trans('At least add one bio link.'));
            }
        }
    }
}
