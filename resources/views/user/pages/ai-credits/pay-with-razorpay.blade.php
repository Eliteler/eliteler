@extends('user.layouts.index', [
    'header' => false,
    'nav' => false,
    'demo' => true,
    'settings' => $settings,
])

@section('content')
    <div class="page-wrapper">
        <!-- Page title -->
        <div class="page-header d-print-none border-bottom pb-3">
            <div class="container d-flex align-items-center justify-content-between gap-2">
                <div class="d-flex align-item-center gap-2">
                    <a href="{{ url()->previous() }}" class="border rounded-3 p-2 text-white">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="icon icon-tabler icons-tabler-outline icon-tabler-arrow-left icon-primary">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                            <path d="M5 12l14 0" />
                            <path d="M5 12l6 6" />
                            <path d="M5 12l6 -6" />
                        </svg>
                    </a>

                    <h2 class="page-title">
                        {{ __('AI Credits Checkout') }}
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
                    <div class="col-lg-6 col-md-8 col-12">
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
                                        {{ $planDetails->plan_name }}
                                    </h2>

                                    <p class="text-secondary mb-0">
                                        {{ __('AI Credits Purchase') }}
                                    </p>
                                </div>

                                <div class="border rounded-3 p-3 mb-4 bg-light">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>{{ __('Credits') }}</span>
                                        <strong>{{ $planDetails->no_of_ai_credits }}</strong>
                                    </div>

                                    <div class="d-flex justify-content-between mb-2">
                                        <span>{{ __('Price') }}</span>
                                        <strong>
                                            {{ $config[1]->config_value }}
                                            {{ formatCurrency($amountToBePaid) }}
                                        </strong>
                                    </div>

                                    <div class="d-flex justify-content-between">
                                        <span>{{ __('Payment Method') }}</span>
                                        <strong>{{ __('Razorpay') }}</strong>
                                    </div>
                                </div>

                                <button id="rzp-button1" class="btn btn-primary w-100 py-3">
                                    {{ __('Pay Now') }}
                                </button>

                            </div>
                        </div>
                    </div>
                </div>

                {{-- Footer --}}
                @include('user.includes.footer')

            </div>
        </div>
    </div>

    @push('custom-js')
        <script type="text/javascript" src="{{ asset('js/razorpay-checkout.js') }}"></script>

        <script>
            "use strict";

            var options = {
                "key": "{{ $config[6]->config_value }}",
                "amount": "{{ $order->amount }}",
                "currency": "{{ $order->currency }}",
                "name": "{{ env('APP_NAME') }}",
                "description": "AI Credits Purchase",
                "image": "{{ asset($settings->favicon) }}",
                "order_id": "{{ $order->id }}",

                "handler": function(response) {
                    window.location =
                        "../../razorpay-payment-status/" +
                        response.razorpay_order_id +
                        "/" +
                        response.razorpay_payment_id;
                },

                "prefill": {
                    "name": "{{ Auth::user()->name }}",
                    "email": "{{ Auth::user()->email }}",
                    "contact": ""
                },

                "notes": {
                    "ai_credits_transaction_id": "{{ $aiCreditsTransactionId }}"
                },

                "theme": {
                    "color": "#613BBB"
                }
            };

            var rzp1 = new Razorpay(options);

            rzp1.on('payment.failed', function(response) {

                window.location =
                    "../../razorpay-payment-status/" +
                    response.error.metadata.order_id +
                    "/" +
                    response.error.metadata.payment_id;
            });

            document.getElementById('rzp-button1').onclick = function(e) {
                rzp1.open();
                e.preventDefault();
            };
        </script>
    @endpush
@endsection
