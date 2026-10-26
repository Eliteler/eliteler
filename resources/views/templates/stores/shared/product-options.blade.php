{{--
    Option (variation) buttons of a product page, driven by public/js/store/storefront.js.
    Put it inside the element that carries {{ $productOptions->attributes($product_details) }}.
    Params: $productOptions (App\Support\Store\ProductOptions)
            $buttonClass, $activeClass (default "active"), $soldOutClass (default "is-sold-out"), $priceClass
--}}
@if ($productOptions->hasOptions)
    <script type="application/json" data-product-options>@json($productOptions->forScript())</script>

    @foreach ($productOptions->list as $index => $option)
        @php $inStock = \App\Support\Store\ProductOptions::inStock($option); @endphp
        <button type="button" data-option="{{ $index }}"
            class="{{ $buttonClass }} {{ $productOptions->isActive($index) ? $activeClass ?? 'active' : '' }} {{ $inStock ? '' : $soldOutClass ?? 'is-sold-out' }}"
            aria-pressed="{{ $productOptions->isActive($index) ? 'true' : 'false' }}"
            @unless ($inStock) disabled title="{{ __('Out of Stock') }}" @endunless>
            <span>{{ $option->variant_name }}</span>
            <span class="{{ $priceClass ?? '' }}">{{ $inStock ? formatCurrencyCard($option->sales_price, $currency) : __('Out of Stock') }}</span>
        </button>
    @endforeach
@endif
