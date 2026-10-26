{{-- Header Component with Adaptive Cart Badge --}}
<header class="store-luxury-header">
    <div class="header-inner-container">
        <!-- Brand identity -->
        <a href="{{ $storeUrl }}" class="header-brand-link">
            <div class="brand-avatar-box">
                <img src="{{ !empty($business_card_details->profile) ? url($business_card_details->profile) : '' }}"
                    alt="{{ $card_details->title }}" class="brand-avatar-img"
                    onerror="this.onerror=null;this.src='data:image/svg+xml;charset=UTF-8,%3Csvg%20width%3D%2280%22%20height%3D%2280%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%3E%3Crect%20width%3D%22100%25%22%20height%3D%22100%25%22%20fill%3D%22%23d4af37%22%2F%3E%3Ctext%20x%3D%2250%25%22%20y%3D%2250%25%22%20fill%3D%22%23ffffff%22%20font-size%3D%2224%22%20text-anchor%3D%22middle%22%20dy%3D%22.3em%22%3EJ%3C%2Ftext%3E%3C%2Fsvg%3E';" />
            </div>
        </a>

        <!-- Controls: Language & Cart -->
        <div class="header-actions-block">
            @include('templates.stores.jewellery-shop.includes.language-switcher')

            <button type="button" class="header-cart-trigger-btn" aria-label="{{ __('Cart') }}" data-cart-open>
                <i class="ti ti-shopping-bag header-cart-icon"></i>
                <span class="header-cart-counter" data-cart-count data-cart-when-filled hidden>0</span>
            </button>
        </div>
    </div>
</header>

<style>
    .store-luxury-header {
        width: 100%;
        background: #ffffff;
        border-bottom: 1px solid rgba(212, 175, 55, 0.2);
        position: sticky;
        top: 0;
        z-index: 1020;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
    }

    .header-inner-container {
        max-width: 1240px;
        margin: 0 auto;
        padding: 12px 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .header-brand-link {
        display: flex;
        align-items: center;
        gap: 14px;
        text-decoration: none !important;
    }

    .brand-avatar-box {
        width: 46px;
        height: 46px;
        border-radius: 50% !important;
        overflow: hidden;
        border: 2px solid #d4af37;
        background: #fffdf8;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .brand-avatar-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .brand-text-block {
        display: flex;
        flex-direction: column;
    }

    .brand-store-name {
        font-family: 'Gloock', serif;
        font-size: 20px;
        font-weight: 700;
        color: #2b2316;
        margin: 0;
        line-height: 1.2;
    }

    .header-actions-block {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .header-cart-trigger-btn {
        width: 44px;
        height: 44px;
        border-radius: 50% !important;
        background: #fbf7ee;
        border: 1px solid rgba(212, 175, 55, 0.35);
        color: #2b2316;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        cursor: pointer;
        transition: all 0.25s ease;
    }

    .header-cart-trigger-btn:hover {
        background: #d4af37;
        color: #ffffff;
        border-color: #d4af37;
    }

    .header-cart-icon {
        font-size: 20px;
    }

    /* Adaptive Cart Badge for Single & Multi-digit Numbers */
    .header-cart-counter {
        position: absolute;
        top: -4px;
        right: -6px;
        min-width: 20px;
        height: 20px;
        padding: 0 5px;
        border-radius: 50px !important;
        background: #dc2626;
        color: #ffffff;
        font-size: 11px;
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
