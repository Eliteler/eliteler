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

    <meta name="theme-color" content="#E8F5E9" />

    <!-- Add to homescreen for Chrome on Android -->
    <meta name="application-name" content="{{ $card_details->title }}">

    <!-- Add to homescreen for Safari on iOS -->
    <meta name="apple-mobile-web-app-title" content="{{ $card_details->title }}">

    <!-- Tile for Win8 -->
    <meta name="msapplication-TileColor" content="#E8F5E9">
    <meta name="msapplication-TileImage" content="{{ url($business_card_details->profile) }}">

    {!! SEOMeta::generate() !!}
    {!! OpenGraph::generate() !!}
    {!! Twitter::generate() !!}
    {!! JsonLd::generate() !!}

    <!-- Fonts: Modern, Geometric, Friendly -->
    <link
        href="https://fonts.googleapis.com/css2?family=Outfit:wght@500;600;700;800&family=DM+Sans:wght@400;500;700&display=swap"
        rel="stylesheet" />

    {{-- Pet store CSS --}}
    <link rel="stylesheet" href="{{ url('templates/css/pet-store-vcard.css') }}">

    {{-- Intro Screen CSS --}}
    @if ($introScreen != null)
        <link rel="stylesheet" href="{{ asset('templates/css/intros/' . $introScreen->intro_code . '.min.css') }}">
    @endif

    {{-- Swiper CSS --}}
    <link rel="stylesheet" href="{{ url('css/swiper-bundle.min.css') }}">

    {{-- Fontawesome CSS --}}
    <link rel="stylesheet" href="{{ url('css/fontawesome.min.css') }}" />

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

        @if (!empty($business_card_details->theme_css))
            <style>
                {!! $business_card_details->theme_css !!}
            </style>
        @endif

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

    <style>
        body {
            direction: {{ App::isLocale('ar') || App::isLocale('ur') || App::isLocale('he') ? 'rtl' : 'ltr' }};
        }
        .relative {
            position: relative;
        }

        @media (min-width: 1024px) {
            .lg\:flex {
                display: flex !important;
            }
        }        
    </style>
</head>

<body class="min-h-screen">
    <!-- NEW: Animated Desktop Background -->
    <div class="desktop-background hidden lg:flex">
        <span><i class="fas fa-paw"></i></span>
        <span><i class="fas fa-bone"></i></span>
        <span><i class="fas fa-cat"></i></span>
        <span><i class="fas fa-dog"></i></span>
        <span><i class="fas fa-heart"></i></span>
        <span><i class="fas fa-paw"></i></span>
        <span><i class="fas fa-bone"></i></span>
        <span><i class="fas fa-fish"></i></span>
        <span><i class="fas fa-cat"></i></span>
        <span><i class="fas fa-dog"></i></span>
    </div>

    {{-- Loader --}}
    @if ($introScreen != null)
        <!-- Loader -->
        <div id="loader">
            <div class="spinner"></div>
        </div>
    @endif

    {{-- Start Check password protected --}}
    <div id="smooth-wrapper">
        <!-- MAIN VCARD -->
        <div id="smooth-content" class="vcard-container">
            @if ($business_card_details->password == null || Session::get('password_protected') == true)
                {{-- Index Screen --}}
                @if ($introScreen != null)
                    @include("templates.includes.intros.{$introScreen->intro_code}", [
                        'theme' => $business_card_details->theme_id,
                    ])
                @endif

                <div id="content-screen">
                    {{-- Check business details --}}
                    @if ($business_card_details != null)                

                        {{-- Cover --}}
                        <div class="bento-cover gsap-bento">
                            {{-- Start default cover image --}}
                            @if ($business_card_details->cover_type == 'none')
                                <img src="{{ url('img/templates/pet-store-vcard/pet-shop-default-cover.jpg') }}"
                                    alt="{{ $business_card_details->title }}" class="cover-media-item active" />
                            @endif
                            {{-- End default cover image --}}

                            {{-- Start custom cover image --}}
                            @if ($business_card_details->cover_type == 'photo')
                                <img src="{{ $business_card_details->cover ? url($business_card_details->cover) : asset('images/default-cover.png') }}"
                                    alt="{{ $business_card_details->title }}" class="cover-media-item active" />
                            @endif
                            {{-- End custom cover image --}}

                            {{-- Start vimeo cover video (auto play) --}}
                            @if ($business_card_details->cover_type == 'vimeo-ap')
                                <iframe id="cover-vimeo" class="cover-media-item active"
                                    src="https://player.vimeo.com/video/{{ $business_card_details->cover }}?autoplay=1&loop=1&autopause=0&muted=1&controls=0"
                                    allow="autoplay; fullscreen"></iframe>
                            @endif
                            {{-- End vimeo cover video (auto play) --}}

                            {{-- Start vimeo cover video --}}
                            @if ($business_card_details->cover_type == 'vimeo')
                                <iframe id="cover-vimeo" class="cover-media-item active"
                                    src="https://player.vimeo.com/video/{{ $business_card_details->cover }}?autoplay=0&loop=1&autopause=0&muted=0&controls=1"
                                    allow="autoplay; fullscreen"></iframe>
                            @endif
                            {{-- End vimeo cover video --}}

                            {{-- Start youtube cover video (auto play) --}}
                            @if ($business_card_details->cover_type == 'youtube-ap')
                                <iframe id="cover-yt" class="cover-media-item active"
                                    src="https://www.youtube.com/embed/{{ $business_card_details->cover }}?autoplay=1&mute=1&controls=0&loop=1&playlist={{ $business_card_details->cover }}"
                                    id="vid-player" frameborder="0" allow="autoplay;"></iframe>
                            @endif
                            {{-- End youtube cover video (auto play) --}}

                            {{-- Start youtube cover video --}}
                            @if ($business_card_details->cover_type == 'youtube')
                                <iframe id="cover-yt" class="cover-media-item active"
                                    src="https://www.youtube.com/embed/{{ $business_card_details->cover }}?autoplay=0&mute=1&controls=0&loop=1&playlist={{ $business_card_details->cover }}"
                                    id="vid-player" frameborder="0" allow="autoplay;"></iframe>
                            @endif
                            {{-- End youtube cover video --}}

                            {{-- Language Switcher --}}
                            @if (
                                $business_card_details->is_enable_language_switcher == 1 &&
                                    is_array(config('app.languages')) &&
                                    count(config('app.languages')) > 1)
                                @include('templates.includes.pet-store-language-switcher')
                            @endif
                        </div>

                        <!-- 2. Profile & Quick Actions (Bento 2 & 3) -->
                        <div class="bento-box profile-box gsap-bento">
                            <i class="fas fa-dog decor-icon anim-float" style="right: -15px; bottom: 20px"></i>
                            <img src="{{ url($business_card_details->profile) }}"
                                alt="{{ $business_card_details->title }}" class="profile-img" />
                            <h1 class="name">{{ $business_card_details->title }}</h1>
                            <p class="title">{{ $business_card_details->sub_title }}</p>
                            <p class="desc">
                                {!! $business_card_details->description !!}
                            </p>
                        </div>

                        {{-- 3. Start Qucik Actions --}}
                        @if ($feature_details->whereIn('type', ['tel', 'email', 'wa', 'map'])->count() > 0)
                            <div class="bento-box gsap-bento" style="padding: 16px">
                                <div class="quick-actions">
                                    @foreach ($feature_details as $feature)
                                        @if (in_array($feature->type, ['tel', 'email', 'wa', 'map']))
                                            {{-- Phone --}}
                                            @if ($feature->type == 'tel')
                                                <a href="tel:{{ $feature->content }}" class="action-btn"><i
                                                        class="fas fa-phone-alt"></i></a>
                                            @endif
                                            {{-- Email --}}
                                            @if ($feature->type == 'email')
                                                <a href="mailto:{{ $feature->content }}" class="action-btn"><i
                                                        class="fas fa-envelope"></i></a>
                                            @endif
                                            {{-- Whatsapp --}}
                                            @if ($feature->type == 'wa')
                                                <a href="https://wa.me/{{ $feature->content }}" target="_blank"
                                                    class="action-btn"><i class="fab fa-whatsapp"></i></a>
                                            @endif

                                            {{-- Location --}}
                                            @if ($feature->type == 'map')
                                                <a href="#map" class="action-btn"><i
                                                        class="fas fa-map-marker-alt"></i></a>
                                            @endif
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @endif
                        {{-- End Quick Actions --}}

                        <!-- 4. Start Features Section (Bento UI) -->
                        @if (!empty($feature_details) && count($feature_details) > 0)
                            @php
                                // List of excluded feature types (handled elsewhere like maps, videos, location maps)
                                // Note: 'address' is NOT excluded here, as requested.
                                $excludedTypes = ['tel', 'email', 'wa', 'map', 'iframe', 'youtube', 'location'];

                                // Filter the features to include only valid ones
                                $validFeatures = collect($feature_details)->filter(function ($feature) use (
                                    $excludedTypes,
                                ) {
                                    return isset($feature->type) && !in_array($feature->type, $excludedTypes);
                                });

                                // Separate features into 'icon-only' and 'content-card' groups for cleaner layout
                                $iconFeatures = $validFeatures->reject(function ($feature) {
                                    return in_array($feature->type, ['text', 'address']);
                                });

                                $contentFeatures = $validFeatures->filter(function ($feature) {
                                    return in_array($feature->type, ['text', 'address']);
                                });
                            @endphp

                            @if ($validFeatures->isNotEmpty())
                                <div class="bento-box gsap-bento">
                                    {{-- Section Header --}}
                                    <div class="bento-header">
                                        <h2 class="bento-title">{{ __($feature_details[0]->title ?? 'Information') }}</h2>
                                    </div>

                                    {{-- 1. Render Address & Text as Full-Width Content Cards --}}
                                    @if ($contentFeatures->isNotEmpty())
                                        <div class="feature-content-stack">
                                            @foreach ($contentFeatures as $feature)
                                                <div class="feature-content-card">
                                                    <div class="feature-content-header">
                                                        <i
                                                            class="{{ $feature->icon ?? 'fas fa-info-circle' }} text-primary"></i>
                                                        <span class="font-bold text-dark">{{ $feature->label }}</span>
                                                    </div>
                                                    <p class="feature-content-text">{{ $feature->content }}</p>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif

                                    {{-- 2. Render all other types as Icon-Only Grid --}}
                                    @if ($iconFeatures->isNotEmpty())
                                        {{-- Add a top margin if content cards exist above the icon grid --}}
                                        <div class="icon-grid"
                                            style="{{ $contentFeatures->isNotEmpty() ? 'margin-top: 20px;' : '' }}">
                                            @foreach ($iconFeatures as $feature)
                                                @php
                                                    // Generate the href value dynamically
                                                    $href = $feature->content ?? 'javascript:void(0);';
                                                    if ($feature->type === 'wa') {
                                                        // Just in case, though 'wa' is in excludedTypes above
                                                        $href = 'https://wa.me/' . ltrim($feature->content, '+');
                                                    } elseif ($feature->type === 'email') {
                                                        // Just in case
                                                        $href = 'mailto:' . $feature->content;
                                                    }
                                                @endphp

                                                {{-- Icon Button --}}
                                                <a href="{{ $href }}"
                                                    target="{{ $href !== 'javascript:void(0);' ? '_blank' : '_self' }}"
                                                    rel="noopener noreferrer" class="feature-icon-btn"
                                                    title="{{ $feature->label ?? 'Link' }}">
                                                    <i class="{{ $feature->icon ?? 'fas fa-link' }}"></i>
                                                </a>
                                            @endforeach
                                        </div>
                                    @endif

                                </div>
                            @endif
                        @endif
                        <!-- End Features Section -->

                        {{-- 5. Start Appointment Section --}}
                        @if ($appointmentEnabled == true && isset($plan_details['appointment']) == 1)
                            @if ($appointment_slots != null)
                                <div class="bento-box highlight-box gsap-bento" id="booking-section">

                                    <i class="far fa-calendar-check decor-icon anim-float" style="font-size:100px"></i>

                                    <div class="bento-header">
                                        <h2 class="bento-title">
                                            <i class="fas fa-calendar-plus" style="font-size:18px"></i>
                                            {{ __(json_decode($appointment_slots, true)['title']) }}
                                        </h2>
                                    </div>

                                    {{-- Error Message --}}
                                    <div id="errorMessage" class="message-box message-error hidden">
                                        {{ __('Please fill all the required fields.') }}
                                    </div>

                                    {{-- Success Message --}}
                                    <div id="successMessage" class="message-box message-success hidden">
                                        {{ __('Appointment booked successfully!') }}
                                    </div>

                                    {{-- Optional: Warning Message --}}
                                    <div id="warningMessage" class="message-box message-warning hidden">
                                        {{ __('Please check your input.') }}
                                    </div>

                                    {{-- Optional: Info Message --}}
                                    <div id="infoMessage" class="message-box message-info hidden">
                                        {{ __('Information message here.') }}
                                    </div>

                                    <div style="position:relative; z-index:1">

                                        {{-- Date --}}
                                        <input type="text" id="appointment-date" class="form-control flatpickr-input"
                                            placeholder="{{ __('Select date') }}" required>

                                        {{-- Time Slot --}}
                                        <select id="time-slot-select" class="form-control" required>
                                            <option value="">{{ __('Select time slot') }}</option>
                                        </select>

                                        {{-- Button --}}
                                        <button id="add-slot-button" class="btn-primary"
                                            onclick="validateAndShowModal()">
                                            {{ __('Book Appointment') }}
                                        </button>

                                    </div>

                                </div>
                            @endif
                        @endif
                        {{-- End Appointment Section --}}

                        {{-- 6. Start Products Section --}}
                        @if (count($product_details) > 0)
                            <div class="bento-box gsap-bento">
                                <i class="fas fa-box decor-icon anim-float"></i>
                                <div class="bento-header">
                                    <h2 class="bento-title">{{ __($product_details[0]->title) }}</h2>
                                </div>

                                {{-- Swiper Container --}}
                                <div class="swiper product-swiper">
                                    <div class="swiper-wrapper">
                                        @foreach ($product_details as $product_detail)
                                            <div class="swiper-slide">
                                                <div class="product-card">
                                                    {{-- Wrapper to allow badge to float over image --}}
                                                    <div class="product-image-wrapper">
                                                        {{-- Badge (Now positioned absolutely via CSS) --}}
                                                        @if (!empty($product_detail->badge))
                                                            <span
                                                                class="product-badge">{{ $product_detail->badge }}</span>
                                                        @endif

                                                        {{-- Product Image --}}
                                                        <img src="{{ url($product_detail->product_image) }}"
                                                            alt="{{ $product_detail->product_name }}" />
                                                    </div>

                                                    {{-- Product Name --}}
                                                    <h4>{{ $product_detail->product_name }}</h4>

                                                    {{-- Product Description --}}
                                                    <p>{{ Str::limit($product_detail->product_description, 40) }}</p>

                                                    {{-- ... rest of your card code (prices, stock, button) remains the same ... --}}

                                                    {{-- Price --}}
                                                    @if ($product_detail->sales_price != 0)
                                                        <span class="price">
                                                            {{ formatCurrencyVcard($product_detail->sales_price, $product_detail->currency) }}
                                                            @if ($product_detail->sales_price != $product_detail->regular_price)
                                                                <span class="price-old">
                                                                    {{ formatCurrencyVcard($product_detail->regular_price, $product_detail->currency) }}
                                                                </span>
                                                            @endif
                                                        </span>
                                                    @endif

                                                    {{-- Stock Status --}}
                                                    @if ($product_detail->product_status != 'null')
                                                        <span
                                                            class="stock-status {{ $product_detail->product_status == 'instock' ? 'in-stock' : 'out-stock' }}">
                                                            {{ $product_detail->product_status == 'outstock' ? __('Out of Stock') : __('In Stock') }}
                                                        </span>
                                                    @endif

                                                    {{-- Enquire Button --}}
                                                    @if ($enquiry_button != null && $whatsAppNumberExists == true)
                                                        <a href="https://wa.me/{{ $enquiry_button }}?text={{ __('Hi, I am interested in your product:') }} {{ $product_detail->product_name }}. {{ __('Please provide more details.') }}"
                                                            target="_blank"
                                                            class="btn-primary {{ $product_detail->product_status == 'outstock' ? 'btn-outline' : '' }}"
                                                            style="padding: 8px; font-size: 12px; border-radius: 8px; text-decoration: none; display: block; text-align: center;">
                                                            {{ $product_detail->product_status == 'outstock' ? __('Notify Me') : __('Enquire') }}
                                                        </a>
                                                    @else
                                                        <button class="btn-primary btn-outline"
                                                            style="padding: 8px; font-size: 12px; border-radius: 8px"
                                                            {{ $product_detail->product_status == 'outstock' ? 'disabled' : '' }}>
                                                            {{ $product_detail->product_status == 'outstock' ? __('Out of Stock') : __('View Details') }}
                                                        </button>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>

                                    {{-- Pagination --}}
                                    <div class="swiper-pagination"></div>
                                </div>
                            </div>
                        @endif
                        {{-- End Products Section --}}

                        {{-- 7. Start Services Section --}}
                        @if (count($service_details) > 0)
                            <div class="bento-box gsap-bento">
                                <i class="fas fa-concierge-bell decor-icon anim-float"></i>
                                <div class="bento-header">
                                    <h2 class="bento-title">{{ __($service_details[0]->title) }}</h2>
                                </div>

                                <div class="swiper serviceSwiper">
                                    <div class="swiper-wrapper">
                                        @foreach ($service_details as $service_detail)
                                            <div class="swiper-slide">
                                                <div class="inner-card">
                                                    {{-- Service Image as Background or Icon --}}
                                                    @if (!empty($service_detail->service_image))
                                                        <div class="service-image-wrapper">
                                                            <img src="{{ url($service_detail->service_image) }}"
                                                                alt="{{ $service_detail->service_name }}"
                                                                class="service-image" />
                                                        </div>
                                                    @else
                                                        <i class="fas fa-concierge-bell icon-main"></i>
                                                    @endif

                                                    {{-- Service Name --}}
                                                    <h4>{{ $service_detail->service_name }}</h4>

                                                    {{-- Service Description --}}
                                                    <p>{{ $service_detail->service_description }}</p>

                                                    {{-- Enquiry Button --}}
                                                    @if ($enquiry_button != null && $whatsAppNumberExists == true && $service_detail->enable_enquiry == 'Enabled')
                                                        <a href="https://wa.me/{{ $enquiry_button }}?text={{ __('Hi, I am interested in your service:') }} {{ $service_detail->service_name }}. {{ __('Please provide more details.') }}"
                                                            target="_blank" class="service-btn">
                                                            <i class="fab fa-whatsapp"></i> {{ __('Enquire') }}
                                                        </a>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                    <div class="swiper-pagination"></div>
                                </div>
                            </div>
                        @endif
                        {{-- End Services Section --}}

                        <!-- 8. Start Visuals: Gallery (Bento Grid) -->
                        @if (count($galleries_details) > 0)
                            <div class="bento-box gsap-bento">
                                <i class="fas fa-camera decor-icon anim-float"></i>
                                <div class="bento-header">
                                    {{-- Use the dynamic title from your DB --}}
                                    <h2 class="bento-title">{{ __($galleries_details[0]->title) }}</h2>
                                </div>

                                {{-- Use the new 2-column grid instead of a swiper --}}
                                <div class="grid-2">
                                    @foreach ($galleries_details as $galleries_detail)
                                        <div class="gallery-item-wrapper">
                                            {{-- Dynamic Image Source --}}
                                            <img src="{{ url($galleries_detail->gallery_image) }}" class="gallery-img"
                                                alt="{{ $galleries_detail->caption ?? 'Gallery Image' }}">

                                            {{-- Handle Caption (Overlay Style) --}}
                                            @if (!empty($galleries_detail->caption))
                                                <div class="gallery-caption">
                                                    <span class="truncate">{{ $galleries_detail->caption }}</span>
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                        <!-- End Visuals: Gallery -->

                        <!-- 9. Start Visuals: Video Slider (Bento Box) -->
                        @if ($feature_details->where('type', 'youtube')->count() > 0 || $feature_details->where('type', 'vimeo')->count() > 0)
                            <div class="bento-box gsap-bento">

                                <i class="fas fa-play-circle decor-icon anim-float"
                                    style="font-size:60px; right:20px; top:10px; animation-delay:1s;"></i>

                                <div class="bento-header">
                                    <h2 class="bento-title">{{ __('Videos') }}</h2>
                                </div>

                                <div class="swiper videoSwiper">
                                    <div class="swiper-wrapper">
                                        @foreach ($feature_details as $feature)
                                            @if ($feature->type == 'youtube' || $feature->type == 'vimeo')
                                                <div class="swiper-slide">
                                                    <div class="video-grid-item-card">
                                                        <div class="video-frame">
                                                            @if ($feature->type == 'youtube')
                                                                <iframe
                                                                    src="https://www.youtube.com/embed/{{ $feature->content }}"
                                                                    frameborder="0" allowfullscreen>
                                                                </iframe>
                                                            @elseif ($feature->type == 'vimeo')
                                                                <iframe
                                                                    src="https://player.vimeo.com/video/{{ $feature->content }}"
                                                                    frameborder="0" allowfullscreen>
                                                                </iframe>
                                                            @endif
                                                        </div>
                                                        {{-- Video Label --}}
                                                        @if (!empty($feature->label))
                                                            <span class="video-label">{{ $feature->label }}</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                    <div class="swiper-pagination"></div>
                                </div>

                            </div>
                        @endif
                        <!-- End Visuals: Video Slider -->

                        <!-- 10. Start Visuals: Testimonials (Bento Box) -->
                        @if (count($testimonials) > 0)
                            <div class="bento-box gsap-bento">
                                {{-- Decorative Icon --}}
                                <i class="fas fa-heart decor-icon anim-float"
                                    style="font-size: 70px; left: -10px; top: -10px;"></i>

                                {{-- Section Header --}}
                                <div class="bento-header">
                                    <h2 class="bento-title">{{ __('Client Love') }}</h2>
                                </div>

                                <div class="swiper testimonialSwiper">
                                    <div class="swiper-wrapper">

                                        @foreach ($testimonials as $testimonial)
                                            <div class="swiper-slide">
                                                <div class="testimonial-item-card">

                                                    <p class="testimonial-review">
                                                        "{{ $testimonial->review }}"
                                                    </p>

                                                    @if (!empty($testimonial->reviewer_image))
                                                        <div class="reviewer-image-wrapper">
                                                            <img src="{{ url($testimonial->reviewer_image) }}"
                                                                alt="{{ $testimonial->reviewer_name }}"
                                                                class="testimonial-image">
                                                        </div>
                                                    @endif

                                                    <strong class="testimonial-name">
                                                        {{ $testimonial->reviewer_name }}
                                                    </strong>

                                                    @if (!empty($testimonial->review_subtext))
                                                        <p class="testimonial-position">
                                                            {{ $testimonial->review_subtext }}
                                                        </p>
                                                    @endif

                                                </div>
                                            </div>
                                        @endforeach

                                    </div>

                                    <div class="swiper-pagination"></div>
                                </div>
                            </div>
                        @endif
                        <!-- End Visuals: Testimonials -->

                        <!-- 11. Start Visuals: Iframe Section (Bento Box - Single Column) -->
                        @if ($feature_details->where('type', 'iframe')->count() > 0)
                            <div class="bento-box gsap-bento">
                                {{-- Decorative Icon --}}
                                <i class="fas fa-desktop decor-icon anim-float"
                                    style="font-size: 60px; right: -10px; top: 10px;"></i>

                                {{-- Section Header --}}
                                <div class="bento-header">
                                    <h2 class="bento-title">{{ __('Custom Content') }}</h2>
                                </div>

                                {{-- Container for iframe(s) --}}
                                <div class="iframe-container">
                                    @foreach ($feature_details as $feature)
                                        @if ($feature->type == 'iframe')
                                            <div class="iframe-item-card">
                                                {{-- Iframe Content --}}
                                                <iframe referrerpolicy="strict-origin-when-cross-origin" width="100%"
                                                    height="200" {{-- Adjusted height for Bento layout --}} src="{{ $feature->content }}"
                                                    title="{{ $feature->label ?? 'Custom Content' }}" frameborder="0"
                                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                                    allowfullscreen>
                                                </iframe>

                                                {{-- Add Iframe title --}}
                                                @if (!empty($feature->label))
                                                    <h4 class="iframe-title">{{ $feature->label }}</h4>
                                                @endif
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @endif
                        <!-- End Visuals: Iframe Section -->

                        <!-- 12. Start Location Section (Bento Box) -->
                        @if (count($feature_details) > 0 && $feature_details->contains('type', 'map'))
                            <div class="bento-box gsap-bento" id="map">
                                {{-- Decorative Icon --}}
                                <i class="fas fa-map-pin decor-icon anim-float"
                                    style="font-size: 70px; left: -10px; top: -10px;"></i>

                                {{-- Section Header --}}
                                <div class="bento-header">
                                    <h2 class="bento-title">{{ __('Location') }}</h2>
                                </div>

                                @foreach ($feature_details as $feature)
                                    @if ($feature->type == 'map')
                                        <div class="location-map-card">
                                            <iframe src="https://www.google.com/maps/embed?{!! $feature->content !!}"
                                                width="100%" height="180" {{-- Adjusted height for Bento layout --}} style="border:0;"
                                                allowfullscreen="" loading="lazy">
                                            </iframe>
                                            {{-- Address below map --}}
                                            <p class="location-address">
                                                {{ $feature->label ?? '123 Happy Tails Lane, Petville, USA' }}</p>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        @endif
                        <!-- End Location Section -->

                        <!-- 13 Start Business Hours Section (Bento Box) -->
                        @if ($plan_details['business_hours'] == 1)
                            @if ($business_hours != null && $business_hours->is_display != 0)
                                <div class="bento-box gsap-bento">
                                    {{-- Decorative Icon --}}
                                    <i class="far fa-clock decor-icon anim-float"
                                        style="font-size: 70px; right: -10px; bottom: -10px;"></i>

                                    {{-- Section Header --}}
                                    <div class="bento-header">
                                        <h2 class="bento-title">{{ __($business_hours->title) }}</h2>
                                    </div>

                                    <div class="business-hours-content">
                                        @if ($business_hours->is_always_open != 'Opening')
                                            {{-- Days and Hours List --}}
                                            @foreach (['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as $day)
                                                @if ($business_hours->$day)
                                                    <div class="hour-row">
                                                        <span class="day-name">{{ __($day) }}</span>
                                                        <span class="day-hours">{{ __($business_hours->$day) }}</span>
                                                    </div>
                                                @endif
                                            @endforeach
                                        @else
                                            {{-- Always Open --}}
                                            <div class="hour-row always-open">
                                                <span class="day-name-highlight">{{ __('Always Open') }}</span>
                                                <span
                                                    class="day-hours-subtext">{{ __('We’re available 24/7 to serve you!') }}</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        @endif
                        <!-- End Business Hours Section -->

                        <!-- 14. Start Service Booking Section (Bento Box) -->
                        @if (isset($plan_details['service_booking']) && $plan_details['service_booking'] == 1)
                            @if (isset($service_booking_details) && $service_booking_details->service_booking == 1)
                                <div class="bento-box gsap-bento" id="service-booking-section">
                                    {{-- Decorative Icon --}}
                                    <i class="fas fa-calendar-alt decor-icon anim-float"
                                        style="font-size: 70px; right: -15px; bottom: 10px;"></i>

                                    {{-- Section Header --}}
                                    <div class="bento-header">
                                        <h2 class="bento-title">{{ __($service_booking_details->title) }}</h2>
                                    </div>

                                    {{-- Service Booking Form --}}
                                    <div class="w-full max-w-full">
                                        <!-- Error Message (hidden by default) -->
                                        <div id="errorMessage1" class="form-alert-error hidden"></div>

                                        {{-- Success Message (hidden by default) --}}
                                        <div id="successMessage1" class="form-alert-success hidden"></div>

                                        <form id="serviceBookingForm"
                                            onsubmit="event.preventDefault(); submitServiceBooking();"
                                            style="position: relative; z-index: 1;">
                                            <div class="grid-2">
                                                {{-- Name --}}
                                                <div class="flex flex-col mb-4">
                                                    <label for="customer_name"
                                                        class="form-label">{{ __('Name') }}</label>
                                                    <input type="text" name="customer_name" id="customer_name"
                                                        placeholder="{{ __('Your Name') }}" class="form-control" />
                                                </div>
                                                {{-- Email --}}
                                                <div class="flex flex-col mb-4">
                                                    <label for="customer_email"
                                                        class="form-label">{{ __('Email') }}</label>
                                                    <input type="email" name="customer_email" id="customer_email"
                                                        placeholder="{{ __('Your Email') }}" class="form-control" />
                                                </div>
                                            </div>

                                            <div class="grid-2">
                                                {{-- Mobile Number --}}
                                                <div class="flex flex-col mb-4">
                                                    <label for="customer_phone"
                                                        class="form-label">{{ __('Mobile Number') }}</label>
                                                    <input type="tel" name="customer_phone" id="customer_phone"
                                                        placeholder="{{ __('Your Mobile Number') }}"
                                                        class="form-control" />
                                                </div>
                                                {{-- No. of Person(s) --}}
                                                <div class="flex flex-col mb-4">
                                                    <label for="no_of_persons"
                                                        class="form-label">{{ __('No. of Person(s)') }}</label>
                                                    <input type="number" name="no_of_persons" id="no_of_persons"
                                                        value="1" step="1"
                                                        placeholder="{{ __('No. of Person(s)') }}"
                                                        class="form-control" />
                                                </div>
                                            </div>

                                            {{-- Address --}}
                                            <div class="flex flex-col mb-4">
                                                <label for="customer_address"
                                                    class="form-label">{{ __('Address') }}</label>
                                                <textarea name="customer_address" id="customer_address" placeholder="{{ __('Your Address') }}" rows="3"
                                                    class="form-control"></textarea>
                                            </div>

                                            {{-- Notes --}}
                                            <div class="flex flex-col mb-4">
                                                <label for="customer_notes"
                                                    class="form-label">{{ __('Notes') }}</label>
                                                <textarea name="customer_notes" id="customer_notes" placeholder="{{ __('Your Message') }}" rows="3"
                                                    class="form-control"></textarea>
                                            </div>

                                            {{-- Service Start Datetime --}}
                                            <div class="flex flex-col mb-4">
                                                <label for="service_start_date"
                                                    class="form-label">{{ __('Service Start DateTime') }}</label>
                                                <div class="grid-2">
                                                    <input type="text" id="service_start_date"
                                                        name="service_start_date"
                                                        value="{{ $service_booking_details->service_booking_start_date ?? '' }}"
                                                        placeholder="{{ __('Service Start Date') }}"
                                                        class="form-control" />
                                                    <input type="time" name="service_start_time"
                                                        id="service_start_time"
                                                        value="{{ $service_booking_details->service_booking_start_time ?? '' }}"
                                                        placeholder="{{ __('Service Start Time') }}"
                                                        class="form-control timepicker" />
                                                </div>
                                            </div>

                                            {{-- Service End Datetime --}}
                                            <div class="flex flex-col mb-4">
                                                <label for="service_end_date"
                                                    class="form-label">{{ __('Service End DateTime') }}</label>
                                                <div class="grid-2">
                                                    <input type="date" id="service_end_date" name="service_end_date"
                                                        placeholder="{{ __('Service End Date') }}"
                                                        class="form-control" />
                                                    <input type="time" name="service_end_time" id="service_end_time"
                                                        placeholder="{{ __('Service End Time') }}"
                                                        class="form-control timepicker" />
                                                </div>
                                            </div>

                                            <div class="flex flex-col">
                                                <button type="submit" class="btn-primary">
                                                    {{ __('Submit Booking') }}
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            @endif
                        @endif
                        <!-- End Service Booking Section -->

                        <!-- 15. Start Contact form section (Bento Box) -->
                        @if ($plan_details['contact_form'] == 1)
                            @if ($business_card_details->enquiry_email != null)
                                <div class="bento-box gsap-bento" id="contact-section">
                                    {{-- Decorative Icon --}}
                                    <i class="fas fa-paper-plane decor-icon anim-float"
                                        style="font-size: 65px; right: -5px; top: 10px;"></i>

                                    {{-- Section Header --}}
                                    <div class="bento-header">
                                        <h2 class="bento-title">{{ __($business_card_details->contact_form_title) }}</h2>
                                    </div>

                                    {{-- Message Alert (Styled for Bento UI) --}}
                                    @if (Session::has('message'))
                                        <div class="form-alert-success mb-4">
                                            <i class="fas fa-check-circle mr-2"></i>
                                            <p class="font-semibold">{{ Session::get('message') }}</p>
                                        </div>
                                    @endif
                                    @if ($errors->any())
                                        {{-- Add validation error display if you want --}}
                                        <div class="form-alert-error mb-4">
                                            <i class="fas fa-exclamation-triangle mr-2"></i>
                                            <p class="font-semibold">{{ __('Please correct the errors below.') }}</p>
                                            <ul class="list-disc list-inside mt-2 text-sm">
                                                @foreach ($errors->all() as $error)
                                                    <li>{{ $error }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif

                                    {{-- Contact Form --}}
                                    <form action="{{ config('app.url') }}/sent-enquiry" method="POST"
                                        style="position: relative; z-index: 1;">
                                        @csrf
                                        <input type="hidden" name="card_id"
                                            value="{{ $business_card_details->card_id }}" />

                                        <div class="grid-2">
                                            <div class="flex flex-col mb-4">
                                                <label for="name" class="form-label">{{ __('Name') }}</label>
                                                <input type="text" name="name" id="name"
                                                    placeholder="{{ __('Your Name') }}" class="form-control"
                                                    value="{{ old('name') }}" required />
                                            </div>
                                            <div class="flex flex-col mb-4">
                                                <label for="email" class="form-label">{{ __('Email') }}</label>
                                                <input type="email" name="email" id="email"
                                                    placeholder="{{ __('Your Email') }}" class="form-control"
                                                    value="{{ old('email') }}" required />
                                            </div>
                                        </div>

                                        <div class="flex flex-col mb-4">
                                            <label for="phone" class="form-label">{{ __('Mobile Number') }}</label>
                                            <input type="tel" name="phone" id="phone"
                                                placeholder="{{ __('Your Mobile Number') }}" class="form-control"
                                                value="{{ old('phone') }}" required />
                                        </div>

                                        <div class="flex flex-col mb-4">
                                            <label for="message" class="form-label">{{ __('Message') }}</label>
                                            <textarea name="message" id="message" placeholder="{{ __('Your Message') }}" rows="3"
                                                class="form-control" required>{{ old('message') }}</textarea>
                                        </div>

                                        <div class="flex flex-col">
                                            <button type="submit" class="btn-primary btn-outline">
                                                {{ __('Send Message') }}
                                            </button>
                                        </div>

                                        {{-- ReCaptcha --}}
                                        @include('templates.includes.recaptcha', [
                                            'recaptchaId' => 'recaptcha-one',
                                        ])

                                    </form>
                                </div>
                            @endif
                        @endif
                        <!-- End Contact form section -->

                        <!-- 16. Start Payments Section (Bento Box) -->
                        @if (count($payment_details) > 0)
                            <div class="bento-box gsap-bento">
                                {{-- Decorative Icon --}}
                                <i class="fas fa-wallet decor-icon anim-float"
                                    style="font-size: 60px; right: -5px; bottom: 10px;"></i>

                                {{-- Section Header --}}
                                <div class="bento-header">
                                    <h2 class="bento-title">{{ __($payment_details[0]->title ?? 'Payments') }}</h2>
                                </div>

                                <div class="pay-links">
                                    @foreach ($payment_details as $payment)
                                        @php
                                            $href = 'javascript:void(0);';
                                            if ($payment->type == 'url') {
                                                $href = 'https://' . str_replace('https://', '', $payment->content);
                                            } elseif ($payment->type == 'upi') {
                                                $href =
                                                    'upi://pay?pa=' .
                                                    $payment->content .
                                                    '&pn=' .
                                                    urlencode($payment->label) .
                                                    '&am=1&cu=INR';
                                            }
                                        @endphp

                                        {{-- Handle 'text' type as a dedicated information card (like SBI) --}}
                                        @if ($payment->type == 'text')
                                            <div class="payment-info-card">
                                                <div class="payment-info-header">
                                                    @if (!empty($payment->icon))
                                                        <i class="{{ $payment->icon }} payment-icon-large"></i>
                                                    @else
                                                        <i class="fas fa-piggy-bank payment-icon-large"></i>
                                                    @endif
                                                    <span class="payment-label-large">{{ $payment->label }}</span>
                                                </div>
                                                <p class="payment-details-text">
                                                    @foreach (explode('.', $payment->content) as $sentence)
                                                        @if (trim($sentence))
                                                            <!-- Make sure the sentence is not empty -->
                                                            {{ trim($sentence) }}
                                                            <br> <!-- Break the line after each sentence -->
                                                        @endif
                                                    @endforeach
                                                </p>
                                            </div>

                                            {{-- NEW: Handle 'image' type as a distinct image card --}}
                                        @elseif ($payment->type == 'image')
                                            <a href="{{ $payment->content ? url($payment->content) : 'javascript:void(0);' }}"
                                                target="_blank" rel="noopener noreferrer" class="payment-image-card">
                                                @if (!empty($payment->content))
                                                    <img src="{{ url($payment->content) }}"
                                                        alt="{{ $payment->label ?? 'Payment Image' }}"
                                                        class="payment-card-img">
                                                @else
                                                    {{-- Fallback if image content is missing --}}
                                                    <div class="payment-card-placeholder">
                                                        <i class="fas fa-image"></i>
                                                        <span>{{ __('Image Missing') }}</span>
                                                    </div>
                                                @endif
                                                @if (!empty($payment->label))
                                                    <span class="payment-card-label">{{ $payment->label }}</span>
                                                @endif
                                            </a>

                                            {{-- Handle 'url' and 'upi' types as clickable buttons --}}
                                        @else
                                            <a href="{{ $href }}" target="_blank" rel="noopener noreferrer"
                                                class="pay-btn">
                                                @if (!empty($payment->icon))
                                                    <i class="{{ $payment->icon }}"></i>
                                                @else
                                                    <i class="fas fa-money-bill-alt"></i>
                                                @endif
                                                <span>{{ $payment->label }}</span>
                                            </a>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @endif
                        <!-- End Payments Section -->

                        <!-- 17. Start Google Wallet section (Bento Box) -->
                        @if (is_dir(base_path('plugins/GoogleWallet')))
                            @if (isset($plan_details['google_wallet']) &&
                                    $plan_details['google_wallet'] == 1 &&
                                    $business_card_details->is_google_wallet_hidden == 0)
                                <div class="bento-box gsap-bento">
                                    {{-- Decorative Icon --}}
                                    <i class="fab fa-google-wallet decor-icon anim-float"
                                        style="font-size: 70px; left: -10px; top: -10px;"></i>

                                    {{-- Section Header --}}
                                    <div class="bento-header">
                                        <h2 class="bento-title">{{ __('Google Wallet') }}</h2>
                                    </div>

                                    <div class="google-wallet-content">
                                        {{-- Pass/Ticket Description --}}
                                        @if ($google_wallet_details->wallet_description != null)
                                            <p class="google-wallet-desc">
                                                {!! $google_wallet_details->wallet_description ?? '' !!}
                                            </p>
                                        @endif

                                        {{-- Google Wallet Button --}}
                                        @if ($google_wallet_details->wallet_link != null)
                                            <div class="google-wallet-btn-wrapper">
                                                <a href="{{ $google_wallet_details->wallet_link }}" target="_blank"
                                                    rel="noopener noreferrer" class="google-wallet-link">
                                                    <img src="{{ url()->to('/') . '/img/google-wallet-btn.png' }}"
                                                        alt="Google Wallet" class="google-wallet-img">
                                                </a>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        @endif
                        <!-- End Google Wallet section -->

                        <!-- 18. Start Branding Section (Bento Box) -->
                        <div class="bento-box gsap-bento">
                            <div class="branding-content">
                                @if ($plan_details['hide_branding'] == 1)
                                    <p class="branding-text">
                                        {{ __('Copyright') }} &copy;
                                        <a class="branding-link" href="{{ url()->current() }}">
                                            {{ $card_details->title }}
                                        </a>
                                        <span id="year"></span>{{ __('. All Rights Reserved.') }}
                                    </p>
                                @else
                                    <p class="branding-text">
                                        {{ __('Made with') }}
                                        <a class="branding-link" href="{{ env('APP_URL') }}">
                                            {{ config('app.name') }}
                                        </a>
                                        <span id="year"></span>{{ __('. All Rights Reserved.') }}
                                    </p>
                                @endif
                            </div>
                        </div>
                        <!-- End Branding Section -->                        
                    @endif
                </div>

                <!-- 19. Floating Bottom Navigation -->
                <div class="bottom-nav gsap-nav">
                    {{-- 1. Home / Profile Icon (Paw) --}}
                    <button class="nav-item active" onclick="window.scrollTo(0,0)">
                        <i class="fas fa-paw"></i>
                    </button>

                    {{-- 2. WhatsApp Send Icon --}}
                    <button class="nav-item" onclick="toggleWhatsAppModal(true)">
                        {{-- Using FontAwesome 'paper-plane' as a good generic 'Send' icon for the new UI --}}
                        <i class="fas fa-paper-plane"></i>
                    </button>

                    {{-- 3. Download vCard Icon (User Plus) --}}
                    <a href="{{ config('app.url') }}/download/{{ $business_card_details->card_id }}"
                        class="nav-item">
                        <i class="fas fa-user-plus"></i>
                    </a>

                    {{-- 4. PWA Install Button (Conditional and Hidden by default) --}}
                    @if ($plan_details != null && $plan_details['pwa'] == 1 && $business_card_details->is_enable_pwa == 1)
                        <button class="nav-item" id="pwa-install-button" style="display:none;"
                            onclick="handleInstallClick()">
                            <i class="fas fa-cloud-download-alt"></i>
                        </button>
                    @endif

                    {{-- 5. Scan QR Code Icon --}}
                    <button class="nav-item" onclick="toggleScanModal(true)">
                        <i class="fas fa-qrcode"></i>
                    </button>

                    {{-- 6. Share Icon --}}
                    <button class="nav-item" onclick="shareToggleModal(true)">
                        <i class="fas fa-share-alt"></i>
                    </button>
                </div>
                {{-- End Floating Bottom Navigation --}}
            @endif

            <!-- 5.1 Start Appointment Modal (By default hidden) -->
            <div id="appointmentModal" class="modal-overlay hidden">
                <!-- Modal Content formatted as a Bento Box -->
                <div class="modal-content bento-box" style="margin: 0;" onclick="event.stopPropagation()">

                    <!-- Close Button -->
                    <i class="fas fa-times close-modal" onclick="toggleModal()"></i>

                    <!-- Modal Header -->
                    <div class="bento-header flex-col justify-center mb-6">
                        <i class="fas fa-calendar-check anim-float"
                            style="font-size: 40px; color: var(--primary); margin-bottom: 15px;"></i>
                        <h2 class="bento-title" style="font-size: 24px; justify-content: center;">
                            {{ __('Book Appointment') }}</h2>
                    </div>

                    <!-- Appointment Form -->
                    <form id="appointmentForm" style="position: relative; z-index: 1;">

                        <!-- Name Field -->
                        <div class="mb-4 text-left">
                            <input type="text" id="name" class="form-control"
                                placeholder="{{ __('Name') }}" required>
                        </div>

                        <!-- Email Field -->
                        <div class="mb-4 text-left">
                            <input type="email" id="email" class="form-control"
                                placeholder="{{ __('Email') }}" required>
                        </div>

                        <!-- Phone Field -->
                        <div class="mb-4 text-left">
                            <input type="text" id="phone" class="form-control"
                                placeholder="{{ __('Phone') }}" required>
                        </div>

                        <!-- Notes Field -->
                        <div class="mb-4 text-left">
                            <textarea id="notes" class="form-control" rows="3" placeholder="{{ __('Notes') }}"></textarea>
                        </div>

                        <!-- Hidden Price Field -->
                        <div class="mb-4 hidden text-left">
                            <input type="text" id="price" class="form-control"
                                placeholder="{{ __('Price') }}" disabled>
                        </div>

                        {{-- ReCaptcha --}}
                        @include('templates.includes.recaptcha', ['recaptchaId' => 'recaptcha-two'])

                        <!-- Submit and Close Buttons -->
                        <div class="flex justify-between gap-3 mt-6">
                            <button type="button" class="btn-primary btn-outline"
                                style="border-radius: 12px; font-size: 14px; padding: 12px;" onclick="toggleModal()">
                                {{ __('Close') }}
                            </button>
                            <button type="submit" id="bookAppointmentButton" class="btn-primary"
                                style="border-radius: 12px; font-size: 14px; padding: 12px;">
                                {{ __('Submit') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            <!-- End Appointment Modal -->

            <!-- 19.1 Start Share Modal (Bento UI) -->
            <div id="shareModal" class="modal-overlay hidden" onclick="shareToggleModal(false)">
                <!-- Modal content -->
                <div class="modal-content bento-box" style="margin:0;" onclick="event.stopPropagation()">
                    <i class="fas fa-times close-modal" onclick="shareToggleModal(false)"></i>

                    <!-- Modal header -->
                    <div class="bento-header flex-col justify-center mb-4">
                        <i class="fas fa-share-alt anim-float"
                            style="font-size: 40px; color: var(--primary); margin-bottom: 15px;"></i>
                        <h2 class="bento-title" style="font-size: 24px; justify-content: center;">
                            {{ __('Share My Card') }}
                        </h2>
                    </div>

                    <!-- QR Code Section -->
                    <div class="flex justify-center mb-6">
                        <canvas id="shareQrCode" style="width:180px; height:180px; border-radius:12px;"></canvas>
                    </div>

                    <!-- Share via Social Media -->
                    <div class="flex justify-around text-dark mb-6" style="gap: 15px;">
                        <a href="{{ $shareComponent['facebook'] }}" target="_blank" class="share-social-btn">
                            <i class="fab fa-facebook fa-1x"></i>
                        </a>
                        <a href="{{ $shareComponent['twitter'] }}" target="_blank" class="share-social-btn">
                            <i class="fab fa-twitter fa-1x"></i>
                        </a>
                        <a href="{{ $shareComponent['linkedin'] }}" target="_blank" class="share-social-btn">
                            <i class="fab fa-linkedin fa-1x"></i>
                        </a>
                        <a href="{{ $shareComponent['whatsapp'] }}" target="_blank" class="share-social-btn">
                            <i class="fab fa-whatsapp fa-1x"></i>
                        </a>
                        <a href="{{ $shareComponent['telegram'] }}" target="_blank" class="share-social-btn">
                            <i class="fab fa-telegram fa-1x"></i>
                        </a>
                    </div>

                    <!-- Copy Link Section -->
                    <div class="flex justify-center">
                        <button onclick="copyLink()" class="btn-primary" style="border-radius:12px; font-weight:600;">
                            {{ __('Copy Link') }}
                        </button>
                    </div>
                </div>
            </div>
            <!-- End Share Modal -->

            <!-- 19.2 Start WhatsApp Modal (Bento UI) -->
            <div id="whatsappModal" class="modal-overlay hidden" onclick="toggleWhatsAppModal(false)">
                <!-- Modal content -->
                <div class="modal-content bento-box" style="margin:0;" onclick="event.stopPropagation()">
                    <i class="fas fa-times close-modal" onclick="toggleWhatsAppModal(false)"></i>

                    <div class="bento-header flex-col justify-center mb-4">
                        <i class="fab fa-whatsapp anim-float"
                            style="font-size: 40px; color: var(--primary); margin-bottom: 15px;"></i>
                        <h2 class="bento-title" style="font-size: 24px; justify-content: center;">
                            {{ __('Share on WhatsApp') }}</h2>
                    </div>

                    <!-- Input for WhatsApp number -->
                    <div class="mb-4">
                        <label for="whatsappNumber" class="form-label mb-2">{{ __('Enter WhatsApp Number') }}:</label>
                        <input type="text" id="whatsappNumber" placeholder="{{ __('e.g., +919876543210') }}"
                            class="form-control" />
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-center">
                        <button onclick="sendMessage()" class="btn-primary" style="border-radius:12px; font-weight:600;">
                            {{ __('Send') }}
                        </button>
                    </div>
                </div>
            </div>
            <!-- End Whatsapp Modal -->

            <!-- 19.3 Start Scan QR Code Modal (Bento UI) -->
            <div id="scanModal" class="modal-overlay hidden" onclick="toggleScanModal(false)">
                <!-- Modal content -->
                <div class="modal-content bento-box" style="margin:0;" onclick="event.stopPropagation()">
                    <i class="fas fa-times close-modal" onclick="toggleScanModal(false)"></i>

                    <div class="bento-header flex-col justify-center mb-4">
                        <i class="fas fa-qrcode anim-float"
                            style="font-size: 40px; color: var(--primary); margin-bottom: 15px;"></i>
                        <h2 class="bento-title" style="font-size: 24px; justify-content: center;">{{ __('Scan My QR') }}
                        </h2>
                    </div>

                    <!-- Qr Code -->
                    <div class="flex justify-center flex-col items-center mb-6">
                        <div class="qr-code mb-2"></div>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-center">
                        <button id="downloadQrBtn"
                            onclick="downloadQr('{{ config('app.url') . route('dynamic.card', $business_card_details->card_id, false) }}', 500)"
                            class="btn-primary" style="border-radius:12px; font-weight:600;">
                            {{ __('Download QR') }}
                        </button>
                    </div>
                </div>
            </div>
            <!-- End Scan QR Code Modal -->

            {{-- 5.1.1 Start Check password protected --}}
            @if ($business_card_details->password != null && Session::get('password_protected') == false)
            <div class="pw-modal">            
                <!-- PASSWORD SCREEN -->
                <div id="password-screen">
                    <i class="fas fa-paw anim-float password-icon"></i>
                    <h2>
                        {{ $business_card_details->title }} {{ __('Portal Access') }}
                    </h2>
                    <p>
                        {{ __('Enter your PIN to continue') }}
                    </p>
                    <form action="{{ config('app.url') }}/check-password/{{ $business_card_details->card_id }}"
                        method="post">
                        @csrf
                        <input type="password" name="password" id="card-password" placeholder="••••••" />

                        {{-- Error --}}
                        @if (Session::has('message'))
                            <div class="error-message">
                                {{ Session::get('message') }}
                            </div>
                        @endif

                        <button type="submit" class="btn-primary">
                            {{ __('Unlock') }}
                        </button>
                    </form>
                </div>
            </div>
            @else
                {{-- 20. Include Information Popup Modal --}}
                @if ($business_card_details != null)
                    {{-- Check Information Popup --}}
                    @if (!empty($business_card_details->is_info_pop_active) && $business_card_details->is_info_pop_active == 1)
                        @include('templates.includes.vcard.pet-store.information_popup_modal', [
                            'introScreen' => $introScreen,
                        ])
                    @endif
                @endif

                {{-- 21. Include Newsletter Modal --}}
                @if ($business_card_details != null)
                    {{-- Check Newsletter --}}
                    @if (!empty($business_card_details->is_newsletter_pop_active) && $business_card_details->is_newsletter_pop_active == 1)
                        @include('templates.includes.vcard.pet-store.newsletter_modal')
                    @endif
                @endif

                {{-- 22. Check PWA --}}
                @if ($plan_details != null)
                    {{-- Check PWA --}}
                    @if ($plan_details['pwa'] == 1 && $business_card_details->is_enable_pwa == 1)
                        @include('vendor.laravelpwa.pet-store')
                    @endif
                @endif
            @endif
            {{-- End Check password protected --}}
        </div>
    </div>

    <!-- Scripts -->
    <script src="{{ url('js/jquery.min.js') }}"></script>

    {{-- Smooth Scroll --}}
    <script src="{{ url('js/smooth-scroll.polyfills.min.js') }}"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/ScrollTrigger.min.js"></script>

    {{-- Flatpickr JS --}}
    <script src="{{ url('js/flatpickr.min.js') }}"></script>

    {{-- Other JS --}}
    <script type="text/javascript" src="{{ url('app/js/footer.js') }}"></script>

    {{-- Swiper JS --}}
    <script src="{{ url('js/swiper-bundle.min.js') }}"></script>

    {{-- Custom JS --}}
    @yield('custom-js')

    {{-- Flatpickr JS --}}
    <script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/l10n/{{ app()->getLocale() }}.js"></script>
    <script>
        "use strict";

        // Assuming $appointment_slots contains data like: {"monday": [...], "tuesday": [...], ...}
        const disableSlots = {!! $appointment_slots !!}; // Outputting the time slots

        @if ($business_card_details->password == null && Session::get('password_protected') == true)
            document.getElementById('year').textContent = ' ' + new Date().getFullYear();
        @endif

        document.addEventListener('DOMContentLoaded', function() {

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
                dateFormat: "H:i", // only HH:MM
                time_24hr: true // force 24-hour, drop AM/PM
            });
        });
    </script>
    <script>
        // Show message with animation
        function showMessage(id) {
            const element = document.getElementById(id);
            if (element) {
                element.classList.remove('hidden', 'hiding');
                element.classList.add('show');

                // Auto-hide after 5 seconds
                setTimeout(() => {
                    hideMessage(id);
                }, 5000);
            }
        }

        // Hide message with animation
        function hideMessage(id) {
            const element = document.getElementById(id);
            if (element) {
                element.classList.add('hiding');

                // Remove after animation completes
                setTimeout(() => {
                    element.classList.remove('show', 'hiding');
                    element.classList.add('hidden');
                }, 300);
            }
        }

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

            // Hide all messages first
            hideMessage('errorMessage1');
            hideMessage('successMessage1');

            // Validation
            if (customerName.length === 0 || customerEmail.length === 0 || customerPhone.length === 0 ||
                noOfPersons.length === 0 || customerAddress.length === 0 || serviceStartDate.length === 0 ||
                serviceStartTime.length === 0 || serviceEndDate.length === 0 || serviceEndTime.length === 0) {

                errorMessage1.textContent = `{{ __('Please fill all the required fields.') }}`;
                showMessage('errorMessage1');
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

                    if (data.success) {
                        // Reset form
                        ['customer_name', 'customer_email', 'customer_phone', 'no_of_persons', 'customer_address',
                            'customer_notes', 'service_start_date', 'service_start_time', 'service_end_date',
                            'service_end_time'
                        ].forEach(id => {
                            const element = document.getElementById(id);
                            if (element) element.value = '';
                        });

                        successMessage1.textContent = data.message ||
                            `{{ __('Your service has been successfully booked!') }}`;
                        showMessage('successMessage1');
                    } else {
                        errorMessage1.textContent = data.message ||
                            `{{ __('Something went wrong. Please try again later.') }}`;
                        showMessage('errorMessage1');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    errorMessage1.textContent =
                        `{{ __('Network error. Please check your connection and try again.') }}`;
                    showMessage('errorMessage1');
                });
        }

        // Ensure the form's submit event is handled
        document.getElementById('serviceBookingForm').addEventListener('submit', function(event) {
            event.preventDefault(); // Prevent default submission
            submitServiceBooking(); // Call your function
        });

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
            @if (env('RECAPTCHA_ENABLE') == 'on')
                formData.g_recaptcha_response = grecaptcha.getResponse(window.recaptchaWidgets['recaptcha-one']);

                if (!formData.g_recaptcha_response) {
                    // Try second reCAPTCHA widget
                    formData.g_recaptcha_response = grecaptcha.getResponse(window.recaptchaWidgets[
                        'recaptcha-two']);
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
                        ['name', 'email', 'phone', 'notes', 'appointment-date', 'time-slot-select', 'price']
                        .forEach(id => {
                            document.getElementById(id).value = '';
                        });

                        generateOption("", "");

                        successMessage.classList.remove('hidden');
                        errorSubmitMessage.classList.add('hidden');

                        // Reset reCAPTCHA
                        @if (env('RECAPTCHA_ENABLE') == 'on')
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
    {{-- Available Time Slots --}}
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

    {{-- Pet Store JS --}}
    <script>
        "use strict";

        gsap.registerPlugin(ScrollTrigger),
            gsap.set(".gsap-bento, .gsap-nav", {
                autoAlpha: 1
            }),
            gsap.from(".bento-cover", {
                y: 20,
                opacity: 0,
                duration: 0.6,
                ease: "power2.out",
            }),
            gsap.from(".profile-box", {
                scale: 0.9,
                opacity: 0,
                duration: 0.6,
                ease: "back.out(1.2)",
                delay: 0.1,
            }),
            window.innerWidth < 769 &&
            gsap.from(".gsap-nav", {
                y: 50,
                opacity: 0,
                duration: 0.6,
                ease: "power2.out",
                delay: 0.4,
            }),
            gsap.utils.toArray(".gsap-bento").forEach((e) => {
                if (
                    !e.classList.contains("bento-cover") &&
                    !e.classList.contains("profile-box")
                ) {
                    gsap.from(e, {
                        scrollTrigger: {
                            trigger: e,
                            start: "top 90%"
                        },
                        y: 30,
                        opacity: 0,
                        duration: 0.6,
                        ease: "power2.out",
                    });
                }
            }),
            ScrollTrigger.refresh();
    </script>
    {{-- Service Cards --}}
    <script>
        "use strict";

        document.addEventListener('DOMContentLoaded', function() {
            const productSwiper = new Swiper('.product-swiper', {
                slidesPerView: 1,
                spaceBetween: 15,
                loop: true,
                autoplay: {
                    delay: 2500,
                    disableOnInteraction: false,
                },
                observer: true,
                observeParents: true,
                pagination: {
                    el: '.swiper-pagination',
                    clickable: true,
                },
            });

            const serviceSwiper = new Swiper('.serviceSwiper', {
                slidesPerView: 1,
                spaceBetween: 15,
                loop: true,
                autoplay: {
                    delay: 2500,
                    disableOnInteraction: false,
                },
                observer: true,
                observeParents: true,
                pagination: {
                    el: '.swiper-pagination',
                    clickable: true,
                },
            });

            const swiper = new Swiper('.videoSwiper', {
                slidesPerView: 1,
                spaceBetween: 15,
                loop: false,
                autoplay: false,
                observer: true,
                    observeParents: true,
                pagination: {
                    el: '.swiper-pagination',
                    clickable: true,
                },
            });

            const testimonialSwiperInstance = new Swiper(".testimonialSwiper", {
                slidesPerView: 1,
                spaceBetween: 15,
                loop: true,
                autoplay: {
                    delay: 4000,
                    disableOnInteraction: false
                }, 
                observer: true,
                    observeParents: true,
                pagination: {
                    el: ".testimonialSwiper .swiper-pagination", 
                    clickable: true
                },
            });
        });
    </script>
    <script>
        "use strict";
        // Generate QR Code and place in shareQrCode using qrious
        const qr = new QRious({
            element: document.getElementById('shareQrCode'),
            value: `{{ config('app.url') . route('dynamic.card', $business_card_details->card_id, false) }}`, // Laravel route
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
            navigator.clipboard.writeText(
                `{{ config('app.url') . route('dynamic.card', $business_card_details->card_id, false) }}`);
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
