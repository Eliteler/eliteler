{{-- Pet store product page: gallery, options, add to cart and related products --}}
@php
    $productImages = array_values(array_filter(array_map('trim', explode(',', (string) $product_details->product_image))));
    $placeholder = asset('img/templates/pet-store/placeholder.svg');
    $firstImage = $productImages ? url($productImages[0]) : $placeholder;
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">

<head>
    @include('templates.stores.pet-store.includes.head')

    <style>
        .option-pill {
            display: inline-flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 2px;
            line-height: 1.2;
            color: inherit;
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

<body dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
    <div class="page page-center preloader-wrapper">
        <div class="container container-slim py-4">
            <div class="text-center">
                <div class="spinner-border text-warning" role="status"></div>
            </div>
        </div>
    </div>

    {{-- Page --}}
    <div id="wrapper" class="page m-0 min-h-screen" style="background-color: #FFEDD5;">
        <div class="page-wrapper m-0 p-0">
            <div class="page-body m-0 p-0">
                <div class="container-xl card rounded-0 p-0 overflow-hidden" style="min-height: 100vh;">
                    <!-- Navbar -->
                    @include('templates.stores.pet-store.includes.header', [
                        'bg_color' => 'warning',
                        'badge_color' => 'primary',
                    ])
                    <!-- Product Details -->
                    <div class="pt-6 px-3 px-lg-4 my-6">
                        <div class="mt-4">
                            <!-- Product Details -->
                            <div class="row">
                                <!-- Product Image -->
                                <div class="col-md-6">
                                    <a href="{{ $firstImage }}" data-fslightbox="gallery">
                                        <div class="ratio ratio-16x9 mb-2">
                                            <img src="{{ $firstImage }}" id="product_image" onerror="this.onerror=null;this.src='{{ $placeholder }}';"
                                                alt="{{ $product_details->product_name }}"
                                                style="object-fit: contain; object-position: center;"
                                                class="w-100 rounded-pet-store bg-opacity-gray">
                                        </div>
                                    </a>
                                    <swiper-container space-between="10" class="mySwiper" autoplay-delay="2500"
                                        breakpoints='{
                                        "320": { "slidesPerView": 2 },
                                        "640": { "slidesPerView": 3 }, 
                                        "1024": { "slidesPerView": 3 } 
                                    }'
                                        autoplay-disable-on-interaction="false" loop="true">
                                        @foreach ($productImages as $productImage)
                                            <swiper-slide>
                                                <a href="{{ url($productImage) }}" data-fslightbox="gallery">
                                                    <div class="product-image-wrapper">
                                                        <img class="img-fluid w-100 h-100 bg-opacity-gray rounded-pet-store"
                                                            style="object-fit: contain; object-position: center;"
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
                                    <h3 class="fs-custom fw-bold" id="product_name">{{ $product_details->product_name }}</h3>
                                    <p class="fs-3 text-muted">{{ $product_details->product_short_description }}</p>

                                    <div class="d-flex flex-column">
                                        <p class="text-muted fs-2 m-0" data-product-regular-price @if (!$productOptions->regularPrice) hidden @endif>
                                            <del>{{ $productOptions->regularPrice ? formatCurrencyCard($productOptions->regularPrice, $currency) : '' }}</del>
                                        </p>
                                        <p class="m-0 fs-1 fw-semibold text-warning" data-product-price>{{ formatCurrencyCard($productOptions->salesPrice, $currency) }}</p>
                                    </div>

                                    <div class="badge-container d-flex flex-wrap align-items-center gap-2 my-3">
                                        @if ($product_details->badge)
                                            <span class="badge">{{ $product_details->badge }}</span>
                                        @endif
                                        <span class="badge stock-badge" data-product-stock data-state="{{ $productOptions->canBuy ? 'in' : 'out' }}">
                                            <span data-product-stock-text>{{ $productOptions->canBuy ? __('In Stock') : __('Out of Stock') }}</span>
                                        </span>
                                    </div>

                                    @if ($productOptions->hasOptions)
                                        <div class="mb-3">
                                            <div class="fs-3 fw-semibold mb-2 text-dark">
                                                {{ __('Select Option') }}:
                                                <span class="text-warning" data-option-label>{{ $productOptions->canBuy ? $productOptions->initial->variant_name : '' }}</span>
                                            </div>
                                            <div class="d-flex flex-wrap gap-2">
                                                @include('templates.stores.shared.product-options', [
                                                    'buttonClass' => 'btn btn-outline-warning option-pill',
                                                    'activeClass' => 'active',
                                                    'priceClass' => 'option-pill-price',
                                                ])
                                            </div>
                                        </div>
                                    @endif

                                    <div class="d-flex flex-wrap align-items-center gap-3 mb-3">
                                        <div class="d-flex align-items-center border rounded-btn px-1 bg-white">
                                            <button type="button" class="btn btn-ghost-secondary p-0 border-0 d-flex align-items-center justify-content-center"
                                                style="width: 38px; height: 38px;" data-qty-step="-1" aria-label="{{ __('Decrease') }}"><i class="ti ti-minus m-0"></i></button>
                                            <span class="fs-3 fw-bold text-center text-dark" style="min-width: 34px;" data-qty-value>1</span>
                                            <button type="button" class="btn btn-ghost-secondary p-0 border-0 d-flex align-items-center justify-content-center"
                                                style="width: 38px; height: 38px;" data-qty-step="1" aria-label="{{ __('Increase') }}"><i class="ti ti-plus m-0"></i></button>
                                        </div>

                                        <button type="button" data-product-add @disabled(!$productOptions->canBuy)
                                            class="btn btn-warning btn-lg flex-grow-1 rounded-pet-store fs-2 fw-bold btn-effect d-flex align-items-center justify-content-center gap-2">
                                            <i class="ti ti-shopping-bag-plus fs-1"></i>
                                            {{ __('Add to Cart') }}
                                        </button>
                                    </div>

                                    <div class="fs-3">{!! $product_details->product_description !!}</div>

                                    {{-- Share --}}
                                    @include('templates.stores.includes.share', [
                                        'productName' => $product_details->product_name,
                                        'productUrl' => url()->current(),
                                    ])
                                </div>
                            </div>
                            <!-- Related Products Slider -->
                            <div class="row">
                                <div class="position-relative">
                                    <img class="position-absolute floater-img"
                                        src="{{ asset('img/templates/pet-store/1.png') }}"
                                        style="
                                            width: 90px;
                                            height: 90px;
                                            left: -20px;
                                            top: 10px;
                                            opacity: 0.4;
                                            rotate: 30deg;
                                            filter: blur(0.8px);
                                        " />
                                </div>
                                <h2 class="head py-5 text-center position-relative fs-custom fw-bold">
                                    {{ __('Related Products') }}
                                    <div class="position-absolute start-50 translate-middle-x bg-warning bottom-bar2">
                                    </div>
                                </h2>
                                <div class="col-md-12">
                                    <swiper-container space-between="10" class="mySwiper" autoplay-delay="2500"
                                        breakpoints='{
                                        "320": { "slidesPerView": 1 },
                                        "640": { "slidesPerView": 3 }, 
                                        "1024": { "slidesPerView": 4 } 
                                    }'
                                        autoplay-disable-on-interaction="false" loop="true">
                                        <!-- Foreach -->
                                        @foreach ($related_products as $product)
                                            <swiper-slide>
                                                @include('templates.stores.pet-store.partials.product-card', ['class' => 'h-100 m-1'])
                                            </swiper-slide>
                                        @endforeach
                                    </swiper-container>
                                </div>
                            </div>
                        </div>
                    </div>
                    @include('templates.stores.pet-store.includes.footer')
                </div>
            </div>
        </div>


        <!-- Bottom Navbar -->
        @include('templates.stores.includes.bottom-bar', ['color' => 'warning', 'bg' => 'light'])
        <!-- End Bottom Navbar -->
    </div>

    @include('templates.stores.pet-store.partials.cart-order-modal')
    @include('templates.stores.pet-store.includes.scripts', ['lightbox' => true])

    <script>
        // Show a gallery thumbnail in the main image box (called by the thumbnails)
        function showProductImage(thumb) {
            const main = document.getElementById('product_image');
            if (main) main.src = thumb.src;
        }
    </script>
</body>

</html>
