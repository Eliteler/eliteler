{{-- Master Single Product View for Fashion Store with Variations Engine --}}
@php
    $productImages = array_values(array_filter(array_map('trim', explode(',', (string) $product_details->product_image))));
    $placeholder = asset('img/templates/fashion/placeholder.svg');
    $firstImage = $productImages ? url($productImages[0]) : $placeholder;
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">

<head>
    @include('templates.stores.fashion.includes.head')

    <style>
        /* Luxury Breadcrumb Banner */
        .fashion-breadcrumb-bar {
            background-color: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            padding: 16px 0;
            margin-bottom: 28px;
        }

        .fashion-breadcrumb {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            font-weight: 600;
            color: #64748b;
            margin: 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .fashion-breadcrumb a {
            color: #64748b;
            transition: color 0.2s ease;
        }

        .fashion-breadcrumb a:hover {
            color: #0f172a;
        }

        .fashion-breadcrumb-current {
            color: #0f172a;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Product Gallery Box */
        .fashion-main-img-box {
            position: relative;
            width: 100%;
            aspect-ratio: 1 / 1;
            background-color: #f1f5f9;
            border: 1px solid #e2e8f0;
            overflow: hidden;
            margin-bottom: 12px;
        }

        .fashion-main-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .fashion-thumb-item {
            width: 100%;
            aspect-ratio: 1 / 1;
            border: 1px solid #cbd5e1;
            background-color: #f8fafc;
            cursor: pointer;
            overflow: hidden;
            transition: border-color 0.2s ease;
        }

        .fashion-thumb-item:hover,
        .fashion-thumb-item.active {
            border-color: #0f172a;
        }

        .fashion-thumb-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        /* Product Info Typography */
        .fashion-single-title {
            font-family: 'Oswald', sans-serif;
            font-size: 32px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #0f172a;
            margin-bottom: 8px;
            line-height: 1.2;
        }

        .fashion-single-subtitle {
            font-size: 14.5px;
            color: #64748b;
            margin-bottom: 18px;
            line-height: 1.5;
        }

        .fashion-price-wrap {
            display: flex;
            align-items: baseline;
            gap: 12px;
            margin-bottom: 16px;
            border-bottom: 1px solid #f1f5f9;
            padding-bottom: 16px;
        }

        .fashion-sales-price-val {
            font-size: 30px;
            font-weight: 800;
            color: #0f172a;
        }

        .fashion-regular-price-val {
            font-size: 18px;
            color: #94a3b8;
            text-decoration: line-through;
        }

        /* High-Contrast Badges */
        .fashion-badge-row {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 22px;
            flex-wrap: wrap;
        }

        .fashion-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 5px 12px;
            font-size: 11.5px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            border-radius: 0px !important;
            line-height: 1;
        }

        .fashion-badge[data-state="in"] {
            background-color: #dcfce7;
            color: #15803d;
            border: 1px solid #bbf7d0;
        }

        .fashion-badge[data-state="out"] {
            background-color: #fee2e2;
            color: #dc2626;
            border: 1px solid #fca5a5;
        }

        .fashion-badge-promo {
            background-color: #0f172a;
            color: #ffffff;
            border: 1px solid #0f172a;
        }

        /* Variant Pill Buttons */
        .fashion-variants-section {
            margin-bottom: 24px;
        }

        .fashion-variant-label {
            font-size: 13px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #0f172a;
            margin-bottom: 10px;
            display: block;
        }

        .fashion-variants-list {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .fashion-variant-pill {
            appearance: none !important;
            border: 1px solid #cbd5e1;
            background-color: #ffffff;
            color: #0f172a;
            padding: 8px 18px;
            border-radius: 0px;
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            cursor: pointer;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .fashion-variant-pill:hover {
            border-color: #0f172a;
            background-color: #f8fafc;
        }

        .fashion-variant-price {
            font-weight: 600;
            opacity: 0.75;
            margin-inline-start: 6px;
        }

        .fashion-variant-selected {
            color: #d97706;
            margin-inline-start: 4px;
        }

        .fashion-variant-pill:disabled,
        .fashion-variant-pill.is-sold-out {
            opacity: 0.5;
            cursor: not-allowed;
            text-decoration: line-through;
            background-color: #f8fafc;
        }

        .fashion-add-btn:disabled,
        .fashion-buy-btn:disabled {
            opacity: 0.55;
            cursor: not-allowed;
            transform: none;
        }

        .fashion-variant-pill.active {
            border-color: #0f172a;
            background-color: #0f172a;
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.2);
        }

        /* Quantity & Action Group */
        .fashion-actions-row {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 24px;
            flex-wrap: wrap;
        }

        .fashion-single-qty-box {
            display: inline-flex;
            align-items: center;
            border: 1px solid #cbd5e1;
            height: 48px;
            background-color: #ffffff;
        }

        .fashion-single-qty-btn {
            width: 42px;
            height: 100%;
            background: transparent;
            border: none;
            font-size: 18px;
            font-weight: 700;
            color: #0f172a;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background-color 0.2s ease;
        }

        .fashion-single-qty-btn:hover {
            background-color: #f1f5f9;
        }

        .fashion-single-qty-val {
            width: 48px;
            text-align: center;
            font-size: 16px;
            font-weight: 800;
            color: #0f172a;
        }

        .fashion-add-btn {
            flex: 1;
            min-width: 160px;
            height: 48px;
            background-color: #0f172a;
            color: #ffffff;
            border: none;
            border-radius: 0px;
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .fashion-add-btn:hover {
            background-color: #1e293b;
            color: #ffffff;
            transform: translateY(-2px);
        }

        .fashion-buy-btn {
            flex: 1;
            min-width: 160px;
            height: 48px;
            background-color: #d97706;
            color: #ffffff;
            border: none;
            border-radius: 0px;
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .fashion-buy-btn:hover {
            background-color: #b45309;
            color: #ffffff;
            transform: translateY(-2px);
        }

        /* Clearance on Mobile to prevent Floating WhatsApp Button overlap */
        .fashion-single-container {
            padding-bottom: 110px;
        }

        @media (min-width: 992px) {
            .fashion-single-container {
                padding-bottom: 40px;
            }
        }

        /* Responsive Related Products Grid Engine */
        .fashion-related-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 14px;
            width: 100%;
        }

        @media (min-width: 768px) {
            .fashion-related-grid {
                grid-template-columns: repeat(3, 1fr);
                gap: 16px;
            }
        }

        @media (min-width: 992px) {
            .fashion-related-grid {
                grid-template-columns: repeat(4, 1fr);
                gap: 18px;
            }
        }

        @media (min-width: 1200px) {
            .fashion-related-grid {
                grid-template-columns: repeat(5, 1fr);
                gap: 20px;
            }
        }
    </style>

    @include('templates.stores.shared.custom-code')
</head>

<body dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
    {{-- 1. Preloader --}}
    @include('templates.stores.fashion.includes.preloader')

    {{-- 2. Header --}}
    @include('templates.stores.fashion.includes.header')

    {{-- 3. Breadcrumb Navigation strictly ABOVE Gallery --}}
    <div class="fashion-breadcrumb-bar">
        <div class="container-xl">
            <div class="fashion-breadcrumb">
                <a href="{{ $storeUrl }}">{{ __('Home') }}</a>
                <i class="ti ti-chevron-right fs-4"></i>
                @if (!empty($product_details->category_name))
                    <a
                        href="{{ $storeUrl . '?category=' . urlencode(strtolower($product_details->category_name)) }}">{{ $product_details->category_name }}</a>
                    <i class="ti ti-chevron-right fs-4"></i>
                @endif
                <span class="fashion-breadcrumb-current">{{ $product_details->product_name }}</span>
            </div>
        </div>
    </div>

    {{-- 4. Main Product Showcase --}}
    <main class="container-xl fashion-single-container">
        <div class="row g-4 g-lg-5">
            {{-- Product Gallery Column --}}
            <div class="col-lg-6">
                <div class="fashion-main-img-box">
                    <a href="{{ $firstImage }}" data-fslightbox="singleGallery" id="mainImageLightboxLink">
                        <img src="{{ $firstImage }}" id="currentMainImg" class="fashion-main-img"
                            alt="{{ $product_details->product_name }}">
                    </a>
                </div>

                @if (count($productImages) > 1)
                    <swiper-container space-between="10" class="mySwiperThumbs"
                        breakpoints='{
                        "320": { "slidesPerView": 4 },
                        "640": { "slidesPerView": 5 },
                        "1024": { "slidesPerView": 5 }
                    }'>
                        @foreach ($productImages as $index => $img)
                            @php $imgUrl = url(trim($img)); @endphp
                            <swiper-slide>
                                <div class="fashion-thumb-item {{ $index == 0 ? 'active' : '' }}"
                                    onclick="switchMainImage('{{ $imgUrl }}', this)">
                                    <img src="{{ $imgUrl }}" class="fashion-thumb-img"
                                        alt="{{ $product_details->product_name }}">
                                </div>
                            </swiper-slide>
                        @endforeach
                    </swiper-container>
                @endif
            </div>

            {{-- Product information: price, options, quantity and buttons are run by the storefront script --}}
            <div class="col-lg-6" {{ $productOptions->attributes($product_details) }}>
                <h1 class="fashion-single-title">{{ $product_details->product_name }}</h1>
                @if (!empty($product_details->product_short_description))
                    <p class="fashion-single-subtitle">{{ $product_details->product_short_description }}</p>
                @endif

                <div class="fashion-price-wrap">
                    <span class="fashion-sales-price-val" data-product-price>{{ formatCurrencyCard($productOptions->salesPrice, $currency) }}</span>
                    <span class="fashion-regular-price-val" data-product-regular-price @if (!$productOptions->regularPrice) hidden @endif>
                        {{ $productOptions->regularPrice ? formatCurrencyCard($productOptions->regularPrice, $currency) : '' }}
                    </span>
                </div>

                <div class="fashion-badge-row">
                    <span class="fashion-badge" data-product-stock data-state="{{ $productOptions->canBuy ? 'in' : 'out' }}">
                        <i class="ti ti-circle-check fs-4"></i>
                        <span data-product-stock-text>{{ $productOptions->canBuy ? __('In Stock') : __('Out of Stock') }}</span>
                    </span>

                    @if (!empty(trim($product_details->badge ?? '')))
                        <span class="fashion-badge fashion-badge-promo">
                            <i class="ti ti-tag fs-4"></i>
                            <span>{{ trim($product_details->badge) }}</span>
                        </span>
                    @endif
                </div>

                @if ($productOptions->hasOptions)
                    <div class="fashion-variants-section">
                        <span class="fashion-variant-label">
                            {{ __('Select Option / Size') }}:
                            <span class="fashion-variant-selected" data-option-label>{{ $productOptions->canBuy ? $productOptions->initial->variant_name : '' }}</span>
                        </span>
                        <div class="fashion-variants-list">
                            @include('templates.stores.shared.product-options', [
                                'buttonClass' => 'fashion-variant-pill',
                                'priceClass' => 'fashion-variant-price',
                            ])
                        </div>
                    </div>
                @endif

                <div class="fashion-actions-row">
                    <div class="fashion-single-qty-box">
                        <button type="button" class="fashion-single-qty-btn" data-qty-step="-1" aria-label="{{ __('Decrease') }}">-</button>
                        <span class="fashion-single-qty-val" data-qty-value>1</span>
                        <button type="button" class="fashion-single-qty-btn" data-qty-step="1" aria-label="{{ __('Increase') }}">+</button>
                    </div>

                    <button type="button" class="fashion-add-btn" data-product-add @disabled(!$productOptions->canBuy)>
                        <i class="ti ti-shopping-bag-plus fs-2"></i>
                        <span>{{ __('Add to Cart') }}</span>
                    </button>

                    {{-- Adds the item, then opens the cart to order on WhatsApp --}}
                    <button type="button" class="fashion-buy-btn" data-product-add data-then="cart" @disabled(!$productOptions->canBuy)>
                        <i class="ti ti-bolt fs-2"></i>
                        <span>{{ __('Buy Now') }}</span>
                    </button>
                </div>

                <!-- Product Full Description -->
                @if (!empty($product_details->product_description))
                    <div class="border-top pt-4 mt-4">
                        <h4 class="head fs-2 mb-2 text-dark">{{ __('Description') }}</h4>
                        <div class="text-secondary fs-3 line-relaxed">
                            {!! $product_details->product_description !!}
                        </div>
                    </div>
                @endif

                <!-- Modern Luxury Share Component -->
                @include('templates.stores.fashion.includes.share', [
                    'productName' => $product_details->product_name,
                    'productUrl' => url()->current(),
                ])
            </div>
        </div>

        {{-- 5. Related Products Section (Rock-Solid CSS Grid) --}}
        @if (count($related_products) > 0)
            <div class="border-top pt-5 mt-5">
                <h2 class="head fs-1 mb-4 text-center text-md-start">{{ __('You May Also Like') }}</h2>

                <div class="fashion-related-grid">
                    @foreach ($related_products as $product)
                        @include('templates.stores.fashion.partials.product-card')
                    @endforeach
                </div>
            </div>
        @endif
    </main>

    {{-- Footer Component --}}
    @include('templates.stores.fashion.includes.footer')

    {{-- Bottom Bar Component --}}
    @include('templates.stores.fashion.includes.bottom-bar')

    {{-- WhatsApp Floating Chat --}}
    @include('templates.stores.fashion.includes.whatsapp-float', ['whatsappNumber' => $enquiry_button])

    {{-- Popups Component --}}
    @include('templates.stores.fashion.includes.popups')

    {{-- PWA Install Modal --}}
    @include('templates.stores.fashion.includes.pwa-modal')

    {{-- Cart drawer, checkout and the shared storefront script --}}
    @include('templates.stores.fashion.partials.cart-order-modal')
    @include('templates.stores.fashion.includes.scripts')

    <script src="{{ url('js/fslightbox.js') }}"></script>
    <script>
        // Show a gallery thumbnail in the main image box and lightbox (called by the thumbnails)
        function switchMainImage(imgUrl, thumbEl) {
            const mainImg = document.getElementById('currentMainImg');
            const lightboxLink = document.getElementById('mainImageLightboxLink');
            if (mainImg) mainImg.src = imgUrl;
            if (lightboxLink) lightboxLink.href = imgUrl;

            document.querySelectorAll('.fashion-thumb-item').forEach((thumb) => thumb.classList.remove('active'));
            if (thumbEl) thumbEl.classList.add('active');

            if (typeof refreshFsLightbox === 'function') refreshFsLightbox();
        }
    </script>
</body>

</html>
