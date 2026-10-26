{{-- GoBiz Luxury Jewellery Categories Page Engine --}}
@php
    $placeholder = asset('img/templates/jewellery-shop/placeholder.svg');
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">

<head>
    @include('templates.stores.jewellery-shop.includes.head')

    <style>

        /* Unified Container */
        .categories-container-box {
            max-width: 1240px;
            margin: 0 auto;
            padding: 24px 20px 50px 20px;
            width: 100%;
        }

        /* Proportionate Breadcrumbs */
        .luxury-breadcrumbs-box {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #ffffff;
            border: 1px solid var(--store-border);
            border-radius: var(--store-radius-pill) !important;
            padding: 6px 18px;
            font-size: 13px;
            color: var(--store-primary-dark);
            margin-bottom: 24px;
            box-shadow: 0 2px 8px rgba(212, 175, 55, 0.06);
        }

        .breadcrumb-link {
            color: var(--store-primary-dark) !important;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: color 0.2s ease;
        }

        .breadcrumb-link:hover {
            color: var(--store-primary-hover) !important;
        }

        .breadcrumb-sep {
            font-size: 13px;
            opacity: 0.5;
        }

        .breadcrumb-current {
            font-weight: 700;
            color: var(--store-dark);
        }

        /* Header & Live Filter Toolbar */
        .categories-top-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 16px;
            margin-bottom: 30px;
        }

        .categories-page-title {
            font-family: 'Gloock', serif;
            font-size: 26px;
            font-weight: 700;
            color: var(--store-dark);
            margin: 0;
            line-height: 1.2;
        }

        .categories-live-search-box {
            position: relative;
            width: 320px;
            max-width: 100%;
        }

        .cat-search-icon {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--store-primary-hover);
            font-size: 17px;
            pointer-events: none;
        }

        [dir="rtl"] .cat-search-icon {
            left: auto;
            right: 18px;
        }

        .cat-live-search-input {
            width: 100%;
            height: 44px;
            border-radius: var(--store-radius-pill) !important;
            border: 1.5px solid var(--store-border);
            padding: 0 20px 0 46px;
            font-size: 13.5px;
            background-color: #ffffff;
            color: var(--store-dark);
            outline: none;
            transition: all 0.25s ease;
        }

        [dir="rtl"] .cat-live-search-input {
            padding: 0 46px 0 20px;
        }

        .cat-live-search-input:focus {
            border-color: var(--store-primary);
            box-shadow: 0 0 0 3px rgba(212, 175, 55, 0.18);
        }

        /* Categories Responsive Grid */
        .categories-luxury-grid {
            display: grid;
            gap: 20px;
            width: 100%;
            grid-template-columns: repeat(2, 1fr);
        }

        @media (min-width: 768px) {
            .categories-luxury-grid {
                grid-template-columns: repeat(3, 1fr);
                gap: 22px;
            }
        }

        @media (min-width: 992px) {
            .categories-luxury-grid {
                grid-template-columns: repeat(4, 1fr);
            }
        }

        @media (min-width: 1200px) {
            .categories-luxury-grid {
                grid-template-columns: repeat(5, 1fr);
            }
        }

        /* Luxury Squircle Category Card */
        .luxury-category-card {
            background: var(--store-card-bg);
            border: 1.5px solid var(--store-border);
            border-radius: var(--store-radius-card) !important;
            padding: 24px 16px 20px 16px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            gap: 14px;
            transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.02);
            height: 100%;
            position: relative;
            overflow: hidden;
        }

        .luxury-category-card:hover {
            transform: translateY(-5px);
            border-color: var(--store-primary);
            box-shadow: 0 14px 30px rgba(212, 175, 55, 0.18);
            background-color: #ffffff;
        }

        .cat-avatar-pod {
            width: 80px;
            height: 80px;
            border-radius: 50% !important;
            overflow: hidden;
            background-color: var(--store-primary-light);
            border: 2px solid var(--store-border);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            transition: transform 0.3s ease;
        }

        .luxury-category-card:hover .cat-avatar-pod {
            transform: scale(1.08);
            border-color: var(--store-primary);
        }

        .cat-avatar-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .cat-title-text {
            font-size: 15px;
            font-weight: 700;
            color: var(--store-dark);
            margin: 0;
            line-height: 1.3;
            max-width: 100%;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .cat-action-chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 14px;
            border-radius: var(--store-radius-pill) !important;
            background-color: var(--store-primary-light);
            color: var(--store-primary-dark);
            border: 1px solid var(--store-border);
            font-size: 11.5px;
            font-weight: 700;
            transition: all 0.25s ease;
        }

        .luxury-category-card:hover .cat-action-chip {
            background-color: var(--store-primary);
            color: #ffffff !important;
            border-color: var(--store-primary);
        }

        [dir="rtl"] .cat-action-chip i {
            transform: rotate(180deg);
        }

        /* Dead-Centered Empty State */
        .categories-empty-pod {
            grid-column: 1 / -1;
            margin: auto 0;
            padding: 5rem 1rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            width: 100%;
        }

        .empty-cat-icon-pod {
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
            border: 1px solid var(--store-border);
        }

        .empty-cat-title {
            font-family: 'Gloock', serif;
            font-size: 22px;
            color: var(--store-dark);
            margin: 0 0 6px 0;
        }

        .empty-cat-sub {
            font-size: 14px;
            color: var(--store-text-muted);
            margin: 0;
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
            <div class="categories-container-box">

                {{-- Proportionate Breadcrumbs --}}
                <div class="luxury-breadcrumbs-box">
                    <a href="{{ $storeUrl }}" class="breadcrumb-link">
                        <i class="ti ti-home"></i>
                        <span>{{ __('Home') }}</span>
                    </a>
                    <i class="ti ti-chevron-right breadcrumb-sep"></i>
                    <span class="breadcrumb-current">{{ __('Categories') }}</span>
                </div>

                {{-- Header & Instant Live Filter Toolbar --}}
                <div class="categories-top-bar">
                    <h1 class="categories-page-title">{{ __('All Collections') }}</h1>

                    @if (count($categories) > 0)
                        <div class="categories-live-search-box">
                            <i class="ti ti-search cat-search-icon"></i>
                            <input type="text" id="catLiveFilterInput" class="cat-live-search-input"
                                placeholder="{{ __('Search collections...') }}"
                                onkeyup="filterCategoriesRealtime(this.value)" />
                        </div>
                    @endif
                </div>

                {{-- Categories Luxury Grid --}}
                @if (count($categories) > 0)
                    <div class="categories-luxury-grid" id="catGridContainer">
                        @foreach ($categories as $category)
                            <div class="category-card-col" data-cat-name="{{ strtolower($category->category_name) }}">
                                <a
                                    href="{{ $storeUrl . '?category=' . urlencode(strtolower($category->category_name)) }}">
                                    <div class="luxury-category-card">
                                        <div class="cat-avatar-pod">
                                            <img src="{{ url($category->thumbnail) }}"
                                                alt="{{ $category->category_name }}" class="cat-avatar-img"
                                                onerror="this.onerror=null;this.src='{{ $placeholder }}';" />
                                        </div>
                                        <h3 class="cat-title-text">{{ __($category->category_name) }}</h3>
                                        <span class="cat-action-chip">
                                            <span>{{ __('Explore') }}</span>
                                            <i class="ti ti-arrow-right"></i>
                                        </span>
                                    </div>
                                </a>
                            </div>
                        @endforeach

                        {{-- Realtime Search Filter Empty State (Hidden by Default) --}}
                        <div class="categories-empty-pod" id="categoriesLiveEmpty" style="display: none;">
                            <div class="empty-cat-icon-pod">
                                <i class="ti ti-search-off"></i>
                            </div>
                            <h3 class="empty-cat-title">{{ __('No Collections Found') }}</h3>
                            <p class="empty-cat-sub">{{ __('We could not find any category matching your search.') }}
                            </p>
                        </div>
                    </div>
                @else
                    {{-- Default Zero-Data Empty State --}}
                    <div class="categories-empty-pod">
                        <div class="empty-cat-icon-pod">
                            <i class="ti ti-category-off"></i>
                        </div>
                        <h3 class="empty-cat-title">{{ __('No Categories Found') }}</h3>
                        <p class="empty-cat-sub">
                            {{ __('Please check back again later as new collections are being added.') }}</p>
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

    {{-- Core JavaScript Bundle --}}
    @include('templates.stores.jewellery-shop.partials.cart-order-modal')
    @include('templates.stores.jewellery-shop.includes.scripts')

    <script>
        // Filter the category cards as the shopper types (called by the search box)
        function filterCategoriesRealtime(keyword) {
            const query = keyword.toLowerCase().trim();
            let visible = 0;

            document.querySelectorAll('.category-card-col').forEach(function(card) {
                const match = (card.getAttribute('data-cat-name') || '').includes(query);
                card.style.display = match ? '' : 'none';
                visible += match ? 1 : 0;
            });

            const empty = document.getElementById('categoriesLiveEmpty');
            if (empty) empty.style.display = visible === 0 ? 'flex' : 'none';
        }
    </script>
</body>

</html>
