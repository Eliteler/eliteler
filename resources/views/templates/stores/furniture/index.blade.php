@php
    $placeholder = asset('img/templates/furniture/placeholder.svg');
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">

<head>
    @include('templates.stores.furniture.includes.head')

    <style>
        .store-catalog-inner-wrap {
            flex: 1 0 auto;
            display: flex;
            flex-direction: column;
            width: 100%;
        }

        /* Section Headings */
        .categories-heading-wrap {
            text-align: center;
            margin-top: 12px;
            margin-bottom: 20px;
        }

        .products-heading-wrap {
            text-align: center;
            margin-top: 28px;
            margin-bottom: 22px;
        }

        /* Pill Section Badges */
        .luxury-section-pill {
            background-color: var(--color-camel);
            color: #ffffff;
            font-family: 'DM Serif Display', serif;
            font-size: 20px;
            padding: 7px 32px;
            border-radius: 50px !important;
            display: inline-block;
            letter-spacing: 0.5px;
        }

        /* 1-Row Unified Inline Search & Filter Toolbar */
        .store-filter-bar {
            background-color: #ffffff;
            border: 1.5px solid var(--color-card-border);
            border-radius: 50px !important;
            padding: 5px 6px 5px 14px;
            margin: 10px 0 24px 0;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03);
        }

        .store-filter-inline-form {
            display: flex;
            align-items: center;
            gap: 6px;
            width: 100%;
        }

        .filter-input-wrap {
            position: relative;
            flex: 1 1 auto;
            min-width: 0;
        }

        .filter-search-icon {
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 16px;
            pointer-events: none;
        }

        .store-filter-search-input {
            width: 100%;
            height: 38px;
            border: none;
            outline: none;
            background: transparent;
            padding-left: 22px;
            padding-right: 6px;
            font-size: 13px;
            color: var(--color-text-main);
        }

        .store-filter-search-input::placeholder {
            color: #94a3b8;
        }

        .filter-sort-wrap {
            flex: 0 0 auto;
            max-width: 130px;
        }

        @media (min-width: 576px) {
            .filter-sort-wrap {
                max-width: 160px;
            }
        }

        .store-filter-sort-select {
            height: 38px;
            border-radius: 50px !important;
            border: 1px solid #e2e8f0;
            background-color: #f8fafc;
            font-size: 12px;
            font-weight: 600;
            color: var(--color-text-main);
            padding: 0 24px 0 10px;
            cursor: pointer;
            width: 100%;
            outline: none;
            text-overflow: ellipsis;
        }

        .store-filter-btn {
            height: 38px;
            padding: 0 16px;
            border-radius: 50px !important;
            background-color: var(--color-forest-teal);
            color: #ffffff !important;
            font-weight: 700;
            font-size: 13px;
            border: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 4px;
            flex-shrink: 0;
            transition: background-color 0.2s ease;
        }

        .store-filter-btn:hover {
            background-color: var(--color-forest-teal-dark);
            color: #ffffff !important;
        }

        /* Catalog Product Grid */
        .store-product-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 16px;
        }

        @media (min-width: 768px) {
            .store-product-grid {
                grid-template-columns: repeat(3, 1fr);
                gap: 20px;
            }
        }

        @media (min-width: 992px) {
            .store-product-grid {
                grid-template-columns: repeat(4, 1fr);
                gap: 24px;
            }
        }

        @media (min-width: 1200px) {
            .store-product-grid {
                grid-template-columns: repeat(5, 1fr);
                gap: 24px;
            }
        }

        /* Dead-Center Empty State */
        .store-empty-catalog-state {
            flex: 1 0 auto;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 40px 20px 60px 20px;
            margin: auto 0;
            width: 100%;
        }

        .empty-icon-pod {
            width: 84px;
            height: 84px;
            border-radius: 50% !important;
            background-color: var(--color-camel-light);
            color: var(--color-camel);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 18px;
            box-shadow: 0 4px 15px rgba(204, 164, 122, 0.2);
        }

        /* Category Slide Card */
        .category-slide-card {
            background: #ffffff;
            border: 1px solid var(--color-card-border);
            border-radius: 14px;
            padding: 14px 10px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            transition: all 0.2s ease;
        }

        .category-slide-card:hover,
        .category-slide-card.active {
            border-color: var(--color-camel);
            background-color: var(--color-camel-light);
        }

        .category-thumb-wrapper {
            width: 54px;
            height: 54px;
            margin-bottom: 8px;
        }

        .category-thumb-wrapper img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }
    </style>

    @include('templates.stores.shared.custom-code')
</head>

<body dir="{{ $isRtl ? 'rtl' : 'ltr' }}">

    {{-- Preloader Include --}}
    @include('templates.stores.furniture.includes.preloader')

    <div class="store-main-wrapper">
        {{-- Header Include --}}
        @include('templates.stores.furniture.includes.header')

        {{-- Flexible Body Content Area --}}
        <div class="store-body-content-area">
            {{-- Cover Banner Carousel --}}
            @if (!request()->has('category'))
                <div class="w-100 overflow-hidden">
                    <swiper-container space-between="0" class="w-100" autoplay-delay="3000" pagination="true"
                        autoplay-disable-on-interaction="false" loop="false">
                        @php
                            $covers = is_string($business_card_details->cover)
                                ? json_decode($business_card_details->cover, true)
                                : null;
                        @endphp

                        @if (is_array($covers) && count($covers) > 0)
                            @foreach ($covers as $cover)
                                <swiper-slide>
                                    <img class="d-block w-100 object-fit-cover"
                                        alt="{{ $business_card_details->title }}" src="{{ url($cover) }}"
                                        style="max-height: 420px;"
                                        onerror="this.onerror=null;this.src='{{ $placeholder }}';" />
                                </swiper-slide>
                            @endforeach
                        @else
                            <swiper-slide>
                                <img class="d-block w-100 object-fit-cover" alt="{{ $business_card_details->title }}"
                                    src="{{ !empty($business_card_details->cover) ? url($business_card_details->cover) : $placeholder }}"
                                    style="max-height: 420px;"
                                    onerror="this.onerror=null;this.src='{{ $placeholder }}';" />
                            </swiper-slide>
                        @endif
                    </swiper-container>
                </div>
            @endif

            <div class="store-catalog-inner-wrap p-3 p-lg-4">
                {{-- 1. Categories Section --}}
                @if (!request()->has('category'))
                    <div class="categories-heading-wrap">
                        <h2 class="luxury-section-pill">{{ __('Categories') }}</h2>
                    </div>

                    <div class="mb-2">
                        <swiper-container space-between="12" autoplay-delay="2500"
                            breakpoints='{
                            "320": { "slidesPerView": 2.2 },
                            "640": { "slidesPerView": 4.2 }, 
                            "1024": { "slidesPerView": 6 } 
                        }'
                            autoplay-disable-on-interaction="false" loop="false">
                            @foreach ($categories as $category)
                                <swiper-slide>
                                    <a href="{{ $storeUrl . '?category=' . urlencode(strtolower($category->category_name)) }}"
                                        class="category-slide-card">
                                        <div class="category-thumb-wrapper">
                                            <img src="{{ !empty($category->thumbnail) ? url($category->thumbnail) : $placeholder }}"
                                                alt="{{ $category->category_name }}"
                                                onerror="this.onerror=null;this.src='{{ $placeholder }}';" />
                                        </div>
                                        <span
                                            class="fs-4 fw-bold text-dark text-truncate w-100">{{ __($category->category_name) }}</span>
                                    </a>
                                </swiper-slide>
                            @endforeach
                        </swiper-container>
                    </div>
                @endif

                {{-- 2. Clean 1-Row Inline Search & Filter Toolbar --}}
                @if (request()->has('category') ||
                        request()->has('query') ||
                        request()->has('min') ||
                        request()->has('max') ||
                        request()->has('sort'))
                    <div class="store-filter-bar">
                        <form method="GET" action="{{ url()->current() }}" class="store-filter-inline-form">
                            @foreach (request()->except(['query', 'sort', 'page']) as $key => $value)
                                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                            @endforeach

                            {{-- Search Input --}}
                            <div class="filter-input-wrap">
                                <i class="ti ti-search filter-search-icon"></i>
                                <input type="text" name="query" placeholder="{{ __('Search products...') }}"
                                    value="{{ request('query') }}" class="store-filter-search-input" />
                            </div>

                            {{-- Sort Dropdown --}}
                            <div class="filter-sort-wrap">
                                <select name="sort" class="store-filter-sort-select" onchange="this.form.submit()">
                                    <option value="default" {{ request('sort') == 'default' ? 'selected' : '' }}>
                                        {{ __('Sort: Default') }}</option>
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

                            {{-- Search Action Button --}}
                            <button type="submit" class="store-filter-btn" aria-label="{{ __('Search') }}">
                                <i class="ti ti-search fs-3"></i>
                                <span class="d-none d-sm-inline">{{ __('Search') }}</span>
                            </button>
                        </form>
                    </div>
                @endif

                {{-- 3. Products Section Heading --}}
                <div class="products-heading-wrap">
                    <h2 class="luxury-section-pill">
                        @if (request()->has('category'))
                            {{ __(ucfirst(request('category'))) }}
                        @else
                            {{ __('Products') }}
                        @endif
                    </h2>
                </div>

                {{-- 4. Products Grid / Dead-Center Empty State --}}
                @if (count($products) > 0)
                    <div class="store-product-grid">
                        @foreach ($products as $product)
                            @include('templates.stores.furniture.partials.product-card')
                        @endforeach
                    </div>

                    {{-- Pagination Links --}}
                    <div class="d-flex justify-content-center mt-5 mb-3">
                        @if (request()->has('category'))
                            {{ $products->appends(request()->except('page'))->links('templates.stores.furniture.partials.pagination') }}
                        @else
                            {{ $products->links('templates.stores.furniture.partials.pagination') }}
                        @endif
                    </div>
                @else
                    {{-- Mathematically Dead-Centered Empty State --}}
                    <div class="store-empty-catalog-state">
                        <div class="empty-icon-pod">
                            <i class="ti ti-package-off" style="font-size: 40px;"></i>
                        </div>
                        <h3 class="fs-1 fw-bold text-dark mb-2">{{ __('No Products Found!') }}</h3>
                        <p class="text-secondary fs-4 mb-4 text-center" style="max-width: 380px;">
                            {{ __('We could not find any products matching your selection. Try resetting filters or explore other categories.') }}
                        </p>
                        <a href="{{ $storeUrl }}" class="btn btn-forest-search px-4 py-2">
                            <i class="ti ti-arrow-left fs-3 me-1"></i> {{ __('View All Products') }}
                        </a>
                    </div>
                @endif
            </div>
        </div>

        {{-- Footer Include (Firmly Anchored to Bottom) --}}
        @include('templates.stores.furniture.includes.footer')
    </div>

    {{-- Bottom Bar Include --}}
    @include('templates.stores.furniture.includes.bottom-bar')

    {{-- Popups Include --}}
    @include('templates.stores.furniture.includes.popups')

    {{-- PWA Modal Include --}}
    @include('templates.stores.furniture.includes.pwa-modal')

    {{-- WhatsApp Interactive Float Include --}}
    @include('templates.stores.furniture.includes.whatsapp-float')

    {{-- Cart drawer, checkout and the shared storefront script --}}
    @include('templates.stores.furniture.partials.cart-order-modal')
    @include('templates.stores.furniture.includes.scripts')
</body>

</html>
