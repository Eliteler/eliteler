@php
    $placeholder = asset('img/templates/gift-shop/placeholder.svg');
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">

<head>
    @include('templates.stores.gift-shop.includes.head')

    <style>
        /* Perfectly Centered Proportionate Breadcrumbs */
        .store-breadcrumbs-wrap {
            padding: 1.5rem 0 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            /* Centered */
            width: 100%;
        }

        .store-breadcrumbs-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: var(--store-radius-pill) !important;
            padding: 6px 18px;
            font-size: 0.85rem;
            font-weight: 600;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
        }

        .breadcrumb-link {
            color: var(--store-text-muted) !important;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            transition: color 0.2s ease;
        }

        .breadcrumb-link:hover {
            color: var(--store-primary) !important;
        }

        .breadcrumb-sep {
            color: #cbd5e1;
            font-size: 0.75rem;
        }

        .breadcrumb-current {
            color: var(--store-primary);
            font-weight: 700;
        }

        /* Luxury Heading Divider */
        .luxury-heading-container {
            text-align: center;
            padding: 0.75rem 1rem 1.25rem;
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

        /* 1-Row Live Category Filter Bar */
        .category-search-bar {
            background: #ffffff;
            border: 1.5px solid #e2e8f0;
            border-radius: var(--store-radius-pill);
            padding: 6px 8px 6px 18px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
            margin: 0 auto 2.5rem;
            max-width: 580px;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: all 0.2s ease;
        }

        .category-search-bar:focus-within {
            border-color: var(--store-primary);
            box-shadow: 0 0 0 4px rgba(220, 38, 38, 0.12);
        }

        .category-search-input {
            border: none;
            background: transparent;
            width: 100%;
            outline: none;
            font-size: 0.95rem;
            color: var(--store-text-main);
            font-weight: 500;
        }

        .category-search-icon-btn {
            width: 38px;
            height: 38px;
            border-radius: 50% !important;
            background: var(--store-dark);
            color: #ffffff;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            flex-shrink: 0;
        }

        /* Responsive Squircle Categories Grid with Bottom Clearance */
        .store-categories-grid {
            display: grid;
            gap: 20px;
            width: 100%;
            grid-template-columns: repeat(2, 1fr);
            margin-bottom: 3.5rem;
            /* Generous bottom spacing */
        }

        @media (min-width: 768px) {
            .store-categories-grid {
                grid-template-columns: repeat(3, 1fr);
                gap: 24px;
                margin-bottom: 4rem;
            }
        }

        @media (min-width: 992px) {
            .store-categories-grid {
                grid-template-columns: repeat(4, 1fr);
            }
        }

        /* Squircle Luxury Category Card */
        .category-luxury-card {
            background: #ffffff;
            border: 1px solid var(--store-border);
            border-radius: var(--store-radius-card);
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.03);
            display: flex;
            flex-direction: column;
            transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1);
            position: relative;
            height: 100%;
        }

        .category-luxury-card:hover {
            transform: translateY(-6px);
            border-color: rgba(220, 38, 38, 0.25);
            box-shadow: 0 12px 30px rgba(220, 38, 38, 0.1);
        }

        .category-thumbnail-box {
            width: 100%;
            aspect-ratio: 4 / 3;
            position: relative;
            background: var(--store-primary-light);
            overflow: hidden;
        }

        .category-thumbnail-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
            transition: transform 0.5s ease;
        }

        .category-luxury-card:hover .category-thumbnail-img {
            transform: scale(1.08);
        }

        .category-card-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(180deg, rgba(0, 0, 0, 0) 40%, rgba(0, 0, 0, 0.6) 100%);
        }

        .category-card-body {
            padding: 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            background: #ffffff;
            flex: 1 1 auto;
        }

        .category-card-name {
            font-size: 1.05rem;
            font-weight: 700;
            color: var(--store-dark);
            margin: 0;
            line-height: 1.3;
            transition: color 0.2s ease;
        }

        .category-luxury-card:hover .category-card-name {
            color: var(--store-primary);
        }

        .category-arrow-circle {
            width: 34px;
            height: 34px;
            border-radius: 50% !important;
            background: var(--store-primary-light);
            color: var(--store-primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            flex-shrink: 0;
            transition: all 0.3s ease;
        }

        .category-luxury-card:hover .category-arrow-circle {
            background: var(--store-primary);
            color: #ffffff;
            transform: translateX(3px);
        }

        /* Dead-Centered Empty State */
        .store-empty-catalog {
            margin: auto 0;
            padding: 3.5rem 1rem 4rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            width: 100%;
        }

        .empty-icon-pod {
            width: 90px;
            height: 90px;
            border-radius: 50% !important;
            background-color: #fef2f2 !important;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.25rem;
            box-shadow: 0 10px 25px rgba(220, 38, 38, 0.1);
        }

        .empty-icon-pod i {
            color: #dc2626 !important;
            font-size: 2.6rem !important;
            display: block;
        }

        .btn-pill-action {
            border-radius: var(--store-radius-pill) !important;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
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
            <div class="container-xl px-3 px-lg-4 py-2">

                {{-- Centered Breadcrumbs Navigation --}}
                <div class="store-breadcrumbs-wrap">
                    <nav class="store-breadcrumbs-pill" aria-label="breadcrumb">
                        <a href="{{ $storeUrl }}" class="breadcrumb-link">
                            <i class="ti ti-home fs-4"></i>
                            <span>{{ __('Shop') }}</span>
                        </a>
                        <i class="ti ti-chevron-right breadcrumb-sep"></i>
                        <span class="breadcrumb-current">{{ __('Categories') }}</span>
                    </nav>
                </div>

                {{-- Luxury Heading Divider --}}
                <div class="luxury-heading-container">
                    <h2 class="luxury-heading-title font-carattere">{{ __('Gift Categories') }}</h2>
                    <div class="heading-divider-wrap">
                        <div class="heading-divider-line"></div>
                        <img src="{{ asset('img/templates/gift-shop/heading.png') }}" class="heading-divider-icon"
                            alt="divider" onerror="this.style.display='none'" />
                        <div class="heading-divider-line right"></div>
                    </div>
                </div>

                {{-- Live Search Filter Input --}}
                @if (count($categories) > 0)
                    <div class="category-search-bar">
                        <input type="text" id="categoryFilterInput" class="category-search-input"
                            placeholder="{{ __('Filter categories instantly...') }}"
                            oninput="filterCategories(this.value)" />
                        <div class="category-search-icon-btn">
                            <i class="ti ti-search"></i>
                        </div>
                    </div>
                @endif

                {{-- Categories Grid Engine --}}
                @if (count($categories) > 0)
                    <div class="store-categories-grid" id="categoriesContainer">
                        @foreach ($categories as $category)
                            <div class="category-grid-item" data-name="{{ strtolower($category->category_name) }}">
                                <a href="{{ $storeUrl . '?category=' . urlencode(strtolower($category->category_name)) }}"
                                    class="category-luxury-card">
                                    <div class="category-thumbnail-box">
                                        <img src="{{ url($category->thumbnail) }}"
                                            alt="{{ $category->category_name }}" class="category-thumbnail-img"
                                            onerror="this.onerror=null;this.src='{{ $placeholder }}';" />
                                        <div class="category-card-overlay"></div>
                                    </div>
                                    <div class="category-card-body">
                                        <h3 class="category-card-name">{{ __($category->category_name) }}</h3>
                                        <div class="category-arrow-circle">
                                            <i class="ti ti-arrow-right"></i>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        @endforeach
                    </div>

                    {{-- Live Filter No Match Empty State with Explicit Red Icon --}}
                    <div class="store-empty-catalog" id="categoriesEmptyState" style="display: none;">
                        <div class="empty-icon-pod">
                            <i class="ti ti-search-off"></i>
                        </div>
                        <h3 class="fs-2 fw-bold text-dark mb-2">{{ __('No Categories Match Your Search') }}</h3>
                        <p class="text-muted fs-4 mb-4">
                            {{ __('Try typing a different keyword or clear the search filter.') }}</p>
                        <button type="button" class="btn btn-pill-action btn-dark px-4 py-2"
                            onclick="resetCategoryFilter()">
                            <i class="ti ti-refresh"></i>
                            <span>{{ __('Clear Filter') }}</span>
                        </button>
                    </div>
                @else
                    {{-- Dead-Centered Empty State when no categories exist in store --}}
                    <div class="store-empty-catalog">
                        <div class="empty-icon-pod">
                            <i class="ti ti-search-off"></i>
                        </div>
                        <h3 class="fs-2 fw-bold text-dark mb-2">{{ __('No Categories Found') }}</h3>
                        <p class="text-muted fs-4 mb-4">
                            {{ __('There are currently no categories created for this store.') }}</p>
                        <a href="{{ $storeUrl }}" class="btn btn-pill-action btn-dark px-4 py-2">
                            <i class="ti ti-arrow-left"></i>
                            <span>{{ __('Return to Shop') }}</span>
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

    <script>
        // Filter the category cards as the shopper types (called by the search box)
        function filterCategories(query) {
            const term = query.toLowerCase().trim();
            let visible = 0;

            document.querySelectorAll('.category-grid-item').forEach(function(item) {
                const match = (item.getAttribute('data-name') || '').includes(term);
                item.style.display = match ? '' : 'none';
                visible += match ? 1 : 0;
            });

            const empty = document.getElementById('categoriesEmptyState');
            if (empty) empty.style.display = visible === 0 ? 'flex' : 'none';
        }

        // Clear the search box and show every category again (empty-state button)
        function resetCategoryFilter() {
            const input = document.getElementById('categoryFilterInput');
            if (!input) return;
            input.value = '';
            filterCategories('');
            input.focus();
        }
    </script>
</body>

</html>
