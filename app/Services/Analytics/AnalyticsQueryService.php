<?php

namespace App\Services\Analytics;

use App\BusinessCard;
use App\Visitor;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Visitor analytics for one card or store, shared by the Blade and React
 * dashboards. Bots are left out everywhere. Visits recorded before location
 * tracking existed show as "Unknown" in the breakdowns.
 */
class AnalyticsQueryService
{
    /** Period key => days. 12m is grouped by month, the rest by day. */
    public const PERIODS = ['7d' => 7, '30d' => 30, '90d' => 90, '12m' => 365];

    public const DEFAULT_PERIOD = '30d';

    private const BREAKDOWN_LIMIT = 10;

    /** The user's card by card_id, or by its URL slug for old links. Null when it isn't theirs. */
    public function cardFor(string $userId, string $id): ?BusinessCard
    {
        return BusinessCard::where('user_id', $userId)
            ->where('card_status', '!=', 'deleted')
            ->where(fn ($query) => $query->where('card_id', $id)->orWhere('card_url', $id))
            ->first();
    }

    public static function period(?string $period): string
    {
        return array_key_exists((string) $period, self::PERIODS) ? $period : self::DEFAULT_PERIOD;
    }

    /** @return array{start: Carbon, end: Carbon, previous_start: Carbon, previous_end: Carbon, monthly: bool} */
    public function window(string $period): array
    {
        $days = self::PERIODS[self::period($period)];
        $monthly = $days > 90;
        $end = now()->endOfDay();
        $start = $monthly ? now()->subMonths(11)->startOfMonth() : now()->subDays($days - 1)->startOfDay();
        $length = $start->diffInSeconds($end);

        return [
            'start'          => $start,
            'end'            => $end,
            'previous_start' => $start->copy()->subSeconds($length + 1),
            'previous_end'   => $start->copy()->subSecond(),
            'monthly'        => $monthly,
        ];
    }

    /** Human visits of one card (or several, for an overview), optionally inside a date range. */
    public function visits(string|array $cardId, ?Carbon $from = null, ?Carbon $to = null): Builder
    {
        return Visitor::query()
            ->human()
            ->when(
                is_array($cardId),
                fn ($query) => $query->whereIn('card_id', $cardId ?: ['']),
                fn ($query) => $query->where('card_id', $cardId),
            )
            ->when($from && $to, fn ($query) => $query->whereBetween('created_at', [$from, $to]));
    }

    /** Everything the analytics page shows for a period (one card, or several card ids combined). */
    public function summary(string|array $cardId, string $period): array
    {
        $period = self::period($period);
        $w = $this->window($period);
        $current = fn () => $this->visits($cardId, $w['start'], $w['end']);
        $previous = fn () => $this->visits($cardId, $w['previous_start'], $w['previous_end']);

        return [
            'period'     => $period,
            'periods'    => array_keys(self::PERIODS),
            'kpis'       => $this->kpis($current, $previous, $cardId),
            'series'     => $this->series($current(), $w),
            'breakdowns' => [
                'countries'    => $this->countries($current()),
                'cities'       => $this->breakdown($current(), "CASE WHEN city = 'Unknown' THEN 'Unknown' ELSE CONCAT(city, ', ', country) END"),
                'browsers'     => $this->breakdown($current(), 'browser'),
                'platforms'    => $this->breakdown($current(), 'platform'),
                'device_types' => $this->breakdown($current(), 'device_type'),
                'sources'      => $this->breakdown($current(), 'source'),
                'referrers'    => $this->breakdown($current()->whereNotNull('referrer_domain'), 'referrer_domain'),
                'languages'    => $this->breakdown($current(), 'language'),
            ],
            'points'     => $this->points($current()),
            'recent'     => $this->recent($cardId),
        ];
    }

    private function kpis(callable $current, callable $previous, string|array $cardId): array
    {
        $now = $current()->selectRaw('COUNT(*) as views, SUM(is_unique) as uniques')->first();
        $before = $previous()->selectRaw('COUNT(*) as views, SUM(is_unique) as uniques')->first();

        return [
            'views'          => (int) $now->views,
            'views_change'   => $this->change((int) $now->views, (int) $before->views),
            'unique'         => (int) $now->uniques,
            'unique_change'  => $this->change((int) $now->uniques, (int) $before->uniques),
            'countries'      => (int) $current()->whereNotNull('country_code')->distinct()->count('country_code'),
            'today'          => (int) $this->visits($cardId, now()->startOfDay(), now()->endOfDay())->count(),
            'all_time'       => (int) $this->visits($cardId)->count(),
        ];
    }

    /** Percent change vs the previous period; null when there was nothing to compare with. */
    private function change(int $now, int $before): ?int
    {
        return $before === 0 ? null : (int) round(($now - $before) / $before * 100);
    }

    /** Views and unique visitors per day (or month), with empty days filled in. */
    private function series(Builder $query, array $w): array
    {
        $format = $w['monthly'] ? '%Y-%m' : '%Y-%m-%d';
        $rows = $query
            ->selectRaw("DATE_FORMAT(created_at, '{$format}') as bucket, COUNT(*) as views, SUM(is_unique) as uniques")
            ->groupBy('bucket')
            ->get()
            ->keyBy('bucket');

        $points = [];
        $step = $w['monthly'] ? '1 month' : '1 day';
        foreach (CarbonPeriod::create($w['start'], $step, $w['end']) as $date) {
            $key = $date->format($w['monthly'] ? 'Y-m' : 'Y-m-d');
            $points[] = [
                'date'   => $key,
                'views'  => (int) ($rows->get($key)?->views ?? 0),
                'unique' => (int) ($rows->get($key)?->uniques ?? 0),
            ];
        }

        return $points;
    }

    /** Top values of one column (or SQL expression) with their share of the period's visits. */
    private function breakdown(Builder $query, string $expression): array
    {
        $rows = $query
            ->selectRaw("{$expression} as label, COUNT(*) as value")
            ->groupBy('label')
            ->orderByDesc('value')
            ->limit(self::BREAKDOWN_LIMIT)
            ->get();

        return $this->withShare($rows->map(fn ($row) => ['label' => (string) ($row->label ?: 'Unknown'), 'value' => (int) $row->value])->all());
    }

    private function countries(Builder $query): array
    {
        $rows = $query
            ->selectRaw('country_code as code, country as label, COUNT(*) as value')
            ->groupBy('country_code', 'country')
            ->orderByDesc('value')
            ->limit(self::BREAKDOWN_LIMIT)
            ->get();

        return $this->withShare($rows->map(fn ($row) => ['code' => $row->code, 'label' => (string) $row->label, 'value' => (int) $row->value])->all());
    }

    private function withShare(array $rows): array
    {
        $total = max(1, array_sum(array_column($rows, 'value')));

        return array_map(fn ($row) => $row + ['share' => round($row['value'] / $total * 100, 1)], $rows);
    }

    /** Visit counts per located city, for the globe / map. */
    private function points(Builder $query): array
    {
        return $query
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->selectRaw('ROUND(latitude, 2) as lat, ROUND(longitude, 2) as lng, MAX(city) as city, MAX(country) as country, MAX(country_code) as code, COUNT(*) as value')
            ->groupBy(DB::raw('ROUND(latitude, 2)'), DB::raw('ROUND(longitude, 2)'))
            ->orderByDesc('value')
            ->limit(500)
            ->get()
            ->map(fn ($row) => [
                'lat'     => (float) $row->lat,
                'lng'     => (float) $row->lng,
                'city'    => $row->city,
                'country' => $row->country,
                'code'    => $row->code,
                'value'   => (int) $row->value,
            ])
            ->all();
    }

    /** The latest visits, newest first (the globe replays these). */
    private function recent(string|array $cardId, int $limit = 30): array
    {
        return $this->visits($cardId)
            ->latest('id')
            ->limit($limit)
            ->get(['id', 'created_at', 'ip_address', 'user_agent', 'country', 'country_code', 'city', 'latitude', 'longitude', 'browser', 'platform', 'device_type', 'source'])
            ->map(fn (Visitor $visit) => $this->row($visit))
            ->all();
    }

    /** One visit as both dashboards show it. */
    public function row(Visitor $visit): array
    {
        return [
            'id'          => $visit->id,
            'date'        => $visit->created_at?->toIso8601String(),
            'country'     => $visit->country,
            'code'        => $visit->country_code,
            'city'        => $visit->city,
            'lat'         => $visit->latitude,
            'lng'         => $visit->longitude,
            'browser'     => $visit->browser,
            'platform'    => $visit->platform,
            'device_type' => $visit->device_type,
            'source'      => $visit->source,
            // Avatar seed: the same person gets the same face, without the IP reaching the browser
            'seed'        => substr(hash_hmac('sha256', $visit->ip_address . '|' . $visit->user_agent, (string) config('app.key')), 0, 16),
        ];
    }
}
