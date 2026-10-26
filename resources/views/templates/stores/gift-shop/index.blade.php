@php
    // Cover banners: a JSON list of images, or a single image path
    $banners = [];
    if (!empty($business_card_details->cover)) {
        $decodedCover = json_decode($business_card_details->cover, true);
        $banners = is_array($decodedCover) ? $decodedCover : [$business_card_details->cover];
    }

    $placeholder = asset('img/templates/gift-shop/placeholder.svg');
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">

<head>
    @include('templates.stores.gift-shop.includes.head')

    <style>
        /* Pill Buttons & UI Geometry */
        .btn-pill-action {
            border-radius: var(--store-radius-pill) !important;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }

        /* Swiper Hero Banner */
        .store-banner-wrapper {
            width: 100%;
            overflow: hidden;
            background: #fff;
            border-bottom: 1px solid var(--store-border);
        }

        .store-banner-slide {
            height: 380px;
            width: 100%;
        }

        .store-banner-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
        }

        @media (max-width: 768px) {
            .store-banner-slide {
                height: 220px;
            }
        }

        /* Luxury Heading Divider */
        .luxury-heading-container {
            text-align: center;
            padding: 2.2rem 1rem 1.25rem;
            position: relative;
        }

        .luxury-heading-title {
            font-size: 3rem;
            line-height: 1.1;
            color: var(--store-dark);
            margin-bottom: 0.5rem;
        }

        .heading-divider-wrap {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
            margin: 0 auto;
            max-width: 280px;
        }

        .heading-divider-icon {
            width: 32px;
            height: 32px;
            object-fit: contain;
        }

        /* Categories Slider */
        .category-slide-card {
            display: block;
            border-radius: var(--store-radius-card);
            overflow: hidden;
            position: relative;
            height: 150px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s ease;
            background: #ffffff;
        }

        .category-slide-card:hover {
            transform: translateY(-4px);
        }

        .category-slide-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .category-slide-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(180deg, rgba(0, 0, 0, 0.1) 0%, rgba(0, 0, 0, 0.7) 100%);
            display: flex;
            align-items: flex-end;
            justify-content: center;
            padding: 12px 8px;
        }

        .category-slide-name {
            color: #ffffff;
            font-size: 1.1rem;
            font-weight: 600;
            text-align: center;
            margin: 0;
        }

        /* 1-Row Unified Filter Toolbar with Min/Max Price Inputs */
        .store-filter-bar {
            background: #ffffff;
            border: 1.5px solid #e2e8f0;
            border-radius: var(--store-radius-pill);
            padding: 8px 10px 8px 18px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
            margin-bottom: 2rem;
        }

        .store-filter-inline-form {
            display: flex;
            align-items: center;
            gap: 10px;
            width: 100%;
            flex-wrap: wrap;
        }

        .filter-search-wrap {
            flex: 1 1 180px;
            min-width: 140px;
        }

        .store-filter-search-input {
            border: none;
            background: transparent;
            width: 100%;
            outline: none;
            font-size: 0.92rem;
            color: var(--store-text-main);
            font-weight: 500;
        }

        .filter-price-wrap {
            display: flex;
            align-items: center;
            gap: 6px;
            flex: 0 0 auto;
        }

        .store-filter-price-input {
            width: 80px;
            height: 38px;
            border: 1px solid #e2e8f0;
            border-radius: var(--store-radius-pill);
            padding: 4px 10px;
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--store-text-main);
            background: #f8fafc;
            text-align: center;
            outline: none;
            transition: all 0.2s ease;
        }

        .store-filter-price-input:focus {
            border-color: var(--store-primary);
            background: #ffffff;
            box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.12);
        }

        .price-sep-dash {
            color: #94a3b8;
            font-weight: bold;
            font-size: 0.85rem;
        }

        .filter-sort-wrap {
            flex: 0 0 160px;
        }

        .store-filter-sort-select {
            border: 1px solid #e2e8f0;
            border-radius: var(--store-radius-pill);
            padding: 6px 12px;
            font-size: 0.85rem;
            font-weight: 600;
            background-color: #f8fafc;
            color: var(--store-text-main);
            outline: none;
            width: 100%;
            height: 38px;
            cursor: pointer;
        }

        .store-filter-btn {
            border-radius: var(--store-radius-pill) !important;
            background: var(--store-dark);
            color: #ffffff;
            border: none;
            padding: 8px 18px;
            font-weight: 700;
            font-size: 0.88rem;
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            height: 38px;
        }

        .store-filter-btn:hover {
            background: var(--store-primary);
            box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3);
        }

        @media (max-width: 768px) {
            .store-filter-bar {
                border-radius: 24px;
                padding: 12px;
            }

            .filter-search-wrap {
                flex: 1 1 100%;
                border-bottom: 1px solid var(--store-border);
                padding-bottom: 8px;
            }

            .filter-price-wrap {
                flex: 1 1 100%;
                justify-content: space-between;
            }

            .store-filter-price-input {
                flex: 1;
            }

            .filter-sort-wrap {
                flex: 1 1 calc(60% - 10px);
            }

            .store-filter-btn {
                flex: 1 1 calc(40% - 10px);
            }
        }

        /* Dead-Centered Catalog Empty State */
        .store-empty-catalog {
            margin: auto 0;
            padding: 4rem 1rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            width: 100%;
        }

        .empty-icon-pod {
            width: 84px;
            height: 84px;
            border-radius: 50%;
            background-color: var(--store-primary-light);
            color: var(--store-primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            margin-bottom: 1.25rem;
        }
    </style>

    @include('templates.stores.shared.custom-code')
</head>

<body dir="{{ $isRtl ? 'rtl' : 'ltr' }}">

    {{-- Preloader Include --}}
    @include('templates.stores.gift-shop.includes.preloader')

    <div class="store-main-wrapper">
        {{-- Top Greeting Bar with Subtitle (Above Header) --}}
        @if (!empty($card_details->sub_title) || !empty($business_card_details->sub_title) || !empty($business_card_details->title2) || !empty($business_card_details->subtitle2))
            @php
                $storeSubTitle = $card_details->sub_title ?? $business_card_details->sub_title;
            @endphp
            <div class="store-top-greeting-bar">
                <i class="ti ti-sparkles greeting-sparkle-icon"></i>
                <span>@include('templates.stores.shared.subtitle-lines'){{ $storeSubTitle }}</span>
            </div>
        @endif

        {{-- Luxury Header Include --}}
        @include('templates.stores.gift-shop.includes.header')

        <div class="store-body-content-area">
            {{-- Swiper Hero Banners --}}
            @if (count($banners) > 0)
                <div class="store-banner-wrapper">
                    <swiper-container class="mySwiper" pagination="true" autoplay-delay="3500"
                        autoplay-disable-on-interaction="false" loop="true">
                        @foreach ($banners as $banner)
                            <swiper-slide class="store-banner-slide">
                                <img src="{{ url($banner) }}" alt="{{ $card_details->title }}"
                                    class="store-banner-img"
                                    onerror="this.onerror=null;this.src='{{ $placeholder }}';" />
                            </swiper-slide>
                        @endforeach
                    </swiper-container>
                </div>
            @endif

            <div class="container-xl px-3 px-lg-4 py-2">

                {{-- Categories Showcase Slider --}}
                @if (!request()->has('category') && count($categories) > 0)
                    <div class="luxury-heading-container">
                        <h2 class="luxury-heading-title font-carattere">{{ __('Categories') }}</h2>
                        <div class="heading-divider-wrap">
                            <div class="heading-divider-line"></div>
                            <img src="{{ asset('img/templates/gift-shop/heading.png') }}" class="heading-divider-icon"
                                alt="divider" onerror="this.style.display='none'" />
                            <div class="heading-divider-line right"></div>
                        </div>
                    </div>

                    <swiper-container space-between="15" autoplay-delay="3000"
                        breakpoints='{ "320": { "slidesPerView": 2.2 }, "640": { "slidesPerView": 3.5 }, "1024": { "slidesPerView": 5.2 } }'
                        autoplay-disable-on-interaction="false" loop="false">
                        @foreach ($categories as $category)
                            <swiper-slide>
                                <a href="{{ $storeUrl . '?category=' . urlencode(strtolower($category->category_name)) }}"
                                    class="category-slide-card">
                                    <img src="{{ url($category->thumbnail) }}" class="category-slide-img"
                                        alt="{{ $category->category_name }}"
                                        onerror="this.onerror=null;this.src='{{ $placeholder }}';" />
                                    <div class="category-slide-overlay">
                                        <h3 class="category-slide-name">{{ __($category->category_name) }}</h3>
                                    </div>
                                </a>
                            </swiper-slide>
                        @endforeach
                    </swiper-container>
                @endif

                {{-- Section Title & Divider --}}
                <div class="luxury-heading-container">
                    <h2 class="luxury-heading-title font-carattere">
                        @if (request()->has('category'))
                            {{ ucfirst(request('category')) }}
                        @elseif(request()->has('query'))
                            {{ __('Search Results') }}
                        @else
                            {{ __('Featured Gifts') }}
                        @endif
                    </h2>
                    <div class="heading-divider-wrap">
                        <div class="heading-divider-line"></div>
                        <img src="{{ asset('img/templates/gift-shop/heading.png') }}" class="heading-divider-icon"
                            alt="divider" onerror="this.style.display='none'" />
                        <div class="heading-divider-line right"></div>
                    </div>
                </div>

                {{-- 1-Row Unified Search, Min/Max Price & Sort Filter Toolbar --}}
                @if (request()->has('category') ||
                        request()->has('query') ||
                        request()->has('sort') ||
                        request()->has('min') ||
                        request()->has('max'))
                    <div class="store-filter-bar">
                        <form method="GET" action="{{ url()->current() }}" class="store-filter-inline-form">
                            {{-- Retain category query --}}
                            @if (request()->has('category'))
                                <input type="hidden" name="category" value="{{ request('category') }}">
                            @endif

                            {{-- Keyword Search Input --}}
                            <div class="filter-search-wrap">
                                <input type="text" name="query" class="store-filter-search-input"
                                    placeholder="{{ __('Search gifts...') }}" value="{{ request('query') }}" />
                            </div>

                            {{-- Min & Max Price Inputs --}}
                            <div class="filter-price-wrap">
                                <input type="number" name="min" class="store-filter-price-input"
                                    placeholder="{{ __('Min') }}" step="any" min="0"
                                    value="{{ request('min') }}" />
                                <span class="price-sep-dash">-</span>
                                <input type="number" name="max" class="store-filter-price-input"
                                    placeholder="{{ __('Max') }}" step="any" min="0"
                                    value="{{ request('max') }}" />
                            </div>

                            {{-- Sort Dropdown --}}
                            <div class="filter-sort-wrap">
                                <select name="sort" class="store-filter-sort-select"
                                    onchange="this.form.submit()">
                                    <option value="default" {{ request('sort') == 'default' ? 'selected' : '' }}>
                                        {{ __('Recently Added') }}</option>
                                    <option value="price_asc" {{ request('sort') == 'price_asc' ? 'selected' : '' }}>
                                        {{ __('Price: Low to High') }}</option>
                                    <option value="price_desc"
                                        {{ request('sort') == 'price_desc' ? 'selected' : '' }}>
                                        {{ __('Price: High to Low') }}</option>
                                    <option value="name_asc" {{ request('sort') == 'name_asc' ? 'selected' : '' }}>
                                        {{ __('Name: A to Z') }}</option>
                                    <option value="name_desc" {{ request('sort') == 'name_desc' ? 'selected' : '' }}>
                                        {{ __('Name: Z to A') }}</option>
                                </select>
                            </div>

                            {{-- Submit Filter Button --}}
                            <button type="submit" class="store-filter-btn">
                                <i class="ti ti-adjustments"></i>
                                <span>{{ __('Filter') }}</span>
                            </button>
                        </form>
                    </div>
                @endif

                {{-- Product 5-Baseline Grid Catalog --}}
                @if (count($products) > 0)
                    <div class="store-product-grid">
                        @foreach ($products as $product)
                            @include('templates.stores.gift-shop.partials.product-card')
                        @endforeach
                    </div>

                    {{-- Modular Pagination Partial Inclusion --}}
                    <div class="w-100 my-4">
                        @if (request()->has('category'))
                            {{ $products->appends(request()->except('page'))->links('templates.stores.gift-shop.partials.pagination') }}
                        @else
                            {{ $products->links('templates.stores.gift-shop.partials.pagination') }}
                        @endif
                    </div>
                @else
                    {{-- Dead-Centered Empty State --}}
                    <div class="store-empty-catalog">
                        <div class="empty-icon-pod">
                            <i class="ti ti-gift-off"></i>
                        </div>
                        <h3 class="fs-2 fw-bold text-dark mb-2">{{ __('No Gifts Found!') }}</h3>
                        <p class="text-muted mb-4">{{ __('We could not find any products matching your selection.') }}
                        </p>
                        <a href="{{ $storeUrl }}" class="btn btn-pill-action btn-dark px-4 py-2">
                            <i class="ti ti-arrow-left"></i>
                            <span>{{ __('View All Items') }}</span>
                        </a>
                    </div>
                @endif
            </div>
        </div>

        {{-- Luxury Footer Include --}}
        @include('templates.stores.gift-shop.includes.footer')
    </div>

    {{-- Bottom Navigation Bar --}}
    @include('templates.stores.gift-shop.includes.bottom-bar')

    {{-- PWA Bottom Sheet Modal --}}
    @include('templates.stores.gift-shop.includes.pwa-modal')

    {{-- VIP Promo & Newsletter Popups --}}
    @include('templates.stores.gift-shop.includes.popups')

    {{-- Interactive WhatsApp Floating Chat Pod --}}
    @include('templates.stores.gift-shop.includes.whatsapp-float')

    {{-- Cart drawer, checkout and the shared storefront script --}}
    @include('templates.stores.gift-shop.partials.cart-order-modal')
    @include('templates.stores.gift-shop.includes.scripts')
</body>

</html>
