<div class="relative">
    <h2 class="text-3xl lg:text-4xl font-bold py-12 text-center relative custom-head">
        {{ __('Iframe') }}
    </h2>
    <img src="{{ url('img/templates/modern/leaf-4.png') }}" alt=""
         class="w-28 absolute top-2 -left-16 rotate-12 animate-move-y" />
    <div class="grid grid-cols-1 gap-4 items-center">
        {{-- Iframe --}}
        @foreach ($feature_details as $feature)
            @if ($feature->type == 'iframe')
                <div class="overflow-hidden rounded-2xl">
                    <iframe width="100%" height="270" src="{{ $feature->content }}" title="{{ $feature->label }}"
                        frameborder="0"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                        allowfullscreen></iframe>
                    {{-- Add Iframe title --}}
                    @if ($feature->label != null)
                        <div class="px-5 py-3 bg-green-100">
                            <div class="text-green-800 font-semibold text-lg text-center">
                                {{ $feature->label }}
                            </div>
                        </div>
                    @endif
                </div>
            @endif
        @endforeach
    </div>
</div>
