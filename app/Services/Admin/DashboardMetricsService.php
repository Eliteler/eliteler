<?php

namespace App\Services\Admin;

use App\AiCreditsTransaction;
use App\BusinessCard;
use App\NfcCardOrderTransaction;
use App\Transaction;
use App\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

/**
 * Admin dashboard metrics.
 *
 * Data notes (verified against the live schema, see README "Database rules"):
 * - Amounts are stored in the base currency (plan price incl. tax), whatever
 *   transactions.transaction_currency says, so they can be summed.
 * - transactions.transaction_amount is varchar: use PLAN_AMOUNT.
 * - Revenue = money actually collected: successful plan payments excluding the
 *   FREE and TRIAL gateways (activations that carry the list price but no
 *   payment), successful NFC orders and paid AI credit purchases.
 */
class DashboardMetricsService
{
    public const PERIODS = ['7d', '30d', '90d', '12m'];

    public const DEFAULT_PERIOD = '30d';

    private const PLAN_AMOUNT = '(transaction_amount + 0)';

    private const NON_PAYING_GATEWAYS = ['FREE', 'TRIAL'];

    private const PAID_PLAN = "CAST(JSON_UNQUOTE(JSON_EXTRACT(plan_details, '$.plan_price')) AS DECIMAL(15,2)) > 0";

    /**
     * Current and previous period windows plus chart buckets.
     *
     * @return array{start: Carbon, end: Carbon, prevStart: Carbon, prevEnd: Carbon, buckets: array<int, array{key: string, label: string}>, bucket: string}
     */
    public function period(string $period): array
    {
        $now = Carbon::now();

        // [first day of the period, bucket size, previous period start]
        [$start, $bucket, $prevStart] = match ($period) {
            '7d'    => [$s = $now->copy()->subDays(6)->startOfDay(), 'day', $s->copy()->subDays(7)],
            '90d'   => [$s = $now->copy()->subDays(89)->startOfDay(), 'week', $s->copy()->subDays(90)],
            '12m'   => [$s = $now->copy()->subMonthsNoOverflow(11)->startOfMonth(), 'month', $s->copy()->subMonthsNoOverflow(12)],
            default => [$s = $now->copy()->subDays(29)->startOfDay(), 'day', $s->copy()->subDays(30)],
        };

        $end     = $now->copy()->endOfDay();
        $prevEnd = $start->copy()->subSecond();

        $buckets = [];
        for ($cursor = $start->copy(); $cursor->lte($end); $cursor = $this->nextBucket($cursor, $bucket)) {
            $buckets[] = [
                'key'   => $this->bucketKey($cursor, $bucket),
                'label' => match ($bucket) {
                    'month' => $cursor->format('M Y'),
                    default => $cursor->format('d M'),
                },
            ];
        }

        return compact('start', 'end', 'prevStart', 'prevEnd', 'buckets', 'bucket');
    }

    /**
     * Headline KPIs for the selected period, each with the previous period value.
     * Without revenue the revenue key is left out entirely.
     */
    public function kpis(array $p, bool $withRevenue = true): array
    {
        $current  = [$p['start'], $p['end']];
        $previous = [$p['prevStart'], $p['prevEnd']];

        $revenue = fn (array $range) => $this->revenueTotal($range);

        $signups = User::where('role_id', 2)
            ->selectRaw('SUM(created_at BETWEEN ? AND ?) as current', self::range($current))
            ->selectRaw('SUM(created_at BETWEEN ? AND ?) as previous', self::range($previous))
            ->first();

        $subscriptions = $this->paidPlanPayments()
            ->selectRaw('SUM(created_at BETWEEN ? AND ?) as current', self::range($current))
            ->selectRaw('SUM(created_at BETWEEN ? AND ?) as previous', self::range($previous))
            ->first();

        $active = $this->activeCustomers()
            ->selectRaw('SUM(' . self::PAID_PLAN . ') as paid')
            ->selectRaw('COUNT(*) as total')
            ->first();

        $paidActive = (int) $active->paid;

        $kpis = $withRevenue ? [
            'revenue' => [
                'current'  => round($revenue($current), 2),
                'previous' => round($revenue($previous), 2),
            ],
        ] : [];

        return $kpis + [
            'signups' => [
                'current'  => (int) $signups->current,
                'previous' => (int) $signups->previous,
            ],
            'subscriptions' => [
                'current'  => (int) $subscriptions->current,
                'previous' => (int) $subscriptions->previous,
            ],
            'activeSubscribers' => [
                'paid'  => $paidActive,
                'free'  => (int) $active->total - $paidActive,
                'total' => (int) $active->total,
            ],
        ];
    }

    /**
     * Revenue per bucket, split by source.
     *
     * @return array<int, array{label: string, plans: float, nfc: float, ai: float}>
     */
    public function revenueSeries(array $p): array
    {
        $range  = self::range([$p['start'], $p['end']]);
        $series = $this->emptySeries($p, ['plans' => 0.0, 'nfc' => 0.0, 'ai' => 0.0]);

        $sources = [
            'plans' => $this->paidPlanPayments()->whereBetween('created_at', $range)
                ->selectRaw('DATE(created_at) as date, SUM(' . self::PLAN_AMOUNT . ') as total'),
            'nfc' => NfcCardOrderTransaction::where('payment_status', 'success')->whereBetween('created_at', $range)
                ->selectRaw('DATE(created_at) as date, SUM(amount) as total'),
            'ai' => AiCreditsTransaction::where('payment_status', 'paid')->whereBetween('created_at', $range)
                ->selectRaw('DATE(created_at) as date, SUM(amount) as total'),
        ];

        foreach ($sources as $source => $query) {
            foreach ($query->groupBy('date')->pluck('total', 'date') as $date => $total) {
                $key = $this->bucketKey(Carbon::parse($date), $p['bucket']);
                if (isset($series[$key])) {
                    $series[$key][$source] = round($series[$key][$source] + (float) $total, 2);
                }
            }
        }

        return array_values($series);
    }

    /**
     * New customers and new paid subscriptions per bucket.
     *
     * @return array<int, array{label: string, signups: int, subscriptions: int}>
     */
    public function growthSeries(array $p): array
    {
        $range  = self::range([$p['start'], $p['end']]);
        $series = $this->emptySeries($p, ['signups' => 0, 'subscriptions' => 0]);

        $sources = [
            'signups' => User::where('role_id', 2)->whereBetween('created_at', $range),
            'subscriptions' => $this->paidPlanPayments()->whereBetween('created_at', $range),
        ];

        foreach ($sources as $source => $query) {
            $counts = $query->selectRaw('DATE(created_at) as date, COUNT(*) as total')->groupBy('date')->pluck('total', 'date');
            foreach ($counts as $date => $total) {
                $key = $this->bucketKey(Carbon::parse($date), $p['bucket']);
                if (isset($series[$key])) {
                    $series[$key][$source] += (int) $total;
                }
            }
        }

        return array_values($series);
    }

    /**
     * Plan revenue by payment gateway in the period.
     *
     * @return array<int, array{name: string, revenue: float, payments: int}>
     */
    public function gateways(array $p): array
    {
        return $this->paidPlanPayments()
            ->whereBetween('created_at', self::range([$p['start'], $p['end']]))
            ->selectRaw('payment_gateway_name as name, SUM(' . self::PLAN_AMOUNT . ') as revenue, COUNT(*) as payments')
            ->groupBy('payment_gateway_name')
            ->orderByDesc('revenue')
            ->get()
            ->map(fn ($row) => [
                'name'     => $row->name,
                'revenue'  => round((float) $row->revenue, 2),
                'payments' => (int) $row->payments,
            ])
            ->all();
    }

    /**
     * Customer activity on the platform in the period vs the previous one.
     */
    public function usage(array $p): array
    {
        $current  = self::range([$p['start'], $p['end']]);
        $previous = self::range([$p['prevStart'], $p['prevEnd']]);

        $count = function ($query) use ($current, $previous) {
            $row = $query
                ->selectRaw('SUM(created_at BETWEEN ? AND ?) as current', $current)
                ->selectRaw('SUM(created_at BETWEEN ? AND ?) as previous', $previous)
                ->first();

            return ['current' => (int) $row->current, 'previous' => (int) $row->previous];
        };

        $cards = fn (string $type) => $count(BusinessCard::where('card_type', $type)->where('card_status', '!=', 'deleted'));

        $metrics = [
            'vcards' => $cards('vcard'),
            'stores' => $cards('store'),
        ];

        foreach (['visits' => 'visitors', 'enquiries' => 'contact_forms', 'storeOrders' => 'store_orders', 'appointments' => 'booked_appointments'] as $key => $table) {
            if (Schema::hasTable($table)) {
                $metrics[$key] = $count(DB::table($table));
            }
        }

        return $metrics;
    }

    /**
     * Active customers by plan.
     *
     * Without prices each row only says whether the plan is paid.
     *
     * @return array<int, array{name: string, price?: float, paid: bool, customers: int}>
     */
    public function planMix(bool $withPrices = true): array
    {
        return $this->activeCustomers()
            ->leftJoin('plans', 'plans.plan_id', '=', 'users.plan_id')
            ->selectRaw("MAX(COALESCE(plans.plan_name, JSON_UNQUOTE(JSON_EXTRACT(users.plan_details, '$.plan_name')), ?)) as name", [__('Unknown plan')])
            ->selectRaw("MAX(COALESCE(plans.plan_price, CAST(JSON_UNQUOTE(JSON_EXTRACT(users.plan_details, '$.plan_price')) AS DECIMAL(15,2)), 0)) as price")
            ->selectRaw('COUNT(*) as customers')
            ->groupBy('users.plan_id')
            ->orderByDesc('customers')
            ->get()
            ->map(fn ($row) => ($withPrices ? ['price' => (float) $row->price] : []) + [
                'name'      => $row->name,
                'paid'      => (float) $row->price > 0,
                'customers' => (int) $row->customers,
            ])
            ->all();
    }

    /**
     * Work waiting for an admin, limited to what the admin may access.
     * Without revenue, money items (offline payments, withdrawals) are left out.
     *
     * @param  array<string, bool>  $permissions
     * @param  array<string, bool>  $features
     * @return array<int, array{key: string, count: int, href: string|null}>
     */
    public function attention(array $permissions, array $features, bool $withRevenue = true): array
    {
        $can   = fn (string $key) => $permissions[$key] ?? false;
        $url   = fn (string $name, array $params = []) => Route::has($name) ? route($name, $params) : null;
        $items = [];

        if ($withRevenue && $can('transactions')) {
            $items[] = [
                'key'   => 'offline_payments',
                'count' => Transaction::where('payment_gateway_name', 'Offline')->where('payment_status', 'PENDING')->count(),
                'href'  => $url('admin.transactions.index', ['type' => 'offline', 'status' => 'unpaid']),
            ];
        }

        if ($features['nfc'] && $can('nfc_card_orders') && Schema::hasTable('nfc_card_orders')) {
            $items[] = [
                'key'   => 'nfc_orders',
                'count' => DB::table('nfc_card_orders')->where('status', 1)->whereIn('order_status', ['pending', 'processing', 'hold'])->count(),
                'href'  => $url('admin.orders'),
            ];
        }

        if ($features['nfc'] && $can('nfc_card_design')) {
            $items[] = [
                'key'   => 'low_stock',
                'count' => DB::table('nfc_card_designs')->where('status', 1)->where('available_stocks', '<', 10)->count(),
                'href'  => $url('admin.designs'),
            ];
        }

        if ($can('custom_domain') && Schema::hasTable('custom_domain_requests')) {
            $items[] = [
                'key'   => 'domain_requests',
                'count' => DB::table('custom_domain_requests')->where('transfer_status', 0)->count(),
                'href'  => $url('admin.custom.domain.requests', ['status' => 'all']),
            ];
        }

        if ($withRevenue && $features['referral'] && $can('referral_system') && Schema::hasTable('referral_withdraw_requests')) {
            $items[] = [
                'key'   => 'withdrawals',
                'count' => DB::table('referral_withdraw_requests')->where('status', 1)->where('payment_status', 0)->count(),
                'href'  => $url('admin.referral.withdrawal.request'),
            ];
        }

        if ($can('customers')) {
            $items[] = [
                'key'   => 'expiring',
                'count' => $this->expiringQuery()->count(),
                'href'  => $url('admin.customers', ['status' => 'all']),
            ];
        }

        return $items;
    }

    /**
     * Latest successful paid plan payments.
     */
    public function recentPayments(int $limit = 6): array
    {
        return $this->paidPlanPayments()
            ->leftJoin('users', 'users.id', '=', 'transactions.user_id')
            ->select(
                'transactions.gobiz_transaction_id',
                'transactions.desciption',
                'transactions.payment_gateway_name',
                'transactions.created_at',
                'users.id as customer_id',
                'users.name',
                'users.email',
            )
            ->selectRaw(self::PLAN_AMOUNT . ' as amount')
            ->latest('transactions.created_at')
            ->limit($limit)
            ->get()
            ->map(fn ($row) => [
                'id'         => $row->gobiz_transaction_id,
                'customer'   => $row->name ?? __('Deleted customer'),
                'email'      => $row->email,
                'customerId' => $row->customer_id,
                'plan'       => $row->desciption,
                'gateway'    => $row->payment_gateway_name,
                'amount'     => round((float) $row->amount, 2),
                'date'       => Carbon::parse($row->created_at)->toIso8601String(),
            ])
            ->all();
    }

    /**
     * Newest customers.
     */
    public function recentCustomers(int $limit = 5): array
    {
        return User::where('role_id', 2)
            ->latest()
            ->limit($limit)
            ->get(['id', 'name', 'email', 'profile_image', 'plan_details', 'plan_validity', 'status', 'created_at'])
            ->map(fn (User $user) => [
                'id'      => $user->id,
                'name'    => $user->name,
                'email'   => $user->email,
                'avatar'  => $user->profile_image ? asset($user->profile_image) : null,
                'plan'    => json_decode($user->plan_details ?? '', true)['plan_name'] ?? null,
                'active'  => (int) $user->status === 1,
                'date'    => $user->created_at?->toIso8601String(),
            ])
            ->all();
    }

    /**
     * Paid subscriptions ending within the next 7 days.
     */
    public function expiringSoon(int $limit = 5): array
    {
        return $this->expiringQuery()
            ->orderBy('plan_validity')
            ->limit($limit)
            ->get(['id', 'name', 'email', 'profile_image', 'plan_details', 'plan_validity'])
            ->map(fn (User $user) => [
                'id'      => $user->id,
                'name'    => $user->name,
                'email'   => $user->email,
                'avatar'  => $user->profile_image ? asset($user->profile_image) : null,
                'plan'    => json_decode($user->plan_details ?? '', true)['plan_name'] ?? null,
                'expires' => Carbon::parse($user->plan_validity)->toIso8601String(),
            ])
            ->all();
    }

    private function expiringQuery(): Builder
    {
        return User::where('role_id', 2)
            ->where('status', 1)
            ->whereRaw(self::PAID_PLAN)
            ->whereBetween('plan_validity', [Carbon::now(), Carbon::now()->addDays(7)->endOfDay()]);
    }

    /** Successful plan payments that collected money. */
    private function paidPlanPayments(): Builder
    {
        return Transaction::where('transactions.payment_status', 'SUCCESS')
            ->whereNotIn('transactions.payment_gateway_name', self::NON_PAYING_GATEWAYS);
    }

    /** Enabled customers with a plan that has not expired. */
    private function activeCustomers(): Builder
    {
        return User::where('users.role_id', 2)
            ->where('users.status', 1)
            ->whereNotNull('users.plan_details')
            ->where('users.plan_validity', '>=', Carbon::now());
    }

    private function revenueTotal(array $range): float
    {
        $range = self::range($range);

        return (float) $this->paidPlanPayments()->whereBetween('created_at', $range)->sum(DB::raw(self::PLAN_AMOUNT))
            + (float) NfcCardOrderTransaction::where('payment_status', 'success')->whereBetween('created_at', $range)->sum('amount')
            + (float) AiCreditsTransaction::where('payment_status', 'paid')->whereBetween('created_at', $range)->sum('amount');
    }

    private function emptySeries(array $p, array $values): array
    {
        $series = [];
        foreach ($p['buckets'] as $bucket) {
            $series[$bucket['key']] = ['label' => $bucket['label'], ...$values];
        }

        return $series;
    }

    private function bucketKey(Carbon $date, string $bucket): string
    {
        return match ($bucket) {
            'week'  => $date->copy()->startOfWeek()->format('Y-m-d'),
            'month' => $date->format('Y-m'),
            default => $date->format('Y-m-d'),
        };
    }

    private function nextBucket(Carbon $cursor, string $bucket): Carbon
    {
        return match ($bucket) {
            'week'  => $cursor->copy()->startOfWeek()->addWeek(),
            'month' => $cursor->copy()->startOfMonth()->addMonthNoOverflow(),
            default => $cursor->copy()->addDay(),
        };
    }

    private static function range(array $range): array
    {
        return [$range[0]->format('Y-m-d H:i:s'), $range[1]->format('Y-m-d H:i:s')];
    }
}
