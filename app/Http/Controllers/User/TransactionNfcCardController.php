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

use App\Plan;
use App\User;
use App\Setting;
use App\Currency;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\NfcCardOrderTransaction;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\NfcCardOrder;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class TransactionNfcCardController extends Controller
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

    //  NFC Card Transactions
    public function index(Request $request)
    {
        // Pre-fetch configs and plan details outside loops to prevent performance N+1 bottlenecks
        $settings = Setting::where('status', 1)->first();
        $config = DB::table('config')->get();
        $user = User::where('user_id', Auth::user()->user_id)->first();
        $active_plan = json_decode($user->plan_details);

        // Validate if the NFC card order system is enabled in configuration parameters
        if (isset($config[76]) && $config[76]->config_value == '0') {
            return abort(404);
        }

        if ($active_plan != null) {
            // Confirm "nfc_card" feature is actively compiled inside current subscription
            if (isset($active_plan->nfc_card) && $active_plan->nfc_card == 1) {
                if ($request->ajax()) {
                    // Pre-compile currency mapping data prior to iteration processing
                    $currencies = Currency::where('status', 1)->pluck('symbol', 'iso_code')->toArray();
                    $configCurrencyCode = isset($config[1]) ? $config[1]->config_value : 'USD';
                    $currencySymbol = $currencies[$configCurrencyCode] ?? '$';

                    // Query builder instance passed directly to DataTable to enable database-level filtering/pagination
                    $nfc_card_transactions = NfcCardOrderTransaction::join('nfc_card_orders', 'nfc_card_order_transactions.nfc_card_order_id', '=', 'nfc_card_orders.nfc_card_order_id')
                        ->join('nfc_card_designs', 'nfc_card_orders.nfc_card_id', '=', 'nfc_card_designs.nfc_card_id')
                        ->join('users', 'nfc_card_orders.user_id', '=', 'users.id')
                        ->select(
                            'nfc_card_order_transactions.id',
                            'nfc_card_order_transactions.nfc_card_order_transaction_id',
                            'nfc_card_order_transactions.payment_transaction_id',
                            'nfc_card_order_transactions.payment_method',
                            'nfc_card_order_transactions.amount',
                            'nfc_card_order_transactions.payment_status',
                            'nfc_card_order_transactions.created_at',
                            'users.id as fetched_user_id',
                            'nfc_card_orders.nfc_card_order_id',
                            'nfc_card_orders.nfc_card_id',
                            'nfc_card_designs.nfc_card_name',
                            'nfc_card_designs.nfc_card_price',
                            'nfc_card_orders.status as order_status',
                            'nfc_card_orders.created_at as order_created_at',
                            'nfc_card_orders.updated_at as order_updated_at'
                        )
                        ->where('nfc_card_orders.user_id', Auth::user()->id)
                        ->orderBy('nfc_card_order_transactions.id', 'desc');

                    return DataTables::of($nfc_card_transactions)
                        ->addIndexColumn()
                        ->editColumn('created_at', function ($transaction) {
                            return '<span class="fw-bold text-secondary">' .
                                formatDateForUser($transaction->created_at) .
                                '</span>';
                        })
                        ->editColumn('nfc_card_order_transaction_id', function ($transaction) {
                            if ($transaction->payment_status == 'success') {
                                return '<a class="fw-bold text-decoration-none text-dark hover-primary" href="' . route('user.transaction.nfc.card.view.invoice', $transaction->nfc_card_order_transaction_id) . '" target="_blank">' .
                                    e($transaction->nfc_card_order_transaction_id) .
                                    '</a>';
                            }
                            return '<span class="fw-bold text-secondary">' . e($transaction->nfc_card_order_transaction_id) . '</span>';
                        })
                        ->editColumn('payment_transaction_id', function ($transaction) {
                            if ($transaction->payment_status == 'success') {
                                return '<a class="fw-bold text-decoration-none text-dark hover-primary" href="' . route('user.transaction.nfc.card.view.invoice', $transaction->payment_transaction_id) . '" target="_blank">' .
                                    e($transaction->payment_transaction_id) .
                                    '</a>';
                            }
                            return '<span class="fw-bold text-secondary">' . e($transaction->payment_transaction_id) . '</span>';
                        })
                        ->editColumn('nfc_card_order_id', function ($transaction) {
                            return '<a class="fw-bold text-decoration-none text-dark hover-primary" href="' . route('user.order.nfc.card.view', $transaction->nfc_card_order_id) . '" target="_blank">' .
                                e($transaction->nfc_card_order_id) .
                                '</a>';
                        })
                        ->editColumn('payment_method', function ($transaction) {
                            return '<span class="badge bg-slate-100 text-slate-600 px-2 py-1 fw-semibold">' .
                                e(trans($transaction->payment_method)) .
                                '</span>';
                        })
                        ->editColumn('amount', function ($transaction) use ($currencySymbol) {
                            return '<span class="fw-bold text-dark">' .
                                e($currencySymbol) . number_format($transaction->amount, 2) .
                                '</span>';
                        })
                        ->editColumn('payment_status', function ($transaction) {
                            // Polished modern soft-muted status badges mapping
                            if ($transaction->payment_status == 'pending') {
                                return '<span class="badge bg-yellow-100 text-yellow-800 px-2 py-1 fw-semibold">' . __('Pending') . '</span>';
                            } elseif ($transaction->payment_status == 'success') {
                                return '<span class="badge bg-green-600 text-green-50 px-2 py-1 fw-semibold">' . __('Success') . '</span>';
                            } else {
                                return '<span class="badge bg-red-100 text-red-700 px-2 py-1 fw-semibold">' . __('Failed') . '</span>';
                            }
                        })
                        ->editColumn('action', function ($transaction) {
                            if ($transaction->payment_status == 'success') {
                                return '<a class="btn-action text-decoration-none" href="#" data-toggle="dropdown" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icon-tabler-dots">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                        <circle cx="5" cy="12" r="1" />
                                        <circle cx="12" cy="12" r="1" />
                                        <circle cx="19" cy="12" r="1" />
                                        </svg>
                                    </a>
                                <div class="dropdown-menu dropdown-menu-end">
                                        <div class="nav-item dropdown">
                                        <a href="' . route('user.transaction.nfc.card.view.invoice', $transaction->nfc_card_order_transaction_id) . '" target="_blank" class="dropdown-item">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="pe-2 icon icon-tabler icon-tabler-invoice"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M14 3v4a1 1 0 0 0 1 1h4" /><path d="M19 12v7a1.78 1.78 0 0 1 -3.1 1.4a1.65 1.65 0 0 0 -2.6 0a1.65 1.65 0 0 1 -2.6 0a1.65 1.65 0 0 0 -2.6 0a1.78 1.78 0 0 1 -3.1 -1.4v-14a2 2 0 0 1 2 -2h7l5 5v4.25" /></svg>' .
                                    __('Invoice') .
                                    '</a>
                                        </div>
                                    </div>';
                            } else {
                                return '<span class="text-center fw-bold no-action">-</span>';
                            }
                        })
                        ->rawColumns(['created_at', 'nfc_card_order_transaction_id', 'payment_transaction_id', 'nfc_card_order_id', 'payment_method', 'amount', 'payment_status', 'action'])
                        ->make(true);
                }
            } else {
                return redirect()->route('user.plans')->with('failed', __('This feature is not available on your current plan. Upgrade to a plan that includes this feature.'));
            }

            return view('user.pages.order.transactions.index', compact('settings', 'config'));
        } else {
            return redirect()->route('user.plans');
        }
    }

    // View invoice
    public function viewTransactionInvoice($transaction)
    {
        // Queries
        $settings = Setting::where('status', 1)->first();
        $config = DB::table('config')->get();
        $currencies = Currency::where('status', 1)->get();

        // Check enable nfc card order system
        if ($config[76]->config_value == '0') {
            return abort(404);
        }

        // Check if the user has a plan
        $active_plan = Plan::where('plan_id', Auth::user()->plan_id)->first();
        $plan = User::where('user_id', Auth::user()->user_id)->first();
        $active_plan = json_decode($plan->plan_details);

        // Joins "nfc_card_orders", "nfc_card_order_transactions" and "nfc_card_designs" tables
        $transaction_details = NfcCardOrderTransaction::join('nfc_card_orders', 'nfc_card_order_transactions.nfc_card_order_id', '=', 'nfc_card_orders.nfc_card_order_id')
            ->join('nfc_card_designs', 'nfc_card_orders.nfc_card_id', '=', 'nfc_card_designs.nfc_card_id')
            ->join('users', 'nfc_card_orders.user_id', '=', 'users.id')
            ->select('nfc_card_order_transactions.*', 'users.*', 'nfc_card_orders.nfc_card_order_id', 'nfc_card_orders.nfc_card_id', 'nfc_card_designs.nfc_card_name', 'nfc_card_designs.nfc_card_price', 'nfc_card_orders.status', 'nfc_card_orders.created_at', 'nfc_card_orders.updated_at', 'nfc_card_orders.order_details')
            ->where('nfc_card_order_transactions.nfc_card_order_transaction_id', $transaction)->orWhere('nfc_card_order_transactions.payment_transaction_id', $transaction)->where('nfc_card_orders.user_id', Auth::user()->id)
            ->first();

        // Check if the transaction exists
        if (!$transaction_details) {
            return redirect()->route('user.transaction.nfc.cards')->with('failed', trans('Transaction not found!'));
        }

        // Check "nfc_card" is available in the plan
        if ($active_plan->nfc_card == 1) {

            return view('user.pages.order.transactions.view-invoice', compact('transaction_details', 'settings', 'config', 'currencies'));
        } else {
            return redirect()->route('user.plans')->with('failed', __('This feature is not available on your current plan. Upgrade to a plan that includes this feature.'));
        }
    }
}
