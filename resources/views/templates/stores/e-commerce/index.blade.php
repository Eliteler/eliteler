<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">

<head>
    @include('templates.stores.e-commerce.includes.head')

    <style>
        .card,
        .bottom-bar2,
        .form-control,
        .form-select,
        .rounded-e-commerce,
        .alert,
        input,
        select {
            border-radius: 0px !important;
        }

        /* ========================================================= */
        /* Cohesive Filter & Search Controls (Exact 42px Alignment)  */
        /* ========================================================= */
        .filter-input-control {
            height: 42px !important;
            border-radius: 10px !important;
            border: 1.5px solid #e2e8f0 !important;
            font-size: 0.9rem !important;
            background-color: #ffffff !important;
        }

        .filter-input-control:focus {
            border-color: #206bc4 !important;
            box-shadow: 0 0 0 3px rgba(32, 107, 196, 0.15) !important;
        }

        .filter-btn-submit {
            height: 42px !important;
            border-radius: 10px !important;
            font-weight: 700 !important;
            font-size: 0.9rem !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
        }

        /* Category Sidebar Card Layout */
        .category-sidebar-card {
            background-color: #ffffff !important;
            border: 1px solid #edf2f7 !important;
            border-radius: 18px !important;
            padding: 20px !important;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.03) !important;
        }

        .sidebar-category-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 12px;
            border-radius: 12px;
            text-decoration: none !important;
            transition: all 0.2s ease;
            color: #334155;
            font-weight: 600;
        }

        .sidebar-category-link:hover {
            background-color: #f8fafc;
            color: #206bc4;
        }

        .sidebar-category-link.active {
            background-color: #e8f2fd;
            color: #206bc4 !important;
            font-weight: 700;
        }

        .sidebar-cat-icon {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            object-fit: contain;
            background-color: #ffffff;
            border: 1px solid #edf2f7;
            padding: 2px;
            flex-shrink: 0;
        }

        .category-thumb-icon {
            width: 40px;
            height: 40px;
            object-fit: contain;
        }


        .section-header-title {
            position: relative;
            font-size: 1.75rem;
            font-weight: 700;
            color: #0f172a;
            padding-bottom: 12px;
            margin-bottom: 24px;
        }

        .section-header-bar {
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

                    <div class="row mt-3 px-3 px-lg-4">

                        {{-- Hero Section: Completely Hidden on Category Filter --}}
                        @if (!request()->has('category'))
                            @if (!empty($business_card_details->sub_title) || !empty($business_card_details->title2) || !empty($business_card_details->subtitle2))
                                <div class="col-md-12 mb-3">
                                    <h3 class="alert alert-important alert-e-commerce-blue p-3 mb-0">
                                        @include('templates.stores.shared.subtitle-lines')
                                        {{ $business_card_details->sub_title }}
                                    </h3>
                                </div>
                            @endif

                            @if (session('success'))
                                <div class="col-md-12">
                                    <h3 class="alert alert-important alert-success p-3">{{ session('success') }}</h3>
                                </div>
                            @endif

                            @if (session('failed'))
                                <div class="col-md-12">
                                    <h3 class="alert alert-important alert-danger p-3">{{ session('failed') }}</h3>
                                </div>
                            @endif

                            <!-- Hero Carousel Banners -->
                            <div class="col-md-12 mb-4 overflow-hidden position-relative">
                                <swiper-container space-between="20" class="mySwiper" autoplay-delay="2500"
                                    pagination="true" autoplay-disable-on-interaction="false" loop="true">
                                    @php $covers = json_decode($business_card_details->cover, true); @endphp
                                    @if (is_array($covers) && count($covers) > 0)
                                        @foreach ($covers as $cover)
                                            <swiper-slide>
                                                <img class="rounded-e-commerce img-fluid d-block mx-auto"
                                                    alt="{{ $business_card_details->title }}"
                                                    src="{{ url($cover) }}" />
                                            </swiper-slide>
                                        @endforeach
                                    @else
                                        <swiper-slide>
                                            <img class="rounded-e-commerce img-fluid d-block mx-auto"
                                                alt="{{ $business_card_details->title }}"
                                                src="{{ $business_card_details->cover ? url($business_card_details->cover) : asset('images/default-cover.png') }}" />
                                        </swiper-slide>
                                    @endif
                                </swiper-container>
                            </div>
                        @endif

                        <div class="row g-3 {{ request('category') ? 'ps-2 ps-lg-3' : '' }}">

                            <!-- Categories Navigation Sidebar & Filter -->
                            <div class="{{ request('category') ? 'col-md-3 my-3' : 'col-md-12 my-3' }}">

                                @if (request('category'))
                                    {{-- Luxury Category Sidebar Card --}}
                                    <div class="category-sidebar-card">

                                        <!-- Price Filter Header & Form -->
                                        <div class="mb-4">
                                            <h4 class="fw-bold text-dark mb-2 d-flex align-items-center">
                                                <i
                                                    class="ti ti-adjustments-horizontal text-e-commerce-blue me-2 fs-2"></i>
                                                <span>{{ __('Price Filter') }}</span>
                                            </h4>

                                            <form class="row g-2 pt-1" method="GET" action="{{ url()->current() }}">
                                                @foreach (request()->except(['min', 'max', 'page']) as $key => $value)
                                                    <input type="hidden" name="{{ $key }}"
                                                        value="{{ $value }}">
                                                @endforeach

                                                <div class="col-6">
                                                    <input type="number" name="min"
                                                        class="form-control filter-input-control"
                                                        placeholder="{{ __('Min') }}" step="0.01" min="0"
                                                        value="{{ request('min') }}">
                                                </div>
                                                <div class="col-6">
                                                    <input type="number" name="max"
                                                        class="form-control filter-input-control"
                                                        placeholder="{{ __('Max') }}" step="0.01" min="0"
                                                        value="{{ request('max') }}">
                                                </div>
                                                <div class="col-12 mt-2">
                                                    <button type="submit"
                                                        class="btn btn-e-commerce-blue filter-btn-submit text-white w-100">
                                                        <i class="ti ti-filter me-1"></i> {{ __('Apply Filter') }}
                                                    </button>
                                                </div>
                                            </form>
                                        </div>

                                        <hr class="my-3 text-muted opacity-25">

                                        <!-- Categories List Header -->
                                        <h4 class="fw-bold text-dark mb-3 d-flex align-items-center">
                                            <i class="ti ti-category text-e-commerce-blue me-2 fs-2"></i>
                                            <span>{{ __('Categories') }}</span>
                                        </h4>

                                        <!-- Categories Vertical List with Active Highlighting -->
                                        <div class="d-flex flex-column gap-1">
                                            @php
                                                $isAllActive =
                                                    request('category') == 'all' ||
                                                    !request()->has('category') ||
                                                    request('category') == '';
                                            @endphp

                                            <!-- "All" Category Item -->
                                            <a href="{{ $storeUrl . '?category=all' }}"
                                                class="sidebar-category-link {{ $isAllActive ? 'active' : '' }}">
                                                <img src="{{ asset('img/templates/all-categories.png') }}"
                                                    alt="All" class="sidebar-cat-icon">
                                                <span class="text-truncate">{{ __('All Products') }}</span>
                                            </a>

                                            <!-- Individual Category Items -->
                                            @foreach ($categories as $category)
                                                @php
                                                    $catSlug = urlencode(strtolower($category->category_name));
                                                    $isCatActive =
                                                        request('category') == $catSlug ||
                                                        request('category') == strtolower($category->category_name);
                                                @endphp
                                                <a href="{{ $storeUrl . '?category=' . $catSlug }}"
                                                    class="sidebar-category-link {{ $isCatActive ? 'active' : '' }}">
                                                    <img src="{{ url($category->thumbnail) }}" class="sidebar-cat-icon"
                                                        alt="{{ $category->category_name }}"
                                                        onerror="this.onerror=null; this.src='{{ asset('img/templates/all-categories.png') }}';" />
                                                    <span
                                                        class="text-truncate">{{ __($category->category_name) }}</span>
                                                </a>
                                            @endforeach
                                        </div>

                                    </div>
                                @else
                                    <!-- Index Swiper View (When no category filter is applied) -->
                                    <div class="text-center position-relative mb-4">
                                        <h2 class="section-header-title d-inline-block">
                                            {{ __('Categories') }}
                                            <span class="section-header-bar start-50 translate-middle-x"></span>
                                        </h2>
                                    </div>

                                    <swiper-container space-between="10" class="mySwiper" autoplay-delay="2500"
                                        breakpoints='{
                                            "320": { "slidesPerView": 2 },
                                            "640": { "slidesPerView": 4 }, 
                                            "1024": { "slidesPerView": 6 } 
                                        }'
                                        autoplay-disable-on-interaction="false" loop="true">

                                        @foreach ($categories as $category)
                                            <swiper-slide class="cursor-pointer">
                                                <a href="{{ $storeUrl . '?category=' . urlencode(strtolower($category->category_name)) }}"
                                                    class="m-1 card text-decoration-none">
                                                    <div
                                                        class="ratio ratio-1x1 d-flex align-items-center justify-content-center">
                                                        <img src="{{ url($category->thumbnail) }}"
                                                            alt="{{ $category->category_name }}"
                                                            class="img-fluid category-thumbnail"
                                                            onerror="this.onerror=null; this.src='{{ asset('img/templates/all-categories.png') }}';">
                                                    </div>
                                                    <h3
                                                        class="fs-3 text-truncate single-product text-dark text-center p-2 mb-0">
                                                        {{ __($category->category_name) }}
                                                    </h3>
                                                </a>
                                            </swiper-slide>
                                        @endforeach
                                    </swiper-container>
                                @endif

                            </div>

                            <!-- Products Listing Catalog Section -->
                            <div class="{{ request('category') ? 'col-md-9 ps-lg-4 my-3' : 'col-md-12' }}">

                                {{-- Unified Search & Sort Bar Row --}}
                                @if (request('category'))
                                    <div class="row align-items-center g-2 mb-4">
                                        <div class="col-md-8">
                                            <form class="d-flex gap-2" method="GET" action="{{ url()->current() }}">
                                                @foreach (request()->except(['query', 'page']) as $key => $value)
                                                    <input type="hidden" name="{{ $key }}"
                                                        value="{{ $value }}">
                                                @endforeach
                                                <input type="text" name="query"
                                                    placeholder="{{ __('Search products...') }}"
                                                    value="{{ request('query') }}"
                                                    class="form-control filter-input-control flex-grow-1" />
                                                <button type="submit"
                                                    class="btn btn-e-commerce-blue filter-btn-submit text-white px-4">
                                                    <i class="ti ti-search me-1"></i> {{ __('Search') }}
                                                </button>
                                            </form>
                                        </div>

                                        <div class="col-md-4 mt-2 mt-md-0">
                                            <select name="sort" class="form-select filter-input-control"
                                                onchange="itemSort(this.value)">
                                                <option value="default"
                                                    {{ request('sort') == 'default' ? 'selected' : '' }}>
                                                    {{ __('Recently Added') }}</option>
                                                <option value="price_asc"
                                                    {{ request('sort') == 'price_asc' ? 'selected' : '' }}>
                                                    {{ __('Price: Low to High') }}</option>
                                                <option value="price_desc"
                                                    {{ request('sort') == 'price_desc' ? 'selected' : '' }}>
                                                    {{ __('Price: High to Low') }}</option>
                                                <option value="name_asc"
                                                    {{ request('sort') == 'name_asc' ? 'selected' : '' }}>
                                                    {{ __('Name: A to Z') }}</option>
                                                <option value="name_desc"
                                                    {{ request('sort') == 'name_desc' ? 'selected' : '' }}>
                                                    {{ __('Name: Z to A') }}</option>
                                            </select>
                                        </div>
                                    </div>
                                @endif

                                <div
                                    class="text-start position-relative mb-4 {{ request('category') ? '' : 'd-none' }}">
                                    <h2 class="section-header-title d-inline-block">
                                        {{ __('Products') }}
                                        <span class="section-header-bar start-0"></span>
                                    </h2>
                                </div>

                                <div
                                    class="text-center position-relative my-4 {{ request('category') ? 'd-none' : '' }}">
                                    <h2 class="section-header-title d-inline-block">
                                        {{ __('Products') }}
                                        <span class="section-header-bar start-50 translate-middle-x"></span>
                                    </h2>
                                </div>

                                <!-- Product Grid Cards -->
                                @if (count($products) > 0)
                                    <div class="row g-3">
                                        @foreach ($products as $product)
                                            @include('templates.stores.e-commerce.partials.product-card', [
                                                'class' =>
                                                    'col-12 ' .
                                                    (request('category') ? 'col-md-4' : 'col-md-3') .
                                                    ' mb-3',
                                            ])
                                        @endforeach

                                        <!-- Standalone Pagination Partial -->
                                        <div class="col-md-12 my-3">
                                            @if (request()->has('category'))
                                                {{ $products->appends(request()->except('page'))->links('templates.stores.e-commerce.partials.pagination', ['color' => 'e-commerce-blue']) }}
                                            @else
                                                {{ $products->links('templates.stores.e-commerce.partials.pagination', ['color' => 'e-commerce-blue']) }}
                                            @endif
                                        </div>
                                    </div>
                                @else
                                    <!-- Elegant Empty State -->
                                    <div class="text-center py-5 my-4 bg-white rounded-4 border p-4 shadow-sm">
                                        <div class="mb-3">
                                            <i class="ti ti-package-off fs-1 text-muted"
                                                style="font-size: 3.5rem !important;"></i>
                                        </div>
                                        <h3 class="fs-1 fw-bold text-dark mb-1">{{ __('No Products Found') }}</h3>
                                        <p class="text-muted fs-3 mb-4">
                                            {{ __('There are no products matching this category or filter.') }}</p>
                                        <a href="{{ $storeUrl }}"
                                            class="btn btn-e-commerce-blue filter-btn-submit text-white px-4 py-2">
                                            <i class="ti ti-arrow-left me-1"></i> {{ __('View All Products') }}
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>
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

    <!-- Modular Popups Include -->
    @include('templates.stores.e-commerce.includes.popups')

    <!-- Modular PWA Install Modal (Index Only) -->
    @include('templates.stores.e-commerce.includes.pwa-modal')

    <!-- Floating WhatsApp Widget Include -->
    @include('templates.stores.e-commerce.includes.whatsapp-float', [
        'businessImage' => $business_card_details->profile,
        'businessName' => $card_details->title,
        'whatsappNumber' => $enquiry_button,
    ])

    {{-- Cart drawer, checkout and the shared storefront script --}}
    @include('templates.stores.e-commerce.partials.cart-order-modal')
    @include('templates.stores.e-commerce.includes.scripts')

    <script>
        // Sort the listing (called by the sort menu)
        function itemSort(sort) {
            const params = new URLSearchParams(window.location.search);
            params.set('sort', sort);
            window.location.href = "{{ url()->current() }}?" + params.toString();
        }
    </script>
</body>

</html>
