<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">

<head>
    @include('templates.stores.e-commerce.includes.head')

    <style>
        .card,
        .form-control,
        .rounded-e-commerce,
        .alert,
        input,
        select {
            border-radius: 0px !important;
        }

        /* Top Banner & Breadcrumbs */
        .categories-hero-banner {
            background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
            border-radius: 20px;
            border: 1px solid #edf2f7;
            padding: 22px 20px;
            margin-bottom: 24px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.02);
        }

        .categories-breadcrumbs {
            font-size: 0.85rem;
            font-weight: 600;
            color: #64748b;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            line-height: 1;
        }

        .categories-breadcrumbs a {
            color: #206bc4;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
        }

        .breadcrumb-separator {
            font-size: 0.75rem;
            color: #94a3b8;
        }

        .categories-count-badge {
            display: inline-flex;
            align-items: center;
            padding: 5px 12px;
            font-size: 0.78rem;
            font-weight: 700;
            color: #206bc4;
            background-color: #e8f2fd;
            border: 1px solid #cfe2ff;
            border-radius: 50px;
            white-space: nowrap !important;
            line-height: 1;
        }

        .categories-hero-title {
            font-family: 'Outfit', sans-serif;
            font-size: 1.75rem;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: -0.4px;
            line-height: 1.2;
        }

        .categories-hero-subtitle {
            font-size: 0.9rem;
            color: #64748b;
            line-height: 1.4;
        }

        /* Search Box */
        .categories-search-box {
            position: relative;
            width: 100%;
        }

        .categories-search-input {
            height: 46px !important;
            border-radius: 12px !important;
            border: 1.5px solid #e2e8f0 !important;
            padding-left: 42px !important;
            padding-right: 16px !important;
            font-size: 0.92rem !important;
            background-color: #ffffff !important;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02) !important;
        }

        .categories-search-input:focus {
            border-color: #206bc4 !important;
            box-shadow: 0 0 0 3px rgba(32, 107, 196, 0.15) !important;
        }

        .categories-search-icon {
            position: absolute;
            top: 50%;
            left: 14px;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 1.2rem;
            pointer-events: none;
        }

        /* Category Grid Card */
        .luxury-cat-card {
            background-color: #ffffff;
            border: 1.5px solid #edf2f7;
            border-radius: 20px;
            padding: 16px 14px;
            text-decoration: none !important;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: space-between;
            height: 100%;
            box-shadow: 0 4px 18px rgba(0, 0, 0, 0.03);
            transition: transform 0.28s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.28s ease, border-color 0.28s ease;
            position: relative;
            overflow: hidden;
        }

        .luxury-cat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 14px 30px rgba(32, 107, 196, 0.12);
            border-color: #206bc4;
        }

        .luxury-cat-img-box {
            width: 100%;
            aspect-ratio: 1 / 1;
            border-radius: 16px;
            background: linear-gradient(135deg, #f8fafc 0%, #edf2f7 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 14px;
            border: 1px solid #f1f5f9;
            overflow: hidden;
            transition: background 0.25s ease;
        }

        .luxury-cat-card:hover .luxury-cat-img-box {
            background: linear-gradient(135deg, #e8f2fd 0%, #f0f7ff 100%);
        }

        .luxury-cat-img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .luxury-cat-card:hover .luxury-cat-img {
            transform: scale(1.08);
        }

        .luxury-cat-title {
            font-family: 'Outfit', sans-serif;
            font-size: 1.1rem;
            font-weight: 700;
            color: #1e293b;
            margin-top: 12px;
            margin-bottom: 2px;
            text-align: center;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            width: 100%;
            transition: color 0.2s ease;
        }

        .luxury-cat-card:hover .luxury-cat-title {
            color: #206bc4;
        }

        .luxury-cat-action {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 0.78rem;
            font-weight: 700;
            color: #64748b;
            margin-top: 4px;
            transition: color 0.2s ease, transform 0.2s ease;
        }

        .luxury-cat-card:hover .luxury-cat-action {
            color: #206bc4;
            transform: translateX(2px);
        }

        /* ========================================================= */
        /* Redesigned Luxury Empty State Card                        */
        /* ========================================================= */
        .category-empty-state-card {
            background-color: #ffffff;
            border-radius: 22px;
            border: 1.5px dashed #cbd5e1;
            padding: 45px 20px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.02);
        }

        .category-empty-icon-box {
            width: 76px;
            height: 76px;
            border-radius: 50%;
            background-color: #e8f2fd;
            color: #206bc4;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.2rem;
            border: 4px solid #ffffff;
            box-shadow: 0 4px 15px rgba(32, 107, 196, 0.15);
        }

        .category-clear-btn {
            height: 44px !important;
            border-radius: 12px !important;
            padding: 0 24px !important;
            font-weight: 700 !important;
            font-size: 0.95rem !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            box-shadow: 0 4px 14px rgba(32, 107, 196, 0.3) !important;
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

                    <div class="col-md-12 my-3 px-3 px-lg-4">

                        <!-- Top Breadcrumbs & Search Banner -->
                        <div class="categories-hero-banner">
                            <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
                                <div class="categories-breadcrumbs">
                                    <a href="{{ $storeUrl }}">
                                        <i class="ti ti-smart-home me-1"></i>{{ __('Home') }}
                                    </a>
                                    <i class="ti ti-chevron-right breadcrumb-separator"></i>
                                    <span>{{ __('Categories') }}</span>
                                </div>

                                <span class="categories-count-badge">
                                    <i class="ti ti-category me-1"></i>{{ count($categories) }} {{ __('Categories') }}
                                </span>
                            </div>

                            <div class="my-2">
                                <h1 class="categories-hero-title mb-1">{{ __('Explore Categories') }}</h1>
                                <p class="categories-hero-subtitle mb-3">
                                    {{ __('Browse our curated catalog by category to find what you need.') }}
                                </p>
                            </div>

                            <div class="categories-search-box">
                                <i class="ti ti-search categories-search-icon"></i>
                                <input type="text" id="categorySearchInput"
                                    class="form-control categories-search-input"
                                    placeholder="{{ __('Search categories...') }}"
                                    onkeyup="filterCategoriesLive(this.value)">
                            </div>
                        </div>

                        <!-- Categories Grid -->
                        @if (count($categories) > 0)
                            <div class="row g-3 g-lg-4 mb-4" id="categoriesGridContainer">
                                @foreach ($categories as $category)
                                    @php
                                        $catUrl =
                                            $storeUrl . '?category=' . urlencode(strtolower($category->category_name));
                                    @endphp
                                    <div class="col-6 col-md-4 col-lg-3 category-item-node"
                                        data-name="{{ strtolower($category->category_name) }}">
                                        <a href="{{ $catUrl }}" class="luxury-cat-card">
                                            <div class="luxury-cat-img-box">
                                                <img src="{{ url($category->thumbnail) }}"
                                                    alt="{{ $category->category_name }}" class="luxury-cat-img"
                                                    onerror="this.onerror=null; this.src='{{ asset('img/templates/all-categories.png') }}';">
                                            </div>

                                            <h3 class="luxury-cat-title">
                                                {{ __($category->category_name) }}
                                            </h3>

                                            <div class="luxury-cat-action">
                                                <span>{{ __('View Products') }}</span>
                                                <i class="ti ti-arrow-right fs-4"></i>
                                            </div>
                                        </a>
                                    </div>
                                @endforeach
                            </div>

                            <!-- Redesigned High-End Live Filter Empty State -->
                            <div id="noSearchCategories" class="text-center category-empty-state-card my-3 d-none">
                                <div class="category-empty-icon-box mx-auto mb-3">
                                    <i class="ti ti-packages"></i>
                                </div>
                                <h3 class="fs-1 fw-bold text-dark mb-1">{{ __('No Matching Categories Found') }}</h3>
                                <p class="text-muted fs-3 mb-4">
                                    {{ __('We couldn\'t find any category matching your search. Try checking for typos or searching a different term.') }}
                                </p>
                                <button type="button" class="btn btn-e-commerce-blue category-clear-btn text-white"
                                    onclick="clearCategorySearch()">
                                    <i class="ti ti-rotate-clockwise me-1"></i> {{ __('Clear Search & View All') }}
                                </button>
                            </div>
                        @else
                            <div class="text-center py-5 my-4 bg-white rounded-4 border p-4 shadow-sm">
                                <div class="mb-3">
                                    <i class="ti ti-category fs-1 text-muted" style="font-size: 3.5rem !important;"></i>
                                </div>
                                <h3 class="fs-1 fw-bold text-dark mb-1">{{ __('No Categories Found') }}</h3>
                                <p class="text-muted fs-3 mb-4">{{ __('Categories have not been added yet.') }}</p>
                                <a href="{{ $storeUrl }}" class="btn btn-e-commerce-blue px-4 py-2"
                                    style="border-radius: 12px !important;">
                                    <i class="ti ti-arrow-left me-1"></i> {{ __('Back to Shop') }}
                                </a>
                            </div>
                        @endif

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
        // Filter the category cards as the shopper types (called by the search box)
        function filterCategoriesLive(query) {
            const term = query.toLowerCase().trim();
            let visible = 0;

            document.querySelectorAll('.category-item-node').forEach(function(item) {
                const match = (item.getAttribute('data-name') || '').includes(term);
                item.style.setProperty('display', match ? 'block' : 'none', 'important');
                visible += match ? 1 : 0;
            });

            const noResults = document.getElementById('noSearchCategories');
            if (noResults) noResults.classList.toggle('d-none', !(visible === 0 && term !== ''));
        }

        // Clear the search box and show every category again (empty-state button)
        function clearCategorySearch() {
            const input = document.getElementById('categorySearchInput');
            if (!input) return;
            input.value = '';
            filterCategoriesLive('');
            input.focus();
        }
    </script>
</body>

</html>
