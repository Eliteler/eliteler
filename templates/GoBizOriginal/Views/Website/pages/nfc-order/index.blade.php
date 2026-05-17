@extends('GoBizOriginal::Website.layouts.index', ['nav' => true, 'banner' => false, 'footer' => true, 'cookie' => true, 'setting' => true, 'title' => __('NFC Card Order')])

{{-- Custom CSS --}}
@section('custom-script')
    <!-- Include Alpine.js -->
    <script src="{{ asset('app/js/alpinejs.js') }}" defer></script>
@endsection

@section('content')
    <section class="pt-8 pb-28">
        <div class="container mx-auto px-4">
            <div class="flex justify-center mb-6">
                <div class="bg-{{ $template_config->template_color }}-100 text-{{ $template_config->template_color }}-600 px-2 py-1 text-xs font-bold inline-block rounded">
                    {{ __('NFC Card Ordering') }}</div>
            </div>
            <h1 class="font-heading text-center text-4xl lg:text-5xl font-bold mb-4 max-w-3xl mx-auto">
                {{ __('Order NFC Cards For Your Business') }}
            </h1>
            <p class="text-gray-600 text-center mb-12 max-w-2xl mx-auto">
                {{ __('Choose the NFC card package that fits your team. You can start with a single card or scale to bundles for your entire organization.') }}
            </p>

            <div class="flex flex-wrap -m-4">
                @forelse ($availableNfcCards as $nfcCard)
                    <div class="w-full md:w-1/3 lg:w-1/3 p-4">
                        <div class="bg-{{ $template_config->template_color }}-50 p-8 h-full flex flex-col">

                            <div class="flex-grow flex flex-col">

                                <!-- Auto Image Slider (no x-for, no image/index vars) -->
                                <div x-data="{
                                    active: 0,
                                    start() {
                                        setInterval(() => {
                                            this.active = this.active === 0 ? 1 : 0;
                                        }, 3000);
                                    }
                                }" x-init="start()" class="relative w-full mb-6"
                                    style="height: 223px;">
                                    <div class="relative w-full h-full overflow-hidden rounded">
                                        <!-- Front image -->
                                        <img x-show="active === 0" x-transition.opacity
                                            src="{{ asset($nfcCard->nfc_card_front_image) }}"
                                            class="absolute inset-0 w-full h-full object-cover">

                                        <!-- Back image -->
                                        <img x-show="active === 1" x-transition.opacity
                                            src="{{ asset($nfcCard->nfc_card_back_image) }}"
                                            class="absolute inset-0 w-full h-full object-cover">
                                    </div>
                                </div>

                                <h3 class="text-{{ $template_config->template_color }}-500 text-xl font-bold text-center">
                                    {{ $nfcCard->nfc_card_name }}
                                </h3>

                                <p class="text-center text-2xl font-extrabold mb-3">
                                    {{ $nfcCard->nfc_card_price == 0 ? __('Free') : formatCurrency($nfcCard->nfc_card_price) }}
                                </p>

                                <p class="text-gray-500 text-center mb-6">
                                    {{ $nfcCard->nfc_card_description }}
                                </p>
                            </div>

                            @guest
                                <a href="{{ route('login') }}?redirect={{ urlencode(route('user.order.nfc.card.checkout', $nfcCard->nfc_card_id)) }}"
                                    class="mt-auto w-full py-3 rounded text-white text-center bg-{{ $template_config->template_color }}-500 hover:bg-{{ $template_config->template_color }}-600 text-sm font-semibold">
                                    {{ __('Order Now') }}
                                </a>
                            @else
                                <a href="{{ route('user.order.nfc.card.checkout', $nfcCard->nfc_card_id) }}"
                                    class="mt-auto w-full py-3 rounded text-white text-center bg-{{ $template_config->template_color }}-500 hover:bg-{{ $template_config->template_color }}-600 text-sm font-semibold">
                                    {{ __('Order Now') }}
                                </a>
                            @endguest

                        </div>
                    </div>
                @empty
                    <div class="w-full p-4">
                        <div class="p-8 h-full flex items-center justify-center">
                            <p class="text-xl font-bold text-center text-gray-500">
                                {{ __('No NFC cards available for ordering.') }}
                            </p>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </section>
@endsection
