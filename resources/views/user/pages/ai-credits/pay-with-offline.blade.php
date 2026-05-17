@extends('user.layouts.index', [
    'header' => false,
    'nav' => false,
    'demo' => true,
    'settings' => $settings,
])

@section('content')
    <div class="page-wrapper">
        <!-- Page Header -->
        <div class="page-header d-print-none border-bottom pb-3">
            <div class="container d-flex align-items-center justify-content-between gap-2">
                <div class="d-flex align-item-center gap-2">
                    <a href="{{ url()->previous() }}" class="border rounded-3 p-2 text-white">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="icon icon-tabler icon-tabler-arrow-left icon-primary">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                            <path d="M5 12l14 0" />
                            <path d="M5 12l6 6" />
                            <path d="M5 12l6 -6" />
                        </svg>
                    </a>

                    <h2 class="page-title">
                        {{ __('AI Credits Bank Transfer') }}
                    </h2>
                </div>

                <div class="navbar-brand navbar-brand-autodark">
                    <a href="{{ route('user.dashboard') }}">
                        @if (file_exists(public_path('img/logo-light.png')))
                            <img src="{{ optional(Auth::user())->choosed_theme == 'light' ? asset($settings->site_logo) : asset('img/logo-light.png') }}"
                                width="200" height="50" alt="{{ $settings->site_name }}"
                                class="navbar-brand-image custom-logo">
                        @else
                            <img src="{{ $settings->site_logo }}" width="200" height="50"
                                alt="{{ $settings->site_name }}" class="navbar-brand-image custom-logo">
                        @endif
                    </a>
                </div>
            </div>
        </div>

        <div class="page-body">
            <div class="container">

                {{-- Failed --}}
                @if (Session::has('failed'))
                    <div class="alert alert-important alert-danger alert-dismissible mb-3" role="alert">
                        <div class="d-flex">
                            <div>{{ Session::get('failed') }}</div>
                        </div>
                        <a class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="close"></a>
                    </div>
                @endif

                {{-- Success --}}
                @if (Session::has('success'))
                    <div class="alert alert-important alert-success alert-dismissible mb-3" role="alert">
                        <div class="d-flex">
                            <div>{{ Session::get('success') }}</div>
                        </div>
                        <a class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="close"></a>
                    </div>
                @endif

                <div class="row justify-content-center">
                    <!-- Payment Form -->
                    <div class="col-lg-6 col-md-12">
                        <div class="card shadow-sm border-0">
                            <div class="card-body p-4">
                                <div class="text-center mb-4">
                                    <div class="avatar avatar-xl bg-primary-lt text-primary rounded-circle mx-auto mb-3">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round">
                                            <path
                                                d="M12 3l1.912 5.813h6.088l-4.912 3.563l1.912 5.812l-4.912-3.562l-4.912 3.562l1.912-5.812l-4.912-3.563h6.088z" />
                                        </svg>
                                    </div>

                                    <h2 class="fw-bold mb-1">
                                        {{ $plan_details->plan_name }}
                                    </h2>

                                    <p class="text-secondary mb-0">
                                        {{ __('Offline AI Credits Purchase') }}
                                    </p>
                                </div>

                                <div class="border rounded-3 p-3 mb-4 bg-light">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>{{ __('Credits') }}</span>
                                        <strong>
                                            {{ $plan_details->no_of_ai_credits }}
                                        </strong>
                                    </div>

                                    <div class="d-flex justify-content-between">
                                        <span>{{ __('Payment Method') }}</span>
                                        <strong>{{ __('Offline / Bank Transfer') }}</strong>
                                    </div>
                                </div>

                                <form action="{{ route('ai.credits.mark.payment.payment') }}" method="post">
                                    @csrf
                                    <input type="hidden" value="{{ $plan_details->ai_credits_plan_id }}" name="plan_id">
                                    <input type="hidden" value="{{ $couponId }}" name="coupon_id">

                                    <div class="mb-4">
                                        <label class="form-label required">
                                            {{ __('Transaction / Payment Details') }}
                                        </label>
                                        <textarea class="form-control" name="transaction_id" rows="5"
                                            placeholder="{{ __('Enter transaction ID, payment reference, sender name, date, or transfer details') }}" required></textarea>
                                    </div>

                                    <button type="submit" class="btn btn-primary w-100 py-3">
                                        {{ __('Submit Payment Details') }}
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Bank Details -->
                    <div class="col-lg-5 col-md-12">
                        <div class="card shadow-sm border-0">
                            <div class="card-body p-4">
                                <h3 class="card-title mb-4">
                                    {{ __('Bank Details') }}
                                </h3>
                                <div class="bg-light rounded-3 p-3">
                                    <pre class="mb-0 text-wrap" style="white-space: pre-wrap;">{!! $config[31]->config_value !!}</pre>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Footer --}}
                @include('user.includes.footer')
            </div>
        </div>
    </div>
@endsection
