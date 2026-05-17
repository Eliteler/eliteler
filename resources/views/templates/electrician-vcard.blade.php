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
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Favicon --}}
    @if (isset($business_card_details->seo_configurations) &&
            json_decode($business_card_details->seo_configurations)->favicon != null)
        <link rel="icon" href="{{ url(json_decode($business_card_details->seo_configurations)->favicon) }}"
            sizes="512x512" type="image/png" />
        <link rel="apple-touch-icon" href="{{ url(json_decode($business_card_details->seo_configurations)->favicon) }}">
    @else
        <link rel="icon" href="{{ url($business_card_details->profile) }}" sizes="512x512" type="image/png" />
        <link rel="apple-touch-icon" href="{{ url($business_card_details->profile) }}">
    @endif

    <meta name="theme-color" content="#e0a96d" />
    <meta name="application-name" content="{{ $card_details->title }}">
    <meta name="apple-mobile-web-app-title" content="{{ $card_details->title }}">
    <meta name="msapplication-TileColor" content="#0d0d0d">
    <meta name="msapplication-TileImage" content="{{ url($business_card_details->profile) }}">

    {!! SEOMeta::generate() !!}
    {!! OpenGraph::generate() !!}
    {!! Twitter::generate() !!}
    {!! JsonLd::generate() !!}

    {{-- Fonts: Playfair Display + Lato --}}
    <link
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,500;0,600;0,700;1,400&family=Lato:wght@300;400;700&display=swap"
        rel="stylesheet">

    {{-- Electrician Theme CSS --}}
    <link rel="stylesheet" href="{{ asset('templates/css/electrician-vcard.css') }}">

    {{-- Intro Screen CSS --}}
    @if ($introScreen != null)
        <link rel="stylesheet" href="{{ asset('templates/css/intros/' . $introScreen->intro_code . '.min.css') }}">
    @endif

    {{-- Swiper CSS --}}
    <link rel="stylesheet" href="{{ url('css/swiper-bundle.min.css') }}">
    {{-- Fontawesome CSS --}}
    <link rel="stylesheet" href="{{ url('css/fontawesome.min.css') }}" />
    {{-- AOS CSS --}}
    <link rel="stylesheet" href="{{ url('css/aos.css') }}" />
    {{-- Flatpickr CSS --}}
    <link href="{{ url('css/flatpickr.min.css') }}" rel="stylesheet">
    {{-- LightGallery CSS --}}
    <link href="{{ asset('css/lightgallery.min.css') }}" rel="stylesheet">

    {{-- QRious --}}
    <script src="{{ url('js/qrious.min.js') }}"></script>
    {{-- AOS JS --}}
    <script src="{{ url('js/aos.js') }}"></script>

    {{-- Check business details --}}
    @if ($business_card_details != null)
        @php
            $custom_css = $business_card_details->custom_css;
            $custom_js = $business_card_details->custom_js;
            if (strpos($custom_css, '<style>') === false && strpos($custom_css, '</style>') === false) {
                $custom_css = '<style>' . $custom_css . '</style>';
            }
            if (strpos($custom_js, '<script>
                ') === false && strpos($custom_js, '
            </script>') === false) {
                $custom_js = '<script>
                    " . $custom_js . "
                </script>';
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

    {{-- PWA --}}
    @if ($plan_details != null && $plan_details['pwa'] == 1 && $business_card_details->is_enable_pwa == 1)
        @laravelPWA
        <link rel="manifest" href="{{ $manifest }}">
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
    {{-- Background floating icons layer --}}
    @include('templates.includes.vcard.electrician.bg-icons')

    {{-- Loader --}}
    @if ($introScreen != null)
        <!-- Loader -->
        <div id="loader">
            <div class="spinner"></div>
        </div>
    @endif

    <div id="smooth-wrapper">
        {{-- ============================================================ MAIN VCARD ============================================================ --}}
        <div id="smooth-content" class="vcard-container">
            @if ($business_card_details->password == null || Session::get('password_protected') == true)
                {{-- Index Screen --}}
                @if ($introScreen != null)
                    @include("templates.includes.intros.{$introScreen->intro_code}", [
                        'theme' => $business_card_details->theme_id,
                    ])
                @endif
                <div id="content-screen">
                    {{-- ── COVER ── --}}
                    <div class="cover-photo">
                        @if ($business_card_details->cover_type == 'none')
                            <div class="cover-media-item"
                                style="background:url('{{ url('img/templates/electrician/banner.png') }}') center/cover no-repeat;">
                            </div>
                        @endif
                        @if ($business_card_details->cover_type == 'photo')
                            <div class="cover-media-item"
                                style="background:url('{{ $business_card_details->cover ? url($business_card_details->cover) : asset('images/default-cover.png') }}') center/cover no-repeat;">
                            </div>
                        @endif
                        @if ($business_card_details->cover_type == 'youtube-ap')
                            <iframe class="cover-media-item"
                                src="https://www.youtube.com/embed/{{ $business_card_details->cover }}?autoplay=1&mute=1&controls=0&loop=1&playlist={{ $business_card_details->cover }}"
                                allow="autoplay; encrypted-media" frameborder="0"></iframe>
                        @endif
                        @if ($business_card_details->cover_type == 'youtube')
                            <iframe class="cover-media-item"
                                src="https://www.youtube.com/embed/{{ $business_card_details->cover }}?autoplay=0&mute=1&controls=0&loop=1&playlist={{ $business_card_details->cover }}"
                                allow="autoplay; encrypted-media" frameborder="0"></iframe>
                        @endif
                        @if ($business_card_details->cover_type == 'vimeo-ap')
                            <iframe class="cover-media-item"
                                src="https://player.vimeo.com/video/{{ $business_card_details->cover }}?background=1&autoplay=1&loop=1&byline=0&title=0"
                                allow="autoplay; fullscreen" frameborder="0"></iframe>
                        @endif
                        @if ($business_card_details->cover_type == 'vimeo')
                            <iframe class="cover-media-item"
                                src="https://player.vimeo.com/video/{{ $business_card_details->cover }}?autoplay=0&loop=1&controls=1"
                                allow="autoplay; fullscreen" frameborder="0"></iframe>
                        @endif
                        <div class="cover-overlay"></div>

                    </div>
                    {{-- END COVER --}}

                    {{-- Language Switcher — outside cover so iframe never intercepts it --}}
                    @if (
                        $business_card_details->is_enable_language_switcher == 1 &&
                            is_array(config('app.languages')) &&
                            count(config('app.languages')) > 1)
                        @include('templates.includes.vcard.electrician.language-switcher')
                    @endif

                    @if ($business_card_details != null)
                        {{-- ── 1. PROFILE ── --}}
                        <div class="profile-section" id="profile">
                            <img src="{{ url($business_card_details->profile) }}"
                                alt="{{ $business_card_details->title }}" class="profile-img gsap-scale" />
                            <h1 class="name gsap-slide-up">{{ $business_card_details->title }}</h1>
                            <span class="title-badge gsap-slide-up">{{ $card_details->sub_title }}</span>
                            @if (isset($business_card_details->description))
                                <div class="desc gsap-slide-up">{!! $business_card_details->description !!}</div>
                            @endif

                            {{-- ── 2. QUICK ACTIONS — inside profile section matching HTML ── --}}
                            @if (count($feature_details) > 0)
                                <div class="quick-actions gsap-fade">
                                    @foreach ($feature_details as $feature)
                                        @if (in_array($feature->type, ['tel', 'address', 'wa', 'instagram']))
                                            @if ($feature->type == 'address')
                                                <a href="#location" class="action-btn">
                                                    <span class="action-btn__icon"><i
                                                            class="{{ $feature->icon }}"></i></span>
                                                    <span class="action-btn__label">{{ __($feature->label) }}</span>
                                                </a>
                                            @elseif ($feature->type == 'tel')
                                                <a href="tel:{{ $feature->content }}" class="action-btn">
                                                    <span class="action-btn__icon"><i
                                                            class="{{ $feature->icon }}"></i></span>
                                                    <span class="action-btn__label">{{ __($feature->label) }}</span>
                                                </a>
                                            @elseif ($feature->type == 'wa')
                                                <a href="https://wa.me/{{ $feature->content }}" class="action-btn">
                                                    <span class="action-btn__icon"><i
                                                            class="{{ $feature->icon }}"></i></span>
                                                    <span class="action-btn__label">{{ __($feature->label) }}</span>
                                                </a>
                                            @elseif ($feature->type == 'instagram')
                                                <a href="{{ $feature->content }}" target="_blank"
                                                    class="action-btn">
                                                    <span class="action-btn__icon"><i
                                                            class="{{ $feature->icon }}"></i></span>
                                                    <span class="action-btn__label">{{ __($feature->label) }}</span>
                                                </a>
                                            @endif
                                        @endif
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        {{-- ── 3. GOOGLE MAPS — matches HTML position (right after profile) ── --}}
                        @if (count($feature_details) > 0 && $feature_details->contains('type', 'map'))
                            <div class="section gsap-fade" id="location">
                                <i class="fas fa-hard-hat decor-icon anim-gear"
                                    style="font-size:80px;right:10px;top:-10px"></i>
                                <div class="section-header">
                                    <h2 class="section-title">{{ __('Location') }}</h2>
                                </div>
                                @foreach ($feature_details as $feature)
                                    @if ($feature->type == 'map')
                                        <div class="map-wrapper">
                                            <iframe src="https://www.google.com/maps/embed?{!! $feature->content !!}"
                                                width="100%" height="200" style="border:0;display:block"
                                                allowfullscreen loading="lazy"></iframe>
                                            @if ($feature->label)
                                                <div class="map-label">
                                                    <h4>{{ $feature->label }}</h4>
                                                </div>
                                            @endif
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        @endif

                        {{-- ── ADDRESS CARD (if no map type) ── --}}
                        @foreach ($feature_details as $feature)
                            @if ($feature->type == 'address')
                                <div class="section gsap-fade">
                                    <a href="https://www.google.com/maps/place/{{ urlencode($feature->content) }}"
                                        target="_blank" class="address-card">
                                        <i class="{{ $feature->icon }}"></i>
                                        <div class="address-card-content">
                                            <h2>{{ $feature->label }}</h2>
                                            <p>{{ $feature->content }}</p>
                                        </div>
                                    </a>
                                </div>
                            @endif
                        @endforeach

                        {{-- ── 4. SOCIAL LINKS — matches HTML position (after maps) ── --}}
                        @php
                            $socialTypes = [
                                'facebook',
                                'twitter',
                                'linkedin',
                                'instagram',
                                'tiktok',
                                'pinterest',
                                'snapchat',
                            ];
                            $socialFeatures = collect($feature_details)->filter(
                                fn($f) => in_array($f->type, $socialTypes),
                            );
                        @endphp
                        @if ($socialFeatures->isNotEmpty())
                            <div class="section gsap-fade">
                                <i class="fas fa-plug decor-icon anim-flicker"
                                    style="font-size:70px;left:10px;top:40px"></i>
                                <div class="section-header">
                                    <h2 class="section-title">{{ __('Network') }}</h2>
                                </div>
                                <div class="grid-3">
                                    @foreach ($socialFeatures as $feature)
                                        <a href="{{ $feature->content }}" target="_blank" class="social-card">
                                            <i class="{{ $feature->icon }}"></i>
                                            <span>{{ $feature->label }}</span>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        {{-- ── 5. SERVICES — matches HTML position ── --}}
                        @if (count($service_details) > 0)
                            <div class="section gsap-fade">
                                <i class="fas fa-bolt decor-icon anim-flicker"
                                    style="font-size:90px;right:10px;top:10px"></i>
                                <div class="section-header">
                                    <h2 class="section-title">{{ __($service_details[0]->title) }}</h2>
                                </div>
                                <div class="swiper serviceSwiper">
                                    <div class="swiper-wrapper">
                                        @foreach ($service_details as $s)
                                            <div class="swiper-slide">
                                                <div class="item-card">
                                                    <img src="{{ url($s->service_image) }}"
                                                        alt="{{ $s->service_name }}" />
                                                    <h4>{{ $s->service_name }}</h4>
                                                    <p>{{ $s->service_description }}</p>
                                                    @if ($enquiry_button != null && $whatsAppNumberExists == true && $s->enable_enquiry == 'Enabled')
                                                        <a href="https://wa.me/{{ $enquiry_button }}?text={{ __('Hi, I am interested in your service:') }} {{ $s->service_name }}."
                                                            target="_blank" class="btn-primary btn-sm"
                                                            style="margin-top:10px">{{ __('Enquire') }}</a>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                    <div class="swiper-pagination"></div>
                                </div>
                            </div>
                        @endif

                        {{-- ── 6. GALLERY — matches HTML position ── --}}
                        @if (count($galleries_details) > 0)
                            <div class="section gsap-fade">
                                <i class="fas fa-tools decor-icon anim-gear"
                                    style="font-size:60px;left:15px;top:-5px"></i>
                                <div class="section-header">
                                    <h2 class="section-title">{{ __($galleries_details[0]->title) }}</h2>
                                </div>
                                <div class="grid-2">
                                    @foreach ($galleries_details as $g)
                                        <div>
                                            <img src="{{ url($g->gallery_image) }}" alt="{{ $g->caption }}"
                                                class="gallery-img" />
                                            @if ($g->caption)
                                                <p
                                                    style="font-family:'Oswald',sans-serif;font-size:11px;font-weight:600;text-transform:uppercase;color:var(--text-light);margin-top:5px;letter-spacing:0.5px">
                                                    {{ $g->caption }}</p>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        {{-- ── 7. YOUTUBE VIDEOS — matches HTML position ── --}}
                        @if ($feature_details->where('type', 'youtube')->count() > 0)
                            <div class="section gsap-fade">
                                <i class="fas fa-video decor-icon anim-flicker"
                                    style="font-size:70px;right:10px;top:10px"></i>
                                <div class="section-header">
                                    <h2 class="section-title">{{ __('Video Guides') }}</h2>
                                </div>
                                <div class="swiper videoSwiper">
                                    <div class="swiper-wrapper">
                                        @foreach ($feature_details as $feature)
                                            @if ($feature->type == 'youtube')
                                                <div class="swiper-slide">
                                                    <div class="item-card" style="padding:10px">
                                                        <iframe
                                                            src="https://www.youtube.com/embed/{{ $feature->content }}"
                                                            title="{{ $feature->label }}" frameborder="0"
                                                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                                            allowfullscreen class="youtube-iframe"></iframe>
                                                        @if ($feature->label)
                                                            <div class="youtube-label">
                                                                <p class="youtube-label-text">
                                                                    {{ $feature->label }}
                                                                </p>
                                                            </div>
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

                        {{-- ── 8. PRODUCTS — matches HTML position ── --}}
                        @if (count($product_details) > 0)
                            <div class="section gsap-fade">
                                <i class="fas fa-lightbulb decor-icon anim-flicker"
                                    style="font-size:65px;left:10px;top:-10px"></i>
                                <div class="section-header">
                                    <h2 class="section-title">{{ __($product_details[0]->title) }}</h2>
                                </div>
                                <div class="grid-1">
                                    @foreach ($product_details as $p)
                                        <div class="item-card" style="position:relative;">
                                            @if (!empty($p->badge))
                                                <span class="product-badge">{{ $p->badge }}</span>
                                            @endif
                                            <img class="prtImg" src="{{ url($p->product_image) }}" alt="{{ $p->product_name }}" />
                                            <h4>{{ $p->product_name }}</h4>
                                            <p>{{ $p->product_description }}</p>
                                            @if ($p->sales_price != 0)
                                                <span
                                                    class="product-price">{{ formatCurrencyVcard($p->sales_price, $p->currency) }}
                                                    @if ($p->sales_price != $p->regular_price)
                                                        <span
                                                            class="product-price-original">{{ formatCurrencyVcard($p->regular_price, $p->currency) }}</span>
                                                    @endif
                                                </span>
                                            @endif
                                            @if ($p->product_status != 'null')
                                                <p
                                                    style="font-size:10px;font-weight:700;letter-spacing:0.5px;text-transform:uppercase;margin-bottom:6px;color:{{ $p->product_status == 'instock' ? '#2e7d32' : '#e65100' }}">
                                                    {{ $p->product_status == 'outstock' ? __('Out of Stock') : __('In Stock') }}
                                                </p>
                                            @endif
                                            @if ($enquiry_button != null && $whatsAppNumberExists == true)
                                                <a href="https://wa.me/{{ $enquiry_button }}?text={{ __('Hi, I am interested in your product:') }} {{ $p->product_name }}."
                                                    target="_blank" class="btn-primary"
                                                    style="padding:9px;font-size:13px">{{ __('Enquire') }}</a>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        {{-- ── FEATURE LINKS ── --}}
                        @php
                            $excludedTypes = ['tel', 'instagram', 'wa', 'address', 'map', 'iframe', 'youtube'];
                            $validFeatures = collect($feature_details)->filter(
                                fn($f) => isset($f->type) && !in_array($f->type, $excludedTypes),
                            );
                        @endphp
                        @if ($validFeatures->isNotEmpty())
                            <div class="section gsap-fade">
                                <div class="section-header">
                                    <h2 class="section-title">{{ __($feature_details[0]->title) }}</h2>
                                </div>
                                <div class="grid-features">
                                    @foreach ($validFeatures as $feature)
                                        @php
                                            $href = $feature->content ?? '';
                                            if ($feature->type === 'wa') {
                                                $href = 'https://wa.me/' . $feature->content;
                                            } elseif ($feature->type === 'email') {
                                                $href = 'mailto:' . $feature->content;
                                            } elseif ($feature->type === 'text') {
                                                $href = 'javascript:void(0);';
                                            }
                                        @endphp
                                        <a href="{{ $href }}" target="_blank" class="feature-link-card">
                                            <i class="{{ $feature->icon ?? '' }}"></i>
                                            <h2>{{ $feature->label ?? '' }}</h2>
                                            <p>{{ $feature->content ?? '' }}</p>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        {{-- ── IFRAMES ── --}}
                        @if ($feature_details->where('type', 'iframe')->count() > 0)
                            <div class="section gsap-fade">
                                <div class="section-header">
                                    <h2 class="section-title">{{ __('Showcase') }}</h2>
                                </div>
                                @foreach ($feature_details as $feature)
                                    @if ($feature->type == 'iframe')
                                        <div class="iframe-wrapper">
                                            <iframe src="{{ $feature->content }}" title="{{ $feature->label }}"
                                                frameborder="0" width="100%" height="260"
                                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                                allowfullscreen></iframe>
                                            @if ($feature->label)
                                                <div class="iframe-label">{{ $feature->label }}</div>
                                            @endif
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        @endif

                        {{-- ── 9. APPOINTMENT BOOKING — matches HTML position ── --}}
                        @if (
                            $appointmentEnabled == true &&
                                isset($plan_details['appointment']) &&
                                $plan_details['appointment'] == 1 &&
                                $appointment_slots != null)
                            <div class="section gsap-fade" id="booking-section">
                                <i class="fas fa-clipboard-list decor-icon anim-gear"
                                    style="font-size:80px;right:5px;top:40px"></i>
                                <div class="section-header">
                                    <h2 class="section-title">
                                        {{ __(json_decode($appointment_slots, true)['title']) }}
                                    </h2>
                                </div>
                                <div class="tech-card" style="gap:14px">
                                    <div id="errorMessage" class="alert-error hidden">
                                        {{ __('Please select a valid date and time slot.') }}</div>
                                    <div id="successMessage" class="alert-success hidden">
                                        {{ __('Appointment booked successfully!') }}</div>
                                    <div id="errorSubmitMessage" class="alert-error hidden">
                                        {{ __('Please fill all the fields.') }}</div>
                                    <div style="display:flex;gap:12px;flex-wrap:wrap;">
                                        <input type="text" id="appointment-date"
                                            class="form-control flatpickr-input"
                                            style="flex:1;min-width:140px;margin-bottom:0"
                                            placeholder="{{ __('Select a date') }}" required />
                                        <select id="time-slot-select" required class="form-control"
                                            style="flex:1;min-width:140px;margin-bottom:0">
                                            <option value="">{{ __('Select a time slot') }}</option>
                                        </select>
                                    </div>
                                    <button id="add-slot-button" class="btn-primary"
                                        onclick="validateAndShowModal()">{{ __('Book Appointment') }}</button>
                                </div>
                            </div>
                        @endif

                        {{-- ── 10. BUSINESS HOURS — matches HTML position ── --}}
                        @if ($plan_details['business_hours'] == 1 && $business_hours != null && $business_hours->is_display != 0)
                            <div class="section gsap-fade">
                                <i class="far fa-clock decor-icon anim-flicker"
                                    style="font-size:70px;left:-10px;top:20px"></i>
                                <div class="section-header">
                                    <h2 class="section-title">{{ __($business_hours->title) }}</h2>
                                </div>
                                <div class="tech-card" style="border-left-color:var(--text-dark)">
                                    @if ($business_hours->is_always_open != 'Opening')
                                        @foreach (['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as $day)
                                            <div class="hour-row">
                                                <span>{{ __($day) }}</span>
                                                <span>{{ __($business_hours->$day ?: __('Closed')) }}</span>
                                            </div>
                                        @endforeach
                                    @else
                                        <div style="display:flex;align-items:center;gap:14px;padding:8px 0">
                                            <i class="fas fa-bolt anim-flicker"
                                                style="font-size:22px;color:var(--primary)"></i>
                                            <div>
                                                <p
                                                    style="font-family:'Oswald',sans-serif;font-size:18px;font-weight:600;text-transform:uppercase;letter-spacing:0.5px">
                                                    {{ __('Always Open') }}</p>
                                                <p style="font-size:13px;color:var(--text-light);margin-top:2px">
                                                    {{ __('24/7 Emergency Service Available!') }}</p>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif

                        {{-- ── SERVICE BOOKING ── --}}
                        @if (isset($plan_details['service_booking']) &&
                                $plan_details['service_booking'] == 1 &&
                                isset($service_booking_details) &&
                                $service_booking_details->service_booking == 1)
                            <div class="section gsap-fade">
                                <div class="section-header">
                                    <h2 class="section-title">{{ __($service_booking_details->title) }}</h2>
                                </div>
                                <div class="tech-card" style="gap:10px">
                                    <div id="errorMessage1" class="alert-error hidden"></div>
                                    <div id="successMessage1" class="alert-success hidden"></div>
                                    <div class="service-booking-form">
                                        <div><label class="form-label">{{ __('Name') }}</label><input
                                                type="text" name="customer_name" id="customer_name"
                                                placeholder="{{ __('Your Name') }}" class="form-control" />
                                        </div>
                                        <div><label class="form-label">{{ __('Email') }}</label><input
                                                type="email" name="customer_email" id="customer_email"
                                                placeholder="{{ __('Your Email') }}" class="form-control" />
                                        </div>
                                        <div><label class="form-label">{{ __('Mobile') }}</label><input
                                                type="tel" name="customer_phone" id="customer_phone"
                                                placeholder="{{ __('Mobile') }}" class="form-control" /></div>
                                        <div><label class="form-label">{{ __('Persons') }}</label><input
                                                type="number" name="no_of_persons" id="no_of_persons"
                                                value="1" class="form-control" />
                                        </div>
                                    </div>
                                    <label class="form-label">{{ __('Address') }}</label>
                                    <textarea name="customer_address" id="customer_address" placeholder="{{ __('Your Address') }}"
                                        class="form-control" rows="3"></textarea>
                                    <label class="form-label">{{ __('Notes') }}</label>
                                    <textarea name="customer_notes" id="customer_notes" placeholder="{{ __('Describe the electrical issue...') }}"
                                        class="form-control" rows="3"></textarea>
                                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                                        <div><label class="form-label">{{ __('Start Date') }}</label><input
                                                type="text" id="service_start_date" name="service_start_date"
                                                class="form-control" />
                                        </div>
                                        <div><label class="form-label">{{ __('Start Time') }}</label><input
                                                type="time" name="service_start_time" id="service_start_time"
                                                class="form-control timepicker" /></div>
                                        <div><label class="form-label">{{ __('End Date') }}</label><input
                                                type="date" id="service_end_date" name="service_end_date"
                                                class="form-control" /></div>
                                        <div><label class="form-label">{{ __('End Time') }}</label><input
                                                type="time" name="service_end_time" id="service_end_time"
                                                class="form-control timepicker" />
                                        </div>
                                    </div>
                                    <button onclick="submitServiceBooking()" class="btn-primary"
                                        style="margin-top:6px">{{ __('Submit') }}</button>
                                </div>
                            </div>
                        @endif

                        {{-- ── 11. PAYMENT LINKS — matches HTML position ── --}}
                        @if (count($payment_details) > 0)
                            <div class="section gsap-fade">
                                <i class="fas fa-credit-card decor-icon anim-gear"
                                    style="font-size:60px;right:10px;top:10px"></i>
                                <div class="section-header">
                                    <h2 class="section-title">{{ __($payment_details[0]->title) }}</h2>
                                </div>
                                <div class="pay-links">
                                    @foreach ($payment_details as $payment)
                                        @if (in_array($payment->type, ['url', 'upi']))
                                            <a href="{{ $payment->type == 'url' ? 'https://' . str_replace('https://', '', $payment->content) : 'upi://pay?pa=' . $payment->content . '&pn=' . urlencode($payment->label) . '&am=1&cu=INR' }}"
                                                target="_blank" class="pay-btn">
                                                @include('templates.partials.payment-link-image') {{ $payment->label }}
                                            </a>
                                        @else
                                            <div class="tech-card" style="width:100%">
                                                @include('templates.partials.payment-link-image')
                                                <h4>{{ $payment->label }}</h4>
                                                @if ($payment->type == 'text')
                                                    <p>
                                                        @foreach (explode('.', $payment->content) as $s)
                                                            @if (trim($s))
                                                                {{ trim($s) }}
                                                                <br>
                                                            @endif
                                                        @endforeach
                                                    </p>
                                                @endif
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        {{-- ── 12. TESTIMONIALS — matches HTML position ── --}}
                        @if (count($testimonials) > 0)
                            <div class="section gsap-fade">
                                <i class="fas fa-thumbs-up decor-icon anim-flicker"
                                    style="font-size:70px;left:0;top:-10px"></i>
                                <div class="section-header">
                                    <h2 class="section-title">{{ __($testimonials[0]->title) }}</h2>
                                </div>
                                <div class="swiper testimonialSwiper">
                                    <div class="swiper-wrapper">
                                        @foreach ($testimonials as $t)
                                            <div class="swiper-slide">
                                                <div class="testimonial-card">
                                                    <i class="fas fa-quote-left"
                                                        style="color:var(--text-dark);opacity:0.18;font-size:28px;margin-bottom:10px;display:block"></i>
                                                    <p
                                                        style="font-style:italic;font-size:14px;color:var(--text-light);line-height:1.65;margin-bottom:14px">
                                                        "{{ $t->review }}"</p>
                                                    <div style="display:flex;align-items:center;gap:12px">
                                                        <img src="{{ url($t->reviewer_image) }}"
                                                            alt="{{ $t->reviewer_name }}"
                                                            style="width:44px;height:44px;border-radius:4px;object-fit:cover;border:2px solid var(--primary)" />
                                                        <div>
                                                            <strong
                                                                style="font-family:'Oswald',sans-serif;font-size:15px;text-transform:uppercase;color:var(--text-dark);font-weight:600">{{ $t->reviewer_name }}</strong>
                                                            @if ($t->review_subtext)
                                                                <p
                                                                    style="font-size:12px;color:var(--text-light);margin-top:1px">
                                                                    {{ $t->review_subtext }}</p>
                                                            @endif
                                                            <div
                                                                style="color:var(--primary);font-size:12px;margin-top:3px">
                                                                ★★★★★
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                    <div class="swiper-pagination"></div>
                                </div>
                            </div>
                        @endif

                        {{-- ── GOOGLE WALLET ── --}}
                        @if (is_dir(base_path('plugins/GoogleWallet')) &&
                                isset($plan_details['google_wallet']) &&
                                $plan_details['google_wallet'] == 1 &&
                                $business_card_details->is_google_wallet_hidden == 0)
                            <div class="section gsap-fade">
                                <div class="section-header">
                                    <h2 class="section-title">{{ __('Google Wallet') }}</h2>
                                </div>
                                <div class="wallet-section">
                                    @if ($google_wallet_details->wallet_description != null)
                                        <div style="font-size:14px;color:var(--text-light);margin-bottom:14px">
                                            {!! $google_wallet_details->wallet_description !!}</div>
                                    @endif
                                    @if ($google_wallet_details->wallet_link != null)
                                        <a href="{{ $google_wallet_details->wallet_link }}" target="_blank"
                                            rel="noopener noreferrer"
                                            style="display:block;max-width:240px;margin:0 auto">
                                            <img src="{{ url()->to('/') . '/img/google-wallet-btn.png' }}"
                                                alt="" style="width:100%" />
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @endif

                        {{-- ── 13. CONTACT FORM — matches HTML position ── --}}
                        @if ($plan_details['contact_form'] == 1 && $business_card_details->enquiry_email != null)
                            <div class="section gsap-fade" id="contact-section">
                                <i class="fas fa-envelope-open-text decor-icon anim-gear"
                                    style="font-size:60px;right:-5px;top:10px"></i>
                                <div class="section-header">
                                    <h2 class="section-title">
                                        {{ __($business_card_details->contact_form_title) }}
                                    </h2>
                                </div>
                                @if (Session::has('message'))
                                    <div class="alert-success">{{ Session::get('message') }}</div>
                                @endif
                                <form class="tech-card" action="{{ config('app.url') }}/sent-enquiry"
                                    method="POST">
                                    @csrf
                                    <input type="hidden" name="card_id"
                                        value="{{ $business_card_details->card_id }}" />
                                    <label class="form-label">{{ __('Name') }}</label>
                                    <input type="text" name="name" placeholder="{{ __('Full Name') }}"
                                        class="form-control" required />
                                    <label class="form-label">{{ __('Email') }}</label>
                                    <input type="email" name="email" placeholder="{{ __('Email Address') }}"
                                        class="form-control" required />
                                    <label class="form-label">{{ __('Mobile Number') }}</label>
                                    <input type="tel" name="phone"
                                        placeholder="{{ __('Your Mobile Number') }}" class="form-control"
                                        required />
                                    <label class="form-label">{{ __('Message') }}</label>
                                    <textarea name="message" placeholder="{{ __('Describe the electrical issue...') }}" class="form-control"
                                        rows="4" required></textarea>
                                    @include('templates.includes.recaptcha', [
                                        'recaptchaId' => 'recaptcha-one',
                                    ])
                                    <button type="submit"
                                        class="btn-primary btn-outline">{{ __('Send Transmission') }}</button>
                                </form>
                            </div>
                        @endif

                        {{-- ── BRANDING FOOTER ── --}}
                        <div class="section branding-footer">
                            @if ($plan_details['hide_branding'] == 1)
                                {{ __('Copyright') }} &copy; <a
                                    href="{{ url()->current() }}">{{ $card_details->title }}</a>
                                <span id="year"></span>{{ __('. All Rights Reserved.') }}
                            @else
                                {{ __('Made with') }} <a href="{{ env('APP_URL') }}">{{ config('app.name') }}</a>
                                <span id="year"></span>{{ __('. All Rights Reserved.') }}
                            @endif
                        </div>
                    @endif
                </div>
                {{-- end .vcard-container --}}

                {{-- ── FLOATING BOTTOM NAV ── --}}
                <div class="bottom-nav gsap-nav">
                    <button class="nav-item active" onclick="window.scrollTo(0,0)"><i
                            class="fas fa-bolt"></i></button>
                    <button class="nav-item"
                        onclick="document.getElementById('booking-section')?.scrollIntoView({behavior:'smooth'})"><i
                            class="fas fa-clipboard-list"></i></button>
                    <a href="{{ config('app.url') }}/download/{{ $business_card_details->card_id }}"
                        class="nav-item"><i class="fas fa-address-card"></i></a>
                    <button class="nav-item" onclick="toggleScanModal(true)"><i class="fas fa-qrcode"></i></button>
                    <button class="nav-item" onclick="toggleWhatsAppModal(true)"><i
                            class="fab fa-whatsapp"></i></button>
                    <button class="nav-item" onclick="shareToggleModal(true)"><i
                            class="fas fa-share-alt"></i></button>
                </div>
            @endif

            {{-- ============================================================ MODALS ============================================================ --}}

            {{-- Appointment Modal --}}
            <div id="appointmentModal" class="std-modal hidden">
                <div class="std-modal-box" onclick="event.stopPropagation()">
                    <button class="close-btn" onclick="toggleModal()"><i class="fas fa-times"></i></button>
                    <h2>{{ __('Book Appointment') }}</h2>
                    <form id="appointmentForm">
                        <label class="form-label">{{ __('Name') }}</label>
                        <input type="text" id="name" class="form-control" required />
                        <label class="form-label">{{ __('Email') }}</label>
                        <input type="email" id="email" class="form-control" required />
                        <label class="form-label">{{ __('Phone') }}</label>
                        <input type="text" id="phone" class="form-control" required />
                        <label class="form-label">{{ __('Notes') }}</label>
                        <textarea id="notes" class="form-control" rows="3"
                            placeholder="{{ __('Describe the electrical issue...') }}"></textarea>
                        <div class="hidden"><input type="text" id="price" class="form-control" disabled />
                        </div>
                        @include('templates.includes.recaptcha', ['recaptchaId' => 'recaptcha-two'])
                        <div class="std-modal-footer">
                            <button type="button" class="btn-cancel"
                                onclick="toggleModal()">{{ __('Cancel') }}</button>
                            <button type="submit" id="bookAppointmentButton"
                                class="btn-confirm">{{ __('Confirm') }}</button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Share Modal --}}
            <div id="shareModal" class="std-modal hidden" onclick="shareToggleModal(false)">
                <div class="std-modal-box" onclick="event.stopPropagation()">
                    <button class="close-btn" onclick="shareToggleModal(false)"><i class="fas fa-times"></i></button>
                    <h2 class="share-title">{{ __('Share') }}</h2>
                    <div class="share-divider"></div>
                    <div class="share-qr">
                        <div class="share-qr-wrap"><canvas id="shareQrCode"></canvas></div>
                    </div>
                    <p class="share-section-label">{{ __('Share on') }}</p>
                    <div class="share-icons">
                        <a href="{{ $shareComponent['facebook'] }}" target="_blank"><i
                                class="fab fa-facebook-f"></i></a>
                        <a href="{{ $shareComponent['twitter'] }}" target="_blank"><i
                                class="fab fa-twitter"></i></a>
                        <a href="{{ $shareComponent['linkedin'] }}" target="_blank"><i
                                class="fab fa-linkedin-in"></i></a>
                        <a href="{{ $shareComponent['whatsapp'] }}" target="_blank"><i
                                class="fab fa-whatsapp"></i></a>
                        <a href="{{ $shareComponent['telegram'] }}" target="_blank"><i
                                class="fab fa-telegram-plane"></i></a>
                    </div>
                    <button onclick="copyLinkStyled(this)" class="share-copy-btn"><i
                            class="fas fa-link"></i><span>{{ __('Copy Link') }}</span></button>
                </div>
            </div>

            {{-- WhatsApp Modal --}}
            <div id="whatsappModal" class="std-modal hidden" onclick="toggleWhatsAppModal(false)">
                <div class="std-modal-box" onclick="event.stopPropagation()">
                    <button class="close-btn" onclick="toggleWhatsAppModal(false)"><i
                            class="fas fa-times"></i></button>
                    <div class="whatsapp-icon-wrap"><i class="fab fa-whatsapp"></i></div>
                    <h3 class="whatsapp-modal-title">{{ __('Send via WhatsApp') }}</h3>
                    <div class="whatsapp-divider"></div>
                    <label class="whatsapp-modal-label" for="whatsappNumber">{{ __('WhatsApp Number') }}</label>
                    <div class="whatsapp-input-wrap">
                        <div class="whatsapp-input-prefix"><i class="fab fa-whatsapp"></i></div>
                        <input type="text" id="whatsappNumber" placeholder="{{ __('e.g., +919876543210') }}"
                            autocomplete="tel" />
                    </div>
                    <button onclick="sendMessage()" class="whatsapp-send-btn"><i
                            class="fab fa-whatsapp"></i><span>{{ __('Send Message') }}</span></button>
                    <p class="whatsapp-helper"><i
                            class="fas fa-info-circle"></i>{{ __('Include country code. e.g., +1, +91, +44') }}</p>
                </div>
            </div>

            {{-- QR Scan Modal --}}
            <div id="scanModal" class="std-modal hidden" onclick="toggleScanModal(false)">
                <div class="std-modal-box" onclick="event.stopPropagation()">
                    <button class="close-btn" onclick="toggleScanModal(false)"><i class="fas fa-times"></i></button>
                    <h3 class="qr-modal-title">{{ __('Scan QR Code') }}</h3>
                    <div class="qr-divider"></div>
                    <div class="qr-card">
                        <div class="qr-code qr-wrapper"></div>
                        <p class="qr-hint"><i class="fas fa-qrcode"></i>{{ __('Point your camera to scan') }}</p>
                    </div>
                    <button id="download"
                        onclick="downloadQrStyled(this,'{{ config('app.url') . route('dynamic.card', $business_card_details->card_id, false) }}',500)"
                        class="qr-download-btn">
                        <i class="fas fa-download"></i><span>{{ __('Download QR') }}</span>
                    </button>
                    <p class="qr-helper"><i
                            class="fas fa-share-alt"></i>{{ __('Share this QR to let others save your contact') }}
                    </p>
                </div>
            </div>

            {{-- Password Modal --}}
            @if ($business_card_details->password != null && Session::get('password_protected') == false)
                <div class="pw-modal">
                    <div class="pw-modal-box">
                        <i class="fas fa-bolt anim-flicker"
                            style="font-size:44px;color:var(--primary);margin-bottom:18px;display:block"></i>
                        <h2>{{ __('Secure Portal') }}</h2>
                        <p>{{ __('Enter your vCard Password') }}</p>
                        <form action="{{ config('app.url') }}/check-password/{{ $business_card_details->card_id }}"
                            method="post">
                            @csrf
                            <input type="password" name="password" class="pw-input" placeholder="••••" required
                                autofocus />
                            @if (Session::has('message'))
                                <div class="alert-error" style="margin-bottom:12px;text-align:left">
                                    {{ Session::get('message') }}</div>
                            @endif
                            <button type="submit" class="btn-primary">{{ __('Verify Access') }}</button>
                        </form>
                    </div>
                </div>
            @else
                {{-- PWA Modal --}}
                @if ($plan_details != null && $plan_details['pwa'] == 1 && $business_card_details->is_enable_pwa == 1)
                    @include('vendor.laravelpwa.electrician-vcard')
                @endif

                {{-- Newsletter Modal --}}
                @if (
                    $business_card_details != null &&
                        !empty($business_card_details->is_newsletter_pop_active) &&
                        $business_card_details->is_newsletter_pop_active == 1)
                    @include('templates.includes.vcard.electrician.newsletter_modal')
                @endif

                {{-- Information Popup Modal --}}
                @if (
                    $business_card_details != null &&
                        !empty($business_card_details->is_info_pop_active) &&
                        $business_card_details->is_info_pop_active == 1)
                    @include('templates.includes.vcard.electrician.information_popup_modal', ['introScreen' => $introScreen])
                @endif
            @endif
        </div>
    </div>

    {{-- ============================================================ SCRIPTS ============================================================ --}}
    <script src="{{ url('js/jquery.min.js') }}"></script>
    <script src="{{ url('js/smooth-scroll.polyfills.min.js') }}"></script>
    <script type="text/javascript" src="{{ url('app/js/footer.js') }}"></script>
    <script src="{{ url('js/flatpickr.min.js') }}"></script>
    <script src="{{ url('js/swiper-bundle.min.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/ScrollTrigger.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/l10n/{{ app()->getLocale() }}.js"></script>
    <script src="{{ asset('js/lightgallery.min.js') }}"></script>
    @yield('custom-js')

    <script>
        "use strict";

        @if ($business_card_details->password == null && Session::get('password_protected') == true)
            document.getElementById('year').textContent = ' ' + new Date().getFullYear();
        @endif

        function changeTheme(r, g, b) {
            document.documentElement.style.setProperty('--primary-rgb', `${r}, ${g}, ${b}`);
            document.querySelector('meta[name="theme-color"]').setAttribute('content', `rgb(${r},${g},${b})`);
        }

        // Swipers
        document.addEventListener('DOMContentLoaded', function() {
            new Swiper('.serviceSwiper', {
                slidesPerView: 1,
                spaceBetween: 14,
                observer: true,
                observeParents: true,
                pagination: {
                    el: '.serviceSwiper .swiper-pagination',
                    clickable: true
                }
            });
            new Swiper('.videoSwiper', {
                slidesPerView: 1,
                spaceBetween: 14,
                observer: true,
                observeParents: true,
                pagination: {
                    el: '.videoSwiper .swiper-pagination',
                    clickable: true
                }
            });
            new Swiper('.testimonialSwiper', {
                slidesPerView: 1,
                spaceBetween: 16,
                loop: true,
                autoplay: {
                    delay: 4000
                },
                observer: true,
                observeParents: true,
                pagination: {
                    el: '.testimonialSwiper .swiper-pagination',
                    clickable: true
                }
            });
        });

        // GSAP — snappier mechanical easing matching original
        gsap.registerPlugin(ScrollTrigger);
        gsap.set('.gsap-fade, .gsap-scale, .gsap-slide-up, .gsap-nav', {
            autoAlpha: 1
        });
        gsap.from('.gsap-scale', {
            scale: 0,
            opacity: 0,
            duration: 0.6,
            ease: 'power4.out'
        });
        gsap.from('.gsap-slide-up', {
            y: 40,
            opacity: 0,
            duration: 0.6,
            stagger: 0.1,
            ease: 'power4.out',
            delay: 0.2
        });
        if (window.innerWidth < 769) gsap.from('.gsap-nav', {
            y: 100,
            opacity: 0,
            duration: 0.6,
            ease: 'power3.out',
            delay: 0.7
        });
        gsap.utils.toArray('.gsap-fade').forEach(s => {
            gsap.from(s, {
                scrollTrigger: {
                    trigger: s,
                    start: 'top 90%'
                },
                y: 30,
                opacity: 0,
                duration: 0.6,
                ease: 'power3.out'
            });
        });
        ScrollTrigger.refresh();

        // Modal helpers
        function shareToggleModal(show) {
            document.getElementById('shareModal').classList.toggle('hidden', !show);
        }

        function toggleScanModal(show) {
            document.getElementById('scanModal').classList.toggle('hidden', !show);
        }

        function toggleWhatsAppModal(show) {
            document.getElementById('whatsappModal').classList.toggle('hidden', !show);
        }

        function toggleModal() {
            document.getElementById('appointmentModal').classList.toggle('hidden');
        }

        function copyLinkStyled(btn) {
            navigator.clipboard.writeText(
                `{{ config('app.url') . route('dynamic.card', $business_card_details->card_id, false) }}`).then(
                () => {
                    const icon = btn.querySelector('i'),
                        span = btn.querySelector('span');
                    btn.classList.add('copied');
                    icon.className = 'fas fa-check';
                    span.textContent = `{{ __('Copied!') }}`;
                    setTimeout(() => {
                        btn.classList.remove('copied');
                        icon.className = 'fas fa-link';
                        span.textContent = `{{ __('Copy Link') }}`;
                    }, 2200);
                });
        }

        function copyLink() {
            navigator.clipboard.writeText(
                `{{ config('app.url') . route('dynamic.card', $business_card_details->card_id, false) }}`);
            alert("{{ __('Link copied to clipboard!') }}");
        }

        function sendMessage() {
            const phoneNumber = document.getElementById('whatsappNumber').value.trim();
            if (phoneNumber) {
                window.open(`https://wa.me/${phoneNumber}?text={{ $shareContent }}`, '_blank');
                toggleWhatsAppModal(false);
                document.getElementById('whatsappNumber').value = '';
            } else {
                alert(`{{ __('Please enter a valid WhatsApp number.') }}`);
            }
        }

        // QR — black on yellow matching electrician theme
        const qr = new QRious({
            element: document.getElementById('shareQrCode'),
            value: `{{ config('app.url') . route('dynamic.card', $business_card_details->card_id, false) }}`,
            size: 200,
            background: '#f4f5f7',
            foreground: '#111111',
            level: 'H'
        });
        window.onload = function() {
            updateQr(`{{ config('app.url') . route('dynamic.card', $business_card_details->card_id, false) }}`);
        };

        function downloadQrStyled(btn, url, size) {
            downloadQr(url, size);
            const icon = btn.querySelector('i'),
                span = btn.querySelector('span');
            btn.classList.add('downloaded');
            icon.className = 'fas fa-check';
            span.textContent = `{{ __('Saved!') }}`;
            setTimeout(() => {
                btn.classList.remove('downloaded');
                icon.className = 'fas fa-download';
                span.textContent = `{{ __('Download QR') }}`;
            }, 2200);
        }

        document.querySelectorAll('.nav-item').forEach(item => {
            item.addEventListener('click', function() {
                document.querySelectorAll('.nav-item').forEach(n => n.classList.remove('active'));
                this.classList.add('active');
            });
        });

        // Info Popup
        function openInfoModal() {
            const o = document.getElementById('customInfoOverlay');
            if (o) {
                o.classList.remove('hidden');
                void o.offsetWidth;
                o.classList.add('is-active');
                if (typeof triggerInfoConfetti === 'function') setTimeout(triggerInfoConfetti, 300);
            }
        }

        function closeInfoModal() {
            const o = document.getElementById('customInfoOverlay');
            if (o) {
                o.classList.remove('is-active');
                setTimeout(() => o.classList.add('hidden'), 300);
            }
        }
        document.addEventListener('DOMContentLoaded', () => {
            if (document.getElementById('customInfoOverlay')) setTimeout(openInfoModal, 1000);
        });

        // Newsletter
        function openNewsModal() {
            document.getElementById('newsletterModal')?.classList.add('is-active');
        }

        function closeNewsModal() {
            document.getElementById('newsletterModal')?.classList.remove('is-active');
        }
        document.addEventListener('DOMContentLoaded', () => {
            if (document.getElementById('newsletterModal')) setTimeout(openNewsModal, 1500);
        });

        // Appointment
        function validateAndShowModal() {
            const date = document.getElementById('appointment-date')?.value;
            const slot = document.getElementById('time-slot-select')?.value;
            const err = document.getElementById('errorMessage');
            if (date && slot) {
                document.getElementById('appointmentModal').classList.remove('hidden');
                err?.classList.add('hidden');
            } else {
                err?.classList.remove('hidden');
            }
        }

        function onloadCallback() {
            window.recaptchaWidgets = window.recaptchaWidgets || {};
            if (typeof grecaptcha !== 'undefined') {
                if (!window.recaptchaWidgets['recaptcha-one'] && document.getElementById('recaptcha-one'))
                    window.recaptchaWidgets['recaptcha-one'] = grecaptcha.render('recaptcha-one', {
                        'sitekey': '{{ env('RECAPTCHA_SITE_KEY') }}'
                    });
                if (!window.recaptchaWidgets['recaptcha-two'] && document.getElementById('recaptcha-two'))
                    window.recaptchaWidgets['recaptcha-two'] = grecaptcha.render('recaptcha-two', {
                        'sitekey': '{{ env('RECAPTCHA_SITE_KEY') }}'
                    });
            }
        }

        document.getElementById('appointmentForm')?.addEventListener('submit', function(e) {
            e.preventDefault();
            const btn = document.getElementById('bookAppointmentButton');
            btn.disabled = true;
            btn.textContent = `{{ __('Booking...') }}`;
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
            @if (env('RECAPTCHA_ENABLE') == 'on')
                formData.g_recaptcha_response = grecaptcha.getResponse(window.recaptchaWidgets['recaptcha-one']) ||
                    grecaptcha.getResponse(window.recaptchaWidgets['recaptcha-two']);
            @endif
            fetch("{{ config('app.url') }}/book-appointment", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(formData)
                })
                .then(async r => {
                    const data = await r.json();
                    if (r.ok) {
                        ['name', 'email', 'phone', 'notes', 'appointment-date', 'time-slot-select', 'price']
                        .forEach(id => {
                            const el = document.getElementById(id);
                            if (el) el.value = '';
                        });
                        generateOption("", "");
                        document.getElementById('successMessage')?.classList.remove('hidden');
                        document.getElementById('errorMessage')?.classList.add('hidden');
                        @if (env('RECAPTCHA_ENABLE') == 'on')
                            grecaptcha.reset(window.recaptchaWidgets[
                                'recaptcha-one']);
                        @endif
                        toggleModal();
                        if (data.success && data.whatsapp_url && data.whatsapp_url !== '#') setTimeout(
                            () => {
                                window.location.href = data.whatsapp_url;
                            }, 3000);
                    } else {
                        const errEl = document.getElementById('errorMessage');
                        if (errEl) {
                            errEl.classList.remove('hidden');
                            errEl.innerHTML = data.message || 'Something went wrong';
                        }
                        toggleModal();
                    }
                    btn.disabled = false;
                    btn.textContent = `{{ __('Confirm') }}`;
                })
                .catch(() => {
                    toggleModal();
                    btn.disabled = false;
                    btn.textContent = `{{ __('Confirm') }}`;
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
                        card: `{{ $business_card_details->card_id }}`,
                        choose_date: selectedDate,
                        day: day
                    })
                })
                .then(r => r.json()).then(data => {
                    if (data.success == true) {
                        document.getElementById('time-slot-select').innerHTML =
                            `<option value="">{{ __('Select a time slot') }}`;
                        JSON.parse(data.available_time_slots).forEach(ts => {
                            document.getElementById('time-slot-select').innerHTML +=
                                `<option value="${ts}">${ts}</option>`;
                        });
                        document.getElementById('price').value = data.price;
                    }
                });
        }

        function submitServiceBooking() {
            const fields = ['customer_name', 'customer_email', 'customer_phone', 'no_of_persons', 'customer_address',
                'service_start_date', 'service_start_time', 'service_end_date', 'service_end_time'
            ];
            const errEl = document.getElementById('errorMessage1');
            const sucEl = document.getElementById('successMessage1');
            errEl.classList.add('hidden');
            sucEl.classList.add('hidden');
            const vals = {};
            for (const f of fields) {
                vals[f] = document.getElementById(f)?.value || '';
            }
            if (Object.values(vals).some(v => v.length === 0)) {
                errEl.classList.remove('hidden');
                errEl.innerHTML = '{{ __('Please fill all the fields.') }}';
                return;
            }
            const formData = {
                card: `{{ $business_card_details->card_id }}`,
                ...vals,
                customer_notes: document.getElementById('customer_notes')?.value || ''
            };
            fetch("{{ config('app.url') }}/book-service", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(formData)
                })
                .then(async r => {
                    const data = await r.json();
                    if (data.success) {
                        fields.forEach(id => {
                            const el = document.getElementById(id);
                            if (el) el.value = '';
                        });
                        sucEl.classList.remove('hidden');
                        sucEl.innerHTML = data.message || 'Booked!';
                    } else {
                        errEl.classList.remove('hidden');
                        errEl.innerHTML = data.message || 'Something went wrong';
                    }
                })
                .catch(() => {
                    errEl.classList.remove('hidden');
                    errEl.innerHTML = 'Something went wrong';
                });
        }

        // Flatpickr
        const disableSlots = {!! $appointment_slots !!};
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
            const allowedDays = Object.keys(availableDays).filter(d => availableDays[d]).map(d => dayMap[d]);
            flatpickr('#appointment-date', {
                dateFormat: 'Y-m-d',
                minDate: 'today',
                locale: '{{ app()->getLocale() }}',
                disable: [function(date) {
                    const d = date.toLocaleString('en-us', {
                        weekday: 'long'
                    }).toLowerCase();
                    return !disableSlots[d] || disableSlots[d].length === 0;
                }],
                onChange: function(selectedDates) {
                    const d = selectedDates[0].toLocaleString('en-us', {
                        weekday: 'long'
                    }).toLowerCase();
                    generateOption(selectedDates[0], d);
                }
            });
            flatpickr('#service_start_date', {
                dateFormat: 'Y-m-d',
                minDate: 'today',
                locale: '{{ app()->getLocale() }}',
                disable: [function(date) {
                    return !allowedDays.includes(date.getDay());
                }]
            });
            flatpickr('#service_end_date', {
                dateFormat: 'Y-m-d',
                minDate: 'today',
                locale: '{{ app()->getLocale() }}',
                disable: [function(date) {
                    return !allowedDays.includes(date.getDay());
                }]
            });
            flatpickr('.timepicker', {
                enableTime: true,
                noCalendar: true,
                dateFormat: 'H:i',
                time_24hr: true
            });
        });

        // Language Switcher
        function positionLangMenu() {
            const btn = document.getElementById('langSwitcherBtn'),
                menu = document.getElementById('langMenu');
            if (!btn || !menu) return;
            const rect = btn.getBoundingClientRect();
            menu.style.top = (rect.bottom + 6) + 'px';
            menu.style.right = (window.innerWidth - rect.right) + 'px';
            menu.style.left = 'auto';
        }

        function toggleLangMenu(e) {
            e.stopPropagation();
            const btn = document.getElementById('langSwitcherBtn'),
                menu = document.getElementById('langMenu');
            if (!btn || !menu) return;
            const isOpen = menu.classList.contains('open');
            if (isOpen) {
                menu.classList.remove('open');
                btn.classList.remove('open');
            } else {
                positionLangMenu();
                menu.classList.add('open');
                btn.classList.add('open');
            }
        }

        function selectLang(locale, label) {
            const currentLang = document.getElementById('currentLang');
            if (currentLang) currentLang.textContent = label;
            document.getElementById('langMenu')?.classList.remove('open');
            document.getElementById('langSwitcherBtn')?.classList.remove('open');
            fetch("{{ config('app.url') }}/set-locale", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": "{{ csrf_token() }}"
                    },
                    body: JSON.stringify({
                        locale: locale,
                        card_id: '{{ $business_card_details->card_id }}'
                    })
                })
                .then(r => {
                    if (!r.ok) throw new Error('Failed');
                    window.location.reload();
                }).catch(err => console.error('Locale switch error:', err));
        }

        document.addEventListener('click', function() {
            document.getElementById('langMenu')?.classList.remove('open');
            document.getElementById('langSwitcherBtn')?.classList.remove('open');
        });
        window.addEventListener('resize', function() {
            if (document.getElementById('langMenu')?.classList.contains('open')) positionLangMenu();
        });
        document.addEventListener('scroll', function() {
            if (document.getElementById('langMenu')?.classList.contains('open')) positionLangMenu();
        }, true);

        const scroll = new SmoothScroll('a[href*="#"]', {
            speed: 300,
            offset: 50,
            easing: 'easeInOutCubic'
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
