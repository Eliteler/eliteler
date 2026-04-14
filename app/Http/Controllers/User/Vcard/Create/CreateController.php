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

use App\User;
use App\Theme;
use App\Setting;
use App\BusinessCard;
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

    // Create Card
    public function CreateCard(Request $request)
    {
        // Queries
        if ($request->query('type') == "business") {
            $themes = Theme::where('theme_description', 'vCard')->whereNotIn('theme_id', ["588969111014", "588969111015", "588969111016", "588969111017", "588969111018", "588969111019", "588969111020", "588969111021", "588969111147"])->orderBy('id', 'desc')->where('status', 1)->get();
        } else {
            $themes = Theme::where('theme_description', 'vCard')->whereIn('theme_id', ["588969111014", "588969111015", "588969111016", "588969111017", "588969111018", "588969111019", "588969111020", "588969111021"])->orderBy('id', 'desc')->where('status', 1)->get();
        }

        $settings = Setting::where('status', 1)->first();
        $cards    = BusinessCard::where('user_id', Auth::user()->user_id)->where('card_type', 'vcard')->where('card_status', 'activated')->count();

        // Active plan details in user
        $plan         = DB::table('users')->where('user_id', Auth::user()->user_id)->where('status', 1)->first();
        $plan_details = json_decode($plan->plan_details);

        $config = DB::table('config')->get();

        // Check unlimited cards
        if ($plan_details->no_of_vcards == 999) {
            $no_cards = 999999;
        } else {
            $no_cards = $plan_details->no_of_vcards;
        }

        // Chech vcard creation limit
        if ($cards < $no_cards) {
            return view('user.pages.cards.create-card', compact('themes', 'settings', 'plan_details', 'config'));
        } else {
            return redirect()->route('user.cards')->with('failed', trans('The maximum limit has been exceeded. Please upgrade your plan.'));
        }
    }

    // Save card
    public function saveBusinessCard(Request $request)
    {
        // Validator
        $validator = Validator::make($request->all(), [
            'theme_id'    => 'required',
            'card_lang'   => 'required',
            'logo'        => 'required',
            'title'       => 'required',
            'cover_type'  => 'required',
            'subtitle'    => 'required',
            'description' => 'required',
        ]);

        // Validate alert
        if ($validator->fails()) {
            return back()->with('failed', $validator->messages()->all()[0])->withInput();
        }

        // Check theme_id is store
        $themeExists = Theme::where('theme_id', $request->theme_id)->where('theme_description', 'vCard')->where('status', 1)->count();

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
        $cards        = BusinessCard::where('user_id', Auth::user()->user_id)->where('card_type', 'vcard')->where('card_status', 'activated')->count();
        $user_details = User::where('user_id', Auth::user()->user_id)->first();
        $plan_details = json_decode($user_details->plan_details, true);

        // Card URL
        $card_url     = strtolower(preg_replace('/\s+/', '-', $personalized_link));
        $current_card = BusinessCard::where('card_url', $card_url)->where('card_status', '!=', 'deleted')->count();

        // Ger purchased plan details
        if ($plan_details['no_of_vcards'] == 999) {
            $no_cards = 999999;
        } else {
            $no_cards = $plan_details['no_of_vcards'];
        }

        // Check card URL is available
        if ($current_card == 0) {
            // Checking, If the user plan allowed card creation is less than created card.
            if ($cards < $no_cards) {
                try {
                    // Check banner image
                    $cover = null;
                    if ($request->has('cover')) {
                        $cover = $request->cover;
                    }

                    //Cover Type - Validation
                    $cover_type = "none"; // Default Value
                    if (in_array($request->cover_type, ["youtube", "youtube-ap", "vimeo", "vimeo-ap", "photo"], true)) {
                        $cover_type = $request->cover_type;
                        // Cover URL no need to update for photo type.
                        if ($request->cover_type != "photo") {
                            if ($request->cover_type == "youtube" || $request->cover_type == "youtube-ap") {
                                // Remove the "https://youtube.com/watch?v=" from the URL
                                try {
                                    // Without www
                                    $cover = str_replace("https://youtube.com/watch?v=", "", $request->cover_url);
                                    // With www
                                    $cover = str_replace("https://www.youtube.com/watch?v=", "", $cover);
                                } catch (\Exception $e) {
                                    $cover = str_replace("https://youtu.be/", "", $request->cover_url);
                                    // With www
                                    $cover = str_replace("https://www.youtu.be/", "", $cover);
                                }
                            }
                            // Vimeo URL
                            if ($request->cover_type == "vimeo" || $request->cover_type == "vimeo-ap") {
                                // Remove the "https://vimeo.com/" from the URL
                                try {
                                    $cover = str_replace("https://vimeo.com/", "", $request->cover_url);
                                    // With www
                                    $cover = str_replace("https://www.vimeo.com/", "", $cover);
                                } catch (\Exception $e) {
                                    $cover = str_replace("https://vimeo.com/album/", "", $request->cover_url);
                                    // With www
                                    $cover = str_replace("https://www.vimeo.com/album/", "", $cover);
                                }
                            }
                        }
                    }

                    // Save
                    $card              = new BusinessCard();
                    $card->card_id     = $cardId;
                    $card->user_id     = Auth::user()->user_id;
                    $card->type        = $request->type;
                    $card->theme_id    = $request->theme_id;
                    $card->card_lang   = $request->card_lang;
                    $card->cover_type  = $cover_type;
                    $card->cover       = $cover;
                    $card->profile     = $request->logo;
                    $card->card_url    = $card_url;
                    $card->card_type   = 'vcard';
                    $card->title       = $request->title;
                    $card->sub_title   = $request->subtitle;
                    $card->description = $request->description;

                    if ($request->type == 'custom') {
                        $card->custom_styles = json_encode([
                            'header_style'            => 'column',
                            'layout'                  => 'row',
                            'profile_image_style'     => 'circle',
                            'font_family'             => 'Poppins',
                            "background_type"         => "single_color",
                            "background_color"        => "#ffffff",
                            "gradient_type"           => "vertical",
                            "gradient_start"          => "#ffffff",
                            "gradient_end"            => "top_to_bottom",
                            "image_url"               => "",
                            "button_background_type"  => "single_color",
                            "button_background_color" => "#000000",
                            "button_gradient_start"   => "#000000",
                            "button_gradient_end"     => "#000000",
                            "button_edge"             => "rounded",
                            'title_color'             => '#000000',
                            'sub_title_color'         => '#000000',
                            'description_color'       => '#000000',
                            "button_text_color"       => "#ffffff",
                            "button_icon_color"       => "#ffffff",
                            "heading_color"           => "#000000",
                            "card_edge"               => "rounded",
                            "bottom_bar_color"        => "#000000",
                        ]);
                    }

                    $card->save();

                    return redirect()->route('user.edit.social.links', $cardId)->with('success', trans('New Business Card Created Successfully!'));
                } catch (\Exception $th) {
                    return redirect()->back()->with('failed', trans('Sorry, the personalized link was already registered.'));
                }
            } else {
                return redirect()->back()->with('failed', trans('Maximum card creation limit is exceeded, Please upgrade your plan to add more card(s).'));
            }
        } else {
            return redirect()->back()->with('failed', trans('Sorry, the personalized link was already registered.'));
        }
    }

    // Check unique card / store link
    public function checkLink(Request $request)
    {
        // Requested link
        $link           = $request->link;
        $is_present     = DB::table('business_cards')->where('card_url', $link)->where('card_status', '!=', 'deleted')->count();
        $resp           = [];
        $resp['status'] = 'failed';

        // Check
        if ($is_present == 0) {
            $resp['status'] = 'success';
        } else {
            $resp['status'] = 'failed';
        }

        return response()->json($resp);
    }

    // Cropping image
    public function vcardCroppedImage(Request $request)
    {
        $croppedImage = $request->file('croppedImage');

        if ($croppedImage && $croppedImage->isValid()) {
            // Generate a random unique name for the image
            $imageName = Str::random(20) . '.' . $croppedImage->extension();

            // Store the image in storage/app/public/profile-images
            Storage::disk('public')->putFileAs('profile-images', $croppedImage, $imageName);

            // Generate the public URL (equivalent to "storage/profile-images/...") after running `php artisan storage:link`
            $imageUrl = Storage::url('profile-images/' . $imageName);

            return response()->json(['success' => true, 'imageUrl' => $imageUrl]);
        }

        return response()->json(['success' => false, 'message' => 'Invalid image uploaded.']);
    }
}
