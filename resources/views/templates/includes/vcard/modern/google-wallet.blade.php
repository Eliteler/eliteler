<div class="relative">
    <h2 class="text-3xl lg:text-4xl font-bold py-12 text-center relative custom-head">
        {{ __('Google Wallet') }}
    </h2>

    <div class="w-full max-w-full border p-6 rounded-2xl bg-green-100 border-green-600">
        {{-- Pass/Ticket Description --}}
        @if ($google_wallet_details->wallet_description != null)
            <div class="text-sm">
                {!! $google_wallet_details->wallet_description ?? '' !!}
            </div>
        @endif
        {{-- Google Wallet Button --}}
        @if ($google_wallet_details->wallet_link != null)
            <div class="flex justify-center mt-6">
                <a href="{{ $google_wallet_details->wallet_link }}" class="w-full lg:w-1/2" target="_blank"
                    rel="noopener noreferrer">
                    <img src="{{ url()->to('/') . '/img/google-wallet-btn.png' }}" alt=""
                        class="w-full object-cover">
                </a>
            </div>
        @endif
    </div>
</div>
