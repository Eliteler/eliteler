{{--
    Eliteler: the store's own social links (all but "map"), shown in the footer instead of the share links.
    Footers include it when $storeSocialLinks has non-map links and keep their share links otherwise.
    Optional params to match the theme:
      $socialItemTag    wrap each link in this tag ("li"), or none (default)
      $socialItemClass  class of that wrapper
      $socialLinkClass  class of the <a>
      $socialIconClass  extra classes of the icon (the icon itself comes from the link)
--}}
@foreach ($storeSocialLinks as $socialLink)
    @if ($socialLink->type != 'map')
        @if (!empty($socialItemTag))
            <{{ $socialItemTag }} class="{{ $socialItemClass ?? '' }}">
        @endif
        <a href="{{ $socialLink->type == 'wa' ? 'https://wa.me/' . $socialLink->content : $socialLink->content }}"
            target="_blank" rel="noopener noreferrer" class="{{ $socialLinkClass ?? '' }}"
            title="{{ $socialLink->label ?: $socialLink->type }}" aria-label="{{ $socialLink->label ?: $socialLink->type }}">
            <i class="{{ $socialLink->icon }} {{ $socialIconClass ?? '' }}"></i>
        </a>
        @if (!empty($socialItemTag))
            </{{ $socialItemTag }}>
        @endif
    @endif
@endforeach
