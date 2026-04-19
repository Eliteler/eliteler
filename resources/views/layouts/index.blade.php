@php
    use Illuminate\Support\Facades\DB;
    use App\Setting;

    // Queries
    $config = DB::table('config')->get();
    $settings = Setting::where('status', 1)->first();
    $supportPage = DB::table('pages')->where('page_name', 'footer')->orWhere('page_name', 'contact')->get();
@endphp

<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- SEO --}}
    @if (isset($setting) && $setting)
        {!! SEOMeta::generate() !!}
        {!! OpenGraph::generate() !!}
        {!! Twitter::generate() !!}
        {!! JsonLd::generate() !!}
    @endif

    {{-- Title --}}
    @if (isset($title) && $title)
        <title>{{ $title }}</title>
    @endif

    {{-- Google verification --}}
    @if (isset($setting) && $setting)
        <meta name="google-site-verification" content="{{ $settings->google_key }}">
        <link rel="icon" href="{{ asset($settings->favicon) }}" sizes="96x96" type="image/png" />
    @endif

    {{-- Check Recaptcha --}}
    @if (env('RECAPTCHA_ENABLE') == 'on')
        {!! htmlScriptTagJsApi() !!}
    @endif

    <!-- CSS files -->
    <link rel="stylesheet" href="{{ asset('app/css/tailwind.min.css') }}">
    <link rel="stylesheet" href="{{ asset('app/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/sweetalert.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/fontawesome.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/tabler-icons.min.css') }}">
    <script type="text/javascript" src="{{ asset('js/jquery.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/theme.bundle.js') }}"></script>
    <script type="text/javascript" src="{{ asset('app/js/main.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/alpine.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/sweetalert.min.js') }}"></script>

    @if (isset($setting) && $setting)
        {{-- Check Google Analytics is "enabled" --}}
        @if (!empty($settings->google_analytics_id) && Cookie::get('laravel_cookie_consent') === '1')
            <!-- Global site tag (gtag.js) - Google Analytics -->
            <script async src="https://www.googletagmanager.com/gtag/js?id={{ $settings->google_analytics_id }}"></script>
            <script>
                window.dataLayer = window.dataLayer || [];

                function gtag() {
                    dataLayer.push(arguments);
                }
                gtag('js', new Date());

                gtag('config', '{{ $settings->google_analytics_id }}');
            </script>
        @endif

        @if ($settings->google_adsense_code != 'DISABLE_ADSENSE_ONLY' && Cookie::get('laravel_cookie_consent') === '1')
            {{-- AdSense code --}}
            <script async
                src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client={{ $settings->google_adsense_code }}"
                crossorigin="anonymous"></script>
        @endif
    @endif

    {{-- Pusher --}}
    @if (env('PUSHER_BEAMS_ENABLED') == '1' && Cookie::get('laravel_cookie_consent') === '1')
        <script src="https://js.pusher.com/beams/1.0/push-notifications-cdn.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const beamsClient = new PusherPushNotifications.Client({
                    instanceId: "{{ env('PUSHER_BEAMS_INSTANCE_ID') }}", // Replace with your Pusher Beams instance ID
                    serviceWorkerPath: '/service-worker.js', // Correctly specify the service worker path
                });

                // Start the Beams client for guest users
                beamsClient
                    .start()
                    .then(() => beamsClient.addDeviceInterest('global')) // Register guest users under 'global' interest
                    .then(() => console.log('Successfully registered for global notifications'))
                    .catch(console.error);

                // For logged-in users, optionally set their unique user ID
                @auth
                beamsClient.setUserId('{{ auth()->id() }}', {
                        headers: {
                            Authorization: 'Bearer {{ csrf_token() }}', // Or another token mechanism
                        },
                    })
                    .then(() => console.log('User ID set for logged-in user'))
                    .catch(console.error);
            @endauth
            });
        </script>
    @endif

    {{-- Custom CSS / JS --}}
    @yield('custom-script')

    {{-- CSS/JS from admin settings --}}
    @if (isset($settings) && $settings)
        @php
            $custom_css = $settings->custom_css;
            $custom_js = $settings->custom_scripts;

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
    @endif
</head>

<body class="antialiased bg-body text-body font-body"
    dir="{{ App::isLocale('ar') || App::isLocale('ur') || App::isLocale('he') ? 'rtl' : 'ltr' }}">

    <!-- Maintenance Mode Alert (conditionally displayed) -->
    @if (app()->isDownForMaintenance())
        @include('website.includes.maintenance-mode')
    @endif

    <div class="{{ app()->isDownForMaintenance() ? 'mt-28 lg:mt-16' : '' }}">

        {{-- Failed Alert Message using Tailwind CSS --}}
        @if (Session::has('failed'))
            <div id="failed-alert" 
                class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 backdrop-blur-md">
                <div class="bg-white border border-gray-200 rounded-lg shadow-xl max-w-sm w-full mx-4 p-6">
                    <div class="flex items-center">
                        <!-- Text Content -->
                        <div class="text-left">
                            <h3 class="text-lg font-semibold text-gray-900">
                               {{ __('Customer Registration Closed') }}
                            </h3>
                            <p class="text-sm text-gray-600 mt-2">
                                {{ Session::get('failed') }}
                            </p>
                        </div>
                    </div>
                    <!-- Close Button -->
                    <div class="mt-4 text-center">
                        <button onclick="closeModal()" 
                                class="bg-red-500 text-white px-4 py-2 rounded-md shadow hover:bg-red-600 focus:outline-none">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        @endif

        {{-- Header --}}
        @if (isset($nav) && $nav)
            @include('website.includes.header')
        @endif

        {{-- Page Content --}}
        @yield('content')

        <!-- HTML to display the notification -->
        <div id="notifications"></div>

        {{-- Cookie consent --}}
        @if (env('COOKIE_CONSENT_ENABLED') == true)
            @include('cookie-consent::index')
        @endif
    </div>

    {{-- WhatsApp Chatbot --}}
    @if (isset($config) && $config)
        @php
            $wp_configs = collect($config)->pluck('config_value', 'config_key');
            $show_chatbot = $wp_configs['show_whatsapp_chatbot'] ?? '0';
        @endphp
        @if ($show_chatbot == '1')
            <div class="fixed bottom-6 right-6 z-50" style="right: 30px; bottom: 30px; position: fixed; z-index: 9999;">
                <div id="whatsappPopup" class="mb-4 bg-white rounded-lg shadow-xl border border-gray-200 w-64 overflow-hidden transition-all duration-300" style="display: none; position: absolute; bottom: 65px; left: -195px; width: 250px;">
                    <div class="bg-green-500 text-white p-3 font-semibold text-sm flex justify-between items-center">
                        <span>{{ __('Chat with us') }}</span>
                        <button onclick="document.getElementById('whatsappPopup').style.display = 'none'" class="text-white hover:text-gray-200 focus:outline-none fill-current" style="background: none; border: none; cursor: pointer;">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="width: 16px; height: 16px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>
                    <div class="max-h-64 overflow-y-auto w-full p-2 space-y-2">
                        @for($i = 1; $i <= 6; $i++)
                            @php
                                $numKey = $i == 1 ? 'whatsapp_chatbot_mobile_number' : "whatsapp_chatbot_mobile_number_{$i}";
                                $msgKey = $i == 1 ? 'whatsapp_chatbot_message' : "whatsapp_chatbot_message_{$i}";
                                $nameKey = "whatsapp_chatbot_name_{$i}";
                                $num = $wp_configs[$numKey] ?? null;
                                $msg = $wp_configs[$msgKey] ?? '';
                                $name = $wp_configs[$nameKey] ?? '';
                            @endphp
                            @if(!empty($num))
                                <a href="https://api.whatsapp.com/send?phone={{ $num }}&text={{ urlencode($msg) }}" target="_blank" class="flex items-center p-2 rounded-md hover:bg-gray-100 transition-colors w-full border border-gray-100 mt-2 text-decoration-none" style="display: flex; align-items: center; padding: 10px; text-decoration: none; border-bottom: 1px solid #f1f1f1;">
                                    <div class="bg-green-100 text-green-600 rounded-full w-8 h-8 flex items-center justify-center me-3 flex-shrink-0" style="width: 32px; height: 32px; border-radius: 50%; background-color: #d1fae5; color: #059669; display: flex; align-items: center; justify-content: center; margin-right: 12px;">
                                        <i class="fab fa-whatsapp"></i>
                                    </div>
                                    <div style="padding-left: 10px; padding-right: 10px; color: #1f2937;" class="text-sm text-gray-800 font-medium truncate">{{ $name ?: __('Support') }}</div>
                                </a>
                            @endif
                        @endfor
                    </div>
                </div>
                
                <button onclick="var el = document.getElementById('whatsappPopup'); el.style.display = el.style.display === 'none' ? 'block' : 'none';" class="whatapp-chatbot cursor-pointer flex items-center justify-center border-0 shadow-lg" style="position: relative !important; right: auto; bottom: auto; width: 60px; height: 60px; border: none; background: transparent; outline: none; padding: 0; cursor: pointer;">
                    <i class="fab fa-whatsapp whatapp-chatbot-icon"></i>
                </button>
            </div>
        @endif
    @endif

    {{-- Tawk.to Chat --}}
    @if (isset($settings) && $settings)
        @if (
            $settings->tawk_chat_bot_key != null &&
                $config[40]->config_value != '1' &&
                Cookie::get('laravel_cookie_consent') === '1')
            <!--Start of Tawk.to Script-->
            <script>
                (function($) {
                    "use strict";
                    var Tawk_API = Tawk_API || {},
                        Tawk_LoadStart = new Date();
                    var s1 = document.createElement("script"),
                        s0 = document.getElementsByTagName("script")[0];
                    s1.async = true;
                    s1.src = 'https://embed.tawk.to/{{ $settings->tawk_chat_bot_key }}';
                    s1.charset = 'UTF-8';
                    s1.setAttribute('crossorigin', '*');
                    s0.parentNode.insertBefore(s1, s0);
                })(jQuery);
            </script>
            <!--End of Tawk.to Script-->
        @endif
    @endif

    {{-- Footer --}}
    @if (isset($footer) && $footer)
        @include('website.includes.footer')
    @endif

    <!-- Smooth Scroll -->
    <script type="text/javascript" src="{{ asset('js/smooth-scroll.polyfills.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('app/js/footer.js') }}"></script>

    {{-- Custom JS --}}
    @yield('custom-js')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            "use strict"; // Avoids conflicts with other scripts

            const alert = document.getElementById('failed-alert');
            if (alert) {
                setTimeout(() => {
                    alert.style.opacity = '0'; // Fades out
                    setTimeout(() => alert.remove(), 300); // Removes after fade-out
                }, 10000); // Auto close after 10 seconds
            }
        });

        function closeModal() {
            "use strict"; // Avoids conflicts with other scripts
            
            const alert = document.getElementById('failed-alert');
            if (alert) alert.remove(); // Removes the modal on close button click
        }
    </script>
</body>

</html>
