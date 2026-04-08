@extends('GoBizOriginal::Website.layouts.index', ['nav' => true, 'banner' => false, 'footer' => true, 'cookie' => false, 'setting' => false,
'title' => true, 'title' => __('419 - Page Expired')])

@php
// Page content
use App\Services\GoBizCommonService;

$supportPage = GoBizCommonService::templatePageContentGet('contact', 'GoBizOriginal');
$config = GoBizCommonService::config();
$settings = GoBizCommonService::settings();
@endphp

@section('content')
{{-- 404 page --}}
<section class="pt-48 lg:pb-20 overflow-hidden">
    <div class="relative container mx-auto lg:px-24 px-4">
        <div class="relative z-10">
            <h2 class="lg:mb-12 mb-12 max-w-max font-heading font-bold text-7xl text-transparent bg-clip-text bg-gradient-to-r from-{{ $template_config->template_color }}-400 to-{{ $template_config->template_color }}-500">
                {{ __('Page Session Expired') }}
            </h2>
            <div class="md:max-w-xl">
                <button class="group relative font-heading py-5 px-9 block w-full md:w-auto text-xs text-white font-semibold uppercase bg-gray-900 overflow-hidden rounded">
                    <a href="/">
                        <div class="absolute top-0 left-0 transform -translate-x-full group-hover:-translate-x-0 h-full w-full transition ease-in-out duration-500 bg-gray-800">
                        </div>
                        <p class="relative z-10">{{ __('Go back to Homepage') }}</p>
                    </a>
                </button>
            </div>
        </div>
    </div>
</section>
@endsection