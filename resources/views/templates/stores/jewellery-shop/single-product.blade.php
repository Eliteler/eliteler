{{-- GoBiz Luxury Jewellery Store - Single Product Details & Variations Engine --}}
@php
    $productImages = array_values(array_filter(array_map('trim', explode(',', (string) $product_details->product_image))));
    $placeholder = asset('img/templates/jewellery-shop/placeholder.svg');
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">

<head>
    @include('templates.stores.jewellery-shop.includes.head')

    <style>
        :root {
            --store-radius-card: 26px;
        }


        *,
        *::before,
        *::after {
            box-sizing: border-box;
        }

        /* 1. Breadcrumbs strictly ABOVE Gallery */
        .store-breadcrumbs-wrap {
            padding: 1.5rem 0 0.85rem 0;
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
            padding: 6px 18px;
            font-size: 13px;
            font-weight: 600;
            color: var(--store-primary-dark);
            box-shadow: 0 2px 8px rgba(212, 175, 55, 0.06);
            max-width: 100%;
            overflow: hidden;
            white-space: nowrap;
        }

        .breadcrumb-link {
            color: var(--store-primary-dark) !important;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: color 0.2s ease;
            flex-shrink: 0;
        }

        .breadcrumb-link:hover {
            color: var(--store-primary-hover) !important;
        }

        .breadcrumb-sep {
            color: var(--store-primary);
            font-size: 12px;
            opacity: 0.6;
            flex-shrink: 0;
        }

        [dir="rtl"] .breadcrumb-sep {
            transform: rotate(180deg);
        }

        .breadcrumb-current {
            color: var(--store-dark);
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
            border: 1.5px solid var(--store-border);
            border-radius: var(--store-radius-card);
            box-shadow: 0 10px 35px rgba(0, 0, 0, 0.03), 0 0 20px rgba(212, 175, 55, 0.06);
            padding: 32px;
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
            border-radius: 20px;
            overflow: hidden;
            background: var(--store-primary-light);
            border: 1.5px solid var(--store-border);
            position: relative;
            margin-bottom: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .single-main-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
            transition: transform 0.4s ease;
        }

        .single-gallery-main:hover .single-main-img {
            transform: scale(1.03);
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
            background: #ffffff;
            border: 2px solid var(--store-border);
            cursor: pointer;
            transition: all 0.25s ease;
            flex-shrink: 0;
        }

        .single-gallery-thumb-item:hover,
        .single-gallery-thumb-item.active {
            border-color: var(--store-primary);
            box-shadow: 0 4px 12px rgba(212, 175, 55, 0.3);
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
            background: var(--store-primary);
            color: #ffffff;
            font-size: 11px;
            font-weight: 700;
            padding: 4px 14px;
            border-radius: var(--store-radius-pill) !important;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .badge-pill-stock {
            background: var(--store-primary-light);
            border: 1px solid var(--store-border);
            color: var(--store-primary-dark);
            font-size: 11px;
            font-weight: 700;
            padding: 4px 14px;
            border-radius: var(--store-radius-pill) !important;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .badge-pill-stock[data-state="out"] {
            background: #fee2e2;
            color: #dc2626;
            border-color: #fecaca;
        }

        .single-product-title {
            font-family: 'Gloock', serif;
            font-size: 30px;
            font-weight: 700;
            color: var(--store-dark);
            margin-bottom: 6px;
            line-height: 1.25;
            text-align: start;
        }

        @media (max-width: 768px) {
            .single-product-title {
                font-size: 24px;
            }
        }

        .single-product-subtitle {
            font-size: 14.5px;
            color: var(--store-text-muted);
            margin-bottom: 1.25rem;
            line-height: 1.6;
            text-align: start;
        }

        /* Price Display */
        .single-price-box {
            display: flex;
            align-items: baseline;
            gap: 14px;
            margin-bottom: 1.5rem;
            padding-bottom: 1.25rem;
            border-bottom: 1px solid rgba(212, 175, 55, 0.2);
        }

        .single-sales-price {
            font-size: 32px;
            font-weight: 800;
            color: var(--store-primary-hover);
            margin: 0;
            line-height: 1;
        }

        .single-regular-price {
            font-size: 16px;
            color: var(--store-text-muted);
            text-decoration: line-through !important;
            margin: 0;
            line-height: 1;
        }

        /* 3. Variant Pill Selector Engine */
        .variant-selector-wrapper {
            margin-bottom: 1.5rem;
            padding: 18px 20px;
            background: #ffffff;
            border: 1.5px solid var(--store-border);
            border-radius: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.02);
        }

        .variant-group-title {
            font-size: 13px;
            font-weight: 800;
            color: var(--store-dark);
            text-transform: uppercase;
            letter-spacing: 0.6px;
            margin-bottom: 12px;
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
            background: var(--store-primary-light);
            color: var(--store-dark);
            padding: 8px 18px;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.25s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            line-height: 1;
        }

        .variant-pill-btn:hover {
            border-color: var(--store-primary);
            background: #ffffff;
            transform: translateY(-2px);
        }

        .variant-pill-price {
            font-weight: 600;
            opacity: 0.75;
        }

        .variant-selected-label {
            color: var(--store-primary);
            text-transform: none;
            letter-spacing: 0;
        }

        .variant-pill-btn:disabled,
        .variant-pill-btn.is-sold-out {
            opacity: 0.5;
            cursor: not-allowed;
            text-decoration: line-through;
            background: #f8f8f8;
            transform: none;
        }

        .variant-pill-btn.active {
            border-color: var(--store-primary) !important;
            background: var(--store-primary) !important;
            color: #ffffff !important;
            box-shadow: 0 6px 18px rgba(212, 175, 55, 0.35);
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
            padding: 4px;
            height: 48px;
            flex-shrink: 0;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        }

        .single-step-btn {
            width: 38px;
            height: 38px;
            border-radius: 50% !important;
            border: none;
            background: var(--store-primary-light);
            color: var(--store-dark);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .single-step-btn:hover {
            background: var(--store-primary);
            color: #ffffff;
        }

        .single-qty-value {
            width: 44px;
            text-align: center;
            font-size: 15px;
            font-weight: 800;
            color: var(--store-dark);
            user-select: none;
        }

        .btn-single-cart {
            flex: 1 1 200px;
            height: 48px;
            border-radius: var(--store-radius-pill) !important;
            background: var(--store-primary);
            color: #ffffff !important;
            border: none;
            font-size: 14px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.3s ease;
            cursor: pointer;
            box-shadow: 0 6px 20px rgba(212, 175, 55, 0.35);
        }

        .btn-single-cart i {
            font-size: 1.25rem;
        }

        .btn-single-cart:hover {
            background: var(--store-primary-hover);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(212, 175, 55, 0.45);
        }

        .btn-single-buy-now {
            width: 100%;
            height: 48px;
            border-radius: var(--store-radius-pill) !important;
            background: linear-gradient(135deg, #25d366 0%, #128c7e 100%) !important;
            color: #ffffff !important;
            border: none;
            font-size: 14px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            cursor: pointer;
            transition: all 0.25s ease;
            box-shadow: 0 6px 20px rgba(37, 211, 102, 0.35);
        }

        .btn-single-buy-now:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(37, 211, 102, 0.45);
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
            border-top: 1px solid rgba(212, 175, 55, 0.2);
            padding-top: 1.5rem;
            color: #433726;
            font-size: 14.5px;
            line-height: 1.8;
            text-align: start;
        }

        .single-description-title {
            font-family: 'Gloock', serif;
            font-size: 20px;
            color: var(--store-dark);
            margin-bottom: 12px;
        }

        /* Related Products Section */
        .related-products-section {
            margin-bottom: 4rem;
        }

        .store-section-title-wrap {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 24px;
        }

        .store-section-heading {
            font-family: 'Gloock', serif;
            font-size: 24px;
            font-weight: 700;
            color: var(--store-dark);
            margin: 0;
        }
    </style>

    @include('templates.stores.shared.custom-code')
</head>

<body dir="{{ $isRtl ? 'rtl' : 'ltr' }}">

    {{-- 1. Orbit Preloader --}}
    @include('templates.stores.jewellery-shop.includes.preloader')

    <div class="store-main-wrapper">
        {{-- 2. Header Component --}}
        @include('templates.stores.jewellery-shop.includes.header')

        <div class="store-body-content-area">
            <div class="container-xl px-3 px-lg-4 py-2">

                {{-- Breadcrumbs Navigation strictly ABOVE Gallery --}}
                <div class="store-breadcrumbs-wrap">
                    <nav class="store-breadcrumbs-pill" aria-label="breadcrumb">
                        <a href="{{ $storeUrl }}" class="breadcrumb-link">
                            <i class="ti ti-home fs-4"></i>
                            <span>{{ __('Home') }}</span>
                        </a>
                        <i class="ti ti-chevron-right breadcrumb-sep"></i>
                        <a href="{{ $storeUrl }}" class="breadcrumb-link">
                            <span>{{ __('Collections') }}</span>
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
                                @if (!empty($product_details->badge))
                                    <span class="badge-pill-promo">{{ $product_details->badge }}</span>
                                @endif
                                <span class="badge-pill-stock" data-product-stock data-state="{{ $productOptions->canBuy ? 'in' : 'out' }}">
                                    <i class="ti {{ $productOptions->canBuy ? 'ti-check' : 'ti-x' }}"></i>
                                    <span data-product-stock-text>{{ $productOptions->canBuy ? __('In Stock') : __('Out of Stock') }}</span>
                                </span>
                            </div>

                            <h1 class="single-product-title">{{ $product_details->product_name }}</h1>
                            @if (!empty($product_details->product_short_description))
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
                                        <i class="ti ti-adjustments-horizontal text-gold"></i>
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
                                    <i class="ti ti-shopping-bag-plus"></i>
                                    <span>{{ __('Add to Cart') }}</span>
                                </button>

                                @if (!empty($enquiry_button))
                                    {{-- Adds the item, then opens the cart to order on WhatsApp --}}
                                    <button type="button" class="btn-single-buy-now" data-product-add data-then="cart" @disabled(!$productOptions->canBuy)>
                                        <i class="ti ti-brand-whatsapp"></i>
                                        <span>{{ __('Buy via WhatsApp') }}</span>
                                    </button>
                                @endif
                            </div>

                            {{-- Long Description Box --}}
                            @if (!empty($product_details->product_description))
                                <div class="single-description-box">
                                    <h3 class="single-description-title">{{ __('Product Overview') }}</h3>
                                    {!! $product_details->product_description !!}
                                </div>
                            @endif

                            {{-- Integrated Luxury Share Component --}}
                            @include('templates.stores.jewellery-shop.includes.share', [
                                'customShareTitle' =>
                                    $product_details->product_name . ' - ' . $card_details->title,
                                'customShareUrl' => url()->current(),
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
                                @include('templates.stores.jewellery-shop.partials.product-card')
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- 3. Sticky Luxury Footer --}}
        @include('templates.stores.jewellery-shop.includes.footer')

        {{-- 4. Bottom Navigation Bar --}}
        @include('templates.stores.jewellery-shop.includes.bottom-bar')
    </div>

    {{-- Interactive WhatsApp Floating Chat Pod --}}
    @include('templates.stores.jewellery-shop.includes.whatsapp-float')

    {{-- PWA Bottom Sheet Modal --}}
    @include('templates.stores.jewellery-shop.includes.pwa-modal')

    {{-- VIP Promo & Newsletter Popups --}}
    @include('templates.stores.jewellery-shop.includes.popups')

    @include('templates.stores.jewellery-shop.partials.cart-order-modal')
    @include('templates.stores.jewellery-shop.includes.scripts')

    <script>
        // Gallery: show the clicked thumbnail as the main image
        function switchMainImage(src, thumbnail) {
            document.getElementById('singleMainDisplayImage').src = src;
            document.querySelectorAll('.single-gallery-thumb-item').forEach((item) => item.classList.toggle('active', item === thumbnail));
        }
    </script>
</body>

</html>
