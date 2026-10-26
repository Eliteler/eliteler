@php
    $bg_color = $bg_color ?? 'e-commerce-blue';
@endphp

<header class="store-navbar-header d-print-none">
    <div class="store-navbar-inner px-3 px-lg-4">

        <!-- Store Brand: Logo & Title -->
        <a href="{{ $storeUrl }}" class="store-brand-link text-decoration-none">
            <img src="{{ url($business_card_details->profile) }}" alt="{{ $card_details->title }}"
                class="store-brand-logo rounded-circle"
                onerror="this.onerror=null; this.src='{{ asset('img/templates/all-categories.png') }}';">
            <span class="store-brand-title text-dark">{{ $card_details->title }}</span>
        </a>

        <!-- Header Actions Wrapper -->
        <div class="store-header-actions">

            {{-- Check if language switcher is enabled --}}
            @if (
                !empty($business_card_details->is_enable_language_switcher) &&
                    $business_card_details->is_enable_language_switcher == 1)
                {{-- Language Switcher --}}
                @include('templates.stores.e-commerce.includes.language-switcher')
            @endif

            {{-- Themed Cart Button Container with True Dead-Center Badge --}}
            <div class="store-cart-btn-wrapper">
                <button type="button" class="btn btn-e-commerce-blue store-action-icon-btn" title="{{ __('Cart') }}"
                    aria-label="{{ __('Cart') }}" data-cart-open>
                    <i class="ti ti-shopping-cart fs-2 text-white"></i>
                </button>

                <!-- 100% Dead-Center Flexbox Badge -->
                <span class="store-cart-badge-pill" data-cart-count data-cart-when-filled hidden>0</span>
            </div>

        </div>

    </div>
</header>

<style>
    .store-navbar-header {
        position: relative;
        width: 100%;
        background-color: #ffffff;
        border-bottom: 1px solid #edf2f7;
        z-index: 1020;
    }

    .store-navbar-inner {
        display: flex;
        align-items: center;
        justify-content: space-between;
        min-height: 65px;
        width: 100%;
    }

    .store-brand-link {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        min-width: 0;
        max-width: 55%;
    }

    .store-brand-logo {
        width: 42px;
        height: 42px;
        object-fit: cover;
        flex-shrink: 0;
    }

    .store-brand-title {
        font-size: 1.25rem;
        font-weight: 700;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        letter-spacing: -0.2px;
    }

    .store-header-actions {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding-right: 8px;
        flex-shrink: 0;
    }

    .store-cart-btn-wrapper {
        position: relative;
        display: inline-flex;
    }

    .store-action-icon-btn {
        width: 40px !important;
        height: 40px !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        padding: 0 !important;
        border-radius: 10px !important;
    }

    /* ========================================================= */
    /* Absolute Dead-Center Circular Cart Badge                  */
    /* ========================================================= */
    .store-cart-badge-pill {
        position: absolute !important;
        top: -5px !important;
        right: -5px !important;
        width: 18px !important;
        height: 18px !important;
        min-width: 18px !important;
        max-width: 18px !important;
        box-sizing: border-box !important;
        background-color: #d63939 !important;
        color: #ffffff !important;
        font-size: 10px !important;
        font-weight: 800 !important;
        border-radius: 50% !important;
        border: 2px solid #ffffff !important;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.25) !important;
        align-items: center !important;
        justify-content: center !important;
        text-align: center !important;
        line-height: 1 !important;
        padding: 0 !important;
        margin: 0 !important;
        pointer-events: none !important;
        z-index: 15 !important;
    }

    /* Show only when not hidden and not empty */
    .store-cart-badge-pill[hidden] {
        display: none !important;
    }

    .store-cart-badge-pill:not([hidden]) {
        display: flex !important;
    }
</style>
