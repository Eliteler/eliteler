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
use App\BusinessField;
use App\BusinessHour;
use App\Category;
use App\ContactForm;
use App\Gallery;
use App\Http\Controllers\Controller;
use App\Payment;
use App\Service;
use App\Setting;
use App\StoreProduct;
use App\Theme;
use App\User;
use App\VcardProduct;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Yajra\DataTables\DataTables;

class CardController extends Controller
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

    // All user cards
    public function index(Request $request)
    {
        $config      = DB::table('config')->get();
        $user        = User::where('user_id', Auth::user()->user_id)->first();
        $active_plan = json_decode($user->plan_details);

        if ($active_plan != null) {
            if ($request->ajax()) {
                // Compiled visitor count subquery to prevent N+1 performance blocks
                $viewsSubquery = DB::table('visitors')
                    ->select(DB::raw('count(*)'))
                    ->whereColumn('visitors.card_id', 'business_cards.card_url');

                $businessCards = DB::table('business_cards')
                    ->join('users', 'business_cards.user_id', '=', 'users.user_id')
                    ->select('users.user_id', 'users.plan_validity', 'business_cards.*')
                    ->selectSub($viewsSubquery, 'views_count')
                    ->where('business_cards.user_id', Auth::user()->user_id)
                    ->where('business_cards.card_type', 'vcard')
                    ->where('business_cards.status', 1)
                    ->where('business_cards.card_status', '!=', 'deleted')
                    ->orderBy('business_cards.id', 'desc')
                    ->get();

                return DataTables::of($businessCards)
                    ->addIndexColumn()
                    ->editColumn('created_at', function ($card) {
                        return '<span class="text-muted small">' . formatDateForUser($card->created_at) . '</span>';
                    })
                    ->editColumn('type', function ($card) {
                        $type = strtolower($card->type ?? 'vcard');
                        if ($type === 'business') {
                            return '<span class="badge bg-primary-lt px-2 py-1 text-uppercase fw-semibold">' . __('Business') . '</span>';
                        } elseif ($type === 'personal') {
                            return '<span class="badge bg-azure-lt px-2 py-1 text-uppercase fw-semibold">' . __('Personal') . '</span>';
                        }
                        return '<span class="badge bg-secondary-lt px-2 py-1 text-uppercase fw-semibold">' . e(ucfirst($type)) . '</span>';
                    })
                    ->editColumn('title', function ($card) {
                        $profileImg = !empty($card->profile) ? asset($card->profile) : null;
                        $avatarHtml = $profileImg
                            ? '<span class="avatar avatar-sm rounded me-2 card-logo" style="background-image: url(' . $profileImg . ')"></span>'
                            : '<span class="avatar avatar-sm rounded bg-primary-lt me-2 card-logo"><svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-id" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><rect x="3" y="4" width="18" height="16" rx="3" /><circle cx="9" cy="10" r="2" /><line x1="15" y1="8" x2="17" y2="8" /><line x1="15" y1="12" x2="17" y2="12" /><line x1="7" y1="16" x2="17" y2="16" /></svg></span>';

                        // Truncate subtitle and add hover tooltip
                        $fullSubTitle  = $card->sub_title ?? '';
                        $shortSubTitle = Str::limit($fullSubTitle, 100, '...');

                        $subTitleHtml = '';
                        if (!empty($fullSubTitle)) {
                            $subTitleHtml = '<div class="text-muted small card-description" data-bs-toggle="tooltip" data-bs-placement="bottom" title="' . e($fullSubTitle) . '" style="cursor: pointer; max-width: 250px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">' . e($shortSubTitle) . '</div>';
                        }

                        return '<div class="d-flex align-items-center">
                            ' . $avatarHtml . '
                            <div style="min-width: 0;">
                                <div class="fw-bold text-truncate">
                                    <a href="' . route('user.edit.card', $card->card_id) . '" class="text-reset text-decoration-none">' . e($card->title) . '</a>
                                </div>
                                ' . $subTitleHtml . '
                            </div>
                        </div>';
                    })
                    ->editColumn('views', function ($card) {
                        return '<span class="badge bg-secondary-lt fw-semibold px-2 py-1 d-inline-flex align-items-center gap-1">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-eye" width="14" height="14" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><circle cx="12" cy="12" r="2" /><path d="M22 12c-2.667 4.667 -6 7 -10 7s-7.333 -2.333 -10 -7c2.667 -4.667 6 -7 10 -7s7.333 2.333 10 7" /></svg>
                            ' . number_format($card->views_count) . '
                        </span>';
                    })
                    ->editColumn('card_url', function ($card) use ($config) {
                        if ($card->custom_domain == null) {
                            if ($config[46]->config_value == '1') {
                                $url = route('subdomain.profile', $card->card_url);
                            } else {
                                $url = route('profile', $card->card_url);
                            }
                        } else {
                            $url = 'https://www.' . $card->custom_domain;
                        }
                        return '<a href="' . $url . '" target="_blank" class="text-primary">' . $card->card_url . '</a>';
                    })
                    ->editColumn('card_status', function ($card) {
                        return $card->card_status == 'inactive'
                            ? '<span class="badge bg-danger-lt px-2 py-1">' . __('Disabled') . '</span>'
                            : '<span class="badge bg-success-lt px-2 py-1">' . __('Enabled') . '</span>';
                    })
                    ->addColumn('action', function ($card) use ($config, $active_plan) {
                        $profileUrl = ($card->custom_domain == null)
                            ? (($config[46]->config_value == '1') ? route('subdomain.profile', $card->card_url) : route('profile', $card->card_url))
                            : 'https://' . $card->custom_domain;

                        $actionBtn = '';

                        // Preview
                        $actionBtn .= '<a class="dropdown-item" href="' . route('user.view.preview', $card->card_id) . '" target="_blank">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon dropdown-item-icon text-muted" width="18" height="18" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><circle cx="12" cy="12" r="2" /><path d="M22 12c-2.667 4.667 -6 7 -10 7s-7.333 -2.333 -10 -7c2.667 -4.667 6 -7 10 -7s7.333 2.333 10 7" /></svg>
                        ' . __('Preview') . '
                    </a>';

                        // Live Card
                        $actionBtn .= '<a class="dropdown-item" href="' . $profileUrl . '" target="_blank">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon dropdown-item-icon text-muted" width="18" height="18" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M19.5 7a9 9 0 0 0 -7.5 -4a8.991 8.991 0 0 0 -7.484 4"/><path d="M11.5 3a16.989 16.989 0 0 0 -1.826 4"/><path d="M12.5 3a16.989 16.989 0 0 1 1.828 4"/><path d="M19.5 17a9 9 0 0 1 -7.5 4a8.991 8.991 0 0 1 -7.484 -4"/><path d="M11.5 21a16.989 16.989 0 0 1 -1.826 -4"/><path d="M12.5 21a16.989 16.989 0 0 0 1.828 -4"/><path d="M2 10l1 4l1.5 -4l1.5 4l1 -4"/><path d="M17 10l1 4l1.5 -4l1.5 4l1 -4"/><path d="M9.5 10l1 4l1.5 -4l1.5 4l1 -4"/></svg>
                        ' . __('Live') . '
                    </a>';

                        // Custom Domain
                        $actionBtn .= '<a class="dropdown-item" href="' . route('user.connect.domain', $card->card_id) . '">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon dropdown-item-icon text-muted" width="18" height="18" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 14a3.5 3.5 0 0 0 5 0l4 -4a3.5 3.5 0 0 0 -5 -5l-.5 .5"/><path d="M14 10a3.5 3.5 0 0 0 -5 0l-4 4a3.5 3.5 0 0 0 5 5l.5 -.5"/></svg>
                        ' . __('Custom Domain') . '
                    </a>';

                        // QR Code
                        $actionBtn .= '<a class="dropdown-item" onclick="updateQr(`' . $profileUrl . '`, `' . e(addslashes($card->title)) . '`); return false;">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon dropdown-item-icon text-muted" width="18" height="18" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 4m0 1a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v4a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1z"/><path d="M7 17l0 .01"/><path d="M14 4m0 1a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v4a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1z"/><path d="M7 7l0 .01"/><path d="M4 14m0 1a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v4a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1z"/><path d="M17 7l0 .01"/><path d="M14 14l3 0"/><path d="M20 14l0 .01"/><path d="M14 14l0 3"/><path d="M14 20l3 0"/><path d="M17 17l3 0"/><path d="M20 17l0 3"/></svg>
                        ' . __('QR Code') . '
                    </a>';

                        // Visitors / Analytics
                        $actionBtn .= '<a class="dropdown-item" href="' . route('user.visitors', $card->card_url) . '">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon dropdown-item-icon text-muted" width="18" height="18" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 13a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v6a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1z"/><path d="M15 9a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v10a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1z"/><path d="M9 5a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v14a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1z"/><path d="M4 20h14"/></svg>
                        ' . __('Analytics') . '
                    </a>';

                        // Newsletters
                        if (!in_array($card->theme_id, range(588969111014, 588969111021))) {
                            $actionBtn .= '<a class="dropdown-item" href="' . route('user.newsletter', $card->card_id) . '">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon dropdown-item-icon text-muted" width="18" height="18" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 9l9 6l9 -6l-9 -6l-9 6"/><path d="M21 9v10a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2v-10"/><path d="M3 19l6 -6"/><path d="M15 13l6 6"/></svg>
                            ' . __('Newsletters') . '
                        </a>';
                        }

                        // Enquiries
                        $actionBtn .= '<a class="dropdown-item" href="' . route('user.enquiries', $card->card_id) . '">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon dropdown-item-icon text-muted" width="18" height="18" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 7a2 2 0 0 1 2 -2h14a2 2 0 0 1 2 2v10a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2v-10z"/><path d="M3 7l9 6l9 -6"/></svg>
                        ' . __('Enquiries') . '
                    </a>';

                        // Contacts
                        $actionBtn .= '<a class="dropdown-item" href="' . route('user.contacts', $card->card_id) . '">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon dropdown-item-icon text-muted" width="18" height="18" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M20 6v12a2 2 0 0 1 -2 2h-10a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2h10a2 2 0 0 1 2 2z"/><path d="M10 16h6"/><path d="M13 11m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"/><path d="M4 8h3"/><path d="M4 12h3"/><path d="M4 16h3"/></svg>
                        ' . __('Contacts') . '
                    </a>';

                        // Appointments
                        if (isset($active_plan->appointment) && $active_plan->appointment == 1) {
                            $actionBtn .= '<a class="dropdown-item" href="' . route('user.appointments', $card->card_id) . '">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon dropdown-item-icon text-muted" width="18" height="18" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10.5 21h-4.5a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v3"/><path d="M16 3v4"/><path d="M8 3v4"/><path d="M4 11h10"/><path d="M18 18m-4 0a4 4 0 1 0 8 0a4 4 0 1 0 -8 0"/><path d="M18 16.5v1.5l.5 .5"/></svg>
                            ' . __('Appointments') . '
                        </a>';
                        }

                        // Service Bookings
                        if (!in_array($card->theme_id, range(588969111014, 588969111021))) {
                            if (isset($active_plan->service_booking) && $active_plan->service_booking == 1) {
                                $actionBtn .= '<a class="dropdown-item" href="' . route('user.all.service.booking', $card->card_id) . '">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon dropdown-item-icon text-muted" width="18" height="18" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10.5 21h-4.5a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v3"/><path d="M16 3v4"/><path d="M8 3v4"/><path d="M4 11h10"/><path d="M18 18m-4 0a4 4 0 1 0 8 0a4 4 0 1 0 -8 0"/><path d="M18 16.5v1.5l.5 .5"/></svg>
                                ' . __('Service Bookings') . '
                            </a>';
                            }
                        }

                        // Edit Card
                        $actionBtn .= '<a class="dropdown-item border-top" href="' . route('user.edit.card', $card->card_id) . '">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon dropdown-item-icon text-muted" width="18" height="18" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 20h4l10.5 -10.5a2.828 2.828 0 1 0 -4 -4l-10.5 10.5v4"/><path d="M13.5 6.5l4 4"/></svg>
                        ' . __('Edit Card') . '
                    </a>';

                        // Duplicate
                        $actionBtn .= '<a class="dropdown-item" onclick="duplicateCard(`' . $card->card_id . '`, `vcard`); return false;">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon dropdown-item-icon text-muted" width="18" height="18" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M7 9.667a2.667 2.667 0 0 1 2.667 -2.667h8.666a2.667 2.667 0 0 1 2.667 2.667v8.666a2.667 2.667 0 0 1 -2.667 2.667h-8.666a2.667 2.667 0 0 1 -2.667 -2.667z"/><path d="M4.012 16.737a2 2 0 0 1 -1.012 -1.737v-10c0 -1.1 .9 -2 2 -2h10c.75 0 1.158 .385 1.5 1"/><path d="M11 14h6"/><path d="M14 11v6"/></svg>
                        ' . __('Duplicate') . '
                    </a>';

                        // Status Toggle
                        $actionBtn .= $card->card_status == 'activated'
                            ? '<a class="dropdown-item text-primary" onclick="toggleCardStatus(`' . $card->card_id . '`); return false;"><svg xmlns="http://www.w3.org/2000/svg" class="icon dropdown-item-icon text-primary" width="18" height="18" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 12h-6a3 3 0 0 1 -3 -3v-2c0 -1.083 .574 -2.033 1.435 -2.56m3.565 -.44h10a3 3 0 0 1 3 3v2a3 3 0 0 1 -3 3h-2"/><path d="M16 12h2a3 3 0 0 1 3 3v2m-1.448 2.568a2.986 2.986 0 0 1 -1.552 .432h-12a3 3 0 0 1 -3 -3v-2a3 3 0 0 1 3 -3h6"/><path d="M7 8v.01"/><path d="M7 16v.01"/><path d="M3 3l18 18"/></svg>' . __('Disable') . '</a>'
                            : '<a class="dropdown-item text-success" onclick="toggleCardStatus(`' . $card->card_id . '`); return false;"><svg xmlns="http://www.w3.org/2000/svg" class="icon dropdown-item-icon text-success" width="18" height="18" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 4m0 3a3 3 0 0 1 3 -3h12a3 3 0 0 1 3 3v2a3 3 0 0 1 -3 3h-12a3 3 0 0 1 -3 -3z"/><path d="M3 12m0 3a3 3 0 0 1 3 -3h12a3 3 0 0 1 3 3v2a3 3 0 0 1 -3 3h-12a3 3 0 0 1 -3 -3z"/><path d="M7 8l0 .01"/><path d="M7 16l0 .01"/></svg>' . __('Enable') . '</a>';

                        // Delete
                        $actionBtn .= '<a class="dropdown-item text-danger border-top" onclick="deleteCard(`' . $card->card_id . '`, `delete`); return false;">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon dropdown-item-icon text-danger" width="18" height="18" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 7l16 0"/><path d="M10 11l0 6"/><path d="M14 11l0 6"/><path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12"/><path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3"/></svg>
                        ' . __('Delete') . '
                    </a>';

                        return '
                    <a class="btn-action text-decoration-none" href="#" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icon-tabler-dots">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                            <circle cx="5" cy="12" r="1" />
                            <circle cx="12" cy="12" r="1" />
                            <circle cx="19" cy="12" r="1" />
                        </svg>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end">
                        <div class="nav-item dropdown">
                            ' . $actionBtn . '
                        </div>
                    </div>';
                    })
                    ->rawColumns(['created_at', 'type', 'title', 'views', 'card_url', 'card_status', 'action'])
                    ->make(true);
            }

            $config   = DB::table('config')->get();
            $settings = Setting::where('status', 1)->first();

            return view('user.pages.cards.cards', compact('settings', 'config'));
        } else {
            return redirect()->route('user.plans');
        }
    }

    // Choose a card type
    public function chooseCardType()
    {
        // Queries
        $settings = Setting::where('status', 1)->first();

        $cards = BusinessCard::where('user_id', Auth::user()->user_id)->where('card_type', 'vcard')->where('card_status', 'activated')->where('status', '1')->count();

        // Active plan details in user
        $user         = Auth::user();
        $plan_details = json_decode($user->plan_details);

        // Check validity
        $validity = User::where('user_id', Auth::user()->user_id)->where('status', 1)->where('plan_validity', '>=', Carbon::now())->count();

        // check new card types
        if (Schema::hasTable('business_card_types')) {
            $new_business_card_types = DB::table('business_card_types')->where('status', 1)->select('business_card_type', 'business_card_type_name', 'business_card_type_image')->get();
        } else {
            $new_business_card_types = null;
        }

        // Check unlimited cards
        if ($plan_details->no_of_vcards == 999) {
            $no_cards = 999999;
        } else {
            $no_cards = $plan_details->no_of_vcards;
        }

        // check ai generator boolean 
        $is_ai_generator_enabled = 0;

        // ai builder settings
        $ai_builder_settings = DB::table('aibuilder_settings')->first();

        // check ai builder settings
        if (empty($ai_builder_settings)) {
            $is_ai_generator_enabled = 0;
        } else {
            // check ai builder enabled
            $is_ai_generator_enabled = $ai_builder_settings->aibuilder;
        }

        // Check vcard creation limit
        if ($validity == 1) {
            if ($cards < $no_cards) {
                return view('user.pages.cards.choose-a-card', compact('settings', 'plan_details', 'is_ai_generator_enabled', 'new_business_card_types'));
            } else {
                return redirect()->route('user.cards')->with('failed', trans('The maximum limit has been exceeded. Please upgrade your plan.'));
            }
        } else {
            // Redirect
            return redirect()->route('user.cards')->with('failed', trans('Your plan is over. Choose your plan renewal or new package and use it.'));
        }
    }

    // Skip business hours
    public function skipAndSave()
    {
        // Redirect
        return redirect()->route('user.cards')->with('success', trans('Your virtual business card is updated!'));
    }

    // Card Status Page
    public function cardStatus(Request $request, $id)
    {
        // Queries
        $businessCard = BusinessCard::where('card_id', $id)->first();

        // Check business card
        if ($businessCard == null) {
            return redirect()->route('user.cards')->with('failed', trans('Card not found!'));
        } else {

            // Queries
            $business_card = BusinessCard::where('user_id', Auth::user()->user_id)->where('card_id', $id)->first();

            // Check business card
            if ($business_card == null) {
                return redirect()->route('user.cards')->with('failed', trans('Card not found!'));
            } else {
                // Check active cards
                if ($business_card->card_status == 'inactive') {

                    // Queries
                    $plan        = User::where('user_id', Auth::user()->user_id)->first();
                    $active_plan = json_decode($plan->plan_details);

                    // vCard
                    if ($business_card->card_type == "vcard") {
                        // vCard
                        $no_of_services       = Service::where('card_id', $id)->count();
                        $no_of_vcard_products = VcardProduct::where('card_id', $id)->count();
                        $no_of_links          = BusinessField::where('card_id', $id)->count();
                        $no_of_payments       = Payment::where('card_id', $id)->count();
                        $no_of_galleries      = Gallery::where('card_id', $id)->count();
                        $business_hours       = BusinessHour::where('card_id', $id)->count();
                        $contact_form         = ContactForm::where('card_id', $id)->count();

                        // Check vcard / store limitation
                        if ($no_of_services <= $active_plan->no_of_services && $no_of_vcard_products <= $active_plan->no_of_vcard_products && $no_of_galleries <= $active_plan->no_of_galleries && $no_of_links <= $active_plan->no_of_links && $no_of_payments <= $active_plan->no_of_payments) {

                            // Queries (vCards)
                            $cards = BusinessCard::where('user_id', Auth::user()->user_id)->where('card_type', 'vcard')->where('card_status', 'activated')->where('status', '1')->count();

                            // Get plan details in user
                            $plan         = DB::table('users')->where('user_id', Auth::user()->user_id)->where('status', 1)->first();
                            $plan_details = json_decode($plan->plan_details);

                            // Number of vcards limitation
                            if ($cards < $plan_details->no_of_vcards) {

                                // Update (vCard)
                                BusinessCard::where('user_id', Auth::user()->user_id)->where('card_type', 'vcard')->where('card_id', $id)->update([
                                    'card_status' => 'activated',
                                ]);

                                return redirect()->route('user.cards')->with('success', trans('Your vcard activated.'));
                            } else {

                                return redirect()->route('user.cards')->with('failed', trans('The maximum limit has been exceeded. Please upgrade your plan.'));
                            }
                        } else {
                            // Queries
                            $cards = BusinessCard::where('user_id', Auth::user()->user_id)->where('card_type', 'vcard')->where('card_status', 'activated')->where('status', '1')->count();

                            // Get plan details in user
                            $plan         = DB::table('users')->where('user_id', Auth::user()->user_id)->where('status', 1)->first();
                            $plan_details = json_decode($plan->plan_details);

                            // Number of vcards limitation
                            if ($cards < $plan_details->no_of_vcards) {
                                return redirect()->route('user.edit.card', $id)->with('failed', trans('You have downgraded your plan. Please re-configure this vcard as per your current plan features.'));
                            } else {
                                return redirect()->route('user.cards')->with('failed', trans('The maximum limit has been exceeded. Please upgrade your plan.'));
                            }
                        }
                    }

                    // Store
                    if ($business_card->card_type == "store") {
                        // Store
                        $no_of_categories     = Category::where('user_id', auth::user()->user_id)->count();
                        $no_of_store_products = StoreProduct::where('card_id', $id)->count();

                        // Check vcard / store limitation
                        if ($no_of_categories <= $active_plan->no_of_categories && $no_of_store_products <= $active_plan->no_of_store_products) {

                            // Queries (Stores)
                            $stores = BusinessCard::where('user_id', Auth::user()->user_id)->where('card_type', 'store')->where('card_status', 'activated')->where('status', '1')->count();

                            // Get plan details in user
                            $plan         = DB::table('users')->where('user_id', Auth::user()->user_id)->where('status', 1)->first();
                            $plan_details = json_decode($plan->plan_details);

                            // Number of stores limitation
                            if ($stores < $plan_details->no_of_stores) {

                                // Update (Stores)
                                BusinessCard::where('user_id', Auth::user()->user_id)->where('card_type', 'store')->where('card_id', $id)->update([
                                    'card_status' => 'activated',
                                ]);

                                return redirect()->route('user.stores')->with('success', trans('Your store link was activated.'));
                            } else {
                                return redirect()->route('user.stores')->with('failed', trans('The maximum limit has been exceeded. Please upgrade your plan.'));
                            }
                        } else {

                            // Queries (Stores)
                            $stores = BusinessCard::where('user_id', Auth::user()->user_id)->where('card_type', 'store')->where('card_status', 'activated')->where('status', '1')->count();

                            // Get plan details in user
                            $plan         = DB::table('users')->where('user_id', Auth::user()->user_id)->where('status', 1)->first();
                            $plan_details = json_decode($plan->plan_details);

                            // Number of stores limitation
                            if ($stores < $plan_details->no_of_stores) {
                                return redirect()->route('user.edit.store', $id)->with('failed', trans('You have downgraded your plan. Please re-configure this vcard as per your current plan features.'));
                            } else {
                                return redirect()->route('user.stores')->with('failed', trans('The maximum limit has been exceeded. Please upgrade your plan.'));
                            }
                        }
                    }
                } else {
                    // Update
                    BusinessCard::where('user_id', Auth::user()->user_id)->where('card_id', $id)->update([
                        'card_status' => 'inactive',
                    ]);

                    return redirect()->back()->with('success', trans('Deactivated'));
                }
            }
        }
    }

    // Delete card
    public function deleteCard(Request $request)
    {
        // Check custom domain
        BusinessCard::where('user_id', Auth::user()->user_id)->where('card_id', $request->query('id'))->first();
        $businessCard = BusinessCard::where('user_id', Auth::user()->user_id)->where('card_id', $request->query('id'))->first();

        if ($businessCard->custom_domain == null) {
            // Delete
            BusinessCard::where('user_id', Auth::user()->user_id)->where('card_id', $request->query('id'))->update([
                'card_status' => 'deleted',
                'status' => '0'
            ]);

            return redirect()->route('user.cards')->with('success', trans('Deleted!'));
        } else {
            return redirect()->route('user.cards')->with('failed', trans('This card cannot be deleted. Please unlink your custom domain before proceeding.'));
        }
    }

    // Search by theme
    public function searchTheme(Request $request)
    {
        $query = $request->get('query');
        $type  = $request->get('type');
        $cardType  = $request->get('cardType');

        if ($cardType == "business") {
            $cards = Theme::where('theme_name', 'LIKE', '%' . $query . '%')->whereNotIn('theme_id', ["588969111014", "588969111015", "588969111016", "588969111017", "588969111018", "588969111019", "588969111020", "588969111021", "588969111147"])->where('theme_description', $type)->where('status', 1)->orderBy('id', 'desc')->get();
        } else {
            $cards = Theme::where('theme_name', 'LIKE', '%' . $query . '%')->whereIn('theme_id', ["588969111014", "588969111015", "588969111016", "588969111017", "588969111018", "588969111019", "588969111020", "588969111021", "588969111147"])->where('theme_description', $type)->where('status', 1)->orderBy('id', 'desc')->get();
        }

        // Translate theme names before returning the response
        $cards = $cards->map(function ($theme) {
            $theme->theme_name = __($theme->theme_name);
            return $theme;
        });

        return response()->json($cards);
    }
}
