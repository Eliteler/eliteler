{{-- Master Index View for Fashion Store --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">

<head>
    @include('templates.stores.fashion.includes.head')

    <style>
        /* Greeting Alert Banner */
        .fashion-greeting-banner {
            background-color: #111827;
            color: #ffffff;
            font-size: 15px;
            font-weight: 600;
            padding: 12px 20px;
            border-radius: 0px;
            margin-bottom: 24px;
        }

        /* Category Swiper Slide */
        .fashion-cat-slide {
            height: auto;
        }

        .fashion-category-card {
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            padding: 14px 10px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            height: 100%;
            transition: all 0.2s ease;
        }

        .fashion-category-card:hover,
        .fashion-category-card.active {
            border-color: #111827;
            background-color: #111827;
        }

        .fashion-category-card:hover span,
        .fashion-category-card.active span {
            color: #ffffff;
        }

        .fashion-category-thumb {
            width: 48px;
            height: 48px;
            object-fit: cover;
            margin-bottom: 8px;
        }

        .fashion-cat-name {
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            color: #111827;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            width: 100%;
        }

        /* Responsive CSS Grid for Products (2 on Mobile, 3 on Tablet, 4 on Desktop, 5 on Wide Desktop) */
        .fashion-products-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 14px;
            width: 100%;
        }

        @media (min-width: 768px) {
            .fashion-products-grid {
                grid-template-columns: repeat(3, 1fr);
                gap: 16px;
            }
        }

        @media (min-width: 992px) {
            .fashion-products-grid {
                grid-template-columns: repeat(4, 1fr);
                gap: 18px;
            }
        }

        @media (min-width: 1200px) {
            .fashion-products-grid {
                grid-template-columns: repeat(5, 1fr);
                gap: 20px;
            }
        }

        .fashion-filter-input {
            height: 42px;
            border-radius: 0px;
            border: 1px solid #cbd5e1;
        }
    </style>

    @include('templates.stores.shared.custom-code')
</head>

<body dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
    {{-- 1. Preloader --}}
    @include('templates.stores.fashion.includes.preloader')

    {{-- 2. Header --}}
    @include('templates.stores.fashion.includes.header')

    <main class="container-xl py-4">
        {{-- Greeting Message: Placed Below Header --}}
        @if (!empty($business_card_details->sub_title) || !empty($business_card_details->title2) || !empty($business_card_details->subtitle2))
            <div class="fashion-greeting-banner">
                @include('templates.stores.shared.subtitle-lines')
                {{ $business_card_details->sub_title }}
            </div>
        @endif

        {{-- Banners Slider (Auto-hidden when category filter is active) --}}
        @if (!request()->has('category'))
            @if (!empty($business_card_details->cover))
                <div class="mb-5">
                    <swiper-container space-between="20" class="mySwiper" autoplay-delay="3000" pagination="true"
                        loop="true">
                        @php
                            $covers = json_decode($business_card_details->cover);
                            $coverList = is_array($covers) ? $covers : [$business_card_details->cover];
                        @endphp
                        @foreach ($coverList as $cImg)
                            <swiper-slide>
                                <img src="{{ url($cImg) }}" class="w-100 object-fit-cover" style="max-height: 380px;"
                                    alt="{{ $card_details->title }}">
                            </swiper-slide>
                        @endforeach
                    </swiper-container>
                </div>
            @endif
        @endif

        {{-- Categories Slider --}}
        <section id="storeCategoriesSection" class="mb-5">
            <h2 class="head fs-1 mb-3 text-start">{{ __('Categories') }}</h2>
            <swiper-container space-between="12" class="mySwiperCat"
                breakpoints='{
                "320": { "slidesPerView": 3.2 },
                "640": { "slidesPerView": 4.5 },
                "768": { "slidesPerView": 6 },
                "1024": { "slidesPerView": 7.5 }
            }'>
                <!-- All Products Category -->
                <swiper-slide class="fashion-cat-slide">
                    <a href="{{ $storeUrl }}"
                        class="fashion-category-card {{ !request('category') ? 'active' : '' }}">
                        <img src="{{ asset('img/templates/all-categories.png') }}" class="fashion-category-thumb"
                            alt="All">
                        <span class="fashion-cat-name">{{ __('All') }}</span>
                    </a>
                </swiper-slide>

                <!-- Dynamic Categories -->
                @foreach ($categories as $cat)
                    @php $catSlug = urlencode(strtolower($cat->category_name)); @endphp
                    <swiper-slide class="fashion-cat-slide">
                        <a href="{{ $storeUrl . '?category=' . $catSlug }}"
                            class="fashion-category-card {{ request('category') == $catSlug ? 'active' : '' }}">
                            <img src="{{ url($cat->thumbnail) }}" class="fashion-category-thumb"
                                alt="{{ $cat->category_name }}">
                            <span class="fashion-cat-name">{{ $cat->category_name }}</span>
                        </a>
                    </swiper-slide>
                @endforeach
            </swiper-container>
        </section>

        {{-- Product Catalog Grid (CSS Grid 4-5 per row on desktop) --}}
        <section id="storeProductsSection">
            <div class="row align-items-center mb-4 gy-3">
                <div class="col-md-6">
                    <h2 class="head fs-1 mb-0">{{ __('Our Products') }}</h2>
                </div>
                <div class="col-md-6">
                    <form method="GET" action="{{ url()->current() }}" class="row g-2 justify-content-md-end">
                        @if (request('category'))
                            <input type="hidden" name="category" value="{{ request('category') }}">
                        @endif
                        <div class="col-7 col-sm-6">
                            <input type="text" name="query" value="{{ request('query') }}"
                                placeholder="{{ __('Search products...') }}" class="form-control fashion-filter-input">
                        </div>
                        <div class="col-5 col-sm-4">
                            <button type="submit"
                                class="btn btn-dark w-100 fashion-filter-input">{{ __('Search') }}</button>
                        </div>
                    </form>
                </div>
            </div>

            @if (count($products) > 0)
                <div class="fashion-products-grid">
                    @foreach ($products as $product)
                        @include('templates.stores.fashion.partials.product-card')
                    @endforeach
                </div>

                {{-- Pagination Component --}}
                <div class="mt-5">
                    @if (request()->has('category'))
                        {{ $products->appends(request()->except('page'))->links('templates.stores.fashion.partials.pagination') }}
                    @else
                        {{ $products->links('templates.stores.fashion.partials.pagination') }}
                    @endif
                </div>
            @else
                <div class="text-center py-5">
                    <h3 class="head fs-1 text-muted">{{ __('No Products Found!') }}</h3>
                    <a href="{{ $storeUrl }}" class="btn btn-dark mt-3">{{ __('View All Products') }}</a>
                </div>
            @endif
        </section>
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
</body>

</html>
