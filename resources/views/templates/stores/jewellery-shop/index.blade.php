{{-- GoBiz Luxury Jewellery Store Engine with Conditional Category Hero & Greetings View --}}
@php
    // Banner images (one or many)
    $banners = json_decode($business_card_details->cover ?? '', true);
    $banners = is_array($banners) ? $banners : array_filter([$business_card_details->cover]);

    $placeholder = asset('img/templates/jewellery-shop/placeholder.svg');
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">

<head>
    @include('templates.stores.jewellery-shop.includes.head')

    <style>

        /* Direct Modal Background Styling */
        .modal {
            background-color: rgba(15, 23, 42, 0.75) !important;
        }

        .modal-backdrop {
            display: none !important;
        }

        /* Direct blur behind modals */
        body.modal-open .store-main-wrapper,
        body.modal-open .luxury-bottom-bar,
        body.modal-open .luxury-wa-floater-root {
            filter: blur(6px) !important;
            -webkit-filter: blur(6px) !important;
            transition: filter 0.25s ease-in-out;
            pointer-events: none;
        }

        .modal-dialog {
            z-index: 1060 !important;
            position: relative !important;
        }

        .modal-content {
            background-color: #ffffff !important;
            box-shadow: 0 25px 65px rgba(0, 0, 0, 0.6) !important;
        }

        /* Unified Container Engine */
        .store-unified-content-box {
            max-width: 1240px;
            margin: 0 auto;
            padding: 0 20px;
            width: 100%;
        }

        /* Subtitle Announcement Greeting Banner */
        .store-subtitle-banner-section {
            margin: 18px 0 0 0;
            width: 100%;
        }

        .store-subtitle-pill-card {
            background-color: var(--store-primary-light);
            border: 1px solid var(--store-border);
            border-radius: var(--store-radius-pill) !important;
            padding: 12px 24px;
            display: flex;
            align-items: center;
            gap: 14px;
            box-shadow: 0 4px 15px rgba(212, 175, 55, 0.08);
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
            font-family: 'Gloock', serif;
            font-size: 15px;
            font-weight: 500;
            color: var(--store-dark);
            margin: 0;
            line-height: 1.5;
            letter-spacing: 0.3px;
        }

        /* Swiper Hero Banner */
        .store-banner-wrapper {
            margin: 16px 0 0 0;
            width: 100%;
        }

        .store-banner-slide {
            height: 340px;
            width: 100%;
            border-radius: var(--store-radius-card);
            overflow: hidden;
            box-shadow: 0 8px 25px rgba(212, 175, 55, 0.1);
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
            margin: 30px 0 16px 0;
            padding: 0;
            width: 100%;
        }

        .store-section-heading {
            font-family: 'Gloock', serif;
            font-size: 22px;
            font-weight: 700;
            color: var(--store-dark);
            margin: 0;
        }

        .store-view-all-pill-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 18px !important;
            border-radius: var(--store-radius-pill) !important;
            background-color: var(--store-primary-light);
            color: var(--store-primary-dark);
            border: 1px solid var(--store-border);
            font-size: 12px;
            font-weight: 700;
            transition: all 0.2s ease;
        }

        .store-view-all-pill-btn:hover {
            background-color: var(--store-primary);
            color: #ffffff !important;
            border-color: var(--store-primary);
        }

        /* Categories Section */
        .store-categories-section {
            width: 100%;
            margin-bottom: 10px;
        }

        .luxury-cat-card {
            background: var(--store-card-bg);
            border: 1px solid var(--store-border);
            border-radius: 20px !important;
            padding: 14px 10px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            gap: 10px;
            transition: all 0.25s ease;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.02);
            height: 100%;
            min-height: 135px;
        }

        .luxury-cat-card:hover {
            transform: translateY(-4px);
            border-color: var(--store-primary);
            box-shadow: 0 10px 25px rgba(212, 175, 55, 0.15);
            background-color: #ffffff;
        }

        .luxury-cat-thumb-box {
            width: 64px;
            height: 64px;
            border-radius: 50% !important;
            overflow: hidden;
            background-color: var(--store-primary-light);
            border: 1.5px solid var(--store-border);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .luxury-cat-thumb-box img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .luxury-cat-name {
            font-size: 13px;
            font-weight: 700;
            color: var(--store-text-main);
            margin: 0;
            line-height: 1.25;
            max-width: 100%;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        /* 1-Row Unified Filter Toolbar */
        .store-filter-bar {
            background: #ffffff;
            border: 1px solid var(--store-border);
            border-radius: var(--store-radius-pill);
            padding: 8px 12px;
            box-shadow: 0 4px 15px rgba(212, 175, 55, 0.05);
            margin-bottom: 2rem;
            width: 100%;
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
            background: #fffdfa;
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
            background-color: #fffdfa;
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
            padding: 8px 20px;
            font-weight: 700;
            font-size: 0.88rem;
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
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

        /* Dead-Centered Catalog Empty State & Compact Pill Button */
        .store-empty-catalog {
            margin: auto 0;
            padding: 5rem 1rem;
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
            border-radius: 50% !important;
            background-color: var(--store-primary-light);
            color: var(--store-primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            margin-bottom: 1.25rem;
            border: 1px solid var(--store-border);
        }

        .empty-catalog-title {
            font-family: 'Gloock', serif;
            font-size: 24px;
            font-weight: 700;
            color: var(--store-dark);
            margin: 0 0 8px 0;
        }

        .empty-catalog-desc {
            font-size: 14px;
            color: var(--store-text-muted);
            margin: 0 0 24px 0;
            max-width: 420px;
        }

        /* Compact Luxury Pill Button (Fixed Width Issue) */
        .btn-empty-view-all {
            width: auto !important;
            /* Fixes the massive stretched width */
            height: 46px;
            padding: 0 28px !important;
            border-radius: var(--store-radius-pill) !important;
            background: var(--store-primary) !important;
            color: #ffffff !important;
            font-size: 14px;
            font-weight: 700;
            border: none;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 8px;
            cursor: pointer;
            transition: all 0.25s ease;
            box-shadow: 0 4px 15px rgba(212, 175, 55, 0.3);
            text-decoration: none !important;
        }

        .btn-empty-view-all:hover {
            background: var(--store-primary-hover) !important;
            box-shadow: 0 6px 20px rgba(212, 175, 55, 0.45);
            transform: translateY(-2px);
            color: #ffffff !important;
        }

        [dir="rtl"] .btn-empty-view-all i {
            transform: rotate(180deg);
        }
    </style>

    @include('templates.stores.shared.custom-code')
</head>

<body dir="{{ $isRtl ? 'rtl' : 'ltr' }}">

    {{-- Preloader Include --}}
    @include('templates.stores.jewellery-shop.includes.preloader')

    <div class="store-main-wrapper">
        {{-- Luxury Header Include --}}
        @include('templates.stores.jewellery-shop.includes.header')

        <div class="store-body-content-area">
            {{-- Single Unified Master Container --}}
            <div class="store-unified-content-box">

                {{-- Top Greeting Subtitle Banner (Strictly hidden when category filter is active) --}}
                @if (!request()->has('category') && (!empty($card_details->sub_title) || !empty($business_card_details->sub_title) || !empty($business_card_details->title2) || !empty($business_card_details->subtitle2)))
                    @php
                        $storeSubTitle = $card_details->sub_title ?? $business_card_details->sub_title;
                    @endphp
                    <div class="store-subtitle-banner-section">
                        <div class="store-subtitle-pill-card">
                            <div class="store-subtitle-icon-pod">
                                <i class="ti ti-sparkles"></i>
                            </div>
                            <p class="store-subtitle-text">@include('templates.stores.shared.subtitle-lines'){{ $storeSubTitle }}</p>
                        </div>
                    </div>
                @endif

                {{-- Swiper Hero Banners (Strictly hidden when category filter is active) --}}
                @if (!request()->has('category') && count($banners) > 0)
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

                {{-- Categories Showcase Slider (Strictly hidden when category filter is active) --}}
                @if (!request()->has('category') && count($categories) > 0)
                    <div class="store-section-title-wrap">
                        <h2 class="store-section-heading">{{ __('Categories') }}</h2>
                        <a href="{{ url($business_card_details->card_url . '/categories') }}"
                            class="store-view-all-pill-btn">
                            <span>{{ __('View All') }}</span>
                            <i class="ti ti-chevron-right"></i>
                        </a>
                    </div>

                    <div class="store-categories-section">
                        <swiper-container space-between="16" autoplay-delay="3000" dir="{{ $isRtl ? 'rtl' : 'ltr' }}"
                            breakpoints='{"320": {"slidesPerView": 3.2}, "640": {"slidesPerView": 4.5}, "768": {"slidesPerView": 5.5}, "1024": {"slidesPerView": 7}}'
                            free-mode="true" autoplay-disable-on-interaction="false">
                            @foreach ($categories as $category)
                                <swiper-slide>
                                    <a
                                        href="{{ $storeUrl . '?category=' . urlencode(strtolower($category->category_name)) }}">
                                        <div class="luxury-cat-card">
                                            <div class="luxury-cat-thumb-box">
                                                <img src="{{ url($category->thumbnail) }}"
                                                    alt="{{ $category->category_name }}"
                                                    onerror="this.onerror=null;this.src='{{ $placeholder }}';" />
                                            </div>
                                            <span class="luxury-cat-name">{{ __($category->category_name) }}</span>
                                        </div>
                                    </a>
                                </swiper-slide>
                            @endforeach
                        </swiper-container>
                    </div>
                @endif

                {{-- Products Section Title --}}
                <div class="store-section-title-wrap">
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

                {{-- 1-Row Unified Search & Filter Toolbar --}}
                @if (request()->has('category') ||
                        request()->has('query') ||
                        request()->has('sort') ||
                        request()->has('min') ||
                        request()->has('max'))
                    <div class="store-filter-bar">
                        <form method="GET" action="{{ url()->current() }}" class="store-filter-inline-form">
                            @if (request()->has('category'))
                                <input type="hidden" name="category" value="{{ request('category') }}">
                            @endif

                            <div class="filter-search-wrap">
                                <input type="text" name="query" class="store-filter-search-input"
                                    placeholder="{{ __('Search jewellery...') }}" value="{{ request('query') }}" />
                            </div>

                            <div class="filter-price-wrap">
                                <input type="number" name="min" class="store-filter-price-input"
                                    placeholder="{{ __('Min') }}" step="any" min="0"
                                    value="{{ request('min') }}" />
                                <span>-</span>
                                <input type="number" name="max" class="store-filter-price-input"
                                    placeholder="{{ __('Max') }}" step="any" min="0"
                                    value="{{ request('max') }}" />
                            </div>

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
                            @include('templates.stores.jewellery-shop.partials.product-card')
                        @endforeach
                    </div>

                    {{-- Modular Pagination Partial Inclusion --}}
                    <div class="w-100 my-4">
                        @if (request()->has('category'))
                            {{ $products->appends(request()->except('page'))->links('templates.stores.jewellery-shop.partials.pagination') }}
                        @else
                            {{ $products->links('templates.stores.jewellery-shop.partials.pagination') }}
                        @endif
                    </div>
                @else
                    {{-- Dead-Centered Empty State --}}
                    <div class="store-empty-catalog">
                        <div class="empty-icon-pod">
                            <i class="ti ti-diamond"></i>
                        </div>
                        <h3 class="empty-catalog-title">{{ __('No Products Found!') }}</h3>
                        <p class="empty-catalog-desc">
                            {{ __('We could not find any products matching your selection.') }}</p>
                        <a href="{{ $storeUrl }}" class="btn-empty-view-all">
                            <i class="ti ti-arrow-left"></i>
                            <span>{{ __('View All Items') }}</span>
                        </a>
                    </div>
                @endif
            </div>
        </div>

        {{-- Luxury Footer Include --}}
        @include('templates.stores.jewellery-shop.includes.footer')
    </div>

    {{-- Bottom Navigation Bar --}}
    @include('templates.stores.jewellery-shop.includes.bottom-bar')

    {{-- PWA Bottom Sheet Modal --}}
    @include('templates.stores.jewellery-shop.includes.pwa-modal')

    {{-- VIP Promo & Newsletter Popups --}}
    @include('templates.stores.jewellery-shop.includes.popups')

    {{-- Interactive WhatsApp Floating Chat Pod --}}
    @include('templates.stores.jewellery-shop.includes.whatsapp-float')

    @include('templates.stores.jewellery-shop.partials.cart-order-modal')
    @include('templates.stores.jewellery-shop.includes.scripts')
</body>

</html>
