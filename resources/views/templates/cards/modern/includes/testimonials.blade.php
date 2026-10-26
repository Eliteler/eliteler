<div class="relative">
    <h2 class="text-3xl lg:text-4xl font-bold py-12 text-center relative custom-head">
        {{ __('Client Reviews') }}
    </h2>
    <img src="{{ url('img/templates/modern/5.png') }}" alt=""
        class="w-20 absolute top-16 -right-4 animate-move-y" />
    <div class="review-slider border border-green-600 p-6 bg-green-100 rounded-2xl">
        {{-- Client Reviews --}}
        @foreach ($testimonials as $testimonial)
            <!-- Testimonial -->
            <div class="text-center">
                {{-- Review --}}
                <p class="text-gray-500 text-lg w-full">
                    "{{ $testimonial->review }}"
                </p>
                <div class="flex flex-col items-center justify-center">
                    {{-- Image --}}
                    <img class="w-14 h-14 rounded-full mt-4 mb-1 object-cover"
                        src="{{ url($testimonial->reviewer_image) }}" alt="{{ $testimonial->reviewer_name }}" />
                    {{-- Name --}}
                    <h4 class="text-green-800 text-lg font-medium text-center ">
                        {{ $testimonial->reviewer_name }}
                    </h4>
                    {{-- Position --}}
                    @if ($testimonial->review_subtext)
                        <p class="text-gray-600 text-sm text-center ">
                            {{ $testimonial->review_subtext }}</p>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</div>
