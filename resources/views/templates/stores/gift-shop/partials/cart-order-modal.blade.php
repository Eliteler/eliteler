{{--
    Gift shop cart drawer and checkout form. Markup only: the shared storefront script
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
    /* High-Contrast Luxury Dark Backdrop with Blur Engine */
    .offcanvas-backdrop,
    .modal-backdrop {
        background-color: rgba(15, 23, 42, 0.75) !important;
        backdrop-filter: blur(8px) !important;
        -webkit-backdrop-filter: blur(8px) !important;
        transition: all 0.3s ease-in-out !important;
    }

    .offcanvas-backdrop.show,
    .modal-backdrop.show {
        opacity: 1 !important;
    }

    /* Ultra-Luxury Cart Offcanvas */
    .cart-drawer-container {
        width: 100%;
        max-width: 440px;
        border: none;
        border-radius: 28px 0 0 28px;
        box-shadow: -15px 0 40px rgba(0, 0, 0, 0.25);
        background: #fafaf9;
    }

    @media (max-width: 576px) {
        .cart-drawer-container {
            border-radius: 0;
            max-width: 100%;
        }
    }

    .cart-drawer-header {
        background: #ffffff;
        border-bottom: 1px solid #f1f5f9;
        padding: 18px 24px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .cart-header-title {
        font-size: 1.25rem;
        font-weight: 800;
        color: #1c1917;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .cart-count-pill {
        background: #fef2f2;
        color: #dc2626;
        font-size: 0.75rem;
        font-weight: 800;
        padding: 3px 10px;
        border-radius: 50px !important;
        border: 1px solid rgba(220, 38, 38, 0.2);
    }

    .cart-close-circle {
        width: 36px;
        height: 36px;
        border-radius: 50% !important;
        background: #f5f5f4;
        border: none;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #78716c;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .cart-close-circle:hover {
        background: #fee2e2;
        color: #dc2626;
    }

    /* Cart Body & Dead-Centered Empty State */
    .cart-drawer-body {
        padding: 20px;
        display: flex;
        flex-direction: column;
        height: 100%;
        overflow-y: auto;
    }

    .cart-empty-wrapper {
        margin: auto 0;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        padding: 2rem 1rem;
    }

    .cart-empty-icon-pod {
        width: 90px;
        height: 90px;
        border-radius: 50% !important;
        background: #fef2f2;
        color: #dc2626;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.8rem;
        margin-bottom: 1.25rem;
        box-shadow: 0 10px 25px rgba(220, 38, 38, 0.1);
    }

    /* Redesigned Luxury Cart Item Card */
    .cart-luxury-card {
        background: #ffffff;
        border: 1px solid #f1f5f9;
        border-radius: 20px !important;
        padding: 12px;
        margin-bottom: 14px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.03);
        display: flex;
        align-items: center;
        gap: 14px;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .cart-luxury-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.06);
    }

    .cart-item-media {
        width: 72px;
        height: 72px;
        border-radius: 14px;
        overflow: hidden;
        background: #fef2f2;
        flex-shrink: 0;
        border: 1px solid #f8fafc;
    }

    .cart-item-media img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .cart-item-content {
        flex: 1 1 auto;
        min-width: 0;
    }

    .cart-item-heading {
        font-size: 0.95rem;
        font-weight: 700;
        color: #1c1917;
        margin-bottom: 2px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        line-height: 1.3;
    }

    .cart-item-variant-tag {
        display: inline-block;
        font-size: 0.72rem;
        font-weight: 700;
        color: #dc2626;
        background: #fef2f2;
        padding: 2px 8px;
        border-radius: 50px !important;
        margin-bottom: 6px;
    }

    .cart-item-price-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-top: 4px;
    }

    .cart-item-total-price {
        font-size: 1rem;
        font-weight: 800;
        color: #dc2626;
        margin: 0;
    }

    /* Symmetrical Quantity Stepper */
    .cart-qty-pill {
        display: inline-flex;
        align-items: center;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 50px !important;
        padding: 2px 4px;
    }

    .cart-step-btn {
        width: 26px;
        height: 26px;
        border-radius: 50% !important;
        border: none;
        background: #ffffff;
        color: #1c1917;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.85rem;
        font-weight: bold;
        cursor: pointer;
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.08);
        transition: background 0.2s;
    }

    .cart-step-btn:hover {
        background: #fef2f2;
        color: #dc2626;
    }

    .cart-step-value {
        min-width: 26px;
        text-align: center;
        font-size: 0.85rem;
        font-weight: 700;
        color: #1c1917;
    }

    /* Trash Button Column */
    .cart-trash-action {
        width: 34px;
        height: 34px;
        border-radius: 50% !important;
        background: #fef2f2;
        color: #dc2626;
        border: none;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
        cursor: pointer;
        flex-shrink: 0;
        transition: all 0.2s ease;
    }

    .cart-trash-action:hover {
        background: #dc2626;
        color: #ffffff;
        transform: scale(1.05);
    }

    /* Cart Footer */
    .cart-drawer-footer {
        background: #ffffff;
        border-top: 1px solid #f1f5f9;
        padding: 20px 24px;
    }

    .cart-total-box {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 16px;
    }

    .cart-total-label {
        font-size: 1rem;
        font-weight: 600;
        color: #78716c;
    }

    .cart-total-amount {
        font-size: 1.4rem;
        font-weight: 800;
        color: #dc2626;
    }

    .btn-checkout-whatsapp {
        width: 100%;
        height: 50px;
        border-radius: 50px !important;
        background: #1c1917;
        color: #ffffff !important;
        border: none;
        font-size: 1rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        transition: all 0.3s ease;
        cursor: pointer;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
    }

    .btn-checkout-whatsapp:hover {
        background: #dc2626;
        box-shadow: 0 8px 25px rgba(220, 38, 38, 0.35);
    }

    /* Customer Order Modal Form Styles */
    .order-modal-card {
        border-radius: 28px !important;
        border: none;
        overflow: hidden;
        background: #ffffff;
        box-shadow: 0 25px 60px rgba(0, 0, 0, 0.3);
        padding: 28px 24px 24px;
    }

    .order-input-pill {
        border-radius: 50px !important;
        border: 1.5px solid #e2e8f0;
        background: #f8fafc;
        padding: 11px 18px;
        font-size: 0.95rem;
        color: #1c1917;
        transition: all 0.2s ease;
        width: 100%;
    }

    .order-input-pill:focus {
        border-color: #dc2626;
        background: #ffffff;
        box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.12);
        outline: none;
    }

    .order-textarea-pill {
        border-radius: 18px !important;
        border: 1.5px solid #e2e8f0;
        background: #f8fafc;
        padding: 12px 18px;
        font-size: 0.95rem;
        color: #1c1917;
        transition: all 0.2s ease;
        width: 100%;
        resize: none;
    }

    .order-textarea-pill:focus {
        border-color: #dc2626;
        background: #ffffff;
        box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.12);
        outline: none;
    }

    /* 3D Segmented Radio Delivery Cards */
    .delivery-radio-card {
        border: 2px solid #f1f5f9;
        border-radius: 18px;
        padding: 12px 16px;
        cursor: pointer;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        gap: 12px;
        background: #ffffff;
    }

    .delivery-radio-card:hover {
        border-color: #fca5a5;
    }

    .form-check-input:checked+.delivery-radio-label .delivery-radio-card {
        border-color: #dc2626;
        background: #fef2f2;
    }

    /* Symmetrical 50/50 Modal Action Buttons */
    .order-modal-action-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
        width: 100%;
        margin-top: 1.5rem;
        padding: 0;
    }

    .btn-order-back {
        width: 100%;
        height: 48px;
        border-radius: 50px !important;
        background: #f8fafc;
        color: #64748b !important;
        border: 1.5px solid #e2e8f0;
        font-size: 0.95rem;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
        cursor: pointer;
    }

    .btn-order-back:hover {
        background: #e2e8f0;
        color: #1e293b !important;
    }

    .btn-order-confirm {
        width: 100%;
        height: 48px;
        border-radius: 50px !important;
        background: #dc2626;
        color: #ffffff !important;
        border: none;
        font-size: 0.95rem;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        box-shadow: 0 6px 18px rgba(220, 38, 38, 0.35);
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .btn-order-confirm:hover {
        background: #b91c1c;
        box-shadow: 0 8px 25px rgba(220, 38, 38, 0.45);
        transform: translateY(-2px);
    }
</style>

{{-- Cart drawer --}}
<div class="offcanvas offcanvas-end cart-drawer-container" tabindex="-1" id="offcanvasCart" aria-labelledby="offcanvasCartLabel"
    data-cart-drawer>
    <div class="cart-drawer-header">
        <div class="d-flex align-items-center gap-2">
            <h3 class="cart-header-title" id="offcanvasCartLabel">
                <i class="ti ti-shopping-bag text-danger"></i>
                <span>{{ __('Shopping Bag') }}</span>
            </h3>
            <span class="cart-count-pill" data-cart-when-filled hidden><span data-cart-count>0</span> {{ __('Items') }}</span>
        </div>
        <button type="button" class="cart-close-circle" data-bs-dismiss="offcanvas" aria-label="{{ __('Close') }}">
            <i class="ti ti-x"></i>
        </button>
    </div>

    <div class="cart-drawer-body">
        <div data-cart-items></div>

        <div class="cart-empty-wrapper" data-cart-when-empty>
            <div class="cart-empty-icon-pod">
                <i class="ti ti-shopping-bag-x"></i>
            </div>
            <h4 class="fw-bold text-dark mb-1 fs-2">{{ __('Your Bag is Empty') }}</h4>
            <p class="text-muted fs-4 mb-4">{{ __('Explore our collection and pick wonderful gifts!') }}</p>
            <button type="button" class="btn btn-pill-action btn-danger px-4 py-2" data-bs-dismiss="offcanvas" style="background: #dc2626;">
                <i class="ti ti-gift"></i>
                <span>{{ __('Start Shopping') }}</span>
            </button>
        </div>
    </div>

    <div class="cart-drawer-footer" data-cart-when-filled hidden>
        <div class="cart-total-box">
            <span class="cart-total-label">{{ __('Grand Total:') }}</span>
            <span class="cart-total-amount" data-cart-total></span>
        </div>
        <button type="button" class="btn-checkout-whatsapp" data-cart-checkout>
            <i class="ti ti-brand-whatsapp fs-2"></i>
            <span>{{ __('Place WhatsApp Order') }}</span>
        </button>
    </div>
</div>

{{-- One cart line (copied by the storefront script for every item) --}}
<template data-cart-item>
    <div class="cart-luxury-card">
        <div class="cart-item-media">
            <img data-cart-image data-fallback="{{ asset('img/templates/gift-shop/placeholder.svg') }}" alt="">
        </div>
        <div class="cart-item-content">
            <h5 class="cart-item-heading" data-cart-name></h5>
            <span class="cart-item-variant-tag" data-cart-variant></span>
            <div class="cart-item-price-row">
                <span class="cart-item-total-price" data-cart-line-total></span>
                <div class="cart-qty-pill">
                    <button type="button" class="cart-step-btn" data-cart-decrease aria-label="{{ __('Decrease') }}">-</button>
                    <span class="cart-step-value" data-cart-qty></span>
                    <button type="button" class="cart-step-btn" data-cart-increase aria-label="{{ __('Increase') }}">+</button>
                </div>
            </div>
        </div>
        <button type="button" class="cart-trash-action" data-cart-remove title="{{ __('Remove Item') }}" aria-label="{{ __('Remove Item') }}">
            <i class="ti ti-trash"></i>
        </button>
    </div>
</template>

{{-- Checkout --}}
<div class="modal fade" id="orderModal" tabindex="-1" aria-hidden="true" data-order-modal>
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content order-modal-card" data-order-form novalidate>
            <div class="d-flex align-items-center justify-content-between mb-4">
                <h3 class="fw-bold text-dark fs-2 mb-0">{{ __('Customer & Delivery Details') }}</h3>
                <button type="button" class="cart-close-circle" data-bs-dismiss="modal" aria-label="{{ __('Close') }}">
                    <i class="ti ti-x"></i>
                </button>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold required text-dark" for="order_name">{{ __('Full Name') }}</label>
                <input type="text" class="order-input-pill" id="order_name" name="customer_name"
                    placeholder="{{ __('Enter your full name') }}" autocomplete="name" required>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold required text-dark" for="order_phone">{{ __('WhatsApp / Mobile Number') }}</label>
                <input type="tel" inputmode="tel" class="order-input-pill" id="order_phone" name="customer_phone"
                    placeholder="{{ __('e.g. 9876543210') }}" autocomplete="tel" required>
            </div>

            @if (count($deliveryChoices) > 1)
                <div class="mb-3">
                    <label class="form-label fw-bold text-dark mb-2">{{ __('Delivery Option') }}</label>
                    <div class="d-flex flex-column gap-2">
                        @foreach (array_keys($deliveryChoices) as $method)
                            <label class="delivery-radio-label w-100 m-0">
                                <input class="form-check-input d-none" type="radio" name="delivery_method" value="{{ $method }}"
                                    @checked($loop->first)>
                                <div class="delivery-radio-card">
                                    <i class="ti {{ $deliveryIcons[$method] }} text-danger fs-2"></i>
                                    <span class="fw-bold fs-3 text-dark">{{ __($method) }}</span>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="mb-3" data-address-field>
                <label class="form-label fw-bold required text-dark" for="order_address">{{ __('Delivery Address') }}</label>
                <input type="text" class="order-input-pill" id="order_address" name="delivery_address"
                    placeholder="{{ __('Street, Area, Landmark') }}" autocomplete="street-address">
            </div>

            <div class="mb-2">
                <label class="form-label fw-bold text-dark" for="order_notes">{{ __('Order Note (Optional)') }}</label>
                <textarea class="order-textarea-pill" id="order_notes" name="delivery_note" rows="2"
                    placeholder="{{ __('Add special packaging or delivery instructions') }}"></textarea>
            </div>

            <div class="order-modal-action-grid">
                <button type="button" class="btn-order-back" data-bs-dismiss="modal">{{ __('Back') }}</button>
                <button type="submit" class="btn-order-confirm">
                    <i class="ti ti-brand-whatsapp fs-2"></i>
                    <span>{{ __('Confirm & Send') }}</span>
                </button>
            </div>
        </form>
    </div>
</div>
