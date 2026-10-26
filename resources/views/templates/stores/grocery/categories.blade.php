{{-- GoBiz Luxury Grocery Store - Categories Showcase Page --}}
@php
    $placeholder = asset('img/templates/grocery/placeholder.svg');
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">

<head>
    @include('templates.stores.grocery.includes.head')

    <style>
        /* Proportionate Breadcrumbs */
        .store-breadcrumb-nav {
            max-width: 1240px;
            margin: 20px auto 0 auto;
            padding: 0 20px;
            width: 100%;
        }

        .store-breadcrumb-list {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #ffffff;
            padding: 8px 18px;
            border-radius: 50px !important;
            border: 1px solid #c8e6c9;
            list-style: none;
            margin: 0;
            box-shadow: 0 2px 6px rgba(46, 125, 50, 0.04);
        }

        .store-breadcrumb-item {
            font-size: 13px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .store-breadcrumb-link {
            color: #616161;
            transition: color 0.2s ease;
        }

        .store-breadcrumb-link:hover {
            color: #2e7d32;
        }

        .store-breadcrumb-sep {
            color: #a5d6a7;
            font-size: 11px;
        }

        [dir="rtl"] .store-breadcrumb-sep {
            transform: rotate(180deg);
        }

        .store-breadcrumb-active {
            color: #1b5e20;
        }

        /* Real-Time Search & Title Section */
        .store-category-header-area {
            max-width: 1240px;
            margin: 25px auto 0 auto;
            padding: 0 20px;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 16px;
        }

        .store-category-title-wrap {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .store-category-main-heading {
            font-size: 26px;
            font-weight: 800;
            color: #1b5e20;
            margin: 0;
            line-height: 1.2;
        }

        .store-category-sub-heading {
            font-size: 13px;
            color: #616161;
            margin: 0;
        }

        /* Real-Time Live Search Input */
        .store-category-search-box {
            position: relative;
            min-width: 280px;
            max-width: 380px;
            flex: 1 1 auto;
        }

        .store-category-search-input {
            width: 100%;
            height: 44px;
            border-radius: 50px !important;
            border: 1px solid #c8e6c9 !important;
            background-color: #ffffff !important;
            padding: 0 42px 0 20px;
            font-size: 13px;
            color: #1f2937;
            outline: none;
            box-shadow: 0 2px 8px rgba(46, 125, 50, 0.04);
            transition: all 0.25s ease;
        }

        [dir="rtl"] .store-category-search-input {
            padding: 0 20px 0 42px;
        }

        .store-category-search-input:focus {
            border-color: #2e7d32 !important;
            box-shadow: 0 0 0 3px rgba(46, 125, 50, 0.15) !important;
        }

        .store-category-search-icon {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 18px;
            color: #2e7d32;
            pointer-events: none;
        }

        [dir="rtl"] .store-category-search-icon {
            right: auto;
            left: 16px;
        }

        /* Responsive Categories Grid */
        .store-categories-grid-section {
            max-width: 1240px;
            margin: 25px auto 45px auto;
            padding: 0 20px;
            width: 100%;
        }

        .store-categories-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 16px;
            width: 100%;
        }

        @media (min-width: 768px) {
            .store-categories-grid {
                grid-template-columns: repeat(3, 1fr);
                gap: 22px;
            }
        }

        @media (min-width: 992px) {
            .store-categories-grid {
                grid-template-columns: repeat(4, 1fr);
                gap: 24px;
            }
        }

        @media (min-width: 1200px) {
            .store-categories-grid {
                grid-template-columns: repeat(5, 1fr);
            }
        }

        /* Luxury Squircle Category Card */
        .store-category-card {
            background: #ffffff;
            border: 1px solid #c8e6c9;
            border-radius: 26px !important;
            padding: 20px 16px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            position: relative;
            box-shadow: 0 4px 14px rgba(46, 125, 50, 0.04);
            cursor: pointer;
        }

        .store-category-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 28px rgba(46, 125, 50, 0.12);
            border-color: #2e7d32;
        }

        .store-category-thumb-wrapper {
            width: 86px;
            height: 86px;
            border-radius: 50% !important;
            background-color: #f1f8e9;
            border: 2px solid #c8e6c9;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            margin-bottom: 14px;
            flex-shrink: 0;
            transition: transform 0.3s ease;
        }

        .store-category-card:hover .store-category-thumb-wrapper {
            transform: scale(1.08);
            border-color: #2e7d32;
        }

        .store-category-thumb {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .store-category-name {
            font-size: 15px;
            font-weight: 700;
            color: #1f2937;
            margin: 0 0 6px 0;
            line-height: 1.3;
            transition: color 0.2s ease;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .store-category-card:hover .store-category-name {
            color: #2e7d32;
        }

        .store-category-badge {
            font-size: 11px;
            font-weight: 600;
            color: #2e7d32;
            background-color: #e8f5e9;
            padding: 4px 12px;
            border-radius: 50px !important;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            transition: all 0.2s ease;
        }

        .store-category-card:hover .store-category-badge {
            background-color: #2e7d32;
            color: #ffffff;
        }

        /* Dead-Centered Empty State */
        .store-empty-categories {
            margin: auto 0;
            padding: 50px 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            width: 100%;
        }

        .empty-category-icon-pod {
            width: 84px;
            height: 84px;
            border-radius: 50% !important;
            background-color: #e8f5e9;
            color: #2e7d32;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 38px;
            margin-bottom: 16px;
        }

        .empty-category-title {
            font-size: 19px;
            font-weight: 800;
            color: #1b5e20;
            margin-bottom: 6px;
        }

        .empty-category-desc {
            font-size: 13px;
            color: #757575;
            max-width: 280px;
            margin-bottom: 20px;
        }

        .btn-empty-return {
            height: 42px;
            padding: 0 24px !important;
            border-radius: 50px !important;
            background-color: #2e7d32 !important;
            color: #ffffff !important;
            border: none !important;
            font-size: 13px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
    </style>

    @include('templates.stores.shared.custom-code')
</head>

<body dir="{{ $isRtl ? 'rtl' : 'ltr' }}">

    {{-- 1. Orbit Preloader --}}
    @include('templates.stores.grocery.includes.preloader')

    <div class="store-main-wrapper">
        {{-- 2. Header Component --}}
        @include('templates.stores.grocery.includes.header')

        <div class="store-body-content-area">
            {{-- Proportionate Breadcrumbs --}}
            <nav class="store-breadcrumb-nav" aria-label="breadcrumb">
                <ol class="store-breadcrumb-list">
                    <li class="store-breadcrumb-item">
                        <a href="{{ $storeUrl }}" class="store-breadcrumb-link">
                            <i class="ti ti-home"></i>
                            <span>{{ __('Shop') }}</span>
                        </a>
                    </li>
                    <li class="store-breadcrumb-sep"><i class="ti ti-chevron-right"></i></li>
                    <li class="store-breadcrumb-item store-breadcrumb-active" aria-current="page">
                        <span>{{ __('Categories') }}</span>
                    </li>
                </ol>
            </nav>

            {{-- Main Title & Real-Time Filter Area --}}
            <div class="store-category-header-area">
                <div class="store-category-title-wrap">
                    <h1 class="store-category-main-heading">{{ __('All Categories') }}</h1>
                    <p class="store-category-sub-heading">
                        {{ __('Browse our wide collection of fresh grocery categories.') }}</p>
                </div>

                {{-- Real-Time Category Search Input --}}
                <div class="store-category-search-box">
                    <input type="text" id="categoryLiveSearchInput" class="store-category-search-input"
                        placeholder="{{ __('Filter categories...') }}" autocomplete="off" />
                    <i class="ti ti-search store-category-search-icon"></i>
                </div>
            </div>

            {{-- Responsive Categories Grid --}}
            <div class="store-categories-grid-section">
                @if (count($categories) > 0)
                    <div class="store-categories-grid" id="categoryCardGrid">
                        @foreach ($categories as $category)
                            @php
                                $catName = $category->category_name;
                                $catUrl = $storeUrl . '?category=' . urlencode(strtolower($catName));
                                $catThumb = !empty($category->thumbnail) ? url($category->thumbnail) : $placeholder;
                            @endphp

                            <a href="{{ $catUrl }}" class="store-category-card category-item-card"
                                data-category-name="{{ strtolower($catName) }}">
                                <div class="store-category-thumb-wrapper">
                                    <img src="{{ $catThumb }}" alt="{{ $catName }}"
                                        class="store-category-thumb"
                                        onerror="this.onerror=null;this.src='{{ $placeholder }}';" />
                                </div>
                                <h3 class="store-category-name">{{ __($catName) }}</h3>
                                <span class="store-category-badge">
                                    <span>{{ __('Explore') }}</span>
                                    <i class="ti ti-arrow-right"></i>
                                </span>
                            </a>
                        @endforeach
                    </div>

                    {{-- Dynamic Live Search Empty State --}}
                    <div id="noSearchCategories" class="store-empty-categories" style="display: none;">
                        <div class="empty-category-icon-pod">
                            <i class="ti ti-search-off"></i>
                        </div>
                        <h4 class="empty-category-title">{{ __('No Categories Match Your Search') }}</h4>
                        <p class="empty-category-desc">
                            {{ __('Try typing another category name or view all categories.') }}</p>
                        <button type="button" class="btn btn-empty-return" onclick="resetCategoryFilter()">
                            <i class="ti ti-refresh"></i>
                            <span>{{ __('Reset Filter') }}</span>
                        </button>
                    </div>
                @else
                    {{-- Mathematically Dead-Centered Empty State --}}
                    <div class="store-empty-categories">
                        <div class="empty-category-icon-pod">
                            <i class="ti ti-folder-off"></i>
                        </div>
                        <h4 class="empty-category-title">{{ __('No Categories Found') }}</h4>
                        <p class="empty-category-desc">
                            {{ __('There are currently no active categories listed in this store.') }}</p>
                        <a href="{{ $storeUrl }}" class="btn btn-empty-return">
                            <i class="ti ti-arrow-left"></i>
                            <span>{{ __('Back to Shop') }}</span>
                        </a>
                    </div>
                @endif
            </div>
        </div>

        {{-- 3. Sticky Luxury Footer --}}
        @include('templates.stores.grocery.includes.footer')

        {{-- 4. Bottom Navigation Bar --}}
        @include('templates.stores.grocery.includes.bottom-bar')
    </div>

    {{-- Interactive Floating WhatsApp --}}
    @include('templates.stores.grocery.includes.whatsapp-float')

    {{-- PWA Install Modal --}}
    @include('templates.stores.grocery.includes.pwa-modal')

    {{-- VIP & Newsletter Popups --}}
    @include('templates.stores.grocery.includes.popups')

    {{-- Cart drawer, checkout and the shared storefront script --}}
    @include('templates.stores.grocery.partials.cart-order-modal')
    @include('templates.stores.grocery.includes.scripts')

    <script>
        // Filter the category cards as the shopper types in the search box
        document.addEventListener('DOMContentLoaded', function() {
            const filterInput = document.getElementById('categoryLiveSearchInput');
            const cardGrid = document.getElementById('categoryCardGrid');
            const noResults = document.getElementById('noSearchCategories');
            if (!filterInput || !cardGrid) return;

            filterInput.addEventListener('input', function() {
                const query = this.value.trim().toLowerCase();
                let visible = 0;

                cardGrid.querySelectorAll('.category-item-card').forEach(function(card) {
                    const match = (card.getAttribute('data-category-name') || '').includes(query);
                    card.style.display = match ? 'flex' : 'none';
                    visible += match ? 1 : 0;
                });

                if (noResults) noResults.style.display = visible === 0 ? 'flex' : 'none';
            });
        });

        // Clear the search box and show every category again (empty-state button)
        function resetCategoryFilter() {
            const filterInput = document.getElementById('categoryLiveSearchInput');
            if (!filterInput) return;
            filterInput.value = '';
            filterInput.dispatchEvent(new Event('input'));
            filterInput.focus();
        }
    </script>
</body>

</html>
