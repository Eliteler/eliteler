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

use App\AiCredit;
use App\AiCreditsPlan;
use App\AiCreditsTransaction;
use App\Currency;
use App\Http\Controllers\Controller;
use App\Setting;
use App\User;
use Illuminate\Http\Request;
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
            $transactions = AiCreditsTransaction::orderBy('created_at', 'desc')->get();

            return DataTables::of($transactions)
                ->addIndexColumn()
                ->addColumn('created_at', function ($transaction) {
                    return $transaction->created_at;
                })
                ->addColumn('ai_credits_order_id', function ($transaction) {
                    return '<strong>' . $transaction->ai_credits_order_id . '</strong>';
                })
                ->addColumn('payment_transaction_id', function ($transaction) {
                    if ($transaction->payment_status == "paid") {
                        return '<a class="fw-bold" href="' . route('admin.ai.credits.transaction.invoice', $transaction->ai_credits_transaction_id) . '">' . $transaction->payment_transaction_id . '</a>';
                    } else {
                        return '<span class="fw-bold">' . $transaction->payment_transaction_id . '</span>';
                    }
                })
                ->addColumn('user_id', function ($transaction) {
                    $user_details = User::where('id', $transaction->user_id)->first();
                    if ($user_details) {
                        return '<a class="fw-bold" href="' . route('admin.view.customer', $user_details->user_id) . '">' . $user_details->name . '</a>';
                    } else {
                        return '<a class="fw-bold" href="#">' . __("Customer not available") . '</a>';
                    }
                })
                ->addColumn('ai_credits_plan_id', function ($transaction) {
                    $plan_details = DB::table('ai_credits_plans')->where('ai_credits_plan_id', $transaction->ai_credits_plan_id)->first();
                    if ($plan_details) {
                        return '<strong>' . $plan_details->plan_name . '</strong>';
                    } else {
                        return '<strong>' . __("Plan not available") . '</strong>';
                    }
                })
                ->addColumn('payment_method', function ($transaction) {
                    return $transaction->payment_method;
                })
                ->addColumn('amount', function ($transaction) {
                    $currencies = Currency::where('status', 1)->pluck('symbol', 'iso_code')->toArray();
                    $symbol = $currencies[$transaction->transaction_currency] ?? '';
                    return formatCurrency($transaction->transaction_amount);
                })
                ->addColumn('payment_status', function ($transaction) {
                    $status = '';
                    if ($transaction->payment_status == 'pending') {
                        $status = '<span class="badge bg-warning text-white">' . __('Pending') . '</span>';
                    } elseif ($transaction->payment_status == 'processing') {
                        $status = '<span class="badge bg-primary text-white">' . __('Processing') . '</span>';
                    } elseif ($transaction->payment_status == 'paid') {
                        $status = '<span class="badge bg-success text-white">' . __('Paid') . '</span>';
                    } elseif ($transaction->payment_status == 'failed') {
                        $status = '<span class="badge bg-danger text-white">' . __('Failed') . '</span>';
                    } elseif ($transaction->payment_status == 'cancelled') {
                        $status = '<span class="badge bg-danger text-white">' . __('Cancelled') . '</span>';
                    } elseif ($transaction->payment_status == 'refunded') {
                        $status = '<span class="badge bg-dark text-white">' . __('Refunded') . '</span>';
                    } elseif ($transaction->payment_status == 'partially_refunded') {
                        $status = '<span class="badge bg-dark text-white">' . __('Partially Refunded') . '</span>';
                    }
                    return $status;
                })
                ->addColumn('action', function ($transaction) {
                    $actions = '
                        <a class="btn-action" href="#" role="button" data-bs-boundary="viewport" data-bs-toggle="dropdown" aria-expanded="false">
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
                        <div class="dropdown-menu dropdown-menu-end">';
                    if ($transaction->payment_status == "paid") {
                        $actions .= '<a class="dropdown-item" href="' . route('admin.ai.credits.transaction.invoice', ['id' => $transaction->ai_credits_transaction_id]) . '">' . __('Invoice') . '</a>';
                    }
                    if ($transaction->payment_status != "paid") {
                        $actions .= '<a class="dropdown-item" href="' . route('admin.ai.credits.transaction.status', ['id' => $transaction->ai_credits_transaction_id, 'status' => 'pending']) . '">' . __('Pending') . '</a>';
                        $actions .= '<a class="dropdown-item" href="' . route('admin.ai.credits.transaction.status', ['id' => $transaction->ai_credits_transaction_id, 'status' => 'processing']) . '">' . __('Processing') . '</a>';
                        $actions .= '<a class="dropdown-item" href="' . route('admin.ai.credits.transaction.status', ['id' => $transaction->ai_credits_transaction_id, 'status' => 'paid']) . '">' . __('Paid') . '</a>';
                        $actions .= '<a class="dropdown-item" href="' . route('admin.ai.credits.transaction.status', ['id' => $transaction->ai_credits_transaction_id, 'status' => 'failed']) . '">' . __('Failed') . '</a>';
                    }
                    $actions .= '</div>';

                    return $actions;
                })
                ->rawColumns(['ai_credits_order_id', 'payment_transaction_id', 'user_id', 'ai_credits_plan_id', 'payment_method', 'amount', 'payment_status', 'action'])
                ->make(true);
        }

        $settings = Setting::where('status', 1)->first();
        $currencies = Currency::where('status', 1)->get();

        return view('admin.pages.ai-credits-transactions.index', compact('settings', 'currencies'));
    }

    // View transaction invoice
    public function viewInvoice($id)
    {
        $transaction = AiCreditsTransaction::where('ai_credits_transaction_id', $id)->orWhere('payment_transaction_id', $id)->first();
        if (!$transaction) {
            return redirect()->route('admin.ai.credits.transactions')->with('error', __('Transaction not found'));
        }

        $settings = Setting::where('status', 1)->first();
        $currencies = Currency::where('status', 1)->get();
        $config = DB::table('config')->get();

        // Transaction details
        $transaction['billing_details'] = json_decode($transaction['invoice_details'], true);

        // Get plan details
        $planDetails = AiCreditsPlan::where('ai_credits_plan_id', $transaction['ai_credits_plan_id'])->first();

        // View
        return view('admin.pages.ai-credits-transactions.view-invoice', compact('transaction', 'planDetails', 'settings', 'config', 'currencies'));
    }

    // Update transaction status
    public function updateStatus(Request $request, $id, $status)
    {
        $allowedStatuses = ['pending', 'processing', 'paid', 'failed', 'cancelled', 'refunded', 'partially_refunded'];

        if (!in_array($status, $allowedStatuses)) {
            $status = 'pending';
        }

        $transaction = AiCreditsTransaction::where('ai_credits_transaction_id', $id)->firstOrFail();

        if ($status === 'paid') {
            $user = User::where('id', $transaction->user_id)->firstOrFail();

            $planDetails = AiCreditsPlan::where('ai_credits_plan_id', $transaction->ai_credits_plan_id)->firstOrFail();

            $aiCreditsOrder = AiCredit::where('user_id', $user->user_id)->first();  // Fix: use $user->id

            if ($aiCreditsOrder) {
                $aiCreditsOrder->credits += $planDetails->no_of_ai_credits;
                $aiCreditsOrder->save();
            } else {
                $aiCreditsOrder = new AiCredit();
                $aiCreditsOrder->user_id = $user->user_id;  // Fix: use $user->id
                $aiCreditsOrder->credits = $planDetails->no_of_ai_credits;
                $aiCreditsOrder->save();
            }
        }

        // Single write, at the end
        $transaction->payment_status = $status;
        $transaction->save();

        return redirect()->route('admin.ai.credits.transactions')->with('success', trans('Updated!'));
    }
}
