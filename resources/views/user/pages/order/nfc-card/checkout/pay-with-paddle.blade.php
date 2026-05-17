@extends('user.layouts.index', ['header' => false, 'nav' => false, 'demo' => true, 'settings' => $settings])

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

        <div class="page-body">
            <div class="container">

                {{-- Failed --}}
                @if (Session::has('failed'))
                    <div class="alert alert-important alert-danger alert-dismissible mb-2" role="alert">
                        <div class="d-flex">
                            <div>{{ Session::get('failed') }}</div>
                        </div>
                        <a class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="close"></a>
                    </div>
                @endif

                {{-- Success --}}
                @if (Session::has('success'))
                    <div class="alert alert-important alert-success alert-dismissible mb-2" role="alert">
                        <div class="d-flex">
                            <div>{{ Session::get('success') }}</div>
                        </div>
                        <a class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="close"></a>
                    </div>
                @endif

                {{-- Paddle inline error alert --}}
                <div id="paddle-error-alert" class="alert alert-danger alert-dismissible mb-2 d-none" role="alert">
                    <div class="d-flex align-items-start gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" class="mt-1 flex-shrink-0">
                            <circle cx="12" cy="12" r="10" />
                            <line x1="12" y1="8" x2="12" y2="12" />
                            <line x1="12" y1="16" x2="12.01" y2="16" />
                        </svg>
                        <div>
                            <strong>{{ __('Payment Error') }}</strong>
                            <div id="paddle-error-message" class="mt-1 small"></div>
                        </div>
                    </div>
                    <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
                </div>

                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h3 class="card-title">
                                {{ __('Choosed NFC Card') }} : {{ __($nfcDetails->nfc_card_name) }}
                            </h3>

                            @if (empty($paddleTransactionIdForCheckout))
                                <div class="alert alert-danger mt-2">
                                    {{ __('Unable to generate checkout. Please go back and try again.') }}
                                </div>
                            @else
                                <button type="button" class="btn btn-primary" id="paddle-pay-btn">
                                    <span id="btn-text">{{ __('Pay Now') }}</span>
                                    <span id="btn-spinner" class="spinner-border spinner-border-sm ms-2 d-none"
                                        role="status" aria-hidden="true"></span>
                                </button>
                            @endif

                        </div>
                    </div>
                </div>

                @include('user.includes.footer')
            </div>
        </div>
    </div>
@endsection

@push('custom-js')
    {{-- Paddle Billing JS v2 --}}
    <script src="https://cdn.paddle.com/paddle/v2/paddle.js"></script>
    <script>
        (function() {

            var paddleClientToken = "{{ $config[67]->config_value }}"; // Client-side token (live_xxx / test_xxx)
            var isSandbox = "{{ $config[64]->config_value }}" === "true";
            var paddleTransactionId = "{{ $paddleTransactionIdForCheckout ?? '' }}";

            if (!paddleTransactionId) return;

            // Warn if config[67] is not a valid Paddle Billing client-side token
            if (!paddleClientToken.startsWith('live_') && !paddleClientToken.startsWith('test_')) {
                console.error(
                    '[Paddle] ❌ config[67] does not look like a Paddle Billing client-side token.\n' +
                    'Go to Paddle Dashboard → Developer Tools → Authentication and copy the Client-side token.'
                );
                showError('{{ __('Payment system configuration error. Please contact the site administrator.') }}');
                return;
            }

            // Initialise Paddle Billing
            try {
                if (isSandbox) {
                    Paddle.Environment.set("sandbox");
                }

                Paddle.Initialize({
                    token: paddleClientToken,
                    eventCallback: function(event) {
                        console.log('[Paddle Event]', event.name, event);

                        switch (event.name) {

                            case 'checkout.loaded':
                                hideBtnSpinner();
                                break;

                                // Payment completed — manually redirect to status route
                                // Paddle Billing JS overlay does NOT auto-redirect
                            case 'checkout.completed':
                                hideBtnSpinner();
                                var ptxn = (event.data && event.data.transaction && event.data.transaction
                                        .id) ?
                                    event.data.transaction.id :
                                    paddleTransactionId;
                                window.location.href = "{{ route('nfc.paddle.payment.status') }}" +
                                    "?_ptxn=" + ptxn;
                                break;

                            case 'checkout.closed':
                                hideBtnSpinner();
                                break;

                            case 'checkout.error':
                                hideBtnSpinner();
                                var msg = (event.data && event.data.message) ?
                                    event.data.message :
                                    '{{ __('An error occurred with the payment provider. Please try again or contact support.') }}';
                                showError(msg);
                                console.error('[Paddle checkout.error]', event.data);
                                break;

                            case 'checkout.payment.failed':
                                hideBtnSpinner();
                                showError(
                                    '{{ __('Payment was declined. Please check your card details and try again.') }}'
                                    );
                                break;
                        }
                    }
                });

            } catch (initError) {
                console.error('[Paddle] Paddle.Initialize failed:', initError);
                showError('{{ __('Payment system failed to initialise. Please refresh the page and try again.') }}');
                return;
            }

            // Open checkout on button click
            var btn = document.getElementById('paddle-pay-btn');
            if (btn) {
                btn.addEventListener('click', function() {
                    showBtnSpinner();
                    hideError();

                    try {
                        Paddle.Checkout.open({
                            transactionId: paddleTransactionId
                        });
                    } catch (openError) {
                        hideBtnSpinner();
                        console.error('[Paddle] Paddle.Checkout.open failed:', openError);
                        showError(
                            '{{ __('Could not open the checkout. Please refresh the page and try again.') }}'
                            );
                    }
                });
            }

            // Helpers
            function showBtnSpinner() {
                var spinner = document.getElementById('btn-spinner');
                var text = document.getElementById('btn-text');
                if (spinner) spinner.classList.remove('d-none');
                if (text) text.textContent = '{{ __('Processing...') }}';
                if (btn) btn.disabled = true;
            }

            function hideBtnSpinner() {
                var spinner = document.getElementById('btn-spinner');
                var text = document.getElementById('btn-text');
                if (spinner) spinner.classList.add('d-none');
                if (text) text.textContent = '{{ __('Pay Now') }}';
                if (btn) btn.disabled = false;
            }

            function showError(message) {
                var alertEl = document.getElementById('paddle-error-alert');
                var msgEl = document.getElementById('paddle-error-message');
                if (msgEl) msgEl.textContent = message;
                if (alertEl) {
                    alertEl.classList.remove('d-none');
                    alertEl.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });
                }
            }

            function hideError() {
                var alertEl = document.getElementById('paddle-error-alert');
                if (alertEl) alertEl.classList.add('d-none');
            }

        })();
    </script>
@endpush
