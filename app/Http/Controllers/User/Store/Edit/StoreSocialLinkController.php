<?php

/*
 |--------------------------------------------------------------------------
 | Eliteler vCard SaaS
 |--------------------------------------------------------------------------
 | Developed by NativeCode © 2021 - https://nativecode.in
 | All rights reserved
 | Unauthorized distribution is prohibited
 |--------------------------------------------------------------------------
*/

namespace App\Http\Controllers\User\Store\Edit;

use App\User;
use App\Setting;
use App\BusinessCard;
use App\BusinessField;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class StoreSocialLinkController extends Controller
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
     * Show the store social links page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */

    // Edit Store Social Links
    public function editStoreSocialLinks(Request $request, $id)
    {
        // Queries
        $business_card = BusinessCard::where('user_id', Auth::user()->user_id)
            ->where('card_id', $id)
            ->where('card_type', 'store')
            ->first();

        // Check business card
        if ($business_card == null) {
            return redirect()->route('user.stores')->with('failed', trans('Store not found!'));
        } else {
            // Get existing store social links
            $features = BusinessField::where('card_id', $id)
                ->where('field_source', 'store')
                ->orderBy('id', 'asc')
                ->get();

            // Plan details
            $plan = User::where('user_id', Auth::user()->user_id)
                ->where('status', 1)
                ->first();
            $plan_details = json_decode($plan->plan_details);

            $settings = Setting::where('status', 1)->first();

            return view('user.pages.edit-store.edit-social-links', compact('business_card', 'plan_details', 'features', 'settings'));
        }
    }

    // Update Store Social Links
    public function updateStoreSocialLinks(Request $request, $id)
    {
        // Queries
        $business_card = BusinessCard::where('user_id', Auth::user()->user_id)
            ->where('card_id', $id)
            ->where('card_type', 'store')
            ->first();

        // Check business card
        if ($business_card == null) {
            return redirect()->route('user.stores')->with('failed', trans('Store not found!'));
        } else {
            // Check icon
            if ($request->icon) {
                // Delete previous store social links only
                BusinessField::where('card_id', $id)
                    ->where('field_source', 'store')
                    ->delete();

                // Get plan details
                $plan = DB::table('users')
                    ->where('user_id', Auth::user()->user_id)
                    ->where('status', 1)
                    ->first();
                $plan_details = json_decode($plan->plan_details);

                // Check social links limit
                if (count($request->icon) <= $plan_details->no_of_links) {

                    // Loop through submitted links
                    for ($i = 0; $i < count($request->icon); $i++) {

                        // Validate required fields
                        if (isset($request->type[$i]) && isset($request->icon[$i]) && isset($request->value[$i])) {

                            $type = $request->type[$i];
                            $customContent = $request->value[$i];

                            if (!empty($customContent)) {
                                // WhatsApp: extract number only
                                if ($type == 'wa') {
                                    $customContent = str_replace(['https://wa.me/', 'http://wa.me/', 'wa.me/'], '', $customContent);
                                    $customContent = ltrim($customContent, '+');
                                    if (strpos($customContent, '00') === 0) {
                                        $customContent = substr($customContent, 2);
                                    }
                                } elseif (strpos($customContent, 'http://') === 0 || strpos($customContent, 'https://') === 0) {
                                    $customContent = str_replace('http://', 'https://', $customContent);
                                } else {
                                    $baseUrls = [
                                        'facebook'  => 'facebook.com/',
                                        'instagram' => 'instagram.com/',
                                        'x-twitter' => 'x.com/',
                                        'linkedin'  => 'linkedin.com/in/',
                                        'pinterest' => 'pinterest.com/',
                                        'reddit'    => 'reddit.com/user/',
                                        'tiktok'    => 'tiktok.com/@',
                                        'threads'   => 'threads.net/@',
                                        'snapchat'  => 'snapchat.com/add/',
                                        'telegram'  => 't.me/',
                                        'tumblr'    => 'tumblr.com/',
                                        'quora'     => 'quora.com/profile/',
                                    ];

                                    if (isset($baseUrls[$type])) {
                                        if (strpos($customContent, $baseUrls[$type]) === false) {
                                            if (in_array($type, ['tiktok', 'threads']) && strpos($customContent, '@') === 0) {
                                                $customContent = substr($customContent, 1);
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

                            // Save
                            $field               = new BusinessField();
                            $field->card_id      = $id;
                            $field->title        = 'Store Social Links';
                            $field->type         = $request->type[$i];
                            $field->icon         = $request->icon[$i];
                            $field->label        = $request->label[$i] ?? '';
                            $field->content      = $customContent;
                            $field->position     = $i + 1;
                            $field->field_source = 'store';
                            $field->save();

                        } else {
                            return redirect()->route('user.edit.store.social.links', $id)
                                ->with('failed', trans('Please fill all fields.'));
                        }
                    }

                    return redirect()->route('user.edit.store.social.links', $id)
                        ->with('success', trans('Social links updated successfully.'));

                } else {
                    return redirect()->route('user.edit.store.social.links', $id)
                        ->with('failed', trans('The maximum limit was exceeded'));
                }
            } else {
                // No links submitted - delete all and save empty
                BusinessField::where('card_id', $id)
                    ->where('field_source', 'store')
                    ->delete();

                return redirect()->route('user.edit.store.social.links', $id)
                    ->with('success', trans('Social links updated successfully.'));
            }
        }
    }
}
