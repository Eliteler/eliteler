{{--
    "Order Now" button for a Smart Product on the public website (any template).
    In stock: links to the checkout (guests go through login first).
    Out of stock: the same-looking button, greyed out and not clickable,
    labelled "Out of Stock". Pass the template's own button classes.

    <x-smart-product-order-button :product="$nfcCard" class="w-full block py-3 btn-primary" />
--}}
@props(['product'])

@if ($product->inStock())
    @php
        $checkout = route('user.order.nfc.card.checkout', $product->nfc_card_id);
    @endphp
    <a href="{{ auth()->check() ? $checkout : route('login') . '?redirect=' . urlencode($checkout) }}"
        {{ $attributes }}>
        {{ __('Order Now') }}
    </a>
@else
    <span role="button" aria-disabled="true" title="{{ __('Out of Stock') }}"
        {{ $attributes->merge(['style' => 'opacity: .55; cursor: not-allowed; pointer-events: none;']) }}>
        {{ __('Out of Stock') }}
    </span>
@endif
