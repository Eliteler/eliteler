<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    @include('templates.stores.cosmetics.includes.head')

    <style>
        /* Clean Replacement for Inline Styles */
        .store-bg-wrapper {
            background-color: #ffdcdc;
        }

        .store-main-card {
            min-height: 100vh;
        }

        .btn,
        .btn-rose,
        a[data-bs-toggle="offcanvas"] {
            border-radius: 10px !important;
            color: #ffffff;
            border: none;
        }

        /* Modern Variant Selector Pills (Resetting Default Button Styling) */
        .variant-pill {
            appearance: none !important;
            -webkit-appearance: none !important;
            background-color: #ffffff !important;
            border: 1.5px solid #e2e8f0 !important;
            color: #1e293b !important;
            border-radius: 10px !important;
            padding: 8px 16px !important;
            font-size: 13px !important;
            font-weight: 600 !important;
            line-height: 1.2 !important;
            cursor: pointer !important;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04) !important;
            transition: all 0.2s ease !important;
            outline: none !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
        }

        .variant-pill:hover {
            background-color: #fff1f4 !important;
            border-color: #f43f5e !important;
            color: #e91e63 !important;
        }

        /* Sold-out option: visible but can't be chosen */
        .variant-pill:disabled,
        .variant-pill.is-sold-out {
            opacity: 0.5 !important;
            cursor: not-allowed !important;
            text-decoration: line-through !important;
            background-color: #f8fafc !important;
            border-color: #e2e8f0 !important;
            color: #64748b !important;
            box-shadow: none !important;
        }

        /* Active Variant State */
        .variant-pill.active {
            background-color: #e91e63 !important;
            border-color: #e91e63 !important;
            color: #ffffff !important;
            box-shadow: 0 4px 12px rgba(233, 30, 99, 0.35) !important;
        }

        /* Decorative Floater Image */
        .related-floater-img {
            width: 120px;
            height: 120px;
            right: -30px;
            top: 0;
            opacity: 0.4;
            transform: rotate(-75deg);
        }



    

        [data-product-stock][data-state="in"] {
            background-color: #dcfce7;
            color: #166534;
        }

        [data-product-stock][data-state="out"] {
            background-color: #fee2e2;
            color: #991b1b;
        }
    </style>

    @include('templates.stores.shared.custom-code')
</head>

<body dir="{{ App::isLocale('ar') || App::isLocale('ur') || App::isLocale('he') ? 'rtl' : 'ltr' }}">

    <!-- Modular Preloader -->
    @include('templates.stores.cosmetics.includes.preloader')

    {{-- Page Wrapper --}}
    <div id="wrapper" class="page m-0 min-h-screen store-bg-wrapper">
        <div class="page-wrapper m-0 p-0">
            <div class="page-body m-0 p-0">
                <div class="container-xl card rounded-0 p-0 overflow-hidden store-main-card">

                    <!-- Store Navbar Header -->
                    @include('templates.stores.cosmetics.includes.header', [
                        'bg' => 'rose',
                        'bg_color' => 'rose',
                        'badge_color' => 'rose',
                    ])

                    <!-- Product Details Section -->
                    <div class="pt-6 px-3 px-lg-4 mt-6">
                        <div class="mt-4">
                            <!-- Product Details Row -->
                            <div class="row">

                                <!-- Left Column: Product Image Gallery -->
                                <div class="col-md-6 mb-4 mb-md-0">
                                    @php($productImages = explode(',', $product_details->product_image))

                                    <!-- Feature Image -->
                                    <a href="{{ url($productImages[0] ?? '') }}" data-fslightbox="gallery"
                                        id="lightbox_main_link">
                                        <div
                                            class="ratio ratio-16x9 mb-2 bg-white rounded-cosmetics border overflow-hidden">
                                            <img src="{{ url($productImages[0] ?? '') }}" id="product_image"
                                                alt="{{ $product_details->product_name }}"
                                                class="w-100 rounded-cosmetics object-fit-contain"
                                                onerror="this.onerror=null; this.src='{{ asset('img/templates/all-categories.png') }}';">
                                        </div>
                                    </a>

                                    <!-- Gallery Thumbnails -->
                                    @if (count($productImages) > 1)
                                        <swiper-container space-between="10" class="mySwiper" autoplay-delay="2500"
                                            breakpoints='{
                                                "320": { "slidesPerView": 2 },
                                                "640": { "slidesPerView": 3 }, 
                                                "1024": { "slidesPerView": 3 } 
                                            }'
                                            autoplay-disable-on-interaction="false" loop="false">
                                            @foreach ($productImages as $productImage)
                                                <swiper-slide>
                                                    <div class="product-image-wrapper ratio ratio-1x1 border rounded-cosmetics overflow-hidden bg-white cursor-pointer"
                                                        onclick="updateSrc(this)">
                                                        <img class="img-fluid w-100 h-100 object-fit-contain rounded-cosmetics"
                                                            alt="{{ $product_details->product_name }}"
                                                            src="{{ url($productImage) }}">
                                                    </div>
                                                </swiper-slide>
                                            @endforeach
                                        </swiper-container>
                                    @endif
                                </div>

                                {{-- Product information: price, options and the add button are run by the storefront script --}}
                                <div class="col-md-6 pt-4 p-lg-3" {{ $productOptions->attributes($product_details) }}>
                                    <h3 class="fs-custom fw-bold text-dark mb-1">{{ $product_details->product_name }}</h3>
                                    <p class="text-secondary fs-3 text-muted mb-3">{{ $product_details->product_short_description }}</p>

                                    <div class="d-flex flex-column mb-3">
                                        <del class="text-muted fs-2 m-0" data-product-regular-price @if (!$productOptions->regularPrice) hidden @endif>
                                            {{ $productOptions->regularPrice ? formatCurrencyCard($productOptions->regularPrice, $currency) : '' }}
                                        </del>
                                        <p class="m-0 fs-1 fw-bold text-dark" data-product-price>
                                            {{ formatCurrencyCard($productOptions->salesPrice, $currency) }}
                                        </p>
                                    </div>

                                    @if ($productOptions->hasOptions)
                                        <div class="my-3">
                                            <div class="d-flex align-items-center gap-2 mb-2">
                                                <span class="fw-semibold fs-3 text-dark">{{ __('Select Option / Variation') }}</span>
                                                <span class="text-rose fw-semibold fs-3" data-option-label>{{ $productOptions->canBuy ? $productOptions->initial->variant_name : '' }}</span>
                                            </div>
                                            <div class="d-flex flex-wrap gap-2">
                                                @include('templates.stores.shared.product-options', [
                                                    'buttonClass' => 'btn variant-pill rounded-cosmetics',
                                                    'priceClass' => 'ms-1 fs-5 opacity-75',
                                                ])
                                            </div>
                                        </div>
                                    @endif

                                    <div class="d-flex flex-wrap align-items-center gap-2 my-3">
                                        @if ($product_details->badge)
                                            <span class="badge bg-light text-rose border rounded-pill px-2 py-1">{{ $product_details->badge }}</span>
                                        @endif
                                        <span class="badge rounded-pill px-2 py-1" data-product-stock data-state="{{ $productOptions->canBuy ? 'in' : 'out' }}">
                                            {{ $productOptions->canBuy ? __('In Stock') : __('Out of Stock') }}
                                        </span>
                                    </div>

                                    @if ($product_details->product_status == 'instock')
                                        <div class="col-12 col-md-6 mb-3">
                                            <button type="button" data-product-add @disabled(!$productOptions->canBuy)
                                                class="btn btn-rose btn-lg w-100 rounded-cosmetics fs-2 fw-bold btn-effect border-0">
                                                <i class="ti ti-shopping-bag-plus fs-2 me-1"></i>
                                                <span>{{ __('Add to Cart') }}</span>
                                            </button>
                                        </div>
                                    @endif

                                    {{-- Full Description --}}
                                    @if (!empty($product_details->product_description))
                                        <div class="fs-3 my-3 text-secondary leading-relaxed">
                                            {!! $product_details->product_description !!}
                                        </div>
                                    @endif

                                    {{-- Share Component --}}
                                    @include('templates.stores.cosmetics.includes.share', [
                                        'productName' => $product_details->product_name,
                                        'productUrl' => url()->current(),
                                    ])
                                </div>
                            </div>

                            <!-- Related Products Slider Section -->
                            @if (count($related_products) > 0)
                                <div class="row mt-5 pt-4 border-top">
                                    <div class="position-relative">
                                        <img class="position-absolute floater-img related-floater-img"
                                            src="{{ asset('img/templates/cosmetics/6.png') }}" alt="Decor" />
                                    </div>
                                    <h2 class="head py-4 text-center position-relative fs-custom fw-bold">
                                        {{ __('Related Products') }}
                                        <div
                                            class="position-absolute start-50 translate-middle-x bg-rose bottom-bar-custom">
                                        </div>
                                    </h2>
                                    <div class="col-md-12">
                                        <swiper-container space-between="15" class="mySwiper" autoplay-delay="2500"
                                            breakpoints='{
                                                "320": { "slidesPerView": 1 },
                                                "640": { "slidesPerView": 3 }, 
                                                "1024": { "slidesPerView": 4 } 
                                            }'
                                            autoplay-disable-on-interaction="false" loop="false">
                                            @foreach ($related_products as $product)
                                                <swiper-slide>
                                                    @include('templates.stores.cosmetics.partials.product-card', ['class' => 'h-100 mb-3'])
                                                </swiper-slide>
                                            @endforeach
                                        </swiper-container>
                                    </div>
                                </div>
                            @endif

                        </div>
                    </div>

                    <!-- Footer Component Include -->
                    @include('templates.stores.cosmetics.includes.footer')
                </div>
            </div>
        </div>

        <!-- Bottom Navbar Include -->
        @include('templates.stores.cosmetics.includes.bottom-bar', [
            'color' => 'rose',
            'bg' => 'light',
            'bg_color' => 'rose',
        ])
    </div>

    @include('templates.stores.cosmetics.partials.cart-order-modal')

    @include('templates.stores.cosmetics.includes.whatsapp-float', [
        'businessImage' => $business_card_details->profile,
        'businessName' => $card_details->title,
        'whatsappNumber' => $enquiry_button,
    ])

    @include('templates.stores.cosmetics.includes.scripts', ['lightbox' => true])

    <script>
        // Gallery: show the clicked thumbnail as the main image (and in the lightbox)
        function updateSrc(element) {
            const image = element.querySelector('img') || element;
            document.getElementById('product_image').src = image.src;
            document.getElementById('lightbox_main_link').href = image.src;
            if (typeof refreshFsLightbox === 'function') {
                refreshFsLightbox();
            }
        }
    </script>
</body>

</html>
