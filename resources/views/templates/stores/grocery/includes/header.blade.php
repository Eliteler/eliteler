{{-- Luxury Grocery Store Header with Dynamic Cart Badge Geometry --}}
<header class="store-luxury-header">
    <div class="store-header-container">
        <!-- Brand Identity Area (Avatar + Store Title) -->
        <a href="{{ $storeUrl }}" class="store-brand-identity">
            <div class="store-brand-avatar-wrapper">
                <img src="{{ !empty($business_card_details->profile) ? url($business_card_details->profile) : '' }}"
                    alt="{{ $card_details->title }}" class="store-brand-avatar"
                    onerror="this.onerror=null;this.src='data:image/svg+xml;charset=UTF-8,%3Csvg%20width%3D%2260%22%20height%3D%2260%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%3E%3Crect%20width%3D%22100%25%22%20height%3D%22100%25%22%20fill%3D%22%232e7d32%22%2F%3E%3Ctext%20x%3D%2250%25%22%20y%3D%2250%25%22%20fill%3D%22%23ffffff%22%20font-family%3D%22sans-serif%22%20font-size%3D%2220%22%20text-anchor%3D%22middle%22%20dy%3D%22.3em%22%3E{{ substr($card_details->title, 0, 1) }}%3C%2Ftext%3E%3C%2Fsvg%3E';">
            </div>
        </a>

        <!-- Header Actions: Language Switcher & Cart Trigger -->
        <div class="store-header-actions">
            {{-- Check if language switcher is enabled --}}
            @if (
                !empty($business_card_details->is_enable_language_switcher) &&
                    $business_card_details->is_enable_language_switcher == 1)
                @include('templates.stores.grocery.includes.language-switcher')
            @endif

            <!-- Circular Cart Trigger -->
            <button type="button" class="btn store-header-cart-btn" aria-label="{{ __('Cart') }}" data-cart-open>
                <i class="ti ti-shopping-bag store-header-cart-icon"></i>
                <span class="store-cart-counter-badge" data-cart-count data-cart-when-filled hidden>0</span>
            </button>
        </div>
    </div>
</header>

<style>
    /* Header Scoped Styling */
    .store-luxury-header {
        width: 100%;
        background: #ffffff;
        border-bottom: 1px solid #c8e6c9;
        position: sticky;
        top: 0;
        z-index: 1020;
        box-shadow: 0 4px 16px rgba(46, 125, 50, 0.05);
    }

    .store-header-container {
        max-width: 1240px;
        margin: 0 auto;
        padding: 12px 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .store-brand-identity {
        display: flex;
        align-items: center;
        gap: 14px;
        color: #1b5e20;
    }

    .store-brand-avatar-wrapper {
        width: 48px;
        height: 48px;
        border-radius: 50% !important;
        border: 2px solid #a5d6a7;
        overflow: hidden;
        background-color: #f1f8e9;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .store-brand-avatar {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .store-brand-titles {
        display: flex;
        align-items: center;
    }

    .store-brand-main-title {
        font-size: 20px;
        font-weight: 800;
        margin: 0;
        color: #1b5e20;
        line-height: 1.2;
    }

    .store-header-actions {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    /* Cart Button Styling */
    .store-header-cart-btn {
        width: 44px;
        height: 44px;
        border-radius: 50% !important;
        background-color: #2e7d32 !important;
        color: #ffffff !important;
        border: none !important;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        box-shadow: 0 4px 12px rgba(46, 125, 50, 0.25);
        transition: transform 0.2s ease, background-color 0.2s ease;
        padding: 0 !important;
    }

    .store-header-cart-btn:hover {
        transform: translateY(-2px);
        background-color: #1b5e20 !important;
    }

    .store-header-cart-icon {
        font-size: 21px;
    }

    /* Dynamic Pill-Rounded Cart Counter Badge (Fix for 2+ digits padding/clipping) */
    .store-cart-counter-badge {
        position: absolute;
        top: -5px;
        right: -6px;
        min-width: 20px;
        height: 20px;
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

    /* RTL Alignment Support */
    [dir="rtl"] .store-cart-counter-badge {
        right: auto;
        left: -6px;
    }
</style>
