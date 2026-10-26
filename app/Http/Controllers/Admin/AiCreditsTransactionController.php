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

use App\AiCreditsTransaction;
use App\Http\Controllers\Controller;
use App\Services\Admin\AiCreditsService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class AiCreditsTransactionController extends Controller
{
    private const TABS = [
        'all'     => null,
        'paid'    => ['paid'],
        'pending' => ['pending', 'processing'],
        'failed'  => ['failed', 'cancelled'],
        'refunded'=> ['refunded', 'partially_refunded'],
    ];

    public function __construct(private AiCreditsService $credits)
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $tab    = array_key_exists($request->query('tab'), self::TABS) ? $request->query('tab') : 'all';
        $search = trim((string) $request->query('search', ''));

        $base = fn () => AiCreditsTransaction::query()
            ->leftJoin('users', 'users.id', '=', 'ai_credits_transactions.user_id')
            ->leftJoin('ai_credits_plans', 'ai_credits_plans.ai_credits_plan_id', '=', 'ai_credits_transactions.ai_credits_plan_id')
            ->when($search !== '', fn ($q) => $q->where(function ($q) use ($search) {
                $q->where('ai_credits_transactions.ai_credits_transaction_id', 'like', "%{$search}%")
                    ->orWhere('ai_credits_transactions.ai_credits_order_id', 'like', "%{$search}%")
                    ->orWhere('ai_credits_transactions.payment_transaction_id', 'like', "%{$search}%")
                    ->orWhere('users.name', 'like', "%{$search}%")
                    ->orWhere('users.email', 'like', "%{$search}%");
            }));

        $transactions = $base()
            ->when(self::TABS[$tab], fn ($q, $statuses) => $q->whereIn('ai_credits_transactions.payment_status', $statuses))
            ->select([
                'ai_credits_transactions.ai_credits_transaction_id', 'ai_credits_transactions.payment_transaction_id',
                'ai_credits_transactions.payment_method', 'ai_credits_transactions.amount', 'ai_credits_transactions.payment_status',
                'ai_credits_transactions.invoice_prefix', 'ai_credits_transactions.invoice_number', 'ai_credits_transactions.created_at',
                'ai_credits_transactions.purchase_details',
                'ai_credits_plans.plan_name', 'ai_credits_plans.no_of_ai_credits',
                'users.user_id as customer_id', 'users.name as customer_name', 'users.email as customer_email',
            ])
            ->orderByDesc('ai_credits_transactions.created_at')
            ->paginate(20)
            ->withQueryString()
            ->through(fn ($row) => [
                'id'       => $row->ai_credits_transaction_id,
                'invoice'  => $row->invoice_number ? $row->invoice_prefix . $row->invoice_number : null,
                'plan'     => $row->plan_name ?? $row->purchase_details,
                'credits'  => $row->no_of_ai_credits !== null ? (int) $row->no_of_ai_credits : null,
                'method'   => $row->payment_method,
                'amount'   => (float) $row->amount,
                'status'   => $row->payment_status,
                'date'     => $row->created_at ? Carbon::parse($row->created_at)->toIso8601String() : null,
                'customer' => $row->customer_id ? ['id' => $row->customer_id, 'name' => $row->customer_name, 'email' => $row->customer_email] : null,
            ]);

        $counts = $base()->selectRaw(collect(self::TABS)->map(function ($statuses, $key) {
            if (! $statuses) {
                return 'COUNT(*) as `all`';
            }

            return "SUM(ai_credits_transactions.payment_status IN ('" . implode("','", $statuses) . "')) as `{$key}`";
        })->implode(', '))->first();

        return Inertia::render('ai-credits/transactions', [
            'transactions' => $transactions,
            'tab'          => $tab,
            'search'       => $search,
            'counts'       => collect(array_keys(self::TABS))->mapWithKeys(fn ($key) => [$key => (int) $counts->{$key}]),
        ]);
    }

    public function viewInvoice($id)
    {
        $row = AiCreditsTransaction::where('ai_credits_transaction_id', $id)->orWhere('payment_transaction_id', $id)->first();

        if (! $row) {
            return redirect()->route('admin.ai.credits.transactions')->with('failed', __('Not Found!'));
        }

        $invoice = json_decode($row->invoice_details ?? '', true) ?: [];
        $plan    = DB::table('ai_credits_plans')->where('ai_credits_plan_id', $row->ai_credits_plan_id)->first();
        $party   = fn (string $side) => array_filter([
            'name'    => $invoice["{$side}_billing_name"] ?? null,
            'address' => collect([$invoice["{$side}_billing_address"] ?? null, $invoice["{$side}_billing_city"] ?? null, $invoice["{$side}_billing_state"] ?? null, $invoice["{$side}_billing_zipcode"] ?? null, $invoice["{$side}_billing_country"] ?? null])->filter()->implode(', ') ?: null,
            'phone'   => $invoice["{$side}_billing_phone"] ?? null,
            'email'   => $invoice["{$side}_billing_email"] ?? null,
            'taxId'   => $invoice["{$side}_vat_number"] ?? null,
        ]);
        $logo = DB::table('settings')->value('site_logo');

        return Inertia::render('shared/invoice', [
            'invoice' => [
                'number'    => $row->invoice_number ? $row->invoice_prefix . $row->invoice_number : null,
                'id'        => $row->ai_credits_transaction_id,
                'reference' => $row->payment_transaction_id,
                'date'      => $row->created_at?->toIso8601String(),
                'status'    => $row->payment_status,
                'method'    => $row->payment_method,
                'logo'      => $logo ? asset($logo) : null,
                'from'      => $party('from'),
                'to'        => $party('to'),
                'item'      => [
                    'name'        => $plan->plan_name ?? $row->purchase_details,
                    'description' => $plan ? __(':count AI credits', ['count' => $plan->no_of_ai_credits]) : null,
                    'price'       => (float) ($invoice['subtotal'] ?? $plan->plan_price ?? 0),
                    'quantity'    => 1,
                ],
                'subtotal' => (float) ($invoice['subtotal'] ?? 0),
                'taxName'  => $invoice['tax_name'] ?? null,
                'taxRate'  => $invoice['tax_value'] ?? null,
                'tax'      => (float) ($invoice['tax_amount'] ?? 0),
                'coupon'   => $invoice['applied_coupon'] ?? null,
                'discount' => ! empty($invoice['applied_coupon']) ? max(0, (float) ($invoice['discounted_price'] ?? 0) - (float) ($invoice['invoice_amount'] ?? 0)) : 0,
                'total'    => (float) ($invoice['invoice_amount'] ?? $row->amount),
            ],
            'back' => ['label' => __('AI credit purchases'), 'href' => route('admin.ai.credits.transactions')],
        ]);
    }

    public function updateStatus(Request $request, $id, $status)
    {
        $request->merge(['status' => $status])->validate(['status' => ['required', Rule::in(AiCreditsService::STATUSES)]]);

        $transaction = AiCreditsTransaction::where('ai_credits_transaction_id', $id)->firstOrFail();
        $this->credits->setPaymentStatus($transaction, $status);

        return back()->with('success', $status === 'paid'
            ? __('Purchase marked as paid. The credits were added to the customer.')
            : __('Purchase status updated.'));
    }
}
