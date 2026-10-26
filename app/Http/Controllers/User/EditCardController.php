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

namespace App\Http\Controllers\User;

use App\BusinessCard;
use App\Http\Controllers\Controller;
use App\Plan;
use App\Setting;
use App\Theme;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;

class EditCardController extends Controller
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

    // Edit Card
    public function editCard(Request $request, $id)
    {
        // Queries
        $business_card = BusinessCard::where('card_id', $id)->first();

        // Check business card
        if ($business_card == null) {
            return redirect()->route('user.cards')->with('failed', trans('Card not found!'));
        }

        // New business card type
        $new_business_card_type = null;

        if (
            Schema::hasTable('business_card_types') &&
            ! in_array($business_card->type, ['business', 'custom', 'personal'])
        ) {
            $new_business_card_type = DB::table('business_card_types')
                ->where('business_card_type', $business_card->type)
                ->where('status', 1)
                ->select(
                    'business_card_type',
                    'business_card_type_name',
                    'business_card_type_image',
                    'business_card_themes'
                )
                ->first();

            if ($new_business_card_type) {
                $new_business_card_type->business_card_themes = json_decode(
                    $new_business_card_type->business_card_themes,
                    true
                );
            }
        }

        // Check business card type
        if ($business_card->type == "business") {
            $themes = Theme::where('theme_description', 'vCard')->whereNotIn('theme_id', ["588969111147", "588969111014", "588969111015", "588969111016", "588969111017", "588969111018", "588969111019", "588969111020", "588969111021"])->orderBy('id', 'desc')->where('status', 1)->get();
        } else if ($business_card->type == "personal") {
            $themes = Theme::where('theme_description', 'vCard')->whereIn('theme_id', ["588969111014", "588969111015", "588969111016", "588969111017", "588969111018", "588969111019", "588969111020", "588969111021"])->orderBy('id', 'desc')->where('status', 1)->get();
        } else if ($business_card->type == "custom") {
            $themes = Theme::where('theme_description', 'vCard')->where('theme_id', "588969111147")->where('status', 1)->get();
        } else {
            // Get themes
            $themes = $new_business_card_type
                ? Theme::where('theme_description', 'vCard')
                ->whereIn('theme_id', $new_business_card_type->business_card_themes ?? [])
                ->where('status', 1)
                ->orderByDesc('id')
                ->get()
                : collect();
        }

        $config = DB::table('config')->get();

        // Check card type
        if ($business_card->card_type == "store") {
            return redirect()->route('user.edit.store', $id);
        } else {
            $settings     = Setting::where('status', 1)->first();
            $plan_details = Plan::where('plan_id', Auth::user()->plan_id)->first();

            return view('user.pages.edit-cards.edit-card', compact('themes', 'business_card', 'settings', 'plan_details', 'config'));
        }
    }

    // Update card
    public function updateBusinessCard(Request $request, $id)
    {
        // Validator
        $validator = Validator::make($request->all(), [
            'theme_id'    => 'required',
            'card_lang'   => 'required',
            'title'       => 'required',
            'cover_type'  => 'required',
            'subtitle'    => 'nullable',
            'description' => 'nullable',
        ]);

        // Validate alert
        if ($validator->fails()) {
            return back()
                ->with('failed', $validator->messages()->all()[0])
                ->withInput();
        }

        // Check theme_id is store
        $themeExists = Theme::where('theme_id', $request->theme_id)
            ->where('theme_description', 'vCard')
            ->where('status', 1)
            ->count();

        if ($themeExists == 0) {
            return back()->with('failed', trans('Invalid Theme'));
        }

        // Check card exists
        $business_card = BusinessCard::where('card_id', $id)->first();

        if ($business_card == null) {
            return redirect()
                ->route('user.cards')
                ->with('failed', trans('Card not found!'));
        }

        // Get user plan details
        $user_details = User::where('user_id', Auth::user()->user_id)->first();
        $plan_details = json_decode($user_details->plan_details, true);

        // Personalized Link
        if (!empty($plan_details['personalized_link'])) {
            // Personalized Link is available in the plan
            $personalized_link = $request->link ?: $business_card->card_url;
        } else {
            // Personalized Link is not available in the plan
            // Always keep the existing card URL
            $personalized_link = $business_card->card_url;
        }

        // Remove spaces
        $personalized_link = trim($personalized_link);

        // Remove https:// or http://
        $personalized_link = str_replace(
            ['https://', 'http://'],
            '',
            $personalized_link
        );

        // Remove www.
        $personalized_link = str_replace('www.', '', $personalized_link);

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

        // Card URL
        $card_url = $personalized_link;

        // Check if card URL already exists
        // Exclude the current card from the check
        $current_card = BusinessCard::where('card_url', $card_url)
            ->where('card_id', '!=', $id)
            ->where('card_status', '!=', 'deleted')
            ->count();

        if ($current_card > 0) {
            return redirect()
                ->route('user.edit.card', $id)
                ->with('failed', trans('Sorry, the personalized link was already registered.'))
                ->withInput();
        }

        // Cover Type - Validation
        if ($request->cover_type != 'none') {

            if (in_array(
                $request->cover_type,
                ['youtube', 'youtube-ap', 'vimeo', 'vimeo-ap', 'photo'],
                true
            )) {

                // Cover URL no need to update for photo type
                if ($request->cover_type != 'photo') {

                    // YouTube URL
                    if (
                        $request->cover_type == 'youtube' ||
                        $request->cover_type == 'youtube-ap'
                    ) {
                        try {
                            // Without www
                            $cover = str_replace(
                                'https://youtube.com/watch?v=',
                                '',
                                $request->cover_url
                            );

                            // With www
                            $cover = str_replace(
                                'https://www.youtube.com/watch?v=',
                                '',
                                $cover
                            );
                        } catch (\Exception $e) {
                            $cover = str_replace(
                                'https://youtu.be/',
                                '',
                                $request->cover_url
                            );

                            // With www
                            $cover = str_replace(
                                'https://www.youtu.be/',
                                '',
                                $cover
                            );
                        }
                    }

                    // Vimeo URL
                    if (
                        $request->cover_type == 'vimeo' ||
                        $request->cover_type == 'vimeo-ap'
                    ) {
                        try {
                            // Without www
                            $cover = str_replace(
                                'https://vimeo.com/',
                                '',
                                $request->cover_url
                            );

                            // With www
                            $cover = str_replace(
                                'https://www.vimeo.com/',
                                '',
                                $cover
                            );
                        } catch (\Exception $e) {
                            $cover = str_replace(
                                'https://vimeo.com/album/',
                                '',
                                $request->cover_url
                            );

                            // With www
                            $cover = str_replace(
                                'https://www.vimeo.com/album/',
                                '',
                                $cover
                            );
                        }
                    }

                    $cover_type = $request->cover_type;
                } else {

                    $cover = $request->cover;
                    $cover_type = $request->cover_type;
                }
            }
        } else {

            $cover = '';
            $cover_type = $request->cover_type;
        }

        // Update
        BusinessCard::where('card_id', $id)->update([
            'profile'     => $request->logo,
            'cover_type'  => $cover_type,
            'cover'       => $cover,
            'theme_id'    => $request->theme_id,
            'card_lang'   => $request->card_lang,
            'card_url'    => $card_url,
            'title'       => $request->title,
            'title2'      => $request->title2,
            'sub_title'   => $request->subtitle,
            'subtitle2'   => $request->subtitle2,
            'copyright'   => $request->copyright,
            'description' => $request->description,
            'email_from_name'    => $request->email_from_name,
            'email_from_address' => $request->email_from_address,
        ]);

        return redirect()
            ->route('user.edit.social.links', $id)
            ->with('success', trans('Details have been updated.'));
    }
}
