{{--
    Jewellery cart drawer and checkout form. Markup only: the shared storefront script
    (public/js/store/storefront.js) fills and runs it through the data-* hooks.
--}}
@php
    $deliveryChoices = array_filter([
        'Order For Delivery' => (bool) ($deliveryOptions->order_for_delivery ?? true),
        'Take Away' => (bool) ($deliveryOptions->take_away ?? false),
        'Dine In' => (bool) ($deliveryOptions->dine_in ?? false),
    ]);
    $deliveryIcons = ['Order For Delivery' => 'ti-truck-delivery', 'Take Away' => 'ti-building-store', 'Dine In' => 'ti-armchair'];
@endphp

{{-- Cart drawer --}}
<div class="offcanvas offcanvas-end luxury-cart-drawer" tabindex="-1" id="offcanvasEnd" aria-labelledby="cartDrawerTitle"
    data-bs-scroll="false" data-cart-drawer>
    <div class="offcanvas-header cart-drawer-header">
        <h3 class="offcanvas-title cart-drawer-heading" id="cartDrawerTitle">
            <i class="ti ti-shopping-bag text-gold"></i> {{ __('Cart Items') }}
        </h3>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="{{ __('Close') }}"></button>
    </div>

    <div class="offcanvas-body cart-drawer-body">
        <div class="cart-items-wrapper" data-cart-items></div>

        <div class="cart-empty-center-pod" data-cart-when-empty>
            <div class="cart-empty-icon-circle">
                <i class="ti ti-shopping-bag-x"></i>
            </div>
            <h4 class="cart-empty-title">{{ __('Your cart is empty.') }}</h4>
            <p class="cart-empty-sub">{{ __('Explore our luxury catalogue and add your favorite jewellery items.') }}</p>
            <button type="button" class="btn btn-gold cart-empty-btn" data-bs-dismiss="offcanvas">{{ __('Start Shopping') }}</button>
        </div>
    </div>

    <div class="offcanvas-footer cart-drawer-footer border-top p-3" data-cart-when-filled>
        <div class="cart-grand-total-row">
            <span class="cart-grand-total-label">{{ __('Grand Total') }}</span>
            <span class="cart-grand-total-price" data-cart-total></span>
        </div>
        <button type="button" class="btn btn-gold w-100 btn-place-wa-order" data-cart-checkout>
            <i class="ti ti-brand-whatsapp"></i>
            <span>{{ __('Place WhatsApp Order') }}</span>
        </button>
    </div>
</div>

{{-- One cart line (copied by the storefront script for every item) --}}
<template data-cart-item>
    <div class="cart-item-card">
        <div class="cart-item-img-pod">
            <img data-cart-image data-fallback="{{ asset('img/templates/jewellery-shop/placeholder.svg') }}" alt="">
        </div>
        <div class="cart-item-info">
            <p class="cart-item-title" data-cart-name></p>
            <span class="cart-item-variant-label" data-cart-variant></span>
            <p class="cart-item-price" data-cart-line-total></p>
            <div class="cart-item-controls">
                <button type="button" class="cart-qty-step-btn" data-cart-decrease aria-label="{{ __('Decrease') }}">-</button>
                <span class="cart-qty-number" data-cart-qty></span>
                <button type="button" class="cart-qty-step-btn" data-cart-increase aria-label="{{ __('Increase') }}">+</button>
            </div>
        </div>
        <button type="button" class="cart-item-trash-btn" data-cart-remove title="{{ __('Remove Item') }}" aria-label="{{ __('Remove Item') }}">
            <i class="ti ti-trash"></i>
        </button>
    </div>
</template>

{{-- Checkout --}}
<div class="modal fade luxury-order-modal" id="orderModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-order-modal>
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content luxury-order-card" data-order-form novalidate>
            <div class="modal-header border-0 pb-0">
                <h4 class="modal-title luxury-modal-heading">{{ __('Please fill following details:') }}</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
            </div>
            <div class="modal-body pt-3">
                <div class="mb-3">
                    <label class="form-label luxury-label required" for="order_name">{{ __('Full Name') }}</label>
                    <input type="text" class="form-control luxury-input" id="order_name" name="customer_name"
                        placeholder="{{ __('Your full name') }}" autocomplete="name" required>
                </div>
                <div class="mb-3">
                    <label class="form-label luxury-label required" for="order_phone">{{ __('Mobile') }}</label>
                    <input type="tel" inputmode="tel" class="form-control luxury-input" id="order_phone" name="customer_phone"
                        placeholder="{{ __('Mobile number with country code, excluding +') }}" autocomplete="tel" required>
                </div>

                @if (count($deliveryChoices) > 1)
                    <div class="mb-3">
                        <label class="form-label luxury-label required">{{ __('Delivery Type') }}</label>
                        <div class="delivery-segmented-grid">
                            @foreach (array_keys($deliveryChoices) as $method)
                                <label class="delivery-card-segment">
                                    <input type="radio" name="delivery_method" value="{{ $method }}" @checked($loop->first)>
                                    <div class="segment-pill-box">
                                        <i class="ti {{ $deliveryIcons[$method] }}"></i>
                                        <span>{{ __($method) }}</span>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="mb-3" data-address-field>
                    <label class="form-label luxury-label required" for="order_address">{{ __('Address') }}</label>
                    <input type="text" class="form-control luxury-input" id="order_address" name="delivery_address"
                        placeholder="{{ __('Delivery street & city address') }}" autocomplete="street-address">
                </div>

                <div class="mb-3">
                    <label class="form-label luxury-label" for="order_notes">{{ __('Notes') }}</label>
                    <textarea class="form-control luxury-input" id="order_notes" name="delivery_note" rows="2"
                        placeholder="{{ __('Special instructions or custom ring sizes...') }}"></textarea>
                </div>

                <div class="order-client-tip">
                    <i class="ti ti-info-circle text-gold"></i>
                    <small>{{ __("After you click the Confirm button, WhatsApp will open. Tap 'Send' in WhatsApp to send your order to the shop owner.") }}</small>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-secondary order-cancel-pill" data-bs-dismiss="modal">{{ __('Close') }}</button>
                <button type="submit" class="btn btn-gold order-confirm-pill">{{ __('Confirm') }}</button>
            </div>
        </form>
    </div>
</div>

<style>
    /* Dark Blur Backdrop for Offcanvas and Modals */
    .offcanvas-backdrop,
    .modal-backdrop {
        background-color: rgba(15, 23, 42, 0.75) !important;
        backdrop-filter: blur(6px) !important;
        -webkit-backdrop-filter: blur(6px) !important;
        z-index: 1040 !important;
        transition: opacity 0.3s ease-in-out !important;
    }

    .offcanvas-backdrop.show,
    .modal-backdrop.show {
        opacity: 1 !important;
    }

    .modal-backdrop {
        z-index: 1050 !important;
    }

    .luxury-order-modal {
        z-index: 1060 !important;
    }



    /* Cart Drawer styling */
    .luxury-cart-drawer {
        width: 420px !important;
        max-width: 95vw !important;
        background: #ffffff;
        z-index: 1050 !important;
        box-shadow: -10px 0 35px rgba(0, 0, 0, 0.25) !important;
    }

    .cart-drawer-header {
        border-bottom: 1px solid rgba(212, 175, 55, 0.25);
        background: #fffcf8;
        padding: 16px 20px;
    }

    .cart-drawer-heading {
        font-family: 'Gloock', serif;
        font-size: 20px;
        color: #2b2316;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .cart-drawer-body {
        padding: 16px;
        display: flex;
        flex-direction: column;
    }

    .cart-items-wrapper {
        display: flex;
        flex-direction: column;
        gap: 12px;
        width: 100%;
    }

    .cart-item-card {
        display: flex;
        align-items: center;
        gap: 12px;
        width: 100%;
        box-sizing: border-box;
        overflow: hidden;
        background: #fffcf8;
        border: 1px solid rgba(212, 175, 55, 0.25);
        border-radius: 18px !important;
        padding: 12px 14px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.02);
    }

    .cart-item-img-pod {
        width: 68px;
        height: 68px;
        border-radius: 14px !important;
        overflow: hidden;
        flex-shrink: 0;
        background: #ffffff;
        border: 1px solid rgba(212, 175, 55, 0.25);
    }

    .cart-item-img-pod img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .cart-item-info {
        flex: 1 1 auto;
        min-width: 0;
        overflow: hidden;
    }

    .cart-item-title {
        font-size: 14px;
        font-weight: 700;
        color: #2b2316;
        margin: 0 0 2px 0;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .cart-item-variant-label {
        font-size: 11px;
        font-weight: 700;
        color: #8c733e;
        background: rgba(212, 175, 55, 0.15);
        display: inline-block;
        padding: 2px 8px;
        border-radius: 50px !important;
        margin-bottom: 4px;
    }

    .cart-item-price {
        font-size: 14px;
        font-weight: 800;
        color: #b89028;
        margin: 0;
    }

    .cart-item-controls {
        display: flex;
        align-items: center;
        gap: 6px;
        margin-top: 6px;
    }

    .cart-qty-step-btn {
        width: 28px;
        height: 28px;
        border-radius: 50% !important;
        border: 1px solid rgba(212, 175, 55, 0.4);
        background: #ffffff;
        color: #2b2316;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        padding: 0;
        font-weight: bold;
        transition: all 0.2s;
    }

    .cart-qty-step-btn:hover {
        background: #d4af37;
        color: #ffffff;
        border-color: #d4af37;
    }

    .cart-qty-number {
        font-size: 13px;
        font-weight: 800;
        min-width: 20px;
        text-align: center;
    }

    /* Redesigned Enlarged 38px Circular Trash Button */
    .cart-item-trash-btn {
        width: 38px;
        height: 38px;
        border-radius: 50% !important;
        background: #fee2e2;
        color: #dc2626;
        border: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        flex-shrink: 0;
        transition: all 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);
        box-shadow: 0 2px 6px rgba(220, 38, 38, 0.15);
    }

    .cart-item-trash-btn i {
        font-size: 1.15rem;
        line-height: 1;
    }

    .cart-item-trash-btn:hover {
        background: #dc2626;
        color: #ffffff;
        transform: scale(1.08);
        box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3);
    }

    /* Redesigned Prominent Grand Total Display */
    .cart-grand-total-row {
        display: flex;
        justify-content: space-between;
        align-items: baseline;
        width: 100%;
        padding: 8px 0 12px 0;
    }

    .cart-grand-total-label {
        font-family: 'Gloock', serif;
        font-size: 1.25rem;
        font-weight: 700;
        color: #2b2316;
        line-height: 1;
    }

    .cart-grand-total-price {
        font-size: 1.45rem;
        font-weight: 800;
        color: #b89028;
        line-height: 1;
    }

    /* Dead-center Empty State */
    .cart-empty-center-pod {
        margin: auto 0;
        text-align: center;
        display: none;
        flex-direction: column;
        align-items: center;
        padding: 30px 10px;
    }

    .cart-empty-icon-circle {
        width: 80px;
        height: 80px;
        border-radius: 50% !important;
        background: #fbf7ee;
        color: #d4af37;
        font-size: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 14px;
    }

    .cart-empty-title {
        font-family: 'Gloock', serif;
        font-size: 18px;
        color: #2b2316;
        margin-bottom: 6px;
    }

    .cart-empty-sub {
        font-size: 13px;
        color: #7a6a4c;
        max-width: 260px;
        line-height: 1.4;
        margin-bottom: 20px;
    }

    .cart-empty-btn {
        border-radius: 50px !important;
        padding: 8px 22px;
        font-size: 13px;
        font-weight: 700;
        background: #d4af37 !important;
        color: #ffffff !important;
        border: none;
    }

    /* Perfect Centering & Alignment for WhatsApp Order Button */
    .btn-place-wa-order {
        height: 48px !important;
        width: 100% !important;
        border-radius: 50px !important;
        padding: 0 24px !important;
        background: #d4af37 !important;
        color: #ffffff !important;
        font-weight: 700 !important;
        font-size: 15px !important;
        border: none !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 10px !important;
        /* Perfect gap between WhatsApp icon and text */
        cursor: pointer !important;
        transition: all 0.25s ease !important;
        box-shadow: 0 4px 15px rgba(212, 175, 55, 0.3) !important;
        line-height: 1 !important;
        text-decoration: none !important;
    }

    .btn-place-wa-order i {
        font-size: 1.35rem !important;
        line-height: 1 !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
    }

    .btn-place-wa-order span {
        line-height: 1 !important;
        display: inline-block !important;
        transform: translateY(1px) !important;
        letter-spacing: 0.3px !important;
    }

    .btn-place-wa-order:hover {
        background: #b89028 !important;
        box-shadow: 0 6px 20px rgba(212, 175, 55, 0.45) !important;
        transform: translateY(-1px) !important;
    }

    /* Modal Backdrop & 3D Segmented Controls */
    .luxury-order-card {
        border-radius: 26px !important;
        border: 1px solid rgba(212, 175, 55, 0.3) !important;
        background: #ffffff;
    }

    .luxury-modal-heading {
        font-family: 'Gloock', serif;
        font-size: 18px;
        color: #2b2316;
    }

    .luxury-label {
        font-size: 13px;
        font-weight: 700;
        color: #443722;
        margin-bottom: 4px;
    }

    .luxury-input {
        border-radius: 14px !important;
        border: 1px solid rgba(212, 175, 55, 0.35);
        padding: 10px 14px;
        font-size: 13px;
    }

    .delivery-segmented-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(110px, 1fr));
        gap: 8px;
    }

    .delivery-card-segment {
        cursor: pointer;
        margin: 0;
    }

    .delivery-card-segment input {
        display: none;
    }

    .segment-pill-box {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 4px;
        padding: 10px 8px;
        background: #fbf7ee;
        border: 1px solid rgba(212, 175, 55, 0.25);
        border-radius: 14px !important;
        font-size: 12px;
        font-weight: 700;
        color: #55452a;
        transition: all 0.2s;
    }

    .segment-pill-box i {
        font-size: 18px;
        color: #b89028;
    }

    .delivery-card-segment input:checked+.segment-pill-box {
        background: #d4af37;
        color: #ffffff;
        border-color: #d4af37;
    }

    .delivery-card-segment input:checked+.segment-pill-box i {
        color: #ffffff;
    }

    .order-client-tip {
        background: #fffcf5;
        border: 1px dashed rgba(212, 175, 55, 0.4);
        border-radius: 12px !important;
        padding: 10px;
        display: flex;
        gap: 8px;
        align-items: flex-start;
        font-size: 12px;
        color: #6d5b3d;
    }

    .order-cancel-pill {
        border-radius: 50px !important;
        padding: 8px 20px;
    }

    .order-confirm-pill {
        border-radius: 50px !important;
        padding: 10px 22px;
        background: #d4af37 !important;
        color: #ffffff !important;
        font-weight: 700;
        border: none;
    }
</style>
