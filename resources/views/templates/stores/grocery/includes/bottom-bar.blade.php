{{-- Luxury 75px Symmetrical Bottom Navigation Bar with Dynamic Badge Geometry --}}
<nav class="store-bottom-bar-nav d-lg-none">
    <div class="store-bottom-bar-container">
        <!-- Shop Action -->
        <a href="{{ $storeUrl }}"
            class="store-bottom-tab {{ $storefront->onHome() && !request()->has('category') ? 'active' : '' }}">
            <div class="store-bottom-icon-pod">
                <i class="ti ti-building-store"></i>
            </div>
            <span class="store-bottom-label">{{ __('Shop') }}</span>
        </a>

        <!-- Categories Action -->
        <a href="{{ $storefront->categoriesUrl }}"
            class="store-bottom-tab {{ $storefront->onCategories() || request()->has('category') ? 'active' : '' }}">
            <div class="store-bottom-icon-pod">
                <i class="ti ti-category-2"></i>
            </div>
            <span class="store-bottom-label">{{ __('Categories') }}</span>
        </a>

        <!-- Cart Action -->
        <button type="button" class="store-bottom-tab" data-cart-open>
            <div class="store-bottom-icon-pod position-relative">
                <i class="ti ti-shopping-cart"></i>
                <span class="store-bottom-cart-badge" data-cart-count data-cart-when-filled hidden>0</span>
            </div>
            <span class="store-bottom-label">{{ __('Cart') }}</span>
        </button>
    </div>
</nav>

<style>
    /* Bottom Navigation Styles */
    .store-bottom-bar-nav {
        position: fixed;
        bottom: 0;
        left: 0;
        width: 100%;
        height: 75px;
        background: #ffffff;
        border-top: 1px solid #c8e6c9;
        z-index: 1010;
        box-shadow: 0 -4px 16px rgba(0, 0, 0, 0.06);
    }

    .store-bottom-bar-container {
        height: 100%;
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        align-items: center;
    }

    .store-bottom-tab {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 3px;
        color: #616161;
        height: 100%;
        transition: color 0.2s ease;
        background: none;
        border: 0;
        padding: 0;
        font-family: inherit;
    }

    .store-bottom-tab.active {
        color: #2e7d32;
    }

    .store-bottom-icon-pod {
        font-size: 22px;
        display: flex;
        align-items: center;
        justify-content: center;
        height: 28px;
    }

    .store-bottom-label {
        font-size: 11px;
        font-weight: 600;
        letter-spacing: 0.2px;
    }

    /* Fixed Padding & Dynamic Responsive Geometry for Double-Digit Cart Counts */
    .store-bottom-cart-badge {
        position: absolute;
        top: -4px;
        right: -10px;
        min-width: 19px;
        height: 19px;
        padding: 0 5px;
        border-radius: 50px !important;
        background-color: #d32f2f;
        color: #ffffff;
        font-size: 10px;
        font-weight: 700;
        line-height: 1;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 2px solid #ffffff;
        box-sizing: border-box;
    }

    /* RTL Direction Alignment */
    [dir="rtl"] .store-bottom-cart-badge {
        right: auto;
        left: -10px;
    }
</style>
