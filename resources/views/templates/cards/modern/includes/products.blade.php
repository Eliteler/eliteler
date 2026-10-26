<div class="relative">
    <h2 class="text-3xl lg:text-4xl font-bold py-12 text-center relative custom-head">
        {{ __('Products') }}
    </h2>
    <img src="{{ url('img/templates/modern/2.png') }}" alt=""
        class="w-32 absolute top-0 -right-20 animate-move-y" />
    <div class="slider">
        {{-- All products --}}
        @foreach ($product_details as $product_detail)
            <div class="flex flex-col justify-center border p-4 rounded-2xl shadow-[0_0_4px_rgba(0,0,0,0.1)]">
                {{-- Badge --}}
                @if (!empty($product_detail->badge))
                    <p class="absolute top-6 right-6 text-white bg-green-800 px-4 py-1 rounded-xl">
                        {{ $product_detail->badge }}
                    </p>
                @endif
                {{-- Image --}}
                <img class="w-full h-48 object-cover rounded-2xl mb-3" src="{{ url($product_detail->product_image) }}"
                    alt="{{ $product_detail->product_name }}" />
                {{-- Stock --}}
                @if ($product_detail->product_status != 'null')
                    <p
                        class="px-3 py-1 rounded-lg {{ $product_detail->product_status == 'instock' ? 'text-green-800 bg-green-100' : 'text-red-800 bg-red-100' }} font-medium inline-block mb-2">
                        {{ $product_detail->product_status == 'outstock' ? __('Out of Stock') : __('In Stock') }}</p>
                @endif
                {{-- Name --}}
                <h2 class="text-lg font-medium mb-2">
                    {{ $product_detail->product_name }}
                </h2>
                {{-- Description --}}
                <p class="text-gray-500 font-normal">
                    {{ $product_detail->product_description }}
                </p>

                <!-- Price & Booking Section -->
                <div class="flex flex-col mt-1">
                    <!-- Price -->
                    @if ($product_detail->sales_price != 0)
                        <div>
                            <h4 class="text-base font-medium">
                                {{ __('Price:') }}
                                <span class="text-gray-500 font-medium">
                                    {{ formatCurrencyVcard($product_detail->sales_price, $product_detail->currency) }}</span>
                                {{-- Check regular price is exists --}}
                                @if ($product_detail->sales_price != $product_detail->regular_price)
                                    <span class="line-through ml-2 text-gray-500 text-base">
                                        {{ formatCurrencyVcard($product_detail->regular_price, $product_detail->currency) }}</span>
                                @endif
                            </h4>
                        </div>
                    @endif

                    <!-- Enquire -->
                    @if ($enquiry_button != null)
                        @if ($whatsAppNumberExists == true)
                            <div class="mt-2 w-full">
                                <a href="https://wa.me/{{ $enquiry_button }}?text={{ __('Hi, I am interested in your product:') }} {{ $product_detail->product_name }}. {{ __('Please provide more details.') }}"
                                    target="_blank"
                                    class="text-white w-full px-12 bg-green-800 text-lg font-medium py-2 rounded-2xl transition-colors block text-center">
                                    {{ __('Enquire') }}
                                </a>
                            </div>
                        @endif
                    @endif
                </div>
                <!-- End Price & Booking Section -->
            </div>
        @endforeach
    </div>
</div>
