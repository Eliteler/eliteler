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

use App\AiCreditsPlan;
use App\AiCreditsTransaction;
use App\Currency;
use App\Http\Controllers\Controller;
use App\Setting;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class AiCreditsTransactionController extends Controller
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

    // Get all transactions
    public function index(Request $request)
    {
        // Queries
        if ($request->ajax()) {
            // Return raw Query Builder to Yajra DataTables to allow efficient server-side pagination with default sorting
            $transactions = AiCreditsTransaction::where('user_id', Auth::id())
                ->orderBy('created_at', 'desc');

            // Pre-fetch AI Plans to prevent database query performance bottlenecks (N+1 resolution)
            $plans = DB::table('ai_credits_plans')
                ->pluck('plan_name', 'ai_credits_plan_id')
                ->toArray();

            // Pre-fetch currencies into a map to prevent dynamic row-level lookups
            $currencies = Currency::where('status', 1)
                ->pluck('symbol', 'iso_code')
                ->toArray();

            return DataTables::of($transactions)
                ->addIndexColumn()
                ->addColumn('created_at', function ($transaction) {
                    return '<span class="fw-bold text-secondary">' . e($transaction->created_at->format('Y-m-d H:i:s')) . '</span>';
                })
                ->addColumn('ai_credits_order_id', function ($transaction) {
                    return '<span class="fw-bold">' . e($transaction->ai_credits_order_id) . '</span>';
                })
                ->addColumn('payment_transaction_id', function ($transaction) {
                    if ($transaction->payment_status == "paid") {
                        return '<a class="fw-bold text-decoration-none" href="' . route('user.ai.credits.transaction.invoice', $transaction->ai_credits_transaction_id) . '">' . e($transaction->payment_transaction_id) . '</a>';
                    }
                    return '<span class="fw-bold text-secondary">' . e($transaction->payment_transaction_id) . '</span>';
                })
                ->addColumn('ai_credits_plan_id', function ($transaction) use ($plans) {
                    $planName = $plans[$transaction->ai_credits_plan_id] ?? null;
                    if ($planName) {
                        return '<span class="fw-bold">' . e(__($planName)) . '</span>';
                    }
                    return '<span class="fw-bold text-muted">' . __("Plan not available") . '</span>';
                })
                ->addColumn('payment_method', function ($transaction) {
                    return '<span class="fw-medium">' . e(__($transaction->payment_method) ?? __('N/A')) . '</span>';
                })
                ->addColumn('amount', function ($transaction) {
                    return '<span class="fw-bold text-dark">' . formatCurrency($transaction->amount) . '</span>';
                })
                ->addColumn('payment_status', function ($transaction) {
                    switch ($transaction->payment_status) {
                        case 'paid':
                            return '<span class="badge bg-green-600 text-green-50">' . __('Paid') . '</span>';
                        case 'pending':
                            return '<span class="badge bg-slate-100 text-slate-600">' . __('Pending') . '</span>';
                        case 'processing':
                            return '<span class="badge bg-yellow-100 text-yellow-800">' . __('Processing') . '</span>';
                        case 'failed':
                            return '<span class="badge bg-red-100 text-red-700">' . __('Failed') . '</span>';
                        case 'cancelled':
                            return '<span class="badge bg-red-100 text-red-700">' . __('Cancelled') . '</span>';
                        case 'refunded':
                            return '<span class="badge bg-slate-100 text-slate-600">' . __('Refunded') . '</span>';
                        case 'partially_refunded':
                            return '<span class="badge bg-slate-100 text-slate-600">' . __('Partially Refunded') . '</span>';
                        default:
                            return '<span class="badge bg-slate-100 text-slate-600">' . e(ucfirst($transaction->payment_status)) . '</span>';
                    }
                })
                ->addColumn('action', function ($transaction) {
                    if ($transaction->payment_status == "paid") {
                        return '
                    <div class="dropdown">
                        <a class="btn-action text-decoration-none" href="#" role="button" data-bs-boundary="viewport" data-bs-toggle="dropdown" aria-expanded="false">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icon-tabler-dots">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                <circle cx="5" cy="12" r="1" />
                                <circle cx="12" cy="12" r="1" />
                                <circle cx="19" cy="12" r="1" />
                            </svg>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end">
                            <a class="dropdown-item" href="' . route('user.ai.credits.transaction.invoice', ['id' => $transaction->ai_credits_transaction_id]) . '">' . __('Invoice') . '</a>
                        </div>
                    </div>';
                    }
                    return '';
                })
                // Custom subquery filter to map search strings directly to Plan Names
                ->filterColumn('ai_credits_plan_id', function ($query, $keyword) {
                    $query->whereIn('ai_credits_plan_id', function ($subQuery) use ($keyword) {
                        $subQuery->select('ai_credits_plan_id')
                            ->from('ai_credits_plans')
                            ->where('plan_name', 'like', "%{$keyword}%");
                    });
                })
                ->rawColumns(['created_at', 'ai_credits_order_id', 'payment_transaction_id', 'ai_credits_plan_id', 'payment_method', 'amount', 'payment_status', 'action'])
                ->make(true);
        }

        $settings = Setting::where('status', 1)->first();
        $currencies = Currency::where('status', 1)->get();

        return view('user.pages.ai-credits-transactions.index', compact('settings', 'currencies'));
    }

    // View transaction invoice
    public function viewInvoice($id)
    {
        // Get transaction details
        $transaction = AiCreditsTransaction::where('user_id', Auth::id())
            ->where(function ($query) use ($id) {
                $query->where('ai_credits_transaction_id', $id)
                    ->orWhere('payment_transaction_id', $id);
            })
            ->first();

        if (!$transaction) {
            return redirect()->route('user.ai.credits.transactions')
                ->with('failed', __('Transaction not found'));
        }

        $settings  = Setting::where('status', 1)->first();
        $currencies = Currency::where('status', 1)->get();
        $config    = DB::table('config')->get();

        // Get billing details from transaction
        $billingDetails = json_decode($transaction->invoice_details, true);

        // Fix: use consistent object access and guard against missing plan
        $planDetails = AiCreditsPlan::where('ai_credits_plan_id', $transaction->ai_credits_plan_id)->first();
        if (!$planDetails) {
            return redirect()->route('user.ai.credits.transactions')
                ->with('failed', __('Plan details not found'));
        }

        return view('user.pages.ai-credits-transactions.view-invoice', compact(
            'transaction',
            'billingDetails',
            'planDetails',
            'settings',
            'config',
            'currencies'
        ));
    }
}
