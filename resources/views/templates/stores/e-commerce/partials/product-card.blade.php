{{--
    E-commerce product card (listing grid and "related products" slider).
    Params: $product (App\StoreProduct with min_variant_price), $class (wrapper classes, optional)
    Products with options link to their page ("Options" / "View Details"); others add straight to the cart
    ("Buy Now" adds and opens the cart).
--}}
@once
    <style>
        .product-card-body {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            height: 100%;
        }

        .price-stack-container {
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            min-height: 64px;
        }

        .starts-from-slot {
            height: 16px;
            line-height: 16px;
            font-size: 0.8rem;
            margin-bottom: 2px;
        }

        .sales-price-slot {
            font-size: 1.45rem;
            font-weight: 700;
            line-height: 1.1;
            margin: 0;
            color: #1e293b;
        }

        .regular-price-slot {
            height: 18px;
            line-height: 18px;
            font-size: 0.95rem;
            margin: 0;
        }

        .product-action-btn-fixed {
            height: 40px !important;
            min-width: 46px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.9rem;
            border-radius: 0px !important;
            cursor: pointer !important;
        }

        .product-options-btn {
            background-color: #e8f2fd;
            color: #206bc4;
            border: 1px solid #206bc4 !important;
            border-radius: 10px !important;
            padding: 0 14px;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .product-options-btn:hover {
            background-color: #206bc4;
            color: #ffffff;
        }

        .product-cart-btn {
            background-color: #206bc4;
            color: #ffffff;
            padding: 0 14px;
            border: 1px solid #206bc4 !important;
            border-radius: 10px !important;
            transition: all 0.2s ease;
        }

        .product-cart-btn:hover {
            background-color: #1a569d;
            color: #ffffff;
        }

        .secondary-action-btn {
            height: 38px !important;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            cursor: pointer !important;
            border-radius: 10px !important;
        }

        .product-grid-image {
            object-fit: contain;
            object-position: center;
        }
    </style>
@endonce

@php
    $images = array_values(array_filter(array_map('trim', explode(',', $product->product_image))));
    $productUrl = $productUrlPrefix . '/' . $product->id;
    $regularPrice = $product->listingRegularPrice();
    $soldOut = !$product->hasOptions() && $product->product_status != 'instock';
    $placeholder = asset('img/templates/e-commerce/placeholder.svg');
@endphp

<div class="{{ $class ?? '' }}">
    <div class="card h-100 p-2 product-card-body">
        <div>
            <a href="{{ $productUrl }}" class="text-decoration-none">
                <div class="carousel slide mb-2" data-bs-ride="carousel">
                    <div class="carousel-inner">
                        @forelse ($images as $image)
                            <div class="carousel-item {{ $loop->first ? 'active' : '' }} ratio ratio-4x3">
                                <img class="d-block w-100 h-100 rounded-e-commerce product-grid-image" alt="{{ $product->product_name }}"
                                    src="{{ url($image) }}" onerror="this.onerror=null; this.src='{{ $placeholder }}';">
                            </div>
                        @empty
                            <div class="carousel-item active ratio ratio-4x3">
                                <img class="d-block w-100 h-100 rounded-e-commerce product-grid-image" alt="{{ $product->product_name }}" src="{{ $placeholder }}">
                            </div>
                        @endforelse
                    </div>
                </div>

                <div class="badge-container mb-2">
                    @if ($soldOut)
                        <span class="badge bg-danger text-white rounded-0">{{ __('Out of Stock') }}</span>
                    @elseif (!empty($product->badge))
                        <span class="badge rounded-0 text-e-commerce-blue">{{ $product->badge }}</span>
                    @else
                        <span class="badge invisible user-select-none">&nbsp;</span>
                    @endif
                </div>

                <h3 class="fs-2 mb-1 text-truncate text-dark fw-bold" title="{{ $product->product_name }}">{{ $product->product_name }}</h3>
                <p class="text-secondary mb-2 text-truncate">{{ $product->product_short_description }}</p>
            </a>
        </div>

        {{-- Price and buttons share one baseline; empty slots keep every card the same height --}}
        <div class="mt-auto pt-2">
            <div class="d-flex justify-content-between align-items-end mb-2">
                <div class="price-stack-container">
                    @if ($product->hasOptions())
                        <div class="starts-from-slot text-muted fw-medium">{{ __('Starts from') }}</div>
                    @else
                        <div class="starts-from-slot invisible user-select-none">&nbsp;</div>
                    @endif

                    <div class="sales-price-slot">{{ formatCurrencyCard($product->listingPrice(), $currency) }}</div>

                    @if ($regularPrice)
                        <div class="regular-price-slot text-muted text-decoration-line-through fw-semibold">{{ formatCurrencyCard($regularPrice, $currency) }}</div>
                    @else
                        <div class="regular-price-slot invisible user-select-none">&nbsp;</div>
                    @endif
                </div>

                <div>
                    @if ($product->hasOptions())
                        <a href="{{ $productUrl }}" class="product-action-btn-fixed product-options-btn" title="{{ __('Select Options') }}">
                            <i class="ti ti-adjustments-horizontal fs-2 me-1"></i>
                            <span>{{ __('Options') }}</span>
                        </a>
                    @elseif ($soldOut)
                        <button type="button" class="product-action-btn-fixed btn btn-light text-muted opacity-75" disabled title="{{ __('Out of Stock') }}">
                            <i class="ti ti-shopping-bag-x fs-1"></i>
                        </button>
                    @else
                        <button type="button" class="product-action-btn-fixed product-cart-btn" {{ $product->cartAttributes() }}
                            title="{{ __('Add to cart') }}" aria-label="{{ __('Add to cart') }}">
                            <i class="ti ti-shopping-bag-plus fs-1"></i>
                        </button>
                    @endif
                </div>
            </div>

            @if ($product->hasOptions())
                <a href="{{ $productUrl }}" class="btn btn-sm w-100 btn-dark btn-effect-loop fs-3 secondary-action-btn">
                    <i class="ti ti-eye text-white fs-2 pe-1"></i>
                    {{ __('View Details') }}
                </a>
            @elseif ($soldOut)
                <button type="button" class="btn btn-sm w-100 btn-secondary fs-3 secondary-action-btn opacity-75" disabled>
                    <i class="ti ti-ban fs-2 pe-1"></i>
                    {{ __('Out of Stock') }}
                </button>
            @else
                {{-- Adds the item, then opens the cart to order on WhatsApp --}}
                <button type="button" class="btn btn-sm w-100 btn-dark btn-effect-loop fs-3 secondary-action-btn"
                    {{ $product->cartAttributes() }} data-then="cart">
                    <i class="ti ti-bolt text-white fs-2 pe-1"></i>
                    {{ __('Buy Now') }}
                </button>
            @endif
        </div>
    </div>
</div>
