@php
// Page content
use App\Services\GoBizCommonService;

$config = GoBizCommonService::config();
$supportPage = GoBizCommonService::templatePageContentGet('contact', 'GoBizOriginal');

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

@extends('GoBizOriginal::Website.layouts.index', ['nav' => $navbar, 'banner' => false, 'footer' => $footer, 'cookie' => false, 'setting' => true,
'title' => true, 'title' => __('Sign In')])
 
@section('content')
{{-- Login page --}}
<section class="pt-12 lg:pb-20 overflow-hidden">
    <div class="container mx-auto px-4">
        <div class="flex flex-wrap items-center -m-6">
            <div class="w-full md:w-1/2 p-6 md:block lg:block xl:block hidden mobile-banner">
                <div class="p-1 mx-auto max-w-max overflow-hidden rounded-full">
                    <img class="object-cover rounded-full" src="{{ asset($template_config->auth_image) }}" alt="{{ $config[0]->config_value }}">
                </div>
            </div>
            <div class="w-full md:w-1/2 p-6 order-1 md:order-2">
                <div class="md:max-w-md">
                    <h2 class="mb-3 font-heading font-bold text-6xl sm:text-7xl">{{ __('Sign In') }}</h2>
                    <p class="mb-8 text-lg">{{ __('Sign in your account') }}</p>

                    {{-- Alert --}}
                    @if(session('error'))
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 mb-5 rounded relative" role="alert">
                            <strong class="font-bold">{{ __('Error!') }}</strong>
                            <span class="block sm:inline">{{ session('error') }}</span>
                        </div>
                    @endif

                    {{-- Status --}}
                    @if (session('status'))
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 mb-5 rounded relative" role="alert">
                            <span class="block sm:inline">{{ session('status') }}</span>
                        </div>
                    @endif

                    {{-- Deleted account message --}}
                    @if (session('deleted_account_message'))
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 mb-5 rounded relative" role="alert">
                            <span class="block sm:inline">{!! session('deleted_account_message') !!}</span>
                        </div>
                    @endif
                    
                    {{-- Login --}}
                    <form method="POST" action="{{ route('login') }}">
                        @csrf
                        <div class="flex flex-wrap -m-2 mb-6">
                            {{-- Email address --}}
                            <div class="w-full p-2 mb-2">
                                <label for="email" class="block mb-2.5 font-medium text-base text-gray-800">
                                    {{ __('Email address') }} <span class="text-red-500">*</span>
                                </label>

                                <div class="relative p-px bg-gradient-to-r from-{{ $template_config->template_color }}-400 to-{{ $template_config->template_color }}-500 rounded-lg">
                                    <input
                                        type="email"
                                        name="email"
                                        id="email"
                                        value="{{ old('email') }}"
                                        required
                                        autocomplete="email"
                                        autofocus
                                        placeholder="{{ __('Your email address') }}"
                                        class="w-full px-6 py-4 placeholder-gray-500 text-base text-gray-900 outline-none focus:ring-4 focus:ring-{{ $template_config->template_color }}-300 rounded-lg @error('email') border-red-500 @enderror"
                                    >
                                </div>

                                @error('email')
                                <span class="invalid-feedback text-red-500" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
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
                                        onclick="togglePasswordVisibility()"
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

                            {{-- Redirect --}}
                            @if (request('redirect'))
                                <input type="hidden" name="redirect" value="{{ request('redirect') }}">
                            @endif

                            {{-- Recaptcha --}}
                            @if ($settings->recaptcha_configuration['RECAPTCHA_ENABLE'] == 'on')
                            <div class="w-full p-2">
                                {!! htmlFormSnippet() !!}
                            </div>
                            @endif
                        </div>

                        {{-- Forget password --}}
                        <div class="flex flex-wrap -m-1.5 mb-5">
                            <div class="flex-1 p-1.5">
                                <a class="underline hover:text-gray-500" href="{{ route('password.request') }}">{{ __('Forgot Your Password?') }}</a>
                            </div>
                        </div>

                        <div class="group relative md:max-w-max mb-5">
                            <div
                                class="absolute top-0 left-0 w-full h-full bg-gradient-to-r from-{{ $template_config->template_color }}-400 to-{{ $template_config->template_color }}-500 opacity-0 group-hover:opacity-50 rounded-full transition ease-out duration-300">
                            </div>
                            <button type="submit"
                                id="loginButton" onclick="disableButton(this)" data-wait-text="{{ __('Please wait, login is being processed.') }}" class="p-1 w-full font-heading font-semibold text-xs text-white uppercase tracking-px overflow-hidden rounded-full">
                                <div class="relative py-5 px-14 bg-gradient-to-r from-{{ $template_config->template_color }}-400 to-{{ $template_config->template_color }}-500 overflow-hidden rounded-full">
                                    <div
                                        class="absolute top-0 left-0 transform -translate-y-full group-hover:-translate-y-0 h-full w-full bg-white transition ease-in-out duration-500">
                                    </div>
                                    <p class="relative z-10 group-hover:text-gray-900">{{ __('Login') }}</p>
                                </div>
                            </button>
                        </div> 
                    </form>

                    {{-- Signin with Google --}}
                    @if ($settings->google_configuration['GOOGLE_ENABLE'] == 'on')
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

                    @if(Route::has('register'))
                    <p class="text-gray-500 text-sm">
                        <span>{{ __('If you do not have an account?') }}</span>
                        <a class="hover:text-gray-800 hover:font-bold" href="{{ route('register') }}">{{ __('Create free account') }}</a>
                    </p>
                    @endif

                    {{-- Email Verification Link --}}
                    @if ($config[43]->config_value == "1")
                        <p class="text-gray-500 text-sm">
                            <span>{{ __('If you not verified your email address?') }}</span>
                            <a class="hover:text-gray-800 hover:font-bold" href="{{ route('verify.public') }}">{{ __('Verify Email Address') }}</a>
                        </p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Custom JS --}}
@section('custom-js')
<script>
function togglePasswordVisibility() {
    "use strict";

    const input = document.getElementById("password");
    input.type = input.type === "password" ? "text" : "password";
}

// Disable button
function disableButton(btn) {
    const email = document.querySelector('input[name="email"]');
    const password = document.querySelector('input[name="password"]');

    // Check if email and password are empty
    if (!email.value.trim() || !password.value.trim()) {
        return false;
    }

    // Update <p> text
    const buttonText = btn.querySelector('p');
    if (buttonText) {
        const waitText = btn.getAttribute('data-wait-text') || `{{ __('Please wait...') }}`;
        buttonText.textContent = waitText;
        buttonText.style.color = {{ $template_config->template_color }}; // white text
    }

    // Update gradient background
    const buttonBackground = btn.querySelector('div.bg-gradient-to-r');
    if (buttonBackground) {
        // Either add Tailwind classes (make sure they're safelisted)...
        buttonBackground.classList.remove('from-emerald-400', 'to-emerald-500');
        buttonBackground.classList.add('from-gray-400', 'to-gray-500');

        // ...OR use inline style (more reliable):
        buttonBackground.style.background = 'linear-gradient(to right, #d1d5db, #9ca3af)'; // gray-300 to gray-400
    }

    // Optionally disable the button to prevent multiple clicks
    btn.disabled = true;
}
</script>
@endsection
@endsection