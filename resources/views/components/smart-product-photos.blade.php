{{--
    Smart Product photos for the public website (any template).
    Cross-fades through every photo of the product (gallery, or the old
    front/back images) and shows the product type in the corner, plus an
    "Out of Stock" badge when the product can't be ordered.
    Uses inline styles only, so it works with every template's CSS.

    <x-smart-product-photos :product="$nfcCard" height="223px" />
--}}
@props(['product', 'height' => '223px', 'showType' => true, 'showStock' => true])

@php
    $photos = array_map(fn ($path) => asset($path), $product->photos());
    $typeLabel = $showType ? \App\Support\SmartProducts\SmartProductCatalog::typeLabel($product->product_type) : null;
    $outOfStock = $showStock && ! $product->inStock();
@endphp

<div {{ $attributes->merge(['class' => 'smart-product-photos']) }}
    style="position: relative; width: 100%; height: {{ $height }}; overflow: hidden; border-radius: 6px; background: rgba(0,0,0,.04);">
    @foreach ($photos as $index => $src)
        <img src="{{ $src }}" alt="{{ $product->nfc_card_name }}" loading="lazy" data-photo
            style="position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; transition: opacity .6s ease; opacity: {{ $index === 0 ? 1 : 0 }};{{ $outOfStock ? ' filter: grayscale(.6);' : '' }}">
    @endforeach

    @if ($typeLabel)
        <span
            style="position: absolute; top: 10px; left: 10px; padding: 2px 10px; border-radius: 999px; font-size: 12px; font-weight: 600; background: rgba(255,255,255,.92); color: #1f2937;">
            {{ $typeLabel }}
        </span>
    @endif

    @if ($outOfStock)
        <span
            style="position: absolute; bottom: 10px; left: 10px; padding: 2px 10px; border-radius: 999px; font-size: 12px; font-weight: 600; background: rgba(17,24,39,.85); color: #fff;">
            {{ __('Out of Stock') }}
        </span>
    @endif
</div>

@once
    <script>
        "use strict";
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.smart-product-photos').forEach(function(box) {
                var photos = box.querySelectorAll('[data-photo]');
                if (photos.length < 2) return;
                var current = 0;
                setInterval(function() {
                    photos[current].style.opacity = 0;
                    current = (current + 1) % photos.length;
                    photos[current].style.opacity = 1;
                }, 3000);
            });
        });
    </script>
@endonce
