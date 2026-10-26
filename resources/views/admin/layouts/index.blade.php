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
        use Illuminate\Support\Facades\DB;
        use Illuminate\Support\Facades\Schema;

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
    <link rel="stylesheet" href="{{ asset('css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flag-icons@6.6.6/css/flag-icons.min.css" />
    <script type="text/javascript" src="{{ asset('js/jquery.min.js') }}"></script>
    <script src="{{ asset('js/chart.js') }}"></script>
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

<body data-bs-theme="{{ optional(Auth::user())->choosed_theme == 'light' ? 'light' : 'dark' }}">

    {{-- Preloader --}}
    <div id="nativecode-loader">
        <div class="nativecode-loading"></div>
    </div>

    <div id="wrapper" class="page bg-body">

        @if (isset($nav) && $nav)
            @include('admin.includes.sidebar')
        @endif

        @if (isset($header) && $header)
            @include('admin.includes.header')
        @endif
        
        @yield('content')
    </div>

    <!-- Tabler Core -->
    <script type="text/javascript" src="{{ asset('js/tabler.min.js') }}"></script>
    @if (isset($demo) && $demo)
    <script type="text/javascript" src="{{ asset('js/admin-delete-query.js') }}"></script>
    @endif
    <script type="text/javascript" src="{{ asset('js/jquery.dataTables.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/dataTables.bootstrap4.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/datatable.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/search.js') }}"></script>
    <script>
        window.pageSearchUrl = "{{ url('admin/page-search') }}";
        window.resultNoResults = "{{ __('No pages found') }}";
    </script>

    {{-- GSAP --}}
    <script type="text/javascript" src="{{ asset('js/gsap.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/animation.js') }}"></script>

    {{-- Preloader --}}
    <script>
       $(document).on('click', function (event) {
            "use strict";

            const navbar = document.querySelector('.navbar-collapse.show');

            if (navbar && !event.target.closest('.navbar')) {
                bootstrap.Collapse.getOrCreateInstance(navbar).hide();
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

        // Choose langages
        $('#chooseLang, #selectLang').change(function () {
            "use strict";

            // set the window's location property to the value of the option the user has selected
            window.location = `?change_language=` + $(this).val();
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
    </script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            "use strict";

            // 5 Most Used Shortcuts Template Definition using Native Tabler SVG Icons
            const defaultSuggestionsHtml = `
                <div id="default-search-suggestions">
                    <div class="subheader mb-3 px-1 text-uppercase text-secondary" style="font-size: 10px; font-weight: 700; letter-spacing: 0.05em;">
                        {{ __('Recommended Shortcuts') }}
                    </div>
                    
                    <div class="d-flex flex-column">
                        <!-- Dashboard -->
                        <a href="{{ route('admin.dashboard') }}" class="recommended-link">
                            <span class="avatar bg-blue-lt text-primary rounded-3">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-layout-dashboard" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                    <path d="M4 4h6v8h-6z" />
                                    <path d="M14 4h6v6h-6z" />
                                    <path d="M4 16h6v4h-6z" />
                                    <path d="M14 14h6v6h-6z" />
                                </svg>
                            </span>
                            <div class="flex-grow-1">${"{{ __('Dashboard') }}"}</div>
                        </a>
                        
                        <!-- Themes -->
                        <a href="{{ route('admin.themes', ['status' => 'all']) }}" class="recommended-link">
                            <span class="avatar bg-purple-lt text-purple rounded-3">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-palette" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                    <path d="M12 21a9 9 0 0 1 0 -18c4.97 0 9 3.582 9 8c0 1.06 -.424 2.078 -1.184 2.816a4.3 4.3 0 0 1 -2.907 1.184h-1.909a1 1 0 0 0 -.707 .293a1 1 0 0 0 -.293 .707v1a1 1 0 0 1 -1 1z" />
                                    <circle cx="7.5" cy="10.5" r="1" fill="currentColor" />
                                    <circle cx="11.5" cy="7.5" r="1" fill="currentColor" />
                                    <circle cx="16.5" cy="9.5" r="1" fill="currentColor" />
                                </svg>
                            </span>
                            <div class="flex-grow-1">${"{{ __('Themes & Layouts') }}"}</div>
                        </a>
                        
                        <!-- Plans -->
                        <a href="{{ route('admin.plans') }}" class="recommended-link">
                            <span class="avatar bg-success-lt text-success rounded-3">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-credit-card">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                    <path d="M3 8a3 3 0 0 1 3 -3h12a3 3 0 0 1 3 3v8a3 3 0 0 1 -3 3h-12a3 3 0 0 1 -3 -3l0 -8" />
                                    <path d="M3 10l18 0" />
                                    <path d="M7 15l.01 0" />
                                    <path d="M11 15l2 0" />
                                </svg>
                            </span>
                            <div class="flex-grow-1">${"{{ __('Plans & Pricing') }}"}</div>
                        </a>
                        
                        <!-- NFC Card Orders -->
                        <a href="{{ route('admin.orders') }}" class="recommended-link">
                            <span class="avatar bg-orange-lt text-orange rounded-3">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-truck" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                    <circle cx="7" cy="17" r="2" />
                                    <circle cx="17" cy="17" r="2" />
                                    <path d="M5 17h-2v-11a1 1 0 0 1 1 -1h9v12m-4 0h6m4 0h2v-6h-8m0 -5h5l3 5" />
                                </svg>
                            </span>
                            <div class="flex-grow-1">${"{{ __('NFC Card Orders') }}"}</div>
                        </a>
                        
                        <!-- Payment Gateways -->
                        <a href="{{ route('admin.payment.methods') }}" class="recommended-link">
                            <span class="avatar bg-cyan-lt text-cyan rounded-3">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-wallet" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                    <path d="M17 8v-3a1 1 0 0 0 -1 -1h-10a2 2 0 0 0 0 4h12a1 1 0 0 1 1 1v3m0 4v3a1 1 0 0 1 -1 1h-12a2 2 0 0 1 -2 -2v-12" />
                                    <path d="M20 12v4h-4a2 2 0 0 1 0 -4h4z" />
                                </svg>
                            </span>
                            <div class="flex-grow-1">${"{{ __('Payment Gateways') }}"}</div>
                        </a>
                    </div>
                </div>
            `;

            const searchInput = document.getElementById('adminPageSearch');
            const resultsContainer = document.getElementById('adminPageResults');
            const searchModal = document.getElementById('pageSearchModal');

            if (resultsContainer) {
                // First load initialization
                resultsContainer.innerHTML = defaultSuggestionsHtml;
            }

            if (searchModal && searchInput && resultsContainer) {
                
                // Standard reset function to execute on modal show/hide transitions
                const resetSearchModal = function() {
                    // Clear active text inputs
                    if (document.activeElement === searchInput) {
                        // Do not steal focus if already active
                    } else {
                        $('#adminPageSearch').val('');
                    }
                    
                    // Instantly re-inject default shortcuts
                    $('#adminPageResults').html(defaultSuggestionsHtml);
                };

                // Force native browser event listeners to trigger UI restorations
                $('#pageSearchModal').on('show.bs.modal shown.bs.modal hide.bs.modal hidden.bs.modal', function() {
                    $('#adminPageSearch').val('');
                    $('#adminPageResults').html(defaultSuggestionsHtml);
                });

                // Dynamically listen for backspace deletions
                $('#adminPageSearch').on('input change keyup paste', function() {
                    var query = $(this).val().trim();
                    if (query.length === 0) {
                        $('#adminPageResults').html(defaultSuggestionsHtml);
                    }
                });
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

    @yield('scripts')
</body>

</html>