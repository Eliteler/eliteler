@php
    // Page content
    use App\Services\GoBizCommonService;
    use App\Country;

    $config = GoBizCommonService::config();
    $supportPage = GoBizCommonService::templatePageContentGet('contact', 'GoBizOriginal');
    $pages = GoBizCommonService::templatePageContentAll('GoBizOriginal');

    // Default
    $navbar = true;
    $footer = true;

    if ($config[38]->config_value == 'no') {
        // $navbar = false;
        $footer = false;
    }

    // Check admin country
    $admin_country = Country::where('country_name', $config[23]->config_value)->first();

    $country_code = '1'; // Default fallback (e.g., US/Canada)
    if ($admin_country) {
        // Strip any existing '+' to keep it standardized as digits only
        $country_code = ltrim($admin_country->phone_code, '+');
    }

    $nfc_ref = request()->get('nfc_ref');
    $postUrl = $nfc_ref ? route('register') . '?nfc_ref=' . urlencode($nfc_ref) : route('register');
@endphp

{{-- Custom CSS --}}
@section('custom-script')
    {{-- intl-tel-input --}}
    <link rel="stylesheet" href="{{ asset('plugins/intlTelInput/intlTelInput.css') }}" />
    <script src="{{ asset('plugins/intlTelInput/intlTelInput.min.js') }}"></script>
    <style>
        .iti {
            width: 100%;
        }

        .iti--allow-dropdown input,
        .iti--separate-dial-code input {
            width: 100% !important;
            height: 56px;
            padding-right: 16px !important;
            border-radius: 12px;
            overflow: hidden;
        }

        .iti__flag-container {
            max-width: 85px;
        }

        .iti__selected-country {
            padding: 0 10px !important;
            max-width: 85px;
            overflow: hidden;
        }

        .iti__country-list {
            width: 100%;
            max-width: 100%;
            border-radius: 12px;
            overflow-x: hidden;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
            border: 1px solid #e5e7eb;
        }

        .iti__search-input {
            width: calc(100% - 20px) !important;
            height: 40px !important;
            margin: 10px !important;
            padding: 10px 12px !important;
            border-radius: 8px !important;
            font-size: 14px;
            border: 1px solid #d1d5db;
            box-sizing: border-box;
        }

        .iti__search-input:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.12);
        }

        .iti__country {
            padding: 10px 12px;
            font-size: 14px;
        }

        .iti__country-name,
        .iti__dial-code {
            white-space: nowrap;
        }

        @media (max-width: 640px) {

            .iti__country-list {
                width: 100vw !important;
                left: -12px !important;
                right: 0 !important;
                max-width: 100vw !important;
            }

            .iti__country-name {
                font-size: 13px;
            }

            .iti__dial-code {
                font-size: 13px;
            }
        }

        /* Rounded Corners */
        .rounded-t-md {
            border-top-right-radius: 0.375rem;
            border-top-left-radius: 0.375rem;
        }

        .rounded-b-md {
            border-bottom-right-radius: 0.375rem;
            border-bottom-left-radius: 0.375rem;
        }
    </style>
@endsection

@extends('GoBizOriginal::Website.layouts.index', ['nav' => $navbar, 'banner' => false, 'footer' => $footer, 'cookie' => false, 'setting' => true, 'title' => true, 'title' => __('Sign Up')])

@section('content')
    {{-- Register page --}}
    <section class="pt-12 lg:pb-20 overflow-hidden">
        <div class="container mx-auto px-4">
            <div class="flex flex-wrap items-center -m-6">
                <div class="w-full md:w-1/2 p-6 lg:block hidden mobile-banner">
                    <div class="p-1 mx-auto max-w-max overflow-hidden rounded-full">
                        <img class="object-cover rounded-full" src="{{ asset($template_config->auth_image) }}"
                            alt="{{ $config[0]->config_value }}">
                    </div>
                </div>
                <div class="w-full md:w-1/2 p-6 order-1 md:order-2">
                    <div class="md:max-w-md">
                        <h2 class="mb-3 font-heading font-bold text-6xl sm:text-7xl break-words">{{ __('Sign Up') }}</h2>
                        <p class="mb-8 text-lg">
                            {{ __('Join the digital business card revolution and simplify your networking.') }}</p>

                        {{-- Register form --}}
                        <form method="POST" action="{{ $postUrl }}" id="registerForm">
                            @csrf
                            <div class="flex flex-wrap -m-2 mb-6">
                                {{-- Full Name --}}
                                <div class="w-full p-2">
                                    <p class="mb-2.5 font-medium text-base">{{ __('Full Name') }} <span
                                            class="text-red-500">*</span></p>
                                    <div
                                        class="p-px border border-{{ $template_config->template_color }}-400 focus:border-{{ $template_config->template_color }}-500 focus:outline-none focus:ring-0 focus:ring-{{ $template_config->template_color }}-500 sm:text-sm rounded-md">
                                        <input type="text" name="name" placeholder="{{ __('Your name') }}" required
                                            autocomplete="name" autofocus value="{{ old('name') }}"
                                            class="w-full px-6 py-4 placeholder-gray-500 text-base outline-none rounded-lg @error('name') is-invalid @enderror">
                                    </div>
                                    @error('name')
                                        <span class="text-red-500 text-sm">{{ $message }}</span>
                                    @enderror
                                </div>

                                {{-- Email --}}
                                <div class="w-full p-2">
                                    <p class="mb-2.5 font-medium text-base">{{ __('Email address') }} <span
                                            class="text-red-500">*</span></p>
                                    <div
                                        class="p-px border border-{{ $template_config->template_color }}-400 focus:border-{{ $template_config->template_color }}-500 focus:outline-none focus:ring-0 focus:ring-{{ $template_config->template_color }}-500 sm:text-sm rounded-md">
                                        <input type="email" name="email" placeholder="{{ __('Your email address') }}"
                                            required autocomplete="email" value="{{ old('email') }}"
                                            class="w-full px-6 py-4 placeholder-gray-500 text-base outline-none rounded-lg @error('email') is-invalid @enderror">
                                    </div>
                                    @error('email')
                                        <span class="text-red-500 text-sm">{!! $message !!}</span>
                                    @enderror
                                </div>

                                {{-- Mobile Number --}}
                                <div class="w-full p-2">
                                    <p class="mb-2.5 font-medium text-base">
                                        {{ __('Mobile Number') }}
                                    </p>

                                    <div
                                        class="p-px border border-{{ $template_config->template_color }}-400 focus:border-{{ $template_config->template_color }}-500 focus:outline-none focus:ring-0 focus:ring-{{ $template_config->template_color }}-500 sm:text-sm rounded-md">

                                        <input type="tel" name="mobile_number" id="mobile_number"
                                            placeholder="{{ __('Enter mobile number') }}" autocomplete="tel"
                                            value="{{ old('mobile_number') }}"
                                            class="w-full py-4 pl-24 pr-4 text-base rounded-lg focus:outline-none">
                                    </div>

                                    {{-- Hidden Country Code --}}
                                    <input type="hidden" name="country_code" id="country_code"
                                        value="{{ old('country_code') ?? $country_code }}">

                                    @error('mobile_number')
                                        <span class="text-red-500 text-sm">{{ $message }}</span>
                                    @enderror
                                </div>

                                {{-- Password --}}
                                <div class="w-full p-2 mb-2">
                                    <p class="mb-2.5 font-medium text-base">
                                        {{ __('Password') }} <span class="text-red-500">*</span>
                                    </p>

                                    <div
                                        class="relative p-px border border-{{ $template_config->template_color }}-400 focus:border-{{ $template_config->template_color }}-500 focus:outline-none focus:ring-0 focus:ring-{{ $template_config->template_color }}-500 sm:text-sm rounded-t-md">
                                        <input
                                            class="w-full px-6 py-4 pr-36 placeholder-gray-500 text-base outline-none focus:ring-4 focus:ring-{{ $template_config->template_color }}-300 rounded-lg @error('password') is-invalid @enderror"
                                            type="password" placeholder="********" name="password" id="password" required
                                            autocomplete="current-password">
                                    </div>
                                    {{-- Show/Hide Password Button --}}
                                    <button type="button"
                                        class="show-password-btn text-left text-sm text-white bg-gradient-to-r from-{{ $template_config->template_color }}-400 to-{{ $template_config->template_color }}-500 rounded-b-md px-3 py-2 w-full"
                                        onclick="togglePassword()" title="{{ __('Show / Hide Password') }}">
                                        {{ __('Show / Hide Password') }}
                                    </button>

                                    @error('password')
                                        <span class="invalid-feedback text-red-500" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>

                                {{-- Confirm Password --}}
                                <div class="w-full p-2">
                                    <p class="mb-2.5 font-medium text-base">{{ __('Confirm Password') }} <span
                                            class="text-red-500">*</span></p>
                                    <div
                                        class="p-px border border-{{ $template_config->template_color }}-400 focus:border-{{ $template_config->template_color }}-500 focus:outline-none focus:ring-0 focus:ring-{{ $template_config->template_color }}-500 sm:text-sm rounded-md">
                                        <input type="password" name="password_confirmation" id="password_confirmation"
                                            required autocomplete="new-password" placeholder="********"
                                            class="w-full px-6 py-4 placeholder-gray-500 text-base outline-none rounded-lg">
                                    </div>
                                </div>

                                {{-- Referral Code --}}
                                @if ($config[80]->config_value == '1')
                                    <div class="w-full p-2">
                                        <p class="mb-2.5 font-medium text-base">{{ __('Referral Code') }}</p>
                                        <div
                                            class="p-px border border-{{ $template_config->template_color }}-400 focus:border-{{ $template_config->template_color }}-500 focus:outline-none focus:ring-0 focus:ring-{{ $template_config->template_color }}-500 sm:text-sm rounded-md">
                                            <input type="text" name="referral_code" id="referral_code"
                                                value="{{ Cookie::get('referral_code') ?? request()->get('ref') }}"
                                                autocomplete="referral_code" placeholder="FNKLJ2156DV"
                                                class="w-full px-6 py-4 placeholder-gray-500 text-base outline-none rounded-lg uppercase @error('referral_code') is-invalid @enderror">
                                        </div>
                                        @error('referral_code')
                                            <span class="text-red-500 text-sm">{{ $message }}</span>
                                        @enderror
                                    </div>
                                @endif

                                {{-- Recaptcha --}}
                                @if ($settings->recaptcha_configuration['RECAPTCHA_ENABLE'] == 'on')
                                    <div class="w-full p-2">
                                        {!! htmlFormSnippet() !!}

                                        {{-- Show reCAPTCHA validation error message --}}
                                        @error('g-recaptcha-response')
                                            <span class="invalid-feedback text-red-500 block mt-2 text-sm" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                @endif

                                {{-- Terms --}}
                                <div class="flex flex-wrap -m-1.5 mb-1">
                                    <div class="w-auto p-1.5">
                                        <input class="w-4 h-4" type="checkbox" name="terms" id="terms" checked>
                                    </div>
                                    <div class="flex-1 p-1.5">
                                        <p class="text-gray-500 text-sm">
                                            {{ __('I agree to the') }}
                                            @if ($pages[108]->page_name == 'terms' && $pages[108]->status == 'active')
                                                <a class="hover:text-gray-800 hover:font-bold"
                                                    href="{{ route('terms.and.conditions') }}">{{ __('Terms & Conditions') }}</a>
                                            @else
                                                <a class="hover:text-gray-800 hover:font-bold"
                                                    href="#">{{ __('Terms & Conditions') }}</a>
                                            @endif
                                            {{ __('of') }} {{ config('app.name') }}.
                                        </p>
                                    </div>
                                </div>
                                @error('terms')
                                    <span class="text-red-500 text-sm">{{ $message }}</span>
                                @enderror
                            </div>

                            {{-- Submit Button with effects --}}
                            <div class="group relative md:max-w-max my-5">
                                <div
                                    class="absolute top-0 left-0 w-full h-full bg-gradient-to-r from-{{ $template_config->template_color }}-400 to-{{ $template_config->template_color }}-500 opacity-0 group-hover:opacity-50 rounded-full transition ease-out duration-300">
                                </div>
                                <button type="submit" id="registerButton" onclick="disableRegisterButton(this)"
                                    data-wait-text="{{ __('Please wait, signing up...') }}"
                                    class="p-1 w-full font-heading font-semibold text-xs text-white uppercase tracking-px overflow-hidden rounded-full">
                                    <div
                                        class="relative py-5 px-14 bg-gradient-to-r from-{{ $template_config->template_color }}-400 to-{{ $template_config->template_color }}-500 overflow-hidden rounded-full">
                                        <div
                                            class="absolute top-0 left-0 transform -translate-y-full group-hover:-translate-y-0 h-full w-full bg-white transition ease-in-out duration-500">
                                        </div>
                                        <p class="relative z-10 group-hover:text-gray-900">{{ __('Sign Up') }}</p>
                                    </div>
                                </button>
                            </div>
                        </form>

                        {{-- Signin with Google --}}
                        @if ($settings->google_configuration['GOOGLE_ENABLE'] == 'on')
                            <h2 class="mb-3 font-heading font-bold text-2xl sm:text-2xl text-center sm:text-left">
                                {{ __('Or sign up instantly with Google') }}</h2>
                            <div class="group relative md:max-w-max mb-5">
                                <div
                                    class="absolute top-0 left-0 w-full h-full bg-gradient-to-r from-{{ $template_config->template_color }}-400 to-{{ $template_config->template_color }}-500 opacity-0 group-hover:opacity-50 rounded-full transition ease-out duration-300">
                                </div>
                                <a href="{{ route('login.google') }}">
                                    <button
                                        class="p-1 w-full font-heading font-semibold text-xs text-white uppercase tracking-px overflow-hidden rounded-full">
                                        <div
                                            class="relative flex py-5 px-14 bg-gradient-to-r from-{{ $template_config->template_color }}-400 to-{{ $template_config->template_color }}-500 overflow-hidden rounded-full">
                                            <i class="ti ti-brand-google brand-google items-center px-3"></i>
                                            {{ __('Continue with Google') }}
                                        </div>
                                    </button>
                                </a>
                            </div>
                        @endif
                        <p class="text-gray-500 text-sm">
                            <span>{{ __('Already have an account?') }}</span>
                            <a class="hover:text-gray-800 hover:font-bold"
                                href="{{ route('login') }}">{{ __('Login now') }}</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Custom JS --}}
@section('custom-js')
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const input = document.querySelector("#mobile_number");
            const countryCodeInput = document.querySelector("#country_code");

            // 1. Initialize intlTelInput
            const iti = window.intlTelInput(input, {
                separateDialCode: true,
                preferredCountries: ["in", "us", "gb", "ae"],
            });

            // 2. Get initial values from backend (PHP / Laravel old input)
            const initialDialCode = countryCodeInput.value ? countryCodeInput.value.replace(/[^0-9]/g, '') : '';
            const initialNumber = input.value ? input.value : '';

            // 3. Set default country flag and phone number on load
            if (initialDialCode) {
                iti.setNumber('+' + initialDialCode + initialNumber);
            }

            // 4. Function to update hidden input value
            function updateCountryCode() {
                const countryData = iti.getSelectedCountryData();

                if (countryData && countryData.dialCode) {
                    countryCodeInput.value = `+${countryData.dialCode}`;
                } else {
                    countryCodeInput.value = '';
                }
            }

            // 5. Run initially to sync hidden field
            updateCountryCode();

            // 6. Listen for changes when user changes country dropdown
            input.addEventListener("countrychange", updateCountryCode);
        });
    </script>
    <script>
        function disableRegisterButton(btn) {
            const form = document.getElementById('registerForm');
            const email = form.querySelector('input[name="email"]');
            const password = form.querySelector('input[name="password"]');

            if (!email.value.trim() || !password.value.trim()) {
                alert("{{ __('Email and Password are required.') }}");
                return;
            }

            // Disable button
            btn.disabled = true;
            btn.classList.add('opacity-50', 'cursor-not-allowed');

            // Change button text
            const textElement = btn.querySelector('p');
            if (textElement) {
                const waitText = btn.getAttribute('data-wait-text') || `{{ __('Please wait...') }}`;
                textElement.textContent = waitText;
                textElement.style.color = '#000000';
            }

            // Change background gradient if button has a bg div
            const bg = btn.querySelector('div.bg-gradient-to-r');
            if (bg) {
                bg.classList.remove('from-emerald-400', 'to-emerald-500');
                bg.classList.add('from-gray-400', 'to-gray-500');

                // Optional: Override with inline background
                bg.style.background = 'linear-gradient(to right, #d1d5db, #9ca3af)';
            }

            // Submit the form
            form.submit();
        }

        function togglePassword() {
            const pwd = document.getElementById('password');
            const confirmPwd = document.getElementById('password_confirmation');
            if (pwd.type === 'password') {
                pwd.type = 'text';
                confirmPwd.type = 'text';
            } else {
                pwd.type = 'password';
                confirmPwd.type = 'password';
            }
        }
    </script>
@endsection
@endsection
