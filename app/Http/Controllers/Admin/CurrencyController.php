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
use App\Currency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;

class CurrencyController extends Controller
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

    // All Currencies
    public function currencies(Request $request)
    {
        // Queries
        $currencies = Currency::where('status', 1)->where('status', 1)->get();
        $config = DB::table('config')->get();
        $settings = Setting::where('status', 1)->first();

        if ($request->ajax()) {
            return DataTables::of($currencies)
                ->addIndexColumn('id')
                ->addColumn('iso_code', function ($row) {
                    return $row->iso_code;
                })
                ->addColumn('name', function ($row) {
                    return '<a href="' . route('admin.edit.currency', $row->id) . '">' . $row->name . '</a>';
                })
                ->addColumn('symbol', function ($row) {
                    return $row->symbol;
                })
                ->addColumn('symbol_first', function ($row) {
                    return $row->symbol_first == 'false' ?
                        '<span class="badge bg-red text-white">' . __('No') . '</span>' :
                        '<span class="badge bg-green text-white">' . __('Yes') . '</span>';
                })
                ->addColumn('status', function ($row) {
                    return '<span class="badge bg-green text-white">' . __('Active') . '</span>';
                })
                ->addColumn('action', function ($row) {
                    $editUrl = route('admin.edit.currency', $row->id);
                    $activateDeactivate = $row->status == 0 ? trans('Activate') : trans('Deactivate');
                    $activateDeactivateFunction = $row->status == 0 ? 'activateCurrency' : 'deactivateCurrency';

                    return '
                        <a class="btn-action" href="#" role="button" data-bs-boundary="viewport" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
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
                            <a class="dropdown-item" href="' . $editUrl . '">' . __('Edit') . '</a>
                            <a class="dropdown-item text-danger" href="#" onclick="deleteCurrency(`' . $row->id . '`); return false;">' . __('Delete') . '</a>
                        </div>';
                })
                ->rawColumns(['name', 'iso_code', 'symbol', 'symbol_first', 'status', 'action'])
                ->make(true);
        }

        return view('admin.pages.currencies.index', compact('config', 'settings'));
    }

    // Edit Currency
    public function editCurrency(Request $request, $id)
    {
        // Queries
        $currency_details = Currency::where('id', $id)->where('status', 1)->first();
        $settings = Setting::where('status', 1)->first();
        $config = DB::table('config')->get();

        if ($currency_details == null) {
            return redirect()->route('admin.currencies')->with('failed', trans('Currency not found!'));
        } else {
            return view('admin.pages.currencies.edit', compact('currency_details', 'settings', 'config'));
        }
    }

    // Update Currency
    public function updateCurrency(Request $request)
    {
        // Validate
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'iso_code' => 'required',
            'symbol' => 'required',
            'symbol_first' => 'required',
        ]);

        if ($validator->fails()) {
            return back()->with('failed', $validator->messages()->all()[0])->withInput();
        }

        // Queries
        $currency_details = Currency::where('id', $request->id)->first();

        if ($currency_details == null) {
            return redirect()->route('admin.currencies')->with('failed', trans('Currency not found!'));
        } else {
            // Update
            Currency::where('id', $request->id)->update([
                'name' => $request->name,
                'iso_code' => $request->iso_code,
                'symbol' => $request->symbol,
                'symbol_first' => $request->symbol_first,
            ]);

            return redirect()->route('admin.currencies')->with('success', trans('Updated!'));
        }
    }

    // Delete Currency
    public function deleteCurrency(Request $request)
    {
        $id = $request->query('id');

        $currency = Currency::where('id', $id)->first();

        if (!$currency) {
            return redirect()->route('admin.currencies')
                ->with('failed', __('Currency not found!'));
        }

        // Check if this is the last active currency
        $activeCount = Currency::where('status', 1)->count();
        if ($activeCount <= 1 && $currency->status == 1) {
            return redirect()->route('admin.currencies')
                ->with('failed', __('Unable to delete currency. Please keep at least one active currency.'));
        }

        // Soft delete (set status = 0)
        Currency::where('id', $id)->update(['status' => 0]);

        return redirect()->route('admin.currencies')
            ->with('success', __('Currency deleted!'));
    }
}
