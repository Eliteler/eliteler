<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @if(isset($business_card_details->seo_configurations) && json_decode($business_card_details->seo_configurations)->favicon != null)
        <link rel="icon" href="{{ url(json_decode($business_card_details->seo_configurations)->favicon) }}" sizes="512x512" type="image/png" />
        <link rel="apple-touch-icon" href="{{ url(json_decode($business_card_details->seo_configurations)->favicon) }}">
    @else
        <link rel="icon" href="{{ url($business_card_details->profile) }}" sizes="512x512" type="image/png" />
        <link rel="apple-touch-icon" href="{{ url($business_card_details->profile) }}">
    @endif

    <meta name="theme-color" content="indigo" />

    <!-- Add to homescreen for Chrome on Android -->
    <meta name="application-name" content="{{ $card_details->title }}">

    <!-- Add to homescreen for Safari on iOS -->
    <meta name="apple-mobile-web-app-title" content="{{ $card_details->title }}">

    <!-- Tile for Win8 -->
    <meta name="msapplication-TileColor" content="indigo">
    <meta name="msapplication-TileImage" content="{{ url($business_card_details->profile) }}">

    {!! SEOMeta::generate() !!}
    {!! OpenGraph::generate() !!}
    {!! Twitter::generate() !!}
    {!! JsonLd::generate() !!}

    <!-- CSS files -->
    <link rel="stylesheet" href="{{ url('app/css/tailwind.min.css') }}">
    <link rel="stylesheet" href="{{ url('templates/css/template-3.css') }}" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <link rel="stylesheet" href="{{ url('css/fontawesome.min.css') }}" />
    <script type="text/javascript" src="{{ url('js/sweetalert.min.js') }}"></script>

    <!-- Include the qrious library -->
    <script src="{{ url('js/qrious.min.js') }}"></script>

    <!-- Flatpickr CSS -->
    <link href="{{ url('css/flatpickr.min.css') }}" rel="stylesheet">
    {{-- Check business details --}}
    @if ($business_card_details != null)
        @php
            $custom_css = $business_card_details->custom_css;
            $custom_js = $business_card_details->custom_js;

            // Ensure <style> tags for custom CSS
            if (strpos($custom_css, '<style>') === false && strpos($custom_css, '</style>') === false) {
                $custom_css = "<style>" . $custom_css . "</style>";
            }

            // Ensure <script> tags for custom JS
            if (strpos($custom_js, '<script>') === false && strpos($custom_js, '</script>') === false) {
                $custom_js = "<script>" . $custom_js . "</script>";
            }
        @endphp

        {!! $custom_css !!}
        {!! $custom_js !!}

        {{-- Theme CSS --}}
        @if(!empty($business_card_details->theme_css))
            <style>
                {!! $business_card_details->theme_css !!}
            </style>
        @endif

        {{-- Theme JS --}}
        @if(!empty($business_card_details->theme_js))
            <script>
                {!! $business_card_details->theme_js !!}
            </script>
        @endif
    @endif

    {{-- JS files --}}
    <script src="{{ url('templates/js/spotlight.bundle.js') }}"></script>

    {{-- Check PWA --}}
    @if ($plan_details != null)
        @if ($plan_details['pwa'] == 1 && $business_card_details->is_enable_pwa == 1)
            @laravelPWA

            <!-- Web Application Manifest -->
            <link rel="manifest" href="{{ $manifest }}">
        @endif
    @endif
</head>

@php
    use Illuminate\Support\Facades\Session;
@endphp

<body class="antialiased text-body font-body lg:bg-indigo-500"
    dir="{{ App::isLocale('ar') || App::isLocale('ur') || App::isLocale('he') ? 'rtl' : 'ltr' }}">

    {{-- Check password protected --}}
    @if ($business_card_details->password == null || Session::get('password_protected') == true)
        {{-- Check business details --}}
        @if ($business_card_details != null)
            <section class="lg:py-5">
                <div class="lg:w-8/12 px-2 mx-auto">
                    <div class="flex flex-wrap template-3 -m-2">
                        <div class="w-full lg:w-1/2 bg-white p-2">

                            {{-- Language Switcher --}}
                            @if ($business_card_details->is_enable_language_switcher == 1 && is_array(config('app.languages')) && count(config('app.languages')) > 1)
                            @include('templates.includes.language-switcher')
                            @endif
                            
                            <!-- Profile -->
                            <div
                                class="relative {{ $business_card_details->cover_type == 'photo' ? 'py-2' : '' }} px-3 my-2 shadow-lg">
                                <div class="relative flex flex-wrap {{ $business_card_details->cover_type == 'photo' ? '' : 'pt-14' }} rounded overflow-hidden">
                                    @if ($business_card_details->cover_type == 'photo')
                                        <!-- Show full image without crop -->
                                        <div class="w-full overflow-hidden bg-gray-100">
                                            <img class="w-full h-auto object-contain"
                                                src="{{ $business_card_details->cover ? url($business_card_details->cover) : asset('images/default-cover.png') }}"
                                                alt="Cover Image" />
                                        </div>
                                    @endif

                                    <!-- Profile image overlap -->
                                    <div class="relative w-full flex justify-start">
                                        <img class="w-32 h-32 rounded-full border border-indigo-50 z-10 template-3-profile-image"
                                            src="{{ url($business_card_details->profile) }}"
                                            alt="{{ $business_card_details->title }}">
                                    </div>
                                </div>

                                <div class="mt-3 mb-7">
                                    {{-- Business name --}}
                                    <div class="relative my-3">
                                        <div class="flex">
                                            <h2 class="font-medium">{{ $business_card_details->title }}</h2>
                                        </div>
                                        <p class="text-sm text-gray-500">{{ $card_details->sub_title }}</p>
                                    </div>

                                    {{-- Business description --}}
                                    @if ($business_card_details->description != null)
                                        <div class="text-md text-left mt-5">
                                            {!! $business_card_details->description !!}
                                        </div>
                                    @endif
                                </div>

                                <!-- Details -->
                                @if (count($feature_details) > 0)
                                    <div class="block max-w-full py-2 mb-6">
                                        @foreach ($feature_details as $feature)
                                            {{-- Email --}}
                                            @if ($feature->type == 'email')
                                                <a class="break-word" href="mailto:{{ $feature->content }}">
                                            @endif

                                            {{-- Tel --}}
                                            @if ($feature->type == 'tel')
                                                <a class="break-word" href="tel:{{ $feature->content }}">
                                            @endif

                                            {{-- WhatsApp --}}
                                            @if ($feature->type == 'wa')
                                                <a class="break-word" href="http://wa.me/{{ $feature->content }}"
                                                    rel="noopener nofollow noreferrer" target="_blank">
                                            @endif

                                            {{-- URL --}}
                                            @if (
                                                $feature->type == 'url' ||
                                                    $feature->type == 'facebook' ||
                                                    $feature->type == 'instagram' ||
                                                    $feature->type == 'x-twitter' ||
                                                    $feature->type == 'linkedin' ||
                                                    $feature->type == 'pinterest' ||
                                                    $feature->type == 'reddit' ||
                                                    $feature->type == 'tiktok' ||
                                                    $feature->type == 'threads' ||
                                                    $feature->type == 'snapchat' ||
                                                    $feature->type == 'wechat' ||
                                                    $feature->type == 'telegram' ||
                                                    $feature->type == 'tumblr' ||
                                                    $feature->type == 'qq' ||
                                                    $feature->type == 'discord' ||
                                                    $feature->type == 'quora')
                                                <a class="break-all"
                                                    href="https://{{ str_replace('https://', '', $feature->content) }}"
                                                    rel="noopener nofollow noreferrer" target="_blank">
                                            @endif

                                            {{-- Youtube --}}
                                            @if ($feature->type != 'iframe' && $feature->type != 'youtube' && $feature->type != 'map' && $feature->type != 'text')
                                                <div
                                                    class="flex {{ $loop->last ? '' : ' mb-3' }} justify-between
                                                items-center">
                                                    <div class="flex items-center">
                                                        <div class="w-1/8">
                                                            {{-- Icon --}}
                                                            <span
                                                                class="flex justify-center items-center content-center bg-indigo-500 hover:bg-indigo-600 shadow-md hover:shadow-lg h-10 w-10 mr-3 rounded fill-current text-white">
                                                                <i class="{{ $feature->icon }}"></i>
                                                            </span>
                                                        </div>
                                                        <div class="w-full">
                                                            <h4 class="text-md font-bold text-gray-800">
                                                                {{ $feature->label }}</h4>
                                                            <p class="font-semibold text-gray-800 break-word text-sm">
                                                                {{ $feature->content }}</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif

                                            @if (
                                                $feature->type == 'url' ||
                                                    $feature->type == 'facebook' ||
                                                    $feature->type == 'instagram' ||
                                                    $feature->type == 'x-twitter' ||
                                                    $feature->type == 'linkedin' ||
                                                    $feature->type == 'pinterest' ||
                                                    $feature->type == 'reddit' ||
                                                    $feature->type == 'tiktok' ||
                                                    $feature->type == 'threads' ||
                                                    $feature->type == 'snapchat' ||
                                                    $feature->type == 'wechat' ||
                                                    $feature->type == 'telegram' ||
                                                    $feature->type == 'tumblr' ||
                                                    $feature->type == 'qq' ||
                                                    $feature->type == 'discord' ||
                                                    $feature->type == 'quora' ||
                                                    $feature->type == 'wa' ||
                                                    $feature->type == 'tel' ||
                                                    $feature->type == 'email')
                                                </a>
                                            @endif
                                        @endforeach
                                    </div>
                                @endif

                                <!-- Actions -->
                                <div class="flex flex-wrap -mx-2">
                                    <!-- Add to Contact -->
                                    <div class="w-1/2 md:w-1/3 px-2 mb-2 md:mb-0 hidden lg:block md:block">
                                        <a class="flex justify-center py-2 text-sm text-white bg-indigo-500 hover:bg-indigo-600 rounded transition duration-200"
                                            href="{{ config('app.url') }}/download/{{ $business_card_details->card_id }}">
                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                class="icon icon-tabler icon-tabler-address-book" width="24"
                                                height="24" viewBox="0 0 24 24" stroke-width="2"
                                                stroke="currentColor" fill="none" stroke-linecap="round"
                                                stroke-linejoin="round">
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                                <path
                                                    d="M20 6v12a2 2 0 0 1 -2 2h-10a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2h10a2 2 0 0 1 2 2z">
                                                </path>
                                                <path d="M10 16h6"></path>
                                                <path d="M13 11m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"></path>
                                                <path d="M4 8h3"></path>
                                                <path d="M4 12h3"></path>
                                                <path d="M4 16h3"></path>
                                            </svg>
                                        </a>
                                    </div>

                                    <!-- QR -->
                                    <div class="w-1/2 md:w-1/3 px-2 mb-2 md:mb-0">
                                        <a class="flex qr-modal-open justify-center py-2 text-sm text-white bg-indigo-500 hover:bg-indigo-600 rounded transition duration-200"
                                            href="#">
                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                class="icon icon-tabler icon-tabler-qrcode" width="24"
                                                height="24" viewBox="0 0 24 24" stroke-width="2"
                                                stroke="currentColor" fill="none" stroke-linecap="round"
                                                stroke-linejoin="round">
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                                <path
                                                    d="M4 4m0 1a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v4a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1z">
                                                </path>
                                                <path d="M7 17l0 .01"></path>
                                                <path
                                                    d="M14 4m0 1a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v4a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1z">
                                                </path>
                                                <path d="M7 7l0 .01"></path>
                                                <path
                                                    d="M4 14m0 1a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v4a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1z">
                                                </path>
                                                <path d="M17 7l0 .01"></path>
                                                <path d="M14 14l3 0"></path>
                                                <path d="M20 14l0 .01"></path>
                                                <path d="M14 14l0 3"></path>
                                                <path d="M14 20l3 0"></path>
                                                <path d="M17 17l3 0"></path>
                                                <path d="M20 17l0 3"></path>
                                            </svg>
                                        </a>
                                    </div>

                                    <!-- Send -->
                                    <div class="w-1/2 md:w-1/3 px-2 mb-2 md:mb-0">
                                        <a class="flex send-modal-open justify-center py-2 text-sm text-white bg-indigo-500 hover:bg-indigo-600 rounded transition duration-200"
                                            href="#">
                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                class="icon icon-tabler icon-tabler-send" width="24"
                                                height="24" viewBox="0 0 24 24" stroke-width="2"
                                                stroke="currentColor" fill="none" stroke-linecap="round"
                                                stroke-linejoin="round">
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                                <path d="M10 14l11 -11"></path>
                                                <path
                                                    d="M21 3l-6.5 18a.55 .55 0 0 1 -1 0l-3.5 -7l-7 -3.5a.55 .55 0 0 1 0 -1l18 -6.5">
                                                </path>
                                            </svg>
                                        </a>
                                    </div>
                                </div>
                            </div>

                            {{-- Custom Text --}}
                            @if ($customTexts != null && !$customTexts->isEmpty())
                                @foreach ($customTexts as $customText)
                                    <div class="py-5 px-3 my-2 shadow-lg">
                                        <h2 class="w-full md:w-auto mt-4 mb-2 md:mb-0 text-2xl font-bold">
                                            {{ __($customText->label) }}
                                        </h2>
                                        <p class="mt-4 font-semibold break-word">{{ $customText->content }}</p>
                                    </div>
                                @endforeach
                            @endif

                            <!-- Services -->
                            @if (count($service_details) > 0 && !$service_details->isEmpty())
                                <div class="py-5 px-3 my-2 shadow-lg">
                                    <h2 class="w-full md:w-auto mt-4 mb-2 md:mb-0 text-2xl font-bold">
                                        {{ __($service_details[0]->title) }}
                                    </h2>

                                    <!-- Slider Container -->
                                    <div class="swiper services mt-4">
                                        <div class="swiper-wrapper mb-3">
                                            @foreach ($service_details as $service_detail)
                                                <div class="swiper-slide pb-4">
                                                    <div class="flex flex-col justify-between bg-white shadow rounded-lg overflow-hidden h-350 p-2">
                                                        <!-- Image -->
                                                        <img src="{{ url($service_detail->service_image) }}"
                                                            alt="{{ $service_detail->service_name }}"
                                                            class="w-full h-48 object-cover rounded" />

                                                        <!-- Content -->
                                                        <div class="flex-1 mt-4">
                                                            {{-- Service Name --}}
                                                            <h3 class="text-xl font-semibold mb-1 service-name text-left">
                                                                {{ $service_detail->service_name }}
                                                            </h3>
                                                            <p class="text-sm text-gray-500 text-left"
                                                            title="{{ $service_detail->service_description }}">
                                                                {{ strlen($service_detail->service_description) > 130
                                                                    ? substr($service_detail->service_description, 0, 130) . '...'
                                                                    : $service_detail->service_description }}
                                                            </p>
                                                        </div>

                                                        <!-- Button -->
                                                        @if ($enquiry_button && $whatsAppNumberExists && $service_detail->enable_enquiry == 'Enabled')
                                                            <div class="mt-4">
                                                                <a href="https://wa.me/{{ $enquiry_button }}?text={{ __('Hi, I am interested in your service:') }} {{ $service_detail->service_name }}. {{ __('Please provide more details.') }}"
                                                                class="block w-full py-2 bg-indigo-500 hover:bg-indigo-600 text-white text-sm text-center rounded"
                                                                target="_blank">
                                                                    {{ __('Make WhatsApp Inquiry') }}
                                                                </a>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>

                                        <!-- Swiper Navigation -->
                                        <div class="swiper-button-next"></div>
                                        <div class="swiper-button-prev"></div>
                                    </div>
                                </div>
                            @endif

                            <!-- Products -->
                            @if (count($product_details) > 0 && !$product_details->isEmpty())
                                <div class="py-5 px-3 my-2 shadow-lg">
                                    <h2 class="w-full md:w-auto mt-2 mb-2 md:mb-0 text-2xl font-bold">
                                        {{ __($product_details[0]->title) }}
                                    </h2>
                                    <div class="flex flex-wrap -mx-2">
                                        <!-- Swiper container -->
                                        <div class="swiper products">
                                            <div class="swiper-wrapper mb-3">
                                                @foreach ($product_details as $product_detail)
                                                    <div class="swiper-slide px-2 pb-4">
                                                        <div class="flex flex-col bg-white shadow rounded-lg overflow-hidden h-400 p-4">
                                                            <!-- Product Image -->
                                                            <img src="{{ url($product_detail->product_image) }}"
                                                                alt="{{ $product_detail->product_name }}"
                                                                class="w-full h-48 object-cover rounded" />

                                                            <!-- Content -->
                                                            <div class="flex-1 mt-4">
                                                                <h3 class="text-xl font-semibold mb-1 product-name">
                                                                    {{ $product_detail->product_name }}
                                                                </h3>
                                                                <p class="text-sm text-gray-500 mb-1 product-desc" title="{{ $product_detail->product_description }}">
                                                                    {{ strlen($product_detail->product_description) > 140
                                                                        ? substr($product_detail->product_description, 0, 140) . '...'
                                                                        : $product_detail->product_description }}
                                                                </p>

                                                                @if ($product_detail->sales_price != 0)
                                                                <p class="text-sm text-gray-700 font-bold text-left">
                                                                    <span id="{{ $product_detail->product_id }}_currency"></span>
                                                                    <span id="{{ $product_detail->product_id }}_price">{{ formatCurrencyVcard($product_detail->sales_price, $product_detail->currency) }}</span>
                                                                    @if ($product_detail->sales_price != $product_detail->regular_price)
                                                                        <span class="text-xs line-through text-indigo-500 font-bold ml-1">
                                                                            {{ formatCurrencyVcard($product_detail->regular_price, $product_detail->currency) }}
                                                                        </span>
                                                                    @endif
                                                                </p>
                                                                @endif
                                                            </div>

                                                            <!-- Enquiry Button -->
                                                            @if ($enquiry_button && $whatsAppNumberExists && $product_detail->product_status == 'instock')
                                                                <div class="mt-4">
                                                                    <a href="https://wa.me/{{ $enquiry_button }}?text={{ __('Hi, I am interested in your product:') }} {{ $product_detail->product_name }}. {{ __('Please provide more details.') }}"
                                                                        class="block w-full py-2 bg-indigo-500 hover:bg-indigo-600 text-white text-sm text-center rounded"
                                                                        target="_blank">
                                                                        {{ __('Make WhatsApp Inquiry') }}
                                                                    </a>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>

                                            <!-- Swiper navigation -->
                                            <div class="swiper-button-next"></div>
                                            <div class="swiper-button-prev"></div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <!-- Gallery -->
                            @if (count($galleries_details) > 0 && !$galleries_details->isEmpty())
                                <div class="py-5 px-3 my-2 shadow-lg">
                                    <h2 class="w-full md:w-auto mt-2 mb-2 md:mb-0 text-2xl font-bold">
                                        {{ __($galleries_details[0]->title) }}
                                    </h2>
                                    <div class="flex flex-wrap -mx-3 px-3">
                                        <!-- Images -->
                                        @foreach ($galleries_details as $galleries_detail)
                                            <div class="w-1/2 bg-white border border-gray-50 rounded-lg shadow my-1">
                                                <a class="spotlight"
                                                    href="{{ url($galleries_detail->gallery_image) }}">
                                                    <img class="rounded-t-lg"
                                                        src="{{ url($galleries_detail->gallery_image) }}"
                                                        alt="{{ $galleries_detail->caption }}" />
                                                </a>
                                                @if ($galleries_detail->caption)
                                                    <div class="p-3">
                                                        <h5
                                                            class="mb-2 text-d font-bold tracking-tight text-gray-900 text-center">
                                                            {{ $galleries_detail->caption }}</h5>
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            {{-- Testimonials --}}
                            @if ($testimonials != null && !$testimonials->isEmpty())
                                <section class="py-5 px-3 my-2 shadow-lg">
                                    <h2 class="w-full md:w-auto mt-2 mb-2 md:mb-0 text-2xl font-bold">
                                        {{ __($testimonials[0]->title) }}
                                    </h2>
                                    <div class="mb-3">
                                        <!-- Slider main container -->
                                        <div class="swiper testimonials">
                                            <!-- Additional required wrapper -->
                                            <div class="swiper-wrapper mb-3">
                                                <!-- Slides -->
                                                @foreach ($testimonials as $testimonial)
                                                    <div class="swiper-slide">
                                                        <section class="overflow-hidden">
                                                            <div class="container mx-auto">
                                                                <div class="rounded-3xl">
                                                                    <div
                                                                        class="py-6 px-2 md:max-w-3xl mx-auto text-center">
                                                                        <p class="mt-6 mb-10 text-xl font-bold">
                                                                            "{{ $testimonial->review }}"</p>
                                                                        <img class="mb-6 w-20 h-20 mx-auto rounded-full"src="{{ url($testimonial->reviewer_image) }}"
                                                                            alt="{{ $testimonial->reviewer_name }}">
                                                                        <h3
                                                                            class="font-heading mb-2 text-xl text-gray-900 font-black capitalize">
                                                                            {{ $testimonial->reviewer_name }}</h3>
                                                                        <p
                                                                            class="text-sm text-gray-500 font-bold capitalize">
                                                                            {{ $testimonial->review_subtext }}</p>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </section>
                                                    </div>
                                                @endforeach
                                            </div>

                                            <!-- If we need pagination -->
                                            <div class="swiper-button-next"></div>
                                            <div class="swiper-button-prev"></div>
                                        </div>
                                    </div>
                                </section>
                            @endif

                            <!-- Videos -->
                            @if (count($feature_details) > 0 && !$feature_details->isEmpty())
                                @php
                                    $i = 0;
                                @endphp

                                @foreach ($feature_details as $value => $feature)
                                    @if ($feature->type == 'youtube' && $i == 0)
                                        <div class="py-5 px-3 my-2 shadow-lg">
                                            <h2 class="w-full md:w-auto mt-2 mb-2 md:mb-0 text-2xl font-bold">
                                                {{ __('Videos') }}
                                            </h2>
                                            <div class="flex flex-wrap -mx-2">
                                                <!-- Slider main container -->
                                                <div class="swiper videos">
                                                    <!-- Additional required wrapper -->
                                                    <div class="swiper-wrapper mb-3">
                                                        <!-- Slides -->
                                                        @foreach ($feature_details as $feature)
                                                            @if ($feature->type == 'youtube')
                                                                <div class="swiper-slide">
                                                                    <div class="pt-6 px-2">
                                                                        <div
                                                                            class="flex mb-4 justify-between items-center">
                                                                            <iframe referrerpolicy="strict-origin-when-cross-origin" width="600" height="315"
                                                                                src="https://www.youtube.com/embed/{!! $feature->content !!}"
                                                                                title="{{ $feature->label }}"
                                                                                frameborder="0"
                                                                                allow="accelerometer; autoplay; clipboard-write; picture-in-picture;"
                                                                                allowfullscreen></iframe>
                                                                        </div>
                                                                        <div>
                                                                            <h3
                                                                                class="mb-2 text-xl font-bold service-name">
                                                                                {{ $feature->label }}
                                                                            </h3>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            @endif
                                                        @endforeach
                                                    </div>

                                                    <!-- If we need pagination -->
                                                    <div class="swiper-button-next"></div>
                                                    <div class="swiper-button-prev"></div>
                                                </div>
                                            </div>
                                        </div>
                                        @php
                                            $i++;
                                        @endphp
                                    @endif
                                @endforeach

                            @endif

                            {{-- Iframes --}}
                            @if ($iframes != null && !$iframes->isEmpty())
                                @foreach ($iframes as $iframe)
                                    <div class="py-5 px-3 my-2 shadow-lg">
                                        <h2 class="w-full md:w-auto mt-2 mb-2 md:mb-0 text-2xl font-bold">
                                            {{ __($iframe->label) }}
                                        </h2>
                                        <div class="-mx-2">
                                            <div class="pt-6 px-2">
                                                <div class="flex mb-4 justify-between items-center">
                                                    <iframe referrerpolicy="strict-origin-when-cross-origin" class="rounded-xl" src="{{ $iframe->content }}"
                                                        style="width: 100%;" height="350" allowfullscreen=""
                                                        loading="lazy"
                                                        referrerpolicy="no-referrer-when-downgrade"></iframe>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @endif

                            <!-- Payments -->
                            @if (count($payment_details) > 0)
                                <div class="py-5 px-3 my-2 shadow-lg">
                                    <h2 class="w-full md:w-auto mt-2 mb-4 md:mb-0 text-2xl font-bold">
                                        {{ __($payment_details[0]->title) }}
                                    </h2>

                                    @foreach ($payment_details as $payment)
                                        @if ($payment->type == 'url')
                                            <a href="https://{{ str_replace('https://', '', $payment->content) }}"
                                                rel="noopener nofollow noreferrer" target="_blank">
                                        @endif

                                        <div class="flex my-4 justify-between items-center">
                                            <div class="flex items-center">
                                                @if ($payment->type != 'image')
                                                <span
                                                    class="flex justify-center items-center content-center bg-indigo-500 hover:bg-indigo-600 shadow-md hover:shadow-lg h-10 w-10 mr-3 rounded fill-current text-white">
                                                    <i class="{{ $payment->icon }}"></i>
                                                </span>
                                                @endif

                                                @if ($payment->type != 'upi' && $payment->type != 'image')
                                                    <div class="w-3/4 mx-5 my-6">
                                                        <p
                                                            class="font-semibold break-word text-gray-800 text-md sub-heading">
                                                            {{ $payment->label }}</p>
                                                        <p class="font-medium text-gray-800 pt-1 break-word text-base">
                                                            @foreach (explode('.', $payment->content) as $sentence)
                                                                @if (trim($sentence))
                                                                    <!-- Make sure the sentence is not empty -->
                                                                    <p class="whitespace-break-spaces text-sm">{{ trim($sentence) }}</p>
                                                                @endif
                                                            @endforeach
                                                        </p>
                                                    </div>
                                                @endif

                                                @if ($payment->type == 'upi')
                                                    <div class="w-3/4 mx-5 my-6">
                                                        <p
                                                            class="font-semibold break-word text-gray-800 text-md sub-heading mb-2">
                                                            {{ $payment->label }}</p>
                                                        <canvas class="upi_qr"></canvas>
                                                        <p
                                                            class="font-medium text-gray-800 pt-1 break-word text-base upi_id hidden">
                                                            {{ $payment->content }}</p>
                                                    </div>
                                                @endif

                                                @if($payment->type == 'image')
                                                    <div class="w-full mx-5 my-6">
                                                        {{-- Image --}}
                                                        <img class="w-full h-64 object-cover rounded-lg mb-2"
                                                            src="{{ url($payment->content) }}"
                                                            alt="{{ $payment->label }}" style="width: 100%; height: 54vh; object-fit: cover;" />
                                                        <p class="font-semibold break-word text-lg text-center">
                                                            {{ $payment->label }}</p>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>

                                        @if ($payment->type == 'url')
                                            </a>
                                        @endif
                                    @endforeach
                                </div>
                            @endif

                            {{-- Show appointment slots in the calendar --}}
                            @if ($appointmentEnabled == true && isset($plan_details['appointment']) == 1)
                                @include('templates.includes.basic-appointment', ['section_bg' => 'white', 'bg_color' => 'white', 'btn_color' => 'indigo-500', 'text_color' => 'white'])
                            @endif

                            <!-- Business Hours -->
                            @if ($plan_details['business_hours'] == 1)
                                @if ($business_hours != null && $business_hours->is_display != 0)
                                    <div class="py-5 px-3 my-2 shadow-lg">
                                        <h2 class="w-full md:w-auto mt-2 mb-4 md:mb-0 text-2xl font-bold">
                                            {{ __($business_hours->title) }}
                                        </h2>

                                        @if ($business_hours->is_always_open != 'Opening')
                                            <div class="flex my-4 justify-between items-center">
                                                <div class="flex items-center">
                                                    <span class="inline-block mr-2">
                                                        <svg xmlns="http://www.w3.org/2000/svg"
                                                            class="icon icon-tabler icon-tabler-calendar-time text-gray-200"
                                                            width="24" height="24" viewBox="0 0 24 24"
                                                            stroke-width="2" stroke="currentColor" fill="none"
                                                            stroke-linecap="round" stroke-linejoin="round">
                                                            <path stroke="none" d="M0 0h24v24H0z" fill="none">
                                                            </path>
                                                            <path
                                                                d="M11.795 21h-6.795a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v4">
                                                            </path>
                                                            <path d="M18 18m-4 0a4 4 0 1 0 8 0a4 4 0 1 0 -8 0"></path>
                                                            <path d="M15 3v4"></path>
                                                            <path d="M7 3v4"></path>
                                                            <path d="M3 11h16"></path>
                                                            <path d="M18 16.496v1.504l1 1"></path>
                                                        </svg>
                                                    </span>
                                                    <h4 class="text-sm text-gray-800 font-bold">{{ __('Monday') }}
                                                    </h4>
                                                </div>
                                                <span class="text-sm">{{ __($business_hours->monday) }}</span>
                                            </div>

                                            <div class="flex my-4 justify-between items-center">
                                                <div class="flex items-center">
                                                    <span class="inline-block mr-2">
                                                        <svg xmlns="http://www.w3.org/2000/svg"
                                                            class="icon icon-tabler icon-tabler-calendar-time text-gray-200"
                                                            width="24" height="24" viewBox="0 0 24 24"
                                                            stroke-width="2" stroke="currentColor" fill="none"
                                                            stroke-linecap="round" stroke-linejoin="round">
                                                            <path stroke="none" d="M0 0h24v24H0z" fill="none">
                                                            </path>
                                                            <path
                                                                d="M11.795 21h-6.795a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v4">
                                                            </path>
                                                            <path d="M18 18m-4 0a4 4 0 1 0 8 0a4 4 0 1 0 -8 0"></path>
                                                            <path d="M15 3v4"></path>
                                                            <path d="M7 3v4"></path>
                                                            <path d="M3 11h16"></path>
                                                            <path d="M18 16.496v1.504l1 1"></path>
                                                        </svg>
                                                    </span>
                                                    <h4 class="text-sm text-gray-800 font-bold">{{ __('Tuesday') }}
                                                    </h4>
                                                </div>
                                                <span class="text-sm">{{ __($business_hours->tuesday) }}</span>
                                            </div>

                                            <div class="flex my-4 justify-between items-center">
                                                <div class="flex items-center">
                                                    <span class="inline-block mr-2">
                                                        <svg xmlns="http://www.w3.org/2000/svg"
                                                            class="icon icon-tabler icon-tabler-calendar-time text-gray-200"
                                                            width="24" height="24" viewBox="0 0 24 24"
                                                            stroke-width="2" stroke="currentColor" fill="none"
                                                            stroke-linecap="round" stroke-linejoin="round">
                                                            <path stroke="none" d="M0 0h24v24H0z" fill="none">
                                                            </path>
                                                            <path
                                                                d="M11.795 21h-6.795a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v4">
                                                            </path>
                                                            <path d="M18 18m-4 0a4 4 0 1 0 8 0a4 4 0 1 0 -8 0"></path>
                                                            <path d="M15 3v4"></path>
                                                            <path d="M7 3v4"></path>
                                                            <path d="M3 11h16"></path>
                                                            <path d="M18 16.496v1.504l1 1"></path>
                                                        </svg>
                                                    </span>
                                                    <h4 class="text-sm text-gray-800 font-bold">{{ __('Wednesday') }}
                                                    </h4>
                                                </div>
                                                <span class="text-sm">{{ __($business_hours->wednesday) }}</span>
                                            </div>

                                            <div class="flex my-4 justify-between items-center">
                                                <div class="flex items-center">
                                                    <span class="inline-block mr-2">
                                                        <svg xmlns="http://www.w3.org/2000/svg"
                                                            class="icon icon-tabler icon-tabler-calendar-time text-gray-200"
                                                            width="24" height="24" viewBox="0 0 24 24"
                                                            stroke-width="2" stroke="currentColor" fill="none"
                                                            stroke-linecap="round" stroke-linejoin="round">
                                                            <path stroke="none" d="M0 0h24v24H0z" fill="none">
                                                            </path>
                                                            <path
                                                                d="M11.795 21h-6.795a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v4">
                                                            </path>
                                                            <path d="M18 18m-4 0a4 4 0 1 0 8 0a4 4 0 1 0 -8 0"></path>
                                                            <path d="M15 3v4"></path>
                                                            <path d="M7 3v4"></path>
                                                            <path d="M3 11h16"></path>
                                                            <path d="M18 16.496v1.504l1 1"></path>
                                                        </svg>
                                                    </span>
                                                    <h4 class="text-sm text-gray-800 font-bold">{{ __('Thursday') }}
                                                    </h4>
                                                </div>
                                                <span class="text-sm">{{ __($business_hours->thursday) }}</span>
                                            </div>

                                            <div class="flex my-4 justify-between items-center">
                                                <div class="flex items-center">
                                                    <span class="inline-block mr-2">
                                                        <svg xmlns="http://www.w3.org/2000/svg"
                                                            class="icon icon-tabler icon-tabler-calendar-time text-gray-200"
                                                            width="24" height="24" viewBox="0 0 24 24"
                                                            stroke-width="2" stroke="currentColor" fill="none"
                                                            stroke-linecap="round" stroke-linejoin="round">
                                                            <path stroke="none" d="M0 0h24v24H0z" fill="none">
                                                            </path>
                                                            <path
                                                                d="M11.795 21h-6.795a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v4">
                                                            </path>
                                                            <path d="M18 18m-4 0a4 4 0 1 0 8 0a4 4 0 1 0 -8 0"></path>
                                                            <path d="M15 3v4"></path>
                                                            <path d="M7 3v4"></path>
                                                            <path d="M3 11h16"></path>
                                                            <path d="M18 16.496v1.504l1 1"></path>
                                                        </svg>
                                                    </span>
                                                    <h4 class="text-sm text-gray-800 font-bold">{{ __('Friday') }}
                                                    </h4>
                                                </div>
                                                <span class="text-sm">{{ __($business_hours->friday) }}</span>
                                            </div>

                                            <div class="flex my-4 justify-between items-center">
                                                <div class="flex items-center">
                                                    <span class="inline-block mr-2">
                                                        <svg xmlns="http://www.w3.org/2000/svg"
                                                            class="icon icon-tabler icon-tabler-calendar-time text-gray-200"
                                                            width="24" height="24" viewBox="0 0 24 24"
                                                            stroke-width="2" stroke="currentColor" fill="none"
                                                            stroke-linecap="round" stroke-linejoin="round">
                                                            <path stroke="none" d="M0 0h24v24H0z" fill="none">
                                                            </path>
                                                            <path
                                                                d="M11.795 21h-6.795a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v4">
                                                            </path>
                                                            <path d="M18 18m-4 0a4 4 0 1 0 8 0a4 4 0 1 0 -8 0"></path>
                                                            <path d="M15 3v4"></path>
                                                            <path d="M7 3v4"></path>
                                                            <path d="M3 11h16"></path>
                                                            <path d="M18 16.496v1.504l1 1"></path>
                                                        </svg>
                                                    </span>
                                                    <h4 class="text-sm text-gray-800 font-bold">{{ __('Saturday') }}
                                                    </h4>
                                                </div>
                                                <span class="text-sm">{{ __($business_hours->saturday) }}</span>
                                            </div>

                                            <div class="flex my-4 justify-between items-center">
                                                <div class="flex items-center">
                                                    <span class="inline-block mr-2">
                                                        <svg xmlns="http://www.w3.org/2000/svg"
                                                            class="icon icon-tabler icon-tabler-calendar-time text-gray-200"
                                                            width="24" height="24" viewBox="0 0 24 24"
                                                            stroke-width="2" stroke="currentColor" fill="none"
                                                            stroke-linecap="round" stroke-linejoin="round">
                                                            <path stroke="none" d="M0 0h24v24H0z" fill="none">
                                                            </path>
                                                            <path
                                                                d="M11.795 21h-6.795a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v4">
                                                            </path>
                                                            <path d="M18 18m-4 0a4 4 0 1 0 8 0a4 4 0 1 0 -8 0"></path>
                                                            <path d="M15 3v4"></path>
                                                            <path d="M7 3v4"></path>
                                                            <path d="M3 11h16"></path>
                                                            <path d="M18 16.496v1.504l1 1"></path>
                                                        </svg>
                                                    </span>
                                                    <h4 class="text-sm text-gray-800 font-bold">{{ __('Sunday') }}
                                                    </h4>
                                                </div>
                                                <span class="text-sm">{{ __($business_hours->sunday) }}</span>
                                            </div>
                                        @else
                                            <div class="flex my-4 justify-between items-center">
                                                <div class="flex items-center">
                                                    <span class="inline-block mr-2">
                                                        <svg xmlns="http://www.w3.org/2000/svg"
                                                            class="icon icon-tabler icon-tabler-calendar-time text-gray-300"
                                                            width="24" height="24" viewBox="0 0 24 24"
                                                            stroke-width="2" stroke="currentColor" fill="none"
                                                            stroke-linecap="round" stroke-linejoin="round">
                                                            <path stroke="none" d="M0 0h24v24H0z" fill="none">
                                                            </path>
                                                            <path
                                                                d="M11.795 21h-6.795a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v4">
                                                            </path>
                                                            <path d="M18 18m-4 0a4 4 0 1 0 8 0a4 4 0 1 0 -8 0"></path>
                                                            <path d="M15 3v4"></path>
                                                            <path d="M7 3v4"></path>
                                                            <path d="M3 11h16"></path>
                                                            <path d="M18 16.496v1.504l1 1"></path>
                                                        </svg>
                                                    </span>
                                                    <h3 class="text-sm text-green-600">{{ __('Always Open') }}</h3>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            @endif

                            <!-- Map -->
                            @if (count($feature_details) > 0 && !$feature_details->isEmpty())
                                @php
                                    $i = 0;
                                @endphp

                                @foreach ($feature_details as $value => $feature)
                                    @if ($feature->type == 'map' && $i == 0)
                                        <div class="py-5 px-3 my-2 shadow-lg">
                                            <h2 class="w-full md:w-auto mt-2 mb-2 md:mb-0 text-2xl font-bold">
                                                {{ __('Location') }}
                                            </h2>
                                            <div class="-mx-2">
                                                <div class="pt-6 px-2">
                                                    <div class="flex mb-4 justify-between items-center">
                                                        <iframe referrerpolicy="strict-origin-when-cross-origin" class="rounded-xl"
                                                            src="https://www.google.com/maps/embed?{!! $feature->content !!}"
                                                            style="width: 100%;" height="350" style="border:0;"
                                                            allowfullscreen="" loading="lazy"
                                                            referrerpolicy="no-referrer-when-downgrade"></iframe>
                                                    </div>
                                                    <h3 class="mb-2 text-xl font-bold">{{ $feature->label }}</h3>
                                                </div>
                                            </div>
                                        </div>
                                        @php
                                            $i++;
                                        @endphp
                                    @endif
                                @endforeach
                            @endif

                            {{-- Service Service Booking --}}
                            @if (isset($plan_details['service_booking']) && $plan_details['service_booking'] == 1)
                                @if (isset($service_booking_details) && $service_booking_details->service_booking == 1)
                                    @include('templates.includes.service-booking', [
                                        'text_color' => 'text-gray-700',
                                        'head_style' => 'w-full md:w-auto mt-2 mb-2 md:mb-0 text-2xl font-bold',
                                        'input_style' => 'w-full p-4 text-xs bg-gray-100 outline-none rounded',
                                        'btn_style' => 'mb-2 w-full py-4 bg-indigo-500 hover:bg-indigo-600 text-sm rounded font-bold text-gray-50 transition duration-200',
                                    ])
                                @endif
                            @endif
                            {{-- End Service Booking --}}

                            <!-- Start Google Wallet section -->
                            @if (is_dir(base_path('plugins/GoogleWallet')))
                                @if (isset($plan_details['google_wallet']) && $plan_details['google_wallet'] == 1 && $business_card_details->is_google_wallet_hidden == 0)
                                    <div class="py-5 px-3 my-2 shadow-lg">
                                        <div class="mb-6">
                                            <h2 class="w-full md:w-auto mt-2 mb-2 md:mb-0 text-2xl font-bold">
                                                    {{ __('Google Wallet') }}
                                            </h2>
                                        </div>

                                        <div class="px-2">
                                            {{-- Pass/Ticket Description --}}
                                            @if ($google_wallet_details->wallet_description != null)
                                                <div class="text-sm">
                                                    {!! $google_wallet_details->wallet_description ?? '' !!}
                                                </div>
                                            @endif
                                            {{-- Google Wallet Button --}}
                                            @if ($google_wallet_details->wallet_link != null)
                                                <div class="flex justify-center mt-6">                                        
                                                    <a href="{{ $google_wallet_details->wallet_link }}" class="w-full lg:w-1/2" target="_blank" rel="noopener noreferrer">
                                                        <img src="{{ url()->to('/') . '/img/google-wallet-btn.png'}}" alt="" class="w-full object-cover">
                                                    </a>
                                                </div>
                                            @endif                                    
                                        </div>                                
                                    </div>
                                @endif
                            @endif
                            <!-- End Google Wallet section -->

                            {{-- Contact form --}}
                            @if ($plan_details['contact_form'] == 1)
                                @if ($business_card_details->enquiry_email != null)
                                    <div class="py-5 px-3 my-2 shadow-lg">
                                        <h2 class="w-full md:w-auto mt-2 mb-2 md:mb-0 text-2xl font-bold">
                                            {{ __($business_card_details->contact_form_title) }}
                                        </h2>

                                        <div class="-mx-2">
                                            <div class="pt-6">
                                                @if (Session::has('message'))
                                                    <div class="px-2 bg-indigo-500 hover:bg-indigo-600 border-t-4 border-indigo-500 rounded-b text-indigo-900 px-4 py-3 shadow-md mb-3"
                                                        role="alert">
                                                        <div class="flex">
                                                            <div class="py-1"><svg
                                                                    class="fill-current h-6 w-6 text-white mr-4"
                                                                    xmlns="http://www.w3.org/2000/svg"
                                                                    viewBox="0 0 20 20">
                                                                    <path
                                                                        d="M2.93 17.07A10 10 0 1 1 17.07 2.93 10 10 0 0 1 2.93 17.07zm12.73-1.41A8 8 0 1 0 4.34 4.34a8 8 0 0 0 11.32 11.32zM9 11V9h2v6H9v-4zm0-6h2v2H9V5z" />
                                                                </svg></div>
                                                            <div>
                                                                <p class="font-bold text-white">
                                                                    {{ Session::get('message') }}</p>
                                                                <p class="text-sm text-white">
                                                                    {{ __('Please wait for the reply to be sent.') }}
                                                                </p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                                <form class="px-2" action="{{ config('app.url') }}/sent-enquiry"
                                                    method="POST">
                                                    @csrf
                                                    <div class="flex flex-wrap -mx-2">
                                                        <div class="mb-3 w-full lg:w-1/2 px-2">
                                                            <input
                                                                class="w-full p-4 text-xs bg-gray-50 outline-none rounded"
                                                                type="hidden"
                                                                value="{{ $business_card_details->card_id }}"
                                                                name="card_id" />
                                                            <input
                                                                class="w-full p-4 text-xs bg-gray-100 outline-none rounded"
                                                                type="text" placeholder="{{ __('Name') }} *"
                                                                name="name" required />
                                                        </div>
                                                        <div class="mb-3 w-full lg:w-1/2 px-2">
                                                            <input
                                                                class="w-full p-4 text-xs bg-gray-100 outline-none rounded"
                                                                type="email" placeholder="{{ __('Email') }} *"
                                                                name="email" required />
                                                        </div>
                                                    </div>
                                                    <div class="mb-3 flex p-4 bg-gray-100 rounded">
                                                        <input class="w-full text-xs bg-gray-100 outline-none"
                                                            type="number" placeholder="{{ __('Mobile Number') }}"
                                                            name="phone" />
                                                    </div>
                                                    <div class="mb-6 flex p-4 bg-gray-100 rounded">
                                                        <textarea class="w-full h-20 text-xs font-semibold bg-gray-100 rounded outline-none" type="text"
                                                            placeholder="{{ __('Message') }} *" name="message" required></textarea>
                                                    </div>
                                                    
                                                    {{-- ReCaptcha --}}
                                                    @include('templates.includes.recaptcha', ['recaptchaId' => 'recaptcha-one'])

                                                    <div class="text-center">
                                                        <button
                                                            class="mb-2 w-full py-4 bg-indigo-500 hover:bg-indigo-600 text-sm rounded font-bold text-gray-50 transition duration-200">
                                                            {{ __('Send') }}
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endif

                            <!-- Share vcard -->
                            <div class="lg:py-5 pt-5 px-3">
                                <h2 class="w-full md:w-auto md:mb-0 text-2xl font-bold">
                                    {{ __('Share') }}
                                </h2>
                                <div class="flex flex-wrap pt-6 -mx-2">
                                    <!-- Facebook -->
                                    <div class="w-1/5 md:w-1/5 px-2 mb-2 md:mb-0">
                                        <a class="flex justify-center py-2 text-sm text-white bg-indigo-500 hover:bg-indigo-600 rounded transition duration-200"
                                            href="{{ $shareComponent['facebook'] }}" target="_blank">
                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                class="icon icon-tabler icon-tabler-brand-facebook" width="24"
                                                height="24" viewBox="0 0 24 24" stroke-width="2"
                                                stroke="currentColor" fill="none" stroke-linecap="round"
                                                stroke-linejoin="round">
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                                <path
                                                    d="M7 10v4h3v7h4v-7h3l1 -4h-4v-2a1 1 0 0 1 1 -1h3v-4h-3a5 5 0 0 0 -5 5v2h-3">
                                                </path>
                                            </svg>
                                        </a>
                                    </div>

                                    <!-- X (Twitter) -->
                                    <div class="w-1/5 md:w-1/5 px-2 mb-2 md:mb-0">
                                        <a class="flex justify-center py-2 text-sm text-white bg-indigo-500 hover:bg-indigo-600 rounded transition duration-200"
                                            href="{{ $shareComponent['twitter'] }}" target="_blank">
                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                class="icon icon-tabler icon-tabler-brand-x" width="24"
                                                height="24" viewBox="0 0 24 24" stroke-width="2"
                                                stroke="currentColor" fill="none" stroke-linecap="round"
                                                stroke-linejoin="round">
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                                <path d="M4 4l11.733 16h4.267l-11.733 -16z"></path>
                                                <path d="M4 20l6.768 -6.768m2.46 -2.46l6.772 -6.772"></path>
                                            </svg>
                                        </a>
                                    </div>

                                    <!-- Linked In -->
                                    <div class="w-1/5 md:w-1/5 px-2 mb-2 md:mb-0">
                                        <a class="flex justify-center py-2 text-sm text-white bg-indigo-500 hover:bg-indigo-600 rounded transition duration-200"
                                            href="{{ $shareComponent['linkedin'] }}" target="_blank">
                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                class="icon icon-tabler icon-tabler-brand-linkedin" width="24"
                                                height="24" viewBox="0 0 24 24" stroke-width="2"
                                                stroke="currentColor" fill="none" stroke-linecap="round"
                                                stroke-linejoin="round">
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                                <path
                                                    d="M4 4m0 2a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2z">
                                                </path>
                                                <path d="M8 11l0 5"></path>
                                                <path d="M8 8l0 .01"></path>
                                                <path d="M12 16l0 -5"></path>
                                                <path d="M16 16v-3a2 2 0 0 0 -4 0"></path>
                                            </svg>
                                        </a>
                                    </div>

                                    <!-- Telegram -->
                                    <div class="w-1/5 md:w-1/5 px-2 mb-2 md:mb-0">
                                        <a class="flex justify-center py-2 text-sm text-white bg-indigo-500 hover:bg-indigo-600 rounded transition duration-200"
                                            href="{{ $shareComponent['telegram'] }}" target="_blank">
                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                class="icon icon-tabler icon-tabler-brand-telegram" width="24"
                                                height="24" viewBox="0 0 24 24" stroke-width="2"
                                                stroke="currentColor" fill="none" stroke-linecap="round"
                                                stroke-linejoin="round">
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                                <path d="M15 10l-4 4l6 6l4 -16l-18 7l4 2l2 6l3 -4"></path>
                                            </svg>
                                        </a>
                                    </div>

                                    <!-- WhatsApp -->
                                    <div class="w-1/5 md:w-1/5 px-2 mb-2 md:mb-0">
                                        <a class="flex justify-center py-2 text-sm text-white bg-indigo-500 hover:bg-indigo-600 rounded transition duration-200"
                                            href="{{ $shareComponent['whatsapp'] }}" target="_blank">
                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                class="icon icon-tabler icon-tabler-brand-whatsapp" width="24"
                                                height="24" viewBox="0 0 24 24" stroke-width="2"
                                                stroke="currentColor" fill="none" stroke-linecap="round"
                                                stroke-linejoin="round">
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                                <path d="M3 21l1.65 -3.8a9 9 0 1 1 3.4 2.9l-5.05 .9"></path>
                                                <path
                                                    d="M9 10a.5 .5 0 0 0 1 0v-1a.5 .5 0 0 0 -1 0v1a5 5 0 0 0 5 5h1a.5 .5 0 0 0 0 -1h-1a.5 .5 0 0 0 0 1">
                                                </path>
                                            </svg>
                                        </a>
                                    </div>
                                </div>
                            </div>

                            {{-- Branding --}}
                            @if ($plan_details['hide_branding'] == 1)
                                <div class="lg:pb-1 pb-10 px-3">
                                    <div
                                        class="flex pb-5 m-auto pt-5 font-semibold text-sm flex-col md:flex-row max-w-6xl">
                                        <div class="mt-2">
                                            {{ __('Copyright') }} &copy; {{ now()->year }} {{ __('by') }}
                                            <a class="text-indigo-500" href="{{ url()->current() }}">
                                                {{ $card_details->title }} </a>
                                            <span id="year"></span>{{ __('. All Rights Reserved.') }}
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="lg:pb-1 pb-10 px-3">
                                    <div
                                        class="flex pb-5 m-auto pt-5 font-semibold text-sm flex-col md:flex-row max-w-6xl">
                                        <div class="mt-2">
                                            {{ __('Copyright') }} &copy; {{ now()->year }}.
                                            {{ __('Made with') }}
                                            <a class="text-indigo-500" href="{{ env('APP_URL') }}">
                                                {{ config('app.name') }} </a>
                                            <span id="year"></span>{{ __('. All Rights Reserved.') }}
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- Add to Contact -->
                        <div class="w-full px-2 mb-2 md:mb-0 add-to-contact lg:hidden">
                            <a class="flex justify-center py-2 text-sm text-white bg-indigo-500 hover:bg-indigo-600 rounded transition duration-200"
                                href="{{ config('app.url') }}/download/{{ $business_card_details->card_id }}">
                                <svg xmlns="http://www.w3.org/2000/svg"
                                    class="icon icon-tabler icon-tabler-address-book mr-3" width="24"
                                    height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                    fill="none" stroke-linecap="round" stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                    <path
                                        d="M20 6v12a2 2 0 0 1 -2 2h-10a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2h10a2 2 0 0 1 2 2z">
                                    </path>
                                    <path d="M10 16h6"></path>
                                    <path d="M13 11m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"></path>
                                    <path d="M4 8h3"></path>
                                    <path d="M4 12h3"></path>
                                    <path d="M4 16h3"></path>
                                </svg>
                                <span class="text-center">{{ __('ADD TO CONTACT') }}</span>
                            </a>
                        </div>
                    </div>
                </div>
            </section>

            {{-- Send vCard --}}
            <div
                class="send-modal opacity-0 transition duration-300 ease-in-out pointer-events-none fixed w-full h-full top-0 left-0 flex items-center justify-center">
                <div class="send-modal-overlay absolute w-full h-full bg-gray-900 opacity-50"></div>
                <div
                    class="modal-container bg-white w-full md:max-w-md mx-auto rounded shadow-lg z-50 overflow-y-auto">
                    <div class="modal-content py-4 text-left px-6">
                        <div class="justify-between items-center">
                            <div class="text-left my-3">
                                <label class="mt-6 block text-gray-700 text-sm font-bold mb-2"
                                    for="phone_number">{{ __("Phone
                                                                Number") }}</label>
                                <input id="phone_number"
                                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                    placeholder="{{ __('For ex: 91987654310') }}">
                                <small>{{ __('For ex: 91987654310 (With Country code) (Without +)') }}</small>
                            </div>
                            <button
                                class="flex justify-center items-center content-center bg-indigo-500 hover:bg-indigo-600 shadow-md hover:shadow-lg h-8 w-auto py-2 px-8 rounded fill-current text-white"
                                onclick="sendVcard()">
                                {{ __('Send') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Scan QR --}}
            <div
                class="qr-modal opacity-0 transition duration-300 ease-in-out pointer-events-none fixed w-full h-full top-0 left-0 flex items-center justify-center">
                <div class="qr-modal-overlay absolute w-full h-full bg-white opacity-50"></div>
                <div
                    class="modal-container bg-white w-auto md:max-w-md mx-auto rounded shadow-lg z-50 overflow-y-auto">
                    <div class="modal-content py-4 text-left px-6">
                        <div class="justify-between items-center px-6 qr-code"></div>
                        <a id="download"
                            onclick="downloadQr('{{ config('app.url') . route('dynamic.card', $business_card_details->card_id, false) }}', 500)"
                            class="mt-3 cursor-pointer w-full flex justify-center items-center content-center bg-indigo-500 hover:bg-indigo-600 shadow-lg hover:shadow-lg h-8 w-8 mr-4 rounded fill-current text-white qr-code-download">
                            <span>{{ __('Download') }}</span>
                        </a>
                    </div>
                </div>
            </div>
        @else
            <div class="leading-tight min-h-screen bg-grey-lighter p-1">
                <br>
                <h4>{{ __('403') }}</h4>
                <h6>{{ __('Oops! Basic details are missing.') }}</h6>
            </div>
        @endif
    @endif

    <!-- Include PWA modal -->
    @if ($plan_details != null)
        {{-- Check PWA --}}
        @if ($plan_details['pwa'] == 1 && $business_card_details->is_enable_pwa == 1)
            @include('vendor.laravelpwa.pwa_modal_center')
        @endif
    @endif

    {{-- Include Newsletter Modal --}}
    @if ($business_card_details != null)
        {{-- Check Newsletter --}}
        @if (!empty($business_card_details->is_newsletter_pop_active) && $business_card_details->is_newsletter_pop_active == 1)
            @include('templates.includes.newsletter_modal', [
                'primary_color' => 'indigo'
            ])
        @endif
    @endif

    {{-- Include Information Popup Modal --}}
    @if ($business_card_details != null)
        {{-- Check Information Popup --}}
        @if (!empty($business_card_details->is_info_pop_active) && $business_card_details->is_info_pop_active == 1)
            @include('templates.includes.information_popup_modal', [
                'primary_color' => 'indigo'
            ])
        @endif
    @endif

    {{-- Check password protected --}}
    @if ($business_card_details->password != null && Session::get('password_protected') == false)
        <div class="bg-indigo-50 p-4 flex items-center justify-center h-screen">
            <div x-data="{ showModal: true }">
                <!-- Modal -->
                <div x-show="showModal" class="fixed inset-0 flex items-center justify-center z-50 p-3">
                    <div class="bg-white rounded-lg p-6 w-96 max-w-full shadow-lg transform transition-all duration-300"
                        x-show.transition.opacity="showModal">
                        <!-- Modal Header -->
                        <div class="flex justify-between items-center border-b-2 border-gray-200 pb-4">
                            <h2 class="text-2xl font-semibold">{{ __('Password Protected') }}</h2>
                        </div>

                        <!-- Modal Content -->
                        <div class="mt-6 space-y-4">
                            <form action="{{ config('app.url') }}/check-password/{{ $business_card_details->card_id }}" method="post">
                                @csrf
                                <p class="text-lg text-gray-600">{{ __('Enter your vcard Password') }}</p>
                                <div class="flex">
                                    <input type="password" name="password"
                                        class="rounded rounded-r-lg bg-gray-50 border text-gray-800 focus:ring-indigo-100 focus:border-indigo-100 block flex-1 min-w-0 w-full text-sm border-gray-100 p-2.5"
                                        placeholder="{{ __('Password') }}" required>
                                </div>

                                {{-- Message --}}
                                @if (Session::has('message'))
                                    <div class="flex items-center p-4 my-4 text-sm text-red-800 rounded-lg bg-red-50 dark:bg-gray-800 dark:text-red-400"
                                        role="alert">
                                        <svg class="flex-shrink-0 inline w-4 h-4 mr-3" aria-hidden="true"
                                            xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                                            viewBox="0 0 20 20">
                                            <path
                                                d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z" />
                                        </svg>
                                        <span class="sr-only">{{ __('Failed') }}</span>
                                        <div>
                                            <span class="font-medium">{{ Session::get('message') }}</span>
                                        </div>
                                    </div>
                                @endif

                                <div class="flex flex-col space-y-4 mt-3">
                                    <button type="submit"
                                        class="bg-indigo-500 text-white px-4 py-2 mt-2 rounded-lg hover:bg-indigo-600 transition duration-300">{{ __('Password') }}</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- JS files --}}
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script type="text/javascript" src="{{ url('js/smooth-scroll.polyfills.min.js') }}"></script>
    <script type="text/javascript" src="{{ url('app/js/footer.js') }}"></script>
    <script src="{{ url('templates/js/template-3.js') }}"></script>
    <script type="text/javascript" src="{{ url('js/jquery-qrcode.min.js') }}"></script>

    <!-- Flatpickr JS -->
    <script src="{{ url('js/flatpickr.min.js') }}"></script>

    {{-- Flatpickr JS --}}
    <script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/l10n/{{ app()->getLocale() }}.js"></script>
    @php
        if (isset($service_booking_details) && $service_booking_details->service_booking == 1) {
            $service_booking_available_days = json_decode($service_booking_details->service_booking_available_days);
        }
    @endphp

    <script>
        // Assuming $appointment_slots contains data like: {"monday": [...], "tuesday": [...], ...}
        const disableSlots = {!! $appointment_slots !!}; // Outputting the time slots

        document.addEventListener('DOMContentLoaded', function() {
            "use strict";

            // Service Booking allowed days
            const availableDays = @json($service_booking_available_days ?? '{}');
            const dayMap = {
                sunday: 0,
                monday: 1,
                tuesday: 2,
                wednesday: 3,
                thursday: 4,
                friday: 5,
                saturday: 6
            };

            const allowedDays = Object.keys(availableDays)
                .filter(day => availableDays[day])
                .map(day => dayMap[day]);

            flatpickr("#appointment-date", {
                dateFormat: "Y-m-d",
                minDate: "today",
                locale: "{{ app()->getLocale() }}",
                disable: [
                    function(date) {
                        const day = date.toLocaleString("en-us", {
                            weekday: 'long'
                        }).toLowerCase();
                        return !disableSlots[day] || disableSlots[day].length === 0;
                    }
                ],
                onChange: function(selectedDates) {
                    const selectedDate = selectedDates[0];
                    const day = selectedDate.toLocaleString("en-us", {
                        weekday: 'long'
                    }).toLowerCase();
                    // Get available time slots in Send data to Laravel route using fetch API
                    generateOption(selectedDate, day);
                }
            });

            flatpickr("#service_start_date", {
                dateFormat: "Y-m-d",
                minDate: "today",
                locale: "{{ app()->getLocale() }}",
                disable: [
                    function(date) {
                        return !allowedDays.includes(date.getDay());
                    }
                ],
            });

            flatpickr("#service_end_date", {
                dateFormat: "Y-m-d",
                minDate: "today",
                locale: "{{ app()->getLocale() }}",
                disable: [
                    function(date) {
                        return !allowedDays.includes(date.getDay());
                    }
                ],
            });            
            
            // Flatpickr timepicker
            flatpickr(".timepicker", {
                enableTime: true,
                noCalendar: true,
                dateFormat: "H:i",   // only HH:MM
                time_24hr: true      // force 24-hour, drop AM/PM
            });
        });
    </script>

    <script>
        // Service Booking
        function submitServiceBooking() {
            "use strict";

            const customerName = document.getElementById('customer_name').value;
            const customerEmail = document.getElementById('customer_email').value;
            const customerPhone = document.getElementById('customer_phone').value;
            const noOfPersons = document.getElementById('no_of_persons').value;
            const customerAddress = document.getElementById('customer_address').value;
            const customerNotes = document.getElementById('customer_notes').value;
            const serviceStartDate = document.getElementById('service_start_date').value;
            const serviceStartTime = document.getElementById('service_start_time').value;
            const serviceEndDate = document.getElementById('service_end_date').value;
            const serviceEndTime = document.getElementById('service_end_time').value;
            const errorMessage1 = document.getElementById('errorMessage1');
            const successMessage1 = document.getElementById('successMessage1');

            errorMessage1.classList.add('hidden');
            successMessage1.classList.add('hidden');

            if (customerName.length === 0 || customerEmail.length === 0 || customerPhone.length === 0 || noOfPersons
                .length === 0 || customerAddress.length === 0 || serviceStartDate.length === 0 || serviceStartTime
                .length === 0 || serviceEndDate.length === 0 || serviceEndTime.length === 0) {
                errorMessage1.classList.remove('hidden');
                errorMessage1.innerHTML = '{{ __('Please fill all the fields.') }}';
                return;
            }

            const formData = {
                card: `{{ $business_card_details->card_id }}`,
                customer_name: customerName,
                customer_email: customerEmail,
                customer_phone: customerPhone,
                no_of_persons: noOfPersons,
                customer_address: customerAddress,
                customer_notes: customerNotes,
                service_start_date: serviceStartDate,
                service_start_time: serviceStartTime,
                service_end_date: serviceEndDate,
                service_end_time: serviceEndTime,
            };

            // Send data via fetch
            fetch("{{ config('app.url') }}/book-service", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(formData)
            })
            .then(async response => {
                const data = await response.json();

                console.log(data);

                if (data.success) {
                    // Reset form
                    ['customer_name', 'customer_email', 'customer_phone', 'no_of_persons', 'customer_address', 'customer_notes', 'service_start_date', 'service_start_time', 'service_end_date', 'service_end_time']
                    .forEach(id => {
                        document.getElementById(id).value = '';
                    });

                    errorMessage1.classList.add('hidden');
                    successMessage1.classList.remove('hidden');
                    successMessage1.innerHTML = data.message || 'Your service has been successfully booked!';
                } else {
                    successMessage1.classList.add('hidden');
                    errorMessage1.classList.remove('hidden');
                    errorMessage1.innerHTML = data.message || 'Something went wrong';
                }
            })
            .catch(error => {
                successMessage1.classList.add('hidden');
                errorMessage1.classList.remove('hidden');
                errorMessage1.innerHTML = data.message || 'Something went wrong';
            });
        }

        // Toggle the modal visibility
        function toggleModal() {
            "use strict";
            
            const modal = document.getElementById('appointmentModal');
            modal.classList.toggle('hidden');
        }

        // Validate appointment date and time slot
        function validateAndShowModal() {
            "use strict";
            
            const appointmentDate = document.getElementById('appointment-date').value;
            const timeSlotSelect = document.getElementById('time-slot-select').value;
            const errorMessage = document.getElementById('errorMessage');
            const successMessage = document.getElementById('successMessage');

            if (appointmentDate && timeSlotSelect) {
                // If both fields are not empty, show the modal
                toggleModal();
                errorMessage.classList.add('hidden'); // Hide any previous error message
            } else {
                // If either field is empty, show an error message
                errorMessage.classList.remove('hidden');
            }
        }

        // Handle form submission
        document.getElementById('appointmentForm').addEventListener('submit', function(event) {
            "use strict";
            
            event.preventDefault();

            // Get the button element
            const button = document.getElementById('bookAppointmentButton');

            // Disable the button and show loader
            button.disabled = true;
            button.innerHTML = `
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-loader animate-spin h-5 w-5 text-white inline-block mr-2">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                    <path d="M12 6l0 -3" />
                    <path d="M16.25 7.75l2.15 -2.15" />
                    <path d="M18 12l3 0" />
                    <path d="M16.25 16.25l2.15 2.15" />
                    <path d="M12 18l0 3" />
                    <path d="M7.75 16.25l-2.15 2.15" />
                    <path d="M6 12l-3 0" />
                    <path d="M7.75 7.75l-2.15 -2.15" />
                </svg>
                {{ __('Booking...') }}
            `;

            const errorMessage = document.getElementById('errorMessage');
            const successMessage = document.getElementById('successMessage');

            // Gather form data
            const formData = {
                name: document.getElementById('name').value,
                email: document.getElementById('email').value,
                phone: document.getElementById('phone').value,
                notes: document.getElementById('notes').value,
                date: document.getElementById('appointment-date').value,
                time_slot: document.getElementById('time-slot-select').value,
                price: document.getElementById('price').value, 
                card: `{{ $business_card_details->card_id }}`
            };

            // Send data to Laravel route using fetch API
            fetch("{{ config('app.url') }}/book-appointment", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}' // Include CSRF token for security
                },
                body: JSON.stringify(formData)
            })
            .then(async response => {
                    const data = await response.json();

                    if (response.ok) {
                        // Success
                        document.getElementById('email').value = "";
                        document.getElementById('phone').value = "";
                        document.getElementById('name').value = "";
                        document.getElementById('notes').value = "";
                        document.getElementById('price').value = "";

                        // Reset appointment-date and time-slot-select
                        document.getElementById('appointment-date').value = "";
                        document.getElementById('time-slot-select').value = "";

                        // Get available time slots again
                        generateOption("", "");

                        successMessage.classList.remove('hidden');
                        errorSubmitMessage.classList.add('hidden');
                        toggleModal();

                        // Redirect to whatsapp url
                        if (data.success && data.whatsapp_url && data.whatsapp_url !== '#') {
                            setTimeout(() => {
                                window.location.href = data.whatsapp_url;
                            }, 3000);
                        };

                    } else {
                        // Handle Laravel validation errors or custom response
                        if (data.errors) {
                            console.error('Validation Errors:', data.errors);
                        }

                        successMessage.classList.add('hidden');

                        errorSubmitMessage.classList.remove('hidden');
                        errorSubmitMessage.innerHTML = data.message || 'Something went wrong';
                        toggleModal();
                    }

                    button.disabled = false;
                    button.innerHTML = `{{ __('Book Appointment') }}`;
                })
                .catch(error => {
                    toggleModal();

                    button.disabled = false;
                    button.innerHTML = `{{ __('Book Appointment') }}`;
                });
        });
    </script>

    <script>
        function generateOption(selectedDate, day) 
        {
            "use strict";
            
            fetch('/get-available-time-slots', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                body: JSON.stringify({
                    card: `{{ $business_card_details->card_id }}`,
                    choose_date: selectedDate,
                    day: day
                })
            }).then(response => response.json())
            .then(data => {
                // Check response
                if (data.success == true) {
                    // Set available time slots in select option
                    document.getElementById('time-slot-select').innerHTML = `<option value="">{{ __('Select a time slot') }}`;
                    // Available time slots in JSON.parse(data.available_time_slots)
                    var available_time_slots = JSON.parse(data.available_time_slots);
                    
                    available_time_slots.forEach(time_slot => {
                        document.getElementById('time-slot-select').innerHTML += `<option value="${time_slot}">${time_slot}</option>`;
                    });

                    // Set price
                    const priceElement = document.getElementById('price');
                    priceElement.value = data.price;
                }
            });
        }
    </script>

    {{-- Custom JS --}}
    @yield('custom-js')

    

    <script>
        function sendVcard() {
            "use strict";
            
            var phone_number = $('#phone_number').val();
            window.open('https://api.whatsapp.com/send/?phone=' + phone_number + '&text=' + `{{ $shareContent }}`,
                '_blank');
            return false;
        }

        window.onload = function() {
            "use strict";

            updateQr(`{{ config('app.url') . route('dynamic.card', $business_card_details->card_id, false) }}`);
        };
    </script>
    <script>
        // UPI Link
        (function() {
            "use strict";

            // Select all .upi_qr elements
            var upiQrElements = document.querySelectorAll('.upi_qr');
            // Select all .upi_id elements
            var upiIdElements = document.querySelectorAll('.upi_id');

            // Ensure both NodeLists have the same length
            if (upiQrElements.length !== upiIdElements.length) {
                console.error('Mismatch in number of .upi_qr and .upi_id elements');
            } else {
                // Loop through each pair of .upi_qr and .upi_id elements
                for (var j = 0; j < upiQrElements.length; j++) {
                    var upiQrElement = upiQrElements[j];
                    var upiIdElement = upiIdElements[j];
                    var UPIAddress = upiIdElement.innerText;

                    if (upiQrElement && upiIdElement) {
                        // Create the QRious instance for each pair
                        new QRious({
                            element: upiQrElement,
                            value: `upi://pay?pa=${UPIAddress.replace(/\s+/g, '')}&pn={{ preg_replace('/\s+/', '', $business_card_details->title) }}&am=1&cu=INR`,
                            size: 200
                        });
                    } else {
                        console.error('Error: Element pair not found');
                    }
                }
            }

            // Additional logic can be added here as needed
        })();
    </script>
</body>

</html>
