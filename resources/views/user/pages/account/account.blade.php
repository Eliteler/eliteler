@extends('user.layouts.index', ['header' => true, 'nav' => true, 'demo' => true, 'settings' => $settings])

@php
    use App\Services\GoBizCommonService;

    // Load config once
    $config = GoBizCommonService::config();
    
    // Fetch default country code from admin config if profile value is missing
    $admin_country = \App\Country::where('country_name', $config[23]->config_value ?? '')->first();
    $default_code = $admin_country ? ltrim($admin_country->phone_code, '+') : '1';
@endphp

{{-- Custom CSS --}}
@section('css')
    <link rel="stylesheet" href="{{ asset('plugins/intlTelInput/intlTelInput.css') }}" />
    <script src="{{ asset('plugins/intlTelInput/intlTelInput.min.js') }}"></script>
    <style>
        .card-footer {
            background-color: transparent !important;
        }

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

@section('content')
    <div class="page-wrapper">
        <!-- Page title (Wraps the inner container-fluid) -->
        <div class="page-header d-print-none">
            <div class="container-fluid">
                <div class="row g-2 align-items-center">
                    <!-- Hierarchical Breadcrumb Trail -->
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="{{ route('user.dashboard') }}" class="text-decoration-none text-secondary">
                                    {{ __('Dashboard') }}
                                </a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">
                                {{ __('My Account') }}
                            </li>
                        </ol>
                    </nav>
                    <div class="col">
                        <h2 class="page-title">
                            {{ __('My Account') }}
                        </h2>

                        <!-- Descriptive Help Subtitle -->
                        <div class="text-muted mt-1">
                            {{ __('Manage your personal profile details, account security, and notification settings.') }}
                        </div>
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
                    <div class="col-sm-12 col-lg-12">
                        <div class="card">
                            <div class="row g-0">
                                <div class="col-12 col-md-3 border-end">
                                    <div class="card-body pt-3">
                                        <div class="list-group list-group-transparent">
                                            {{-- Nav links --}}
                                            @include('user.pages.account.includes.navlinks', [
                                                'link' => 'profile',
                                            ])
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12 col-md-9 d-flex flex-column">
                                    <form action="{{ route('user.update.account') }}" method="post"
                                        enctype="multipart/form-data">
                                        @csrf
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="row">

                                                        {{-- Name --}}
                                                        <div class="col-md-6">
                                                            <div class="mb-3">
                                                                <label
                                                                    class="form-label required">{{ __('Name') }}</label>
                                                                <input type="text" class="form-control" name="name"
                                                                    placeholder="{{ __('Enter your name') }}"
                                                                    value="{{ old('name', $account_details->name) }}"
                                                                    required>
                                                            </div>
                                                        </div>

                                                        {{-- Email --}}
                                                        <div class="col-md-6">
                                                            <div class="mb-3">
                                                                <label
                                                                    class="form-label required">{{ __('Email') }}</label>
                                                                <input type="text" class="form-control" name="email"
                                                                    placeholder="{{ __('Enter your email') }}"
                                                                    value="{{ old('email', $account_details->email) }}"
                                                                    required>
                                                            </div>
                                                        </div>

                                                        {{-- Mobile Number --}}
                                                        <div class="col-md-6">
                                                            <div class="mb-3">
                                                                <label class="form-label">
                                                                    {{ __('Mobile Number') }}
                                                                </label>

                                                                <input type="hidden" name="country_code" id="country_code"
                                                                    value="{{ old('country_code', $account_details->country_code ?: $default_code) }}">

                                                                <input type="tel" class="form-control"
                                                                    id="mobile_number" name="mobile_number"
                                                                    placeholder="{{ __('Enter your mobile number') }}"
                                                                    value="{{ old('mobile_number', $account_details->mobile_number) }}">
                                                            </div>
                                                        </div>

                                                        {{-- Whatsapp Number --}}
                                                        <div class="col-md-6">
                                                            <div class="mb-3">
                                                                <label class="form-label">
                                                                    {{ __('Whatsapp Number') }}
                                                                </label>

                                                                <input type="hidden" name="whatsapp_country_code"
                                                                    id="whatsapp_country_code"
                                                                    value="{{ old('whatsapp_country_code', $account_details->whatsapp_country_code ?: $default_code) }}">

                                                                <input type="tel" class="form-control"
                                                                    id="whatsapp_number" name="whatsapp_number"
                                                                    placeholder="{{ __('Enter your whatsapp number') }}"
                                                                    value="{{ old('whatsapp_number', $account_details->whatsapp_number) }}">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-footer text-end">
                                            <button type="submit" class="btn btn-primary">
                                                <svg xmlns="http://www.w3.org/2000/svg"
                                                    class="icon icon-tabler icon-tabler-device-floppy" width="24"
                                                    height="24" viewBox="0 0 24 24" stroke-width="2"
                                                    stroke="currentColor" fill="none" stroke-linecap="round"
                                                    stroke-linejoin="round">
                                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                    <path
                                                        d="M6 4h10l4 4v10a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2" />
                                                    <path d="M12 14m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" />
                                                    <path d="M14 4l0 4h-6l0 -4" />
                                                </svg>
                                                {{ __('Save') }}
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Delete My Account --}}
                <div class="card mt-3 border-danger">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title text-danger mb-1">{{ __('Delete Account') }}</h5>
                            <p class="text-muted mb-0">
                                {{ __('If you proceed, you will delete your account. This action cannot be undone.') }}
                            </p>
                        </div>
                        <div>
                            <a class="btn btn-sm btn-danger px-5" href="#"
                                onclick="deleteAccount(); return false;">
                                {{ __('Delete My Account') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Footer --}}
        @include('user.includes.footer')
    </div>

    {{-- Delete Account --}}
    <div class="modal modal-bg fade" id="delete-modal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
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
                    <div id="delete_status" class="text-secondary">
                        {{ __('If you proceed, you will delete your account. This action cannot be undone.') }}
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
                                {{-- POST with CSRF token (the account can't be deleted by just opening a URL) --}}
                                <form method="POST" action="{{ route('user.delete.account') }}" class="m-0">
                                    @csrf
                                    <button type="submit" class="btn btn-danger w-100" id="delete_id">
                                        {{ __('Yes, proceed') }}
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

{{-- Custom JS --}}
@section('scripts')
    <script>
        "use strict";

        document.addEventListener("DOMContentLoaded", function() {

            // Reusable function to setup intlTelInput on any phone field
            function initPhoneInput(phoneSelector, countryCodeSelector) {
                const phoneInput = document.querySelector(phoneSelector);
                const countryCodeInput = document.querySelector(countryCodeSelector);

                if (!phoneInput || !countryCodeInput) return;

                // 1. Initialize Plugin
                const iti = window.intlTelInput(phoneInput, {
                    separateDialCode: true,
                    preferredCountries: ["in", "us", "gb", "ae"],
                });

                // 2. Load Initial Country & Phone
                const dialCode = countryCodeInput.value ? countryCodeInput.value.replace(/[^0-9]/g, '') : '';
                const phoneNum = phoneInput.value ? phoneInput.value : '';

                if (dialCode) {
                    iti.setNumber('+' + dialCode + phoneNum);
                }

                // 3. Helper to update hidden country code input
                function updateCountryCode() {
                    const countryData = iti.getSelectedCountryData();
                    if (countryData && countryData.dialCode) {
                        countryCodeInput.value = '+' + countryData.dialCode;
                    } else {
                        countryCodeInput.value = '';
                    }
                }

                // 4. Update initially & attach listeners
                updateCountryCode();
                phoneInput.addEventListener("countrychange", updateCountryCode);

                if (phoneInput.form) {
                    phoneInput.form.addEventListener("submit", updateCountryCode);
                }
            }

            // Initialize Mobile Field
            initPhoneInput("#mobile_number", "#country_code");

            // Initialize WhatsApp Field
            initPhoneInput("#whatsapp_number", "#whatsapp_country_code");

        });

        // Delete Account Modal
        function deleteAccount() {
            "use strict";
            $("#delete-modal").modal("show");
        }
    </script>
@endsection
