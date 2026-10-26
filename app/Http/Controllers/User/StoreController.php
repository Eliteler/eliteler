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

namespace App\Http\Controllers\User;

use App\BusinessCard;
use App\Http\Controllers\Controller;
use App\Setting;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Yajra\DataTables\DataTables;

class StoreController extends Controller
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
     * Display a listing of storefront stores.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $config      = DB::table('config')->get();
        $user        = User::where('user_id', Auth::user()->user_id)->first();
        $active_plan = json_decode($user->plan_details);

        if ($active_plan != null) {
            if ($request->ajax()) {
                // Compiled visitor count subquery to prevent N+1 queries
                $viewsSubquery = DB::table('visitors')
                    ->select(DB::raw('count(*)'))
                    ->whereColumn('visitors.card_id', 'business_cards.card_url');

                $business_cards = DB::table('business_cards')
                    ->join('users', 'business_cards.user_id', '=', 'users.user_id')
                    ->select('users.user_id', 'users.plan_validity', 'business_cards.*')
                    ->selectSub($viewsSubquery, 'views_count')
                    ->where('business_cards.user_id', Auth::user()->user_id)
                    ->where('business_cards.card_type', 'store')
                    ->where('business_cards.status', '1')
                    ->where('business_cards.card_status', '!=', 'deleted')
                    ->orderBy('business_cards.id', 'desc')
                    ->get();

                return DataTables::of($business_cards)
                    ->addIndexColumn()
                    ->editColumn('created_at', function ($card) {
                        return '<span class="text-muted small">' . formatDateForUser($card->created_at) . '</span>';
                    })
                    ->editColumn('title', function ($card) {
                        $logo = !empty($card->profile) ? asset($card->profile) : null;
                        $avatarHtml = $logo
                            ? '<span class="avatar avatar-sm rounded me-2 store-logo" style="background-image: url(' . $logo . ')"></span>'
                            : '<span class="avatar avatar-sm rounded bg-primary-lt me-2 store-logo"><svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-building-store" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 21l18 0"/><path d="M3 7v1a3 3 0 0 0 6 0v-1m0 1a3 3 0 0 0 6 0v-1m0 1a3 3 0 0 0 6 0v-1h-18l2 -4h14l2 4"/><path d="M5 21l0 -10.15"/><path d="M19 21l0 -10.15"/><path d="M9 21v-4a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v4"/></svg></span>';

                        // Truncate subtitle and add hover tooltip for full description
                        $fullSubTitle  = $card->sub_title ?? '';
                        $shortSubTitle = Str::limit($fullSubTitle, 100, '...');

                        $subTitleHtml = '';
                        if (!empty($fullSubTitle)) {
                            $subTitleHtml = '<div class="text-muted small store-description" data-bs-toggle="tooltip" data-bs-placement="bottom" title="' . e($fullSubTitle) . '" style="cursor: pointer; max-width: auto; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">' . e($shortSubTitle) . '</div>';
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
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-eye" width="14" height="14" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0"/><path d="M21 12c-2.4 4 -5.4 6 -9 6c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c3.6 0 6.6 2 9 6"/></svg>
                                ' . number_format($card->views_count) . '
                            </span>';
                    })
                    ->editColumn('card_status', function ($card) {
                        return $card->card_status == 'inactive'
                            ? '<span class="badge bg-danger-lt px-2 py-1">' . __('Disabled') . '</span>'
                            : '<span class="badge bg-success-lt px-2 py-1">' . __('Enabled') . '</span>';
                    })
                    ->addColumn('action', function ($card) use ($config) {
                        $profileUrl = ($card->custom_domain == null)
                            ? (($config[46]->config_value == '1') ? route('subdomain.profile', $card->card_url) : route('profile', $card->card_url))
                            : 'https://' . $card->custom_domain;

                        return '
                        <a class="btn-action text-decoration-none" href="#" data-toggle="dropdown" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icon-tabler-dots">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                <circle cx="5" cy="12" r="1" />
                                <circle cx="12" cy="12" r="1" />
                                <circle cx="19" cy="12" r="1" />
                            </svg>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end">
                            <div class="nav-item dropdown">
                                <a class="dropdown-item" href="' . route('user.view.preview', $card->card_id) . '" target="_blank">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon dropdown-item-icon text-muted" width="18" height="18" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0"/><path d="M21 12c-2.4 4 -5.4 6 -9 6c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c3.6 0 6.6 2 9 6"/></svg>
                                    ' . __('Preview') . '
                                </a>
                                <a class="dropdown-item" href="' . $profileUrl . '" target="_blank">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon dropdown-item-icon text-muted" width="18" height="18" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M19.5 7a9 9 0 0 0 -7.5 -4a8.991 8.991 0 0 0 -7.484 4"/><path d="M11.5 3a16.989 16.989 0 0 0 -1.826 4"/><path d="M12.5 3a16.989 16.989 0 0 1 1.828 4"/><path d="M19.5 17a9 9 0 0 1 -7.5 4a8.991 8.991 0 0 1 -7.484 -4"/><path d="M11.5 21a16.989 16.989 0 0 1 -1.826 -4"/><path d="M12.5 21a16.989 16.989 0 0 0 1.828 -4"/><path d="M2 10l1 4l1.5 -4l1.5 4l1 -4"/><path d="M17 10l1 4l1.5 -4l1.5 4l1 -4"/><path d="M9.5 10l1 4l1.5 -4l1.5 4l1 -4"/></svg>
                                    ' . __('Live') . '
                                </a>
                                <a class="dropdown-item" href="' . route('user.connect.domain', $card->card_id) . '">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon dropdown-item-icon text-muted" width="18" height="18" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 14a3.5 3.5 0 0 0 5 0l4 -4a3.5 3.5 0 0 0 -5 -5l-.5 .5"/><path d="M14 10a3.5 3.5 0 0 0 -5 0l-4 4a3.5 3.5 0 0 0 5 5l.5 -.5"/></svg>
                                    ' . __('Custom Domain') . '
                                </a>
                                <a class="dropdown-item" onclick="updateQr(`' . $profileUrl . '`)">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon dropdown-item-icon text-muted" width="18" height="18" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 4m0 1a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v4a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1z"/><path d="M7 17l0 .01"/><path d="M14 4m0 1a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v4a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1z"/><path d="M7 7l0 .01"/><path d="M4 14m0 1a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v4a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1z"/><path d="M17 7l0 .01"/><path d="M14 14l3 0"/><path d="M20 14l0 .01"/><path d="M14 14l0 3"/><path d="M14 20l3 0"/><path d="M17 17l3 0"/><path d="M20 17l0 3"/></svg>
                                    ' . __('QR Code') . '
                                </a>
                                <a class="dropdown-item" href="' . route('user.visitors', $card->card_url) . '">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon dropdown-item-icon text-muted" width="18" height="18" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 13a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v6a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1z"/><path d="M15 9a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v10a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1z"/><path d="M9 5a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v14a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1z"/><path d="M4 20h14"/></svg>
                                    ' . __('Analytics') . '
                                </a>
                                <a class="dropdown-item" href="' . route('user.newsletter', $card->card_id) . '">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon dropdown-item-icon text-muted" width="18" height="18" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 9l9 6l9 -6l-9 -6l-9 6"/><path d="M21 9v10a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2v-10"/><path d="M3 19l6 -6"/><path d="M15 13l6 6"/></svg>
                                    ' . __('Subscribers') . '
                                </a>
                                <a class="dropdown-item border-top" href="' . route('user.edit.card', $card->card_id) . '">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon dropdown-item-icon text-muted" width="18" height="18" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 20h4l10.5 -10.5a2.828 2.828 0 1 0 -4 -4l-10.5 10.5v4"/><path d="M13.5 6.5l4 4"/></svg>
                                    ' . __('Edit Store') . '
                                </a>
                                <a class="dropdown-item" href="' . route('user.edit.categories', $card->card_id) . '">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon dropdown-item-icon text-muted" width="18" height="18" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 4h6v6h-6z"/><path d="M14 4h6v6h-6z"/><path d="M4 14h6v6h-6z"/><path d="M17 17m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0"/></svg>
                                    ' . __('Categories') . '
                                </a>
                                <a class="dropdown-item" href="' . route('user.edit.products', $card->card_id) . '">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon dropdown-item-icon text-muted" width="18" height="18" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 5l0 14"/><path d="M5 12l14 0"/></svg>
                                    ' . __('Products') . '
                                </a>
                                <a class="dropdown-item" href="' . route('user.store.orders', $card->card_id) . '">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon dropdown-item-icon text-muted" width="18" height="18" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M7 17m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"/><path d="M17 17m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"/><path d="M5 17h-2v-11a1 1 0 0 1 1 -1h9v12m-4 0h6m4 0h2v-6h-8m0 -5h5l3 5"/></svg>
                                    ' . __('Manage Orders') . '
                                </a>
                                <a class="dropdown-item" onclick="duplicateStore(`' . $card->card_id . '`, `store`); return false;">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon dropdown-item-icon text-muted" width="18" height="18" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M7 9.667a2.667 2.667 0 0 1 2.667 -2.667h8.666a2.667 2.667 0 0 1 2.667 2.667v8.666a2.667 2.667 0 0 1 -2.667 2.667h-8.666a2.667 2.667 0 0 1 -2.667 -2.667z"/><path d="M4.012 16.737a2 2 0 0 1 -1.012 -1.737v-10c0 -1.1 .9 -2 2 -2h10c.75 0 1.158 .385 1.5 1"/><path d="M11 14h6"/><path d="M14 11v6"/></svg>
                                    ' . __('Duplicate') . '
                                </a>
                                ' . ($card->card_status == 'activated'
                            ? '<a class="dropdown-item text-primary" onclick="toggleStoreStatus(`' . $card->card_id . '`); return false;"><svg xmlns="http://www.w3.org/2000/svg" class="icon dropdown-item-icon text-primary" width="18" height="18" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 12h-6a3 3 0 0 1 -3 -3v-2c0 -1.083 .574 -2.033 1.435 -2.56m3.565 -.44h10a3 3 0 0 1 3 3v2a3 3 0 0 1 -3 3h-2"/><path d="M16 12h2a3 3 0 0 1 3 3v2m-1.448 2.568a2.986 2.986 0 0 1 -1.552 .432h-12a3 3 0 0 1 -3 -3v-2a3 3 0 0 1 3 -3h6"/><path d="M7 8v.01"/><path d="M7 16v.01"/><path d="M3 3l18 18"/></svg>' . __('Disable') . '</a>'
                            : '<a class="dropdown-item text-success" onclick="toggleStoreStatus(`' . $card->card_id . '`); return false;"><svg xmlns="http://www.w3.org/2000/svg" class="icon dropdown-item-icon text-success" width="18" height="18" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 4m0 3a3 3 0 0 1 3 -3h12a3 3 0 0 1 3 3v2a3 3 0 0 1 -3 3h-12a3 3 0 0 1 -3 -3z"/><path d="M3 12m0 3a3 3 0 0 1 3 -3h12a3 3 0 0 1 3 3v2a3 3 0 0 1 -3 3h-12a3 3 0 0 1 -3 -3z"/><path d="M7 8l0 .01"/><path d="M7 16l0 .01"/></svg>' . __('Enable') . '</a>') . '
                                <a class="dropdown-item text-danger border-top" onclick="deleteStore(`' . $card->card_id . '`, `delete`); return false;">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon dropdown-item-icon text-danger" width="18" height="18" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 7l16 0"/><path d="M10 11l0 6"/><path d="M14 11l0 6"/><path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12"/><path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3"/></svg>
                                    ' . __('Delete') . '
                                </a>
                            </div>
                        </div>';
                    })
                    ->rawColumns(['created_at', 'title', 'views', 'card_status', 'action'])
                    ->make(true);
            }

            $config   = DB::table('config')->get();
            $settings = Setting::where('status', 1)->first();

            return view('user.pages.stores.index', compact('settings', 'config'));
        } else {
            return redirect()->route('user.plans');
        }
    }

    // Delete store
    public function deleteStore(Request $request)
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

            return redirect()->route('user.stores')->with('success', trans('Removed!'));
        } else {
            return redirect()->route('user.stores')->with('failed', trans('This store cannot be deleted. Please unlink your custom domain before proceeding.'));
        }
    }
}
