{{--
    Restaurant cart drawer and checkout form. Markup only: the shared storefront script
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
    /* Prevent text and element overflow inside restaurant cart cards */
    .cart-item-card {
        overflow: hidden !important;
    }

    .cart-item-content {
        min-width: 0 !important;
        overflow: hidden !important;
    }

    .cart-item-subtitle {
        display: block !important;
        white-space: nowrap !important;
        overflow: hidden !important;
        text-overflow: ellipsis !important;
        max-width: 100% !important;
    }
</style>

{{-- Cart drawer --}}
<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasEnd" aria-labelledby="offcanvasEndLabel" data-cart-drawer>
    <div class="offcanvas-header">
        <h2 class="offcanvas-title fs-custom" id="offcanvasEndLabel">{{ __('Cart Items') }}</h2>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas"
            aria-label="{{ __('Close') }}"></button>
    </div>

    <div class="offcanvas-body">
        <div class="row" data-cart-items></div>

        <div class="p-3" data-cart-when-empty>
            <p class="px-4 py-4 mb-4 text-center fs-2">{{ __('Your cart is empty.') }}</p>
            <button type="button" class="btn btn-restaurant-red d-flex btn-effect justify-content-center w-100"
                data-bs-dismiss="offcanvas">{{ __('Start Shopping') }}</button>
        </div>
    </div>

    <div class="offcanvas-footer border-top p-3" data-cart-when-filled hidden>
        <h3 class="font-bold fs-2 d-flex justify-content-between">
            <span>{{ __('Grand total') }}</span>
            <span class="text-restaurant-red" data-cart-total></span>
        </h3>
        <button type="button" class="btn btn-restaurant-red fs-2 btn-effect w-100 justify-content-center"
            data-cart-checkout>
            {{ __('Place WhatsApp Order') }}
        </button>
    </div>
</div>

{{-- One cart line (copied by the storefront script for every item) --}}
<template data-cart-item>
    <div class="col-12 mb-3">
        <div class="card p-3 cart-item-card">
            <div class="d-flex align-items-center w-100" style="min-width: 0;">
                <div class="me-3 flex-shrink-0" style="width: 95px; height: 95px;">
                    <img class="img-fluid rounded-restaurant object-fit-contain h-100 w-100" data-cart-image
                        data-fallback="{{ asset('img/templates/restaurant/placeholder.svg') }}" alt="">
                </div>
                <div class="flex-grow-1 cart-item-content">
                    <h4 class="text-dark fs-2 mb-1 text-truncate" data-cart-name></h4>
                    <span class="badge bg-restaurant-red-lt mb-1" data-cart-variant></span>
                    <small class="text-muted cart-item-subtitle" data-cart-subtitle></small>
                    <p class="text-secondary mt-1 mb-2 fw-semibold fs-3" data-cart-line-total></p>

                    <div class="d-flex align-items-center flex-wrap">
                        <button type="button"
                            class="btn text-dark border d-flex align-items-center justify-content-center p-0"
                            style="width: 32px; height: 32px;" data-cart-decrease aria-label="{{ __('Decrease') }}">
                            <i class="ti ti-minus m-0"></i>
                        </button>
                        <span class="text-dark p-0 d-flex align-items-center justify-content-center fs-3 fw-bold"
                            style="width: 32px; height: 32px;" data-cart-qty></span>
                        <button type="button"
                            class="btn text-dark border d-flex align-items-center justify-content-center p-0"
                            style="width: 32px; height: 32px;" data-cart-increase aria-label="{{ __('Increase') }}">
                            <i class="ti ti-plus m-0"></i>
                        </button>
                        <button type="button"
                            class="ms-2 btn btn-danger p-0 d-flex align-items-center justify-content-center"
                            style="width: 32px; height: 32px;" data-cart-remove aria-label="{{ __('Remove') }}">
                            <i class="ti ti-trash m-0"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

{{-- Checkout --}}
<div class="modal fade" id="orderModal" tabindex="-1" aria-hidden="true" style="background: rgba(0, 0, 0, 0.7);"
    data-order-modal>
    <div class="modal-dialog modal-md modal-dialog-centered" role="document">
        <form class="modal-content" data-order-form novalidate>
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Please fill following details:') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"
                    aria-label="{{ __('Close') }}"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label required" for="cus_name">{{ __('Full Name') }}</label>
                    <input type="text" class="form-control" id="cus_name" name="customer_name" autocomplete="name"
                        required />
                </div>
                <div class="mb-3">
                    <label class="form-label required" for="cus_mobile">{{ __('Mobile') }}</label>
                    <input type="tel" inputmode="tel" class="form-control" id="cus_mobile" name="customer_phone"
                        autocomplete="tel" placeholder="{{ __('Mobile number with country code, excluding +') }}"
                        required />
                </div>

                @if (count($deliveryChoices) > 1)
                    <div class="mb-3">
                        <div class="form-label required mb-3">{{ __('Delivery Type') }}</div>
                        <div class="d-flex flex-column gap-2">
                            @foreach (array_keys($deliveryChoices) as $method)
                                <label class="form-check">
                                    <input class="form-check-input" type="radio" name="delivery_method"
                                        value="{{ $method }}" @checked($loop->first)>
                                    <span class="form-check-label">{{ __($method) }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="mb-3" data-address-field>
                    <label class="form-label required" for="cus_address">{{ __('Address') }}</label>
                    <input type="text" class="form-control" id="cus_address" name="delivery_address"
                        autocomplete="street-address" />
                </div>

                <div class="mb-3">
                    <label class="form-label" for="cus_notes">{{ __('Notes') }}</label>
                    <textarea class="form-control" id="cus_notes" name="delivery_note" rows="3"></textarea>
                </div>

                <div class="mb-3">
                    <small>{{ __('Customer Notes') }}: </small>
                    <small
                        class="text-muted">{{ __("After you click the Confirm button, WhatsApp will open. Tap 'Send' in WhatsApp to send your order to the shop owner.") }}</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-dark" data-bs-dismiss="modal">{{ __('Close') }}</button>
                <button type="submit" class="btn btn-restaurant-red">{{ __('Confirm') }}</button>
            </div>
        </form>
    </div>
</div>
