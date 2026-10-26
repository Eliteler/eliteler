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

use App\Setting;
use App\Currency;
use App\Transaction;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class TransactionsController extends Controller
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

    public function indexTransactions(Request $request)
    {
        $user = Auth::user();
        $active_plan = json_decode($user->plan_details, true);
        $config = DB::table('config')->get();
        $aibuilder_settings = DB::table('aibuilder_settings')->first();
        $settings = Setting::where('status', 1)->first();

        // Check if user has an active plan
        if (!$active_plan) {
            return redirect()->route('user.plans');
        }

        if ($request->ajax()) {
            $typeFilter = $request->get('type', 'all');
            $statusFilter = $request->get('status', 'all');

            // 1. Subscription Plan Transactions
            $planQuery = DB::table('transactions')
                ->where('transactions.user_id', $user->id)
                ->join('plans', 'transactions.plan_id', '=', 'plans.plan_id')
                ->select(
                    'transactions.id as raw_id',
                    DB::raw("'plan' as transaction_type"),
                    'transactions.gobiz_transaction_id as display_id',
                    'transactions.transaction_id as payment_trans_id',
                    'plans.plan_name as item_name',
                    'transactions.payment_gateway_name as gateway',
                    'transactions.transaction_amount as amount',
                    'transactions.payment_status as status',
                    'transactions.invoice_number as invoice_id',
                    'transactions.created_at as created_at'
                );

            // 2. NFC Card Order Transactions
            $nfcQuery = DB::table('nfc_card_order_transactions')
                ->join('nfc_card_orders', 'nfc_card_order_transactions.nfc_card_order_id', '=', 'nfc_card_orders.nfc_card_order_id')
                ->join('nfc_card_designs', 'nfc_card_orders.nfc_card_id', '=', 'nfc_card_designs.nfc_card_id')
                ->where('nfc_card_orders.user_id', $user->id)
                ->select(
                    'nfc_card_order_transactions.id as raw_id',
                    DB::raw("'nfc_card' as transaction_type"),
                    'nfc_card_order_transactions.nfc_card_order_transaction_id as display_id',
                    'nfc_card_order_transactions.payment_transaction_id as payment_trans_id',
                    'nfc_card_designs.nfc_card_name as item_name',
                    'nfc_card_order_transactions.payment_method as gateway',
                    'nfc_card_order_transactions.amount as amount',
                    'nfc_card_order_transactions.payment_status as status',
                    'nfc_card_order_transactions.nfc_card_order_transaction_id as invoice_id',
                    'nfc_card_order_transactions.created_at as created_at'
                );

            // 3. AI Credits Transactions
            $aiQuery = DB::table('ai_credits_transactions')
                ->leftJoin('ai_credits_plans', 'ai_credits_transactions.ai_credits_plan_id', '=', 'ai_credits_plans.ai_credits_plan_id')
                ->where('ai_credits_transactions.user_id', $user->id)
                ->select(
                    'ai_credits_transactions.id as raw_id',
                    DB::raw("'ai_credit' as transaction_type"),
                    'ai_credits_transactions.ai_credits_transaction_id as display_id',
                    'ai_credits_transactions.payment_transaction_id as payment_trans_id',
                    DB::raw("COALESCE(ai_credits_plans.plan_name, 'AI Credits') as item_name"),
                    'ai_credits_transactions.payment_method as gateway',
                    'ai_credits_transactions.amount as amount',
                    'ai_credits_transactions.payment_status as status',
                    'ai_credits_transactions.ai_credits_transaction_id as invoice_id',
                    'ai_credits_transactions.created_at as created_at'
                );

            // Apply Type Category Filter
            if ($typeFilter == 'plan') {
                $finalQuery = $planQuery;
            } elseif ($typeFilter == 'nfc_card') {
                $finalQuery = $nfcQuery;
            } elseif ($typeFilter == 'ai_credit') {
                $finalQuery = $aiQuery;
            } else {
                // Combine all available tables into a single unified query builder
                $finalQuery = $planQuery->unionAll($nfcQuery)->unionAll($aiQuery);
            }

            // Wrap unified queries into a subquery table for DataTables operations
            $unifiedQuery = DB::table(DB::raw("({$finalQuery->toSql()}) as unified_transactions"))
                ->mergeBindings($finalQuery);

            // Apply Status Filter
            if ($statusFilter == 'paid') {
                $unifiedQuery->whereIn(DB::raw('LOWER(status)'), ['success', 'paid']);
            } elseif ($statusFilter == 'unpaid') {
                $unifiedQuery->whereIn(DB::raw('LOWER(status)'), ['pending', 'failed', 'cancelled', 'processing']);
            }

            return DataTables::of($unifiedQuery)
                ->addIndexColumn()
                ->editColumn('created_at', function ($row) {
                    return '<span class="fw-bold text-dark">' . formatDateForUser($row->created_at) . '</span>';
                })
                ->addColumn('gobiz_transaction_id', function ($row) {
                    return '<span class="text-muted fw-semibold">' . e($row->display_id) . '</span>';
                })
                ->addColumn('payment_trans_id', function ($row) {
                    if (!empty($row->payment_trans_id)) {
                        return '<span class="text-secondary small fw-semibold">' . e($row->payment_trans_id) . '</span>';
                    }
                    return '<span class="text-muted small">-</span>';
                })
                ->addColumn('transaction_type', function ($row) {
                    if ($row->transaction_type == 'plan') {
                        return '<span class="badge bg-blue-lt text-uppercase fw-bold" style="font-size: 10px;">' . __('Plan') . '</span>';
                    } elseif ($row->transaction_type == 'nfc_card') {
                        return '<span class="badge bg-purple-lt text-uppercase fw-bold" style="font-size: 10px;">' . __('NFC Card') . '</span>';
                    } elseif ($row->transaction_type == 'ai_credit') {
                        return '<span class="badge bg-azure-lt text-uppercase fw-bold" style="font-size: 10px;">' . __('AI Credits') . '</span>';
                    }
                    return '-';
                })
                ->addColumn('details', function ($row) {
                    return '<span class="fw-bold text-dark">' . e(__($row->item_name)) . '</span>';
                })
                ->addColumn('payment_gateway_name', function ($row) {
                    return '<span class="badge bg-slate-100 text-slate-600 px-2 py-1 fw-semibold">' . e(__($row->gateway ?? 'N/A')) . '</span>';
                })
                ->addColumn('transaction_amount', function ($row) {
                    return '<span class="fw-bold text-dark">' . formatCurrency($row->amount) . '</span>';
                })
                ->addColumn('payment_status', function ($row) {
                    $status = strtolower($row->status);
                    if ($status == 'success' || $status == 'paid') {
                        return '<span class="badge bg-green-600 text-green-50">' . __('Paid') . '</span>';
                    } elseif ($status == 'failed' || $status == 'cancelled') {
                        return '<span class="badge bg-red-100 text-red-700">' . __(ucfirst($status)) . '</span>';
                    } else {
                        return '<span class="badge bg-yellow-100 text-yellow-800">' . __('Pending') . '</span>';
                    }
                })
                ->addColumn('action', function ($row) {
                    $status = strtolower($row->status);
                    $invoiceUrl = null;

                    if ($status == 'success' || $status == 'paid') {
                        if ($row->transaction_type == 'plan' && $row->invoice_id > 0) {
                            $invoiceUrl = route('user.view.invoice', ['id' => $row->display_id]);
                        } elseif ($row->transaction_type == 'nfc_card') {
                            $invoiceUrl = route('user.transaction.nfc.card.view.invoice', ['id' => $row->display_id]);
                        } elseif ($row->transaction_type == 'ai_credit') {
                            $invoiceUrl = route('user.ai.credits.transaction.invoice', ['id' => $row->display_id]);
                        }
                    }

                    if ($invoiceUrl) {
                        return '
                    <a href="' . $invoiceUrl . '" target="_blank" class="btn btn-primary btn-icon text-decoration-none shadow-sm" title="' . __('Invoice') . '">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icon-tabler-invoice">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                            <path d="M14 3v4a1 1 0 0 0 1 1h4" />
                            <path d="M19 12v7a1.78 1.78 0 0 1 -3.1 1.4a1.65 1.65 0 0 0 -2.6 0a1.65 1.65 0 0 1 -2.6 0a1.65 1.65 0 0 0 -2.6 0a1.78 1.78 0 0 1 -3.1 -1.4v-14a2 2 0 0 1 2 -2h7l5 5v4.25" />
                        </svg>
                    </a>';
                    }

                    return '<span class="text-muted small">-</span>';
                })
                ->rawColumns(['created_at', 'gobiz_transaction_id', 'payment_trans_id', 'transaction_type', 'details', 'payment_gateway_name', 'transaction_amount', 'payment_status', 'action'])
                ->make(true);
        }

        $currencies = Currency::where('status', 1)->get();

        return view('user.pages.transactions.index', compact(
            'settings',
            'currencies',
            'config',
            'active_plan',
            'aibuilder_settings'
        ));
    }

    // View invoice
    public function viewInvoice($id)
    {
        // Queries
        $transaction = Transaction::where('gobiz_transaction_id', $id)->orWhere('transaction_id', $id)->first();

        if ($transaction) {
            $settings = Setting::where('status', 1)->first();
            $config = DB::table('config')->get();
            $currencies = Currency::where('status', 1)->get();
            $transaction['billing_details'] = json_decode($transaction['invoice_details'], true);

            return view('user.pages.transactions.view-invoice', compact('transaction', 'settings', 'config', 'currencies'));
        } else {
            return back()->with('failed', trans('Invoice not found!'));
        }
    }
}
