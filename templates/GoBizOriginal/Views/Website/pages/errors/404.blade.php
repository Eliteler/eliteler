@php
    // Fetch service configurations at the top
    use App\Services\GoBizCommonService;

    $supportPage = GoBizCommonService::templatePageContentGet('contact', 'GoBizOriginal');
    $config = GoBizCommonService::config();
    $settings = GoBizCommonService::settings();

    // Check if website status is set to 'yes'
    $showWebsite = isset($config[38]->config_value) && strtolower($config[38]->config_value) === 'yes';

    // Set redirect URL: If website is enabled use '/', otherwise use custom redirect URL
    $homepageUrl = $showWebsite ? '/' : (!empty($config[101]->config_value) ? $config[101]->config_value : '/');
@endphp

@extends('GoBizOriginal::Website.layouts.index', [
    'nav' => $showWebsite,
    'banner' => false,
    'footer' => $showWebsite,
    'cookie' => false,
    'setting' => false,
    'title' => __('404 - Page Not Found'),
])

@section('content')
    {{-- Left-aligned and vertically centered 404 content --}}
    <section class="overflow-hidden py-12" style="min-height: 85vh; display: flex; align-items: center;">
        <div class="relative container mx-auto lg:px-24 px-4 w-full">
            <div class="relative z-10">
                {{-- 404 Gradient Heading --}}
                <h2
                    class="lg:mb-8 mb-6 max-w-max font-heading font-bold text-7xl sm:text-8xl text-transparent bg-clip-text bg-gradient-to-r from-{{ $template_config->template_color }}-400 to-{{ $template_config->template_color }}-500">
                    {{ __('404') }}
                </h2>

                <div class="md:max-w-xl">
                    <p class="mb-4 font-heading font-semibold text-xs text-gray-500 uppercase tracking-px">
                        {{ __('Nothing found') }}
                    </p>
                    <h2 class="mb-5 font-heading font-bold text-5xl sm:text-6xl text-gray-900 leading-tight">
                        {{ __('The page you are looking for is not available!') }}
                    </h2>
                    <p class="mb-8 text-gray-600 text-lg">
                        {{ __('Try something different or go back to the homepage.') }}
                    </p>

                    {{-- Dynamic Redirect Button --}}
                    <a href="{{ $homepageUrl }}"
                        class="group relative font-heading py-4 px-8 inline-block text-xs text-white font-semibold uppercase bg-gray-900 overflow-hidden rounded">
                        <div
                            class="absolute top-0 left-0 transform -translate-x-full group-hover:-translate-x-0 h-full w-full transition ease-in-out duration-500 bg-gray-800">
                        </div>
                        <span class="relative z-10">{{ __('Go back to Homepage') }}</span>
                    </a>
                </div>
            </div>
        </div>
    </section>
@endsection
