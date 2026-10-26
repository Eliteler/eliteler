@php
    // Template-specific dynamic classes with sensible fallbacks
    $sectionClass = $sectionClass ?? 'relative my-8';
    $headerClass = $headerClass ?? 'flex items-center my-10';
    $titleClass = $titleClass ?? 'mx-4 text-2xl lg:text-3xl font-medium';
    $dividerClass = $dividerClass ?? 'flex-grow border-t-[3px] opacity-40';
    $cardClass = $cardClass ?? 'overflow-hidden rounded-2xl shadow-sm border bg-white';
    $labelClass = $labelClass ?? 'px-4 py-2.5 font-semibold text-center text-sm';

    // Column Control: 1 for Single Column, 2 for Desktop Double Column
    $columns = $columns ?? 2;
    $gridColClass = $columns == 1 ? 'vcard-insta-grid-1col' : 'vcard-insta-grid-2col';

    // Height Control: 460px fits perfectly up to "View more on Instagram" / Likes
    $height = $height ?? '460px';
@endphp

{{-- Scoped CSS for Standalone 1-Column and 2-Column Grid --}}
<style>
    .vcard-insta-grid-container {
        display: grid;
        grid-template-columns: 1fr;
        gap: 20px;
        width: 100%;
        align-items: start;
        margin: 0 auto;
        padding: 0 1rem;
    }

    /* 2 Columns on Tablet / Desktop */
    @media (min-width: 768px) {
        .vcard-insta-grid-2col {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .vcard-insta-grid-1col {
            grid-template-columns: minmax(0, 1fr);
            max-width: 540px;
        }
    }

    .vcard-insta-card {
        width: 100%;
        max-width: 540px;
        margin: 0 auto;
        box-sizing: border-box;
    }
</style>

<!-- Start Instagram Section -->
@if (isset($feature_details) && $feature_details->where('type', 'instagram-embed')->count() > 0)
    <div class="{{ $sectionClass }}">

        {{-- Render Custom Template Header if provided, otherwise standard adaptive header --}}
        @if (isset($customHeader))
            {!! $customHeader !!}
        @else
            <div class="{{ $headerClass }}">
                <hr class="{{ $dividerClass }}">
                <span class="{{ $titleClass }}">
                    {{ __('Instagram') }}
                </span>
                <hr class="{{ $dividerClass }}">
            </div>
        @endif

        {{-- Self-contained Responsive Grid Container --}}
        <div class="vcard-insta-grid-container {{ $gridColClass }}">
            @foreach ($feature_details as $feature)
                @if ($feature->type == 'instagram-embed')
                    @php
                        $rawUrl = $feature->content ?? '';

                        // Extract shortcode from Instagram Post, Reel, or TV URL
                        preg_match('/(?:p|reel|tv)\/([a-zA-Z0-9_-]+)/i', $rawUrl, $matches);
                        $shortcode = $matches[1] ?? null;

                        // Generate Instagram Embed URL
                        $embedUrl = $shortcode ? "https://www.instagram.com/p/{$shortcode}/embed" : $rawUrl;
                    @endphp

                    @if (!empty($shortcode))
                        <div class="vcard-insta-card {{ $cardClass }}">
                            {{-- Instagram Responsive Embed Iframe (Compact Height) --}}
                            <iframe referrerpolicy="strict-origin-when-cross-origin" width="100%"
                                src="{{ $embedUrl }}" title="{{ $feature->label ?? 'Instagram' }}" frameborder="0"
                                scrolling="no" allowtransparency="true"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                allowfullscreen
                                style="width: 100%; min-height: {{ $height }}; height: {{ $height }}; border: none; overflow: hidden; display: block;">
                            </iframe>

                            {{-- Optional Section Title / Label --}}
                            @if (!empty($feature->label) && $feature->label != 'Instagram Post')
                                <div class="{{ $labelClass }}">
                                    {{ $feature->label }}
                                </div>
                            @endif
                        </div>
                    @endif
                @endif
            @endforeach
        </div>
    </div>
@endif
<!-- End Instagram Section -->
