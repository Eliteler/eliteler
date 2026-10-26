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
use App\NfcCardOrderTransaction;
use App\Services\Admin\Nfc\NfcOrderService;
use App\Support\Admin\AdminSharedProps;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class NfcCardOrderTransactionController extends Controller
{
    private const TABS = ['all' => null, 'success' => 'success', 'pending' => 'pending', 'failed' => 'failed'];

    public function __construct(private NfcOrderService $orders)
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            abort_unless(AdminSharedProps::features()['nfc'], 404);

            return $next($request);
        });
    }

    public function index(Request $request)
    {
        $tab    = array_key_exists($request->query('tab'), self::TABS) ? $request->query('tab') : 'all';
        $search = trim((string) $request->query('search', ''));

        $base = fn () => NfcCardOrderTransaction::query()
            ->join('nfc_card_orders', 'nfc_card_orders.nfc_card_order_id', '=', 'nfc_card_order_transactions.nfc_card_order_id')
            ->leftJoin('users', 'users.id', '=', 'nfc_card_orders.user_id')
            ->where('nfc_card_order_transactions.status', '!=', 2)
            ->when($search !== '', fn ($q) => $q->where(function ($q) use ($search) {
                $q->where('nfc_card_order_transactions.nfc_card_order_transaction_id', 'like', "%{$search}%")
                    ->orWhere('nfc_card_order_transactions.payment_transaction_id', 'like', "%{$search}%")
                    ->orWhere('nfc_card_orders.nfc_card_order_id', 'like', "%{$search}%")
                    ->orWhere('users.name', 'like', "%{$search}%")
                    ->orWhere('users.email', 'like', "%{$search}%");
            }));

        $transactions = $base()
            ->when(self::TABS[$tab], fn ($q, $status) => $q->where('nfc_card_order_transactions.payment_status', $status))
            ->select([
                'nfc_card_order_transactions.nfc_card_order_transaction_id', 'nfc_card_order_transactions.payment_transaction_id',
                'nfc_card_order_transactions.payment_method', 'nfc_card_order_transactions.amount', 'nfc_card_order_transactions.payment_status',
                'nfc_card_order_transactions.invoice_prefix', 'nfc_card_order_transactions.invoice_number', 'nfc_card_order_transactions.created_at',
                'nfc_card_orders.nfc_card_order_id', 'nfc_card_orders.order_status',
                'users.user_id as customer_id', 'users.name as customer_name', 'users.email as customer_email',
            ])
            ->orderByDesc('nfc_card_order_transactions.created_at')
            ->paginate(20)
            ->withQueryString()
            ->through(fn ($row) => [
                'id'        => $row->nfc_card_order_transaction_id,
                'reference' => $row->payment_transaction_id !== $row->nfc_card_order_transaction_id ? $row->payment_transaction_id : null,
                'invoice'   => $row->invoice_number ? $row->invoice_prefix . $row->invoice_number : null,
                'method'    => $row->payment_method,
                'amount'    => (float) $row->amount,
                'status'    => $row->payment_status,
                'orderId'   => $row->nfc_card_order_id,
                'delivery'  => $row->order_status,
                'date'      => $row->created_at ? Carbon::parse($row->created_at)->toIso8601String() : null,
                'customer'  => $row->customer_id ? ['id' => $row->customer_id, 'name' => $row->customer_name, 'email' => $row->customer_email] : null,
            ]);

        $counts = $base()
            ->selectRaw("COUNT(*) as `all`, SUM(payment_status = 'success') as success, SUM(payment_status = 'pending') as pending, SUM(payment_status = 'failed') as failed")
            ->first();

        return Inertia::render('nfc/transactions', [
            'transactions' => $transactions,
            'tab'          => $tab,
            'search'       => $search,
            'counts'       => collect(array_keys(self::TABS))->mapWithKeys(fn ($key) => [$key => (int) $counts->{$key}]),
        ]);
    }

    // Invoice
    public function show($transaction)
    {
        $row = NfcCardOrderTransaction::query()
            ->join('nfc_card_orders', 'nfc_card_orders.nfc_card_order_id', '=', 'nfc_card_order_transactions.nfc_card_order_id')
            ->where(fn ($q) => $q->where('nfc_card_order_transactions.nfc_card_order_transaction_id', $transaction)
                ->orWhere('nfc_card_order_transactions.payment_transaction_id', $transaction))
            ->select('nfc_card_order_transactions.*', 'nfc_card_orders.order_details', 'nfc_card_orders.user_id')
            ->first();

        if (! $row) {
            return redirect()->route('admin.transactions')->with('failed', __('Not Found!'));
        }

        $invoice = json_decode($row->invoice_details ?? '', true) ?: [];
        $details = json_decode($row->order_details ?? '', true) ?: [];
        $logo    = DB::table('settings')->value('site_logo');

        return Inertia::render('shared/invoice', [
            'invoice' => [
                'number'    => $row->invoice_number ? $row->invoice_prefix . $row->invoice_number : null,
                'id'        => $row->nfc_card_order_transaction_id,
                'reference' => $row->payment_transaction_id,
                'orderId'   => $row->nfc_card_order_id,
                'date'      => $row->created_at?->toIso8601String(),
                'status'    => $row->payment_status,
                'method'    => $row->payment_method,
                'currency'  => $row->currency,
                'logo'      => $logo ? asset($logo) : null,
                'from'      => $this->party($invoice, 'from'),
                'to'        => $this->party($invoice, 'to'),
                'item'      => [
                    'name'        => $details['order_item'] ?? '',
                    'description' => $details['order_description'] ?? null,
                    'price'       => (float) ($details['price'] ?? 0),
                    'quantity'    => max((int) ($details['order_quantity'] ?? 1), 1),
                ],
                'subtotal' => (float) ($invoice['subtotal'] ?? 0),
                'taxName'  => $invoice['tax_name'] ?? null,
                'taxRate'  => $invoice['tax_value'] ?? null,
                'taxType'  => $invoice['tax_type'] ?? null,
                'tax'      => (float) ($invoice['tax_amount'] ?? 0),
                'coupon'   => $invoice['applied_coupon'] ?? null,
                'discount' => ! empty($invoice['applied_coupon']) ? max(0, (float) ($invoice['discounted_price'] ?? 0) - (float) ($invoice['invoice_amount'] ?? 0)) : 0,
                'total'    => (float) ($invoice['invoice_amount'] ?? $row->amount),
            ],
            'back' => ['label' => __('NFC payments'), 'href' => route('admin.transactions')],
        ]);
    }

    // Mark as paid / pending / failed
    public function action(Request $request)
    {
        $data = $request->validate([
            'id'     => 'required|string',
            'status' => 'required|in:success,pending,failed',
        ]);

        $transaction = NfcCardOrderTransaction::where('nfc_card_order_transaction_id', $data['id'])
            ->orWhere('payment_transaction_id', $data['id'])
            ->first();

        if (! $transaction) {
            return back()->with('failed', __('Not Found!'));
        }

        $this->orders->setPaymentStatus($transaction, $data['status']);

        return back()->with('success', match ($data['status']) {
            'success' => __('Payment marked as paid. The order is now being processed.'),
            'failed'  => __('Payment marked as failed. The order was cancelled.'),
            'pending' => __('Payment marked as pending.'),
        });
    }

    private function party(array $invoice, string $side): array
    {
        return array_filter([
            'name'    => $invoice["{$side}_billing_name"] ?? null,
            'address' => collect([$invoice["{$side}_billing_address"] ?? null, $invoice["{$side}_billing_city"] ?? null, $invoice["{$side}_billing_state"] ?? null, $invoice["{$side}_billing_zipcode"] ?? null, $invoice["{$side}_billing_country"] ?? null])->filter()->implode(', ') ?: null,
            'phone'   => $invoice["{$side}_billing_phone"] ?? null,
            'email'   => $invoice["{$side}_billing_email"] ?? null,
            'taxId'   => $invoice["{$side}_vat_number"] ?? null,
        ]);
    }
}
