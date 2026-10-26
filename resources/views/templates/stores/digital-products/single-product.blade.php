@php
    $productImages = array_values(array_filter(array_map('trim', explode(',', (string) $product_details->product_image))));
    $placeholder = asset('img/templates/digital-products/placeholder.svg');
    $firstImage = $productImages ? url($productImages[0]) : $placeholder;
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth dark" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">

<head>
    @include('templates.stores.digital-products.includes.head')

    <style>
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

        .variant-pill.active-variant {
            border-color: #6366f1 !important;
            background-color: rgba(99, 102, 241, 0.08) !important;
            color: #6366f1 !important;
            box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.25);
        }

        /* Stock badge and option buttons follow the selected option (storefront script) */
        .stock-pill[data-state="in"] {
            background-color: rgba(16, 185, 129, 0.1);
            color: #059669;
            border-color: rgba(16, 185, 129, 0.2);
        }

        .stock-pill[data-state="out"] {
            background-color: rgba(244, 63, 94, 0.1);
            color: #e11d48;
            border-color: rgba(244, 63, 94, 0.2);
        }

        .stock-pill .stock-dot {
            background-color: currentColor;
        }

        .variant-pill:disabled,
        .variant-pill.is-sold-out {
            opacity: 0.55;
            cursor: not-allowed;
        }

        .variant-pill .variant-pill-price {
            font-size: 0.75rem;
            font-weight: 800;
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

    <!-- Main Full-Width Product Container -->
    <main class="max-w-6xl mx-auto px-4 py-6 sm:py-8 space-y-10">

        <!-- Top Row: Breadcrumbs & Back Button (Overflow Protected) -->
        <div class="flex items-center justify-between gap-3 border-b border-slate-200/80 dark:border-slate-800/80 pb-4">
            
            {{-- Left: Breadcrumbs with min-w-0 to allow proper truncation --}}
            <div class="flex items-center gap-1.5 text-xs font-semibold text-slate-500 dark:text-slate-400 min-w-0 flex-1">
                <a href="{{ $storeUrl }}"
                    class="hover:text-indigo-600 dark:hover:text-indigo-400 transition shrink-0">{{ __('Home') }}</a>
                <span class="text-slate-300 dark:text-slate-600 shrink-0">&rsaquo;</span>
                <span class="text-indigo-600 dark:text-indigo-400 font-bold truncate" title="{{ $product_details->product_name }}">
                    {{ $product_details->product_name }}
                </span>
            </div>

            {{-- Right: Back to Store Button --}}
            <a href="{{ $storeUrl }}"
                class="inline-flex items-center gap-1.5 bg-white dark:bg-slate-800/90 hover:bg-indigo-600 hover:text-white text-slate-700 dark:text-slate-200 font-semibold px-3 py-1.5 sm:px-3.5 sm:py-2 rounded-xl text-xs border border-slate-200 dark:border-slate-700/80 transition shadow-xs shrink-0 whitespace-nowrap">
                <i class="ti ti-arrow-left text-xs"></i>
                <span>{{ __('Back to Store') }}</span>
            </a>

        </div>

        <!-- Product Primary View -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-10 items-start">

            <!-- Left Column: Gallery Display -->
            <div class="lg:col-span-5 space-y-4 gsap-product-gallery">
                <div
                    class="relative aspect-[4/3] w-full rounded-3xl overflow-hidden border border-slate-200 dark:border-slate-800 bg-slate-100 dark:bg-[#111625] shadow-lg group">
                    <img id="main-product-image" src="{{ $firstImage }}" onerror="this.onerror=null;this.src='{{ $placeholder }}';" alt="{{ $product_details->product_name }}"
                        class="w-full h-full object-cover transition-all duration-300 group-hover:scale-105">

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

            {{-- Product information: price, options, quantity and buttons are run by the storefront script --}}
            <div class="lg:col-span-7 space-y-5 gsap-product-info" {{ $productOptions->attributes($product_details, 'active-variant') }}>
                @php
                    $onSale = $productOptions->regularPrice && $productOptions->regularPrice > $productOptions->salesPrice;
                @endphp

                <div class="space-y-2">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="stock-pill inline-flex items-center gap-1 text-xs font-bold px-3 py-1 rounded-full border" data-product-stock
                            data-state="{{ $productOptions->canBuy ? 'in' : 'out' }}">
                            <span class="stock-dot w-1.5 h-1.5 rounded-full"></span>
                            <span data-product-stock-text>{{ $productOptions->canBuy ? __('In Stock') : __('Out of Stock') }}</span>
                        </span>

                        <span class="inline-block bg-rose-500/10 text-rose-600 dark:text-rose-400 text-xs font-bold px-2.5 py-0.5 rounded-full border border-rose-500/20"
                            data-product-discount @if (!$onSale) hidden @endif>
                            {{ __('Save') }} <span data-product-discount-value>{{ $onSale ? round((($productOptions->regularPrice - $productOptions->salesPrice) / $productOptions->regularPrice) * 100) : '' }}</span>%
                        </span>
                    </div>

                    <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight leading-snug">
                        {{ $product_details->product_name }}
                    </h1>

                    @if (!empty($product_details->product_short_description))
                        <p class="text-slate-500 dark:text-slate-400 text-xs sm:text-sm leading-relaxed">{{ $product_details->product_short_description }}</p>
                    @endif
                </div>

                <div class="p-5 rounded-3xl bg-white dark:bg-[#111625] border border-slate-200 dark:border-slate-800 shadow-sm space-y-5">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="text-xs text-slate-400 font-bold block uppercase tracking-wider">{{ __('Price') }}</span>
                            <div class="flex items-baseline gap-2 mt-0.5">
                                <span class="text-3xl font-extrabold text-slate-900 dark:text-white" data-product-price>{{ formatCurrencyCard($productOptions->salesPrice, $currency) }}</span>
                                <del class="text-sm text-slate-400 font-semibold" data-product-regular-price @if (!$onSale) hidden @endif>
                                    {{ $onSale ? formatCurrencyCard($productOptions->regularPrice, $currency) : '' }}
                                </del>
                            </div>
                        </div>

                        <div class="flex items-center border border-slate-300 dark:border-slate-700 bg-slate-100 dark:bg-slate-900/80 rounded-2xl overflow-hidden p-1 shadow-inner">
                            <button type="button" data-qty-step="-1" aria-label="{{ __('Decrease') }}"
                                class="w-8 h-8 flex items-center justify-center text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-200 dark:hover:bg-slate-800 rounded-xl transition font-bold text-sm cursor-pointer">-</button>
                            <span class="w-9 text-center text-sm font-bold text-slate-900 dark:text-white" data-qty-value>1</span>
                            <button type="button" data-qty-step="1" aria-label="{{ __('Increase') }}"
                                class="w-8 h-8 flex items-center justify-center text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-200 dark:hover:bg-slate-800 rounded-xl transition font-bold text-sm cursor-pointer">+</button>
                        </div>
                    </div>

                    @if ($productOptions->hasOptions)
                        <div class="pt-4 border-t border-slate-100 dark:border-slate-800/80 space-y-3">
                            <label class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider flex items-center gap-1.5">
                                <i class="ti ti-color-swatch text-indigo-500 text-sm"></i>
                                <span>{{ __('Select Option / Variation') }}:</span>
                                <span class="text-indigo-600 dark:text-indigo-400 font-extrabold normal-case" data-option-label>{{ $productOptions->canBuy ? $productOptions->initial->variant_name : '' }}</span>
                            </label>

                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5">
                                @include('templates.stores.shared.product-options', [
                                    'buttonClass' => 'variant-pill text-left p-3 rounded-2xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50 text-slate-700 dark:text-slate-300 hover:border-slate-300 dark:hover:border-slate-700 transition-all duration-200 flex flex-col justify-between gap-1 cursor-pointer text-xs font-bold',
                                    'activeClass' => 'active-variant',
                                    'priceClass' => 'variant-pill-price',
                                ])
                            </div>
                        </div>
                    @endif

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
                        <button type="button" data-product-add @disabled(!$productOptions->canBuy)
                            class="w-full bg-indigo-600 hover:bg-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed text-white font-bold py-3 px-4 rounded-2xl text-xs sm:text-sm flex items-center justify-center gap-2 shadow-md shadow-indigo-600/25 hover:scale-[1.01] active:scale-[0.99] transition-all cursor-pointer">
                            <i class="ti ti-shopping-bag-plus text-lg"></i>
                            <span>{{ __('Add to Cart') }}</span>
                        </button>

                        <button type="button" onclick="enquireViaWhatsApp(this)"
                            class="w-full bg-emerald-600/15 hover:bg-[#059669] text-emerald-600 dark:text-emerald-400 hover:text-white border border-emerald-500/30 font-bold py-3 px-4 rounded-2xl text-xs sm:text-sm flex items-center justify-center gap-2 hover:scale-[1.01] active:scale-[0.99] transition-all cursor-pointer">
                            <i class="ti ti-brand-whatsapp text-lg"></i>
                            <span>{{ __('Enquiry on WhatsApp') }}</span>
                        </button>
                    </div>
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
                        @include('templates.stores.includes.share', [
                            'productName' => $product_details->product_name,
                            'productUrl' => url()->current(),
                        ])
                    </div>
                </div>

            </div>

        </div>

        <!-- Related Products Section -->
        @if (isset($related_products) && count($related_products) > 0)
            <div class="pt-10 border-t border-slate-200/80 dark:border-slate-800/80 space-y-6">
                <div>
                    <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                        {{ __('Related Products') }}
                    </h2>
                    <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">
                        {{ __('You might also be interested in these digital assets.') }}
                    </p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    @foreach ($related_products as $product)
                        @include('templates.stores.digital-products.partials.product-card')
                    @endforeach
                </div>
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
        // Thumbnails under the main image
        if (document.querySelector('.productThumbSwiper')) {
            new Swiper('.productThumbSwiper', { slidesPerView: 'auto', spaceBetween: 8, freeMode: true });
        }

        // Show a thumbnail in the main image box (called by the thumbnails)
        function switchMainProductImage(imgSrc, btnEl) {
            const mainImg = document.getElementById('main-product-image');
            if (mainImg) {
                mainImg.style.opacity = '0';
                setTimeout(() => {
                    mainImg.src = imgSrc;
                    mainImg.style.opacity = '1';
                }, 150);
            }
            document.querySelectorAll('.thumb-slide').forEach((thumb) => thumb.classList.remove('active-thumb'));
            if (btnEl) btnEl.classList.add('active-thumb');
        }

        // "Copy Link" in the share box
        function copyCurrentProductLink() {
            navigator.clipboard.writeText(window.location.href)
                .then(() => window.showStoreToast(@json(__('Product link copied to clipboard!'))))
                .catch(() => window.showStoreToast(@json(__('Copy failed. Please try again.')), 'error'));
        }
    </script>

    @stack('custom-js')
</body>

</html>
