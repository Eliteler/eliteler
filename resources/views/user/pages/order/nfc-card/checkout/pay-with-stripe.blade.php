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

                <div class="col-lg-6 col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <h3 class="card-title">
                                {{ __('Choosed NFC Card') }}: {{ $nfcDetails->nfc_card_name }}
                            </h3>
                
                            <div class="col-12">
                                <form action="{{ route('nfc.stripe.payment.status', $paymentId) }}" method="post" id="payment-form">
                                    @csrf
                                    <div id="payment-element">
                                        <!-- Stripe Payment Element will be inserted here -->
                                    </div>
                                    <div id="card-errors" class="text-danger mt-3" role="alert"></div>
                                    <div class="mt-3">
                                        <button id="submit-button" class="btn btn-dark" type="submit">
                                            {{ __('Pay Now') }}
                                        </button>
                                    </div>
                                </form>
                            </div>
                
                            <br>
                
                            <a class="mt-2 text-muted text-underline" href="{{ route('nfc.stripe.payment.cancel', $paymentId) }}">
                                {{ __('Cancel payment and back to home') }}
                            </a>
                        </div>
                    </div>
                </div>                
                @include('user.includes.footer')
            </div>
        </div>
    </div>
    
    @push('custom-js')
        <script src="https://js.stripe.com/v3/"></script>
        <script>
            (async function() {
                "use strict";
        
                const stripe = Stripe('{{ $config[9]->config_value }}'); // Replace with your Stripe publishable key
                const clientSecret = '{{ $intent }}'; // Replace with the PaymentIntent client secret from your server
        
                const elements = stripe.elements({
                    clientSecret,
                    appearance: {
                        theme: 'stripe',
                    },
                });
        
                // Create the Payment Element
                const paymentElement = elements.create('payment');
                paymentElement.mount('#payment-element');
        
                // Form submission handling
                const form = document.getElementById('payment-form');
                const submitButton = document.getElementById('submit-button');
                const errorContainer = document.getElementById('card-errors');
        
                form.addEventListener('submit', async (event) => {
                    event.preventDefault();
        
                    // Disable the submit button to prevent multiple submissions
                    submitButton.disabled = true;
        
                    const { error } = await stripe.confirmPayment({
                        elements,
                        confirmParams: {
                            return_url: '{{ route('nfc.stripe.payment.status', $paymentId) }}', // Optional: Redirect URL on success
                        },
                    });
        
                    if (error) {
                        // Show error to the customer
                        errorContainer.textContent = error.message;
                        submitButton.disabled = false;
                    }
                });
            })();
        </script>        
    @endpush
@endsection
