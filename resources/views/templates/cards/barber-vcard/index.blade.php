<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

@php
    use Illuminate\Support\Facades\Session;
    use App\BusinessCardIntro;

    if (isset($service_booking_details) && $service_booking_details->service_booking == 1) {
        $service_booking_available_days = json_decode($service_booking_details->service_booking_available_days);
    }

    $introScreen = BusinessCardIntro::where('business_card_intro_id', $business_card_details->intro_screen)
        ->where('status', 1)
        ->first();
@endphp

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
        <link rel="icon" href="{{ url($business_card_details->profile) }}" sizes="512x512" type="image/png" />
        <link rel="apple-touch-icon" href="{{ url($business_card_details->profile) }}">
    @endif

    <meta name="theme-color" content="#14100D" />

    <!-- Add to homescreen for Chrome on Android -->
    <meta name="application-name" content="{{ $card_details->title }}">

    <!-- Add to homescreen for Safari on iOS -->
    <meta name="apple-mobile-web-app-title" content="{{ $card_details->title }}">

    <!-- Tile for Win8 -->
    <meta name="msapplication-TileColor" content="#14100D">
    <meta name="msapplication-TileImage" content="{{ url($business_card_details->profile) }}">

    {!! SEOMeta::generate() !!}
    {!! OpenGraph::generate() !!}
    {!! Twitter::generate() !!}
    {!! JsonLd::generate() !!}

    {{-- Intro Screen CSS --}}
    @if ($introScreen != null)
        <link rel="stylesheet" href="{{ asset('templates/css/intros/' . $introScreen->intro_code . '.min.css') }}">
    @endif

    <!-- Tailwind CSS -->
    <link rel="stylesheet" href="{{ url('templates/css/barber-vcard.css') }}">
    {{-- Swiper CSS --}}
    <link rel="stylesheet" href="{{ url('css/swiper-bundle.min.css') }}">
    {{-- Fontawesome CSS --}}
    <link rel="stylesheet" href="{{ url('css/fontawesome.min.css') }}" />

    <!-- Google Fonts: Bebas Neue (signage display) + Libre Franklin (body) -->
    <link
        href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Libre+Franklin:wght@400;500;600;700;800&display=swap"
        rel="stylesheet" />

    <style>
        /* Keep all long vCard descriptions to three lines with an ellipsis. */
        .bb-clamp-3 {
            display: -webkit-box;
            overflow: hidden;
            text-overflow: ellipsis;
            word-break: break-word;
            -webkit-box-orient: vertical;
            -webkit-line-clamp: 3;
            line-clamp: 3;
        }

        /* Consistent service, product, and gallery card sizing. */
        .bb-card-grid {
            align-items: stretch;
        }

        .bb-service-card,
        .bb-product-card {
            min-height: 0;
            height: auto;
        }

        .bb-service-image,
        .bb-product-image {
            width: 100%;
            aspect-ratio: 16 / 10;
            height: auto;
            object-fit: cover;
            border-radius: .75rem;
        }

        .bb-service-card .bb-btn-outline,
        .bb-product-card .bb-btn-outline {
            margin-top: auto;
        }

        /* Booking form fields */
        .bb-booking-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(min(100%, 16rem), 1fr));
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .bb-booking-date-grid {
            grid-template-columns: repeat(auto-fit, minmax(min(100%, 11rem), 1fr));
            margin-bottom: 0;
        }

        .bb-booking-form .bb-input {
            min-width: 0;
        }

        /* Subtle layered depth behind the vCard. */
        body.bb-body {
            position: relative;
            isolation: isolate;
            overflow-x: hidden;
            background:
                radial-gradient(circle at 15% 10%, rgba(201, 162, 75, .16), transparent 28rem),
                radial-gradient(circle at 88% 68%, rgba(183, 40, 46, .13), transparent 32rem),
                linear-gradient(135deg, #100d0a 0%, #201a15 48%, #100d0a 100%);
        }

        body.bb-body::before,
        body.bb-body::after {
            content: "";
            position: fixed;
            z-index: -1;
            pointer-events: none;
            border: 1px solid rgba(201, 162, 75, .15);
            transform: rotate(35deg) skewY(-8deg);
            box-shadow: 0 35px 90px rgba(0, 0, 0, .48);
        }

        body.bb-body::before {
            width: 27rem;
            height: 27rem;
            top: 10%;
            left: -14rem;
            background: linear-gradient(135deg, rgba(201, 162, 75, .10), transparent 65%);
        }

        body.bb-body::after {
            width: 34rem;
            height: 34rem;
            right: -21rem;
            bottom: 4%;
            background: linear-gradient(135deg, rgba(183, 40, 46, .10), transparent 68%);
        }

        /* Modal close button styling */
        .modal-close-btn {
            position: absolute;
            z-index: 10;
            top: .85rem;
            right: .85rem;
            width: 2.25rem;
            height: 2.25rem;
            color: var(--bb-gold);
            background: var(--bb-char-2);
            border: 1px solid var(--bb-line);
            border-radius: 50%;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .modal-close-btn:hover {
            color: var(--bb-black);
            background: var(--bb-gold);
        }
    </style>

    <!-- Include the qrious library -->
    <script src="{{ url('js/qrious.min.js') }}"></script>

    {{-- Swiper JS --}}
    <script src="{{ url('js/swiper-bundle.min.js') }}"></script>

    {{-- GSAP + ScrollTrigger --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js"></script>

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

<body class="bb-body min-h-screen"
    dir="{{ App::isLocale('ar') || App::isLocale('ur') || App::isLocale('he') ? 'rtl' : 'ltr' }}">

    {{-- Include 3D Barber Background Partial --}}
    @include('templates.cards.barber-vcard.includes.bg-icons')

    {{-- Loader --}}
    @if ($introScreen != null)
        <!-- Loader (barber pole spin-up) -->
        <div id="loader" class="bb-loader">
            <div class="spinner"></div>
        </div>
    @endif

    <div id="smooth-wrapper">
        <div id="smooth-content" class="container max-w-2xl mx-auto lg:my-8 relative">
            {{-- Start Check password protected --}}
            @if ($business_card_details->password == null || Session::get('password_protected') == true)
                {{-- Check business details --}}
                @if ($business_card_details != null)
                    <!-- Profile Card -->
                    <div class="bb-panel lg:rounded-2xl overflow-hidden relative" id="profile">

                        {{-- Index Screen --}}
                        @if ($introScreen != null)
                            @include("templates.cards.includes.intros.{$introScreen->intro_code}", [
                                'theme' => $business_card_details->theme_id,
                            ])
                        @endif

                        <div id="content-screen">
                            <!-- Start Cover Image Section -->
                            @if ($business_card_details->cover_type == 'none')
                                <div class="lg:h-80 h-80 relative bb-hero" id="bb-hero">
                                    <img src="{{ url('img/templates/barber/banner.png') }}"
                                        alt="{{ $business_card_details->title }}"
                                        class="w-full h-full object-cover opacity-70" />
                                    <div
                                        class="absolute inset-0 bg-gradient-to-b from-black/10 via-black/40 to-[#14100D]">
                                    </div>
                                    <!-- Signature element: animated barber pole -->
                                    <div class="absolute top-1/2 -translate-y-1/2 ltr:right-8 rtl:left-8 h-32 lg:h-48">
                                        <div class="bb-pole-cap bb-pole-cap-top"></div>
                                        <div class="bb-pole h-full"></div>
                                        <div class="bb-pole-cap bb-pole-cap-bottom"></div>
                                    </div>
                                </div>
                            @endif
                            <!-- End Cover Image Section -->

                            <!-- Start Cover Image Section -->
                            @if ($business_card_details->cover_type == 'photo')
                                <div class="lg:h-80 h-80 relative" id="bb-hero">
                                    <img src="{{ $business_card_details->cover ? url($business_card_details->cover) : asset('images/default-cover.png') }}"
                                        alt="{{ $business_card_details->title }}"
                                        class="w-full h-full object-cover grayscale-[20%]" />
                                    <div
                                        class="absolute inset-0 bg-gradient-to-b from-black/10 via-black/40 to-[#14100D]">
                                    </div>
                                </div>
                            @endif
                            <!-- End Cover Image Section -->

                            <!-- Start Cover Video Section -->
                            @if ($business_card_details->cover_type == 'vimeo-ap')
                                <div class="relative w-full" style="padding-top: 56.25%;" id="bb-hero">
                                    <iframe referrerpolicy="strict-origin-when-cross-origin"
                                        src="https://player.vimeo.com/video/{{ $business_card_details->cover }}?autoplay=1&loop=1&autopause=0&muted=1&controls=0"
                                        id="vid-player" frameborder="0" allow="autoplay;"
                                        class="absolute top-0 left-0 w-full h-full">
                                    </iframe>
                                </div>
                            @endif

                            {{-- Cover Vimeo --}}
                            @if ($business_card_details->cover_type == 'vimeo')
                                <div class="relative w-full" style="padding-top: 56.25%;" id="bb-hero">
                                    <iframe referrerpolicy="strict-origin-when-cross-origin"
                                        src="https://player.vimeo.com/video/{{ $business_card_details->cover }}?autoplay=0&loop=1&autopause=0&muted=0&controls=1"
                                        id="vid-player" frameborder="0" allow="autoplay;"
                                        class="absolute top-0 left-0 w-full h-full">
                                    </iframe>
                                </div>
                            @endif

                            {{-- Cover YouTube AP --}}
                            @if ($business_card_details->cover_type == 'youtube-ap')
                                <div class="relative w-full" style="padding-top: 56.25%;" id="bb-hero">
                                    <iframe referrerpolicy="strict-origin-when-cross-origin"
                                        src="https://www.youtube.com/embed/{{ $business_card_details->cover }}?autoplay=1&mute=1&controls=0&loop=1&playlist={{ $business_card_details->cover }}"
                                        id="vid-player" frameborder="0" allow="autoplay;"
                                        class="absolute top-0 left-0 w-full h-full">
                                    </iframe>
                                </div>
                            @endif

                            {{-- Cover YouTube --}}
                            @if ($business_card_details->cover_type == 'youtube')
                                <div class="relative w-full" style="padding-top: 56.25%;" id="bb-hero">
                                    <iframe referrerpolicy="strict-origin-when-cross-origin"
                                        src="https://www.youtube.com/embed/{{ $business_card_details->cover }}?autoplay=0&mute=1&controls=0&loop=1&playlist={{ $business_card_details->cover }}"
                                        id="vid-player" frameborder="0" allow="autoplay;"
                                        class="absolute top-0 left-0 w-full h-full">
                                    </iframe>
                                </div>
                            @endif
                            <!-- End Cover Video Section -->

                            {{-- Language Switcher --}}
                            @if (
                                $business_card_details->is_enable_language_switcher == 1 &&
                                    is_array(config('app.languages')) &&
                                    count(config('app.languages')) > 1)
                                @include('templates.cards.barber-vcard.includes.language-switcher')
                            @endif

                            <!-- Profile Info -->
                            <div class="relative px-6 -mt-0.5 pb-32 lg:pb-6">
                                <div class="flex justify-center">
                                    <img src="{{ url($business_card_details->profile) }}"
                                        alt="{{ $business_card_details->title }}"
                                        class="w-36 h-36 rounded-full -mt-16 z-10 object-cover border-4"
                                        style="border-color: var(--bb-gold); box-shadow: var(--bb-shadow);" />
                                </div>
                                <div class="text-center my-4 relative" id="bb-name-block">
                                    <p class="bb-eyebrow mb-1">{{ __('Master Barbers') }}</p>

                                    {{-- Check title --}}
                                    <h1 class="bb-display text-6xl bb-gold-text bb-reveal-up" id="pName">
                                        {{ $business_card_details->title }}
                                    </h1>

                                    {{-- Check sub title --}}
                                    <p class="text-[#B7282E] font-semibold mt-1 text-sm uppercase tracking-widest">
                                        {{ $card_details->sub_title }}
                                    </p>

                                    {{-- Check description --}}
                                    @if ($business_card_details->description != null)
                                        <div class="mt-4 text-sm leading-relaxed px-6 font-medium bb-text-muted">
                                            {!! $business_card_details->description !!}
                                        </div>
                                    @endif
                                </div>


                                {{-- Quick Actions & Contact Section --}}
                                @include('templates.cards.barber-vcard.includes.quick-actions')

                                <!-- Start Location Section -->
                                @if (count($feature_details) > 0)
                                    @foreach ($feature_details as $feature)
                                        @if (in_array($feature->type, ['address']))
                                            <div class="flex justify-center relative bb-scroll-reveal">
                                                <div class="mt-8 mb-4 bb-card rounded-2xl w-full">
                                                    <a href="https://www.google.com/maps/place/{{ urlencode($feature->content) }}"
                                                        target="_blank"
                                                        class="px-6 py-5 flex flex-col justify-center">
                                                        <!-- Font Awesome Icon -->
                                                        <i
                                                            class="{{ $feature->icon }} fa-xl bb-gold-text text-xl py-4"></i>
                                                        <!-- Title -->
                                                        <h2 class="text-[#EDE4D3] text-md font-semibold">
                                                            {{ $feature->label }}
                                                        </h2>
                                                        <!-- Description -->
                                                        <p class="bb-text-muted text-sm flex items-center">
                                                            {{ $feature->content }}
                                                        </p>
                                                    </a>
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                @endif
                                {{-- End Location Section --}}

                                <!-- Start Social Links Section -->
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
                                        <div class="bb-divider bb-scroll-reveal">
                                            <span class="bb-divider-mark"></span>
                                            <h2 class="bb-display text-3xl bb-gold-text whitespace-nowrap">
                                                {{ __($feature_details[0]->title) }}
                                            </h2>
                                        </div>

                                        <!-- Start contact with icon, title and description -->
                                        <div
                                            class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 py-4 bb-scroll-reveal">
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
                                                    class="bb-card p-4 rounded-2xl flex flex-col">
                                                    <i
                                                        class="{{ $feature->icon }} bb-gold-text text-xl -mt-1 mb-1"></i>
                                                    <h2 class="text-[#EDE4D3] text-md font-semibold">
                                                        {{ $feature->label }}</h2>
                                                    <p class="bb-text-muted text-sm truncate">
                                                        {{ $feature->content }}
                                                    </p>
                                                </a>
                                            @endforeach
                                        </div>
                                    @endif
                                @endif
                                <!-- End Social Links Section -->

                                <!-- Start Services Section -->
                                @if (count($service_details) > 0)
                                    <div class="py-10 bb-scroll-reveal">
                                        <div class="bb-divider">
                                            <span class="bb-divider-mark"></span>
                                            <h2 class="bb-display text-3xl bb-gold-text whitespace-nowrap">
                                                {{ __($service_details[0]->title) }}
                                            </h2>
                                        </div>
                                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6 bb-card-grid">
                                            @foreach ($service_details as $service_detail)
                                                <div
                                                    class="flex flex-col bb-card rounded-2xl p-3 h-full bb-service-card">
                                                    <img src="{{ url($service_detail->service_image) }}"
                                                        alt="{{ $service_detail->service_name }}"
                                                        class="bb-service-image mb-3" />
                                                    <h2
                                                        class="text-[#EDE4D3] text-md font-semibold bb-display tracking-wide text-lg">
                                                        {{ $service_detail->service_name }}
                                                    </h2>
                                                    <p class="bb-text-muted text-sm mt-2 mb-3 bb-clamp-3">
                                                        {{ $service_detail->service_description }}
                                                    </p>
                                                    @if ($enquiry_button != null)
                                                        @if ($whatsAppNumberExists == true && $whatsAppNumberExists == true && $service_detail->enable_enquiry == 'Enabled')
                                                            <a href="https://wa.me/{{ $enquiry_button }}?text={{ __('Hi, I am interested in your product/service:') }} {{ $service_detail->service_name }}. {{ __('Please provide more details.') }}"
                                                                target="_blank"
                                                                class="bb-btn-outline text-sm text-center font-semibold mt-3 py-2 px-4 rounded-lg focus:outline-none">
                                                                {{ __('Enquire') }}
                                                            </a>
                                                        @endif
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                                <!-- End Services Section -->

                                <!-- Start Products Section -->
                                @if (count($product_details) > 0)
                                    <div class="py-8 bb-scroll-reveal">
                                        <div class="bb-divider">
                                            <span class="bb-divider-mark"></span>
                                            <h2 class="bb-display text-3xl bb-gold-text whitespace-nowrap">
                                                {{ __($product_details[0]->title) }}
                                            </h2>
                                        </div>
                                        <div
                                            class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-2 gap-4 mt-6 bb-card-grid">
                                            @foreach ($product_details as $product_detail)
                                                <div
                                                    class="flex flex-col bb-card rounded-2xl p-3 h-full bb-product-card">
                                                    <div class="relative flex items-center">
                                                        <img src="{{ url($product_detail->product_image) }}"
                                                            alt="{{ $product_detail->product_name }}"
                                                            class="bb-product-image mb-2" />

                                                        @if (!empty($product_detail->badge))
                                                            <span
                                                                class="bb-badge text-xs font-semibold px-2 py-1 rounded-full absolute bottom-4 right-2">
                                                                {{ $product_detail->badge }}
                                                            </span>
                                                        @endif
                                                    </div>
                                                    <h3
                                                        class="text-[#EDE4D3] text-md font-semibold bb-display tracking-wide text-lg">
                                                        {{ $product_detail->product_name }}
                                                    </h3>
                                                    <p class="bb-text-muted text-sm mt-2 mb-3 bb-clamp-3">
                                                        {{ $product_detail->product_description }}
                                                    </p>
                                                    <div class="grid grid-cols-1 gap-1 mt-2 mb-3">
                                                        @if ($product_detail->sales_price != 0)
                                                            <div class="flex items-center">
                                                                <span
                                                                    class="font-bold text-[#EDE4D3] text-sm mr-2">{{ __('Price:') }}</span>
                                                                <span class="text-[#C9A24B] text-sm font-semibold">
                                                                    {{ formatCurrencyVcard($product_detail->sales_price, $product_detail->currency) }}
                                                                    @if ($product_detail->sales_price != $product_detail->regular_price)
                                                                        <span class="line-through ml-2 bb-text-muted">
                                                                            {{ formatCurrencyVcard($product_detail->regular_price, $product_detail->currency) }}
                                                                        </span>
                                                                    @endif
                                                                </span>
                                                            </div>
                                                        @endif

                                                        @if ($product_detail->product_status != 'null')
                                                            <div class="flex items-center">
                                                                <span
                                                                    class="font-bold text-[#EDE4D3] text-sm mr-2">{{ __('Stock:') }}</span>
                                                                <span
                                                                    class="text-{{ $product_detail->product_status == 'instock' ? 'green-500' : 'red-500' }} text-sm capitalize font-semibold">{{ $product_detail->product_status == 'outstock' ? __('Out of Stock') : __('In Stock') }}</span>
                                                            </div>
                                                        @endif
                                                    </div>
                                                    @if ($enquiry_button != null)
                                                        @if ($whatsAppNumberExists == true)
                                                            <a href="https://wa.me/{{ $enquiry_button }}?text={{ __('Hi, I am interested in your product:') }} {{ $product_detail->product_name }}. {{ __('Please provide more details.') }}"
                                                                target="_blank"
                                                                class="bb-btn-outline text-sm text-center font-semibold mt-3 py-2 px-4 rounded-lg focus:outline-none">
                                                                {{ __('Enquire') }}
                                                            </a>
                                                        @endif
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                                <!-- End Products Section -->

                                <!-- Start Gallery Section -->
                                @if (count($galleries_details) > 0)
                                    <div class="py-8 bb-scroll-reveal">
                                        <div class="bb-divider">
                                            <span class="bb-divider-mark"></span>
                                            <h2 class="bb-display text-3xl bb-gold-text whitespace-nowrap">
                                                {{ __($galleries_details[0]->title) }}
                                            </h2>
                                        </div>
                                        <div class="grid grid-cols-1 gap-4 mb-10">
                                            <div class="swiper-container mySwiper">
                                                <div class="swiper-wrapper">
                                                    @foreach ($galleries_details as $galleries_detail)
                                                        <div class="swiper-slide h-auto">
                                                            <div class="bb-card bb-gallery-card">
                                                                <img src="{{ url($galleries_detail->gallery_image) }}"
                                                                    alt="{{ $galleries_detail->caption }}"
                                                                    class="bb-gallery-image {{ $galleries_detail->caption ? '' : 'rounded-b-2xl' }}" />

                                                                @if ($galleries_detail->caption)
                                                                    <div class="bb-gallery-caption text-center p-4">
                                                                        <h2
                                                                            class="text-base font-semibold text-[#EDE4D3] bb-clamp-3">
                                                                            {{ $galleries_detail->caption }}
                                                                        </h2>
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                                <div class="swiper-pagination mt-4"></div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                                <!-- End Gallery Section -->

                                <!-- Start Instagram Section -->
                                @include('templates.cards.includes.instagram-embed', [
                                    'sectionClass' => 'pt-10 pb-8 bb-scroll-reveal',
                                    'customHeader' => '<div class="bb-divider"><span class="bb-divider-mark"></span><h2 class="bb-display text-3xl bb-gold-text whitespace-nowrap">' . __('Instagram') . '</h2></div>',
                                    'cardClass'    => 'rounded-2xl overflow-hidden bb-card shadow-lg',
                                    'labelClass'   => 'px-5 py-3 text-[#EDE4D3] font-semibold text-lg text-center'
                                ])
                                <!-- End Instagram Section -->

                                <!-- Start Client Reviews section -->
                                @if (count($testimonials) > 0)
                                    <div class="pt-10 pb-8 bb-scroll-reveal">
                                        <div class="bb-divider">
                                            <span class="bb-divider-mark"></span>
                                            <h2 class="bb-display text-3xl bb-gold-text whitespace-nowrap">
                                                {{ __($testimonials[0]->title) }}
                                            </h2>
                                        </div>
                                        <div class="swiper-container testimonialSwiper">
                                            <div class="swiper-wrapper">
                                                @foreach ($testimonials as $testimonial)
                                                    <div class="swiper-slide h-auto">
                                                        <div class="bb-card p-4 rounded-2xl h-full">
                                                            <img src="{{ url($testimonial->reviewer_image) }}"
                                                                alt="{{ $testimonial->reviewer_name }}"
                                                                class="h-16 w-16 rounded-full object-cover mb-2 border-2"
                                                                style="border-color: var(--bb-gold);" />
                                                            <p class="text-[#EDE4D3] text-sm italic bb-clamp-3">
                                                                "{{ $testimonial->review }}"
                                                            </p>
                                                            <p class="bb-gold-text font-medium text-sm mt-2">
                                                                - {{ $testimonial->reviewer_name }}
                                                                @if ($testimonial->review_subtext)
                                                                    <span class="bb-text-muted text-xs font-normal">
                                                                        ({{ $testimonial->review_subtext }})
                                                                    </span>
                                                                @endif
                                                            </p>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                            <div class="swiper-pagination mt-4"></div>
                                        </div>
                                    </div>
                                @endif
                                <!-- End Client Reviews section -->

                                <!-- Start Youtube Video Section -->
                                @if ($feature_details->where('type', 'youtube')->count() > 0)
                                    <div class="py-5 bb-scroll-reveal">
                                        <h2 class="bb-display text-3xl bb-gold-text mb-4">
                                            {{ __('Behind The Chair') }}
                                        </h2>
                                        <div class="grid sm:grid-cols-2 lg:grid-cols-2 gap-4 items-center">
                                            @foreach ($feature_details as $feature)
                                                @if ($feature->type == 'youtube')
                                                    <div class="rounded-2xl overflow-hidden bb-card">
                                                        <iframe referrerpolicy="strict-origin-when-cross-origin"
                                                            width="100%" height="270"
                                                            src="https://www.youtube.com/embed/{{ $feature->content }}"
                                                            title="{{ $feature->label }}" frameborder="0"
                                                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                                            allowfullscreen></iframe>
                                                        @if ($feature->label != null)
                                                            <div class="px-5 py-3">
                                                                <div class="mb-2">
                                                                    <div
                                                                        class="text-[#EDE4D3] font-semibold text-lg mb-2">
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
                                    <div class="py-5 bb-scroll-reveal">
                                        <h2 class="bb-display text-3xl bb-gold-text mb-4">
                                            {{ __('Iframe') }}
                                        </h2>
                                        <div class="grid sm:grid-cols-2 lg:grid-cols-1 gap-4 items-center">
                                            @foreach ($feature_details as $feature)
                                                @if ($feature->type == 'iframe')
                                                    <div class="rounded-2xl overflow-hidden bb-card">
                                                        <iframe referrerpolicy="strict-origin-when-cross-origin"
                                                            width="100%" height="270"
                                                            src="{{ $feature->content }}"
                                                            title="{{ $feature->label }}" frameborder="0"
                                                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                                            allowfullscreen></iframe>
                                                        @if ($feature->label != null)
                                                            <div class="px-5 py-3">
                                                                <div class="mb-2">
                                                                    <div
                                                                        class="text-[#EDE4D3] font-semibold text-lg mb-2">
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

                                <!-- Start Appointment Section -->
                                @if ($appointmentEnabled == true && isset($plan_details['appointment']) == 1)
                                    <div id="appointment-section" class="py-8 bb-scroll-reveal">
                                        @if ($plan_details['appointment'] == 1)
                                            @if ($appointment_slots != null)
                                                <div class="bb-divider">
                                                    <span class="bb-divider-mark"></span>
                                                    <h2 class="bb-display text-3xl bb-gold-text whitespace-nowrap">
                                                        {{ __(json_decode($appointment_slots, true)['title']) }}
                                                    </h2>
                                                </div>
                                                <div class="bb-card rounded-2xl p-5 w-full">
                                                    <div id="errorMessage" class="text-red-400 text-sm mt-2 hidden">
                                                        {{ __('Please select a valid date and time slot.') }}</div>

                                                    <div id="successMessage"
                                                        class="text-green-400 text-sm mt-2 hidden">
                                                        {{ __('Appointment booked successfully!') }}</div>

                                                    <div id="errorSubmitMessage"
                                                        class="text-red-400 text-sm mt-2 hidden">
                                                        {{ __('Please fill all the fields.') }}</div>

                                                    <div class="w-full mx-auto my-4">
                                                        <input type="text" id="appointment-date"
                                                            class="bb-input flatpickr-input block w-full px-3 py-2 rounded-md shadow-sm focus:outline-none"
                                                            placeholder="{{ __('Select a date') }}" required>
                                                    </div>

                                                    <div class="w-full mx-auto my-4">
                                                        <select id="time-slot-select"
                                                            class="bb-input block w-full px-3 py-2 rounded-md shadow-sm focus:outline-none mt-2"
                                                            required>
                                                            <option value="">{{ __('Select a time slot') }}
                                                            </option>
                                                        </select>
                                                    </div>

                                                    <div class="flex flex-col items-center justify-center">
                                                        <button type="button" id="add-slot-button"
                                                            class="bb-btn-primary block w-full text-center py-3 rounded-xl text-lg"
                                                            onclick="validateAndShowModal()">
                                                            {{ __('Book Appointment') }}
                                                        </button>
                                                    </div>
                                                </div>
                                            @endif
                                        @endif
                                    </div>
                                @endif
                                <!-- End Appointment Section -->

                                {{-- Start Business Hours --}}
                                @if ($plan_details['business_hours'] == 1)
                                    @if ($business_hours != null && $business_hours->is_display != 0)
                                        <section class="bb-scroll-reveal">
                                            <div class="bb-divider">
                                                <span class="bb-divider-mark"></span>
                                                <h2 class="bb-display text-3xl bb-gold-text whitespace-nowrap">
                                                    {{ __($business_hours->title) }}
                                                </h2>
                                            </div>

                                            <div class="bb-card rounded-2xl py-4 px-4">
                                                @if ($business_hours->is_always_open != 'Opening')
                                                    <div
                                                        class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 pt-3">
                                                        @foreach (['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as $day)
                                                            <div class="flex items-center space-x-4">
                                                                <div class="flex items-center justify-center w-10 h-10 rounded-full"
                                                                    style="background: rgba(201,162,75,0.15); color: var(--bb-gold);">
                                                                    <svg xmlns="http://www.w3.org/2000/svg"
                                                                        width="24" height="24"
                                                                        viewBox="0 0 24 24" fill="none"
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
                                                                        <path
                                                                            d="M18 18m-4 0a4 4 0 1 0 8 0a4 4 0 1 0 -8 0" />
                                                                        <path d="M18 16.5v1.5l.5 .5" />
                                                                    </svg>
                                                                </div>
                                                                <div>
                                                                    <p
                                                                        class="text-sm font-medium bb-text-muted capitalize">
                                                                        {{ __($day) }}</p>
                                                                    <p class="text-base text-[#EDE4D3]">
                                                                        {{ __($business_hours->$day ?: __('Closed')) }}
                                                                    </p>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @else
                                                    <div class="flex items-start space-x-4 py-4">
                                                        <div class="flex items-center justify-center w-12 h-12 rounded-full transform hover:scale-110 transition-transform duration-300 ease-in-out"
                                                            style="background: rgba(201,162,75,0.15); color: var(--bb-gold);">
                                                            <svg class="w-6 h-6 animate-pulse" fill="currentColor"
                                                                xmlns="http://www.w3.org/2000/svg"
                                                                viewBox="0 0 24 24">
                                                                <path
                                                                    d="M12 2a10 10 0 100 20 10 10 0 000-20zM10 16l6-4-6-4v8z" />
                                                            </svg>
                                                        </div>
                                                        <div>
                                                            <p class="text-xl font-bold bb-gold-text">
                                                                {{ __('Always Open') }}</p>
                                                            <p class="text-sm bb-text-muted">
                                                                {{ __('We\'re available 24/7 to serve you!') }}</p>
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
                                        <div class="py-14 lg:py-10 bb-scroll-reveal">
                                            <div class="bb-divider">
                                                <span class="bb-divider-mark"></span>
                                                <h2 class="bb-display text-3xl bb-gold-text whitespace-nowrap">
                                                    {{ __($service_booking_details->title) }}
                                                </h2>
                                            </div>

                                            <div class="w-full mx-auto bb-card rounded-2xl p-5 bb-booking-form">
                                                <div id="errorMessage1"
                                                    class="bg-red-600 text-sm my-2 hidden p-3 text-white rounded-xl">
                                                </div>

                                                <div id="successMessage1"
                                                    class="bg-green-600 text-sm my-2 hidden p-3 text-white rounded-xl">
                                                </div>

                                                <div class="bb-booking-grid">
                                                    <div class="flex flex-col">
                                                        <label for="customer_name"
                                                            class="text-[#EDE4D3] font-medium mb-2">{{ __('Name') }}</label>
                                                        <input type="text" name="customer_name" id="customer_name"
                                                            placeholder="{{ __('Your Name') }}"
                                                            class="bb-input w-full px-4 py-3 rounded-lg focus:outline-none" />
                                                    </div>
                                                    <div class="flex flex-col">
                                                        <label for="customer_email"
                                                            class="text-[#EDE4D3] font-medium mb-2">{{ __('Email') }}</label>
                                                        <input type="email" name="customer_email"
                                                            id="customer_email" placeholder="{{ __('Your Email') }}"
                                                            class="bb-input w-full px-4 py-3 rounded-lg focus:outline-none" />
                                                    </div>
                                                </div>
                                                <div class="bb-booking-grid">
                                                    <div class="flex flex-col">
                                                        <label for="customer_phone"
                                                            class="text-[#EDE4D3] font-medium mb-2">{{ __('Mobile Number') }}</label>
                                                        <input type="tel" name="customer_phone"
                                                            id="customer_phone"
                                                            placeholder="{{ __('Your Mobile Number') }}"
                                                            class="bb-input w-full px-4 py-3 rounded-lg focus:outline-none" />
                                                    </div>
                                                    <div class="flex flex-col">
                                                        <label for="no_of_persons"
                                                            class="text-[#EDE4D3] font-medium mb-2">{{ __('No. of Person(s)') }}</label>
                                                        <input type="number" name="no_of_persons" id="no_of_persons"
                                                            value="1" step="1"
                                                            placeholder="{{ __('No. of Person(s)') }}"
                                                            class="bb-input w-full px-4 py-3 rounded-lg focus:outline-none" />
                                                    </div>
                                                </div>
                                                <div class="flex flex-col mb-4">
                                                    <label for="customer_address"
                                                        class="text-[#EDE4D3] font-medium mb-2">{{ __('Address') }}</label>
                                                    <textarea name="customer_address" id="customer_address" placeholder="{{ __('Your Address') }}" rows="3"
                                                        class="bb-input w-full px-4 py-3 rounded-lg focus:outline-none"></textarea>
                                                </div>
                                                <div class="flex flex-col mb-4">
                                                    <label for="customer_message"
                                                        class="text-[#EDE4D3] font-medium mb-2">{{ __('Notes') }}</label>
                                                    <textarea name="customer_notes" id="customer_notes" placeholder="{{ __('Your Message') }}" rows="3"
                                                        class="bb-input w-full px-4 py-3 rounded-lg focus:outline-none"></textarea>
                                                </div>
                                                <div class="flex flex-col mb-4">
                                                    <label for="service_start_date"
                                                        class="text-[#EDE4D3] font-medium mb-2">{{ __('Service Start DateTime') }}</label>
                                                    <div class="bb-booking-grid bb-booking-date-grid">
                                                        <div class="flex flex-col">
                                                            <input type="text" id="service_start_date"
                                                                name="service_start_date"
                                                                value="{{ $service_booking_details->service_booking_start_date ?? '' }}"
                                                                placeholder="{{ __('Service Start Date') }}"
                                                                class="bb-input w-full px-4 py-3 rounded-lg focus:outline-none" />
                                                        </div>
                                                        <div class="flex flex-col">
                                                            <input type="time" name="service_start_time"
                                                                id="service_start_time"
                                                                value="{{ $service_booking_details->service_booking_start_time ?? '' }}"
                                                                placeholder="{{ __('Service Start Time') }}"
                                                                class="bb-input w-full px-4 py-3 rounded-lg focus:outline-none timepicker" />
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="flex flex-col mb-4">
                                                    <label for="service_end_date"
                                                        class="text-[#EDE4D3] font-medium mb-2">{{ __('Service End DateTime') }}</label>
                                                    <div class="bb-booking-grid bb-booking-date-grid">
                                                        <div class="flex flex-col">
                                                            <input type="date" id="service_end_date"
                                                                name="service_end_date"
                                                                placeholder="{{ __('Service End Date') }}"
                                                                class="bb-input w-full px-4 py-3 rounded-lg focus:outline-none" />
                                                        </div>
                                                        <div class="flex flex-col">
                                                            <input type="time" name="service_end_time"
                                                                id="service_end_time"
                                                                placeholder="{{ __('Service End Time') }}"
                                                                class="bb-input w-full px-4 py-3 rounded-lg focus:outline-none timepicker" />
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="flex flex-col">
                                                    <button type="button" id="btn-submit-service-booking"
                                                        onclick="submitServiceBooking()"
                                                        class="bb-btn-primary w-full px-4 py-4 rounded-xl text-lg focus:outline-none">
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
                                    <div class="py-8 bb-scroll-reveal">
                                        <div class="bb-divider">
                                            <span class="bb-divider-mark"></span>
                                            <h2 class="bb-display text-3xl bb-gold-text whitespace-nowrap">
                                                {{ __($payment_details[0]->title) }}
                                            </h2>
                                        </div>
                                        <div class="grid lg:grid-cols-2 gap-4">
                                            @foreach ($payment_details as $payment)
                                                <div class="flex flex-col bb-card rounded-2xl p-4">
                                                    <div class="flex justify-between items-center">
                                                        @include('templates.cards.partials.payment-link-image')

                                                        @if ($payment->type == 'url')
                                                            <a href="https://{{ str_replace('https://', '', $payment->content) }}"
                                                                target="_blank" rel="noopener noreferrer">
                                                                <div class="rounded-full p-2 flex items-center justify-center"
                                                                    style="border: 1px solid var(--bb-line); background: rgba(201,162,75,0.1);">
                                                                    <svg xmlns="http://www.w3.org/2000/svg"
                                                                        width="24" height="24"
                                                                        viewBox="0 0 24 24" fill="none"
                                                                        stroke="currentColor" stroke-width="2"
                                                                        stroke-linecap="round" stroke-linejoin="round"
                                                                        class="icon icon-tabler icons-tabler-outline icon-tabler-external-link bb-gold-text h-6 w-6">
                                                                        <path stroke="none" d="M0 0h24v24H0z"
                                                                            fill="none" />
                                                                        <path
                                                                            d="M12 6h-6a2 2 0 0 0 -2 2v10a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-6" />
                                                                        <path d="M11 13l9 -9" />
                                                                        <path d="M15 4h5v5" />
                                                                    </svg>
                                                                </div>
                                                            </a>
                                                        @endif

                                                        @if ($payment->type == 'upi')
                                                            <a href="upi://pay?pa={{ $payment->content }}&pn={{ urlencode($payment->label) }}&am=1&cu=INR"
                                                                target="_blank" rel="noopener noreferrer">
                                                                <div class="rounded-full p-2 flex items-center justify-center"
                                                                    style="border: 1px solid var(--bb-line); background: rgba(201,162,75,0.1);">
                                                                    <svg xmlns="http://www.w3.org/2000/svg"
                                                                        width="24" height="24"
                                                                        viewBox="0 0 24 24" fill="none"
                                                                        stroke="currentColor" stroke-width="2"
                                                                        stroke-linecap="round" stroke-linejoin="round"
                                                                        class="icon icon-tabler icons-tabler-outline icon-tabler-external-link bb-gold-text h-6 w-6">
                                                                        <path stroke="none" d="M0 0h24v24H0z"
                                                                            fill="none" />
                                                                        <path
                                                                            d="M12 6h-6a2 2 0 0 0 -2 2v10a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-6" />
                                                                        <path d="M11 13l9 -9" />
                                                                        <path d="M15 4h5v5" />
                                                                    </svg>
                                                                </div>
                                                            </a>
                                                        @endif
                                                    </div>
                                                    <div class="flex flex-col">
                                                        <h3
                                                            class="font-medium text-[#EDE4D3] {{ $payment->type == 'text' ? 'py-3' : 'pt-3 text-center' }}">
                                                            {{ $payment->label }}</h3>

                                                        @if ($payment->type == 'text')
                                                            <p class="bb-text-muted text-xs mt-2 break-keep">
                                                                @foreach (explode('.', $payment->content) as $sentence)
                                                                    @if (trim($sentence))
                                                                        {{ trim($sentence) }}
                                                                        <br>
                                                                    @endif
                                                                @endforeach
                                                            </p>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                                <!-- End Payment section -->

                                <!-- Start Location section -->
                                @if (count($feature_details) > 0 && $feature_details->contains('type', 'map'))
                                    <div id="location-section" class="py-8 bb-scroll-reveal">
                                        <h2 class="bb-display text-3xl bb-gold-text mb-4">
                                            {{ __('Location') }}
                                        </h2>
                                        @foreach ($feature_details as $feature)
                                            @if ($feature->type == 'map')
                                                <iframe referrerpolicy="strict-origin-when-cross-origin"
                                                    src="https://www.google.com/maps/embed?{!! $feature->content !!}"
                                                    width="100%" height="300" style="border: 0"
                                                    allowfullscreen="" loading="lazy" class="rounded-t-2xl">
                                                </iframe>
                                                @if ($feature->label != null)
                                                    <div class="px-5 py-3 bb-card rounded-b-2xl border-t-0">
                                                        <div class="mb-2">
                                                            <div class="text-[#EDE4D3] font-semibold text-lg mb-2">
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
                                @if (plugin_active('GoogleWallet'))
                                    @if (isset($plan_details['google_wallet']) &&
                                            $plan_details['google_wallet'] == 1 &&
                                            $business_card_details->is_google_wallet_hidden == 0)
                                        <div class="py-14 lg:py-10 bb-scroll-reveal">
                                            <h2 class="bb-display text-3xl bb-gold-text mb-6">
                                                {{ __('Google Wallet') }}
                                            </h2>

                                            <div class="w-full mx-auto">
                                                @if ($google_wallet_details->wallet_description != null)
                                                    <div class="text-sm bb-text-muted">
                                                        {!! $google_wallet_details->wallet_description ?? '' !!}
                                                    </div>
                                                @endif
                                                @if ($google_wallet_details->wallet_link != null)
                                                    <div class="flex justify-center mt-6">
                                                        <a href="{{ $google_wallet_details->wallet_link }}"
                                                            class="w-full lg:w-1/2" target="_blank"
                                                            rel="noopener noreferrer">
                                                            <img src="{{ url()->to('/') . '/img/google-wallet-btn.png' }}"
                                                                alt="" class="w-full object-cover">
                                                        </a>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    @endif
                                @endif
                                <!-- End Google Wallet section -->

                                <!-- Start Contact Form Section -->
                                @if ($plan_details['contact_form'] == 1)
                                    @if ($business_card_details->enquiry_email != null)
                                        <section class="py-14 lg:py-10 bb-scroll-reveal">
                                            <div class="bb-divider">
                                                <span class="bb-divider-mark"></span>
                                                <h2 class="bb-display text-3xl bb-gold-text whitespace-nowrap">
                                                    {{ __($business_card_details->contact_form_title) }}
                                                </h2>
                                            </div>

                                            @if (Session::has('message'))
                                                <div class="px-6 py-4 bb-card border-t-4 rounded-lg mb-6"
                                                    style="border-top-color: var(--bb-gold);">
                                                    <div class="flex items-start">
                                                        <div class="mr-4">
                                                            <svg class="w-6 h-6 bb-gold-text"
                                                                xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                                                fill="currentColor">
                                                                <path
                                                                    d="M2.93 17.07A10 10 0 1 1 17.07 2.93 10 10 0 0 1 2.93 17.07zm12.73-1.41A8 8 0 1 0 4.34 4.34a8 8 0 0 0 11.32 11.32zM9 11V9h2v6H9v-4zm0-6h2v2H9V5z" />
                                                            </svg>
                                                        </div>
                                                        <div>
                                                            <p class="font-semibold text-[#EDE4D3]">
                                                                {{ Session::get('message') }}</p>
                                                            <p class="text-sm bb-text-muted">
                                                                {{ __('Please wait for the reply to be sent.') }}</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif

                                            <form id="contactForm" class="w-full mx-auto"
                                                action="{{ config('app.url') }}/sent-enquiry" method="POST">
                                                @csrf
                                                <input type="hidden" name="card_id"
                                                    value="{{ $business_card_details->card_id }}" />

                                                <div class="flex flex-col mb-4">
                                                    <label for="name"
                                                        class="text-[#EDE4D3] font-semibold mb-2">{{ __('Name') }}</label>
                                                    <input type="text" name="name"
                                                        placeholder="{{ __('Your Name') }}"
                                                        class="bb-input w-full px-4 py-3 rounded-lg focus:outline-none"
                                                        required />
                                                </div>

                                                <div class="flex flex-col mb-4">
                                                    <label for="email"
                                                        class="text-[#EDE4D3] font-semibold mb-2">{{ __('Email') }}</label>
                                                    <input type="email" name="email"
                                                        placeholder="{{ __('Your Email') }}"
                                                        class="bb-input w-full px-4 py-3 rounded-lg focus:outline-none"
                                                        required />
                                                </div>

                                                <div class="flex flex-col mb-4">
                                                    <label for="phone"
                                                        class="text-[#EDE4D3] font-semibold mb-2">{{ __('Mobile Number') }}</label>
                                                    <input type="tel" name="phone"
                                                        placeholder="{{ __('Your Mobile Number') }}"
                                                        class="bb-input w-full px-4 py-3 rounded-lg focus:outline-none"
                                                        required />
                                                </div>

                                                <div class="flex flex-col mb-6">
                                                    <label for="message"
                                                        class="text-[#EDE4D3] font-semibold mb-2">{{ __('Message') }}</label>
                                                    <textarea name="message" placeholder="{{ __('Your Message') }}"
                                                        class="bb-input w-full px-4 py-3 rounded-lg focus:outline-none" required></textarea>
                                                </div>

                                                @include('templates.cards.includes.recaptcha', [
                                                    'recaptchaId' => 'recaptcha-one',
                                                ])

                                                <div class="flex flex-col mb-6">
                                                    <button type="submit" id="btn-submit-contact"
                                                        class="bb-btn-primary w-full px-4 py-4 rounded-xl text-lg focus:outline-none">
                                                        {{ __('Send') }}
                                                    </button>
                                                </div>
                                            </form>
                                        </section>
                                    @endif
                                @endif
                                <!-- End Contact Form Section -->

                                <!-- Branding Section -->
                                @if ($plan_details['hide_branding'] == 1)
                                    <div class="pb-1">
                                        <div
                                            class="flex pt-5 px-3 m-auto font-semibold text-sm flex-col md:flex-row max-w-6xl">
                                            <div class="mt-2 bb-text-muted">
                                                {{ __('Copyright') }} &copy;
                                                <a class="bb-gold-text" href="{{ url()->current() }}">
                                                    {{ $card_details->title }}</a>
                                                <span id="year"></span>{{ __('. All Rights Reserved.') }}
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <div class="pb-1">
                                        <div
                                            class="flex px-3 m-auto pt-5 font-semibold text-sm flex-col md:flex-row max-w-6xl">
                                            <div class="mt-2 bb-text-muted">
                                                {{ __('Made with') }}
                                                <a class="bb-gold-text" href="{{ env('APP_URL') }}">
                                                    {{ config('app.name') }} </a>
                                                <span id="year"></span>{{ __('. All Rights Reserved.') }}
                                            </div>
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
                    class="bb-float-bar fixed w-11/12 left-1/2 bottom-4 rounded-full py-4 px-3 flex lg:hidden md:hidden transform -translate-x-1/2 z-50">
                    <div class="flex-1 flex items-center justify-center">
                        <a class="bb-icon-btn p-3 rounded-full" href="#profile">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round"
                                class="icon icon-tabler icons-tabler-outline icon-tabler-user h-6 w-6">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                <path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0" />
                                <path d="M6 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" />
                            </svg>
                        </a>
                    </div>

                    <div class="flex-1 flex items-center justify-center">
                        <button class="bb-icon-btn p-3 rounded-full" onclick="toggleWhatsAppModal(true)">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round"
                                class="icon icon-tabler icons-tabler-outline icon-tabler-send h-6 w-6">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                <path d="M10 14l11 -11" />
                                <path d="M21 3l-6.5 18a.55 .55 0 0 1 -1 0l-3.5 -7l-7 -3.5a.55 .55 0 0 1 0 -1l18 -6.5" />
                            </svg>
                        </button>
                    </div>

                    <div class="flex-1 flex items-center justify-center">
                        <a href="{{ config('app.url') }}/download/{{ $business_card_details->card_id }}"
                            class="bb-icon-btn p-3 rounded-full">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round"
                                class="icon icon-tabler icons-tabler-outline icon-tabler-download h-6 w-6">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                <path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2 -2v-2" />
                                <path d="M7 11l5 5l5 -5" />
                                <path d="M12 4l0 12" />
                            </svg>
                        </a>
                    </div>

                    <div class="flex-1 flex items-center justify-center">
                        <button class="bb-icon-btn p-3 rounded-full" onclick="toggleScanModal(true)">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round"
                                class="icon icon-tabler icons-tabler-outline icon-tabler-line-scan h-6 w-6">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                <path d="M4 8v-2a2 2 0 0 1 2 -2h2" />
                                <path d="M4 16v2a2 2 0 0 0 2 2h2" />
                                <path d="M16 4h2a2 2 0 0 1 2 2v2" />
                                <path d="M16 20h2a2 2 0 0 0 2 -2v-2" />
                                <path d="M7 12h10" />
                            </svg>
                        </button>
                    </div>

                    <div class="flex-1 flex items-center justify-center">
                        <button class="bb-icon-btn p-3 rounded-full" onclick="shareToggleModal(true)">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round"
                                class="icon icon-tabler icons-tabler-outline icon-tabler-share h-6 w-6">
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

            {{-- Start Check password protected Modal --}}
            @if ($business_card_details->password != null && Session::get('password_protected') == false)
                <div class="p-4 flex items-center justify-center">
                    <div x-data="{ showModal: true }">
                        <div x-show="showModal"
                            class="fixed inset-0 flex items-center justify-center z-50 p-3 bb-modal-overlay">
                            <div class="bb-panel rounded-lg p-6 w-96 max-w-full transform transition-all duration-300"
                                x-show.transition.opacity="showModal">
                                <div class="flex justify-between items-center border-b-2 pb-4"
                                    style="border-color: var(--bb-line);">
                                    <h2 class="bb-display text-2xl bb-gold-text">{{ __('Password Protected') }}</h2>
                                </div>

                                <div class="mt-6 space-y-4">
                                    <form id="passwordProtectionForm"
                                        action="{{ config('app.url') }}/check-password/{{ $business_card_details->card_id }}"
                                        method="post">
                                        @csrf
                                        <p class="text-lg bb-text-muted">{{ __('Enter your vcard Password') }}</p>
                                        <div class="flex">
                                            <input type="password" name="password"
                                                class="bb-input rounded-lg block flex-1 min-w-0 w-full text-sm p-2.5"
                                                placeholder="{{ __('Password') }}" required>
                                        </div>

                                        @if (Session::has('message'))
                                            <div class="flex items-center p-4 my-4 text-sm rounded-lg"
                                                style="background: rgba(183,40,46,0.15); color: #f3b0b3;"
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
                                            <button type="submit" id="btn-submit-password"
                                                class="bb-btn-primary px-4 py-2 mt-2 rounded-lg transition duration-300">{{ __('Password') }}</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                {{-- Password Protected Enable --}}
                @if ($business_card_details->password == null || Session::get('password_protected') == true)

                    <div id="root-level-modals" style="display:none;">
                        <!-- Include PWA modal -->
                        @if ($plan_details != null)
                            @if ($plan_details['pwa'] == 1 && $business_card_details->is_enable_pwa == 1)
                                @include('vendor.laravelpwa.barber_pwa_modal', [
                                    'primary_color' => 'pink',
                                    'img' => $business_card_details->profile,
                                ])
                            @endif
                        @endif

                        {{-- Include Newsletter Modal --}}
                        @if ($business_card_details != null)
                            @if (!empty($business_card_details->is_newsletter_pop_active) && $business_card_details->is_newsletter_pop_active == 1)
                                @include('templates.cards.barber-vcard.includes.newsletter_modal', [
                                    'primary_color' => 'pink',
                                ])
                            @endif
                        @endif

                        {{-- Include Information Popup Modal --}}
                        @if ($business_card_details != null)
                            @if (!empty($business_card_details->is_info_pop_active) && $business_card_details->is_info_pop_active == 1)
                                @include('templates.cards.barber-vcard.includes.information_popup_modal', [
                                    'primary_color' => 'pink',
                                ])
                            @endif
                        @endif
                    </div>
                @endif
            @endif
            {{-- End Check password protected Modal --}}
        </div>
    </div>

    <!-- Start Appointment Modal -->
    <div id="appointmentModal" class="fixed inset-0 bb-modal-overlay flex items-center justify-center z-50 hidden"
        onclick="toggleModal()">
        <div class="bb-panel rounded-2xl w-full max-w-md p-6 mx-4 relative space-y-4"
            onclick="event.stopPropagation()">
            <!-- Close Button -->
            <button type="button" class="modal-close-btn flex items-center justify-center" onclick="toggleModal()"
                aria-label="{{ __('Close') }}">
                <i class="fas fa-times"></i>
            </button>

            <!-- Header -->
            <div class="text-center pt-2">
                <p class="bb-eyebrow mb-1">{{ __('Reservation') }}</p>
                <h2 class="bb-display text-3xl bb-gold-text">{{ __('Book Appointment') }}</h2>
            </div>

            <!-- Appointment Form -->
            <form id="appointmentForm" class="space-y-4">
                <div>
                    <label for="name"
                        class="block text-sm font-medium text-[#EDE4D3] mb-1">{{ __('Name') }}</label>
                    <input type="text" id="name" class="bb-input p-3 rounded-lg w-full text-sm" required>
                </div>

                <div>
                    <label for="email"
                        class="block text-sm font-medium text-[#EDE4D3] mb-1">{{ __('Email') }}</label>
                    <input type="email" id="email" class="bb-input p-3 rounded-lg w-full text-sm" required>
                </div>

                <div>
                    <label for="phone"
                        class="block text-sm font-medium text-[#EDE4D3] mb-1">{{ __('Phone') }}</label>
                    <input type="tel" id="phone" class="bb-input p-3 rounded-lg w-full text-sm" required>
                </div>

                <div>
                    <label for="notes"
                        class="block text-sm font-medium text-[#EDE4D3] mb-1">{{ __('Notes') }}</label>
                    <textarea id="notes" class="bb-input p-3 rounded-lg w-full text-sm" rows="3"></textarea>
                </div>

                <div class="hidden">
                    <input type="text" id="price" class="bb-input p-3 rounded-lg w-full" disabled>
                </div>

                @include('templates.cards.includes.recaptcha', ['recaptchaId' => 'recaptcha-two'])

                <div class="flex gap-3 pt-2">
                    <button type="button" class="bb-btn-outline py-3 mr-2 rounded-xl w-1/2 font-semibold text-sm"
                        onclick="toggleModal()">
                        {{ __('Cancel') }}
                    </button>
                    <button type="submit" id="bookAppointmentButton"
                        class="bb-btn-primary py-3 rounded-xl w-1/2 text-base">
                        {{ __('Submit') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
    {{-- End Appointment Modal --}}

    <!-- Start Share Modal -->
    <div id="shareModal" class="fixed inset-0 bb-modal-overlay flex items-center justify-center hidden z-50"
        onclick="shareToggleModal(false)">
        <div class="bb-panel rounded-2xl w-full max-w-md p-6 mx-4 relative space-y-5"
            onclick="event.stopPropagation()">
            <!-- Close Button -->
            <button type="button" class="modal-close-btn flex items-center justify-center"
                onclick="shareToggleModal(false)" aria-label="{{ __('Close') }}">
                <i class="fas fa-times"></i>
            </button>

            <!-- Header -->
            <div class="text-center pt-2">
                <p class="bb-eyebrow mb-1">{{ __('Spread The Word') }}</p>
                <h2 class="bb-display text-3xl bb-gold-text">{{ __('Share vCard') }}</h2>
            </div>

            <!-- Social Media Icons Grid -->
            <div class="grid grid-cols-5 gap-3 text-center py-2">
                <a href="{{ $shareComponent['facebook'] }}" target="_blank"
                    class="bb-icon-btn p-3 rounded-xl flex items-center justify-center hover:scale-105 transition-transform">
                    <i class="fab fa-facebook-f text-xl"></i>
                </a>
                <a href="{{ $shareComponent['twitter'] }}" target="_blank"
                    class="bb-icon-btn p-3 rounded-xl flex items-center justify-center hover:scale-105 transition-transform">
                    <i class="fab fa-twitter text-xl"></i>
                </a>
                <a href="{{ $shareComponent['linkedin'] }}" target="_blank"
                    class="bb-icon-btn p-3 rounded-xl flex items-center justify-center hover:scale-105 transition-transform">
                    <i class="fab fa-linkedin-in text-xl"></i>
                </a>
                <a href="{{ $shareComponent['whatsapp'] }}" target="_blank"
                    class="bb-icon-btn p-3 rounded-xl flex items-center justify-center hover:scale-105 transition-transform">
                    <i class="fab fa-whatsapp text-xl"></i>
                </a>
                <a href="{{ $shareComponent['telegram'] }}" target="_blank"
                    class="bb-icon-btn p-3 rounded-xl flex items-center justify-center hover:scale-105 transition-transform">
                    <i class="fab fa-telegram-plane text-xl"></i>
                </a>
            </div>

            <!-- QR Preview -->
            <div class="flex flex-col items-center justify-center p-3 rounded-xl"
                style="background: var(--bb-char-2); border: 1px solid var(--bb-line);">
                <canvas id="shareQrCode" class="rounded-lg"></canvas>
            </div>

            <!-- Copy Link Button + Notification -->
            <div class="space-y-2">
                <div id="shareCopySuccess"
                    class="text-green-400 text-xs font-semibold text-center hidden py-1.5 rounded bg-green-950/50 border border-green-800/60">
                    <i class="fas fa-check-circle mr-1"></i> {{ __('Link copied to clipboard!') }}
                </div>
                <button onclick="copyLink()" id="copyLinkBtn"
                    class="bb-btn-primary py-3 px-4 rounded-xl w-full flex items-center justify-center text-base">
                    <i class="fas fa-copy mr-2"></i> {{ __('Copy Link') }}
                </button>
            </div>
        </div>
    </div>
    <!-- End Share Modal -->

    <!-- WhatsApp Modal -->
    <div id="whatsappModal" class="fixed inset-0 bb-modal-overlay flex items-center justify-center hidden z-50"
        onclick="toggleWhatsAppModal(false)">
        <div class="bb-panel rounded-2xl w-full max-w-md p-6 mx-4 relative space-y-5"
            onclick="event.stopPropagation()">
            <!-- Close Button -->
            <button type="button" class="modal-close-btn flex items-center justify-center"
                onclick="toggleWhatsAppModal(false)" aria-label="{{ __('Close') }}">
                <i class="fas fa-times"></i>
            </button>

            <!-- Header -->
            <div class="text-center pt-2">
                <div class="w-12 h-12 rounded-full mx-auto mb-3 flex items-center justify-center text-2xl text-green-400"
                    style="background: rgba(34, 197, 94, 0.15); border: 1px solid rgba(34, 197, 94, 0.3);">
                    <i class="fab fa-whatsapp"></i>
                </div>
                <h2 class="bb-display text-3xl bb-gold-text">{{ __('Send via WhatsApp') }}</h2>
                <p class="text-xs bb-text-muted mt-1">{{ __('Share this vCard directly to a WhatsApp contact') }}</p>
            </div>

            <!-- Inline Error Notice (Replaces Alert) -->
            <div id="whatsappError"
                class="hidden p-3 rounded-lg text-xs font-semibold text-red-300 bg-red-950/60 border border-red-800/60 flex items-center">
                <i class="fas fa-exclamation-circle text-red-400 mr-2 text-sm flex-shrink-0"></i>
                <span id="whatsappErrorText">{{ __('Please enter a valid WhatsApp number.') }}</span>
            </div>

            <!-- Input for WhatsApp number -->
            <div>
                <label for="whatsappNumber"
                    class="block text-[#EDE4D3] text-sm font-medium mb-2">{{ __('Mobile Number with Country Code') }}</label>
                <input type="tel" id="whatsappNumber"
                    placeholder="{{ __('e.g. +14155552671 or 919876543210') }}"
                    class="bb-input w-full px-4 py-3 rounded-lg focus:outline-none text-sm" />
            </div>

            <!-- Submit Button -->
            <button type="button" id="btn-send-whatsapp" onclick="sendMessage()"
                class="bb-btn-primary py-3 px-4 rounded-xl w-full flex items-center justify-center text-base">
                <i class="fab fa-whatsapp mr-2 text-lg"></i> {{ __('Send Message') }}
            </button>
        </div>
    </div>
    <!-- End WhatsApp Modal -->

    <!-- Scan Modal -->
    <div id="scanModal" class="fixed inset-0 bb-modal-overlay flex items-center justify-center hidden z-50"
        onclick="toggleScanModal(false)">
        <div class="bb-panel rounded-2xl w-full max-w-md p-6 mx-4 relative space-y-5 text-center"
            onclick="event.stopPropagation()">
            <!-- Close Button -->
            <button type="button" class="modal-close-btn flex items-center justify-center"
                onclick="toggleScanModal(false)" aria-label="{{ __('Close') }}">
                <i class="fas fa-times"></i>
            </button>

            <!-- Header -->
            <div class="pt-2">
                <p class="bb-eyebrow mb-1">{{ __('Scan & Save') }}</p>
                <h2 class="bb-display text-3xl bb-gold-text">{{ __('QR Code') }}</h2>
                <p class="text-xs bb-text-muted mt-1">{{ __('Scan with your phone camera to save contact') }}</p>
            </div>

            <!-- QR Code Container -->
            <div class="flex justify-center p-4 rounded-2xl mx-auto w-fit"
                style="background: var(--bb-char-2); border: 1px solid var(--bb-gold);">
                <div class="qr-code"></div>
            </div>

            <!-- Download Button -->
            <div class="pt-1">
                <button id="download"
                    onclick="downloadQr('{{ config('app.url') . route('dynamic.card', $business_card_details->card_id, false) }}', 500)"
                    class="bb-btn-primary py-3 px-6 rounded-xl w-full flex items-center justify-center text-base">
                    <i class="fas fa-download mr-2"></i> {{ __('Download QR Code') }}
                </button>
            </div>
        </div>
    </div>
    <!-- End Scan Modal -->

    {{-- Modal helper scripts --}}
    <script>
        "use strict";
        (function() {
            const cardUrl = `{{ config('app.url') . route('dynamic.card', $business_card_details->card_id, false) }}`;

            function setModal(id, show) {
                const modal = document.getElementById(id);
                if (modal) modal.classList.toggle('hidden', !show);

                // Reset status indicators when opening or closing
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

            // Copy Link (without alert popups)
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
                    btn.innerHTML = `<i class="fas fa-check mr-2"></i> {{ __('Copied!') }}`;
                    setTimeout(() => {
                        btn.innerHTML = originalHtml;
                        if (successMsg) successMsg.classList.add('hidden');
                    }, 2500);
                }
            };

            // WhatsApp Message Send (without alert popups)
            window.sendMessage = function() {
                const input = document.getElementById('whatsappNumber');
                const errorBox = document.getElementById('whatsappError');
                const phoneNumber = input ? input.value.replace(/[^0-9]/g, '') : '';

                if (!phoneNumber || phoneNumber.length < 5) {
                    if (errorBox) errorBox.classList.remove('hidden');
                    return;
                }

                if (errorBox) errorBox.classList.add('hidden');
                window.open(`https://wa.me/${phoneNumber}?text={{ rawurlencode($shareContent) }}`, '_blank',
                    'noopener');
                if (input) input.value = '';
                window.toggleWhatsAppModal(false);
            };

            function renderQr(element, size) {
                if (!element || typeof QRious === 'undefined') return null;
                if (element.tagName === 'CANVAS') {
                    return new QRious({
                        element: element,
                        value: cardUrl,
                        size: size,
                        background: '#221d19',
                        foreground: '#c9a24b',
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
                    background: '#221d19',
                    foreground: '#c9a24b',
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
                    background: '#221d19',
                    foreground: '#c9a24b',
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

    {{-- Jquery --}}
    <script src="{{ url('js/jquery.min.js') }}"></script>
    {{-- Smooth Scroll --}}
    <script src="{{ url('js/smooth-scroll.polyfills.min.js') }}"></script>
    {{-- Other JS --}}
    <script type="text/javascript" src="{{ url('app/js/footer.js') }}"></script>
    {{-- Flatpickr JS --}}
    <script src="{{ url('js/flatpickr.min.js') }}"></script>
    {{-- Custom JS --}}
    @yield('custom-js')

    {{-- Flatpickr JS --}}
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

        // Global Button Loader Helpers
        function setBtnLoading(btn, text) {
            if (!btn) return;
            btn.disabled = true;
            btn.dataset.originalHtml = btn.innerHTML;
            btn.classList.add('opacity-75', 'cursor-not-allowed');
            btn.innerHTML = `
                <svg class="animate-spin -ml-1 mr-2 h-5 w-5 text-white inline-block align-middle" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
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
            // Datepicker setup
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

            if (document.getElementById('appointment-date')) {
                flatpickr("#appointment-date", {
                    dateFormat: "Y-m-d",
                    minDate: "today",
                    locale: "{{ $locale }}",
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

            // ✉️ 1. Contact Form Loader
            const contactForm = document.getElementById('contactForm');
            if (contactForm) {
                contactForm.addEventListener('submit', function() {
                    const btn = document.getElementById('btn-submit-contact') || contactForm.querySelector(
                        'button[type="submit"]');
                    setBtnLoading(btn, '{{ __('Sending...') }}');
                });
            }

            // 🔑 2. Password Protection Form Loader
            const passwordForm = document.getElementById('passwordProtectionForm');
            if (passwordForm) {
                passwordForm.addEventListener('submit', function() {
                    const btn = document.getElementById('btn-submit-password') || passwordForm
                        .querySelector('button[type="submit"]');
                    setBtnLoading(btn, '{{ __('Verifying...') }}');
                });
            }
        });

        // 📝 3. Service Booking Form Action
        function submitServiceBooking() {
            const btn = document.getElementById('btn-submit-service-booking') || event?.target;
            const customerName = document.getElementById('customer_name')?.value || '';
            const customerEmail = document.getElementById('customer_email')?.value || '';
            const customerPhone = document.getElementById('customer_phone')?.value || '';
            const noOfPersons = document.getElementById('no_of_persons')?.value || '';
            const customerAddress = document.getElementById('customer_address')?.value || '';
            const customerNotes = document.getElementById('customer_notes')?.value || '';
            const serviceStartDate = document.getElementById('service_start_date')?.value || '';
            const serviceStartTime = document.getElementById('service_start_time')?.value || '';
            const serviceEndDate = document.getElementById('service_end_date')?.value || '';
            const serviceEndTime = document.getElementById('service_end_time')?.value || '';
            const errorMessage1 = document.getElementById('errorMessage1');
            const successMessage1 = document.getElementById('successMessage1');

            if (errorMessage1) errorMessage1.classList.add('hidden');
            if (successMessage1) successMessage1.classList.add('hidden');

            if (customerName.length === 0 || customerEmail.length === 0 || customerPhone.length === 0 || noOfPersons
                .length === 0 ||
                customerAddress.length === 0 || serviceStartDate.length === 0 || serviceStartTime.length === 0 ||
                serviceEndDate.length === 0 || serviceEndTime.length === 0) {
                if (errorMessage1) {
                    errorMessage1.classList.remove('hidden');
                    errorMessage1.innerHTML = '{{ __('Please fill all the fields.') }}';
                }
                return;
            }

            // Disable button with spinner
            setBtnLoading(btn, '{{ __('Booking...') }}');

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
                            successMessage1.classList.remove('hidden');
                            successMessage1.innerHTML = data.message ||
                                '{{ __('Your service has been successfully booked!') }}';
                        }
                    } else {
                        if (successMessage1) successMessage1.classList.add('hidden');
                        if (errorMessage1) {
                            errorMessage1.classList.remove('hidden');
                            errorMessage1.innerHTML = data.message || '{{ __('Something went wrong.') }}';
                        }
                    }
                })
                .catch(error => {
                    if (successMessage1) successMessage1.classList.add('hidden');
                    if (errorMessage1) {
                        errorMessage1.classList.remove('hidden');
                        errorMessage1.innerHTML = '{{ __('Something went wrong.') }}';
                    }
                })
                .finally(() => {
                    resetBtnLoading(btn);
                });
        }

        // Modal Helpers
        function toggleModal() {
            const modal = document.getElementById('appointmentModal');
            if (modal) modal.classList.toggle('hidden');
        }

        function validateAndShowModal() {
            const appointmentDate = document.getElementById('appointment-date')?.value;
            const timeSlotSelect = document.getElementById('time-slot-select')?.value;
            const errorMessage = document.getElementById('errorMessage');

            if (appointmentDate && timeSlotSelect) {
                toggleModal();
                if (errorMessage) errorMessage.classList.add('hidden');
            } else {
                if (errorMessage) errorMessage.classList.remove('hidden');
            }
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

        // ✂️ 4. Book Appointment Modal Form Action
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
                card: `{{ $business_card_details->card_id }}`
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

        // 💬 5. WhatsApp Direct Send Action
        window.sendMessage = function() {
            const btn = document.getElementById('btn-send-whatsapp') || event?.target;
            const input = document.getElementById('whatsappNumber');
            const errorBox = document.getElementById('whatsappError');
            const phoneNumber = input ? input.value.replace(/[^0-9]/g, '') : '';

            if (!phoneNumber || phoneNumber.length < 5) {
                if (errorBox) errorBox.classList.remove('hidden');
                return;
            }

            if (errorBox) errorBox.classList.add('hidden');

            setBtnLoading(btn, '{{ __('Opening...') }}');

            window.open(`https://wa.me/${phoneNumber}?text={{ rawurlencode($shareContent ?? '') }}`, '_blank',
                'noopener');

            setTimeout(() => {
                resetBtnLoading(btn);
                if (input) input.value = '';
                if (typeof toggleWhatsAppModal === 'function') toggleWhatsAppModal(false);
            }, 1200);
        };

        function generateOption(selectedDate, day) {
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

        // Carousels & Page Init
        if (document.querySelector('.mySwiper')) {
            new Swiper(".mySwiper", {
                slidesPerView: 1,
                spaceBetween: 20,
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
                    delay: 3000
                },
            });
        }

        if (document.querySelector('.testimonialSwiper')) {
            new Swiper(".testimonialSwiper", {
                slidesPerView: 1,
                spaceBetween: 20,
                pagination: {
                    el: ".testimonialSwiper .swiper-pagination",
                    clickable: true
                },
                autoplay: {
                    delay: 4000
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

    {{-- GSAP Motion --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            "use strict";

            if (typeof gsap === 'undefined') return;

            gsap.registerPlugin(ScrollTrigger);

            const heroTl = gsap.timeline({
                defaults: {
                    ease: 'power3.out'
                }
            });

            heroTl
                .from('#bb-hero', {
                    opacity: 0,
                    duration: 0.6
                })
                .from('.bb-pole-cap, .bb-pole', {
                    opacity: 0,
                    y: -20,
                    duration: 0.5,
                    stagger: 0.08
                }, '-=0.3')
                .from('#pName', {
                    opacity: 0,
                    y: 16,
                    duration: 0.6
                }, '-=0.2')
                .from('#bb-name-block p, #bb-name-block div', {
                    opacity: 0,
                    y: 10,
                    duration: 0.5,
                    stagger: 0.08
                }, '-=0.35');

            gsap.fromTo('#pName', {
                skewX: 0
            }, {
                skewX: 2,
                duration: 0.05,
                repeat: 5,
                yoyo: true,
                delay: 1.1,
                ease: 'none'
            });

            gsap.utils.toArray('.bb-scroll-reveal').forEach((el) => {
                gsap.to(el, {
                    opacity: 1,
                    y: 0,
                    duration: 0.7,
                    ease: 'power2.out',
                    scrollTrigger: {
                        trigger: el,
                        start: 'top 85%',
                        toggleActions: 'play none none reverse'
                    }
                });
            });

            gsap.utils.toArray('.bb-divider-mark').forEach((mark) => {
                gsap.from(mark, {
                    scale: 0,
                    rotate: 0,
                    duration: 0.4,
                    ease: 'back.out(3)',
                    scrollTrigger: {
                        trigger: mark,
                        start: 'top 90%',
                        toggleActions: 'play none none reverse'
                    }
                });
            });
        });
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
