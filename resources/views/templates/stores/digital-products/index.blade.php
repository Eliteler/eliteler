<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth dark" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">

<head>
    @include('templates.stores.digital-products.includes.head')

    <style>
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

    @include('templates.stores.shared.custom-code')
    @stack('page-css')
</head>

<body
    class="bg-slate-50 dark:bg-[#0b0f19] text-slate-800 dark:text-slate-100 min-h-screen antialiased transition-colors duration-300 relative pb-14 md:pb-0"
    dir="{{ $isRtl ? 'rtl' : 'ltr' }}">

    <!-- Fullscreen Preloader -->
    @include('templates.stores.digital-products.includes.preloader')


    <!-- 1. Header Component Include -->
    @include('templates.stores.digital-products.includes.header')

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

        <!-- Hero Cover Banner Section (HIDDEN WHEN ?category IS ACTIVE) -->
        @if (!request('category'))
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
        @endif

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
                                @include('templates.stores.digital-products.partials.product-card')
                            @endforeach
                        </div>

                        <!-- Pagination Section -->
                        <div class="flex items-center justify-center pt-10 pb-4">
                            @if (request()->has('category'))
                                {{ $products->appends(request()->except('page'))->links('templates.stores.digital-products.partials.pagination') }}
                            @else
                                {{ $products->links('templates.stores.digital-products.partials.pagination') }}
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
    @include('templates.stores.digital-products.includes.bottom-bar')

    <!-- 1. Popups Include -->
    @include('templates.stores.digital-products.includes.popups')

    <!-- 3. PWA Modal Include -->
    @include('templates.stores.digital-products.includes.pwa-modal')

    <!-- 4. WhatsApp Float Component Include -->
    @include('templates.stores.digital-products.includes.whatsapp-float', [
        'businessImage' => $business_card_details->profile,
        'businessName' => $card_details->title,
        'whatsappNumber' => $enquiry_button,
    ])

    {{-- Cart, checkout and the shared storefront script --}}
    @include('templates.stores.digital-products.partials.cart-order-modal')
    @include('templates.stores.digital-products.includes.scripts')

    <script>
        // Sort the listing (called by the sort menu)
        function itemSort(sort) {
            const params = new URLSearchParams(window.location.search);
            params.set('sort', sort);
            window.location.href = "{{ url()->current() }}?" + params.toString();
        }

        if (document.querySelector('.heroSwiper')) {
            new Swiper('.heroSwiper', {
                loop: true,
                autoplay: { delay: 4000, disableOnInteraction: false },
                pagination: { el: '.swiper-pagination', clickable: true },
            });
        }

        if (document.querySelector('.categorySwiper')) {
            new Swiper('.categorySwiper', { slidesPerView: 'auto', spaceBetween: 8, freeMode: true });
        }
    </script>

    @stack('custom-js')
</body>

</html>
