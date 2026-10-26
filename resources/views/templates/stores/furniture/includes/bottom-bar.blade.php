<style>
    .store-bottom-bar {
        position: fixed;
        bottom: 0;
        left: 0;
        width: 100%;
        height: 75px;
        background: #ffffff;
        border-top: 1px solid #f1ede4;
        display: flex;
        align-items: center;
        justify-content: space-around;
        z-index: 1030;
        box-shadow: 0 -4px 15px rgba(0, 0, 0, 0.05);
    }

    .bottom-bar-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        color: #64748b;
        font-size: 11px;
        font-weight: 600;
        text-decoration: none;
        flex: 1;
        height: 100%;
        position: relative;
        transition: color 0.2s ease;
        background: none;
        border: 0;
        padding: 0;
        font-family: inherit;
    }

    .bottom-bar-item.active,
    .bottom-bar-item:hover {
        color: #d97706;
    }

    .bottom-bar-icon {
        font-size: 22px;
        margin-bottom: 2px;
    }

    /* Fixed Bottom Cart Badge with Proper Visibility Logic */
    .bottom-cart-badge {
        position: absolute;
        top: 6px;
        left: 52%;
        background: #dc2626;
        color: #ffffff;
        font-size: 10px;
        font-weight: 700;
        min-width: 18px;
        height: 18px;
        border-radius: 9px;
        align-items: center;
        justify-content: center;
        text-align: center;
        line-height: 1;
        padding: 0 4px;
        border: 2px solid #ffffff;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
        pointer-events: none;
    }

    /* Hide when empty or attribute hidden exists */
    .bottom-cart-badge[hidden] {
        display: none !important;
    }

    /* Reveal dynamically with flex alignment when filled */
    .bottom-cart-badge:not([hidden]) {
        display: inline-flex !important;
    }
</style>

<div class="store-bottom-bar d-md-none">
    {{-- Shop / Catalog --}}
    <a href="{{ $storeUrl }}"
        class="bottom-bar-item {{ $storefront->onHome() && !request()->has('category') ? 'active' : '' }}">
        <i class="ti ti-home bottom-bar-icon"></i>
        <span>{{ __('Shop') }}</span>
    </a>

    {{-- Categories --}}
    <a href="{{ $storefront->categoriesUrl }}"
        class="bottom-bar-item {{ $storefront->onCategories() || request()->has('category') ? 'active' : '' }}">
        <i class="ti ti-layout-grid bottom-bar-icon"></i>
        <span>{{ __('Categories') }}</span>
    </a>

    {{-- Cart Trigger --}}
    <button type="button" class="bottom-bar-item" data-cart-open>
        <i class="ti ti-shopping-bag bottom-bar-icon"></i>
        <span>{{ __('Cart') }}</span>
        <span class="bottom-cart-badge" data-cart-count data-cart-when-filled hidden>0</span>
    </button>
</div>
