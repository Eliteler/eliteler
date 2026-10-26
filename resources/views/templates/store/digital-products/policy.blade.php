<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    {{-- CSRF Token --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Dynamic URL configuration for Master Domain, Subdomain, and Custom Domain --}}
    @php
        $currentHost = request()->getHost();
        $masterHost = parse_url(config('app.url'), PHP_URL_HOST);
        $isCustomDomainOrSubdomain = $currentHost !== $masterHost;

        $storeUrl = $isCustomDomainOrSubdomain ? url('/') : url($business_card_details->card_url);
        $productUrlPrefix = $isCustomDomainOrSubdomain
            ? url('product')
            : url($business_card_details->card_url . '/product');
    @endphp

    <title>{{ __($pageTitle) }} - {{ $card_details->title }}</title>

    {{-- Favicon & Store Icon --}}
    @php
        $seo = null;
        if (!empty($business_card_details->seo_configurations)) {
            $decoded = json_decode($business_card_details->seo_configurations);
            $seo = is_object($decoded) ? $decoded : null;
        }
    @endphp

    @if ($seo && !empty($seo->favicon))
        <link rel="icon" href="{{ url($seo->favicon) }}" sizes="512x512" type="image/png">
        <link rel="apple-touch-icon" href="{{ url($seo->favicon) }}">
    @else
        <link rel="icon" href="{{ url($business_card_details->profile) }}" sizes="512x512" type="image/png">
        <link rel="apple-touch-icon" href="{{ url($business_card_details->profile) }}">
    @endif

    <meta name="theme-color" content="#4f46e5" />
    <meta name="application-name" content="{{ $card_details->title }}">
    <meta name="apple-mobile-web-app-title" content="{{ $card_details->title }}">

    <!-- Tailwind CSS CDN with DarkMode Config -->
    <link href="{{ url('templates/css/store/digital-store.css') }}" rel="stylesheet" />

    <!-- Load jQuery in <head> -->
    <script src="{{ url('js/jquery.min.js') }}"></script>
    <script>
        window.jQuery || document.write('<script src="https://code.jquery.com/jquery-3.7.1.min.js"><\/script>');
    </script>

    <!-- Swiper JS CSS CDN -->
    <link rel="stylesheet" href="{{ url('css/swiper-bundle.min.css') }}" />

    <!-- GSAP CDN -->
    <script src="{{ url('js/gsap.min.js') }}"></script>

    <!-- Google Fonts & Tabler Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="{{ url('css/tabler-icons.min.css') }}">

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        /* Policy Article Typography */
        .policy-content {
            line-height: 1.8;
            font-size: 0.925rem;
        }

        .policy-content h1,
        .policy-content h2,
        .policy-content h3,
        .policy-content h4 {
            font-weight: 800;
            margin-top: 1.5rem;
            margin-bottom: 0.75rem;
            letter-spacing: -0.02em;
        }

        .policy-content p {
            margin-bottom: 1.25rem;
        }

        .policy-content ul,
        .policy-content ol {
            margin-left: 1.5rem;
            margin-bottom: 1.25rem;
        }

        .policy-content li {
            margin-bottom: 0.5rem;
        }

        .policy-content a {
            color: #6366f1;
            text-decoration: underline;
        }
    </style>

    {{-- Custom CSS/JS from Backend --}}
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
            \
            n " . $plain_js . "\
            n
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

    {{-- SEO Tags --}}
    {!! SEOMeta::generate() !!}
    {!! OpenGraph::generate() !!}
    {!! Twitter::generate() !!}
    {!! JsonLd::generate() !!}

    @php
        use Illuminate\Support\Facades\DB;
        $config = DB::table('config')->get();
    @endphp

    {{-- Custom CSS/JS from Backend --}}
    @stack('page-css')
</head>

<body
    class="bg-slate-50 dark:bg-[#0b0f19] text-slate-800 dark:text-slate-100 min-h-screen antialiased transition-colors duration-300 relative pb-14 md:pb-0"
    dir="{{ App::isLocale('ar') || App::isLocale('ur') || App::isLocale('he') ? 'rtl' : 'ltr' }}">

    <!-- Fullscreen Preloader -->
    @include('templates.store.digital-products.includes.preloader')

    <!-- Toast Notification -->
    <div id="cart-toast"
        class="fixed top-5 right-5 z-50 bg-emerald-600 text-white text-xs font-semibold px-4 py-2.5 rounded-xl shadow-2xl flex items-center gap-2 -translate-y-20 opacity-0 pointer-events-none transition-all">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
        </svg>
        <span id="toast-message">{{ __('Item added to cart!') }}</span>
    </div>

    <!-- Header Include -->
    @include('templates.store.digital-products.includes.header')

    <!-- Main Content -->
    <main class="max-w-4xl mx-auto px-4 py-6 sm:py-8 space-y-6">

        <!-- Top Row: Breadcrumbs & Back Button -->
        <div class="flex items-center justify-between gap-3 border-b border-slate-200/80 dark:border-slate-800/80 pb-4">
            <div class="flex items-center gap-2 text-xs font-semibold text-slate-500 dark:text-slate-400">
                <a href="{{ $storeUrl }}"
                    class="hover:text-indigo-600 dark:hover:text-indigo-400 transition">{{ __('Home') }}</a>
                <span class="text-slate-300 dark:text-slate-600">&rsaquo;</span>
                <span class="text-indigo-600 dark:text-indigo-400 font-bold truncate">{{ __($pageTitle) }}</span>
            </div>

            <a href="{{ $storeUrl }}"
                class="inline-flex items-center gap-1.5 bg-white dark:bg-slate-800/90 hover:bg-indigo-600 hover:text-white text-slate-700 dark:text-slate-200 font-semibold px-3.5 py-2 rounded-xl text-xs border border-slate-200 dark:border-slate-700/80 transition shadow-xs flex-shrink-0">
                <i class="ti ti-arrow-left text-xs"></i>
                <span>{{ __('Back to Store') }}</span>
            </a>
        </div>

        <!-- Policy Card -->
        <div
            class="bg-white dark:bg-[#111625] border border-slate-200 dark:border-slate-800/80 rounded-3xl p-6 sm:p-10 shadow-xl space-y-6">

            <!-- Heading with Icon -->
            <div class="border-b border-slate-100 dark:border-slate-800/80 pb-6 flex items-center gap-4">
                <div
                    class="w-12 h-12 rounded-2xl bg-indigo-50 dark:bg-indigo-950/50 border border-indigo-200 dark:border-indigo-800/50 text-indigo-600 dark:text-indigo-400 flex items-center justify-center flex-shrink-0 shadow-sm">
                    <i class="ti ti-shield-check text-2xl"></i>
                </div>
                <div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight m-0">
                        {{ __($pageTitle) }}
                    </h1>
                    <p class="text-xs text-slate-400 dark:text-slate-500 mt-1 m-0">
                        {{ __('Official store legal documentation and user privacy guidelines.') }}
                    </p>
                </div>
            </div>

            <!-- Policy Dynamic Body -->
            <div class="policy-content text-slate-600 dark:text-slate-300">
                {!! $pageContent ?? '' !!}
            </div>

        </div>

    </main>

    <!-- Bottom Bar Component Include (Footer + Mobile Navigation) -->
    @include('templates.store.digital-products.includes.bottom-bar')

    <!-- WhatsApp Float Component Include -->
    @include('templates.store.digital-products.includes.whatsapp-float', [
        'businessImage' => $business_card_details->profile,
        'businessName' => $card_details->title,
        'whatsappNumber' => $enquiry_button,
    ])

    <!-- Reusable Cart & Order Modals with Full JS Engine -->
    @include('templates.store.digital-products.partials.cart-order-modal')

    <!-- Swiper JS CDN -->
    <script src="{{ url('js/swiper-bundle.min.js') }}"></script>

    <script>
        // Theme Toggle Controller
        function toggleTheme() {
            const html = document.documentElement;
            if (!html) return;

            if (html.classList.contains('dark')) {
                html.classList.remove('dark');
                localStorage.setItem('theme', 'light');
            } else {
                html.classList.add('dark');
                localStorage.setItem('theme', 'dark');
            }
        }

        if (localStorage.getItem('theme') === 'light') {
            document.documentElement.classList.remove('dark');
        }
    </script>

    {{-- Custom JS --}}
    @stack('custom-js')
</body>

</html>
