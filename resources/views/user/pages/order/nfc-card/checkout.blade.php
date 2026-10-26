@extends('user.layouts.index', ['header' => false, 'nav' => false, 'demo' => true])

{{-- Custom CSS --}}
@section('css')
    <link rel="stylesheet" href="{{ asset('plugins/intlTelInput/intlTelInput.css') }}" />
    <script src="{{ asset('plugins/intlTelInput/intlTelInput.min.js') }}"></script>
    <style>
        .iti {
            width: 100%;
        }

        .iti--fullscreen-popup.iti--container {
            top: 50px !important;
        }

        .iti__search-input {
            width: calc(100% - 20px) !important;
            margin: 10px !important;
            padding: 8px 12px !important;
            border-radius: 8px;
            border: 1px solid #ddd;
        }

        .iti__country-list {
            width: 100%;
            max-width: 100%;
            overflow-x: hidden;
        }
    </style>
@endsection

@php
    use App\Services\GoBizCommonService;

    // Load config once
    $config = GoBizCommonService::config();

    // Fetch default country code from admin config if profile value is missing
    $admin_country = \App\Country::where('country_name', $config[23]->config_value ?? '')->first();
    $default_code = $admin_country ? ltrim($admin_country->phone_code, '+') : '1';

    $phone_code = Auth::user()->country_code ?: $default_code;
    $whatsapp_code = Auth::user()->whatsapp_country_code ?: $default_code;
@endphp

@section('content')
    <div class="page-wrapper">
        <!-- Page title -->
        <div class="page-header d-print-none border-bottom pb-3">
            <div class="container d-flex align-items-center justify-content-between gap-2">
                <div class="d-flex align-item-center gap-2">
                    <a href="{{ url()->previous() }}" class="border rounded-3 p-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round"
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
                                width="200" height="50" alt="{{ config('app.name') }}"
                                class="navbar-brand-image custom-logo">
                        @else
                            <img src="{{ $settings->site_logo }}" width="200" height="50"
                                alt="{{ config('app.name') }}" class="navbar-brand-image custom-logo">
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

                <div class="row row-cards">

                    {{-- Order Details --}}
                    <div class="col-lg-4 mb-3">
                        <div class="card">
                            <div class="card-body">

                                @php
                                    $price = $nfcCardDetails->nfc_card_price;
                                    $taxPercentage = (float) $config[25]->config_value;
                                    $taxAmount = ($price * $taxPercentage) / 100;
                                    $grandTotal = $price + $taxAmount;
                                @endphp

                                <h3 class="card-title">{{ __('Order Details') }}</h3>

                                <div class="card-table table-responsive">
                                    <table class="table table-vcenter border">
                                        <thead>
                                            <tr>
                                                <th>{{ __('Description') }}</th>
                                                <th class="text-end">{{ __('Amount') }}</th>
                                            </tr>
                                        </thead>

                                        <tbody>

                                            {{-- NFC Card Name --}}
                                            <tr>
                                                <td>{{ __('NFC Card Name') }}</td>
                                                <td class="fw-bold text-end">
                                                    {{ $nfcCardDetails->nfc_card_name }}
                                                </td>
                                            </tr>

                                            {{-- Unit Price --}}
                                            <tr>
                                                <td>{{ __('Unit Price') }}</td>
                                                <td class="fw-bold text-end">
                                                    {{ formatCurrency($price) }}
                                                </td>
                                            </tr>

                                            {{-- Quantity --}}
                                            <tr>
                                                <td>{{ __('Quantity') }}</td>
                                                <td class="text-end">
                                                    <input type="number" id="quantity" name="quantity"
                                                        class="form-control text-end" min="1" value="1">
                                                </td>
                                            </tr>

                                            {{-- Subtotal --}}
                                            <tr>
                                                <td>{{ __('Subtotal') }}</td>
                                                <td class="fw-bold text-end">
                                                    <span id="subtotal">
                                                        {{ formatCurrency($price) }}
                                                    </span>
                                                </td>
                                            </tr>

                                            {{-- Tax --}}
                                            @if ($taxPercentage > 0)
                                                <tr>
                                                    <td>
                                                        {{ __($config[24]->config_value) }}
                                                        ({{ $taxPercentage }}%)
                                                    </td>

                                                    <td class="fw-bold text-end">
                                                        <span id="taxAmount">
                                                            {{ formatCurrency($taxAmount) }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            @endif

                                            {{-- Applied Coupon --}}
                                            <tr class="d-none" id="appliedCoupon"></tr>

                                            {{-- Total --}}
                                            <tr>
                                                <td>
                                                    <strong>{{ __('Total') }}</strong>
                                                </td>

                                                <td class="fw-bold text-end">
                                                    <span id="total">
                                                        {{ formatCurrency($grandTotal) }}
                                                    </span>
                                                </td>
                                            </tr>

                                        </tbody>
                                    </table>
                                </div>

                                {{-- Coupon Code --}}
                                <form id="couponForm" class="mt-3">
                                    <div class="input-group">

                                        <input type="text" class="form-control" name="coupon_code" id="coupon_code"
                                            placeholder="{{ __('Coupon Code') }}"
                                            value="{{ old('coupon_code') ?? $coupon_code }}">

                                        <button type="submit" class="btn btn-primary" id="applyCoupon">
                                            {{ __('Apply') }}
                                        </button>

                                    </div>

                                    <p class="fw-bold mt-2 mb-0" id="discountMessage"></p>
                                </form>

                            </div>
                        </div>
                    </div>

                    {{-- Shipping Details --}}
                    <div class="col-lg-8">

                        <form
                            action="{{ route('user.order.nfc.card.place.order', ['id' => $nfcCardDetails->nfc_card_id]) }}"
                            method="POST">

                            @csrf

                            {{-- Hidden Fields --}}
                            <input type="hidden" name="quantity" id="final_quantity" value="1">

                            <input type="hidden" name="applied_coupon" id="applied_coupon">

                            <input type="hidden" name="payment_gateway_amount" id="payment_gateway_amount"
                                value="{{ $grandTotal }}">

                            <div class="card mb-3">
                                <div class="card-body">

                                    <h3 class="card-title text-muted mb-4">
                                        {{ __('Shipping Details') }}
                                    </h3>

                                    <div class="row">

                                        {{-- Name --}}
                                        <div class="col-md-6 col-xl-4">
                                            <div class="mb-3">
                                                <label class="form-label required">
                                                    {{ __('Name') }}
                                                </label>

                                                <input type="text" class="form-control" name="shipping_name" required
                                                    value="{{ Auth::user()->billing_name ?? Auth::user()->name }}">
                                            </div>
                                        </div>

                                        {{-- Email --}}
                                        <div class="col-md-6 col-xl-4">
                                            <div class="mb-3">

                                                <label class="form-label required">
                                                    {{ __('Email') }}
                                                </label>

                                                <input type="email" class="form-control" name="shipping_email" required
                                                    value="{{ Auth::user()->billing_email ?? Auth::user()->email }}">
                                            </div>
                                        </div>

                                        {{-- Mobile --}}
                                        <div class="col-md-6 col-xl-4">
                                            <div class="mb-3">

                                                <label class="form-label required">
                                                    {{ __('Mobile Number') }}
                                                </label>

                                                <input type="hidden" name="country_code" id="country_code"
                                                    value="{{ old('country_code', $phone_code) }}">

                                                <input type="tel" class="form-control" name="mobile_number"
                                                    id="mobile_number"
                                                    value="{{ old('mobile_number', Auth::user()->mobile_number) }}">
                                            </div>
                                        </div>

                                        {{-- WhatsApp Number --}}
                                        <div class="col-md-6 col-xl-4">
                                            <div class="mb-3">

                                                <label class="form-label">
                                                    {{ __('WhatsApp Number') }}
                                                </label>

                                                <input type="hidden" name="whatsapp_country_code"
                                                    id="whatsapp_country_code"
                                                    value="{{ old('whatsapp_country_code', $whatsapp_code) }}">

                                                <input type="tel" class="form-control" name="whatsapp_number"
                                                    id="whatsapp_number"
                                                    value="{{ old('whatsapp_number', Auth::user()->whatsapp_number) }}">
                                            </div>
                                        </div>

                                        {{-- Address --}}
                                        <div class="col-md-6 col-xl-4">
                                            <div class="mb-3">

                                                <label class="form-label required">
                                                    {{ __('Address') }}
                                                </label>

                                                <input type="text" class="form-control" name="shipping_address"
                                                    required value="{{ Auth::user()->billing_address }}">
                                            </div>
                                        </div>

                                        {{-- City --}}
                                        <div class="col-md-6 col-xl-4">
                                            <div class="mb-3">

                                                <label class="form-label required">
                                                    {{ __('City') }}
                                                </label>

                                                <input type="text" class="form-control" name="shipping_city" required
                                                    value="{{ Auth::user()->billing_city }}">
                                            </div>
                                        </div>

                                        {{-- State --}}
                                        <div class="col-md-6 col-xl-4">
                                            <div class="mb-3">

                                                <label class="form-label required">
                                                    {{ __('State/Province') }}
                                                </label>

                                                <input type="text" class="form-control" name="shipping_state" required
                                                    value="{{ Auth::user()->billing_state }}">
                                            </div>
                                        </div>

                                        {{-- Zip --}}
                                        <div class="col-md-6 col-xl-4">
                                            <div class="mb-3">

                                                <label class="form-label">
                                                    {{ __('Zip Code') }}
                                                </label>

                                                <input type="text" class="form-control" name="shipping_zipcode"
                                                    value="{{ Auth::user()->billing_zipcode }}">
                                            </div>
                                        </div>

                                        {{-- Country --}}
                                        <div class="col-md-6 col-xl-4">
                                            @include('user.pages.checkout.includes.countries')
                                        </div>

                                        {{-- Type --}}
                                        <div class="col-md-6 col-xl-4">
                                            <div class="mb-3">

                                                <label class="form-label">
                                                    {{ __('Type') }}
                                                </label>

                                                <select name="type" class="form-control" required>

                                                    <option value="personal"
                                                        {{ Auth::user()->type == 'personal' ? 'selected' : '' }}>
                                                        {{ __('Personal') }}
                                                    </option>

                                                    <option value="business"
                                                        {{ Auth::user()->type == 'business' ? 'selected' : '' }}>
                                                        {{ __('Business') }}
                                                    </option>

                                                </select>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>

                            {{-- Payment Methods --}}
                            <div class="card">
                                <div class="card-body">
                                    <h3 class="card-title text-muted">
                                        {{ __('Payment Method') }}
                                    </h3>
                                    <div class="row">
                                        @foreach ($gateways as $gateway)
                                            <div class="col-xl-4 col-sm-6 mb-3">
                                                <label class="form-selectgroup-item w-100">
                                                    <input type="radio" name="payment_gateway_id"
                                                        value="{{ $gateway->payment_gateway_id }}"
                                                        class="form-selectgroup-input">
                                                    <div class="form-selectgroup-label d-flex align-items-center p-3">
                                                        <span class="avatar me-3"
                                                            style="background-image: url({{ asset($gateway->payment_gateway_logo) }})">
                                                        </span>
                                                        <div class="fw-bold">
                                                            {{ __($gateway->display_name) }}
                                                        </div>
                                                    </div>
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>

                                    <div class="mt-3">
                                        <button type="submit" class="btn btn-primary">
                                            {{ __('Continue for payment') }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        {{-- Footer --}}
        @include('user.includes.footer')
    </div>
@endsection

{{-- Custom JS --}}
@section('scripts')
    <script type="text/javascript" src="{{ asset('js/tom-select.base.min.js') }}"></script>
    <script src="{{ asset('js/confetti.browser.min.js') }}"></script>
    <script>
        "use strict";

        function formatCurrency(amount) {
            return "{{ $currency->symbol }}" + parseFloat(amount).toFixed(2);
        }
    </script>
    <script>
        "use strict";

        $(document).ready(function() {

            // Helper function to setup intlTelInput fields
            function setupPhoneInput(inputSelector, countryCodeSelector, initialCode, initialNumber) {
                const input = document.querySelector(inputSelector);
                const countryCodeInput = document.querySelector(countryCodeSelector);

                if (!input) return null;

                // 1. Initialize Plugin
                const iti = window.intlTelInput(input, {
                    separateDialCode: true,
                    preferredCountries: ["in", "us", "gb", "ae"],
                });

                // 2. Clean numeric values
                let code = (initialCode || '').replace(/[^0-9]/g, '');
                let number = (initialNumber || '');

                // 3. Set country flag & number on load (Ensures '+' is present)
                if (code) {
                    iti.setNumber('+' + code + number);
                }

                // 4. Update hidden input field
                function updateCountryCode() {
                    const countryData = iti.getSelectedCountryData();
                    if (countryData && countryData.dialCode && countryCodeInput) {
                        $(countryCodeSelector).val('+' + countryData.dialCode);
                    }
                }

                // Initial sync & update on country change
                updateCountryCode();
                input.addEventListener("countrychange", updateCountryCode);

                return iti;
            }

            // Initialize Mobile Number Field
            const mobileITI = setupPhoneInput(
                "#mobile_number",
                "#country_code",
                "{{ old('country_code', $phone_code) }}",
                "{{ old('mobile_number', Auth::user()->mobile_number) }}"
            );

            // Initialize WhatsApp Field
            const whatsappITI = setupPhoneInput(
                "#whatsapp_number",
                "#whatsapp_country_code",
                "{{ old('whatsapp_country_code', $whatsapp_code) }}",
                "{{ old('whatsapp_number', Auth::user()->whatsapp_number) }}"
            );

            // Submit Handler
            $("form").on("submit", function() {
                if (mobileITI) {
                    const mobileData = mobileITI.getSelectedCountryData();
                    if (mobileData && mobileData.dialCode) {
                        $("#country_code").val('+' + mobileData.dialCode);
                    }
                }

                if (whatsappITI) {
                    const whatsappData = whatsappITI.getSelectedCountryData();
                    if (whatsappData && whatsappData.dialCode) {
                        $("#whatsapp_country_code").val('+' + whatsappData.dialCode);
                    }
                }
            });
        });
    </script>

    <script>
        "use strict";

        // Array of element IDs
        var elementIds = ['billing_country', 'type'];

        // Loop through each element ID
        elementIds.forEach(function(id) {
            // Check if the element exists
            var el = document.getElementById(id);
            if (el) {
                // Apply TomSelect to the element
                new TomSelect(el, {
                    copyClassesToDropdown: false,
                    dropdownClass: 'dropdown-menu ts-dropdown',
                    optionClass: 'dropdown-item',
                    controlInput: '<input>',
                    maxOptions: null,
                    render: {
                        item: function(data, escape) {
                            if (data.customProperties) {
                                return '<div><span class="dropdown-item-indicator">' + data
                                    .customProperties + '</span>' + escape(data.text) + '</div>';
                            }
                            return '<div>' + escape(data.text) + '</div>';
                        },
                        option: function(data, escape) {
                            if (data.customProperties) {
                                return '<div><span class="dropdown-item-indicator">' + data
                                    .customProperties + '</span>' + escape(data.text) + '</div>';
                            }
                            return '<div>' + escape(data.text) + '</div>';
                        },
                    },
                });
            }
        });
    </script>
    <script>
        "use strict";
        document.addEventListener('DOMContentLoaded', function() {

            const quantityInput = document.getElementById('quantity');
            const finalQuantity = document.getElementById('final_quantity');
            const subtotalElement = document.getElementById('subtotal');
            const taxElement = document.getElementById('taxAmount');
            const totalElement = document.getElementById('total');
            const gatewayAmount = document.getElementById('payment_gateway_amount');
            const unitPrice = {{ $price }};
            const taxPercentage = {{ $taxPercentage }};

            // UPDATE AMOUNT
            function updateAmount() {
                let quantity = parseInt(quantityInput.value) || 1;
                if (quantity < 1) {
                    quantity = 1;
                    quantityInput.value = 1;
                }

                finalQuantity.value = quantity;
                const subtotal = unitPrice * quantity;
                const tax = (subtotal * taxPercentage) / 100;
                const total = subtotal + tax;

                // SUBTOTAL
                subtotalElement.innerText = formatCurrency(subtotal);

                // TAX
                if (taxElement) {
                    taxElement.innerText = formatCurrency(tax);
                }

                // TOTAL
                totalElement.innerText = formatCurrency(total);
                // GATEWAY AMOUNT
                gatewayAmount.value = total.toFixed(2);
            }

            quantityInput.addEventListener('input', updateAmount);
            updateAmount();

            // COUPON
            document.getElementById('couponForm').addEventListener('submit', function(e) {
                "use strict";

                e.preventDefault();

                const form = this;
                const formData = new FormData(form);

                // QUANTITY
                formData.append('quantity', document.getElementById('quantity').value);
                const couponCodeInput = document.getElementById('coupon_code');
                const appliedCoupon = document.getElementById('appliedCoupon');
                const discountMessage = document.getElementById('discountMessage');
                const appliedCouponInput = document.getElementById('applied_coupon');

                fetch('{{ route('user.order.nfc.card.checkout.coupon', $nfcCardDetails->nfc_card_id) }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        },
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // CONFETTI
                            confetti({
                                particleCount: 200,
                                spread: 90,
                            });

                            // INPUT STYLE
                            couponCodeInput.classList.remove('is-invalid');
                            couponCodeInput.classList.add('is-valid');

                            // SHOW COUPON
                            appliedCoupon.classList.remove('d-none');

                            // COUPON ROW
                            appliedCoupon.innerHTML = `
                    <td>
                        {{ __('Coupon Code') }} :
                        <strong>${data.coupon_code}</strong>
                    </td>

                    <td class="fw-bold text-end text-success">
                        -${formatCurrency(data.discountPrice)}
                    </td>
                `;

                            // MESSAGE
                            discountMessage.innerHTML = '{{ __('Coupon applied successfully!') }}';
                            // SUBTOTAL
                            subtotalElement.innerHTML = formatCurrency(data.subtotal);

                            // TAX
                            if (taxElement) {
                                taxElement.innerHTML = formatCurrency(data.taxAmount);
                            }

                            // TOTAL
                            totalElement.innerHTML = formatCurrency(data.total);

                            // GATEWAY
                            gatewayAmount.value = parseFloat(data.total).toFixed(2);

                            // SAVE COUPON
                            appliedCouponInput.value = data.coupon_id;
                        } else {

                            // INVALID STYLE
                            couponCodeInput.classList.remove('is-valid');

                            couponCodeInput.classList.add('is-invalid');

                            // HIDE COUPON
                            appliedCoupon.classList.add('d-none');

                            appliedCoupon.innerHTML = '';

                            // ERROR MESSAGE
                            discountMessage.innerHTML = data.message;

                            // REMOVE COUPON
                            appliedCouponInput.value = '';

                            // NORMAL CALCULATION
                            const quantity = parseInt(document.getElementById('quantity').value) || 1;

                            const unitPrice = {{ $nfcCardDetails->nfc_card_price }};

                            const taxPercentage = {{ (float) $config[25]->config_value }};

                            const subtotal = unitPrice * quantity;

                            const tax = (subtotal * taxPercentage) / 100;

                            const total = subtotal + tax;

                            // SUBTOTAL
                            subtotalElement.innerHTML = formatCurrency(subtotal);

                            // TAX
                            if (taxElement) {
                                taxElement.innerHTML = formatCurrency(tax);
                            }

                            // TOTAL
                            totalElement.innerHTML = formatCurrency(total);

                            // GATEWAY
                            gatewayAmount.value = total.toFixed(2);
                        }
                    })

                    .catch(error => {
                        console.error(
                            'Coupon Error:',
                            error
                        );
                    });
            });
        });
    </script>
@endsection
