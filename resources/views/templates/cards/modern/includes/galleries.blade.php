<div class="relative">
    <h2 class="text-3xl lg:text-4xl font-bold py-12 text-center relative custom-head">
        {{ __('Gallery') }}
    </h2>
    <img src="{{ url('img/templates/modern/4.png') }}" alt=""
        class="w-36 absolute -top-4 -left-[70px] -rotate-12 animate-move-y" />
    <div class="w-full slider">
        {{-- Slider images --}}
        @foreach ($galleries_details as $galleries_detail)
            <div class="">
                <!-- Gallery -->
                <div class="flex flex-col items-center justify-center">
                    <img class="w-full h-60 object-cover rounded-t-2xl {{ $galleries_detail->caption ? '' : 'rounded-b-2xl' }}"
                        src="{{ url($galleries_detail->gallery_image) }}" alt="{{ $galleries_detail->caption }}" />
                    @if ($galleries_detail->caption)
                        <h3 class="text-center py-2 text-green-800 bg-green-100 rounded-b-2xl text-md w-full">
                            {{ $galleries_detail->caption }}</h3>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</div>
