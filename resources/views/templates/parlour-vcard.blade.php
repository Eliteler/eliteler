<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
 
<?php
use Illuminate\Support\Facades\Session;
use App\BusinessCardIntro;
 
if (isset($service_booking_details) && $service_booking_details->service_booking == 1) {
    $service_booking_available_days = json_decode($service_booking_details->service_booking_available_days);
}

$introScreen = BusinessCardIntro::where('business_card_intro_id', $business_card_details->intro_screen ?? null)
    ->where('status', 1)
    ->first();
?>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @if (isset($business_card_details->seo_configurations) &&
            json_decode($business_card_details->seo_configurations)->favicon != null)
        <link rel="icon" href="{{ url(json_decode($business_card_details->seo_configurations)->favicon) }}"
            sizes="512x512" type="image/png" />
        <link rel="apple-touch-icon" href="{{ url(json_decode($business_card_details->seo_configurations)->favicon) }}">
    @else
        <link rel="icon" href="{{ url($business_card_details->profile ?? '') }}" sizes="512x512" type="image/png" />
        <link rel="apple-touch-icon" href="{{ url($business_card_details->profile ?? '') }}">
    @endif

    <meta name="theme-color" content="#F7F3ED" />

    <!-- Add to homescreen for Chrome on Android -->
    <meta name="application-name" content="{{ $card_details->title ?? '' }}">

    <!-- Add to homescreen for Safari on iOS --> 
    <meta name="apple-mobile-web-app-title" content="{{ $card_details->title ?? '' }}">

    <!-- Tile for Win8 -->
    <meta name="msapplication-TileColor" content="#F7F3ED">
    <meta name="msapplication-TileImage" content="{{ url($business_card_details->profile ?? '') }}">

    {!! SEOMeta::generate() !!}
    {!! OpenGraph::generate() !!}
    {!! Twitter::generate() !!}
    {!! JsonLd::generate() !!}

    {{-- Intro Screen CSS --}}
    @if ($introScreen != null)
        <link rel="stylesheet" href="{{ asset('templates/css/intros/' . $introScreen->intro_code . '.min.css') }}">
    @endif

    <!-- Atelier Salon CSS -->
    <link rel="stylesheet" href="{{ url('templates/css/parlour-vcard.css') }}">

    {{-- Swiper CSS --}}
    <link rel="stylesheet" href="{{ url('css/swiper-bundle.min.css') }}">
    {{-- Fontawesome CSS --}}
    <link rel="stylesheet" href="{{ url('css/fontawesome.min.css') }}" />

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,500;0,600;0,700;1,500&family=Manrope:wght@400;500;600;700;800&display=swap"
        rel="stylesheet" />

    <!-- Include QRious library -->
    <script src="{{ url('js/qrious.min.js') }}"></script>

    {{-- Swiper JS --}}
    <script src="{{ url('js/swiper-bundle.min.js') }}"></script>

    <!-- Flatpickr CSS -->
    <link href="{{ url('css/flatpickr.min.css') }}" rel="stylesheet">

    {{-- Check business details --}}
    @if ($business_card_details != null)
        @php
            $custom_css = trim($business_card_details->custom_css ?? '');
            $custom_js = trim($business_card_details->custom_js ?? '');

            // Wrap CSS if it doesn't already contain a <style> tag
if (!empty($custom_css) && stripos($custom_css, '<style') === false) {
    $custom_css = "<style>\n{$custom_css}\n</style>";
}

// Process Custom JS
$include_custom_js = '';

if (!empty($custom_js)) {
    // If HTML tags are present (script, meta, iframe, etc.),
    // output the content as-is.
    if (preg_match('/<(script|meta|noscript|iframe|link|style|img)\b/i', $custom_js)) {
        $include_custom_js = $custom_js;
    } else {
        // Otherwise wrap plain JavaScript in a script tag.
        $include_custom_js = '<script>
            \
            n {
                $custom_js
            }\
            n
        </script>';
                }
            }
        @endphp

        {{-- Custom CSS --}}
        {!! $custom_css !!}

        {{-- Custom JS / HTML --}}
        {!! $include_custom_js !!}

        {{-- Theme CSS --}}
        @if (!empty($business_card_details->theme_css))
            <style>
                {!! $business_card_details->theme_css !!}
            </style>
        @endif

        {{-- Theme JS --}}
        @if (!empty($business_card_details->theme_js))
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

<body class="sl-body" dir="{{ App::isLocale('ar') || App::isLocale('ur') || App::isLocale('he') ? 'rtl' : 'ltr' }}">

    {{-- Include Background Ambient Partial --}}
    @include('templates.includes.vcard.parlour_vcard.bg-icons')

    {{-- Loader --}}
    @if ($introScreen != null)
        <!-- Loader -->
        <div id="loader" class="bb-loader">
            <div class="spinner"></div>
        </div>
    @endif

    <div id="smooth-wrapper">
        <div id="smooth-content" class="container max-w-2xl mx-auto lg:my-8 relative px-0 sm:px-0">

            {{-- Check password protected --}}
            @if ($business_card_details->password == null || Session::get('password_protected') == true)
                @if ($business_card_details != null)

                    <!-- Main Profile Card Panel -->
                    <div class="sl-panel rounded-3xl overflow-hidden relative shadow-2xl" id="profile">

                        {{-- Index Screen --}}
                        @if ($introScreen != null)
                            @include("templates.includes.intros.{$introScreen->intro_code}", [
                                'theme' => $business_card_details->theme_id,
                            ])
                        @endif

                        <div id="content-screen">
                            <!-- Cover Banner Section -->
                            @if ($business_card_details->cover_type == 'none')
                                <div class="lg:h-72 h-72 relative overflow-hidden" id="sl-hero">
                                    <img src="{{ url('img/templates/parlour/banner.png') }}"
                                        alt="{{ $business_card_details->title }}" class="w-full h-full object-cover" />
                                    <div
                                        class="absolute inset-0 bg-gradient-to-b from-black/20 via-transparent to-[#ffffff]">
                                    </div>
                                </div>
                            @endif

                            @if ($business_card_details->cover_type == 'photo')
                                <div class="lg:h-72 h-72 relative overflow-hidden" id="sl-hero">
                                    <img src="{{ $business_card_details->cover ? url($business_card_details->cover) : asset('images/default-cover.png') }}"
                                        alt="{{ $business_card_details->title }}" class="w-full h-full object-cover" />
                                    <div
                                        class="absolute inset-0 bg-gradient-to-b from-black/20 via-transparent to-[#ffffff]">
                                    </div>
                                </div>
                            @endif

                            @if ($business_card_details->cover_type == 'vimeo-ap' || $business_card_details->cover_type == 'vimeo')
                                <div class="relative w-full" style="padding-top: 56.25%;" id="sl-hero">
                                    <iframe referrerpolicy="strict-origin-when-cross-origin"
                                        src="https://player.vimeo.com/video/{{ $business_card_details->cover }}?autoplay=1&loop=1&autopause=0&muted=1&controls=0"
                                        frameborder="0" allow="autoplay;" class="absolute top-0 left-0 w-full h-full">
                                    </iframe>
                                </div>
                            @endif

                            @if ($business_card_details->cover_type == 'youtube-ap' || $business_card_details->cover_type == 'youtube')
                                <div class="relative w-full" style="padding-top: 56.25%;" id="sl-hero">
                                    <iframe referrerpolicy="strict-origin-when-cross-origin"
                                        src="https://www.youtube.com/embed/{{ $business_card_details->cover }}?autoplay=1&mute=1&controls=0&loop=1&playlist={{ $business_card_details->cover }}"
                                        frameborder="0" allow="autoplay;" class="absolute top-0 left-0 w-full h-full">
                                    </iframe>
                                </div>
                            @endif

                            {{-- Language Switcher --}}
                            @if (
                                $business_card_details->is_enable_language_switcher == 1 &&
                                    is_array(config('app.languages')) &&
                                    count(config('app.languages')) > 1)
                                @include('templates.includes.vcard.barber.language-switcher')
                            @endif

                            <!-- Profile Info Header -->
                            <div class="relative px-6 sm:px-10 pb-24 lg:pb-8">
                                <div class="flex justify-center sl-avatar-frame">
                                    <div class="relative">
                                        <img src="{{ url($business_card_details->profile) }}"
                                            alt="{{ $business_card_details->title }}"
                                            class="w-36 h-36 rounded-full -mt-16 z-10 object-cover border-4 border-white shadow-xl relative" />
                                        <span
                                            class="absolute bottom-1 right-1 z-20 bg-[#361E2B] text-[#C59B78] border border-[#C59B78]/40 text-[10px] uppercase font-bold tracking-widest px-2.5 py-0.5 rounded-full shadow-md">
                                            {{ __('Atelier') }}
                                        </span>
                                    </div>
                                </div>

                                <div class="text-center my-4 relative" id="sl-name-block">
                                    <p class="text-xs font-bold uppercase tracking-widest text-[#C59B78] mb-1">
                                        {{ __('Hairstylist & Atelier Lounge') }}
                                    </p>

                                    <h1 class="sl-display text-4xl sm:text-5xl font-bold text-[#1A1617]" id="pName">
                                        {{ $business_card_details->title }}
                                    </h1>

                                    <p
                                        class="text-[#361E2B] font-semibold mt-1 text-xs sm:text-sm uppercase tracking-widest">
                                        {{ $card_details->sub_title }}
                                    </p>

                                    @if ($business_card_details->description != null)
                                        <div
                                            class="mt-3 text-sm leading-relaxed max-w-lg mx-auto font-medium text-[#685E5A]">
                                            {!! $business_card_details->description !!}
                                        </div>
                                    @endif
                                </div>

                                {{-- Quick Actions Component --}}
                                @include('templates.includes.vcard.parlour_vcard.quick-actions')

                                <!-- Location Address Section -->
                                @if (count($feature_details) > 0)
                                    @foreach ($feature_details as $feature)
                                        @if (in_array($feature->type, ['address']))
                                            <div class="flex justify-center relative my-4">
                                                <div class="sl-card rounded-2xl w-full">
                                                    <a href="https://www.google.com/maps/place/{{ urlencode($feature->content) }}"
                                                        target="_blank"
                                                        class="px-5 py-4 flex flex-col justify-center items-center text-center group">
                                                        <div
                                                            class="w-10 h-10 rounded-full bg-[#361E2B]/5 border border-[#C59B78]/30 flex items-center justify-center text-[#C59B78] mb-2 group-hover:bg-[#361E2B] group-hover:text-white transition-all">
                                                            <i class="{{ $feature->icon }} text-lg"></i>
                                                        </div>
                                                        <h2 class="text-[#1A1617] text-sm font-bold">
                                                            {{ $feature->label }}
                                                        </h2>
                                                        <p class="text-[#685E5A] text-xs mt-1 max-w-md">
                                                            {{ $feature->content }}
                                                        </p>
                                                    </a>
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                @endif

                                <!-- Social Links Section -->
                                @if (!empty($feature_details) && count($feature_details) > 0)
                                    @php
                                        $excludedTypes = [
                                            'tel',
                                            'snapchat',
                                            'instagram',
                                            'address',
                                            'map',
                                            'iframe',
                                            'youtube',
                                        ];
                                        $validFeatures = collect($feature_details)->filter(function ($feature) use (
                                            $excludedTypes,
                                        ) {
                                            return isset($feature->type) && !in_array($feature->type, $excludedTypes);
                                        });
                                    @endphp

                                    @if ($validFeatures->isNotEmpty())
                                        <div class="sl-divider">
                                            <span class="sl-divider-mark"></span>
                                            <h2
                                                class="sl-display text-2xl sm:text-3xl font-bold text-[#1A1617] whitespace-nowrap">
                                                {{ __($feature_details[0]->title) }}
                                            </h2>
                                        </div>

                                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 py-2">
                                            @foreach ($validFeatures as $feature)
                                                @php
                                                    $href = $feature->content;
                                                    if ($feature->type == 'email') {
                                                        $href = 'mailto:' . $feature->content;
                                                    } elseif ($feature->type == 'wa') {
                                                        $href = 'https://wa.me/' . $feature->content;
                                                    } elseif ($feature->type == 'text') {
                                                        $href = 'javascript:void(0);';
                                                    }
                                                @endphp
                                                <a href="{{ $href }}" target="_blank"
                                                    class="sl-card p-3.5 rounded-2xl flex items-center gap-3 group">
                                                    <div
                                                        class="w-9 h-9 rounded-xl bg-[#F7F3ED] border border-[#C59B78]/25 flex items-center justify-center text-[#C59B78] flex-shrink-0 group-hover:bg-[#361E2B] group-hover:text-white transition-all">
                                                        <i class="{{ $feature->icon }} text-base"></i>
                                                    </div>
                                                    <div class="overflow-hidden min-w-0">
                                                        <h2 class="text-[#1A1617] text-xs font-bold truncate">
                                                            {{ $feature->label }}</h2>
                                                        <p class="text-[#685E5A] text-[11px] truncate mt-0.5">
                                                            {{ $feature->content }}</p>
                                                    </div>
                                                </a>
                                            @endforeach
                                        </div>
                                    @endif
                                @endif

                                <!-- Services Section -->
                                @if (count($service_details) > 0)
                                    <div class="py-6" id="services-section">
                                        <div class="sl-divider">
                                            <span class="sl-divider-mark"></span>
                                            <h2
                                                class="sl-display text-2xl sm:text-3xl font-bold text-[#1A1617] whitespace-nowrap">
                                                {{ __($service_details[0]->title) }}
                                            </h2>
                                        </div>
                                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mt-6 items-stretch">
                                            @foreach ($service_details as $service_detail)
                                                <div
                                                    class="flex flex-col sl-card rounded-2xl p-4 h-full sl-service-card group">
                                                    <div
                                                        class="overflow-hidden rounded-xl mb-3 flex-shrink-0 relative">
                                                        <img src="{{ url($service_detail->service_image) }}"
                                                            alt="{{ $service_detail->service_name }}"
                                                            class="w-full h-44 object-cover group-hover:scale-105 transition-transform duration-500" />
                                                    </div>
                                                    <h2 class="sl-display text-xl font-bold text-[#1A1617]">
                                                        {{ $service_detail->service_name }}
                                                    </h2>
                                                    <p
                                                        class="text-[#685E5A] text-xs leading-relaxed mt-1.5 mb-4 sl-clamp-3">
                                                        {{ $service_detail->service_description }}
                                                    </p>
                                                    @if ($enquiry_button != null && $service_detail->enable_enquiry == 'Enabled')
                                                        <a href="https://wa.me/{{ $enquiry_button }}?text={{ __('Hi, I am interested in:') }} {{ $service_detail->service_name }}"
                                                            target="_blank"
                                                            class="sl-btn-outline text-xs text-center font-semibold mt-auto py-2.5 px-4 rounded-xl">
                                                            <i class="fab fa-whatsapp mr-1.5"></i>
                                                            {{ __('Enquire Service') }}
                                                        </a>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                <!-- Products Section -->
                                @if (count($product_details) > 0)
                                    <div class="py-6">
                                        <div class="sl-divider">
                                            <span class="sl-divider-mark"></span>
                                            <h2
                                                class="sl-display text-2xl sm:text-3xl font-bold text-[#1A1617] whitespace-nowrap">
                                                {{ __($product_details[0]->title) }}
                                            </h2>
                                        </div>
                                        <div
                                            class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-2 gap-4 mt-6 items-stretch">
                                            @foreach ($product_details as $product_detail)
                                                <div
                                                    class="flex flex-col sl-card rounded-2xl p-4 h-full sl-product-card group">
                                                    <div
                                                        class="relative overflow-hidden rounded-xl mb-3 flex-shrink-0">
                                                        <img src="{{ url($product_detail->product_image) }}"
                                                            alt="{{ $product_detail->product_name }}"
                                                            class="w-full h-44 object-cover group-hover:scale-105 transition-transform duration-500" />
                                                        @if (!empty($product_detail->badge))
                                                            <span
                                                                class="bg-[#361E2B] text-[#C59B78] text-[10px] font-bold uppercase tracking-wider px-2.5 py-1 rounded-full absolute bottom-2 right-2 shadow-md border border-[#C59B78]/30">
                                                                {{ $product_detail->badge }}
                                                            </span>
                                                        @endif
                                                    </div>
                                                    <h3 class="sl-display text-lg font-bold text-[#1A1617]">
                                                        {{ $product_detail->product_name }}
                                                    </h3>
                                                    <p
                                                        class="text-[#685E5A] text-xs leading-relaxed mt-1 mb-3 sl-clamp-3">
                                                        {{ $product_detail->product_description }}
                                                    </p>
                                                    <div class="mt-auto pt-2 border-t border-[#C59B78]/15">
                                                        @if ($product_detail->sales_price != 0)
                                                            <div class="flex items-center justify-between mb-2">
                                                                <span
                                                                    class="font-semibold text-[#685E5A] text-xs uppercase">{{ __('Price') }}</span>
                                                                <span class="text-[#1A1617] text-sm font-bold">
                                                                    {{ formatCurrencyVcard($product_detail->sales_price, $product_detail->currency) }}
                                                                    @if ($product_detail->sales_price != $product_detail->regular_price)
                                                                        <span
                                                                            class="line-through ml-1 text-xs text-[#887D78] font-normal">
                                                                            {{ formatCurrencyVcard($product_detail->regular_price, $product_detail->currency) }}
                                                                        </span>
                                                                    @endif
                                                                </span>
                                                            </div>
                                                        @endif
                                                        @if ($enquiry_button != null && $whatsAppNumberExists == true)
                                                            <a href="https://wa.me/{{ $enquiry_button }}?text={{ __('Hi, I am interested in product:') }} {{ $product_detail->product_name }}"
                                                                target="_blank"
                                                                class="sl-btn-outline block text-xs text-center font-semibold py-2 px-4 rounded-xl mt-1">
                                                                <i class="fab fa-whatsapp mr-1"></i>
                                                                {{ __('Buy / Enquire') }}
                                                            </a>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                <!-- Gallery Section -->
                                @if (count($galleries_details) > 0)
                                    <div class="py-6">
                                        <div class="sl-divider">
                                            <span class="sl-divider-mark"></span>
                                            <h2
                                                class="sl-display text-2xl sm:text-3xl font-bold text-[#1A1617] whitespace-nowrap">
                                                {{ __($galleries_details[0]->title) }}
                                            </h2>
                                        </div>
                                        <div class="swiper-container mySwiper overflow-hidden mt-6">
                                            <div class="swiper-wrapper">
                                                @foreach ($galleries_details as $galleries_detail)
                                                    <div class="swiper-slide h-auto">
                                                        <div class="sl-card rounded-2xl overflow-hidden group">
                                                            <div class="relative overflow-hidden">
                                                                <img src="{{ url($galleries_detail->gallery_image) }}"
                                                                    alt="{{ $galleries_detail->caption }}"
                                                                    class="w-full h-60 object-cover group-hover:scale-105 transition-transform duration-500" />
                                                            </div>
                                                            @if ($galleries_detail->caption)
                                                                <div
                                                                    class="p-3 text-center bg-[#F7F3ED]/50 border-t border-[#C59B78]/20">
                                                                    <p
                                                                        class="text-xs font-semibold text-[#1A1617] sl-clamp-3">
                                                                        {{ $galleries_detail->caption }}
                                                                    </p>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                            <div class="swiper-pagination mt-4"></div>
                                        </div>
                                    </div>
                                @endif

                                <!-- Start Instagram Section -->
                                @include('templates.includes.instagram-embed', [
                                    'sectionClass' => 'py-6',
                                    'customHeader' => '<div class="sl-divider"><span class="sl-divider-mark"></span><h2 class="sl-display text-2xl sm:text-3xl font-bold text-[#1A1617] whitespace-nowrap">' . __('Instagram') . '</h2></div>',
                                    'cardClass'    => 'sl-card rounded-2xl overflow-hidden',
                                    'labelClass'   => 'p-3 text-center bg-[#F7F3ED]/50 border-t border-[#C59B78]/20 text-xs font-semibold text-[#1A1617]'
                                ])
                                <!-- End Instagram Section -->

                                <!-- Client Testimonials Section -->
                                @if (count($testimonials) > 0)
                                    <div class="py-6">
                                        <div class="sl-divider">
                                            <span class="sl-divider-mark"></span>
                                            <h2
                                                class="sl-display text-2xl sm:text-3xl font-bold text-[#1A1617] whitespace-nowrap">
                                                {{ __($testimonials[0]->title) }}
                                            </h2>
                                        </div>
                                        <div class="swiper-container testimonialSwiper overflow-hidden mt-6">
                                            <div class="swiper-wrapper">
                                                @foreach ($testimonials as $testimonial)
                                                    <div class="swiper-slide h-auto">
                                                        <div
                                                            class="sl-card p-5 rounded-2xl h-full flex flex-col justify-between">
                                                            <p
                                                                class="text-[#685E5A] text-xs sm:text-sm italic leading-relaxed sl-clamp-3 mb-4">
                                                                "{{ $testimonial->review }}"
                                                            </p>
                                                            <div
                                                                class="flex items-center gap-3 border-t border-[#C59B78]/20 pt-3">
                                                                <img src="{{ url($testimonial->reviewer_image) }}"
                                                                    alt="{{ $testimonial->reviewer_name }}"
                                                                    class="h-10 w-10 rounded-full object-cover border-2 border-[#C59B78]" />
                                                                <div class="min-w-0">
                                                                    <p
                                                                        class="text-[#1A1617] font-bold text-xs sm:text-sm truncate">
                                                                        {{ $testimonial->reviewer_name }}
                                                                    </p>
                                                                    @if ($testimonial->review_subtext)
                                                                        <span
                                                                            class="text-[#887D78] text-[11px] font-medium block truncate">
                                                                            {{ $testimonial->review_subtext }}
                                                                        </span>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                            <div class="swiper-pagination mt-4"></div>
                                        </div>
                                    </div>
                                @endif

                                <!-- Appointment Booking Section -->
                                @if (
                                    $appointmentEnabled == true &&
                                        isset($plan_details['appointment']) &&
                                        $plan_details['appointment'] == 1 &&
                                        $appointment_slots != null)
                                    <div id="appointment-section" class="py-6">
                                        <div class="sl-divider">
                                            <span class="sl-divider-mark"></span>
                                            <h2
                                                class="sl-display text-2xl sm:text-3xl font-bold text-[#1A1617] whitespace-nowrap">
                                                {{ __(json_decode($appointment_slots, true)['title']) }}
                                            </h2>
                                        </div>
                                        <div class="sl-card rounded-2xl p-5 w-full mt-6">
                                            <div id="errorMessage"
                                                class="hidden p-3 my-2 text-xs font-semibold rounded-xl bg-red-100 border border-red-300 text-red-800">
                                            </div>
                                            <div id="successMessage"
                                                class="hidden p-3 my-2 text-xs font-semibold rounded-xl bg-green-100 border border-green-300 text-green-800">
                                            </div>

                                            <div class="my-3">
                                                <label
                                                    class="block text-xs font-bold text-[#1A1617] mb-1 uppercase tracking-wider">{{ __('Date') }}</label>
                                                <input type="text" id="appointment-date"
                                                    class="sl-input block w-full px-4 py-2.5 rounded-xl text-xs sm:text-sm"
                                                    placeholder="{{ __('Select Date') }}" required>
                                            </div>
                                            <div class="my-3">
                                                <label
                                                    class="block text-xs font-bold text-[#1A1617] mb-1 uppercase tracking-wider">{{ __('Time Slot') }}</label>
                                                <select id="time-slot-select"
                                                    class="sl-input block w-full px-4 py-2.5 rounded-xl text-xs sm:text-sm"
                                                    required>
                                                    <option value="">{{ __('Select Time Slot') }}</option>
                                                </select>
                                            </div>
                                            <button type="button"
                                                class="sl-btn-primary w-full py-3 rounded-xl text-xs sm:text-sm uppercase tracking-wider font-bold mt-2"
                                                onclick="validateAndShowModal()">
                                                <i class="far fa-calendar-check mr-1.5"></i>
                                                {{ __('Book Appointment') }}
                                            </button>
                                        </div>
                                    </div>
                                @endif

                                <!-- Business Hours Section -->
                                @if ($plan_details['business_hours'] == 1 && $business_hours != null && $business_hours->is_display != 0)
                                    <section class="py-6">
                                        <div class="sl-divider">
                                            <span class="sl-divider-mark"></span>
                                            <h2
                                                class="sl-display text-2xl sm:text-3xl font-bold text-[#1A1617] whitespace-nowrap">
                                                {{ __($business_hours->title) }}
                                            </h2>
                                        </div>
                                        <div class="sl-card rounded-2xl p-4 mt-6">
                                            @if ($business_hours->is_always_open != 'Opening')
                                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                                                    @foreach (['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as $day)
                                                        <div
                                                            class="flex items-center gap-3 p-2.5 rounded-xl bg-[#F7F3ED] border border-[#C59B78]/20">
                                                            <div
                                                                class="w-8 h-8 rounded-full flex items-center justify-center text-[#C59B78] bg-[#361E2B]/5 flex-shrink-0">
                                                                <i class="far fa-clock text-xs"></i>
                                                            </div>
                                                            <div class="min-w-0">
                                                                <p
                                                                    class="text-[11px] font-bold text-[#887D78] uppercase tracking-wider truncate">
                                                                    {{ __($day) }}
                                                                </p>
                                                                <p class="text-xs font-bold text-[#1A1617] truncate">
                                                                    {{ __($business_hours->$day ?: __('Closed')) }}
                                                                </p>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @else
                                                <div
                                                    class="flex items-center gap-4 p-4 rounded-xl bg-[#F7F3ED] border border-[#C59B78]/20">
                                                    <div
                                                        class="w-10 h-10 rounded-full flex items-center justify-center text-[#C59B78] bg-[#361E2B]/10 flex-shrink-0">
                                                        <i class="fas fa-sparkles text-sm"></i>
                                                    </div>
                                                    <div>
                                                        <p class="text-sm font-bold text-[#361E2B]">
                                                            {{ __('Always Open') }}
                                                        </p>
                                                        <p class="text-xs text-[#685E5A]">
                                                            {{ __('We are available 24/7 to serve you!') }}
                                                        </p>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </section>
                                @endif

                                <!-- Service Booking Form -->
                                @if (isset($plan_details['service_booking']) && $plan_details['service_booking'] == 1)
                                    @if (isset($service_booking_details) && $service_booking_details->service_booking == 1)
                                        <div class="py-6">
                                            <div class="sl-divider">
                                                <span class="sl-divider-mark"></span>
                                                <h2
                                                    class="sl-display text-2xl sm:text-3xl font-bold text-[#1A1617] whitespace-nowrap">
                                                    {{ __($service_booking_details->title) }}
                                                </h2>
                                            </div>
                                            <div class="sl-card rounded-2xl p-5 w-full space-y-3 mt-6">
                                                <div id="errorMessage1"
                                                    class="hidden p-3 mb-3 text-xs font-semibold rounded-xl bg-red-100 border border-red-300 text-red-800">
                                                </div>
                                                <div id="successMessage1"
                                                    class="hidden p-3 mb-3 text-xs font-semibold rounded-xl bg-green-100 border border-green-300 text-green-800">
                                                </div>

                                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                                    <input type="text" id="customer_name"
                                                        placeholder="{{ __('Your Name') }}"
                                                        class="sl-input w-full p-2.5 rounded-xl text-xs sm:text-sm">
                                                    <input type="email" id="customer_email"
                                                        placeholder="{{ __('Your Email') }}"
                                                        class="sl-input w-full p-2.5 rounded-xl text-xs sm:text-sm">
                                                </div>
                                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                                    <input type="tel" id="customer_phone"
                                                        placeholder="{{ __('Mobile Number') }}"
                                                        class="sl-input w-full p-2.5 rounded-xl text-xs sm:text-sm">
                                                    <input type="number" id="no_of_persons" value="1"
                                                        placeholder="{{ __('No. of Person(s)') }}"
                                                        class="sl-input w-full p-2.5 rounded-xl text-xs sm:text-sm">
                                                </div>
                                                <textarea id="customer_address" placeholder="{{ __('Your Address') }}"
                                                    class="sl-input w-full p-2.5 rounded-xl text-xs sm:text-sm" rows="2"></textarea>
                                                <textarea id="customer_notes" placeholder="{{ __('Notes / Special Instructions') }}"
                                                    class="sl-input w-full p-2.5 rounded-xl text-xs sm:text-sm" rows="2"></textarea>

                                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                                    <input type="text" id="service_start_date"
                                                        placeholder="{{ __('Start Date') }}"
                                                        class="sl-input w-full p-2.5 rounded-xl text-xs sm:text-sm">
                                                    <input type="time" id="service_start_time"
                                                        class="sl-input w-full p-2.5 rounded-xl text-xs sm:text-sm timepicker">
                                                </div>
                                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                                    <input type="text" id="service_end_date"
                                                        placeholder="{{ __('End Date') }}"
                                                        class="sl-input w-full p-2.5 rounded-xl text-xs sm:text-sm">
                                                    <input type="time" id="service_end_time"
                                                        class="sl-input w-full p-2.5 rounded-xl text-xs sm:text-sm timepicker">
                                                </div>

                                                <button type="button" id="btn-submit-service-booking"
                                                    onclick="submitServiceBooking()"
                                                    class="sl-btn-primary w-full py-3 rounded-xl text-xs sm:text-sm uppercase tracking-wider font-bold">
                                                    <i class="fas fa-cut mr-1.5"></i> {{ __('Book Service') }}
                                                </button>
                                            </div>
                                        </div>
                                    @endif
                                @endif

                                <!-- Payment Section -->
                                @if (count($payment_details) > 0)
                                    <div class="py-6">
                                        <div class="sl-divider">
                                            <span class="sl-divider-mark"></span>
                                            <h2
                                                class="sl-display text-2xl sm:text-3xl font-bold text-[#1A1617] whitespace-nowrap">
                                                {{ __($payment_details[0]->title) }}
                                            </h2>
                                        </div>
                                        <div class="grid lg:grid-cols-2 gap-4 mt-6">
                                            @foreach ($payment_details as $payment)
                                                <div class="flex flex-col sl-card rounded-2xl p-4 group">
                                                    <div class="flex justify-between items-center mb-2">
                                                        @include('templates.partials.payment-link-image')
                                                        @if ($payment->type == 'url' || $payment->type == 'upi')
                                                            <a href="{{ $payment->type == 'upi' ? 'upi://pay?pa=' . $payment->content : 'https://' . str_replace('https://', '', $payment->content) }}"
                                                                target="_blank"
                                                                class="w-8 h-8 rounded-full bg-[#F7F3ED] border border-[#C59B78]/30 flex items-center justify-center text-[#C59B78] group-hover:bg-[#361E2B] group-hover:text-white transition-all">
                                                                <i class="fas fa-external-link-alt text-xs"></i>
                                                            </a>
                                                        @endif
                                                    </div>
                                                    <h3
                                                        class="font-bold text-[#1A1617] text-xs uppercase tracking-wider">
                                                        {{ $payment->label }}
                                                    </h3>
                                                    @if ($payment->type == 'text')
                                                        <p class="text-[#685E5A] text-xs mt-1 leading-relaxed">
                                                            {{ $payment->content }}
                                                        </p>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                <!-- Location Map Section -->
                                @if (count($feature_details) > 0 && $feature_details->contains('type', 'map'))
                                    <div id="location-section" class="py-6">
                                        <div class="sl-divider">
                                            <span class="sl-divider-mark"></span>
                                            <h2
                                                class="sl-display text-2xl sm:text-3xl font-bold text-[#1A1617] whitespace-nowrap">
                                                {{ __('Location') }}
                                            </h2>
                                        </div>
                                        <div
                                            class="mt-6 rounded-2xl overflow-hidden shadow-sm border border-[#C59B78]/30">
                                            @foreach ($feature_details as $feature)
                                                @if ($feature->type == 'map')
                                                    <iframe referrerpolicy="strict-origin-when-cross-origin"
                                                        src="https://www.google.com/maps/embed?{!! $feature->content !!}"
                                                        width="100%" height="260" style="border:0"></iframe>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                <!-- Start Google Wallet section -->
                                @if (is_dir(base_path('plugins/GoogleWallet')))
                                    @if (isset($plan_details['google_wallet']) &&
                                            $plan_details['google_wallet'] == 1 &&
                                            $business_card_details->is_google_wallet_hidden == 0)
                                        <div class="py-6 bb-scroll-reveal">

                                            {{-- Unified Section Header --}}
                                            <div class="sl-divider">
                                                <span class="sl-divider-mark"></span>
                                                <h2
                                                    class="sl-display text-2xl sm:text-3xl font-bold text-[#721B29] whitespace-nowrap">
                                                    {{ __('Google Wallet') }}
                                                </h2>
                                            </div>

                                            {{-- Card Container (Matching Payment/Services design pattern) --}}
                                            <div class="mt-6 sl-card rounded-2xl p-5 w-full">
                                                @if ($google_wallet_details->wallet_description != null)
                                                    <div
                                                        class="text-xs sm:text-sm text-center text-[#735D61] leading-relaxed mb-4">
                                                        {!! $google_wallet_details->wallet_description ?? '' !!}
                                                    </div>
                                                @endif

                                                @if ($google_wallet_details->wallet_link != null)
                                                    <div class="flex justify-center pt-2">
                                                        <a href="{{ $google_wallet_details->wallet_link }}"
                                                            class="w-full sm:w-auto flex justify-center group"
                                                            target="_blank" rel="noopener noreferrer">
                                                            <img src="{{ url()->to('/') . '/img/google-wallet-btn.png' }}"
                                                                alt="Add to Google Wallet"
                                                                class="object-cover rounded-xl border border-[#E29A88]/25 shadow-md max-w-[15rem] transition-transform duration-300 group-hover:scale-[1.03]">
                                                        </a>
                                                    </div>
                                                @endif
                                            </div>

                                        </div>
                                    @endif
                                @endif
                                <!-- End Google Wallet section -->

                                <!-- Contact Form Section -->
                                @if ($plan_details['contact_form'] == 1 && $business_card_details->enquiry_email != null)
                                    <section class="py-6">
                                        <div class="sl-divider">
                                            <span class="sl-divider-mark"></span>
                                            <h2
                                                class="sl-display text-2xl sm:text-3xl font-bold text-[#1A1617] whitespace-nowrap">
                                                {{ __($business_card_details->contact_form_title) }}
                                            </h2>
                                        </div>

                                        @if (Session::has('message'))
                                            <div
                                                class="p-4 mb-4 text-xs font-semibold rounded-xl bg-green-100 border border-green-300 text-green-800 flex items-center">
                                                <i class="fas fa-check-circle text-green-600 text-base mr-2"></i>
                                                <span>{{ Session::get('message') }}</span>
                                            </div>
                                        @endif

                                        <form id="contactForm" class="w-full mx-auto space-y-3 mt-6"
                                            action="{{ config('app.url') }}/sent-enquiry" method="POST">
                                            @csrf
                                            <input type="hidden" name="card_id"
                                                value="{{ $business_card_details->card_id }}" />
                                            <input type="text" name="name"
                                                placeholder="{{ __('Your Name') }}"
                                                class="sl-input w-full px-4 py-2.5 rounded-xl text-xs sm:text-sm"
                                                required />
                                            <input type="email" name="email"
                                                placeholder="{{ __('Your Email') }}"
                                                class="sl-input w-full px-4 py-2.5 rounded-xl text-xs sm:text-sm"
                                                required />
                                            <input type="tel" name="phone"
                                                placeholder="{{ __('Mobile Number') }}"
                                                class="sl-input w-full px-4 py-2.5 rounded-xl text-xs sm:text-sm"
                                                required />
                                            <textarea name="message" placeholder="{{ __('Your Message') }}"
                                                class="sl-input w-full px-4 py-2.5 rounded-xl text-xs sm:text-sm" rows="3" required></textarea>

                                            @include('templates.includes.recaptcha', [
                                                'recaptchaId' => 'recaptcha-one',
                                            ])

                                            <button type="submit" id="btn-submit-contact"
                                                class="sl-btn-primary w-full py-3 rounded-xl text-xs sm:text-sm uppercase tracking-wider font-bold">
                                                <i class="far fa-paper-plane mr-1.5"></i> {{ __('Send Message') }}
                                            </button>
                                        </form>
                                    </section>
                                @endif

                                <!-- Branding Footer -->
                                <div
                                    class="pb-2 pt-6 text-center text-[11px] text-[#887D78] font-semibold tracking-wide">
                                    {{ __('Copyright') }} &copy; <a class="text-[#C59B78] hover:underline"
                                        href="{{ url()->current() }}">{{ $card_details->title }}</a>.
                                    {{ __('All Rights Reserved.') }}
                                </div>

                            </div>
                        </div>

                    </div>
                @endif
            @endif

            {{-- Password Protected Modal --}}
            @if ($business_card_details->password != null && Session::get('password_protected') == false)
                <div class="fixed inset-0 flex items-center justify-center z-50 p-4 bg-[#1A1617]/80 backdrop-blur-md">
                    <div class="sl-panel rounded-3xl p-6 w-96 max-w-full text-center shadow-2xl">
                        <h2 class="sl-display text-2xl font-bold text-[#1A1617] mb-3">{{ __('Password Protected') }}
                        </h2>
                        <form id="passwordProtectionForm"
                            action="{{ config('app.url') }}/check-password/{{ $business_card_details->card_id }}"
                            method="post" class="space-y-3">
                            @csrf
                            <input type="password" name="password"
                                class="sl-input w-full p-3 rounded-xl text-xs sm:text-sm"
                                placeholder="{{ __('Enter Password') }}" required>

                            @if (Session::has('message'))
                                <div class="flex items-center p-4 my-4 text-sm rounded-lg"
                                    style="background: rgba(183,40,46,0.15); color: var(--bb-red);">
                                    <svg class="flex-shrink-0 inline w-4 h-4 mr-3" aria-hidden="true"
                                        xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                                        <path
                                            d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z" />
                                    </svg>
                                    <span class="sr-only">{{ __('Failed') }}</span>
                                    <div>
                                        <span class="font-medium">{{ Session::get('message') }}</span>
                                    </div>
                                </div>
                            @endif

                            <button type="submit" id="btn-submit-password"
                                class="sl-btn-primary w-full py-3 rounded-xl text-xs uppercase tracking-wider font-bold">{{ __('Submit') }}</button>
                        </form>
                    </div>
                </div>
            @endif

            {{-- Floating Mobile Action Bar --}}
            <div class="sl-float-bar fixed rounded-full flex lg:hidden transform -translate-x-1/2 z-50">
                <div class="flex-1 flex items-center justify-center">
                    <a class="w-10 h-10 rounded-full flex items-center justify-center text-[#C59B78] bg-[#F7F3ED] border border-[#C59B78]/30 shadow-sm"
                        href="#profile">
                        <i class="far fa-user text-sm"></i>
                    </a>
                </div>
                <div class="flex-1 flex items-center justify-center">
                    <button
                        class="w-10 h-10 rounded-full flex items-center justify-center text-[#C59B78] bg-[#F7F3ED] border border-[#C59B78]/30 shadow-sm"
                        onclick="toggleWhatsAppModal(true)">
                        <i class="fab fa-whatsapp text-base"></i>
                    </button>
                </div>
                <div class="flex-1 flex items-center justify-center">
                    <a href="{{ config('app.url') }}/download/{{ $business_card_details->card_id }}"
                        class="w-10 h-10 rounded-full flex items-center justify-center text-[#C59B78] bg-[#F7F3ED] border border-[#C59B78]/30 shadow-sm">
                        <i class="fas fa-download text-sm"></i>
                    </a>
                </div>
                <div class="flex-1 flex items-center justify-center">
                    <button
                        class="w-10 h-10 rounded-full flex items-center justify-center text-[#C59B78] bg-[#F7F3ED] border border-[#C59B78]/30 shadow-sm"
                        onclick="toggleScanModal(true)">
                        <i class="fas fa-qrcode text-sm"></i>
                    </button>
                </div>
                <div class="flex-1 flex items-center justify-center">
                    <button
                        class="w-10 h-10 rounded-full flex items-center justify-center text-[#C59B78] bg-[#F7F3ED] border border-[#C59B78]/30 shadow-sm"
                        onclick="shareToggleModal(true)">
                        <i class="fas fa-share-alt text-sm"></i>
                    </button>
                </div>
            </div>

            {{-- Root Level Modals Includes (Always accessible) --}}
            @if ($business_card_details->password == null || Session::get('password_protected') == true)

                <div id="root-level-modals" style="display:none;">
                    @if ($plan_details != null && $plan_details['pwa'] == 1 && $business_card_details->is_enable_pwa == 1)
                        @include('vendor.laravelpwa.parlour_vcard_pwa_modal')
                    @endif

                    @if (
                        $business_card_details != null &&
                            !empty($business_card_details->is_newsletter_pop_active) &&
                            $business_card_details->is_newsletter_pop_active == 1)
                        @include('templates.includes.vcard.parlour_vcard.newsletter_modal')
                    @endif

                    @if (
                        $business_card_details != null &&
                            !empty($business_card_details->is_info_pop_active) &&
                            $business_card_details->is_info_pop_active == 1)
                        @include('templates.includes.vcard.parlour_vcard.information_popup_modal')
                    @endif
                </div>
            @endif

        </div>
    </div>

    <!-- Appointment Modal -->
    <div id="appointmentModal" class="hidden" onclick="toggleModal()">
        <div class="sl-panel rounded-3xl w-full max-w-md p-6 mx-4 relative space-y-4 text-left shadow-2xl"
            onclick="event.stopPropagation()">
            <button type="button" class="sl-modal-close" onclick="toggleModal()"><i
                    class="fas fa-times"></i></button>
            <h2 class="sl-display text-2xl sm:text-3xl font-bold text-center text-[#1A1617]">
                {{ __('Book Appointment') }}</h2>

            <form id="appointmentForm" class="space-y-3">
                <input type="text" id="name" placeholder="{{ __('Name') }}"
                    class="sl-input p-3 rounded-xl w-full text-xs sm:text-sm" required>
                <input type="email" id="email" placeholder="{{ __('Email') }}"
                    class="sl-input p-3 rounded-xl w-full text-xs sm:text-sm" required>
                <input type="tel" id="phone" placeholder="{{ __('Phone') }}"
                    class="sl-input p-3 rounded-xl w-full text-xs sm:text-sm" required>
                <textarea id="notes" placeholder="{{ __('Notes') }}"
                    class="sl-input p-3 rounded-xl w-full text-xs sm:text-sm" rows="3"></textarea>

                @include('templates.includes.recaptcha', ['recaptchaId' => 'recaptcha-two'])

                <button type="submit" id="bookAppointmentButton"
                    class="sl-btn-primary w-full py-3.5 rounded-xl text-xs sm:text-sm uppercase tracking-wider font-bold">
                    {{ __('Submit Booking') }}
                </button>
            </form>
        </div>
    </div>

    <!-- Share Modal -->
    <div id="shareModal" class="hidden" onclick="shareToggleModal(false)">
        <div class="sl-panel rounded-3xl w-full max-w-md p-6 mx-4 relative space-y-5 text-center shadow-2xl"
            onclick="event.stopPropagation()">
            <button type="button" class="sl-modal-close" onclick="shareToggleModal(false)"><i
                    class="fas fa-times"></i></button>
            <h2 class="sl-display text-2xl sm:text-3xl font-bold text-[#1A1617]">{{ __('Share vCard') }}</h2>

            <div class="grid grid-cols-5 gap-2.5 py-2">
                <a href="{{ $shareComponent['facebook'] }}" target="_blank"
                    class="p-3 rounded-2xl border border-[#C59B78]/30 text-[#C59B78] hover:bg-[#361E2B] hover:text-white transition-all"><i
                        class="fab fa-facebook-f text-lg"></i></a>
                <a href="{{ $shareComponent['twitter'] }}" target="_blank"
                    class="p-3 rounded-2xl border border-[#C59B78]/30 text-[#C59B78] hover:bg-[#361E2B] hover:text-white transition-all"><i
                        class="fab fa-twitter text-lg"></i></a>
                <a href="{{ $shareComponent['linkedin'] }}" target="_blank"
                    class="p-3 rounded-2xl border border-[#C59B78]/30 text-[#C59B78] hover:bg-[#361E2B] hover:text-white transition-all"><i
                        class="fab fa-linkedin-in text-lg"></i></a>
                <a href="{{ $shareComponent['whatsapp'] }}" target="_blank"
                    class="p-3 rounded-2xl border border-[#C59B78]/30 text-[#C59B78] hover:bg-[#361E2B] hover:text-white transition-all"><i
                        class="fab fa-whatsapp text-lg"></i></a>
                <a href="{{ $shareComponent['telegram'] }}" target="_blank"
                    class="p-3 rounded-2xl border border-[#C59B78]/30 text-[#C59B78] hover:bg-[#361E2B] hover:text-white transition-all"><i
                        class="fab fa-telegram-plane text-lg"></i></a>
            </div>

            <div
                class="flex flex-col items-center justify-center p-4 rounded-2xl bg-[#F7F3ED] border border-[#C59B78]/20">
                <canvas id="shareQrCode" class="rounded-lg"></canvas>
            </div>

            <button onclick="copyLink()" id="copyLinkBtn"
                class="sl-btn-primary py-3.5 px-4 rounded-xl w-full text-xs sm:text-sm uppercase tracking-wider font-bold">
                <i class="fas fa-copy mr-1.5"></i> {{ __('Copy Link') }}
            </button>
        </div>
    </div>

    <!-- WhatsApp Modal -->
    <div id="whatsappModal" class="hidden" onclick="toggleWhatsAppModal(false)">
        <div class="sl-panel rounded-3xl w-full max-w-md p-6 mx-4 relative space-y-4 text-center shadow-2xl"
            onclick="event.stopPropagation()">
            <button type="button" class="sl-modal-close" onclick="toggleWhatsAppModal(false)"><i
                    class="fas fa-times"></i></button>
            <h2 class="sl-display text-2xl sm:text-3xl font-bold text-[#1A1617]">{{ __('WhatsApp Consultation') }}
            </h2>
            <div id="whatsappError"
                class="hidden p-3 mb-3 rounded-xl text-xs font-semibold text-red-600 bg-red-100 border border-red-300">
                {{ __('Please enter a valid WhatsApp number.') }}</div>
            <input type="tel" id="whatsappNumber" placeholder="{{ __('e.g. +14155552671') }}"
                class="sl-input w-full p-3.5 rounded-xl text-xs sm:text-sm" />
            <button type="button" id="btn-send-whatsapp" onclick="sendMessage()"
                class="sl-btn-primary w-full py-3.5 rounded-xl text-xs sm:text-sm uppercase tracking-wider font-bold">
                <i class="fab fa-whatsapp mr-1.5"></i> {{ __('Send Message') }}
            </button>
        </div>
    </div>

    <!-- Scan Modal -->
    <div id="scanModal" class="hidden" onclick="toggleScanModal(false)">
        <div class="sl-panel rounded-3xl w-full max-w-md p-6 mx-4 relative space-y-5 text-center shadow-2xl"
            onclick="event.stopPropagation()">
            <button type="button" class="sl-modal-close" onclick="toggleScanModal(false)"><i
                    class="fas fa-times"></i></button>
            <h2 class="sl-display text-2xl sm:text-3xl font-bold text-[#1A1617]">{{ __('QR Code') }}</h2>
            <div class="flex justify-center p-4 rounded-2xl mx-auto bg-[#F7F3ED] border border-[#C59B78]/30">
                <div class="qr-code"></div>
            </div>
            <button id="download"
                onclick="downloadQr('{{ config('app.url') . route('dynamic.card', $business_card_details->card_id, false) }}', 500)"
                class="sl-btn-primary py-3.5 px-6 rounded-xl w-full text-xs sm:text-sm uppercase tracking-wider font-bold">
                <i class="fas fa-download mr-1.5"></i> {{ __('Download QR Code') }}
            </button>
        </div>
    </div>

    {{-- Helper Scripts --}}
    <script>
        "use strict";
        (function() {
            const cardUrl = `{{ config('app.url') . route('dynamic.card', $business_card_details->card_id, false) }}`;

            function setModal(id, show) {
                const modal = document.getElementById(id);
                if (modal) modal.classList.toggle('hidden', !show);

                if (!show) {
                    const copyMsg = document.getElementById('shareCopySuccess');
                    if (copyMsg) copyMsg.classList.add('hidden');

                    const waErr = document.getElementById('whatsappError');
                    if (waErr) waErr.classList.add('hidden');
                }
            }

            window.shareToggleModal = function(show) {
                setModal('shareModal', show);
            };
            window.toggleWhatsAppModal = function(show) {
                setModal('whatsappModal', show);
            };
            window.toggleScanModal = function(show) {
                setModal('scanModal', show);
            };

            window.copyLink = async function() {
                const btn = document.getElementById('copyLinkBtn');
                const successMsg = document.getElementById('shareCopySuccess');

                try {
                    await navigator.clipboard.writeText(cardUrl);
                } catch (error) {
                    const tempInput = document.createElement('input');
                    tempInput.value = cardUrl;
                    document.body.appendChild(tempInput);
                    tempInput.select();
                    document.execCommand('copy');
                    document.body.removeChild(tempInput);
                }

                if (successMsg) successMsg.classList.remove('hidden');
                if (btn) {
                    const originalHtml = btn.innerHTML;
                    btn.innerHTML = `<i class="fas fa-check mr-1.5"></i> {{ __('Copied!') }}`;
                    setTimeout(() => {
                        btn.innerHTML = originalHtml;
                        if (successMsg) successMsg.classList.add('hidden');
                    }, 2500);
                }
            };

            window.sendMessage = function() {
                const btn = document.getElementById('btn-send-whatsapp');
                const input = document.getElementById('whatsappNumber');
                const errorBox = document.getElementById('whatsappError');
                const phoneNumber = input ? input.value.replace(/[^0-9]/g, '') : '';

                if (!phoneNumber || phoneNumber.length < 5) {
                    if (errorBox) errorBox.classList.remove('hidden');
                    return;
                }

                if (errorBox) errorBox.classList.add('hidden');

                if (typeof setBtnLoading === 'function') setBtnLoading(btn, '{{ __('Opening...') }}');

                window.open(`https://wa.me/${phoneNumber}?text={{ rawurlencode($shareContent ?? '') }}`, '_blank',
                    'noopener');

                setTimeout(() => {
                    if (typeof resetBtnLoading === 'function') resetBtnLoading(btn);
                    if (input) input.value = '';
                    window.toggleWhatsAppModal(false);
                }, 1200);
            };

            function renderQr(element, size) {
                if (!element || typeof QRious === 'undefined') return null;
                if (element.tagName === 'CANVAS') {
                    return new QRious({
                        element: element,
                        value: cardUrl,
                        size: size,
                        background: '#ffffff',
                        foreground: '#361E2B',
                        level: 'H'
                    });
                }
                element.replaceChildren();
                const canvas = document.createElement('canvas');
                element.appendChild(canvas);
                return new QRious({
                    element: canvas,
                    value: cardUrl,
                    size: size,
                    background: '#ffffff',
                    foreground: '#361E2B',
                    level: 'H'
                });
            }

            window.downloadQr = function(url, size) {
                const canvas = document.createElement('canvas');
                if (typeof QRious === 'undefined') return;
                new QRious({
                    element: canvas,
                    value: url || cardUrl,
                    size: size || 500,
                    background: '#ffffff',
                    foreground: '#361E2B',
                    level: 'H'
                });
                const link = document.createElement('a');
                link.download = 'vcard-qr-code.png';
                link.href = canvas.toDataURL('image/png');
                link.click();
            };

            document.addEventListener('DOMContentLoaded', function() {
                renderQr(document.getElementById('shareQrCode'), 180);
                document.querySelectorAll('#scanModal .qr-code').forEach(function(element) {
                    renderQr(element, 200);
                });
            });
        })();
    </script>

    {{-- Jquery & Plugins --}}
    <script src="{{ url('js/jquery.min.js') }}"></script>
    <script src="{{ url('js/smooth-scroll.polyfills.min.js') }}"></script>
    <script type="text/javascript" src="{{ url('app/js/footer.js') }}"></script>
    <script src="{{ url('js/flatpickr.min.js') }}"></script>
    @yield('custom-js')

    {{-- Flatpickr Locales --}}
    @php
        $flatpickrLocales = [
            'pt_BR' => 'pt',
            'zh' => 'zh',
            'ar' => 'ar',
            'bg' => 'bg',
            'hr' => 'hr',
            'nl' => 'nl',
            'fr' => 'fr',
            'de' => 'de',
            'he' => 'he',
            'hu' => 'hu',
            'id' => 'id',
            'it' => 'it',
            'ja' => 'ja',
            'lt' => 'lt',
            'ms' => 'ms',
            'pl' => 'pl',
            'ro' => 'ro',
            'ru' => 'ru',
            'es' => 'es',
            'sv' => 'sv',
            'th' => 'th',
            'tr' => 'tr',
            'vi' => 'vn',
        ];
        $locale = $flatpickrLocales[app()->getLocale()] ?? 'en';
    @endphp

    <script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/l10n/{{ $locale }}.js"></script>

    <script>
        "use strict";

        // Global Button Loaders
        function setBtnLoading(btn, text) {
            if (!btn) return;
            btn.disabled = true;
            btn.dataset.originalHtml = btn.innerHTML;
            btn.classList.add('opacity-75', 'cursor-not-allowed');
            btn.innerHTML = `
                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline-block align-middle" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span class="align-middle">${text || '{{ __('Processing...') }}'}</span>
            `;
        }

        function resetBtnLoading(btn) {
            if (!btn) return;
            btn.disabled = false;
            btn.classList.remove('opacity-75', 'cursor-not-allowed');
            if (btn.dataset.originalHtml) {
                btn.innerHTML = btn.dataset.originalHtml;
            }
        }

        const disableSlots = {!! $appointment_slots ?? '{}' !!};

        document.addEventListener('DOMContentLoaded', function() {
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
            const allowedDays = Object.keys(availableDays).filter(day => availableDays[day]).map(day => dayMap[
                day]);

            if (document.getElementById('appointment-date')) {
                flatpickr("#appointment-date", {
                    dateFormat: "Y-m-d",
                    minDate: "today",
                    locale: "{{ $locale }}",
                    monthSelectorType: "static", // Forces static header to show Month and Year cleanly
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
                        generateOption(selectedDate, day);
                    }
                });
            }

            if (document.getElementById('service_start_date')) {
                flatpickr("#service_start_date", {
                    dateFormat: "Y-m-d",
                    minDate: "today",
                    locale: "{{ $locale }}",
                    monthSelectorType: "static", // Forces static header to show Month and Year cleanly
                    disable: [function(date) {
                        return !allowedDays.includes(date.getDay());
                    }],
                });
            }

            if (document.getElementById('service_end_date')) {
                flatpickr("#service_end_date", {
                    dateFormat: "Y-m-d",
                    minDate: "today",
                    locale: "{{ $locale }}",
                    monthSelectorType: "static", // Forces static header to show Month and Year cleanly
                    disable: [function(date) {
                        return !allowedDays.includes(date.getDay());
                    }],
                });
            }

            flatpickr(".timepicker", {
                enableTime: true,
                noCalendar: true,
                dateFormat: "H:i",
                time_24hr: true
            });

            // Contact Form Listener
            const contactForm = document.getElementById('contactForm');
            if (contactForm) {
                contactForm.addEventListener('submit', function() {
                    const btn = document.getElementById('btn-submit-contact') || contactForm.querySelector(
                        'button[type="submit"]');
                    setBtnLoading(btn, '{{ __('Sending...') }}');
                });
            }

            // Password Protection Form Listener
            const passwordForm = document.getElementById('passwordProtectionForm');
            if (passwordForm) {
                passwordForm.addEventListener('submit', function() {
                    const btn = document.getElementById('btn-submit-password') || passwordForm
                        .querySelector('button[type="submit"]');
                    setBtnLoading(btn, '{{ __('Verifying...') }}');
                });
            }
        });

        // Service Booking Action
        function submitServiceBooking() {
            const btn = document.getElementById('btn-submit-service-booking') || event?.target;
            const customerName = (document.getElementById('customer_name')?.value || '').trim();
            const customerEmail = (document.getElementById('customer_email')?.value || '').trim();
            const customerPhone = (document.getElementById('customer_phone')?.value || '').trim();
            const noOfPersons = (document.getElementById('no_of_persons')?.value || '').trim();
            const customerAddress = (document.getElementById('customer_address')?.value || '').trim();
            const customerNotes = (document.getElementById('customer_notes')?.value || '').trim();
            const serviceStartDate = (document.getElementById('service_start_date')?.value || '').trim();
            const serviceStartTime = (document.getElementById('service_start_time')?.value || '').trim();
            const serviceEndDate = (document.getElementById('service_end_date')?.value || '').trim();
            const serviceEndTime = (document.getElementById('service_end_time')?.value || '').trim();
            const errorMessage1 = document.getElementById('errorMessage1');
            const successMessage1 = document.getElementById('successMessage1');

            if (errorMessage1) errorMessage1.classList.add('hidden');
            if (successMessage1) successMessage1.classList.add('hidden');

            if (!customerName || !customerEmail || !customerPhone || !noOfPersons ||
                !customerAddress || !serviceStartDate || !serviceStartTime ||
                !serviceEndDate || !serviceEndTime) {
                if (errorMessage1) {
                    errorMessage1.innerHTML =
                        '<i class="fas fa-exclamation-circle mr-1"></i> {{ __('Please fill all the required fields.') }}';
                    errorMessage1.classList.remove('hidden');
                }
                return;
            }

            setBtnLoading(btn, '{{ __('Booking...') }}');

            const formData = {
                card: `{{ $business_card_details->card_id ?? '' }}`,
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
                    if (data.success) {
                        ['customer_name', 'customer_email', 'customer_phone', 'no_of_persons', 'customer_address',
                            'customer_notes', 'service_start_date', 'service_start_time', 'service_end_date',
                            'service_end_time'
                        ]
                        .forEach(id => {
                            const el = document.getElementById(id);
                            if (el) el.value = '';
                        });

                        if (errorMessage1) errorMessage1.classList.add('hidden');
                        if (successMessage1) {
                            successMessage1.innerHTML = '<i class="fas fa-check-circle mr-1"></i> ' + (data
                                .message || '{{ __('Your service has been successfully booked!') }}');
                            successMessage1.classList.remove('hidden');
                        }
                    } else {
                        if (successMessage1) successMessage1.classList.add('hidden');
                        if (errorMessage1) {
                            errorMessage1.innerHTML = '<i class="fas fa-exclamation-circle mr-1"></i> ' + (data
                                .message || '{{ __('Something went wrong.') }}');
                            errorMessage1.classList.remove('hidden');
                        }
                    }
                })
                .catch(error => {
                    if (successMessage1) successMessage1.classList.add('hidden');
                    if (errorMessage1) {
                        errorMessage1.innerHTML =
                            '<i class="fas fa-exclamation-circle mr-1"></i> {{ __('Something went wrong. Please try again.') }}';
                        errorMessage1.classList.remove('hidden');
                    }
                })
                .finally(() => {
                    resetBtnLoading(btn);
                });
        }

        function toggleModal() {
            const modal = document.getElementById('appointmentModal');
            if (modal) modal.classList.toggle('hidden');
        }

        function validateAndShowModal() {
            const appointmentDate = document.getElementById('appointment-date')?.value;
            const timeSlotSelect = document.getElementById('time-slot-select')?.value;
            if (appointmentDate && timeSlotSelect) toggleModal();
        }

        function onloadCallback() {
            window.recaptchaWidgets = window.recaptchaWidgets || {};

            if (typeof grecaptcha !== 'undefined' && typeof grecaptcha.render === 'function') {
                if (!window.recaptchaWidgets['recaptcha-one'] && document.getElementById('recaptcha-one')) {
                    window.recaptchaWidgets['recaptcha-one'] = grecaptcha.render('recaptcha-one', {
                        'sitekey': '{{ env('RECAPTCHA_SITE_KEY') }}'
                    });
                }

                if (!window.recaptchaWidgets['recaptcha-two'] && document.getElementById('recaptcha-two')) {
                    window.recaptchaWidgets['recaptcha-two'] = grecaptcha.render('recaptcha-two', {
                        'sitekey': '{{ env('RECAPTCHA_SITE_KEY') }}'
                    });
                }
            }
        }

        // Book Appointment Modal Form Action
        document.getElementById('appointmentForm')?.addEventListener('submit', function(event) {
            event.preventDefault();

            const button = document.getElementById('bookAppointmentButton');
            const errorSubmitMessage = document.getElementById('errorMessage');
            const successMessage = document.getElementById('successMessage');

            setBtnLoading(button, '{{ __('Booking...') }}');

            const formData = {
                name: document.getElementById('name')?.value || '',
                email: document.getElementById('email')?.value || '',
                phone: document.getElementById('phone')?.value || '',
                notes: document.getElementById('notes')?.value || '',
                date: document.getElementById('appointment-date')?.value || '',
                time_slot: document.getElementById('time-slot-select')?.value || '',
                price: document.getElementById('price')?.value || '',
                card: `{{ $business_card_details->card_id ?? '' }}`
            };

            @if (env('RECAPTCHA_ENABLE') == 'on')
                if (window.recaptchaWidgets) {
                    formData.g_recaptcha_response = grecaptcha.getResponse(window.recaptchaWidgets[
                            'recaptcha-one']) ||
                        grecaptcha.getResponse(window.recaptchaWidgets['recaptcha-two']);
                }
            @endif

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
                        ['name', 'email', 'phone', 'notes', 'appointment-date', 'time-slot-select', 'price']
                        .forEach(id => {
                            const el = document.getElementById(id);
                            if (el) el.value = '';
                        });

                        if (typeof generateOption === 'function') generateOption("", "");

                        if (successMessage) successMessage.classList.remove('hidden');
                        if (errorSubmitMessage) errorSubmitMessage.classList.add('hidden');

                        @if (env('RECAPTCHA_ENABLE') == 'on')
                            if (window.recaptchaWidgets && window.recaptchaWidgets['recaptcha-one']) {
                                grecaptcha.reset(window.recaptchaWidgets['recaptcha-one']);
                            }
                        @endif

                        toggleModal();

                        if (data.success && data.whatsapp_url && data.whatsapp_url !== '#') {
                            setTimeout(() => {
                                window.location.href = data.whatsapp_url;
                            }, 2000);
                        }
                    } else {
                        if (successMessage) successMessage.classList.add('hidden');
                        if (errorSubmitMessage) {
                            errorSubmitMessage.classList.remove('hidden');
                            errorSubmitMessage.innerHTML = data.message ||
                                '{{ __('Something went wrong.') }}';
                        }
                        toggleModal();
                    }
                })
                .catch(error => {
                    toggleModal();
                })
                .finally(() => {
                    resetBtnLoading(button);
                });
        });

        function generateOption(selectedDate, day) {
            fetch('/get-available-time-slots', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    body: JSON.stringify({
                        card: `{{ $business_card_details->card_id ?? '' }}`,
                        choose_date: selectedDate,
                        day: day
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success == true) {
                        const select = document.getElementById('time-slot-select');
                        if (select) {
                            select.innerHTML = `<option value="">{{ __('Select a time slot') }}`;
                            var available_time_slots = JSON.parse(data.available_time_slots);

                            available_time_slots.forEach(time_slot => {
                                select.innerHTML += `<option value="${time_slot}">${time_slot}</option>`;
                            });
                        }

                        const priceElement = document.getElementById('price');
                        if (priceElement) priceElement.value = data.price;
                    }
                });
        }

        // Swiper Initializers
        if (document.querySelector('.mySwiper')) {
            new Swiper(".mySwiper", {
                slidesPerView: 1,
                spaceBetween: 16,
                observer: true,
                observeParents: true,
                breakpoints: {
                    1024: {
                        slidesPerView: 2
                    }
                },
                pagination: {
                    el: ".mySwiper .swiper-pagination",
                    clickable: true
                },
                autoplay: {
                    delay: 3500
                },
            });
        }

        if (document.querySelector('.testimonialSwiper')) {
            new Swiper(".testimonialSwiper", {
                slidesPerView: 1,
                spaceBetween: 16,
                observer: true,
                observeParents: true,
                pagination: {
                    el: ".testimonialSwiper .swiper-pagination",
                    clickable: true
                },
                autoplay: {
                    delay: 4500
                },
            });
        }

        const scroll = new SmoothScroll('a[href*="#"]', {
            speed: 400,
            offset: 50,
            easing: "easeInOutCubic",
        });

        @if ($introScreen != null)
            window.addEventListener("load", () => {
                const loader = document.getElementById("loader");
                if (loader) loader.classList.add("hidden");
            });
        @endif
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var introScreen = document.getElementById('index');
            var rootModals = document.getElementById('root-level-modals');
            var revealed = false;

            function revealModals() {
                if (revealed) return;
                revealed = true;
                if (rootModals) rootModals.style.display = '';
                document.dispatchEvent(new Event('introDismissed'));
            }

            // No intro screen at all (desktop / disabled) → show modals immediately
            if (!introScreen) {
                revealModals();
                return;
            }

            // 1) Direct tap on "Swipe to Continue" pill
            document.getElementById('index-text')?.addEventListener('click', revealModals);

            // 2) content-screen becomes visible → intro is effectively dismissed
            var contentScreen = document.getElementById('content-screen');
            if (contentScreen && 'IntersectionObserver' in window) {
                var io = new IntersectionObserver(function(entries) {
                    entries.forEach(function(entry) {
                        if (entry.isIntersecting) {
                            revealModals();
                            io.disconnect();
                        }
                    });
                }, {
                    threshold: 0.15
                });
                io.observe(contentScreen);
            }

            // 3) Fallback: any scroll/touch movement past a small threshold
            var scrollTarget = document.getElementById('smooth-content') || window;

            function onScrollLike() {
                var scrolled = (scrollTarget === window) ?
                    window.scrollY :
                    (window.pageYOffset || document.documentElement.scrollTop);
                if (scrolled > 60) revealModals();
            }
            window.addEventListener('scroll', onScrollLike, {
                passive: true
            });
            window.addEventListener('wheel', onScrollLike, {
                passive: true
            });
            window.addEventListener('touchmove', onScrollLike, {
                passive: true
            });
        });
    </script>
</body>

</html>
