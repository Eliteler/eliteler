@php
// Page content
use App\Services\GoBizCommonService;

$config = GoBizCommonService::config();
$supportPage = GoBizCommonService::templatePageContentGet('contact', 'GoBizOriginal');
$pages = GoBizCommonService::templatePageContentAll('GoBizOriginal');

// Default
$navbar = true;
$footer = true;

if ($config[38]->config_value == "no") { 
    // $navbar = false;
    $footer = false;
}
@endphp

{{-- Custom CSS --}}
@section('css')
<style>
    .spinner {
        border: 2px solid transparent;
        border-top: 2px solid white;
        border-radius: 50%;
        width: 1em;
        height: 1em;
        animation: spin 0.6s linear infinite;
    }

    @keyframes spin {
        to {
            transform: rotate(360deg);
        }
    }

    .show-password-btn {
        position: absolute;
        top: 50%;
        right: 1rem; /* equivalent to Tailwind's right-4 */
        transform: translateY(-50%);
        font-size: 0.875rem; /* text-sm */
        color: #4b5563; /* Tailwind gray-600 */
        background: none;
        border: none;
        cursor: pointer;
        padding: 0;
    }

    .show-password-btn:hover {
        color: #1f2937; /* Tailwind gray-800 */
    }

    .btn-waiting {
        background: linear-gradient(to right, #d1d5db, #9ca3af) !important;
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
                    <img class="object-cover rounded-full" src="{{ asset($template_config->auth_image) }}" alt="{{ $config[0]->config_value }}">
                </div>
            </div>
            <div class="w-full md:w-1/2 p-6 order-1 md:order-2">
                <div class="md:max-w-md">
                    <h2 class="mb-3 font-heading font-bold text-6xl sm:text-7xl break-words">{{ __('Sign Up') }}</h2>
                    <p class="mb-8 text-lg">{{ __('Join the digital business card revolution and simplify your networking.') }}</p>

                    {{-- Register form --}}
                    <form method="POST" action="{{ route('register') }}" id="registerForm">
                        @csrf  
                        <div class="flex flex-wrap -m-2 mb-6">
                            {{-- Full Name --}}
                            <div class="w-full p-2">
                                <p class="mb-2.5 font-medium text-base">{{ __('Full Name') }} <span class="text-red-500">*</span></p>
                                <div class="p-px bg-gradient-to-r from-{{ $template_config->template_color }}-400 to-{{ $template_config->template_color }}-500 rounded-lg">
                                    <input type="text" name="name" placeholder="{{ __('Your name') }}" required autocomplete="name" autofocus
                                        value="{{ old('name') }}"
                                        class="w-full px-6 py-4 placeholder-gray-500 text-base outline-none rounded-lg @error('name') is-invalid @enderror">
                                </div>
                                @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>

                            {{-- Email --}}
                            <div class="w-full p-2">
                                <p class="mb-2.5 font-medium text-base">{{ __('Email address') }} <span class="text-red-500">*</span></p>
                                <div class="p-px bg-gradient-to-r from-{{ $template_config->template_color }}-400 to-{{ $template_config->template_color }}-500 rounded-lg">
                                    <input type="email" name="email" placeholder="{{ __('Your email address') }}" required autocomplete="email"
                                        value="{{ old('email') }}"
                                        class="w-full px-6 py-4 placeholder-gray-500 text-base outline-none rounded-lg @error('email') is-invalid @enderror">
                                </div>
                                @error('email') <span class="text-red-500 text-sm">{!! $message !!}</span> @enderror
                            </div>

                            {{-- Mobile Number --}}
                            <div class="w-full p-2">
                                <p class="mb-2.5 font-medium text-base">{{ __('Mobile Number with country code') }}</p>
                                <div class="p-px bg-gradient-to-r from-{{ $template_config->template_color }}-400 to-{{ $template_config->template_color }}-500 rounded-lg">
                                    <input type="tel" name="mobile_number" id="mobile_number" placeholder="{{ __('Your mobile number with country code') }}" autocomplete="tel"
                                        class="w-full px-6 py-4 placeholder-gray-500 text-base outline-none rounded-lg @error('mobile_number') is-invalid @enderror">
                                </div>
                                @error('mobile_number') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>

                            {{-- Password --}}
                            <div class="w-full p-2 mb-2">
                                <p class="mb-2.5 font-medium text-base">
                                    {{ __('Password') }} <span class="text-red-500">*</span>
                                </p>

                                <div class="relative p-px bg-gradient-to-r from-{{ $template_config->template_color }}-400 to-{{ $template_config->template_color }}-500 rounded-lg">
                                    <input
                                        class="w-full px-6 py-4 pr-36 placeholder-gray-500 text-base outline-none focus:ring-4 focus:ring-{{ $template_config->template_color }}-300 rounded-lg @error('password') is-invalid @enderror"
                                        type="password"
                                        placeholder="********"
                                        name="password"
                                        id="password"
                                        required
                                        autocomplete="current-password"
                                    >

                                    {{-- Show/Hide Password Button --}}
                                    <button type="button"
                                        class="show-password-btn text-sm text-white my-1 px-1"
                                        onclick="togglePassword()"
                                        title="{{ __('Show / Hide Password') }}">
                                        {{ __('Show / Hide Password') }}
                                    </button>
                                </div>

                                @error('password')
                                <span class="invalid-feedback text-red-500" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>

                            {{-- Confirm Password --}}
                            <div class="w-full p-2">
                                <p class="mb-2.5 font-medium text-base">{{ __('Confirm Password') }} <span class="text-red-500">*</span></p>
                                <div class="p-px bg-gradient-to-r from-{{ $template_config->template_color }}-400 to-{{ $template_config->template_color }}-500 rounded-lg">
                                    <input type="password" name="password_confirmation" id="password_confirmation" required autocomplete="new-password" placeholder="********"
                                        class="w-full px-6 py-4 placeholder-gray-500 text-base outline-none rounded-lg">
                                </div>
                            </div>

                            {{-- Referral Code --}}
                            @if ($config[80]->config_value == '1')
                                <div class="w-full p-2">
                                    <p class="mb-2.5 font-medium text-base">{{ __('Referral Code') }}</p>
                                    <div class="p-px bg-gradient-to-r from-{{ $template_config->template_color }}-400 to-{{ $template_config->template_color }}-500 rounded-lg">
                                        <input type="text" name="referral_code" id="referral_code" value="{{ Cookie::get('referral_code') ?? request()->get('ref') }}" autocomplete="referral_code"
                                            placeholder="FNKLJ2156DV"
                                            class="w-full px-6 py-4 placeholder-gray-500 text-base outline-none rounded-lg uppercase @error('referral_code') is-invalid @enderror">
                                    </div>
                                    @error('referral_code') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                            @endif

                            {{-- Recaptcha --}}
                            @if ($settings->recaptcha_configuration['RECAPTCHA_ENABLE'] == 'on')
                                <div class="w-full p-2">
                                    {!! htmlFormSnippet() !!}
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
                                            <a class="hover:text-gray-800 hover:font-bold" href="{{ route('terms.and.conditions') }}">{{ __('Terms & Conditions') }}</a>
                                        @else
                                            <a class="hover:text-gray-800 hover:font-bold" href="#">{{ __('Terms & Conditions') }}</a>
                                        @endif
                                        {{ __('of') }} {{ config('app.name') }}.
                                    </p>
                                </div>
                            </div>
                            @error('terms') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        {{-- Submit Button with effects --}}
                        <div class="group relative md:max-w-max my-5">
                            <div class="absolute top-0 left-0 w-full h-full bg-gradient-to-r from-{{ $template_config->template_color }}-400 to-{{ $template_config->template_color }}-500 opacity-0 group-hover:opacity-50 rounded-full transition ease-out duration-300"></div>
                            <button
                                type="submit"
                                id="registerButton"
                                onclick="disableRegisterButton(this)"
                                data-wait-text="{{ __('Please wait, signing up...') }}"
                                class="p-1 w-full font-heading font-semibold text-xs text-white uppercase tracking-px overflow-hidden rounded-full">
                                <div class="relative py-5 px-14 bg-gradient-to-r from-{{ $template_config->template_color }}-400 to-{{ $template_config->template_color }}-500 overflow-hidden rounded-full">
                                    <div class="absolute top-0 left-0 transform -translate-y-full group-hover:-translate-y-0 h-full w-full bg-white transition ease-in-out duration-500"></div>
                                    <p class="relative z-10 group-hover:text-gray-900">{{ __('Sign Up') }}</p>
                                </div>
                            </button>
                        </div>
                    </form>

                    {{-- Signin with Google --}}
                    @if ($settings->google_configuration['GOOGLE_ENABLE'] == 'on')
                    <h2 class="mb-3 font-heading font-bold text-2xl sm:text-2xl text-center sm:text-left">{{ __('Or sign up instantly with Google') }}</h2>
                        <div class="group relative md:max-w-max mb-5">
                            <div
                                class="absolute top-0 left-0 w-full h-full bg-gradient-to-r from-{{ $template_config->template_color }}-400 to-{{ $template_config->template_color }}-500 opacity-0 group-hover:opacity-50 rounded-full transition ease-out duration-300">
                            </div>
                            <a href="{{ route('login.google') }}">
                                <button
                                    class="p-1 w-full font-heading font-semibold text-xs text-white uppercase tracking-px overflow-hidden rounded-full">
                                    <div class="relative flex py-5 px-14 bg-gradient-to-r from-{{ $template_config->template_color }}-400 to-{{ $template_config->template_color }}-500 overflow-hidden rounded-full">
                                        <i class="ti ti-brand-google brand-google items-center px-3"></i> {{ __('Continue with Google') }}
                                    </div>
                                </button>
                            </a>
                        </div>
                    @endif
                    <p class="text-gray-500 text-sm">
                        <span>{{ __('Already have an account?') }}</span>
                        <a class="hover:text-gray-800 hover:font-bold" href="{{ route('login') }}">{{ __('Login now') }}</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Custom JS --}}
@section('custom-js')
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