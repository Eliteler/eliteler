@extends('user.layouts.index', ['header' => true, 'nav' => true, 'demo' => true])

{{-- Custom CSS (Kept dry for structural alignment overrides) --}}
@section('css')
    <style>
        .badge {
            padding: 0.25em 0.25em !important;
            margin-top: 0.5em !important;
            margin-right: 0.5em !important;
        }
    </style>
@endsection

@section('content')
    <div class="page-wrapper">
        <!-- Page Header (Wraps the inner container-fluid) -->
        <div class="page-header d-print-none">
            <div class="container-fluid">
                <div class="row g-2 align-items-center">
                    <!-- Hierarchical Breadcrumb Trail -->
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="{{ route('user.dashboard') }}" class="text-secondary text-decoration-none">
                                    {{ __('Dashboard') }}
                                </a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">
                                {{ __('AI Credit') }}
                            </li>
                        </ol>
                    </nav>
                    <div class="col">
                        <h2 class="page-title">
                            {{ __('AI Credit Plans') }}
                        </h2>

                        <!-- Descriptive Help Subtitle -->
                        <div class="text-muted mt-1">
                            {{ __('Acquire dynamic credits to unlock premium machine learning algorithms and real-time generation services.') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="page-body">
            <div class="container-fluid">
                {{-- Failed Alert --}}
                @if (Session::has('failed'))
                    <div class="alert alert-important alert-danger alert-dismissible mb-3 border-0 shadow-sm"
                        role="alert">
                        <div class="d-flex">
                            <div>{{ Session::get('failed') }}</div>
                        </div>
                        <a class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="close"></a>
                    </div>
                @endif

                {{-- Success Alert --}}
                @if (Session::has('success'))
                    <div class="alert alert-important alert-success alert-dismissible mb-3 border-0 shadow-sm"
                        role="alert">
                        <div class="d-flex">
                            <div>{{ Session::get('success') }}</div>
                        </div>
                        <a class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="close"></a>
                    </div>
                @endif

                <div class="row row-deck row-cards">
                    {{-- AI Credit Plans Directory Grid --}}
                    @if (count($aiCreditsPlans) > 0)
                        @foreach ($aiCreditsPlans as $plan)
                            <div class="col-sm-6 col-md-4 col-lg-3">
                                <!-- Upgraded to Borderless Shadowed Card -->
                                <div class="card border-0 shadow-sm">
                                    <div class="card-body">
                                        <h3 class="card-title mb-2">{{ __($plan->plan_name) }}</h3>
                                        <p class="card-text text-muted small mb-3">{{ __($plan->plan_description) }}</p>
                                        <h4 class="card-price mb-3 text-dark fw-bold fs-2">
                                            {{ formatCurrency($plan->plan_price) }}</h4>
                                        <ul class="list-unstyled lh-lg mb-0">
                                            <li class="d-flex align-items-center text-secondary small">
                                                <span
                                                    class="badge bg-green-600 text-green-50 p-1 me-2 rounded-circle d-inline-flex align-items-center justify-content-center"
                                                    style="width: 16px; height: 16px;">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12"
                                                        viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                        stroke-width="3" stroke-linecap="round" stroke-linejoin="round"
                                                        class="icon icon-tabler icon-tabler-check m-0">
                                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                        <path d="M5 12l5 5l10 -10" />
                                                    </svg>
                                                </span>
                                                <strong class="text-dark me-1">
                                                    {{ number_format($plan->no_of_ai_credits) }}
                                                </strong>
                                                /{{ __('AI Credit') }}
                                            </li>
                                        </ul>
                                    </div>
                                    <div class="card-footer bg-transparent border-0 pt-0">
                                        <a href="{{ route('user.ai.credits.checkout', $plan->ai_credits_plan_id) }}"
                                            class="btn btn-primary btn-md w-100 d-inline-flex align-items-center justify-content-center">
                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                class="icon icon-tabler icon-tabler-shopping-cart me-2" width="24"
                                                height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                                fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                <circle cx="6" cy="19" r="2" />
                                                <circle cx="17" cy="19" r="2" />
                                                <path d="M17 17h-11v-14h-2" />
                                                <path d="M6 5l14 1l-1 7h-13" />
                                            </svg>
                                            {{ __('Buy Now') }}
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="col-12">
                            <!-- Empty State standard card borderless with shadow template mapping -->
                            <div class="card border-0 shadow-sm">
                                <div class="card-body text-center py-6">
                                    <!-- Dynamic Avatar Placeholder -->
                                    <div class="mb-4">
                                        <span class="avatar avatar-xl rounded-circle bg-blue-lt text-blue mx-auto">
                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                class="icon icon-tabler icon-tabler-brand-openai" width="24"
                                                height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                                fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                <path
                                                    d="M18.358 13.06a4.3 4.3 0 0 0 -1.143 -5.748l-.055 -.033a4.264 4.264 0 0 0 -5.79 1.157a4.3 4.3 0 0 0 -4.384 -1.332l-.062 .016a4.264 4.264 0 0 0 -3.078 4.795a4.3 4.3 0 0 0 -3.242 4.417l.004 .064a4.264 4.264 0 0 0 4.636 3.639a4.3 4.3 0 0 0 1.142 5.747l.055 .034a4.264 4.264 0 0 0 5.79 -1.158a4.3 4.3 0 0 0 4.384 1.331l.063 -.015a4.264 4.264 0 0 0 3.078 -4.796a4.3 4.3 0 0 0 3.241 -4.416l-.004 -.064a4.264 4.264 0 0 0 -4.636 -3.64" />
                                            </svg>
                                        </span>
                                    </div>

                                    <!-- Soft Indicators Badge mapping standard -->
                                    <div class="mb-3">
                                        <span class="badge bg-slate-100 text-slate-600 px-3 py-1">
                                            <span class="badge-indicator badge-indicator-animated bg-green me-2"></span>
                                            {{ __('AI Credit') }}
                                        </span>
                                    </div>

                                    <h2 class="h2 mb-2 text-dark">{{ __('No Plans Available') }}</h2>

                                    <p class="text-secondary mb-4 mx-auto" style="max-width: 460px;">
                                        {{ __('We are currently working on developing updated credit tiers for you. Check back soon for premium offerings.') }}
                                    </p>

                                    <!-- Action options layout map -->
                                    <div class="d-flex gap-2 justify-content-center">
                                        <a href="{{ route('user.ai.credits.plans') }}"
                                            class="btn btn-primary d-inline-flex align-items-center">
                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                class="icon icon-tabler icon-tabler-refresh me-2" width="24"
                                                height="24" viewBox="0 0 24 24" stroke-width="2"
                                                stroke="currentColor" fill="none" stroke-linecap="round"
                                                stroke-linejoin="round">
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                <path d="M20 11a8.1 8.1 0 0 0 -15.5 -2m-.5 -5v5h5" />
                                                <path d="M4 13a8.1 8.1 0 0 0 15.5 2m.5 5v-5h-5" />
                                            </svg>
                                            {{ __('Refresh Plans') }}
                                        </a>
                                        <a href="{{ route('contact') }}"
                                            class="btn btn-outline-secondary d-inline-flex align-items-center text-decoration-none">
                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                class="icon icon-tabler icon-tabler-headset me-2" width="24"
                                                height="24" viewBox="0 0 24 24" stroke-width="2"
                                                stroke="currentColor" fill="none" stroke-linecap="round"
                                                stroke-linejoin="round">
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                <rect x="4" y="13" rx="2" width="4" height="6" />
                                                <rect x="16" y="13" rx="2" width="4" height="6" />
                                                <path d="M4 15v-3a8 8 0 0 1 16 0v3" />
                                                <path d="M18 19a6 3 0 0 1 -6 3" />
                                            </svg>
                                            {{ __('Contact Support') }}
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Footer -->
        @include('user.includes.footer')
    </div>
@endsection
