@if ($plan_details['hide_branding'] == 1)
    <div class="pb-1">
        <div class="flex pt-5 m-auto font-semibold text-white text-sm flex-col md:flex-row max-w-6xl">
            <div class="mt-2 text-gray-500">
                {{ __('Copyright') }} &copy;
                @php $__cr = $business_card_details->copyright ?? ''; $__cr_is_domain = !empty($__cr) && (str_starts_with($__cr, 'http') || str_contains($__cr, '.')); $__cr_url = $__cr_is_domain ? (str_starts_with($__cr, 'http') ? $__cr : 'https://'.$__cr) : (!empty($__cr) ? null : env('APP_URL')); $__cr_text = !empty($__cr) ? $__cr : parse_url(config('app.url'), PHP_URL_HOST); @endphp @if($__cr_url)<a class="text-[#C6AC8E]" href="{{ $__cr_url }}" target="_blank" rel="noopener noreferrer">{{ $__cr_text }}</a>@else<span class="text-[#C6AC8E]">{{ $__cr_text }}</span>@endif
                <span id="year"></span>{{ __('. All Rights Reserved.') }}
            </div>
        </div>
    </div>
@else
    <div class="pb-1">
        <div class="flex m-auto pt-5 font-semibold text-white text-sm flex-col md:flex-row max-w-6xl">
            <div class="mt-2 text-gray-500">
                {{ __('Made with') }}
                @php $__cr = $business_card_details->copyright ?? ''; $__cr_is_domain = !empty($__cr) && (str_starts_with($__cr, 'http') || str_contains($__cr, '.')); $__cr_url = $__cr_is_domain ? (str_starts_with($__cr, 'http') ? $__cr : 'https://'.$__cr) : (!empty($__cr) ? null : env('APP_URL')); $__cr_text = !empty($__cr) ? $__cr : parse_url(config('app.url'), PHP_URL_HOST); @endphp @if($__cr_url)<a class="text-[#C6AC8E]" href="{{ $__cr_url }}" target="_blank" rel="noopener noreferrer">{{ $__cr_text }}</a>@else<span class="text-[#C6AC8E]">{{ $__cr_text }}</span>@endif
                <span id="year"></span>{{ __('. All Rights Reserved.') }}
            </div>
        </div>
    </div>
@endif
