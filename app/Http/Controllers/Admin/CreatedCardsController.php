<?php

/*
 |--------------------------------------------------------------------------
 | Eliteler vCard SaaS
 |--------------------------------------------------------------------------
 | Developed by NativeCode © 2021 - https://nativecode.in
 | All rights reserved
 | Unauthorized distribution is prohibited
 |--------------------------------------------------------------------------
*/


namespace App\Http\Controllers\Admin;

use App\BusinessCard;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

/**
 * Business cards and stores created by customers.
 *
 * business_cards.card_status: activated / inactive / deleted (deleted are hidden)
 * business_cards.status: '1', or '0' when the owner's account is suspended/inactive
 * visitors.card_id holds the card_url (see ProfileController).
 */
class CreatedCardsController extends Controller
{
    private const TABS = ['all', 'active', 'inactive', 'disabled'];

    private const SORTS = ['newest', 'oldest', 'visits', 'title'];

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function createdCards(Request $request)
    {
        return $this->index($request, 'vcard');
    }

    public function createdStores(Request $request)
    {
        return $this->index($request, 'store');
        }

    private function index(Request $request, string $type)
    {
        $filters = [
            'tab'    => in_array($request->query('tab'), self::TABS, true) ? $request->query('tab') : 'all',
            'search' => trim((string) $request->query('search', '')),
            'plan'   => in_array($request->query('plan'), ['active', 'expired'], true) ? $request->query('plan') : 'all',
            'theme'  => (string) $request->query('theme', 'all'),
            'sort'   => in_array($request->query('sort'), self::SORTS, true) ? $request->query('sort') : 'newest',
        ];

        $base = fn () => BusinessCard::query()
            ->join('users', 'users.user_id', '=', 'business_cards.user_id')
            ->where('users.role_id', 2)
            ->where('business_cards.card_type', $type)
            ->where('business_cards.card_status', '!=', 'deleted');

        $query = $this->filter($base(), $filters)
            ->leftJoin('themes', 'themes.theme_id', '=', 'business_cards.theme_id')
            ->select([
                'business_cards.card_id', 'business_cards.title', 'business_cards.card_url', 'business_cards.custom_domain',
                'business_cards.card_status', 'business_cards.status', 'business_cards.created_at', 'business_cards.theme_id',
                'users.user_id as owner_id', 'users.name as owner_name', 'users.email as owner_email',
                'users.plan_validity as owner_plan_validity', 'themes.theme_name',
            ])
            ->selectSub(
                DB::table('visitors')->selectRaw('COUNT(*)')->whereColumn('visitors.card_id', 'business_cards.card_url'),
                'visits'
            );

        match ($filters['sort']) {
            'oldest' => $query->orderBy('business_cards.id'),
            'visits' => $query->orderByDesc('visits'),
            'title'  => $query->orderBy('business_cards.title'),
            default  => $query->orderByDesc('business_cards.id'),
        };

        $cards = $query->paginate(20)->withQueryString()->through(function ($card) use ($type) {
            $expires = $card->owner_plan_validity ? Carbon::parse($card->owner_plan_validity) : null;

            return [
                'id'           => $card->card_id,
                'type'         => $type,
                'title'        => $card->title,
                'url'          => url($card->card_url),
                'customDomain' => $card->custom_domain ?: null,
                'theme'        => $card->theme_name,
                'state'        => (string) $card->status !== '1' ? 'disabled' : ($card->card_status === 'activated' ? 'active' : 'inactive'),
                'visits'       => (int) $card->visits,
                'created'      => $card->created_at?->toIso8601String(),
                'owner'        => [
                    'id'          => $card->owner_id,
                    'name'        => $card->owner_name,
                    'email'       => $card->owner_email,
                    'planExpires' => $expires?->toIso8601String(),
                    'planExpired' => $expires ? $expires->isPast() : true,
                ],
            ];
        });

        $counts = $this->filter($base(), [...$filters, 'tab' => 'all'])
            ->selectRaw('COUNT(*) as `all`')
            ->selectRaw("SUM(business_cards.status = '1' AND business_cards.card_status = 'activated') as active")
            ->selectRaw("SUM(business_cards.status = '1' AND business_cards.card_status = 'inactive') as inactive")
            ->selectRaw("SUM(business_cards.status != '1') as disabled")
            ->first();

        $themes = DB::table('themes')
            ->whereIn('theme_id', $base()->select('business_cards.theme_id')->distinct())
            ->orderBy('theme_name')
            ->get(['theme_id', 'theme_name'])
            ->map(fn ($theme) => ['id' => $theme->theme_id, 'name' => $theme->theme_name]);

        return Inertia::render('cards/index', [
            'type'    => $type,
            'cards'   => $cards,
            'filters' => $filters,
            'counts'  => collect(self::TABS)->mapWithKeys(fn ($tab) => [$tab => (int) $counts->{$tab}]),
            'themes'  => $themes,
        ]);
    }

    private function filter(Builder $query, array $filters): Builder
    {
        $search = $filters['search'];

        return $query
            ->when($filters['tab'] === 'active', fn ($q) => $q->where('business_cards.status', '1')->where('business_cards.card_status', 'activated'))
            ->when($filters['tab'] === 'inactive', fn ($q) => $q->where('business_cards.status', '1')->where('business_cards.card_status', 'inactive'))
            ->when($filters['tab'] === 'disabled', fn ($q) => $q->where('business_cards.status', '!=', '1'))
            ->when($search !== '', fn ($q) => $q->where(function ($q) use ($search) {
                $q->where('business_cards.title', 'like', "%{$search}%")
                    ->orWhere('business_cards.card_url', 'like', "%{$search}%")
                    ->orWhere('business_cards.custom_domain', 'like', "%{$search}%")
                    ->orWhere('users.name', 'like', "%{$search}%")
                    ->orWhere('users.email', 'like', "%{$search}%");
            }))
            ->when($filters['plan'] === 'active', fn ($q) => $q->where('users.plan_validity', '>=', Carbon::now()))
            ->when($filters['plan'] === 'expired', fn ($q) => $q->where(fn ($q) => $q->whereNull('users.plan_validity')->orWhere('users.plan_validity', '<', Carbon::now())))
            ->when($filters['theme'] !== 'all', fn ($q) => $q->where('business_cards.theme_id', $filters['theme']));
    }
}
