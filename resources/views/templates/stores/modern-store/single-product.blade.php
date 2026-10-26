{{-- Modern store product page: gallery, options, add to cart and related products --}}
@php
    $dark = $storeTheme->isDark();
    $ink = $dark ? 'text-white' : 'text-dark';
    $heading = $dark ? 'text-white' : '';
    $productImages = array_values(array_filter(array_map('trim', explode(',', (string) $product_details->product_image))));
    $placeholder = asset('img/templates/modern-store/placeholder.svg');
    $firstImage = $productImages ? url($productImages[0]) : $placeholder;
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">

<head>
    @include('templates.stores.modern-store.includes.head')

    <style>
        .option-pill {
            display: inline-flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 2px;
            line-height: 1.2;
        }

        .option-pill-price {
            font-size: 0.8rem;
            opacity: 0.8;
        }

        .option-pill:disabled,
        .option-pill.is-sold-out {
            opacity: 0.5;
            text-decoration: line-through;
        }

        .stock-badge[data-state="in"] {
            background-color: var(--tblr-green-lt);
            color: var(--tblr-green);
        }

        .stock-badge[data-state="out"] {
            background-color: var(--tblr-red-lt);
            color: var(--tblr-red);
        }
    </style>

    @include('templates.stores.shared.custom-code')
</head>

<body @if ($dark) data-bs-theme="dark" @endif dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
    <div class="page page-center preloader-wrapper {{ $dark ? 'bg-dark' : '' }}">
        <div class="container container-slim py-4">
            <div class="text-center">
                <div class="spinner-border text-{{ $storeTheme->color }}" role="status"></div>
            </div>
        </div>
    </div>

    {{-- Page --}}
    <div id="wrapper" class="page">
        <!-- Navbar -->
        @include('templates.stores.includes.header', ['bg_color' => $storeTheme->color, 'badge_color' => 'danger'])

        <div class="page-wrapper mt-4 p-3">
            <div class="page-body">
                <div class="container-xl">
                    <!-- Product Details -->
                    <div class="pt-6">
                        <div class="card">
                            <div class="card-body p-3 p-lg-4">
                                <div class="row">
                                    <!-- Product Image -->
                                    <div class="col-md-6">
                                        <a href="{{ $firstImage }}" data-fslightbox="gallery">
                                            <div class="ratio ratio-16x9 mb-2">
                                                <img src="{{ $firstImage }}" id="product_image" onerror="this.onerror=null;this.src='{{ $placeholder }}';"
                                                    alt="{{ $product_details->product_name }}"
                                                    class="w-100 rounded object-fit-contain">
                                            </div>
                                        </a>
                                        <swiper-container space-between="10" class="mySwiper" autoplay-delay="2500"
                                            breakpoints='{
                                            "320": { "slidesPerView": 2 },
                                            "640": { "slidesPerView": 3 }, 
                                            "1024": { "slidesPerView": 3 } 
                                        }'
                                            autoplay-disable-on-interaction="false"
                                            loop="{{ count($productImages) > 3 ? 'true' : 'false' }}">
                                            @foreach ($productImages as $productImage)
                                                <swiper-slide>
                                                    <a href="{{ url($productImage) }}" data-fslightbox="gallery">
                                                        <div class="product-image-wrapper">
                                                            <img class="img-fluid w-100 h-100 object-fit-contain rounded"
                                                                onclick="showProductImage(this)"
                                                                alt="{{ $product_details->product_name }}"
                                                                src="{{ url($productImage) }}">
                                                        </div>
                                                    </a>
                                                </swiper-slide>
                                            @endforeach
                                        </swiper-container>
                                    </div>
                                    {{-- Product information: price, options, quantity and buttons are run by the storefront script --}}
                                    <div class="col-md-6 pt-4 p-lg-3" {{ $productOptions->attributes($product_details) }}>
                                        <h3 class="fs-custom fw-bold {{ $heading }}" id="product_name">{{ $product_details->product_name }}</h3>
                                        <p class="fs-3 {{ $dark ? 'text-white' : 'text-muted' }}">{{ $product_details->product_short_description }}</p>

                                        <div class="d-flex flex-column">
                                            <p class="text-muted fs-2 m-0" data-product-regular-price @if (!$productOptions->regularPrice) hidden @endif>
                                                <del>{{ $productOptions->regularPrice ? formatCurrencyCard($productOptions->regularPrice, $currency) : '' }}</del>
                                            </p>
                                            <p class="m-0 fs-1 fw-semibold {{ $ink }}" data-product-price>{{ formatCurrencyCard($productOptions->salesPrice, $currency) }}</p>
                                        </div>

                                        <div class="badge-container d-flex flex-wrap align-items-center gap-2 my-3">
                                            @if ($product_details->badge)
                                                <span class="badge {{ $dark ? 'badge-custom' : '' }}">{{ $product_details->badge }}</span>
                                            @endif
                                            <span class="badge stock-badge" data-product-stock data-state="{{ $productOptions->canBuy ? 'in' : 'out' }}">
                                                <span data-product-stock-text>{{ $productOptions->canBuy ? __('In Stock') : __('Out of Stock') }}</span>
                                            </span>
                                        </div>

                                        @if ($productOptions->hasOptions)
                                            <div class="mb-3">
                                                <div class="fs-3 fw-semibold mb-2 {{ $ink }}">
                                                    {{ __('Select Option') }}:
                                                    <span class="text-{{ $storeTheme->color }}" data-option-label>{{ $productOptions->canBuy ? $productOptions->initial->variant_name : '' }}</span>
                                                </div>
                                                <div class="d-flex flex-wrap gap-2">
                                                    @include('templates.stores.shared.product-options', [
                                                        'buttonClass' => 'btn btn-outline-' . $storeTheme->color . ' option-pill',
                                                        'activeClass' => 'active',
                                                        'priceClass' => 'option-pill-price',
                                                    ])
                                                </div>
                                            </div>
                                        @endif

                                        <div class="d-flex flex-wrap align-items-center gap-3 mb-3">
                                            <div class="d-flex align-items-center border rounded-pill px-1">
                                                <button type="button" class="btn btn-ghost-secondary p-0 border-0 d-flex align-items-center justify-content-center"
                                                    style="width: 38px; height: 38px;" data-qty-step="-1" aria-label="{{ __('Decrease') }}"><i class="ti ti-minus m-0"></i></button>
                                                <span class="fs-3 fw-bold text-center {{ $ink }}" style="min-width: 34px;" data-qty-value>1</span>
                                                <button type="button" class="btn btn-ghost-secondary p-0 border-0 d-flex align-items-center justify-content-center"
                                                    style="width: 38px; height: 38px;" data-qty-step="1" aria-label="{{ __('Increase') }}"><i class="ti ti-plus m-0"></i></button>
                                            </div>

                                            <button type="button" data-product-add @disabled(!$productOptions->canBuy)
                                                class="btn btn-{{ $storeTheme->color }} btn-lg flex-grow-1 fs-2 fw-bold btn-effect d-flex align-items-center justify-content-center gap-2">
                                                <i class="ti ti-shopping-bag-plus fs-1"></i>
                                                {{ __('Add to Cart') }}
                                            </button>
                                        </div>

                                        <div class="fs-3 {{ $dark ? 'text-white' : '' }}">{!! $product_details->product_description !!}</div>

                                        @include('templates.stores.includes.share', [
                                            'productName' => $product_details->product_name,
                                            'productUrl' => url()->current(),
                                        ])
                                    </div>
                                </div>

                                <!-- Related Products Slider -->
                                @if (count($related_products) > 0)
                                    <div class="row">
                                        <h2 class="position-relative fs-custom fw-bold pt-6 pb-5 {{ $heading }}">
                                            {{ __('Related Products') }}
                                            <div class="position-absolute bottom-bar2 bg-{{ $storeTheme->color }}">
                                            </div>
                                        </h2>
                                        <div class="col-md-12">
                                            <swiper-container space-between="10" class="mySwiper"
                                                autoplay-delay="2500"
                                                breakpoints='{
                                                "320": { "slidesPerView": 1 },
                                                "640": { "slidesPerView": 3 }, 
                                                "1024": { "slidesPerView": 4 } 
                                            }'
                                                autoplay-disable-on-interaction="false"
                                                loop="{{ count($related_products) > 4 ? 'true' : 'false' }}">
                                                @foreach ($related_products as $product)
                                                    <swiper-slide>
                                                        @include('templates.stores.modern-store.partials.product-card', ['class' => 'h-100'])
                                                    </swiper-slide>
                                                @endforeach
                                            </swiper-container>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @include('templates.stores.modern-store.includes.footer')
        </div>

        @include('templates.stores.includes.bottom-bar', ['color' => $storeTheme->color, 'bg' => $dark ? 'dark' : 'light'])
    </div>

    @include('templates.stores.modern-store.partials.cart-order-modal')
    @include('templates.stores.modern-store.includes.scripts', ['lightbox' => true])

    <script>
        // Show a gallery thumbnail in the main image box (called by the thumbnails)
        function showProductImage(thumb) {
            const main = document.getElementById('product_image');
            if (main) main.src = thumb.src;
        }
    </script>
</body>

</html>
