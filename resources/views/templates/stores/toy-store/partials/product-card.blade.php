{{--
    Toy store product card (listing grid and "Related Products" slider).
    Params: $product (App\StoreProduct with min_variant_price), $class (wrapper classes, optional)
    Products with options link to their page; others add straight to the cart.
--}}
@php
    $images = array_values(array_filter(array_map('trim', explode(',', $product->product_image))));
    $productUrl = $productUrlPrefix . '/' . $product->id;
    $regularPrice = $product->listingRegularPrice();
    $soldOut = !$product->hasOptions() && $product->product_status != 'instock';
    $placeholder = asset('img/templates/toy-store/placeholder.svg');
@endphp

<div class="{{ $class ?? '' }}">
    <div class="card d-flex flex-column p-3 h-100">
        <a href="{{ $productUrl }}" class="d-flex flex-column text-decoration-none">
            <div class="carousel slide mb-3" data-bs-ride="carousel">
                <div class="carousel-inner">
                    @forelse ($images as $image)
                        <div class="carousel-item {{ $loop->first ? 'active' : '' }} ratio ratio-4x3">
                            <img class="d-block img-fluid object-fit-contain w-100 rounded-toy-store" alt="{{ $product->product_name }}"
                                src="{{ url($image) }}" onerror="this.onerror=null;this.src='{{ $placeholder }}';">
                        </div>
                    @empty
                        <div class="carousel-item active ratio ratio-4x3">
                            <img class="d-block img-fluid object-fit-contain w-100 rounded-toy-store" alt="{{ $product->product_name }}" src="{{ $placeholder }}">
                        </div>
                    @endforelse
                </div>
            </div>

            @if ($product->badge || $soldOut)
                <div class="badge-container d-flex flex-wrap align-items-center gap-2 mb-2">
                    @if ($product->badge)
                        <span class="badge rounded-toy-store bg-opacity-red">{{ $product->badge }}</span>
                    @endif
                    @if ($soldOut)
                        <span class="badge bg-danger text-white">{{ __('Out of Stock') }}</span>
                    @endif
                </div>
            @endif

            <h3 class="fs-2 single-product mb-1 text-truncate text-dark" title="{{ $product->product_name }}">{{ $product->product_name }}</h3>
            <p class="text-secondary mb-2 text-truncate">{{ $product->product_short_description }}</p>
        </a>

        {{-- Empty lines keep every card's price and button at the same height --}}
        <div class="d-flex justify-content-between align-items-end mt-auto">
            <div>
                <small class="d-block fs-5 lh-1 mb-1 fw-semibold {{ $product->hasOptions() ? 'text-red' : 'invisible' }}">
                    @if ($product->hasOptions())
                        {{ __('Starts from') }}
                    @else
                        &nbsp;
                    @endif
                </small>
                <p class="m-0 fs-3 text-red fw-bold">{{ formatCurrencyCard($product->listingPrice(), $currency) }}</p>
                <p class="fs-4 m-0 {{ $regularPrice ? 'text-muted' : 'invisible' }}">
                    @if ($regularPrice)
                        <del>{{ formatCurrencyCard($regularPrice, $currency) }}</del>
                    @else
                        &nbsp;
                    @endif
                </p>
            </div>
        </div>

        <div class="mt-2">
            @if ($product->hasOptions())
                <a href="{{ $productUrl }}" class="btn btn-sm w-100 btn-red btn-effect-loop fs-3 p-custom">
                    <i class="ti ti-adjustments-horizontal text-white fs-2 pe-1"></i>
                    {{ __('Options') }}
                </a>
            @elseif (!$soldOut)
                <button type="button" class="btn btn-sm w-100 btn-red btn-effect-loop fs-3 p-custom" {{ $product->cartAttributes() }}>
                    <i class="ti ti-shopping-bag-plus text-white fs-2 pe-1"></i>
                    {{ __('Add to Cart') }}
                </button>
            @endif
        </div>
    </div>
</div>
