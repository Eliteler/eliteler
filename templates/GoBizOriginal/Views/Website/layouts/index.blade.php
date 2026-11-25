@php
    use Illuminate\Support\Facades\DB;
    use App\Services\GoBizCommonService;

    $settings = is_object($settings ?? null) ? $settings : GoBizCommonService::settings();

    // Queries
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
    @if (isset($setting) && $setting && is_object($settings))
        <meta name="google-site-verification" content="{{ $settings->google_key }}">
        <link rel="icon" href="{{ asset($settings->favicon) }}" sizes="96x96" type="image/png" />
    @endif

    {{-- Check Recaptcha --}}
    @if (env('RECAPTCHA_ENABLE') == 'on')
        {!! htmlScriptTagJsApi() !!}
    @endif

    <!-- CSS files -->
    <link rel="stylesheet" href="{{ asset('gobiz_original_assets/css/tailwind.min.css') }}">
    <link rel="stylesheet" href="{{ asset('gobiz_original_assets/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('gobiz_original_assets/css/sweetalert.min.css') }}">
    <link rel="stylesheet" href="{{ asset('gobiz_original_assets/css/fontawesome.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('gobiz_original_assets/css/tabler-icons.min.css') }}">
    <script type="text/javascript" src="{{ asset('gobiz_original_assets/js/jquery.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('gobiz_original_assets/js/theme.bundle.js') }}"></script>
    <script type="text/javascript" src="{{ asset('gobiz_original_assets/js/main.js') }}"></script>
    <script type="text/javascript" src="{{ asset('gobiz_original_assets/js/alpine.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('gobiz_original_assets/js/sweetalert.min.js') }}"></script>

    @if (is_object($settings))
        {{-- Check Google Analytics is "enabled" --}}
        @if (!empty($settings->google_analytics_id) && (Cookie::get('laravel_cookie_consent') === '1' || !config('cookie-consent.enabled') || env('COOKIE_CONSENT_ENABLED') == "" || env('COOKIE_CONSENT_ENABLED') == false))
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

        @if (!empty($settings->google_adsense_code) && $settings->google_adsense_code !== 'DISABLE_ADSENSE_ONLY' && (Cookie::get('laravel_cookie_consent') === '1' || !config('cookie-consent.enabled') || env('COOKIE_CONSENT_ENABLED') == "" || env('COOKIE_CONSENT_ENABLED') == false))
            {{-- AdSense code --}}
            <script async
                src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client={{ $settings->google_adsense_code }}"
                crossorigin="anonymous"></script>
        @endif
    @endif

    {{-- Pusher --}}
    @if (env('PUSHER_BEAMS_ENABLED') == '1' && (Cookie::get('laravel_cookie_consent') === '1' || !config('cookie-consent.enabled') || env('COOKIE_CONSENT_ENABLED') == "" || env('COOKIE_CONSENT_ENABLED') == false))
        <script src="{{ asset('gobiz_original_assets/js/pusher.js') }}"></script>
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
    @if (isset($template_config) && $template_config)
        @php
            $custom_css = $template_config->custom_css;

            // Ensure <style> tags for custom CSS
            if (strpos($custom_css, '<style>') === false && strpos($custom_css, '</style>') === false) {
                $custom_css = "<style>" . $custom_css . "</style>";
            }
        @endphp

        {!! $custom_css !!}
    @endif
</head>

<body class="antialiased bg-body text-body font-body"
    dir="{{ App::isLocale('ar') || App::isLocale('ur') || App::isLocale('he') ? 'rtl' : 'ltr' }}">

    {{-- Announcements --}}
    @if (\App\Announcement::where('status', 1)->count() > 0)
        @include('GoBizOriginal::Website.includes.announcements')
    @endif

    {{-- Web Popup --}}
    @if (\App\WebPopup::where('status', 1)->count() > 0)
        @include('GoBizOriginal::Website.includes.web-pop')
    @endif

    <!-- Maintenance Mode Alert (conditionally displayed) -->
    @if (app()->isDownForMaintenance())
        @include('GoBizOriginal::Website.includes.maintenance-mode')
    @endif

    <div class="{{ app()->isDownForMaintenance() ? 'mt-28 lg:mt-16' : '' }}">
        {{-- Registeration Closed Alert Message using Tailwind CSS --}}
        @include('GoBizOriginal::Website.includes.register-closed-alert')

        {{-- Header --}}
        @if (isset($nav) && $nav)
            @include('GoBizOriginal::Website.includes.header')
        @endif

        {{-- Page Content --}}
        @yield('content')

        <!-- HTML to display the notification -->
        <div id="notifications"></div>

        {{-- Cookie consent --}}
        @if (env('COOKIE_CONSENT_ENABLED') == true)
            @include('cookie-consent::index', ['theme_id' => 'GoBizOriginal', 'template_color' => $template_config->template_color])
        @endif
    </div>
 
    {{-- WhatsApp Chatbot (up to 6 contacts, Admin > Plugins > WhatsApp Chat Button) --}}
    @if (isset($config) && $config)
        @php
            $waCfg = collect($config)->pluck('config_value', 'config_key');
            $waContacts = class_exists(\Plugins\WhatsAppChatButton\Controllers\WhatsAppChatButtonController::class)
                ? \Plugins\WhatsAppChatButton\Controllers\WhatsAppChatButtonController::contacts($config)
                : [];
        @endphp
        @if (($waCfg['show_whatsapp_chatbot'] ?? '0') == '1' && count($waContacts) > 0)
            @if (count($waContacts) === 1)
                <a href="https://api.whatsapp.com/send?phone={{ $waContacts[0]['number'] }}&text={{ urlencode($waContacts[0]['message']) }}"
                    class="whatapp-chatbot" target="_blank" rel="noopener">
                    <i class="fab fa-whatsapp whatapp-chatbot-icon"></i>
                </a>
            @else
                <div id="whatsappPopup" style="display:none; position:fixed; right:25px; bottom:95px; width:260px; max-width:calc(100vw - 40px); background:#fff; border-radius:12px; box-shadow:0 10px 30px rgba(0,0,0,.18); overflow:hidden; z-index:101; font-family:inherit;">
                    <div style="background:#25d366; color:#fff; padding:10px 14px; font-weight:600; display:flex; justify-content:space-between; align-items:center;">
                        <span>{{ __('Chat with us') }}</span>
                        <button type="button" onclick="document.getElementById('whatsappPopup').style.display='none'" style="background:none; border:0; color:#fff; font-size:18px; line-height:1; cursor:pointer;">&times;</button>
                    </div>
                    <div style="max-height:280px; overflow-y:auto; padding:8px;">
                        @foreach ($waContacts as $contact)
                            <a href="https://api.whatsapp.com/send?phone={{ $contact['number'] }}&text={{ urlencode($contact['message']) }}" target="_blank" rel="noopener"
                                style="display:flex; align-items:center; gap:10px; padding:10px; border-radius:8px; color:#1f2937; text-decoration:none; border-bottom:1px solid #f1f1f1;">
                                <span style="width:32px; height:32px; border-radius:50%; background:#d1fae5; color:#059669; display:flex; align-items:center; justify-content:center; flex-shrink:0;"><i class="fab fa-whatsapp"></i></span>
                                <span style="font-size:14px; font-weight:500;">{{ $contact['name'] !== '' ? $contact['name'] : __('Support') }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
                <a href="#" class="whatapp-chatbot" onclick="event.preventDefault(); var el = document.getElementById('whatsappPopup'); el.style.display = el.style.display === 'none' ? 'block' : 'none';" aria-label="WhatsApp">
                    <i class="fab fa-whatsapp whatapp-chatbot-icon"></i>
                </a>
            @endif
        @endif
    @endif

    {{-- Tawk.to Chat --}}
    @if (is_object($settings))
        @if (
            $settings->tawk_chat_bot_key != null &&
                $config[40]->config_value != '1' &&
                (Cookie::get('laravel_cookie_consent') === '1' || !config('cookie-consent.enabled') || env('COOKIE_CONSENT_ENABLED') == "" || env('COOKIE_CONSENT_ENABLED') == false))
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
        @include('GoBizOriginal::Website.includes.footer')
    @endif

    <!-- Smooth Scroll -->
    <script type="text/javascript" src="{{ asset('gobiz_original_assets/js/smooth-scroll.polyfills.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('gobiz_original_assets/js/footer.js') }}"></script>

    {{-- CSS/JS from admin settings --}}
    @if (isset($template_config) && $template_config)
        @php
            $custom_js = $template_config->custom_js;

            // Ensure <script> tags for custom JS
            if (strpos($custom_js, '<script>') === false && strpos($custom_js, '</script>') === false) {
                $custom_js = "<script>" . $custom_js . "</script>";
            }
        @endphp

        {!! $custom_js !!}
    @endif

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
