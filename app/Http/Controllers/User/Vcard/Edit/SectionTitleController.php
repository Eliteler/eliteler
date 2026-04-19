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

use App\User;
use App\Setting;
use App\BusinessCard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class SectionTitleController extends Controller
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

    // Edit section title
    public function editSectionTitle(Request $request, $id)
    {
        // Get plan & card info
        $plan = User::where('user_id', Auth::user()->user_id)->where('status', 1)->first();
        $plan_details = json_decode($plan->plan_details);
        $business_card = BusinessCard::where('card_id', $id)->first();
        $settings      = Setting::where('status', 1)->first();

        if (!$business_card) {
            return redirect()->route('user.cards')->with('failed', trans('Card not found!'));
        }

        $cardId = $id;

        // Define all sections without numeric keys
        $sections = [
            ['table' => 'business_fields',       'column' => 'title',              'label' => trans('Social Links')],
            ['table' => 'payments',               'column' => 'title',              'label' => trans('Payments')],
            ['table' => 'services',               'column' => 'title',              'label' => trans('Services')],
            ['table' => 'vcard_products',         'column' => 'title',              'label' => trans('Products')],
            ['table' => 'galleries',              'column' => 'title',              'label' => trans('Galleries')],
            ['table' => 'testimonials',           'column' => 'title',              'label' => trans('Testimonials')],
            ['table' => 'business_hours',         'column' => 'title',              'label' => trans('Business Hours')],
            ['table' => 'card_appointment_times', 'column' => 'title',              'label' => trans('Appointments')],
            ['table' => 'business_cards',         'column' => 'contact_form_title', 'label' => trans('Contact Form')],
            ['table' => 'service_bookings',       'column' => 'title',              'label' => trans('Service Booking')],
        ];

        $sectionTitles = collect();

        foreach ($sections as $index => $section) {
            $table        = $section['table'];
            $column       = $section['column'];
            $cardIdColumn = ($table === 'service_bookings') ? 'vcard_id' : 'card_id';
            $arColumn     = ($table === 'business_cards') ? 'contact_form_title_ar' : 'title_ar';

            // Fetch rows
            $rows = DB::table($table)
                ->where($cardIdColumn, $cardId)
                ->select(
                    DB::raw("COALESCE($column, '') as title"),
                    DB::raw("COALESCE($arColumn, '') as title_ar")
                )
                ->get();

            // Use first title or fallback to label
            $title    = $rows->first()->title    ?? $section['label'];
            $title_ar = $rows->first()->title_ar ?? '';

            $sectionTitles->push((object)[
                'id'       => $index + 1,
                'title'    => trim($title) !== '' ? trim($title) : $section['label'],
                'title_ar' => trim($title_ar),
                'label'    => $section['label'],
            ]);
        }

        return view('user.pages.edit-cards.edit-section-title', compact('sectionTitles', 'plan_details', 'business_card', 'settings'));
    }

    // Update section title
    public function updateSectionTitle(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'titles'       => 'required|array',
            'titles.*'     => 'required|string|max:255',
            'titles_ar'    => 'nullable|array',
            'titles_ar.*'  => 'nullable|string|max:255',
        ], [
            'titles.required'    => trans('Section title required'),
            'titles.*.required'  => trans('Section title required'),
        ]);

        if ($validator->fails()) {
            return back()
                ->with('failed', $validator->messages()->all()[0])
                ->withInput();
        }

        $plan         = DB::table('users')->where('user_id', Auth::user()->user_id)->where('status', 1)->first();
        $plan_details = json_decode($plan->plan_details);
        $titles_ar    = $request->titles_ar ?? [];

        foreach ($request->titles as $key => $value) {
            $value    = trim($value);
            $value_ar = isset($titles_ar[$key]) ? (trim($titles_ar[$key]) ?: null) : null;

            switch ($key) {
                case 1:
                    DB::table('business_fields')->where('card_id', $id)->update(['title' => $value, 'title_ar' => $value_ar]);
                    break;
                case 2:
                    DB::table('payments')->where('card_id', $id)->update(['title' => $value, 'title_ar' => $value_ar]);
                    break;
                case 3:
                    DB::table('services')->where('card_id', $id)->update(['title' => $value, 'title_ar' => $value_ar]);
                    break;
                case 4:
                    DB::table('vcard_products')->where('card_id', $id)->update(['title' => $value, 'title_ar' => $value_ar]);
                    break;
                case 5:
                    DB::table('galleries')->where('card_id', $id)->update(['title' => $value, 'title_ar' => $value_ar]);
                    break;
                case 6:
                    DB::table('testimonials')->where('card_id', $id)->update(['title' => $value, 'title_ar' => $value_ar]);
                    break;
                case 7:
                    DB::table('business_hours')->where('card_id', $id)->update(['title' => $value, 'title_ar' => $value_ar]);
                    break;
                case 8:
                    DB::table('card_appointment_times')->where('card_id', $id)->update(['title' => $value, 'title_ar' => $value_ar]);
                    break;
                case 9:
                    DB::table('business_cards')->where('card_id', $id)->update(['contact_form_title' => $value, 'contact_form_title_ar' => $value_ar]);
                    break;
                case 10:
                    DB::table('service_bookings')->where('vcard_id', $id)->update(['title' => $value, 'title_ar' => $value_ar]);
                    break;
            }
        }

        if (isset($plan_details->google_wallet) && $plan_details->google_wallet == 1 && is_dir(base_path('plugins/GoogleWallet'))) {
            return redirect()->route('user.edit.google-wallet', $id);
        } else {
            return redirect()->route('user.edit.intro-screen', $id);
        }
    }
}
