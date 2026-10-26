<style>
    /* Top Announcement Bar */
    .store-top-announcement {
        background-color: #1f4b48;
        color: #ffffff;
        padding: 9px 16px;
        text-align: center;
        font-size: 13px;
        line-height: 1.4;
        font-weight: 500;
        letter-spacing: 0.2px;
    }

    /* Main Camel Header */
    .store-header {
        background-color: #cca47a;
        padding: 12px 20px;
        position: sticky;
        top: 0;
        z-index: 1020;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    }

    .header-brand-wrap {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .header-avatar {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        object-fit: cover;
        background-color: #ffffff;
        border: 2px solid rgba(255, 255, 255, 0.6);
    }

    .header-title {
        font-family: 'DM Serif Display', serif;
        font-size: 22px;
        color: #ffffff;
        margin: 0;
        line-height: 1.1;
        letter-spacing: 0.5px;
    }

    .header-actions-group {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    /* Circular Pill Cart Button */
    .header-cart-trigger {
        position: relative;
        background-color: #1f4b48;
        border: none;
        width: 42px;
        height: 42px;
        border-radius: 50% !important;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: transform 0.2s ease, background-color 0.2s ease;
        padding: 0;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.12);
    }

    .header-cart-trigger i {
        color: #ffffff !important;
        font-size: 20px !important;
        line-height: 1;
    }

    .header-cart-trigger:hover {
        background-color: #163836;
        transform: scale(1.06);
    }

    /* Fixed Header Cart Badge with Proper Visibility Logic */
    .header-cart-badge {
        position: absolute;
        top: -4px;
        right: -4px;
        background-color: #dc2626;
        color: #ffffff;
        font-size: 10px;
        font-weight: 700;
        min-width: 18px;
        height: 18px;
        border-radius: 50%;
        align-items: center;
        justify-content: center;
        text-align: center;
        line-height: 1;
        padding: 0 4px;
        border: 2px solid #cca47a;
        pointer-events: none;
    }

    /* Hide when empty or attribute hidden exists */
    .header-cart-badge[hidden] {
        display: none !important;
    }

    /* Reveal dynamically with flex alignment when filled */
    .header-cart-badge:not([hidden]) {
        display: inline-flex !important;
    }
</style>

{{-- Subtitle Announcement Bar --}}
@if (!empty($card_details->sub_title) || !empty($business_card_details->title2) || !empty($business_card_details->subtitle2))
    <div class="store-top-announcement">
        @include('templates.stores.shared.subtitle-lines')
        {{ $card_details->sub_title }}
    </div>
@endif

<header class="store-header">
    <div class="d-flex align-items-center justify-content-between">
        {{-- Brand Logo & Title --}}
        <a href="{{ $storeUrl }}" class="header-brand-wrap text-decoration-none">
            <img src="{{ !empty($business_card_details->profile) ? url($business_card_details->profile) : 'data:image/svg+xml;charset=UTF-8,%3Csvg%20width%3D%2244%22%20height%3D%2244%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%3E%3Ccircle%20cx%3D%2222%22%20cy%3D%2222%22%20r%3D%2222%22%20fill%3D%22%23ffffff%22%2F%3E%3C%2Fsvg%3E' }}"
                alt="{{ $card_details->title }}" class="header-avatar"
                onerror="this.onerror=null;this.src='data:image/svg+xml;charset=UTF-8,%3Csvg%20width%3D%2244%22%20height%3D%2244%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%3E%3Ccircle%20cx%3D%2222%22%20cy%3D%2222%22%20r%3D%2222%22%20fill%3D%22%23ffffff%22%2F%3E%3C%2Fsvg%3E';">
            <div>
                <h1 class="header-title">{{ $card_details->title }}</h1>
            </div>
        </a>

        {{-- Language Switcher & Cart Group --}}
        <div class="header-actions-group">
            {{-- Check if language switcher is enabled --}}
            @if (
                !empty($business_card_details->is_enable_language_switcher) &&
                    $business_card_details->is_enable_language_switcher == 1)
                @include('templates.stores.furniture.includes.language-switcher')
            @endif

            <button type="button" class="header-cart-trigger" aria-label="{{ __('View Cart') }}" data-cart-open>
                <i class="ti ti-shopping-bag"></i>
                <span class="header-cart-badge" data-cart-count data-cart-when-filled hidden>0</span>
            </button>
        </div>
    </div>
</header>
