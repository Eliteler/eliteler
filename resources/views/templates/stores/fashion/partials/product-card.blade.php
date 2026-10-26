{{--
    Fashion product card (listing grid and "You May Also Like"). Use inside the page's product grid.
    Params: $product (App\StoreProduct with min_variant_price)
    Products with options link to their page ("Select Options"); others add straight to the cart.
--}}
@once
    <style>
        .fashion-product-card {
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 0px;
            display: flex;
            flex-direction: column;
            height: 100%;
            padding: 12px;
            transition: box-shadow 0.2s ease;
        }

        .fashion-product-card:hover {
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
        }

        .fashion-product-img-box {
            position: relative;
            width: 100%;
            aspect-ratio: 1 / 1;
            background-color: #f1f5f9;
            overflow: hidden;
            margin-bottom: 10px;
        }

        .fashion-product-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .slot-starts-from {
            height: 16px;
            font-size: 11px;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            line-height: 16px;
            margin-bottom: 4px;
        }

        .slot-title {
            height: 40px;
            margin-bottom: 6px;
        }

        .slot-title a {
            color: #0f172a;
            font-size: 13.5px;
            font-weight: 700;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            line-height: 1.4;
        }

        .slot-sales-price {
            height: 26px;
            font-size: 16px;
            font-weight: 800;
            color: #0f172a;
            display: flex;
            align-items: center;
        }

        .slot-regular-price {
            height: 18px;
            font-size: 12px;
            color: #94a3b8;
            line-height: 18px;
            margin-bottom: 10px;
        }

        .slot-action-btn {
            height: 40px;
            width: 100%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            border-radius: 0px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border: none;
            cursor: pointer;
        }

        .slot-action-btn i,
        .slot-action-btn svg {
            font-size: 17px !important;
            width: 17px;
            height: 17px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
    </style>
@endonce

@php
    $images = array_filter(array_map('trim', explode(',', $product->product_image)));
    $productUrl = $productUrlPrefix . '/' . $product->id;
    $regularPrice = $product->listingRegularPrice();
    $soldOut = !$product->hasOptions() && $product->product_status != 'instock';
    $placeholder = asset('img/templates/fashion/placeholder.svg');
@endphp

<div class="fashion-product-card">
    <div class="fashion-product-img-box">
        <a href="{{ $productUrl }}">
            <img src="{{ $images ? url(reset($images)) : $placeholder }}" alt="{{ $product->product_name }}"
                class="fashion-product-img" onerror="this.onerror=null;this.src='{{ $placeholder }}';">
        </a>
        @if ($soldOut)
            <span class="badge bg-danger position-absolute top-0 start-0 m-2">{{ __('Out of Stock') }}</span>
        @elseif ($product->badge)
            <span class="badge bg-dark position-absolute top-0 start-0 m-2">{{ $product->badge }}</span>
        @endif
    </div>

    {{-- Fixed-height slots keep every card's price and button on the same line --}}
    <div class="slot-starts-from">{{ $product->hasOptions() ? __('Starts from') : '' }}</div>

    <div class="slot-title">
        <a href="{{ $productUrl }}">{{ $product->product_name }}</a>
    </div>

    <div class="slot-sales-price">{{ formatCurrencyCard($product->listingPrice(), $currency) }}</div>

    <div class="slot-regular-price">
        @if ($regularPrice)
            <del>{{ formatCurrencyCard($regularPrice, $currency) }}</del>
        @endif
    </div>

    @if ($product->hasOptions())
        <a href="{{ $productUrl }}" class="slot-action-btn btn btn-dark">{{ __('Select Options') }}</a>
    @elseif ($soldOut)
        <button type="button" class="slot-action-btn btn btn-secondary" disabled>{{ __('Out of Stock') }}</button>
    @else
        <button type="button" class="slot-action-btn btn btn-dark" {{ $product->cartAttributes() }}>
            <i class="ti ti-shopping-bag-plus"></i>
            <span>{{ __('Add to Cart') }}</span>
        </button>
    @endif
</div>
