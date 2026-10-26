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

    <title>{{ $product_details->product_name }} - {{ $card_details->title }}</title>

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

        .product-description-content {
            line-height: 1.75;
            font-size: 0.9rem;
        }

        .product-description-content p {
            margin-bottom: 0.95rem;
        }

        .product-description-content ul,
        .product-description-content ol {
            margin-left: 1.25rem;
            margin-bottom: 0.95rem;
        }

        .product-description-content li {
            margin-bottom: 0.35rem;
        }

        .thumb-slide.active-thumb {
            border-color: #6366f1 !important;
            box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.4);
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
        $productImages = explode(',', $product_details->product_image);
        $firstImage = url($productImages[0] ?? '');

        // Calculate Discount
        $hasDiscount = false;
        $discountPercent = 0;
        if ($product_details->regular_price && $product_details->sales_price < $product_details->regular_price) {
            $hasDiscount = true;
            $discountPercent = round(
                (($product_details->regular_price - $product_details->sales_price) / $product_details->regular_price) *
                    100,
            );
        }
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

    <!-- Main Full-Width Product Container -->
    <main class="max-w-6xl mx-auto px-4 py-6 sm:py-8 space-y-10">

        <!-- Top Breadcrumbs & Back Button -->
        <div class="flex items-center justify-between gap-3 border-b border-slate-200/80 dark:border-slate-800/80 pb-4">
            <div class="flex items-center gap-2 text-xs font-semibold text-slate-500 dark:text-slate-400 truncate">
                <a href="{{ $storeUrl }}"
                    class="hover:text-indigo-600 dark:hover:text-indigo-400 transition">{{ __('Home') }}</a>
                <span class="text-slate-300 dark:text-slate-600">&rsaquo;</span>
                <span
                    class="text-indigo-600 dark:text-indigo-400 font-bold truncate">{{ $product_details->product_name }}</span>
            </div>

            <a href="{{ $storeUrl }}"
                class="inline-flex items-center gap-1.5 bg-white dark:bg-slate-800/90 hover:bg-indigo-600 hover:text-white text-slate-700 dark:text-slate-200 font-semibold px-3.5 py-2 rounded-xl text-xs border border-slate-200 dark:border-slate-700/80 transition shadow-xs flex-shrink-0">
                <i class="ti ti-arrow-left text-xs"></i>
                <span>{{ __('Back to Store') }}</span>
            </a>
        </div>

        <!-- Product Primary View (2 Balanced Columns) -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-10 items-start">

            <!-- Left Column: Gallery Display (5 Cols) -->
            <div class="lg:col-span-5 space-y-4 gsap-product-gallery">

                <!-- Main Preview Display -->
                <div
                    class="relative aspect-[4/3] w-full rounded-3xl overflow-hidden border border-slate-200 dark:border-slate-800 bg-slate-100 dark:bg-[#111625] shadow-lg group">
                    <img id="main-product-image" src="{{ $firstImage }}" alt="{{ $product_details->product_name }}"
                        class="w-full h-full object-cover transition-all duration-300 group-hover:scale-105">

                    <!-- Digital Tag -->
                    <span
                        class="absolute top-3 left-3 bg-slate-950/80 backdrop-blur-md text-indigo-400 text-[11px] font-bold px-3 py-1 rounded-xl border border-indigo-500/30 flex items-center gap-1.5 shadow-sm">
                        <i class="ti ti-download"></i> {{ __('Digital Asset') }}
                    </span>

                    @if ($product_details->badge)
                        <span
                            class="absolute bottom-3 left-3 bg-indigo-600 text-white text-[11px] font-bold px-3 py-1 rounded-xl shadow-md">
                            {{ __($product_details->badge) }}
                        </span>
                    @endif
                </div>

                <!-- Thumbnails Carousel -->
                @if (count($productImages) > 1)
                    <div class="swiper productThumbSwiper pt-1">
                        <div class="swiper-wrapper">
                            @foreach ($productImages as $idx => $img)
                                <div class="swiper-slide !w-16 !h-16">
                                    <button type="button"
                                        onclick="switchMainProductImage('{{ url($img) }}', this)"
                                        class="thumb-slide w-full h-full rounded-2xl overflow-hidden border-2 border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 transition-all p-0.5 cursor-pointer {{ $idx == 0 ? 'active-thumb' : '' }}">
                                        <img src="{{ url($img) }}" alt="{{ $product_details->product_name }}"
                                            class="w-full h-full object-cover rounded-xl" />
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

            </div>

            <!-- Right Column: Purchasing Details & Actions (7 Cols) -->
            <div class="lg:col-span-7 space-y-5 gsap-product-info">

                <!-- Title & Stock Status -->
                <div class="space-y-2">
                    <div class="flex items-center gap-2 flex-wrap">
                        @if ($product_details->product_status == 'instock')
                            <span
                                class="inline-flex items-center gap-1 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 text-xs font-bold px-3 py-1 rounded-full border border-emerald-500/20">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                {{ __('In Stock') }}
                            </span>
                        @else
                            <span
                                class="inline-flex items-center gap-1 bg-rose-500/10 text-rose-600 dark:text-rose-400 text-xs font-bold px-3 py-1 rounded-full border border-rose-500/20">
                                {{ __('Out of Stock') }}
                            </span>
                        @endif

                        @if ($hasDiscount)
                            <span
                                class="bg-rose-500/10 text-rose-600 dark:text-rose-400 text-xs font-bold px-2.5 py-0.5 rounded-full border border-rose-500/20">
                                {{ __('Save') }} {{ $discountPercent }}%
                            </span>
                        @endif
                    </div>

                    <h1
                        class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight leading-snug">
                        {{ $product_details->product_name }}
                    </h1>

                    @if (!empty($product_details->product_short_description))
                        <p class="text-slate-500 dark:text-slate-400 text-xs sm:text-sm leading-relaxed">
                            {{ $product_details->product_short_description }}
                        </p>
                    @endif
                </div>

                <!-- Price & Action Box -->
                <div
                    class="p-5 rounded-3xl bg-white dark:bg-[#111625] border border-slate-200 dark:border-slate-800 shadow-sm space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <span
                                class="text-xs text-slate-400 font-bold block uppercase tracking-wider">{{ __('Price') }}</span>
                            <div class="flex items-baseline gap-2 mt-0.5">
                                <span class="text-3xl font-extrabold text-slate-900 dark:text-white">
                                    {{ formatCurrencyCard($product_details->sales_price, $currency) }}
                                </span>
                                @if ($product_details->sales_price != $product_details->regular_price && $product_details->regular_price)
                                    <del class="text-sm text-slate-400 font-semibold">
                                        {{ formatCurrencyCard($product_details->regular_price, $currency) }}
                                    </del>
                                @endif
                            </div>
                        </div>

                        <!-- Quantity Selector -->
                        @if ($product_details->product_status == 'instock')
                            <div
                                class="flex items-center border border-slate-300 dark:border-slate-700 bg-slate-100 dark:bg-slate-900/80 rounded-2xl overflow-hidden p-1 shadow-inner">
                                <button onclick="changeDetailsQty(-1)"
                                    class="w-8 h-8 flex items-center justify-center text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-200 dark:hover:bg-slate-800 rounded-xl transition font-bold text-sm cursor-pointer">-</button>
                                <span id="product-details-qty"
                                    class="w-9 text-center text-sm font-bold text-slate-900 dark:text-white">1</span>
                                <button onclick="changeDetailsQty(1)"
                                    class="w-8 h-8 flex items-center justify-center text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-200 dark:hover:bg-slate-800 rounded-xl transition font-bold text-sm cursor-pointer">+</button>
                            </div>
                        @endif
                    </div>

                    <!-- Action Buttons -->
                    @if ($product_details->product_status == 'instock')
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                            <!-- Add to Cart -->
                            <button type="button" onclick="addProductDetailsToCart()"
                                class="w-full bg-indigo-600 hover:bg-indigo-500 text-white font-bold py-3 px-4 rounded-2xl text-xs sm:text-sm flex items-center justify-center gap-2 shadow-md shadow-indigo-600/25 hover:scale-[1.01] active:scale-[0.99] transition-all cursor-pointer">
                                <i class="ti ti-shopping-bag-plus text-lg"></i>
                                <span>{{ __('Add to Cart') }}</span>
                            </button>

                            <!-- WhatsApp Enquiry -->
                            <button type="button" onclick="enquireCurrentProductViaWhatsApp()"
                                class="w-full bg-emerald-600/15 hover:bg-[#059669] text-emerald-600 dark:text-emerald-400 hover:text-white border border-emerald-500/30 font-bold py-3 px-4 rounded-2xl text-xs sm:text-sm flex items-center justify-center gap-2 hover:scale-[1.01] active:scale-[0.99] transition-all cursor-pointer">
                                <i class="ti ti-brand-whatsapp text-lg"></i>
                                <span>{{ __('Enquiry on WhatsApp') }}</span>
                            </button>
                        </div>
                    @endif
                </div>

                <!-- Product Description Box -->
                <div
                    class="bg-white dark:bg-[#111625] border border-slate-200 dark:border-slate-800 rounded-3xl p-5 sm:p-6 shadow-sm space-y-3">
                    <h3
                        class="text-sm sm:text-base font-bold text-slate-900 dark:text-white flex items-center gap-2 m-0 border-b border-slate-100 dark:border-slate-800 pb-3">
                        <i class="ti ti-file-text text-indigo-500 text-lg"></i>
                        {{ __('Product Overview & Details') }}
                    </h3>
                    <div class="product-description-content text-slate-600 dark:text-slate-300">
                        {!! $product_details->product_description !!}
                    </div>
                </div>

                <!-- Share Component -->
                <div
                    class="bg-white dark:bg-[#111625] border border-slate-200 dark:border-slate-800 rounded-3xl p-4 shadow-sm space-y-2.5">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
                            <i class="ti ti-share text-indigo-500 text-sm"></i>
                            {{ __('Share Product') }}
                        </span>

                        <button onclick="copyCurrentProductLink()"
                            class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:underline flex items-center gap-1 cursor-pointer">
                            <i class="ti ti-copy"></i>
                            <span>{{ __('Copy Link') }}</span>
                        </button>
                    </div>

                    <div class="pt-2 border-t border-slate-100 dark:border-slate-800">
                        @include('templates.includes.store.share', [
                            'productName' => $product_details->product_name,
                            'productUrl' => url()->current(),
                        ])
                    </div>
                </div>

            </div>

        </div>

        <!-- Full-Width Spacious Related Products Section -->
        @if (isset($related_products) && count($related_products) > 0)
            <div class="pt-10 border-t border-slate-200/80 dark:border-slate-800/80 space-y-6">

                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                            {{ __('Related Products') }}
                        </h2>
                        <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">
                            {{ __('You might also be interested in these digital assets.') }}
                        </p>
                    </div>
                </div>

                <!-- Responsive Full-Width Grid (Fills whole container evenly) -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    @foreach ($related_products as $related)
                        @php
                            $relImages = explode(',', $related->product_image);
                            $relFirstImage = url($relImages[0] ?? '');
                        @endphp
                        <div
                            class="bg-white dark:bg-[#111625] border border-slate-200 dark:border-slate-800 hover:border-indigo-500/50 rounded-2xl p-4 flex flex-col justify-between transition-all duration-300 hover:-translate-y-1.5 shadow-sm hover:shadow-xl group">
                            <div>
                                <a href="{{ $productUrlPrefix . '/' . $related->id }}"
                                    class="block relative aspect-video rounded-xl overflow-hidden mb-3 bg-slate-100 dark:bg-slate-900">
                                    <img src="{{ $relFirstImage }}" alt="{{ $related->product_name }}"
                                        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                    <span
                                        class="absolute top-2 left-2 bg-slate-950/80 text-indigo-400 text-[10px] font-bold px-2 py-0.5 rounded-md border border-indigo-500/30">
                                        {{ __('Digital') }}
                                    </span>
                                </a>

                                @if ($related->badge)
                                    <span
                                        class="inline-block bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 text-[10px] px-2.5 py-0.5 rounded-full font-bold mb-1.5 border border-indigo-200 dark:border-indigo-500/20">
                                        {{ __($related->badge) }}
                                    </span>
                                @endif

                                <a href="{{ $productUrlPrefix . '/' . $related->id }}">
                                    <h4 class="font-bold text-slate-900 dark:text-white text-sm truncate group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors"
                                        title="{{ $related->product_name }}">
                                        {{ $related->product_name }}
                                    </h4>
                                </a>
                                <p class="text-slate-500 dark:text-slate-400 text-xs mt-1 line-clamp-2">
                                    {{ $related->product_short_description }}
                                </p>
                            </div>

                            <div
                                class="pt-3 border-t border-slate-100 dark:border-slate-800 mt-3 flex items-center justify-between">
                                <span class="text-base font-extrabold text-slate-900 dark:text-white">
                                    {{ formatCurrencyCard($related->sales_price, $currency) }}
                                </span>

                                @if ($related->product_status == 'instock')
                                    <button type="button"
                                        onclick="addToCartLive('{{ $related->product_id }}', '{{ addslashes($related->product_name) }}', {{ $related->sales_price }}, '{{ $relFirstImage }}')"
                                        class="w-9 h-9 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white flex items-center justify-center shadow-md shadow-indigo-600/25 hover:shadow-lg hover:shadow-indigo-600/35 transition-all duration-200 hover:scale-105 active:scale-95 cursor-pointer focus:outline-none group/btn"
                                        aria-label="{{ __('Add to Cart') }}" title="{{ __('Add to Cart') }}">
                                        <svg class="w-4 h-4 fill-none stroke-current group-hover/btn:scale-110 transition-transform"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2"
                                                d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                        </svg>
                                    </button>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

            </div>
        @endif

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
        var detailsQuantity = 1;
        const currentProductId = "{{ $product_details->product_id }}";
        const currentProductName = "{{ addslashes($product_details->product_name) }}";
        const currentProductPrice = Number("{{ $product_details->sales_price }}");
        let currentProductImage = "{{ $firstImage }}";

        // Gallery Thumbnail Switcher
        function switchMainProductImage(imgSrc, btnEl) {
            const mainImg = document.getElementById('main-product-image');
            if (mainImg) {
                mainImg.style.opacity = '0';
                setTimeout(() => {
                    mainImg.src = imgSrc;
                    currentProductImage = imgSrc;
                    mainImg.style.opacity = '1';
                }, 150);
            }

            document.querySelectorAll('.thumb-slide').forEach(b => b.classList.remove('active-thumb'));
            if (btnEl) btnEl.classList.add('active-thumb');
        }

        // Swipers
        const thumbSwiper = new Swiper('.productThumbSwiper', {
            slidesPerView: 'auto',
            spaceBetween: 8,
            freeMode: true
        });

        const relatedSwiper = new Swiper('.relatedProductsSwiper', {
            slidesPerView: 1.2,
            spaceBetween: 14,
            breakpoints: {
                640: { slidesPerView: 2.3 },
                1024: { slidesPerView: 3.3 }
            }
        });

        // Details Page Quantity Picker
        function changeDetailsQty(delta) {
            detailsQuantity = Math.max(1, detailsQuantity + delta);
            const qtyEl = document.getElementById('product-details-qty');
            if (qtyEl) {
                qtyEl.innerText = detailsQuantity;
                if (typeof gsap !== 'undefined') {
                    gsap.fromTo(qtyEl, { scale: 1.3 }, { scale: 1, duration: 0.2 });
                }
            }
        }

        // Add Product from Details page to Global Cart
        function addProductDetailsToCart() {
            if (typeof window.cart === 'undefined') {
                window.cart = [];
            }

            const existingIndex = window.cart.findIndex(item => item.id == currentProductId);

            if (existingIndex > -1) {
                window.cart[existingIndex].quantity += detailsQuantity;
            } else {
                window.cart.push({
                    id: currentProductId,
                    title: currentProductName,
                    price: currentProductPrice,
                    img: currentProductImage,
                    quantity: detailsQuantity
                });
            }

            const addedQty = detailsQuantity;
            detailsQuantity = 1;
            const qtyEl = document.getElementById('product-details-qty');
            if (qtyEl) qtyEl.innerText = 1;

            if (typeof updateCartBadge === 'function') updateCartBadge();
            if (typeof saveCartStorage === 'function') saveCartStorage();

            const toast = document.getElementById('cart-toast');
            if (toast && typeof gsap !== 'undefined') {
                document.getElementById('toast-message').innerText = `{{ __('Added') }} (${addedQty}x): ${currentProductName}`;
                gsap.killTweensOf(toast);
                const tl = gsap.timeline();
                tl.to(toast, { y: 25, opacity: 1, duration: 0.3, ease: 'power2.out' })
                .to(toast, { y: -20, opacity: 0, duration: 0.3, delay: 2, ease: 'power2.in' });
            }
        }

        // Direct WhatsApp Enquiry for Single Product
        function enquireCurrentProductViaWhatsApp() {
            const sub = currentProductPrice * detailsQuantity;
            const formattedSub = typeof jsFormatCurrency === 'function' ? jsFormatCurrency(sub) : sub;
            const message = `{{ __('Hello! I have an enquiry regarding') }} "${currentProductName}" ({{ __('Quantity') }}: ${detailsQuantity}, {{ __('Price') }}: ${formattedSub}). {{ __('Can you please share more details?') }}`;
            window.open(`https://api.whatsapp.com/send/?phone=${window.businessPhoneNumber}&text=${encodeURI(message)}`, '_blank');
        }

        // Related Products Add to Cart Helper
        function addToCartLive(productId, title, price, img) {
            if (typeof window.cart === 'undefined') {
                window.cart = [];
            }

            const existingIndex = window.cart.findIndex(item => item.id == productId);

            if (existingIndex > -1) {
                window.cart[existingIndex].quantity += 1;
            } else {
                window.cart.push({ id: productId, title: title, price: Number(price), img: img, quantity: 1 });
            }

            if (typeof updateCartBadge === 'function') updateCartBadge();
            if (typeof saveCartStorage === 'function') saveCartStorage();

            const toast = document.getElementById('cart-toast');
            if (toast && typeof gsap !== 'undefined') {
                document.getElementById('toast-message').innerText = `{{ __('Added') }} (1x): ${title}`;
                gsap.killTweensOf(toast);
                const tl = gsap.timeline();
                tl.to(toast, { y: 25, opacity: 1, duration: 0.3, ease: 'power2.out' })
                .to(toast, { y: -20, opacity: 0, duration: 0.3, delay: 2, ease: 'power2.in' });
            }
        }

        // Copy Product Link Helper
        function copyCurrentProductLink() {
            navigator.clipboard.writeText(window.location.href).then(() => {
                const toast = document.getElementById('cart-toast');
                if (toast && typeof gsap !== 'undefined') {
                    document.getElementById('toast-message').innerText = `{{ __('Product link copied to clipboard!') }}`;
                    gsap.killTweensOf(toast);
                    const tl = gsap.timeline();
                    tl.to(toast, { y: 25, opacity: 1, duration: 0.3, ease: 'power2.out' })
                    .to(toast, { y: -20, opacity: 0, duration: 0.3, delay: 2, ease: 'power2.in' });
                }
            });
        }

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
