<div class="relative">
    <h2 class="text-3xl lg:text-4xl font-bold py-12 text-center relative custom-head">
        {{ __('Youtube Videos') }}
    </h2>
    <img src="{{ url('img/templates/modern/3.png') }}" alt=""
        class="w-36 absolute top-2 -right-16 animate-move-y" />
    <div class="grid sm:grid-cols-2 lg:grid-cols-2 gap-4 items-center ">
        {{-- Videos --}}
        @foreach ($feature_details as $feature)
            @if ($feature->type == 'youtube')
                <!-- Video 1 -->
                <div class="overflow-hidden rounded-2xl">
                    {{-- Add Youtube title --}}
                    @if ($feature->label != null)
                        <div class="px-4 py-4 bg-black">
                            <div class="text-white font-medium text-lg">
                                {{ $feature->label }}
                            </div>
                        </div>
                    @endif
                    <iframe width="100%" height="270" src="https://www.youtube.com/embed/{{ $feature->content }}"
                        title="{{ $feature->label }}" frameborder="0"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                        allowfullscreen></iframe>
                </div>
            @endif
        @endforeach
    </div>
</div>
