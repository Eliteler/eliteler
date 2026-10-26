@extends('admin.layouts.index', ['header' => true, 'nav' => true, 'demo' => true])

@section('css')
    <style>
        .plans-container {
            background-color: #fbfeff;
            min-height: 100vh;
        }

        /* Page Header */
        .page-title-text {
            font-size: 1.5rem;
            font-weight: 700;
            color: #0f172a;
        }

        .btn-create-plan {
            background-color: var(--tblr-btn-hover-bg);
            color: #ffffff;
            border-radius: 10px;
            padding: 10px 20px;
            font-weight: 600;
            border: none;
            transition: all 0.2s ease;
        }

        .btn-create-plan:hover {
            background-color: var(--tblr-btn-hover-bg);
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.25);
        }

        /* Tabs & Search Filter Bar */
        .nav-pill-custom .nav-link {
            color: #64748b;
            font-weight: 600;
            border-radius: 10px;
            padding: 8px 16px;
            background: #f1f5f9;
            margin-right: 8px;
            border: 1px solid transparent;
            transition: all 0.2s ease;
        }

        .nav-pill-custom .nav-link.active {
            background-color: #e0e7ff;
            color: var(--tblr-btn-hover-bg);
            border-color: #c7d2fe;
        }

        .count-badge {
            font-size: 0.75rem;
            padding: 2px 8px;
            border-radius: 6px;
            margin-left: 6px;
            background: #e2e8f0;
            color: #334155;
        }

        .nav-link.active .count-badge {
            background: #ffffff;
            color: var(--tblr-btn-hover-bg);
        }

        .search-input-group {
            position: relative;
            width: 260px;
        }

        .search-input-group input {
            border-radius: 10px;
            border: 1px solid #e2e8f0;
            padding-left: 40px;
            height: 42px;
            font-size: 0.9rem;
        }

        .search-input-group .search-icon {
            position: absolute;
            left: 12px;
            top: 11px;
            color: #94a3b8;
        }

        /* Plan Cards Design */
        .modern-plan-card {
            background: #ffffff;
            border: 1px solid #f1f5f9;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .modern-plan-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 28px rgba(0, 0, 0, 0.06);
        }

        .plan-icon-box {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .plan-icon-purple {
            background: #eef2ff;
            color: var(--tblr-btn-hover-bg);
        }

        .plan-icon-green {
            background: #f0fdf4;
            color: #22c55e;
        }

        .plan-icon-orange {
            background: #fff7ed;
            color: #f97316;
        }

        .status-badge-active {
            background-color: #f0fdf4;
            color: #16a34a;
            font-weight: 600;
            border-radius: 8px;
            padding: 4px 12px;
        }

        .status-badge-disabled {
            background-color: #f1f5f9;
            color: #64748b;
            font-weight: 600;
            border-radius: 8px;
            padding: 4px 12px;
        }

        .plan-price-title {
            font-size: 1.8rem;
            font-weight: 800;
            color: #0f172a;
        }

        .plan-price-period {
            color: #64748b;
            font-size: 0.9rem;
            font-weight: 500;
        }

        /* Feature Items List Styling */
        .feature-item-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .feature-item-list li {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
            font-size: 0.88rem;
            color: #475569;
            font-weight: 500;
        }

        .feature-section-title {
            font-size: 0.75rem;
            letter-spacing: 0.5px;
            margin-top: 14px;
            margin-bottom: 8px !important;
            border-bottom: 1px dashed #e2e8f0;
            padding-bottom: 4px;
        }

        .feature-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .feature-icon.is-on {
            color: #16a34a;
        }

        .feature-icon.is-off {
            color: #94a3b8;
        }

        .opacity-50 {
            opacity: 0.55;
        }

        /* Custom Toggle Switch */
        .form-switch .form-check-input {
            width: 2.6em;
            height: 1.4em;
            cursor: pointer;
        }

        .btn-action-soft-edit {
            background: #f1f5f9;
            color: var(--tblr-btn-hover-bg);
            border-radius: 10px;
            width: 38px;
            height: 38px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: none;
            transition: all 0.2s;
        }

        .btn-action-soft-edit:hover {
            background: #e0e7ff;
            color: var(--tblr-btn-hover-bg);
        }

        .btn-action-soft-delete {
            background: #fef2f2;
            color: #ef4444;
            border-radius: 10px;
            width: 38px;
            height: 38px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: none;
            transition: all 0.2s;
        }

        .btn-action-soft-delete:hover {
            background: #fee2e2;
            color: #dc2626;
        }

        /* Clean Footer Pagination */
        .pagination {
            gap: 6px;
            margin-bottom: 0;
        }

        .pagination .page-item .page-link {
            border-radius: 8px !important;
            border: 1px solid #e2e8f0;
            color: #475569;
            font-weight: 600;
            padding: 6px 12px;
        }

        .pagination .page-item.active .page-link {
            background-color: var(--primary-color) !important;
            border-color: var(--primary-color) !important;
            color: #ffffff !important;
        }
    </style>
@endsection

@section('content')
    <div class="page-wrapper plans-container py-4">
        <div class="container-fluid">

            <!-- Top Header Bar -->
            <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
                <div>
                    <h2 class="page-title-text mb-1">{{ __('Pricing Plans') }}</h2>
                    <p class="text-muted mb-0 small">{{ __('Create and manage pricing plans for your customers.') }}</p>
                </div>
                <div>
                    <a href="{{ route('admin.add.plan') }}"
                        class="btn btn-primary btn-icon-only d-inline-flex align-items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                            stroke-linejoin="round">
                            <path d="M12 5v14M5 12h14" />
                        </svg>
                        <span class="d-none d-sm-inline">{{ __('Create New Plan') }}</span>
                    </a>
                </div>
            </div>

            {{-- Failed alert --}}
            @if (Session::has('failed'))
                <div class="alert alert-important alert-danger alert-dismissible mb-2" role="alert">
                    <div class="d-flex">
                        <div>
                            {{ Session::get('failed') }}
                        </div>
                    </div>
                    <a class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="close"></a>
                </div>
            @endif

            {{-- Success alert --}}
            @if (Session::has('success'))
                <div class="alert alert-important alert-success alert-dismissible mb-2" role="alert">
                    <div class="d-flex">
                        <div>
                            {{ Session::get('success') }}
                        </div>
                    </div>
                    <a class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="close"></a>
                </div>
            @endif

            <!-- Filter Tabs & Search Controls -->
            <div class="d-flex flex-wrap align-items-center justify-content-between mb-4 gap-3">
                <ul class="nav nav-pill-custom d-flex align-items-center">
                    <li class="nav-item my-2">
                        <a class="nav-link {{ !request('status') || request('status') == 'all' ? 'active' : '' }}"
                            href="{{ route('admin.plans', ['status' => 'all']) }}">
                            {{ __('All Plans') }}
                        </a>
                    </li>
                    <li class="nav-item my-2">
                        <a class="nav-link {{ request('status') == 'active' ? 'active' : '' }}"
                            href="{{ route('admin.plans', ['status' => 'active']) }}">
                            {{ __('Active') }} <span class="count-badge">{{ $active_count }}</span>
                        </a>
                    </li>
                    <li class="nav-item my-2">
                        <a class="nav-link {{ request('status') == 'disabled' ? 'active' : '' }}"
                            href="{{ route('admin.plans', ['status' => 'disabled']) }}">
                            {{ __('Disabled') }} <span class="count-badge">{{ $disabled_count }}</span>
                        </a>
                    </li>
                </ul>

                <form action="{{ route('admin.plans') }}" method="GET" class="d-flex align-items-center gap-2">
                    @if (request('status'))
                        <input type="hidden" name="status" value="{{ request('status') }}">
                    @endif
                    <div class="search-input-group">
                        <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round">
                            <circle cx="11" cy="11" r="8" />
                            <path d="m21 21-4.3-4.3" />
                        </svg>
                        <input type="text" name="search" class="form-control" placeholder="{{ __('Search plans...') }}"
                            value="{{ request('search') }}">
                    </div>
                </form>
            </div>

            <!-- Plans Cards Grid -->
            <div class="row row-cards">
                @forelse ($plans as $index => $plan)
                    @php
                        // Dynamic Theme Styles based on plan index
                        $themeClass = 'purple';
                        if ($index % 3 == 1) {
                            $themeClass = 'green';
                        }
                        if ($index % 3 == 2) {
                            $themeClass = 'orange';
                        }
                    @endphp

                    <div class="col-md-4 col-lg-4 mb-4">
                        <div class="modern-plan-card p-4 d-flex flex-column h-100">

                            <!-- Top Icon & Status -->
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div class="plan-icon-box plan-icon-{{ $themeClass }}">
                                    @if ($themeClass == 'purple')
                                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <polygon
                                                points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" />
                                        </svg>
                                    @elseif($themeClass == 'green')
                                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M5 16L3 5l5.5 5L12 4l3.5 6L21 5l-2 11H5z" />
                                            <path d="M19 19H5" />
                                        </svg>
                                    @else
                                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path
                                                d="M4.5 16.5c-1.5 1.26-2 5-2 5s3.74-.5 5-2c.71-.71 1.1-1.63 1.1-2.58a3.1 3.1 0 0 0-3.1-3.1c-.95 0-1.87.39-2.58 1.1z" />
                                            <path
                                                d="M12 15l-3-3 7.5-7.5c.78-.78 2.05-.78 2.83 0l.17.17c.78.78.78 2.05 0 2.83L12 15z" />
                                        </svg>
                                    @endif
                                </div>

                                <div class="d-flex align-items-center gap-2">
                                    @if ($plan->status == 1)
                                        <span class="status-badge-active">{{ __('Active') }}</span>
                                    @else
                                        <span class="status-badge-disabled">{{ __('Disabled') }}</span>
                                    @endif

                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-icon btn-link border-0 bg-transparent text-muted"
                                            type="button" data-bs-toggle="dropdown">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2">
                                                <circle cx="12" cy="12" r="1" />
                                                <circle cx="12" cy="5" r="1" />
                                                <circle cx="12" cy="19" r="1" />
                                            </svg>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end rounded-3 shadow-sm border-0">
                                            <li><a class="dropdown-item"
                                                    href="{{ route('admin.edit.plan', $plan->plan_id) }}">{{ __('Edit Plan') }}</a>
                                            </li>
                                            <li><a class="dropdown-item text-danger" href="javascript:void(0)"
                                                    onclick="deletePlan('{{ $plan->plan_id }}')">{{ __('Delete Plan') }}</a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <!-- Title & Pricing -->
                            <h3 class="fw-bold text-dark mb-1">{{ __($plan->plan_name) }}</h3>
                            <div class="d-flex align-items-baseline gap-1 mb-2">
                                <span class="plan-price-title">
                                    {{ $plan->plan_price == 0 ? __('Free') : $symbol . number_format($plan->plan_price, 0) }}
                                </span>
                                <span class="plan-price-period">
                                    /
                                    {{ $plan->validity == 9999 ? __('forever') : ($plan->validity >= 365 ? __('year') : __('month')) }}
                                </span>
                            </div>

                            <p class="text-muted small mb-3">{{ __($plan->plan_description) }}</p>

                            <hr class="my-2 border-light">

                            <!-- Full Feature List using Icon Blade Includes -->
                            <ul class="feature-item-list my-3 flex-grow-1">

                                {{-- vCard Features --}}
                                @if ($plan->plan_type == 'BOTH' || $plan->plan_type == 'VCARD')
                                    <li class="feature-section-title fw-bold text-uppercase text-muted">
                                        {{ __('vCard Features') }}</li>

                                    {{-- AI Credits --}}
                                    @if (isset($aibuilder_settings) && $aibuilder_settings->aibuilder == 1)
                                        @if ($plan->trial != 0)
                                            <li class="{{ $plan->trial_ai_credits == 0 ? 'opacity-50' : '' }}">
                                                <span
                                                    class="feature-icon {{ $plan->trial_ai_credits == 0 ? 'is-off' : 'is-on' }}">
                                                    @include(
                                                        'admin.pages.plans.includes.icon-' .
                                                            ($plan->trial_ai_credits == 0 ? 'x' : 'check'))
                                                </span>
                                                <span>
                                                    @if ($plan->trial_ai_credits != 0)
                                                        <strong class="text-dark">{{ $plan->trial_ai_credits }}</strong>
                                                    @endif
                                                    {{ __('AI Credits (Trial)') }}
                                                </span>
                                            </li>
                                        @endif

                                        <li class="{{ $plan->ai_credits == 0 ? 'opacity-50' : '' }}">
                                            <span class="feature-icon {{ $plan->ai_credits == 0 ? 'is-off' : 'is-on' }}">
                                                @include(
                                                    'admin.pages.plans.includes.icon-' .
                                                        ($plan->ai_credits == 0 ? 'x' : 'check'))
                                            </span>
                                            <span>
                                                @if ($plan->ai_credits != 0)
                                                    <strong
                                                        class="text-dark">{{ $plan->ai_credits == -1 ? __('Unlimited') : $plan->ai_credits }}</strong>
                                                @endif
                                                {{ __('AI Credit') }}
                                            </span>
                                        </li>
                                    @endif

                                    <li class="{{ $plan->no_of_vcards == 0 ? 'opacity-50' : '' }}">
                                        <span class="feature-icon {{ $plan->no_of_vcards == 0 ? 'is-off' : 'is-on' }}">
                                            @include(
                                                'admin.pages.plans.includes.icon-' .
                                                    ($plan->no_of_vcards == 0 ? 'x' : 'check'))
                                        </span>
                                        <span>
                                            @if ($plan->no_of_vcards != 0)
                                                <strong
                                                    class="text-dark">{{ $plan->no_of_vcards == 999 ? __('Unlimited') : $plan->no_of_vcards }}</strong>
                                            @endif
                                            {{ __('vCards') }}
                                        </span>
                                    </li>

                                    <li class="{{ $plan->no_of_services == 0 ? 'opacity-50' : '' }}">
                                        <span class="feature-icon {{ $plan->no_of_services == 0 ? 'is-off' : 'is-on' }}">
                                            @include(
                                                'admin.pages.plans.includes.icon-' .
                                                    ($plan->no_of_services == 0 ? 'x' : 'check'))
                                        </span>
                                        <span>
                                            @if ($plan->no_of_services != 0)
                                                <strong
                                                    class="text-dark">{{ $plan->no_of_services == 999 ? __('Unlimited') : $plan->no_of_services }}</strong>
                                            @endif
                                            {{ __('Services') }}
                                        </span>
                                    </li>

                                    <li class="{{ $plan->no_of_vcard_products == 0 ? 'opacity-50' : '' }}">
                                        <span
                                            class="feature-icon {{ $plan->no_of_vcard_products == 0 ? 'is-off' : 'is-on' }}">
                                            @include(
                                                'admin.pages.plans.includes.icon-' .
                                                    ($plan->no_of_vcard_products == 0 ? 'x' : 'check'))
                                        </span>
                                        <span>
                                            @if ($plan->no_of_vcard_products != 0)
                                                <strong
                                                    class="text-dark">{{ $plan->no_of_vcard_products == 999 ? __('Unlimited') : $plan->no_of_vcard_products }}</strong>
                                            @endif
                                            {{ __('Products') }}
                                        </span>
                                    </li>

                                    <li class="{{ $plan->no_of_links == 0 ? 'opacity-50' : '' }}">
                                        <span class="feature-icon {{ $plan->no_of_links == 0 ? 'is-off' : 'is-on' }}">
                                            @include(
                                                'admin.pages.plans.includes.icon-' .
                                                    ($plan->no_of_links == 0 ? 'x' : 'check'))
                                        </span>
                                        <span>
                                            @if ($plan->no_of_links != 0)
                                                <strong
                                                    class="text-dark">{{ $plan->no_of_links == 999 ? __('Unlimited') : $plan->no_of_links }}</strong>
                                            @endif
                                            {{ __('Links') }}
                                        </span>
                                    </li>

                                    <li class="{{ $plan->no_of_payments == 0 ? 'opacity-50' : '' }}">
                                        <span class="feature-icon {{ $plan->no_of_payments == 0 ? 'is-off' : 'is-on' }}">
                                            @include(
                                                'admin.pages.plans.includes.icon-' .
                                                    ($plan->no_of_payments == 0 ? 'x' : 'check'))
                                        </span>
                                        <span>
                                            @if ($plan->no_of_payments != 0)
                                                <strong
                                                    class="text-dark">{{ $plan->no_of_payments == 999 ? __('Unlimited') : $plan->no_of_payments }}</strong>
                                            @endif
                                            {{ __('Payment Listed') }}
                                        </span>
                                    </li>

                                    <li class="{{ $plan->no_of_galleries == 0 ? 'opacity-50' : '' }}">
                                        <span class="feature-icon {{ $plan->no_of_galleries == 0 ? 'is-off' : 'is-on' }}">
                                            @include(
                                                'admin.pages.plans.includes.icon-' .
                                                    ($plan->no_of_galleries == 0 ? 'x' : 'check'))
                                        </span>
                                        <span>
                                            @if ($plan->no_of_galleries != 0)
                                                <strong
                                                    class="text-dark">{{ $plan->no_of_galleries == 999 ? __('Unlimited') : $plan->no_of_galleries }}</strong>
                                            @endif
                                            {{ __('Galleries') }}
                                        </span>
                                    </li>

                                    <li class="{{ $plan->no_testimonials == 0 ? 'opacity-50' : '' }}">
                                        <span class="feature-icon {{ $plan->no_testimonials == 0 ? 'is-off' : 'is-on' }}">
                                            @include(
                                                'admin.pages.plans.includes.icon-' .
                                                    ($plan->no_testimonials == 0 ? 'x' : 'check'))
                                        </span>
                                        <span>
                                            @if ($plan->no_testimonials != 0)
                                                <strong
                                                    class="text-dark">{{ $plan->no_testimonials == 999 ? __('Unlimited') : $plan->no_testimonials }}</strong>
                                            @endif
                                            {{ __('Testimonials') }}
                                        </span>
                                    </li>

                                    <li class="{{ $plan->business_hours == 0 ? 'opacity-50' : '' }}">
                                        <span class="feature-icon {{ $plan->business_hours == 0 ? 'is-off' : 'is-on' }}">
                                            @include(
                                                'admin.pages.plans.includes.icon-' .
                                                    ($plan->business_hours == 0 ? 'x' : 'check'))
                                        </span>
                                        <span>{{ __('Business Hours') }}</span>
                                    </li>

                                    <li class="{{ $plan->appointment == 0 ? 'opacity-50' : '' }}">
                                        <span class="feature-icon {{ $plan->appointment == 0 ? 'is-off' : 'is-on' }}">
                                            @include(
                                                'admin.pages.plans.includes.icon-' .
                                                    ($plan->appointment == 0 ? 'x' : 'check'))
                                        </span>
                                        <span>{{ __('Appointments') }}</span>
                                    </li>

                                    <li class="{{ $plan->service_booking == 0 ? 'opacity-50' : '' }}">
                                        <span class="feature-icon {{ $plan->service_booking == 0 ? 'is-off' : 'is-on' }}">
                                            @include(
                                                'admin.pages.plans.includes.icon-' .
                                                    ($plan->service_booking == 0 ? 'x' : 'check'))
                                        </span>
                                        <span>{{ __('Service Booking') }}</span>
                                    </li>

                                    <li class="{{ $plan->contact_form == 0 ? 'opacity-50' : '' }}">
                                        <span class="feature-icon {{ $plan->contact_form == 0 ? 'is-off' : 'is-on' }}">
                                            @include(
                                                'admin.pages.plans.includes.icon-' .
                                                    ($plan->contact_form == 0 ? 'x' : 'check'))
                                        </span>
                                        <span>{{ __('Contact Form') }}</span>
                                    </li>

                                    <li class="{{ $plan->no_of_enquires == 0 ? 'opacity-50' : '' }}">
                                        <span class="feature-icon {{ $plan->no_of_enquires == 0 ? 'is-off' : 'is-on' }}">
                                            @include(
                                                'admin.pages.plans.includes.icon-' .
                                                    ($plan->no_of_enquires == 0 ? 'x' : 'check'))
                                        </span>
                                        <span>
                                            @if ($plan->no_of_enquires != 0)
                                                <strong
                                                    class="text-dark">{{ $plan->no_of_enquires == 999 ? __('Unlimited') : $plan->no_of_enquires }}</strong>
                                            @endif
                                            {{ __('Enquiries') }}
                                        </span>
                                    </li>

                                    @if (is_dir(base_path('plugins/GoogleWallet')))
                                        @if ($plan->google_wallet == 1)
                                            <li class="{{ $plan->no_of_google_wallets == 0 ? 'opacity-50' : '' }}">
                                                <span
                                                    class="feature-icon {{ $plan->no_of_google_wallets == 0 ? 'is-off' : 'is-on' }}">
                                                    @include(
                                                        'admin.pages.plans.includes.icon-' .
                                                            ($plan->no_of_google_wallets == 0 ? 'x' : 'check'))
                                                </span>
                                                <span>
                                                    @if ($plan->no_of_google_wallets != 0)
                                                        <strong
                                                            class="text-dark">{{ $plan->no_of_google_wallets == 999 ? __('Unlimited') : $plan->no_of_google_wallets }}</strong>
                                                    @endif
                                                    {{ __('Google Wallets') }}
                                                </span>
                                            </li>
                                        @endif
                                    @endif

                                    <li class="{{ $plan->password_protected == 0 ? 'opacity-50' : '' }}">
                                        <span
                                            class="feature-icon {{ $plan->password_protected == 0 ? 'is-off' : 'is-on' }}">
                                            @include(
                                                'admin.pages.plans.includes.icon-' .
                                                    ($plan->password_protected == 0 ? 'x' : 'check'))
                                        </span>
                                        <span>{{ __('Password Protected') }}</span>
                                    </li>
                                @endif

                                {{-- Store Features --}}
                                @if ($plan->plan_type == 'BOTH' || $plan->plan_type == 'STORE')
                                    <li class="feature-section-title fw-bold text-uppercase text-muted">
                                        {{ __('Store Features') }}</li>

                                    <li class="{{ $plan->no_of_stores == 0 ? 'opacity-50' : '' }}">
                                        <span class="feature-icon {{ $plan->no_of_stores == 0 ? 'is-off' : 'is-on' }}">
                                            @include(
                                                'admin.pages.plans.includes.icon-' .
                                                    ($plan->no_of_stores == 0 ? 'x' : 'check'))
                                        </span>
                                        <span>
                                            @if ($plan->no_of_stores != 0)
                                                <strong
                                                    class="text-dark">{{ $plan->no_of_stores == 999 ? __('Unlimited') : $plan->no_of_stores }}</strong>
                                            @endif
                                            {{ __('Stores') }}
                                        </span>
                                    </li>

                                    <li class="{{ $plan->no_of_categories == 0 ? 'opacity-50' : '' }}">
                                        <span
                                            class="feature-icon {{ $plan->no_of_categories == 0 ? 'is-off' : 'is-on' }}">
                                            @include(
                                                'admin.pages.plans.includes.icon-' .
                                                    ($plan->no_of_categories == 0 ? 'x' : 'check'))
                                        </span>
                                        <span>
                                            @if ($plan->no_of_categories != 0)
                                                <strong
                                                    class="text-dark">{{ $plan->no_of_categories == 999 ? __('Unlimited') : $plan->no_of_categories }}</strong>
                                            @endif
                                            {{ __('Categories') }}
                                        </span>
                                    </li>

                                    <li class="{{ $plan->no_of_store_products == 0 ? 'opacity-50' : '' }}">
                                        <span
                                            class="feature-icon {{ $plan->no_of_store_products == 0 ? 'is-off' : 'is-on' }}">
                                            @include(
                                                'admin.pages.plans.includes.icon-' .
                                                    ($plan->no_of_store_products == 0 ? 'x' : 'check'))
                                        </span>
                                        <span>
                                            @if ($plan->no_of_store_products != 0)
                                                <strong
                                                    class="text-dark">{{ $plan->no_of_store_products == 999 ? __('Unlimited') : $plan->no_of_store_products }}</strong>
                                            @endif
                                            {{ __('Products') }}
                                        </span>
                                    </li>
                                @endif

                                {{-- Additional Features --}}
                                <li class="feature-section-title fw-bold text-uppercase text-muted">
                                    {{ __('Additional Features') }}</li>

                                <li class="{{ $plan->custom_domain == 0 ? 'opacity-50' : '' }}">
                                    <span class="feature-icon {{ $plan->custom_domain == 0 ? 'is-off' : 'is-on' }}">
                                        @include(
                                            'admin.pages.plans.includes.icon-' .
                                                ($plan->custom_domain == 0 ? 'x' : 'check'))
                                    </span>
                                    <span>{{ __('Custom Domain') }}</span>
                                </li>

                                @if (isset($config[76]) && $config[76]->config_value == '1')
                                    <li>
                                        <span class="feature-icon is-on">
                                            @include('admin.pages.plans.includes.icon-check')
                                        </span>
                                        <span>{{ __('Order NFC Card') }}</span>
                                    </li>
                                @endif

                                <li class="{{ $plan->storage == 0 ? 'opacity-50' : '' }}">
                                    <span class="feature-icon {{ $plan->storage == 0 ? 'is-off' : 'is-on' }}">
                                        @include(
                                            'admin.pages.plans.includes.icon-' .
                                                ($plan->storage == 0 ? 'x' : 'check'))
                                    </span>
                                    <span>
                                        @if ($plan->storage != 0)
                                            <strong
                                                class="text-dark">{{ $plan->storage == 999 ? __('Unlimited') : $plan->storage . 'MB' }}</strong>
                                        @endif
                                        {{ __('storage limit') }}
                                    </span>
                                </li>

                                <li class="{{ $plan->advanced_settings == 0 ? 'opacity-50' : '' }}">
                                    <span class="feature-icon {{ $plan->advanced_settings == 0 ? 'is-off' : 'is-on' }}">
                                        @include(
                                            'admin.pages.plans.includes.icon-' .
                                                ($plan->advanced_settings == 0 ? 'x' : 'check'))
                                    </span>
                                    <span>{{ __('Advanced Settings') }}</span>
                                </li>

                                <li class="{{ $plan->pwa == 0 ? 'opacity-50' : '' }}">
                                    <span class="feature-icon {{ $plan->pwa == 0 ? 'is-off' : 'is-on' }}">
                                        @include(
                                            'admin.pages.plans.includes.icon-' .
                                                ($plan->pwa == 0 ? 'x' : 'check'))
                                    </span>
                                    <span>{{ __('Progressive Web App (PWA)') }}</span>
                                </li>

                                <li class="{{ $plan->personalized_link == 0 ? 'opacity-50' : '' }}">
                                    <span class="feature-icon {{ $plan->personalized_link == 0 ? 'is-off' : 'is-on' }}">
                                        @include(
                                            'admin.pages.plans.includes.icon-' .
                                                ($plan->personalized_link == 0 ? 'x' : 'check'))
                                    </span>
                                    <span>{{ __('Personalized Link') }}</span>
                                </li>

                                <li class="{{ $plan->hide_branding == 0 ? 'opacity-50' : '' }}">
                                    <span class="feature-icon {{ $plan->hide_branding == 0 ? 'is-off' : 'is-on' }}">
                                        @include(
                                            'admin.pages.plans.includes.icon-' .
                                                ($plan->hide_branding == 0 ? 'x' : 'check'))
                                    </span>
                                    <span>{{ __('Hide Branding') }}</span>
                                </li>

                                <li class="{{ $plan->free_setup == 0 ? 'opacity-50' : '' }}">
                                    <span class="feature-icon {{ $plan->free_setup == 0 ? 'is-off' : 'is-on' }}">
                                        @include(
                                            'admin.pages.plans.includes.icon-' .
                                                ($plan->free_setup == 0 ? 'x' : 'check'))
                                    </span>
                                    <span>{{ __('Free Setup') }}</span>
                                </li>

                                <li class="{{ $plan->free_support == 0 ? 'opacity-50' : '' }}">
                                    <span class="feature-icon {{ $plan->free_support == 0 ? 'is-off' : 'is-on' }}">
                                        @include(
                                            'admin.pages.plans.includes.icon-' .
                                                ($plan->free_support == 0 ? 'x' : 'check'))
                                    </span>
                                    <span>{{ __('Free Support') }}</span>
                                </li>

                            </ul>

                            <!-- Bottom Card Actions -->
                            <div class="pt-3 border-top d-flex align-items-center justify-content-between mt-auto">
                                <div class="form-check form-switch d-flex align-items-center gap-2 m-0 ps-0">
                                    <input class="form-check-input m-0" type="checkbox" role="switch"
                                        id="toggle-{{ $plan->plan_id }}" {{ $plan->status == 1 ? 'checked' : '' }}
                                        onchange="toggleStatus('{{ $plan->plan_id }}')">
                                    <label
                                        class="form-check-label fw-semibold small {{ $plan->status == 1 ? 'text-success' : 'text-muted' }}"
                                        for="toggle-{{ $plan->plan_id }}">
                                        {{ $plan->status == 1 ? __('Enabled') : __('Disabled') }}
                                    </label>
                                </div>

                                <div class="d-flex align-items-center gap-2">
                                    <a href="{{ route('admin.edit.plan', $plan->plan_id) }}"
                                        class="btn-action-soft-edit" title="{{ __('Edit') }}">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
                                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
                                        </svg>
                                    </a>
                                    <button type="button" onclick="deletePlan('{{ $plan->plan_id }}')"
                                        class="btn-action-soft-delete" title="{{ __('Delete') }}">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <polyline points="3 6 5 6 21 6" />
                                            <path
                                                d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
                                        </svg>
                                    </button>
                                </div>
                            </div>

                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="card border-0 shadow-sm rounded-4 text-center py-5 px-4 my-3"
                            style="background: #ffffff;">
                            <!-- Icon Wrapper -->
                            <div class="mx-auto mb-3 d-flex align-items-center justify-content-center"
                                style="width: 80px; height: 80px; background-color: #eef2ff; color: #6366f1; border-radius: 50%;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="38" height="38"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round"
                                    class="icon icon-tabler icons-tabler-outline icon-tabler-tag">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                    <path d="M6.5 7.5a1 1 0 1 0 2 0a1 1 0 1 0 -2 0"></path>
                                    <path
                                        d="M3 6v5.172a2 2 0 0 0 .586 1.414l7.71 7.71a2.41 2.41 0 0 0 3.408 0l5.592 -5.592a2.41 2.41 0 0 0 0 -3.408l-7.71 -7.71a2 2 0 0 0 -1.414 -.586h-5.172a3 3 0 0 0 -3 3">
                                    </path>
                                </svg>
                            </div>

                            <!-- Title & Description -->
                            <h3 class="fw-bold text-dark mb-2">{{ __('No Pricing Plans Found') }}</h3>
                            <p class="text-muted mx-auto mb-4"
                                style="max-width: 440px; font-size: 0.95rem; line-height: 1.6;">
                                {{ __('We couldn\'t find any plans matching your current filter or search query. You can create a new plan or reset your search filters.') }}
                            </p>

                            <!-- Action Buttons -->
                            <div class="d-flex align-items-center justify-content-center gap-2 flex-wrap">
                                <a href="{{ route('admin.add.plan') }}"
                                    class="btn btn-primary rounded-pill px-4 py-2 fw-semibold d-inline-flex align-items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                                        stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M12 5v14M5 12h14" />
                                    </svg>
                                    {{ __('Create New Plan') }}
                                </a>

                                @if (request('search') || (request('status') && request('status') != 'all'))
                                    <a href="{{ route('admin.plans') }}"
                                        class="btn btn-light rounded-pill px-4 py-2 fw-semibold text-secondary">
                                        {{ __('Clear Filters') }}
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforelse
            </div>

            <!-- Clean Pagination Footer -->
            @if ($plans->hasPages())
                <div class="d-flex flex-wrap align-items-center justify-content-between pt-3 border-top mt-3">
                    <p class="text-muted small mb-0">
                        {{ __('Showing') }} <strong>{{ $plans->firstItem() }}</strong> {{ __('to') }}
                        <strong>{{ $plans->lastItem() }}</strong> {{ __('of') }}
                        <strong>{{ $plans->total() }}</strong> {{ __('plans') }}
                    </p>
                    <div>
                        {{ $plans->links('pagination::bootstrap-4') }}
                    </div>
                </div>
            @endif

        </div>
    </div>

    <!-- Status Modal -->
    <div class="modal modal-bg fade" id="status-modal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-status"></div>
                <div class="modal-body text-center py-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon mb-2 text-danger icon-lg" width="24"
                        height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                        <path d="M12 9v2m0 4v.01"></path>
                        <path d="M5 19h14a2 2 0 0 0 1.84 -2.75l-7.1 -12.25a2 2 0 0 0 -3.5 0l-7.1 12.25a2 2 0 0 0 1.75 2.75">
                        </path>
                    </svg>
                    <h3>{{ __('Are you sure?') }}</h3>
                    <div id="modal-message" class="text-secondary">
                        {{ __('If you proceed, you will activate or deactivate this plan.') }}
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="w-100">
                        <div class="row">
                            <div class="col">
                                <a href="#" class="btn w-100" data-bs-dismiss="modal">
                                    {{ __('Cancel') }}
                                </a>
                            </div>
                            <div class="col">
                                <a class="btn btn-danger w-100" id="user_id">
                                    {{ __('Yes, proceed') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Modal -->
    <div class="modal modal-bg fade" id="delete-modal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-status"></div>
                <div class="modal-body text-center py-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon mb-2 text-danger icon-lg" width="24"
                        height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                        <path d="M12 9v2m0 4v.01"></path>
                        <path d="M5 19h14a2 2 0 0 0 1.84 -2.75l-7.1 -12.25a2 2 0 0 0 -3.5 0l-7.1 12.25a2 2 0 0 0 1.75 2.75">
                        </path>
                    </svg>
                    <h3>{{ __('Are you sure?') }}</h3>
                    <div id="action_status" class="text-secondary">
                        {{ __('If you proceed, you will delete this plan.') }}
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="w-100">
                        <div class="row">
                            <div class="col">
                                <a href="#" class="btn w-100" data-bs-dismiss="modal">
                                    {{ __('Cancel') }}
                                </a>
                            </div>
                            <div class="col">
                                <a class="btn btn-danger w-100" id="deleted_plan_id">
                                    {{ __('Yes, proceed') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script type="text/javascript">
        "use strict";

        // Toggle Status Function
        function toggleStatus(planId) {
            $("#status-modal").modal("show");

            var link = document.getElementById("user_id");
            link.setAttribute("href", "/admin/status-plan?id=" + planId);
        }

        // Delete Plan Function
        function deletePlan(planId) {
            $("#delete-modal").modal("show");

            var link = document.getElementById("deleted_plan_id");
            link.setAttribute("href", "/admin/delete-plan?id=" + planId);
        }
    </script>
@endsection
