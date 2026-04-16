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

use App\Setting;
use App\BusinessCard;
use App\BusinessField;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
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
    public function socialLinks(Request $request, $id)
    {
        // Queries
        $business_card = BusinessCard::where('card_id', $id)->first();

        // Check business card
        if ($business_card == null) {
            return redirect()->route('user.cards')->with('failed', trans('Card not found!'));
        } else {
            // Queries
            $features = BusinessField::where('card_id', $id)->orderBy('id', 'asc')->get();
            $plan = DB::table('users')->where('user_id', Auth::user()->user_id)->where('status', 1)->first();
            $plan_details = json_decode($plan->plan_details);
            $settings = Setting::where('status', 1)->first();

            if ($plan_details->no_of_links > 0) {
                return view('user.pages.edit-cards.edit-social-links', compact('business_card', 'plan_details', 'features', 'settings'));
            } else if ($plan_details->no_of_payments > 0) {
                return redirect()->route('user.edit.payment.links', request()->segment(3));
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

    // Update social links
    public function updateSocialLinks(Request $request, $id)
    {
        // Queries
        $business_card = BusinessCard::where('card_id', $id)->first();

        // Check business card
        if ($business_card == null) {
            return redirect()->route('user.cards')->with('failed', trans('Card not found!'));
        } else {
            // Check icon
            if ($request->icon) {
                // Get temporary title before delete
                $temp_title = BusinessField::where('card_id', $id)->first();
                $tempTitle  = $temp_title?->title ?? 'Social Links';

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
                        if (isset($request->type[$i]) && isset($request->icon[$i]) && isset($request->value[$i])) {

                            $type = $request->type[$i];
                            $customContent = $request->value[$i];

                            if (!empty($customContent)) {
                                if (strpos($customContent, 'http://') === 0 || strpos($customContent, 'https://') === 0) {
                                    $customContent = str_replace('http://', 'https://', $customContent);
                                } else {
                                    $baseUrls = [
                                        'facebook' => 'facebook.com/',
                                        'instagram' => 'instagram.com/',
                                        'x-twitter' => 'x.com/',
                                        'linkedin' => 'linkedin.com/in/',
                                        'pinterest' => 'pinterest.com/',
                                        'reddit' => 'reddit.com/user/',
                                        'tiktok' => 'tiktok.com/@',
                                        'threads' => 'threads.net/@',
                                        'snapchat' => 'snapchat.com/add/',
                                        'telegram' => 't.me/',
                                        'tumblr' => 'tumblr.com/',
                                        'quora' => 'quora.com/profile/',
                                        'wa' => 'wa.me/',
                                    ];

                                    if (isset($baseUrls[$type])) {
                                        if (strpos($customContent, $baseUrls[$type]) === false) {
                                            if (in_array($type, ['tiktok', 'threads']) && strpos($customContent, '@') === 0) {
                                                $customContent = substr($customContent, 1);
                                            }
                                            
                                            // Handle WhatsApp specifically to remove + or 00
                                            if ($type == 'wa') {
                                                $customContent = ltrim($customContent, '+');
                                                if (strpos($customContent, '00') === 0) {
                                                    $customContent = substr($customContent, 2);
                                                }
                                            }

                                            $customContent = 'https://' . $baseUrls[$type] . ltrim($customContent, '/');
                                        } else {
                                            $customContent = 'https://' . ltrim($customContent, '/');
                                        }
                                    } elseif ($type == 'url' || $type == 'g-review') {
                                        $customContent = 'https://' . ltrim($customContent, '/');
                                    }
                                }
                            }

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
                            $field->title = $tempTitle;
                            $field->type = $request->type[$i];
                            $field->icon = $request->icon[$i];
                            $field->label = $request->label[$i] ?? '';
                            $field->content = $customContent;
                            $field->position = $i + 1;
                            $field->save();
                        } else {
                            return redirect()->route('user.edit.social.links', $id)->with('failed', trans('Please add at least one bio link.'));
                        }
                    }

                    // Check type
                    if ($business_card->type == "personal") {
                        return redirect()->route('user.edit.appointment', $id)->with('success', trans('Bio links are updated.'));
                    } else {
                        return redirect()->route('user.edit.payment.links', $id)->with('success', trans('Bio links are updated.'));
                    }
                } else {
                    return redirect()->route('user.edit.social.links', $id)->with('failed', trans('The maximum limit was exceeded'));
                }
            } else {
                return redirect()->route('user.edit.social.links', $id)->with('failed', trans('Please add at least one bio link.'));
            }
        }
    }
}
