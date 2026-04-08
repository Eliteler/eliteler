@php
// Page content
use App\Services\GoBizCommonService;

// Get settings
$settings = GoBizCommonService::settings();
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

@extends('GoBizOriginal::Website.layouts.index', ['nav' => $navbar, 'banner' => false, 'footer' => $footer, 'cookie' => false, 'setting' => true,
'title' => true, 'title' => __('Verify')])

@section('custom-script')
<link rel="icon" href="{{ asset($settings->favicon) }}" sizes="96x96" type="image/png" />
@endsection

@section('content')
{{-- Verify page --}}
<section class="pt-12 lg:pb-20 overflow-hidden">
    <div class="container mx-auto px-4">
        <div class="flex flex-wrap items-center -m-6">
            {{-- Left image --}}
            <div class="w-full md:w-1/2 p-6 lg:block hidden mobile-banner">
                <div class="p-1 mx-auto max-w-max overflow-hidden rounded-full">
                    <img class="object-cover rounded-full" src="{{ asset($template_config->auth_image) }}" alt="{{ $config[0]->config_value }}">
                </div>
            </div>

            {{-- Right form --}}
            <div class="w-full md:w-1/2 p-6 order-1 md:order-2">
                <div class="md:max-w-md">
                    <h2 class="mb-8 font-heading font-bold text-3xl sm:text-3xl">{{ __('Verify Your Email Address') }}</h2>

                    {{-- Error --}}
                    @if(session('error'))
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 mb-5 rounded relative" role="alert">
                            <strong class="font-bold">{{ __('Error!') }}</strong>
                            <span class="block sm:inline">{{ session('error') }}</span>
                        </div>
                    @endif

                    {{-- Message --}}
                    @if (session('message'))
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 mb-5 rounded relative" role="alert">
                            <span class="block sm:inline">{{ session('message') }}</span>
                        </div>
                    @endif

                    {{-- Status --}}
                    @if (session('status'))
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 mb-5 rounded relative" role="alert">
                            <span class="block sm:inline">{{ session('status') }}</span>
                        </div>
                    @endif

                    <p class="mb-8 text-lg">{{ __('A verification link will be sent to your email. Enter your email below to resend the verification email.') }}</p>

                    {{-- Public Verification form --}}
                    <form method="POST" action="{{ route('verification.resend.public') }}">
                        @csrf
                        <div class="group relative mb-5">
                            <div class="w-full p-2">
                                <p class="mb-2.5 font-medium text-base">
                                    {{ __('Email address') }} <span class="text-red-500">*</span>
                                </p>
                                <div class="p-px bg-gradient-to-r from-{{ $template_config->template_color }}-400 to-{{ $template_config->template_color }}-500 rounded-lg">
                                    <input type="email" 
                                        name="email" 
                                        placeholder="{{ __('Your email address') }}" 
                                        required 
                                        autocomplete="email"
                                        value="{{ old('email') }}"
                                        class="w-full px-6 py-4 placeholder-gray-500 text-base text-gray-900 outline-none rounded-lg bg-white @error('email') border border-red-500 @enderror">
                                </div>
                                @error('email') 
                                    <span class="text-red-500 text-sm">{{ $message }}</span> 
                                @enderror
                            </div>

                            <button type="submit" class="p-1 w-full font-heading font-semibold text-xs text-white uppercase tracking-px overflow-hidden rounded-full">
                                <div class="relative py-5 px-14 bg-gradient-to-r from-{{ $template_config->template_color }}-400 to-{{ $template_config->template_color }}-500 overflow-hidden rounded-full">
                                    <div class="absolute top-0 left-0 transform -translate-y-full group-hover:-translate-y-0 h-full w-full bg-gradient-to-r from-{{ $template_config->template_color }}-700 to-{{ $template_config->template_color }}-800 transition ease-in-out duration-500"></div>
                                    <p class="relative z-10 group-hover:text-gray-50">{{ __('click here to request another') }}</p>
                                </div>
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</section>
@endsection