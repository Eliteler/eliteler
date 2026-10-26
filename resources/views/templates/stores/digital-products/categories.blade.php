<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth dark" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">

<head>
    @include('templates.stores.digital-products.includes.head')

    <style>
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

    @include('templates.stores.shared.custom-code')
    @stack('page-css')
</head>

<body
    class="bg-slate-50 dark:bg-[#0b0f19] text-slate-800 dark:text-slate-100 min-h-screen antialiased transition-colors duration-300 relative pb-14 md:pb-0"
    dir="{{ $isRtl ? 'rtl' : 'ltr' }}">

    <!-- Fullscreen Preloader -->
    @include('templates.stores.digital-products.includes.preloader')


    <!-- Header Include -->
    @include('templates.stores.digital-products.includes.header')

    <!-- Main Categories Page Container -->
    <main class="max-w-6xl mx-auto px-4 py-6 sm:py-8 space-y-6 sm:space-y-8">

        <!-- Top Row: Breadcrumbs & Back Button (Overflow Protected) -->
        <div class="flex items-center justify-between gap-3 border-b border-slate-200/80 dark:border-slate-800/80 pb-4">
            
            {{-- Left: Breadcrumbs with min-w-0 to allow proper truncation --}}
            <div class="flex items-center gap-1.5 text-xs font-semibold text-slate-500 dark:text-slate-400 min-w-0 flex-1">
                <a href="{{ $storeUrl }}"
                    class="hover:text-indigo-600 dark:hover:text-indigo-400 transition shrink-0">{{ __('Home') }}</a>
                <span class="text-slate-300 dark:text-slate-600 shrink-0">&rsaquo;</span>
                <span class="text-indigo-600 dark:text-indigo-400 font-bold truncate" title="{{ __('Categories') }}">
                    {{ __('Categories') }}
                </span>
            </div>

            {{-- Right: Back to Store Button --}}
            <a href="{{ $storeUrl }}"
                class="inline-flex items-center gap-1.5 bg-white dark:bg-slate-800/90 hover:bg-indigo-600 hover:text-white text-slate-700 dark:text-slate-200 font-semibold px-3 py-1.5 sm:px-3.5 sm:py-2 rounded-xl text-xs border border-slate-200 dark:border-slate-700/80 transition shadow-xs shrink-0 whitespace-nowrap">
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
    @include('templates.stores.digital-products.includes.bottom-bar')

    <!-- WhatsApp Float Component Include -->
    @include('templates.stores.digital-products.includes.whatsapp-float', [
        'businessImage' => $business_card_details->profile,
        'businessName' => $card_details->title,
        'whatsappNumber' => $enquiry_button,
    ])

    {{-- Cart, checkout and the shared storefront script --}}
    @include('templates.stores.digital-products.partials.cart-order-modal')
    @include('templates.stores.digital-products.includes.scripts')

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

        initInfiniteScroll();

        new Swiper('.categorySwiper', {
            slidesPerView: 'auto',
            spaceBetween: 8,
            freeMode: true,
        });
    </script>

    @stack('custom-js')
</body>

</html>
