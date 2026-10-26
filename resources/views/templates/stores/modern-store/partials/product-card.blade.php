{{--
    Modern store product card (listing grid and "Related Products" slider).
    Params: $product (App\StoreProduct with min_variant_price), $class (wrapper classes, optional)
    Products with options link to their page; others add straight to the cart.
--}}
@php
    $images = array_values(array_filter(array_map('trim', explode(',', $product->product_image))));
    $productUrl = $productUrlPrefix . '/' . $product->id;
    $regularPrice = $product->listingRegularPrice();
    $soldOut = !$product->hasOptions() && $product->product_status != 'instock';
    $placeholder = asset('img/templates/modern-store/placeholder.svg');
    $ink = $storeTheme->isDark() ? 'text-white' : 'text-dark';
@endphp

<div class="{{ $class ?? '' }}">
    <div class="card d-flex flex-column p-3 h-100">
        <a href="{{ $productUrl }}" class="d-flex flex-column text-decoration-none">
            <div class="carousel slide mb-3" data-bs-ride="carousel">
                <div class="carousel-inner">
                    @forelse ($images as $image)
                        <div class="carousel-item {{ $loop->first ? 'active' : '' }} ratio ratio-4x3">
                            <img class="d-block img-fluid object-fit-contain w-100 rounded" alt="{{ $product->product_name }}"
                                src="{{ url($image) }}" onerror="this.onerror=null;this.src='{{ $placeholder }}';">
                        </div>
                    @empty
                        <div class="carousel-item active ratio ratio-4x3">
                            <img class="d-block img-fluid object-fit-contain w-100 rounded" alt="{{ $product->product_name }}" src="{{ $placeholder }}">
                        </div>
                    @endforelse
                </div>
            </div>

            @if ($product->badge || $soldOut)
                <div class="badge-container d-flex flex-wrap align-items-center gap-2 mb-2">
                    @if ($product->badge)
                        <span class="badge {{ $storeTheme->isDark() ? 'badge-custom' : '' }}">{{ $product->badge }}</span>
                    @endif
                    @if ($soldOut)
                        <span class="badge bg-danger text-white">{{ __('Out of Stock') }}</span>
                    @endif
                </div>
            @endif

            <h3 class="fs-2 single-product mb-1 text-truncate {{ $ink }}" title="{{ $product->product_name }}">{{ $product->product_name }}</h3>
            <p class="{{ $storeTheme->isDark() ? 'text-white' : 'text-secondary' }} mb-2 text-truncate">{{ $product->product_short_description }}</p>
        </a>

        {{-- Price and button share one baseline; empty lines keep every card the same height --}}
        <div class="d-flex justify-content-between align-items-end mt-auto">
            <div>
                <small class="d-block fs-5 lh-1 mb-1 fw-semibold {{ $product->hasOptions() ? 'text-' . $storeTheme->color : 'invisible' }}">
                    @if ($product->hasOptions())
                        {{ __('Starts from') }}
                    @else
                        &nbsp;
                    @endif
                </small>
                <p class="m-0 fs-3 {{ $ink }}">{{ formatCurrencyCard($product->listingPrice(), $currency) }}</p>
                <p class="fs-4 m-0 {{ $regularPrice ? 'text-muted' : 'invisible' }}">
                    @if ($regularPrice)
                        <del>{{ formatCurrencyCard($regularPrice, $currency) }}</del>
                    @else
                        &nbsp;
                    @endif
                </p>
            </div>

            @if ($product->hasOptions())
                <a href="{{ $productUrl }}" class="btn btn-{{ $storeTheme->color }} btn-sm d-inline-flex align-items-center gap-1 btn-effect"
                    title="{{ __('Select Options') }}">
                    <i class="ti ti-adjustments-horizontal fs-2"></i>
                    <span>{{ __('Options') }}</span>
                </a>
            @elseif (!$soldOut)
                <button type="button" class="border-0 bg-transparent p-0 cursor-pointer btn-effect" {{ $product->cartAttributes() }}
                    title="{{ __('Add to Cart') }}" aria-label="{{ __('Add to Cart') }}">
                    <i class="ti ti-shopping-bag-plus bg-{{ $storeTheme->color }} text-white p-custom fs-1 rounded-circle"></i>
                </button>
            @endif
        </div>
    </div>
</div>
