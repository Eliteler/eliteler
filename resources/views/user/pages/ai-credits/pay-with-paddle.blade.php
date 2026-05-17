@extends('user.layouts.index', [
    'header' => false,
    'nav' => false,
    'demo' => true,
    'settings' => $settings,
])

@section('content')
    <div class="page-wrapper">

        <!-- Header -->
        <div class="page-header d-print-none border-bottom pb-3">
            <div class="container d-flex align-items-center justify-content-between gap-2">

                <div class="d-flex align-items-center gap-2">
                    <a href="{{ url()->previous() }}" class="border rounded-3 p-2 text-white">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="icon">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                            <path d="M5 12l14 0" />
                            <path d="M5 12l6 6" />
                            <path d="M5 12l6 -6" />
                        </svg>
                    </a>

                    <h2 class="page-title">
                        {{ __('Checkout') }}
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

        <!-- Body -->
        <div class="page-body">
            <div class="container">

                {{-- Failed Alert --}}
                @if (Session::has('failed'))
                    <div class="alert alert-danger alert-dismissible mb-3">
                        {{ Session::get('failed') }}

                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                {{-- Success Alert --}}
                @if (Session::has('success'))
                    <div class="alert alert-success alert-dismissible mb-3">
                        {{ Session::get('success') }}

                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                {{-- Paddle Error --}}
                <div id="paddle-error-alert" class="alert alert-danger alert-dismissible d-none mb-3">

                    <strong>{{ __('Payment Error') }}</strong>

                    <div id="paddle-error-message"></div>

                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>

                <div class="row justify-content-center">

                    <div class="col-lg-6">

                        <div class="card">

                            <div class="card-body">

                                <h3 class="card-title mb-3">
                                    {{ __('Choosed Plan') }}
                                    :
                                    {{ __($plan_details->plan_name) }}
                                </h3>

                                <div class="mb-2">
                                    <strong>{{ __('Price') }}:</strong>
                                    {{ $config[1]->config_value }}
                                    {{ number_format($plan_details->plan_price, 2) }}
                                </div>

                                @if (empty($paddleTransactionIdForCheckout))
                                    <div class="alert alert-danger mt-3">
                                        {{ __('Unable to generate checkout. Please try again.') }}
                                    </div>
                                @else
                                    <button type="button" class="btn btn-primary w-100" id="customCheckout">

                                        <span id="btn-text">
                                            {{ __('Buy Now!') }}
                                        </span>

                                        <span id="btn-spinner" class="spinner-border spinner-border-sm ms-2 d-none">
                                        </span>

                                    </button>
                                @endif

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

@push('custom-js')
    <script src="https://cdn.paddle.com/paddle/v2/paddle.js"></script>

    <script>
        (function() {

            // Paddle Config
            var paddleClientToken =
                "{{ $config[67]->config_value }}";

            var isSandbox =
                "{{ $config[64]->config_value }}" === "true";

            var paddleTransactionId =
                "{{ $paddleTransactionIdForCheckout ?? '' }}";

            // Validation
            if (!paddleTransactionId) {

                showError(
                    '{{ __('Checkout could not be initialised.') }}'
                );

                return;
            }

            // Init Paddle
            try {

                if (isSandbox) {
                    Paddle.Environment.set("sandbox");
                }

                Paddle.Initialize({

                    token: paddleClientToken,

                    eventCallback: function(event) {

                        console.log(event);

                        switch (event.name) {

                            case 'checkout.loaded':
                                hideBtnSpinner();
                                break;

                            case 'checkout.completed':

                                hideBtnSpinner();

                                var txnId =
                                    (event.data &&
                                        event.data.transaction &&
                                        event.data.transaction.id) ?
                                    event.data.transaction.id :
                                    paddleTransactionId;

                                window.location.href =
                                    "{{ route('ai.credits.paddle.payment.status') }}" +
                                    "?_ptxn=" + txnId;

                                break;

                            case 'checkout.closed':
                                hideBtnSpinner();
                                break;

                            case 'checkout.error':

                                hideBtnSpinner();

                                var msg =
                                    (event.data && event.data.message) ?
                                    event.data.message :
                                    '{{ __('Payment provider error.') }}';

                                showError(msg);

                                break;

                            case 'checkout.payment.failed':

                                hideBtnSpinner();

                                showError(
                                    '{{ __('Payment failed. Please try again.') }}'
                                );

                                break;
                        }
                    }
                });

            } catch (e) {

                console.error(e);

                showError(
                    '{{ __('Failed to initialise Paddle.') }}'
                );

                return;
            }

            // Checkout Button
            var btn =
                document.getElementById('customCheckout');

            if (btn) {

                btn.addEventListener('click', function() {

                    showBtnSpinner();

                    hideError();

                    try {

                        Paddle.Checkout.open({
                            transactionId: paddleTransactionId
                        });

                    } catch (e) {

                        hideBtnSpinner();

                        console.error(e);

                        showError(
                            '{{ __('Could not open checkout.') }}'
                        );
                    }
                });
            }

            // Helpers
            function showBtnSpinner() {

                var spinner =
                    document.getElementById('btn-spinner');

                var text =
                    document.getElementById('btn-text');

                if (spinner) {
                    spinner.classList.remove('d-none');
                }

                if (text) {
                    text.textContent =
                        '{{ __('Processing...') }}';
                }

                if (btn) {
                    btn.disabled = true;
                }
            }

            function hideBtnSpinner() {
                var spinner =
                    document.getElementById('btn-spinner');

                var text =
                    document.getElementById('btn-text');

                if (spinner) {
                    spinner.classList.add('d-none');
                }

                if (text) {
                    text.textContent =
                        '{{ __('Buy Now!') }}';
                }

                if (btn) {
                    btn.disabled = false;
                }
            }

            function showError(message) {
                var alertEl =
                    document.getElementById('paddle-error-alert');

                var msgEl =
                    document.getElementById('paddle-error-message');

                if (msgEl) {
                    msgEl.textContent = message;
                }

                if (alertEl) {
                    alertEl.classList.remove('d-none');
                }
            }

            function hideError() {
                var alertEl =
                    document.getElementById('paddle-error-alert');

                if (alertEl) {
                    alertEl.classList.add('d-none');
                }
            }
        })();
    </script>
@endpush
