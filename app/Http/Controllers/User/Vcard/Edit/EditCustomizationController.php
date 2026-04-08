<?php

namespace App\Http\Controllers\User\Vcard\Edit;

use App\Setting;
use App\BusinessCard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class EditCustomizationController extends Controller
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

    // Edit customization
    public function editCustomization(Request $request, $id)
    {
        // Queries
        $business_card = BusinessCard::where('card_id', $id)->first();
        $plan          = DB::table('users')->where('user_id', Auth::user()->user_id)->where('status', 1)->first();
        $plan_details  = json_decode($plan->plan_details);
        $settings      = Setting::where('status', 1)->first();

        // Check business card
        if ($business_card == null) {
            return redirect()->route('user.cards')->with('failed', trans('Card not found!'));
        } else {
            return view('user.pages.edit-cards.edit-customization', compact('plan_details', 'business_card', 'settings'));
        }
    }

    // Update customization
    public function updateCustomization(Request $request)
    {
        // Validator
        $validator = Validator::make($request->all(), [
            'type'    => 'string|max:50',
            'card_id' => 'string|max:100',
        ]);

        // Check result
        if (! $validator->fails()) {
            if ($request->type == 'title_color') {
                $customStyles                = json_decode(BusinessCard::where('card_id', $request->card_id)->first()->custom_styles, true);
                $customStyles['title_color'] = $request->title_color;

                BusinessCard::where('card_id', $request->card_id)->update([
                    'custom_styles' => json_encode($customStyles),
                ]);
                return response()->json(['status' => 'success', 'type' => $request->type]);
            } elseif ($request->type == 'sub_title_color') {
                $customStyles                    = json_decode(BusinessCard::where('card_id', $request->card_id)->first()->custom_styles, true);
                $customStyles['sub_title_color'] = $request->sub_title_color;

                BusinessCard::where('card_id', $request->card_id)->update([
                    'custom_styles' => json_encode($customStyles),
                ]);
                return response()->json(['status' => 'success', 'type' => $request->type]);
            } elseif ($request->type == 'description_color') {
                $customStyles                      = json_decode(BusinessCard::where('card_id', $request->card_id)->first()->custom_styles, true);
                $customStyles['description_color'] = $request->description_color;

                BusinessCard::where('card_id', $request->card_id)->update([
                    'custom_styles' => json_encode($customStyles),
                ]);
                return response()->json(['status' => 'success', 'type' => $request->type]);
            } elseif ($request->type == 'layout') {
                $customStyles           = json_decode(BusinessCard::where('card_id', $request->card_id)->first()->custom_styles, true);
                $customStyles['layout'] = $request->layout;

                BusinessCard::where('card_id', $request->card_id)->update([
                    'custom_styles' => json_encode($customStyles),
                ]);
                return response()->json(['status' => 'success', 'type' => $request->type]);
            } elseif ($request->type == 'profile_image_style') {
                $customStyles                        = json_decode(BusinessCard::where('card_id', $request->card_id)->first()->custom_styles, true);
                $customStyles['profile_image_style'] = $request->profile_image_style;

                BusinessCard::where('card_id', $request->card_id)->update([
                    'custom_styles' => json_encode($customStyles),
                ]);
                return response()->json(['status' => 'success', 'type' => $request->type]);
            } elseif ($request->type == 'font') {
                $customStyles                = json_decode(BusinessCard::where('card_id', $request->card_id)->first()->custom_styles, true);
                $customStyles['font_family'] = $request->font;

                BusinessCard::where('card_id', $request->card_id)->update([
                    'custom_styles' => json_encode($customStyles),
                ]);
                return response()->json(['status' => 'success', 'type' => $request->type]);
            } elseif ($request->type == 'bg_style') {
                $customStyles                    = json_decode(BusinessCard::where('card_id', $request->card_id)->first()->custom_styles, true);
                $customStyles['background_type'] = $request->bg_style;

                BusinessCard::where('card_id', $request->card_id)->update([
                    'custom_styles' => json_encode($customStyles),
                ]);
                return response()->json(['status' => 'success', 'type' => $request->type]);
            } elseif ($request->type == 'background_color') {
                $customStyles                     = json_decode(BusinessCard::where('card_id', $request->card_id)->first()->custom_styles, true);
                $customStyles['background_color'] = $request->background_color;

                BusinessCard::where('card_id', $request->card_id)->update([
                    'custom_styles' => json_encode($customStyles),
                ]);
                return response()->json(['status' => 'success', 'type' => $request->type]);
            } elseif ($request->type == 'gradient_background_color') {
                $customStyles                   = json_decode(BusinessCard::where('card_id', $request->card_id)->first()->custom_styles, true);
                $customStyles['gradient_start'] = $request->gradient_from_color;
                $customStyles['gradient_end']   = $request->gradient_end_color;

                BusinessCard::where('card_id', $request->card_id)->update([
                    'custom_styles' => json_encode($customStyles),
                ]);
                return response()->json(['status' => 'success', 'type' => $request->type]);
            } elseif ($request->type == 'background_image') {
                $validator = Validator::make($request->all(), [
                    'background_image' => 'required|mimes:jpeg,png,jpg,gif,svg,webp|max:' . env("SIZE_LIMIT"),
                ]);

                if ($validator->fails()) {
                    return response()->json([
                        'status' => 'failed',
                        'message' => $validator->errors()->first(),
                        'type' => $request->type
                    ]);
                }

                $file = $request->file('background_image');
                $extension = $file->getClientOriginalExtension();

                // Generate filename: card_id.extension
                $filename = $request->card_id . '.' . $extension;

                // Put the file into storage/app/public/images/card/background
                $path = 'images/card/background';
                Storage::disk('public')->putFileAs($path, $file, $filename);

                // Get public URL: /storage/images/card/background/card_id.extension
                $imageUrl = Storage::url("$path/$filename");

                // Update the business card
                $businessCard = BusinessCard::where('card_id', $request->card_id)->first();
                $customStyles = json_decode($businessCard->custom_styles, true) ?? [];
                $customStyles['image_url'] = $imageUrl;

                BusinessCard::where('card_id', $request->card_id)->update([
                    'custom_styles' => json_encode($customStyles),
                ]);

                return response()->json([
                    'status' => 'success',
                    'type' => $request->type,
                    'image_url' => $imageUrl,
                ]);
            } elseif ($request->type == 'button_bg_style') {
                $customStyles                           = json_decode(BusinessCard::where('card_id', $request->card_id)->first()->custom_styles, true);
                $customStyles['button_background_type'] = $request->button_bg_style;

                BusinessCard::where('card_id', $request->card_id)->update([
                    'custom_styles' => json_encode($customStyles),
                ]);
                return response()->json(['status' => 'success', 'type' => $request->type]);
            } elseif ($request->type == 'button_background_color') {
                $customStyles                            = json_decode(BusinessCard::where('card_id', $request->card_id)->first()->custom_styles, true);
                $customStyles['button_background_color'] = $request->button_background_color;

                BusinessCard::where('card_id', $request->card_id)->update([
                    'custom_styles' => json_encode($customStyles),
                ]);
                return response()->json(['status' => 'success', 'type' => $request->type]);
            } elseif ($request->type == 'button_gradient_background_color') {
                $customStyles                          = json_decode(BusinessCard::where('card_id', $request->card_id)->first()->custom_styles, true);
                $customStyles['button_gradient_start'] = $request->button_gradient_from_color;
                $customStyles['button_gradient_end']   = $request->button_gradient_to_color;

                BusinessCard::where('card_id', $request->card_id)->update([
                    'custom_styles' => json_encode($customStyles),
                ]);
                return response()->json(['status' => 'success', 'type' => $request->type]);
            } elseif ($request->type == 'button_edge') {
                $customStyles                = json_decode(BusinessCard::where('card_id', $request->card_id)->first()->custom_styles, true);
                $customStyles['button_edge'] = $request->button_edge;

                BusinessCard::where('card_id', $request->card_id)->update([
                    'custom_styles' => json_encode($customStyles),
                ]);
                return response()->json(['status' => 'success', 'type' => $request->type]);
            } elseif ($request->type == 'button_text_color') {
                $customStyles                      = json_decode(BusinessCard::where('card_id', $request->card_id)->first()->custom_styles, true);
                $customStyles['button_text_color'] = $request->button_text_color;

                BusinessCard::where('card_id', $request->card_id)->update([
                    'custom_styles' => json_encode($customStyles),
                ]);
                return response()->json(['status' => 'success', 'type' => $request->type]);
            } elseif ($request->type == 'button_icon_color') {
                $customStyles                      = json_decode(BusinessCard::where('card_id', $request->card_id)->first()->custom_styles, true);
                $customStyles['button_icon_color'] = $request->button_icon_color;

                BusinessCard::where('card_id', $request->card_id)->update([
                    'custom_styles' => json_encode($customStyles),
                ]);
                return response()->json(['status' => 'success', 'type' => $request->type]);
            } elseif ($request->type == 'heading_color') {
                $customStyles                  = json_decode(BusinessCard::where('card_id', $request->card_id)->first()->custom_styles, true);
                $customStyles['heading_color'] = $request->heading_color;

                BusinessCard::where('card_id', $request->card_id)->update([
                    'custom_styles' => json_encode($customStyles),
                ]);
                return response()->json(['status' => 'success', 'type' => $request->type]);
            } elseif ($request->type == 'card_edge') {
                $customStyles              = json_decode(BusinessCard::where('card_id', $request->card_id)->first()->custom_styles, true);
                $customStyles['card_edge'] = $request->card_edge;

                BusinessCard::where('card_id', $request->card_id)->update([
                    'custom_styles' => json_encode($customStyles),
                ]);
                return response()->json(['status' => 'success', 'type' => $request->type]);
            } elseif ($request->type == 'bottom_bar_color') {
                $customStyles                     = json_decode(BusinessCard::where('card_id', $request->card_id)->first()->custom_styles, true);
                $customStyles['bottom_bar_color'] = $request->bottom_bar_color;

                BusinessCard::where('card_id', $request->card_id)->update([
                    'custom_styles' => json_encode($customStyles),
                ]);
                return response()->json(['status' => 'success', 'type' => $request->type]);
            }
        } else {
            return response()->json(['status' => 'failed', 'message' => $validator->errors()->first(), 'type' => $request->type]);
        }
    }
}
