{{--
    Cosmetics product card (listing and "related products").
    Params: $product (App\StoreProduct with min_variant_price), $class (wrapper classes, optional)
    Products with options link to their page ("Options"); others add straight to the cart.
--}}
@php
    $images = array_filter(array_map('trim', explode(',', $product->product_image)));
    $productUrl = $productUrlPrefix . '/' . $product->id;
    $regularPrice = $product->listingRegularPrice();
@endphp

<div class="{{ $class ?? '' }}">
    <div class="card d-flex flex-column justify-content-between h-100 p-3 rounded-cosmetics shadow-sm border">
        <a href="{{ $productUrl }}" class="d-flex flex-column text-decoration-none">
            <div id="product-carousel-{{ $product->id }}" class="carousel slide mb-3" data-bs-ride="carousel">
                <div class="carousel-inner">
                    @foreach ($images as $image)
                        <div class="carousel-item {{ $loop->first ? 'active' : '' }} ratio ratio-4x3">
                            <img class="d-block img-fluid object-fit-contain w-100 rounded-cosmetics"
                                alt="{{ $product->product_name }}" src="{{ url($image) }}"
                                onerror="this.onerror=null; this.src='{{ asset('img/templates/all-categories.png') }}';">
                        </div>
                    @endforeach
                </div>
            </div>

            @if ($product->badge)
                <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                    <span class="badge bg-rose-lt text-rose border rounded-pill px-2 py-1">{{ $product->badge }}</span>
                </div>
            @endif

            <h3 class="fs-2 single-product mb-1 text-truncate text-dark fw-bold" title="{{ $product->product_name }}">
                {{ $product->product_name }}
            </h3>
            <p class="text-secondary mb-2 text-truncate">{{ $product->product_short_description }}</p>
        </a>

        {{-- Price and action share one baseline; empty lines keep every card the same height --}}
        <div class="d-flex justify-content-between align-items-end mt-3 pt-2 border-top">
            <div class="d-flex flex-column justify-content-end">
                <small class="d-block fs-5 lh-1 mb-1 fw-semibold {{ $product->hasOptions() ? 'text-rose' : 'invisible' }}">
                    @if ($product->hasOptions())
                        {{ __('Starts from') }}
                    @else
                        &nbsp;
                    @endif
                </small>
                <p class="m-0 fs-2 text-dark fw-bold lh-1">{{ formatCurrencyCard($product->listingPrice(), $currency) }}</p>
                <p class="m-0 fs-4 lh-1 mt-1 {{ $regularPrice ? 'text-muted' : 'invisible' }}">
                    @if ($regularPrice)
                        <del>{{ formatCurrencyCard($regularPrice, $currency) }}</del>
                    @else
                        &nbsp;
                    @endif
                </p>
            </div>

            <div>
                @if ($product->hasOptions())
                    <a href="{{ $productUrl }}"
                        class="btn btn-rose rounded-cosmetics d-inline-flex align-items-center justify-content-center gap-1 px-3 text-decoration-none shadow-none btn-effect"
                        style="height: 40px; min-width: 95px;">
                        <i class="ti ti-adjustments-horizontal fs-2"></i>
                        <span class="fs-4 fw-bold">{{ __('Options') }}</span>
                    </a>
                @elseif ($product->product_status == 'instock')
                    <button type="button" {{ $product->cartAttributes() }}
                        class="btn btn-rose rounded-cosmetics d-inline-flex align-items-center justify-content-center shadow-none btn-effect"
                        style="height: 40px; width: 44px;" title="{{ __('Add to Cart') }}" aria-label="{{ __('Add to Cart') }}">
                        <i class="ti ti-shopping-bag-plus fs-1 text-white"></i>
                    </button>
                @else
                    <button type="button" class="btn btn-secondary rounded-cosmetics px-2" style="height: 40px;" disabled
                        title="{{ __('Out of Stock') }}">
                        <i class="ti ti-ban fs-2"></i>
                    </button>
                @endif
            </div>
        </div>
    </div>
</div>
