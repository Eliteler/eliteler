{{-- Master Categories View for Fashion Store --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">

<head>
    @include('templates.stores.fashion.includes.head')

    <style>
        /* Luxury Breadcrumb Banner */
        .fashion-page-header {
            background-color: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            padding: 24px 0;
            margin-bottom: 30px;
        }

        .fashion-breadcrumb {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            font-weight: 600;
            color: #64748b;
            margin-bottom: 8px;
        }

        .fashion-breadcrumb a {
            color: #64748b;
            transition: color 0.2s ease;
        }

        .fashion-breadcrumb a:hover {
            color: #0f172a;
        }

        .fashion-page-title {
            font-family: 'Oswald', sans-serif;
            font-size: 28px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #0f172a;
            margin: 0;
        }

        /* Live Category Search Box */
        .fashion-cat-search-box {
            position: relative;
            width: 100%;
            max-width: 380px;
        }

        .fashion-cat-search-input {
            height: 46px;
            border-radius: 0px;
            border: 1px solid #cbd5e1;
            padding-left: 42px;
            padding-right: 16px;
            font-size: 14px;
            font-weight: 500;
            background-color: #ffffff;
            transition: all 0.2s ease;
        }

        .fashion-cat-search-input:focus {
            border-color: #0f172a;
            box-shadow: 0 0 0 2px rgba(15, 23, 42, 0.08);
            outline: none;
        }

        .fashion-cat-search-icon {
            position: absolute;
            top: 50%;
            left: 14px;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 18px;
            pointer-events: none;
        }

        /* Responsive Categories Grid */
        .fashion-categories-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 16px;
            width: 100%;
        }

        @media (min-width: 768px) {
            .fashion-categories-grid {
                grid-template-columns: repeat(3, 1fr);
                gap: 20px;
            }
        }

        @media (min-width: 992px) {
            .fashion-categories-grid {
                grid-template-columns: repeat(4, 1fr);
                gap: 22px;
            }
        }

        @media (min-width: 1200px) {
            .fashion-categories-grid {
                grid-template-columns: repeat(5, 1fr);
                gap: 24px;
            }
        }

        /* Luxury Category Card */
        .fashion-cat-item-card {
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 0px;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            height: 100%;
        }

        .fashion-cat-item-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 28px rgba(0, 0, 0, 0.08);
            border-color: #0f172a;
        }

        .fashion-cat-img-wrap {
            position: relative;
            width: 100%;
            aspect-ratio: 1 / 1;
            background-color: #f1f5f9;
            overflow: hidden;
        }

        .fashion-cat-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.4s ease;
        }

        .fashion-cat-item-card:hover .fashion-cat-img {
            transform: scale(1.06);
        }

        .fashion-cat-content {
            padding: 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background-color: #ffffff;
            border-top: 1px solid #f1f5f9;
        }

        .fashion-cat-title {
            font-family: 'Oswald', sans-serif;
            font-size: 16px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #0f172a;
            margin: 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .fashion-cat-arrow {
            width: 32px;
            height: 32px;
            border-radius: 0px;
            background-color: #f8fafc;
            color: #0f172a;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            transition: all 0.2s ease;
        }

        .fashion-cat-item-card:hover .fashion-cat-arrow {
            background-color: #0f172a;
            color: #ffffff;
            transform: translateX(3px);
        }

        /* Empty State */
        .fashion-empty-box {
            text-align: center;
            padding: 60px 20px;
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
        }
    </style>

    @include('templates.stores.shared.custom-code')
</head>

<body dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
    {{-- 1. Preloader --}}
    @include('templates.stores.fashion.includes.preloader')

    {{-- 2. Header --}}
    @include('templates.stores.fashion.includes.header')

    {{-- 3. Breadcrumb & Page Banner --}}
    <div class="fashion-page-header">
        <div class="container-xl">
            <div class="row align-items-center gy-3">
                <div class="col-md-6">
                    <div class="fashion-breadcrumb">
                        <a href="{{ $storeUrl }}">{{ __('Home') }}</a>
                        <i class="ti ti-chevron-right fs-4"></i>
                        <span>{{ __('Categories') }}</span>
                    </div>
                    <h1 class="fashion-page-title">{{ __('Explore All Categories') }}</h1>
                </div>

                <!-- Instant Live Category Filter -->
                <div class="col-md-6 d-flex justify-content-md-end">
                    <div class="fashion-cat-search-box">
                        <i class="ti ti-search fashion-cat-search-icon"></i>
                        <input type="text" id="categoryLiveSearchInput" class="form-control fashion-cat-search-input"
                            placeholder="{{ __('Filter categories...') }}" onkeyup="filterCategoryCards(this.value)">
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 4. Main Category Catalog Grid --}}
    <main class="container-xl pb-5">
        @if (count($categories) > 0)
            <div class="fashion-categories-grid" id="categoriesGridContainer">
                <!-- All Products Category Card -->
                <div class="fashion-cat-item-node" data-name="{{ strtolower(__('All Products')) }}">
                    <a href="{{ $storeUrl }}" class="fashion-cat-item-card">
                        <div class="fashion-cat-img-wrap">
                            <img src="{{ asset('img/templates/all-categories.png') }}" class="fashion-cat-img"
                                alt="All Products">
                        </div>
                        <div class="fashion-cat-content">
                            <h3 class="fashion-cat-title">{{ __('All Products') }}</h3>
                            <div class="fashion-cat-arrow">
                                <i class="ti ti-arrow-right"></i>
                            </div>
                        </div>
                    </a>
                </div>

                <!-- Dynamic Categories -->
                @foreach ($categories as $category)
                    @php
                        $catSlug = urlencode(strtolower($category->category_name));
                        $catThumb = !empty($category->thumbnail)
                            ? url($category->thumbnail)
                            : asset('img/templates/all-categories.png');
                    @endphp
                    <div class="fashion-cat-item-node" data-name="{{ strtolower($category->category_name) }}">
                        <a href="{{ $storeUrl . '?category=' . $catSlug }}" class="fashion-cat-item-card">
                            <div class="fashion-cat-img-wrap">
                                <img src="{{ $catThumb }}" class="fashion-cat-img"
                                    alt="{{ $category->category_name }}">
                            </div>
                            <div class="fashion-cat-content">
                                <h3 class="fashion-cat-title">{{ $category->category_name }}</h3>
                                <div class="fashion-cat-arrow">
                                    <i class="ti ti-arrow-right"></i>
                                </div>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>

            <!-- No Search Results Matching Alert -->
            <div id="noCatSearchMatch" class="fashion-empty-box d-none mt-4">
                <i class="ti ti-search-off fs-1 text-muted mb-2 d-block"></i>
                <h4 class="head fs-2 text-dark">{{ __('No matching categories found') }}</h4>
                <p class="text-muted">{{ __('Try searching with different keywords.') }}</p>
                <button type="button" class="btn btn-dark mt-2"
                    onclick="resetCategorySearch()">{{ __('Clear Search') }}</button>
            </div>
        @else
            <div class="fashion-empty-box">
                <i class="ti ti-category-2 fs-1 text-muted mb-2 d-block"></i>
                <h3 class="head fs-1 text-muted">{{ __('No Categories Found') }}</h3>
                <a href="{{ $storeUrl }}" class="btn btn-dark mt-3">{{ __('Return to Shop') }}</a>
            </div>
        @endif
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

    <script>
        // Filter the category cards as the shopper types (called by the search box)
        function filterCategoryCards(query) {
            const term = query.toLowerCase().trim();
            let visible = 0;

            document.querySelectorAll('.fashion-cat-item-node').forEach(function(node) {
                const match = (node.getAttribute('data-name') || '').includes(term);
                node.style.display = match ? 'block' : 'none';
                visible += match ? 1 : 0;
            });

            const noMatch = document.getElementById('noCatSearchMatch');
            if (noMatch) noMatch.classList.toggle('d-none', visible !== 0);
        }

        // Clear the search box and show every category again (empty-state button)
        function resetCategorySearch() {
            const input = document.getElementById('categoryLiveSearchInput');
            if (!input) return;
            input.value = '';
            filterCategoryCards('');
        }
    </script>
</body>

</html>
