{{-- GoBiz Luxury Grocery Store - Single Product Details & Variations Engine --}}
@php
    $productImages = array_values(array_filter(array_map('trim', explode(',', (string) $product_details->product_image))));
    $placeholder = asset('img/templates/grocery/placeholder.svg');
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">

<head>
    @include('templates.stores.grocery.includes.head')

    <style>
        :root {
            --store-radius-card: 28px;
        }

        /* 1. Breadcrumbs strictly ABOVE Gallery */
        .store-breadcrumbs-wrap {
            padding: 1.5rem 0 0.75rem;
            display: flex;
            align-items: center;
            justify-content: flex-start;
            width: 100%;
        }

        .store-breadcrumbs-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #ffffff;
            border: 1px solid var(--store-border);
            border-radius: var(--store-radius-pill) !important;
            padding: 7px 18px;
            font-size: 0.85rem;
            font-weight: 600;
            box-shadow: 0 2px 8px rgba(46, 125, 50, 0.04);
            max-width: 100%;
            overflow: hidden;
            white-space: nowrap;
        }

        .breadcrumb-link {
            color: var(--store-text-muted) !important;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            transition: color 0.2s ease;
            flex-shrink: 0;
        }

        .breadcrumb-link:hover {
            color: var(--store-primary) !important;
        }

        .breadcrumb-sep {
            color: #a5d6a7;
            font-size: 0.75rem;
            flex-shrink: 0;
        }

        [dir="rtl"] .breadcrumb-sep {
            transform: rotate(180deg);
        }

        .breadcrumb-current {
            color: var(--store-primary);
            font-weight: 700;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            max-width: 280px;
        }

        @media (max-width: 768px) {
            .breadcrumb-current {
                max-width: 140px;
            }
        }

        /* 2. Single Product Master Card Layout */
        .product-master-card {
            background: #ffffff;
            border: 1px solid var(--store-border);
            border-radius: var(--store-radius-card);
            box-shadow: 0 6px 24px rgba(46, 125, 50, 0.05);
            padding: 28px;
            margin-bottom: 3.5rem;
        }

        @media (max-width: 768px) {
            .product-master-card {
                padding: 18px;
            }
        }

        /* Gallery Viewer */
        .single-gallery-main {
            width: 100%;
            aspect-ratio: 1 / 1;
            border-radius: 22px;
            overflow: hidden;
            background: var(--store-primary-light);
            border: 1.5px solid #f1f8e9;
            position: relative;
            margin-bottom: 14px;
        }

        .single-main-img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            object-position: center;
            transition: transform 0.4s ease;
        }

        .single-gallery-thumbs-row {
            display: flex;
            align-items: center;
            gap: 10px;
            overflow-x: auto;
            padding: 4px 2px;
            scrollbar-width: none;
        }

        .single-gallery-thumbs-row::-webkit-scrollbar {
            display: none;
        }

        .single-gallery-thumb-item {
            width: 72px;
            height: 72px;
            border-radius: 16px;
            overflow: hidden;
            background: #f8fafc;
            border: 2px solid var(--store-border);
            cursor: pointer;
            transition: all 0.25s ease;
            flex-shrink: 0;
        }

        .single-gallery-thumb-item:hover,
        .single-gallery-thumb-item.active {
            border-color: var(--store-primary);
            box-shadow: 0 4px 12px rgba(46, 125, 50, 0.25);
            transform: translateY(-2px);
        }

        .single-gallery-thumb-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        /* Badges & Titles */
        .single-badge-wrap {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
            margin-bottom: 1rem;
        }

        .badge-pill-promo {
            background: var(--store-primary-light);
            color: var(--store-primary);
            border: 1px solid var(--store-border);
            font-size: 0.75rem;
            font-weight: 800;
            padding: 4px 12px;
            border-radius: var(--store-radius-pill) !important;
            text-transform: uppercase;
        }

        .badge-pill-stock {
            background: #ecfdf5;
            color: #059669;
            border: 1px solid #a7f3d0;
            font-size: 0.75rem;
            font-weight: 800;
            padding: 4px 12px;
            border-radius: var(--store-radius-pill) !important;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .badge-pill-stock[data-state="out"] {
            background: #fef2f2;
            color: #dc2626;
            border-color: #fecaca;
        }

        .single-product-title {
            font-size: 1.85rem;
            font-weight: 800;
            color: var(--store-primary-hover);
            margin-bottom: 6px;
            line-height: 1.25;
            text-align: start;
        }

        .single-product-subtitle {
            font-size: 0.95rem;
            color: var(--store-text-muted);
            margin-bottom: 1.25rem;
            line-height: 1.5;
            text-align: start;
        }

        /* Price Display */
        .single-price-box {
            display: flex;
            align-items: baseline;
            gap: 12px;
            margin-bottom: 1.25rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #f1f8e9;
        }

        .single-sales-price {
            font-size: 2.1rem;
            font-weight: 800;
            color: var(--store-primary);
            margin: 0;
            line-height: 1;
        }

        .single-regular-price {
            font-size: 1.15rem;
            color: #94a3b8;
            text-decoration: line-through !important;
            margin: 0;
            line-height: 1;
        }

        /* 3. Variant Pill Selector Engine */
        .variant-selector-wrapper {
            margin-bottom: 1.5rem;
            padding: 16px;
            background: #f8fafc;
            border: 1px solid var(--store-border);
            border-radius: 20px;
        }

        .variant-group-title {
            font-size: 0.88rem;
            font-weight: 700;
            color: var(--store-dark);
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .variant-pills-list {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .variant-pill-btn {
            border-radius: var(--store-radius-pill) !important;
            border: 1.5px solid var(--store-border);
            background: #ffffff;
            color: var(--store-text-main);
            padding: 8px 18px;
            font-size: 0.88rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.25s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .variant-pill-btn:hover {
            border-color: var(--store-primary);
            background: var(--store-primary-light);
            color: var(--store-primary);
        }

        .variant-pill-price {
            font-weight: 600;
            opacity: 0.75;
        }

        .variant-selected-label {
            color: var(--store-primary);
        }

        .variant-pill-btn:disabled,
        .variant-pill-btn.is-sold-out {
            opacity: 0.5;
            cursor: not-allowed;
            text-decoration: line-through;
            background: #f8fafc;
        }

        .variant-pill-btn.active {
            border-color: var(--store-primary) !important;
            background: var(--store-primary) !important;
            color: #ffffff !important;
            box-shadow: 0 4px 14px rgba(46, 125, 50, 0.35);
        }

        /* Quantity & Action Controls */
        .single-actions-row {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 2rem;
        }

        .single-qty-pill {
            display: inline-flex;
            align-items: center;
            background: #ffffff;
            border: 1.5px solid var(--store-border);
            border-radius: var(--store-radius-pill) !important;
            padding: 4px 6px;
            height: 50px;
        }

        .single-step-btn {
            width: 36px;
            height: 36px;
            border-radius: 50% !important;
            border: none;
            background: var(--store-primary-light);
            color: var(--store-primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .single-step-btn:hover {
            background: var(--store-primary);
            color: #ffffff;
        }

        .single-qty-value {
            min-width: 40px;
            text-align: center;
            font-size: 1.1rem;
            font-weight: 800;
            color: #1f2937;
        }

        .btn-single-cart {
            flex: 1 1 180px;
            height: 50px;
            border-radius: var(--store-radius-pill) !important;
            background: var(--store-primary);
            color: #ffffff !important;
            border: none;
            font-size: 1rem;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.3s ease;
            cursor: pointer;
            box-shadow: 0 4px 14px rgba(46, 125, 50, 0.25);
        }

        .btn-single-cart:hover {
            background: var(--store-primary-hover);
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(46, 125, 50, 0.35);
        }

        .btn-single-buy-now {
            flex: 1 1 180px;
            height: 50px;
            border-radius: var(--store-radius-pill) !important;
            background: #25d366;
            color: #ffffff !important;
            border: none;
            font-size: 1rem;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.3s ease;
            cursor: pointer;
            box-shadow: 0 4px 14px rgba(37, 211, 102, 0.3);
        }

        .btn-single-buy-now:hover {
            background: #1eb856;
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(37, 211, 102, 0.45);
        }

        .btn-single-cart:disabled,
        .btn-single-buy-now:disabled {
            opacity: 0.55;
            cursor: not-allowed;
            pointer-events: none;
            background: #9ca3af !important;
            box-shadow: none !important;
        }

        /* Description Container */
        .single-description-box {
            border-top: 1px solid #f1f8e9;
            padding-top: 1.5rem;
            color: #374151;
            font-size: 0.98rem;
            line-height: 1.8;
            text-align: start;
        }

        /* Related Products Section */
        .related-products-section {
            margin-bottom: 4rem;
        }

        .store-section-title-wrap {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .store-section-heading {
            font-size: 22px;
            font-weight: 800;
            color: var(--store-primary-hover);
            margin: 0;
        }
    </style>

    @include('templates.stores.shared.custom-code')
</head>

<body dir="{{ $isRtl ? 'rtl' : 'ltr' }}">

    {{-- 1. Orbit Preloader --}}
    @include('templates.stores.grocery.includes.preloader')

    <div class="store-main-wrapper">
        {{-- 2. Header Component --}}
        @include('templates.stores.grocery.includes.header')

        <div class="store-body-content-area">
            <div class="container-xl px-3 px-lg-4 py-2">

                {{-- Breadcrumbs Navigation strictly ABOVE Gallery --}}
                <div class="store-breadcrumbs-wrap">
                    <nav class="store-breadcrumbs-pill" aria-label="breadcrumb">
                        <a href="{{ $storeUrl }}" class="breadcrumb-link">
                            <i class="ti ti-home fs-4"></i>
                            <span>{{ __('Shop') }}</span>
                        </a>
                        <i class="ti ti-chevron-right breadcrumb-sep"></i>
                        <span class="breadcrumb-current">{{ $product_details->product_name }}</span>
                    </nav>
                </div>

                {{-- Single Product Master Container --}}
                <div class="product-master-card">
                    <div class="row g-4">
                        {{-- Left Column: Gallery Viewer --}}
                        <div class="col-lg-6">
                            <div class="single-gallery-main">
                                <img src="{{ $productImages ? url($productImages[0]) : $placeholder }}"
                                    id="singleMainDisplayImage" alt="{{ $product_details->product_name }}"
                                    class="single-main-img"
                                    onerror="this.onerror=null;this.src='{{ $placeholder }}';">
                            </div>

                            @if (count($productImages) > 1)
                                <div class="single-gallery-thumbs-row">
                                    @foreach ($productImages as $index => $img)
                                        <div class="single-gallery-thumb-item {{ $index === 0 ? 'active' : '' }}"
                                            onclick="switchMainImage('{{ url($img) }}', this)">
                                            <img src="{{ url($img) }}" alt="Thumbnail"
                                                class="single-gallery-thumb-img"
                                                onerror="this.onerror=null;this.src='{{ $placeholder }}';">
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        {{-- Product information: price, options, quantity and buttons are run by the storefront script --}}
                        <div class="col-lg-6" {{ $productOptions->attributes($product_details) }}>
                            <div class="single-badge-wrap">
                                @if ($product_details->badge)
                                    <span class="badge-pill-promo">{{ $product_details->badge }}</span>
                                @endif
                                <span class="badge-pill-stock" data-product-stock data-state="{{ $productOptions->canBuy ? 'in' : 'out' }}">
                                    <i class="ti {{ $productOptions->canBuy ? 'ti-check' : 'ti-x' }}"></i>
                                    <span data-product-stock-text>{{ $productOptions->canBuy ? __('In Stock') : __('Out of Stock') }}</span>
                                </span>
                            </div>

                            <h1 class="single-product-title">{{ $product_details->product_name }}</h1>
                            @if ($product_details->product_short_description)
                                <p class="single-product-subtitle">{{ $product_details->product_short_description }}</p>
                            @endif

                            <div class="single-price-box">
                                <p class="single-sales-price" data-product-price>{{ formatCurrencyCard($productOptions->salesPrice, $currency) }}</p>
                                <p class="single-regular-price" data-product-regular-price @if (!$productOptions->regularPrice) hidden @endif>
                                    {{ $productOptions->regularPrice ? formatCurrencyCard($productOptions->regularPrice, $currency) : '' }}
                                </p>
                            </div>

                            @if ($productOptions->hasOptions)
                                <div class="variant-selector-wrapper">
                                    <h4 class="variant-group-title">
                                        <i class="ti ti-adjustments-horizontal text-success"></i>
                                        <span>{{ __('Select Option / Size / Variation:') }}</span>
                                        <span class="variant-selected-label" data-option-label>{{ $productOptions->canBuy ? $productOptions->initial->variant_name : '' }}</span>
                                    </h4>
                                    <div class="variant-pills-list">
                                        @include('templates.stores.shared.product-options', [
                                            'buttonClass' => 'variant-pill-btn',
                                            'priceClass' => 'variant-pill-price',
                                        ])
                                    </div>
                                </div>
                            @endif

                            <div class="single-actions-row">
                                <div class="single-qty-pill">
                                    <button type="button" class="single-step-btn" data-qty-step="-1" aria-label="{{ __('Decrease') }}">-</button>
                                    <span class="single-qty-value" data-qty-value>1</span>
                                    <button type="button" class="single-step-btn" data-qty-step="1" aria-label="{{ __('Increase') }}">+</button>
                                </div>

                                <button type="button" class="btn-single-cart" data-product-add @disabled(!$productOptions->canBuy)>
                                    <i class="ti ti-shopping-bag-plus fs-2"></i>
                                    <span>{{ __('Add to Cart') }}</span>
                                </button>

                                {{-- Adds the item, then opens the cart to order on WhatsApp --}}
                                <button type="button" class="btn-single-buy-now" data-product-add data-then="cart" @disabled(!$productOptions->canBuy)>
                                    <i class="ti ti-brand-whatsapp fs-2"></i>
                                    <span>{{ __('Buy via WhatsApp') }}</span>
                                </button>
                            </div>

                            {{-- Long Description Box --}}
                            @if (!empty($product_details->product_description))
                                <div class="single-description-box">
                                    <h3 class="fw-bold text-dark mb-3">{{ __('Product Overview') }}</h3>
                                    {!! $product_details->product_description !!}
                                </div>
                            @endif

                            {{-- Integrated Luxury Share Component --}}
                            @include('templates.stores.grocery.includes.share', [
                                'productName' => $product_details->product_name,
                                'productUrl' => url()->current(),
                            ])
                        </div>
                    </div>
                </div>

                {{-- Related Products Section --}}
                @if (isset($related_products) && count($related_products) > 0)
                    <div class="related-products-section">
                        <div class="store-section-title-wrap">
                            <h2 class="store-section-heading">{{ __('You May Also Like') }}</h2>
                        </div>

                        <div class="store-product-grid">
                            @foreach ($related_products as $product)
                                @include('templates.stores.grocery.partials.product-card')
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- 3. Sticky Luxury Footer --}}
        @include('templates.stores.grocery.includes.footer')

        {{-- 4. Bottom Navigation Bar --}}
        @include('templates.stores.grocery.includes.bottom-bar')
    </div>

    {{-- Interactive WhatsApp Floating Chat Pod --}}
    @include('templates.stores.grocery.includes.whatsapp-float')

    {{-- PWA Bottom Sheet Modal --}}
    @include('templates.stores.grocery.includes.pwa-modal')

    {{-- VIP Promo & Newsletter Popups --}}
    @include('templates.stores.grocery.includes.popups')

    {{-- Cart drawer, checkout and the shared storefront script --}}
    @include('templates.stores.grocery.partials.cart-order-modal')
    @include('templates.stores.grocery.includes.scripts')

    <script>
        // Show a gallery thumbnail in the main image box (called by the thumbnails)
        function switchMainImage(src, element) {
            const mainImg = document.getElementById('singleMainDisplayImage');
            if (mainImg) mainImg.src = src;

            document.querySelectorAll('.single-gallery-thumb-item').forEach((thumb) => thumb.classList.remove('active'));
            if (element) element.classList.add('active');
        }
    </script>
</body>

</html>
