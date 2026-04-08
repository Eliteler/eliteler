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


namespace App\Http\Controllers\Admin;

use App\Setting;
use Carbon\Carbon;
use App\BusinessCard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;

class CreatedCardsController extends Controller
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

    public function createdCards(Request $request)
    {
        // Queries
        $config = DB::table('config')->get();
        $settings = Setting::where('status', 1)->first();

        // All Customer's vCards with created customer details
        $cards = BusinessCard::where('business_cards.user_id', '!=', '1')->join('users', 'users.user_id', '=', 'business_cards.user_id')
            ->select('business_cards.*', 'users.name as customer_name', 'users.email as customer_email', 'users.mobile_number as customer_phone', 'users.plan_validity as card_expiry')
            ->where('business_cards.card_type', 'vcard')
            ->orderBy('business_cards.id', 'desc')
            ->get();

        if ($request->ajax()) {
            return DataTables::of($cards)
                ->addIndexColumn('id')
                ->addColumn('customer_name', function ($row) {
                    return '<a href="' . route('admin.view.customer', $row->user_id) . '">' . $row->customer_name . '</a>';
                })
                ->addColumn('customer_email', function ($row) {
                    return '<a href="mailto:' . $row->customer_email . '">' . $row->customer_email . '</a>';
                })
                ->addColumn('customer_phone', function ($row) {
                    return '<a href="tel:' . $row->customer_phone . '">' . $row->customer_phone . '</a>';
                })
                ->addColumn('card_name', function ($row) {
                    return '<a href="' . route('profile', $row->card_url) . '" target="_blank">' . $row->title . '</a>';
                })
                ->addColumn('card_type', function ($row) {
                    return '<span class="badge bg-green text-white">' . __('vCard') . '</span>';
                })
                ->addColumn('card_expiry', function ($row) {
                    // Check card expiry date current date
                    $current_date = Carbon::now();
                    $remaining_days = $current_date->diffInDays($row->card_expiry, false);

                    if ($remaining_days > 0) {
                        $remaining_days = $remaining_days . ' ' . __('days');
                        return '<span class="fw-bold">' . (int) $remaining_days . ' ' . __('days remaining') . '</span>';
                    } else {
                        return '<span class="badge bg-red text-white">' . __('Expired') . '</span>';
                    }
                })
                ->addColumn('card_status', function ($row) {
                    return $row->card_status != 'activated'
                        ? '<span class="badge bg-red text-white">' . __('Deactivate') . '</span>'
                        : '<span class="badge bg-green text-white">' . __('Active') . '</span>';
                })
                ->rawColumns(['customer_name', 'customer_email', 'customer_phone', 'card_name', 'card_type', 'card_expiry', 'card_status'])
                ->make(true);
        }

        return view('admin.pages.customers.created-cards', compact('settings', 'config'));
    }

    public function createdStores(Request $request)
    {
        // Queries
        $config = DB::table('config')->get();
        $settings = Setting::where('status', 1)->first();

        // All Customer's vCards with created customer details
        $cards = BusinessCard::where('business_cards.user_id', '!=', '1')->join('users', 'users.user_id', '=', 'business_cards.user_id')
            ->select('business_cards.*', 'users.name as customer_name', 'users.email as customer_email', 'users.mobile_number as customer_phone', 'users.plan_validity as card_expiry')
            ->where('business_cards.card_type', 'store')
            ->orderBy('business_cards.id', 'desc')
            ->get();

        if ($request->ajax()) {
            return DataTables::of($cards)
                ->addIndexColumn('id')
                ->addColumn('customer_name', function ($row) {
                    return '<a href="' . route('admin.view.customer', $row->user_id) . '">' . $row->customer_name . '</a>';
                })
                ->addColumn('customer_email', function ($row) {
                    return '<a href="mailto:' . $row->customer_email . '">' . $row->customer_email . '</a>';
                })
                ->addColumn('customer_phone', function ($row) {
                    return '<a href="tel:' . $row->customer_phone . '">' . $row->customer_phone . '</a>';
                })
                ->addColumn('card_name', function ($row) {
                    return '<a href="' . route('profile', $row->card_url) . '" target="_blank">' . $row->title . '</a>';
                })
                ->addColumn('card_type', function ($row) {
                    return '<span class="badge bg-green text-white">' . __('Store') . '</span>';
                })
                ->addColumn('card_expiry', function ($row) {
                    // Check card expiry date current date
                    $current_date = Carbon::now();
                    $remaining_days = $current_date->diffInDays($row->card_expiry, false);

                    if ($remaining_days > 0) {
                        $remaining_days = $remaining_days . ' ' . __('days');
                        return '<span class="fw-bold">' . (int) $remaining_days . ' ' . __('days remaining') . '</span>';
                    } else {
                        return '<span class="badge bg-red text-white">' . __('Expired') . '</span>';
                    }
                })
                ->addColumn('card_status', function ($row) {
                    return $row->card_status != 'activated'
                        ? '<span class="badge bg-red text-white">' . __('Deactive') . '</span>'
                        : '<span class="badge bg-green text-white">' . __('Active') . '</span>';
                })
                ->rawColumns(['customer_name', 'customer_email', 'customer_phone', 'card_name', 'card_type', 'card_expiry', 'card_status'])
                ->make(true);
        }

        return view('admin.pages.customers.created-stores', compact('settings', 'config'));
    }
}
