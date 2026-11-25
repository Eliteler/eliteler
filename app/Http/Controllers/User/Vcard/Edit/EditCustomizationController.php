<?php

namespace App\Http\Controllers\User\Vcard\Edit;

use App\BusinessCard;
use App\Helpers\StorageHelper;
use App\Http\Controllers\Controller;
use App\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
        $business_card = BusinessCard::where('user_id', Auth::user()->user_id)->where('card_id', $id)->first();
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
            $card = BusinessCard::where('user_id', Auth::user()->user_id)->where('card_id', $request->card_id)->first();
            if (!$card) {
                return response()->json(['status' => 'failed', 'message' => trans('Card not found!')], 403);
            }

            $customStyles = !empty($card->custom_styles) ? (json_decode($card->custom_styles, true) ?: []) : [];

            if ($request->type == 'title_color') {
                $customStyles['title_color'] = $request->title_color;

                $card->update([
                    'custom_styles' => json_encode($customStyles),
                ]);
                return response()->json(['status' => 'success', 'type' => $request->type]);
            } elseif ($request->type == 'sub_title_color') {
                $customStyles['sub_title_color'] = $request->sub_title_color;

                $card->update([
                    'custom_styles' => json_encode($customStyles),
                ]);
                return response()->json(['status' => 'success', 'type' => $request->type]);
            } elseif ($request->type == 'description_color') {
                $customStyles['description_color'] = $request->description_color;

                $card->update([
                    'custom_styles' => json_encode($customStyles),
                ]);
                return response()->json(['status' => 'success', 'type' => $request->type]);
            } elseif ($request->type == 'layout') {
                $customStyles['layout'] = $request->layout;

                $card->update([
                    'custom_styles' => json_encode($customStyles),
                ]);
                return response()->json(['status' => 'success', 'type' => $request->type]);
            } elseif ($request->type == 'profile_image_style') {
                $customStyles['profile_image_style'] = $request->profile_image_style;

                $card->update([
                    'custom_styles' => json_encode($customStyles),
                ]);
                return response()->json(['status' => 'success', 'type' => $request->type]);
            } elseif ($request->type == 'font') {
                $customStyles['font_family'] = $request->font;

                $card->update([
                    'custom_styles' => json_encode($customStyles),
                ]);
                return response()->json(['status' => 'success', 'type' => $request->type]);
            } elseif ($request->type == 'bg_style') {
                $customStyles['background_type'] = $request->bg_style;

                $card->update([
                    'custom_styles' => json_encode($customStyles),
                ]);
                return response()->json(['status' => 'success', 'type' => $request->type]);
            } elseif ($request->type == 'background_color') {
                $customStyles['background_color'] = $request->background_color;

                $card->update([
                    'custom_styles' => json_encode($customStyles),
                ]);
                return response()->json(['status' => 'success', 'type' => $request->type]);
            } elseif ($request->type == 'gradient_background_color') {
                $customStyles['gradient_start'] = $request->gradient_from_color;
                $customStyles['gradient_end']   = $request->gradient_end_color;

                $card->update([
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

                // Upload card background image with custom name (card_id) and get relative path
                $imageUrl = StorageHelper::uploadImage(
                    $request->file('background_image'),
                    'images/card/background',
                    $request->card_id
                );

                // Update the business card custom styles
                $customStyles['image_url'] = $imageUrl;

                $card->update([
                    'custom_styles' => json_encode($customStyles),
                ]);

                return response()->json([
                    'status' => 'success',
                    'type' => $request->type,
                    'image_url' => $imageUrl,
                ]);
            } elseif ($request->type == 'button_bg_style') {
                $customStyles['button_background_type'] = $request->button_bg_style;

                $card->update([
                    'custom_styles' => json_encode($customStyles),
                ]);
                return response()->json(['status' => 'success', 'type' => $request->type]);
            } elseif ($request->type == 'button_background_color') {
                $customStyles['button_background_color'] = $request->button_background_color;

                $card->update([
                    'custom_styles' => json_encode($customStyles),
                ]);
                return response()->json(['status' => 'success', 'type' => $request->type]);
            } elseif ($request->type == 'button_gradient_background_color') {
                $customStyles['button_gradient_start'] = $request->button_gradient_from_color;
                $customStyles['button_gradient_end']   = $request->button_gradient_to_color;

                $card->update([
                    'custom_styles' => json_encode($customStyles),
                ]);
                return response()->json(['status' => 'success', 'type' => $request->type]);
            } elseif ($request->type == 'button_edge') {
                $customStyles['button_edge'] = $request->button_edge;

                $card->update([
                    'custom_styles' => json_encode($customStyles),
                ]);
                return response()->json(['status' => 'success', 'type' => $request->type]);
            } elseif ($request->type == 'button_text_color') {
                $customStyles['button_text_color'] = $request->button_text_color;

                $card->update([
                    'custom_styles' => json_encode($customStyles),
                ]);
                return response()->json(['status' => 'success', 'type' => $request->type]);
            } elseif ($request->type == 'button_icon_color') {
                $customStyles['button_icon_color'] = $request->button_icon_color;

                $card->update([
                    'custom_styles' => json_encode($customStyles),
                ]);
                return response()->json(['status' => 'success', 'type' => $request->type]);
            } elseif ($request->type == 'heading_color') {
                $customStyles['heading_color'] = $request->heading_color;

                $card->update([
                    'custom_styles' => json_encode($customStyles),
                ]);
                return response()->json(['status' => 'success', 'type' => $request->type]);
            } elseif ($request->type == 'card_edge') {
                $customStyles['card_edge'] = $request->card_edge;

                $card->update([
                    'custom_styles' => json_encode($customStyles),
                ]);
                return response()->json(['status' => 'success', 'type' => $request->type]);
            } elseif ($request->type == 'bottom_bar_color') {
                $customStyles['bottom_bar_color'] = $request->bottom_bar_color;

                $card->update([
                    'custom_styles' => json_encode($customStyles),
                ]);
                return response()->json(['status' => 'success', 'type' => $request->type]);
            }
        } else {
            return response()->json(['status' => 'failed', 'message' => $validator->errors()->first(), 'type' => $request->type]);
        }
    }
}
