<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    @include('templates.stores.cosmetics.includes.head')

    <style>
        /* Pure CSS Classes Replacing Inline Styles */
        .store-bg-wrapper {
            background-color: #ffdcdc;
        }

        .store-main-card {
            min-height: 100vh;
        }

        .card,
        input,
        select,

        .btn,
        .btn-rose,
        a[data-bs-toggle="offcanvas"] {
            border-radius: 10px !important;
            color: #ffffff;
            border: none;
        }

        /* Luxury Desktop Hero Card */
        .category-hero-banner {
            background: linear-gradient(135deg, #fff1f4 0%, #ffe4ec 50%, #fed7e2 100%);
            border-radius: 24px !important;
            border: 1px solid rgba(255, 255, 255, 0.8) !important;
            box-shadow: 0 10px 30px rgba(233, 30, 99, 0.08) !important;
            position: relative;
            overflow: hidden;
        }

        /* Luxury Category Card */
        .category-card {
            border-radius: 20px !important;
            border: 1px solid #f1f5f9 !important;
            background-color: #ffffff;
            transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1) !important;
            overflow: hidden;
            text-decoration: none !important;
            position: relative;
        }

        .category-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px -10px rgba(233, 30, 99, 0.15), 0 4px 15px rgba(0, 0, 0, 0.04) !important;
            border-color: #fbcfe8 !important;
        }

        /* Inner Soft Image Container */
        .category-img-box {
            background: linear-gradient(145deg, #fdf8f9 0%, #fbf1f4 100%);
            border-radius: 16px;
            overflow: hidden;
            position: relative;
        }

        .category-card:hover .category-img-box img {
            transform: scale(1.09);
        }

        .category-card .category-img-box img {
            transition: transform 0.4s ease;
        }

        /* Collection Count Pill - Pixel-Perfect Center Alignment */
        .collection-count-pill {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 6px !important;
            line-height: 1 !important;
            padding: 6px 16px !important;
            font-size: 13px !important;
            font-weight: 700 !important;
            border-radius: 9999px !important;
            background-color: #ffffff !important;
            color: #e91e63 !important;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06) !important;
            border: none !important;
        }

        .collection-count-pill i {
            font-size: 15px !important;
            line-height: 1 !important;
            display: inline-flex !important;
            align-items: center !important;
        }

        .collection-count-pill span {
            line-height: 1 !important;
            display: inline-block !important;
        }

        /* Collection Pill Badge */
        .collection-badge {
            position: absolute;
            top: 12px;
            right: 12px;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(4px);
            color: #e91e63;
            font-size: 11px;
            font-weight: 700;
            border-radius: 20px;
            padding: 4px 10px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.06);
            z-index: 2;
        }

        /* Action Pill Button */
        .category-action-btn {
            border-radius: 12px !important;
            font-size: 13px !important;
            font-weight: 600 !important;
            padding: 8px 16px !important;
            color: #475569 !important;
            background-color: #f8fafc !important;
            border: 1px solid #e2e8f0 !important;
            transition: all 0.25s ease !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 6px !important;
            width: 100% !important;
        }

        .category-card:hover .category-action-btn {
            background-color: #e91e63 !important;
            border-color: #e91e63 !important;
            color: #ffffff !important;
            box-shadow: 0 4px 12px rgba(233, 30, 99, 0.3) !important;
        }

    
    </style>

    @include('templates.stores.shared.custom-code')
</head>

<body dir="{{ App::isLocale('ar') || App::isLocale('ur') || App::isLocale('he') ? 'rtl' : 'ltr' }}">

    <!-- Modular Preloader -->
    @include('templates.stores.cosmetics.includes.preloader')

    {{-- Page Wrapper --}}
    <div id="wrapper" class="page m-0 min-h-screen store-bg-wrapper">
        <div class="page-wrapper m-0 p-0">
            <div class="page-body m-0 p-0">
                <div class="container-xl card rounded-0 p-0 overflow-hidden store-main-card">

                    <!-- Store Navbar Header -->
                    @include('templates.stores.cosmetics.includes.header', [
                        'bg' => 'rose',
                        'bg_color' => 'rose',
                        'badge_color' => 'rose',
                    ])

                    <!-- Redesigned Hero & Categories Section -->
                    <div class="col-md-12 mb-5 mt-7 px-3 px-lg-5">

                        <!-- Desktop & Mobile Redesigned Hero Banner -->
                        <div class="category-hero-banner p-4 p-md-5 mb-5 text-center">
                            <div class="mb-2 text-center">
                                <span class="collection-count-pill">
                                    <i class="ti ti-sparkles"></i>
                                    <span>{{ count($categories) }} {{ __('Collections Available') }}</span>
                                </span>
                            </div>

                            <h1 class="head fs-1 fw-bold text-dark mb-2">
                                {{ __('Explore Categories') }}
                            </h1>
                            <p class="text-secondary fs-4 max-w-lg mx-auto mb-4">
                                {{ __('Discover premium products organized into curated collections designed to elevate your lifestyle.') }}
                            </p>

                            <!-- Modern Search Input with Proper Padding -->
                            @if (count($categories) > 3)
                                <div class="col-12 col-md-8 col-lg-5 mx-auto">
                                    <div class="position-relative">
                                        <i class="ti ti-search fs-2 text-rose position-absolute"
                                            style="top: 50%; left: 16px; transform: translateY(-50%); pointer-events: none; z-index: 5;"></i>
                                        <input type="text" id="categorySearchInput"
                                            class="form-control rounded-pill border-0 py-3 fs-3 shadow-sm bg-white"
                                            style="padding-left: 48px !important; padding-right: 18px !important;"
                                            placeholder="{{ __('Search collections by name...') }}" autocomplete="off">
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- Redesigned Luxury Category Cards Grid -->
                        @if (count($categories) > 0)
                            <div class="row g-3 g-md-4 g-xl-4" id="categoriesGrid">
                                @foreach ($categories as $category)
                                    <div class="col-6 col-md-4 col-lg-3 category-item"
                                        data-name="{{ strtolower($category->category_name) }}">
                                        <a href="{{ $storeUrl . '?category=' . urlencode(strtolower($category->category_name)) }}"
                                            class="card category-card h-100 p-3 p-md-3.5 shadow-sm">

                                            <!-- Image Box with Corner Badge and 404 Fallback -->
                                            <div
                                                class="category-img-box ratio ratio-1x1 d-flex align-items-center justify-content-center p-3 mb-3">
                                                <img src="{{ url($category->thumbnail) }}"
                                                    alt="{{ $category->category_name }}"
                                                    class="img-fluid object-fit-contain w-100 h-100"
                                                    onerror="this.onerror=null; this.src='{{ asset('img/templates/all-categories.png') }}';">
                                            </div>

                                            <!-- Category Title -->
                                            <div class="text-center mb-3">
                                                <h3 class="fs-2 fw-bold text-dark mb-0 text-truncate"
                                                    title="{{ $category->category_name }}">
                                                    {{ __($category->category_name) }}
                                                </h3>
                                            </div>

                                            <!-- Interactive Action Pill Button -->
                                            <div class="mt-auto">
                                                <div class="category-action-btn">
                                                    <span>{{ __('View Products') }}</span>
                                                    <i class="ti ti-arrow-right fs-4"></i>
                                                </div>
                                            </div>

                                        </a>
                                    </div>
                                @endforeach
                            </div>

                            <!-- No Results on Search Message -->
                            <div id="noSearchCategories" class="col-12 py-5 text-center d-none">
                                <i class="ti ti-folder-x fs-1 text-muted d-block mb-2"></i>
                                <h3 class="fs-2 text-muted">{{ __('No matching collections found.') }}</h3>
                            </div>
                        @else
                            <div class="col-12 py-5 text-center">
                                <i class="ti ti-package-off fs-1 text-muted d-block mb-2"></i>
                                <h3 class="fs-1 text-muted">{{ __('No Categories Found') }}</h3>
                            </div>
                        @endif

                    </div>

                    {{-- Modular Footer Include --}}
                    @include('templates.stores.cosmetics.includes.footer')
                </div>
            </div>
        </div>
    </div> <!-- end wrapper -->

    <!-- Mobile Bottom Navigation (Outside wrapper with safe bg variables) -->
    @include('templates.stores.cosmetics.includes.bottom-bar', [
        'color' => 'rose',
        'bg' => 'light',
        'bg_color' => 'rose',
    ])

    @include('templates.stores.cosmetics.partials.cart-order-modal')

    @if ($plan_details != null && $plan_details['pwa'] == 1 && $business_card_details->is_enable_pwa == 1)
        @include('templates.stores.cosmetics.includes.pwa-modal')
    @endif

    @include('templates.stores.cosmetics.includes.whatsapp-float', [
        'businessImage' => $business_card_details->profile,
        'businessName' => $card_details->title,
        'whatsappNumber' => $enquiry_button,
    ])

    @include('templates.stores.cosmetics.includes.scripts')

    <script>
        // Filter the category list as the shopper types
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('categorySearchInput');
            if (!searchInput) return;

            searchInput.addEventListener('input', function(event) {
                const query = event.target.value.toLowerCase().trim();
                let visible = 0;

                document.querySelectorAll('.category-item').forEach(function(item) {
                    const match = item.getAttribute('data-name').includes(query);
                    item.style.display = match ? 'block' : 'none';
                    visible += match ? 1 : 0;
                });

                document.getElementById('noSearchCategories')?.classList.toggle('d-none', visible > 0);
            });
        });
    </script>
</body>

</html>
