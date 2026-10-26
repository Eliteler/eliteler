{{--
    Digital products card (listing grid and "Related Products"). Use inside the page's product grid.
    Params: $product (App\StoreProduct with min_variant_price)
    The card is a small [data-product] area for the storefront script: simple products get a quantity
    stepper and "Add to Cart"; products with options link to their page. "Enquiry" asks on WhatsApp.
--}}
@php
    $images = array_filter(array_map('trim', explode(',', $product->product_image)));
    $productUrl = $productUrlPrefix . '/' . $product->id;
    $regularPrice = $product->listingRegularPrice();
    $soldOut = !$product->hasOptions() && $product->product_status != 'instock';
    $placeholder = asset('img/templates/digital-products/placeholder.svg');
@endphp

<div {{ \App\Support\Store\ProductOptions::toAttributes([
        'data-product' => true,
        'data-product-id' => $product->product_id,
        'data-name' => $product->product_name,
        'data-price' => $product->listingPrice(),
        'data-image' => $images ? url(reset($images)) : '',
        'data-subtitle' => $product->product_short_description ?? '',
    ]) }}
    class="product-item bg-white dark:bg-[#111625] border border-slate-200 dark:border-slate-800 hover:border-indigo-500/50 rounded-2xl p-4 flex flex-col justify-between transition-all duration-300 hover:-translate-y-1 shadow-md dark:shadow-lg group">
    <div>
        <a href="{{ $productUrl }}" class="block relative aspect-video rounded-xl overflow-hidden mb-3 bg-slate-100 dark:bg-slate-900">
            <img src="{{ $images ? url(reset($images)) : $placeholder }}" alt="{{ $product->product_name }}"
                onerror="this.onerror=null;this.src='{{ $placeholder }}';"
                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
            <span class="absolute top-2.5 left-2.5 bg-slate-950/80 backdrop-blur-md text-indigo-400 text-[10px] font-bold px-2 py-0.5 rounded-md border border-indigo-500/30">
                {{ __('Digital Asset') }}
            </span>
            @if ($soldOut)
                <span class="absolute top-2.5 right-2.5 bg-rose-600 text-white text-[10px] font-bold px-2 py-0.5 rounded-md">{{ __('Out of Stock') }}</span>
            @endif
        </a>

        @if ($product->badge)
            <span class="inline-block bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 text-[10px] px-2.5 py-0.5 rounded-full font-bold mb-1.5 border border-indigo-200 dark:border-indigo-500/20">
                {{ __($product->badge) }}
            </span>
        @endif

        <a href="{{ $productUrl }}">
            <h4 class="font-bold text-slate-900 dark:text-white text-sm leading-snug truncate group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors"
                title="{{ $product->product_name }}">{{ $product->product_name }}</h4>
        </a>
        <p class="text-slate-500 dark:text-slate-400 text-xs mt-1 line-clamp-2">{{ $product->product_short_description }}</p>
    </div>

    <div class="pt-3 border-t border-slate-100 dark:border-slate-800 mt-4 space-y-3">
        <div class="flex items-center justify-between">
            <div>
                @if ($product->hasOptions())
                    <span class="text-[10px] font-bold text-indigo-600 dark:text-indigo-400 block -mb-1 uppercase tracking-wider">{{ __('Starts from') }}</span>
                @endif
                <span class="text-lg font-extrabold text-slate-900 dark:text-white">{{ formatCurrencyCard($product->listingPrice(), $currency) }}</span>
                @if ($regularPrice)
                    <span class="text-xs text-slate-400 line-through ml-1">{{ formatCurrencyCard($regularPrice, $currency) }}</span>
                @endif
            </div>

            @if ($product->hasOptions())
                <span class="text-[11px] font-semibold text-slate-500 dark:text-slate-400 bg-slate-100 dark:bg-slate-800 px-2 py-1 rounded-lg">{{ __('Options') }}</span>
            @elseif (!$soldOut)
                <div class="flex items-center border border-slate-300 dark:border-slate-700 bg-slate-100 dark:bg-slate-900/80 rounded-xl overflow-hidden p-0.5">
                    <button type="button" data-qty-step="-1" aria-label="{{ __('Decrease') }}"
                        class="w-6 h-6 flex items-center justify-center text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-200 dark:hover:bg-slate-800 rounded transition font-bold text-xs">-</button>
                    <span class="w-7 text-center text-xs font-bold text-slate-900 dark:text-white" data-qty-value>1</span>
                    <button type="button" data-qty-step="1" aria-label="{{ __('Increase') }}"
                        class="w-6 h-6 flex items-center justify-center text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-200 dark:hover:bg-slate-800 rounded transition font-bold text-xs">+</button>
                </div>
            @endif
        </div>

        <div class="grid grid-cols-2 gap-2">
            @if ($product->hasOptions())
                <a href="{{ $productUrl }}"
                    class="w-full bg-indigo-600 hover:bg-indigo-500 text-white font-bold py-2.5 px-2 rounded-xl text-xs flex items-center justify-center gap-1.5 transition shadow-sm">
                    <i class="ti ti-adjustments-horizontal text-sm"></i>
                    <span>{{ __('Options') }}</span>
                </a>
            @else
                <button type="button" data-product-add @disabled($soldOut)
                    class="w-full bg-indigo-600 hover:bg-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed text-white font-bold py-2.5 px-2 rounded-xl text-xs flex items-center justify-center gap-1.5 transition shadow-sm">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                    </svg>
                    <span>{{ $soldOut ? __('Out of Stock') : __('Add to Cart') }}</span>
                </button>
            @endif

            <button type="button" onclick="enquireViaWhatsApp(this)"
                class="w-full bg-emerald-600/15 hover:bg-[#059669] text-emerald-600 dark:text-emerald-400 hover:text-white border border-emerald-500/30 font-bold py-2.5 px-2 rounded-xl text-xs flex items-center justify-center gap-1.5 transition">
                <span>{{ __('Enquiry') }}</span>
            </button>
        </div>
    </div>
</div>
