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

use App\BusinessCard;
use App\User;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

/**
 * Business cards and stores created by customers.
 *
 * business_cards.card_status: activated / inactive / deleted (deleted are hidden)
 * business_cards.status: '1', or '0' when the owner's account is suspended/inactive
 * visitors.card_id holds business_cards.card_id (see App\Services\Analytics\VisitTracker).
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

    /**
     * Open a customer's card/store in the user panel editor as administrator.
     * The owner is remembered in the session (see AdminCardContext middleware);
     * the admin stays logged in to their own account.
     */
    public function editCard(Request $request, string $id)
    {
        $card = BusinessCard::where('card_id', $id)->where('card_status', '!=', 'deleted')->first();

        if (! $card) {
            return redirect()->route('admin.created.cards')->with('failed', __('Card not found!'));
        }

        $owner = User::where('user_id', $card->user_id)->first();

        if (! $owner) {
            return redirect()->route('admin.created.cards')->with('failed', __('Unable to find user account!'));
        }

        if ((int) $owner->id !== (int) Auth::id()) {
            $request->session()->put('admin_card_context', [
                'owner_id' => $owner->id,
                'card_id'  => $card->card_id,
                'type'     => $card->card_type,
            ]);
        } else {
            $request->session()->forget('admin_card_context');
        }

        return redirect()->route($card->card_type === 'store' ? 'user.edit.store' : 'user.edit.card', $card->card_id);
    }

    /**
     * Start the card/store builder in the user panel on behalf of a customer
     * (?customer=<user_id>) or in the administrator's own account (no customer).
     * Only the super admin can act inside the user panel.
     */
    public function createCard(Request $request)
    {
        $type      = $request->query('type') === 'store' ? 'store' : 'vcard';
        $listRoute = $type === 'store' ? 'admin.created.stores' : 'admin.created.cards';
        $builder   = $type === 'store' ? 'user.create.store' : 'user.choose.card.type';

        if ((int) $request->user()->role_id !== 1) {
            return redirect()->route($listRoute)->with('failed', __('Only the main administrator can create cards for customers.'));
        }

        $customerId = (string) $request->query('customer', '');

        // The administrator's own account
        if ($customerId === '') {
            $request->session()->forget('admin_card_context');

            return redirect()->route($builder);
        }

        $owner = User::where('user_id', $customerId)->where('role_id', 2)->first();

        if (! $owner) {
            return redirect()->route($listRoute)->with('failed', __('Unable to find user account!'));
        }

        if ((int) $owner->status !== 1) {
            return redirect()->route($listRoute)->with('failed', __(':name is not an active account.', ['name' => $owner->name]));
        }

        $plan = json_decode($owner->plan_details ?? '');

        if (! $plan || ! $owner->plan_validity || Carbon::parse($owner->plan_validity)->isPast()) {
            return redirect()->route($listRoute)->with('failed', __(':name has no active plan. Assign a plan to this customer first.', ['name' => $owner->name]));
        }

        $limit = (int) ($type === 'store' ? ($plan->no_of_stores ?? 0) : ($plan->no_of_vcards ?? 0));
        $used  = BusinessCard::where('user_id', $owner->user_id)->where('card_type', $type)->where('card_status', 'activated')->count();

        if ($limit !== 999 && $used >= $limit) {
            return redirect()->route($listRoute)->with('failed', __(':name has reached the limit of their plan (:used of :limit).', ['name' => $owner->name, 'used' => $used, 'limit' => $limit]));
        }

        $request->session()->put('admin_card_context', [
            'owner_id' => $owner->id,
            'card_id'  => null,
            'type'     => $type,
        ]);

        return redirect()->route($builder);
    }

    /** Show or hide a card in the website "Our Work" section. */
    public function toggleShowcase(Request $request, string $id)
    {
        $card = BusinessCard::where('card_id', $id)->where('card_type', 'vcard')->where('card_status', '!=', 'deleted')->first();

        if (! $card) {
            return back()->with('failed', __('Card not found!'));
        }

        $featured = ! (bool) $card->show_in_showcase;
        BusinessCard::where('card_id', $id)->update(['show_in_showcase' => $featured ? 1 : 0]);

        return back()->with('success', $featured
            ? __(':title is now shown in Our Work on the website.', ['title' => $card->title])
            : __(':title was removed from Our Work.', ['title' => $card->title]));
    }

    /** Leave the customer context and return to the admin list. */
    public function leaveContext(Request $request)
    {
        $type = $request->session()->get('admin_card_context.type', 'vcard');
        $request->session()->forget('admin_card_context');

        return redirect()->route($type === 'store' ? 'admin.created.stores' : 'admin.created.cards');
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
            ->whereIn('users.role_id', [1, 2])
            ->where('business_cards.card_type', $type)
            ->where('business_cards.card_status', '!=', 'deleted');

        $query = $this->filter($base(), $filters)
            ->leftJoin('themes', 'themes.theme_id', '=', 'business_cards.theme_id')
            ->select([
                'business_cards.card_id', 'business_cards.title', 'business_cards.card_url', 'business_cards.custom_domain', 'business_cards.show_in_showcase',
                'business_cards.card_status', 'business_cards.status', 'business_cards.created_at', 'business_cards.theme_id',
                'users.user_id as owner_id', 'users.name as owner_name', 'users.email as owner_email',
                'users.plan_validity as owner_plan_validity', 'themes.theme_name',
            ])
            ->selectSub(
                DB::table('visitors')->selectRaw('COUNT(*)')->whereColumn('visitors.card_id', 'business_cards.card_id')->where('visitors.is_bot', 0),
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
                'featured'     => (bool) $card->show_in_showcase,
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
            'canCreate' => (int) $request->user()->role_id === 1,
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
