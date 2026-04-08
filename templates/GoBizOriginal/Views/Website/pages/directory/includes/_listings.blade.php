@if ($business_cards->count() > 0)
    <div class="dir-grid" id="dir-grid">
        @foreach ($business_cards as $businessCard)
            @php
                // ── Default fallbacks ──
                $defaultCover = asset('images/default-cover.png');
                $defaultProfile = asset('images/default-profile.jpg');

                // ── Cover logic ──
                if ($businessCard->card_type === 'store') {
                    // Store: cover is a JSON array
                    $covers = json_decode($businessCard->cover, true);
                    $coverSrc = !empty($covers) && !empty($covers[0]) ? $covers[0] : $defaultCover;
                    $coverType = 'photo';
                } else {
                    // vCard: resolve by cover_type
                    $coverType = !empty($businessCard->cover_type) ? $businessCard->cover_type : 'none';

                    if ($coverType === 'photo') {
                        // photo type: use uploaded cover if exists, else default
                        $coverSrc = !empty($businessCard->cover) ? asset($businessCard->cover) : $defaultCover;
                    } elseif (in_array($coverType, ['youtube', 'youtube-ap', 'vimeo', 'vimeo-ap'])) {
                        // video type: use video ID if exists, else fallback to default image
                        $coverSrc = !empty($businessCard->cover) ? $businessCard->cover : null;
                        // if no video ID, force fallback to default image
                        if (empty($coverSrc)) {
                            $coverType = 'none';
                            $coverSrc = $defaultCover;
                        }
                    } elseif ($coverType === 'none') {
                        // none type: use theme banner if theme_code exists, else default
                        $coverSrc = !empty($businessCard->theme_code)
                            ? asset('img/templates/' . $businessCard->theme_code . '/banner.png')
                            : $defaultCover;
                    } else {
                        // unexpected value: fallback to default
                        $coverType = 'none';
                        $coverSrc = $defaultCover;
                    }
                }

                // ── Profile logic ──
                $profileSrc = !empty($businessCard->profile) ? asset($businessCard->profile) : $defaultProfile;

                // ── Stats ──
                $viewsCount = $businessCard->views_count ?? 0;
                $createdDate = \Carbon\Carbon::parse($businessCard->created_at)->format('M j, Y');
            @endphp

            <div class="dir-card">
                <div class="dir-cover">
                    @if (in_array($coverType, ['youtube', 'youtube-ap']))
                        <iframe
                            src="https://www.youtube.com/embed/{{ $coverSrc }}?autoplay={{ $coverType === 'youtube-ap' ? 1 : 0 }}&mute=1&loop=1&playlist={{ $coverSrc }}&controls=0&showinfo=0&rel=0&playsinline=1"
                            frameborder="0" allow="autoplay; encrypted-media" allowfullscreen loading="lazy"
                            title="{{ $businessCard->title }}">
                        </iframe>
                    @elseif (in_array($coverType, ['vimeo', 'vimeo-ap']))
                        <iframe
                            src="https://player.vimeo.com/video/{{ $coverSrc }}?autoplay={{ $coverType === 'vimeo-ap' ? 1 : 0 }}&muted=1&loop=1&controls=0&background=1"
                            frameborder="0" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen
                            loading="lazy" title="{{ $businessCard->title }}">
                        </iframe>
                    @else
                        <img src="{{ $coverSrc }}" alt="{{ $businessCard->title ?? 'Cover' }}" loading="lazy"
                            onerror="this.onerror=null;this.src='{{ $defaultCover }}'">
                    @endif
                </div>

                <div class="dir-body">
                    <div class="dir-meta-row">
                        <img src="{{ $profileSrc }}" alt="{{ $businessCard->title ?? 'Profile' }}"
                            class="dir-avatar" onerror="this.onerror=null;this.src='{{ $defaultProfile }}'">
                        <span class="dir-badge {{ $businessCard->card_type ?? '' }}">
                            {{ $businessCard->card_type === 'vcard' ? __('vCard') : __('Store') }}
                        </span>
                    </div>
                    <h3 class="dir-title">{{ $businessCard->title ?? '' }}</h3>
                    <p class="dir-sub">{{ $businessCard->sub_title ?? '' }}</p>
                    <div class="dir-stats-row">
                        <span class="dir-stat" title="{{ __('Total views') }}">
                            <i class="fas fa-eye"></i>
                            {{ number_format($viewsCount) }}
                        </span>
                        <span class="dir-stat" title="{{ __('Created') }}">
                            <i class="fas fa-calendar-days"></i>
                            {{ $createdDate }}
                        </span>
                    </div>
                    <a href="{{ route('profile', $businessCard->card_url) }}" target="_blank" class="dir-btn-view">
                        {{ __('View Profile') }} <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        @endforeach
    </div>

    @if ($business_cards->lastPage() > 1)
        <div class="dir-pagination">
            {{ $business_cards->appends(request()->query())->links('vendor.pagination.directory') }}
        </div>
    @endif
@else
    <div class="dir-empty">
        <div class="dir-empty-icon"><i class="fas fa-magnifying-glass"></i></div>
        <h3>{{ __('No listings found') }}</h3>
        <p>{{ __('Try adjusting your search or filters to find what\'s right for you.') }}</p>
    </div>
@endif
