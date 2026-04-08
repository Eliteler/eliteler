@if ($plan_details['hide_branding'] == 1)
    <div class="pb-1">
        <div class="flex pt-5 m-auto font-semibold text-white text-sm flex-col md:flex-row max-w-6xl">
            <div class="mt-2 text-gray-500">
                {{ __('Copyright') }} &copy;
                <a class="text-green-800" href="{{ url()->current() }}">
                    {{ $card_details->title }} </a>
                <span id="year"></span>{{ __('. All Rights Reserved.') }}
            </div>
        </div>
    </div>
@else
    <div class="pb-1">
        <div class="flex m-auto pt-5 font-semibold text-white text-sm flex-col md:flex-row max-w-6xl">
            <div class="mt-2 text-gray-500">
                {{ __('Made with') }}
                <a class="text-green-800" href="{{ env('APP_URL') }}">
                    {{ config('app.name') }} </a>
                <span id="year"></span>{{ __('. All Rights Reserved.') }}
            </div>
        </div>
    </div>
@endif
