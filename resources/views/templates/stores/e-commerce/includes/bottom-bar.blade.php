<div class="store-bottom-nav d-lg-none fixed-bottom">
    <div class="row g-0 text-center store-bottom-nav-inner">
        <!-- 1. Shop Home -->
        <div class="col">
            <a href="{{ $storeUrl }}"
                class="store-bottom-nav-link {{ $storefront->onHome() && !request()->has('category') ? 'active text-' . $color : 'text-secondary' }}">
                <i class="ti ti-smart-home fs-1 d-block mb-1"></i>
                <span class="store-bottom-nav-text">{{ __('Shop') }}</span>
            </a>
        </div>

        <!-- 2. Categories -->
        <div class="col">
            <a href="{{ $storefront->categoriesUrl }}"
                class="store-bottom-nav-link {{ $storefront->onCategories() || request()->has('category') ? 'active text-' . $color : 'text-secondary' }}">
                <i class="ti ti-category fs-1 d-block mb-1"></i>
                <span class="store-bottom-nav-text">{{ __('Categories') }}</span>
            </a>
        </div>

        <!-- 3. Mobile Cart with Pixel-Perfect Badge Wrapper -->
        <div class="col">
            <a href="#offcanvasEnd" role="button" class="store-bottom-nav-link text-secondary" data-cart-open>
                <div class="mobile-cart-icon-wrapper">
                    <i class="ti ti-shopping-bag fs-1 d-block mb-1"></i>
                    <!-- Symmetrical Centered Circular Mobile Badge -->
                    <span class="store-mobile-badge" data-cart-count data-cart-when-filled hidden>0</span>
                </div>
                <span class="store-bottom-nav-text">{{ __('Cart') }}</span>
            </a>
        </div>
    </div>
</div>

<style>
    /* 75px Tall Mobile Bottom Navigation Bar */
    .store-bottom-nav {
        background-color: #ffffff;
        box-shadow: 0 -3px 12px rgba(0, 0, 0, 0.08);
        z-index: 1040;
        height: 75px;
    }

    .store-bottom-nav-inner {
        height: 75px;
        display: flex;
        align-items: center;
    }

    .store-bottom-nav-link {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        height: 100%;
        padding: 4px 0;
    }

    .store-bottom-nav-text {
        font-size: 0.78rem;
        font-weight: 600;
        display: block;
        line-height: 1.1;
    }

    /* Isolated Icon Wrapper for Exact Corner Docking */
    .mobile-cart-icon-wrapper {
        position: relative;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    /* Mathematical Circular Mobile Count Badge */
    .store-mobile-badge {
        position: absolute !important;
        top: -4px !important;
        right: -8px !important;
        width: 18px !important;
        height: 18px !important;
        box-sizing: border-box !important;
        background-color: #d63939 !important;
        color: #ffffff !important;
        font-size: 10px !important;
        font-weight: 800 !important;
        line-height: 1 !important;
        border-radius: 50% !important;
        border: 2px solid #ffffff !important;
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.25) !important;
        align-items: center !important;
        justify-content: center !important;
        pointer-events: none !important;
    }

    /* Show only when not hidden */
    .store-mobile-badge[hidden] {
        display: none !important;
    }

    .store-mobile-badge:not([hidden]) {
        display: flex !important;
    }
</style>
