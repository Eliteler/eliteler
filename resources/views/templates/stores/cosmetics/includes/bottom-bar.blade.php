{{-- Luxury Bottom Navigation Bar with Dynamic Cart Count Badge --}}
@php
    $homeUrl = $storeUrl ?? url('/');
    $categoriesUrl =
        isset($storefront) && isset($storefront->categoriesUrl)
            ? $storefront->categoriesUrl
            : (isset($business_card_details)
                ? url($business_card_details->card_url . '/categories')
                : url('categories'));
    $isHomeActive =
        isset($storefront) && method_exists($storefront, 'onHome')
            ? $storefront->onHome()
            : !request()->has('category') && !request()->routeIs('*categories*');
    $isCategoriesActive =
        isset($storefront) && method_exists($storefront, 'onCategories')
            ? $storefront->onCategories()
            : request()->has('category') || request()->routeIs('*categories*');
@endphp

<nav
    class="navbar-2 border navbar-expand d-print-none p-3 d-lg-none {{ ($bg ?? '') == 'dark' ? 'bg-dark' : 'bg-white' }} fixed-bottom store-bottom-nav-bar">
    <ul class="navbar-nav nav-justified d-flex justify-content-between w-100 px-5 mb-0">
        {{-- Home Link --}}
        <li class="d-flex justify-content-center">
            <a class="d-flex flex-column justify-content-center align-items-center {{ $isHomeActive && (($bg ?? '') == 'dark' || ($bg ?? '') == 'light') ? 'text-' . ($color ?? 'primary') : (($bg ?? '') == 'dark' ? 'text-white' : 'text-dark') }}"
                href="{{ $homeUrl }}">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                    <path d="M5 12l-2 0l9 -9l9 9l-2 0" />
                    <path d="M5 12v7a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-7" />
                    <path d="M9 21v-6a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v6" />
                </svg>
                <span class="mt-1 fs-4">{{ __('Home') }}</span>
            </a>
        </li>

        {{-- Categories Link --}}
        <li class="d-flex justify-content-center">
            <a class="d-flex flex-column justify-content-center align-items-center {{ $isCategoriesActive && (($bg ?? '') == 'dark' || ($bg ?? '') == 'light') ? 'text-' . ($color ?? 'primary') : (($bg ?? '') == 'dark' ? 'text-white' : 'text-dark') }}"
                href="{{ $categoriesUrl }}">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                    <path d="M4 4h6v6h-6z" />
                    <path d="M14 4h6v6h-6z" />
                    <path d="M4 14h6v6h-6z" />
                    <path d="M17 17m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0" />
                </svg>
                <span class="mt-1 fs-4">{{ __('Categories') }}</span>
            </a>
        </li>

        {{-- Cart Link with Position Relative Container and Count Badge --}}
        <li class="d-flex justify-content-center">
            <a class="d-flex flex-column justify-content-center align-items-center {{ ($bg ?? '') == 'dark' ? 'text-white' : 'text-dark' }}"
                href="#offcanvasCartDrawer" data-bs-toggle="offcanvas" role="button"
                aria-controls="offcanvasCartDrawer" data-cart-open>
                <div class="store-bottom-cart-icon-wrap position-relative d-inline-flex">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                        <path
                            d="M6.331 8h11.339a2 2 0 0 1 1.977 2.304l-1.255 8.152a3 3 0 0 1 -2.966 2.544h-6.852a3 3 0 0 1 -2.965 -2.544l-1.255 -8.152a2 2 0 0 1 1.977 -2.304z" />
                        <path d="M9 11v-5a3 3 0 0 1 6 0v5" />
                    </svg>
                    {{-- Dynamic Pill-Rounded Cart Count Badge --}}
                    <span class="store-bottom-cart-count-badge" id="bottomBarCartBadge" data-cart-badge>0</span>
                </div>
                <span class="mt-1 fs-4">{{ __('Cart') }}</span>
            </a>
        </li>
    </ul>
</nav>

<style>
    /* Scoped Bottom Nav Bar Styling */
    .store-bottom-nav-bar {
        z-index: 1020 !important;
        box-shadow: 0 -4px 16px rgba(0, 0, 0, 0.06);
    }

    .store-bottom-cart-icon-wrap {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        position: relative;
    }

    /* Luxury Pill-Rounded Cart Badge */
    .store-bottom-cart-count-badge {
        position: absolute;
        top: -4px;
        right: -10px;
        min-width: 18px;
        height: 18px;
        padding: 0 4px;
        border-radius: 50px !important;
        background-color: #e11d48;
        /* High-contrast badge color */
        color: #ffffff;
        font-size: 10px;
        font-weight: 700;
        line-height: 1;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 2px solid #ffffff;
        box-sizing: border-box;
        transition: transform 0.2s ease;
    }

    /* RTL Alignment Support */
    [dir="rtl"] .store-bottom-cart-count-badge {
        right: auto;
        left: -10px;
    }
</style>