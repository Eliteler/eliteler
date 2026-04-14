<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

@php
    use Illuminate\Support\Facades\Session;
    use App\BusinessCardIntro;

    if (isset($service_booking_details) && $service_booking_details->service_booking == 1) {
        $service_booking_available_days = json_decode($service_booking_details->service_booking_available_days);
    }

    $introScreen = BusinessCardIntro::where('business_card_intro_id', $business_card_details->intro_screen)->where('status', 1)->first();
@endphp

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

    <meta name="theme-color" content="#FFF7ED" />

    <!-- Add to homescreen for Chrome on Android -->
    <meta name="application-name" content="{{ $card_details->title }}">

    <!-- Add to homescreen for Safari on iOS -->
    <meta name="apple-mobile-web-app-title" content="{{ $card_details->title }}">

    <!-- Tile for Win8 -->
    <meta name="msapplication-TileColor" content="#FFF7ED">
    <meta name="msapplication-TileImage" content="{{ url($business_card_details->profile) }}">

    {!! SEOMeta::generate() !!}
    {!! OpenGraph::generate() !!}
    {!! Twitter::generate() !!}
    {!! JsonLd::generate() !!}

    {{-- Intro Screen CSS --}}
    @if ($introScreen != null)
        <link rel="stylesheet" href="{{ asset('templates/css/intros/' . $introScreen->intro_code . '.min.css') }}">
    @endif

    {{-- Fonts --}}
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600&display=swap" rel="stylesheet" />

    {{-- Tailwind CSS --}}
    <link rel="stylesheet" href="{{ url('templates/css/travel-agency.css') }}">

    {{-- Fontawesome --}}
    <link rel="stylesheet" href="{{ url('css/fontawesome.min.css') }}">

    {{-- Swiper CSS --}}
    <link rel="stylesheet" href="{{ url('css/swiper-bundle.min.css') }}">

    {{-- Flatpickr --}}
    <link rel="stylesheet" href="{{ url('css/flatpickr.min.css') }}">

    {{-- QRious --}}
    <script src="{{ url('js/qrious.min.js') }}"></script>

    {{-- Custom CSS --}}
    <style>
        body {
            font-family: "Outfit", sans-serif;
        }

        button:active {
            transform: scale(0.97);
        }

        a:active {
            transform: scale(0.97);
        }

        .shadow-inner {
            box-shadow: inset 0 0 10px rgba(17, 69, 225, 0.70);
        }
    </style>

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

    {{-- Check PWA --}}
    @if ($plan_details != null)
        @if ($plan_details['pwa'] == 1 && $business_card_details->is_enable_pwa == 1)
            @laravelPWA
            <!-- Web Application Manifest -->
            <link rel="manifest" href="{{ $manifest }}">
        @endif
    @endif
</head>

<body class="bg-blue-100 min-h-screen"
    dir="{{ App::isLocale('ar') || App::isLocale('ur') || App::isLocale('he') ? 'rtl' : 'ltr' }}">

    {{-- Loader --}}
    @if ($introScreen != null)
        <!-- Loader -->
        <div id="loader">
            <div class="spinner"></div>
        </div>
    @endif

    <div id="smooth-wrapper">    
        <div id="smooth-content" class="container max-w-2xl mx-auto bg-blue-200 relative">
            {{-- Start Check password protected --}}
            @if ($business_card_details->password == null || Session::get('password_protected') == true)
                {{-- Check business details --}}
                @if ($business_card_details != null)
                    <div class="shadow-[0_0_4px_rgba(0,0,0,0.1)] overflow-hidden relative">
                        {{-- Index Screen --}}
                        @if ($introScreen != null)
                            @include("templates.includes.intros.{$introScreen->intro_code}", [
                                'theme' => $business_card_details->theme_id
                            ])
                        @endif 

                        <div id="content-screen">                           
                            <!-- Start Cover Image Section -->
                            @if ($business_card_details->cover_type == 'none')
                                <div class="h-60 lg:h-96 relative p-3 lg:p-6" id="profile">
                                    <img src="{{ url('img/templates/travel-agency/banner.png') }}"
                                        alt="{{ $business_card_details->title }}"
                                        class="w-full h-full object-cover rounded-3xl" />
                                </div>
                            @endif
                            <!-- End Cover Image Section -->

                            <!-- Start Cover Image Section -->
                            @if ($business_card_details->cover_type == 'photo')
                                <div class="h-60 lg:h-96 relative p-3 lg:p-6" id="profile">
                                    <img src="{{ $business_card_details->cover ? url($business_card_details->cover) : asset('images/default-cover.png') }}"
                                        alt="{{ $business_card_details->title }}"
                                        class="w-full h-full object-cover rounded-3xl" />
                                </div>
                            @endif
                            <!-- End Cover Image Section -->

                            <!-- Start Cover Video Section (Vimeo AP) -->
                            @if ($business_card_details->cover_type == 'vimeo-ap')
                                <div class="relative w-full p-3 lg:p-6" style="padding-top: 56.25%;" id="profile">
                                    <iframe referrerpolicy="strict-origin-when-cross-origin"
                                        src="https://player.vimeo.com/video/{{ $business_card_details->cover }}?autoplay=1&loop=1&autopause=0&muted=1&controls=0"
                                        id="vid-player" frameborder="0"
                                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                        allowfullscreen class="absolute top-0 left-0 w-full h-full">
                                    </iframe>
                                </div>
                            @endif
                            <!-- End Cover Video Section (Vimeo AP) -->

                            <!-- Start Cover Video Section (Vimeo) -->
                            @if ($business_card_details->cover_type == 'vimeo')
                                <div class="relative w-full p-3 lg:p-6" style="padding-top: 56.25%;" id="profile">
                                    <iframe referrerpolicy="strict-origin-when-cross-origin"
                                        src="https://player.vimeo.com/video/{{ $business_card_details->cover }}?autoplay=0&loop=1&autopause=0&muted=0&controls=1"
                                        id="vid-player" frameborder="0"
                                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                        allowfullscreen class="absolute top-0 left-0 w-full h-full">
                                    </iframe>
                                </div>
                            @endif
                            <!-- End Cover Video Section (Vimeo) -->

                            <!-- Start Cover Video Section (Youtube AP) -->
                            @if ($business_card_details->cover_type == 'youtube-ap')
                                <div class="relative w-full p-3 lg:p-6" style="padding-top: 56.25%;" id="profile">
                                    <iframe referrerpolicy="strict-origin-when-cross-origin"
                                        src="https://www.youtube.com/embed/{{ $business_card_details->cover }}?autoplay=1&mute=1&controls=0&loop=1&playlist={{ $business_card_details->cover }}"
                                        id="vid-player" frameborder="0"
                                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                        allowfullscreen class="absolute top-0 left-0 w-full h-full">
                                    </iframe>
                                </div>
                            @endif
                            <!-- End Cover Video Section (Youtube AP) -->

                            <!-- Start Cover Video Section -->
                            @if ($business_card_details->cover_type == 'youtube')
                                <div class="relative w-full p-3 lg:p-6" style="padding-top: 56.25%;" id="profile">
                                    <iframe referrerpolicy="strict-origin-when-cross-origin"
                                        src="https://www.youtube.com/embed/{{ $business_card_details->cover }}?autoplay=0&mute=1&controls=0&loop=1&playlist={{ $business_card_details->cover }}"
                                        id="vid-player" frameborder="0"
                                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                        allowfullscreen class="absolute top-0 left-0 w-full h-full">
                                    </iframe>
                                </div>
                            @endif
                            <!-- End Cover Video Section -->

                            {{-- Language Switcher --}}
                            @if ($business_card_details->is_enable_language_switcher == 1 && is_array(config('app.languages')) && count(config('app.languages')) > 1)
                                @include('templates.includes.language-switcher')
                            @endif

                            <!-- Profile Info -->
                            <div class="px-3 lg:px-6 pb-24 lg:pb-0">

                                <!-- Start Profile Info -->

                                {{-- Background Image --}}
                                <div class="text-center relative flex">
                                    {{-- Profile Image --}}
                                    <img src="{{ url($business_card_details->profile) }}"
                                        alt="{{ $business_card_details->title }}"
                                        class="w-28 h-28 md:w-36 md:h-36 rounded-full object-cover border-4 border-blue-600" />
                                    <div class="flex flex-col ltr:ml-4 rtl:mr-4 ltr:lg:ml-6 rtl:lg:mr-6">
                                        {{-- Name --}}
                                        <h1 class="lg:text-5xl text-3xl font-medium text-gray-900 head text-start">
                                            {{ $business_card_details->title }}
                                        </h1>
                                        {{-- Job Title --}}
                                        <p class="text-blue-600 text-base font-bold mt-1 text-md text-start">
                                            {{ $card_details->sub_title }}
                                        </p>
                                        {{-- About --}}
                                        @if (optional($business_card_details)->description || optional($business_card_details)->address)
                                            <div
                                                class="mt-2 text-base text-gray-600 leading-relaxed font-medium line-clamp-4 text-start">
                                                {!! $business_card_details->description !!}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                <!-- End Profile Info -->

                                <!-- Start Quick Contact -->
                                @if (count($feature_details) > 0)
                                    <div class="flex justify-center gap-4 m-6">
                                        {{-- Loop through the feature_details array and display the icons --}}
                                        @foreach ($feature_details as $feature)
                                            @if (in_array($feature->type, ['tel', 'address', 'wa', 'instagram']))
                                                {{-- Location --}}
                                                @if ($feature->type == 'address')
                                                    <a href="#location"
                                                        class="bg-blue-200 shadow-inner hover:bg-blue-300 rounded-full flex items-center justify-center w-14 h-14 border border-blue-400 focus:outline-none">
                                                        <i class="{{ $feature->icon }} fa-2xl text-blue-600"></i>
                                                    </a>
                                                @endif
                                                {{-- Phone --}}
                                                @if ($feature->type == 'tel')
                                                    <a href="tel:{{ $feature->content }}"
                                                        class="bg-blue-200 shadow-inner hover:bg-blue-300 rounded-full flex items-center justify-center w-14 h-14 border border-blue-400 focus:outline-none">
                                                        <i class="{{ $feature->icon }} fa-xl text-blue-600"></i>
                                                    </a>
                                                @endif
                                                {{-- WhatsApp --}}
                                                @if ($feature->type == 'wa')
                                                    <a href="https://wa.me/{{ $feature->content }}"
                                                        class="bg-blue-200 shadow-inner hover:bg-blue-300 rounded-full flex items-center justify-center w-14 h-14 border border-blue-400 focus:outline-none">
                                                        <i class="{{ $feature->icon }} fa-2xl text-blue-600"></i>
                                                    </a>
                                                @endif
                                                {{-- Instagram --}}
                                                @if ($feature->type == 'instagram')
                                                    <a href="{{ $feature->content }}" target="_blank"
                                                        class="bg-blue-200 shadow-inner hover:bg-blue-300 rounded-full flex items-center justify-center w-14 h-14 border border-blue-400 focus:outline-none">
                                                        <i class="{{ $feature->icon }} fa-2xl text-blue-600"></i>
                                                    </a>
                                                @endif
                                            @endif
                                        @endforeach
                                    </div>
                                @endif
                                {{-- End Quick Contact --}}

                                <!-- Start Section location -->
                                @if (count($feature_details) > 0)
                                    @foreach ($feature_details as $feature)
                                        @if (in_array($feature->type, ['address']))
                                            <div class="flex justify-center relative">
                                                <!-- Font Awesome Icon -->
                                                <a href="https://www.google.com/maps/place/{{ urlencode($feature->content) }}"
                                                    target="_blank"
                                                    class="p-6 flex flex-col rounded-2xl bg-blue-200 shadow-inner hover:bg-blue-300 w-full border border-blue-400">
                                                    {{-- Icon --}}
                                                    <i class="{{ $feature->icon }} fa-xl text-blue-600 text-2xl py-2"></i>
                                                    <!-- Title -->
                                                    <h2 class="text-lg font-medium mt-1.5 text-blue-600">
                                                        {{ $feature->label }}
                                                    </h2>
                                                    <!-- Description -->
                                                    <p class="text-md flex items-center mt-1 text-gray-800">
                                                        {{ $feature->content }}
                                                    </p>
                                                </a>
                                            </div>
                                        @endif
                                    @endforeach
                                @endif
                                <!-- End Section location -->

                                <!-- Start Social links section -->
                                @if (!empty($feature_details) && count($feature_details) > 0)
                                    @php
                                        // List of excluded feature types
                                        $excludedTypes = ['tel', 'address', 'map', 'iframe', 'youtube', 'instagram', 'wa'];

                                        // Filter the features to include only valid ones
                                        $validFeatures = collect($feature_details)->filter(function ($feature) use (
                                            $excludedTypes,
                                        ) {
                                            return isset($feature->type) && !in_array($feature->type, $excludedTypes);
                                        });
                                    @endphp

                                    @if ($validFeatures->isNotEmpty())
                                        <div class="relative">
                                            <img src="{{ asset('img/templates/travel-agency/1.png') }}" alt=""
                                                class="w-28 lg:w-36 absolute -top-0 lg:-top-2 -right-8 lg:-right-8 opacity-80" />
                                            <img src="{{ asset('img/templates/travel-agency/2.png') }}" alt=""
                                                class="w-28 lg:w-36 absolute -top-0 lg:-top-2 -left-12 lg:-left-12 opacity-80" />
                                            <h2
                                                class="text-3xl lg:text-4xl font-medium text-black py-12 text-center relative ">
                                                <div
                                                    class="absolute bottom-10 left-1/2 h-1.5 w-16 bg-blue-600 -translate-x-1/2 rounded-full">
                                                </div>
                                                {{ __($feature_details[0]->title) }}
                                            </h2>
                                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                                                @foreach ($validFeatures as $feature)
                                                    {{-- Generate href value dynamically --}}
                                                    @php
                                                        $href = $feature->content;
                                                        if ($feature->type == 'wa') {
                                                            $href = 'https://wa.me/' . $feature->content;
                                                        } elseif ($feature->type == 'email') {
                                                            $href = 'mailto:' . $feature->content;
                                                        } elseif ($feature->type == 'text') {
                                                            $href = 'javascript:void(0);';
                                                        }
                                                    @endphp
                                                    <!-- {{ $feature->label }} -->
                                                    <a href="{{ $href }}" target="_blank"
                                                        class="p-4 flex flex-col rounded-2xl bg-blue-200 shadow-inner hover:bg-blue-300 w-full border border-blue-400">
                                                        <!-- Font Awesome Icon -->
                                                        <i class="{{ $feature->icon }} text-blue-600 text-2xl"></i>
                                                        <!-- Title -->
                                                        <h2 class="text-base font-medium mt-1 text-blue-600">
                                                            {{ $feature->label }}</h2>
                                                        <!-- Description -->
                                                        <p class="text-sm truncate text-gray-800">{{ $feature->content }}</p>
                                                    </a>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                @endif
                                <!-- End social links section -->

                                <!-- Start Services Section -->
                                @if (count($service_details) > 0)
                                    <div class="relative">
                                        <img src="{{ asset('img/templates/travel-agency/5.png') }}" alt=""
                                            class="w-24 lg:w-28 absolute top-4 -right-6 lg:-right-6 opacity-80" />
                                        <img src="{{ asset('img/templates/travel-agency/4.png') }}" alt=""
                                            class="w-20 lg:w-28 absolute top-4 -left-6 lg:-left-6 opacity-80" />
                                        <h2
                                            class="text-3xl lg:text-4xl font-medium text-black py-12 text-center relative ">
                                            <div
                                                class="absolute bottom-10 left-1/2 h-1.5 w-16 bg-blue-600 -translate-x-1/2 rounded-full">
                                            </div>
                                            {{ __($service_details[0]->title) }}
                                        </h2>
                                        <div class="">
                                            <swiper-container
                                                breakpoints='{ "640": { "slidesPerView": 1 }, "1024": { "slidesPerView": 2 } }'
                                                space-between="20" class="mySwiper" autoplay-delay="2500"
                                                autoplay-disable-on-interaction="false" loop="true">
                                                {{-- All services --}}
                                                @foreach ($service_details as $service_detail)
                                                    <!-- Service -->
                                                    <swiper-slide class="p-1">
                                                        <div
                                                            class="flex flex-col justify-center p-6 rounded-2xl bg-blue-200 shadow-inner w-full border border-blue-400">
                                                            {{-- Image --}}
                                                            <img class="w-full h-44 object-cover mb-2 rounded-2xl"
                                                                src="{{ url($service_detail->service_image) }}"
                                                                alt="{{ $service_detail->service_name }}" />
                                                            {{-- Name --}}
                                                            <h2 class="text-[#121212] text-lg font-bold mb-1">
                                                                {{ $service_detail->service_name }}
                                                            </h2>
                                                            {{-- Description --}}
                                                            <p class="text-gray-500 font-normal text-sm mb-1">
                                                                {{ $service_detail->service_description }}
                                                            </p>

                                                            <!-- Enquiry Button -->
                                                            @if ($enquiry_button != null)
                                                                @if ($whatsAppNumberExists == true && $whatsAppNumberExists == true && $service_detail->enable_enquiry == 'Enabled')
                                                                    <div class="flex flex-col my-1">
                                                                        <!-- Enquiry Button -->
                                                                        <div class="mt-4 w-full">
                                                                            <a href="https://wa.me/{{ $enquiry_button }}?text={{ __('Hi, I am interested in your product/service:') }} {{ $service_detail->service_name }}. {{ __('Please provide more details.') }}"
                                                                                target="_blank"
                                                                                class="text-white rounded-xl text-lg w-full px-12 lg:w-auto bg-blue-600 border border-blue-700 text-base font-medium py-2 transition-colors block text-center">
                                                                                {{ __('Enquire') }}
                                                                            </a>
                                                                        </div>
                                                                    </div>
                                                                @endif
                                                            @endif
                                                            <!-- End Price & Booking Section -->
                                                        </div>
                                                    </swiper-slide>
                                                @endforeach
                                            </swiper-container>
                                        </div>
                                    </div>
                                @endif
                                <!-- End Services Section -->

                                <!-- Start Products Section -->
                                @if (count($product_details) > 0)
                                    <div class="relative">           
                                        <img src="{{ asset('img/templates/travel-agency/7.png') }}" alt=""
                                                class="w-24 lg:w-28 absolute top-4 lg:top-4 -right-8 lg:-right-8 opacity-80" />
                                            <img src="{{ asset('img/templates/travel-agency/6.png') }}" alt=""
                                                class="w-24 lg:w-28 absolute top-4 lg:top-4 -left-10 lg:-left-10 opacity-80" />                     
                                        <h2
                                            class="text-3xl lg:text-4xl font-medium text-black py-12 text-center relative ">
                                            <div
                                                class="absolute bottom-10 left-1/2 h-1.5 w-16 bg-blue-600 -translate-x-1/2 rounded-full">
                                            </div>
                                            {{ __($product_details[0]->title) }}
                                        </h2>
                                        <swiper-container
                                            breakpoints='{ "640": { "slidesPerView": 1 }, "1024": { "slidesPerView": 2 } }'
                                            space-between="20" class="mySwiper" autoplay-delay="2500"
                                            autoplay-disable-on-interaction="false" loop="true">
                                            {{-- All products --}}
                                            @foreach ($product_details as $product_detail)
                                                <!-- Product -->
                                                <swiper-slide class="p-1">
                                                    <div
                                                        class="flex flex-col justify-center p-6 rounded-2xl bg-blue-200 shadow-inner w-full border border-blue-400">
                                                        {{-- Badge --}}
                                                        @if (!empty($product_detail->badge))
                                                            <p
                                                                class="absolute top-9 right-9 font-medium text-white bg-blue-600 px-4 py-1 rounded-2xl">
                                                                {{ $product_detail->badge }}
                                                            </p>
                                                        @endif
                                                        {{-- Image --}}
                                                        <img class="w-full h-44 object-cover mb-4 rounded-2xl"
                                                            src="{{ url($product_detail->product_image) }}"
                                                            alt="{{ $product_detail->product_name }}" />
                                                        {{-- Name --}}
                                                        <h2 class="text-blue-600 text-lg font-bold mb-2">
                                                            {{ $product_detail->product_name }}
                                                        </h2>
                                                        {{-- Description --}}
                                                        <p class="text-gray-500 font-normal mb-2 text-sm">
                                                            {{ $product_detail->product_description }}
                                                        </p>

                                                        <!-- Price & Booking Section -->
                                                        <div class="flex flex-col my-2">
                                                            <!-- Price -->
                                                            <div class="flex-flex-col">
                                                                @if ($product_detail->sales_price != 0)
                                                                <div>
                                                                    <h4 class="text-lg font-medium">
                                                                        {{ __('Price:') }}<span
                                                                            class="text-blue-600 font-medium ml-1">
                                                                            {{ formatCurrencyVcard($product_detail->sales_price, $product_detail->currency) }}</span>
                                                                        {{-- Check regular price is exists --}}
                                                                        @if ($product_detail->sales_price != $product_detail->regular_price)
                                                                            <span
                                                                                class="line-through ml-2 text-blue-400 text-base font-normal">
                                                                                {{ formatCurrencyVcard($product_detail->regular_price, $product_detail->currency) }}</span>
                                                                        @endif
                                                                    </h4>
                                                                </div>
                                                                @endif

                                                                @if ($product_detail->product_status != 'null')
                                                                <div>
                                                                    <h4 class="text-lg font-medium">
                                                                        {{ __('Stock:') }} <span
                                                                            class="font-medium text-{{ $product_detail->product_status == 'instock' ? 'green-500' : 'red-500' }}">{{ $product_detail->product_status == 'outstock' ? __('Out of Stock') : __('In Stock') }}</span>
                                                                    </h4>
                                                                </div>
                                                                @endif
                                                            </div>

                                                            <!-- Enquiry Button -->
                                                            @if ($enquiry_button != null)
                                                                @if ($whatsAppNumberExists == true)
                                                                    <div class="mt-4 w-full">
                                                                        <a href="https://wa.me/{{ $enquiry_button }}?text={{ __('Hi, I am interested in your product:') }} {{ $product_detail->product_name }}. {{ __('Please provide more details.') }}"
                                                                            target="_blank"
                                                                            class="text-white rounded-2xl text-lg w-full px-12 lg:w-auto bg-blue-600 border border-blue-700 text-base font-medium py-2 transition-colors block text-center">
                                                                            {{ __('Enquire') }}
                                                                        </a>
                                                                    </div>
                                                                @endif
                                                            @endif
                                                        </div>
                                                        <!-- End Price & Booking Section -->
                                                    </div>
                                                </swiper-slide>
                                            @endforeach
                                        </swiper-container>
                                    </div>
                                @endif
                                <!-- End Products Section -->                        

                                <!-- Start Gallery Section with Swiper (Desktop 2 Slides & mobile 1 Slide) -->
                                @if (count($galleries_details) > 0)
                                    <div class="relative">
                                        <img src="{{ asset('img/templates/travel-agency/8.png') }}" alt=""
                                            class="w-28 lg:w-32 absolute -top-4 -right-6 lg:-right-6 opacity-80" />
                                        <img src="{{ asset('img/templates/travel-agency/9.png') }}" alt=""
                                            class="w-24 lg:w-28 absolute top-4 -left-6 lg:-left-6 opacity-80" />
                                        <h2
                                            class="text-3xl lg:text-4xl font-medium text-black py-12 text-center relative ">
                                            <div
                                                class="absolute bottom-10 left-1/2 h-1.5 w-16 bg-blue-600 -translate-x-1/2 rounded-full">
                                            </div>
                                            {{ __($galleries_details[0]->title) }}
                                        </h2>
                                        <div>
                                            <swiper-container class="mySwiper" autoplay-delay="3000"
                                                autoplay-disable-on-interaction="false">
                                                {{-- Slider images --}}
                                                @foreach ($galleries_details as $galleries_detail)
                                                    <swiper-slide
                                                        class="p-6 rounded-2xl bg-blue-200 shadow-inner w-full border border-blue-400">
                                                        <!-- Gallery -->
                                                        <img class="w-full h-full object-cover rounded-2xl"
                                                            src="{{ url($galleries_detail->gallery_image) }}"
                                                            alt="{{ $galleries_detail->caption }}" />

                                                        {{-- Title --}}
                                                        @if ($galleries_detail->caption)
                                                            <div class="text-center px-6 pt-6">
                                                                <h2 class="text-lg font-medium text-gray-800 ">
                                                                    {{ $galleries_detail->caption }}
                                                                </h2>
                                                            </div>
                                                        @endif
                                                    </swiper-slide>
                                                @endforeach
                                            </swiper-container>
                                        </div>
                                    </div>
                                @endif
                                <!-- End Gallery Section -->

                                <!-- Start Youtube Video Section -->
                                @if ($feature_details->where('type', 'youtube')->count() > 0)
                                    <div class="relative">
                                        <h2
                                            class="text-3xl lg:text-4xl font-medium text-black py-12 text-center relative ">
                                            <div
                                                class="absolute bottom-10 left-1/2 h-1.5 w-16 bg-blue-600 -translate-x-1/2 rounded-full">
                                            </div>
                                            {{ __('Youtube Videos') }}
                                        </h2>
                                        <div class="grid sm:grid-cols-2 lg:grid-cols-2 gap-4 items-center">
                                            {{-- Videos --}}
                                            @foreach ($feature_details as $feature)
                                                @if ($feature->type == 'youtube')
                                                    <!-- Video -->
                                                    <div class="rounded-2xl overflow-hidden">
                                                        <iframe referrerpolicy="strict-origin-when-cross-origin" width="100%" height="270"
                                                            src="https://www.youtube.com/embed/{{ $feature->content }}"
                                                            title="{{ $feature->label }}" frameborder="0"
                                                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                                            allowfullscreen></iframe>
                                                        {{-- Add Youtube title --}}
                                                        @if ($feature->label != null)
                                                            <div class="px-5 py-3 bg-blue-300 rounded-b-2xl">
                                                                <div class="mb-2">
                                                                    <div class="text-gray-800 font-medium text-lg mb-2">
                                                                        {{ $feature->label }}
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endif
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                                <!-- End Youtube Video Section -->

                                <!-- Start iframe Section -->
                                @if ($feature_details->where('type', 'iframe')->count() > 0)
                                    <div class="relative">
                                        <h2
                                            class="text-3xl lg:text-4xl font-medium text-black py-12 text-center relative ">
                                            <div
                                                class="absolute bottom-10 left-1/2 h-1.5 w-16 bg-blue-600 -translate-x-1/2 rounded-full">
                                            </div>
                                            {{ __('Iframe') }}
                                        </h2>
                                        <div class="grid grid-cols-1 gap-4 items-center">
                                            <!-- iframe -->
                                            @foreach ($feature_details as $feature)
                                                @if ($feature->type == 'iframe')
                                                    <div class="rounded-2xl overflow-hidden">
                                                        {{-- Content --}}
                                                        <iframe referrerpolicy="strict-origin-when-cross-origin" width="100%" height="270" src="{{ $feature->content }}"
                                                            title="{{ $feature->label }}" frameborder="0"
                                                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                                            allowfullscreen></iframe>
                                                        {{-- Add Iframe title --}}
                                                        @if ($feature->label != null)
                                                            <div class="px-5 py-3 bg-blue-300">
                                                                <div class="mb-2">
                                                                    <div class="text-gray-800 font-medium text-lg mb-2">
                                                                        {{ $feature->label }}
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endif
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                                <!-- End iframe Section -->

                                <!-- Start Client Reviews section with Swiper JS -->
                                @if (count($testimonials) > 0)
                                    <div class="relative">
                                        <img src="{{ asset('img/templates/travel-agency/11.png') }}" alt=""
                                                class="w-24 lg:w-28 absolute top-4 lg:top-4 -right-10 lg:-right-10 opacity-80" />
                                            <img src="{{ asset('img/templates/travel-agency/10.png') }}" alt=""
                                                class="w-24 lg:w-28 absolute top-4 lg:top-4 -left-6 lg:-left-6 opacity-80" />  
                                        <h2
                                            class="text-3xl lg:text-4xl font-medium text-black py-12 text-center relative ">
                                            <div
                                                class="absolute bottom-10 left-1/2 h-1.5 w-16 bg-blue-600 -translate-x-1/2 rounded-full">
                                            </div>
                                            {{ __($testimonials[0]->title) }}
                                        </h2>
                                        <swiper-container class="mySwiper" autoplay-delay="3000"
                                            autoplay-disable-on-interaction="false" loop="true">
                                            {{-- Client Reviews --}}
                                            @foreach ($testimonials as $testimonial)
                                                <div class="swiper-slide">
                                                    <div
                                                        class="p-6 rounded-2xl bg-blue-200 shadow-inner w-full border border-blue-400">
                                                        {{-- Image --}}
                                                        <img src="{{ url($testimonial->reviewer_image) }}"
                                                            alt="{{ $testimonial->reviewer_name }}"
                                                            class="h-16 w-16 rounded-full object-cover mb-2" />
                                                        {{-- Review --}}
                                                        <p class="text-blue-500 text-lg italic">"{{ $testimonial->review }}"
                                                        </p>
                                                        <p class="text-blue-600 font-medium text-md mt-2 text-right mr-2">
                                                            {{-- Name --}}
                                                            - {{ $testimonial->reviewer_name }}
                                                            {{-- Position --}}
                                                            @if ($testimonial->review_subtext)
                                                                <span class="text-gray-500 text-sm font-normal">
                                                                    ({{ $testimonial->review_subtext }})
                                                                </span>
                                                            @endif
                                                        </p>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </swiper-container>
                                    </div>
                                @endif
                                <!-- End Client Reviews section with Swiper JS -->

                                <!-- Start an Appointment section -->
                                @if ($appointmentEnabled == true && isset($plan_details['appointment']) == 1)
                                    <div class="relative">
                                        <img src="{{ asset('img/templates/travel-agency/13.png') }}" alt=""
                                                class="w-24 lg:w-28 absolute top-4 lg:top-4 -right-10 lg:-right-10 opacity-80" />
                                            <img src="{{ asset('img/templates/travel-agency/12.png') }}" alt=""
                                                class="w-28 lg:w-32 absolute top-5 lg:top-2 -left-6 lg:-left-6 opacity-80" /> 
                                        {{-- Check appointment slots in the calendar --}}
                                        @if ($plan_details['appointment'] == 1)
                                            @if ($appointment_slots != null)
                                                {{-- Heading --}}
                                                <h2
                                                    class="text-3xl lg:text-4xl font-medium text-black py-12 text-center relative ">
                                                    <div
                                                        class="absolute bottom-10 left-1/2 h-1.5 w-16 bg-blue-600 -translate-x-1/2 rounded-full">
                                                    </div>
                                                    {{ __(json_decode($appointment_slots, true)['title']) }}
                                                </h2>

                                                <div
                                                    class="p-6 rounded-2xl bg-blue-200 shadow-inner w-full border border-blue-400">
                                                    <!-- Error Message (hidden by default) -->
                                                    <div id="errorMessage" class="text-red-500 text-sm my-2 hidden">
                                                        {{ __('Please select a valid date and time slot.') }}</div>

                                                    {{-- Success Message (hidden by default) --}}
                                                    <div id="successMessage" class="text-green-500 text-sm my-2 hidden">
                                                        {{ __('Appointment booked successfully!') }}</div>

                                                    <!-- Error Message (hidden by default) -->
                                                    <div id="errorSubmitMessage" class="text-red-500 text-sm my-2 hidden">
                                                        {{ __('Please fill all the fields.') }}</div>

                                                    <div class="flex flex-col lg:flex-row justify-between mb-4 gap-2">
                                                        <!-- flatpickr Calendar -->
                                                        <input type="text" id="appointment-date"
                                                            class="w-full lg:w-1/2 flatpickr-input w-full px-4 py-3 text-gray-800 bg-white border border-gray-300 rounded-2xl focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-600 focus:ring-opacity-50"
                                                            placeholder="{{ __('Select a date') }}" />

                                                        <!-- Select time in dropdown -->
                                                        <select id="time-slot-select"
                                                            class="w-full lg:w-1/2 px-4 py-3 text-gray-800 bg-white border border-gray-300 rounded-2xl focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-600 focus:ring-opacity-50">
                                                            <option value="">{{ __('Select a time slot') }}</option>
                                                        </select>
                                                    </div>

                                                    <!-- Booking button -->
                                                    <div class="flex justify-center">
                                                        <button id="add-slot-button"
                                                            class="w-full p-3 bg-blue-600 text-white text-lg text-center rounded-2xl font-medium border border-blue-700"
                                                            onclick="validateAndShowModal()">
                                                            {{ __('Book Appointment') }}
                                                        </button>
                                                    </div>
                                                </div>
                                            @endif
                                        @endif
                                    </div>
                                @endif
                                <!-- End an Application section -->                        

                                {{-- Start Business Hours --}}
                                @if ($plan_details['business_hours'] == 1)
                                    @if ($business_hours != null && $business_hours->is_display != 0)
                                        <section class="relative">
                                        <img src="{{ asset('img/templates/travel-agency/14.png') }}" alt=""
                                                class="w-24 lg:w-28 absolute top-10 lg:top-7 -right-6 lg:-right-6 opacity-80" />
                                            <img src="{{ asset('img/templates/travel-agency/15.png') }}" alt=""
                                                class="w-24 lg:w-28 absolute top-9 lg:top-6 -left-6 lg:-left-6 opacity-80" /> 
                                            <h2
                                                class="text-3xl lg:text-4xl font-medium text-black py-12 text-center relative ">
                                                <div
                                                    class="absolute bottom-10 left-1/2 h-1.5 w-16 bg-blue-600 -translate-x-1/2 rounded-full">
                                                </div>
                                                {{ __($business_hours->title) }}
                                            </h2>
                                            <!-- Business Hours Card -->
                                            <div
                                                class="p-6 rounded-2xl bg-blue-200 shadow-inner w-full border border-blue-400">
                                                @if ($business_hours->is_always_open != 'Opening')
                                                    <!-- Days and Hours List -->
                                                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                                                        @foreach (['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as $day)
                                                            <div class="flex items-center space-x-4">
                                                                <!-- Day Icon -->
                                                                <div
                                                                    class="flex items-center justify-center w-10 h-10 bg-blue-300 text-blue-600 border border-blue-500 rounded-full">
                                                                    <svg xmlns="http://www.w3.org/2000/svg" width="24"
                                                                        height="24" viewBox="0 0 24 24" fill="none"
                                                                        stroke="currentColor" stroke-width="2"
                                                                        stroke-linecap="round" stroke-linejoin="round"
                                                                        class="icon icon-tabler icons-tabler-outline icon-tabler-calendar-clock">
                                                                        <path stroke="none" d="M0 0h24v24H0z"
                                                                            fill="none" />
                                                                        <path
                                                                            d="M10.5 21h-4.5a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v3" />
                                                                        <path d="M16 3v4" />
                                                                        <path d="M8 3v4" />
                                                                        <path d="M4 11h10" />
                                                                        <path d="M18 18m-4 0a4 4 0 1 0 8 0a4 4 0 1 0 -8 0" />
                                                                        <path d="M18 16.5v1.5l.5 .5" />
                                                                    </svg>
                                                                </div>
                                                                <!-- Day and Hours -->
                                                                <div>
                                                                    <p class="text-sm font-medium text-blue-600 capitalize">
                                                                        {{ __($day) }}</p>
                                                                    <p class="text-base text-gray-800">
                                                                        {{ __($business_hours->$day ?: __('Closed')) }}
                                                                    </p>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @else
                                                    <!-- Always Open -->
                                                    <div class="flex items-start space-x-4">
                                                        <!-- Animated Icon -->
                                                        <div
                                                            class="flex items-center justify-center w-12 h-12 bg-blue-300 text-blue-600 rounded-full transform hover:scale-110 transition-transform duration-300 ease-in-out">
                                                            <svg class="w-6 h-6 animate-pulse" fill="currentColor"
                                                                xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                                                                <path
                                                                    d="M12 2a10 10 0 100 20 10 10 0 000-20zM10 16l6-4-6-4v8z" />
                                                            </svg>
                                                        </div>
                                                        <!-- Text -->
                                                        <div>
                                                            <p class="text-xl font-bold text-blue-600">
                                                                {{ __('Always Open') }}</p>
                                                            <p class="text-sm text-gray-600">
                                                                {{ __('We’re available 24/7 to serve you!') }}</p>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        </section>
                                    @endif
                                @endif
                                {{-- End Business Hours --}}

                                {{-- Service Service Booking --}}
                                @if (isset($plan_details['service_booking']) && $plan_details['service_booking'] == 1)
                                    @if (isset($service_booking_details) && $service_booking_details->service_booking == 1)
                                        <div class="relative">
                                            <img src="{{ asset('img/templates/travel-agency/13.png') }}" alt=""
                                                    class="w-24 lg:w-28 absolute top-4 lg:top-4 -right-10 lg:-right-10 opacity-80" />
                                                <img src="{{ asset('img/templates/travel-agency/12.png') }}" alt=""
                                                    class="w-28 lg:w-32 absolute top-5 lg:top-2 -left-6 lg:-left-6 opacity-80" /> 
                                            <h2
                                                class="text-3xl lg:text-4xl font-medium text-black py-12 text-center relative ">
                                                <div
                                                    class="absolute bottom-10 left-1/2 h-1.5 w-16 bg-blue-600 -translate-x-1/2 rounded-full">
                                                </div>
                                                {{ __($service_booking_details->title) }}
                                            </h2>

                                            {{-- Service Booking Form --}}
                                            <div class="rounded-2xl bg-blue-200 shadow-inner w-full border border-blue-400 p-6">
                                                <!-- Error Message (hidden by default) -->
                                                <div id="errorMessage1"
                                                    class="bg-red-500 text-sm my-2 hidden p-3 text-white rounded-xl"></div>

                                                {{-- Success Message (hidden by default) --}}
                                                <div id="successMessage1"
                                                    class="bg-green-500 text-sm my-2 hidden p-3 text-white rounded-xl"></div>

                                                <div class="flex flex-col lg:flex-row lg:gap-4 mb-4">
                                                    {{-- Name --}}
                                                    <div class="flex flex-col w-full lg:w-1/2 mb-4 lg:mb-0">
                                                        <label for="customer_name"
                                                            class="text-gray-800 font-medium mb-2">{{ __('Name') }}</label>
                                                        <input type="text" name="customer_name" id="customer_name"
                                                            placeholder="{{ __('Your Name') }}"
                                                            class="w-full px-4 py-2.5 border border-gray-300 rounded-2xl focus:outline-none focus:border-blue-300 focus:ring-2 focus:ring-blue-600 focus:ring-opacity-50" />
                                                    </div>
                                                    {{-- Email --}}
                                                    <div class="flex flex-col w-full lg:w-1/2">
                                                        <label for="customer_email"
                                                            class="text-gray-800 font-medium mb-2">{{ __('Email') }}</label>
                                                        <input type="email" name="customer_email" id="customer_email"
                                                            placeholder="{{ __('Your Email') }}"
                                                            class="w-full px-4 py-2.5 border border-gray-300 rounded-2xl focus:outline-none focus:border-blue-300 focus:ring-2 focus:ring-blue-600 focus:ring-opacity-50" />
                                                    </div>
                                                </div>
                                                <div class="flex flex-col lg:flex-row lg:gap-4 mb-4">
                                                    {{-- Mobile Number --}}
                                                    <div class="flex flex-col w-full lg:w-1/2 mb-4 lg:mb-0">
                                                        <label for="customer_phone"
                                                            class="text-gray-800 font-medium mb-2">{{ __('Mobile Number') }}</label>
                                                        <input type="tel" name="customer_phone" id="customer_phone"
                                                            placeholder="{{ __('Your Mobile Number') }}"
                                                            class="w-full px-4 py-2.5 border border-gray-300 rounded-2xl focus:outline-none focus:border-blue-300 focus:ring-2 focus:ring-blue-600 focus:ring-opacity-50" />
                                                    </div>
                                                    {{-- No. of Person(s) --}}
                                                    <div class="flex flex-col w-full lg:w-1/2">
                                                        <label for="no_of_persons"
                                                            class="text-gray-800 font-medium mb-2">{{ __('No. of Person(s)') }}</label>
                                                        <input type="number" name="no_of_persons" id="no_of_persons"
                                                            value="1" step="1"
                                                            placeholder="{{ __('No. of Person(s)') }}"
                                                            class="w-full px-4 py-2.5 border border-gray-300 rounded-2xl focus:outline-none focus:border-blue-300 focus:ring-2 focus:ring-blue-600 focus:ring-opacity-50" />
                                                    </div>
                                                </div>
                                                {{-- Address --}}
                                                <div class="flex flex-col mb-4">
                                                    <label for="customer_address"
                                                        class="text-gray-800 font-medium mb-2">{{ __('Address') }}</label>
                                                    <textarea name="customer_address" id="customer_address" placeholder="{{ __('Your Address') }}" rows="3"
                                                        class="w-full px-4 py-2.5 border border-gray-300 rounded-2xl focus:outline-none focus:border-blue-300 focus:ring-2 focus:ring-blue-600 focus:ring-opacity-50"></textarea>
                                                </div>
                                                {{-- Notes --}}
                                                <div class="flex flex-col mb-4">
                                                    <label for="customer_message"
                                                        class="text-gray-800 font-medium mb-2">{{ __('Notes') }}</label>
                                                    <textarea name="customer_notes" id="customer_notes" placeholder="{{ __('Your Message') }}" rows="3"
                                                        class="w-full px-4 py-2.5 border border-gray-300 rounded-2xl focus:outline-none focus:border-blue-300 focus:ring-2 focus:ring-blue-600 focus:ring-opacity-50"></textarea>
                                                </div>
                                                {{-- Service Start Datetime --}}
                                                <div class="flex flex-col mb-4">
                                                    {{-- Date --}}
                                                    <label for="service_start_date"
                                                        class="text-gray-800 font-medium mb-2">{{ __('Service Start DateTime') }}</label>
                                                    <div class="flex flex-row gap-4">
                                                        <div class="flex flex-col w-1/2">
                                                            <input type="text" id="service_start_date"
                                                                name="service_start_date"
                                                                value="{{ $service_booking_details->service_booking_start_date ?? '' }}"
                                                                placeholder="{{ __('Service Start Date') }}"
                                                                class="w-full px-4 py-2.5 border border-gray-300 rounded-2xl focus:outline-none focus:border-blue-300 focus:ring-2 focus:ring-blue-600 focus:ring-opacity-50" />
                                                        </div>
                                                        {{-- Time --}}
                                                        <div class="flex flex-col w-1/2">
                                                            <input type="time" name="service_start_time"
                                                                id="service_start_time"
                                                                value="{{ $service_booking_details->service_booking_start_time ?? '' }}"
                                                                placeholder="{{ __('Service Start Time') }}"
                                                                class="w-full px-4 py-2.5 border border-gray-300 rounded-2xl focus:outline-none focus:border-blue-300 focus:ring-2 focus:ring-blue-600 focus:ring-opacity-50 timepicker" />
                                                        </div>
                                                    </div>
                                                </div>
                                                {{-- Service End Datetime --}}
                                                <div class="flex flex-col mb-4">
                                                    {{-- Date --}}
                                                    <label for="service_end_date"
                                                        class="text-gray-800 font-medium mb-2">{{ __('Service End DateTime') }}</label>
                                                    <div class="flex flex-row gap-4">
                                                        <div class="flex flex-col w-1/2">
                                                            <input type="date" id="service_end_date" name="service_end_date"
                                                                placeholder="{{ __('Service End Date') }}"
                                                                class="w-full px-4 py-2.5 border border-gray-300 rounded-2xl focus:outline-none focus:border-blue-300 focus:ring-2 focus:ring-blue-600 focus:ring-opacity-50" />
                                                        </div>
                                                        {{-- Time --}}
                                                        <div class="flex flex-col w-1/2">
                                                            <input type="time" name="service_end_time" id="service_end_time"
                                                                placeholder="{{ __('Service End Time') }}"
                                                                class="w-full px-4 py-2.5 border border-gray-300 rounded-2xl focus:outline-none focus:border-blue-300 focus:ring-2 focus:ring-blue-600 focus:ring-opacity-50 timepicker" />
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="flex flex-col">
                                                    <button onclick="submitServiceBooking()"
                                                        class="w-full px-4 py-3 bg-blue-600 text-white text-xl font-medium focus:outline-none rounded-xl">
                                                        {{ __('Submit') }}
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                @endif
                                {{-- End Service Booking --}}

                                <!-- Start Payment section -->
                                @if (count($payment_details) > 0)
                                    <div class="relative">
                                        <h2
                                            class="text-3xl lg:text-4xl font-medium text-black py-12 text-center relative ">
                                            <div
                                                class="absolute bottom-10 left-1/2 h-1.5 w-16 bg-blue-600 -translate-x-1/2 rounded-full">
                                            </div>
                                            {{ __($payment_details[0]->title) }}
                                        </h2>
                                        <div class="grid lg:grid-cols-2 gap-4">
                                            {{-- Payment options --}}
                                            @foreach ($payment_details as $payment)
                                                <!-- {{ $payment->label }} -->
                                                <div
                                                    class="p-6 rounded-2xl bg-blue-200 shadow-inner w-full border border-blue-400">
                                                    <div class="flex justify-between items-center">
                                                        {{-- Payment icon/image --}}
                                                        @include('templates.partials.payment-link-image', [
                                                            'iconColor' => 'text-blue-600',
                                                        ])

                                                        <!-- Payment link icon -->
                                                        @if ($payment->type == 'url')
                                                            <a href="https://{{ str_replace('https://', '', $payment->content) }}"
                                                                target="_blank" rel="noopener noreferrer">

                                                                <svg xmlns="http://www.w3.org/2000/svg" width="24"
                                                                    height="24" viewBox="0 0 24 24" fill="none"
                                                                    stroke="currentColor" stroke-width="2"
                                                                    stroke-linecap="round" stroke-linejoin="round"
                                                                    class="icon icon-tabler icons-tabler-outline icon-tabler-external-link text-blue-600 h-6 w-6">
                                                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                                    <path
                                                                        d="M12 6h-6a2 2 0 0 0 -2 2v10a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-6" />
                                                                    <path d="M11 13l9 -9" />
                                                                    <path d="M15 4h5v5" />
                                                                </svg>
                                                            </a>
                                                        @endif

                                                        {{-- UPI Payment --}}
                                                        @if ($payment->type == 'upi')
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="24"
                                                                height="24" viewBox="0 0 24 24" fill="none"
                                                                stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                                                stroke-linejoin="round"
                                                                class="icon icon-tabler icons-tabler-outline icon-tabler-external-link text-blue-600 h-6 w-6">
                                                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                                <path
                                                                    d="M12 6h-6a2 2 0 0 0 -2 2v10a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-6" />
                                                                <path d="M11 13l9 -9" />
                                                                <path d="M15 4h5v5" />
                                                            </svg>
                                                        @endif
                                                    </div>
                                                    <h3
                                                        class="font-medium text-gray-800 {{ $payment->type == 'text' ? 'py-3' : 'pt-3' }}">
                                                        {{ $payment->label }}</h3>
                                                    <!-- Bank Details (Optional) -->
                                                    @if ($payment->type == 'text')
                                                        <p class="text-gray-600 text-sm break-word text-base">
                                                            @foreach (explode('.', $payment->content) as $sentence)
                                                                @if (trim($sentence))
                                                                    <!-- Make sure the sentence is not empty -->
                                                                    {{ trim($sentence) }}
                                                                    <br> <!-- Break the line after each sentence -->
                                                                @endif
                                                            @endforeach
                                                        </p>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                                <!-- End Payment section -->

                                <!-- Start Location section -->
                                @if (count($feature_details) > 0 && $feature_details->contains('type', 'map'))
                                    <div class="relative">
                                        <img src="{{ asset('img/templates/travel-agency/17.png') }}" alt=""
                                                class="w-24 lg:w-28 absolute top-4 lg:top-3 -right-10 lg:-right-10 opacity-80" />
                                            <img src="{{ asset('img/templates/travel-agency/16.png') }}" alt=""
                                                class="w-28 lg:w-32 absolute top-5 lg:top-2 -left-10 lg:-left-10 opacity-80" /> 
                                        <h2
                                            class="text-3xl lg:text-4xl font-medium text-black py-12 text-center relative ">
                                            <div
                                                class="absolute bottom-10 left-1/2 h-1.5 w-16 bg-blue-600 -translate-x-1/2 rounded-full">
                                            </div>
                                            {{ __('Location') }}
                                        </h2>
                                        {{-- Google Maps --}}
                                        @foreach ($feature_details as $feature)
                                            @if ($feature->type == 'map')
                                                <iframe referrerpolicy="strict-origin-when-cross-origin" src="https://www.google.com/maps/embed?{!! $feature->content !!}"
                                                    width="100%" height="300" style="border: 0" allowfullscreen=""
                                                    loading="lazy" class="rounded-t-2xl">
                                                </iframe>
                                                {{-- Map title --}}
                                                @if ($feature->label != null)
                                                    <div class="px-5 py-3 bg-blue-300 rounded-b-2xl">
                                                        <div class="mb-2">
                                                            <div class="text-gray-800 font-medium text-lg mb-2">
                                                                {{ $feature->label }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                            @endif
                                        @endforeach
                                    </div>
                                @endif
                                <!-- End Location section -->

                                <!-- Start Google Wallet section -->
                                @if (is_dir(base_path('plugins/GoogleWallet')))
                                    @if (isset($plan_details['google_wallet']) && $plan_details['google_wallet'] == 1 && $business_card_details->is_google_wallet_hidden == 0)
                                        <div class="relative">
                                            <h2
                                                class="text-3xl lg:text-4xl font-medium text-black py-12 text-center relative ">
                                                <div
                                                    class="absolute bottom-10 left-1/2 h-1.5 w-16 bg-blue-600 -translate-x-1/2 rounded-full">
                                                </div>
                                                {{ __('Google Wallet') }}
                                            </h2>

                                            <div class="w-full max-w-full p-6 rounded-2xl bg-blue-200 shadow-inner w-full border border-blue-400">
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

                                <!-- Start Contact form section -->
                                @if ($plan_details['contact_form'] == 1)
                                    @if ($business_card_details->enquiry_email != null)
                                        <div class="relative pb-6">
                                            <img src="{{ asset('img/templates/travel-agency/18.png') }}" alt=""
                                                class="w-24 lg:w-28 absolute top-9 lg:top-6 right-2 lg:right-0 opacity-80" />
                                            <img src="{{ asset('img/templates/travel-agency/19.png') }}" alt=""
                                                class="w-24 lg:w-28 absolute top-6 lg:top-4 -left-4 lg:-left-6 opacity-80" /> 
                                            <h2
                                                class="text-3xl lg:text-4xl font-medium text-black py-12 text-center relative ">
                                                <div
                                                    class="absolute bottom-10 left-1/2 h-1.5 w-16 bg-blue-600 -translate-x-1/2 rounded-full">
                                                </div>
                                                {{ __($business_card_details->contact_form_title) }}
                                            </h2>

                                            {{-- Message Alert --}}
                                            @if (Session::has('message'))
                                                <div
                                                    class="px-6 py-4 bg-blue-100 border-t-4 border-blue-100 rounded-lg shadow-md mb-6">
                                                    <div class="flex items-start">
                                                        <div class="mr-4">
                                                            <svg class="w-6 h-6 text-gray-800"
                                                                xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                                                fill="currentColor">
                                                                <path
                                                                    d="M2.93 17.07A10 10 0 1 1 17.07 2.93 10 10 0 0 1 2.93 17.07zm12.73-1.41A8 8 0 1 0 4.34 4.34a8 8 0 0 0 11.32 11.32zM9 11V9h2v6H9v-4zm0-6h2v2H9V5z" />
                                                            </svg>
                                                        </div>
                                                        <div>
                                                            <p class="font-medium text-gray-800">
                                                                {{ Session::get('message') }}</p>
                                                            <p class="text-sm text-gray-600">
                                                                {{ __('Please wait for the reply to be sent.') }}</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif

                                            <form
                                                class="w-full max-w-full p-6 rounded-2xl bg-blue-200 shadow-inner w-full border border-blue-400"
                                                action="{{ config('app.url') }}/sent-enquiry" method="POST">
                                                @csrf
                                                <!-- Grid Layout -->
                                                <div class="grid grid-cols-1 gap-2 lg:gap-6 lg:grid-cols-2">
                                                    <!-- Left Side Inputs -->
                                                    <div class="grid grid-cols-1 gap-2">
                                                        <input type="hidden" name="card_id"
                                                            value="{{ $business_card_details->card_id }}" />
                                                        {{-- Name --}}
                                                        <div>
                                                            <label for="name"
                                                                class="text-gray-800 font-medium mb-2 block">{{ __('Name') }}</label>
                                                            <input type="text" name="name"
                                                                placeholder="{{ __('Your Name') }}"
                                                                class="w-full px-4 py-2.5 border border-gray-300 rounded-2xl focus:outline-none focus:border-blue-300 focus:ring-2 focus:ring-blue-600 focus:ring-opacity-50"
                                                                required />
                                                        </div>
                                                        {{-- Email --}}
                                                        <div>
                                                            <label for="email"
                                                                class="text-gray-800 font-medium mb-2 block">{{ __('Email') }}</label>
                                                            <input type="email" name="email"
                                                                placeholder="{{ __('Your Email') }}"
                                                                class="w-full px-4 py-2.5 border border-gray-300 rounded-2xl focus:outline-none focus:border-blue-300 focus:ring-2 focus:ring-blue-600 focus:ring-opacity-50"
                                                                required />
                                                        </div>
                                                        {{-- Mobile Number --}}
                                                        <div>
                                                            <label for="phone"
                                                                class="text-gray-800 font-medium mb-2 block">{{ __('Mobile Number') }}</label>
                                                            <input type="tel" name="phone"
                                                                placeholder="{{ __('Your Mobile Number') }}"
                                                                class="w-full px-4 py-2.5 border border-gray-300 rounded-2xl focus:outline-none focus:border-blue-300 focus:ring-2 focus:ring-blue-600 focus:ring-opacity-50"
                                                                required />
                                                        </div>
                                                    </div>

                                                    <!-- Right Side Textarea -->
                                                    <div class="h-full pb-8">
                                                        <label for="message"
                                                            class="text-gray-800 font-medium mb-2 block">{{ __('Message') }}</label>
                                                        <textarea name="message" placeholder="{{ __('Your Message') }}"
                                                            class="w-full h-full px-4 py-2 border border-gray-300 rounded-2xl focus:outline-none focus:ring-2 focus:ring-blue-600 focus:ring-opacity-50 focus:border-blue-300 resize-none"
                                                            required style="min-height: 10rem"></textarea>
                                                    </div>

                                                    {{-- ReCaptcha --}}
                                                    @include('templates.includes.recaptcha', ['recaptchaId' => 'recaptcha-one'])

                                                </div>

                                                <!-- Submit Button -->
                                                <div class="mt-6">
                                                    <button type="submit"
                                                        class="w-full px-4 py-3 bg-blue-600 text-white text-xl font-medium rounded-2xl focus:outline-none border border-blue-600 hover:bg-blue-2000">
                                                        {{ __('Send') }}
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    @endif
                                @endif
                                <!-- End Contact form section -->

                                <!-- Branding Section -->
                                @if ($plan_details['hide_branding'] == 1)
                                    <div class="py-6">
                                        <div class="mt-2 text-gray-700 w-full text-center">
                                            {{ __('Copyright') }} &copy;
                                            <a class="text-blue-600" href="{{ url()->current() }}">
                                                {{ $card_details->title }}</a><span
                                                id="year"></span>{{ __('. All Rights Reserved.') }}
                                        </div>
                                    </div>
                                @else
                                    <div class="py-6">
                                        <div class="mt-2 text-gray-700 w-full text-center">
                                            {{ __('Made with') }}
                                            <a class="text-blue-600" href="{{ env('APP_URL') }}">
                                                {{ config('app.name') }} </a>
                                            <span id="year"></span>{{ __('. All Rights Reserved.') }}
                                        </div>
                                    </div>
                                @endif
                                <!-- Branding Section -->
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Start Floating icon button bar section -->
                <div
                    class="fixed w-11/12 left-1/2 bottom-4 bg-blue-400/60 border border-blue-500 rounded-full backdrop-blur-md z-20 py-4 px-3 flex lg:hidden md:hidden transform -translate-x-1/2">
                    <!-- Profile Icon -->
                    <div class="flex-1 flex items-center justify-center">
                        <a class="border border-blue-500 p-3 rounded-full bg-blue-100" href="#profile">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round"
                                class="icon icon-tabler icons-tabler-outline icon-tabler-user text-blue-600 h-6 w-6">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                <path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0" />
                                <path d="M6 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" />
                            </svg>
                        </a>
                    </div>

                    <!-- Send Icon -->
                    <div class="flex-1 flex items-center justify-center">
                        <button class="border border-blue-500 p-3 rounded-full bg-blue-100"
                            onclick="toggleWhatsAppModal(true)">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round"
                                class="icon icon-tabler icons-tabler-outline icon-tabler-send text-blue-600 h-6 w-6">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                <path d="M10 14l11 -11" />
                                <path d="M21 3l-6.5 18a.55 .55 0 0 1 -1 0l-3.5 -7l-7 -3.5a.55 .55 0 0 1 0 -1l18 -6.5" />
                            </svg>
                        </button>
                    </div>

                    <!-- Download Icon -->
                    <div class="flex-1 flex items-center justify-center">
                        <a href="{{ config('app.url') }}/download/{{ $business_card_details->card_id }}"
                            class="border border-blue-500 p-3 rounded-full bg-blue-100">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round"
                                class="icon icon-tabler icons-tabler-outline icon-tabler-download text-blue-600 h-6 w-6">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                <path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2 -2v-2" />
                                <path d="M7 11l5 5l5 -5" />
                                <path d="M12 4l0 12" />
                            </svg>
                        </a>
                    </div>

                    <!-- Scan Icon -->
                    <div class="flex-1 flex items-center justify-center">
                        <button class="border border-blue-500 p-3 rounded-full bg-blue-100"
                            onclick="toggleScanModal(true)">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round"
                                class="icon icon-tabler icons-tabler-outline icon-tabler-line-scan text-blue-600 h-6 w-6">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                <path d="M4 8v-2a2 2 0 0 1 2 -2h2" />
                                <path d="M4 16v2a2 2 0 0 0 2 2h2" />
                                <path d="M16 4h2a2 2 0 0 1 2 2v2" />
                                <path d="M16 20h2a2 2 0 0 0 2 -2v-2" />
                                <path d="M7 12h10" />
                            </svg>
                        </button>
                    </div>

                    <!-- Share Icon -->
                    <div class="flex-1 flex items-center justify-center">
                        <button class="border border-blue-500 p-3 rounded-full bg-blue-100"
                            onclick="shareToggleModal(true)">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round"
                                class="icon icon-tabler icons-tabler-outline icon-tabler-share text-blue-600 h-6 w-6">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                <path d="M6 12m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0" />
                                <path d="M18 6m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0" />
                                <path d="M18 18m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0" />
                                <path d="M8.7 10.7l6.6 -3.4" />
                                <path d="M8.7 13.3l6.6 3.4" />
                            </svg>
                        </button>
                    </div>
                </div>
                <!-- End Floating icon button bar section -->
            @endif
            {{-- End Check password protected --}}

            <!-- Start Apointment Modal (By default hidden) -->
            <div id="appointmentModal"
                class="fixed inset-0 bg-gray-800 bg-opacity-50 flex items-center justify-center z-50 hidden">
                <!-- Modal Content -->
                <div class="bg-white rounded-xl w-full max-w-md p-6 mx-4 shadow-lg">
                    <!-- Modal Header -->
                    <div class="flex justify-center items-center mb-4">
                        <h2 class="text-xl font-bold text-gray-800">{{ __('Book Appointment') }}</h2>
                    </div>

                    <!-- Appointment Form -->
                    <form id="appointmentForm">
                        <!-- Name Field -->
                        <div class="mb-4">
                            <label for="name"
                                class="block text-sm font-medium text-gray-800">{{ __('Name') }}</label>
                            <input type="text" id="name"
                                class="mt-1 p-2 border border-gray-300 rounded-lg w-full" required>
                        </div>

                        <!-- Email Field -->
                        <div class="mb-4">
                            <label for="email"
                                class="block text-sm font-medium text-gray-800">{{ __('Email') }}</label>
                            <input type="email" id="email"
                                class="mt-1 p-2 border border-gray-300 rounded-lg w-full" required>
                        </div>

                        <!-- Phone Field -->
                        <div class="mb-4">
                            <label for="phone"
                                class="block text-sm font-medium text-gray-800">{{ __('Phone') }}</label>
                            <input type="text" id="phone"
                                class="mt-1 p-2 border border-gray-300 rounded-lg w-full" required>
                        </div>

                        <!-- Notes Field -->
                        <div class="mb-4">
                            <label for="notes"
                                class="block text-sm font-medium text-gray-800">{{ __('Notes') }}</label>
                            <textarea id="notes" class="mt-1 p-2 border border-gray-300 rounded-lg w-full" rows="3"></textarea>
                        </div>

                        <!-- Hidden Price Field -->
                        <div class="mb-4 hidden">
                            <label for="price"
                                class="block text-sm font-medium text-gray-800">{{ __('Price') }}</label>
                            <input type="text" id="price"
                                class="mt-1 p-2 border border-gray-300 rounded-lg w-full" disabled>
                        </div>

                        {{-- ReCaptcha --}}
                        @include('templates.includes.recaptcha', ['recaptchaId' => 'recaptcha-two'])

                        <!-- Submit and Close Buttons -->
                        <div class="flex justify-between">
                            <button type="button" class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600"
                                onclick="validateAndShowModal()">
                                {{ __('Close') }}
                            </button>
                            <button type="submit" id="bookAppointmentButton"
                                class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-600">
                                {{ __('Submit') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            {{-- End Appointment Modal --}}

            <!-- Share Modal -->
            <div id="shareModal"
                class="fixed inset-0 bg-gray-800 bg-opacity-50 flex items-center justify-center hidden z-50"
                onclick="shareToggleModal(false)">
                <!-- Modal content -->
                <div class="bg-white rounded-xl w-full max-w-md p-6 mx-4 space-y-6" onclick="event.stopPropagation()">
                    <!-- Modal header -->
                    <div class="flex justify-center items-center">
                        <h2 class="text-2xl text-center font-bold">{{ __('Share on') }}</h2>
                    </div>

                    <!-- QR Code Section -->
                    <div class="flex justify-center">
                        <canvas id="shareQrCode"></canvas>
                    </div>

                    <!-- Share via Social Media -->
                    <div class="flex justify-around text-blue-600">
                        <a href="{{ $shareComponent['facebook'] }}" target="_blank" class="hover:text-blue-600">
                            <i class="fab fa-facebook fa-2x"></i>
                        </a>
                        <a href="{{ $shareComponent['twitter'] }}" target="_blank" class="hover:text-blue-600">
                            <i class="fab fa-twitter fa-2x"></i>
                        </a>
                        <a href="{{ $shareComponent['linkedin'] }}" target="_blank" class="hover:text-blue-600">
                            <i class="fab fa-linkedin fa-2x"></i>
                        </a>
                        <a href="{{ $shareComponent['whatsapp'] }}" target="_blank" class="hover:text-blue-600">
                            <i class="fab fa-whatsapp fa-2x"></i>
                        </a>
                        <a href="{{ $shareComponent['telegram'] }}" target="_blank" class="hover:text-blue-600">
                            <i class="fab fa-telegram fa-2x"></i>
                        </a>
                    </div>

                    <!-- Copy Link Section -->
                    <div class="flex justify-center">
                        <button onclick="copyLink()"
                            class="bg-blue-600 text-white font-medium py-2.5 px-4 rounded-xl w-full border border-blue-600">
                            {{ __('Copy Link') }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- WhatsApp Modal -->
            <div id="whatsappModal"
                class="fixed inset-0 bg-gray-800 bg-opacity-50 flex items-center justify-center hidden z-50"
                onclick="toggleWhatsAppModal(false)">
                <!-- Modal content (stops propagation to prevent closing when clicking inside) -->
                <div class="bg-blue-100 border-blue-300 rounded-3xl w-full max-w-md p-6 mx-4 space-y-6"
                    onclick="event.stopPropagation()">
                    <!-- Input for WhatsApp number -->
                    <div>
                        <label for="whatsappNumber"
                            class="block text-gray-800">{{ __('Enter WhatsApp Number') }}:</label>
                        <input type="text" id="whatsappNumber" placeholder="e.g., +919876543210"
                            class="w-full mt-1 px-4 py-2 border border-gray-300 rounded-xl focus:outline-none focus:border-blue-300 focus:ring-2 focus:ring-blue-600 focus:ring-opacity-50" />
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-center">
                        <button onclick="sendMessage()"
                            class="bg-blue-600 text-white font-bold py-2 px-4 rounded-xl w-full border border-blue-600">
                            {{ __('Send') }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Scan Modal -->
            <div id="scanModal"
                class="fixed inset-0 bg-gray-800 bg-opacity-50 flex items-center justify-center qr-modal hidden z-50"
                onclick="toggleScanModal(false)">
                <!-- Modal content (stops propagation to prevent closing when clicking inside) -->
                <div class="rounded-2xl w-full max-w-md p-6 mx-4 space-y-6 border-blue-300 bg-blue-100 qr-modal-overlay"
                    onclick="event.stopPropagation()">
                    <!-- Qr Code -->
                    <div class="flex justify-center flex-col items-center">
                        <div class="qr-code mb-2"></div>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-center">
                        <button onclick="downloadQr('{{ config('app.url') . route('dynamic.card', $business_card_details->card_id, false) }}', 500)"
                            id="download" class="bg-blue-200 border border-blue-300 font-bold p-3 rounded-full">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round"
                                class="icon icon-tabler icons-tabler-outline icon-tabler-download text-blue-600 h-6 w-6">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                <path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2 -2v-2" />
                                <path d="M7 11l5 5l5 -5" />
                                <path d="M12 4l0 12" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Start Check password protected Modal --}}
            @if ($business_card_details->password != null && Session::get('password_protected') == false)
                <div class="p-4 flex items-center justify-center">
                    <div x-data="{ showModal: true }">
                        <!-- Modal -->
                        <div x-show="showModal" class="fixed inset-0 flex items-center justify-center z-50 p-3">
                            <div class="bg-white rounded-lg p-6 w-96 max-w-full shadow-lg transform transition-all duration-300"
                                x-show.transition.opacity="showModal">
                                <!-- Modal Header -->
                                <div class="flex justify-between items-center border-b-2 border-gray-200 pb-4">
                                    <h2 class="text-2xl font-medium">{{ __('Password Protected') }}</h2>
                                </div>

                                <!-- Modal Content -->
                                <div class="mt-6 space-y-4">
                                    <form action="{{ config('app.url') }}/check-password/{{ $business_card_details->card_id }}"
                                        method="post">
                                        @csrf
                                        <p class="text-lg text-gray-600">{{ __('Enter your vcard Password') }}</p>
                                        <div class="flex">
                                            <input type="password" name="password"
                                                class="rounded rounded-r-lg bg-blue-100 border text-blue-800 focus:ring-blue-100 focus:border-blue-100 block flex-1 min-w-0 w-full text-sm border-blue-100 p-2.5"
                                                placeholder="{{ __('Password') }}" required>
                                        </div>

                                        {{-- Message --}}
                                        @if (Session::has('message'))
                                            <div class="flex items-center p-4 my-4 text-sm text-red-800 rounded-lg bg-red-50 dark:bg-blue-800 dark:text-red-400"
                                                role="alert">
                                                <svg class="flex-shrink-0 inline w-4 h-4 mr-3 text-gray-50"
                                                    aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                                    fill="currentColor" viewBox="0 0 20 20">
                                                    <path
                                                        d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z" />
                                                </svg>
                                                <span class="sr-only">{{ __('Failed') }}</span>
                                                <div>
                                                    <span
                                                        class="font-medium text-gray-50">{{ Session::get('message') }}</span>
                                                </div>
                                            </div>
                                        @endif

                                        <div class="flex flex-col space-y-4 mt-3">
                                            <button type="submit"
                                                class="bg-blue-1000 text-white px-4 py-2 mt-2 rounded-lg hover:bg-blue-2000 transition duration-300">{{ __('Password') }}</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <!-- Include PWA modal -->
                @if ($plan_details != null)
                    {{-- Check PWA --}}
                    @if ($plan_details['pwa'] == 1 && $business_card_details->is_enable_pwa == 1)
                        @include('vendor.laravelpwa.new_pwa_modal', [
                            'primary_color' => 'blue',
                            'img' => $business_card_details->profile,
                        ])
                    @endif
                @endif

                {{-- Include Newsletter Modal --}}
                @if ($business_card_details != null)
                    {{-- Check Newsletter --}}
                    @if (!empty($business_card_details->is_newsletter_pop_active) && $business_card_details->is_newsletter_pop_active == 1)
                        @include('templates.includes.old_theme_newsletter_modal', [
                            'primary_color' => 'blue',
                        ])
                    @endif
                @endif

                {{-- Include Information Popup Modal --}}
                @if ($business_card_details != null)
                    {{-- Check Information Popup --}}
                    @if (!empty($business_card_details->is_info_pop_active) && $business_card_details->is_info_pop_active == 1)
                        @include('templates.includes.information_popup_modal', [
                            'primary_color' => 'blue',
                        ])
                    @endif
                @endif
            @endif
            {{-- End Check password protected Modal --}}
        </div>
    </div>

    {{-- Jquery --}}
    <script src="{{ url('js/jquery.min.js') }}"></script>
    {{-- Smooth Scroll --}}
    <script src="{{ url('js/smooth-scroll.polyfills.min.js') }}"></script>
    {{-- Other JS --}}
    <script type="text/javascript" src="{{ url('app/js/footer.js') }}"></script>
    {{-- Swiper JS --}}
    <script src="{{ url('js/swiper-element-bundle.min.js') }}"></script>
    {{-- Flatpicker --}}
    <script src="{{ url('js/flatpickr.min.js') }}"></script>
    {{-- Custom JS --}}
    @yield('custom-js')

    {{-- Flatpickr JS --}}
    <script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/l10n/{{ app()->getLocale() }}.js"></script>
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

        // Show reCAPTCHA widget instances globally
        function onloadCallback() {
            window.recaptchaWidgets = window.recaptchaWidgets || {};

            // Check if grecaptcha is available
            if (typeof grecaptcha !== 'undefined' && typeof grecaptcha.render === 'function') {

                // Render 'recaptcha-one'
                if (!window.recaptchaWidgets['recaptcha-one'] && document.getElementById('recaptcha-one')) {
                    window.recaptchaWidgets['recaptcha-one'] = grecaptcha.render('recaptcha-one', {
                        'sitekey': '{{ env('RECAPTCHA_SITE_KEY') }}'
                    });
                }

                // Render 'recaptcha-two'
                if (!window.recaptchaWidgets['recaptcha-two'] && document.getElementById('recaptcha-two')) {
                    window.recaptchaWidgets['recaptcha-two'] = grecaptcha.render('recaptcha-two', {
                        'sitekey': '{{ env('RECAPTCHA_SITE_KEY') }}'
                    });
                }

            } else {
                console.error('grecaptcha is not loaded yet.');
            }
        }

        // Handle appointment form submission
        document.getElementById('appointmentForm').addEventListener('submit', function(event) {
            "use strict";

            event.preventDefault();

            const button = document.getElementById('bookAppointmentButton');
            const errorSubmitMessage = document.getElementById('errorMessage');
            const successMessage = document.getElementById('successMessage');

            // Show loader on button
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

            // Add reCAPTCHA response if enabled
            @if(env('RECAPTCHA_ENABLE') == 'on')
                formData.g_recaptcha_response = grecaptcha.getResponse(window.recaptchaWidgets['recaptcha-one']);

                if (!formData.g_recaptcha_response) {
                    // Try second reCAPTCHA widget
                    formData.g_recaptcha_response = grecaptcha.getResponse(window.recaptchaWidgets['recaptcha-two']);
                }
            @endif

            // Send data via fetch
            fetch("{{ config('app.url') }}/book-appointment", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(formData)
            })
            .then(async response => {
                const data = await response.json();

                if (response.ok) {
                    // Reset form
                    ['name', 'email', 'phone', 'notes', 'appointment-date', 'time-slot-select', 'price'].forEach(id => {
                        document.getElementById(id).value = '';
                    });

                    generateOption("", "");

                    successMessage.classList.remove('hidden');
                    errorSubmitMessage.classList.add('hidden');

                    // Reset reCAPTCHA
                    @if(env('RECAPTCHA_ENABLE') == 'on')
                        grecaptcha.reset(window.recaptchaWidgets['recaptcha-one']);
                    @endif

                    toggleModal();

                    // Redirect to whatsapp url
                    if (data.success && data.whatsapp_url && data.whatsapp_url !== '#') {
                        setTimeout(() => {
                            window.location.href = data.whatsapp_url;
                        }, 3000);
                    };
                } else {
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
                console.error('Request failed:', error);
                toggleModal();

                button.disabled = false;
                button.innerHTML = `{{ __('Book Appointment') }}`;
            });
        });
    </script>
    <script>
        function generateOption(selectedDate, day) {
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
                        document.getElementById('time-slot-select').innerHTML =
                            `<option value="">{{ __('Select a time slot') }}`;
                        // Available time slots in JSON.parse(data.available_time_slots)
                        var available_time_slots = JSON.parse(data.available_time_slots);

                        available_time_slots.forEach(time_slot => {
                            document.getElementById('time-slot-select').innerHTML +=
                                `<option value="${time_slot}">${time_slot}</option>`;
                        });

                        // Set price
                        const priceElement = document.getElementById('price');
                        priceElement.value = data.price;
                    }
                });
        }
    </script>
    {{-- Custom JS --}}
    <script>
        // Generate QR Code and place in shareQrCode using qrious
        const qr = new QRious({
            element: document.getElementById('shareQrCode'),
            value: `{{ config('app.url') . route('dynamic.card', $business_card_details->card_id, false) }}`,
            size: 200,
            background: 'white', // Background color
            foreground: 'black', // Foreground (QR code) color
            level: 'H' // Error correction level
        });

        // Share Modal
        function shareToggleModal(show) {
            "use strict";

            document
                .getElementById("shareModal")
                .classList.toggle("hidden", !show);
        }

        // Function to toggle WhatsApp modal visibility
        function toggleScanModal(show) {
            "use strict";

            document
                .getElementById("scanModal")
                .classList.toggle("hidden", !show);
        }

        // Generate QR Code
        window.onload = function() {
            "use strict";

            updateQr(`{{ config('app.url') . route('dynamic.card', $business_card_details->card_id, false) }}`);
        };

        // Copy Link
        function copyLink() {
            "use strict";

            // From browser url to clipboard
            navigator.clipboard.writeText(`{{ config('app.url') . route('dynamic.card', $business_card_details->card_id, false) }}`);
            alert("Link copied to clipboard!");
        }

        // Function to toggle WhatsApp modal visibility
        function toggleWhatsAppModal(show) {
            "use strict";

            document
                .getElementById("whatsappModal")
                .classList.toggle("hidden", !show);
        }

        // Function to send WhatsApp message
        function sendMessage() {
            "use strict";

            const phoneNumber = document
                .getElementById("whatsappNumber")
                .value.trim();
            const whatsappModal = document.getElementById("whatsappModal");

            if (phoneNumber) {
                const message = `{{ $shareContent }}`;
                const whatsappUrl = `https://wa.me/${phoneNumber}?text=${message}`;

                // Open the URL in a new tab
                window.open(whatsappUrl, "_blank");
                whatsappModal.classList.add("hidden"); // Close the modal
                // Reset the input field
                document.getElementById("whatsappNumber").value = "";
            } else {
                alert(`{{ __('Please enter a valid WhatsApp number.') }}`);
            }
        }
    </script>
    <script>
        // Initialize smooth scroll
        const scroll = new SmoothScroll('a[href*="#"]', {
            speed: 300, // Duration of scroll in milliseconds
            offset: 50, // Offset in pixels from the top
            easing: "easeInOutCubic", // Scroll easing function
        });

        @if ($introScreen != null)
            // Wait until all assets are fully loaded
            window.addEventListener("load", () => {
                const loader = document.getElementById("loader");
                loader.classList.add("hidden");
            });
        @endif
    </script>
</body>

</html>
