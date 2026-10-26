<style>
    .store-luxury-header {
        background: #ffffff;
        border-bottom: 1px solid #f1f5f9;
        position: sticky;
        top: 0;
        z-index: 1020;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
    }

    .header-inner-container {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 12px 0;
        gap: 12px;
    }

    .header-brand-wrap {
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 0;
        flex: 1 1 auto;
    }

    .header-brand-avatar {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid #fef2f2;
        flex-shrink: 0;
    }

    .header-brand-title {
        font-size: 1.15rem;
        font-weight: 700;
        color: #1c1917;
        margin: 0;
        line-height: 1.2;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .header-actions-wrap {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-shrink: 0;
    }

    /* Precision Cart Trigger & Symmetrical Badge */
    .btn-header-cart {
        position: relative;
        width: 42px;
        height: 42px;
        border-radius: 50% !important;
        background: #fef2f2;
        color: #dc2626;
        border: 1.5px solid rgba(220, 38, 38, 0.15);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        cursor: pointer;
        transition: all 0.3s ease;
        flex-shrink: 0;
        padding: 0;
    }

    .btn-header-cart:hover {
        background: #dc2626;
        color: #ffffff;
        box-shadow: 0 4px 12px rgba(220, 38, 38, 0.25);
    }

    .header-cart-badge {
        position: absolute;
        top: -8px;
        right: -3px;
        min-width: 20px;
        height: 20px;
        padding: 0 0px;
        border-radius: 50px !important;
        /* Adaptive pill for 1 or multiple digits */
        background: #dc2626;
        color: #ffffff;
        font-size: 0.68rem;
        font-weight: 800;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 2px solid #ffffff;
        box-shadow: 0 2px 6px rgba(220, 38, 38, 0.35);
        line-height: 1;
        z-index: 2;
    }
</style>

<header class="store-luxury-header">
    <div class="container-xl px-3 px-lg-4">
        <div class="header-inner-container">
            {{-- Brand Left --}}
            <a href="{{ $storeUrl }}" class="header-brand-wrap">
                @if (!empty($business_card_details->profile))
                    <img src="{{ url($business_card_details->profile) }}" alt="{{ $card_details->title }}"
                        class="header-brand-avatar" onerror="this.style.display='none'">
                @endif
                <h1 class="header-brand-title">{{ $card_details->title }}</h1>
            </a>

            {{-- Actions Right --}}
            <div class="header-actions-wrap">
                {{-- Check if language switcher is enabled --}}
                @if (
                    !empty($business_card_details->is_enable_language_switcher) &&
                        $business_card_details->is_enable_language_switcher == 1)
                    {{-- Language Switcher --}}
                    @include('templates.stores.gift-shop.includes.language-switcher')
                @endif

                {{-- Cart Trigger with Pill Badge --}}
                <button type="button" class="btn-header-cart" aria-label="{{ __('Cart') }}" data-cart-open>
                    <i class="ti ti-shopping-bag"></i>
                    <span class="header-cart-badge" data-cart-count data-cart-when-filled hidden>0</span>
                </button>
            </div>
        </div>
    </div>
</header>
