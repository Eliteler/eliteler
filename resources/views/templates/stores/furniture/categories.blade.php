@php
    $placeholder = asset('img/templates/furniture/placeholder.svg');
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">

<head>
    @include('templates.stores.furniture.includes.head')

    <style>
        .store-categories-body-area {
            flex: 1 0 auto;
            width: 100%;
            display: flex;
            flex-direction: column;
        }

        /* Refined Luxury Breadcrumbs Navigation */
        .store-breadcrumbs-bar {
            background-color: #faf8f5;
            border-bottom: 1px solid var(--color-card-border);
            padding: 12px 20px;
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 8px;
            font-size: 13px;
            font-weight: 600;
            line-height: 1.4;
        }

        .breadcrumb-link {
            color: var(--color-forest-teal);
            display: inline-flex;
            align-items: center;
            gap: 5px;
            flex-shrink: 0;
            transition: color 0.2s ease;
        }

        .breadcrumb-link i {
            font-size: 15px !important;
        }

        .breadcrumb-link:hover {
            color: var(--color-camel);
        }

        .breadcrumb-separator {
            color: var(--color-camel);
            display: inline-flex;
            align-items: center;
            flex-shrink: 0;
        }

        .breadcrumb-separator i {
            font-size: 12px !important;
        }

        .breadcrumb-current {
            color: var(--color-text-muted);
            font-weight: 600;
        }

        /* Section Headings */
        .categories-page-heading-wrap {
            text-align: center;
            margin-top: 24px;
            margin-bottom: 20px;
        }

        .luxury-section-pill {
            background-color: var(--color-camel);
            color: #ffffff;
            font-family: 'DM Serif Display', serif;
            font-size: 20px;
            padding: 7px 34px;
            border-radius: 50px !important;
            display: inline-block;
            letter-spacing: 0.5px;
        }

        /* Live Filter Search Input */
        .categories-search-box {
            max-width: 480px;
            margin: 0 auto 28px auto;
            position: relative;
            width: 100%;
        }

        .categories-search-input {
            height: 48px;
            border-radius: 50px !important;
            border: 1.5px solid #cbd5e1;
            padding-left: 48px;
            padding-right: 20px;
            font-size: 14px;
            background-color: #ffffff;
            transition: all 0.2s ease;
        }

        .categories-search-input:focus {
            border-color: var(--color-forest-teal);
            box-shadow: 0 0 0 3px rgba(31, 75, 72, 0.15);
            outline: none;
        }

        .search-icon-inside {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 18px;
            pointer-events: none;
        }

        /* Categories Responsive CSS Grid */
        .categories-card-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 16px;
        }

        @media (min-width: 768px) {
            .categories-card-grid {
                grid-template-columns: repeat(3, 1fr);
                gap: 20px;
            }
        }

        @media (min-width: 992px) {
            .categories-card-grid {
                grid-template-columns: repeat(4, 1fr);
                gap: 24px;
            }
        }

        @media (min-width: 1200px) {
            .categories-card-grid {
                grid-template-columns: repeat(5, 1fr);
                gap: 24px;
            }
        }

        /* Squircle Category Card */
        .category-squircle-card {
            background: #ffffff;
            border: 1px solid var(--color-card-border);
            border-radius: 18px !important;
            padding: 16px 14px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.25s ease, border-color 0.25s ease;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
            position: relative;
            overflow: hidden;
        }

        .category-squircle-card:hover {
            transform: translateY(-4px);
            border-color: var(--color-camel);
            box-shadow: 0 12px 28px -5px rgba(204, 164, 122, 0.2);
            background-color: var(--color-camel-light);
        }

        .category-image-container {
            width: 100%;
            aspect-ratio: 1 / 1;
            border-radius: 14px;
            overflow: hidden;
            background-color: #f8fafc;
            border: 1px solid #f1ede4;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 10px;
        }

        .category-image-container img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            transition: transform 0.35s ease;
        }

        .category-squircle-card:hover .category-image-container img {
            transform: scale(1.08);
        }

        .category-card-title {
            font-size: 15px;
            font-weight: 700;
            color: var(--color-text-main);
            margin: 0;
            line-height: 1.3;
            transition: color 0.2s ease;
            max-width: 100%;
        }

        .category-squircle-card:hover .category-card-title {
            color: var(--color-forest-teal);
        }

        /* Redesigned Centered Empty State */
        .categories-empty-state-wrap {
            flex: 1 0 auto;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 48px 20px 60px;
            margin: auto 0;
            width: 100%;
        }

        .categories-empty-icon-pod {
            width: 86px;
            height: 86px;
            border-radius: 50% !important;
            background-color: var(--color-camel-light);
            color: var(--color-camel);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 18px;
            box-shadow: 0 4px 15px rgba(204, 164, 122, 0.2);
        }

        .categories-empty-title {
            font-family: 'DM Serif Display', serif;
            font-size: 24px;
            color: var(--color-text-main);
            margin-bottom: 8px;
        }

        .categories-empty-desc {
            font-size: 14px;
            color: var(--color-text-muted);
            max-width: 380px;
            margin-bottom: 22px;
            line-height: 1.5;
        }

        .btn-clear-category-search {
            height: 44px;
            padding: 0 24px;
            border-radius: 50px !important;
            background-color: var(--color-forest-teal);
            color: #ffffff !important;
            font-weight: 700;
            font-size: 13.5px;
            border: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            box-shadow: 0 4px 14px rgba(31, 75, 72, 0.3);
            transition: background-color 0.2s ease, transform 0.2s ease;
        }

        .btn-clear-category-search:hover {
            background-color: var(--color-forest-teal-dark);
            transform: translateY(-1px);
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

        {{-- Luxury Breadcrumbs Navigation --}}
        <div class="store-breadcrumbs-bar">
            <a href="{{ $storeUrl }}" class="breadcrumb-link">
                <i class="ti ti-home"></i>
                <span>{{ __('Home') }}</span>
            </a>
            <span class="breadcrumb-separator"><i class="ti ti-chevron-right"></i></span>
            <span class="breadcrumb-current">{{ __('Categories') }}</span>
        </div>

        {{-- Flexible Main Content Area --}}
        <div class="store-categories-body-area p-3 p-lg-4">
            {{-- Section Heading Badge --}}
            <div class="categories-page-heading-wrap">
                <h2 class="luxury-section-pill">{{ __('All Categories') }}</h2>
            </div>

            {{-- Live Instant Search Input --}}
            <div class="categories-search-box">
                <i class="ti ti-search search-icon-inside"></i>
                <input type="text" id="categorySearchInput" class="form-control categories-search-input"
                    placeholder="{{ __('Filter categories by name...') }}" onkeyup="filterCategoriesLive(this.value)">
            </div>

            {{-- Categories Grid Showcase --}}
            @if (count($categories) > 0)
                <div class="categories-card-grid" id="categoriesGrid">
                    {{-- Quick Action Card: All Products --}}
                    <a href="{{ $storeUrl }}" class="category-squircle-card category-item-filter"
                        data-name="{{ strtolower(__('All Products')) }}">
                        <div class="category-image-container">
                            <img src="{{ asset('img/templates/all-categories.png') }}" alt="{{ __('All Products') }}"
                                onerror="this.onerror=null;this.src='{{ $placeholder }}';">
                        </div>
                        <h3 class="category-card-title text-truncate w-100">{{ __('All Products') }}</h3>
                    </a>

                    {{-- Dynamic Categories Loop --}}
                    @foreach ($categories as $category)
                        @php
                            $catImg = !empty($category->thumbnail)
                                ? url($category->thumbnail)
                                : $placeholder;
                            $catTargetUrl = $storeUrl . '?category=' . urlencode(strtolower($category->category_name));
                        @endphp

                        <a href="{{ $catTargetUrl }}" class="category-squircle-card category-item-filter"
                            data-name="{{ strtolower($category->category_name) }}">
                            <div class="category-image-container">
                                <img src="{{ $catImg }}" alt="{{ $category->category_name }}"
                                    onerror="this.onerror=null;this.src='{{ $placeholder }}';">
                            </div>
                            <h3 class="category-card-title text-truncate w-100">
                                {{ __($category->category_name) }}
                            </h3>
                        </a>
                    @endforeach
                </div>

                {{-- Redesigned Live Search Empty State (Dead-Centered) --}}
                <div id="noCategoriesFound" class="categories-empty-state-wrap d-none">
                    <div class="categories-empty-icon-pod">
                        <i class="ti ti-folder-search" style="font-size: 38px;"></i>
                    </div>
                    <h3 class="categories-empty-title">{{ __('No Matching Categories Found') }}</h3>
                    <p class="categories-empty-desc">
                        {{ __('We could not find any categories matching your keyword. Try searching for something else.') }}
                    </p>
                    <button type="button" class="btn-clear-category-search" onclick="resetCategorySearch()">
                        <i class="ti ti-rotate-clockwise fs-3"></i>
                        <span>{{ __('Clear Search') }}</span>
                    </button>
                </div>
            @else
                {{-- Initial Zero Categories Found State --}}
                <div class="categories-empty-state-wrap">
                    <div class="categories-empty-icon-pod">
                        <i class="ti ti-folder-x" style="font-size: 38px;"></i>
                    </div>
                    <h3 class="categories-empty-title">{{ __('No Categories Available') }}</h3>
                    <p class="categories-empty-desc">{{ __('There are no categories listed at the moment.') }}</p>
                    <a href="{{ $storeUrl }}" class="btn-clear-category-search">
                        <i class="ti ti-arrow-left fs-3"></i>
                        <span>{{ __('Back to Store') }}</span>
                    </a>
                </div>
            @endif
        </div>

        {{-- Footer Include (Firmly Anchored to Bottom) --}}
        @include('templates.stores.furniture.includes.footer')
    </div>

    {{-- Bottom Bar Include --}}
    @include('templates.stores.furniture.includes.bottom-bar')

    {{-- PWA Modal Include --}}
    @include('templates.stores.furniture.includes.pwa-modal')

    {{-- WhatsApp Interactive Float Include --}}
    @include('templates.stores.furniture.includes.whatsapp-float')

    {{-- Cart drawer, checkout and the shared storefront script --}}
    @include('templates.stores.furniture.partials.cart-order-modal')
    @include('templates.stores.furniture.includes.scripts')

    <script>
        // Filter the category cards as the shopper types (called by the search box)
        function filterCategoriesLive(query) {
            const term = query.toLowerCase().trim();
            let visible = 0;

            document.querySelectorAll('.category-item-filter').forEach(function(card) {
                const match = (card.getAttribute('data-name') || '').includes(term);
                card.style.display = match ? 'flex' : 'none';
                visible += match ? 1 : 0;
            });

            const noMatch = document.getElementById('noCategoriesFound');
            const grid = document.getElementById('categoriesGrid');
            if (noMatch) noMatch.classList.toggle('d-none', visible !== 0);
            if (grid) grid.classList.toggle('d-none', visible === 0);
        }

        // Clear the search box and show every category again (empty-state button)
        function resetCategorySearch() {
            const input = document.getElementById('categorySearchInput');
            if (!input) return;
            input.value = '';
            filterCategoriesLive('');
        }
    </script>
</body>

</html>
