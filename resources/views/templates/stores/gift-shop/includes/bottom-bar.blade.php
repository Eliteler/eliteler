<style>
    .store-bottom-nav {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        height: 75px;
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border-top: 1px solid #f1f5f9;
        display: flex;
        align-items: center;
        justify-content: space-around;
        z-index: 1040;
        box-shadow: 0 -5px 20px rgba(0, 0, 0, 0.04);
    }

    @media (min-width: 992px) {
        .store-bottom-nav {
            display: none;
        }
    }

    .bottom-nav-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        color: #78716c;
        font-size: 0.75rem;
        font-weight: 600;
        gap: 4px;
        flex: 1;
        height: 100%;
        position: relative;
        background: none;
        border: 0;
        padding: 0;
        font-family: inherit;
    }

    .bottom-nav-item.active,
    .bottom-nav-item:hover {
        color: #dc2626;
    }

    .bottom-nav-icon {
        font-size: 1.4rem;
    }

    .bottom-nav-badge {
        position: absolute;
        top: 12px;
        right: calc(50% - 18px);
        width: 17px;
        height: 17px;
        border-radius: 50%;
        background: #dc2626;
        color: #ffffff;
        font-size: 0.6rem;
        font-weight: 800;
        display: flex;
        align-items: center;
        justify-content: center;
    }
</style>

<nav class="store-bottom-nav">
    {{-- Shop/Home --}}
    <a href="{{ $storeUrl }}"
        class="bottom-nav-item {{ !request()->has('category') && $storefront->onHome() ? 'active' : '' }}">
        <i class="ti ti-home bottom-nav-icon"></i>
        <span>{{ __('Shop') }}</span>
    </a>

    {{-- Categories --}}
    <a href="{{ $storefront->categoriesUrl }}" class="bottom-nav-item {{ $storefront->onCategories() ? 'active' : '' }}">
        <i class="ti ti-category bottom-nav-icon"></i>
        <span>{{ __('Categories') }}</span>
    </a>

    {{-- Cart Drawer Trigger --}}
    <button type="button" class="bottom-nav-item" data-cart-open>
        <i class="ti ti-shopping-bag bottom-nav-icon"></i>
        <span>{{ __('Cart') }}</span>
        <span class="bottom-nav-badge" data-cart-count data-cart-when-filled hidden>0</span>
    </button>
</nav>
