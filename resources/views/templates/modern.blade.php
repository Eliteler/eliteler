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

    <meta name="theme-color" content="#EFFAF4" />

    <!-- Add to homescreen for Chrome on Android -->
    <meta name="application-name" content="{{ $card_details->title }}">

    <!-- Add to homescreen for Safari on iOS -->
    <meta name="apple-mobile-web-app-title" content="{{ $card_details->title }}">

    <!-- Tile for Win8 -->
    <meta name="msapplication-TileColor" content="#EFFAF4">
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
    <link rel="stylesheet" href="{{ url('templates/css/modern.css') }}">
    {{-- Slick --}}
    <link rel="stylesheet" href="{{ url('css/slick.css') }}" />
    <link rel="stylesheet" href="{{ url('css/slick-theme.css') }}" />
    {{-- Fontawesome CSS --}}
    <link rel="stylesheet" href="{{ url('css/fontawesome.min.css') }}" />    

    {{-- Google Fonts --}}
    <link
        href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&family=Inter:wght@400;700&display=swap"
        rel="stylesheet">

    <!-- Include the qrious library -->
    <script src="{{ url('js/qrious.min.js') }}"></script>

    <style>
        body {
            font-family: 'DM Sans', sans-serif;
            letter-spacing: -0.4px;
        }

        .custom-head,
        .title-text {
            font-family: 'Inter', serif;
            letter-spacing: -2px;
        }

        .shine-text {
            position: relative;
            color: #fff;
            /* fallback */
            background: linear-gradient(110deg,
                    rgba(255, 255, 255, 0.5) 0%,
                    #ffffff 40%,
                    rgba(255, 255, 255, 0.5) 60%);
            background-size: 200% auto;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            animation: shine 5s linear infinite;
        }

        @keyframes shine {
            0% {
                background-position: 200% center;
            }

            100% {
                background-position: -200% center;
            }
        }

        .slider .slick-slide {
            transition: transform 0.3s ease, opacity 0.3s ease;
            opacity: 0.5;
            /* Dim non-active slides */
            transform: scale(0.8);
            /* Scale down non-active slides */
        }

        .slider .slick-center {
            opacity: 1;
            /* Fully visible */
            transform: scale(1);
            /* Scale up the active slide */
            z-index: 1;
            /* Bring to front */
        }

        button:active {
            transform: scale(0.97);
        }

        a:active {
            transform: scale(0.97);
        }

        @keyframes move-y {
            0% {
                -webkit-transform: translateY(0);
                transform: translateY(0);
            }

            50% {
                -webkit-transform: translateY(var(--move-y, 10px));
                transform: translateY(var(--move-y, 10px));
            }

            100% {
                -webkit-transform: translateY(0);
                transform: translateY(0);
            }
        }

        .animate-move-y {
            animation: move-y 7s infinite;
        }
        
        /* Loader wrapper */
        #loader {
            position: fixed;
            inset: 0;
            z-index: 999999;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.6);
            backdrop-filter: blur(100px);
            opacity: 1;
            transition: opacity .3s ease-in-out, backdrop-filter .2s ease-in-out;
        }

        /* When hidden → fade out only opacity */
        #loader.hidden {
            opacity: 0;
            backdrop-filter: blur(0px);
            pointer-events: none;
        }

        /* Spinner */
        .spinner {
            width: 30px;
            height: 30px;
            border: 1.5px solid #044e1a;
            border-top: 1.5px solid transparent;
            border-radius: 50%;
            animation: spin 0.6s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }
    </style>

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

    {{-- Check PWA --}}
    @if ($plan_details != null)
        @if ($plan_details['pwa'] == 1 && $business_card_details->is_enable_pwa == 1)
            @laravelPWA

            <!-- Web Application Manifest -->
            <link rel="manifest" href="{{ $manifest }}">
        @endif
    @endif
</head>

<body class="bg-green-100 min-h-screen"
    dir="{{ App::isLocale('ar') || App::isLocale('ur') || App::isLocale('he') ? 'rtl' : 'ltr' }}">

    <!-- Loader -->
    @if ($introScreen != null)
        <div id="loader">
            <div class="spinner"></div>
        </div>
    @endif

    {{-- Page Content --}}
    <div id="smooth-wrapper">
        <div id="smooth-content" class="container max-w-2xl mx-auto relative overflow-hidden">
            {{-- Start Check password protected --}}
            @if ($business_card_details->password == null || Session::get('password_protected') == true)
                {{-- Check business details --}}
                @if ($business_card_details != null)
                    <div class="bg-white shadow-[0_0_4px_rgba(0,0,0,0.1)] overflow-hidden relative">
                        {{-- Index Screen --}}
                        @if ($introScreen != null)
                            @include("templates.includes.intros.{$introScreen->intro_code}", [
                                'theme' => $business_card_details->theme_id
                            ])
                        @endif                   

                        <div id="content-screen">
                            {{-- Banner Section --}}
                            @include('templates.includes.vcard.modern.banner')

                            <!-- Profile Info -->
                            <div class="relative px-6 -mt-0.5 pb-32 lg:p-6">
                                <!-- Profile Section -->
                                @include('templates.includes.vcard.modern.profile')

                                <!-- Contact Section -->
                                @if (count($feature_details) > 0)
                                    @include('templates.includes.vcard.modern.contact')
                                @endif

                                <!-- Services Section -->
                                @if (count($service_details) > 0)
                                    @include('templates.includes.vcard.modern.services')
                                @endif

                                <!-- Products Section -->
                                @if (count($product_details) > 0)
                                    @include('templates.includes.vcard.modern.products')
                                @endif

                                <!-- Gallery Section -->
                                @if (count($galleries_details) > 0)
                                    @include('templates.includes.vcard.modern.galleries')
                                @endif

                                <!-- Youtube Video Section -->
                                @if ($feature_details->where('type', 'youtube')->count() > 0)
                                    @include('templates.includes.vcard.modern.youtube-videos')
                                @endif

                                <!-- Iframe Section -->
                                @if ($feature_details->where('type', 'iframe')->count() > 0)
                                    @include('templates.includes.vcard.modern.iframe')
                                @endif

                                <!-- Client Reviews section -->
                                @if (count($testimonials) > 0)
                                    @include('templates.includes.vcard.modern.testimonials')
                                @endif

                                <!-- Business Hours -->
                                @if ($plan_details['business_hours'] == 1)
                                    @if ($business_hours != null && $business_hours->is_display != 0)
                                        @include('templates.includes.vcard.modern.business-hours')
                                    @endif
                                @endif

                                <!-- Appointment section -->
                                @if ($appointmentEnabled == true && isset($plan_details['appointment']) == 1)
                                    @include('templates.includes.vcard.modern.appointment')
                                @endif

                                <!-- Service Service Booking -->
                                @if (isset($plan_details['service_booking']) && $plan_details['service_booking'] == 1)
                                    @if (isset($service_booking_details) && $service_booking_details->service_booking == 1)
                                        @include('templates.includes.vcard.modern.service-booking')
                                    @endif
                                @endif

                                <!-- Start Payment section -->
                                @if (count($payment_details) > 0)
                                    @include('templates.includes.vcard.modern.payment')
                                @endif                                

                                <!-- Start Location section -->
                                @if (count($feature_details) > 0 && $feature_details->contains('type', 'map'))
                                    @include('templates.includes.vcard.modern.map')
                                @endif
                                <!-- End Location section -->

                                <!-- Start Google Wallet section -->
                                @if (is_dir(base_path('plugins/GoogleWallet')))
                                    @if (isset($plan_details['google_wallet']) && $plan_details['google_wallet'] == 1 && $business_card_details->is_google_wallet_hidden == 0)
                                        @include('templates.includes.vcard.modern.google-wallet')
                                    @endif
                                @endif

                                <!-- Start Contact form section -->
                                @if ($plan_details['contact_form'] == 1)
                                    @if ($business_card_details->enquiry_email != null)
                                        @include('templates.includes.vcard.modern.contact-us')
                                    @endif
                                @endif

                                <!-- Branding Section -->
                                @include('templates.includes.vcard.modern.branding')
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Bottom Bar -->
                @include('templates.includes.vcard.modern.bottom-bar')
            @endif

            {{-- All Modal Windows --}}
            @include('templates.includes.vcard.modern.modals')
        </div>
    </div>

    {{-- Jquery --}}
    <script src="{{ url('js/jquery.min.js') }}"></script>
    
    {{-- Smooth Scroll --}}
    <script src="{{ url('js/smooth-scroll.polyfills.min.js') }}"></script>
    {{-- Other JS --}}
    <script type="text/javascript" src="{{ url('app/js/footer.js') }}"></script>
    {{-- Flatpickr JS --}}
    <script src="{{ url('js/flatpickr.min.js') }}"></script>
    {{-- Slick --}}
    <script src="{{ url('js/slick.min.js') }}"></script>

    {{-- Animation --}}
    <script src="{{ url('js/gobiz-animation.min.js') }}"></script>
    <script src="{{ url('js/gobiz-animation-scrolltrigger.min.js') }}"></script>
    <script src="{{ url('js/gobiz-animation-splittext.js') }}"></script>

    {{-- Custom JS --}}
    @yield('custom-js')

    {{-- Flatpickr JS --}}
    <script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/l10n/{{ app()->getLocale() }}.js"></script>

    <script>
        // Assuming $appointment_slots contains data like: {"monday": [...], "tuesday": [...], ...}
        const disableSlots = {!! $appointment_slots !!}; // Outputting the time slots

        document.addEventListener('DOMContentLoaded', function() {
            "use strict";

            const direction =
                `{{ App::isLocale('ar') || App::isLocale('ur') || App::isLocale('he') ? 'rtl' : 'ltr' }}`;

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

            // slider fn
            $(".slider").slick({
                rtl: direction == 'rtl' ? true : false,
                slidesToShow: 1,
                slidesToScroll: 1,
                centerMode: true,
                arrows: false,
                centerPadding: "140px",
                infinite: true,
                autoplaySpeed: 3000,
                autoplay: true,
                responsive: [{
                        breakpoint: 768,
                        settings: {
                            centerPadding: "120px",
                        },
                    },
                    {
                        breakpoint: 575,
                        settings: {
                            centerPadding: "0px",
                        },
                    },
                ],
            });

            $(".review-slider").slick({
                rtl: direction == 'rtl' ? true : false,
                slidesToShow: 1,
                slidesToScroll: 1,
                infinite: true,
                autoplaySpeed: 3000,
                autoplay: true,
            });

            // animation
            gsap.registerPlugin(SplitText);
            const textMultipleElements = document.querySelectorAll(".custom-head");

            // Text Effect
            textMultipleElements.forEach((textElement) => {
            // Use SplitText plugin for better performance and control
            const splitText = new SplitText(textElement, {
                type: "chars",
                charsClass: "wave-char",
            });

            const chars = splitText.chars;

            // Set initial state
            gsap.set(chars, {
                y: 20,
                opacity: 0,
            });

            // Create a timeline for this element
            const tl = gsap.timeline({ paused: true });

            // Define the entrance animation
            tl.to(chars, {
                y: 0,
                opacity: 1,
                color: "#000000",
                duration: 0.5,
                ease: "back.out(1.2)",
                stagger: {
                amount: 0.8,
                from: "start",
                },
            });

            // Create ScrollTrigger with better sync
            ScrollTrigger.create({
                trigger: textElement,
                start: "top 80%",
                end: "bottom 15%",
                animation: tl,
                toggleActions: "play none none reverse",
                // Remove scrub to prevent conflict with toggleActions
                // scrub: 1,

                // Optional: Add refresh on update to handle fast scrolling
                refreshPriority: -1,

                // Ensure proper cleanup and state management
                onToggle: (self) => {
                if (self.isActive) {
                    // Ensure we're in the correct state when entering
                    gsap.set(chars, {
                    y: 20,
                    opacity: 0,
                    });
                    tl.restart();
                } else {
                    // Ensure we're in the correct state when leaving
                    tl.reverse();
                }
                },

                // Handle fast scrolling edge cases
                onUpdate: (self) => {},
            });
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
                        ['customer_name', 'customer_email', 'customer_phone', 'no_of_persons', 'customer_address',
                            'customer_notes', 'service_start_date', 'service_start_time', 'service_end_date',
                            'service_end_time'
                        ]
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
    <script>
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
