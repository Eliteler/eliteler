{{-- GoBiz Luxury Grocery Store Engine with Standard Clean Architecture --}}
@php
    // Cover banners: a JSON list of images, or a single image path
    $banners = [];
    if (!empty($business_card_details->cover)) {
        $decodedCover = json_decode($business_card_details->cover, true);
        $banners = is_array($decodedCover) ? $decodedCover : [$business_card_details->cover];
    }

    $placeholder = asset('img/templates/grocery/placeholder.svg');
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">

<head>
    @include('templates.stores.grocery.includes.head')

    <style>
        /* Subtitle Announcement Greeting Banner (Below Header, Above Banners) */
        .store-subtitle-banner-section {
            max-width: 1240px;
            margin: 18px auto 0 auto;
            padding: 0 20px;
            width: 100%;
        }

        .store-subtitle-pill-card {
            background-color: var(--store-primary-light);
            border: 1px solid var(--store-border);
            border-radius: 20px !important;
            padding: 14px 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            box-shadow: 0 2px 8px rgba(46, 125, 50, 0.05);
        }

        .store-subtitle-icon-pod {
            width: 36px;
            height: 36px;
            border-radius: 50% !important;
            background-color: var(--store-primary);
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            flex-shrink: 0;
        }

        .store-subtitle-text {
            font-size: 14px;
            font-weight: 600;
            color: var(--store-primary-hover);
            margin: 0;
            line-height: 1.5;
        }

        /* Swiper Hero Banner */
        .store-banner-wrapper {
            max-width: 1240px;
            margin: 16px auto 0 auto;
            padding: 0 20px;
            width: 100%;
        }

        .store-banner-slide {
            height: 320px;
            width: 100%;
            border-radius: var(--store-radius-card);
            overflow: hidden;
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

        /* Section Headings */
        .store-section-title-wrap {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 16px;
            padding: 0 4px;
        }

        .store-section-heading {
            font-size: 20px;
            font-weight: 800;
            color: var(--store-primary-hover);
            margin: 0;
        }

        .store-view-all-pill-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 18px !important;
            border-radius: var(--store-radius-pill) !important;
            background-color: var(--store-primary-light);
            color: var(--store-primary);
            border: 1px solid var(--store-border);
            font-size: 12px;
            font-weight: 700;
            transition: all 0.2s ease;
        }

        .store-view-all-pill-btn:hover {
            background-color: var(--store-primary);
            color: #ffffff !important;
        }

        [dir="rtl"] .store-view-all-pill-btn i {
            transform: rotate(180deg);
        }

        /* Categories Slider */
        .store-categories-section {
            max-width: 1240px;
            margin: 25px auto 0 auto;
            padding: 0 20px;
            width: 100%;
        }

        .store-cat-pill-card {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            background: #ffffff;
            border: 1px solid var(--store-border);
            border-radius: var(--store-radius-pill) !important;
            padding: 6px 18px 6px 6px;
            transition: all 0.2s ease;
        }

        [dir="rtl"] .store-cat-pill-card {
            padding: 6px 6px 6px 18px !important;
        }

        .store-cat-pill-card:hover {
            background-color: var(--store-primary-light);
            transform: translateY(-2px);
        }

        .store-cat-thumb-round {
            width: 48px;
            height: 48px;
            border-radius: 50% !important;
            overflow: hidden;
            background-color: #f1f8e9;
            flex-shrink: 0;
        }

        .store-cat-thumb-round img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .store-cat-name {
            font-size: 13px;
            font-weight: 700;
            color: var(--store-text-main);
            margin: 0;
            white-space: nowrap;
        }

        /* 1-Row Unified Filter Toolbar */
        .store-filter-bar {
            background: #ffffff;
            border: 1px solid var(--store-border);
            border-radius: var(--store-radius-pill);
            padding: 8px 12px;
            box-shadow: 0 2px 8px rgba(46, 125, 50, 0.05);
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
            padding: 0 10px;
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
            border: 1px solid var(--store-border);
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
        }

        .filter-sort-wrap {
            flex: 0 0 160px;
        }

        .store-filter-sort-select {
            border: 1px solid var(--store-border);
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
            background: var(--store-primary);
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
            background: var(--store-primary-hover);
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

        /* Responsive Catalog Grid */
        .store-catalog-section {
            max-width: 1240px;
            margin: 30px auto 40px auto;
            padding: 0 20px;
            width: 100%;
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

        .store-empty-btn {
            background: var(--store-primary);
            color: #ffffff !important;
            border-radius: var(--store-radius-pill) !important;
            font-size: 0.82rem;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .store-empty-btn:hover {
            background: var(--store-primary-hover);
        }
    </style>

    @include('templates.stores.shared.custom-code')
</head>

<body dir="{{ $isRtl ? 'rtl' : 'ltr' }}">

    {{-- Preloader Include --}}
    @include('templates.stores.grocery.includes.preloader')

    <div class="store-main-wrapper">
        {{-- Luxury Header Include --}}
        @include('templates.stores.grocery.includes.header')

        <div class="store-body-content-area">
            {{-- Top Greeting Subtitle Banner (Below Header, Above Banners) --}}
            @if (!empty($card_details->sub_title) || !empty($business_card_details->sub_title))
                @php
                    $storeSubTitle = $card_details->sub_title ?? $business_card_details->sub_title;
                @endphp
                <div class="store-subtitle-banner-section">
                    <div class="store-subtitle-pill-card">
                        <div class="store-subtitle-icon-pod">
                            <i class="ti ti-bell-ringing"></i>
                        </div>
                        <p class="store-subtitle-text">{{ $storeSubTitle }}</p>
                    </div>
                </div>
            @endif

            {{-- Swiper Hero Banners --}}
            @if (count($banners) > 0)
                <div class="store-banner-wrapper">
                    <swiper-container class="mySwiper" pagination="true" autoplay-delay="3500"
                        autoplay-disable-on-interaction="false" dir="{{ $isRtl ? 'rtl' : 'ltr' }}"
                        @if (count($banners) > 1) loop="true" @endif>
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
                    <div class="store-categories-section">
                        <div class="store-section-title-wrap">
                            <h2 class="store-section-heading">{{ __('Categories') }}</h2>
                            <a href="{{ $storefront->categoriesUrl }}"
                                class="store-view-all-pill-btn">
                                <span>{{ __('View All') }}</span>
                                <i class="ti ti-chevron-right"></i>
                            </a>
                        </div>
                        <swiper-container space-between="15" autoplay-delay="3000" dir="{{ $isRtl ? 'rtl' : 'ltr' }}"
                            slides-per-view="auto" free-mode="true" autoplay-disable-on-interaction="false">
                            @foreach ($categories as $category)
                                <swiper-slide class="w-auto">
                                    <a href="{{ $storeUrl . '?category=' . urlencode(strtolower($category->category_name)) }}"
                                        class="store-cat-pill-card">
                                        <div class="store-cat-thumb-round">
                                            <img src="{{ url($category->thumbnail) }}"
                                                alt="{{ $category->category_name }}"
                                                onerror="this.onerror=null;this.src='{{ $placeholder }}';" />
                                        </div>
                                        <span class="store-cat-name">{{ __($category->category_name) }}</span>
                                    </a>
                                </swiper-slide>
                            @endforeach
                        </swiper-container>
                    </div>
                @endif

                {{-- Section Title --}}
                <div class="store-section-title-wrap mt-4">
                    <h2 class="store-section-heading">
                        @if (request()->has('category'))
                            {{ ucfirst(request('category')) }}
                        @elseif(request()->has('query'))
                            {{ __('Search Results') }}
                        @else
                            {{ __('All Products') }}
                        @endif
                    </h2>
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
                                    placeholder="{{ __('Search products...') }}" value="{{ request('query') }}" />
                            </div>

                            {{-- Min & Max Price Inputs --}}
                            <div class="filter-price-wrap">
                                <input type="number" name="min" class="store-filter-price-input"
                                    placeholder="{{ __('Min') }}" step="any" min="0"
                                    value="{{ request('min') }}" />
                                <span>-</span>
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
                            @include('templates.stores.grocery.partials.product-card')
                        @endforeach
                    </div>

                    {{-- Modular Pagination Partial Inclusion --}}
                    <div class="w-100 my-4">
                        @if (request()->has('category'))
                            {{ $products->appends(request()->except('page'))->links('templates.stores.grocery.partials.pagination') }}
                        @else
                            {{ $products->links('templates.stores.grocery.partials.pagination') }}
                        @endif
                    </div>
                @else
                    {{-- Dead-Centered Empty State --}}
                    <div class="store-empty-catalog">
                        <div class="empty-icon-pod">
                            <i class="ti ti-basket-off"></i>
                        </div>
                        <h3 class="fs-2 fw-bold text-dark mb-2">{{ __('No Products Found!') }}</h3>
                        <p class="text-muted mb-4">{{ __('We could not find any products matching your selection.') }}
                        </p>
                        <a href="{{ $storeUrl }}" class="btn store-empty-btn px-4 py-2">
                            <i class="ti ti-arrow-left"></i>
                            <span>{{ __('View All Items') }}</span>
                        </a>
                    </div>
                @endif
            </div>
        </div>

        {{-- Luxury Footer Include --}}
        @include('templates.stores.grocery.includes.footer')
    </div>

    {{-- Bottom Navigation Bar --}}
    @include('templates.stores.grocery.includes.bottom-bar')

    {{-- PWA Bottom Sheet Modal --}}
    @include('templates.stores.grocery.includes.pwa-modal')

    {{-- VIP Promo & Newsletter Popups --}}
    @include('templates.stores.grocery.includes.popups')

    {{-- Interactive WhatsApp Floating Chat Pod --}}
    @include('templates.stores.grocery.includes.whatsapp-float')

    {{-- Cart drawer, checkout and the shared storefront script --}}
    @include('templates.stores.grocery.partials.cart-order-modal')
    @include('templates.stores.grocery.includes.scripts')
</body>

</html>
