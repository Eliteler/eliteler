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

    {{-- Luxury Augusta Deep Green Accent Meta --}}
    <meta name="theme-color" content="#0F382C" />
    <meta name="application-name" content="{{ $card_details->title }}">x
    <meta name="apple-mobile-web-app-title" content="{{ $card_details->title }}">
    <meta name="msapplication-TileColor" content="#07221A">
    <meta name="msapplication-TileImage" content="{{ url($business_card_details->profile) }}">

    {!! SEOMeta::generate() !!}
    {!! OpenGraph::generate() !!}
    {!! Twitter::generate() !!}
    {!! JsonLd::generate() !!}

    {{-- Fonts: Cinzel + Playfair Display + Plus Jakarta Sans --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Cinzel:wght@500;600;700;800;900&family=Playfair+Display:ital,wght@0,500;0,600;0,700;1,400&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">

    {{-- Golf Club Clubhouse Pass Theme CSS --}}
    <link rel="stylesheet" href="{{ asset('templates/css/golf-vcard.css') }}">

    {{-- Intro Screen CSS --}}
    @if ($introScreen != null)
        <link rel="stylesheet" href="{{ asset('templates/css/intros/' . $introScreen->intro_code . '.min.css') }}">
    @endif

    {{-- Vendor Styles --}}
    <link rel="stylesheet" href="{{ url('css/swiper-bundle.min.css') }}">
    <link rel="stylesheet" href="{{ url('css/fontawesome.min.css') }}" />
    <link href="{{ url('css/flatpickr.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/lightgallery.min.css') }}" rel="stylesheet">

    {{-- QRious --}}
    <script src="{{ url('js/qrious.min.js') }}"></script>

    {{-- Check business details & Custom code injection --}}
    @if ($business_card_details != null)
        @php
            $custom_css = trim($business_card_details->custom_css ?? '');
            $custom_js = trim($business_card_details->custom_js ?? '');

            if (!empty($custom_css) && stripos($custom_css, '<style') === false) {
                $custom_css = "<style>{$custom_css}</style>";
            }

            $include_custom_js = '';
            if (!empty($custom_js)) {
                preg_match_all(
                '/<(meta|script|noscript|iframe|link|style|img)[^>]*>.*?<\/script>?|<(meta|link|img)[^>]*\/@endphp/is',
        $custom_js,
        $matches,
    );

    $html_tags = implode("\n", $matches[0]);
    $plain_js = trim(
        preg_replace(
            '/<(meta|script|noscript|iframe|link|style|img)[^>]*>.*?<\/script>?|<(meta|link|img)[^>]*\/?>/is',
            '',
            $custom_js,
        ),
    );

    $include_custom_js .= $html_tags;
    if (!empty($plain_js)) {
        $include_custom_js .= "\n<script>
            {
                $plain_js
            }
        </script>";
    }
}
?>

        {!! $custom_css !!}
        {!! $include_custom_js !!}

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
    {{-- Background floating Golf Motifs layer --}}
    @include('templates.cards.golf-vcard.includes.bg-icons')

    {{-- Loader --}}
    @if ($introScreen != null)
        <div id="loader">
            <div class="spinner"></div>
        </div>
    @endif

    <div id="smooth-wrapper">
        <div id="smooth-content" class="vcard-container">

            @if ($business_card_details->password == null || Session::get('password_protected') == true)
                {{-- Intro Screen --}}
                @if ($introScreen != null)
                    @include("templates.cards.includes.intros.{$introScreen->intro_code}", [
                        'theme' => $business_card_details->theme_id,
                    ])
                @endif

                <div id="content-screen">

                    {{-- ── COVER / CLUBHOUSE BANNER ── --}}
                    <div class="cover-photo">
                        {{-- 1. Default Pattern --}}
                        @if ($business_card_details->cover_type == 'none')
                            <div class="cover-media-item"
                                style="background:url('{{ url('img/templates/golf/banner.png') }}') center/cover no-repeat !important;">
                            </div>
                            <div class="cover-overlay"></div>
                        @endif

                        {{-- 2. Photo Cover --}}
                        @if ($business_card_details->cover_type == 'photo')
                            <div class="cover-media-item"
                                style="background:url('{{ $business_card_details->cover ? url($business_card_details->cover) : asset('images/default-cover.png') }}') center/cover no-repeat !important;">
                            </div>
                            <div class="cover-overlay"></div>
                        @endif

                        {{-- 3. YouTube Autoplay --}}
                        @if ($business_card_details->cover_type == 'youtube-ap')
                            <iframe class="cover-media-item cover-video-item"
                                src="https://www.youtube.com/embed/{{ $business_card_details->cover }}?autoplay=1&mute=1&controls=1&loop=1&playsinline=1&enablejsapi=1&playlist={{ $business_card_details->cover }}"
                                allow="autoplay; encrypted-media; picture-in-picture"
                                allowfullscreen frameborder="0"></iframe>
                        @endif

                        {{-- 4. YouTube Normal (Click to Play on All Devices) --}}
                        @if ($business_card_details->cover_type == 'youtube')
                            <iframe class="cover-media-item cover-video-item"
                                src="https://www.youtube.com/embed/{{ $business_card_details->cover }}?autoplay=0&mute=0&controls=1&rel=0&playsinline=1&enablejsapi=1"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                allowfullscreen frameborder="0"></iframe>
                        @endif

                        {{-- 5. Vimeo Autoplay --}}
                        @if ($business_card_details->cover_type == 'vimeo-ap')
                            <iframe class="cover-media-item cover-video-item"
                                src="https://player.vimeo.com/video/{{ $business_card_details->cover }}?background=1&autoplay=1&loop=1&byline=0&title=0&muted=1"
                                allow="autoplay; fullscreen" frameborder="0"></iframe>
                        @endif

                        {{-- 6. Vimeo Normal --}}
                        @if ($business_card_details->cover_type == 'vimeo')
                            <iframe class="cover-media-item cover-video-item"
                                src="https://player.vimeo.com/video/{{ $business_card_details->cover }}?autoplay=0&loop=0&controls=1"
                                allow="autoplay; fullscreen; picture-in-picture" frameborder="0"></iframe>
                        @endif
                    </div>

                    {{-- Language Switcher --}}
                    @if (
                        $business_card_details->is_enable_language_switcher == 1 &&
                            is_array(config('app.languages')) &&
                            count(config('app.languages')) > 1)
                        @include('templates.cards.golf-vcard.includes.language-switcher')
                    @endif

                    @if ($business_card_details != null)

                        {{-- ── PROFILE / MEMBER PASS BADGE ── --}}
                        <div class="profile-section" id="profile">
                            <div class="profile-img-wrap">
                                <div class="profile-img-ring"></div>
                                <img src="{{ url($business_card_details->profile) }}"
                                    alt="{{ $business_card_details->title }}" class="profile-img gsap-scale" />
                                <div class="profile-img-shine"></div>
                            </div>
                            <h1 class="name gsap-slide-up">{{ $business_card_details->title }}</h1>
                            <div class="name-rule gsap-slide-up">
                                <span class="name-rule__line"></span>
                                <span class="name-rule__diamond"></span>
                                <span class="name-rule__line"></span>
                            </div>
                            <span class="title-badge gsap-slide-up">{{ $card_details->sub_title }}</span>
                            @if (isset($business_card_details->description))
                                <div class="desc gsap-slide-up">{!! $business_card_details->description !!}</div>
                            @endif
                        </div>

                        {{-- ── QUICK ACTIONS ── --}}
                        @if (count($feature_details) > 0)
                            <div class="quick-actions gsap-fade">
                                @foreach ($feature_details as $feature)
                                    @if (in_array($feature->type, ['tel', 'address', 'wa', 'instagram']))
                                        @if ($feature->type == 'address')
                                            <a href="#location" class="action-btn">
                                                <span class="action-btn__icon"><i
                                                        class="{{ $feature->icon }}"></i></span>
                                                <span class="action-btn__label">{{ __('Club Course') }}</span>
                                            </a>
                                        @elseif ($feature->type == 'tel')
                                            <a href="tel:{{ $feature->content }}" class="action-btn">
                                                <span class="action-btn__icon"><i
                                                        class="{{ $feature->icon }}"></i></span>
                                                <span class="action-btn__label">{{ __('Pro-Desk') }}</span>
                                            </a>
                                        @elseif ($feature->type == 'wa')
                                            <a href="https://wa.me/{{ $feature->content }}" class="action-btn">
                                                <span class="action-btn__icon"><i
                                                        class="{{ $feature->icon }}"></i></span>
                                                <span class="action-btn__label">{{ __('Concierge') }}</span>
                                            </a>
                                        @elseif ($feature->type == 'instagram')
                                            <a href="{{ $feature->content }}" target="_blank" class="action-btn">
                                                <span class="action-btn__icon"><i
                                                        class="{{ $feature->icon }}"></i></span>
                                                <span class="action-btn__label">{{ __('Clubhouse') }}</span>
                                            </a>
                                        @endif
                                    @endif
                                @endforeach
                            </div>
                        @endif

                        {{-- ── ADDRESS / CLUB LOCATION ── --}}
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

                        {{-- ── FEATURE HIGHLIGHTS ── --}}
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
                                    <div class="section-deco">
                                        <span class="section-deco__dash"></span>
                                        <span class="section-deco__diamond"></span>
                                        <span class="section-deco__dash"></span>
                                    </div>
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
                                            <i class="{{ $feature->icon ?? 'fas fa-golf-ball' }}"></i>
                                            <h2>{{ $feature->label ?? '' }}</h2>
                                            <p>{{ $feature->content ?? '' }}</p>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        {{-- ── COURSE / VIRTUAL TOURS ── --}}
                        @if ($feature_details->where('type', 'youtube')->count() > 0)
                            <div class="section gsap-fade">
                                <div class="section-header">
                                    <h2 class="section-title">{{ __('Course Flyover') }}</h2>
                                    <div class="section-deco">
                                        <span class="section-deco__dash"></span>
                                        <span class="section-deco__diamond"></span>
                                        <span class="section-deco__dash"></span>
                                    </div>
                                </div>
                                <div class="swiper videoSwiper">
                                    <div class="swiper-wrapper">
                                        @foreach ($feature_details as $feature)
                                            @if ($feature->type == 'youtube')
                                                <div class="swiper-slide">
                                                    <div class="item-card">
                                                        @if ($feature->label)
                                                            <div class="p-3 bg-stone-900 text-gold-bright">
                                                                <p
                                                                    class="text-xs uppercase font-bold tracking-widest text-amber-300">
                                                                    {{ $feature->label }}</p>
                                                            </div>
                                                        @endif
                                                        <iframe
                                                            src="https://www.youtube.com/embed/{{ $feature->content }}"
                                                            referrerpolicy="strict-origin-when-cross-origin"
                                                            title="{{ $feature->label }}" frameborder="0"
                                                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                                            allowfullscreen class="w-full h-52"></iframe>
                                                        <div class="item-card__body">
                                                            <h4>{{ $feature->label }}</h4>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                    <div class="swiper-pagination"></div>
                                </div>
                            </div>
                        @endif

                        {{-- ── CLUB SERVICES & EXPERIENCES ── --}}
                        @if (count($service_details) > 0)
                            <div class="section gsap-fade">
                                <div class="section-header">
                                    <h2 class="section-title">{{ __($service_details[0]->title) }}</h2>
                                    <div class="section-deco">
                                        <span class="section-deco__dash"></span>
                                        <span class="section-deco__diamond"></span>
                                        <span class="section-deco__dash"></span>
                                    </div>
                                </div>
                                <div class="swiper serviceSwiper">
                                    <div class="swiper-wrapper">
                                        @foreach ($service_details as $s)
                                            <div class="swiper-slide">
                                                <div class="item-card">
                                                    <div
                                                        style="width:100%;aspect-ratio:16/9;background:url('{{ url($s->service_image) }}') center/cover no-repeat;">
                                                    </div>
                                                    <div class="item-card__body">
                                                        <h4>{{ $s->service_name }}</h4>
                                                        <p style="margin-top:4px">{{ $s->service_description }}</p>
                                                        @if ($enquiry_button != null && $whatsAppNumberExists == true && $s->enable_enquiry == 'Enabled')
                                                            <a href="https://wa.me/{{ $enquiry_button }}?text={{ __('Hi, I am interested in your golf service:') }} {{ $s->service_name }}. {{ __('Please provide more details.') }}"
                                                                target="_blank" class="btn-primary btn-sm"
                                                                style="margin-top:14px">{{ __('Reserve Service') }}</a>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                    <div class="swiper-pagination"></div>
                                </div>
                            </div>
                        @endif

                        {{-- ── PRO SHOP & PRODUCTS ── --}}
                        @if (count($product_details) > 0)
                            <div class="section gsap-fade">
                                <div class="section-header">
                                    <h2 class="section-title">{{ __($product_details[0]->title) }}</h2>
                                    <div class="section-deco">
                                        <span class="section-deco__dash"></span>
                                        <span class="section-deco__diamond"></span>
                                        <span class="section-deco__dash"></span>
                                    </div>
                                </div>
                                <div class="swiper productSwiper">
                                    <div class="swiper-wrapper">
                                        @foreach ($product_details as $p)
                                            <div class="swiper-slide">
                                                <div class="item-card relative">
                                                    @if (!empty($p->badge))
                                                        <span class="product-badge">{{ $p->badge }}</span>
                                                    @endif
                                                    <img src="{{ url($p->product_image) }}"
                                                        alt="{{ $p->product_name }}" />
                                                    <div class="item-card__body">
                                                        <h4>{{ $p->product_name }}</h4>
                                                        <p style="margin-top:4px">{{ $p->product_description }}</p>
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
                                                                style="font-size:10px;font-weight:700;letter-spacing:1px;text-transform:uppercase;margin-bottom:8px;color:{{ $p->product_status == 'instock' ? '#184E3F' : '#8A2A1A' }}">
                                                                {{ $p->product_status == 'outstock' ? __('Out of Stock') : __('Available at Pro Shop') }}
                                                            </p>
                                                        @endif
                                                        @if ($enquiry_button != null && $whatsAppNumberExists == true)
                                                            <a href="https://wa.me/{{ $enquiry_button }}?text={{ __('Hi, I am interested in Pro Shop item:') }} {{ $p->product_name }}. {{ __('Please provide availability.') }}"
                                                                target="_blank" class="btn-primary"
                                                                style="padding:10px;font-size:10px">{{ __('Inquire Equipment') }}</a>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                    <div class="swiper-pagination"></div>
                                </div>
                            </div>
                        @endif

                        {{-- ── COURSE & CLUBHOUSE GALLERY ── --}}
                        @if (count($galleries_details) > 0)
                            <div class="section gsap-fade">
                                <div class="section-header">
                                    <h2 class="section-title">{{ __($galleries_details[0]->title) }}</h2>
                                    <div class="section-deco">
                                        <span class="section-deco__dash"></span>
                                        <span class="section-deco__diamond"></span>
                                        <span class="section-deco__dash"></span>
                                    </div>
                                </div>
                                <div class="swiper gallerySwiper">
                                    <div class="swiper-wrapper">
                                        @foreach ($galleries_details as $g)
                                            <div class="swiper-slide">
                                                <div class="gallery-card">
                                                    <img src="{{ url($g->gallery_image) }}"
                                                        alt="{{ $g->caption }}" class="gallery-img" />
                                                    @if ($g->caption)
                                                        <div class="gallery-caption-overlay">
                                                            <p>{{ $g->caption }}</p>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                    <div class="swiper-pagination"></div>
                                </div>
                            </div>
                        @endif

                        {{-- ── INSTAGRAM FEED ── --}}
                        @include('templates.cards.includes.instagram-embed', [
                            'columns' => 1,
                            'sectionClass' => 'section gsap-fade',
                            'customHeader' =>
                                '<div class="section-header"><h2 class="section-title">' .
                                __('Clubfeed') .
                                '</h2><div class="section-deco"><span class="section-deco__dash"></span><span class="section-deco__diamond"></span><span class="section-deco__dash"></span></div></div>',
                            'cardClass' => 'gallery-card overflow-hidden shadow-md',
                            'labelClass' => 'gallery-caption-overlay p-3 text-center',
                        ])

                        {{-- ── IFRAME SHOWCASE ── --}}
                        @if ($feature_details->where('type', 'iframe')->count() > 0)
                            <div class="section gsap-fade">
                                <div class="section-header">
                                    <h2 class="section-title">{{ __('Scorecard & Tour') }}</h2>
                                    <div class="section-deco">
                                        <span class="section-deco__dash"></span>
                                        <span class="section-deco__diamond"></span>
                                        <span class="section-deco__dash"></span>
                                    </div>
                                </div>
                                @foreach ($feature_details as $feature)
                                    @if ($feature->type == 'iframe')
                                        <div class="rounded-xl overflow-hidden mb-4 border border-stone-200 shadow-sm">
                                            <iframe src="{{ $feature->content }}" title="{{ $feature->label }}"
                                                referrerpolicy="strict-origin-when-cross-origin" frameborder="0"
                                                width="100%" height="270"
                                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                                allowfullscreen></iframe>
                                            @if ($feature->label)
                                                <div
                                                    class="p-2.5 bg-stone-900 text-center font-bold text-xs uppercase tracking-widest text-amber-300">
                                                    {{ $feature->label }}</div>
                                            @endif
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        @endif

                        {{-- ── TEE-TIME / APPOINTMENT BOOKING ── --}}
                        @if (
                            $appointmentEnabled == true &&
                                isset($plan_details['appointment']) &&
                                $plan_details['appointment'] == 1 &&
                                $appointment_slots != null)
                            <div class="section gsap-fade" id="booking-section">
                                <div class="section-header">
                                    <h2 class="section-title">{{ __(json_decode($appointment_slots, true)['title']) }}
                                    </h2>
                                    <div class="section-deco">
                                        <span class="section-deco__dash"></span>
                                        <span class="section-deco__diamond"></span>
                                        <span class="section-deco__dash"></span>
                                    </div>
                                </div>
                                <div class="gem-card">
                                    <div id="errorMessage" class="alert-error hidden">
                                        {{ __('Please select a valid date and tee slot.') }}</div>
                                    <div id="successMessage" class="alert-success hidden">
                                        {{ __('Tee Time reserved successfully!') }}</div>
                                    <div id="errorSubmitMessage" class="alert-error hidden">
                                        {{ __('Please fill all the fields.') }}</div>
                                    <div style="display:flex;gap:12px;margin-bottom:16px;flex-wrap:wrap;">
                                        <input type="text" id="appointment-date"
                                            class="form-control flatpickr-input"
                                            style="flex:1;min-width:140px;margin-bottom:0"
                                            placeholder="{{ __('Select Date') }}" required />
                                        <select id="time-slot-select" required class="form-control"
                                            style="flex:1;min-width:140px;margin-bottom:0">
                                            <option value="">{{ __('Select Tee Slot') }}</option>
                                        </select>
                                    </div>
                                    <button id="add-slot-button" class="btn-primary"
                                        onclick="validateAndShowModal()">{{ __('Book Tee Time') }}</button>
                                </div>
                            </div>
                        @endif

                        {{-- ── CLUBHOUSE HOURS & TEE AVAILABILITY ── --}}
                        @if ($plan_details['business_hours'] == 1 && $business_hours != null && $business_hours->is_display != 0)
                            <div class="section gsap-fade">
                                <div class="section-header">
                                    <h2 class="section-title">{{ __($business_hours->title) }}</h2>
                                    <div class="section-deco">
                                        <span class="section-deco__dash"></span>
                                        <span class="section-deco__diamond"></span>
                                        <span class="section-deco__dash"></span>
                                    </div>
                                </div>
                                <div class="gem-card">
                                    @if ($business_hours->is_always_open != 'Opening')
                                        @foreach (['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as $day)
                                            <div class="hour-row">
                                                <span>{{ __($day) }}</span>
                                                <span>{{ __($business_hours->$day ?: __('Course Closed')) }}</span>
                                            </div>
                                        @endforeach
                                    @else
                                        <div style="display:flex;align-items:center;gap:16px;padding:8px 0">
                                            <i class="fas fa-flag" style="color:var(--gold);font-size:22px"></i>
                                            <div>
                                                <p
                                                    style="font-family:'Cinzel',serif;font-weight:700;font-size:16px;color:var(--forest-deep)">
                                                    {{ __('Fairway Always Open') }}</p>
                                                <p style="font-size:12px;color:var(--text-muted);margin-top:3px">
                                                    {{ __('Club facilities available 24/7 for championship members.') }}
                                                </p>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif

                        {{-- ── SERVICE BOOKING / CADDIE & COURSE PACKAGES ── --}}
                        @if (isset($plan_details['service_booking']) &&
                                $plan_details['service_booking'] == 1 &&
                                isset($service_booking_details) &&
                                $service_booking_details->service_booking == 1)
                            <div class="section gsap-fade">
                                <div class="section-header">
                                    <h2 class="section-title">{{ __($service_booking_details->title) }}</h2>
                                    <div class="section-deco">
                                        <span class="section-deco__dash"></span>
                                        <span class="section-deco__diamond"></span>
                                        <span class="section-deco__dash"></span>
                                    </div>
                                </div>
                                <div class="gem-card">
                                    <div id="errorMessage1" class="alert-error hidden"></div>
                                    <div id="successMessage1" class="alert-success hidden"></div>
                                    <div class="service-booking-form">
                                        <div><label class="form-label">{{ __('Golfer Name') }}</label><input
                                                type="text" name="customer_name" id="customer_name"
                                                placeholder="{{ __('Full Name') }}" class="form-control" /></div>
                                        <div><label class="form-label">{{ __('Member Email') }}</label><input
                                                type="email" name="customer_email" id="customer_email"
                                                placeholder="{{ __('Member Email') }}" class="form-control" /></div>
                                        <div><label class="form-label">{{ __('Phone / Mobile') }}</label><input
                                                type="tel" name="customer_phone" id="customer_phone"
                                                placeholder="{{ __('Mobile Number') }}" class="form-control" />
                                        </div>
                                        <div><label class="form-label">{{ __('Players (Flight)') }}</label><input
                                                type="number" name="no_of_persons" id="no_of_persons"
                                                value="1" class="form-control" /></div>
                                    </div>
                                    <label class="form-label">{{ __('Locker / Address') }}</label>
                                    <textarea name="customer_address" id="customer_address" placeholder="{{ __('Address or Member ID') }}"
                                        class="form-control" rows="2"></textarea>
                                    <label class="form-label">{{ __('Special Requests (Caddie / Cart)') }}</label>
                                    <textarea name="customer_notes" id="customer_notes"
                                        placeholder="{{ __('Golf Cart, Pro Caddie, Rental clubs...') }}" class="form-control" rows="2"></textarea>
                                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                                        <div><label class="form-label">{{ __('Round Date') }}</label><input
                                                type="text" id="service_start_date" name="service_start_date"
                                                class="form-control" />
                                        </div>
                                        <div><label class="form-label">{{ __('Start Time') }}</label><input
                                                type="time" name="service_start_time" id="service_start_time"
                                                class="form-control timepicker" /></div>
                                        <div><label class="form-label">{{ __('Finish Date') }}</label><input
                                                type="date" id="service_end_date" name="service_end_date"
                                                class="form-control" />
                                        </div>
                                        <div><label class="form-label">{{ __('Finish Time') }}</label><input
                                                type="time" name="service_end_time" id="service_end_time"
                                                class="form-control timepicker" /></div>
                                    </div>
                                    <button onclick="submitServiceBooking()" class="btn-primary"
                                        style="margin-top:8px">{{ __('Confirm Reservation') }}</button>
                                </div>
                            </div>
                        @endif

                        {{-- ── MEMBER TESTIMONIALS ── --}}
                        @if (count($testimonials) > 0)
                            <div class="section gsap-fade">
                                <div class="section-header">
                                    <h2 class="section-title">{{ __($testimonials[0]->title) }}</h2>
                                    <div class="section-deco">
                                        <span class="section-deco__dash"></span>
                                        <span class="section-deco__diamond"></span>
                                        <span class="section-deco__dash"></span>
                                    </div>
                                </div>
                                <div class="swiper testimonialSwiper">
                                    <div class="swiper-wrapper">
                                        @foreach ($testimonials as $t)
                                            <div class="swiper-slide">
                                                <div class="testimonial-card">
                                                    <p
                                                        style="font-family:'Playfair Display',serif;font-style:italic;font-size:15px;color:var(--text-body);line-height:1.8;margin-bottom:18px">
                                                        "{{ $t->review }}"</p>
                                                    <img src="{{ url($t->reviewer_image) }}"
                                                        alt="{{ $t->reviewer_name }}"
                                                        style="width:52px;height:52px;border-radius:50%;object-fit:cover;margin:0 auto 10px;display:block;border:2px solid var(--gold)" />
                                                    <strong
                                                        style="font-family:'Cinzel',serif;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:1.5px;color:var(--forest-deep)">{{ $t->reviewer_name }}</strong>
                                                    @if ($t->review_subtext)
                                                        <p
                                                            style="font-size:11px;color:var(--text-muted);margin-top:3px;letter-spacing:0.5px">
                                                            {{ $t->review_subtext }}</p>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                    <div class="swiper-pagination"></div>
                                </div>
                            </div>
                        @endif

                        {{-- ── PAYMENT LINKS / CLUB DUES ── --}}
                        @if (count($payment_details) > 0)
                            <div class="section gsap-fade">
                                <div class="section-header">
                                    <h2 class="section-title">{{ __($payment_details[0]->title) }}</h2>
                                    <div class="section-deco">
                                        <span class="section-deco__dash"></span>
                                        <span class="section-deco__diamond"></span>
                                        <span class="section-deco__dash"></span>
                                    </div>
                                </div>
                                <div class="pay-links-grid">
                                    @foreach ($payment_details as $payment)
                                        @if (in_array($payment->type, ['url', 'upi']))
                                            <a href="{{ $payment->type == 'url' ? 'https://' . str_replace('https://', '', $payment->content) : 'upi://pay?pa=' . $payment->content . '&pn=' . urlencode($payment->label) . '&am=1&cu=INR' }}"
                                                target="_blank" class="pay-btn">
                                                <div class="pay-btn-media">
                                                    @include('templates.cards.partials.payment-link-image', ['style' => 'no'])
                                                </div>
                                                <span class="pay-btn-label">{{ $payment->label }}</span>
                                                <i class="fas fa-arrow-up-right-from-square pay-btn-arrow"></i>
                                            </a>
                                        @else
                                            <div class="pay-card">
                                                <div class="pay-card-header">
                                                    <div class="pay-card-media">
                                                        @include('templates.cards.partials.payment-link-image', ['style' => 'no'])
                                                    </div>
                                                    <h4>{{ $payment->label }}</h4>
                                                </div>
                                                @if ($payment->type == 'text')
                                                    <div class="pay-card-body">
                                                        @foreach (explode('.', $payment->content) as $s)
                                                            @if (trim($s))
                                                                <p>{{ trim($s) }}</p>
                                                            @endif
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        {{-- ── SOCIAL & CLUB NETWORK ── --}}
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
                                <div class="section-header">
                                    <h2 class="section-title">{{ __('Club Network') }}</h2>
                                    <div class="section-deco">
                                        <span class="section-deco__dash"></span>
                                        <span class="section-deco__diamond"></span>
                                        <span class="section-deco__dash"></span>
                                    </div>
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

                        {{-- ── GOOGLE MAPS COURSE LOCATION ── --}}
                        @if (count($feature_details) > 0 && $feature_details->contains('type', 'map'))
                            <div class="section gsap-fade" id="location">
                                <div class="section-header">
                                    <h2 class="section-title">{{ __('Course Location') }}</h2>
                                    <div class="section-deco">
                                        <span class="section-deco__dash"></span>
                                        <span class="section-deco__diamond"></span>
                                        <span class="section-deco__dash"></span>
                                    </div>
                                </div>
                                @foreach ($feature_details as $feature)
                                    @if ($feature->type == 'map')
                                        <div class="rounded-xl overflow-hidden border border-stone-200 shadow-sm">
                                            <iframe src="https://www.google.com/maps/embed?{!! $feature->content !!}"
                                                referrerpolicy="strict-origin-when-cross-origin" width="100%"
                                                height="220" style="border:0;display:block" allowfullscreen
                                                loading="lazy"></iframe>
                                            @if ($feature->label)
                                                <div
                                                    class="p-2.5 bg-stone-900 text-center font-bold text-xs uppercase tracking-widest text-amber-300">
                                                    {{ $feature->label }}</div>
                                            @endif
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        @endif

                        {{-- ── GOOGLE WALLET PASS ── --}}
                        @if (plugin_active('GoogleWallet') &&
                                isset($plan_details['google_wallet']) &&
                                $plan_details['google_wallet'] == 1 &&
                                $business_card_details->is_google_wallet_hidden == 0)
                            <div class="section gsap-fade">
                                <div class="section-header">
                                    <h2 class="section-title">{{ __('Club Membership Pass') }}</h2>
                                    <div class="section-deco">
                                        <span class="section-deco__dash"></span>
                                        <span class="section-deco__diamond"></span>
                                        <span class="section-deco__dash"></span>
                                    </div>
                                </div>
                                <div class="gem-card text-center">
                                    @if ($google_wallet_details->wallet_description != null)
                                        <div style="font-size:13px;color:var(--text-muted);margin-bottom:16px">
                                            {!! $google_wallet_details->wallet_description !!}</div>
                                    @endif
                                    @if ($google_wallet_details->wallet_link != null)
                                        <a href="{{ $google_wallet_details->wallet_link }}" target="_blank"
                                            rel="noopener noreferrer"
                                            style="display:block;max-width:240px;margin:0 auto">
                                            <img src="{{ url()->to('/') . '/img/google-wallet-btn.png' }}"
                                                alt="Add to Google Wallet" style="width:100%;object-fit:cover" />
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @endif

                        {{-- ── CONTACT & CONCIERGE DESK ── --}}
                        @if ($plan_details['contact_form'] == 1 && $business_card_details->enquiry_email != null)
                            <div class="section gsap-fade" id="contact-section">
                                <div class="section-header">
                                    <h2 class="section-title">{{ __($business_card_details->contact_form_title) }}
                                    </h2>
                                    <div class="section-deco">
                                        <span class="section-deco__dash"></span>
                                        <span class="section-deco__diamond"></span>
                                        <span class="section-deco__dash"></span>
                                    </div>
                                </div>
                                @if (Session::has('message'))
                                    <div class="alert-success">{{ Session::get('message') }}</div>
                                @endif
                                <form class="gem-card" action="{{ config('app.url') }}/sent-enquiry"
                                    method="POST">
                                    @csrf
                                    <input type="hidden" name="card_id"
                                        value="{{ $business_card_details->card_id }}" />
                                    <label class="form-label">{{ __('Member / Golfer Name') }}</label>
                                    <input type="text" name="name" placeholder="{{ __('Your Name') }}"
                                        class="form-control" required />
                                    <label class="form-label">{{ __('Email Address') }}</label>
                                    <input type="email" name="email" placeholder="{{ __('Your Email') }}"
                                        class="form-control" required />
                                    <label class="form-label">{{ __('Telephone') }}</label>
                                    <input type="tel" name="phone" placeholder="{{ __('Mobile Number') }}"
                                        class="form-control" required />
                                    <label class="form-label">{{ __('Message to Concierge') }}</label>
                                    <textarea name="message" placeholder="{{ __('How may the clubhouse assist your round today?') }}"
                                        class="form-control" rows="4" required></textarea>
                                    @include('templates.cards.includes.recaptcha', [
                                        'recaptchaId' => 'recaptcha-one',
                                    ])
                                    <button type="submit"
                                        class="btn-primary">{{ __('Send Concierge Request') }}</button>
                                </form>
                            </div>
                        @endif

                        {{-- ── BRANDING FOOTER ── --}}
                        <div class="section branding-footer">
                            @if ($plan_details['hide_branding'] == 1)
                                {{ __('Copyright') }} &copy; <a
                                    href="{{ url()->current() }}">{{ $card_details->title }}</a> <span
                                    id="year"></span>{{ __('. All Rights Reserved.') }}
                            @else
                                {{ __('Powered by') }} <a
                                    href="{{ env('APP_URL') }}">{{ config('app.name') }}</a>
                                <span id="year"></span>{{ __('. All Rights Reserved.') }}
                            @endif
                        </div>

                    @endif
                </div>

                {{-- ── FLOATING CLUBHOUSE PASS BOTTOM NAV ── --}}
                <div class="bottom-nav gsap-nav">
                    <button class="nav-item active" onclick="window.scrollTo({top:0, behavior:'smooth'})"
                        title="Top">
                        <i class="fas fa-shield-alt"></i>
                    </button>
                    <button class="nav-item"
                        onclick="document.getElementById('booking-section')?.scrollIntoView({behavior:'smooth'})"
                        title="Tee Times">
                        <i class="far fa-calendar-alt"></i>
                    </button>
                    <a href="{{ config('app.url') }}/download/{{ $business_card_details->card_id }}"
                        class="nav-item" title="Save Contact">
                        <i class="fas fa-address-card"></i>
                    </a>
                    <button class="nav-item" onclick="toggleScanModal(true)" title="Pass QR">
                        <i class="fas fa-qrcode"></i>
                    </button>
                    <button class="nav-item" onclick="toggleWhatsAppModal(true)" title="WhatsApp">
                        <i class="fab fa-whatsapp"></i>
                    </button>
                    <button class="nav-item" onclick="shareToggleModal(true)" title="Share">
                        <i class="fas fa-share-alt"></i>
                    </button>
                </div>
            @endif


            {{-- ============================================================ APPOINTMENT MODAL ============================================================ --}}
            <div id="appointmentModal" class="std-modal hidden">
                <div class="std-modal-box" onclick="event.stopPropagation()">
                    <button class="close-btn" onclick="toggleModal()"><i class="fas fa-times"></i></button>
                    <h2>{{ __('Reserve Tee Time') }}</h2>
                    <form id="appointmentForm">
                        <label class="form-label text-left">{{ __('Player Name') }}</label>
                        <input type="text" id="name" class="form-control" required />
                        <label class="form-label text-left">{{ __('Email') }}</label>
                        <input type="email" id="email" class="form-control" required />
                        <label class="form-label text-left">{{ __('Phone') }}</label>
                        <input type="text" id="phone" class="form-control" required />
                        <label class="form-label text-left">{{ __('Flight Notes (Handicap / Cart)') }}</label>
                        <textarea id="notes" class="form-control" rows="3"></textarea>
                        <div class="hidden"><input type="text" id="price" class="form-control" disabled />
                        </div>
                        @include('templates.cards.includes.recaptcha', ['recaptchaId' => 'recaptcha-two'])
                        <div class="std-modal-footer">
                            <button type="button" class="btn-cancel"
                                onclick="toggleModal()">{{ __('Cancel') }}</button>
                            <button type="submit" id="bookAppointmentButton"
                                class="btn-confirm">{{ __('Confirm Slot') }}</button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- ============================================================ SHARE MODAL ============================================================ --}}
            <div id="shareModal" class="std-modal hidden" onclick="shareToggleModal(false)">
                <div class="std-modal-box" onclick="event.stopPropagation()">
                    <button class="close-btn" onclick="shareToggleModal(false)"><i class="fas fa-times"></i></button>
                    <h2 style="font-size:20px">{{ __('Share Pass') }}</h2>
                    <div class="section-deco mb-4" style="margin-bottom: 16px;">
                        <span class="section-deco__dash"></span>
                        <span class="section-deco__diamond"></span>
                        <span class="section-deco__dash"></span>
                    </div>
                    <div style="display:flex; justify-content:center; margin-bottom: 16px;">
                        <div style="padding: 12px; background: #fff; border: 1px solid var(--border); border-radius: 12px; box-shadow: inset 0 2px 4px rgba(0,0,0,0.04);">
                            <canvas id="shareQrCode"></canvas>
                        </div>
                    </div>
                    <p class="form-label" style="text-align:center; margin-bottom: 12px;">{{ __('Share Via') }}</p>
                    <div style="display:flex; justify-content:center; gap: 12px; margin-bottom: 20px;">
                        <a href="{{ $shareComponent['facebook'] }}" target="_blank"
                            class="social-card" style="width:42px; height:42px; border-radius:50%; display:flex; align-items:center; justify-content:center;"><i class="fab fa-facebook-f"></i></a>
                        <a href="{{ $shareComponent['twitter'] }}" target="_blank"
                            class="social-card" style="width:42px; height:42px; border-radius:50%; display:flex; align-items:center; justify-content:center;"><i class="fab fa-twitter"></i></a>
                        <a href="{{ $shareComponent['linkedin'] }}" target="_blank"
                            class="social-card" style="width:42px; height:42px; border-radius:50%; display:flex; align-items:center; justify-content:center;"><i class="fab fa-linkedin-in"></i></a>
                        <a href="{{ $shareComponent['whatsapp'] }}" target="_blank"
                            class="social-card" style="width:42px; height:42px; border-radius:50%; display:flex; align-items:center; justify-content:center;"><i class="fab fa-whatsapp"></i></a>
                        <a href="{{ $shareComponent['telegram'] }}" target="_blank"
                            class="social-card" style="width:42px; height:42px; border-radius:50%; display:flex; align-items:center; justify-content:center;"><i class="fab fa-telegram-plane"></i></a>
                    </div>
                    <button onclick="copyLinkStyled(this)" class="btn-primary">
                        <i class="fas fa-link"></i><span>{{ __('Copy Membership Link') }}</span>
                    </button>
                </div>
            </div>

            {{-- ============================================================ WHATSAPP MODAL ============================================================ --}}
            <div id="whatsappModal" class="std-modal hidden" onclick="toggleWhatsAppModal(false)">
                <div class="std-modal-box" onclick="event.stopPropagation()">
                    <button class="close-btn" onclick="toggleWhatsAppModal(false)"><i class="fas fa-times"></i></button>
                    <div class="whatsapp-icon-wrap" style="width:56px; height:56px; background:rgba(37,211,102,0.1); border:1px solid rgba(37,211,102,0.3); border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 14px; color:#25D366; font-size:24px;">
                        <i class="fab fa-whatsapp"></i>
                    </div>
                    <h3 style="font-size:20px; margin-bottom:6px;">{{ __('Direct WhatsApp Concierge') }}</h3>
                    <div class="section-deco mb-4" style="margin-bottom: 16px;">
                        <span class="section-deco__dash"></span>
                        <span class="section-deco__diamond"></span>
                        <span class="section-deco__dash"></span>
                    </div>
                    <label class="form-label" style="text-align:left;"
                        for="whatsappNumber">{{ __('WhatsApp Number With Country Code') }}</label>
                    <input type="text" id="whatsappNumber" placeholder="{{ __('e.g., +1234567890') }}"
                        class="form-control" autocomplete="tel" />
                    <button onclick="sendMessage()" class="btn-primary">
                        <i class="fab fa-whatsapp"></i><span>{{ __('Send Message') }}</span>
                    </button>
                    <p style="font-size:11px; color:var(--text-muted); margin-top:12px; text-align:center;">
                        <i class="fas fa-lock" style="color:var(--gold); margin-right:4px;"></i>{{ __('Include country code without special characters') }}
                    </p>
                </div>
            </div>

            {{-- ============================================================ QR SCAN MODAL ============================================================ --}}
            <div id="scanModal" class="std-modal hidden" onclick="toggleScanModal(false)">
                <div class="std-modal-box" onclick="event.stopPropagation()">
                    <button class="close-btn" onclick="toggleScanModal(false)"><i class="fas fa-times"></i></button>
                    <h3 style="font-size:20px; margin-bottom:6px;">{{ __('Member Digital Pass QR') }}</h3>
                    <div class="section-deco mb-4" style="margin-bottom: 16px;">
                        <span class="section-deco__dash"></span>
                        <span class="section-deco__diamond"></span>
                        <span class="section-deco__dash"></span>
                    </div>
                    <div style="padding:16px; background:#fff; border:1px solid var(--border); border-radius:12px; max-width:240px; margin:0 auto 16px; box-shadow:0 4px 12px rgba(0,0,0,0.04);">
                        <div class="qr-code qr-wrapper" style="display:flex; justify-content:center;"></div>
                        <p style="font-size:10px; font-weight:700; color:var(--text-muted); text-transform:uppercase; letter-spacing:1.5px; margin-top:10px; display:flex; align-items:center; justify-content:center; gap:6px;">
                            <i class="fas fa-qrcode" style="color:var(--gold);"></i>{{ __('Scan with Camera') }}
                        </p>
                    </div>
                    <button id="download"
                        onclick="downloadQrStyled(this,'{{ config('app.url') . route('dynamic.card', $business_card_details->card_id, false) }}',500)"
                        class="btn-primary">
                        <i class="fas fa-download"></i><span>{{ __('Download Pass QR') }}</span>
                    </button>
                    <p style="font-size:11px; color:var(--text-muted); margin-top:12px; text-align:center;">
                        {{ __('Share this badge pass to save contact instantly') }}</p>
                </div>
            </div>

            {{-- ============================================================ PASSWORD SCREEN ============================================================ --}}
            @if ($business_card_details->password != null && Session::get('password_protected') == false)
                <div class="pw-section">
                    <div class="pw-box">
                        <div class="pw-icon-wrap">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <h2>{{ __('Clubhouse Private Access') }}</h2>
                        <div class="pw-divider">
                            <span class="pw-divider__line"></span>
                            <span class="pw-divider__diamond">◆</span>
                            <span class="pw-divider__line"></span>
                        </div>
                        <p>{{ __('Please enter pass password') }}</p>
                        
                        <form action="{{ config('app.url') }}/check-password/{{ $business_card_details->card_id }}"
                            method="post" class="pw-form">
                            @csrf
                            <input type="password" name="password" class="pw-input" placeholder="••••" required autofocus />
                            
                            @if (Session::has('message'))
                                <div class="alert-error" style="margin-bottom: 14px;">{{ Session::get('message') }}</div>
                            @endif
                            
                            <button type="submit" class="btn-primary pw-btn">{{ __('Unlock Pass') }}</button>
                        </form>
                    </div>
                </div>
            @else
                {{-- ============================================================ PWA MODAL ============================================================ --}}
                @if ($plan_details != null && $plan_details['pwa'] == 1 && $business_card_details->is_enable_pwa == 1)
                    @include('vendor.laravelpwa.golf-vcard')
                @endif

                {{-- ============================================================ NEWSLETTER MODAL ============================================================ --}}
                @if (
                    $business_card_details != null &&
                        !empty($business_card_details->is_newsletter_pop_active) &&
                        $business_card_details->is_newsletter_pop_active == 1)
                    @include('templates.cards.golf-vcard.includes.newsletter_modal')
                @endif

                {{-- ============================================================ INFORMATION POPUP MODAL ============================================================ --}}
                @if (
                    $business_card_details != null &&
                        !empty($business_card_details->is_info_pop_active) &&
                        $business_card_details->is_info_pop_active == 1)
                    @include('templates.cards.golf-vcard.includes.information_popup_modal', ['introScreen' => $introScreen])
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
    <script src="{{ asset('js/lightgallery.min.js') }}"></script>
    <script src="{{ asset('js/lightgallery-thumbnail.min.js') }}"></script>
    <script src="{{ asset('js/lightgallery-zoom.min.js') }}"></script>
    @yield('custom-js')

    <script>
        "use strict";

        // ── Dynamic Copyright Year ──
        @if ($business_card_details->password == null && Session::get('password_protected') == true)
            const yearEl = document.getElementById('year');
            if (yearEl) yearEl.textContent = ' ' + new Date().getFullYear();
        @endif

        // ── Swiper Carousels ──
        document.addEventListener('DOMContentLoaded', function() {
            new Swiper('.serviceSwiper', {
                slidesPerView: 1,
                spaceBetween: 16,
                observer: true,
                observeParents: true,
                pagination: {
                    el: '.serviceSwiper .swiper-pagination',
                    clickable: true
                }
            });
            new Swiper('.productSwiper', {
                slidesPerView: 1,
                spaceBetween: 16,
                observer: true,
                observeParents: true,
                pagination: {
                    el: '.productSwiper .swiper-pagination',
                    clickable: true
                }
            });
            new Swiper('.gallerySwiper', {
                slidesPerView: 1,
                spaceBetween: 14,
                observer: true,
                observeParents: true,
                pagination: {
                    el: '.gallerySwiper .swiper-pagination',
                    clickable: true
                }
            });
            new Swiper('.videoSwiper', {
                slidesPerView: 1,
                spaceBetween: 16,
                observer: true,
                observeParents: true,
                pagination: {
                    el: '.videoSwiper .swiper-pagination',
                    clickable: true
                }
            });
            new Swiper('.testimonialSwiper', {
                slidesPerView: 1,
                spaceBetween: 20,
                loop: true,
                autoplay: {
                    delay: 5000,
                    disableOnInteraction: false
                },
                observer: true,
                observeParents: true,
                pagination: {
                    el: '.testimonialSwiper .swiper-pagination',
                    clickable: true
                }
            });
        });

        // ── GSAP & ScrollTrigger Animations ──
        gsap.registerPlugin(ScrollTrigger);
        gsap.set('.gsap-fade, .gsap-scale, .gsap-slide-up, .gsap-nav', {
            autoAlpha: 1
        });

        gsap.from('.gsap-scale', {
            scale: 0.88,
            opacity: 0,
            duration: 1.1,
            ease: 'power2.out'
        });

        gsap.from('.gsap-slide-up', {
            y: 26,
            opacity: 0,
            duration: 0.95,
            stagger: 0.14,
            ease: 'power2.out',
            delay: 0.15
        });

        if (window.innerWidth < 769) {
            gsap.from('.gsap-nav', {
                y: 60,
                opacity: 0,
                duration: 0.9,
                ease: 'power2.out',
                delay: 0.5
            });
        }

        gsap.utils.toArray('.gsap-fade').forEach(section => {
            gsap.from(section, {
                scrollTrigger: {
                    trigger: section,
                    start: 'top 88%'
                },
                y: 30,
                opacity: 0,
                duration: 0.9,
                ease: 'power2.out'
            });
        });
        ScrollTrigger.refresh();

        // ── Modal Handlers ──
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
                    const span = btn.querySelector('span');
                    btn.classList.add('bg-emerald-800');
                    span.textContent = `{{ __('Link Copied!') }}`;
                    setTimeout(() => {
                        btn.classList.remove('bg-emerald-800');
                        span.textContent = `{{ __('Copy Membership Link') }}`;
                    }, 2200);
                });
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

        // ── QRious Pass Generator ──
        const qr = new QRious({
            element: document.getElementById('shareQrCode'),
            value: `{{ config('app.url') . route('dynamic.card', $business_card_details->card_id, false) }}`,
            size: 200,
            background: '#FFFFFF',
            foreground: '#0F382C',
            level: 'H'
        });
        window.onload = function() {
            updateQr(`{{ config('app.url') . route('dynamic.card', $business_card_details->card_id, false) }}`);
        };

        function downloadQrStyled(btn, url, size) {
            downloadQr(url, size);
            const span = btn.querySelector('span');
            span.textContent = `{{ __('Pass Saved!') }}`;
            setTimeout(() => {
                span.textContent = `{{ __('Download Pass QR') }}`;
            }, 2200);
        }

        // ── Bottom Nav Active Switcher ──
        document.querySelectorAll('.nav-item').forEach(item => {
            item.addEventListener('click', function() {
                document.querySelectorAll('.nav-item').forEach(n => n.classList.remove('active'));
                this.classList.add('active');
            });
        });

        // ── Info & Newsletter Modals ──
        function openInfoModal() {
            const o = document.getElementById('customInfoOverlay');
            if (o) {
                o.classList.remove('hidden');
                void o.offsetWidth;
                o.classList.add('is-active');
            }
        }

        function closeInfoModal() {
            const o = document.getElementById('customInfoOverlay');
            if (o) {
                o.classList.remove('is-active');
                setTimeout(() => o.classList.add('hidden'), 300);
            }
        }
        document.addEventListener('DOMContentLoaded', function() {
            if (document.getElementById('customInfoOverlay')) setTimeout(openInfoModal, 1000);
            if (document.getElementById('newsletterModal')) {
                setTimeout(() => document.getElementById('newsletterModal').classList.add('is-active'), 1500);
            }
        });

        function closeNewsModal() {
            document.getElementById('newsletterModal')?.classList.remove('is-active');
        }

        // ── Tee-Time Appointment Booking ──
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

        document.getElementById('appointmentForm')?.addEventListener('submit', function(e) {
            e.preventDefault();
            const btn = document.getElementById('bookAppointmentButton');
            btn.disabled = true;
            btn.innerHTML = `{{ __('Reserving...') }}`;
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
            }).then(async r => {
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
                    toggleModal();
                    if (data.success && data.whatsapp_url && data.whatsapp_url !== '#') {
                        setTimeout(() => {
                            window.location.href = data.whatsapp_url;
                        }, 2500);
                    }
                } else {
                    document.getElementById('successMessage')?.classList.add('hidden');
                    const errEl = document.getElementById('errorMessage');
                    if (errEl) {
                        errEl.classList.remove('hidden');
                        errEl.innerHTML = data.message || 'Something went wrong';
                    }
                    toggleModal();
                }
                btn.disabled = false;
                btn.innerHTML = `{{ __('Confirm Slot') }}`;
            }).catch(() => {
                toggleModal();
                btn.disabled = false;
                btn.innerHTML = `{{ __('Confirm Slot') }}`;
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
            }).then(r => r.json()).then(data => {
                if (data.success == true) {
                    document.getElementById('time-slot-select').innerHTML =
                        `<option value="">{{ __('Select Tee Slot') }}</option>`;
                    JSON.parse(data.available_time_slots).forEach(ts => {
                        document.getElementById('time-slot-select').innerHTML +=
                            `<option value="${ts}">${ts}</option>`;
                    });
                    document.getElementById('price').value = data.price;
                }
            });
        }

        // ── Service Booking (Multi-day Package/Caddie) ──
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
            }).then(async r => {
                const data = await r.json();
                if (data.success) {
                    fields.forEach(id => {
                        const el = document.getElementById(id);
                        if (el) el.value = '';
                    });
                    sucEl.classList.remove('hidden');
                    sucEl.innerHTML = data.message || '{{ __('Reservation confirmed successfully!') }}';
                } else {
                    errEl.classList.remove('hidden');
                    errEl.innerHTML = data.message || 'Something went wrong';
                }
            }).catch(() => {
                errEl.classList.remove('hidden');
                errEl.innerHTML = 'Something went wrong';
            });
        }

        // ── Flatpickr Initialization ──
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
                locale: '{{ $locale }}',
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
                locale: '{{ $locale }}',
                disable: [function(date) {
                    return !allowedDays.includes(date.getDay());
                }]
            });
            flatpickr('#service_end_date', {
                dateFormat: 'Y-m-d',
                minDate: 'today',
                locale: '{{ $locale }}',
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

        // ── LightGallery & Intro Loader ──
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.lightgallery').forEach(gallery => {
                lightGallery(gallery, {
                    selector: 'a',
                    plugins: [lgZoom, lgThumbnail],
                    speed: 400,
                    backdropDuration: 300,
                    zoomFromOrigin: true
                });
            });
        });

        @if ($introScreen != null)
            window.addEventListener("load", () => {
                const loader = document.getElementById("loader");
                if (loader) loader.classList.add("hidden");
            });
        @endif
    </script>
</body>

</html>
