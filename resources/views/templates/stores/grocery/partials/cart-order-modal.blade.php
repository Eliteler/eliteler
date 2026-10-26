{{--
    Grocery cart drawer and checkout form. Markup only: the shared storefront script
    (public/js/store/storefront.js) fills and runs it through the data-* hooks.
--}}
@php
    $deliveryChoices = array_filter([
        'Order For Delivery' => (bool) ($deliveryOptions->order_for_delivery ?? true),
        'Take Away' => (bool) ($deliveryOptions->take_away ?? false),
        'Dine In' => (bool) ($deliveryOptions->dine_in ?? false),
    ]);
    $deliveryLabels = ['Order For Delivery' => 'Delivery', 'Take Away' => 'Take Away', 'Dine In' => 'Dine In'];
    $deliveryIcons = ['Order For Delivery' => 'ti-truck-delivery', 'Take Away' => 'ti-building-store', 'Dine In' => 'ti-tools-kitchen-2'];
@endphp

{{-- Cart drawer --}}
<div class="offcanvas offcanvas-end store-cart-drawer" tabindex="-1" id="offcanvasCartDrawer"
    aria-labelledby="offcanvasCartDrawerLabel" data-cart-drawer>
    <div class="offcanvas-header store-cart-drawer-header">
        <div class="d-flex align-items-center gap-2">
            <div class="store-cart-header-icon-pod">
                <i class="ti ti-shopping-cart"></i>
            </div>
            <h3 class="offcanvas-title store-cart-drawer-title" id="offcanvasCartDrawerLabel">{{ __('Shopping Cart') }}</h3>
        </div>
        <button type="button" class="btn store-cart-close-btn" data-bs-dismiss="offcanvas" aria-label="{{ __('Close') }}">
            <i class="ti ti-x"></i>
        </button>
    </div>

    <div class="offcanvas-body store-cart-drawer-body">
        <div class="store-cart-items-container" data-cart-items></div>

        <div class="store-cart-empty-box" data-cart-when-empty>
            <div class="store-cart-empty-icon-pod">
                <i class="ti ti-basket-cancel"></i>
            </div>
            <h4 class="store-cart-empty-title">{{ __('Your cart is empty') }}</h4>
            <p class="store-cart-empty-text">{{ __('Explore fresh groceries and add your favorite items to cart.') }}</p>
            <button type="button" class="btn store-cart-start-btn" data-bs-dismiss="offcanvas">{{ __('Start Shopping') }}</button>
        </div>
    </div>

    <div class="offcanvas-footer store-cart-drawer-footer" data-cart-when-filled hidden>
        <div class="store-cart-summary-line">
            <span>{{ __('Grand Total') }}</span>
            <span class="text-success" data-cart-total></span>
        </div>
        <button type="button" class="btn store-cart-checkout-btn" data-cart-checkout>
            <i class="ti ti-brand-whatsapp me-2"></i> {{ __('Place WhatsApp Order') }}
        </button>
    </div>
</div>

{{-- One cart line (copied by the storefront script for every item) --}}
<template data-cart-item>
    <div class="cart-item-card">
        <div class="cart-item-thumb-box">
            <img class="cart-item-img" data-cart-image data-fallback="{{ asset('img/templates/grocery/placeholder.svg') }}" alt="">
        </div>
        <div class="cart-item-info">
            <h4 class="cart-item-title" data-cart-name></h4>
            <span class="cart-item-var-badge" data-cart-variant></span>
            <p class="cart-item-price" data-cart-line-total></p>
            <div class="cart-item-qty-row">
                <button type="button" class="btn cart-qty-btn" data-cart-decrease aria-label="{{ __('Decrease') }}"><i class="ti ti-minus"></i></button>
                <span class="cart-qty-value" data-cart-qty></span>
                <button type="button" class="btn cart-qty-btn" data-cart-increase aria-label="{{ __('Increase') }}"><i class="ti ti-plus"></i></button>
                <button type="button" class="btn cart-trash-btn" data-cart-remove aria-label="{{ __('Remove Item') }}"><i class="ti ti-trash"></i></button>
            </div>
        </div>
    </div>
</template>

{{-- Checkout --}}
<div class="modal fade" id="orderModal" tabindex="-1" aria-hidden="true" data-order-modal>
    <div class="modal-dialog modal-md modal-dialog-centered">
        <form class="modal-content store-order-modal-content" data-order-form novalidate>
            <div class="modal-header store-order-modal-header">
                <h4 class="modal-title store-order-modal-title">{{ __('Order & Delivery Information') }}</h4>
                <button type="button" class="btn store-order-close-btn" data-bs-dismiss="modal" aria-label="{{ __('Close') }}">
                    <i class="ti ti-x"></i>
                </button>
            </div>

            <div class="modal-body store-order-modal-body">
                <div class="mb-3">
                    <label class="form-label store-form-label required" for="cus_name">{{ __('Full Name') }}</label>
                    <input type="text" class="form-control store-form-input" id="cus_name" name="customer_name"
                        placeholder="{{ __('Enter your full name') }}" autocomplete="name" required />
                </div>

                <div class="mb-3">
                    <label class="form-label store-form-label required" for="cus_mobile">{{ __('WhatsApp / Mobile') }}</label>
                    <input type="tel" inputmode="tel" class="form-control store-form-input" id="cus_mobile" name="customer_phone"
                        placeholder="{{ __('Mobile number with country code, without +') }}" autocomplete="tel" required />
                </div>

                @if (count($deliveryChoices) > 1)
                    <div class="mb-3">
                        <label class="form-label store-form-label required">{{ __('Delivery Type') }}</label>
                        <div class="store-delivery-radio-grid">
                            @foreach (array_keys($deliveryChoices) as $method)
                                <label class="store-delivery-card">
                                    <input type="radio" name="delivery_method" value="{{ $method }}" @checked($loop->first)>
                                    <span class="store-delivery-card-inner">
                                        <i class="ti {{ $deliveryIcons[$method] }}"></i>
                                        <span>{{ __($deliveryLabels[$method]) }}</span>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="mb-3" data-address-field>
                    <label class="form-label store-form-label required" for="cus_address">{{ __('Delivery Address') }}</label>
                    <textarea class="form-control store-form-textarea" id="cus_address" name="delivery_address" rows="2"
                        placeholder="{{ __('Complete street address, landmark') }}" autocomplete="street-address"></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label store-form-label" for="cus_notes">{{ __('Order Notes (Optional)') }}</label>
                    <textarea class="form-control store-form-textarea" id="cus_notes" name="delivery_note" rows="2"
                        placeholder="{{ __('E.g. Ring the bell, deliver fresh') }}"></textarea>
                </div>

                <div class="store-order-safety-notice">
                    <i class="ti ti-info-circle me-1"></i>
                    <span>{{ __("WhatsApp will launch automatically. Tap 'Send' to dispatch your order directly.") }}</span>
                </div>
            </div>

            <div class="modal-footer store-order-modal-footer">
                <button type="button" class="btn store-order-cancel-btn" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                <button type="submit" class="btn store-order-confirm-btn">{{ __('Confirm Order') }}</button>
            </div>
        </form>
    </div>
</div>

<style>
    /* Dark Blur Backdrop for Cart Offcanvas & Order Modal */
    .offcanvas-backdrop.show,
    .modal-backdrop.show {
        background-color: rgba(15, 23, 42, 0.82) !important;
        backdrop-filter: blur(6px) !important;
        -webkit-backdrop-filter: blur(6px) !important;
        opacity: 1 !important;
    }

    /* Cart Drawer Styles */
    .store-cart-drawer {
        width: 420px !important;
        max-width: 100vw;
        border-top-left-radius: 28px !important;
        border-bottom-left-radius: 28px !important;
        border-left: 1px solid #c8e6c9 !important;
        box-shadow: -10px 0 35px rgba(0, 0, 0, 0.35) !important;
    }

    [dir="rtl"] .store-cart-drawer {
        border-top-left-radius: 0 !important;
        border-bottom-left-radius: 0 !important;
        border-top-right-radius: 28px !important;
        border-bottom-right-radius: 28px !important;
        border-left: none !important;
        border-right: 1px solid #c8e6c9 !important;
    }

    .store-cart-drawer-header {
        padding: 20px 24px;
        background: #ffffff;
        border-bottom: 1px solid #e8f5e9;
    }

    .store-cart-header-icon-pod {
        width: 36px;
        height: 36px;
        border-radius: 50% !important;
        background-color: #e8f5e9;
        color: #2e7d32;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 19px;
    }

    .store-cart-drawer-title {
        font-size: 18px;
        font-weight: 700;
        color: #1b5e20;
        margin: 0;
    }

    .store-cart-close-btn {
        width: 34px;
        height: 34px;
        border-radius: 50% !important;
        background-color: #f1f5f9 !important;
        color: #475569 !important;
        border: none !important;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0 !important;
        transition: all 0.2s ease;
    }

    .store-cart-close-btn:hover {
        background-color: #e2e8f0 !important;
        color: #0f172a !important;
    }

    .store-cart-drawer-body {
        padding: 20px;
        background-color: #f7faf7;
        overflow-y: auto;
    }

    .store-cart-items-container {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .cart-item-card {
        display: flex;
        align-items: center;
        gap: 12px;
        background: #ffffff;
        border: 1px solid #c8e6c9;
        border-radius: 20px !important;
        padding: 12px;
        width: 100%;
        box-sizing: border-box;
    }

    .cart-item-thumb-box {
        width: 72px;
        height: 72px;
        border-radius: 16px !important;
        overflow: hidden;
        background-color: #f1f8e9;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .cart-item-img {
        width: 100%;
        height: 100%;
        object-fit: contain;
    }

    .cart-item-info {
        flex: 1 1 auto;
        min-width: 0;
        overflow: hidden;
    }

    .cart-item-title {
        font-size: 14px;
        font-weight: 700;
        color: #212121;
        margin: 0 0 2px 0;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .cart-item-var-badge {
        font-size: 11px;
        font-weight: 600;
        color: #2e7d32;
        background: #e8f5e9;
        padding: 2px 8px;
        border-radius: 50px !important;
        display: inline-block;
        margin-bottom: 4px;
    }

    .cart-item-price {
        font-size: 14px;
        font-weight: 700;
        color: #1b5e20;
        margin: 0 0 6px 0;
    }

    .cart-item-qty-row {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .cart-qty-btn {
        width: 28px;
        height: 28px;
        border-radius: 50% !important;
        background-color: #f1f8e9 !important;
        color: #1b5e20 !important;
        border: 1px solid #c8e6c9 !important;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0 !important;
        font-size: 14px;
    }

    .cart-qty-value {
        font-size: 13px;
        font-weight: 700;
        color: #212121;
        min-width: 20px;
        text-align: center;
    }

    /* Redesigned 36px Trash Button */
    .cart-trash-btn {
        width: 36px;
        height: 36px;
        border-radius: 50% !important;
        background-color: #fee2e2 !important;
        color: #dc2626 !important;
        border: none !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-inline-start: auto;
        padding: 0 !important;
        font-size: 18px;
        flex-shrink: 0;
        cursor: pointer;
        transition: all 0.25s ease;
    }

    .cart-trash-btn:hover {
        background-color: #dc2626 !important;
        color: #ffffff !important;
        transform: scale(1.06);
    }

    .store-cart-empty-box {
        height: 100%;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        padding: 30px;
    }

    .store-cart-empty-icon-pod {
        width: 84px;
        height: 84px;
        border-radius: 50% !important;
        background-color: #e8f5e9;
        color: #2e7d32;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 38px;
        margin-bottom: 16px;
    }

    .store-cart-empty-title {
        font-size: 18px;
        font-weight: 700;
        color: #1b5e20;
        margin-bottom: 6px;
    }

    .store-cart-empty-text {
        font-size: 13px;
        color: #757575;
        margin-bottom: 20px;
        max-width: 240px;
    }

    .store-cart-start-btn {
        height: 42px;
        padding: 0 24px !important;
        border-radius: 50px !important;
        background-color: #2e7d32 !important;
        color: #ffffff !important;
        border: none !important;
        font-size: 13px;
        font-weight: 600;
    }

    .store-cart-drawer-footer {
        padding: 20px;
        background: #ffffff;
        border-top: 1px solid #e8f5e9;
    }

    .store-cart-summary-line {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 16px;
        font-weight: 700;
        color: #212121;
        margin-bottom: 16px;
    }

    .store-cart-checkout-btn {
        width: 100%;
        height: 48px;
        border-radius: 50px !important;
        background-color: #2e7d32 !important;
        color: #ffffff !important;
        border: none !important;
        font-size: 15px;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    /* Modal Alignment & Layout Fixes */
    .store-order-modal-content {
        border-radius: 28px !important;
        border: 1px solid #c8e6c9 !important;
        overflow: hidden;
        box-shadow: 0 20px 45px rgba(0, 0, 0, 0.35) !important;
        background: #ffffff;
    }

    .store-order-modal-header {
        padding: 20px 24px;
        border-bottom: 1px solid #e8f5e9;
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        width: 100%;
    }

    .store-order-modal-title {
        font-size: 18px;
        font-weight: 800;
        color: #1b5e20;
        margin: 0;
    }

    .store-order-close-btn {
        width: 34px;
        height: 34px;
        border-radius: 50% !important;
        background-color: #f1f5f9 !important;
        color: #475569 !important;
        border: none !important;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0 !important;
        flex-shrink: 0;
        transition: all 0.2s ease;
    }

    .store-order-close-btn:hover {
        background-color: #e2e8f0 !important;
        color: #0f172a !important;
    }

    .store-order-modal-body {
        padding: 24px;
    }

    .store-form-label {
        font-size: 13px;
        font-weight: 700;
        color: #374151;
        margin-bottom: 6px;
    }

    .store-form-input {
        height: 44px;
        border-radius: 50px !important;
        border: 1px solid #c8e6c9 !important;
        padding: 0 18px;
        font-size: 13px;
        color: #1f2937;
        background-color: #ffffff;
    }

    .store-form-textarea {
        border-radius: 18px !important;
        border: 1px solid #c8e6c9 !important;
        padding: 12px 18px;
        font-size: 13px;
        color: #1f2937;
        background-color: #ffffff;
        resize: vertical;
    }

    .store-form-input:focus,
    .store-form-textarea:focus {
        border-color: #2e7d32 !important;
        box-shadow: 0 0 0 3px rgba(46, 125, 50, 0.15) !important;
        outline: none;
    }

    .store-delivery-radio-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 10px;
    }

    .store-delivery-card input {
        display: none;
    }

    .store-delivery-card-inner {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 6px;
        padding: 12px 6px;
        border: 1px solid #c8e6c9;
        border-radius: 18px !important;
        cursor: pointer;
        font-size: 12px;
        font-weight: 600;
        color: #616161;
        transition: all 0.2s ease;
    }

    .store-delivery-card-inner i {
        font-size: 20px;
    }

    .store-delivery-card input:checked+.store-delivery-card-inner {
        background-color: #e8f5e9;
        border-color: #2e7d32;
        color: #1b5e20;
        box-shadow: 0 4px 10px rgba(46, 125, 50, 0.15);
    }

    .store-order-safety-notice {
        font-size: 12px;
        color: #4b5563;
        background-color: #f1f8e9;
        padding: 12px 16px;
        border-radius: 16px !important;
        display: flex;
        align-items: center;
        margin-top: 14px;
        border: 1px solid #c8e6c9;
    }

    /* Fixed Modal Footer & Buttons Styling */
    .store-order-modal-footer {
        padding: 18px 24px;
        border-top: 1px solid #e8f5e9;
        display: flex;
        align-items: center;
        gap: 12px;
        background: #ffffff;
    }

    .store-order-cancel-btn {
        flex: 1;
        height: 46px;
        border-radius: 50px !important;
        background-color: #f8fafc !important;
        color: #475569 !important;
        border: 1.5px solid #cbd5e1 !important;
        font-weight: 700;
        font-size: 14px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
    }

    .store-order-cancel-btn:hover {
        background-color: #e2e8f0 !important;
        color: #0f172a !important;
        border-color: #94a3b8 !important;
    }

    .store-order-confirm-btn {
        flex: 2;
        height: 46px;
        border-radius: 50px !important;
        background-color: #2e7d32 !important;
        color: #ffffff !important;
        border: none !important;
        font-weight: 700;
        font-size: 14px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 12px rgba(46, 125, 50, 0.25);
        transition: all 0.2s ease;
    }

    .store-order-confirm-btn:hover {
        background-color: #1b5e20 !important;
        transform: translateY(-1px);
        box-shadow: 0 6px 16px rgba(46, 125, 50, 0.35);
    }
</style>
