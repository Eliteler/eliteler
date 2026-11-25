<div class="flex flex-wrap -m-4">
    @forelse ($availableNfcCards as $nfcCard)
        <div class="w-full md:w-1/3 lg:w-1/3 p-4">
            <div class="bg-{{ $template_config->template_color }}-50 p-8 h-full flex flex-col">
                <div class="flex-grow flex flex-col">
                    <x-smart-product-photos :product="$nfcCard" class="mb-6" />

                    <h3 class="text-{{ $template_config->template_color }}-500 text-xl font-bold text-center">
                        {{ $nfcCard->nfc_card_name }}
                    </h3>

                    <p class="text-center text-2xl font-extrabold mb-3">
                        {{ $nfcCard->nfc_card_price == 0 ? __('Free') : formatCurrency($nfcCard->nfc_card_price) }}
                        @if ($nfcCard->compare_at_price && $nfcCard->compare_at_price > $nfcCard->nfc_card_price)
                            <span class="text-base font-medium text-gray-400 line-through ml-1">{{ formatCurrency($nfcCard->compare_at_price) }}</span>
                        @endif
                    </p>

                    <p class="text-gray-500 text-center mb-6">
                        {{ $nfcCard->short_description ?: $nfcCard->nfc_card_description }}
                    </p>
                </div>

                <x-smart-product-order-button :product="$nfcCard"
                    class="mt-auto w-full py-3 rounded text-white text-center bg-{{ $template_config->template_color }}-500 hover:bg-{{ $template_config->template_color }}-600 text-sm font-semibold" />
            </div>
        </div>
    @empty
        <div class="w-full p-4">
            <div class="p-8 h-full flex items-center justify-center">
                <p class="text-xl font-bold text-center text-gray-500">
                    {{ __('No smart products available for ordering.') }}
                </p>
            </div>
        </div>
    @endforelse
</div>
