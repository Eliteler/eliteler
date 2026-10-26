@extends('user.layouts.index', ['header' => true, 'nav' => true, 'demo' => true, 'settings' => $settings])

{{-- Custom CSS --}}
@section('css')
    <link href="{{ asset('css/plan.min.css') }}" rel="stylesheet">

    <style>
        .btn {
            font-size: 14px !important;
            padding: 10px 16px !important;
        }
    </style>
@endsection

{{-- AI Builder --}}
@php
    use Illuminate\Support\Facades\Auth;

    $aibuilder_settings = DB::table('aibuilder_settings')->first();

    // check nfc session
    $nfc_ref = session('nfc_ref');
    $planAssignType = empty(Auth::user()->plan_id) ? 'new' : 'overwrite';
@endphp

@section('content')
    <div class="page-wrapper">
        <!-- ============================================================ -->
        <!-- Page header -->
        <!-- ============================================================ -->
        <div class="page-header d-print-none">
            <div class="container-fluid">
                <div class="row g-2 align-items-center">
                    <!-- Breadcrumb Navigation -->
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="{{ route('user.dashboard') }}" class="text-secondary text-decoration-none">
                                    {{ __('Dashboard') }}
                                </a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">
                                {{ __('Plans') }}
                            </li>
                        </ol>
                    </nav>
                    <div class="col">
                        <!-- Page Title -->
                        <h2 class="page-title">
                            {{ __('My Plan') }}
                        </h2>

                        <!-- Descriptive Subtitle -->
                        <div class="text-muted mt-1">
                            {{ __('Review your current subscription, renew, upgrade, or manage your plan details.') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="page-body">
            <div class="container-fluid">
                {{-- Failed --}}
                @if (Session::has('failed'))
                    <div class="alert alert-important alert-danger alert-dismissible mb-3" role="alert">
                        <div class="d-flex">
                            <div>
                                {{ Session::get('failed') }}
                            </div>
                        </div>
                        <a class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="close"></a>
                    </div>
                @endif

                {{-- Success --}}
                @if (Session::has('success'))
                    <div class="alert alert-important alert-success alert-dismissible mb-3" role="alert">
                        <div class="d-flex">
                            <div>
                                {{ Session::get('success') }}
                            </div>
                        </div>
                        <a class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="close"></a>
                    </div>
                @endif

                {{-- My Plan summary card --}}
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex flex-wrap align-items-center gap-3">

                                    <!-- Icon -->
                                    <span class="avatar avatar-lg rounded-3 bg-primary-lt text-primary">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                            <path
                                                d="M3 7a4 4 0 0 1 4 -4h10a4 4 0 0 1 4 4v10a4 4 0 0 1 -4 4h-10a4 4 0 0 1 -4 -4v-10z" />
                                            <path d="M3 10h18" />
                                            <path d="M7 15h.01" />
                                            <path d="M11 15h2" />
                                        </svg>
                                    </span>

                                    <!-- Plan info -->
                                    <div class="flex-fill">
                                        <div class="text-muted text-uppercase small mb-1">{{ __('My plan') }}</div>

                                        @if (isset($active_plan))
                                            <div class="d-flex flex-wrap align-items-center gap-2">
                                                <h3 class="mb-0 text-uppercase fw-bold">{{ __($active_plan->plan_name) }}
                                                </h3>

                                                @if ($active_plan->plan_price == 0)
                                                    <span class="badge bg-secondary-lt text-secondary rounded-pill">
                                                        {{ __('Free plan') }}
                                                    </span>
                                                @elseif ($active_plan->validity == 9999)
                                                    <span class="badge bg-primary-lt text-primary rounded-pill">
                                                        {{ __('Lifetime') }}
                                                    </span>
                                                @endif
                                            </div>

                                            @if ($active_plan->plan_price != 0 && $active_plan->validity != 9999)
                                                <div class="mt-2 d-flex flex-wrap align-items-center gap-2">
                                                    <span class="fw-bold">
                                                        {{ $remaining_days > 0 ? floor($remaining_days) . ' ' . __('days left') : __('Plan Expired!') }}
                                                    </span>
                                                    <span class="text-muted">&middot;</span>
                                                    <span class="text-muted">
                                                        {{ __('Expires on') }}:
                                                        {{ formatDateForUser(Auth::user()->plan_validity) }}
                                                    </span>
                                                </div>
                                            @endif
                                        @else
                                            <h3 class="mb-0">{{ __('No active plans!') }}</h3>
                                        @endif
                                    </div>

                                    <!-- Actions -->
                                    <div class="d-flex flex-wrap gap-2">
                                        @if (isset($active_plan))
                                            @if ($free_plan == 0 || $active_plan->plan_price != 0)
                                                <a href="{{ route('user.checkout', $active_plan->plan_id) }}"
                                                    class="btn btn-primary rounded-pill">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                        viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                        class="icon icon-tabler icons-tabler-outline icon-tabler-rotate">
                                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                        <path d="M19.95 11a8 8 0 1 0 -.5 4m.5 5v-5h-5" />
                                                    </svg>
                                                    {{ __('Renew') }}
                                                </a>
                                            @endif

                                            <a href="#upgrade-plan-button" class="btn btn-primary rounded-pill">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                                                    viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                    class="icon icon-tabler icons-tabler-outline icon-tabler-circle-arrow-up">
                                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                    <path d="M3 12a9 9 0 1 0 18 0a9 9 0 0 0 -18 0" />
                                                    <path d="M12 8l-4 4" />
                                                    <path d="M12 8v8" />
                                                    <path d="M16 12l-4 -4" />
                                                </svg>
                                                {{ __('Upgrade') }}
                                            </a>

                                            @if ($cancelSubscription == 1)
                                                <button onclick="cancelSubscription()" class="btn btn-danger rounded-pill">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                        viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                        class="icon icon-tabler icons-tabler-outline icon-tabler-circle-x">
                                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                        <path d="M3 12a9 9 0 1 0 18 0a9 9 0 1 0 -18 0" />
                                                        <path d="M10 10l4 4m0 -4l-4 4" />
                                                    </svg>
                                                    {{ __('Cancel') }}
                                                </button>
                                            @endif

                                            <button type="button" class="btn btn-primary rounded-pill currentPlanModal"
                                                data-bs-toggle="modal" data-bs-target="#currentPlanModal">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                    viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                    class="icon icon-tabler icons-tabler-outline icon-tabler-info-circle">
                                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                    <path d="M3 12a9 9 0 1 0 18 0a9 9 0 0 0 -18 0" />
                                                    <path d="M12 9h.01" />
                                                    <path d="M11 12h1v4h1" />
                                                </svg>
                                                {{ __('My Plan') }}
                                            </button>
                                        @else
                                            <a href="#plans"
                                                class="btn btn-primary rounded-pill">{{ __('Choose plan') }}</a>
                                        @endif
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div id="plans" class="page-body">
                <div class="container-fluid">

                    <div class="row row-deck row-cards">

                        @foreach ($plans as $plan)
                            <div class="col-sm-4 col-lg-4 mb-3 d-flex">
                                <div
                                    class="card card-md plan-card w-100 {{ $plan->recommended == '1' ? 'is-recommended' : '' }}">

                                    @if ($plan->recommended == '1')
                                        <div class="plan-recommended-badge">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14"
                                                viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                                fill="currentColor" stroke-linecap="round" stroke-linejoin="round">
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                <path
                                                    d="M12 17.75l-6.172 3.245l1.179 -6.873l-5 -4.867l6.9 -1l3.086 -6.253l3.086 6.253l6.9 1l-5 4.867l1.179 6.873z" />
                                            </svg>
                                            {{ __('Recommended') }}
                                        </div>
                                    @endif

                                    <div class="card-body d-flex flex-column">
                                        <div>
                                            <span class="badge bg-primary text-white plan-type-pill">
                                                {{ __($plan->plan_type == 'BOTH' ? 'VCARD & STORE' : $plan->plan_type) }}
                                            </span>

                                            {{-- Trial Period --}}
                                            @if (Auth::user()->trial == 0 && $plan->trial != 0)
                                                <span class="badge bg-dark text-white text-uppercase">
                                                    {{ __($plan->trial) }} {{ __('Days Free Trial') }}
                                                </span>
                                            @endif

                                            <div class="text-capitalize fw-bold h2 mt-2 mb-1">{{ __($plan->plan_name) }}
                                            </div>

                                            <div class="mb-2">
                                                <span class="plan-price">
                                                    {{ $plan->plan_price == '0' ? __('FREE') : formatCurrency($plan->plan_price) }}
                                                </span>

                                                <small class="text-capitalize d-block text-muted">
                                                    @if ((!str_contains(strtolower($plan->plan_name), 'trial') && (int) $plan->plan_price == 0) || $plan->validity == 9999)
                                                        {{ __('Forever') }}
                                                    @endif
                                                    @if ((int) $plan->plan_price != 0 && $plan->validity >= 29 && $plan->validity <= 31)
                                                        {{ __('Per') }} {{ __('Month') }}
                                                    @endif
                                                    @if ((int) $plan->plan_price != 0 && $plan->validity >= 365 && $plan->validity <= 366)
                                                        {{ __('Per') }} {{ __('Year') }}
                                                    @endif
                                                    @if (str_contains(strtolower($plan->plan_name), 'trial') &&
                                                            (int) $plan->plan_price != 0 &&
                                                            $plan->validity > 1 &&
                                                            $plan->validity != 9999 &&
                                                            $plan->validity != 29 &&
                                                            $plan->validity != 30 &&
                                                            $plan->validity != 31 &&
                                                            $plan->validity != 365 &&
                                                            $plan->validity != 366 &&
                                                            $plan->validity != 9999)
                                                        {{ __('Per') . ' ' . $plan->validity . ' ' . __('Days') }}
                                                    @endif
                                                </small>
                                            </div>

                                            <hr>

                                            <p class="text-muted mb-3">{{ __($plan->plan_description) }}</p>

                                            <ul class="feature-list">
                                                {{-- Check Card type is "Both" or "VCARD" --}}
                                                @if ($plan->plan_type == 'BOTH' || $plan->plan_type == 'VCARD')
                                                    <li class="feature-section-title">{{ __('vCard Features') }}</li>

                                                    {{-- AI Credits --}}
                                                    @if ($aibuilder_settings->aibuilder == 1)
                                                        @if (Auth::user()->trial == 0 && $plan->trial != 0)
                                                            <li>
                                                                <div class="feature-left">
                                                                    <span
                                                                        class="feature-icon {{ $plan->trial_ai_credits == 0 ? 'is-off' : 'is-on' }}">
                                                                        @include(
                                                                            'user.pages.plans.includes.icon-' .
                                                                                ($plan->trial_ai_credits == 0
                                                                                    ? 'x'
                                                                                    : 'check'))
                                                                    </span>
                                                                    <span>
                                                                        @if ($plan->trial_ai_credits != 0)
                                                                            <span
                                                                                class="value">{{ $plan->trial_ai_credits }}</span>
                                                                        @endif
                                                                        {{ __('AI Credits (Trial)') }}
                                                                        <span id="popover-element"
                                                                            style="width: 1rem !important;height: 1rem !important;"
                                                                            class="form-help bg-primary text-white ms-1"
                                                                            data-bs-toggle="popover"
                                                                            data-bs-placement="top" data-bs-html="true"
                                                                            data-bs-content="<div class='space-y-1'><p>{{ __('1 Credit = 1 AI Generation') }}</p><p>{{ __('These credits can only be used during the trial period.') }}</p></div>">
                                                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                                                width="24" height="24"
                                                                                viewBox="0 0 24 24" fill="none"
                                                                                stroke="currentColor" stroke-width="2"
                                                                                stroke-linecap="round"
                                                                                stroke-linejoin="round"
                                                                                class="icon icon-tabler icons-tabler-outline icon-tabler-question-mark">
                                                                                <path stroke="none" d="M0 0h24v24H0z"
                                                                                    fill="none" />
                                                                                <path
                                                                                    d="M8 8a3.5 3 0 0 1 3.5 -3h1a3.5 3 0 0 1 3.5 3a3 3 0 0 1 -2 3a3 4 0 0 0 -2 4" />
                                                                                <path d="M12 19l0 .01" />
                                                                            </svg>
                                                                        </span>
                                                                    </span>
                                                                </div>
                                                                <span
                                                                    class="badge badge-new bg-green text-white">{{ __('New') }}</span>
                                                            </li>
                                                        @endif

                                                        <li>
                                                            <div class="feature-left">
                                                                <span
                                                                    class="feature-icon {{ $plan->ai_credits == 0 ? 'is-off' : 'is-on' }}">
                                                                    @include(
                                                                        'user.pages.plans.includes.icon-' .
                                                                            ($plan->ai_credits == 0
                                                                                ? 'x'
                                                                                : 'check'))
                                                                </span>
                                                                <span>
                                                                    @if ($plan->ai_credits != 0)
                                                                        <span
                                                                            class="value">{{ $plan->ai_credits == -1 ? __('Unlimited') : $plan->ai_credits }}</span>
                                                                    @endif
                                                                    {{ __('AI Credit') }}
                                                                    <span id="popover-element"
                                                                        style="width: 1rem !important;height: 1rem !important;"
                                                                        class="form-help bg-primary text-white ms-1"
                                                                        data-bs-toggle="popover" data-bs-placement="top"
                                                                        data-bs-html="true"
                                                                        data-bs-content="<p>{{ __('1 Credit = 1 AI Generation') }}</p>">
                                                                        <svg xmlns="http://www.w3.org/2000/svg"
                                                                            width="24" height="24"
                                                                            viewBox="0 0 24 24" fill="none"
                                                                            stroke="currentColor" stroke-width="2"
                                                                            stroke-linecap="round" stroke-linejoin="round"
                                                                            class="icon icon-tabler icons-tabler-outline icon-tabler-question-mark">
                                                                            <path stroke="none" d="M0 0h24v24H0z"
                                                                                fill="none" />
                                                                            <path
                                                                                d="M8 8a3.5 3 0 0 1 3.5 -3h1a3.5 3 0 0 1 3.5 3a3 3 0 0 1 -2 3a3 4 0 0 0 -2 4" />
                                                                            <path d="M12 19l0 .01" />
                                                                        </svg>
                                                                    </span>
                                                                </span>
                                                            </div>
                                                            <span
                                                                class="badge badge-new bg-green text-white">{{ __('New') }}</span>
                                                        </li>
                                                    @endif

                                                    <li>
                                                        <div class="feature-left">
                                                            <span
                                                                class="feature-icon {{ $plan->no_of_vcards > 0 ? 'is-on' : 'is-off' }}">
                                                                @include(
                                                                    'user.pages.plans.includes.icon-' .
                                                                        ($plan->no_of_vcards > 0 ? 'check' : 'x'))
                                                            </span>
                                                            <span>
                                                                @if ($plan->no_of_vcards != 0)
                                                                    <span
                                                                        class="value">{{ $plan->no_of_vcards == 999 ? __('Unlimited') : $plan->no_of_vcards }}</span>
                                                                @endif
                                                                {{ __('vCards') }}
                                                            </span>
                                                        </div>
                                                    </li>

                                                    <li>
                                                        <div class="feature-left">
                                                            <span
                                                                class="feature-icon {{ $plan->no_of_services > 0 ? 'is-on' : 'is-off' }}">
                                                                @include(
                                                                    'user.pages.plans.includes.icon-' .
                                                                        ($plan->no_of_services > 0
                                                                            ? 'check'
                                                                            : 'x'))
                                                            </span>
                                                            <span>
                                                                @if ($plan->no_of_services != 0)
                                                                    <span
                                                                        class="value">{{ $plan->no_of_services == 999 ? __('Unlimited') : $plan->no_of_services }}</span>
                                                                @endif
                                                                {{ __('Services') }}
                                                            </span>
                                                        </div>
                                                    </li>

                                                    <li>
                                                        <div class="feature-left">
                                                            <span
                                                                class="feature-icon {{ $plan->no_of_vcard_products > 0 ? 'is-on' : 'is-off' }}">
                                                                @include(
                                                                    'user.pages.plans.includes.icon-' .
                                                                        ($plan->no_of_vcard_products > 0
                                                                            ? 'check'
                                                                            : 'x'))
                                                            </span>
                                                            <span>
                                                                @if ($plan->no_of_vcard_products != 0)
                                                                    <span
                                                                        class="value">{{ $plan->no_of_vcard_products == 999 ? __('Unlimited') : $plan->no_of_vcard_products }}</span>
                                                                @endif
                                                                {{ __('Products') }}
                                                            </span>
                                                        </div>
                                                    </li>

                                                    <li>
                                                        <div class="feature-left">
                                                            <span
                                                                class="feature-icon {{ $plan->no_of_links > 0 ? 'is-on' : 'is-off' }}">
                                                                @include(
                                                                    'user.pages.plans.includes.icon-' .
                                                                        ($plan->no_of_links > 0 ? 'check' : 'x'))
                                                            </span>
                                                            <span>
                                                                @if ($plan->no_of_links != 0)
                                                                    <span
                                                                        class="value">{{ $plan->no_of_links == 999 ? __('Unlimited') : $plan->no_of_links }}</span>
                                                                @endif
                                                                {{ __('Links') }}
                                                            </span>
                                                        </div>
                                                    </li>

                                                    <li>
                                                        <div class="feature-left">
                                                            <span
                                                                class="feature-icon {{ $plan->no_of_payments > 0 ? 'is-on' : 'is-off' }}">
                                                                @include(
                                                                    'user.pages.plans.includes.icon-' .
                                                                        ($plan->no_of_payments > 0
                                                                            ? 'check'
                                                                            : 'x'))
                                                            </span>
                                                            <span>
                                                                @if ($plan->no_of_payments != 0)
                                                                    <span
                                                                        class="value">{{ $plan->no_of_payments == 999 ? __('Unlimited') : $plan->no_of_payments }}</span>
                                                                @endif
                                                                {{ __('Payment Listed') }}
                                                            </span>
                                                        </div>
                                                    </li>

                                                    <li>
                                                        <div class="feature-left">
                                                            <span
                                                                class="feature-icon {{ $plan->no_of_galleries > 0 ? 'is-on' : 'is-off' }}">
                                                                @include(
                                                                    'user.pages.plans.includes.icon-' .
                                                                        ($plan->no_of_galleries > 0
                                                                            ? 'check'
                                                                            : 'x'))
                                                            </span>
                                                            <span>
                                                                @if ($plan->no_of_galleries != 0)
                                                                    <span
                                                                        class="value">{{ $plan->no_of_galleries == 999 ? __('Unlimited') : $plan->no_of_galleries }}</span>
                                                                @endif
                                                                {{ __('Galleries') }}
                                                            </span>
                                                        </div>
                                                    </li>

                                                    <li>
                                                        <div class="feature-left">
                                                            <span
                                                                class="feature-icon {{ $plan->no_testimonials > 0 ? 'is-on' : 'is-off' }}">
                                                                @include(
                                                                    'user.pages.plans.includes.icon-' .
                                                                        ($plan->no_testimonials > 0
                                                                            ? 'check'
                                                                            : 'x'))
                                                            </span>
                                                            <span>
                                                                @if ($plan->no_testimonials != 0)
                                                                    <span
                                                                        class="value">{{ $plan->no_testimonials == 999 ? __('Unlimited') : $plan->no_testimonials }}</span>
                                                                @endif
                                                                {{ __('Testimonials') }}
                                                            </span>
                                                        </div>
                                                    </li>

                                                    <li>
                                                        <div class="feature-left">
                                                            <span
                                                                class="feature-icon {{ $plan->business_hours == 1 ? 'is-on' : 'is-off' }}">
                                                                @include(
                                                                    'user.pages.plans.includes.icon-' .
                                                                        ($plan->business_hours == 1
                                                                            ? 'check'
                                                                            : 'x'))
                                                            </span>
                                                            <span>{{ __('Business Hours') }}</span>
                                                        </div>
                                                    </li>

                                                    <li>
                                                        <div class="feature-left">
                                                            <span
                                                                class="feature-icon {{ $plan->appointment == 1 ? 'is-on' : 'is-off' }}">
                                                                @include(
                                                                    'user.pages.plans.includes.icon-' .
                                                                        ($plan->appointment == 1 ? 'check' : 'x'))
                                                            </span>
                                                            <span>{{ __('Appointments') }}</span>
                                                        </div>
                                                    </li>

                                                    <li>
                                                        <div class="feature-left">
                                                            <span
                                                                class="feature-icon {{ $plan->service_booking > 0 ? 'is-on' : 'is-off' }}">
                                                                @include(
                                                                    'user.pages.plans.includes.icon-' .
                                                                        ($plan->service_booking > 0
                                                                            ? 'check'
                                                                            : 'x'))
                                                            </span>
                                                            <span>{{ __('Service Booking') }}</span>
                                                        </div>
                                                        <span
                                                            class="badge badge-new bg-green text-white">{{ __('New') }}</span>
                                                    </li>

                                                    <li>
                                                        <div class="feature-left">
                                                            <span
                                                                class="feature-icon {{ $plan->contact_form == 1 ? 'is-on' : 'is-off' }}">
                                                                @include(
                                                                    'user.pages.plans.includes.icon-' .
                                                                        ($plan->contact_form == 1
                                                                            ? 'check'
                                                                            : 'x'))
                                                            </span>
                                                            <span>{{ __('Contact Form') }}</span>
                                                        </div>
                                                    </li>

                                                    <li>
                                                        <div class="feature-left">
                                                            <span
                                                                class="feature-icon {{ $plan->no_of_enquires > 0 ? 'is-on' : 'is-off' }}">
                                                                @include(
                                                                    'user.pages.plans.includes.icon-' .
                                                                        ($plan->no_of_enquires > 0
                                                                            ? 'check'
                                                                            : 'x'))
                                                            </span>
                                                            <span>
                                                                @if ($plan->no_of_enquires != 0)
                                                                    <span
                                                                        class="value">{{ $plan->no_of_enquires == 999 ? __('Unlimited') : $plan->no_of_enquires }}</span>
                                                                @endif
                                                                {{ __('Enquiries') }}
                                                            </span>
                                                        </div>
                                                    </li>

                                                    @if (plugin_active('GoogleWallet'))
                                                        @if ($plan->google_wallet == 1)
                                                            <li>
                                                                <div class="feature-left">
                                                                    <span
                                                                        class="feature-icon {{ $plan->no_of_google_wallets > 0 ? 'is-on' : 'is-off' }}">
                                                                        @include(
                                                                            'user.pages.plans.includes.icon-' .
                                                                                ($plan->no_of_google_wallets > 0
                                                                                    ? 'check'
                                                                                    : 'x'))
                                                                    </span>
                                                                    <span>
                                                                        @if ($plan->no_of_google_wallets != 0)
                                                                            <span
                                                                                class="value">{{ $plan->no_of_google_wallets == 999 ? __('Unlimited') : $plan->no_of_google_wallets }}</span>
                                                                        @endif
                                                                        {{ __('Google Wallets') }}
                                                                    </span>
                                                                </div>
                                                            </li>
                                                        @endif
                                                    @endif

                                                    <li>
                                                        <div class="feature-left">
                                                            <span
                                                                class="feature-icon {{ $plan->password_protected == 1 ? 'is-on' : 'is-off' }}">
                                                                @include(
                                                                    'user.pages.plans.includes.icon-' .
                                                                        ($plan->password_protected == 1
                                                                            ? 'check'
                                                                            : 'x'))
                                                            </span>
                                                            <span>{{ __('Password Protected') }}</span>
                                                        </div>
                                                    </li>
                                                @endif

                                                {{-- Check Card type is "Both" or "STORE" --}}
                                                @if ($plan->plan_type == 'BOTH' || $plan->plan_type == 'STORE')
                                                    <li class="feature-section-title">{{ __('Store Features') }}</li>

                                                    <li>
                                                        <div class="feature-left">
                                                            <span
                                                                class="feature-icon {{ $plan->no_of_stores > 0 ? 'is-on' : 'is-off' }}">
                                                                @include(
                                                                    'user.pages.plans.includes.icon-' .
                                                                        ($plan->no_of_stores > 0 ? 'check' : 'x'))
                                                            </span>
                                                            <span>
                                                                @if ($plan->no_of_stores != 0)
                                                                    <span
                                                                        class="value">{{ $plan->no_of_stores == '999' ? __('Unlimited') : $plan->no_of_stores }}</span>
                                                                @endif
                                                                {{ __('Stores') }}
                                                            </span>
                                                        </div>
                                                    </li>

                                                    <li>
                                                        <div class="feature-left">
                                                            <span
                                                                class="feature-icon {{ $plan->no_of_categories > 0 ? 'is-on' : 'is-off' }}">
                                                                @include(
                                                                    'user.pages.plans.includes.icon-' .
                                                                        ($plan->no_of_categories > 0
                                                                            ? 'check'
                                                                            : 'x'))
                                                            </span>
                                                            <span>
                                                                @if ($plan->no_of_categories != 0)
                                                                    <span
                                                                        class="value">{{ $plan->no_of_categories == '999' ? __('Unlimited') : $plan->no_of_categories }}</span>
                                                                @endif
                                                                {{ __('Categories') }}
                                                            </span>
                                                        </div>
                                                    </li>

                                                    <li>
                                                        <div class="feature-left">
                                                            <span
                                                                class="feature-icon {{ $plan->no_of_store_products > 0 ? 'is-on' : 'is-off' }}">
                                                                @include(
                                                                    'user.pages.plans.includes.icon-' .
                                                                        ($plan->no_of_store_products > 0
                                                                            ? 'check'
                                                                            : 'x'))
                                                            </span>
                                                            <span>
                                                                @if ($plan->no_of_store_products != 0)
                                                                    <span
                                                                        class="value">{{ $plan->no_of_store_products == '999' ? __('Unlimited') : $plan->no_of_store_products }}</span>
                                                                @endif
                                                                {{ __('Products') }}
                                                            </span>
                                                        </div>
                                                    </li>
                                                @endif

                                                {{-- Additional Features --}}
                                                <li class="feature-section-title">{{ __('Additional Features') }}</li>

                                                <li>
                                                    <div class="feature-left">
                                                        <span
                                                            class="feature-icon {{ $plan->custom_domain == 1 ? 'is-on' : 'is-off' }}">
                                                            @include(
                                                                'user.pages.plans.includes.icon-' .
                                                                    ($plan->custom_domain == 1 ? 'check' : 'x'))
                                                        </span>
                                                        <span>{{ __('Custom Domain') }}</span>
                                                    </div>
                                                    <span
                                                        class="badge badge-new bg-green text-white">{{ __('New') }}</span>
                                                </li>

                                                @if ($config[76]->config_value == '1')
                                                    <li>
                                                        <div class="feature-left">
                                                            <span class="feature-icon is-on">
                                                                @include('user.pages.plans.includes.icon-check')
                                                            </span>
                                                            <span>{{ __('Order NFC Card') }}</span>
                                                        </div>
                                                        <span
                                                            class="badge badge-new bg-green text-white">{{ __('New') }}</span>
                                                    </li>
                                                @endif

                                                <li>
                                                    <div class="feature-left">
                                                        <span
                                                            class="feature-icon {{ $plan->storage > 0 ? 'is-on' : 'is-off' }}">
                                                            @include(
                                                                'user.pages.plans.includes.icon-' .
                                                                    ($plan->storage > 0 ? 'check' : 'x'))
                                                        </span>
                                                        <span>
                                                            @if ($plan->storage != 0)
                                                                <span
                                                                    class="value">{{ $plan->storage == '999' ? __('Unlimited') : $plan->storage . 'MB' }}</span>
                                                            @endif
                                                            {{ __('storage limit') }}
                                                        </span>
                                                    </div>
                                                    <span
                                                        class="badge badge-new bg-green text-white">{{ __('New') }}</span>
                                                </li>

                                                <li>
                                                    <div class="feature-left">
                                                        <span
                                                            class="feature-icon {{ $plan->advanced_settings == 1 ? 'is-on' : 'is-off' }}">
                                                            @include(
                                                                'user.pages.plans.includes.icon-' .
                                                                    ($plan->advanced_settings == 1
                                                                        ? 'check'
                                                                        : 'x'))
                                                        </span>
                                                        <span>{{ __('Advanced Settings') }}</span>
                                                    </div>
                                                </li>

                                                <li>
                                                    <div class="feature-left">
                                                        <span
                                                            class="feature-icon {{ $plan->pwa == 1 ? 'is-on' : 'is-off' }}">
                                                            @include(
                                                                'user.pages.plans.includes.icon-' .
                                                                    ($plan->pwa == 1 ? 'check' : 'x'))
                                                        </span>
                                                        <span>{{ __('Progressive Web App (PWA)') }}</span>
                                                    </div>
                                                </li>

                                                <li>
                                                    <div class="feature-left">
                                                        <span
                                                            class="feature-icon {{ $plan->personalized_link == 1 ? 'is-on' : 'is-off' }}">
                                                            @include(
                                                                'user.pages.plans.includes.icon-' .
                                                                    ($plan->personalized_link == 1
                                                                        ? 'check'
                                                                        : 'x'))
                                                        </span>
                                                        <span>{{ __('Personalized Link') }}</span>
                                                    </div>
                                                </li>

                                                <li>
                                                    <div class="feature-left">
                                                        <span
                                                            class="feature-icon {{ $plan->hide_branding == 1 ? 'is-on' : 'is-off' }}">
                                                            @include(
                                                                'user.pages.plans.includes.icon-' .
                                                                    ($plan->hide_branding == 1 ? 'check' : 'x'))
                                                        </span>
                                                        <span>{{ __('Hide Branding') }}</span>
                                                    </div>
                                                </li>

                                                <li>
                                                    <div class="feature-left">
                                                        <span
                                                            class="feature-icon {{ $plan->free_setup == 1 ? 'is-on' : 'is-off' }}">
                                                            @include(
                                                                'user.pages.plans.includes.icon-' .
                                                                    ($plan->free_setup == 1 ? 'check' : 'x'))
                                                        </span>
                                                        <span>{{ __('Free Setup') }}</span>
                                                    </div>
                                                </li>

                                                <li>
                                                    <div class="feature-left">
                                                        <span
                                                            class="feature-icon {{ $plan->free_support == 1 ? 'is-on' : 'is-off' }}">
                                                            @include(
                                                                'user.pages.plans.includes.icon-' .
                                                                    ($plan->free_support == 1 ? 'check' : 'x'))
                                                        </span>
                                                        <span>{{ __('Free Support') }}</span>
                                                    </div>
                                                </li>
                                            </ul>
                                        </div>

                                        <div class="text-center mt-4 mt-auto pt-3" id="upgrade-plan-button">
                                            @php
                                                $user = Auth::user();

                                                $isPaidPlan = $plan->plan_price != 0;
                                                $isFreePlan = $plan->plan_price == 0;

                                                $onTrial = $user->trial != 0;
                                                $userHasPlan = $user->plan_id == $plan->plan_id;
                                                $userHasNoPlan = is_null($user->plan_id);

                                                $eligibleForTrial =
                                                    $user->trial == 0 && $plan->trial != 0 && $isPaidPlan;

                                                // Check if user has already activated free plan
                                                $userHasUsedFreePlan =
                                                    !$userHasNoPlan && $user->plan_id && $plan->plan_price == 0;
                                            @endphp

                                            @if ($userHasPlan && $isPaidPlan)
                                                <a class="btn btn-dark w-100"
                                                    href="{{ route('user.checkout', $plan->plan_id) }}">
                                                    {{ __('Current Plan') }}
                                                </a>
                                            @elseif ($eligibleForTrial)
                                                <a class="btn btn-dark w-100"
                                                    href="{{ route('user.checkout', $plan->plan_id) }}">
                                                    {{ __('Start') }} {{ $plan->trial }} {{ __('Days Trial') }}
                                                </a>
                                            @elseif ($isPaidPlan && $onTrial)
                                                <a class="open-plan-model btn btn-primary w-100"
                                                    data-id="{{ $plan->plan_id }}"
                                                    href="#openPlanModel">{{ __('Choose plan') }}
                                                </a>
                                            @elseif ($isFreePlan && $free_plan != 0 && $userHasNoPlan)
                                                {{-- First-time user selecting free plan --}}
                                                <a class="open-plan-model btn btn-primary w-100"
                                                    data-id="{{ $plan->plan_id }}"
                                                    href="#openPlanModel">{{ __('Choose plan') }}
                                                </a>
                                            @elseif ($isFreePlan && $userHasUsedFreePlan)
                                                {{-- Show downPlanModal if free plan already activated --}}
                                                <a class="down-plan-model btn btn-primary w-100"
                                                    data-id="{{ $plan->plan_id }}"
                                                    href="#downPlanModel">{{ __('Choose plan') }}
                                                </a>
                                            @elseif ($isPaidPlan)
                                                <a class="open-plan-model btn btn-primary w-100"
                                                    data-id="{{ $plan->plan_id }}"
                                                    href="#openPlanModel">{{ __('Choose plan') }}
                                                </a>
                                            @else
                                                {{-- Fallback (should rarely trigger) --}}
                                                <a class="open-plan-model btn btn-primary w-100"
                                                    data-id="{{ $plan->plan_id }}"
                                                    href="#openPlanModel">{{ __('Choose plan') }}
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach

                    </div>
                </div>
            </div>
        </div>

        @if (!empty($nfc_ref) && empty(Auth::user()->plan_id))
            <x-activate-nfc-plan :nfc_ref="$nfc_ref" :plan_assign_type="$planAssignType" />
        @endif

        {{-- Footer --}}
        @include('user.includes.footer')
    </div>

    {{-- Current Plan Modal --}}
    @if (isset($active_plan))
        @include('user.pages.plans.includes.current-plan-modal')
    @endif

    {{-- Plan Modal --}}
    <div class="modal modal-bg fade" id="planModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-status"></div>
                <div class="modal-body text-center py-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon mb-2 text-danger icon-lg" width="24"
                        height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                        <path d="M12 9v2m0 4v.01" />
                        <path
                            d="M5 19h14a2 2 0 0 0 1.84 -2.75l-7.1 -12.25a2 2 0 0 0 -3.5 0l-7.1 12.25a2 2 0 0 0 1.75 2.75" />
                    </svg>
                    <h3>{{ __('Are you sure?') }}</h3>
                    <div class="text-muted">
                        {{ __('If you proceed, your subscription will be canceled and it will not renew again. You will still be able to use your current plan until it expires.') }}
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="w-100">
                        <div class="row">
                            <div class="col">
                                <button type="button" class="btn w-100" data-bs-dismiss="modal">
                                    {{ __('Cancel') }}
                                </button>
                            </div>
                            <div class="col">
                                <a class="btn btn-danger w-100" id="plan_id">
                                    {{ __('Yes, proceed') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Plan Downgrade Modal --}}
    <div class="modal modal-bg fade" id="downPlanModel" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-status"></div>
                <div class="modal-body text-center py-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon mb-2 text-danger icon-lg" width="24"
                        height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                        <path d="M12 9v2m0 4v.01" />
                        <path
                            d="M5 19h14a2 2 0 0 0 1.84 -2.75l-7.1 -12.25a2 2 0 0 0 -3.5 0l-7.1 12.25a2 2 0 0 0 1.75 2.75" />
                    </svg>
                    <h3>{{ __('UNABLE TO DOWNGRADE') }}</h3>
                    <div class="text-muted">{{ __("Because you are already activated the 'Free' plan.") }}</div>
                </div>
                <div class="modal-footer">
                    <div class="w-100">
                        <div class="row">
                            <div class="col">
                                <button type="button" class="btn w-100" data-bs-dismiss="modal">
                                    {{ __('Close') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- cancelSubscription --}}
    <div class="modal modal-bg fade" id="subscriptionCancelModel" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-status"></div>
                <div class="modal-body text-center py-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon mb-2 text-danger icon-lg" width="24"
                        height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                        <path d="M12 9v2m0 4v.01" />
                        <path
                            d="M5 19h14a2 2 0 0 0 1.84 -2.75l-7.1 -12.25a2 2 0 0 0 -3.5 0l-7.1 12.25a2 2 0 0 0 1.75 2.75" />
                    </svg>
                    <h3>{{ __('Are you sure?') }}</h3>
                    <div id="modal-message" class="text-muted">
                        {{ __('If you proceed, you will cancel the subscription.') }}
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="w-100">
                        <div class="row">
                            <div class="col">
                                <button type="button" class="btn w-100" data-bs-dismiss="modal">
                                    {{ __('Cancel') }}
                                </button>
                            </div>
                            <div class="col">
                                <a data-method="post" class="btn btn-danger w-100" id="subscription_id">
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
    <script src="{{ asset('js/confetti.browser.min.js') }}"></script>
    <script type="text/javascript">
        "use strict";

        // Scroll to plans section
        document.addEventListener("click", function(e) {
            if (e.target.closest("#plan")) {
                e.preventDefault();

                const plans = document.getElementById("plans");

                if (plans) {
                    plans.scrollIntoView({
                        behavior: "smooth",
                        block: "start"
                    });
                }
            }
        });

        // Cancel subscription
        function cancelSubscription() {
            var subscriptionId = "{{ $subscriptionId }}";

            // Show modal
            $("#subscriptionCancelModel").modal("show");

            // Modal message
            var modalMessage = document.getElementById("modal-message");
            let message = "{{ __('If you proceed, you will cancel the subscription.') }}";
            modalMessage.innerHTML = message;

            // Status ID
            var link = document.getElementById("subscription_id");
            link.getAttribute("href");
            link.setAttribute("href", "/user/cancel-subscription/" + subscriptionId);
        }
    </script>
@endsection
