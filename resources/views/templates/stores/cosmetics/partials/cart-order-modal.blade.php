{{--
    Cosmetics cart drawer and checkout form. Markup only: the shared storefront script
    (public/js/store/storefront.js) fills and runs it through the data-* hooks.
--}}
<style>
    /* Responsive Cart Drawer */
    .offcanvas.cart-drawer {
        z-index: 1055 !important;
        background-color: #ffffff !important;
        width: 100% !important;
        max-width: 500px !important;
        box-shadow: -10px 0 35px rgba(0, 0, 0, 0.15) !important;
        border-left: 1px solid #f1f5f9 !important;
    }

    @media (max-width: 576px) {
        .offcanvas.cart-drawer {
            width: 100vw !important;
            max-width: 100vw !important;
        }
    }

    .cart-drawer .offcanvas-body {
        overflow-x: hidden !important;
    }

    .offcanvas-backdrop {
        z-index: 1050 !important;
        background-color: rgba(0, 0, 0, 0.5) !important;
    }

    .cart-drawer-header {
        background-color: #ffffff;
        padding: 1.25rem 1.5rem;
    }

    /* Fixed Cart Card Container */
    .cart-item-card {
        border-radius: 16px !important;
        border: 1px solid #f1f5f9 !important;
        background-color: #ffffff !important;
        overflow: hidden !important;
        width: 100% !important;
        box-sizing: border-box !important;
    }

    .cart-item-card:hover {
        border-color: #fbcfe8 !important;
        box-shadow: 0 4px 15px rgba(233, 30, 99, 0.06) !important;
    }

    .cart-item-img-container {
        width: 76px !important;
        height: 76px !important;
        flex-shrink: 0 !important;
        background: linear-gradient(145deg, #fff5f7 0%, #fdf2f4 100%);
        border-radius: 12px;
        overflow: hidden;
    }

    /* Strict Constraint to Prevent Any Overflow */
    .cart-item-info-col {
        flex: 1 1 0% !important;
        min-width: 0 !important;
        max-width: calc(100% - 92px) !important;
        overflow: hidden !important;
    }

    .cart-item-name {
        font-size: 14px !important;
        font-weight: 700 !important;
        color: #0f172a !important;
        white-space: nowrap !important;
        overflow: hidden !important;
        text-overflow: ellipsis !important;
        display: block !important;
        width: 100% !important;
    }

    .cart-item-subtitle {
        font-size: 12px !important;
        color: #64748b !important;
        white-space: nowrap !important;
        overflow: hidden !important;
        text-overflow: ellipsis !important;
        display: block !important;
        width: 100% !important;
    }

    .cart-qty-pill {
        border-radius: 10px !important;
        border: 1px solid #e2e8f0 !important;
        background-color: #f8fafc !important;
        padding: 2px 4px;
        height: 32px;
    }

    .cart-btn-step {
        width: 26px;
        height: 26px;
        border-radius: 8px !important;
        color: #475569 !important;
        background: transparent !important;
    }

    .cart-btn-step:hover {
        background-color: #e2e8f0 !important;
        color: #0f172a !important;
    }

    .cart-qty-val {
        min-width: 28px;
        text-align: center;
        font-size: 13px;
        font-weight: 700;
        color: #0f172a;
    }

    .cart-btn-trash {
        width: 32px !important;
        height: 32px !important;
        flex-shrink: 0 !important;
        border-radius: 10px !important;
        background-color: #fef2f2 !important;
        color: #ef4444 !important;
        border: none !important;
        transition: all 0.2s ease !important;
    }

    .cart-btn-trash:hover {
        background-color: #ef4444 !important;
        color: #ffffff !important;
    }

    .empty-cart-icon-box {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background-color: #fff1f4;
        color: #e91e63;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1rem auto;
    }

    .cart-pricing-box {
        background: #f8fafc;
        border-radius: 14px;
        border: 1px solid #f1f5f9;
        padding: 12px 16px;
    }

    .btn-checkout-wa {
        height: 40px !important;
        border-radius: 12px !important;
        background-color: #e91e63 !important;
        color: #ffffff !important;
        border: none !important;
        box-shadow: 0 4px 15px rgba(233, 30, 99, 0.3) !important;
        transition: all 0.25s ease !important;
    }

    .btn-checkout-wa:hover {
        background-color: #d81b60 !important;
        transform: translateY(-1px);
        box-shadow: 0 6px 20px rgba(233, 30, 99, 0.4) !important;
    }

    /* Modal Responsive Styling */
    .order-dialog-card {
        border-radius: 20px !important;
        border: 1px solid #f1f5f9 !important;
        overflow: hidden;
    }

    .delivery-radio-card {
        border: 1.5px solid #e2e8f0;
        border-radius: 12px;
        padding: 10px 14px;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .delivery-radio-card:hover {
        border-color: #f43f5e;
        background-color: #fff1f4;
    }

    .form-check-input:checked~.delivery-radio-label {
        color: #e91e63 !important;
        font-weight: 700;
    }
</style>

{{-- Cart drawer --}}
<div class="offcanvas offcanvas-end cart-drawer" tabindex="-1" id="offcanvasEnd" aria-labelledby="cartDrawerTitle"
    data-cart-drawer>
    <div class="offcanvas-header cart-drawer-header border-bottom">
        <div class="d-flex align-items-center gap-2">
            <i class="ti ti-shopping-bag fs-1 text-rose"></i>
            <h2 class="offcanvas-title fs-2 fw-bold text-dark m-0" id="cartDrawerTitle">{{ __('Shopping Cart') }}</h2>
            <span class="badge bg-rose-lt text-rose rounded-pill px-2 py-1 fw-bold fs-5 ms-1">
                <span data-cart-count>0</span> {{ __('Items') }}
            </span>
        </div>
        <button type="button" class="btn-close text-reset shadow-none" data-bs-dismiss="offcanvas"
            aria-label="{{ __('Close') }}"></button>
    </div>

    <div class="offcanvas-body p-3 p-md-4">
        <div class="d-flex flex-column gap-2 w-100" data-cart-items></div>

        <div class="text-center py-5" data-cart-when-empty>
            <div class="empty-cart-icon-box">
                <i class="ti ti-shopping-bag-x fs-1"></i>
            </div>
            <h3 class="fs-2 fw-bold text-dark mb-1">{{ __('Your cart is empty') }}</h3>
            <p class="text-muted fs-4 mb-4">{{ __('Looks like you haven\'t added any items yet.') }}</p>
            <button type="button" class="btn btn-rose rounded-cosmetics px-4 py-2 fs-3 fw-bold shadow-sm"
                data-bs-dismiss="offcanvas">
                <span>{{ __('Start Shopping') }}</span>
                <i class="ti ti-arrow-right ms-1"></i>
            </button>
        </div>
    </div>

    <div class="offcanvas-footer border-top p-3 p-md-4 bg-white" data-cart-when-filled>
        <div class="cart-pricing-box d-flex justify-content-between align-items-center mb-3">
            <div>
                <span class="fs-4 text-muted d-block">{{ __('Total Amount') }}</span>
                <span class="fs-2 fw-bold text-dark">{{ __('Grand Total') }}</span>
            </div>
            <span class="fs-1 fw-bold text-rose" data-cart-total></span>
        </div>
        <button type="button"
            class="btn btn-checkout-wa w-100 fs-3 fw-bold d-flex align-items-center justify-content-center gap-2"
            data-cart-checkout>
            <i class="ti ti-brand-whatsapp fs-1"></i>
            <span>{{ __('Place WhatsApp Order') }}</span>
        </button>
    </div>
</div>

{{-- One cart line (copied by the storefront script for every item) --}}
<template data-cart-item>
    <div class="card cart-item-card p-3 shadow-none w-100">
        <div class="d-flex align-items-center w-100 overflow-hidden">
            <div class="me-3 cart-item-img-container d-flex align-items-center justify-content-center p-1">
                <img data-cart-image data-fallback="{{ asset('img/templates/all-categories.png') }}"
                    class="img-fluid rounded object-fit-contain h-100 w-100" alt="">
            </div>
            <div class="cart-item-info-col">
                <h4 class="mb-1 cart-item-name" data-cart-name></h4>
                <span class="badge bg-rose-lt text-rose fw-bold rounded-pill px-2 mb-1" style="font-size: 11px;"
                    data-cart-variant></span>
                <small class="cart-item-subtitle mb-1" data-cart-subtitle></small>
                <p class="text-rose mb-2 fw-bold fs-3 lh-1" data-cart-line-total></p>

                <div class="d-flex align-items-center justify-content-between w-100">
                    <div class="d-flex align-items-center cart-qty-pill">
                        <button type="button" class="border-0 btn d-flex align-items-center justify-content-center p-0 cart-btn-step"
                            data-cart-decrease aria-label="{{ __('Decrease') }}"><i class="ti ti-minus"></i></button>
                        <span class="cart-qty-val" data-cart-qty></span>
                        <button type="button" class="border-0 btn d-flex align-items-center justify-content-center p-0 cart-btn-step"
                            data-cart-increase aria-label="{{ __('Increase') }}"><i class="ti ti-plus"></i></button>
                    </div>
                    <button type="button" class="btn cart-btn-trash d-flex align-items-center justify-content-center p-0"
                        data-cart-remove title="{{ __('Delete') }}" aria-label="{{ __('Delete') }}">
                        <i class="ti ti-trash fs-2"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

{{-- Checkout --}}
@php
    $deliveryChoices = array_filter([
        'Order For Delivery' => (bool) ($deliveryOptions->order_for_delivery ?? true),
        'Take Away' => (bool) ($deliveryOptions->take_away ?? false),
        'Dine In' => (bool) ($deliveryOptions->dine_in ?? false),
    ]);
@endphp
<div class="modal modal-blur fade" id="orderModal" tabindex="-1" aria-labelledby="orderModalTitle" aria-hidden="true" data-order-modal>
    <div class="modal-dialog modal-md modal-dialog-centered modal-dialog-scrollable" role="document">
        <form class="modal-content order-dialog-card shadow-lg border-0" data-order-form novalidate>
            <div class="modal-header border-bottom px-4 py-3 bg-white">
                <div class="d-flex align-items-center gap-2">
                    <i class="ti ti-receipt text-rose fs-2"></i>
                    <h5 class="modal-title fw-bold fs-2 text-dark m-0" id="orderModalTitle">{{ __('Checkout Details') }}</h5>
                </div>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
            </div>

            <div class="modal-body p-4 bg-white">
                <div class="mb-3">
                    <label class="form-label required fw-bold fs-4 text-dark mb-1" for="order_name">{{ __('Full Name') }}</label>
                    <div class="input-icon">
                        <span class="input-icon-addon"><i class="ti ti-user text-muted"></i></span>
                        <input type="text" class="form-control rounded-cosmetics fs-3" id="order_name" name="customer_name"
                            placeholder="{{ __('Enter your full name') }}" autocomplete="name" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label required fw-bold fs-4 text-dark mb-1" for="order_phone">{{ __('Mobile Number') }}</label>
                    <div class="input-icon">
                        <span class="input-icon-addon"><i class="ti ti-phone text-muted"></i></span>
                        <input type="tel" inputmode="tel" class="form-control rounded-cosmetics fs-3" id="order_phone"
                            name="customer_phone" placeholder="{{ __('Mobile number with country code, excluding +') }}"
                            autocomplete="tel" required>
                    </div>
                </div>

                @if (count($deliveryChoices) > 1)
                    <div class="mb-3">
                        <label class="form-label required fw-bold fs-4 text-dark mb-2">{{ __('Delivery Type') }}</label>
                        <div class="d-flex flex-column gap-2">
                            @foreach (array_keys($deliveryChoices) as $method)
                                <label class="delivery-radio-card d-flex align-items-center gap-2 m-0">
                                    <input class="form-check-input m-0" type="radio" name="delivery_method"
                                        value="{{ $method }}" @checked($loop->first)>
                                    <span class="delivery-radio-label fs-4 text-dark">{{ __($method) }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="mb-3" data-address-field>
                    <label class="form-label required fw-bold fs-4 text-dark mb-1" for="order_address">{{ __('Delivery Address') }}</label>
                    <div class="input-icon">
                        <span class="input-icon-addon"><i class="ti ti-map-pin text-muted"></i></span>
                        <input type="text" class="form-control rounded-cosmetics fs-3" id="order_address" name="delivery_address"
                            placeholder="{{ __('Street, building, city...') }}" autocomplete="street-address">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold fs-4 text-dark mb-1" for="order_notes">{{ __('Order Notes (Optional)') }}</label>
                    <textarea class="form-control rounded-cosmetics fs-4" id="order_notes" name="delivery_note" rows="2"
                        placeholder="{{ __('Any special instructions for the owner...') }}"></textarea>
                </div>

                <div class="p-3 bg-light rounded-cosmetics border">
                    <small class="fw-bold text-dark d-block">{{ __('How it works:') }}</small>
                    <small class="text-muted d-block mt-1">
                        {{ __("After you click 'Confirm & Send', WhatsApp will open automatically with your items list. Just press Send in WhatsApp to complete your order.") }}
                    </small>
                </div>
            </div>

            <div class="modal-footer border-top px-4 py-3 bg-white">
                <button type="button" class="btn btn-secondary rounded-cosmetics px-3 fs-3" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                <button type="submit" class="btn btn-rose rounded-cosmetics px-4 fs-3 fw-bold d-flex align-items-center gap-2">
                    <i class="ti ti-brand-whatsapp fs-2"></i>
                    <span>{{ __('Confirm & Send') }}</span>
                </button>
            </div>
        </form>
    </div>
</div>
