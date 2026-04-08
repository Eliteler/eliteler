<div class="relative" id="location">
    <h2 class="text-3xl lg:text-4xl font-bold py-12 text-center relative custom-head">
        {{ __('Location') }}
    </h2>
    <img src="{{ url('img/templates/modern/6.png') }}" alt=""
        class="w-36 absolute -top-0 -left-20 animate-move-y" />
    {{-- Google Maps --}}
    @foreach ($feature_details as $feature)
        @if ($feature->type == 'map')
            {{-- Map title --}}
            @if ($feature->label != null)
                <div class="px-5 py-3 bg-green-100 rounded-t-2xl">
                    <div class="text-green-800 font-medium text-lg">
                        {{ $feature->label }}
                    </div>
                </div>
            @endif
            <iframe src="https://www.google.com/maps/embed?{!! $feature->content !!}" width="100%" height="300"
                style="border: 0" allowfullscreen="" loading="lazy" class="rounded-b-2xl"></iframe>
        @endif
    @endforeach
</div>
