@php
    use Illuminate\Support\Facades\Auth;
    use App\Services\GoBizCommonService;
    use App\Transaction;
    use Carbon\Carbon;
    use App\User;
    use App\AibuilderSetting;

    // Config
    $config = GoBizCommonService::config();

    // User
    $user = Auth::user();
    $currentPermissions = json_decode($user->permissions, true);
    $allowedPermissions = is_array($currentPermissions) ? $currentPermissions : [];

    // Default permissions
    $defaultPermissions = [
        'currencies' => 1,
        'coupons' => 1,
        'custom_domain' => 1,
        'marketing' => 1,
        'maintenance_mode' => 1,
        'demo_mode' => 1,
        'backup' => 1,
        'nfc_card_design' => 1,
        'nfc_card_orders' => 1,
        'nfc_card_order_transactions' => 1,
        'nfc_card_key_generations' => 1,
        'email_templates' => 1,
        'plugins' => 1,
        'referral_system' => 1,
        'in_app_purchases' => 1,
        'vcard_store' => 1,
        'business_card_intros' => 1,
        'ai_credits' => 1,
    ];

    // Merge permissions
    $mergedPermissions = array_merge($defaultPermissions, $allowedPermissions);

    // Update only if changed
    if ($mergedPermissions != $allowedPermissions) {
        User::where('id', $user->id)->update([
            'permissions' => json_encode($mergedPermissions),
            'updated_at' => Carbon::now(),
        ]);
    }

    $allowedPermissions = $mergedPermissions;

    // Money areas are closed to admins with revenue hidden (display only, never saved)
    $hidesRevenue = \App\Support\Admin\AdminPermissions::hidesRevenue($user);
    if ($hidesRevenue) {
        $allowedPermissions = array_merge($allowedPermissions, array_fill_keys(\App\Support\Admin\AdminPermissions::REVENUE_AREAS, 0));
    }

    // Transactions base query
    $onlineTransactions = Transaction::where('payment_gateway_name', '!=', 'Offline');
    $offlineTransactions = Transaction::where('payment_gateway_name', 'Offline');

    $onlinePaidTransactions = (clone $onlineTransactions)->where('payment_status', 'SUCCESS')->count();

    $onlineUnpaidTransactions = (clone $onlineTransactions)->where('payment_status', '!=', 'SUCCESS')->count();

    $offlinePaidTransactions = (clone $offlineTransactions)->where('payment_status', 'SUCCESS')->count();

    $offlineUnpaidTransactions = (clone $offlineTransactions)->where('payment_status', '!=', 'SUCCESS')->count();

    // Mobile app API check
    $mobile_app_api_enabled = plugin_active('MobileAppAPI') ? 1 : 0;

    // Ai builder settings
    $aibuilder_settings = AibuilderSetting::first();
@endphp

<!-- Sidebar -->
<aside class="navbar navbar-vertical navbar-expand-lg d-print-none bg-body-tertiary">
    <div class="container-fluid">
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#sidebar-menu"
            aria-controls="sidebar-menu" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="navbar-brand navbar-brand-autodark">
            <a href="{{ route('admin.dashboard') }}">
                @php($lightLogo = collect(glob(public_path('img/logo-light.*')) ?: [])->first())
                @if ($lightLogo)
                    <img src="{{ optional(Auth::user())->choosed_theme == 'light' ? asset($settings->site_logo) : asset('img/' . basename($lightLogo)) . '?v=' . filemtime($lightLogo) }}"
                        width="200" height="50" alt="{{ $settings->site_name }}"
                        class="navbar-brand-image custom-logo">
                @else
                    <img src="{{ asset($settings->site_logo) }}" width="200" height="50"
                        alt="{{ $settings->site_name }}" class="navbar-brand-image custom-logo">
                @endif
            </a>
        </div>

        <div class="navbar-nav flex-row d-lg-none">
            <div class="d-md-none">
                <button id="openSearchModalMobile" class="btn btn-icon my-3 mx-1">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" class="icon">
                        <path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0" />
                        <path d="M21 21l-6 -6" />
                    </svg>
                </button>
            </div>

            <div class="nav-item dropdown">
                <a href="#" class="nav-link d-flex lh-1 text-reset p-0" data-bs-toggle="dropdown"
                    aria-label="Open user menu">
                    <span class="img-rounded">
                        <img class="img-rounded"
                            src="{{ asset(auth::user()->profile_image == null ? 'profile.png' : auth::user()->profile_image) }}"
                            alt="{{ auth::user()->name }}">
                    </span>
                    <div class="d-none d-xl-block ps-2">
                        <div>{{ Auth::user()->name }}</div>
                        <div class="mt-1 small text-muted">
                            {{ Auth::user()->role_id == 4 ? __('Manager') : __('Administrator') }}</div>
                    </div>
                </a>
                <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                    <a href="{{ route('admin.account') }}" class="dropdown-item">{{ __('Profile & account') }}</a>
                    @if (count(config('app.languages')) > 1)
                        @php
                            $flagMap = config('app.flag-icons');
                        @endphp
                        <a class="dropdown-item d-flex align-items-center gap-2 py-2 lang-trigger"
                            data-bs-toggle="modal" data-bs-target="#languageModal">
                            <span class="fi fi-{{ $flagMap[app()->getLocale()] ?? 'us' }}"></span>
                            <span class="fw-semibold text-uppercase">
                                {{ __(app()->getLocale()) }}
                            </span>
                            <span class="ms-auto text-muted small">
                                {{ __('Change') }}
                            </span>
                        </a>
                    @endif
                    <a href="{{ route('admin.change.theme', 'dark') }}" class="dropdown-item hide-theme-dark">
                        {{ __('Dark mode') }}
                    </a>
                    <a href="{{ route('admin.change.theme', 'light') }}" class="dropdown-item hide-theme-light">
                        {{ __('Light mode') }}
                    </a>
                    <a href="{{ route('logout') }}" class="dropdown-item"
                        onclick="event.preventDefault(); document.getElementById('logout-form').submit();">{{ __('Logout') }}</a>
                    <form class="logout" id="logout-form" action="{{ route('logout') }}" method="POST">
                        @csrf
                    </form>
                </div>
            </div>
        </div>

        <div class="collapse navbar-collapse" id="sidebar-menu">
            <ul class="navbar-nav m-0 ml-lg-auto p-3 p-lg-0 overflow-y-auto bg-body-tertiary"
                style="z-index: 9999999 !important;">
                <li class="d-inline d-lg-none">
                    <button class="navbar-toggler float-right" type="button" data-bs-toggle="collapse"
                        data-bs-target="#sidebar-menu" aria-controls="sidebar-menu" aria-expanded="false"
                        aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                </li>

                {{-- SECTION 1: CORE CONSOLE --}}
                <li class="menu-title mt-2 mb-1 px-3 text-secondary text-uppercase fw-bold"
                    style="font-size: 0.65rem; letter-spacing: 0.08em; opacity: 0.8;">
                    {{ __('Main Console') }}
                </li>

                {{-- Dashboard --}}
                <li class="nav-item {{ request()->is('admin/dashboard') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('admin.dashboard') }}">
                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24"
                                viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                                stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                <polyline points="5 12 3 12 12 3 21 12 19 12" />
                                <path d="M5 12v7a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-7" />
                                <path d="M9 21v-6a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v6" />
                            </svg>
                        </span>
                        <span class="nav-link-title">
                            {{ __('Dashboard') }}
                        </span>
                    </a>
                </li>


                {{-- NEW SECTION TITLE: BUSINESS MANAGEMENT --}}
                {{-- Covers Themes, Customers, Plans, NFC Sales, AI Credit Plans, Website Content, Referral Program --}}
                @if (
                    $allowedPermissions['themes'] ||
                        $allowedPermissions['business_card_intros'] ||
                        $allowedPermissions['customers'] ||
                        $allowedPermissions['custom_domain'] ||
                        $allowedPermissions['plans'] ||
                        ($config[76]->config_value == '1' &&
                            ($allowedPermissions['nfc_card_design'] ||
                                $allowedPermissions['nfc_card_orders'] ||
                                $allowedPermissions['nfc_card_order_transactions'] ||
                                $allowedPermissions['nfc_card_key_generations'])) ||
                        ($allowedPermissions['ai_credits'] && $aibuilder_settings->aibuilder == 1) ||
                        $allowedPermissions['web_templates'] ||
                        $allowedPermissions['blogs'] ||
                        ($config[80]->config_value == '1' && $allowedPermissions['referral_system']))
                    <li class="menu-title mt-3 mb-1 px-3 text-secondary text-uppercase fw-bold"
                        style="font-size: 0.65rem; letter-spacing: 0.08em; opacity: 0.8;">
                        {{ __('My Business') }}
                    </li>
                @endif

                {{-- SECTION 2: DESIGN & GALLERY --}}
                @if ($allowedPermissions['themes'] || $allowedPermissions['business_card_intros'])
                    <li
                        class="nav-item dropdown {{ request()->is('admin/themes*') || request()->is('admin/edit-theme*') || request()->is('admin/search*') || request()->is('admin/business-card-intros*') ? 'active' : '' }}">
                        <a class="nav-link dropdown-toggle" href="#navbar-gallery" data-bs-toggle="dropdown"
                            role="button" aria-expanded="false">
                            <span class="nav-link-icon d-md-none d-lg-inline-block">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round"
                                    class="icon icon-tabler icons-tabler-outline icon-tabler-photo">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                    <path d="M15 8h.01" />
                                    <path
                                        d="M3 6a3 3 0 0 1 3 -3h12a3 3 0 0 1 3 3v12a3 3 0 0 1 -3 3h-12a3 3 0 0 1 -3 -3v-12z" />
                                    <path d="M3 16l5 -5c.928 -.893 2.072 -.893 3 0l5 5" />
                                    <path d="M14 14l1 -1c.928 -.893 2.072 -.893 3 0l3 3" />
                                </svg>
                            </span>
                            <span class="nav-link-title">{{ __('Themes') }}</span>
                        </a>
                        <div class="dropdown-menu">
                            @if ($allowedPermissions['themes'])
                                <a class="dropdown-item"
                                    href="{{ route('admin.themes', ['status' => 'all']) }}">{{ __('Manage Themes') }}</a>
                            @endif
                            @if ($allowedPermissions['business_card_intros'])
                                <a class="dropdown-item"
                                    href="{{ route('admin.business-card-intros') }}">{{ __('eCard Intros') }}</a>
                            @endif
                        </div>
                    </li>
                @endif


                {{-- SECTION 3: CUSTOMER MANAGEMENT --}}
                @if ($allowedPermissions['customers'] || $allowedPermissions['custom_domain'])
                    <li
                        class="nav-item dropdown {{ request()->is('admin/customers*') || request()->is('admin/create-customer') || request()->is('admin/created-cards') || request()->is('admin/created-stores') || request()->is('admin/custom-domain-requests*') || request()->is('admin/approved-custom-domain') || request()->is('admin/rejected-custom-domain') ? 'active' : '' }}">
                        <a class="nav-link dropdown-toggle" href="#navbar-customers" data-bs-toggle="dropdown"
                            role="button" aria-expanded="false">
                            <span class="nav-link-icon d-md-none d-lg-inline-block">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round"
                                    class="icon icon-tabler icons-tabler-outline icon-tabler-users-group">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                    <path d="M10 13a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" />
                                    <path d="M8 21v-1a2 2 0 0 1 2 -2h4a2 2 0 0 1 2 2v1" />
                                    <path d="M15 5a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" />
                                    <path d="M17 10h2a2 2 0 0 1 2 2v1" />
                                    <path d="M5 5a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" />
                                    <path d="M3 13v-1a2 2 0 0 1 2 -2h2" />
                                </svg>
                            </span>
                            <span class="nav-link-title">{{ __('Customers') }}</span>
                        </a>
                        <div class="dropdown-menu">
                            @if ($allowedPermissions['customers'])
                                <a class="dropdown-item"
                                    href="{{ route('admin.customers', ['status' => 'all']) }}">{{ __('Manage Customers') }}</a>
                                <a class="dropdown-item"
                                    href="{{ route('admin.created.cards') }}">{{ __('Created eCards') }}</a>
                                <a class="dropdown-item"
                                    href="{{ route('admin.created.stores') }}">{{ __('Stores') }}</a>
                            @endif
                            @if ($allowedPermissions['custom_domain'])
                                <a class="dropdown-item"
                                    href="{{ route('admin.custom.domain.requests', ['status' => 'all']) }}">{{ __('Custom Domains') }}</a>
                            @endif
                        </div>
                    </li>
                @endif

                {{-- Vcard / Store --}}
                @if ($allowedPermissions['vcard_store'])
                    <li
                        class="nav-item dropdown {{ request()->is('admin/vcard-store') || request()->is('admin/create-vcard-store') || request()->is('admin/edit-vcard-store/*') || request()->is('admin/delete-vcard-store') ? 'active' : '' }}">
                        <a class="nav-link dropdown-toggle" href="#navbar-extra" data-bs-toggle="dropdown"
                            role="button" aria-expanded="false">
                            <span class="nav-link-icon d-md-none d-lg-inline-block">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round"
                                    class="icon icon-tabler icons-tabler-outline icon-tabler-cards">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                    <path
                                        d="M3.604 7.197l7.138 -3.109a.96 .96 0 0 1 1.27 .527l4.924 11.902a1 1 0 0 1 -.514 1.304l-7.137 3.109a.96 .96 0 0 1 -1.271 -.527l-4.924 -11.903a1 1 0 0 1 .514 -1.304z" />
                                    <path d="M15 4h1a1 1 0 0 1 1 1v3.5" />
                                    <path d="M20 6c.264 .112 .52 .217 .768 .315a1 1 0 0 1 .53 1.311l-2.298 5.374" />
                                </svg>
                            </span>
                            <span class="nav-link-title">
                                {{ __('eCard & Store') }}
                            </span>
                        </a>
                        <div class="dropdown-menu">
                            <a class="dropdown-item"
                                href="{{ route('admin.create.vcard.store', ['id' => 'choose.card.type']) }}">
                                {{ __('Create eCard') }}
                            </a>

                            <a class="dropdown-item"
                                href="{{ route('admin.create.vcard.store', ['id' => 'create.store']) }}">
                                {{ __('Create Store') }}
                            </a>

                            <a class="dropdown-item" href="{{ route('admin.create.vcard.store', ['id' => 'cards']) }}">
                                {{ __('Manage created eCards') }}
                            </a>

                            <a class="dropdown-item" href="{{ route('admin.create.vcard.store', ['id' => 'stores']) }}">
                                {{ __('Manage created stores') }}
                            </a>
                        </div>
                    </li>
                @endif

                {{-- SECTION 4: Plans --}}
                @if ($allowedPermissions['plans'])
                    <li
                        class="nav-item {{ request()->is('admin/plans') || request()->is('admin/add-plan') || request()->is('admin/edit-plan/*') || request()->is('admin/status-plan/*') || request()->is('admin/delete-plan') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('admin.plans') }}">
                            <span class="nav-link-icon d-md-none d-lg-inline-block">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round"
                                    class="icon icon-tabler icons-tabler-outline icon-tabler-tag">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                    <path d="M6.5 7.5a1 1 0 1 0 2 0a1 1 0 1 0 -2 0" />
                                    <path
                                        d="M3 6v5.172a2 2 0 0 0 .586 1.414l7.71 7.71a2.41 2.41 0 0 0 3.408 0l5.592 -5.592a2.41 2.41 0 0 0 0 -3.408l-7.71 -7.71a2 2 0 0 0 -1.414 -.586h-5.172a3 3 0 0 0 -3 3" />
                                </svg>
                            </span>
                            <span class="nav-link-title">
                                {{ __('Plans') }}
                            </span>
                        </a>
                    </li>
                @endif

                {{-- SECTION 5: NFC CARDS --}}
                @if ($config[76]->config_value == '1')
                    @if (
                        $allowedPermissions['nfc_card_design'] ||
                            $allowedPermissions['nfc_card_orders'] ||
                            $allowedPermissions['nfc_card_order_transactions'] ||
                            $allowedPermissions['nfc_card_key_generations']
                    )
                        <li
                            class="nav-item dropdown {{ request()->is('admin/nfc-card-design') || request()->is('admin/nfc-card*') || request()->is('admin/nfc-card-order-transactions') ? 'active' : '' }}">
                            <a class="nav-link dropdown-toggle" href="#navbar-nfc" data-bs-toggle="dropdown"
                                role="button" aria-expanded="false">
                                <span class="nav-link-icon d-md-none d-lg-inline-block">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round"
                                        class="icon icon-tabler icons-tabler-outline icon-tabler-nfc">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                        <path d="M11 20a3 3 0 0 1 -3 -3v-11l5 5" />
                                        <path d="M13 4a3 3 0 0 1 3 3v11l-5 -5" />
                                        <path
                                            d="M4 4m0 3a3 3 0 0 1 3 -3h10a3 3 0 0 1 3 3v10a3 3 0 0 1 -3 3h-10a3 3 0 0 1 -3 -3z" />
                                    </svg>
                                </span>
                                <span class="nav-link-title">{{ __('NFC Sales') }}</span>
                            </a>
                            <div class="dropdown-menu">
                                @if ($allowedPermissions['nfc_card_design'])
                                    <a href="{{ route('admin.designs') }}"
                                        class="dropdown-item">{{ __('NFC Cards') }}</a>
                                @endif
                                @if ($allowedPermissions['nfc_card_orders'])
                                    <a href="{{ route('admin.orders') }}"
                                        class="dropdown-item">{{ __('Orders') }}</a>
                                @endif
                                @if ($allowedPermissions['nfc_card_key_generations'])
                                    <a href="{{ route('admin.key.generations') }}"
                                        class="dropdown-item">{{ __('Activation Keys') }}</a>
                                @endif
                                @if ($allowedPermissions['nfc_card_order_transactions'])
                                    <a href="{{ route('admin.transactions') }}"
                                        class="dropdown-item">{{ __('Transactions') }}</a>
                                @endif
                            </div>
                        </li>
                    @endif
                @endif

                {{-- SECTION 6: AI CREDIT PLANS --}}
                @if ($allowedPermissions['ai_credits'] && $aibuilder_settings->aibuilder == 1)
                    <li
                        class="nav-item dropdown {{ request()->is('admin/ai-credits-plans') || request()->is('admin/ai-credits/create-plan') || request()->is('admin/ai-credits/edit-plan/*') || request()->is('admin/ai-credits/transactions*') ? 'active' : '' }}">
                        <a class="nav-link dropdown-toggle" href="#navbar-ai-credits" data-bs-toggle="dropdown"
                            role="button" aria-expanded="false">
                            <span class="nav-link-icon d-md-none d-lg-inline-block">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round"
                                    class="icon icon-tabler icons-tabler-outline icon-tabler-credits">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                    <path d="M3 14a6 2 0 1 0 12 0a6 2 0 1 0 -12 0" />
                                    <path d="M3 14v5c0 1.105 2.686 2 6 2s6 -.895 6 -2v-5" />
                                    <path d="M9 5a6 2 0 1 0 12 0a6 2 0 1 0 -12 0" />
                                    <path d="M9 5v3" />
                                    <path d="M18.365 11.656c1.59 -.36 2.635 -.966 2.635 -1.656v-5" />
                                </svg>
                            </span>
                            <span class="nav-link-title">{{ __('AI Credit Plans') }}</span>
                        </a>
                        <div class="dropdown-menu">
                            <a class="dropdown-item"
                                href="{{ route('admin.ai.credits.plans') }}">{{ __('Plans') }}</a>
                            <a class="dropdown-item"
                                href="{{ route('admin.ai.credits.transactions') }}">{{ __('Transactions') }}</a>
                        </div>
                    </li>
                @endif


                {{-- SECTION 7: WEBSITE CONTENT --}}
                @if ($allowedPermissions['web_templates'] || $allowedPermissions['blogs'])
                    <li
                        class="nav-item dropdown {{ request()->is('admin/web-templates*') || request()->is('admin/web-template*') || request()->is('admin/blogs*') || request()->is('admin/create-blog*') || request()->is('admin/edit-blog/*') || request()->is('admin/blog-categories*') || request()->is('admin/create-blog-category*') || request()->is('admin/edit-blog-category/*') || request()->is('admin/delete-blog-category/*') ? 'active' : '' }}">
                        <a class="nav-link dropdown-toggle" href="#navbar-content" data-bs-toggle="dropdown"
                            role="button" aria-expanded="false">
                            <span class="nav-link-icon d-md-none d-lg-inline-block">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round"
                                    class="icon icon-tabler icons-tabler-outline icon-tabler-article">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                    <path
                                        d="M3 4m0 2a2 2 0 0 1 2 -2h14a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2z" />
                                    <path d="M7 8h10" />
                                    <path d="M7 12h10" />
                                    <path d="M7 16h10" />
                                </svg>
                            </span>
                            <span class="nav-link-title">{{ __('Website Content') }}</span>
                        </a>
                        <div class="dropdown-menu">
                            @if ($allowedPermissions['web_templates'])
                                <a class="dropdown-item"
                                    href="{{ route('admin.web-templates') }}">{{ __('Web Templates') }}</a>
                            @endif
                            @if ($allowedPermissions['blogs'])
                                <a class="dropdown-item" href="{{ route('admin.blogs') }}">{{ __('Blogs') }}</a>
                                <a class="dropdown-item"
                                    href="{{ route('admin.blog.categories') }}">{{ __('Blog Categories') }}</a>
                            @endif
                        </div>
                    </li>
                @endif


                {{-- SECTION 8: REFERRAL PROGRAM --}}
                @if ($config[80]->config_value == '1' && $allowedPermissions['referral_system'])
                    <li
                        class="nav-item dropdown {{ request()->is('admin/referrals*') || request()->is('admin/referral-withdrawal-requests*') || request()->is('admin/referral-system-configuration*') ? 'active' : '' }}">
                        <a class="nav-link dropdown-toggle" href="#navbar-referrals" data-bs-toggle="dropdown"
                            role="button" aria-expanded="false">
                            <span class="nav-link-icon d-md-none d-lg-inline-block">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round"
                                    class="icon icon-tabler icon-tabler-users">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                    <circle cx="9" cy="7" r="4"></circle>
                                    <path d="M3 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2"></path>
                                </svg>
                            </span>
                            <span class="nav-link-title">{{ __('Referral Program') }}</span>
                        </a>
                        <div class="dropdown-menu">
                            <a class="dropdown-item" href="{{ route('admin.referrals') }}">{{ __('Referrals') }}</a>
                            @unless ($hidesRevenue)
                            <a class="dropdown-item"
                                href="{{ route('admin.referral.withdrawal.request') }}">{{ __('Withdrawal Requests') }}</a>
                            <a class="dropdown-item"
                                href="{{ route('admin.referral.system.configuration') }}">{{ __('Configuration') }}</a>
                            @endunless
                        </div>
                    </li>
                @endif


                {{-- SECTION 9: MARKETING & PROMOTIONS --}}
                @if ($allowedPermissions['coupons'] || $allowedPermissions['marketing'])
                    <li class="menu-title mt-3 mb-1 px-3 text-secondary text-uppercase fw-bold"
                        style="font-size: 0.65rem; letter-spacing: 0.08em; opacity: 0.8;">
                        {{ __('Marketing') }}
                    </li>

                    {{-- Coupons --}}
                    @if ($allowedPermissions['coupons'])
                        <li
                            class="nav-item {{ request()->is('admin/coupons*') || request()->is('admin/create-coupon') || request()->is('admin/edit-coupon/*') || request()->is('admin/statistics-coupon*') ? 'active' : '' }}">
                            <a class="nav-link text-decoration-none" href="{{ route('admin.coupons') }}">
                                <span class="nav-link-icon d-md-none d-lg-inline-block">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round"
                                        class="icon icon-tabler icons-tabler-outline icon-tabler-discount">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                        <path d="M9 15l6 -6" />
                                        <circle cx="9.5" cy="9.5" r=".5" fill="currentColor" />
                                        <circle cx="14.5" cy="14.5" r=".5" fill="currentColor" />
                                        <path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0" />
                                    </svg>
                                </span>
                                <span class="nav-link-title">
                                    {{ __('Coupons') }}
                                </span>
                            </a>
                        </li>
                    @endif

                    {{-- Campaigns --}}
                    @if ($allowedPermissions['marketing'])
                        <li
                            class="nav-item dropdown {{ request()->is('admin/marketing/campaigns') || request()->is('admin/marketing/campaigns/*') || request()->is('admin/marketing/groups') || request()->is('admin/marketing/groups/*') || request()->is('admin/marketing/customers') || request()->is('admin/marketing/customers/*') || request()->is('admin/marketing/mailgun') || request()->is('admin/marketing/mailgun/*') ? 'active' : '' }}">
                            <a class="nav-link dropdown-toggle" href="#navbar-marketing-campaigns"
                                data-bs-toggle="dropdown" role="button" aria-expanded="false">
                                <span class="nav-link-icon d-md-none d-lg-inline-block">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round"
                                        class="icon icon-tabler icons-tabler-outline icon-tabler-mail-opened">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                        <path d="M3 9l9 6l9 -6l-9 -6l-9 6" />
                                        <path d="M21 9v10a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2v-10" />
                                        <path d="M3 19l6 -6" />
                                        <path d="M15 13l6 6" />
                                    </svg>
                                </span>
                                <span class="nav-link-title">{{ __('Email Marketing') }}</span>
                            </a>
                            <div class="dropdown-menu">
                                <a class="dropdown-item"
                                    href="{{ route('admin.marketing.campaigns') }}">{{ __('Campaigns') }}</a>
                                <a class="dropdown-item"
                                    href="{{ route('admin.marketing.groups') }}">{{ __('Groups') }}</a>
                                <a class="dropdown-item"
                                    href="{{ route('admin.marketing.customers') }}">{{ __('Customers') }}</a>
                                <a class="dropdown-item"
                                    href="{{ route('admin.marketing.mailgun') }}">{{ __('Mailgun Configuration') }}</a>
                            </div>
                        </li>

                        {{-- Pusher Notification --}}
                        <li
                            class="nav-item dropdown {{ request()->is('admin/marketing/pusher-notification') || request()->is('admin/marketing/pusher-notification/*') || request()->is('admin/marketing/pusher') ? 'active' : '' }}">
                            <a class="nav-link dropdown-toggle" href="#navbar-notifications"
                                data-bs-toggle="dropdown" role="button" aria-expanded="false">
                                <span class="nav-link-icon d-md-none d-lg-inline-block">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round"
                                        class="icon icon-tabler icons-tabler-outline icon-tabler-send">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                        <path d="M10 14l11 -11" />
                                        <path
                                            d="M21 3l-6.5 18a.55 .55 0 0 1 -1 0l-3.5 -7l-7 -3.5a.55 .55 0 0 1 0 -1l18 -6.5" />
                                    </svg>
                                </span>
                                <span class="nav-link-title">{{ __('Web Notifications') }}</span>
                            </a>
                            <div class="dropdown-menu">
                                <a class="dropdown-item"
                                    href="{{ route('admin.marketing.pusher.notification') }}">{{ __('Notification') }}</a>
                                <a class="dropdown-item"
                                    href="{{ route('admin.marketing.pusher') }}">{{ __('Pusher Configurations') }}</a>
                            </div>
                        </li>
                    @endif
                @endif


                {{-- SECTION 11: FINANCIAL RECORDS --}}
                @if (
                    $allowedPermissions['transactions'] ||
                        ($allowedPermissions['in_app_purchases'] && $mobile_app_api_enabled == 1) ||
                        $allowedPermissions['payment_methods']
                )
                    <li class="menu-title mt-3 mb-1 px-3 text-secondary text-uppercase fw-bold"
                        style="font-size: 0.65rem; letter-spacing: 0.08em; opacity: 0.8;">
                        {{ __('Sales & Payments') }}
                    </li>

                    {{-- Unified Financial Transactions --}}
                    @if ($allowedPermissions['transactions'])
                        <li
                            class="nav-item {{ request()->is('admin/transactions*') || request()->is('admin/view-invoice*') ? 'active' : '' }}">
                            <a class="nav-link text-decoration-none" href="{{ route('admin.transactions.index') }}">
                                <span class="nav-link-icon d-md-none d-lg-inline-block">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round"
                                        class="icon icon-tabler icons-tabler-outline icon-tabler-report-money">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                        <path
                                            d="M9 5h-2a2 2 0 0 0 -2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-12a2 2 0 0 0 -2 -2h-2" />
                                        <path
                                            d="M9 3m0 2a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v0a2 2 0 0 1 -2 2h-2a2 2 0 0 1 -2 -2z" />
                                        <path d="M14 11h-2.5a1.5 1.5 0 0 0 0 3h1a1.5 1.5 0 0 1 0 3h-2.5" />
                                        <path d="M12 17v1m0 -8v1" />
                                    </svg>
                                </span>
                                <span class="nav-link-title">
                                    {{ __('Transactions') }}
                                </span>
                            </a>
                        </li>
                    @endif

                    {{-- In App Purchases --}}
                    @if ($allowedPermissions['in_app_purchases'] && $mobile_app_api_enabled == 1)
                        <li class="nav-item {{ request()->is('admin/in-app-purchases') ? 'active' : '' }}">
                            <a class="nav-link text-decoration-none" href="{{ route('admin.in-app-purchases') }}">
                                <span class="nav-link-icon d-md-none d-lg-inline-block">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round"
                                        class="icon icon-tabler icons-tabler-outline icon-tabler-report-money">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                        <path
                                            d="M9 5h-2a2 2 0 0 0 -2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-12a2 2 0 0 0 -2 -2h-2" />
                                        <path
                                            d="M9 3m0 2a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v0a2 2 0 0 1 -2 2h-2a2 2 0 0 1 -2 -2z" />
                                        <path d="M14 11h-2.5a1.5 1.5 0 0 0 0 3h1a1.5 1.5 0 0 1 0 3h-2.5" />
                                        <path d="M12 17v1m0 -8v1" />
                                    </svg>
                                </span>
                                <span class="nav-link-title">
                                    {{ __('In App Purchases') }}
                                </span>
                            </a>
                        </li>
                    @endif

                    {{-- Payment Gateways --}}
                    @if ($allowedPermissions['payment_methods'])
                        <li
                            class="nav-item {{ request()->is('admin/payment-methods') || request()->is('admin/configure-payment-method*') || request()->is('admin/edit-payment-method/*') || request()->is('admin/delete-payment-method') ? 'active' : '' }}">
                            <a class="nav-link text-decoration-none" href="{{ route('admin.payment.methods') }}">
                                <span class="nav-link-icon d-md-none d-lg-inline-block">
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                        class="icon icon-tabler icon-tabler-building-bank" width="24"
                                        height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                        fill="none" stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                        <line x1="3" y1="21" x2="21" y2="21"></line>
                                        <line x1="3" y1="10" x2="21" y2="10"></line>
                                        <polyline points="5 6 12 3 19 6"></polyline>
                                        <line x1="4" y1="10" x2="4" y2="21"></line>
                                        <line x1="20" y1="10" x2="20" y2="21"></line>
                                        <line x1="8" y1="14" x2="8" y2="17"></line>
                                        <line x1="12" y1="14" x2="12" y2="17"></line>
                                        <line x1="16" y1="14" x2="16" y2="17"></line>
                                    </svg>
                                </span>
                                <span class="nav-link-title">
                                    {{ __('Payment Gateways') }}
                                </span>
                            </a>
                        </li>
                    @endif
                @endif


                {{-- SECTION 13: SYSTEM UTILITIES --}}
                @if (
                    $allowedPermissions['general_settings'] ||
                        $allowedPermissions['currencies'] ||
                        $allowedPermissions['email_templates'] ||
                        $allowedPermissions['translations'] ||
                        $allowedPermissions['backup'] ||
                        $allowedPermissions['users'] ||
                        $allowedPermissions['plugins']
                )
                    <li class="menu-title mt-3 mb-1 px-3 text-secondary text-uppercase fw-bold"
                        style="font-size: 0.65rem; letter-spacing: 0.08em; opacity: 0.8;">
                        {{ __('Settings & Tools') }}
                    </li>

                    {{-- Plugins --}}
                    @if ($allowedPermissions['plugins'])
                        <li
                            class="nav-item {{ request()->is('admin/plugins') || request()->is('admin/plugin*') ? 'active' : '' }}">
                            <a class="nav-link text-decoration-none" href="{{ route('admin.plugins') }}">
                                <span class="nav-link-icon d-md-none d-lg-inline-block">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round"
                                        class="icon icon-tabler icons-tabler-outline icon-tabler-apps">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                        <path
                                            d="M4 4m0 1a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v4a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1z" />
                                        <path
                                            d="M4 14m0 1a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v4a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1z" />
                                        <path
                                            d="M14 14m0 1a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v4a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1z" />
                                        <path d="M14 7l6 0" />
                                        <path d="M17 4l0 6" />
                                    </svg>
                                </span>
                                <span class="nav-link-title">
                                    {{ __('Plugins') }}
                                </span>
                            </a>
                        </li>
                    @endif

                    {{-- WhatsApp Chat Button Settings --}}
                    @if ($allowedPermissions['plugins'])
                        <li class="nav-item {{ request()->is('admin/plugin/whatsapp_chat_button/settings') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ url('admin/plugin/whatsapp_chat_button/settings') }}">
                                <span class="nav-link-icon d-md-none d-lg-inline-block">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round"
                                        class="icon icon-tabler icons-tabler-outline icon-tabler-brand-whatsapp">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                        <path d="M3 21l1.65 -3.8a9 9 0 1 1 3.4 2.9l-5.05 .9" />
                                        <path d="M9 10a.5 .5 0 0 0 1 0v-1a.5 .5 0 0 0 -1 0v1a5 5 0 0 0 5 5h1a.5 .5 0 0 0 0 -1h-1a.5 .5 0 0 0 0 1" />
                                    </svg>
                                </span>
                                <span class="nav-link-title">
                                    {{ __('WhatsApp Settings') }}
                                </span>
                            </a>
                        </li>
                    @endif

                    <li
                        class="nav-item dropdown {{ request()->is('admin/settings*') || request()->is('admin/cron*') || request()->is('admin/sitemap*') || request()->is('admin/currencies*') || request()->is('admin/create-currency') || request()->is('admin/edit-currency*') || request()->is('admin/users*') || request()->is('admin/create-user') || request()->is('admin/edit-user*') || request()->is('admin/roles') || request()->is('admin/create-role') || request()->is('admin/edit-role*') || request()->is('admin/email-templates*') || request()->is('admin/translations*') || request()->is('admin/backups*') || request()->is('admin/clear-cache*') || request()->is('admin/software-update*') ? 'active' : '' }}">
                        <a class="nav-link dropdown-toggle" href="#navbar-system" data-bs-toggle="dropdown"
                            role="button" aria-expanded="false">
                            <span class="nav-link-icon d-md-none d-lg-inline-block">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-settings"
                                    width="24" height="24" viewBox="0 0 24 24" stroke-width="2"
                                    stroke="currentColor" fill="none" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                    <path
                                        d="M10.325 4.317c.426 -1.756 2.924 -1.756 3.35 0a1.724 1.724 0 0 0 2.573 1.066c1.543 -.94 3.31 .826 2.37 2.37a1.724 1.724 0 0 0 1.065 2.572c1.756 .426 1.756 2.924 0 3.35a1.724 1.724 0 0 0 -1.066 2.573c.94 1.543 -.826 3.31 -2.37 2.37a1.724 1.724 0 0 0 -2.572 1.065c-.426 1.756 -2.924 1.756 -3.35 0a1.724 1.724 0 0 0 -2.573 -1.066c-1.543 .94 -3.31 -.826 -2.37 -2.37a1.724 1.724 0 0 0 -1.065 -2.572c-1.756 -.426 -1.756 -2.924 0 -3.35a1.724 1.724 0 0 0 1.066 -2.573c-.94 -1.543 .826 -3.31 2.37 -2.37c1 .608 2.296 .07 2.572 -1.065z">
                                    </path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                            </span>
                            <span class="nav-link-title">{{ __('System Utilities') }}</span>
                        </a>
                        <div class="dropdown-menu">
                            @if ($allowedPermissions['general_settings'])
                                <a class="dropdown-item"
                                    href="{{ route('admin.settings') }}">{{ __('General Settings') }}</a>
                                <a class="dropdown-item"
                                    href="{{ route('admin.cron.jobs') }}">{{ __('Cron Jobs') }}</a>
                            @endif
                            @if ($allowedPermissions['sitemap'])
                                <a class="dropdown-item"
                                    href="{{ route('admin.sitemap') }}">{{ __('Generate Sitemap') }}</a>
                            @endif
                            @if ($allowedPermissions['invoice_tax'])
                                <a class="dropdown-item"
                                    href="{{ route('admin.tax.setting') }}">{{ __('Invoice & Tax Settings') }}</a>
                            @endif
                            @if ($allowedPermissions['currencies'])
                                <a class="dropdown-item"
                                    href="{{ route('admin.currencies') }}">{{ __('Currencies') }}</a>
                            @endif
                            @if ($allowedPermissions['users'])
                                <a class="dropdown-item"
                                    href="{{ route('admin.users') }}">{{ __('Administrators & Staff') }}</a>
                                <a class="dropdown-item"
                                    href="{{ route('admin.roles') }}">{{ __('Roles') }}</a>
                            @endif
                            @if ($allowedPermissions['email_templates'])
                                <a class="dropdown-item"
                                    href="{{ route('admin.email.templates.index', ['id' => '584922675196']) }}">{{ __('Email Templates') }}</a>
                            @endif
                            @if ($allowedPermissions['translations'])
                                <a class="dropdown-item"
                                    href="{{ route('translation-manager.index') }}">{{ __('System Translations') }}</a>
                            @endif
                            @if ($allowedPermissions['backup'])
                                <a class="dropdown-item"
                                    href="{{ route('admin.backups') }}">{{ __('System Backups') }}</a>
                            @endif
                            @if ($allowedPermissions['general_settings'])
                                <form action="{{ route('admin.clear.cache') }}" method="post" class="m-0">
                                    @csrf
                                    <button type="submit" class="dropdown-item">{{ __('Clear App Cache') }}</button>
                                </form>
                            @endif
                            @if ($allowedPermissions['software_update'])
                                <a class="dropdown-item"
                                    href="{{ route('admin.check') }}">{{ __('Software Updates') }}</a>
                            @endif
                        </div>
                    </li>
                @endif


                {{-- SECTION 14: MY ACCOUNT --}}
                <li class="menu-title mt-3 mb-1 px-3 text-secondary text-uppercase fw-bold"
                    style="font-size: 0.65rem; letter-spacing: 0.08em; opacity: 0.8;">
                    {{ __('Personal') }}
                </li>

                <li class="nav-item {{ request()->is('admin/account') ? 'active' : '' }}">
                    <a class="nav-link text-decoration-none" href="{{ route('admin.account') }}">
                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24"
                                viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                                stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                <circle cx="9" cy="7" r="4"></circle>
                                <path d="M3 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2"></path>
                                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                                <path d="M21 21v-2a4 4 0 0 0 -3 -3.85"></path>
                            </svg>
                        </span>
                        <span class="nav-link-title">
                            {{ __('My Account') }}
                        </span>
                    </a>
                </li>

            </ul>
        </div>
    </div>
</aside>
