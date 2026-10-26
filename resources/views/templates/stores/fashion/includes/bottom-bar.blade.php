{{-- Bottom Bar Component --}}
<style>
    .fashion-bottom-bar {
        position: fixed;
        bottom: 0;
        left: 0;
        width: 100%;
        height: 75px;
        background-color: #111827;
        display: flex;
        align-items: center;
        justify-content: space-around;
        z-index: 1040;
        border-top: 1px solid rgba(255, 255, 255, 0.1);
        box-shadow: 0 -4px 10px rgba(0, 0, 0, 0.15);
    }

    .fashion-bar-item {
        flex: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        color: #9ca3af;
        text-decoration: none;
        font-size: 12px;
        font-weight: 500;
        height: 100%;
        cursor: pointer;
        border: none;
        background: transparent;
        transition: color 0.2s ease;
    }

    .fashion-bar-item:hover,
    .fashion-bar-item.active {
        color: #ffffff;
    }

    .mobile-cart-icon-wrapper {
        position: relative;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .mobile-bottom-cart-badge {
        position: absolute;
        top: -6px;
        right: -8px;
        width: 18px;
        height: 18px;
        line-height: 1;
        border-radius: 50%;
        border: 2px solid #111827;
        background-color: #dc2626;
        color: #ffffff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 10px;
        font-weight: 800;
    }

    @media (min-width: 992px) {
        .fashion-bottom-bar {
            display: none !important;
        }
    }
</style>

<div class="fashion-bottom-bar d-lg-none">
    <!-- Shop / Home -->
    <a href="{{ $storeUrl }}" class="fashion-bar-item {{ $storefront->onHome() && !request('category') ? 'active' : '' }}">
        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none"
            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
            <path d="M3 21l18 0" />
            <path d="M3 7v1a3 3 0 0 0 6 0v-1m0 1a3 3 0 0 0 6 0v-1m0 1a3 3 0 0 0 6 0v-1h-18l2 -4h14l2 4" />
            <path d="M5 21l0 -10.15" />
            <path d="M19 21l0 -10.15" />
            <path d="M9 21v-4a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v4" />
        </svg>
        <span>{{ __('Shop') }}</span>
    </a>

    <!-- Categories -->
    <a href="{{ $storefront->categoriesUrl }}" class="fashion-bar-item {{ $storefront->onCategories() || request('category') ? 'active' : '' }}">
        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none"
            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
            <path d="M4 4h6v6h-6z" />
            <path d="M14 4h6v6h-6z" />
            <path d="M4 14h6v6h-6z" />
            <path d="M14 14h6v6h-6z" />
        </svg>
        <span>{{ __('Categories') }}</span>
    </a>

    <!-- Cart Drawer Trigger -->
    <button type="button" class="fashion-bar-item" data-cart-open>
        <div class="mobile-cart-icon-wrapper">
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                <path d="M6 19m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" />
                <path d="M17 19m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" />
                <path d="M17 17h-11v-14h-2" />
                <path d="M6 5l14 1l-1 7h-13" />
            </svg>
            <span class="mobile-bottom-cart-badge" data-cart-count data-cart-when-filled hidden>0</span>
        </div>
        <span>{{ __('Cart') }}</span>
    </button>
</div>
