{{--
    Fashion cart drawer and checkout form. Markup only: the shared storefront script
    (public/js/store/storefront.js) fills and runs it through the data-* hooks.
--}}
@php
    $deliveryChoices = array_filter([
        'Order For Delivery' => (bool) ($deliveryOptions->order_for_delivery ?? true),
        'Take Away' => (bool) ($deliveryOptions->take_away ?? false),
        'Dine In' => (bool) ($deliveryOptions->dine_in ?? false),
    ]);
@endphp

<style>
    /* Offcanvas Dark Backdrop & Blur */
    .offcanvas-backdrop {
        background-color: rgba(15, 23, 42, 0.75) !important;
        backdrop-filter: blur(4px) !important;
        z-index: 1045 !important;
    }

    .offcanvas-backdrop.show {
        opacity: 1 !important;
    }

    .offcanvas.offcanvas-end {
        border-left: 1px solid rgba(0, 0, 0, 0.1);
        box-shadow: -10px 0 35px rgba(0, 0, 0, 0.25);
        z-index: 1050 !important;
        width: 100%;
        max-width: 400px;
    }

    .offcanvas-body {
        position: relative;
        height: 100%;
        padding: 0;
        overflow: hidden;
    }

    /* Modal Layering */
    .fashion-modal-backdrop {
        background: rgba(15, 23, 42, 0.75) !important;
        backdrop-filter: blur(4px) !important;
        z-index: 1055 !important;
    }

    .fashion-modal-content {
        border-radius: 0px !important;
        border: 1px solid #e5e7eb;
    }

    /* Cart Items List */
    .fashion-cart-scroll-area {
        height: 100%;
        overflow-y: auto;
        padding: 20px;
    }

    .fashion-cart-item {
        border-bottom: 1px solid #f1f5f9;
        padding-bottom: 16px;
        margin-bottom: 16px;
    }

    .fashion-cart-thumb {
        width: 60px;
        height: 60px;
        object-fit: cover;
        border: 1px solid #e2e8f0;
    }

    .fashion-qty-group {
        display: inline-flex;
        align-items: center;
        border: 1px solid #cbd5e1;
        height: 36px;
    }

    .fashion-qty-btn {
        width: 34px;
        height: 100%;
        background-color: #f8fafc;
        border: none;
        color: #0f172a;
        font-size: 16px;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: background-color 0.2s ease;
    }

    .fashion-qty-btn:hover {
        background-color: #e2e8f0;
    }

    .fashion-qty-val {
        padding: 0 14px;
        font-size: 14px;
        font-weight: 800;
        color: #0f172a;
    }

    /* Luxury Trash Delete Button */
    .fashion-cart-trash-btn {
        width: 36px;
        height: 36px;
        background-color: #fee2e2;
        border: 1px solid #fca5a5;
        border-radius: 0px;
        color: #dc2626;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s ease;
        padding: 0;
    }

    .fashion-cart-trash-btn i,
    .fashion-cart-trash-btn svg {
        font-size: 18px !important;
        width: 18px;
        height: 18px;
    }

    .fashion-cart-trash-btn:hover {
        background-color: #dc2626;
        border-color: #dc2626;
        color: #ffffff;
        transform: scale(1.05);
    }

    /* Mathematical Dead-Center Empty Cart Stage */
    .fashion-empty-cart-stage {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 100%;
        max-width: 320px;
        padding: 20px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        box-sizing: border-box;
    }

    .fashion-empty-icon-circle {
        width: 90px;
        height: 90px;
        background-color: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #64748b;
        margin-bottom: 20px;
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.04);
    }

    .fashion-empty-icon-circle i {
        font-size: 38px !important;
        line-height: 1;
    }

    .fashion-empty-title {
        font-family: 'Oswald', sans-serif;
        font-size: 24px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #0f172a;
        margin-bottom: 8px;
        line-height: 1.2;
    }

    .fashion-empty-text {
        font-size: 14px;
        color: #64748b;
        line-height: 1.5;
        margin-bottom: 24px;
    }

    .fashion-empty-btn {
        background-color: #0f172a;
        color: #ffffff !important;
        border: none;
        padding: 13px 28px;
        font-size: 13px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        border-radius: 0px;
        cursor: pointer;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        text-decoration: none !important;
    }

    .fashion-empty-btn:hover {
        background-color: #1e293b;
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(15, 23, 42, 0.15);
    }

    /* Delivery Segmented Radio Cards */
    .delivery-radio-card {
        border: 1px solid #d1d5db;
        padding: 12px 14px;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 10px;
        transition: all 0.2s ease;
    }

    .delivery-radio-card:hover {
        border-color: #111827;
    }

    .delivery-radio-card input[type="radio"]:checked+.delivery-label {
        font-weight: 700;
        color: #111827;
    }

    .luxury-variant-pill {
        display: inline-block;
        background-color: #f1f5f9;
        color: #334155;
        font-size: 11px;
        font-weight: 700;
        padding: 2px 8px;
        border: 1px solid #e2e8f0;
        margin-top: 3px;
        text-transform: uppercase;
    }
</style>

{{-- Cart drawer --}}
<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasEnd" aria-labelledby="offcanvasEndLabel" data-cart-drawer>
    <div class="offcanvas-header border-bottom py-3 px-4">
        <h3 class="offcanvas-title head fs-2 mb-0" id="offcanvasEndLabel">{{ __('Cart Items') }}</h3>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="{{ __('Close') }}"></button>
    </div>

    <div class="offcanvas-body">
        <div class="fashion-cart-scroll-area" data-cart-when-filled hidden>
            <div class="d-flex flex-column" data-cart-items></div>
        </div>

        <div class="fashion-empty-cart-stage" data-cart-when-empty>
            <div class="fashion-empty-icon-circle">
                <i class="ti ti-shopping-bag-x"></i>
            </div>
            <h4 class="fashion-empty-title">{{ __('Your Cart is Empty') }}</h4>
            <p class="fashion-empty-text">{{ __('Looks like you have not added any pieces to your bag yet.') }}</p>
            <button type="button" class="fashion-empty-btn" data-bs-dismiss="offcanvas">
                <i class="ti ti-shopping-bag fs-3"></i>
                <span>{{ __('Start Shopping') }}</span>
            </button>
        </div>
    </div>

    <div class="offcanvas-footer p-3 p-md-4 border-top" data-cart-when-filled hidden>
        <div class="d-flex justify-content-between align-items-center fs-2 font-bold text-dark mb-3">
            <span>{{ __('Grand Total') }}</span>
            <span data-cart-total></span>
        </div>
        <button type="button" class="btn btn-dark w-100 py-3 fs-3 font-bold text-uppercase" data-cart-checkout>
            {{ __('Place WhatsApp Order') }}
        </button>
    </div>
</div>

{{-- One cart line (copied by the storefront script for every item) --}}
<template data-cart-item>
    <div class="fashion-cart-item">
        <div class="d-flex align-items-center gap-3">
            <img class="fashion-cart-thumb" data-cart-image data-fallback="{{ asset('img/templates/fashion/placeholder.svg') }}" alt="">
            <div class="flex-grow-1 min-w-0">
                <h5 class="mb-0 text-dark fs-3 font-bold text-truncate" data-cart-name></h5>
                <span class="luxury-variant-pill" data-cart-variant></span>
                <div class="text-secondary fw-bold mt-1" data-cart-unit-price></div>
            </div>
        </div>
        <div class="d-flex align-items-center justify-content-between mt-3">
            <div class="fashion-qty-group">
                <button type="button" class="fashion-qty-btn" data-cart-decrease aria-label="{{ __('Decrease') }}">-</button>
                <span class="fashion-qty-val" data-cart-qty></span>
                <button type="button" class="fashion-qty-btn" data-cart-increase aria-label="{{ __('Increase') }}">+</button>
            </div>
            <div class="d-flex align-items-center gap-3">
                <span class="fw-bold fs-3 text-dark" data-cart-line-total></span>
                <button type="button" class="fashion-cart-trash-btn" data-cart-remove title="{{ __('Remove Item') }}" aria-label="{{ __('Remove Item') }}">
                    <i class="ti ti-trash"></i>
                </button>
            </div>
        </div>
    </div>
</template>

{{-- Checkout --}}
<div class="modal fade fashion-modal-backdrop" id="orderModal" tabindex="-1" aria-hidden="true" data-order-modal>
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content fashion-modal-content" data-order-form novalidate>
            <div class="modal-header border-bottom">
                <h5 class="modal-title head fs-2 mb-0">{{ __('Order Details') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="form-label required font-bold" for="cus_name">{{ __('Full Name') }}</label>
                    <input type="text" class="form-control" id="cus_name" name="customer_name" autocomplete="name" required
                        placeholder="{{ __('Enter your full name') }}" />
                </div>
                <div class="mb-3">
                    <label class="form-label required font-bold" for="cus_mobile">{{ __('Mobile (with Country Code)') }}</label>
                    <input type="tel" inputmode="tel" class="form-control" id="cus_mobile" name="customer_phone" autocomplete="tel" required
                        placeholder="{{ __('e.g. 919876543210 (without +)') }}" />
                </div>

                @if (count($deliveryChoices) > 1)
                    <div class="mb-3">
                        <label class="form-label required font-bold mb-2">{{ __('Delivery Type') }}</label>
                        <div class="d-flex flex-column gap-2">
                            @foreach (array_keys($deliveryChoices) as $method)
                                <label class="delivery-radio-card">
                                    <input type="radio" name="delivery_method" value="{{ $method }}" @checked($loop->first)>
                                    <span class="delivery-label">{{ __($method) }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="mb-3" data-address-field>
                    <label class="form-label required font-bold" for="cus_address">{{ __('Delivery Address') }}</label>
                    <input type="text" class="form-control" id="cus_address" name="delivery_address" autocomplete="street-address"
                        placeholder="{{ __('Enter complete address') }}" />
                </div>

                <div class="mb-3">
                    <label class="form-label font-bold" for="cus_notes">{{ __('Order Notes (Optional)') }}</label>
                    <textarea class="form-control" id="cus_notes" name="delivery_note" rows="2" placeholder="{{ __('Special instructions...') }}"></textarea>
                </div>
            </div>
            <div class="modal-footer border-top">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Close') }}</button>
                <button type="submit" class="btn btn-dark">{{ __('Confirm Order') }}</button>
            </div>
        </form>
    </div>
</div>
