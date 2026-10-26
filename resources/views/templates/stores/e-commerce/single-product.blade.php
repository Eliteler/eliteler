@php
    $productImages = array_values(array_filter(array_map('trim', explode(',', (string) $product_details->product_image))));
    $placeholder = asset('img/templates/e-commerce/placeholder.svg');
    $firstProductImage = $productImages ? url($productImages[0]) : $placeholder;
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">

<head>
    @include('templates.stores.e-commerce.includes.head')

    <style>
        .card,
        .form-control,
        .rounded-e-commerce,
        .alert,
        input,
        select {
            border-radius: 0px !important;
        }

        /* ========================================================= */
        /* Top Breadcrumbs Card (Above Gallery)                      */
        /* ========================================================= */
        .single-breadcrumbs-top {
            background-color: #ffffff;
            border: 1px solid #edf2f7;
            border-radius: 14px;
            padding: 12px 16px;
            font-size: 0.85rem;
            font-weight: 600;
            color: #64748b;
            display: flex;
            align-items: center;
            gap: 6px;
            width: 100%;
            overflow: hidden;
            white-space: nowrap;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.02);
            margin-bottom: 8px;
        }

        .single-breadcrumb-link {
            color: #206bc4;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            flex-shrink: 0;
        }

        .single-breadcrumb-separator {
            font-size: 0.75rem;
            color: #94a3b8;
            flex-shrink: 0;
        }

        .single-breadcrumb-current {
            white-space: nowrap !important;
            overflow: hidden !important;
            text-overflow: ellipsis !important;
            min-width: 0 !important;
            flex: 1 1 auto !important;
            color: #475569;
        }

        /* Gallery Styles */
        .product-main-gallery-card {
            background-color: #ffffff;
            border-radius: 20px;
            border: 1px solid #edf2f7;
            padding: 16px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.02);
        }

        .product-main-img-box {
            border-radius: 16px;
            overflow: hidden;
            background-color: #f8fafc;
            border: 1px solid #f1f5f9;
        }

        .product-main-img {
            object-fit: contain;
            object-position: center;
            width: 100%;
            height: 100%;
            padding: 8px;
        }

        .product-thumb-item {
            width: 100%;
            aspect-ratio: 1 / 1;
            border-radius: 12px;
            overflow: hidden;
            background-color: #ffffff;
            border: 2px solid #e2e8f0;
            padding: 4px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .product-thumb-item:hover,
        .product-thumb-item.active {
            border-color: #206bc4;
            box-shadow: 0 2px 8px rgba(32, 107, 196, 0.2);
        }

        .product-thumb-img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        /* Typography */
        .single-product-title {
            font-family: 'Outfit', sans-serif;
            font-size: 1.85rem;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: -0.4px;
            line-height: 1.25;
            margin-bottom: 8px;
        }

        .single-product-subtitle {
            font-size: 0.95rem;
            color: #64748b;
            line-height: 1.5;
            margin-bottom: 16px;
        }

        /* Pricing Area */
        .single-price-box {
            display: flex;
            align-items: baseline;
            gap: 12px;
            margin-bottom: 16px;
        }

        .single-sales-price {
            font-size: 2rem;
            font-weight: 800;
            color: #206bc4;
            line-height: 1;
        }

        .single-regular-price {
            font-size: 1.25rem;
            font-weight: 600;
            color: #94a3b8;
            text-decoration: line-through;
            line-height: 1;
        }

        .single-discount-badge {
            display: inline-flex;
            align-items: center;
            padding: 4px 10px;
            font-size: 0.78rem;
            font-weight: 700;
            color: #ef4444;
            background-color: #fee2e2;
            border-radius: 50px;
            line-height: 1;
        }

        /* Badges */
        .single-badge-pill {
            display: inline-flex;
            align-items: center;
            padding: 5px 12px;
            font-size: 0.78rem;
            font-weight: 700;
            border-radius: 50px;
            line-height: 1;
        }

        .single-badge-pill[data-state="in"] {
            background-color: #dcfce7;
            color: #16a34a;
            border: 1px solid #bbf7d0;
        }

        .single-badge-pill[data-state="out"] {
            background-color: #fee2e2;
            color: #dc2626;
            border: 1px solid #fecaca;
        }

        /* Variations Selector UI (Rule 6 Protocol) */
        .variant-selector-wrapper {
            background-color: #f8fafc;
            border: 1px solid #edf2f7;
            border-radius: 16px;
            padding: 16px;
            margin-bottom: 20px;
        }

        .variant-selector-label {
            font-size: 0.85rem;
            font-weight: 700;
            color: #334155;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .variant-pills-container {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .variant-pill {
            appearance: none !important;
            -webkit-appearance: none !important;
            background-color: #ffffff;
            border: 1.5px solid #cbd5e1;
            border-radius: 12px;
            padding: 8px 16px;
            font-size: 0.88rem;
            font-weight: 700;
            color: #334155;
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .variant-pill:hover {
            border-color: #206bc4;
            color: #206bc4;
            background-color: #f0f7ff;
        }

        .variant-pill.active {
            border-color: #206bc4;
            background-color: #206bc4;
            color: #ffffff !important;
            box-shadow: 0 4px 12px rgba(32, 107, 196, 0.25);
        }

        .variant-pill-price {
            font-weight: 600;
            opacity: 0.75;
        }

        .variant-pill:disabled,
        .variant-pill.is-sold-out {
            opacity: 0.5;
            cursor: not-allowed;
            border-style: dashed;
            background-color: #f1f5f9;
        }

        /* Quantity & Action Buttons */
        .single-action-row {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 24px;
            flex-wrap: wrap;
        }

        .single-qty-picker {
            display: inline-flex;
            align-items: center;
            background-color: #f1f5f9;
            border-radius: 12px;
            padding: 4px;
            height: 48px;
        }

        .single-qty-btn {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            border: none;
            background-color: #ffffff;
            color: #0f172a;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            cursor: pointer;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
            transition: background-color 0.15s ease;
        }

        .single-qty-btn:hover {
            background-color: #e2e8f0;
        }

        .single-qty-input {
            width: 44px;
            border: none;
            background: transparent;
            text-align: center;
            font-weight: 700;
            font-size: 1rem;
            color: #0f172a;
        }

        .single-btn-cart {
            height: 48px !important;
            border-radius: 12px !important;
            background-color: #206bc4 !important;
            color: #ffffff !important;
            font-weight: 700 !important;
            font-size: 0.95rem !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            padding: 0 24px !important;
            box-shadow: 0 4px 14px rgba(32, 107, 196, 0.35) !important;
            flex: 1 1 auto;
            cursor: pointer !important;
            border: none !important;
        }

        .single-btn-cart i,
        .single-btn-cart span {
            color: #ffffff !important;
        }

        .single-btn-cart:hover {
            background-color: #1a569d !important;
            color: #ffffff !important;
        }

        .single-btn-buy {
            height: 48px !important;
            border-radius: 12px !important;
            background-color: #0f172a !important;
            color: #ffffff !important;
            font-weight: 700 !important;
            font-size: 0.95rem !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            padding: 0 20px !important;
            flex: 1 1 auto;
            cursor: pointer !important;
            border: none !important;
        }

        .single-btn-buy i,
        .single-btn-buy span {
            color: #ffffff !important;
        }

        /* Description Container */
        .single-description-box {
            border-top: 1px solid #edf2f7;
            padding-top: 20px;
            margin-bottom: 24px;
            color: #334155;
            font-size: 0.95rem;
            line-height: 1.7;
        }

        /* Related Products Action Buttons */
        .related-section-header {
            position: relative;
            font-size: 1.75rem;
            font-weight: 700;
            color: #0f172a;
            padding-bottom: 12px;
            margin-bottom: 24px;
        }

        .related-header-bar {
            position: absolute;
            bottom: 0;
            width: 50px;
            height: 3px;
            background-color: #206bc4;
        }

    </style>

    @include('templates.stores.shared.custom-code')
</head>

<body dir="{{ $isRtl ? 'rtl' : 'ltr' }}">

    <!-- Fail-safe Auto-Dismiss Preloader -->
    @include('templates.stores.e-commerce.includes.preloader')

    <div id="wrapper" class="page m-0 store-bg-wrapper">
        <div class="page-wrapper m-0 p-0">
            <div class="page-body m-0 p-0">
                <div class="container-xl card rounded-0 p-0 overflow-hidden store-main-container">

                    <!-- Modular Top Header -->
                    @include('templates.stores.e-commerce.includes.header', [
                        'bg_color' => 'e-commerce-blue',
                        'badge_color' => 'red',
                        'color' => 'e-commerce-blue',
                    ])

                    <div class="col-md-12 my-3 px-3 px-lg-4">

                        <!-- 1. Top Breadcrumbs (Placed strictly ABOVE image gallery) -->
                        <div class="single-breadcrumbs-top">
                            <a href="{{ $storeUrl }}" class="single-breadcrumb-link">
                                <i class="ti ti-smart-home me-1"></i>{{ __('Home') }}
                            </a>
                            <i class="ti ti-chevron-right single-breadcrumb-separator"></i>
                            <a href="{{ $storeUrl }}" class="single-breadcrumb-link">
                                {{ __('Products') }}
                            </a>
                            <i class="ti ti-chevron-right single-breadcrumb-separator"></i>
                            <span class="single-breadcrumb-current" title="{{ $product_details->product_name }}">
                                {{ $product_details->product_name }}
                            </span>
                        </div>

                        <!-- 2. Main Product View Row -->
                        <div class="row g-4 my-1">

                            <!-- Left Column: Product Image Gallery -->
                            <div class="col-12 col-md-6">
                                <div class="product-main-gallery-card">
                                    <!-- Main Feature Image with Lightbox Zoom -->
                                    <a href="{{ $firstProductImage }}" data-fslightbox="product-gallery"
                                        id="main_image_link">
                                        <div class="ratio ratio-4x3 product-main-img-box mb-3">
                                            <img src="{{ $firstProductImage }}" id="main_product_image"
                                                alt="{{ $product_details->product_name }}" class="product-main-img"
                                                onerror="this.onerror=null; this.src='{{ $placeholder }}';">
                                        </div>
                                    </a>

                                    <!-- Thumbnails Slider -->
                                    @if (count($productImages) > 1)
                                        <swiper-container space-between="10" class="mySwiper" autoplay-delay="3000"
                                            breakpoints='{
                                                "320": { "slidesPerView": 3 },
                                                "640": { "slidesPerView": 4 },
                                                "1024": { "slidesPerView": 4 }
                                            }'
                                            autoplay-disable-on-interaction="false" loop="false">
                                            @foreach ($productImages as $pImg)
                                                @php $thumbUrl = url($pImg); @endphp
                                                <swiper-slide>
                                                    <div class="product-thumb-item {{ $loop->first ? 'active' : '' }}"
                                                        onclick="switchMainImage('{{ $thumbUrl }}', this)">
                                                        <img src="{{ $thumbUrl }}" class="product-thumb-img"
                                                            alt="{{ $product_details->product_name }}"
                                                            onerror="this.onerror=null; this.src='{{ $placeholder }}';">
                                                    </div>
                                                </swiper-slide>
                                            @endforeach
                                        </swiper-container>
                                    @endif
                                </div>
                            </div>

                            {{-- Product information: price, options, quantity and buttons are run by the storefront script --}}
                            <div class="col-12 col-md-6" {{ $productOptions->attributes($product_details) }}>
                                <div class="p-1 p-lg-2">
                                    <h1 class="single-product-title">{{ $product_details->product_name }}</h1>
                                    @if (!empty($product_details->product_short_description))
                                        <p class="single-product-subtitle">{{ $product_details->product_short_description }}</p>
                                    @endif

                                    @php
                                        $onSale = $productOptions->regularPrice && $productOptions->regularPrice > $productOptions->salesPrice;
                                    @endphp
                                    <div class="single-price-box">
                                        <span class="single-sales-price" data-product-price>{{ formatCurrencyCard($productOptions->salesPrice, $currency) }}</span>
                                        <span class="single-regular-price" data-product-regular-price @if (!$onSale) hidden @endif>
                                            {{ $onSale ? formatCurrencyCard($productOptions->regularPrice, $currency) : '' }}
                                        </span>
                                        <span class="single-discount-badge" data-product-discount @if (!$onSale) hidden @endif>
                                            <span data-product-discount-value>{{ $onSale ? round((($productOptions->regularPrice - $productOptions->salesPrice) / $productOptions->regularPrice) * 100) : '' }}</span>% {{ __('OFF') }}
                                        </span>
                                    </div>

                                    <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                                        @if (!empty($product_details->badge))
                                            <span class="single-badge-pill bg-e-commerce-blue text-white">{{ $product_details->badge }}</span>
                                        @endif

                                        <span class="single-badge-pill" data-product-stock data-state="{{ $productOptions->canBuy ? 'in' : 'out' }}">
                                            {{ $productOptions->canBuy ? __('In Stock') : __('Out of Stock') }}
                                        </span>
                                    </div>

                                    @if ($productOptions->hasOptions)
                                        <div class="variant-selector-wrapper">
                                            <div class="variant-selector-label">
                                                <span>{{ __('Select Option / Variation:') }}</span>
                                                <span class="text-primary fw-bold" data-option-label>{{ $productOptions->canBuy ? $productOptions->initial->variant_name : '' }}</span>
                                            </div>
                                            <div class="variant-pills-container">
                                                @include('templates.stores.shared.product-options', [
                                                    'buttonClass' => 'variant-pill',
                                                    'priceClass' => 'variant-pill-price',
                                                ])
                                            </div>
                                        </div>
                                    @endif

                                    <div class="single-action-row">
                                        <div class="single-qty-picker">
                                            <button type="button" class="single-qty-btn" data-qty-step="-1" aria-label="{{ __('Decrease') }}"><i class="ti ti-minus"></i></button>
                                            <span class="single-qty-input" data-qty-value>1</span>
                                            <button type="button" class="single-qty-btn" data-qty-step="1" aria-label="{{ __('Increase') }}"><i class="ti ti-plus"></i></button>
                                        </div>

                                        <button type="button" class="btn single-btn-cart" data-product-add @disabled(!$productOptions->canBuy)>
                                            <i class="ti ti-shopping-bag-plus fs-2 me-2"></i>
                                            <span>{{ __('Add to Cart') }}</span>
                                        </button>

                                        {{-- Adds the item, then opens the cart to order on WhatsApp --}}
                                        <button type="button" class="btn single-btn-buy btn-effect-loop" data-product-add data-then="cart" @disabled(!$productOptions->canBuy)>
                                            <i class="ti ti-bolt fs-2 me-1"></i>
                                            <span>{{ __('Buy Now') }}</span>
                                        </button>
                                    </div>

                                    <!-- Product Description -->
                                    @if (!empty($product_details->product_description))
                                        <div class="single-description-box">
                                            <h4 class="fw-bold text-dark mb-2 fs-2">{{ __('Product Description') }}</h4>
                                            <div>{!! $product_details->product_description !!}</div>
                                        </div>
                                    @endif

                                    <!-- Share Trigger -->
                                    @include('templates.stores.e-commerce.includes.share', [
                                        'productName' => $product_details->product_name,
                                        'productUrl' => url()->current(),
                                    ])

                                </div>
                            </div>

                        </div>

                        <!-- Related Products Section (Rule 5 & 6 Protocol) -->
                        @if (isset($related_products) && count($related_products) > 0)
                            <div class="my-5">
                                <div class="text-center position-relative mb-4">
                                    <h2 class="related-section-header d-inline-block">
                                        {{ __('Related Products') }}
                                        <span class="related-header-bar start-50 translate-middle-x"></span>
                                    </h2>
                                </div>

                                <div class="col-md-12">
                                    <swiper-container space-between="15" class="mySwiper" autoplay-delay="3000"
                                        breakpoints='{
                                            "320": { "slidesPerView": 1 },
                                            "640": { "slidesPerView": 2 }, 
                                            "1024": { "slidesPerView": 4 } 
                                        }'
                                        autoplay-disable-on-interaction="false" loop="true">

                                        @foreach ($related_products as $product)
                                            <swiper-slide>
                                                @include('templates.stores.e-commerce.partials.product-card', ['class' => 'h-100'])
                                            </swiper-slide>
                                        @endforeach
                                    </swiper-container>
                                </div>
                            </div>
                        @endif

                    </div>

                </div>

                <!-- Modular Footer Include -->
                @include('templates.stores.e-commerce.includes.footer')
            </div>
        </div>

        <!-- Modular Mobile Bottom Bar Include -->
        @include('templates.stores.e-commerce.includes.bottom-bar', [
            'color' => 'e-commerce-blue',
            'bg' => 'light',
            'bg_color' => 'e-commerce-blue',
        ])
    </div>

    <!-- Floating WhatsApp Widget Include -->
    @include('templates.stores.e-commerce.includes.whatsapp-float', [
        'businessImage' => $business_card_details->profile,
        'businessName' => $card_details->title,
        'whatsappNumber' => $enquiry_button,
    ])

    {{-- Cart drawer, checkout and the shared storefront script --}}
    @include('templates.stores.e-commerce.partials.cart-order-modal')
    @include('templates.stores.e-commerce.includes.scripts')

    <script src="{{ url('js/fslightbox.js') }}"></script>
    <script>
        // Show a gallery thumbnail in the main image box and lightbox (called by the thumbnails)
        function switchMainImage(imgUrl, thumbEl) {
            const mainImg = document.getElementById('main_product_image');
            const mainLink = document.getElementById('main_image_link');
            if (mainImg) mainImg.src = imgUrl;
            if (mainLink) mainLink.href = imgUrl;

            document.querySelectorAll('.product-thumb-item').forEach((thumb) => thumb.classList.remove('active'));
            if (thumbEl) thumbEl.classList.add('active');

            if (typeof refreshFsLightbox === 'function') refreshFsLightbox();
        }
    </script>
</body>

</html>
