<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    {{-- CSRF Token --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Dynamic URL configuration for Master Domain, Subdomain, and Custom Domain --}}
    @php
        $currentHost = request()->getHost();
        $masterHost = parse_url(config('app.url'), PHP_URL_HOST);
        $isCustomDomainOrSubdomain = $currentHost !== $masterHost;

        if ($isCustomDomainOrSubdomain) {
            $storeUrl = url('/');
            $productUrlPrefix = url('product');
        } else {
            $storeUrl = url($business_card_details->card_url);
            $productUrlPrefix = url($business_card_details->card_url . '/product');
        }
    @endphp

    <title>{{ $card_details->title }} - {{ __('Digital Store') }}</title>

    {{-- Favicon & App Icons --}}
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

    <!-- Tailwind CSS CDN with DarkMode Class Config -->
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

        .swiper-pagination-bullet {
            background: #ffffff;
            opacity: 0.5;
            transition: all 0.3s ease;
        }

        .swiper-pagination-bullet-active {
            background: #6366f1 !important;
            width: 24px;
            border-radius: 9999px;
            opacity: 1;
        }

        /* Adaptive Shimmer Animation */
        @keyframes placeholderGlow {
            0% {
                background-position: -200% 0;
            }

            100% {
                background-position: 200% 0;
            }
        }

        .placeholder-glow {
            background-size: 200% 100%;
            animation: placeholderGlow 1.6s infinite ease-in-out;
        }

        .dark .placeholder-glow {
            background-image: linear-gradient(90deg, #111625 25%, #1e293b 50%, #111625 75%);
        }

        html:not(.dark) .placeholder-glow {
            background-image: linear-gradient(90deg, #e2e8f0 25%, #cbd5e1 50%, #e2e8f0 75%);
        }
    </style>

    {{-- SEO Tags --}}
    {!! SEOMeta::generate() !!}
    {!! OpenGraph::generate() !!}
    {!! Twitter::generate() !!}
    {!! JsonLd::generate() !!}

    {{-- PWA Check --}}
    @if ($plan_details != null && $plan_details['pwa'] == 1 && $business_card_details->is_enable_pwa == 1)
        @laravelPWA
        <link rel="manifest" href="{{ $manifest }}">
    @endif

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

    <!-- 1. Header Component Include -->
    @include('templates.store.digital-products.includes.header')

    <main class="max-w-6xl mx-auto px-4 py-6 space-y-8">

        {{-- Flash Alerts --}}
        @if (session('success'))
            <div
                class="p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/50 text-emerald-800 dark:text-emerald-300 text-xs sm:text-sm flex items-center gap-2">
                <i class="ti ti-circle-check text-lg"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if (session('failed'))
            <div
                class="p-4 rounded-2xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800/50 text-rose-800 dark:text-rose-300 text-xs sm:text-sm flex items-center gap-2">
                <i class="ti ti-alert-circle text-lg"></i>
                <span>{{ session('failed') }}</span>
            </div>
        @endif

        <!-- Hero Cover Banner Section -->
        <section id="hero-section" class="hero-slider-section">
            <div
                class="swiper heroSwiper rounded-3xl overflow-hidden border border-slate-200 dark:border-slate-800 shadow-xl">
                <div class="swiper-wrapper">
                    @php
                        $covers = is_array(json_decode($business_card_details->cover))
                            ? json_decode($business_card_details->cover)
                            : [
                                $business_card_details->cover ?:
                                'https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?auto=format&fit=crop&w=1400&q=80',
                            ];
                    @endphp

                    @foreach ($covers as $cover)
                        <div
                            class="swiper-slide relative h-48 sm:h-80 md:h-[360px] bg-slate-200 dark:bg-slate-900 placeholder-glow">
                            <img src="{{ url($cover) }}" alt="{{ $business_card_details->title }}"
                                class="w-full h-full object-cover select-none opacity-0 transition-opacity duration-500"
                                onload="this.classList.remove('opacity-0')">
                        </div>
                    @endforeach
                </div>
                <div class="swiper-pagination pb-2"></div>
            </div>
        </section>

        <!-- Dynamic Store Content -->
        <div class="grid grid-cols-1 {{ request('category') ? 'lg:grid-cols-12 gap-8 items-start' : 'gap-8' }}">

            <!-- Sidebar: Price Filter & Vertical Category List (Appears ONLY when request('category') is active) -->
            @if (request('category'))
                <aside class="lg:col-span-3 space-y-6">

                    <!-- Price Filter Card -->
                    <div
                        class="p-5 rounded-3xl bg-white dark:bg-[#111625] border border-slate-200 dark:border-slate-800 shadow-sm space-y-4">
                        <h4 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2 m-0">
                            <i class="ti ti-adjustments-horizontal text-indigo-500 text-base"></i>
                            {{ __('Filter Price') }}
                        </h4>

                        <form method="GET" action="{{ url()->current() }}" class="space-y-3">
                            @foreach (request()->except(['min', 'max', 'page']) as $key => $value)
                                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                            @endforeach

                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label
                                        class="text-[10px] font-bold text-slate-400 block mb-1 uppercase">{{ __('Min') }}</label>
                                    <input type="number" name="min"
                                        class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-2.5 py-1.5 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-indigo-500"
                                        placeholder="{{ __('Min') }}" step="0.01" min="0"
                                        value="{{ request('min') }}">
                                </div>
                                <div>
                                    <label
                                        class="text-[10px] font-bold text-slate-400 block mb-1 uppercase">{{ __('Max') }}</label>
                                    <input type="number" name="max"
                                        class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-2.5 py-1.5 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-indigo-500"
                                        placeholder="{{ __('Max') }}" step="0.01" min="0"
                                        value="{{ request('max') }}">
                                </div>
                            </div>

                            <button type="submit"
                                class="w-full py-2 bg-indigo-600 hover:bg-indigo-500 text-white font-bold rounded-xl text-xs transition shadow-sm">
                                {{ __('Apply Filter') }}
                            </button>
                        </form>
                    </div>

                    <!-- Vertical Categories List (FIXED ACTIVE HIGHLIGHTING) -->
                    <div
                        class="p-5 rounded-3xl bg-white dark:bg-[#111625] border border-slate-200 dark:border-slate-800 shadow-sm space-y-3">
                        <div
                            class="flex items-center justify-between pb-2 border-b border-slate-100 dark:border-slate-800">
                            <span
                                class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider">{{ __('Categories') }}</span>
                            <a href="{{ $storeUrl }}"
                                class="text-[11px] font-semibold text-indigo-600 dark:text-indigo-400 hover:underline">{{ __('Reset') }}</a>
                        </div>

                        @php
                            // Decode query parameter so "developer+resources" matches "developer resources"
                            $currentActiveCategory = strtolower(trim(urldecode(request('category') ?? '')));
                        @endphp

                        <div class="space-y-1.5">
                            <!-- All Assets link -->
                            <a href="{{ $storeUrl }}"
                                class="flex items-center justify-between p-2.5 rounded-xl text-xs font-semibold transition-all duration-200 {{ empty($currentActiveCategory) ? 'bg-indigo-600 text-white font-bold shadow-md shadow-indigo-600/30' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                                <div class="flex items-center gap-2">
                                    <i class="ti ti-layout-grid text-sm"></i>
                                    <span>{{ __('All Assets') }}</span>
                                </div>
                                @if (empty($currentActiveCategory))
                                    <span class="w-1.5 h-1.5 rounded-full bg-white animate-pulse"></span>
                                @endif
                            </a>

                            <!-- Category Items -->
                            @foreach ($categories as $category)
                                @php
                                    $catCleanName = strtolower(trim($category->category_name));
                                    $categorySlug = urlencode($catCleanName);

                                    // Bulletproof match checking name or ID
                                    $isActive =
                                        !empty($currentActiveCategory) &&
                                        ($currentActiveCategory === $catCleanName ||
                                            request('category') == $category->id);
                                @endphp
                                <a href="{{ $storeUrl . '?category=' . $categorySlug }}"
                                    class="flex items-center justify-between p-2.5 rounded-xl text-xs transition-all duration-200 {{ $isActive ? 'bg-indigo-600 text-white font-bold shadow-md shadow-indigo-600/30 -translate-x-0.5' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 font-medium' }}">
                                    <div class="flex items-center gap-2.5 min-w-0">
                                        <img src="{{ url($category->thumbnail) }}"
                                            class="w-5 h-5 object-contain rounded shrink-0 p-0.5 {{ $isActive ? 'bg-white/20' : 'bg-slate-100 dark:bg-slate-800' }}"
                                            alt="{{ $category->category_name }}"
                                            onerror="this.src='{{ asset('img/templates/all-categories.png') }}'">
                                        <span class="truncate">{{ __($category->category_name) }}</span>
                                    </div>
                                    @if ($isActive)
                                        <span
                                            class="w-1.5 h-1.5 rounded-full bg-white shrink-0 animate-pulse ml-2"></span>
                                    @endif
                                </a>
                            @endforeach
                        </div>
                    </div>

                </aside>
            @endif

            <!-- Main Content Area -->
            <div class="{{ request('category') ? 'lg:col-span-9 space-y-6' : 'w-full space-y-8' }}">

                <!-- Full Categories Horizontal Swiper (Shown when category filter is not applied) -->
                @if (!request('category'))
                    <section id="categories-section" class="py-2">
                        <div class="flex items-center justify-between mb-3 px-1">
                            <div class="flex items-center gap-2">
                                <span
                                    class="w-2 h-2 rounded-full bg-indigo-600 dark:bg-indigo-400 animate-pulse inline-block"></span>
                                <h3
                                    class="text-base sm:text-lg font-extrabold text-slate-900 dark:text-white tracking-tight m-0">
                                    {{ __('Explore Categories') }}
                                </h3>
                            </div>
                            <a href="{{ $storeUrl . '/categories' }}"
                                class="inline-flex items-center gap-1.5 text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:text-indigo-500 transition-colors group">
                                <span>{{ __('View All') }}</span>
                                <span class="transition-transform group-hover:translate-x-1">&rarr;</span>
                            </a>
                        </div>

                        <div class="swiper categorySwiper py-2 -mx-1 px-1">
                            <div class="swiper-wrapper">
                                <div class="swiper-slide !w-auto">
                                    <a href="{{ $storeUrl }}"
                                        class="category-btn inline-flex items-center gap-2.5 h-11 px-4 rounded-2xl text-xs font-bold transition-all duration-200 select-none {{ !request('category') ? 'bg-indigo-600 border-indigo-600 text-white shadow-md shadow-indigo-600/30 -translate-y-0.5' : 'bg-white dark:bg-[#111625] text-slate-700 dark:text-slate-300 border-slate-200/80 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800 hover:border-indigo-500/40 hover:-translate-y-0.5 shadow-sm' }} border">
                                        <span
                                            class="w-7 h-7 rounded-xl {{ !request('category') ? 'bg-white/20 text-white' : 'bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400' }} flex items-center justify-center text-xs shrink-0 font-black">
                                            ✦
                                        </span>
                                        <span>{{ __('All Assets') }}</span>
                                    </a>
                                </div>

                                @foreach ($categories as $category)
                                    @php
                                        $catCleanName = strtolower(trim($category->category_name));
                                        $categorySlug = urlencode($catCleanName);
                                        $isActive =
                                            !empty($currentActiveCategory) &&
                                            ($currentActiveCategory === $catCleanName ||
                                                request('category') == $category->id);
                                    @endphp
                                    <div class="swiper-slide !w-auto">
                                        <a href="{{ $storeUrl . '?category=' . $categorySlug }}"
                                            class="category-btn inline-flex items-center gap-3 h-11 pl-2.5 pr-4 rounded-2xl text-xs font-semibold transition-all duration-200 select-none border {{ $isActive ? 'bg-indigo-600 border-indigo-600 text-white font-bold shadow-md shadow-indigo-600/30 -translate-y-0.5' : 'bg-white dark:bg-[#111625] text-slate-700 dark:text-slate-300 border-slate-200/80 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white hover:border-indigo-500/40 hover:-translate-y-0.5 shadow-sm' }}">
                                            <span
                                                class="w-8 h-8 rounded-xl overflow-hidden shrink-0 border {{ $isActive ? 'border-white/30 bg-white/20' : 'border-slate-200 dark:border-slate-700 bg-slate-100 dark:bg-slate-800' }} flex items-center justify-center shadow-inner">
                                                <img src="{{ url($category->thumbnail) }}"
                                                    class="w-full h-full object-cover"
                                                    alt="{{ $category->category_name }}" loading="lazy"
                                                    onerror="this.src='{{ asset('img/templates/all-categories.png') }}'">
                                            </span>
                                            <span class="whitespace-nowrap">{{ __($category->category_name) }}</span>
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </section>
                @endif

                <!-- Search Form & Sort Dropdown Toolbar (Appears ONLY when request('category') is active) -->
                @if (request('category'))
                    <div
                        class="flex flex-col sm:flex-row items-center justify-between gap-3 bg-white dark:bg-[#111625] p-3.5 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm">
                        <!-- Search Form -->
                        <form class="w-full sm:flex-1 flex gap-2" method="GET" action="{{ url()->current() }}">
                            @foreach (request()->except(['query', 'page']) as $key => $value)
                                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                            @endforeach

                            <div class="relative w-full">
                                <i
                                    class="ti ti-search absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                                <input type="text" name="query"
                                    placeholder="{{ __('Search digital templates, assets, UI kits...') }}"
                                    value="{{ request('query') }}"
                                    class="w-full pl-9 pr-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:border-indigo-500" />
                            </div>
                            <button type="submit"
                                class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white font-bold rounded-xl text-xs transition shadow-sm shrink-0">
                                {{ __('Search') }}
                            </button>
                        </form>

                        <!-- Sort Dropdown -->
                        <div class="w-full sm:w-48 shrink-0">
                            <select name="sort"
                                class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 text-xs font-semibold rounded-xl px-3 py-2 focus:outline-none focus:border-indigo-500 cursor-pointer"
                                onchange="itemSort(this.value)">
                                <option value="default" {{ request('sort') == 'default' ? 'selected' : '' }}>
                                    {{ __('Recently Added') }}</option>
                                <option value="price_asc" {{ request('sort') == 'price_asc' ? 'selected' : '' }}>
                                    {{ __('Price: Low to High') }}</option>
                                <option value="price_desc" {{ request('sort') == 'price_desc' ? 'selected' : '' }}>
                                    {{ __('Price: High to Low') }}</option>
                                <option value="name_asc" {{ request('sort') == 'name_asc' ? 'selected' : '' }}>
                                    {{ __('Name: A to Z') }}</option>
                                <option value="name_desc" {{ request('sort') == 'name_desc' ? 'selected' : '' }}>
                                    {{ __('Name: Z to A') }}</option>
                            </select>
                        </div>
                    </div>
                @endif

                <!-- Products Grid Section -->
                <section id="products-section">
                    @if (count($products) > 0)
                        <div id="products-grid"
                            class="grid grid-cols-1 sm:grid-cols-2 {{ request('category') ? 'lg:grid-cols-3' : 'lg:grid-cols-3 xl:grid-cols-4' }} gap-6 min-h-[400px]">
                            @foreach ($products as $product)
                                @php
                                    $productImages = explode(',', $product->product_image);
                                    $firstImage = url($productImages[0] ?? '');
                                @endphp
                                <div
                                    class="product-item bg-white dark:bg-[#111625] border border-slate-200 dark:border-slate-800 hover:border-indigo-500/50 rounded-2xl p-4 flex flex-col justify-between transition-all duration-300 hover:-translate-y-1 shadow-md dark:shadow-lg group">
                                    <div>
                                        <a href="{{ $productUrlPrefix . '/' . $product->id }}"
                                            class="block relative aspect-video rounded-xl overflow-hidden mb-3 bg-slate-100 dark:bg-slate-900">
                                            <img src="{{ $firstImage }}" alt="{{ $product->product_name }}"
                                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                            <span
                                                class="absolute top-2.5 left-2.5 bg-slate-950/80 backdrop-blur-md text-indigo-400 text-[10px] font-bold px-2 py-0.5 rounded-md border border-indigo-500/30">
                                                {{ __('Digital Asset') }}
                                            </span>
                                        </a>

                                        @if ($product->badge)
                                            <span
                                                class="inline-block bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 text-[10px] px-2.5 py-0.5 rounded-full font-bold mb-1.5 border border-indigo-200 dark:border-indigo-500/20">
                                                {{ __($product->badge) }}
                                            </span>
                                        @endif

                                        <a href="{{ $productUrlPrefix . '/' . $product->id }}">
                                            <h4 class="font-bold text-slate-900 dark:text-white text-sm leading-snug truncate group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors"
                                                title="{{ $product->product_name }}">
                                                {{ $product->product_name }}
                                            </h4>
                                        </a>
                                        <p class="text-slate-500 dark:text-slate-400 text-xs mt-1 line-clamp-2">
                                            {{ $product->product_short_description }}
                                        </p>
                                    </div>

                                    <div class="pt-3 border-t border-slate-100 dark:border-slate-800 mt-4 space-y-3">
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <span class="text-lg font-extrabold text-slate-900 dark:text-white">
                                                    {{ formatCurrencyCard($product->sales_price, $currency) }}
                                                </span>
                                                @if ($product->sales_price != $product->regular_price && $product->regular_price)
                                                    <span class="text-xs text-slate-400 line-through ml-1">
                                                        {{ formatCurrencyCard($product->regular_price, $currency) }}
                                                    </span>
                                                @endif
                                            </div>

                                            <!-- Quantity Selector -->
                                            <div
                                                class="flex items-center border border-slate-300 dark:border-slate-700 bg-slate-100 dark:bg-slate-900/80 rounded-xl overflow-hidden p-0.5">
                                                <button
                                                    onclick="changeProductCardQty('{{ $product->product_id }}', -1)"
                                                    class="w-6 h-6 flex items-center justify-center text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-200 dark:hover:bg-slate-800 rounded transition font-bold text-xs">-</button>
                                                <span id="product-qty-{{ $product->product_id }}"
                                                    class="w-7 text-center text-xs font-bold text-slate-900 dark:text-white">1</span>
                                                <button
                                                    onclick="changeProductCardQty('{{ $product->product_id }}', 1)"
                                                    class="w-6 h-6 flex items-center justify-center text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-200 dark:hover:bg-slate-800 rounded transition font-bold text-xs">+</button>
                                            </div>
                                        </div>

                                        <div class="grid grid-cols-2 gap-2">
                                            <button
                                                onclick="addToCartLive('{{ $product->product_id }}', '{{ addslashes($product->product_name) }}', {{ $product->sales_price }}, '{{ $firstImage }}')"
                                                class="w-full bg-indigo-600 hover:bg-indigo-500 text-white font-bold py-2.5 px-2 rounded-xl text-xs flex items-center justify-center gap-1.5 transition shadow-sm">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                                                </svg>
                                                <span>{{ __('Add to Cart') }}</span>
                                            </button>

                                            <button
                                                onclick="enquireViaWhatsApp('{{ addslashes($product->product_name) }}', {{ $product->sales_price }}, '{{ $product->product_id }}')"
                                                class="w-full bg-emerald-600/15 hover:bg-[#059669] text-emerald-600 dark:text-emerald-400 hover:text-white border border-emerald-500/30 font-bold py-2.5 px-2 rounded-xl text-xs flex items-center justify-center gap-1.5 transition">
                                                <span>{{ __('Enquiry') }}</span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Pagination -->
                        <div class="flex items-center justify-center pt-10 pb-4">
                            @if (request()->has('category'))
                                {{ $products->appends(request()->except('page'))->links() }}
                            @else
                                {{ $products->links() }}
                            @endif
                        </div>
                    @else
                        <div
                            class="text-center py-20 bg-white dark:bg-[#111625] rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm">
                            <span class="text-4xl mb-3 inline-block">📂</span>
                            <h3 class="text-base font-bold text-slate-800 dark:text-slate-200">
                                {{ __('No Products Found!') }}</h3>
                        </div>
                    @endif
                </section>

            </div>

        </div>

    </main>

    <!-- 2. Bottom Bar Component Include -->
    @include('templates.store.digital-products.includes.bottom-bar')

    <!-- 3. PWA Modal Include -->
    @include('templates.store.digital-products.includes.pwa-modal')

    <!-- 4. WhatsApp Float Component Include -->
    @include('templates.store.digital-products.includes.whatsapp-float', [
        'businessImage' => $business_card_details->profile,
        'businessName' => $card_details->title,
        'whatsappNumber' => $enquiry_button,
    ])

    <!-- Include Reusable Cart & Customer Order Modals -->
    @include('templates.store.digital-products.partials.cart-order-modal')

    <!-- Swiper JS CDN -->
    <script src="{{ url('js/swiper-bundle.min.js') }}"></script>

    <script>
        window.productQuantities = typeof window.productQuantities !== 'undefined' ? window.productQuantities : {};

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

        function itemSort(sort) {
            const params = new URLSearchParams(window.location.search);
            params.set('sort', sort);
            window.location.href = "{{ url()->current() }}?" + params.toString();
        }

        const heroSwiper = new Swiper('.heroSwiper', {
            loop: true,
            autoplay: {
                delay: 4000,
                disableOnInteraction: false,
            },
            pagination: {
                el: '.swiper-pagination',
                clickable: true,
            },
        });

        const categorySwiper = new Swiper('.categorySwiper', {
            slidesPerView: 'auto',
            spaceBetween: 8,
            freeMode: true,
        });

        function changeProductCardQty(productId, delta) {
            const current = window.productQuantities[productId] || 1;
            const updated = Math.max(1, current + delta);
            window.productQuantities[productId] = updated;

            const qtyElement = document.getElementById(`product-qty-${productId}`);
            if (qtyElement) {
                qtyElement.innerText = updated;
                if (typeof gsap !== 'undefined') {
                    gsap.fromTo(qtyElement, {
                        scale: 1.3
                    }, {
                        scale: 1,
                        duration: 0.2
                    });
                }
            }
        }

        function addToCartLive(productId, title, price, img) {
            const qtyToAdd = window.productQuantities[productId] || 1;

            if (typeof window.cart === 'undefined') {
                window.cart = [];
            }

            const existingIndex = window.cart.findIndex(item => item.id == productId);

            if (existingIndex > -1) {
                window.cart[existingIndex].quantity += qtyToAdd;
            } else {
                window.cart.push({
                    id: productId,
                    title: title,
                    price: Number(price),
                    img: img,
                    quantity: qtyToAdd
                });
            }

            window.productQuantities[productId] = 1;
            const qtyElement = document.getElementById(`product-qty-${productId}`);
            if (qtyElement) qtyElement.innerText = 1;

            if (typeof updateCartBadge === 'function') updateCartBadge();
            if (typeof saveCartStorage === 'function') saveCartStorage();

            const toast = document.getElementById('cart-toast');
            if (toast && typeof gsap !== 'undefined') {
                document.getElementById('toast-message').innerText = `{{ __('Added') }} (${qtyToAdd}x): ${title}`;
                gsap.killTweensOf(toast);
                const tl = gsap.timeline();
                tl.to(toast, {
                        y: 25,
                        opacity: 1,
                        duration: 0.3,
                        ease: 'power2.out'
                    })
                    .to(toast, {
                        y: -20,
                        opacity: 0,
                        duration: 0.3,
                        delay: 2,
                        ease: 'power2.in'
                    });
            }
        }

        function enquireViaWhatsApp(productTitle, price, productId) {
            const qty = window.productQuantities[productId] || 1;
            const sub = price * qty;
            const formattedSub = typeof jsFormatCurrency === 'function' ? jsFormatCurrency(sub) : sub;
            const message =
                `{{ __('Hello! I have an enquiry regarding') }} "${productTitle}" ({{ __('Quantity') }}: ${qty}, {{ __('Price') }}: ${formattedSub}). {{ __('Can you please share more details?') }}`;
            window.open(`https://api.whatsapp.com/send/?phone=${window.businessPhoneNumber}&text=${encodeURI(message)}`,
                '_blank');
        }
    </script>

    {{-- Custom JS --}}
    @stack('custom-js')
</body>

</html>
