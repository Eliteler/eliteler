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

use App\Http\Controllers\Controller;
use App\Services\Admin\CustomerPlanService;
use App\Transaction;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

/**
 * Plan payments (transactions). payment_status: SUCCESS, PENDING, FAILED,
 * CANCELLED. Gateways FREE and TRIAL are plan activations without payment.
 */
class TransactionsController extends Controller
{
    private const TABS = [
        'all'     => null,
        'paid'    => ['SUCCESS'],
        'pending' => ['PENDING'],
        'failed'  => ['FAILED', 'CANCELLED'],
    ];

    public function __construct(private CustomerPlanService $plans)
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $tab    = array_key_exists($request->query('tab'), self::TABS) ? $request->query('tab') : 'all';
        $type   = in_array($request->query('type'), ['online', 'offline', 'free'], true) ? $request->query('type') : 'all';
        $search = trim((string) $request->query('search', ''));

        $base = fn() => Transaction::query()
            ->leftJoin('users', 'users.id', '=', 'transactions.user_id')
            ->when($type === 'offline', fn($q) => $q->where('transactions.payment_gateway_name', 'Offline'))
            ->when($type === 'free', fn($q) => $q->whereIn('transactions.payment_gateway_name', ['FREE', 'TRIAL']))
            ->when($type === 'online', fn($q) => $q->whereNotIn('transactions.payment_gateway_name', ['Offline', 'FREE', 'TRIAL']))
            ->when($search !== '', fn($q) => $q->where(fn($q) => $q
                ->where('transactions.gobiz_transaction_id', 'like', "%{$search}%")
                ->orWhere('transactions.transaction_id', 'like', "%{$search}%")
                ->orWhere(DB::raw("CONCAT(COALESCE(transactions.invoice_prefix, ''), COALESCE(transactions.invoice_number, ''))"), 'like', "%{$search}%")
                ->orWhere('transactions.desciption', 'like', "%{$search}%")
                ->orWhere('transactions.payment_gateway_name', 'like', "%{$search}%")
                ->orWhere('users.name', 'like', "%{$search}%")
                ->orWhere('users.email', 'like', "%{$search}%")));

        $transactions = $base()
            ->when(self::TABS[$tab], fn($q, $statuses) => $q->whereIn('transactions.payment_status', $statuses))
            ->select([
                'transactions.gobiz_transaction_id',
                'transactions.transaction_id',
                'transactions.desciption',
                'transactions.payment_gateway_name',
                'transactions.transaction_amount',
                'transactions.transaction_currency',
                'transactions.payment_status',
                'transactions.invoice_prefix',
                'transactions.invoice_number',
                'transactions.created_at',
                'users.user_id as customer_id',
                'users.name',
                'users.email',
            ])
            ->orderByDesc('transactions.created_at')
            ->paginate(25)
            ->withQueryString()
            ->through(fn($row) => [
                'id'        => $row->gobiz_transaction_id,
                'reference' => $row->transaction_id ?: null,
                'invoice'   => $row->invoice_number ? $row->invoice_prefix . $row->invoice_number : null,
                'plan'      => $row->desciption,
                'gateway'   => $row->payment_gateway_name,
                'amount'    => (float) $row->transaction_amount,
                'currency'  => $row->transaction_currency,
                'status'    => strtolower((string) $row->payment_status),
                'date'      => $row->created_at ? Carbon::parse($row->created_at)->toIso8601String() : null,
                'customer'  => $row->customer_id ? ['id' => $row->customer_id, 'name' => $row->name, 'email' => $row->email] : null,
            ]);

        $counts = $base()->selectRaw("COUNT(*) as `all`, SUM(transactions.payment_status = 'SUCCESS') as paid, SUM(transactions.payment_status = 'PENDING') as pending, SUM(transactions.payment_status IN ('FAILED','CANCELLED')) as failed")->first();

        return Inertia::render('transactions/index', [
            'transactions' => $transactions,
            'filters'      => compact('tab', 'type', 'search'),
            'counts'       => collect(array_keys(self::TABS))->mapWithKeys(fn($k) => [$k => (int) $counts->{$k}]),
        ]);
    }

    public function viewInvoice($id)
    {
        $row = Transaction::where(fn($q) => $q->where('gobiz_transaction_id', $id)->orWhere('transaction_id', $id))->first();

        if (! $row || $row->payment_status !== 'SUCCESS') {
            return redirect()->route('admin.transactions.index')->with('failed', __('Not Found!'));
        }

        $invoice = json_decode($row->invoice_details ?? '', true) ?: [];

        // Helper to extract party details from invoice JSON
        $party = fn(string $side) => array_filter([
            'name'    => $invoice["{$side}_billing_name"] ?? null,
            'address' => collect([
                $invoice["{$side}_billing_address"] ?? null,
                $invoice["{$side}_billing_city"] ?? null,
                $invoice["{$side}_billing_state"] ?? null,
                $invoice["{$side}_billing_zipcode"] ?? null,
                $invoice["{$side}_billing_country"] ?? null,
            ])->filter()->implode(', ') ?: null,
            'phone'   => $invoice["{$side}_billing_phone"] ?? null,
            'email'   => $invoice["{$side}_billing_email"] ?? null,
            'taxId'   => $invoice["{$side}_vat_number"] ?? null,
        ]);

        // 1. Fetch config table data (as key-value pairs)
        $config = DB::table('config')->pluck('config_value', 'config_key')->toArray();

        // Prepare config fallback address
        $configAddress = collect([
            $config['invoice_address'] ?? null,
            $config['invoice_city'] ?? null,
            $config['invoice_state'] ?? null,
            $config['invoice_zipcode'] ?? null,
            $config['invoice_country'] ?? null,
        ])->filter()->implode(', ') ?: null;

        // Config fallback for "From"
        $fromFallback = array_filter([
            'name'    => $config['invoice_name'] ?? null,
            'address' => $configAddress,
            'phone'   => $config['invoice_phone'] ?? null,
            'email'   => $config['invoice_email'] ?? null,
            'taxId'   => $config['tax_number'] ?? null,
        ]);

        // Use existing invoice from data; if empty, fallback to config table
        $fromData = ! empty($party('from')) ? $party('from') : $fromFallback;

        // 2. Fetch customer related to this transaction for "Bill to"
        $customer = $row->user ?? (User::find($row->user_id ?? null));

        // Fallback for "To": Customer name and email only
        $toFallback = array_filter([
            'name'  => $customer?->name ?? null,
            'email' => $customer?->email ?? null,
        ]);

        // Use existing invoice to data; if empty, fallback to customer name and email only
        $toData = ! empty($party('to')) ? $party('to') : $toFallback;

        $logo = DB::table('settings')->value('site_logo') ?? ($config['site_logo'] ?? null);

        return Inertia::render('shared/invoice', [
            'invoice' => [
                'number'    => $row->invoice_number ? $row->invoice_prefix . $row->invoice_number : null,
                'id'        => $row->gobiz_transaction_id,
                'reference' => $row->transaction_id ?: $row->gobiz_transaction_id,
                'date'      => $row->created_at?->toIso8601String(),
                'status'    => 'success',
                'method'    => $row->payment_gateway_name,
                'currency'  => $row->transaction_currency,
                'logo'      => $logo ? asset($logo) : null,
                'from'      => $fromData,
                'to'        => $toData,
                'item'      => ['name' => $row->desciption, 'description' => null, 'price' => (float) ($invoice['subtotal'] ?? 0), 'quantity' => 1],
                'subtotal'  => (float) ($invoice['subtotal'] ?? 0),
                'taxName'   => $invoice['tax_name'] ?? ($config['tax_name'] ?? null),
                'taxRate'   => $invoice['tax_value'] ?? ($config['tax_value'] ?? null),
                'tax'       => (float) ($invoice['tax_amount'] ?? 0),
                'coupon'    => $invoice['applied_coupon'] ?? null,
                // discounted_price holds the discount amount
                'discount'  => ! empty($invoice['applied_coupon']) ? max(0, (float) ($invoice['discounted_price'] ?? 0)) : 0,
                'total'     => (float) ($invoice['invoice_amount'] ?? $row->transaction_amount),
            ],
            'back' => ['label' => __('Transactions'), 'href' => route('admin.transactions.index')],
        ]);
    }

    /** Mark as paid (activates the plan) or as pending / failed / cancelled. */
    public function transactionStatus(Request $request, $id, $status)
    {
        $request->merge(['status' => strtoupper($status)])->validate(['status' => ['required', Rule::in(['SUCCESS', 'PENDING', 'FAILED', 'CANCELLED'])]]);
        $status      = strtoupper($status);
        $transaction = Transaction::where('gobiz_transaction_id', $id)->where('status', 1)->firstOrFail();

        if ($status === $transaction->payment_status) {
            return back();
        }

        if ($status === 'SUCCESS') {
            $result = $this->plans->confirmPayment($transaction);

            if (! $result) {
                return back()->with('failed', __('The plan or customer of this payment no longer exists.'));
            }

            return back()->with('success', __('Payment confirmed. The plan is active until :date.', ['date' => formatDateOnlyForUser($result['validUntil'], true)])
                . ($result['cardsDeactivated'] ? ' ' . __('Their business cards were set to inactive.') : ''));
        }

        // Leaving "paid" removes the invoice number; the customer's plan is not changed.
        $transaction->forceFill([
            'payment_status' => $status,
            'invoice_prefix' => $transaction->payment_status === 'SUCCESS' ? null : $transaction->invoice_prefix,
            'invoice_number' => $transaction->payment_status === 'SUCCESS' ? null : $transaction->invoice_number,
        ])->save();

        return back()->with('success', __('Payment status updated.'));
    }

    // Same as transactionStatus (kept for old links)
    public function offlineTransactionStatus(Request $request, $id, $status)
    {
        return $this->transactionStatus($request, $id, $status);
    }
}
