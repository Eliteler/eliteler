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
use App\NfcCardKey;
use App\NfcCardOrder;
use App\NfcCardOrderTransaction;
use App\Services\Admin\Nfc\NfcKeyService;
use App\Services\Admin\Nfc\NfcOrderService;
use App\Support\Admin\AdminPermissions;
use App\Support\Admin\AdminSharedProps;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class NfcCardOrderController extends Controller
{
    /** Order list tabs => order_status values */
    private const TABS = [
        'all'         => null,
        'new'         => ['processing'],
        'in_progress' => ['printing process begun', 'shipped', 'out for delivery'],
        'hold'        => ['hold'],
        'delivered'   => ['delivered'],
        'unpaid'      => ['pending'],
        'cancelled'   => ['cancelled'],
    ];

    public function __construct(private NfcOrderService $orders, private NfcKeyService $keys)
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

        $base = fn () => NfcCardOrder::query()
            ->join('nfc_card_order_transactions', 'nfc_card_order_transactions.nfc_card_order_id', '=', 'nfc_card_orders.nfc_card_order_id')
            ->leftJoin('nfc_card_designs', 'nfc_card_designs.nfc_card_id', '=', 'nfc_card_orders.nfc_card_id')
            ->leftJoin('users', 'users.id', '=', 'nfc_card_orders.user_id')
            ->where('nfc_card_order_transactions.status', '!=', 2)
            ->when($search !== '', fn ($q) => $q->where(function ($q) use ($search) {
                $q->where('nfc_card_orders.nfc_card_order_id', 'like', "%{$search}%")
                    ->orWhere('users.name', 'like', "%{$search}%")
                    ->orWhere('users.email', 'like', "%{$search}%")
                    ->orWhere('nfc_card_designs.nfc_card_name', 'like', "%{$search}%");
            }));

        $orders = $base()
            ->when(self::TABS[$tab], fn ($q, $statuses) => $q->whereIn('nfc_card_orders.order_status', $statuses))
            ->select([
                'nfc_card_orders.nfc_card_order_id', 'nfc_card_orders.order_details', 'nfc_card_orders.order_status',
                'nfc_card_orders.nfc_card_logo', 'nfc_card_orders.created_at',
                'nfc_card_order_transactions.amount', 'nfc_card_order_transactions.payment_status', 'nfc_card_order_transactions.payment_method',
                'nfc_card_designs.nfc_card_name', 'nfc_card_designs.nfc_card_front_image',
                'users.user_id as customer_id', 'users.name as customer_name', 'users.email as customer_email',
            ])
            ->orderByDesc('nfc_card_orders.created_at')
            ->paginate(20)
            ->withQueryString()
            ->through(function ($row) {
                $details = json_decode($row->order_details ?? '', true) ?: [];

                return [
                    'id'       => $row->nfc_card_order_id,
                    'date'     => $row->created_at ? Carbon::parse($row->created_at)->toIso8601String() : null,
                    'item'     => $details['order_item'] ?? $row->nfc_card_name,
                    'image'    => $row->nfc_card_front_image ? asset($row->nfc_card_front_image) : null,
                    'quantity' => max((int) ($details['order_quantity'] ?? 1), 1),
                    'amount'   => AdminPermissions::amount((float) $row->amount),
                    'payment'  => $row->payment_status,
                    'method'   => $row->payment_method,
                    'delivery' => $row->order_status,
                    'hasLogo'  => ! empty($row->nfc_card_logo),
                    'customer' => $row->customer_id ? ['id' => $row->customer_id, 'name' => $row->customer_name, 'email' => $row->customer_email] : null,
                ];
            });

        $countSql = collect(self::TABS)->map(function ($statuses, $key) {
            if ($statuses === null) {
                return 'COUNT(*) as `all`';
            }

            $list = implode(',', array_map(fn ($s) => DB::getPdo()->quote($s), $statuses));

            return "SUM(nfc_card_orders.order_status IN ({$list})) as `{$key}`";
        })->implode(', ');

        $counts = $base()->selectRaw($countSql)->first();

        return Inertia::render('nfc/orders', [
            'orders' => $orders,
            'tab'    => $tab,
            'search' => $search,
            'counts' => collect(array_keys(self::TABS))->mapWithKeys(fn ($key) => [$key => (int) $counts->{$key}]),
        ]);
    }

    public function show(Request $request, $orderId)
    {
        $order = NfcCardOrder::where('nfc_card_order_id', $orderId)->first();

        if (! $order) {
            return redirect()->route('admin.orders')->with('failed', __('Not Found!'));
        }

        $transaction = NfcCardOrderTransaction::where('nfc_card_order_id', $order->nfc_card_order_id)->first();
        $design      = DB::table('nfc_card_designs')->where('nfc_card_id', $order->nfc_card_id)->first();
        $customer    = DB::table('users')->where('id', $order->user_id)->first(['user_id', 'name', 'email']);
        $details     = NfcOrderService::details($order);
        $address     = json_decode($order->delivery_address ?? '', true) ?: [];
        $paid        = $transaction?->payment_status === 'success';
        $permissions = \App\Support\Admin\AdminPermissions::for($request->user());

        $keyRows = NfcCardKey::whereIn('unqiue_key', NfcOrderService::orderKeys($order))
            ->leftJoin('business_cards', 'business_cards.card_id', '=', 'nfc_card_keys.card_id')
            ->get(['nfc_card_keys.unqiue_key', 'nfc_card_keys.link_status', 'business_cards.title', 'business_cards.card_url'])
            ->keyBy('unqiue_key');

        $pick = fn (string $prefix) => array_filter([
            'name'    => $address["{$prefix}_name"] ?? null,
            'address' => collect([$address["{$prefix}_address"] ?? null, $address["{$prefix}_city"] ?? null, $address["{$prefix}_state"] ?? null, $address["{$prefix}_zipcode"] ?? null, $address["{$prefix}_country"] ?? null])->filter()->implode(', ') ?: null,
            'phone'   => $address["{$prefix}_phone"] ?? null,
            'email'   => $address["{$prefix}_email"] ?? null,
        ]);

        $beforeCoupon = (float) ($details['discounted_price'] ?? 0);
        $total        = (float) ($details['invoice_amount'] ?? $transaction?->amount ?? 0);

        return Inertia::render('nfc/order', [
            'order' => [
                'id'        => $order->nfc_card_order_id,
                'date'      => $order->created_at?->toIso8601String(),
                'updated'   => $order->updated_at?->toIso8601String(),
                'delivery'  => $order->order_status,
                'note'      => in_array($order->delivery_note, [null, '', '-'], true) ? null : $order->delivery_note,
                'logo'      => $order->nfc_card_logo ? asset($order->nfc_card_logo) : null,
                'tracking'  => array_filter([
                    'number'  => $details['tracking_number'] ?? null,
                    'courier' => $details['courier_partner'] ?? null,
                    'message' => $details['delivery_message'] ?? null,
                ]),
                'item' => [
                    'name'        => $details['order_item'] ?? $design?->nfc_card_name,
                    'description' => $details['order_description'] ?? null,
                    'image'       => $design?->nfc_card_front_image ? asset($design->nfc_card_front_image) : null,
                    'price'       => AdminPermissions::amount((float) ($details['price'] ?? $design?->nfc_card_price ?? 0)),
                    'quantity'    => NfcOrderService::quantity($order),
                ],
                // No pricing at all when revenue is hidden
                'pricing' => AdminPermissions::hidesRevenue($request->user()) ? null : [
                    'subtotal'  => (float) ($details['subtotal'] ?? 0),
                    'taxName'   => $details['tax_name'] ?? null,
                    'taxRate'   => $details['tax_value'] ?? null,
                    'tax'       => (float) ($details['tax_amount'] ?? 0),
                    'coupon'    => $details['applied_coupon'] ?? null,
                    'discount'  => ! empty($details['applied_coupon']) ? max(0, $beforeCoupon - $total) : 0,
                    'total'     => $total,
                ],
                'payment' => $transaction ? [
                    'id'        => $transaction->nfc_card_order_transaction_id,
                    'reference' => $transaction->payment_transaction_id,
                    'method'    => $transaction->payment_method,
                    'status'    => $transaction->payment_status,
                    'currency'  => $transaction->currency,
                    'invoice'   => $paid && ($permissions['nfc_card_order_transactions'] ?? false)
                        ? route('admin.transaction.show', $transaction->nfc_card_order_transaction_id) : null,
                ] : null,
                'billing'  => $pick('billing'),
                'shipping' => $pick('shipping'),
                'taxId'    => $address['vat_number'] ?? null,
                'customer' => $customer ? ['id' => $customer->user_id, 'name' => $customer->name, 'email' => $customer->email] : null,
                'keys'     => collect(NfcOrderService::orderKeys($order))->map(fn ($key) => [
                    'key'      => $key,
                    'url'      => $this->keys->readUrl($key),
                    'linked'   => ($keyRows[$key]->link_status ?? null) === 'linked',
                    'card'     => isset($keyRows[$key]) && $keyRows[$key]->card_url ? ['title' => $keyRows[$key]->title, 'url' => url($keyRows[$key]->card_url)] : null,
                    'greeting' => route('admin.key.greeting.letter', $key),
                ])->values(),
                'missingKeys' => $paid ? max(0, NfcOrderService::quantity($order) - count(NfcOrderService::orderKeys($order))) : 0,
                'paid'        => $paid,
            ],
            'statuses'   => NfcOrderService::DELIVERY_STATUSES,
            'openDialog' => $request->query('dialog') === 'delivery' ? 'delivery' : null,
        ]);
    }

    // Old tracking page: open the delivery dialog on the order page
    public function updateOrder($orderId)
    {
        return redirect()->route('admin.order.show', ['order' => $orderId, 'dialog' => 'delivery']);
    }

    public function updatedOrder(Request $request)
    {
        $data = $request->validate([
            'order_id'         => 'required|string',
            'status'           => ['required', Rule::in(NfcOrderService::DELIVERY_STATUSES)],
            'tracking_number'  => 'nullable|string|max:191',
            'courier_partner'  => 'nullable|string|max:191',
            'delivery_message' => 'nullable|string|max:1000',
            'notify'           => 'boolean',
        ]);

        $order = NfcCardOrder::where('nfc_card_order_id', $data['order_id'])->first();

        if (! $order) {
            return back()->with('failed', __('Not Found!'));
        }

        $emailed = $this->orders->updateDelivery($order, $data + ['notify' => $request->boolean('notify', true)]);

        return back()->with(
            $emailed ? 'success' : 'failed',
            $emailed ? __('Delivery details updated.') : __('Delivery details updated, but the email to the customer could not be sent.')
        );
    }

    // Old "Write NFC" page: the links are on the order page
    public function writeToNfcCard($orderId)
    {
        return redirect()->route('admin.order.show', $orderId);
    }

    // Create the missing activation keys of a paid order
    public function generateKeys($orderId)
    {
        $order       = NfcCardOrder::where('nfc_card_order_id', $orderId)->firstOrFail();
        $transaction = NfcCardOrderTransaction::where('nfc_card_order_id', $orderId)->first();

        if ($transaction?->payment_status !== 'success') {
            return back()->with('failed', __('NFC links can only be created for paid orders.'));
        }

        $created = $this->orders->ensureKeys($order);

        return back()->with('success', trans_choice(':count NFC link created.|:count NFC links created.', $created, ['count' => $created]));
    }

    // Old order greeting letter: open the letter of the order's first key
    public function greetingLetter($orderId)
    {
        $order = NfcCardOrder::where('nfc_card_order_id', $orderId)->first();
        $key   = $order ? (NfcOrderService::orderKeys($order)[0] ?? null) : null;

        return $key
            ? redirect()->route('admin.key.greeting.letter', $key)
            : redirect()->route('admin.orders')->with('failed', __('This order has no activation key yet.'));
    }

    // Email the greeting letter for a key
    public function sendGreetingLetter(Request $request, $key)
    {
        $data = $request->validate([
            'email'         => 'required|email',
            'customer_name' => 'nullable|string|max:191',
            'qr_code'       => 'nullable|string',
        ]);

        $nfcKey = NfcCardKey::where('unqiue_key', $key)->firstOrFail();
        $name   = $data['customer_name'] ?? $this->keys->customerNameFor($data['email']) ?? $this->keys->ownerName($nfcKey);
        $sent   = $this->keys->sendGreeting($nfcKey, $data['email'], $name, $data['qr_code'] ?? null);

        return back()->with(
            $sent ? 'success' : 'failed',
            $sent ? __('Greeting letter sent to :email.', ['email' => $data['email']]) : __('The greeting letter could not be sent. Check the mail settings.')
        );
    }
}
