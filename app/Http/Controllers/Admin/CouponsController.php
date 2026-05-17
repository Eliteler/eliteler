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
use App\Coupon;
use App\Setting;
use App\Currency;
use Carbon\Carbon;
use App\Transaction;
use App\AppliedCoupon;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\NfcCardOrderTransaction;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Console\Application;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class CouponsController extends Controller
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

    // Get all coupons
    public function indexCoupons(Request $request)
    {
        // Queries
        $coupons = Coupon::where('status', '!=', 2)->orderBy('id', 'desc')->get();
        $settings = Setting::where('status', 1)->first();
        $config = DB::table('config')->get();

        if ($request->ajax()) {
            return DataTables::of($coupons)
                ->addIndexColumn()
                ->addColumn('used_for', function ($coupon) {
                    switch ($coupon->used_for) {
                        case 'plan':
                            return '<span class="badge bg-green text-white text-white">' . __('Plan') . '</span>';
                        case 'nfc':
                            return '<span class="badge bg-green text-white text-white">' . __('NFC Card') . '</span>';
                        case 'ai_credits':
                            return '<span class="badge bg-green text-white text-white">' . __('AI Credits') . '</span>';
                    }
                })
                ->addColumn('coupon_code', function ($coupon) {
                    return '<span class="fw-bold text-uppercase">' . $coupon->coupon_code . '</span>';
                })
                ->addColumn('coupon_amount', function ($coupon) {
                    // Get config
                    $data = DB::table('config')->get();
                    $currencies = Currency::where('status', 1)->pluck('symbol', 'iso_code')->toArray();
                    $symbol = $currencies[$data[1]->config_value] ?? '';

                    if ($coupon->coupon_type == 'fixed') {
                        return formatCurrency($coupon->coupon_amount);
                    } else {
                        return $coupon->coupon_amount . '%';
                    }
                })
                ->addColumn('validity', function ($coupon) {
                    return '<span class="fw-bold">' . date('Y-m-d', strtotime($coupon->coupon_expired_on)) . '</span>';
                })
                // Number of users who have used the coupon
                ->addColumn('user', function ($coupon) {
                    // Count the number of users who have used the coupon (user_id wise users -> id)
                    $count = AppliedCoupon::whereIn('coupon_id', [$coupon->coupon_code, $coupon->coupon_id])->distinct('user_id')->count('user_id');
                    return '<span class="fw-bold">' . __(':count User:plural (Used)', ['count' => $count, 'plural' => $count > 1 ? 's' : '']) . '</span>';
                })
                // Number of times used
                ->addColumn('used', function ($coupon) {
                    // Count the number of times used
                    $count = AppliedCoupon::whereIn('coupon_id', [$coupon->coupon_code, $coupon->coupon_id])->count();
                    return '<span class="fw-bold">' . __(':count Time:plural (Used)', ['count' => $count, 'plural' => $count > 1 ? 's' : '']) . '</span>';
                })
                ->addColumn('status', function ($coupon) {
                    if ($coupon->status == 0) {
                        return '<span class="badge bg-red text-white text-white">' . __('Disabled') . '</span>';
                    } else {
                        return '<span class="badge bg-green text-white text-white">' . __('Active') . '</span>';
                    }
                })
                ->addColumn('action', function ($coupon) {
                    $editButton = '<a class="dropdown-item" href="' . route('admin.edit.coupon', $coupon->coupon_id) . '">' . __('Edit') . '</a>';
                    $statisticsButton = '<a class="dropdown-item" href="' . route('admin.statistics.coupon', Str::lower($coupon->coupon_code)) . '">' . __('Statistics') . '</a>';

                    // Activate or Deactivate based on status
                    $statusAction = $coupon->status == 0
                        ? '<a class="dropdown-item" href="#" onclick="getCoupon(`' . $coupon->coupon_id . '`); return false;">' . __('Activate') . '</a>'
                        : '<a class="dropdown-item" href="#" onclick="getCoupon(`' . $coupon->coupon_id . '`); return false;">' . __('Deactivate') . '</a>';

                    $deleteButton = '<a class="dropdown-item text-danger" href="#" onclick="deleteCoupon(`' . $coupon->coupon_id . '`); return false;">' . __('Delete') . '</a>';

                    return '
                        <a class="btn-action" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <!-- Download SVG icon from http://tabler-icons.io/i/dots-vertical -->
                            <svg xmlns="http://www.w3.org/2000/svg" width="24"
                                height="24" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round"
                                class="icon icon-tabler icons-tabler-outline icon-tabler-dots fw-bold">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                <path d="M4 12a1 1 0 1 0 2 0a1 1 0 1 0 -2 0" />
                                <path d="M11 12a1 1 0 1 0 2 0a1 1 0 1 0 -2 0" />
                                <path d="M18 12a1 1 0 1 0 2 0a1 1 0 1 0 -2 0" />
                            </svg>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end">
                            ' . $editButton . '
                            ' . $statisticsButton . '
                            ' . $statusAction . '
                            ' . $deleteButton . '
                        </div>
                    ';
                })
                ->rawColumns(['used_for', 'coupon_code', 'coupon_amount', 'validity', 'user', 'used', 'status', 'action'])
                ->make(true);
        }

        return view('admin.pages.coupons.index', compact('coupons', 'settings', 'config'));
    }

    // Create a new coupon
    public function createCoupon(Request $request)
    {
        // Queries
        $settings = Setting::where('status', 1)->first();
        $config = DB::table('config')->get();

        return view('admin.pages.coupons.create', compact('settings', 'config'));
    }

    // Save a new coupon
    public function storeCoupon(Request $request)
    {
        // Validate
        $validator = Validator::make($request->all(), [
            'used_for' => 'required',
            'code' => 'required',
            'type' => 'required',
            'discount' => 'required',
            'validity' => 'required',
            'user_limit' => 'required',
            'total_limit' => 'required',
        ]);

        // Validate message
        if ($validator->fails()) {
            return redirect()->back()->with('failed', $validator->messages()->all()[0])->withInput();
        }

        // Coupon code already exists
        if (Coupon::where('coupon_code', $request->code)
            ->where('used_for', $request->used_for)
            ->where('status', '!=', 2)
            ->where(function ($q) {
                $q->whereNull('coupon_expired_on')
                    ->orWhere('coupon_expired_on', '>=', Carbon::now());
            })
            ->exists()
        ) {

            return redirect()->route('admin.coupons')->with('failed', trans('This coupon code already exists!'));
        }

        // Save
        $coupon = new Coupon;
        $coupon->coupon_id = uniqid();
        $coupon->used_for = $request->used_for;
        $coupon->coupon_code = Str::upper($request->code);
        $coupon->coupon_desc = $request->description;
        $coupon->coupon_type = $request->type;
        $coupon->coupon_amount = $request->discount;
        $coupon->coupon_expired_on = $request->validity . " 23:59:59";
        $coupon->coupon_user_usage_limit = $request->user_limit;
        $coupon->coupon_total_usage_limit = $request->total_limit;
        $coupon->save();

        return redirect()->route('admin.coupons')->with('success', trans('Created!'));
    }

    // Statistics of coupon
    public function statisticsCoupon(Request $request, $id)
    {
        // Queries
        $settings = Setting::where('status', 1)->first();
        $config = DB::table('config')->get();

        // Get config
        $currencies = Currency::where('status', 1)->pluck('symbol', 'iso_code')->toArray();
        $symbol = $currencies[$config[1]->config_value] ?? '';

        // Get coupon details
        $couponDetails = Coupon::where('coupon_code', $id)->first();

        // Check $couponDetails is not null
        if (!$couponDetails) {
            return redirect()->route('admin.coupons')->with('failed', trans('Not Found!'));
        }

        // Check "used_for" for plan or nfc card
        if ($couponDetails->used_for == 'plan') {
            // Get applied coupon and coupon details (joins)
            $couponUsage = AppliedCoupon::where('applied_coupons.coupon_id', $couponDetails->coupon_id)
                ->join('coupons', 'coupons.coupon_id', '=', 'applied_coupons.coupon_id')
                ->select('applied_coupons.*', 'coupons.*') // Select all columns
                ->get();

            for ($i = 0; $i < count($couponUsage); $i++) {
                // Transactions
                $couponUsage[$i]->transactions = Transaction::whereJsonContains('invoice_details->applied_coupon', $couponDetails->coupon_code)->get();
                $couponUsage[$i]->user = User::where('id', $couponUsage[$i]->user_id)->first();
            }
        } else {
            // Get applied coupon and coupon details (joins)
            $couponUsage = AppliedCoupon::where('applied_coupons.coupon_id', $couponDetails->coupon_code)
                ->join('coupons', 'coupons.coupon_code', '=', 'applied_coupons.coupon_id')
                ->select('applied_coupons.*', 'coupons.*') // Select all columns
                ->get();

            for ($i = 0; $i < count($couponUsage); $i++) {
                // Transactions
                $couponUsage[$i]->transactions = NfcCardOrderTransaction::whereJsonContains('invoice_details->applied_coupon', $couponDetails->coupon_code)->get();
                $couponUsage[$i]->user = User::where('id', $couponUsage[$i]->user_id)->first();
            }
        }

        // Return the view with the necessary data
        return view('admin.pages.coupons.statistics', compact('couponUsage', 'symbol', 'settings', 'config'));
    }

    // Edit a coupon
    public function editCoupon(Request $request, $id)
    {
        // First we need to find the coupon
        $couponDetails = Coupon::where('coupon_id', $id)->first();

        // Check coupon exists
        if ($couponDetails == null) {
            return redirect()->route('admin.coupons')->with('failed', trans('Not Found!'));
        }

        // Queries
        $settings = Setting::where('status', 1)->first();
        $config = DB::table('config')->get();

        return view('admin.pages.coupons.edit', compact('couponDetails', 'config', 'settings'));
    }

    // Update a coupon
    public function updateCoupon(Request $request, $id)
    {
        // Validate the input
        $validator = Validator::make($request->all(), [
            'used_for' => 'required',
            'code' => 'required',
            'type' => 'required',
            'discount' => 'required',
            'validity' => 'required',
            'user_limit' => 'required',
            'total_limit' => 'required',
        ]);

        // Validate message
        if ($validator->fails()) {
            return redirect()->back()->with('failed', $validator->messages()->all()[0])->withInput();
        }

        // Update coupon
        $coupon = Coupon::where('coupon_id', $id)->first();
        $coupon->used_for = $request->used_for;
        $coupon->coupon_code = Str::upper($request->code);
        $coupon->coupon_desc = $request->description;
        $coupon->coupon_type = $request->type;
        $coupon->coupon_amount = $request->discount;
        $coupon->coupon_expired_on = $request->validity . " 23:59:59";
        $coupon->coupon_user_usage_limit = $request->user_limit;
        $coupon->coupon_total_usage_limit = $request->total_limit;
        $coupon->save();

        return redirect()->route('admin.coupons')->with('success', trans('Updated!'));
    }

    // Update coupon status
    public function updateCouponStatus(Request $request)
    {
        // Update
        $coupon = Coupon::where('coupon_id', $request->query('id'))->first();

        // Check status
        if ($coupon->status == 1) {
            $coupon->status = 0;
        } else {
            $coupon->status = 1;
        }
        $coupon->save();

        return redirect()->route('admin.coupons')->with('success', trans('Updated!'));
    }

    // Delete coupon
    public function deleteCoupon(Request $request)
    {
        // Update
        $coupon = Coupon::where('coupon_id', $request->query('id'))->first();
        $coupon->status = 2;
        $coupon->save();

        return redirect()->route('admin.coupons')->with('success', trans('Deleted!'));
    }

    // Check coupon code exists
    public function codeExists(Request $request)
    {
        // Check coupon code exists
        if (Coupon::where('coupon_code', $request->code)
            ->where('used_for', $request->used_for)
            ->where('status', '!=', 2)
            ->where(function ($q) {
                $q->whereNull('coupon_expired_on')
                    ->orWhere('coupon_expired_on', '>=', Carbon::now());
            })
            ->exists()
        ) {
            return response()->json(['success' => true]);
        } else {
            return response()->json(['success' => false]);
        }
    }
}
