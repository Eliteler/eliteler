{{-- Bottom Navbar Component with Adaptive Cart Badge (Mobile) --}}
<div class="luxury-bottom-bar d-lg-none">
    <div class="bottom-bar-grid">
        <!-- Shop -->
        <a href="{{ $storeUrl }}" class="bottom-bar-item {{ $storefront->onCategories() ? '' : 'active' }}">
            <div class="bottom-bar-icon-wrap">
                <i class="ti ti-building-store"></i>
            </div>
            <span class="bottom-bar-label">{{ __('Shop') }}</span>
        </a>

        <!-- Categories -->
        <a href="{{ $storefront->categoriesUrl }}" class="bottom-bar-item {{ $storefront->onCategories() ? 'active' : '' }}">
            <div class="bottom-bar-icon-wrap">
                <i class="ti ti-category"></i>
            </div>
            <span class="bottom-bar-label">{{ __('Categories') }}</span>
        </a>

        <!-- Cart trigger -->
        <button type="button" class="bottom-bar-item bottom-bar-cart-btn" data-cart-open>
            <div class="bottom-bar-icon-wrap position-relative">
                <i class="ti ti-shopping-bag"></i>
                <span class="bottom-cart-badge" data-cart-count data-cart-when-filled hidden>0</span>
            </div>
            <span class="bottom-bar-label">{{ __('Cart') }}</span>
        </button>
    </div>
</div>

<style>
    .luxury-bottom-bar {
        position: fixed;
        bottom: 0;
        left: 0;
        width: 100%;
        height: 75px;
        background: #ffffff;
        border-top: 1px solid rgba(212, 175, 55, 0.25);
        box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.08);
        z-index: 1030;
    }

    .bottom-bar-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        height: 100%;
        max-width: 500px;
        margin: 0 auto;
    }

    .bottom-bar-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 4px;
        color: #7a6a4c !important;
        text-decoration: none !important;
        background: transparent;
        border: none;
        cursor: pointer;
        transition: color 0.2s;
    }

    .bottom-bar-item:hover,
    .bottom-bar-item.active {
        color: #d4af37 !important;
    }

    .bottom-bar-icon-wrap {
        font-size: 22px;
        line-height: 1;
        position: relative;
    }

    .bottom-bar-label {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    /* Adaptive Bottom Cart Badge */
    .bottom-cart-badge {
        position: absolute;
        top: -6px;
        right: -10px;
        min-width: 19px;
        height: 19px;
        padding: 0 4px;
        border-radius: 50px !important;
        background: #dc2626;
        color: #ffffff;
        font-size: 10px;
        font-weight: 800;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 2px solid #ffffff;
        line-height: 1;
        white-space: nowrap;
        box-sizing: border-box;
    }
</style>
