{{--
    Furniture cart drawer and checkout form. Markup only: the shared storefront script
    (public/js/store/storefront.js) fills and runs it through the data-* hooks.
--}}
@php
    $deliveryChoices = array_filter([
        'Order For Delivery' => (bool) ($deliveryOptions->order_for_delivery ?? true),
        'Take Away' => (bool) ($deliveryOptions->take_away ?? false),
        'Dine In' => (bool) ($deliveryOptions->dine_in ?? false),
    ]);
    $deliveryIcons = ['Order For Delivery' => 'ti-truck-delivery', 'Take Away' => 'ti-building-store', 'Dine In' => 'ti-tools-kitchen-2'];
@endphp

<style>
    /* 1. Global Dark Blur Backdrop */
    .modal-backdrop,
    .modal-backdrop.show {
        background-color: rgba(15, 23, 42, 0.8) !important;
        opacity: 1 !important;
        backdrop-filter: blur(5px) !important;
        -webkit-backdrop-filter: blur(5px) !important;
        z-index: 1050 !important;
    }

    #orderModal {
        z-index: 1055 !important;
    }

    #orderModal .modal-content {
        border: 1px solid rgba(255, 255, 255, 0.2);
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.45) !important;
        border-radius: 20px !important;
        overflow: hidden;
    }

    /* 2. Cart Offcanvas Drawer */
    .cart-drawer-wrapper {
        width: 100%;
        max-width: 440px;
        background-color: #ffffff;
        border-left: 1px solid #ede8df;
        z-index: 1045 !important;
    }

    .cart-drawer-header {
        border-bottom: 1px solid #ede8df;
        padding: 18px 20px;
    }

    .cart-drawer-body {
        position: relative;
        padding: 16px 18px;
        overflow-y: auto;
    }

    /* Dead-Center Empty Cart State */
    .empty-cart-state {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 100%;
        text-align: center;
        padding: 20px;
        display: none;
    }

    .empty-cart-icon-wrap {
        width: 90px;
        height: 90px;
        border-radius: 50%;
        background-color: #f5ece1;
        color: #cca47a;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 16px;
    }

    /* 3. Fully Contained Cart Item Card (Zero Overflow) */
    .cart-item-card {
        background: #ffffff;
        border: 1px solid #ede8df;
        border-radius: 14px;
        padding: 12px;
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        gap: 12px;
        width: 100%;
        box-sizing: border-box;
        overflow: hidden;
    }

    .cart-item-thumb {
        width: 66px;
        height: 66px;
        border-radius: 10px;
        object-fit: contain;
        background-color: #f8fafc;
        border: 1px solid #e2e8f0;
        flex-shrink: 0;
    }

    .cart-item-info {
        flex: 1 1 auto;
        min-width: 0;
        /* Prevents text overflow outside container */
        overflow: hidden;
    }

    .cart-item-title {
        font-size: 14px;
        font-weight: 700;
        color: #1e293b;
        margin: 0 0 2px 0;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        display: block;
        width: 100%;
    }

    .luxury-variant-pill {
        display: inline-block;
        font-size: 10px;
        font-weight: 700;
        color: #1f4b48;
        background-color: #e6f0ef;
        padding: 2px 8px;
        border-radius: 50px !important;
        margin-bottom: 3px;
        max-width: 100%;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .cart-item-price {
        font-weight: 700;
        font-size: 13.5px;
        color: #1f4b48;
        margin-bottom: 6px;
    }

    .cart-item-actions-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        width: 100%;
    }

    /* Circular Pill Trash Button */
    .btn-cart-trash {
        width: 32px;
        height: 32px;
        border-radius: 50% !important;
        border: 1px solid #fca5a5;
        background-color: #fee2e2;
        color: #dc2626;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0;
        cursor: pointer;
        transition: all 0.2s ease;
        flex-shrink: 0;
    }

    .btn-cart-trash:hover {
        background-color: #dc2626;
        color: #ffffff;
        border-color: #dc2626;
    }

    /* Circular Pill Quantity Step Buttons */
    .btn-qty-step {
        width: 30px;
        height: 30px;
        border: 1px solid #cbd5e1;
        background-color: #ffffff;
        border-radius: 50% !important;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #1e293b;
        cursor: pointer;
        transition: background-color 0.2s ease;
        flex-shrink: 0;
    }

    .btn-qty-step:hover {
        background-color: #f1f5f9;
    }

    .cart-qty-number {
        font-size: 14px;
        font-weight: 700;
        color: #1e293b;
        min-width: 24px;
        text-align: center;
    }

    /* Checkout Button */
    .btn-cart-checkout {
        background-color: #1f4b48;
        color: #ffffff !important;
        font-size: 15px;
        font-weight: 700;
        border-radius: 50px !important;
        padding: 13px 24px;
        width: 100%;
        border: none;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 12px rgba(31, 75, 72, 0.25);
        transition: transform 0.2s ease, background-color 0.2s ease;
    }

    .btn-cart-checkout:hover {
        background-color: #163836;
        color: #ffffff !important;
        transform: translateY(-1px);
    }

    /* Segmented Delivery Cards */
    .segmented-delivery-card {
        border: 1px solid #cbd5e1;
        border-radius: 50px !important;
        padding: 12px 20px;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .segmented-delivery-card:hover,
    input[type="radio"]:checked+.segmented-delivery-card {
        border-color: #1f4b48;
        background-color: #f0f7f6;
    }

    .checkout-form-input {
        border-radius: 50px !important;
        border: 1px solid #cbd5e1;
        padding: 10px 18px;
    }

    .checkout-form-textarea {
        border-radius: 16px !important;
        border: 1px solid #cbd5e1;
        padding: 12px 18px;
    }
</style>

{{-- Cart drawer --}}
<div class="offcanvas offcanvas-end cart-drawer-wrapper" tabindex="-1" id="offcanvasCart" aria-labelledby="offcanvasCartLabel"
    data-cart-drawer>
    <div class="cart-drawer-header d-flex align-items-center justify-content-between">
        <h3 class="fs-2 fw-bold mb-0 text-dark" id="offcanvasCartLabel">{{ __('Your Shopping Bag') }}</h3>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="{{ __('Close') }}"></button>
    </div>

    <div class="offcanvas-body cart-drawer-body">
        <div data-cart-items></div>

        <div class="empty-cart-state" data-cart-when-empty>
            <div class="empty-cart-icon-wrap">
                <i class="ti ti-shopping-cart-x" style="font-size: 38px;"></i>
            </div>
            <h4 class="fs-2 fw-bold text-dark mb-1">{{ __('Your Cart is Empty') }}</h4>
            <p class="text-muted fs-4 mb-3">{{ __('Explore our products and start adding your favorites.') }}</p>
            <button type="button" class="btn btn-cart-checkout" data-bs-dismiss="offcanvas">{{ __('Start Shopping') }}</button>
        </div>
    </div>

    <div class="offcanvas-footer border-top p-3" data-cart-when-filled hidden>
        <div class="d-flex align-items-center justify-content-between mb-3 px-2">
            <span class="fs-3 fw-bold text-dark">{{ __('Grand Total:') }}</span>
            <span class="fs-2 fw-bold" style="color: #1f4b48;" data-cart-total></span>
        </div>
        <button type="button" class="btn btn-cart-checkout" data-cart-checkout>
            <i class="ti ti-brand-whatsapp fs-2 me-2"></i> {{ __('Proceed to Checkout') }}
        </button>
    </div>
</div>

{{-- One cart line (copied by the storefront script for every item) --}}
<template data-cart-item>
    <div class="cart-item-card">
        <img class="cart-item-thumb" data-cart-image data-fallback="{{ asset('img/templates/furniture/placeholder.svg') }}" alt="">
        <div class="cart-item-info">
            <span class="luxury-variant-pill" data-cart-variant></span>
            <h4 class="cart-item-title" data-cart-name></h4>
            <div class="cart-item-price" data-cart-line-total></div>
            <div class="cart-item-actions-row">
                <div class="d-flex align-items-center gap-1">
                    <button type="button" class="btn-qty-step" data-cart-decrease aria-label="{{ __('Decrease') }}"><i class="ti ti-minus"></i></button>
                    <span class="cart-qty-number" data-cart-qty></span>
                    <button type="button" class="btn-qty-step" data-cart-increase aria-label="{{ __('Increase') }}"><i class="ti ti-plus"></i></button>
                </div>
                <button type="button" class="btn-cart-trash" data-cart-remove title="{{ __('Remove') }}" aria-label="{{ __('Remove') }}">
                    <i class="ti ti-trash"></i>
                </button>
            </div>
        </div>
    </div>
</template>

{{-- Checkout --}}
<div class="modal fade" id="orderModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-order-modal>
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-bottom py-3 px-4">
                <h4 class="modal-title fw-bold fs-2 text-dark">{{ __('Delivery Details') }}</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
            </div>
            <div class="modal-body p-4">
                <form data-order-form novalidate>
                    <div class="mb-3">
                        <label class="form-label required" for="order_cus_name">{{ __('Full Name') }}</label>
                        <input type="text" class="form-control checkout-form-input" id="order_cus_name" name="customer_name" autocomplete="name" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label required" for="order_cus_mobile">{{ __('Mobile Phone (with country code)') }}</label>
                        <input type="tel" inputmode="tel" class="form-control checkout-form-input" id="order_cus_mobile" name="customer_phone"
                            placeholder="e.g. 919876543210" autocomplete="tel" required>
                    </div>

                    @if (count($deliveryChoices) > 1)
                        <div class="mb-3">
                            <label class="form-label required">{{ __('Delivery Type') }}</label>
                            <div class="d-flex flex-column gap-2">
                                @foreach (array_keys($deliveryChoices) as $method)
                                    <label class="position-relative mb-0">
                                        <input type="radio" name="delivery_method" value="{{ $method }}" class="d-none" @checked($loop->first)>
                                        <div class="segmented-delivery-card d-flex align-items-center justify-content-between">
                                            <span class="fw-semibold text-dark">{{ __($method) }}</span>
                                            <i class="ti {{ $deliveryIcons[$method] }} fs-2" style="color: #1f4b48;"></i>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="mb-3" data-address-field>
                        <label class="form-label required" for="order_cus_address">{{ __('Delivery Address') }}</label>
                        <textarea class="form-control checkout-form-textarea" id="order_cus_address" name="delivery_address" rows="2" autocomplete="street-address"></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="order_cus_notes">{{ __('Order Notes (Optional)') }}</label>
                        <textarea class="form-control checkout-form-textarea" id="order_cus_notes" name="delivery_note" rows="2"></textarea>
                    </div>

                    <button type="submit" class="btn-cart-checkout py-3 mt-2">
                        <i class="ti ti-brand-whatsapp fs-2 me-2"></i> {{ __('Send WhatsApp Order') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
