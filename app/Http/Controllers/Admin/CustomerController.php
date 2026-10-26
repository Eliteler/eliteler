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
use App\BusinessCard;
use App\Http\Controllers\Controller;
use App\NfcCardOrder;
use App\NfcCardOrderTransaction;
use App\Plan;
use App\Referral;
use App\Services\Admin\CardDuplicator;
use App\Services\Admin\CustomerPlanService;
use App\Services\Admin\CustomerService;
use App\Support\Admin\AdminPermissions;
use App\Support\Admin\AdminSharedProps;
use App\Transaction;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class CustomerController extends Controller
{
    private const LIST_LIMIT = 50;

    public function __construct(
        private CustomerService $customers,
        private CustomerPlanService $plans,
    ) {
        $this->middleware('auth');
    }

    // All customers
    public function customers(Request $request, $status)
    {
        if (! in_array($status, CustomerService::TABS, true)) {
            return redirect()->route('admin.customers', ['status' => 'all']);
        }

        $filters = [
            'search'       => trim((string) $request->query('search', '')),
            'plan'         => (string) $request->query('plan', 'all'),
            'subscription' => in_array($request->query('subscription'), ['active', 'expired', 'expiring'], true) ? $request->query('subscription') : 'all',
            'sort'         => in_array($request->query('sort'), CustomerService::SORTS, true) ? $request->query('sort') : 'newest',
        ];

        $query = $this->customers->query()->select([
            'id', 'user_id', 'name', 'email', 'profile_image', 'email_verified_at', 'status',
            'plan_id', 'plan_details', 'plan_validity', 'plan_activation_date', 'term', 'created_at',
        ]);

        $this->customers->applyTab($query, $status);
        $this->customers->applyFilters($query, $filters);
        $this->customers->withCardCounts($query);

        $customers = $query->paginate(20)->withQueryString()->through(fn(User $user) => [
            'id'       => $user->user_id,
            'name'     => $user->name,
            'email'    => $user->email,
            'avatar'   => $user->profile_image ? asset($user->profile_image) : null,
            'state'    => $this->customers->state($user),
            'verified' => $user->email_verified_at !== null,
            'plan'     => $this->customers->planSummary($user),
            'vcards'   => (int) $user->vcards_count,
            'stores'   => (int) $user->stores_count,
            'joined'   => $user->created_at?->toIso8601String(),
        ]);

        // Calculate tab counts using the current search and plan filters.
        $countQuery = $this->customers->applyFilters(
            $this->customers->query(),
            $filters
        );

        return Inertia::render('customers/index', [
            'customers' => $customers,
            'tab'       => $status,
            'counts'    => $this->customers->tabCounts($countQuery),
            'filters'   => $filters,
            'plans'     => $this->planOptions(),
        ]);
    }

    // Customer details
    public function viewCustomer(Request $request, string $id)
    {
        $user = $this->findCustomer($id);

        if (! $user) {
            return redirect()
                ->route('admin.customers', ['status' => 'all'])
                ->with('failed', __('Not Found!'));
        }

        $features     = AdminSharedProps::features();
        $permissions  = AdminPermissions::for($request->user());
        $hidesRevenue = AdminPermissions::hidesRevenue($request->user());

        $date = fn($value) => $value
            ? Carbon::parse($value)->toIso8601String()
            : null;

        $tab     = $request->query('tab', 'cards');
        $page    = max(1, (int) $request->query('page', 1));
        $perPage = 14;

        $allowedTabs = [
            'cards',
            'stores',
            'payments',
            'nfc_orders',
            'nfc_payments',
            'ai_payments',
        ];

        // Payment history is money: not offered when revenue is hidden
        if ($hidesRevenue) {
            $allowedTabs = array_values(array_diff($allowedTabs, ['payments', 'nfc_payments', 'ai_payments']));
        }

        if (! in_array($tab, $allowedTabs, true)) {
            $tab = 'cards';
        }

        // Check optional tables before querying them.
        $nfcOrdersTableExists = Schema::hasTable('nfc_card_orders');

        $nfcTransactionsTableExists = Schema::hasTable(
            'nfc_card_order_transactions'
        );

        $aiTransactionsTableExists = Schema::hasTable(
            'ai_credits_transactions'
        );

        $aiPlansTableExists = Schema::hasTable('ai_credits_plans');

        $nfcEnabled = (bool) ($features['nfc'] ?? false)
            && $nfcOrdersTableExists
            && $nfcTransactionsTableExists;

        $aiBuilderEnabled = (bool) ($features['aibuilder'] ?? false)
            && $aiTransactionsTableExists;

        // Validate requested tab against enabled features and tables.
        if (
            $tab === 'nfc_orders'
            && (! ($features['nfc'] ?? false) || ! $nfcOrdersTableExists)
        ) {
            $tab = 'cards';
        }

        if ($tab === 'nfc_payments' && ! $nfcEnabled) {
            $tab = 'cards';
        }

        if ($tab === 'ai_payments' && ! $aiBuilderEnabled) {
            $tab = 'cards';
        }

        // Fetch only the current tab's activity.
        $activity = match ($tab) {
            'cards', 'stores' => BusinessCard::where(
                'business_cards.user_id',
                $user->user_id
            )
                ->where('business_cards.card_status', '!=', 'deleted')
                ->when(
                    $tab === 'stores',
                    fn($query) => $query->where(
                        'business_cards.card_type',
                        'store'
                    ),
                    fn($query) => $query->where(function ($query) {
                        $query->where('business_cards.card_type', '!=', 'store')
                            ->orWhereNull('business_cards.card_type');
                    })
                )
                ->leftJoin(
                    'themes',
                    'themes.theme_id',
                    '=',
                    'business_cards.theme_id'
                )
                ->orderByDesc('business_cards.id')
                ->paginate($perPage, [
                    'business_cards.card_id',
                    'business_cards.card_type',
                    'business_cards.title',
                    'business_cards.card_url',
                    'business_cards.card_status',
                    'business_cards.status',
                    'business_cards.created_at',
                    'themes.theme_name',
                ], 'page', $page)
                ->through(fn($card) => [
                    'id'      => $card->card_id,
                    'type'    => $card->card_type === 'store'
                        ? 'store'
                        : 'vcard',
                    'title'   => $card->title,
                    'url'     => url($card->card_url),
                    'theme'   => $card->theme_name,
                    'active'  => $card->card_status === 'activated'
                    && (string) $card->status === '1',
                    'state'   => $card->card_status,
                    'created' => $date($card->created_at),
                ]),

            'payments'     => Transaction::where('user_id', $user->id)
                ->orderByDesc('id')
                ->paginate($perPage, ['*'], 'page', $page)
                ->through(fn($t) => [
                    'id'          => $t->gobiz_transaction_id,
                    'reference'   => $t->transaction_id,
                    'description' => $t->desciption,
                    'gateway'     => $t->payment_gateway_name,
                    'amount'      => (float) $t->transaction_amount,
                    'status'      => strtolower($t->payment_status),
                    'date'        => $date($t->created_at),
                    'invoice'     => ($permissions['transactions'] ?? false)
                    && $t->payment_status === 'SUCCESS'
                        ? route(
                        'admin.view.invoice',
                        $t->gobiz_transaction_id
                    )
                        : null,
                ]),

            'nfc_orders'   => NfcCardOrder::where('user_id', $user->id)
                ->orderByDesc('id')
                ->paginate($perPage, ['*'], 'page', $page)
                ->through(function ($order) use ($date) {
                    $details = json_decode(
                        $order->order_details ?? '',
                        true
                    ) ?: [];

                    return [
                        'id'       => $order->nfc_card_order_id,
                        'item'     => $details['order_item'] ?? null,
                        'quantity' => (int) (
                            $details['order_quantity'] ?? 1
                        ),
                        'amount'   => AdminPermissions::amount((float) (
                            $details['invoice_amount'] ?? 0
                        )),
                        'status'   => $order->order_status,
                        'date'     => $date($order->created_at),
                    ];
                }),

            'nfc_payments' => NfcCardOrderTransaction::join(
                'nfc_card_orders',
                'nfc_card_order_transactions.nfc_card_order_id',
                '=',
                'nfc_card_orders.nfc_card_order_id'
            )
                ->where('nfc_card_orders.user_id', $user->id)
                ->orderByDesc('nfc_card_order_transactions.id')
                ->paginate(
                    $perPage,
                    ['nfc_card_order_transactions.*'],
                    'page',
                    $page
                )
                ->through(fn($t) => [
                    'id'        => $t->nfc_card_order_transaction_id,
                    'orderId'   => $t->nfc_card_order_id,
                    'reference' => $t->payment_transaction_id,
                    'gateway'   => $t->payment_method,
                    'amount'    => (float) $t->amount,
                    'status'    => $t->payment_status,
                    'date'      => $date($t->created_at),
                ]),

            'ai_payments'  => AiCreditsTransaction::query()
                ->when(
                    $aiPlansTableExists,
                    fn($query) => $query->leftJoin(
                        'ai_credits_plans',
                        'ai_credits_transactions.ai_credits_plan_id',
                        '=',
                        'ai_credits_plans.ai_credits_plan_id'
                    )
                )
                ->where('ai_credits_transactions.user_id', $user->id)
                ->orderByDesc('ai_credits_transactions.id')
                ->paginate($perPage, [
                    'ai_credits_transactions.*',
                    ...(
                        $aiPlansTableExists
                            ? ['ai_credits_plans.plan_name']
                            : []
                    ),
                ], 'page', $page)
                ->through(fn($t) => [
                    'id'      => $t->ai_credits_transaction_id,
                    'plan'    => $aiPlansTableExists
                        ? $t->plan_name
                        : null,
                    'gateway' => $t->payment_method,
                    'amount'  => (float) $t->amount,
                    'status'  => $t->payment_status,
                    'date'    => $date($t->created_at),
                ]),
        };

        // AI credits balance.
        $credits = Schema::hasTable('ai_credits')
            ? DB::table('ai_credits')
            ->where('user_id', $user->user_id)
            ->first()
            : null;

        // Referral information.
        $referral = Referral::where('user_id', $user->user_id)->first();

        $referrer = $referral
            ? User::where(
            'user_id',
            $referral->referred_user_id
        )->first(['user_id', 'name', 'email'])
            : null;

        // Card and store counts.
        $baseCards = BusinessCard::where(
            'user_id',
            $user->user_id
        )->where('card_status', '!=', 'deleted');

        $cardCount = (clone $baseCards)
            ->where(function ($query) {
                $query->where('card_type', '!=', 'store')
                    ->orWhereNull('card_type');
            })
            ->count();

        $storeCount = (clone $baseCards)
            ->where('card_type', 'store')
            ->count();

        // Counts are independent of the current pagination page.
        $tabCounts = [
            'cards'        => $cardCount,

            'stores'       => $storeCount,

            'payments'     => Transaction::where(
                'user_id',
                $user->id
            )->count(),

            'nfc_orders'   => ($features['nfc'] ?? false)
            && $nfcOrdersTableExists
                ? NfcCardOrder::where(
                'user_id',
                $user->id
            )->count()
                : 0,

            'nfc_payments' => $nfcEnabled
                ? NfcCardOrderTransaction::join(
                'nfc_card_orders',
                'nfc_card_order_transactions.nfc_card_order_id',
                '=',
                'nfc_card_orders.nfc_card_order_id'
            )
                ->where('nfc_card_orders.user_id', $user->id)
                ->count()
                : 0,

            'ai_payments'  => $aiBuilderEnabled
                ? AiCreditsTransaction::where(
                'user_id',
                $user->id
            )->count()
                : 0,
        ];

        return Inertia::render('customers/show', [
            'customer'         => [
                'id'         => $user->user_id,
                'name'       => $user->name,
                'email'      => $user->email,

                'avatar'     => $user->profile_image
                    ? asset($user->profile_image)
                    : null,

                'phone'      => $user->mobile_number
                    ? trim(
                    ($user->country_code
                            ? '+' . ltrim($user->country_code, '+') . ' '
                            : ''
                    ) . $user->mobile_number
                )
                    : null,

                'whatsapp'   => $user->whatsapp_number
                    ? trim(
                    ($user->whatsapp_country_code
                            ? '+' . ltrim($user->whatsapp_country_code, '+') . ' '
                            : ''
                    ) . $user->whatsapp_number
                )
                    : null,

                'language'   => $user->lang,
                'signInWith' => $user->auth_type,
                'state'      => $this->customers->state($user),
                'verified'   => $user->email_verified_at !== null,
                'joined'     => $date($user->created_at),
                'updated'    => $date($user->updated_at),
                'plan'       => $this->customers->planSummary($user),

                'billing'    => array_filter([
                    'name'    => $user->billing_name,
                    'email'   => $user->billing_email,

                    'phone'   => trim(
                        ($user->billing_country_code ?? '')
                        . ' '
                        . ($user->billing_phone ?? '')
                    ) ?: null,

                    'address' => collect([
                        $user->billing_address,
                        $user->billing_city,
                        $user->billing_state,
                        $user->billing_zipcode,
                        $user->billing_country,
                    ])->filter()->implode(', ') ?: null,

                    'taxId'   => $user->vat_number,
                    'type'    => $user->type,
                ]),

                'aiCredits'  => $credits
                    ? [
                    'credits' => (int) $credits->credits,
                    'trial'   => (int) $credits->trial_credits,
                ]
                    : null,

                'referredBy' => $referrer
                    ? [
                    'id'    => $referrer->user_id,
                    'name'  => $referrer->name,
                    'email' => $referrer->email,
                ]
                    : null,
            ],

            'activeTab'        => $tab,
            'activity'         => $activity,
            'tabCounts'        => $hidesRevenue
                ? array_diff_key($tabCounts, array_flip(['payments', 'nfc_payments', 'ai_payments']))
                : $tabCounts,
            'hideRevenue'      => $hidesRevenue,

            'nfcEnabled'       => $nfcEnabled,
            'aiBuilderEnabled' => $aiBuilderEnabled,

            'plans'            => $this->planOptions(),
            'listLimit'        => self::LIST_LIMIT,

            'openDialog'       => $request->query('dialog') === 'plan'
                ? 'plan'
                : null,
        ]);
    }

    // Edit customer
    public function editCustomer($id)
    {
        $user = $this->findCustomer($id);

        if (! $user) {
            return redirect()->route('admin.customers', ['status' => 'all'])->with('failed', __('Not Found!'));
        }

        return Inertia::render('customers/form', [
            'customer' => [
                'id'     => $user->user_id,
                'name'   => $user->name,
                'email'  => $user->email,
                'status' => (int) $user->status === 0 ? 0 : 1,
                'state'  => $this->customers->state($user),
            ],
            'plans'    => [],
        ]);
    }

    // Update customer
    public function updateCustomer(Request $request)
    {
        $user = $this->findCustomer((string) $request->input('user_id'));

        if (! $user) {
            return redirect()->route('admin.customers', ['status' => 'all'])->with('failed', __('Not Found!'));
        }

        $data = $request->validate([
            'full_name'      => 'required|string|max:255',
            'email'          => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password'       => 'nullable|string|min:6',
            'status'         => 'required|in:0,1',
            'welcome_email'  => 'boolean',
            'reset_password' => 'boolean',
        ], [
            'email.unique' => __('This email address already registered. Try to another email address.'),
        ]);

        // Suspended/deleted accounts keep their state; status here is active/inactive only.
        if (in_array((int) $user->status, [2, -1], true)) {
            $data['status'] = $user->status;
        }

        $this->customers->update($user, $data);

        if ($request->boolean('welcome_email')) {
            $this->customers->sendWelcomeEmail($user);
        }

        if ($request->boolean('reset_password') && ! $this->customers->sendPasswordResetEmail($user)) {
            return redirect()->route('admin.view.customer', $user->user_id)
                ->with('failed', __('Customer updated, but the password reset email could not be sent.'));
        }

        return redirect()->route('admin.view.customer', $user->user_id)->with('success', __('Customer updated.'));
    }

    // Change plan (opens the plan dialog on the customer page)
    public function ChangeCustomerPlan($id)
    {
        return redirect()->route('admin.view.customer', ['id' => $id, 'dialog' => 'plan']);
    }

    // Assign a plan
    public function UpdateCustomerPlan(Request $request)
    {
        $data = $request->validate([
            'user_id' => 'required|string',
            'plan_id' => ['required', Rule::exists('plans', 'plan_id')->where('status', 1)],
        ]);

        $user = $this->findCustomer($data['user_id']);

        if (! $user) {
            return back()->with('failed', __('Not Found!'));
        }

        $plan   = Plan::where('plan_id', $data['plan_id'])->firstOrFail();
        $result = $this->plans->assign($user, $plan);

        $message = __('Plan changed to :plan, valid until :date.', [
            'plan' => $plan->plan_name,
            'date' => $result['validUntil']->format('d M Y'),
        ]);

        if ($result['cardsDeactivated']) {
            $message .= ' ' . __('Their business cards were set to inactive.');
        }

        return redirect()->route('admin.view.customer', $user->user_id)->with('success', $message);
    }

    // Activate / deactivate
    public function updateStatus(Request $request)
    {
        $user = $this->findCustomer((string) $request->input('id'));

        if (! $user) {
            return back()->with('failed', __('Not Found!'));
        }

        $activate = (int) $user->status === 0;
        $this->customers->changeState($user, $activate ? 'activate' : 'deactivate');

        return back()->with('success', $activate ? __('Customer activated.') : __('Customer deactivated.'));
    }

    // Verify / unverify / suspend / unsuspend
    public function customerVerified(Request $request)
    {
        $data = $request->validate([
            'id'     => 'required|string',
            'action' => 'required|in:verify,unverify,suspend,unsuspend',
        ]);

        $user = $this->findCustomer($data['id']);

        if (! $user) {
            return back()->with('failed', __('Not Found!'));
        }

        $this->customers->changeState($user, $data['action']);

        return back()->with('success', match ($data['action']) {
            'verify'    => __('Email marked as verified.'),
            'unverify'  => __('Email marked as unverified.'),
            'suspend'   => __('Customer suspended.'),
            'unsuspend' => __('Customer unsuspended.'),
        });
    }

    // Restore an account the customer deleted
    public function undeleteCustomer(Request $request)
    {
        $user = $this->findCustomer((string) $request->input('id'));

        if (! $user) {
            return back()->with('failed', __('Not Found!'));
        }

        if (! $this->customers->restore($user)) {
            return back()->with('failed', __('Unable to undelete user. Email already exists.'));
        }

        return back()->with('success', __('Customer undeleted!'));
    }

    // Permanently delete
    public function deleteCustomer(Request $request)
    {
        $user = $this->findCustomer((string) $request->input('id'));

        if (! $user) {
            return back()->with('failed', __('Not Found!'));
        }

        $this->customers->delete($user);

        return redirect()->route('admin.customers', ['status' => 'all'])->with('success', __('Customer deleted.'));
    }

    // Login as customer (ends the admin session)
    public function authAs($id)
    {
        $user = User::where('role_id', 2)->where('user_id', $id)->where('status', 1)->first();

        if (! $user) {
            return redirect()->route('admin.customers', ['status' => 'all'])->with('failed', __('Unable to find user account!'));
        }

        Auth::loginUsingId($user->id);

        return redirect()->route('user.dashboard');
    }

    // Copy a business card / store to another customer
    public function assignCard(Request $request, CardDuplicator $duplicator)
    {
        $data = $request->validate([
            'card_id'     => ['required', Rule::exists('business_cards', 'card_id')],
            'customer_id' => 'required|string',
        ]);

        $target = $this->findCustomer($data['customer_id']);
        $card   = BusinessCard::where('card_id', $data['card_id'])->first();

        if (! $target || ! $card) {
            return back()->with('failed', __('Not Found!'));
        }

        $plan = json_decode($target->plan_details ?? '');

        if (! $plan) {
            return back()->with('failed', __('The selected customer has no plan.'));
        }

        // 999 means unlimited
        $limit = (int) ($card->card_type === 'store' ? ($plan->no_of_stores ?? 0) : ($plan->no_of_vcards ?? 0));
        $used  = BusinessCard::where('user_id', $target->user_id)
            ->where('card_type', $card->card_type)
            ->where('card_status', 'activated')
            ->where('status', '1')
            ->count();

        if ($limit !== 999 && $used >= $limit) {
            return back()->with('failed', __('Unable to assign card. The maximum limit has been exceeded.'));
        }

        $duplicator->duplicate($card, $target->user_id);

        return back()->with('success', __('Copied to :name.', ['name' => $target->name]));
    }

    // Customer lookup for pickers (JSON)
    public function search(Request $request)
    {
        $term = trim((string) $request->query('q', ''));

        return $this->customers->query()
            ->where('status', 1)
            ->when($request->query('except'), fn($q, $except) => $q->where('user_id', '!=', $except))
            ->when($term !== '', fn($q) => $q->where(fn($q) => $q->where('name', 'like', "%{$term}%")->orWhere('email', 'like', "%{$term}%")))
            ->orderBy('name')
            ->limit(20)
            ->get(['user_id', 'name', 'email', 'profile_image'])
            ->map(fn(User $user) => [
                'id'     => $user->user_id,
                'name'   => $user->name,
                'email'  => $user->email,
                'avatar' => $user->profile_image ? asset($user->profile_image) : null,
            ]);
    }

    private function findCustomer(string $userId): ?User
    {
        return $this->customers->query()->where('user_id', $userId)->first();
    }

    private function planOptions(): array
    {
        return Plan::where('status', 1)
            ->orderBy('plan_price')
            ->get(['plan_id', 'plan_name', 'plan_price', 'validity', 'plan_type', 'no_of_vcards', 'no_of_stores'])
            ->map(fn(Plan $plan) => [
                'id'       => $plan->plan_id,
                'name'     => $plan->plan_name,
                'price'    => AdminPermissions::price((float) $plan->plan_price),
                'validity' => (int) $plan->validity,
                'type'     => $plan->plan_type,
                'vcards'   => (int) $plan->no_of_vcards,
                'stores'   => (int) $plan->no_of_stores,
            ])
            ->all();
    }
}
