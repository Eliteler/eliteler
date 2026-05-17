@extends('user.layouts.index', ['header' => true, 'nav' => true, 'demo' => true])

{{-- Custom CSS --}}
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
        <!-- Page title -->
        <div class="page-header d-print-none">
            <div class="container-fluid">
                <div class="row g-2 align-items-center">
                    <div class="col">
                        <div class="page-pretitle">
                            {{ __('Overview') }}
                        </div>
                        <h2 class="page-title">
                            {{ __('AI Credits Plans') }}
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

                <div class="row row-deck row-cards">
                    {{-- Aicredits plans --}}
                    @if (count($aiCreditsPlans) > 0)
                        @foreach ($aiCreditsPlans as $plan)
                            <div class="col-sm-3 col-lg-3">
                                <div class="card">
                                    <div class="card-body">
                                        <h3 class="card-title mb-3">{{ __($plan->plan_name) }}</h3>
                                        <p class="card-text text-muted mb-3">{{ __($plan->plan_description) }}</p>
                                        <h4 class="card-price mb-3">{{ formatCurrency($plan->plan_price) }}</h4>
                                        <ul class="list-unstyled lh-lg mb-3">
                                            <li class="d-flex align-items-center">
                                                <span class="badge bg-success me-1"></span>
                                                <strong>
                                                    {{ $plan->no_of_ai_credits }}
                                                    {{ __('AI Credits') }}
                                                </strong>
                                            </li>
                                        </ul>
                                    </div>
                                    <div class="card-footer">
                                        <a href="{{ route('user.ai.credits.checkout', $plan->ai_credits_plan_id) }}"
                                            class="btn btn-primary">
                                            {{ __('Buy Now') }}
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="card card-md shadow-sm">
                            <div class="card-body text-center py-5">

                                <!-- Icon -->
                                <div class="mb-4">
                                    <span class="avatar avatar-xl rounded-circle bg-blue-lt text-blue">
                                        <i class="ti ti-brand-openai fs-1"></i>
                                    </span>
                                </div>

                                <!-- Badge -->
                                <div class="mb-3">
                                    <span class="badge bg-blue-lt text-blue">
                                        <span class="badge-indicator badge-indicator-animated bg-green me-1"></span>
                                        {{ __('AI Credits') }}
                                    </span>
                                </div>

                                <!-- Title -->
                                <h2 class="h2 mb-2">{{ __('No Plans Available') }}</h2>

                                <!-- Description -->
                                <p class="text-muted mb-4">
                                    {{ __("We're working on new AI credit plans for you.") }}<br>
                                    {{ __('Check back soon - great options are on their way.') }}
                                </p>

                                <!-- Actions -->
                                <div class="d-flex gap-2 justify-content-center">
                                    <a href="{{ route('user.ai.credits.plans') }}" class="btn btn-primary">
                                        <i class="ti ti-refresh me-1"></i>
                                        {{ __('Refresh Plans') }}
                                    </a>
                                    <a href="{{ route('contact') }}" class="btn btn-outline-info">
                                        <i class="ti ti-headset me-1"></i>
                                        {{ __('Contact Support') }}
                                    </a>
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
