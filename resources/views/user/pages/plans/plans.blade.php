@extends('user.layouts.index', ['header' => true, 'nav' => true, 'demo' => true, 'settings' => $settings])

{{-- Custom CSS --}}
@section('css')
    <style>
        .icon-color {
            color: forestgreen;
            font-size: 20px;
            display: inline;
        }

        .ti {
            font-size: 16px !important;
        }

        .badge {
            padding: 5px 5px !important;
            margin-bottom: 0px !important;
            margin-left: 0px !important;
            margin-right: 10px !important;
        }

        /* RTL support */
        [dir="rtl"] .badge {
            margin-left: 10px !important;
            /* Reset LTR margin */
            margin-right: 0px !important;
        }

        .badge-sm {
            --tblr-badge-font-size: 0.71428571em;
            --tblr-badge-icon-size: 1em;
            --tblr-badge-padding-y: 2px;
            --tblr-badge-padding-x: 0.25rem;
        }

        .ms-6 {
            padding: 2px !important;
            font-size: 10px !important;
            margin-left: 5px !important;
        }

        /* RTL support */
        [dir="rtl"] .ms-6 {
            margin-left: 0px !important;
            margin-right: 5px !important;
        }

        /* Medium devices (tablets, 768px and up) */
        @media (max-width: 767.98px) {
            .currentPlanDialog {
                bottom: 0;
                left: 0;
                right: 0;
                margin-top: 4rem;
            }
        }

        /* Extra small devices (portrait phones, less than 576px) */
        @media (max-width: 320px) {
            .currentPlanModal {
                margin-top: 0.5rem;
            }
        }
    </style>
@endsection

{{-- AI Builder --}}
@php
    $aibuilder_settings = DB::table('aibuilder_settings')->first();
@endphp

@section('content')
    <div class="page-wrapper">
        <!-- Page title -->
        <div class="page-header d-print-none">
            <div class="container-fluid">
                <div class="row g-2 align-items-center">
                    <div class="col">
                        <div class="page-pretitle">
                            {{ __('Overview') }}
                        </div>
                        <h2 class="page-title">
                            {{ __('Plans') }}
                        </h2>
                    </div>
                </div>
            </div>
        </div>

        <div class="page-body">
            <div class="container-fluid">
                {{-- Failed --}}
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

                {{-- Success --}}
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

                <div class="col-12">
                    <div class="card">
                        <div class="card-body space-y-3">
                            <h3 class="card-title">{{ __('My plan') }}</h3>

                            @if (isset($active_plan))

                                @if ($active_plan->plan_price == 0)
                                    <p class="text-uppercase"><b>{{ __($active_plan->plan_name) }}</b></p>
                                    <p class="h4 h-lg-1 text-custom">{{ __('FREE PLAN') }}</p>
                                @else
                                    <p class="text-uppercase"><b>{{ __($active_plan->plan_name) }}</b></p>
                                    @if ($active_plan->validity == 9999)
                                        <p class="h4 h-lg-1 text-custom">{{ __('Lifetime') }}</p>
                                    @else
                                        <p class="fw-bold display-6">
                                            {{ $remaining_days > 0 ? floor($remaining_days) : __('Plan Expired!') }}
                                        </p>
                                        <span class="fw-bold">
                                            {{ __('Expires on') }}:
                                            {{ formatDateForUser(Auth::user()->plan_validity) }}
                                        </span>
                                    @endif
                                @endif

                                <div class="card-text d-flex flex-wrap gap-2 align-items-center">
                                    @if ($free_plan == 0 || $active_plan->plan_price != 0)
                                        <a href="{{ route('user.checkout', $active_plan->plan_id) }}" class="btn btn-md">
                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                class="icon icon-tabler icon-tabler-rotate" width="24" height="24"
                                                viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                                                stroke-linecap="round" stroke-linejoin="round">
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                                <path d="M19.95 11a8 8 0 1 0 -.5 4m.5 5v-5h-5"></path>
                                            </svg>
                                            {{ __('Renew') }}
                                        </a>
                                    @endif
                                    <a href="#plans" class="btn btn-md btn-primary">
                                        <svg xmlns="http://www.w3.org/2000/svg"
                                            class="icon icon-tabler icon-tabler-circle-arrow-up-filled" width="24"
                                            height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                            fill="none" stroke-linecap="round" stroke-linejoin="round">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                            <path
                                                d="M17 3.34a10 10 0 1 1 -14.995 8.984l-.005 -.324l.005 -.324a10 10 0 0 1 14.995 -8.336zm-4.98 3.66l-.163 .01l-.086 .016l-.142 .045l-.113 .054l-.07 .043l-.095 .071l-.058 .054l-4 4l-.083 .094a1 1 0 0 0 1.497 1.32l2.293 -2.293v5.586l.007 .117a1 1 0 0 0 1.993 -.117v-5.585l2.293 2.292l.094 .083a1 1 0 0 0 1.32 -1.497l-4 -4l-.082 -.073l-.089 -.064l-.113 -.062l-.081 -.034l-.113 -.034l-.112 -.02l-.098 -.006z"
                                                stroke-width="0" fill="currentColor"></path>
                                        </svg>
                                        {{ __('Upgrade') }}
                                    </a>
                                    @if ($cancelSubscription == 1)
                                        <button onclick="cancelSubscription()" class="btn btn-md btn-danger">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                stroke-linecap="round" stroke-linejoin="round"
                                                class="icon icon-tabler icons-tabler-outline icon-tabler-x">
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                <path d="M18 6l-12 12" />
                                                <path d="M6 6l12 12" />
                                            </svg>
                                            {{ __('Cancel') }}
                                        </button>
                                    @endif
                                    {{-- View Current Plan Feature --}}
                                    <button type="button" class="btn btn-md btn-dark currentPlanModal"
                                        data-bs-toggle="modal" data-bs-target="#currentPlanModal">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round"
                                            class="icon icon-tabler icon-tabler-eye">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                            <circle cx="12" cy="12" r="2" />
                                            <path d="M22 12c-2.5 4-6.5 7-10 7s-7.5-3-10-7c2.5-4 6.5-7 10-7s7.5 3 10 7" />
                                        </svg>
                                        {{ __('My Plan') }}
                                    </button>
                                </div>
                            @else
                                <p>{{ __('No active plans!') }}</p>

                                <div class="card-text">
                                    <a href="#plans" class="btn btn-primary">{{ __('Choose plan') }}</a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div id="plans" class="page-body">
                <div class="container-fluid">

                    <div class="row">

                        @foreach ($plans as $plan)
                            <div class="col-sm-4 col-lg-4 mb-3">
                                <div class="card card-md">

                                    @if ($plan->recommended == '1')
                                        <div class="ribbon ribbon-top ribbon-bookmark bg-green">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-filled"
                                                width="24" height="24" viewBox="0 0 24 24" stroke-width="2"
                                                stroke="currentColor" fill="none" stroke-linecap="round"
                                                stroke-linejoin="round">
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                <path
                                                    d="M12 17.75l-6.172 3.245l1.179 -6.873l-5 -4.867l6.9 -1l3.086 -6.253l3.086 6.253l6.9 1l-5 4.867l1.179 6.873z" />
                                            </svg>
                                        </div>
                                    @endif

                                    <div class="card-body">
                                        <span
                                            class="badge bg-primary text-white">{{ __($plan->plan_type == 'BOTH' ? 'VCARD & STORE' : $plan->plan_type) }}</span>
                                        {{-- Trial Period --}}
                                        @if (Auth::user()->trial == 0 && $plan->trial != 0)
                                            <span class="badge bg-dark text-white text-uppercase">{{ __($plan->trial) }}
                                                {{ __('Days Free Trial') }}</span>
                                        @endif
                                        <div class="text-capitalize font-weight-bold h2 mt-2">{{ __($plan->plan_name) }}
                                        </div>
                                        <div class="my-3">
                                            <h3 class="display-4">
                                                <strong>
                                                    {{ $plan->plan_price == '0' ? '' : '' }}{{ $plan->plan_price == '0' ? __('FREE') : formatCurrency($plan->plan_price) }}
                                                </strong>
                                            </h3>

                                            <small class="text-capitalize">
                                                @if ((!str_contains(strtolower($plan->plan_name), 'trial') && (int) $plan->plan_price == 0) || $plan->validity == 9999)
                                                    {{ __('Forever') }}
                                                @endif
                                                @if ((int) $plan->plan_price != 0 && $plan->validity >= 29 && $plan->validity <= 31)
                                                    {{ __('Per') }} {{ __('Month') }}</span>
                                                @endif
                                                @if ((int) $plan->plan_price != 0 && $plan->validity >= 365 && $plan->validity <= 366)
                                                    {{ __('Per') }} {{ __('Year') }}</span>
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
                                        <p class="my-3">{{ __($plan->plan_description) }}</p>
                                        <ul class="list-unstyled lh-lg">
                                            {{-- Check Card type is "Both" or "VCARD" --}}
                                            @if ($plan->plan_type == 'BOTH' || $plan->plan_type == 'VCARD')
                                                <h4 class="mb-3 text-primary">{{ __('vCard Features') }}</h4>

                                                {{-- AI Credits --}}
                                                @if ($aibuilder_settings->aibuilder == 1)
                                                    <li class="d-flex align-items-center">
                                                        <span
                                                            class="badge bg-{{ __($plan->ai_credits > 0 ? 'success' : 'danger') }} me-1"></span>
                                                        <strong>
                                                            {{ $plan->ai_credits == 999 ? __('Unlimited') : ($plan->ai_credits != 0 ? $plan->ai_credits : '') }}
                                                            {{ __('AI Credits') }}
                                                            <span id="popover-element"
                                                                style="width: 1rem !important;height: 1rem !important;"
                                                                class="form-help bg-primary text-white ms-1"
                                                                data-bs-toggle="popover" data-bs-placement="top"
                                                                data-bs-html="true"
                                                                data-bs-content="<p>{{ __('1 Credit = 1 AI Generation') }}</p>">
                                                                ?
                                                            </span>
                                                        </strong>
                                                        <span
                                                            class="badge badge-sm bg-green text-white text-uppercase ms-6">{{ __('New') }}</span>
                                                    </li>
                                                @endif

                                                <li><span
                                                        class="badge bg-{{ __($plan->no_of_vcards > 0 ? 'success' : 'danger') }} me-1"></span><strong>{{ $plan->no_of_vcards == 999 ? __('Unlimited') : ($plan->no_of_vcards != 0 ? $plan->no_of_vcards : '') }}
                                                        {{ __('vCards') }}</strong></li>

                                                <li><span
                                                        class="badge bg-{{ __($plan->no_of_services > 0 ? 'success' : 'danger') }} me-1"></span><strong>{{ $plan->no_of_services == 999 ? __('Unlimited') : ($plan->no_of_services != 0 ? $plan->no_of_services : '') }}
                                                        {{ __('Services') }}</strong></li>

                                                <li><span
                                                        class="badge bg-{{ __($plan->no_of_vcard_products > 0 ? 'success' : 'danger') }} me-1"></span><strong>{{ $plan->no_of_vcard_products == 999 ? __('Unlimited') : ($plan->no_of_vcard_products != 0 ? $plan->no_of_vcard_products : '') }}
                                                        {{ __('Products') }}</strong></li>

                                                <li><span
                                                        class="badge bg-{{ __($plan->no_of_links > 0 ? 'success' : 'danger') }} me-1"></span><strong>{{ $plan->no_of_links == 999 ? __('Unlimited') : ($plan->no_of_links != 0 ? $plan->no_of_links : '') }}
                                                        {{ __('Links') }}</strong></li>

                                                <li><span
                                                        class="badge bg-{{ __($plan->no_of_payments > 0 ? 'success' : 'danger') }} me-1"></span><strong>{{ $plan->no_of_payments == 999 ? __('Unlimited') : ($plan->no_of_payments != 0 ? $plan->no_of_payments : '') }}
                                                        {{ __('Payment Listed') }}</strong></li>

                                                <li><span
                                                        class="badge bg-{{ __($plan->no_of_galleries > 0 ? 'success' : 'danger') }} me-1"></span><strong>{{ $plan->no_of_galleries == 999 ? __('Unlimited') : ($plan->no_of_galleries != 0 ? $plan->no_of_galleries : '') }}
                                                        {{ __('Galleries') }}</strong></li>

                                                <li><span
                                                        class="badge bg-{{ __($plan->no_testimonials > 0 ? 'success' : 'danger') }} me-1"></span><strong>{{ $plan->no_testimonials == 999 ? __('Unlimited') : ($plan->no_testimonials != 0 ? $plan->no_testimonials : '') }}
                                                        {{ __('Testimonials') }}</strong></li>

                                                <li><span
                                                        class="badge bg-{{ __($plan->business_hours == 1 ? 'success' : 'danger') }} me-1"></span>
                                                    <strong>{{ __('Business Hours') }}</strong>
                                                </li>

                                                <li><span
                                                        class="badge bg-{{ __($plan->appointment == 1 ? 'success' : 'danger') }} me-1"></span>
                                                    <strong>{{ __('Appointments') }}</strong>
                                                </li>

                                                <li class="d-flex align-items-center justify-content-between">
                                                    <div class="d-flex align-items-center">
                                                        <span
                                                            class="badge rounded-circle bg-{{ $plan->service_booking > 0 ? 'success' : 'danger' }} me-2"
                                                            style="width: 10px; height: 10px;"></span>
                                                        <strong>
                                                            {{ __('Service Booking') }}
                                                        </strong>
                                                        <span
                                                            class="badge badge-sm bg-green text-white text-uppercase ms-6">{{ __('New') }}</span>
                                                    </div>
                                                </li>

                                                <li><span
                                                        class="badge bg-{{ __($plan->contact_form == 1 ? 'success' : 'danger') }} me-1"></span>
                                                    <strong>{{ __('Contact Form') }}</strong>
                                                </li>

                                                <li><span
                                                        class="badge bg-{{ __($plan->no_of_enquires > 0 ? 'success' : 'danger') }} me-1"></span><strong>{{ $plan->no_of_enquires == 999 ? __('Unlimited') : ($plan->no_of_enquires != 0 ? $plan->no_of_enquires : '') }}
                                                        {{ __('Enquiries') }}</strong></li>

                                                @if (is_dir(base_path('plugins/GoogleWallet')))
                                                    @if ($plan->google_wallet == 1)
                                                        <li><span
                                                                class="badge bg-{{ __($plan->no_of_google_wallets > 0 ? 'success' : 'danger') }} me-1"></span><strong>{{ $plan->no_of_google_wallets == 999 ? __('Unlimited') : ($plan->no_of_google_wallets != 0 ? $plan->no_of_google_wallets : '') }}
                                                                {{ __('Google Wallets') }}</strong></li>
                                                    @endif
                                                @endif

                                                <li><span
                                                        class="badge bg-{{ __($plan->password_protected == 1 ? 'success' : 'danger') }} me-1"></span>
                                                    <strong>{{ __('Password Protected') }}</strong>
                                                </li>
                                            @endif

                                            {{-- Check Card type is "Both" or "STORE" --}}
                                            @if ($plan->plan_type == 'BOTH' || $plan->plan_type == 'STORE')
                                                <h4 class="mt-3 mb-3 text-primary">{{ __('Store Features') }}</h4>

                                                <li><span
                                                        class="badge bg-{{ __($plan->no_of_stores > 0 ? 'success' : 'danger') }} me-1"></span><strong>{{ $plan->no_of_stores == '999' ? __('Unlimited') : ($plan->no_of_stores != 0 ? $plan->no_of_stores : '') }}
                                                        {{ __('Stores') }}</strong></li>

                                                <li>
                                                    <span
                                                        class="badge bg-{{ __($plan->no_of_categories > 0 ? 'success' : 'danger') }} me-1"></span>
                                                    <strong>{{ $plan->no_of_categories == '999' ? __('Unlimited') : ($plan->no_of_categories != 0 ? $plan->no_of_categories : '') }}
                                                        {{ __('Categories') }}</strong>
                                                </li>
                                                <li>
                                                    <span
                                                        class="badge bg-{{ __($plan->no_of_store_products > 0 ? 'success' : 'danger') }} me-1"></span>
                                                    <strong>{{ $plan->no_of_store_products == '999' ? __('Unlimited') : ($plan->no_of_store_products != 0 ? $plan->no_of_store_products : '') }}
                                                        {{ __('Products') }}</strong>
                                                </li>
                                            @endif

                                            {{-- Additional Features --}}
                                            <h4 class="mt-3 mb-3 text-primary">{{ __('Additional Features') }}</h4>
                                            <li class="d-flex align-items-center justify-content-between">
                                                <div class="d-flex align-items-center">
                                                    <span
                                                        class="badge rounded-circle bg-{{ $plan->custom_domain == 1 ? 'success' : 'danger' }} me-2"
                                                        style="width: 10px; height: 10px;"></span>
                                                    <strong>{{ __('Custom Domain') }}</strong>
                                                    <span
                                                        class="badge badge-sm bg-green text-white text-uppercase ms-6">{{ __('New') }}</span>
                                                </div>
                                            </li>

                                            @if ($config[76]->config_value == '1')
                                                <li class="d-flex align-items-center justify-content-between">
                                                    <div class="d-flex align-items-center">
                                                        <span class="badge rounded-circle bg-success me-2"
                                                            style="width: 10px; height: 10px;"></span>
                                                        <strong>{{ __('Order NFC Card') }}</strong>
                                                        <span
                                                            class="badge badge-sm bg-green text-white text-uppercase ms-6">{{ __('New') }}</span>
                                                    </div>
                                                </li>
                                            @endif

                                            <li class="d-flex align-items-center justify-content-between">
                                                <div class="d-flex align-items-center">
                                                    <span
                                                        class="badge rounded-circle bg-{{ $plan->storage > 0 ? 'success' : 'danger' }} me-2"
                                                        style="width: 10px; height: 10px;"></span>
                                                    <strong>
                                                        {{ $plan->storage == '999' ? __('Unlimited') : ($plan->storage != 0 ? $plan->storage . 'MB' : '') }}
                                                        {{ __('storage limit') }}
                                                    </strong>
                                                    <span
                                                        class="badge badge-sm bg-green text-white text-uppercase ms-6">{{ __('New') }}</span>
                                                </div>
                                            </li>
                                            <li><span
                                                    class="badge bg-{{ __($plan->advanced_settings == 1 ? 'success' : 'danger') }} text-{{ $plan->advanced_settings == 1 ? 'green' : 'red' }} me-1"></span>
                                                <strong>{{ __('Advanced Settings') }}</strong>
                                            </li>
                                            <li><span
                                                    class="badge bg-{{ __($plan->pwa == 1 ? 'success' : 'danger') }} text-{{ $plan->pwa == 1 ? 'green' : 'red' }} me-1"></span>
                                                <strong>{{ __('Progressive Web App (PWA)') }}</strong>
                                            </li>
                                            <li><span
                                                    class="badge bg-{{ __($plan->personalized_link == 1 ? 'success' : 'danger') }} text-{{ $plan->personalized_link == 1 ? 'green' : 'red' }} me-1"></span>
                                                <strong>{{ __('Personalized Link') }}</strong>
                                            </li>
                                            <li><span
                                                    class="badge bg-{{ __($plan->hide_branding == 1 ? 'success' : 'danger') }} text-{{ $plan->hide_branding == 1 ? 'green' : 'red' }} me-1"></span>
                                                <strong>{{ __('Hide Branding') }}</strong>
                                            </li>
                                            <li><span
                                                    class="badge bg-{{ __($plan->free_setup == 1 ? 'success' : 'danger') }} text-{{ $plan->free_setup == 1 ? 'green' : 'red' }} me-1"></span>
                                                <strong>{{ __('Free Setup') }}</strong>
                                            </li>
                                            <li><span
                                                    class="badge bg-{{ __($plan->free_support == 1 ? 'success' : 'danger') }} text-{{ $plan->free_support == 1 ? 'green' : 'red' }} me-1"></span>
                                                <strong>{{ __('Free Support') }}</strong>
                                            </li>
                                        </ul>
                                        <div class="text-center mt-4">
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
                                                <a class="btn btn-outline-dark w-100"
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

        {{-- Footer --}}
        @include('user.includes.footer')
    </div>

    {{-- Current Plan Modal --}}
    @if (isset($active_plan))
        @include('user.pages.plans.includes.current-plan-modal')
    @endif

    {{-- Plan Modal --}}
    <div class="modal modal-blur fade" id="planModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-md modal-dialog-centered" role="document">
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
    <div class="modal modal-blur fade" id="downPlanModel" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-md modal-dialog-centered" role="document">
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
    <div class="modal modal-blur fade" id="subscriptionCancelModel" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-md modal-dialog-centered" role="document">
            <div class="modal-content">
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
                                <a class="btn btn-danger w-100" id="subscription_id">
                                    {{ __('Yes, proceed') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    @section('scripts')
        <script type="text/javascript">
            "use strict";

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
@endsection
