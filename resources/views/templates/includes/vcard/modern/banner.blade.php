<div class="relative">
    {{-- Language Switcher --}}
    @if (
        $business_card_details->is_enable_language_switcher == 1 &&
            is_array(config('app.languages')) &&
            count(config('app.languages')) > 1)
        @include('templates.includes.language-switcher')
    @endif
    <!-- Start Cover Image Section -->
    @if ($business_card_details->cover_type == 'none')
        <div class="lg:h-80 h-60" id="profile">
            {{-- Cover Image --}}
            <img src="{{ url('img/templates/modern/banner.png') }}" alt="{{ $business_card_details->title }}"
                class="w-full h-full object-cover" />
        </div>
    @endif
    <!-- End Cover Image Section -->

    <!-- Start Cover Image Section -->
    @if ($business_card_details->cover_type == 'photo')
        <div class="relative lg:h-80 h-60" id="profile">
            {{-- Cover Image --}}
            <img src="{{ $business_card_details->cover ? url($business_card_details->cover) : asset('images/default-cover.png') }}"
                alt="{{ $business_card_details->title }}" class="w-full h-full object-cover" />
        </div>
    @endif
    <!-- End Cover Image Section -->

    <!-- Start Cover Video Section (Vimeo AP) -->
    @if ($business_card_details->cover_type == 'vimeo-ap')
        <div class="relative w-full lg:h-80 h-60" style="padding-top: 56.25%;" id="profile">
            {{-- Cover Video --}}
            <iframe
                src="https://player.vimeo.com/video/{{ $business_card_details->cover }}?autoplay=1&loop=1&autopause=0&muted=1&controls=0"
                id="vid-player" frameborder="0" referrerpolicy="strict-origin-when-cross-origin"
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                allowfullscreen class="absolute top-0 left-0 w-full h-full">
            </iframe>
        </div>
    @endif
    <!-- End Cover Video Section (Vimeo AP) -->

    <!-- Start Cover Video Section (Vimeo) -->
    @if ($business_card_details->cover_type == 'vimeo')
        <div class="w-full lg:h-80 h-60" style="padding-top: 56.25%;" id="profile">
            {{-- Cover Video --}}
            <iframe
                src="https://player.vimeo.com/video/{{ $business_card_details->cover }}?autoplay=0&loop=1&autopause=0&muted=0&controls=1"
                id="vid-player" frameborder="0" referrerpolicy="strict-origin-when-cross-origin"
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                allowfullscreen class="absolute top-0 left-0 w-full h-full">
            </iframe>
        </div>
    @endif
    <!-- End Cover Video Section (Vimeo) -->

    <!-- Start Cover Video Section (Youtube AP) -->
    @if ($business_card_details->cover_type == 'youtube-ap')
        <div class="relative w-full lg:h-80 h-60" style="padding-top: 56.25%;" id="profile">
            {{-- Cover Video --}}
            <iframe
                src="https://www.youtube.com/embed/{{ $business_card_details->cover }}?autoplay=1&mute=1&controls=0&loop=1&playlist={{ $business_card_details->cover }}"
                id="vid-player" frameborder="0" referrerpolicy="strict-origin-when-cross-origin"
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                allowfullscreen class="absolute top-0 left-0 w-full h-full">
            </iframe>
        </div>
    @endif
    <!-- End Cover Video Section (Youtube AP) -->

    <!-- Start Cover Video Section -->
    @if ($business_card_details->cover_type == 'youtube')
        <div class="w-full lg:h-80 h-60" style="padding-top: 56.25%;" id="profile">
            {{-- Cover Video --}}
            <iframe
                src="https://www.youtube.com/embed/{{ $business_card_details->cover }}?autoplay=0&mute=1&controls=0&loop=1&playlist={{ $business_card_details->cover }}"
                id="vid-player" frameborder="0" referrerpolicy="strict-origin-when-cross-origin"
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                allowfullscreen class="w-full h-full">
            </iframe>
        </div>
    @endif
    <!-- End Cover Video Section -->
</div>
