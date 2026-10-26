{{--
    Jewellery product card (listing grid and "You May Also Like"). Use inside <div class="store-product-grid">.
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
            margin-bottom: 40px;
        }

        .store-product-card {
            background: var(--store-card-bg);
            border-radius: var(--store-radius-card);
            border: 1px solid var(--store-border);
            padding: 14px;
            display: flex;
            flex-direction: column;
            height: 100%;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.02);
            transition: all 0.3s ease;
            position: relative;
        }

        .store-product-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 28px rgba(212, 175, 55, 0.14);
        }

        .product-media-container {
            width: 100%;
            aspect-ratio: 1 / 1;
            border-radius: 18px;
            overflow: hidden;
            position: relative;
            background: #fffdf8;
            margin-bottom: 12px;
            flex-shrink: 0;
        }

        .product-thumbnail-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
        }

        .product-pill-badge {
            position: absolute;
            top: 10px;
            left: 10px;
            background: var(--store-primary);
            color: #ffffff;
            font-size: 0.7rem;
            font-weight: 700;
            padding: 4px 10px;
            border-radius: var(--store-radius-pill);
            z-index: 2;
        }

        [dir="rtl"] .product-pill-badge {
            left: auto;
            right: 10px;
        }

        .slot-title {
            height: 44px !important;
            min-height: 44px !important;
            max-height: 44px !important;
            flex: 0 0 44px !important;
            margin-bottom: 4px !important;
            overflow: hidden !important;
            display: flex !important;
            align-items: flex-start !important;
        }

        .product-grid-title {
            font-size: 0.95rem;
            font-weight: 700;
            color: var(--store-dark);
            margin: 0 !important;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            line-height: 1.35;
            text-align: start;
        }

        .slot-starts-from {
            height: 20px !important;
            min-height: 20px !important;
            max-height: 20px !important;
            flex: 0 0 20px !important;
            margin-bottom: 4px !important;
            display: flex !important;
            align-items: center !important;
        }

        .starts-from-label {
            font-size: 0.68rem;
            font-weight: 800;
            color: var(--store-primary-dark);
            letter-spacing: 0.6px;
            text-transform: uppercase;
            line-height: 1;
            display: inline-block;
        }

        .starts-from-spacer {
            display: inline-block !important;
            visibility: hidden !important;
            height: 14px !important;
            line-height: 14px !important;
            user-select: none !important;
        }

        .slot-sales-price {
            height: 28px !important;
            min-height: 28px !important;
            max-height: 28px !important;
            flex: 0 0 28px !important;
            display: flex !important;
            align-items: center !important;
        }

        .product-grid-sales-price {
            font-size: 1.25rem;
            font-weight: 800;
            color: var(--store-primary-hover);
            margin: 0 !important;
            line-height: 1;
        }

        .slot-regular-price {
            height: 20px !important;
            min-height: 20px !important;
            max-height: 20px !important;
            flex: 0 0 20px !important;
            margin-bottom: 12px !important;
            display: flex !important;
            align-items: center !important;
        }

        .product-grid-regular-price {
            font-size: 0.8rem;
            color: var(--store-text-muted);
            text-decoration: line-through !important;
            margin: 0 !important;
            line-height: 1;
        }

        .regular-price-spacer {
            display: inline-block !important;
            visibility: hidden !important;
            height: 14px !important;
            line-height: 14px !important;
            user-select: none !important;
        }

        .slot-action-button {
            height: 42px !important;
            min-height: 42px !important;
            max-height: 42px !important;
            flex: 0 0 42px !important;
            margin-top: auto !important;
            width: 100%;
        }

        .btn-card-cart {
            width: 100%;
            height: 100%;
            background: var(--store-primary-light);
            color: var(--store-primary-dark) !important;
            font-size: 0.85rem;
            border-radius: var(--store-radius-pill) !important;
            border: 1.5px solid var(--store-border);
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 8px !important;
            transition: all 0.25s ease;
            cursor: pointer;
            font-weight: 700;
            padding: 0 16px;
            line-height: 1 !important;
        }

        .btn-card-cart i {
            font-size: 1.25rem !important;
            line-height: 1 !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
        }

        .btn-card-cart span {
            line-height: 1 !important;
            display: inline-block !important;
            transform: translateY(1px);
        }

        .btn-card-cart:hover {
            background: var(--store-primary);
            color: #ffffff !important;
            border-color: var(--store-primary);
        }

        .btn-card-cart:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            background: #e5e7eb !important;
            color: #9ca3af !important;
            border-color: #d1d5db !important;
        }

        .btn-card-options {
            width: 100%;
            height: 100%;
            background: var(--store-primary);
            color: #ffffff !important;
            border: none;
            font-size: 0.85rem;
            border-radius: var(--store-radius-pill) !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 8px !important;
            transition: all 0.25s ease;
            font-weight: 700;
            padding: 0 16px;
            line-height: 1 !important;
        }

        .btn-card-options i {
            font-size: 1.2rem !important;
            line-height: 1 !important;
        }

        .btn-card-options span {
            line-height: 1 !important;
            display: inline-block !important;
            transform: translateY(1px);
        }

        .btn-card-options:hover {
            background: var(--store-primary-hover);
            color: #ffffff !important;
        }

        @media (min-width: 768px) {
            .store-product-grid {
                grid-template-columns: repeat(3, 1fr);
                gap: 22px;
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
    $placeholder = asset('img/templates/jewellery-shop/placeholder.svg');
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
