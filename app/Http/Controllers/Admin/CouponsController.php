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

use App\Coupon;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

/**
 * Discount coupons. coupons.used_for: plan, nfc, ai_credits; plan_id holds a
 * JSON array of the item ids it applies to (["all"] = every item).
 * status: 1 active, 0 disabled, 2 deleted.
 *
 * applied_coupons rows are written at checkout (status 1 once paid). Plan
 * checkouts store the coupon_id, NFC and AI credit checkouts store the code.
 */
class CouponsController extends Controller
{
    private const TABS = ['active', 'expired', 'disabled', 'all'];

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function indexCoupons(Request $request)
    {
        $tab    = in_array($request->query('tab'), self::TABS, true) ? $request->query('tab') : 'all';
        $search = trim((string) $request->query('search', ''));
        $now    = Carbon::now();

        $base = fn () => Coupon::where('status', '!=', 2)
            ->when($search !== '', fn ($q) => $q->where(fn ($q) => $q->where('coupon_code', 'like', "%{$search}%")->orWhere('coupon_desc', 'like', "%{$search}%")));

        $coupons = $base()
            ->when($tab === 'active', fn ($q) => $q->where('status', 1)->where(fn ($q) => $q->whereNull('coupon_expired_on')->orWhere('coupon_expired_on', '>=', $now)))
            ->when($tab === 'expired', fn ($q) => $q->where('coupon_expired_on', '<', $now))
            ->when($tab === 'disabled', fn ($q) => $q->where('status', 0))
            ->orderByDesc('id')
            ->get();

        $usage = $this->usage($coupons);
        $names = $this->itemNames();

        $counts = $base()->selectRaw('COUNT(*) as `all`, SUM(status = 1 AND (coupon_expired_on IS NULL OR coupon_expired_on >= ?)) as active, SUM(coupon_expired_on < ?) as expired, SUM(status = 0) as disabled', [$now, $now])->first();

        return Inertia::render('coupons/index', [
            'coupons' => $coupons->map(fn (Coupon $coupon) => [
                ...$this->present($coupon, $names),
                'redeemed' => $usage[$coupon->coupon_id]['redeemed'] ?? 0,
                'customers'=> $usage[$coupon->coupon_id]['customers'] ?? 0,
            ]),
            'tab'     => $tab,
            'search'  => $search,
            'counts'  => collect(self::TABS)->mapWithKeys(fn ($k) => [$k => (int) $counts->{$k}]),
            'items'   => $this->itemOptions(),
            'open'    => $request->query('edit') ?: ($request->boolean('new') ? 'new' : null),
        ]);
    }

    // Old pages: open the dialog on the coupons page
    public function createCoupon()
    {
        return redirect()->route('admin.coupons', ['new' => 1]);
    }

    public function editCoupon($id)
    {
        return redirect()->route('admin.coupons', ['edit' => $id]);
    }

    public function storeCoupon(Request $request)
    {
        $data = $this->validated($request);

        $coupon             = new Coupon();
        $coupon->coupon_id  = uniqid();
        $this->fill($coupon, $data)->save();

        return back()->with('success', __('Coupon :code created.', ['code' => $coupon->coupon_code]));
    }

    public function updateCoupon(Request $request, $id)
    {
        $coupon = Coupon::where('coupon_id', $id)->firstOrFail();
        $data   = $this->validated($request, $coupon);

        $this->fill($coupon, $data)->save();

        return back()->with('success', __('Coupon updated.'));
    }

    public function updateCouponStatus(Request $request)
    {
        $coupon = Coupon::where('coupon_id', (string) $request->input('id'))->firstOrFail();
        $coupon->status = (int) $coupon->status === 1 ? 0 : 1;
        $coupon->save();

        return back()->with('success', $coupon->status ? __('Coupon enabled.') : __('Coupon disabled.'));
    }

    public function deleteCoupon(Request $request)
    {
        $coupon = Coupon::where('coupon_id', (string) $request->input('id'))->firstOrFail();
        $coupon->status = 2;
        $coupon->save();

        return back()->with('success', __('Coupon deleted.'));
    }

    // Usage of one coupon
    public function statisticsCoupon($id)
    {
        $coupon = Coupon::where('coupon_id', $id)->first() ?? Coupon::where('coupon_code', $id)->where('status', '!=', 2)->latest('id')->first();

        if (! $coupon) {
            return redirect()->route('admin.coupons')->with('failed', __('Not Found!'));
        }

        [$table, $refs] = match ($coupon->used_for) {
            'nfc'        => ['nfc_card_order_transactions', ['payment_transaction_id', 'nfc_card_order_transaction_id']],
            'ai_credits' => ['ai_credits_transactions', ['payment_transaction_id', 'ai_credits_transaction_id']],
            default      => ['transactions', ['transaction_id', 'gobiz_transaction_id']],
        };
        $amountColumn = $coupon->used_for === 'plan' ? 'transaction_amount' : 'amount';

        $rows = $this->appliedQuery($coupon)
            ->leftJoin('users', 'users.id', '=', 'applied_coupons.user_id')
            ->orderByDesc('applied_coupons.id')
            ->limit(200)
            ->get(['applied_coupons.transaction_id', 'applied_coupons.status', 'applied_coupons.created_at', 'users.user_id as customer_id', 'users.name', 'users.email']);

        $refs   = collect($refs);
        $paid   = DB::table($table)->where(fn ($q) => $refs->each(fn ($c) => $q->orWhereIn($c, $rows->pluck('transaction_id'))))
            ->get([...$refs->all(), $amountColumn . ' as amount', 'invoice_details']);
        $lookup = [];
        foreach ($paid as $t) {
            foreach ($refs as $c) {
                $lookup[$t->{$c}] = $t;
            }
        }

        return Inertia::render('coupons/usage', [
            'coupon' => $this->present($coupon, $this->itemNames()),
            'uses'   => $rows->map(function ($row) use ($lookup) {
                $t       = $lookup[$row->transaction_id] ?? null;
                $invoice = $t ? (json_decode($t->invoice_details ?? '', true) ?: []) : [];

                return [
                    'reference' => $row->transaction_id,
                    'redeemed'  => (int) $row->status === 1,
                    'date'      => $row->created_at ? Carbon::parse($row->created_at)->toIso8601String() : null,
                    'customer'  => $row->customer_id ? ['id' => $row->customer_id, 'name' => $row->name, 'email' => $row->email] : null,
                    'paid'      => $t ? (float) $t->amount : null,
                    'discount'  => $t && isset($invoice['discounted_price'], $invoice['invoice_amount']) ? max(0, (float) $invoice['discounted_price'] - (float) $invoice['invoice_amount']) : null,
                ];
            }),
        ]);
    }

    // Kept for old clients: is the code taken? (JSON)
    public function codeExists(Request $request)
    {
        return response()->json(['success' => $this->codeTaken((string) $request->input('code'), (string) $request->input('used_for'))]);
    }

    private function validated(Request $request, ?Coupon $coupon = null): array
    {
        $request->merge(['code' => Str::upper(trim((string) $request->input('code')))]);

        $data = $request->validate([
            'used_for'    => ['required', Rule::in(['plan', 'nfc', 'ai_credits'])],
            'plan_id'     => 'required|array|min:1',
            'plan_id.*'   => 'string',
            'code'        => 'required|string|max:50|regex:/^[A-Z0-9_-]+$/',
            'description' => 'nullable|string|max:191',
            'type'        => 'required|in:percentage,fixed',
            'discount'    => 'required|numeric|gt:0' . ($request->input('type') === 'percentage' ? '|max:100' : ''),
            'validity'    => 'required|date' . ($coupon ? '' : '|after_or_equal:today'),
            'user_limit'  => 'required|integer|min:1',
            'total_limit' => 'required|integer|min:1|gte:user_limit',
        ], [
            'code.regex'       => __('Use only letters, numbers, dashes and underscores.'),
            'total_limit.gte'  => __('The total limit can’t be lower than the limit per customer.'),
            'plan_id.required' => __('Choose at least one item, or “All”.'),
        ], [
            'plan_id'     => __('items'),
            'validity'    => __('expiry date'),
            'user_limit'  => __('uses per customer'),
            'total_limit' => __('total uses'),
        ]);

        if ($this->codeTaken($data['code'], $data['used_for'], $coupon?->coupon_id)) {
            throw \Illuminate\Validation\ValidationException::withMessages(['code' => __('An active coupon already uses this code.')]);
        }

        return $data;
    }

    private function codeTaken(string $code, string $usedFor, ?string $exceptId = null): bool
    {
        return Coupon::where('coupon_code', Str::upper($code))
            ->where('used_for', $usedFor)
            ->where('status', '!=', 2)
            ->when($exceptId, fn ($q) => $q->where('coupon_id', '!=', $exceptId))
            ->where(fn ($q) => $q->whereNull('coupon_expired_on')->orWhere('coupon_expired_on', '>=', Carbon::now()))
            ->exists();
    }

    private function fill(Coupon $coupon, array $data): Coupon
    {
        $items = in_array('all', $data['plan_id'], true) ? ['all'] : array_values($data['plan_id']);

        $coupon->used_for                 = $data['used_for'];
        $coupon->plan_id                  = json_encode($items);
        $coupon->coupon_code              = $data['code'];
        $coupon->coupon_desc              = $data['description'] ?? null;
        $coupon->coupon_type              = $data['type'];
        $coupon->coupon_amount            = $data['discount'];
        $coupon->coupon_expired_on        = Carbon::parse($data['validity'])->format('Y-m-d') . ' 23:59:59';
        $coupon->coupon_user_usage_limit  = $data['user_limit'];
        $coupon->coupon_total_usage_limit = $data['total_limit'];

        return $coupon;
    }

    private function appliedQuery(Coupon $coupon)
    {
        return DB::table('applied_coupons')->where(fn ($q) => $q
            ->where('applied_coupons.coupon_id', $coupon->coupon_id)
            ->when($coupon->used_for !== 'plan', fn ($q) => $q->orWhere('applied_coupons.coupon_id', $coupon->coupon_code)));
    }

    /** @return array<string, array{redeemed: int, customers: int}> */
    private function usage($coupons): array
    {
        $out = [];
        foreach ($coupons as $coupon) {
            $row = $this->appliedQuery($coupon)->where('status', 1)->selectRaw('COUNT(*) as n, COUNT(DISTINCT user_id) as u')->first();
            $out[$coupon->coupon_id] = ['redeemed' => (int) $row->n, 'customers' => (int) $row->u];
        }

        return $out;
    }

    private function itemNames(): array
    {
        return [
            'plan'       => DB::table('plans')->pluck('plan_name', 'plan_id')->all(),
            'nfc'        => DB::table('nfc_card_designs')->pluck('nfc_card_name', 'nfc_card_id')->all(),
            'ai_credits' => DB::table('ai_credits_plans')->pluck('plan_name', 'ai_credits_plan_id')->all(),
        ];
    }

    private function itemOptions(): array
    {
        $map = fn ($rows, $id, $name, $price) => $rows->map(fn ($r) => ['id' => $r->{$id}, 'name' => $r->{$name}, 'price' => (float) $r->{$price}])->values();

        return [
            'plan'       => $map(DB::table('plans')->where('status', 1)->orderBy('plan_price')->get(), 'plan_id', 'plan_name', 'plan_price'),
            'nfc'        => $map(DB::table('nfc_card_designs')->where('status', 1)->get(), 'nfc_card_id', 'nfc_card_name', 'nfc_card_price'),
            'ai_credits' => $map(DB::table('ai_credits_plans')->where('status', 'active')->orderBy('plan_price')->get(), 'ai_credits_plan_id', 'plan_name', 'plan_price'),
        ];
    }

    private function present(Coupon $coupon, array $names): array
    {
        $ids = json_decode($coupon->plan_id ?? '', true);
        $ids = is_array($ids) ? $ids : ($coupon->plan_id ? [$coupon->plan_id] : ['all']);
        $expires = $coupon->coupon_expired_on ? Carbon::parse($coupon->coupon_expired_on) : null;

        return [
            'id'          => $coupon->coupon_id,
            'code'        => $coupon->coupon_code,
            'description' => $coupon->coupon_desc,
            'usedFor'     => $coupon->used_for,
            'itemIds'     => $ids,
            'items'       => in_array('all', $ids, true) ? null : array_values(array_filter(array_map(fn ($id) => $names[$coupon->used_for][$id] ?? null, $ids))),
            'type'        => $coupon->coupon_type,
            'amount'      => (float) $coupon->coupon_amount,
            'expires'     => $expires?->toIso8601String(),
            'expired'     => $expires ? $expires->isPast() : false,
            'perCustomer' => (int) $coupon->coupon_user_usage_limit,
            'total'       => (int) $coupon->coupon_total_usage_limit,
            'active'      => (int) $coupon->status === 1,
        ];
    }
}
