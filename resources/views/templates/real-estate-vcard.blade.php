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

    <meta name="theme-color" content="#1a56db" />
    <meta name="application-name" content="{{ $card_details->title }}">
    <meta name="apple-mobile-web-app-title" content="{{ $card_details->title }}">
    <meta name="msapplication-TileColor" content="#1a56db">
    <meta name="msapplication-TileImage" content="{{ url($business_card_details->profile) }}">

    {!! SEOMeta::generate() !!}
    {!! OpenGraph::generate() !!}
    {!! Twitter::generate() !!}
    {!! JsonLd::generate() !!}

    <!-- Google Fonts: Playfair Display + Inter -->
    <link
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,600&family=Inter:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">

    {{-- Real Estate Theme CSS --}}
    <link rel="stylesheet" href="{{ url('templates/css/real-estate.css') }}">

    {{-- Intro Screen CSS --}}
    @if ($introScreen != null)
        <link rel="stylesheet" href="{{ asset('templates/css/intros/' . $introScreen->intro_code . '.min.css') }}">
    @endif

    <!-- Swiper CSS -->
    <link rel="stylesheet" href="{{ url('css/swiper-bundle.min.css') }}">
    <!-- Fontawesome CSS -->
    <link rel="stylesheet" href="{{ url('css/fontawesome.min.css') }}" />
    <!-- AOS CSS -->
    <link rel="stylesheet" href="{{ url('css/aos.css') }}" />
    <!-- Flatpickr CSS -->
    <link href="{{ url('css/flatpickr.min.css') }}" rel="stylesheet">

    <!-- QRious -->
    <script src="{{ url('js/qrious.min.js') }}"></script>
    <!-- AOS JS -->
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

    {{-- Check PWA --}}
    @if ($plan_details != null)
        @if ($plan_details['pwa'] == 1 && $business_card_details->is_enable_pwa == 1)
            @laravelPWA
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

    {{-- Loader --}}
    @if ($introScreen != null)
        <!-- Loader -->
        <div id="loader">
            <div class="spinner"></div>
        </div>
    @endif

    <div id="smooth-wrapper">
        <div id="smooth-content" class="vcard-container">

            {{-- ============================================================
                PASSWORD GUARD
            ============================================================ --}}
            @if ($business_card_details->password == null || Session::get('password_protected') == true)
                {{-- Index Screen --}}
                @if ($introScreen != null)
                    @include("templates.includes.intros.{$introScreen->intro_code}", [
                        'theme' => $business_card_details->theme_id,
                    ])
                @endif

                {{-- ============================================================
                    Content Screen
                ============================================================ --}}
                <div id="content-screen">
                    @if ($business_card_details != null)

                        {{-- ============================================================
                            COVER PHOTO / VIDEO
                        ============================================================ --}}
                        <div class="cover-photo">

                            @if ($business_card_details->cover_type == 'none')
                                <div id="cover-img" class="cover-media-item"
                                    style="background:url('{{ url('img/templates/real-estate/banner.png') }}') center/cover no-repeat;">
                                </div>
                            @endif

                            @if ($business_card_details->cover_type == 'photo')
                                <div id="cover-img" class="cover-media-item"
                                    style="background:url('{{ $business_card_details->cover ? url($business_card_details->cover) : asset('images/default-cover.png') }}') center/cover no-repeat;">
                                </div>
                            @endif

                            @if ($business_card_details->cover_type == 'youtube-ap')
                                <iframe id="cover-yt" class="cover-media-item"
                                    src="https://www.youtube.com/embed/{{ $business_card_details->cover }}?autoplay=1&mute=1&controls=0&loop=1&playlist={{ $business_card_details->cover }}"
                                    allow="autoplay; encrypted-media" frameborder="0"></iframe>
                            @endif

                            @if ($business_card_details->cover_type == 'youtube')
                                <iframe id="cover-yt" class="cover-media-item"
                                    src="https://www.youtube.com/embed/{{ $business_card_details->cover }}?autoplay=0&mute=1&controls=0&loop=1&playlist={{ $business_card_details->cover }}"
                                    allow="autoplay; encrypted-media" frameborder="0"></iframe>
                            @endif

                            @if ($business_card_details->cover_type == 'vimeo-ap')
                                <iframe id="cover-vimeo" class="cover-media-item"
                                    src="https://player.vimeo.com/video/{{ $business_card_details->cover }}?background=1&autoplay=1&loop=1&byline=0&title=0"
                                    allow="autoplay; fullscreen" frameborder="0"></iframe>
                            @endif

                            @if ($business_card_details->cover_type == 'vimeo')
                                <iframe id="cover-vimeo" class="cover-media-item"
                                    src="https://player.vimeo.com/video/{{ $business_card_details->cover }}?autoplay=0&loop=1&controls=1"
                                    allow="autoplay; fullscreen" frameborder="0"></iframe>
                            @endif

                            <div class="cover-overlay"></div>

                        </div>
                        {{-- END COVER --}}

                        {{-- Language Switcher — OUTSIDE .cover-photo so iframe never intercepts touch --}}
                        @if (
                            $business_card_details->is_enable_language_switcher == 1 &&
                                is_array(config('app.languages')) &&
                                count(config('app.languages')) > 1)
                            @include('templates.includes.vcard.real-estate.language-switcher')
                        @endif

                        {{-- ============================================================
                            PROFILE SECTION
                        ============================================================ --}}
                        <div class="profile-section" id="profile">
                            <div class="profile-img-wrap">
                                <div class="profile-img-ring"></div>
                                <img src="{{ url($business_card_details->profile) }}"
                                    alt="{{ $business_card_details->title }}" class="profile-img gsap-scale" />
                                <div class="profile-img-shine"></div>
                            </div>
                            <h1 class="name gsap-slide-up">{{ $business_card_details->title }}</h1>
                            <div class="name-divider gsap-slide-up">
                                <span class="name-divider__line"></span>
                                <span class="name-divider__diamond">◆</span>
                                <span class="name-divider__line"></span>
                            </div>
                            <p class="title-badge gsap-slide-up">{{ $card_details->sub_title }}</p>
                            @if (isset($business_card_details->description))
                                <div class="desc gsap-slide-up">{!! $business_card_details->description !!}</div>
                            @endif
                        </div>

                        {{-- ============================================================
                            QUICK CONTACT ACTIONS
                        ============================================================ --}}
                        @if (count($feature_details) > 0)
                            <div class="quick-actions gsap-fade">
                                @foreach ($feature_details as $feature)
                                    @if (in_array($feature->type, ['tel', 'address', 'wa', 'instagram']))
                                        @if ($feature->type == 'address')
                                            <a href="#location" class="action-btn">
                                                <span class="action-btn__icon"><i
                                                        class="{{ $feature->icon }}"></i></span>
                                                <span class="action-btn__label">{{ __('Location') }}</span>
                                            </a>
                                        @elseif ($feature->type == 'tel')
                                            <a href="tel:{{ $feature->content }}" class="action-btn">
                                                <span class="action-btn__icon"><i
                                                        class="{{ $feature->icon }}"></i></span>
                                                <span class="action-btn__label">{{ __('Call') }}</span>
                                            </a>
                                        @elseif ($feature->type == 'wa')
                                            <a href="https://wa.me/{{ $feature->content }}" target="_blank"
                                                class="action-btn">
                                                <span class="action-btn__icon"><i
                                                        class="{{ $feature->icon }}"></i></span>
                                                <span class="action-btn__label">{{ __('WhatsApp') }}</span>
                                            </a>
                                        @elseif ($feature->type == 'instagram')
                                            <a href="{{ $feature->content }}" target="_blank" class="action-btn">
                                                <span class="action-btn__icon"><i
                                                        class="{{ $feature->icon }}"></i></span>
                                                <span class="action-btn__label">{{ __('Instagram') }}</span>
                                            </a>
                                        @endif
                                    @endif
                                @endforeach
                            </div>
                        @endif

                        {{-- ============================================================
                                ADDRESS CARD
                        ============================================================ --}}
                        @if (count($feature_details) > 0)
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
                        @endif

                        {{-- ============================================================
                                CONTACT FEATURE LINKS
                        ============================================================ --}}
                        @if (!empty($feature_details) && count($feature_details) > 0)
                            @php
                                $excludedTypes = ['tel', 'instagram', 'wa', 'address', 'map', 'iframe', 'youtube'];
                                $validFeatures = collect($feature_details)->filter(
                                    fn($f) => isset($f->type) && !in_array($f->type, $excludedTypes),
                                );
                            @endphp
                            @if ($validFeatures->isNotEmpty())
                                <div class="section gsap-fade">
                                    <div class="section-divider">
                                        <hr><span>{{ __($feature_details[0]->title) }}</span>
                                        <hr>
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
                                                <h2>{{ $feature->label ?? 'N/A' }}</h2>
                                                <p>{{ $feature->content ?? '' }}</p>
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        @endif

                        {{-- ============================================================
                            SERVICES
                        ============================================================ --}}
                        @if (count($service_details) > 0)
                            <div class="section gsap-fade">
                                <i class="fas fa-building decor-icon anim-pulse service-icon"
                                    style="font-size:85px;left:0;top:20px"></i>
                                <div class="section-divider">
                                    <hr><span>{{ __($service_details[0]->title) }}</span>
                                    <hr>
                                </div>
                                <div class="swiper serviceSwiper">
                                    <div class="swiper-wrapper">
                                        @foreach ($service_details as $service_detail)
                                            <div class="swiper-slide">
                                                <div class="realtor-card service-card">
                                                    <div
                                                        style="width:100%;aspect-ratio:16/9;background:url('{{ url($service_detail->service_image) }}') center/cover no-repeat;border-radius:14px 14px 0 0;">
                                                    </div>
                                                    <div class="service-details">
                                                        <h4>{{ $service_detail->service_name }}</h4>
                                                        <p>{{ $service_detail->service_description }}</p>
                                                        @if ($enquiry_button != null && $whatsAppNumberExists == true && $service_detail->enable_enquiry == 'Enabled')
                                                            <a href="https://wa.me/{{ $enquiry_button }}?text={{ __('Hi, I am interested in your product/service:') }} {{ $service_detail->service_name }}. {{ __('Please provide more details.') }}"
                                                                target="_blank"
                                                                class="btn-primary btn-sm service-btn">{{ __('Enquire') }}</a>
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

                        {{-- ============================================================
                            PRODUCTS
                        ============================================================ --}}
                        @if (count($product_details) > 0)
                            <div class="section gsap-fade">
                                <div class="section-divider">
                                    <hr><span>{{ __($product_details[0]->title) }}</span>
                                    <hr>
                                </div>
                                <div class="swiper productSwiper">
                                    <div class="swiper-wrapper">
                                        @foreach ($product_details as $product_detail)
                                            <div class="swiper-slide">
                                                <div class="product-card">
                                                    @if (!empty($product_detail->badge))
                                                        <span
                                                            class="product-badge">{{ $product_detail->badge }}</span>
                                                    @endif
                                                    <img src="{{ url($product_detail->product_image) }}"
                                                        alt="{{ $product_detail->product_name }}" />
                                                    <h4 class="product-name">{{ $product_detail->product_name }}</h4>
                                                    <p class="product-description">
                                                        {{ $product_detail->product_description }}
                                                    </p>
                                                    @if ($product_detail->sales_price != 0)
                                                        <span class="product-price">
                                                            {{ formatCurrencyVcard($product_detail->sales_price, $product_detail->currency) }}
                                                            @if ($product_detail->sales_price != $product_detail->regular_price)
                                                                <span
                                                                    class="product-price-original">{{ formatCurrencyVcard($product_detail->regular_price, $product_detail->currency) }}</span>
                                                            @endif
                                                        </span>
                                                    @endif
                                                    @if ($product_detail->product_status != 'null')
                                                        <p class="product-status"
                                                            style="color:{{ $product_detail->product_status == 'instock' ? '#059669' : '#dc2626' }}">
                                                            {{ $product_detail->product_status == 'outstock' ? __('Out of Stock') : __('In Stock') }}
                                                        </p>
                                                    @endif
                                                    @if ($enquiry_button != null && $whatsAppNumberExists == true)
                                                        <a href="https://wa.me/{{ $enquiry_button }}?text={{ __('Hi, I am interested in your product:') }} {{ $product_detail->product_name }}. {{ __('Please provide more details.') }}"
                                                            target="_blank"
                                                            class="btn-primary product-btn">{{ __('Enquire') }}</a>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                    <div class="swiper-pagination"></div>
                                </div>
                            </div>
                        @endif

                        {{-- ============================================================
                            GALLERY
                        ============================================================ --}}
                        @if (count($galleries_details) > 0)
                            <div class="section gsap-fade">
                                <i class="far fa-images decor-icon anim-pulse gallery-icon"
                                    style="font-size:60px;left:15px;top:10px"></i>
                                <div class="section-divider">
                                    <hr><span>{{ __($galleries_details[0]->title) }}</span>
                                    <hr>
                                </div>
                                <div class="swiper gallerySwiper">
                                    <div class="swiper-wrapper">
                                        @foreach ($galleries_details as $galleries_detail)
                                            <div class="swiper-slide">
                                                <div class="item-card gallery-card">
                                                    <img src="{{ url($galleries_detail->gallery_image) }}"
                                                        alt="{{ $galleries_detail->caption }}" class="gallery-img" />
                                                    @if ($galleries_detail->caption)
                                                        <p class="gallery-caption">{{ $galleries_detail->caption }}
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

                        {{-- ============================================================
                            YOUTUBE VIDEOS
                        ============================================================ --}}
                        @if ($feature_details->where('type', 'youtube')->count() > 0)
                            <div class="section gsap-fade">
                                <i class="fas fa-video decor-icon anim-glide video-icon"
                                    style="font-size:70px;right:20px;top:10px"></i>
                                <div class="section-divider">
                                    <hr><span>{{ __('Virtual Tours') }}</span>
                                    <hr>
                                </div>
                                <div class="swiper videoSwiper">
                                    <div class="swiper-wrapper">
                                        @foreach ($feature_details as $feature)
                                            @if ($feature->type == 'youtube')
                                                <div class="swiper-slide">
                                                    <div class="item-card video-card">
                                                        @if ($feature->label)
                                                            <div class="youtube-label">
                                                                <p class="youtube-label-text">{{ $feature->label }}
                                                                </p>
                                                            </div>
                                                        @endif
                                                        <iframe
                                                            src="https://www.youtube.com/embed/{{ $feature->content }}"
                                                            title="{{ $feature->label }}" frameborder="0"
                                                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                                            allowfullscreen class="youtube-iframe"></iframe>
                                                    </div>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                    <div class="swiper-pagination"></div>
                                </div>
                            </div>
                        @endif

                        {{-- ============================================================
                            IFRAMES
                        ============================================================ --}}
                        @if ($feature_details->where('type', 'iframe')->count() > 0)
                            <div class="section gsap-fade">
                                <div class="section-divider">
                                    <hr><span>{{ __('Iframe') }}</span>
                                    <hr>
                                </div>
                                @foreach ($feature_details as $feature)
                                    @if ($feature->type == 'iframe')
                                        <div class="iframe-wrapper">
                                            <iframe src="{{ $feature->content }}" title="{{ $feature->label }}"
                                                frameborder="0" width="100%" height="270"
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

                        {{-- ============================================================
                            APPOINTMENT BOOKING
                        ============================================================ --}}
                        @if ($appointmentEnabled == true && isset($plan_details['appointment']) && $plan_details['appointment'] == 1)
                            @if ($appointment_slots != null)
                                <div class="section gsap-fade" id="booking-section">
                                    <i class="far fa-calendar-check decor-icon anim-glide"
                                        style="font-size:75px;right:10px;top:30px"></i>
                                    <div class="section-divider">
                                        <hr><span>{{ __(json_decode($appointment_slots, true)['title']) }}</span>
                                        <hr>
                                    </div>
                                    <div class="realtor-card" style="padding:24px">
                                        <div id="errorMessage" class="alert-error hidden">
                                            {{ __('Please select a valid date and time slot.') }}</div>
                                        <div id="successMessage" class="alert-success hidden">
                                            {{ __('Appointment booked successfully!') }}</div>
                                        <div id="errorSubmitMessage" class="alert-error hidden">
                                            {{ __('Please fill all the fields.') }}</div>
                                        <div style="display:flex;gap:12px;margin-bottom:16px;flex-wrap:wrap;">
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
                                            onclick="validateAndShowModal()">
                                            {{ __('Book Appointment') }}
                                        </button>
                                    </div>
                                </div>
                            @endif
                        @endif

                        {{-- ============================================================
                            BUSINESS HOURS
                        ============================================================ --}}
                        @if ($plan_details['business_hours'] == 1)
                            @if ($business_hours != null && $business_hours->is_display != 0)
                                <div class="section gsap-fade">
                                    <i class="far fa-clock decor-icon anim-pulse"
                                        style="font-size:70px;left:10px;top:20px"></i>
                                    <div class="section-divider">
                                        <hr><span>{{ __($business_hours->title) }}</span>
                                        <hr>
                                    </div>
                                    <div class="realtor-card" style="padding:20px 24px">
                                        @if ($business_hours->is_always_open != 'Opening')
                                            @foreach (['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as $day)
                                                <div class="hour-row">
                                                    <span>{{ __($day) }}</span>
                                                    <span>{{ __($business_hours->$day ?: __('Closed')) }}</span>
                                                </div>
                                            @endforeach
                                        @else
                                            <div style="display:flex;align-items:center;gap:16px;padding:8px 0">
                                                <div
                                                    style="width:44px;height:44px;border-radius:50%;background:var(--gold-pale);display:flex;align-items:center;justify-content:center;border:1px solid var(--gold-mid)">
                                                    <i class="fas fa-clock"
                                                        style="color:var(--gold);font-size:18px"></i>
                                                </div>
                                                <div>
                                                    <p
                                                        style="font-weight:700;font-size:16px;font-family:'Playfair Display',serif">
                                                        {{ __('Always Open') }}</p>
                                                    <p style="font-size:13px;color:var(--text-muted);margin-top:2px">
                                                        {{ __("We're available 24/7 to serve you!") }}</p>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        @endif

                        {{-- ============================================================
                            SERVICE BOOKING
                        ============================================================ --}}
                        @if (isset($plan_details['service_booking']) && $plan_details['service_booking'] == 1)
                            @if (isset($service_booking_details) && $service_booking_details->service_booking == 1)
                                <div class="section gsap-fade">
                                    <div class="section-divider">
                                        <hr><span>{{ __($service_booking_details->title) }}</span>
                                        <hr>
                                    </div>
                                    <div class="realtor-card service-booking-card">
                                        <div id="errorMessage1" class="alert-error hidden"></div>
                                        <div id="successMessage1" class="alert-success hidden"></div>
                                        <div class="service-booking-form">
                                            <div>
                                                <label class="form-label">{{ __('Name') }}</label>
                                                <input type="text" name="customer_name" id="customer_name"
                                                    placeholder="{{ __('Your Name') }}" class="form-control" />
                                            </div>
                                            <div>
                                                <label class="form-label">{{ __('Email') }}</label>
                                                <input type="email" name="customer_email" id="customer_email"
                                                    placeholder="{{ __('Your Email') }}" class="form-control" />
                                            </div>
                                            <div>
                                                <label class="form-label">{{ __('Mobile') }}</label>
                                                <input type="tel" name="customer_phone" id="customer_phone"
                                                    placeholder="{{ __('Your Mobile') }}" class="form-control" />
                                            </div>
                                            <div>
                                                <label class="form-label">{{ __('Persons') }}</label>
                                                <input type="number" name="no_of_persons" id="no_of_persons"
                                                    value="1" class="form-control" />
                                            </div>
                                        </div>
                                        <label class="form-label">{{ __('Address') }}</label>
                                        <textarea name="customer_address" id="customer_address" placeholder="{{ __('Your Address') }}"
                                            class="form-control" rows="3"></textarea>
                                        <label class="form-label">{{ __('Notes') }}</label>
                                        <textarea name="customer_notes" id="customer_notes" placeholder="{{ __('Your Message') }}" class="form-control"
                                            rows="3"></textarea>
                                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                                            <div>
                                                <label class="form-label">{{ __('Start Date') }}</label>
                                                <input type="text" id="service_start_date"
                                                    name="service_start_date" class="form-control" />
                                            </div>
                                            <div>
                                                <label class="form-label">{{ __('Start Time') }}</label>
                                                <input type="time" name="service_start_time"
                                                    id="service_start_time" class="form-control timepicker" />
                                            </div>
                                            <div>
                                                <label class="form-label">{{ __('End Date') }}</label>
                                                <input type="date" id="service_end_date" name="service_end_date"
                                                    class="form-control" />
                                            </div>
                                            <div>
                                                <label class="form-label">{{ __('End Time') }}</label>
                                                <input type="time" name="service_end_time" id="service_end_time"
                                                    class="form-control timepicker" />
                                            </div>
                                        </div>
                                        <button onclick="submitServiceBooking()" class="btn-primary"
                                            style="margin-top:8px">{{ __('Submit') }}</button>
                                    </div>
                                </div>
                            @endif
                        @endif

                        {{-- ============================================================
                            PAYMENT LINKS
                        ============================================================ --}}
                        @if (count($payment_details) > 0)
                            <div class="section gsap-fade">
                                <i class="fas fa-file-invoice-dollar decor-icon anim-pulse"
                                    style="font-size:60px;left:-5px;top:10px"></i>
                                <div class="section-divider">
                                    <hr><span>{{ __($payment_details[0]->title) }}</span>
                                    <hr>
                                </div>
                                <div class="grid-2">
                                    @foreach ($payment_details as $payment)
                                        <div class="realtor-card"
                                            style="padding:16px;display:flex;flex-direction:column;gap:10px">
                                            @include('templates.partials.real-estate-payment-link-image')
                                            @if ($payment->type == 'url')
                                                <a href="https://{{ str_replace('https://', '', $payment->content) }}"
                                                    target="_blank"
                                                    class="btn-primary btn-sm">{{ $payment->label }}</a>
                                            @elseif ($payment->type == 'upi')
                                                <a href="upi://pay?pa={{ $payment->content }}&pn={{ urlencode($payment->label) }}&am=1&cu=INR"
                                                    target="_blank"
                                                    class="btn-primary btn-sm">{{ $payment->label }}</a>
                                            @elseif ($payment->type == 'text')
                                                <h4 style="font-size:14px;font-family:'Playfair Display',serif">
                                                    {{ $payment->label }}</h4>
                                                <p style="font-size:13px;color:var(--text-muted)">
                                                    @foreach (explode('.', $payment->content) as $sentence)
                                                        @if (trim($sentence))
                                                            {{ trim($sentence) }}<br>
                                                        @endif
                                                    @endforeach
                                                </p>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        {{-- ============================================================
                            TESTIMONIALS
                        ============================================================ --}}
                        @if (count($testimonials) > 0)
                            <div class="section gsap-fade">
                                <i class="far fa-comments decor-icon anim-pulse"
                                    style="font-size:70px;left:10px;top:-10px"></i>
                                <div class="section-divider">
                                    <hr><span>{{ __($testimonials[0]->title) }}</span>
                                    <hr>
                                </div>
                                <div class="swiper testimonialSwiper">
                                    <div class="swiper-wrapper">
                                        @foreach ($testimonials as $testimonial)
                                            <div class="swiper-slide">
                                                <div class="testimonial-card"
                                                    style="text-align:center;padding:30px 24px">
                                                    <i class="fas fa-quote-left"
                                                        style="color:var(--gold);opacity:0.25;font-size:38px;margin-bottom:12px;display:block"></i>
                                                    <p
                                                        style="font-style:italic;font-size:15px;color:var(--text-body);margin:12px 0;line-height:1.8;font-family:'Playfair Display',serif">
                                                        "{{ $testimonial->review }}"</p>
                                                    <img src="{{ url($testimonial->reviewer_image) }}"
                                                        alt="{{ $testimonial->reviewer_name }}"
                                                        style="width:52px;height:52px;border-radius:50%;object-fit:cover;margin:16px auto 8px;display:block;border:2px solid var(--gold-mid)" />
                                                    <strong
                                                        style="font-size:14px;font-weight:700;font-family:'Jost',sans-serif">{{ $testimonial->reviewer_name }}</strong>
                                                    @if ($testimonial->review_subtext)
                                                        <p
                                                            style="font-size:12px;color:var(--gold);margin-top:3px;font-family:'Jost',sans-serif;letter-spacing:0.5px">
                                                            {{ $testimonial->review_subtext }}</p>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                    <div class="swiper-pagination"></div>
                                </div>
                            </div>
                        @endif

                        {{-- ============================================================
                            GOOGLE MAPS
                        ============================================================ --}}
                        @if (count($feature_details) > 0 && $feature_details->contains('type', 'map'))
                            <div class="section gsap-fade" id="location">
                                <i class="fas fa-map-marked-alt decor-icon anim-glide"
                                    style="font-size:70px;right:-10px;top:-10px"></i>
                                <div class="section-divider">
                                    <hr><span>{{ __('Location') }}</span>
                                    <hr>
                                </div>
                                @foreach ($feature_details as $feature)
                                    @if ($feature->type == 'map')
                                        <div class="map-wrapper">
                                            <iframe src="https://www.google.com/maps/embed?{!! $feature->content !!}"
                                                width="100%" height="260" style="border:0;display:block"
                                                allowfullscreen loading="lazy"></iframe>
                                            @if ($feature->label)
                                                <div class="map-label">{{ $feature->label }}</div>
                                            @endif
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        @endif

                        {{-- ============================================================
                            GOOGLE WALLET
                        ============================================================ --}}
                        @if (is_dir(base_path('plugins/GoogleWallet')))
                            @if (isset($plan_details['google_wallet']) &&
                                    $plan_details['google_wallet'] == 1 &&
                                    $business_card_details->is_google_wallet_hidden == 0)
                                <div class="section gsap-fade">
                                    <div class="section-divider">
                                        <hr><span>{{ __('Google Wallet') }}</span>
                                        <hr>
                                    </div>
                                    <div class="wallet-section">
                                        @if ($google_wallet_details->wallet_description != null)
                                            <div
                                                style="font-size:14px;color:var(--text-muted);margin-bottom:16px;font-family:'Jost',sans-serif">
                                                {!! $google_wallet_details->wallet_description !!}</div>
                                        @endif
                                        @if ($google_wallet_details->wallet_link != null)
                                            <a href="{{ $google_wallet_details->wallet_link }}" target="_blank"
                                                rel="noopener noreferrer"
                                                style="display:block;max-width:260px;margin:0 auto">
                                                <img src="{{ url()->to('/') . '/img/google-wallet-btn.png' }}"
                                                    alt="" style="width:100%;object-fit:cover" />
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        @endif

                        {{-- ============================================================
                            CONTACT FORM
                        ============================================================ --}}
                        @if ($plan_details['contact_form'] == 1)
                            @if ($business_card_details->enquiry_email != null)
                                <div class="section gsap-fade" id="contact-section">
                                    <i class="far fa-paper-plane decor-icon anim-glide"
                                        style="font-size:65px;right:-5px;top:10px"></i>
                                    <div class="section-divider">
                                        <hr><span>{{ __($business_card_details->contact_form_title) }}</span>
                                        <hr>
                                    </div>
                                    @if (Session::has('message'))
                                        <div class="alert-success">{{ Session::get('message') }}</div>
                                    @endif
                                    <form class="realtor-card" style="padding:24px"
                                        action="{{ config('app.url') }}/sent-enquiry" method="POST">
                                        @csrf
                                        <input type="hidden" name="card_id"
                                            value="{{ $business_card_details->card_id }}" />
                                        <label class="form-label">{{ __('Name') }}</label>
                                        <input type="text" name="name" placeholder="{{ __('Your Name') }}"
                                            class="form-control" required />
                                        <label class="form-label">{{ __('Email') }}</label>
                                        <input type="email" name="email" placeholder="{{ __('Your Email') }}"
                                            class="form-control" required />
                                        <label class="form-label">{{ __('Mobile Number') }}</label>
                                        <input type="tel" name="phone"
                                            placeholder="{{ __('Your Mobile Number') }}" class="form-control"
                                            required />
                                        <label class="form-label">{{ __('Message') }}</label>
                                        <textarea name="message" placeholder="{{ __('Your Message') }}" class="form-control" rows="4" required></textarea>
                                        @include('templates.includes.recaptcha', [
                                            'recaptchaId' => 'recaptcha-one',
                                        ])
                                        <button type="submit"
                                            class="btn-primary btn-outline">{{ __('Send') }}</button>
                                    </form>
                                </div>
                            @endif
                        @endif

                        {{-- ============================================================
                            BRANDING FOOTER
                        ============================================================ --}}
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

                    @endif {{-- end business_card_details --}}
                </div>

                {{-- ============================================================
                    FLOATING BOTTOM NAVIGATION
                ============================================================ --}}
                <div class="bottom-nav gsap-nav">
                    <button class="nav-item active" onclick="window.scrollTo(0,0)">
                        <i class="far fa-user-circle"></i>
                    </button>
                    <button class="nav-item"
                        onclick="document.getElementById('booking-section')?.scrollIntoView({behavior:'smooth'})">
                        <i class="far fa-calendar-check"></i>
                    </button>
                    <a href="{{ config('app.url') }}/download/{{ $business_card_details->card_id }}"
                        class="nav-item">
                        <i class="fas fa-user-plus"></i>
                    </a>
                    <button class="nav-item" onclick="toggleScanModal(true)">
                        <i class="fas fa-qrcode"></i>
                    </button>
                    <button class="nav-item" onclick="toggleWhatsAppModal(true)">
                        <i class="fab fa-whatsapp"></i>
                    </button>
                    <button class="nav-item" onclick="shareToggleModal(true)">
                        <i class="fas fa-share-alt"></i>
                    </button>
                </div>
            @endif {{-- end password check --}}

            {{-- ================================================================
                APPOINTMENT MODAL
            ================================================================ --}}
            <div id="appointmentModal" class="std-modal hidden">
                <div class="std-modal-box">
                    <h2
                        style="text-align:center;font-family:'Playfair Display',serif;font-size:22px;font-weight:700;margin-bottom:16px;">
                        {{ __('Book Appointment') }}</h2>
                    <form id="appointmentForm">
                        <label class="form-label">{{ __('Name') }}</label>
                        <input type="text" id="name" class="form-control" required />
                        <label class="form-label">{{ __('Email') }}</label>
                        <input type="email" id="email" class="form-control" required />
                        <label class="form-label">{{ __('Phone') }}</label>
                        <input type="text" id="phone" class="form-control" required />
                        <label class="form-label">{{ __('Notes') }}</label>
                        <textarea id="notes" class="form-control" rows="3"></textarea>
                        <div class="hidden"><input type="text" id="price" class="form-control" disabled />
                        </div>
                        @include('templates.includes.recaptcha', ['recaptchaId' => 'recaptcha-two'])
                        <div class="std-modal-footer">
                            <button type="button" class="btn-cancel"
                                onclick="closeAppointmentModal()">{{ __('Close') }}</button>
                            <button type="submit" id="bookAppointmentButton"
                                class="btn-confirm">{{ __('Submit') }}</button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- ================================================================
            SHARE MODAL
            ================================================================ --}}
            {{-- ================================================================
            SHARE MODAL — Real Estate Revamp Design
            Warm Ivory + Deep Slate + Antique Gold
            Fonts: Playfair Display + Jost
            ================================================================ --}}
            <div id="shareModal" class="hidden" onclick="shareToggleModal(false)">
                <div class="share-modal-box" onclick="event.stopPropagation()">

                    {{-- Title --}}
                    <h2 class="share-title">{{ __('Share') }}</h2>

                    {{-- Gold divider --}}
                    <div class="share-divider">
                        <span class="share-divider__line"></span>
                        <span class="share-divider__diamond">◆</span>
                        <span class="share-divider__line"></span>
                    </div>

                    {{-- QR Code --}}
                    <div class="share-qr">
                        <div class="share-qr-wrap">
                            <canvas id="shareQrCode"></canvas>
                        </div>
                    </div>

                    {{-- Social icons label --}}
                    <p class="share-section-label">{{ __('Share on') }}</p>

                    {{-- Social Icons ── --}}
                    <div class="share-icons">
                        <a href="{{ $shareComponent['facebook'] }}" target="_blank" title="Facebook">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="{{ $shareComponent['twitter'] }}" target="_blank" title="Twitter">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="{{ $shareComponent['linkedin'] }}" target="_blank" title="LinkedIn">
                            <i class="fab fa-linkedin-in"></i>
                        </a>
                        <a href="{{ $shareComponent['whatsapp'] }}" target="_blank" title="WhatsApp">
                            <i class="fab fa-whatsapp"></i>
                        </a>
                        <a href="{{ $shareComponent['telegram'] }}" target="_blank" title="Telegram">
                            <i class="fab fa-telegram-plane"></i>
                        </a>
                    </div>

                    {{-- Copy Link Button --}}
                    <button onclick="copyLink()" class="share-copy-btn">
                        <i class="fas fa-link"></i>
                        <span>{{ __('Copy Link') }}</span>
                    </button>

                </div>
            </div>

            {{-- ================================================================
                WHATSAPP MODAL
            ================================================================ --}}

            <div id="whatsappModal" class="hidden" onclick="toggleWhatsAppModal(false)">
                <div class="whatsapp-modal-box" onclick="event.stopPropagation()">

                    {{-- Close button --}}
                    <button class="whatsapp-modal-close" onclick="toggleWhatsAppModal(false)">
                        <i class="fas fa-times"></i>
                    </button>

                    {{-- WhatsApp icon badge --}}
                    <div class="whatsapp-icon-wrap">
                        <i class="fab fa-whatsapp"></i>
                    </div>

                    {{-- Title --}}
                    <h3 class="whatsapp-modal-title">{{ __('Send via WhatsApp') }}</h3>

                    {{-- Gold divider --}}
                    <div class="whatsapp-divider">
                        <span class="whatsapp-divider__line"></span>
                        <span class="whatsapp-divider__diamond">◆</span>
                        <span class="whatsapp-divider__line"></span>
                    </div>

                    {{-- Label --}}
                    <label class="whatsapp-modal-label" for="whatsappNumber">
                        {{ __('WhatsApp Number') }}
                    </label>

                    {{-- Input with icon prefix --}}
                    <div class="whatsapp-input-wrap">
                        <div class="whatsapp-input-prefix">
                            <i class="fab fa-whatsapp"></i>
                        </div>
                        <input type="text" id="whatsappNumber" placeholder="{{ __('e.g., +919876543210') }}"
                            autocomplete="tel" />
                    </div>

                    {{-- Send button --}}
                    <button onclick="sendMessage()" class="whatsapp-send-btn">
                        <i class="fab fa-whatsapp"></i>
                        <span>{{ __('Send Message') }}</span>
                    </button>

                    {{-- Helper note --}}
                    <p class="whatsapp-helper">
                        <i class="fas fa-lock"></i>
                        {{ __('Include country code. e.g., +1, +91, +44') }}
                    </p>

                </div>
            </div>

            {{-- ================================================================
                QR SCAN MODAL
            ================================================================ --}}
            <div id="scanModal" class="hidden" onclick="toggleScanModal(false)">
                <div class="qr-modal-box" onclick="event.stopPropagation()">

                    {{-- Close button --}}
                    <button class="qr-modal-close" onclick="toggleScanModal(false)">
                        <i class="fas fa-times"></i>
                    </button>

                    {{-- Title --}}
                    <h3 class="qr-modal-title">{{ __('Scan QR Code') }}</h3>

                    {{-- Gold divider --}}
                    <div class="qr-divider">
                        <span class="qr-divider__line"></span>
                        <span class="qr-divider__diamond">◆</span>
                        <span class="qr-divider__line"></span>
                    </div>

                    {{-- QR card with corner brackets --}}
                    <div class="qr-card">
                        <div class="qr-code qr-wrapper"></div>
                        <p class="qr-hint">
                            <i class="fas fa-qrcode"></i>
                            {{ __('Point your camera to scan') }}
                        </p>
                    </div>

                    {{-- Download button --}}
                    <button id="download"
                        onclick="downloadQrStyled(this, '{{ config('app.url') . route('dynamic.card', $business_card_details->card_id, false) }}', 500)"
                        class="qr-download-btn">
                        <i class="fas fa-download"></i>
                        <span>{{ __('Download QR') }}</span>
                    </button>

                    {{-- Helper note --}}
                    <p class="qr-helper">
                        <i class="fas fa-share-alt"></i>
                        {{ __('Share this QR to let others save your contact instantly') }}
                    </p>

                </div>
            </div>

            {{-- ================================================================
                PASSWORD PROTECTED MODAL
            ================================================================ --}}
            @if ($business_card_details->password != null && Session::get('password_protected') == false)
                <div class="pw-modal">
                    <div class="pw-modal-box">
                        <h2
                            style="font-family:'Playfair Display',serif;font-size:24px;font-weight:700;margin-bottom:20px;border-bottom:1px solid var(--border);padding-bottom:14px">
                            {{ __('Password Protected') }}</h2>
                        <form action="{{ config('app.url') }}/check-password/{{ $business_card_details->card_id }}"
                            method="post">
                            @csrf
                            <p style="font-size:14px;color:var(--text-muted);margin-bottom:14px">
                                {{ __('Enter your vcard Password') }}</p>
                            <input type="password" name="password" class="form-control"
                                placeholder="{{ __('Password') }}" required autofocus />
                            @if (Session::has('message'))
                                <div class="alert-error">{{ Session::get('message') }}</div>
                            @endif
                            <button type="submit" class="btn-primary"
                                style="margin-top:8px">{{ __('Unlock') }}</button>
                        </form>
                    </div>
                </div>
            @else
                {{-- ================================================================
                PWA MODAL
            ================================================================ --}}
                @if ($plan_details != null)
                    @if ($plan_details['pwa'] == 1 && $business_card_details->is_enable_pwa == 1)
                        @include('vendor.laravelpwa.real-estate-vcard')
                    @endif
                @endif

                {{-- ================================================================
                NEWSLETTER MODAL (inline — from newsletter_modal.blade.php)
            ================================================================ --}}
                @if ($business_card_details != null)
                    @if (!empty($business_card_details->is_newsletter_pop_active) && $business_card_details->is_newsletter_pop_active == 1)
                        @include('templates.includes.vcard.real-estate.newsletter_modal')
                    @endif
                @endif

                {{-- ================================================================
                    INFORMATION POPUP MODAL (inline — from information_popup_modal.blade.php)
                ================================================================ --}}
                @if ($business_card_details != null)
                    @if (!empty($business_card_details->is_info_pop_active) && $business_card_details->is_info_pop_active == 1)
                        @include('templates.includes.vcard.real-estate.information_popup_modal', [
                            'introScreen' => $introScreen,
                        ])
                    @endif
                @endif
            @endif
        </div>
    </div>

    {{-- ================================================================
         SCRIPTS
    ================================================================ --}}
    <script src="{{ url('js/jquery.min.js') }}"></script>
    <script src="{{ url('js/smooth-scroll.polyfills.min.js') }}"></script>
    <script type="text/javascript" src="{{ url('app/js/footer.js') }}"></script>
    <script src="{{ url('js/flatpickr.min.js') }}"></script>
    <script src="{{ url('js/swiper-bundle.min.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/ScrollTrigger.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/l10n/{{ app()->getLocale() }}.js"></script>
    @yield('custom-js')

    <script>
        "use strict";

        // ── Year ──
        @if ($business_card_details->password == null && Session::get('password_protected') == true)
            document.getElementById('year').textContent = ' ' + new Date().getFullYear();
        @endif

        // ── Theme changer ──
        function changeTheme(r, g, b) {
            document.documentElement.style.setProperty('--primary-rgb', `${r}, ${g}, ${b}`);
            document.querySelector('meta[name="theme-color"]').setAttribute('content', `rgb(${r},${g},${b})`);
        }

        // ── Swiper init ──
        document.addEventListener('DOMContentLoaded', function() {
            new Swiper('.serviceSwiper', {
                slidesPerView: 1,
                spaceBetween: 15,
                loop: true,
                autoplay: {
                    delay: 5000
                },
                observer: true,
                observeParents: true,
                pagination: {
                    el: '.serviceSwiper .swiper-pagination',
                    clickable: true
                }
            });
            new Swiper('.productSwiper', {
                slidesPerView: 1,
                spaceBetween: 15,
                loop: true,
                autoplay: {
                    delay: 5000
                },
                observer: true,
                observeParents: true,
                pagination: {
                    el: '.productSwiper .swiper-pagination',
                    clickable: true
                }
            });
            new Swiper('.gallerySwiper', {
                slidesPerView: 1,
                spaceBetween: 15,
                loop: true,
                autoplay: {
                    delay: 5000
                },
                observer: true,
                observeParents: true,
                pagination: {
                    el: '.gallerySwiper .swiper-pagination',
                    clickable: true
                }
            });
            new Swiper('.videoSwiper', {
                slidesPerView: 1,
                spaceBetween: 15,
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
                    delay: 5000
                },
                observer: true,
                observeParents: true,
                pagination: {
                    el: '.testSwiper .swiper-pagination',
                    clickable: true
                }
            });
        });

        // ── GSAP Animations ──
        gsap.registerPlugin(ScrollTrigger);
        gsap.set('.gsap-fade, .gsap-scale, .gsap-slide-up, .gsap-nav', {
            autoAlpha: 1
        });
        gsap.from('.gsap-scale', {
            scale: 0.8,
            opacity: 0,
            duration: 1,
            ease: 'power2.out'
        });
        gsap.from('.gsap-slide-up', {
            y: 20,
            opacity: 0,
            duration: 0.8,
            stagger: 0.1,
            ease: 'power2.out',
            delay: 0.2
        });
        if (window.innerWidth < 769) {
            gsap.from('.gsap-nav', {
                y: 50,
                opacity: 0,
                duration: 0.8,
                ease: 'power2.out',
                delay: 0.8
            });
        }
        gsap.utils.toArray('.gsap-fade').forEach(section => {
            gsap.from(section, {
                scrollTrigger: {
                    trigger: section,
                    start: 'top 90%'
                },
                y: 30,
                opacity: 0,
                duration: 0.8,
                ease: 'power2.out'
            });
        });
        ScrollTrigger.refresh();

        // ── AOS ──
        AOS.init({
            duration: 1000,
            once: true
        });

        // ── Modal helpers ──
        function shareToggleModal(show) {
            document.getElementById('shareModal').classList.toggle('hidden', !show);
        }

        function toggleScanModal(show) {
            document.getElementById('scanModal').classList.toggle('hidden', !show);
        }

        function toggleWhatsAppModal(show) {
            document.getElementById('whatsappModal').classList.toggle('hidden', !show);
        }

        function copyLink() {
            navigator.clipboard.writeText(
                `{{ config('app.url') . route('dynamic.card', $business_card_details->card_id, false) }}`);
            alert("{{ __('Link copied to clipboard!') }}");
        }

        function sendMessage() {
            const phoneNumber = document.getElementById('whatsappNumber').value.trim();
            if (phoneNumber) {
                const message = `{{ $shareContent }}`;
                window.open(`https://wa.me/${phoneNumber}?text=${message}`, '_blank');
                toggleWhatsAppModal(false);
                document.getElementById('whatsappNumber').value = '';
            } else {
                alert(`{{ __('Please enter a valid WhatsApp number.') }}`);
            }
        }

        // ── QR Code ──
        const qr = new QRious({
            element: document.getElementById('shareQrCode'),
            value: `{{ config('app.url') . route('dynamic.card', $business_card_details->card_id, false) }}`,
            size: 200,
            background: 'white',
            foreground: 'black',
            level: 'H'
        });
        window.onload = function() {
            updateQr(`{{ config('app.url') . route('dynamic.card', $business_card_details->card_id, false) }}`);
        };

        // ── Nav active state ──
        document.querySelectorAll('.nav-item').forEach(item => {
            item.addEventListener('click', function() {
                document.querySelectorAll('.nav-item').forEach(n => n.classList.remove('active'));
                this.classList.add('active');
            });
        });

        // ── Appointment ──
        function validateAndShowModal() {
            const date = document.getElementById('appointment-date')?.value;
            const slot = document.getElementById('time-slot-select')?.value;
            const errEl = document.getElementById('errorMessage');
            if (date && slot) {
                document.getElementById('appointmentModal').classList.remove('hidden');
                errEl?.classList.add('hidden');
            } else {
                errEl?.classList.remove('hidden');
            }
        }

        // Appointment modal close button
        function closeAppointmentModal() {
            document.getElementById('appointmentModal')?.classList.add('hidden');
        }

        function toggleModal() {
            document.getElementById('appointmentModal').classList.toggle('hidden');
        }

        function onloadCallback() {
            window.recaptchaWidgets = window.recaptchaWidgets || {};
            if (typeof grecaptcha !== 'undefined' && typeof grecaptcha.render === 'function') {
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
        document.getElementById('appointmentForm')?.addEventListener('submit', function(event) {
            event.preventDefault();
            const button = document.getElementById('bookAppointmentButton');
            const errorSubmitMessage = document.getElementById('errorMessage');
            const successMessage = document.getElementById('successMessage');
            button.disabled = true;
            button.innerHTML =
                `{{ __('Booking...') }}`;
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
                .then(async response => {
                    const data = await response.json();
                    if (response.ok) {
                        ['name', 'email', 'phone', 'notes', 'appointment-date', 'time-slot-select', 'price']
                        .forEach(id => {
                            const el = document.getElementById(id);
                            if (el) el.value = '';
                        });
                        generateOption("", "");
                        successMessage?.classList.remove('hidden');
                        errorSubmitMessage?.classList.add('hidden');
                        @if (env('RECAPTCHA_ENABLE') == 'on')
                            grecaptcha.reset(window.recaptchaWidgets[
                                'recaptcha-one']);
                        @endif
                        toggleModal();
                        if (data.success && data.whatsapp_url && data.whatsapp_url !== '#') {
                            setTimeout(() => {
                                window.location.href = data.whatsapp_url;
                            }, 3000);
                        }
                    } else {
                        successMessage?.classList.add('hidden');
                        errorSubmitMessage?.classList.remove('hidden');
                        errorSubmitMessage.innerHTML = data.message || 'Something went wrong';
                        toggleModal();
                    }
                    button.disabled = false;
                    button.innerHTML = `{{ __('Book Appointment') }}`;
                })
                .catch(() => {
                    toggleModal();
                    button.disabled = false;
                    button.innerHTML = `{{ __('Book Appointment') }}`;
                });
        });

        // ── Time Slots ──
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
                        `<option value="">{{ __('Select a time slot') }}`;
                    JSON.parse(data.available_time_slots).forEach(ts => {
                        document.getElementById('time-slot-select').innerHTML +=
                            `<option value="${ts}">${ts}</option>`;
                    });
                    document.getElementById('price').value = data.price;
                }
            });
        }

        // ── Service Booking ──
        function submitServiceBooking() {
            const fields = ['customer_name', 'customer_email', 'customer_phone', 'no_of_persons', 'customer_address',
                'service_start_date', 'service_start_time', 'service_end_date', 'service_end_time'
            ];
            const errEl = document.getElementById('errorMessage1');
            const succEl = document.getElementById('successMessage1');
            errEl.classList.add('hidden');
            succEl.classList.add('hidden');
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
                    succEl.classList.remove('hidden');
                    succEl.innerHTML = data.message || 'Booked successfully!';
                } else {
                    errEl.classList.remove('hidden');
                    errEl.innerHTML = data.message || 'Something went wrong';
                }
            }).catch(() => {
                errEl.classList.remove('hidden');
                errEl.innerHTML = 'Something went wrong';
            });
        }

        // ── Flatpickr ──
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

        // ── Smooth Scroll ──
        const scroll = new SmoothScroll('a[href*="#"]', {
            speed: 300,
            offset: 50,
            easing: 'easeInOutCubic'
        });

        // ── Information Popup Modal ──
        function openInfoModal() {
            const overlay = document.getElementById('customInfoOverlay');
            if (overlay) {
                overlay.classList.remove('hidden');
                void overlay.offsetWidth;
                overlay.classList.add('is-active');
                if (typeof triggerInfoConfetti === 'function') setTimeout(triggerInfoConfetti, 300);
            }
        }

        function closeInfoModal() {
            const overlay = document.getElementById('customInfoOverlay');
            if (overlay) {
                overlay.classList.remove('is-active');
                setTimeout(() => overlay.classList.add('hidden'), 300);
            }
        }
        document.addEventListener('DOMContentLoaded', function() {
            if (document.getElementById('customInfoOverlay')) setTimeout(openInfoModal, 1000);
        });

        // ── Newsletter Modal ──
        function openNewsModal() {
            document.getElementById('newsletterModal')?.classList.add('is-active');
        }

        function closeNewsModal() {
            document.getElementById('newsletterModal')?.classList.remove('is-active');
        }
        document.addEventListener('DOMContentLoaded', function() {
            if (document.getElementById('newsletterModal')) setTimeout(openNewsModal, 1500);
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
