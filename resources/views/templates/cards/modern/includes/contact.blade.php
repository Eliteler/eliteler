<div class="flex justify-center gap-4 mt-6 z-20">
    {{-- Loop through the feature_details array and display the icons --}}
    @foreach ($feature_details as $feature)
        @if (in_array($feature->type, ['tel', 'email', 'instagram', 'snapchat', 'address']))
            {{-- Phone --}}
            @if ($feature->type == 'tel')
                <a href="tel:{{ $feature->content }}"
                    class="bg-green-100 h-14 w-14 flex justify-center items-center rounded-2xl hover:bg-green-200 border border-green-600 z-20">
                    <i class="{{ $feature->icon }} fa-xl text-green-800"></i>
                </a>
            @endif

            {{-- Email --}}
            @if ($feature->type == 'email')
                <a href="mailto:{{ $feature->content }}"
                    class="bg-green-100 h-14 w-14 flex justify-center items-center rounded-2xl hover:bg-green-200 border border-green-600 z-20">
                    <i class="{{ $feature->icon }} fa-xl text-green-800"></i>
                </a>
            @endif

            {{-- Location --}}
            @if ($feature->type == 'address')
                <a href="#location"
                    class="bg-green-100 h-14 w-14 flex justify-center items-center rounded-2xl hover:bg-green-200 border border-green-600 z-20">
                    <i class="{{ $feature->icon }} fa-xl text-green-800"></i>
                </a>
            @endif

            {{-- Snapchat --}}
            @if ($feature->type == 'snapchat')
                <a href="{{ $feature->content }}" target="_blank"
                    class="bg-green-100 h-14 w-14 flex justify-center items-center rounded-2xl hover:bg-green-200 border border-green-600 z-20">
                    <i class="{{ $feature->icon }} fa-xl text-green-800"></i>
                </a>
            @endif

            {{-- Instagram --}}
            @if ($feature->type == 'instagram')
                <a href="{{ $feature->content }}" target="_blank"
                    class="bg-green-100 h-14 w-14 flex justify-center items-center rounded-2xl hover:bg-green-200 border border-green-600 z-20">
                    <i class="{{ $feature->icon }} fa-xl text-green-800"></i>
                </a>
            @endif
        @endif
    @endforeach
</div>

{{-- Location --}}
@foreach ($feature_details as $feature)
    @if (in_array($feature->type, ['address']))
        <div class="grid grid-cols-1 gap-4 relative">
            <div
                class="mt-6 bg-green-100 hover:bg-green-200 transition-colors rounded-2xl border border-green-600 w-full">
                <!-- Font Awesome Icon -->
                <a href="https://www.google.com/maps/place/{{ urlencode($feature->content) }}" target="_blank"
                    class="p-4 flex flex-col justify-center">
                    <!-- Font Awesome Icon -->
                    <i class="{{ $feature->icon }} fa-xl text-green-800 text-2xl py-4"></i>
                    <!-- Title -->
                    <h2 class="text-green-800 text-md font-semibold">
                        {{ $feature->label }}
                    </h2>
                    <!-- Description -->
                    <p class="text-gray-600 text-sm flex items-center">
                        {{ $feature->content }}
                    </p>
                </a>
            </div>
        </div>
    @endif
@endforeach


@php
    // List of excluded feature types
    $excludedTypes = ['email', 'tel', 'instagram', 'snapchat', 'address', 'map', 'iframe', 'youtube'];

    // Filter the features to include only valid ones
    $validFeatures = collect($feature_details)->filter(function ($feature) use ($excludedTypes) {
        return isset($feature->type) && !in_array($feature->type, $excludedTypes);
    });
@endphp

@if ($validFeatures->isNotEmpty())
    <div class="relative">
        <h2 class="text-3xl lg:text-4xl font-bold py-12 text-center relative custom-head">
            {{ __('Social Links') }}
        </h2>
        <img src="{{ url('img/templates/modern/1.png') }}" alt=""
            class="w-28 absolute top-2 -right-10 -rotate-12 animate-move-y" />
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach ($validFeatures as $feature)
                {{-- Generate href value dynamically --}}
                @php
                    $href = $feature->content;
                    if ($feature->type == 'wa') {
                        $href = 'https://wa.me/' . $feature->content;
                    } elseif ($feature->type == 'email') {
                        $href = 'mailto:' . $feature->content;
                    } elseif ($feature->type == 'text') {
                        $href = 'javascript:void(0);';
                    }
                @endphp
                <!-- {{ $feature->label }} -->
                <a href="{{ $href }}" target="_blank"
                    class="p-4 bg-green-100 hover:bg-green-200 transition-colors rounded-2xl flex flex-col border border-green-600">
                    <!-- Font Awesome Icon -->
                    <i class="{{ $feature->icon }} text-green-800 text-2xl mb-1"></i>
                    <!-- Title -->
                    <h2 class="text-green-800 text-md font-semibold">
                        {{ $feature->label }}
                    </h2>
                    <!-- Description -->
                    <p class="text-gray-600 text-sm truncate">
                        {{ $feature->content }}
                    </p>
                </a>
            @endforeach
        </div>
    </div>
@endif
