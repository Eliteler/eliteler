<div class="relative">
    <h2 class="text-3xl lg:text-4xl font-bold py-12 text-center relative custom-head">
        {{ __('Payment Options') }}
    </h2>
    <img src="{{ url('img/templates/modern/2.png') }}" alt=""
        class="w-32 absolute top-0 -right-20 animate-move-y" />
    <div class="grid lg:grid-cols-2 gap-4">
        {{-- Payment options --}}
        @foreach ($payment_details as $payment)
            <!-- {{ $payment->label }} Option -->
            <div class="flex flex-col bg-green-100 border border-green-600 rounded-2xl p-4">
                <div class="flex justify-between items-center">
                    {{-- Payment icon/image --}}
                    @include('templates.partials.payment-link-image')

                    <!-- Payment link icon -->
                    @if ($payment->type == 'url')
                        <a href="https://{{ str_replace('https://', '', $payment->content) }}" target="_blank"
                            rel="noopener noreferrer">
                            <div
                                class="border border-green-600 bg-green-100 rounded-full p-2 flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round"
                                    class="icon icon-tabler icons-tabler-outline icon-tabler-external-link text-green-800 h-6 w-6">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                    <path d="M12 6h-6a2 2 0 0 0 -2 2v10a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-6" />
                                    <path d="M11 13l9 -9" />
                                    <path d="M15 4h5v5" />
                                </svg>
                            </div>
                        </a>
                    @endif

                    {{-- UPI Payment --}}
                    @if ($payment->type == 'upi')
                        <a href="upi://pay?pa={{ $payment->content }}&pn={{ urlencode($payment->label) }}&am=1&cu=INR"
                            target="_blank" rel="noopener noreferrer">
                            <div
                                class="border border-green-600 bg-green-100 rounded-full p-2 flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round"
                                    class="icon icon-tabler icons-tabler-outline icon-tabler-external-link text-green-800 h-6 w-6">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                    <path d="M12 6h-6a2 2 0 0 0 -2 2v10a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-6" />
                                    <path d="M11 13l9 -9" />
                                    <path d="M15 4h5v5" />
                                </svg>
                            </div>
                        </a>
                    @endif
                </div>
                <h3 class="font-medium text-gray-800 {{ $payment->type == 'text' ? 'py-3' : 'pt-3' }}">
                    {{ $payment->label }}</h3>
                <!-- Bank Details (Optional) -->
                @if ($payment->type == 'text')
                    <p class="text-gray-600 break-word text-base">
                        @foreach (explode('.', $payment->content) as $sentence)
                            @if (trim($sentence))
                                <!-- Make sure the sentence is not empty -->
                                {{ trim($sentence) }}
                                <br> <!-- Break the line after each sentence -->
                            @endif
                        @endforeach
                    </p>
                @endif
            </div>
        @endforeach
    </div>
</div>
