{{--
    Eliteler: a Google Maps embed for each "map" store social link, shown right before the footer.
    Optional params to match the theme:
      $mapWrapperClass  outer box (default "container-xl mb-4")
      $mapCardClass     frame around the map (default "card p-2 shadow-sm rounded")
      $mapCardStyle     inline style for that frame
      $mapHeight        iframe height in px (default 300)
--}}
@if (isset($storeSocialLinks) && $storeSocialLinks->count() > 0)
    @foreach ($storeSocialLinks as $socialLink)
        @if ($socialLink->type == 'map')
            <div class="{{ $mapWrapperClass ?? 'container-xl mb-4' }}">
                <div class="{{ $mapCardClass ?? 'card p-2 shadow-sm rounded' }}" @if (!empty($mapCardStyle)) style="{{ $mapCardStyle }}" @endif>
                    <iframe referrerpolicy="strict-origin-when-cross-origin" src="https://www.google.com/maps/embed?{!! $socialLink->content !!}" width="100%" height="{{ $mapHeight ?? 300 }}" style="border:0;display:block;" allowfullscreen="" loading="lazy" title="{{ __('Store location') }}"></iframe>
                </div>
            </div>
        @endif
    @endforeach
@endif
