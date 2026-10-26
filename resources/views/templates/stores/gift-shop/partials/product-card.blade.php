{{--
    Gift shop product card (listing grid and "You May Also Like"). Use inside <div class="store-product-grid">.
    Params: $product (App\StoreProduct with min_variant_price)
    Products with options link to their page ("Select Options"); others add straight to the cart.
--}}
@once
    <style>
        .store-product-grid {
            display: grid;
            gap: 20px;
            width: 100%;
            grid-template-columns: repeat(2, 1fr);
        }

        .store-product-card {
            background: var(--store-card-bg);
            border-radius: var(--store-radius-card);
            border: 1px solid var(--store-border);
            padding: 14px;
            display: flex;
            flex-direction: column;
            height: 100%;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.03);
            transition: all 0.3s ease;
            position: relative;
        }

        .store-product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(220, 38, 38, 0.08);
            border-color: rgba(220, 38, 38, 0.2);
        }

        .product-media-container {
            width: 100%;
            aspect-ratio: 1 / 1;
            border-radius: 16px;
            overflow: hidden;
            position: relative;
            background: var(--store-primary-light);
            margin-bottom: 12px;
        }

        .product-thumbnail-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
            transition: transform 0.4s ease;
        }

        .store-product-card:hover .product-thumbnail-img {
            transform: scale(1.05);
        }

        .product-pill-badge {
            position: absolute;
            top: 10px;
            left: 10px;
            background: rgba(220, 38, 38, 0.9);
            backdrop-filter: blur(4px);
            color: #ffffff;
            font-size: 0.7rem;
            font-weight: 700;
            padding: 4px 10px;
            border-radius: var(--store-radius-pill);
            z-index: 2;
            text-transform: uppercase;
        }

        .slot-title {
            height: 40px;
            margin-bottom: 4px;
            overflow: hidden;
        }

        .product-grid-title {
            font-size: 0.95rem;
            font-weight: 700;
            color: var(--store-dark);
            margin: 0;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            line-height: 1.25;
        }

        .slot-starts-from {
            height: 16px;
            display: flex;
            align-items: center;
            margin-bottom: 2px;
        }

        .starts-from-label {
            font-size: 0.65rem;
            font-weight: 800;
            color: var(--store-primary);
            letter-spacing: 0.6px;
            text-transform: uppercase;
        }

        .slot-sales-price {
            height: 28px;
            display: flex;
            align-items: center;
        }

        .product-grid-sales-price {
            font-size: 1.25rem;
            font-weight: 800;
            color: var(--store-primary);
            margin: 0;
            line-height: 1;
        }

        .slot-regular-price {
            height: 18px;
            display: flex;
            align-items: center;
            margin-bottom: 12px;
        }

        .product-grid-regular-price {
            font-size: 0.8rem;
            color: var(--store-text-muted);
            text-decoration: line-through !important;
            margin: 0;
            line-height: 1;
        }

        .slot-action-button {
            height: 38px;
            margin-top: auto;
            width: 100%;
        }

        .btn-card-cart {
            width: 100%;
            height: 100%;
            background: var(--store-dark);
            color: #ffffff !important;
            font-size: 0.82rem;
            border-radius: var(--store-radius-pill) !important;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .btn-card-cart:hover {
            background: var(--store-primary);
            box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3);
        }

        .btn-card-cart:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            background: #78716c !important;
            box-shadow: none !important;
        }

        .btn-card-options {
            width: 100%;
            height: 100%;
            background: var(--store-primary-light);
            color: var(--store-primary) !important;
            border: 1px solid rgba(220, 38, 38, 0.3);
            font-size: 0.82rem;
            border-radius: var(--store-radius-pill) !important;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            transition: all 0.3s ease;
        }

        .btn-card-options:hover {
            background: var(--store-primary);
            color: #ffffff !important;
        }

        @media (min-width: 768px) {
            .store-product-grid {
                grid-template-columns: repeat(3, 1fr);
                gap: 24px;
            }
        }

        @media (min-width: 992px) {
            .store-product-grid {
                grid-template-columns: repeat(4, 1fr);
            }
        }

        @media (min-width: 1200px) {
            .store-product-grid {
                grid-template-columns: repeat(5, 1fr);
            }
        }
    </style>
@endonce

@php
    $images = array_filter(array_map('trim', explode(',', $product->product_image)));
    $productUrl = $productUrlPrefix . '/' . $product->id;
    $regularPrice = $product->listingRegularPrice();
    $placeholder = asset('img/templates/gift-shop/placeholder.svg');
@endphp

<div class="store-product-card">
    <a href="{{ $productUrl }}" class="product-media-container">
        @if ($product->badge)
            <span class="product-pill-badge">{{ $product->badge }}</span>
        @endif
        <img src="{{ $images ? url(reset($images)) : $placeholder }}" alt="{{ $product->product_name }}"
            class="product-thumbnail-img" onerror="this.onerror=null;this.src='{{ $placeholder }}';" />
    </a>

    {{-- Fixed-height slots keep every card's price and button on the same line --}}
    <div class="slot-title">
        <a href="{{ $productUrl }}">
            <h3 class="product-grid-title" title="{{ $product->product_name }}">{{ $product->product_name }}</h3>
        </a>
    </div>

    <div class="slot-starts-from">
        @if ($product->hasOptions())
            <span class="starts-from-label">{{ __('Starts From') }}</span>
        @else
            <span class="starts-from-spacer">&nbsp;</span>
        @endif
    </div>

    <div class="slot-sales-price">
        <p class="product-grid-sales-price">{{ formatCurrencyCard($product->listingPrice(), $currency) }}</p>
    </div>

    <div class="slot-regular-price">
        @if ($regularPrice)
            <p class="product-grid-regular-price">{{ formatCurrencyCard($regularPrice, $currency) }}</p>
        @else
            <span class="regular-price-spacer">&nbsp;</span>
        @endif
    </div>

    <div class="slot-action-button">
        @if ($product->hasOptions())
            <a href="{{ $productUrl }}" class="btn-card-options">
                <i class="ti ti-adjustments-horizontal"></i>
                <span>{{ __('Select Options') }}</span>
            </a>
        @elseif ($product->product_status == 'instock')
            <button type="button" class="btn-card-cart" {{ $product->cartAttributes() }}>
                <i class="ti ti-shopping-bag-plus"></i>
                <span>{{ __('Add to Cart') }}</span>
            </button>
        @else
            <button type="button" class="btn-card-cart" disabled>
                <span>{{ __('Out of Stock') }}</span>
            </button>
        @endif
    </div>
</div>
