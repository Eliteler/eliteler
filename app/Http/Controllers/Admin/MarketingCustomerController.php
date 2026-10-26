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

use App\Plan;
use App\User;
use App\Setting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;

class MarketingCustomerController extends Controller
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
    // All Customers
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = User::where('role_id', 2)->orderBy('created_at', 'desc')
                ->select('user_id', 'name', 'email', 'email_verified_at', 'plan_id', 'plan_validity', 'status');

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('name', function ($row) {
                    return '<span class="fw-bold">' . e($row->name) . '</span>';
                })
                ->addColumn('email', function ($row) {
                    return '<a href="mailto:' . e($row->email) . '">' . e($row->email) . '</a>';
                })
                ->addColumn('email_verified_at', function ($row) {
                    return $row->email_verified_at
                        ? '<span class="badge bg-green text-white fw-bold">' . trans('Verified') . '</span>'
                        : '<span class="badge bg-red text-white fw-bold">' . trans('Not Verified') . '</span>';
                })
                ->addColumn('subscriped_plan', function ($row) {
                    $plan = Plan::where('plan_id', $row->plan_id)->first();
                    $columnName = $plan ? trans($plan->plan_name) : trans('-');
                    return '<span class="fw-bold">' . $columnName . '</span>';
                })
                ->addColumn('valid_until', function ($row) {
                    $columnName = $row->plan_validity ? Carbon::parse($row->plan_validity)->format('jS, F Y') : '-';
                    return '<span class="fw-bold">' . $columnName . '</span>';
                })
                ->addColumn('subscriped_badge', function ($row) {
                    return $row->plan_id
                        ? '<span class="badge bg-green text-white fw-bold">' . trans('Subscribed') . '</span>'
                        : '<span class="badge bg-red text-white fw-bold">' . trans('Not Subscribed') . '</span>';
                })
                ->addColumn('status', function ($row) {
                    return $row->status == 1
                        ? '<span class="badge bg-green text-white fw-bold">' . trans('Active') . '</span>'
                        : '<span class="badge bg-red text-white fw-bold">' . trans('Inactive') . '</span>';
                })
                ->addColumn('action', function ($row) {
                    $viewUrl = route('admin.view.customer', $row->user_id);
                    $editUrl = route('admin.edit.customer', $row->user_id);

                    return '
                    <a class="btn-action" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
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
                        <a class="dropdown-item" href="' . $viewUrl . '">' . __('View') . '</a>
                        <a class="dropdown-item" href="' . $editUrl . '">' . __('Edit') . '</a>
                    </div>
                ';
                })
                // Explicit global search — this is what makes typing in the search box actually work
                ->filter(function ($query) use ($request) {
                    if ($request->filled('search.value')) {
                        $search = $request->input('search.value');
                        $query->where(function ($q) use ($search) {
                            $q->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%")
                                ->orWhereHas('plan', function ($p) use ($search) {
                                    // only if User model has a `plan()` relation to Plan
                                    $p->where('plan_name', 'like', "%{$search}%");
                                });
                        });
                    }
                }, true) // `true` = override Yajra's default global search entirely

                ->rawColumns(['name', 'email', 'email_verified_at', 'subscriped_plan', 'valid_until', 'subscriped_badge', 'status', 'action'])
                ->make(true);
        }

        $settings = Setting::where('status', 1)->first();
        $config = DB::table('config')->get();

        return view('admin.pages.marketing.customers.index', compact('settings', 'config'));
    }
}
