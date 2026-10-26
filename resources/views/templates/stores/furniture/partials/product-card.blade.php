{{--
    Furniture product card (listing grid and "Related Products"). Use inside <div class="store-product-grid">.
    Params: $product (App\StoreProduct with min_variant_price)
    Products with options link to their page ("Select Options"); others add straight to the cart.
--}}
@once
    <style>
        .product-luxury-card {
            background: #ffffff;
            border: 1px solid var(--color-card-border);
            border-radius: 14px;
            padding: 12px;
            display: flex;
            flex-direction: column;
            position: relative;
            transition: transform 0.25s ease, box-shadow 0.25s ease;
        }

        .product-luxury-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.06);
            border-color: #dfd7ca;
        }

        .product-image-container {
            width: 100%;
            aspect-ratio: 4 / 3;
            border-radius: 10px;
            overflow: hidden;
            background-color: #f8fafc;
            margin-bottom: 12px;
            position: relative;
        }

        .product-image-container img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            object-position: center;
            transition: transform 0.3s ease;
        }

        .product-luxury-card:hover .product-image-container img {
            transform: scale(1.04);
        }

        .product-badge-pill {
            position: absolute;
            top: 8px;
            left: 8px;
            z-index: 2;
            background: var(--color-camel);
            color: #ffffff;
            font-size: 11px;
            font-weight: 700;
            padding: 3px 10px;
            border-radius: 50px !important;
            text-transform: uppercase;
        }

        .slot-title {
            min-height: 40px;
            max-height: 40px;
            font-size: 15px;
            font-weight: 600;
            color: var(--color-text-main);
            line-height: 1.3;
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            margin-bottom: 4px;
        }

        .slot-variable-label {
            min-height: 16px;
            max-height: 16px;
            font-size: 11px;
            font-weight: 700;
            color: var(--color-forest-teal);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 2px;
            display: flex;
            align-items: center;
        }

        .slot-sales-price {
            min-height: 28px;
            max-height: 28px;
            font-size: 18px;
            font-weight: 700;
            color: var(--color-forest-teal);
            display: flex;
            align-items: center;
        }

        .slot-regular-price {
            min-height: 18px;
            max-height: 18px;
            font-size: 13px;
            color: var(--color-text-muted);
            text-decoration: line-through;
            display: flex;
            align-items: center;
            margin-bottom: 12px;
        }

        .slot-action-btn {
            min-height: 38px;
            max-height: 38px;
            width: 100%;
        }

        .btn-luxury-action {
            background-color: var(--color-forest-teal);
            color: #ffffff !important;
            font-size: 13px;
            font-weight: 700;
            border-radius: 50px !important;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            height: 100%;
            width: 100%;
            border: none;
            transition: background-color 0.2s ease;
        }

        .btn-luxury-action:hover {
            background-color: var(--color-forest-teal-dark);
            color: #ffffff !important;
        }

        .btn-luxury-options-redesign {
            background-color: #ffffff;
            color: var(--color-forest-teal) !important;
            font-size: 13px;
            font-weight: 700;
            border-radius: 50px !important;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            height: 100%;
            width: 100%;
            border: 1.5px solid var(--color-forest-teal);
            transition: all 0.2s ease-in-out;
        }

        .btn-luxury-options-redesign:hover {
            background-color: var(--color-forest-teal);
            color: #ffffff !important;
            border-color: var(--color-forest-teal);
        }
    </style>
@endonce

@php
    $images = array_filter(array_map('trim', explode(',', $product->product_image)));
    $productUrl = $productUrlPrefix . '/' . $product->id;
    $regularPrice = $product->listingRegularPrice();
    $placeholder = asset('img/templates/furniture/placeholder.svg');
@endphp

<div class="product-luxury-card">
    <a href="{{ $productUrl }}">
        <div class="product-image-container">
            @if (!empty(trim($product->badge ?? '')))
                <span class="product-badge-pill">{{ $product->badge }}</span>
            @endif
            <img src="{{ $images ? url(reset($images)) : $placeholder }}" alt="{{ $product->product_name }}"
                onerror="this.onerror=null;this.src='{{ $placeholder }}';" />
        </div>
    </a>

    {{-- Fixed-height slots keep every card's price and button on the same line --}}
    <div class="slot-title" title="{{ $product->product_name }}">
        <a href="{{ $productUrl }}" class="text-dark"><span>{{ $product->product_name }}</span></a>
    </div>

    <div class="slot-variable-label">
        @if ($product->hasOptions())
            <span>{{ __('STARTS FROM') }}</span>
        @endif
    </div>

    <div class="slot-sales-price">
        <span>{{ formatCurrencyCard($product->listingPrice(), $currency) }}</span>
    </div>

    <div class="slot-regular-price">
        @if ($regularPrice)
            <span>{{ formatCurrencyCard($regularPrice, $currency) }}</span>
        @endif
    </div>

    <div class="slot-action-btn">
        @if ($product->hasOptions())
            <a href="{{ $productUrl }}" class="btn-luxury-options-redesign">
                <i class="ti ti-adjustments-horizontal fs-2"></i>
                <span>{{ __('Select Options') }}</span>
            </a>
        @elseif ($product->product_status == 'instock')
            <button type="button" class="btn-luxury-action" {{ $product->cartAttributes() }}>
                <i class="ti ti-shopping-bag-plus fs-2"></i>
                <span>{{ __('Add to Cart') }}</span>
            </button>
        @else
            <button type="button" class="btn btn-secondary w-100 h-100 rounded-pill" disabled>{{ __('Out of Stock') }}</button>
        @endif
    </div>
</div>
