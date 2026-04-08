@extends('user.layouts.index', ['header' => false, 'nav' => false, 'demo' => true])

{{-- Custom CSS --}}
@section('css')
<style>
    .custom-payment-logo {
       width: 75px;
       height: 75px;
       border-radius: 10px;
    }

    /* Media queries for responsiveness */
    @media (max-width: 768px) {
        .custom-payment-logo {
            width: 40px;
            height: 40px;
        }
    }
</style>
@endsection

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
            @if ($selected_plan == null)
                <div class="container">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <h3 class="card-title">{{ __('No Plan Found') }}</h3>
                                <a href="{{ route('user.pages.checkout', Request::segment(3)) }}"
                                    class="btn btn-primary">{{ __('Back') }}</a>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            @else
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

                    <div class="row">
                        <div class="col-lg-4 mb-3">
                            <div class="card">
                                <div class="card-body">
                                    <h3 class="card-title">{{ __('Upgrade/Renewal Plan') }}</h3>
                                    <div class="card-table table-responsive">
                                        <table class="table table-vcenter border">
                                            <thead>
                                                <tr>
                                                    <th class="w-1">{{ __('Description') }}</th>
                                                    <th class="w-1 text-end">{{ __('Price') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>
                                                        <div>
                                                            {{ __($selected_plan->plan_name) }} -
                                                            @if ($selected_plan->validity == 9999 || $selected_plan->validity == 0)
                                                                {{ __('Lifetime') }}
                                                            @elseif ($selected_plan->validity == 365 || $selected_plan->validity == 366)
                                                                {{ __('Per Year') }}
                                                            @elseif ($selected_plan->validity == 30 || $selected_plan->validity == 31)
                                                                {{ __('Per Month') }}
                                                            @else
                                                                {{ __($selected_plan->validity) }}
                                                                {{ __('Days') }}
                                                            @endif
                                                        </div>
                                                    </td>
                                                    <td class="text-bold text-end">
                                                        {{ $selected_plan->plan_price == '0' ? 0 : formatCurrency($selected_plan->plan_price) }}
                                                    </td>
                                                </tr>
                                                @if ($config[25]->config_value > 0)
                                                    <tr>
                                                        <td>
                                                            <div>
                                                                {{ __($config[24]->config_value) }}
                                                                ({{ $config[25]->config_value }}%)
                                                            </div>
                                                        </td>
                                                        <td class="text-bold text-end">
                                                            {{ formatCurrency(((float) $selected_plan->plan_price * (float) $config[25]->config_value) / 100) }}
                                                        </td>
                                                    </tr>
                                                @endif
                                                <tr class="d-none" id="appliedCoupon"></tr>
                                                <tr>
                                                    <td class="h5 text-bold"> {{ __('Total Payable Amount') }} </td>
                                                    <td class="w-1 text-bold h3 text-end" id="total">
                                                        {{ formatCurrency($total) }}
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>

                                    <!-- Coupon Code -->
                                    <form id="couponForm" class="my-3">
                                        <div class="input-group">
                                            <input type="text" class="form-control text-uppercase"
                                                placeholder="{{ __('Coupon Code') }}"
                                                value="{{ old('coupon_code') ?? $coupon_code }}" name="coupon_code"
                                                id="coupon_code">
                                            <div class="px-2"></div>
                                            <button type="submit" class="btn btn-primary "
                                                id="applyCoupon">{{ __('Apply') }}</button>
                                        </div>
                                        <p class="fw-bold mt-2" id="discountMessage"></p>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-8">
                            <form action="{{ route('prepare.payment.gateway', $selected_plan->plan_id) }}" method="post">
                                @csrf
                                <div class="col-lg-12 mb-3">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="row">
                                                    <h3 class="card-title text-muted mb-3">{{ __('Billing Details') }}</h1>
                                                        <input type="hidden" name="applied_coupon" id="applied_coupon"
                                                            class="form-control">
                                                        {{-- Name --}}
                                                        <div class="col-sm-6 col-xl-4">
                                                            <div class="mb-3">
                                                                <label
                                                                    class="form-label required">{{ __('Name') }}</label>
                                                                <input type="text" class="form-control"
                                                                    name="billing_name" placeholder="{{ __('Name') }}"
                                                                    value="{{ Auth::user()->billing_name == null ? Auth::user()->name : Auth::user()->billing_name }}"
                                                                    required>
                                                            </div>
                                                        </div>
                                                        {{-- Email --}}
                                                        <div class="col-sm-6 col-xl-4">
                                                            <div class="mb-3">
                                                                <label
                                                                    class="form-label required">{{ __('Email') }}</label>
                                                                <input type="email" class="form-control"
                                                                    name="billing_email"
                                                                    placeholder="{{ __('Email') }}"
                                                                    value="{{ Auth::user()->billing_email == null ? Auth::user()->email : Auth::user()->billing_email }}"
                                                                    required>
                                                            </div>
                                                        </div>
                                                        {{-- Phone --}}
                                                        <div class="col-sm-6 col-xl-4">
                                                            <div class="mb-3">
                                                                <label
                                                                    class="form-label">{{ __('Mobile Number (With Country Code)') }}</label>
                                                                <input type="tel" class="form-control"
                                                                    name="billing_phone"
                                                                    placeholder="{{ __('Example: 91 9876543210') }}"
                                                                    value="{{ Auth::user()->billing_phone == null ? Auth::user()->mobile_number : Auth::user()->billing_phone }}">
                                                            </div>
                                                        </div>
                                                        {{-- WhatsApp Number --}}
                                                        <div class="col-sm-6 col-xl-4">
                                                            <div class="mb-3">
                                                                <label
                                                                    class="form-label">{{ __('WhatsApp Number (With Country Code)') }}</label>
                                                                <input type="text" class="form-control"
                                                                    name="billing_whatsapp"
                                                                    placeholder="{{ __('Example: 91 9876543210') }}"
                                                                    value="{{ Auth::user()->whatsapp_number }}">
                                                            </div>
                                                        </div>
                                                        {{-- Address --}}
                                                        <div class="col-sm-6 col-xl-4">
                                                            <div class="mb-3">
                                                                <label
                                                                    class="form-label required">{{ __('Address') }}</label>
                                                                <input type="text" class="form-control"
                                                                    name="billing_address" id="billing_address"
                                                                    placeholder="{{ __('Address') }}"
                                                                    value="{{ Auth::user()->billing_address }}" required>
                                                            </div>
                                                        </div>
                                                        {{-- City --}}
                                                        <div class="col-sm-6 col-xl-4">
                                                            <div class="mb-3">
                                                                <label
                                                                    class="form-label required">{{ __('City') }}</label>
                                                                <input type="text" class="form-control"
                                                                    name="billing_city" placeholder="{{ __('City') }}"
                                                                    value="{{ Auth::user()->billing_city }}" required>
                                                            </div>
                                                        </div>
                                                        {{-- State/Province --}}
                                                        <div class="col-sm-6 col-xl-4">
                                                            <div class="mb-3">
                                                                <label
                                                                    class="form-label required">{{ __('State/Province') }}</label>
                                                                <input type="text" class="form-control"
                                                                    name="billing_state"
                                                                    placeholder="{{ __('State/Province') }}"
                                                                    value="{{ Auth::user()->billing_state }}" required>
                                                            </div>
                                                        </div>
                                                        {{-- Zip Code --}}
                                                        <div class="col-sm-6 col-xl-4">
                                                            <div class="mb-3">
                                                                <label class="form-label">{{ __('Zip Code') }}</label>
                                                                <input type="text" class="form-control"
                                                                    name="billing_zipcode"
                                                                    placeholder="{{ __('Zip Code') }}"
                                                                    value="{{ Auth::user()->billing_zipcode }}">
                                                            </div>
                                                        </div>
                                                        {{-- Country --}}
                                                        <div class="col-sm-6 col-xl-4">
                                                            {{-- Include countries --}}
                                                            @include('user.pages.checkout.includes.countries')
                                                        </div>
                                                        {{-- Type --}}
                                                        <div class="col-sm-6 col-xl-4">
                                                            <div class="mb-3">
                                                                <label class="form-label"
                                                                    for="type">{{ __('Type') }}</label>
                                                                <select name="type" id="type"
                                                                    class="form-control" required>
                                                                    <option value="personal"
                                                                        {{ Auth::user()->type == 'personal' ? 'selected' : '' }}>
                                                                        {{ __('Personal') }}</option>
                                                                    <option value="business"
                                                                        {{ Auth::user()->type == 'business' ? 'selected' : '' }}>
                                                                        {{ __('Business') }}</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        {{-- Tax Number --}}
                                                        <div class="col-sm-6 col-xl-4">
                                                            <div class="mb-3">
                                                                <label class="form-label">{{ __('Tax Number') }} </label>
                                                                <input type="text" class="form-control"
                                                                    name="vat_number"
                                                                    placeholder="{{ __('Tax Number') }}"
                                                                    value="{{ Auth::user()->vat_number }}">
                                                            </div>
                                                        </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-lg-12">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <div class="mb-3">
                                                        <div class="row">
                                                            <input type="hidden" name="payment_gateway_amount"
                                                                id="payment_gateway_amount" value="{{ $total }}"
                                                                class="form-control">

                                                            {{-- One time payment gateways --}}
                                                            @if (count($gateways) > 0)
                                                                <h3 class="card-title text-muted">
                                                                    {{ __('One-time Payment methods') }}
                                                                </h3>
                                                                @foreach ($gateways as $gateway)
                                                                    <div class="col-xl-4 col-sm-6 mb-3">
                                                                        <div
                                                                            class="form-selectgroup form-selectgroup-boxes d-flex flex-column">
                                                                            <label class="form-selectgroup-item flex-fill">
                                                                                <input type="radio"
                                                                                    name="payment_gateway_id"
                                                                                    onclick="showAlert('one-time')"
                                                                                    value="{{ $gateway->payment_gateway_id }}"
                                                                                    class="form-selectgroup-input">
                                                                                <div
                                                                                    class="form-selectgroup-label d-flex align-items-center p-3">
                                                                                    <div class="me-3">
                                                                                        <span
                                                                                            class="form-selectgroup-check"></span>
                                                                                    </div>
                                                                                    @php
                                                                                        $file = public_path(
                                                                                            $gateway->payment_gateway_logo,
                                                                                        );
                                                                                        $final = file_exists($file)
                                                                                            ? asset(
                                                                                                $gateway->payment_gateway_logo,
                                                                                            )
                                                                                            : asset(
                                                                                                'img/payment-method/default.png',
                                                                                            );
                                                                                    @endphp

                                                                                    <span class="avatar me-3"
                                                                                        style="background-image: url('{{ $final }}')"></span>
                                                                                    <div>
                                                                                        <div class="font-weight-medium h4">
                                                                                            {{ __($gateway->display_name) }}
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </label>
                                                                        </div>
                                                                    </div>
                                                                @endforeach
                                                            @endif

                                                            {{-- recurring payment gateways --}}
                                                            @if (!empty($recurring_payment_gateways) && $selected_plan->plan_validity < 9998)
                                                                <h3 class="card-title text-muted mt-1 mb-3 col-12">
                                                                    {{ __('Recurring Payment methods') }}</h3>
                                                                @foreach ($recurring_payment_gateways as $gateway)
                                                                    <div class="col-xl-4 col-sm-6 mb-3">
                                                                        <div
                                                                            class="form-selectgroup form-selectgroup-boxes d-flex flex-column">
                                                                            <label class="form-selectgroup-item flex-fill">
                                                                                <input type="radio"
                                                                                    name="payment_gateway_id"
                                                                                    onclick="showAlert('recurring')"
                                                                                    value="{{ $gateway->payment_gateway_id }}"
                                                                                    class="form-selectgroup-input">
                                                                                <div
                                                                                    class="form-selectgroup-label d-flex align-items-center p-3">
                                                                                    <div class="me-3">
                                                                                        <span
                                                                                            class="form-selectgroup-check"></span>
                                                                                    </div>
                                                                                    @php
                                                                                        $file = public_path(
                                                                                            $gateway->payment_gateway_logo,
                                                                                        );
                                                                                        $final = file_exists($file)
                                                                                            ? asset(
                                                                                                $gateway->payment_gateway_logo,
                                                                                            )
                                                                                            : asset(
                                                                                                'img/payment-method/default.png',
                                                                                            );
                                                                                    @endphp

                                                                                    <span
                                                                                        class="me-3 d-flex align-items-center justify-content-center custom-payment-logo">
                                                                                        <img src="{{ $final }}"
                                                                                            alt="{{ $gateway->display_name }}"
                                                                                            style="max-width:100%; max-height:100%; object-fit:contain; border-radius:10px;">
                                                                                    </span>
                                                                                    <div>
                                                                                        <div class="font-weight-medium h4">
                                                                                            {{ __($gateway->display_name) }}
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </label>
                                                                        </div>
                                                                    </div>
                                                                @endforeach
                                                            @endif
                                                        </div>
                                                    </div>

                                                    <div class="col-12">
                                                        {{-- Coupon alert for subscription --}}
                                                        <div id="subscription-alert"
                                                            class="alert alert-important alert-info mb-3 d-none"
                                                            role="alert">
                                                            {{ __('Note: Coupon codes can only be used with one-time payment methods, not with recurring payment methods.') }}
                                                        </div>
                                                        <input type="submit" value="{{ __('Continue for payment') }}"
                                                            class="btn btn-primary">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    {{-- Footer --}}
                    @include('user.includes.footer')
                </div>
            @endif
        </div>
    </div>

    {{-- Custom JS --}}
@section('scripts')
    <script type="text/javascript" src="{{ asset('js/tom-select.base.min.js') }}"></script>
    <script src="{{ asset('js/confetti.browser.min.js') }}"></script>
    <script>
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
        document.getElementById('couponForm').addEventListener('submit', function(e) {
            "use strict";
            e.preventDefault(); // Prevent form from submitting the traditional way

            let form = this;
            let formData = new FormData(form);
            let couponCodeInput = document.getElementById('coupon_code');
            let appliedCoupon = document.getElementById('appliedCoupon');
            let discountMessage = document.getElementById('discountMessage');
            let applied_coupon = document.getElementById('applied_coupon');
            let applyCoupon = document.getElementById('applyCoupon');

            fetch('{{ route('user.checkout.coupon', $selected_plan->plan_id) }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    },
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // trigger confetti
                        confetti({
                            particleCount: 200,
                            spread: 900,
                            colors: ['#f1c40f', '#f39c12', '#e67e22', '#e74c3c', '#9b59b6', '#8e44ad',
                                '#7f8c8d'
                            ],
                        });

                        couponCodeInput.classList.remove('is-invalid');
                        couponCodeInput.classList.add('is-valid');
                        appliedCoupon.classList.remove('d-none');

                        // Update the table with coupon code and discount
                        let newRow = `
                            <tr>
                                <td>{{ __('Coupon Code') }} : <strong>${data.coupon_code}</strong></td>
                                <td class="text-bold text-end">-{{ $currency->symbol }}${parseFloat(data.discountPrice).toFixed(2)}</td>
                            </tr>
                        `;
                        appliedCoupon.innerHTML = newRow; // Replace the existing table with the new one

                        // Display discount message
                        discountMessage.innerHTML = '{{ __('Coupon applied!') }}';

                        // Update the total
                        if (data.total > 0) {
                            document.getElementById('total').innerHTML = '{{ $currency->symbol }}' + data.total
                                .toFixed(2);
                            // Update payment_gateway_amount value
                            document.getElementById('payment_gateway_amount').value = data.total;
                        } else {
                            document.getElementById('total').innerHTML = '{{ $currency->symbol }}0.00';
                            // Update payment_gateway_amount value
                            document.getElementById('payment_gateway_amount').value = 0;
                        }

                        // Update the coupon code input
                        applied_coupon.value = data.coupon_id;
                    } else {
                        couponCodeInput.classList.remove('is-valid');
                        couponCodeInput.classList.add('is-invalid');
                        appliedCoupon.classList.add('d-none');
                        appliedCoupon.innerHTML = ""; // Replace the existing table with the new one
                        discountMessage.innerHTML = data.message;
                        // Update the total
                        document.getElementById('total').innerHTML =
                            '{{ $currency->symbol }}{{ $total }}';

                        // Update the total
                        applied_coupon.value = "";
                    }
                })
                .catch(error => console.error('Error:', error));
        });

        // Show alert
        function showAlert(type) {
            if (type == 'recurring') {
                let couponCodeInput = document.getElementById('coupon_code');
                let appliedCoupon = document.getElementById('appliedCoupon');
                let discountMessage = document.getElementById('discountMessage');
                let applied_coupon = document.getElementById('applied_coupon');
                let applyCoupon = document.getElementById('applyCoupon');

                couponCodeInput.value = "";
                couponCodeInput.classList.remove('is-valid');
                couponCodeInput.classList.remove('is-invalid');
                appliedCoupon.classList.add('d-none');
                appliedCoupon.innerHTML = "";
                discountMessage.innerHTML = "";
                // Update the total
                document.getElementById('total').innerHTML =
                    '{{ $currency->symbol }}{{ $total }}';

                // Update the total
                applied_coupon.value = "";
                document.getElementById('subscription-alert').classList.remove('d-none');
            } else {
                document.getElementById('subscription-alert').classList.add('d-none');
            }
        }
    </script>
@endsection
@endsection
