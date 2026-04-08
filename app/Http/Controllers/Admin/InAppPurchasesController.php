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

use App\User;
use App\Setting;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class InAppPurchasesController extends Controller
{
    // in-app purchases
    public function inAppPurchases(Request $request)
    {
        if ($request->ajax()) {

            // purchases
            $purchases = DB::table('in_app_purchases')->get();

            return DataTables::of($purchases)
                ->addIndexColumn()
                ->addColumn('user', function ($row) {
                    $user_details = User::where('user_id', $row->user_id)->first();
                    if ($user_details) {
                        return '<a href="' . route('admin.view.user', $user_details->user_id) . '">' . $user_details->name . '</a>';
                    } else {
                        return '<a href="#">' . __("Customer not available") . '</a>';
                    }
                })
                ->addColumn('platform', function ($row) {
                    $platform = $row->platform;
                    if ($platform == 'android') {
                        return 'Android';
                    } elseif ($platform == 'ios') {
                        return 'iOS';
                    }
                })
                ->addColumn('plan', function ($row) {
                    $plan_details = DB::table('plans')->where('plan_id', $row->plan_id)->first();
                    if ($plan_details) {
                        return '<a href="' . route('admin.plans') . '">' . $plan_details->plan_name . '</a>';
                    } else {
                        return '<a href="#">' . __("Plan not available") . '</a>';
                    }
                })                
                ->addColumn('purchase_type', function ($row) {
                    return $row->purchase_type == 'one_time' ? __('One Time') : __('Subscription');
                })
                ->addColumn('price', function ($row) {
                    return $row->transaction_currency . ' ' . formatCurrencyCard($row->transaction_amount);
                })
                ->addColumn('status', function ($row) {
                    $status = '';
                    if ($row->transaction_status == 'ACTIVE') {
                        $status = '<span class="badge bg-green text-white">' . __('Active') . '</span>';
                    } elseif ($row->transaction_status == 'CANCELED') {
                        $status = '<span class="badge bg-red text-white">' . __('Cancelled') . '</span>';
                    } else {
                        $status = '<span class="badge bg-orange text-white">' . __('Pending') . '</span>';
                    }
                    return $status;
                })
                ->rawColumns(['user', 'status', 'plan', 'purchase_type'])
                ->make(true);
        }

        $settings = Setting::where('status', 1)->first();

        return view('admin.pages.in-app-purchases.index', compact('settings'));
    }
}
