<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    {{-- CSRF Token --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Dynamic URL configuration --}}
    @php
        $currentHost = request()->getHost();
        $masterHost = parse_url(config('app.url'), PHP_URL_HOST);
        $isCustomDomainOrSubdomain = $currentHost !== $masterHost;

        $storeUrl = $isCustomDomainOrSubdomain ? url('/') : url($business_card_details->card_url);
        $productUrlPrefix = $isCustomDomainOrSubdomain
            ? url('product')
            : url($business_card_details->card_url . '/product');
    @endphp

    <title>{{ __('Categories') }} - {{ $card_details->title }}</title>

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

        .category-card {
            opacity: 1;
            visibility: visible;
        }

        .category-item-hidden {
            display: none !important;
            opacity: 0;
            transform: translateY(20px);
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

    <!-- Main Categories Page Container -->
    <main class="max-w-6xl mx-auto px-4 py-6 sm:py-8 space-y-6 sm:space-y-8">

        <!-- Top Row: Breadcrumb (Left) & Back to Store Button (Right) -->
        <div class="flex items-center justify-between gap-3 border-b border-slate-200/80 dark:border-slate-800/80 pb-4">
            <!-- Left Breadcrumbs -->
            <div class="flex items-center gap-2 text-xs font-semibold text-slate-500 dark:text-slate-400">
                <a href="{{ $storeUrl }}"
                    class="hover:text-indigo-600 dark:hover:text-indigo-400 transition">{{ __('Home') }}</a>
                <span class="text-slate-300 dark:text-slate-600">&rsaquo;</span>
                <span class="text-indigo-600 dark:text-indigo-400 font-bold">{{ __('Categories') }}</span>
            </div>

            <!-- Right Back to Store Button -->
            <a href="{{ $storeUrl }}"
                class="inline-flex items-center gap-1.5 bg-white dark:bg-slate-800/90 hover:bg-indigo-600 hover:text-white text-slate-700 dark:text-slate-200 font-semibold px-3.5 py-2 rounded-xl text-xs border border-slate-200 dark:border-slate-700/80 transition shadow-xs">
                <i class="ti ti-arrow-left text-xs"></i>
                <span>{{ __('Back to Store') }}</span>
            </a>
        </div>

        <!-- Page Heading -->
        <div class="pt-1">
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                {{ __('Explore Digital Categories') }}
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">
                {{ __('Browse our curated collections of templates, source code, UI kits, and digital assets.') }}
            </p>
        </div>

        <!-- Categories Cards Grid -->
        @if (isset($categories) && count($categories) > 0)
            <div id="categoriesGrid" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6 pt-2">
                @foreach ($categories as $index => $category)
                    @php
                        $categorySlug = urlencode(strtolower($category->category_name));
                        $categoryUrl = $storeUrl . '?category=' . $categorySlug;
                    @endphp
                    <a href="{{ $categoryUrl }}" data-category-index="{{ $index }}"
                        class="category-card group bg-white dark:bg-[#111625] border border-slate-200 dark:border-slate-800 hover:border-indigo-500/60 rounded-2xl p-5 sm:p-6 flex flex-col items-center justify-center text-center transition-all duration-300 hover:-translate-y-1.5 shadow-sm hover:shadow-xl relative overflow-hidden {{ $index >= 8 ? 'category-item-hidden' : '' }}">

                        <!-- Top Ambient Glow -->
                        <div
                            class="absolute inset-0 bg-gradient-to-b from-indigo-500/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity">
                        </div>

                        <!-- Category Thumbnail -->
                        <div
                            class="relative w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-slate-100 dark:bg-slate-900/80 border border-slate-200 dark:border-slate-800 p-3 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-300 shadow-sm">
                            <img src="{{ url($category->thumbnail) }}" class="w-full h-full object-contain"
                                alt="{{ $category->category_name }}"
                                onerror="this.src='{{ asset('img/templates/all-categories.png') }}'" />
                        </div>

                        <!-- Category Name -->
                        <h3
                            class="font-bold text-slate-900 dark:text-white text-sm sm:text-base group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors line-clamp-1 m-0">
                            {{ __($category->category_name) }}
                        </h3>

                        <!-- Explore Link -->
                        <span
                            class="text-[11px] font-semibold text-slate-400 group-hover:text-indigo-500 mt-2 flex items-center gap-1 transition-colors">
                            <span>{{ __('Explore') }}</span>
                            <span class="group-hover:translate-x-1 transition-transform">&rarr;</span>
                        </span>
                    </a>
                @endforeach
            </div>

            <!-- Infinite Scroll Loader -->
            <div id="infinite-scroll-trigger"
                class="py-8 flex flex-col items-center justify-center text-center {{ count($categories) <= 8 ? 'hidden' : '' }}">
                <div id="infinite-loading-spinner"
                    class="w-7 h-7 rounded-full border-2 border-slate-300 dark:border-slate-700 border-t-indigo-600 animate-spin mb-2">
                </div>
                <span class="text-xs text-slate-400 font-medium">{{ __('Loading more collections...') }}</span>
            </div>
        @else
            <div
                class="text-center py-20 bg-white dark:bg-[#111625] rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm">
                <span class="text-4xl mb-3 inline-block">📂</span>
                <h3 class="text-base font-bold text-slate-800 dark:text-slate-200">{{ __('No Categories Found') }}
                </h3>
            </div>
        @endif

    </main>

    <!-- Bottom Bar Component Include -->
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

        // Progressive Infinite Scroll for Categories
        let isFetchingMore = false;
        const batchSize = 6;

        function initInfiniteScroll() {
            const hiddenCards = document.querySelectorAll('.category-item-hidden');
            const triggerEl = document.getElementById('infinite-scroll-trigger');

            if (hiddenCards.length === 0) {
                if (triggerEl) triggerEl.style.display = 'none';
                return;
            }

            window.addEventListener('scroll', () => {
                if (isFetchingMore) return;

                const scrollPosition = window.innerHeight + window.scrollY;
                const threshold = document.body.offsetHeight - 400;

                if (scrollPosition >= threshold) {
                    loadMoreCategories();
                }
            });
        }

        function loadMoreCategories() {
            const hiddenCards = Array.from(document.querySelectorAll('.category-item-hidden'));
            const triggerEl = document.getElementById('infinite-scroll-trigger');

            if (hiddenCards.length === 0) {
                if (triggerEl) triggerEl.style.display = 'none';
                return;
            }

            isFetchingMore = true;
            const nextBatch = hiddenCards.slice(0, batchSize);

            setTimeout(() => {
                nextBatch.forEach(card => {
                    card.classList.remove('category-item-hidden');
                    if (typeof gsap !== 'undefined') {
                        gsap.fromTo(card, 
                            { opacity: 0, y: 25 }, 
                            { opacity: 1, y: 0, duration: 0.45, ease: "power2.out" }
                        );
                    }
                });

                const remainingHidden = document.querySelectorAll('.category-item-hidden');
                if (remainingHidden.length === 0 && triggerEl) {
                    triggerEl.style.display = 'none';
                }

                isFetchingMore = false;
            }, 300);
        }

        // Theme Controller
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

        // Swiper Initializer
        const categorySwiper = new Swiper('.categorySwiper', {
            slidesPerView: 'auto',
            spaceBetween: 8,
            freeMode: true,
        });
    </script>

    {{-- Custom JS --}}
    @stack('custom-js')
</body>

</html>
