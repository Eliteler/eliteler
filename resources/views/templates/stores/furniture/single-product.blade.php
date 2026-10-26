@php
    $productImages = array_values(array_filter(array_map('trim', explode(',', (string) $product_details->product_image))));
    $placeholder = asset('img/templates/furniture/placeholder.svg');
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">

<head>
    @include('templates.stores.furniture.includes.head')

    <style>
        .store-body-content-area {
            flex: 1 0 auto;
            width: 100%;
            display: block;
        }

        /* 1. Breadcrumbs strictly ABOVE gallery */
        .store-breadcrumbs-bar {
            background-color: #faf8f5;
            border-bottom: 1px solid var(--color-card-border);
            padding: 12px 20px;
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 8px;
            font-size: 13px;
            font-weight: 600;
            line-height: 1.4;
        }

        .breadcrumb-link {
            color: var(--color-forest-teal);
            display: inline-flex;
            align-items: center;
            gap: 5px;
            flex-shrink: 0;
            transition: color 0.2s ease;
        }

        .breadcrumb-link i {
            font-size: 15px !important;
        }

        .breadcrumb-link:hover {
            color: var(--color-camel);
        }

        .breadcrumb-separator {
            color: var(--color-camel);
            display: inline-flex;
            align-items: center;
            flex-shrink: 0;
        }

        .breadcrumb-separator i {
            font-size: 12px !important;
        }

        .breadcrumb-current {
            color: var(--color-text-muted);
            font-weight: 600;
            word-break: break-word;
        }

        /* 2. Product Showcase Layout */
        .product-showcase-container {
            padding: 24px 16px 40px;
        }

        @media (min-width: 768px) {
            .product-showcase-container {
                padding: 36px 32px 50px;
            }
        }

        /* Main Image Stage */
        .single-main-image-stage {
            width: 100%;
            aspect-ratio: 4 / 3;
            border-radius: 18px !important;
            overflow: hidden;
            background-color: #f8fafc;
            border: 1px solid var(--color-card-border);
            margin-bottom: 14px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .single-main-image-stage img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            object-position: center;
            transition: transform 0.3s ease;
        }

        .single-main-image-stage:hover img {
            transform: scale(1.03);
        }

        /* Thumbnails Slider */
        .single-thumb-slide {
            aspect-ratio: 1 / 1;
            border-radius: 12px !important;
            overflow: hidden;
            background-color: #f8fafc;
            border: 1.5px solid var(--color-card-border);
            cursor: pointer;
            transition: all 0.2s ease;
            padding: 6px;
        }

        .single-thumb-slide.active,
        .single-thumb-slide:hover {
            border-color: var(--color-forest-teal);
            box-shadow: 0 0 0 2px rgba(31, 75, 72, 0.2);
        }

        .single-thumb-slide img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        /* Product Badges Clean Alignment */
        .product-single-badges-wrap {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 8px;
            margin-bottom: 14px;
        }

        .badge-custom-promo {
            background-color: var(--color-camel-light) !important;
            color: var(--color-camel-dark) !important;
            font-size: 11px !important;
            font-weight: 800 !important;
            padding: 5px 14px !important;
            border-radius: 50px !important;
            display: inline-flex !important;
            align-items: center !important;
            gap: 5px !important;
            text-transform: uppercase !important;
            border: 1px solid rgba(204, 164, 122, 0.4) !important;
        }

        .stock-badge[data-state="in"] {
            background-color: #dcfce7 !important;
            color: #15803d !important;
            font-size: 11px !important;
            font-weight: 700 !important;
            padding: 5px 14px !important;
            border-radius: 50px !important;
            display: inline-flex !important;
            align-items: center !important;
            gap: 5px !important;
        }

        .stock-badge[data-state="out"] {
            background-color: #fee2e2 !important;
            color: #dc2626 !important;
            font-size: 11px !important;
            font-weight: 700 !important;
            padding: 5px 14px !important;
            border-radius: 50px !important;
            display: inline-flex !important;
            align-items: center !important;
            gap: 5px !important;
        }

        /* Product Title & Subtitle */
        .product-single-title {
            font-family: 'DM Serif Display', serif;
            font-size: 28px;
            color: var(--color-text-main);
            line-height: 1.25;
            margin-bottom: 8px;
        }

        @media (min-width: 768px) {
            .product-single-title {
                font-size: 34px;
            }
        }

        .product-single-subtitle {
            font-size: 15px;
            color: var(--color-text-muted);
            line-height: 1.6;
            margin-bottom: 18px;
        }

        /* Price Box */
        .product-single-price-box {
            display: flex;
            align-items: baseline;
            gap: 12px;
            margin-bottom: 24px;
            padding-bottom: 18px;
            border-bottom: 1.5px solid var(--color-card-border);
        }

        .product-single-sales-price {
            font-size: 28px;
            font-weight: 700;
            color: var(--color-forest-teal);
        }

        .product-single-regular-price {
            font-size: 17px;
            color: var(--color-text-muted);
            text-decoration: line-through;
        }

        /* 3. Variant Pill Selector Engine */
        .variant-selector-wrapper {
            background-color: #faf8f5;
            border: 1px solid var(--color-card-border);
            border-radius: 18px !important;
            padding: 16px 18px;
            margin-bottom: 24px;
        }

        .variant-group-label {
            font-size: 12px;
            font-weight: 800;
            text-transform: uppercase;
            color: var(--color-text-main);
            letter-spacing: 0.8px;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .variant-pills-flex {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .variant-pill-btn {
            height: 42px;
            padding: 0 20px;
            border-radius: 50px !important;
            border: 1.5px solid #cbd5e1;
            background-color: #ffffff;
            color: var(--color-text-main);
            font-size: 13.5px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            outline: none;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.02);
        }

        .variant-pill-btn:hover {
            border-color: var(--color-forest-teal);
            color: var(--color-forest-teal);
            transform: translateY(-1px);
        }

        .variant-pill-price {
            font-weight: 600;
            opacity: 0.75;
            margin-inline-start: 6px;
        }

        .variant-selected-label {
            color: var(--color-camel-dark);
            margin-inline-start: 4px;
        }

        .variant-pill-btn:disabled,
        .variant-pill-btn.is-sold-out {
            opacity: 0.5;
            cursor: not-allowed;
            text-decoration: line-through;
            background: #f8fafc;
        }

        .btn-single-add-cart:disabled,
        .btn-single-buy-now:disabled {
            opacity: 0.55;
            cursor: not-allowed;
        }

        .variant-pill-btn.active {
            background-color: var(--color-forest-teal) !important;
            border-color: var(--color-forest-teal) !important;
            color: #ffffff !important;
            box-shadow: 0 4px 14px rgba(31, 75, 72, 0.35);
        }

        /* Action Buttons & Counter */
        .product-action-controls-wrap {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            align-items: center;
            margin-bottom: 28px;
        }

        .single-qty-counter {
            display: inline-flex;
            align-items: center;
            background-color: #f8fafc;
            border: 1.5px solid #cbd5e1;
            border-radius: 50px !important;
            padding: 3px 6px;
            height: 48px;
        }

        .btn-qty-single {
            width: 38px;
            height: 38px;
            border-radius: 50% !important;
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            color: var(--color-text-main);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-qty-single:hover {
            background-color: var(--color-forest-teal);
            color: #ffffff;
        }

        .qty-number-display {
            width: 40px;
            text-align: center;
            font-size: 16px;
            font-weight: 700;
            color: var(--color-text-main);
        }

        .btn-single-add-cart {
            height: 48px;
            border-radius: 50px !important;
            background-color: var(--color-forest-teal);
            color: #ffffff !important;
            font-weight: 700;
            font-size: 14px;
            padding: 0 24px;
            border: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            flex-grow: 1;
            cursor: pointer;
            box-shadow: 0 6px 18px rgba(31, 75, 72, 0.3);
            transition: all 0.2s ease;
        }

        .btn-single-add-cart:hover {
            background-color: var(--color-forest-teal-dark);
            transform: translateY(-2px);
        }

        .btn-single-buy-now {
            height: 48px;
            border-radius: 50px !important;
            background-color: #ffffff;
            color: var(--color-forest-teal) !important;
            font-weight: 700;
            font-size: 14px;
            padding: 0 24px;
            border: 1.5px solid var(--color-forest-teal);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            flex-grow: 1;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-single-buy-now:hover {
            background-color: var(--color-forest-teal);
            color: #ffffff !important;
        }

        /* Rich Description Box */
        .single-description-box {
            background-color: #faf8f5;
            border: 1px solid var(--color-card-border);
            border-radius: 20px !important;
            padding: 24px;
            margin-bottom: 24px;
            color: #334155;
            font-size: 15px;
            line-height: 1.7;
        }

        .single-description-box h3,
        .single-description-box h4 {
            font-family: 'DM Serif Display', serif;
            color: var(--color-text-main);
            margin-bottom: 12px;
        }

        /* 4. Related Products Section & Baseline Grid */
        .related-products-section {
            padding: 40px 16px;
            border-top: 1px solid var(--color-card-border);
            background-color: #ffffff;
        }

        @media (min-width: 768px) {
            .related-products-section {
                padding: 48px 32px;
            }
        }

        .luxury-section-pill {
            background-color: var(--color-camel);
            color: #ffffff;
            font-family: 'DM Serif Display', serif;
            font-size: 20px;
            padding: 7px 32px;
            border-radius: 50px !important;
            display: inline-block;
            letter-spacing: 0.5px;
        }

        .store-product-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 16px;
            margin-top: 24px;
        }

        @media (min-width: 768px) {
            .store-product-grid {
                grid-template-columns: repeat(3, 1fr);
                gap: 20px;
            }
        }

        @media (min-width: 992px) {
            .store-product-grid {
                grid-template-columns: repeat(4, 1fr);
                gap: 24px;
            }
        }
    </style>

    @include('templates.stores.shared.custom-code')
</head>

<body dir="{{ $isRtl ? 'rtl' : 'ltr' }}">

    {{-- Preloader Include --}}
    @include('templates.stores.furniture.includes.preloader')

    <div class="store-main-wrapper">
        {{-- Header Include --}}
        @include('templates.stores.furniture.includes.header')

        {{-- 1. Breadcrumbs Bar strictly ABOVE Gallery --}}
        <div class="store-breadcrumbs-bar">
            <a href="{{ $storeUrl }}" class="breadcrumb-link">
                <i class="ti ti-home"></i>
                <span>{{ __('Home') }}</span>
            </a>
            <span class="breadcrumb-separator"><i class="ti ti-chevron-right"></i></span>
            <a href="{{ $storeUrl }}" class="breadcrumb-link">
                <span>{{ __('Products') }}</span>
            </a>
            <span class="breadcrumb-separator"><i class="ti ti-chevron-right"></i></span>
            <span class="breadcrumb-current">{{ $product_details->product_name }}</span>
        </div>

        {{-- 2. Main Product Showcase Container --}}
        <div class="store-body-content-area">
            <div class="product-showcase-container">
                <div class="row g-4 align-items-start">
                    {{-- Left Column: Image Gallery --}}
                    <div class="col-12 col-lg-6">
                        {{-- Main Active Image Stage --}}
                        <div class="single-main-image-stage" id="mainImageStage">
                            <img src="{{ $productImages ? url($productImages[0]) : $placeholder }}"
                                id="currentShowcaseImg" alt="{{ $product_details->product_name }}"
                                onerror="this.onerror=null;this.src='{{ $placeholder }}';">
                        </div>

                        {{-- Gallery Thumbnails Carousel --}}
                        @if (count($productImages) > 1)
                            <div class="mt-2">
                                <swiper-container space-between="10" slides-per-view="4"
                                    autoplay-disable-on-interaction="false">
                                    @foreach ($productImages as $index => $img)
                                        <swiper-slide>
                                            <div class="single-thumb-slide {{ $index == 0 ? 'active' : '' }}"
                                                onclick="switchMainImage('{{ url($img) }}', this)">
                                                <img src="{{ url($img) }}" alt="Thumb {{ $index }}"
                                                    onerror="this.onerror=null;this.src='{{ $placeholder }}';">
                                            </div>
                                        </swiper-slide>
                                    @endforeach
                                </swiper-container>
                            </div>
                        @endif
                    </div>

                    {{-- Product information: price, options, quantity and buttons are run by the storefront script --}}
                    <div class="col-12 col-lg-6" {{ $productOptions->attributes($product_details) }}>
                        <div class="product-single-badges-wrap">
                            @if (!empty(trim($product_details->badge ?? '')))
                                <span class="badge-custom-promo">
                                    <i class="ti ti-sparkles"></i>
                                    <span>{{ $product_details->badge }}</span>
                                </span>
                            @endif

                            <span class="stock-badge" data-product-stock data-state="{{ $productOptions->canBuy ? 'in' : 'out' }}">
                                <i class="ti {{ $productOptions->canBuy ? 'ti-check' : 'ti-x' }}"></i>
                                <span data-product-stock-text>{{ $productOptions->canBuy ? __('In Stock') : __('Out of Stock') }}</span>
                            </span>
                        </div>

                        <h1 class="product-single-title">{{ $product_details->product_name }}</h1>
                        @if (!empty($product_details->product_short_description))
                            <p class="product-single-subtitle">{{ $product_details->product_short_description }}</p>
                        @endif

                        <div class="product-single-price-box">
                            <span class="product-single-sales-price" data-product-price>{{ formatCurrencyCard($productOptions->salesPrice, $currency) }}</span>
                            <span class="product-single-regular-price" data-product-regular-price @if (!$productOptions->regularPrice) hidden @endif>
                                {{ $productOptions->regularPrice ? formatCurrencyCard($productOptions->regularPrice, $currency) : '' }}
                            </span>
                        </div>

                        @if ($productOptions->hasOptions)
                            <div class="variant-selector-wrapper">
                                <div class="variant-group-label">
                                    <i class="ti ti-adjustments-horizontal fs-3 text-teal"></i>
                                    <span>{{ __('Select Option / Variant:') }}</span>
                                    <span class="variant-selected-label" data-option-label>{{ $productOptions->canBuy ? $productOptions->initial->variant_name : '' }}</span>
                                </div>
                                <div class="variant-pills-flex">
                                    @include('templates.stores.shared.product-options', [
                                        'buttonClass' => 'variant-pill-btn',
                                        'priceClass' => 'variant-pill-price',
                                    ])
                                </div>
                            </div>
                        @endif

                        <div class="product-action-controls-wrap">
                            <div class="single-qty-counter">
                                <button type="button" class="btn-qty-single" data-qty-step="-1" aria-label="{{ __('Decrease') }}"><i class="ti ti-minus"></i></button>
                                <span class="qty-number-display" data-qty-value>1</span>
                                <button type="button" class="btn-qty-single" data-qty-step="1" aria-label="{{ __('Increase') }}"><i class="ti ti-plus"></i></button>
                            </div>

                            <button type="button" class="btn-single-add-cart" data-product-add @disabled(!$productOptions->canBuy)>
                                <i class="ti ti-shopping-bag-plus fs-2"></i>
                                <span>{{ __('Add to Cart') }}</span>
                            </button>

                            {{-- Adds the item, then opens the cart to order on WhatsApp --}}
                            <button type="button" class="btn-single-buy-now" data-product-add data-then="cart" @disabled(!$productOptions->canBuy)>
                                <i class="ti ti-bolt fs-2"></i>
                                <span>{{ __('Buy Now') }}</span>
                            </button>
                        </div>

                        {{-- Product Description Box (Product Overview) --}}
                        @if (!empty($product_details->product_description))
                            <div class="single-description-box">
                                <h4>{{ __('Product Overview') }}</h4>
                                <div>{!! $product_details->product_description !!}</div>
                            </div>
                        @endif

                        {{-- Share Component Placed Directly Below Product Overview --}}
                        @include('templates.stores.furniture.includes.share', [
                            'productName' => $product_details->product_name,
                            'productUrl' => url()->current(),
                        ])
                    </div>
                </div>
            </div>

            {{-- 4. Related Products Section --}}
            @if (isset($related_products) && count($related_products) > 0)
                <div class="related-products-section">
                    <div class="text-center mb-4">
                        <h2 class="luxury-section-pill">{{ __('Related Products') }}</h2>
                    </div>

                    <div class="store-product-grid">
                        @foreach ($related_products as $product)
                            @include('templates.stores.furniture.partials.product-card')
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        {{-- Footer Include --}}
        @include('templates.stores.furniture.includes.footer')
    </div>

    {{-- Bottom Bar Include --}}
    @include('templates.stores.furniture.includes.bottom-bar')

    {{-- PWA Modal Include --}}
    @include('templates.stores.furniture.includes.pwa-modal')

    {{-- WhatsApp Interactive Float Include --}}
    @include('templates.stores.furniture.includes.whatsapp-float')

    {{-- Cart drawer, checkout and the shared storefront script --}}
    @include('templates.stores.furniture.partials.cart-order-modal')
    @include('templates.stores.furniture.includes.scripts')

    <script>
        // Show a gallery thumbnail in the main image box (called by the thumbnails)
        function switchMainImage(imgUrl, thumbEl) {
            const mainImg = document.getElementById('currentShowcaseImg');
            if (mainImg) mainImg.src = imgUrl;

            document.querySelectorAll('.single-thumb-slide').forEach((thumb) => thumb.classList.remove('active'));
            if (thumbEl) thumbEl.classList.add('active');
        }
    </script>
</body>

</html>
