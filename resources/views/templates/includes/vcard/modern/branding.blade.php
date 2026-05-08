@if ($plan_details['hide_branding'] == 1)
    <div class="pb-1">
        <div class="flex pt-5 m-auto font-semibold text-white text-sm flex-col md:flex-row max-w-6xl">
            <div class="mt-2 text-gray-500">
                {{ __('Copyright') }} &copy;
                <a class="text-[#C6AC8E]" href="{{ !empty($business_card_details->copyright) ? (str_starts_with($business_card_details->copyright, 'http') ? $business_card_details->copyright : 'https://'.$business_card_details->copyright) : env('APP_URL') }}" target="_blank" rel="noopener noreferrer">
                    {{ !empty($business_card_details->copyright) ? $business_card_details->copyright : $card_details->title }} </a>
                <span id="year"></span>{{ __('. All Rights Reserved.') }}
            </div>
        </div>
    </div>
@else
    <div class="pb-1">
        <div class="flex m-auto pt-5 font-semibold text-white text-sm flex-col md:flex-row max-w-6xl">
            <div class="mt-2 text-gray-500">
                {{ __('Made with') }}
                <a class="text-[#C6AC8E]" href="{{ !empty($business_card_details->copyright) ? (str_starts_with($business_card_details->copyright, 'http') ? $business_card_details->copyright : 'https://'.$business_card_details->copyright) : env('APP_URL') }}" target="_blank" rel="noopener noreferrer">
                    {{ !empty($business_card_details->copyright) ? $business_card_details->copyright : parse_url(config('app.url'), PHP_URL_HOST) }} </a>
                <span id="year"></span>{{ __('. All Rights Reserved.') }}
            </div>
        </div>
    </div>
@endif
