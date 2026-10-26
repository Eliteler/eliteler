{{--
    E-commerce cart drawer and checkout form. Markup only: the shared storefront script
    (public/js/store/storefront.js) fills and runs it through the data-* hooks.
--}}
@php
    $deliveryChoices = array_filter([
        'Order For Delivery' => (bool) ($deliveryOptions->order_for_delivery ?? true),
        'Take Away' => (bool) ($deliveryOptions->take_away ?? false),
        'Dine In' => (bool) ($deliveryOptions->dine_in ?? false),
    ]);
    $deliveryLabels = ['Order For Delivery' => 'Delivery', 'Take Away' => 'Take Away', 'Dine In' => 'Dine In'];
    $deliveryIcons = ['Order For Delivery' => 'ti-truck', 'Take Away' => 'ti-shopping-bag', 'Dine In' => 'ti-tools-kitchen-2'];
@endphp

{{-- Cart drawer --}}
<div class="offcanvas offcanvas-end luxury-cart-drawer" tabindex="-1" id="offcanvasEnd" aria-labelledby="offcanvasEndLabel"
    data-cart-drawer>
    <div class="offcanvas-header luxury-cart-header border-bottom px-4 py-3">
        <div class="d-flex align-items-center gap-2">
            <div class="luxury-cart-header-icon">
                <i class="ti ti-shopping-bag fs-2 text-e-commerce-blue"></i>
            </div>
            <div>
                <h4 class="offcanvas-title fw-bold text-dark mb-0 fs-2" id="offcanvasEndLabel">{{ __('Your Cart') }}</h4>
                <small class="text-muted fs-5"><span data-cart-count>0</span> {{ __('items') }}</small>
            </div>
        </div>
        <button type="button" class="btn-close luxury-modal-close" data-bs-dismiss="offcanvas" aria-label="{{ __('Close') }}"></button>
    </div>

    <div class="offcanvas-body luxury-cart-body p-4">
        <div data-cart-items></div>

        <div class="text-center py-5" data-cart-when-empty>
            <div class="luxury-empty-cart-icon mx-auto mb-3">
                <i class="ti ti-shopping-bag-x fs-1 text-muted"></i>
            </div>
            <h3 class="text-dark fw-bold mb-1">{{ __('Your cart is empty') }}</h3>
            <p class="text-muted fs-4 mb-4">{{ __('Looks like you haven\'t added any items yet.') }}</p>
            <button type="button" class="btn btn-e-commerce-blue luxury-btn text-white px-4 py-2" data-bs-dismiss="offcanvas">
                <i class="ti ti-arrow-left me-1"></i> {{ __('Start Shopping') }}
            </button>
        </div>
    </div>

    <div class="offcanvas-footer luxury-cart-footer border-top p-4 bg-white" data-cart-when-filled hidden>
        <div class="mb-3">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <span class="text-muted fs-4">{{ __('Subtotal') }}</span>
                <span class="fs-3 fw-bold text-dark" data-cart-total></span>
            </div>
            <div class="d-flex justify-content-between align-items-center">
                <span class="fs-2 fw-bold text-dark">{{ __('Grand Total') }}</span>
                <span class="fs-1 fw-bold text-e-commerce-blue" data-cart-total></span>
            </div>
        </div>
        <button type="button" class="btn btn-e-commerce-blue luxury-checkout-btn text-white w-100" data-cart-checkout>
            <i class="ti ti-brand-whatsapp fs-2 me-1"></i>
            <span>{{ __('Proceed to WhatsApp Checkout') }}</span>
        </button>
    </div>
</div>

{{-- One cart line (copied by the storefront script for every item) --}}
<template data-cart-item>
    <div class="luxury-cart-item">
        <img class="luxury-cart-item-img" data-cart-image data-fallback="{{ asset('img/templates/e-commerce/placeholder.svg') }}" alt="">
        <div class="luxury-cart-item-details">
            <div class="luxury-cart-item-title" data-cart-name></div>
            <span class="luxury-variant-pill" data-cart-variant></span>
            <div class="luxury-cart-item-price" data-cart-line-total></div>
            <div class="d-flex align-items-center justify-content-between mt-1">
                <div class="luxury-qty-control">
                    <button type="button" class="luxury-qty-btn" data-cart-decrease aria-label="{{ __('Decrease') }}"><i class="ti ti-minus"></i></button>
                    <span class="luxury-qty-val" data-cart-qty></span>
                    <button type="button" class="luxury-qty-btn" data-cart-increase aria-label="{{ __('Increase') }}"><i class="ti ti-plus"></i></button>
                </div>
                <button type="button" class="luxury-remove-btn" data-cart-remove title="{{ __('Remove') }}" aria-label="{{ __('Remove') }}">
                    <i class="ti ti-trash"></i>
                </button>
            </div>
        </div>
    </div>
</template>

{{-- Checkout --}}
<div class="modal fade luxury-order-modal" id="orderModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-order-modal>
    <div class="modal-dialog modal-dialog-centered luxury-order-dialog" role="document">
        <form class="modal-content luxury-order-card border-0 shadow-lg" data-order-form novalidate>
            <div class="modal-header border-bottom px-4 py-3">
                <div class="d-flex align-items-center gap-2">
                    <div class="luxury-order-header-icon">
                        <i class="ti ti-clipboard-check fs-2 text-e-commerce-blue"></i>
                    </div>
                    <div>
                        <h4 class="modal-title fw-bold text-dark mb-0">{{ __('Checkout Details') }}</h4>
                        <small class="text-muted">{{ __('Please provide your delivery information') }}</small>
                    </div>
                </div>
                <button type="button" class="btn-close luxury-modal-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
            </div>

            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="form-label luxury-form-label required" for="cus_name">{{ __('Full Name') }}</label>
                    <input type="text" class="form-control luxury-input" id="cus_name" name="customer_name" autocomplete="name"
                        placeholder="{{ __('Enter your full name') }}" required />
                </div>

                <div class="mb-3">
                    <label class="form-label luxury-form-label required" for="cus_mobile">{{ __('Mobile Number') }}</label>
                    <input type="tel" inputmode="tel" class="form-control luxury-input" id="cus_mobile" name="customer_phone" autocomplete="tel"
                        placeholder="{{ __('Mobile number with country code') }}" required />
                </div>

                @if (count($deliveryChoices) > 1)
                    <div class="mb-3">
                        <label class="form-label luxury-form-label required mb-2">{{ __('Delivery Type') }}</label>
                        <div class="row g-2">
                            @foreach (array_keys($deliveryChoices) as $method)
                                <div class="col-12 col-sm-4">
                                    <label class="luxury-delivery-card">
                                        <input type="radio" name="delivery_method" value="{{ $method }}" @checked($loop->first)>
                                        <div class="luxury-delivery-card-inner text-center">
                                            <i class="ti {{ $deliveryIcons[$method] }} fs-2 d-block mb-1"></i>
                                            <span>{{ __($deliveryLabels[$method]) }}</span>
                                        </div>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="mb-3" data-address-field>
                    <label class="form-label luxury-form-label required" for="cus_address">{{ __('Delivery Address') }}</label>
                    <textarea class="form-control luxury-input luxury-textarea" id="cus_address" name="delivery_address" rows="2" autocomplete="street-address"
                        placeholder="{{ __('House/Street, Area, City, Pincode') }}"></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label luxury-form-label" for="cus_notes">{{ __('Notes / Special Instructions') }}</label>
                    <textarea class="form-control luxury-input" id="cus_notes" name="delivery_note" rows="2"
                        placeholder="{{ __('Any special instructions for the store...') }}"></textarea>
                </div>

                <div class="luxury-info-callout d-flex align-items-center gap-2 p-3">
                    <i class="ti ti-brand-whatsapp fs-1 text-success flex-shrink-0"></i>
                    <small class="text-muted lh-sm">
                        {{ __("After clicking 'Confirm & Send', WhatsApp will open automatically. Tap 'Send' to dispatch your order to the shop owner.") }}
                    </small>
                </div>
            </div>

            <div class="modal-footer border-top p-3 px-4 bg-light">
                <button type="button" class="btn luxury-btn-secondary" data-bs-dismiss="modal">{{ __('Close') }}</button>
                <button type="submit" class="btn btn-e-commerce-blue luxury-btn-confirm text-white">
                    <i class="ti ti-send me-1"></i> {{ __('Confirm & Send') }}
                </button>
            </div>
        </form>
    </div>
</div>

<style>
    /* Cart Offcanvas Styles */
    .offcanvas.luxury-cart-drawer {
        width: 100% !important;
        max-width: 480px !important;
        z-index: 1055 !important;
        background-color: #f8fafc !important;
        border-left: 1px solid rgba(0, 0, 0, 0.08) !important;
    }

    .luxury-cart-header {
        background-color: #ffffff;
    }

    .luxury-cart-header-icon,
    .luxury-order-header-icon {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        background-color: #e8f2fd;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .luxury-empty-cart-icon {
        width: 76px;
        height: 76px;
        border-radius: 50%;
        background-color: #edf2f7;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .luxury-cart-item {
        background-color: #ffffff;
        border-radius: 16px;
        border: 1px solid #edf2f7;
        padding: 14px;
        margin-bottom: 12px;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.03);
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .luxury-cart-item-img {
        width: 70px;
        height: 70px;
        border-radius: 12px;
        object-fit: contain;
        background-color: #f8fafc;
        border: 1px solid #f1f5f9;
        flex-shrink: 0;
    }

    .luxury-cart-item-details {
        flex: 1 1 0% !important;
        min-width: 0 !important;
        overflow: hidden !important;
    }

    .luxury-cart-item-title {
        font-weight: 700;
        font-size: 0.95rem;
        white-space: nowrap !important;
        overflow: hidden !important;
        text-overflow: ellipsis !important;
        color: #1e293b;
        margin-bottom: 2px;
    }

    .luxury-variant-pill {
        display: inline-block;
        padding: 2px 8px;
        font-size: 0.72rem;
        font-weight: 700;
        color: #206bc4;
        background-color: #e8f2fd;
        border-radius: 20px;
        margin-bottom: 4px;
    }

    .luxury-cart-item-price {
        font-weight: 700;
        color: #206bc4;
        font-size: 1rem;
        margin-bottom: 4px;
    }

    .luxury-qty-control {
        display: inline-flex;
        align-items: center;
        background-color: #f1f5f9;
        border-radius: 8px;
        padding: 2px;
    }

    .luxury-qty-btn {
        width: 28px;
        height: 28px;
        border-radius: 6px;
        border: none;
        background-color: #ffffff;
        color: #1e293b;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
    }

    .luxury-qty-val {
        min-width: 28px;
        text-align: center;
        font-weight: 700;
        font-size: 0.9rem;
    }

    .luxury-remove-btn {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        border: none;
        background-color: #fee2e2;
        color: #ef4444;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        margin-left: 8px;
    }

    /* Checkout Button Rules: Strictly hides when cart is 0 */
    .luxury-checkout-btn {
        height: 48px !important;
        border-radius: 14px !important;
        font-weight: 700 !important;
        font-size: 1rem !important;
        align-items: center !important;
        justify-content: center !important;
        box-shadow: 0 4px 15px rgba(32, 107, 196, 0.3) !important;
    }

    .luxury-checkout-btn.d-none {
        display: none !important;
    }

    .luxury-checkout-btn:not(.d-none) {
        display: inline-flex !important;
    }

    .luxury-order-modal {
        background: rgba(15, 23, 42, 0.65) !important;
        z-index: 1060 !important;
    }

    .luxury-order-dialog {
        max-width: 480px !important;
        margin: 0 auto;
        padding: 16px;
    }

    .luxury-order-card {
        border-radius: 24px !important;
        background-color: #ffffff !important;
        overflow: hidden !important;
    }

    .luxury-form-label {
        font-size: 0.85rem;
        font-weight: 700;
        color: #334155;
        margin-bottom: 6px;
    }

    .luxury-input {
        height: 46px !important;
        border-radius: 12px !important;
        border: 1.5px solid #e2e8f0 !important;
        padding: 10px 14px !important;
        font-size: 0.92rem !important;
    }

    .luxury-input:focus {
        border-color: #206bc4 !important;
        box-shadow: 0 0 0 3px rgba(32, 107, 196, 0.15) !important;
    }

    .luxury-textarea {
        height: auto !important;
    }

    .luxury-delivery-card {
        display: block;
        cursor: pointer;
        user-select: none;
    }

    .luxury-delivery-card input {
        display: none;
    }

    .luxury-delivery-card-inner {
        padding: 12px 8px;
        border-radius: 12px;
        border: 1.5px solid #e2e8f0;
        background-color: #f8fafc;
        color: #64748b;
        font-weight: 600;
        font-size: 0.82rem;
        transition: all 0.2s ease;
    }

    .luxury-delivery-card input:checked+.luxury-delivery-card-inner {
        border-color: #206bc4;
        background-color: #e8f2fd;
        color: #206bc4;
        box-shadow: 0 2px 8px rgba(32, 107, 196, 0.15);
    }

    .luxury-info-callout {
        background-color: #f0fdf4;
        border-radius: 12px;
        border: 1px solid #dcfce7;
    }

    .luxury-btn-secondary {
        height: 44px !important;
        border-radius: 12px !important;
        background-color: #e2e8f0 !important;
        color: #475569 !important;
        font-weight: 600 !important;
        padding: 0 20px !important;
        border: none !important;
    }

    .luxury-btn-confirm {
        height: 44px !important;
        border-radius: 12px !important;
        font-weight: 700 !important;
        font-size: 0.95rem !important;
        padding: 0 24px !important;
        box-shadow: 0 4px 14px rgba(32, 107, 196, 0.3) !important;
        border: none !important;
    }
</style>
