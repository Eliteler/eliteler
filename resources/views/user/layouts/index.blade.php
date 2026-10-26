@php
    // Queries
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Schema;
    use App\Services\GoBizCommonService;

    $config = DB::table('config')->get();
    $template_config = GoBizCommonService::gobizModernConfig();
@endphp

<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ App::isLocale('ar') || App::isLocale('ur') || App::isLocale('he') ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name') }}</title>

    <link rel="icon" href="{{ asset($settings->favicon) }}" sizes="96x96" type="image/png" />

    <!-- CSS files -->
    @if (App::isLocale('ar') || App::isLocale('ur') || App::isLocale('he'))
        <link href="{{ asset('css/tabler.rtl.min.css') }}" rel="stylesheet" />
        <link href="{{ asset('css/tabler-flags.rtl.min.css') }}" rel="stylesheet" />
        <link href="{{ asset('css/tabler-payments.rtl.min.css') }}" rel="stylesheet" />
        <link href="{{ asset('css/tabler-vendors.rtl.min.css') }}" rel="stylesheet" />
        <link href="{{ asset('css/demo.rtl.min.css') }}" rel="stylesheet" />
    @else
        <link href="{{ asset('css/tabler.min.css') }}" rel="stylesheet" />
        <link href="{{ asset('css/tabler-flags.min.css') }}" rel="stylesheet" />
        <link href="{{ asset('css/tabler-payments.min.css') }}" rel="stylesheet" />
        <link href="{{ asset('css/tabler-vendors.min.css') }}" rel="stylesheet" />
        <link href="{{ asset('css/demo.min.css') }}" rel="stylesheet" />
    @endif

    {{-- 1. Load the stylesheet first --}}
    <link rel="stylesheet" href="{{ asset('css/gobiz-style.min.css') }}">

    @php
        // 1. Get active template name from config
        $activeTemplate = DB::table('config')
            ->where('config_key', 'web_template')
            ->orWhere('id', 94)
            ->value('config_value');

        $tableMap = [
            'gobizmodern'   => 'gobiz_modern_config',
            'gobizoriginal' => 'gobiz_original_config',
            'gobizrich'     => 'gobiz_rich_config',
        ];

        $cleanKey = strtolower(str_replace([' ', '_', '-'], '', $activeTemplate ?? ''));
        $targetTable = $tableMap[$cleanKey] ?? 'gobiz_original_config';

        // 2. Fetch template_color (e.g. 'red')
        $rawColor = 'red';
        if (Schema::hasTable($targetTable)) {
            $rawColor = DB::table($targetTable)->value('template_color') ?? 'red';
        }
        $rawColor = strtolower(trim($rawColor));

        // 3. Vibrant & High-Contrast Colors (Ensures charts NEVER turn dark/black)
        $colorDictionary = [
            'blue'    => '#3b82f6',
            'indigo'  => '#6366f1',
            'green'   => '#10b981',
            'yellow'  => '#f59e0b',
            'red'     => '#ef4444',
            'purple'  => '#a855f7',
            'pink'    => '#ec4899',
            'gray'    => '#94a3b8',
            'slate'   => '#94a3b8',
            'zinc'    => '#a1a1aa',
            'neutral' => '#a3a3a3',
            'stone'   => '#a8a29e',
            'orange'  => '#f97316',
            'amber'   => '#f59e0b',
            'lime'    => '#84cc16',
            'emerald' => '#10b981',
            'teal'    => '#14b8a6',
            'cyan'    => '#06b6d4',
            'sky'     => '#0ea5e9',
            'violet'  => '#8b5cf6',
            'fuchsia' => '#d946ef',
            'rose'    => '#f43f5e',
        ];

        $classMap = [
            'bg-blue'   => 'blue',   'bg-indigo' => 'indigo', 'bg-green' => 'green',
            'bg-yellow' => 'yellow', 'bg-red'    => 'red',    'bg-purple' => 'purple',
            'bg-pink'   => 'pink',   'bg-muted'  => 'gray',
        ];

        if (isset($classMap[$rawColor])) {
            $rawColor = $classMap[$rawColor];
        }

        $themeHex = $colorDictionary[$rawColor] ?? (preg_match('/^#[a-f0-9]{6}$/i', $rawColor) ? $rawColor : '#ef4444');

        $cleanHex = ltrim($themeHex, '#');
        $r = hexdec(substr($cleanHex, 0, 2));
        $g = hexdec(substr($cleanHex, 2, 2));
        $b = hexdec(substr($cleanHex, 4, 2));
        $themeRgb = "{$r}, {$g}, {$b}";
        $themeFg  = '#ffffff';
    @endphp

    <style>
        /* ======================================================================
        DYNAMIC SYSTEM COLOR & SURFACE SECONDARY INJECTION
        ====================================================================== */

        /* Light Mode */
        :root,
        [data-bs-theme="light"],
        body:not([data-bs-theme="dark"]) {
            --tblr-primary: {{ $themeHex }} !important;
            --tblr-primary-rgb: {{ $themeRgb }} !important;
            --tblr-primary-fg: {{ $themeFg }} !important;
            --tblr-primary-lt: rgba({{ $themeRgb }}, 0.15) !important;
            --tblr-blue: {{ $themeHex }} !important;
            --tblr-blue-rgb: {{ $themeRgb }} !important;
            
            /* Secondary Surface for Light Mode */
            --tblr-bg-surface-secondary: #ededed !important;
        }

        /* Dark Mode */
        [data-bs-theme="dark"],
        body.theme-dark {
            --tblr-primary: {{ $themeHex }} !important;
            --tblr-primary-rgb: {{ $themeRgb }} !important;
            --tblr-primary-fg: {{ $themeFg }} !important;
            --tblr-primary-lt: rgba({{ $themeRgb }}, 0.15) !important;
            --tblr-blue: {{ $themeHex }} !important;
            --tblr-blue-rgb: {{ $themeRgb }} !important;

            /* Secondary Surface for Dark Mode */
            --tblr-bg-surface-secondary: #172442 !important;
        }
    </style>

    <link href="{{ asset('css/styles.css') }}" rel="stylesheet" />
    <link href="{{ asset('css/custom.css') }}" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('css/wa.css') }}">
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
    <link href="{{ asset('css/fontawesome-iconpicker.min.css') }}" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">

    {{-- JS --}}
    <link rel="stylesheet" href="{{ asset('css/dataTables.bootstrap4.min.css') }}">
    <script type="text/javascript" src="{{ asset('js/jquery.min.js') }}"></script>
    <script src="{{ asset('js/chart.js') }}"></script>

    {{-- GSAP --}}
    <script type="text/javascript" src="{{ asset('js/gsap.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/animation.js') }}"></script>
    
    {{-- Custom CSS --}}
    @yield('css')

    @if (isset($settings) && $settings)
        {{-- Check Google Analytics is "enabled" --}}
        @if (!empty($settings->google_analytics_id) && (Cookie::get('laravel_cookie_consent') === '1' || env('COOKIE_CONSENT_ENABLED') == ""))
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

        @if ($settings->google_adsense_code != 'DISABLE_ADSENSE_ONLY' && (Cookie::get('laravel_cookie_consent') === '1' || env('COOKIE_CONSENT_ENABLED') == ""))
            {{-- AdSense code --}}
            <script async
                src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client={{ $settings->google_adsense_code }}"
                crossorigin="anonymous"></script>
        @endif
    @endif

    {{-- Admin injected CSS / JS --}}
    @if (isset($template_config) && $template_config)
        @php
            $custom_css = $template_config->custom_css;

            if ($custom_css && !str_contains($custom_css, '<style')) {
                $custom_css = '<style>' . $custom_css . '</style>';
            }
        @endphp

        {!! $custom_css !!}
    @endif
</head>

<body data-bs-theme="{{ optional(Auth::user())->choosed_theme == 'light' ? 'light' : 'dark' }}">

    {{-- Preloader --}}
    <div id="nativecode-loader">
        <div class="nativecode-loading"></div>
    </div>

    <div id="wrapper" class="page bg-body">

        @if (isset($nav) && $nav)
            @include('user.includes.sidebar')
        @endif

        @if (isset($header) && $header)
            @include('user.includes.header')
        @endif

        {{-- Check email verification --}}
        @if ($config[43]->config_value == '1' && auth()->check() && auth()->user()->email_verified_at == null)
            <div class="page-wrapper mt-3">
                @include('auth.verification-notice')
            </div>
        @else 
            @yield('content')
        @endif
    </div>

    <!-- Tabler Core -->
    <script type="text/javascript" src="{{ asset('js/tabler.min.js') }}"></script>
    @if (isset($demo) && $demo)
        <script type="text/javascript" src="{{ asset('js/user-delete-query.js') }}"></script>
        <script type="text/javascript" src="{{ asset('js/jquery.dataTables.min.js') }}"></script>
        <script type="text/javascript" src="{{ asset('js/dataTables.bootstrap4.min.js') }}"></script>
        <script type="text/javascript" src="{{ asset('js/datatable.js') }}"></script>
        <script type="text/javascript" src="{{ asset('js/jquery-qrcode.min.js') }}"></script>
        <script type="text/javascript" src="{{ asset('js/sweetalert.min.js') }}"></script>
    @endif

    {{-- WhatsApp Chatbot --}}
    @if (isset($config[100]->config_value) && $config[100]->config_value == '1')
        @include('user.includes.whatsapp-chatbot')
    @endif

    {{-- Tawk.to Chat --}}
    @if (isset($settings) && $settings)
        @if (
            $settings->tawk_chat_bot_key != null && 
                $config[40]->config_value != '1' &&
                (Cookie::get('laravel_cookie_consent') === '1' || env('COOKIE_CONSENT_ENABLED') == ""))
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

    {{-- Preloader --}}
    <script>
        $(document).click(function(event) {
            "use strict";

            var clickover = $(event.target);
            var $navbar = $(".navbar-collapse");
            var _opened = $navbar.hasClass("show");
            if (_opened && !clickover.closest('.navbar').length) {
                $navbar.collapse('hide');
            }
        });

        // Wait for the window to load
        window.onload = function() {
            "use strict";

            // Get the preloader element
            var preloader = document.getElementById('nativecode-loader');

            // Add the fade-out class to start the fade-out effect
            setTimeout(function() {
                preloader.classList.add('fade-out');

                // After the fade-out effect, remove the preloader and show the content
                setTimeout(function() {
                    preloader.style.display = 'none'; // Remove preloader from view
                    document.querySelector('.page').style.display = 'flex'; // Show content
                }, 7000); // This duration matches the CSS transition duration (2s)
            }, 100); // Optional delay to allow the page to load fully
        };

        // Change langages
        $('#chooseLang, #selectLang').change(function() {
            "use strict";

            // set the window's location property to the value of the option the user has selected
            window.location = '?change_language=' + $(this).val();
        });

        // Close sidebar
        document.addEventListener("click", function (event) {
            "use strict";

            const sidebar = document.getElementById("sidebar-menu");
            const toggler = document.querySelector(".navbar-toggler");

            // Check if the sidebar is open
            if (sidebar.classList.contains("show")) {
                // If the click is outside the sidebar and the toggler button
                if (!sidebar.contains(event.target) && !toggler.contains(event.target)) {
                    const bsCollapse = new bootstrap.Collapse(sidebar, {
                        toggle: false
                    });
                    bsCollapse.hide(); // Close the sidebar
                }
            }
        });

        $.ajaxSetup({
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        // Support multiple tables on the same page, and skip safely if none exist
        document.addEventListener('DOMContentLoaded', function () {
            // Support multiple tables on the same page, and skip safely if none exist
            document.querySelectorAll('.table-responsive').forEach(function (el) {
                let isDown = false, startX, scrollLeft;

                el.addEventListener('mousedown', (e) => {
                    isDown = true;
                    el.style.cursor = 'grabbing';
                    startX = e.pageX - el.offsetLeft;
                    scrollLeft = el.scrollLeft;
                });

                el.addEventListener('mouseleave', () => {
                    isDown = false;
                    el.style.cursor = 'grab';
                });

                el.addEventListener('mouseup', () => {
                    isDown = false;
                    el.style.cursor = 'grab';
                });

                el.addEventListener('mousemove', (e) => {
                    if (!isDown) return;
                    e.preventDefault();
                    const x = e.pageX - el.offsetLeft;
                    const walk = (x - startX) * 1.5;
                    el.scrollLeft = scrollLeft - walk;
                });

                el.style.cursor = 'grab';
            });
        });
    </script>

    {{-- Admin injected CSS / JS --}}
    @if (isset($template_config) && $template_config)
        @php
            $custom_js  = $template_config->custom_js;

            if ($custom_js && !str_contains($custom_js, '<script')) {
                $custom_js = '<script>' . $custom_js . '</script>';
            }
        @endphp

        {!! $custom_js !!}
    @endif

    {{-- Links marked data-method="post" (delete, duplicate, status, accept/reject...) submit as a
         POST with the CSRF token, so these actions can't be triggered by just opening a URL --}}
    <script>
        document.addEventListener('click', function(event) {
            "use strict";
            var link = event.target.closest('a[data-method="post"]');
            if (!link || !link.getAttribute('href') || link.getAttribute('href') === '#') return;
            event.preventDefault();
            if (link.dataset.submitting) return; // ignore double clicks
            link.dataset.submitting = '1';
            var form = document.createElement('form');
            form.method = 'POST';
            form.action = link.href;
            form.style.display = 'none';
            var token = document.createElement('input');
            token.type = 'hidden';
            token.name = '_token';
            token.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            form.appendChild(token);
            document.body.appendChild(form);
            form.submit();
        });
    </script>

    @yield('scripts')
    
    @stack('custom-js')
</body>

</html>
