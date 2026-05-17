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
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
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

    <meta name="theme-color" content="#C9A84C" />
    <meta name="application-name" content="{{ $card_details->title }}">
    <meta name="apple-mobile-web-app-title" content="{{ $card_details->title }}">
    <meta name="msapplication-TileColor" content="#0D1117">
    <meta name="msapplication-TileImage" content="{{ url($business_card_details->profile) }}">

    {!! SEOMeta::generate() !!}
    {!! OpenGraph::generate() !!}
    {!! Twitter::generate() !!}
    {!! JsonLd::generate() !!}

    {{-- Construction Theme CSS --}}
    <link rel="stylesheet" href="{{ asset('templates/css/construction-vcard.css') }}">

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


    <!-- Google Fonts: Cormorant Garamond (Luxury Display) + DM Sans (Clean Body) -->
    <link
        href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;0,700;1,400;1,600&family=DM+Sans:wght@300;400;500;600;700&family=Barlow+Condensed:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">

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
    <div class="desktop-bg hidden lg:flex"></div>

    {{-- Loader --}}
    @if ($introScreen != null)
        <!-- Loader -->
        <div id="loader">
            <div class="spinner"></div>
        </div>
    @endif

    <div id="smooth-wrapper">
        <!-- MAIN VCARD -->
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

                <div id="content-screen">               
                    @if ($business_card_details != null)

                        <!-- COVER HEADER -->
                        <div class="cover-wrap">
                            @if ($business_card_details->cover_type == 'none' || $business_card_details->cover_type == 'photo')
                                <div id="cover-img" class="cover-media-item active"
                                    style="background:url('{{ $business_card_details->cover ? url($business_card_details->cover) : url('img/templates/construction/banner.png') }}') center/cover no-repeat;">
                                </div>
                            @endif

                            @if ($business_card_details->cover_type == 'youtube-ap' || $business_card_details->cover_type == 'youtube')
                                <iframe id="cover-yt"
                                    class="cover-media-item {{ in_array($business_card_details->cover_type, ['youtube-ap', 'youtube']) ? 'active' : '' }}"
                                    src="https://www.youtube.com/embed/{{ $business_card_details->cover }}?autoplay={{ $business_card_details->cover_type == 'youtube-ap' ? '1' : '0' }}&mute=1&loop=1&controls=0&playlist={{ $business_card_details->cover }}"
                                    allow="autoplay; encrypted-media"></iframe>
                            @endif

                            @if ($business_card_details->cover_type == 'vimeo-ap' || $business_card_details->cover_type == 'vimeo')
                                <iframe id="cover-vimeo"
                                    class="cover-media-item {{ in_array($business_card_details->cover_type, ['vimeo-ap', 'vimeo']) ? 'active' : '' }}"
                                    src="https://player.vimeo.com/video/{{ $business_card_details->cover }}?background={{ $business_card_details->cover_type == 'vimeo-ap' ? '1' : '0' }}&autoplay={{ $business_card_details->cover_type == 'vimeo-ap' ? '1' : '0' }}&loop=1&byline=0&title=0"
                                    allow="autoplay; fullscreen"></iframe>
                            @endif

                            <div class="cover-overlay"></div>

                            {{-- Language Switcher — OUTSIDE .cover-photo so iframe never intercepts touch --}}
                            @if (
                                $business_card_details->is_enable_language_switcher == 1 &&
                                    is_array(config('app.languages')) &&
                                    count(config('app.languages')) > 1)
                                @include('templates.includes.vcard.construction.language-switcher')
                            @endif
                        </div>

                        <div class="gold-rule-bold"></div>

                        <!-- PROFILE -->
                        <div class="profile-block anim-fade-up">
                            <div class="profile-img-wrap">
                                <img src="{{ url($business_card_details->profile) }}"
                                    alt="{{ $business_card_details->title }}" class="profile-img">
                            </div>
                            <h1 class="company-name">{{ $business_card_details->title }}</h1>
                            <div class="company-subtitle">{{ $card_details->sub_title }}</div>
                            @if ($business_card_details->description != null)
                                <div class="company-desc">{!! $business_card_details->description !!}</div>
                            @endif
                        </div>

                        <!-- QUICK ACTIONS -->
                        <div class="quick-actions anim-fade-up anim-delay-1">

                            @if (isset($feature_details) && count($feature_details) > 0)
                                @foreach ($feature_details as $feature)
                                    @if ($feature->type == 'tel')
                                        <a href="tel:{{ $feature->content }}" class="qa-btn">
                                            <i class="fas fa-phone-alt"></i><span>{{ __('Call') }}</span>
                                        </a>
                                    @elseif ($feature->type == 'email')
                                        <a href="mailto:{{ $feature->content }}" class="qa-btn">
                                            <i class="fas fa-envelope"></i><span>{{ __('Email') }}</span>
                                        </a>
                                    @elseif ($feature->type == 'wa')
                                        <a href="https://wa.me/{{ ltrim($feature->content, '+') }}" target="_blank"
                                            class="qa-btn">
                                            <i class="fab fa-whatsapp"></i><span>{{ __('Chat') }}</span>
                                        </a>
                                    @elseif ($feature->type == 'map')
                                        <a href="#location" class="qa-btn">
                                            <i class="fas fa-map-marker-alt"></i><span>{{ __('Location') }}</span>
                                        </a>
                                    @endif
                                @endforeach
                            @endif

                        </div>

                        <div class="gold-rule" style="margin: 0 16px 16px;"></div>

                        <!-- CUSTOM LINKS & INFO -->
                        @php
                            $excludedTypes = ['tel', 'email', 'wa', 'map', 'iframe', 'youtube', 'location'];
                            $validFeatures = collect($feature_details)->filter(
                                fn($f) => isset($f->type) && !in_array($f->type, $excludedTypes),
                            );
                        @endphp

                        @if ($validFeatures->isNotEmpty())
                            <div class="vault-panel gsap-reveal">
                                <div class="panel-head">
                                    <div class="panel-icon"><i class="fas fa-clipboard-list"></i></div>
                                    <span class="panel-title">{{ __($feature_details[0]->title ?? 'Information') }}</span>
                                </div>

                                <div class="feat-grid">
                                    @foreach ($validFeatures as $feature)
                                        @php
                                            $href = $feature->content;

                                            if ($feature->type === 'address') {
                                                $href = 'https://www.google.com/maps?q=' . urlencode($feature->content);
                                            } elseif ($feature->type === 'url') {
                                                $href = strpos($feature->content, 'http') === 0
                                                    ? $feature->content
                                                    : 'https://' . $feature->content;
                                            }
                                        @endphp

                                        <!-- Notice the conditional class 'feat-link-full' added here -->
                                        <a href="{{ $href }}" target="_blank"
                                            class="feat-link {{ $feature->type === 'address' ? 'feat-link-full' : '' }}">

                                            <div class="feat-link-icon"><i
                                                    class="{{ $feature->icon ?? 'fas fa-chevron-right' }}"></i>
                                            </div>

                                            <div class="feat-link-text">
                                                <h4>{{ $feature->label }}</h4>
                                                @if ($feature->type === 'text' || $feature->type === 'address')
                                                    <!-- Address text is allowed to wrap natively via CSS now -->
                                                    <p>{{ $feature->content }}</p>
                                                @elseif($feature->type === 'url')
                                                    <p>{{ __('View Link') }}</p>
                                                @endif
                                            </div>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <!-- APPOINTMENT / SITE VISIT -->
                        @if ($appointmentEnabled == true && isset($plan_details['appointment']) && $plan_details['appointment'] == 1)
                            @if ($appointment_slots != null)
                                <div class="vault-panel gsap-reveal" id="booking-section">

                                    <!-- NEW DESIGN: Header -->
                                    <div class="panel-head">
                                        <div class="panel-icon"><i class="fas fa-calendar-alt"></i></div>
                                        <span
                                            class="panel-title">{{ __(json_decode($appointment_slots, true)['title']) }}</span>
                                    </div>

                                    <!-- Form Wrapper -->
                                    <div>

                                        <!-- OLD LOGIC: Alert Messages required by JS -->
                                        <div id="errorMessage" class="alert-error hidden"
                                            style="margin-bottom: 15px; font-size: 13px;">
                                            {{ __('Please select a valid date and time slot.') }}
                                        </div>
                                        <div id="successMessage" class="alert-success hidden"
                                            style="margin-bottom: 15px; font-size: 13px;">
                                            {{ __('Appointment booked successfully!') }}
                                        </div>
                                        <div id="errorSubmitMessage" class="alert-error hidden"
                                            style="margin-bottom: 15px; font-size: 13px;">
                                            {{ __('Please fill all the fields.') }}
                                        </div>

                                        <!-- OLD LOGIC + NEW DESIGN: Side-by-side Date and Dynamic Time Slot -->
                                        <div class="grid-2"
                                            style="margin-bottom: 18px; gap: 12px; display: grid; grid-template-columns: 1fr 1fr;">
                                            <div>
                                                <label class="f-label"
                                                    style="display: block; margin-bottom: 6px;">{{ __('Date') }}</label>
                                                <input type="text" id="appointment-date"
                                                    class="f-input flatpickr-input"
                                                    placeholder="{{ __('Select a date') }}" required />
                                            </div>
                                            <div>
                                                <label class="f-label"
                                                    style="display: block; margin-bottom: 6px;">{{ __('Time Slot') }}</label>
                                                <select id="time-slot-select" class="f-input" required>
                                                    <option value="">{{ __('Select a time slot') }}</option>
                                                </select>
                                            </div>
                                        </div>

                                        <!-- Hidden price input required by JS -->
                                        <input type="hidden" id="price" disabled>

                                        <!-- NEW DESIGN: Button with Old Logic onclick action -->
                                        <button id="add-slot-button" type="button" class="btn-gold"
                                            style="width: 100%;" onclick="validateAndShowModal()">
                                            {{ __('Book Appointment') }}
                                        </button>

                                    </div>

                                </div>
                            @endif
                        @endif

                        <!-- SERVICES -->
                        @if (count($service_details) > 0)
                            <div class="vault-panel gsap-reveal">
                                <div class="panel-head">
                                    <div class="panel-icon"><i class="fas fa-tools"></i></div>
                                    <span class="panel-title">{{ __($service_details[0]->title) }}</span>
                                </div>
                                <div class="swiper serviceSwiper">
                                    <div class="swiper-wrapper">
                                        @foreach ($service_details as $s)
                                            <div class="swiper-slide">
                                                <div class="struct-card">
                                                    @if (!empty($s->service_image))
                                                        <img src="{{ url($s->service_image) }}"
                                                            alt="{{ $s->service_name }}" class="struct-card-img">
                                                    @else
                                                        <div class="struct-card-no-img"><i
                                                                class="{{ $s->icon ?? 'fas fa-cog' }}"></i></div>
                                                    @endif
                                                    <div class="struct-card-body">
                                                        <h4>{{ $s->service_name }}</h4>
                                                        <p>{{ $s->service_description }}</p>
                                                        @if ($enquiry_button != null && $whatsAppNumberExists == true && $s->enable_enquiry == 'Enabled')
                                                            <a href="https://wa.me/{{ $enquiry_button }}?text={{ __('Hi, I need info on:') }} {{ $s->service_name }}."
                                                                target="_blank" class="btn-outline"
                                                                style="font-size:13px; padding:10px;">{{ __('Request Quote') }}</a>
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

                        <!-- PRODUCTS / EQUIPMENT -->
                        @if (count($product_details) > 0)
                            <div class="vault-panel gsap-reveal">
                                <div class="panel-head">
                                    <div class="panel-icon"><i class="fas fa-truck-loading"></i></div>
                                    <span class="panel-title">{{ __($product_details[0]->title) }}</span>
                                </div>
                                <div class="swiper productSwiper">
                                    <div class="swiper-wrapper">
                                        @foreach ($product_details as $p)
                                            <div class="swiper-slide">
                                                <div class="struct-card" style="position:relative;">
                                                    @if (!empty($p->badge))
                                                        <span class="badge-label">{{ $p->badge }}</span>
                                                    @endif
                                                    <img src="{{ url($p->product_image) }}" alt="{{ $p->product_name }}"
                                                        class="struct-card-img">
                                                    <div class="struct-card-body">
                                                        <h4>{{ $p->product_name }}</h4>
                                                        <p>{{ Str::limit($p->product_description, 60) }}</p>
                                                        @if ($p->sales_price != 0)
                                                            <div class="price-badge">
                                                                {{ formatCurrencyVcard($p->sales_price, $p->currency) }}
                                                                @if ($p->sales_price != $p->regular_price)
                                                                    <strike>{{ formatCurrencyVcard($p->regular_price, $p->currency) }}</strike>
                                                                @endif
                                                            </div>
                                                        @endif
                                                        @if ($p->product_status != 'null')
                                                            <div class="stock-tag"
                                                                style="color:{{ $p->product_status == 'instock' ? '#6EE7B7' : '#FCA5A5' }}">
                                                                <i
                                                                    class="fas {{ $p->product_status == 'instock' ? 'fa-check-circle' : 'fa-times-circle' }}"></i>
                                                                {{ $p->product_status == 'outstock' ? __('Out of Stock') : __('Available') }}
                                                            </div>
                                                        @endif
                                                        @if ($enquiry_button != null && $whatsAppNumberExists == true)
                                                            @if ($p->product_status == 'outstock')
                                                                <button class="btn-outline"
                                                                    style="opacity:0.4; cursor:not-allowed;">{{ __('Unavailable') }}</button>
                                                            @else
                                                                <a href="https://wa.me/{{ $enquiry_button }}?text={{ __('Hi, I want to order:') }} {{ $p->product_name }}."
                                                                    target="_blank" class="btn-gold"
                                                                    style="font-size:13px; padding:10px;">{{ __('Order Now') }}</a>
                                                            @endif
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

                        <!-- GALLERY -->
                        @if (count($galleries_details) > 0)
                            <div class="vault-panel gsap-reveal">
                                <div class="panel-head">
                                    <div class="panel-icon"><i class="fas fa-images"></i></div>
                                    <span class="panel-title">{{ __($galleries_details[0]->title) }}</span>
                                </div>
                                <div class="grid-2">
                                    @foreach ($galleries_details as $g)
                                        <div class="gallery-item">
                                            <img src="{{ url($g->gallery_image) }}" alt="{{ $g->caption }}">
                                            @if (!empty($g->caption))
                                                <div class="gallery-caption">{{ $g->caption }}</div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <!-- PROJECT VIDEOS -->
                        @if ($feature_details->where('type', 'youtube')->count() > 0 || $feature_details->where('type', 'vimeo')->count() > 0)
                            <div class="vault-panel gsap-reveal">
                                <div class="panel-head">
                                    <div class="panel-icon"><i class="fas fa-play-circle"></i></div>
                                    <span class="panel-title">{{ __('Project Videos') }}</span>
                                </div>
                                <div class="swiper videoSwiper">
                                    <div class="swiper-wrapper">
                                        @foreach ($feature_details as $feature)
                                            @if ($feature->type == 'youtube' || $feature->type == 'vimeo')
                                                <div class="swiper-slide">
                                                    <div
                                                        style="border-radius:6px; overflow:hidden; border:1px solid rgba(201,168,76,0.1);">
                                                        @if ($feature->type == 'youtube')
                                                            <iframe
                                                                src="https://www.youtube.com/embed/{{ $feature->content }}"
                                                                title="{{ $feature->label }}" frameborder="0"
                                                                allowfullscreen
                                                                style="width:100%; height:160px; display:block; background:#000;"></iframe>
                                                        @elseif ($feature->type == 'vimeo')
                                                            <iframe
                                                                src="https://player.vimeo.com/video/{{ $feature->content }}"
                                                                title="{{ $feature->label }}" frameborder="0"
                                                                allowfullscreen
                                                                style="width:100%; height:160px; display:block; background:#000;"></iframe>
                                                        @endif
                                                        <div style="padding:10px 12px; background:var(--ink-3);">
                                                            <p
                                                                style="font-family:'Cormorant Garamond',serif; font-size:16px; color:var(--fog);">
                                                                {{ $feature->label ?? 'Video' }}</p>
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

                        <!-- IFRAMES / PORTALS -->
                        @if ($feature_details->where('type', 'iframe')->count() > 0)
                            <div class="vault-panel gsap-reveal">
                                <div class="panel-head">
                                    <div class="panel-icon"><i class="fas fa-desktop"></i></div>
                                    <span class="panel-title">{{ __('Portals') }}</span>
                                </div>
                                <div style="display:flex; flex-direction:column; gap:14px;">
                                    @foreach ($feature_details as $feature)
                                        @if ($feature->type == 'iframe')
                                            <div
                                                style="border-radius:6px; overflow:hidden; border:1px solid rgba(201,168,76,0.1);">
                                                <iframe src="{{ $feature->content }}" title="{{ $feature->label }}"
                                                    frameborder="0" width="100%" height="200" allowfullscreen
                                                    style="display:block; background:#fff;"></iframe>
                                                @if (!empty($feature->label))
                                                    <div style="padding:10px 12px; background:var(--ink-3);">
                                                        <p
                                                            style="font-family:'Barlow Condensed',sans-serif; font-size:14px; font-weight:600; letter-spacing:1px; text-transform:uppercase; color:var(--fog);">
                                                            {{ $feature->label }}</p>
                                                    </div>
                                                @endif
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <!-- SERVICE BOOKING FORM -->
                        @if (isset($plan_details['service_booking']) &&
                                $plan_details['service_booking'] == 1 &&
                                isset($service_booking_details) &&
                                $service_booking_details->service_booking == 1)
                            <div class="vault-panel gsap-reveal" id="service-booking-section">
                                <div class="panel-head">
                                    <div class="panel-icon"><i class="fas fa-clipboard-list"></i></div>
                                    <span class="panel-title">{{ __($service_booking_details->title) }}</span>
                                </div>
                                <div id="errorMessage1" class="alert-box alert-error hidden"></div>
                                <div id="successMessage1" class="alert-box alert-success hidden"></div>
                                <form id="serviceBookingForm" onsubmit="event.preventDefault(); submitServiceBooking();">
                                    <div class="grid-2">
                                        <div><label class="f-label">{{ __('Name') }}</label><input type="text"
                                                name="customer_name" id="customer_name" class="f-input" /></div>
                                        <div><label class="f-label">{{ __('Email') }}</label><input type="email"
                                                name="customer_email" id="customer_email" class="f-input" /></div>
                                    </div>
                                    <div class="grid-2">
                                        <div><label class="f-label">{{ __('Mobile') }}</label><input type="tel"
                                                name="customer_phone" id="customer_phone" class="f-input" /></div>
                                        <div><label class="f-label">{{ __('Crew Size') }}</label><input type="number"
                                                name="no_of_persons" id="no_of_persons" value="1"
                                                class="f-input" />
                                        </div>
                                    </div>
                                    <label class="f-label">{{ __('Site Address') }}</label>
                                    <textarea name="customer_address" id="customer_address" rows="2" class="f-input"></textarea>
                                    <label class="f-label">{{ __('Project Scope') }}</label>
                                    <textarea name="customer_notes" id="customer_notes" rows="3" class="f-input"></textarea>
                                    <label class="f-label">{{ __('Estimated Start') }}</label>
                                    <div class="grid-2">
                                        <input type="text" id="service_start_date" name="service_start_date"
                                            placeholder="{{ __('Date') }}" class="f-input" />
                                        <input type="time" name="service_start_time" id="service_start_time"
                                            class="f-input timepicker" />
                                    </div>
                                    <label class="f-label">{{ __('Estimated End') }}</label>
                                    <div class="grid-2">
                                        <input type="date" id="service_end_date" name="service_end_date"
                                            class="f-input" />
                                        <input type="time" name="service_end_time" id="service_end_time"
                                            class="f-input timepicker" />
                                    </div>
                                    <button type="submit" class="btn-gold">{{ __('Submit Proposal') }}</button>
                                </form>
                            </div>
                        @endif

                        <!-- TESTIMONIALS -->
                        @if (count($testimonials) > 0)
                            <div class="vault-panel gsap-reveal">
                                <div class="panel-head">
                                    <div class="panel-icon"><i class="fas fa-star"></i></div>
                                    <span class="panel-title">{{ __($testimonials[0]->title) }}</span>
                                </div>
                                <div class="swiper testimonialSwiper">
                                    <div class="swiper-wrapper">
                                        @foreach ($testimonials as $t)
                                            <div class="swiper-slide">
                                                <div class="testi-card">
                                                    <p class="testi-review">{{ $t->review }}</p>
                                                    <div class="testi-user">
                                                        @if (!empty($t->reviewer_image))
                                                            <img src="{{ url($t->reviewer_image) }}"
                                                                alt="{{ $t->reviewer_name }}" class="testi-avatar">
                                                        @else
                                                            <div class="testi-avatar-fallback"><i class="fas fa-user"></i>
                                                            </div>
                                                        @endif
                                                        <div>
                                                            <h4>{{ $t->reviewer_name }}</h4>
                                                            @if (!empty($t->review_subtext))
                                                                <p>{{ $t->review_subtext }}</p>
                                                            @endif
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

                        <!-- BUSINESS HOURS -->
                        @if ($plan_details['business_hours'] == 1 && $business_hours != null && $business_hours->is_display != 0)
                            <div class="vault-panel gsap-reveal">
                                <div class="panel-head">
                                    <div class="panel-icon"><i class="far fa-clock"></i></div>
                                    <span class="panel-title">{{ __($business_hours->title) }}</span>
                                </div>
                                @if ($business_hours->is_always_open != 'Opening')
                                    @foreach (['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as $day)
                                        @if ($business_hours->$day)
                                            <div class="hour-row">
                                                <span class="hour-day">{{ __($day) }}</span>
                                                <span class="hour-time">{{ __($business_hours->$day) }}</span>
                                            </div>
                                        @endif
                                    @endforeach
                                @else
                                    <div class="hour-open-24">
                                        <h3>{{ __('24 / 7 Operations') }}</h3>
                                        <p>{{ __('Emergency crews always on standby.') }}</p>
                                    </div>
                                @endif
                            </div>
                        @endif

                        <!-- GOOGLE MAPS -->
                        @if (count($feature_details) > 0 && $feature_details->contains('type', 'map'))
                            <div class="vault-panel gsap-reveal" id="location">
                                <div class="panel-head">
                                    <div class="panel-icon"><i class="fas fa-map-marked-alt"></i></div>
                                    <span class="panel-title">{{ __('Site Location') }}</span>
                                </div>
                                @foreach ($feature_details as $feature)
                                    @if ($feature->type == 'map')
                                        <div class="map-container" style="margin-bottom:12px;">
                                            <iframe src="https://www.google.com/maps/embed?{!! $feature->content !!}"
                                                width="100%" height="200" style="border:0;" allowfullscreen=""
                                                loading="lazy"></iframe>
                                        </div>
                                        <p
                                            style="font-family:'Barlow Condensed',sans-serif; font-size:13px; font-weight:600; letter-spacing:1px; text-transform:uppercase; text-align:center; color:var(--slate-light);">
                                            {{ $feature->label }}</p>
                                    @endif
                                @endforeach
                            </div>
                        @endif

                        <!-- PAYMENTS -->
                        @if (count($payment_details) > 0)
                            <div class="vault-panel gsap-reveal">
                                <div class="panel-head">
                                    <div class="panel-icon"><i class="fas fa-file-invoice-dollar"></i></div>
                                    <span class="panel-title">{{ __($payment_details[0]->title ?? 'Invoicing') }}</span>
                                </div>
                                <div style="display:flex; flex-direction:column; gap:10px;">
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
                                        @if ($payment->type == 'text')
                                            <div class="pay-info-static">
                                                <h4><i class="{{ $payment->icon ?? 'fas fa-university' }}"
                                                        style="color:var(--gold);"></i> {{ $payment->label }}</h4>
                                                <p>{!! str_replace('.', '<br>', $payment->content) !!}</p>
                                            </div>
                                        @elseif ($payment->type == 'image')
                                            <a href="{{ $payment->content ? url($payment->content) : 'javascript:void(0);' }}"
                                                target="_blank"
                                                style="display:block; border-radius:6px; overflow:hidden; border:1px solid rgba(201,168,76,0.1);">
                                                @if (!empty($payment->content))
                                                    <img src="{{ url($payment->content) }}"
                                                        alt="{{ $payment->label ?? 'Payment' }}"
                                                        style="width:100%; display:block;">
                                                @endif
                                            </a>
                                        @else
                                            <a href="{{ $href }}" target="_blank" class="pay-block">
                                                <i class="{{ $payment->icon ?? 'fas fa-money-check' }}"></i>
                                                <span>{{ $payment->label }}</span>
                                            </a>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <!-- GOOGLE WALLET -->
                        @if (is_dir(base_path('plugins/GoogleWallet')) &&
                                isset($plan_details['google_wallet']) &&
                                $plan_details['google_wallet'] == 1 &&
                                $business_card_details->is_google_wallet_hidden == 0)
                            <div class="vault-panel gsap-reveal">
                                <div class="panel-head">
                                    <div class="panel-icon"><i class="fab fa-google-wallet"></i></div>
                                    <span class="panel-title">{{ __('Digital Pass') }}</span>
                                </div>
                                <div style="text-align:center;">
                                    @if ($google_wallet_details->wallet_description != null)
                                        <p style="font-size:14px; color:var(--slate); margin-bottom:15px;">
                                            {!! $google_wallet_details->wallet_description !!}
                                        </p>
                                    @endif
                                    @if ($google_wallet_details->wallet_link != null)
                                        <a href="{{ $google_wallet_details->wallet_link }}" target="_blank"
                                            style="display:inline-block; max-width:200px; border-radius:8px; overflow:hidden; border:1px solid var(--gold-border);">
                                            <img src="{{ url()->to('/') . '/img/google-wallet-btn.png' }}"
                                                alt="Google Wallet" style="width:100%; display:block;">
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @endif

                        <!-- SOCIAL LINKS -->
                        @php
                            $socialTypes = [
                                'facebook',
                                'twitter',
                                'linkedin',
                                'instagram',
                                'tiktok',
                                'pinterest',
                                'youtube',
                                'snapchat',
                            ];
                            $socialFeatures = collect($feature_details)->filter(fn($f) => in_array($f->type, $socialTypes));
                        @endphp
                        @if ($socialFeatures->isNotEmpty())
                            <div class="vault-panel gsap-reveal">
                                <div class="panel-head">
                                    <div class="panel-icon"><i class="fas fa-network-wired"></i></div>
                                    <span class="panel-title">{{ __('Network') }}</span>
                                </div>
                                <div class="grid-3">
                                    @foreach ($socialFeatures as $feature)
                                        <a href="{{ $feature->content }}" target="_blank" class="social-tile">
                                            <i class="{{ $feature->icon }}"></i>
                                            <span>{{ $feature->label }}</span>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <!-- CONTACT FORM -->
                        @if ($plan_details['contact_form'] == 1 && $business_card_details->enquiry_email != null)
                            <div class="vault-panel gsap-reveal" id="contact-section">
                                <div class="panel-head">
                                    <div class="panel-icon"><i class="fas fa-envelope"></i></div>
                                    <span
                                        class="panel-title">{{ __($business_card_details->contact_form_title ?? 'Contact Us') }}</span>
                                </div>
                                @if (Session::has('message'))
                                    <div class="alert-box alert-success"><i class="fas fa-check-circle"></i>
                                        {{ Session::get('message') }}</div>
                                @endif
                                @if ($errors->any())
                                    <div class="alert-box alert-error"
                                        style="flex-direction:column; align-items:flex-start;">
                                        <ul style="padding-left:16px; margin:0;">
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                                <form action="{{ config('app.url') }}/sent-enquiry" method="POST">
                                    @csrf
                                    <input type="hidden" name="card_id"
                                        value="{{ $business_card_details->card_id }}" />
                                    <label class="f-label">{{ __('Name') }}</label>
                                    <input type="text" name="name" class="f-input" value="{{ old('name') }}"
                                        required>
                                    <label class="f-label">{{ __('Email') }}</label>
                                    <input type="email" name="email" class="f-input" value="{{ old('email') }}"
                                        required>
                                    <label class="f-label">{{ __('Phone') }}</label>
                                    <input type="tel" name="phone" class="f-input" value="{{ old('phone') }}"
                                        required>
                                    <label class="f-label">{{ __('Project Details') }}</label>
                                    <textarea name="message" class="f-input" required>{{ old('message') }}</textarea>
                                    <button type="submit" class="btn-gold">{{ __('Send Message') }}</button>
                                    <div style="margin-top:14px;">@include('templates.includes.recaptcha', [
                                        'recaptchaId' => 'recaptcha-one',
                                    ])</div>
                                </form>
                            </div>
                        @endif

                        <!-- FOOTER -->
                        <div class="vault-footer">
                            <div class="gold-rule" style="margin-bottom:16px;"></div>
                            <p>
                                @if ($plan_details['hide_branding'] == 1)
                                    &copy; <a href="{{ url()->current() }}">{{ $card_details->title }}</a> <span
                                        id="year"></span>. {{ __('All Rights Reserved.') }}
                                @else
                                    {{ __('Powered by') }} <a
                                        href="{{ env('APP_URL') }}">{{ config('app.name') }}</a>
                                    <span id="year"></span>
                                @endif
                            </p>
                        </div>                    
                    @endif
                </div>

                <!-- BOTTOM NAV -->
                <div class="vault-nav">
                    <button class="nav-item active" onclick="window.scrollTo({top:0,behavior:'smooth'})">
                        <i class="fas fa-hard-hat"></i><span>{{ __('Home') }}</span>
                    </button>
                    <button class="nav-item"
                        onclick="document.getElementById('booking-section')?.scrollIntoView({behavior:'smooth'})">
                        <i class="fas fa-calendar-alt"></i><span>{{ __('Book') }}</span>
                    </button>
                    <a href="{{ config('app.url') }}/download/{{ $business_card_details->card_id }}"
                        class="nav-item nav-item-center">
                        <i class="fas fa-id-card"></i><span>{{ __('Save') }}</span>
                    </a>
                    <button class="nav-item" onclick="toggleScanModal(true)">
                        <i class="fas fa-qrcode"></i><span>{{ __('QR') }}</span>
                    </button>
                    <button class="nav-item" onclick="shareToggleModal(true)">
                        <i class="fas fa-share-alt"></i><span>{{ __('Share') }}</span>
                    </button>
                </div>
            @endif


            {{-- ================================================================ 
                APPOINTMENT MODAL
            ================================================================ --}}
            <div id="appointmentModal" class="appt-overlay hidden">
                <div class="appt-box" onclick="event.stopPropagation()">

                    {{-- Corner accents --}}
                    <span class="appt-corner appt-corner--tl"></span>
                    <span class="appt-corner appt-corner--tr"></span>
                    <span class="appt-corner appt-corner--bl"></span>
                    <span class="appt-corner appt-corner--br"></span>

                    {{-- Header --}}
                    <div class="appt-header">
                        <button class="appt-close" type="button" onclick="closeAppointmentModal()">
                            <i class="fas fa-times"></i>
                        </button>
                        <div class="appt-header-icon">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <h2 class="appt-title">{{ __('Book Appointment') }}</h2>
                        <p class="appt-subtitle">{{ __('Fill in your details below') }}</p>
                    </div>

                    {{-- Form --}}
                    <form id="appointmentForm">
                        <div class="appt-form">

                            {{-- Name + Phone row --}}
                            <div class="appt-row">
                                <div class="appt-field">
                                    <label class="appt-label">{{ __('Name') }}</label>
                                    <input type="text" id="name" class="appt-input"
                                        placeholder="{{ __('Your name') }}" required />
                                </div>
                                <div class="appt-field">
                                    <label class="appt-label">{{ __('Phone') }}</label>
                                    <input type="text" id="phone" class="appt-input"
                                        placeholder="{{ __('+1 234 567') }}" required />
                                </div>
                            </div>

                            {{-- Email --}}
                            <div class="appt-field">
                                <label class="appt-label">{{ __('Email') }}</label>
                                <input type="email" id="email" class="appt-input"
                                    placeholder="{{ __('you@example.com') }}" required />
                            </div>

                            {{-- Notes --}}
                            <div class="appt-field">
                                <label class="appt-label">{{ __('Notes') }}</label>
                                <textarea id="notes" class="appt-textarea" rows="3" placeholder="{{ __('Any additional details…') }}"></textarea>
                            </div>

                            {{-- Hidden price --}}
                            <div class="hidden">
                                <input type="text" id="price" class="appt-input" disabled />
                            </div>

                            {{-- Recaptcha --}}
                            @include('templates.includes.recaptcha', ['recaptchaId' => 'recaptcha-two'])

                        </div>

                        {{-- Footer --}}
                        <div class="appt-footer">
                            <button type="button" class="appt-btn-cancel"
                                onclick="closeAppointmentModal()">{{ __('Close') }}</button>
                            <button type="submit" id="bookAppointmentButton" class="appt-btn-submit">
                                <i class="fas fa-check"></i> {{ __('Confirm Booking') }}
                            </button>
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
                    <button onclick="copyLinkStyled(this)" class="share-copy-btn"><i
                            class="fas fa-link"></i><span>{{ __('Copy Link') }}</span></button>

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
                        @include('vendor.laravelpwa.construction-vcard')
                    @endif
                @endif

                {{-- ================================================================
                    NEWSLETTER MODAL (inline — from newsletter_modal.blade.php)
                ================================================================ --}}
                @if ($business_card_details != null)
                    @if (!empty($business_card_details->is_newsletter_pop_active) && $business_card_details->is_newsletter_pop_active == 1)
                        @include('templates.includes.vcard.construction.newsletter_modal')
                    @endif
                @endif

                {{-- ================================================================
                    INFORMATION POPUP MODAL (inline — from information_popup_modal.blade.php)
                ================================================================ --}}
                @if ($business_card_details != null)
                    @if (!empty($business_card_details->is_info_pop_active) && $business_card_details->is_info_pop_active == 1)
                        @include('templates.includes.vcard.construction.information_popup_modal', [
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
        gsap.from('.cover-wrap', {
            y: -20,
            opacity: 0,
            duration: 0.7,
            ease: 'power3.out'
        });
        gsap.from('.profile-block', {
            scale: 0.95,
            opacity: 0,
            duration: 0.6,
            ease: 'power2.out',
            delay: 0.2
        });

        document.querySelectorAll('.gsap-reveal').forEach(el => {
            gsap.fromTo(el, {
                y: 24,
                opacity: 0
            }, {
                y: 0,
                opacity: 1,
                duration: 0.6,
                ease: 'power2.out',
                scrollTrigger: {
                    trigger: el,
                    start: 'top 92%'
                }
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
            var date = document.getElementById('appointment-date')?.value;
            var slot = document.getElementById('time-slot-select')?.value;
            var errEl = document.getElementById('errorMessage');

            if (date && slot) {
                errEl?.classList.add('hidden');
                openAppointmentModal();
            } else {
                errEl?.classList.remove('hidden');
            }
        }

        // Appointment modal open button
        function openAppointmentModal() {
            var overlay = document.getElementById('appointmentModal');
            if (!overlay) return;
            overlay.classList.remove('hidden');
            requestAnimationFrame(function() {
                requestAnimationFrame(function() {
                    overlay.classList.add('show');
                });
            });
        }

        // Appointment modal close button
        function closeAppointmentModal() {
            var overlay = document.getElementById('appointmentModal');
            if (!overlay) return;
            overlay.classList.remove('show');
            setTimeout(function() {
                overlay.classList.add('hidden');
            }, 300);
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
                            grecaptcha.reset(window.recaptchaWidgets['recaptcha-one']);
                        @endif
                        closeAppointmentModal();
                        if (data.success && data.whatsapp_url && data.whatsapp_url !== '#') {
                            setTimeout(() => {
                                window.location.href = data.whatsapp_url;
                            }, 3000);
                        }
                    } else {
                        successMessage?.classList.add('hidden');
                        errorSubmitMessage?.classList.remove('hidden');
                        errorSubmitMessage.innerHTML = data.message || 'Something went wrong';
                        closeAppointmentModal();
                    }
                    button.disabled = false;
                    button.innerHTML = `{{ __('Book Appointment') }}`;
                })
                .catch(() => {
                    closeAppointmentModal();
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
                    succEl.innerHTML = data.message || `{{ __('Service booked successfully!') }}`;
                } else {
                    errEl.classList.remove('hidden');
                    errEl.innerHTML = data.message || `{{ __('Something went wrong') }}`;
                }
            }).catch(() => {
                errEl.classList.remove('hidden');
                errEl.innerHTML = `{{ __('Something went wrong') }}`;
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
            var overlay = document.getElementById('customInfoOverlay');
            if (!overlay) return;
            overlay.classList.remove('hidden');
            requestAnimationFrame(function() {
                requestAnimationFrame(function() {
                    overlay.classList.add('show');
                    if (typeof triggerInfoConfetti === 'function') {
                        setTimeout(triggerInfoConfetti, 300);
                    }
                });
            });
        }

        function closeInfoModal() {
            var overlay = document.getElementById('customInfoOverlay');
            if (!overlay) return;
            overlay.classList.remove('show');
            setTimeout(function() {
                overlay.classList.add('hidden');
            }, 300);
        }

        document.addEventListener('DOMContentLoaded', function() {
            if (document.getElementById('customInfoOverlay')) {
                // Open modal
                openInfoModal();
            }
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
