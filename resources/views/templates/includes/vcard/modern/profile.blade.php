{{-- Profile Image --}}
<div class="flex justify-center">
    <img src="{{ url($business_card_details->profile) }}" alt="{{ $business_card_details->title }}"
        class="w-36 h-36 lg:w-40 lg:h-40 rounded-2xl border lg:-mt-[100px] -mt-[70px] z-20 bg-transparent object-cover" />
</div>

{{-- Background Image --}}
<div class="text-center mt-3 relative -mx-6">
    <img src="{{ url('img/templates/modern/bg.png') }}" alt="{{ $business_card_details->title }}"
        class="w-full object-cover absolute lg:-top-[94px] -top-[84px] z-10" />
    <div class="relative z-10">
        {{-- Name --}}
        <h1 class="lg:text-4xl text-3xl font-bold text-[#121212] title-text">
            {{ $business_card_details->title }}
        </h1>
        {{-- Job Title --}}
        <p class="text-green-800 font-bold mt-2 text-lg">
            {{ $card_details->sub_title }}
        </p>
        {{-- About --}}
        @if (isset($business_card_details->description) || isset($business_card_details->address))
            <div class="mt-2 text-gray-500 text-md leading-relaxed font-medium px-6">
                {!! $business_card_details->description !!}
            </div>
        @endif
    </div>
</div>
