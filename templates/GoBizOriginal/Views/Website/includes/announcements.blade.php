@php
    use App\Announcement;

    $announcement = Announcement::where('template_id', 'GoBizOriginal')->where('status', 1)->first();
    $config = $announcement?->announcement_configuration ?? [];
    $items = $config['items'] ?? [];

    $dir = app()->isLocale('ar') || app()->isLocale('ur') || app()->isLocale('he') ? 'rtl' : 'ltr';

    $activeItems = collect($items)->filter(fn($i) => !empty($i['active']))->values();
@endphp

<style>
    #announcement-bar {
        top: 0;
        inset-inline-start: 0;
        /* RTL SAFE */
        width: 100%;
        z-index: 1300;
    }

    /* Marquee container */
    .announcement-marquee {
        overflow: hidden;
        width: 100%;
    }

    /* Track that moves */
    .marquee-track {
        display: inline-flex;
        align-items: center;
        gap: 48px;
        padding: 8px 24px;
        white-space: nowrap;
        will-change: transform;
        direction: ltr;
        /* REQUIRED for RTL */
    }

    /* Item */
    .announcement-item {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: 14px;
    }

    /* LTR: move left */
    .marquee-ltr .marquee-track {
        animation: marquee-ltr linear infinite;
    }

    /* RTL: move right */
    .marquee-rtl .marquee-track {
        animation: marquee-rtl linear infinite;
    }

    /* Keyframes */
    @keyframes marquee-ltr {
        from {
            transform: translateX(0);
        }

        to {
            transform: translateX(-50%);
        }
    }

    @keyframes marquee-rtl {
        from {
            transform: translateX(0);
        }

        to {
            transform: translateX(50%);
        }
    }

    /* Pause on hover */
    .announcement-marquee:hover .marquee-track {
        animation-play-state: paused;
    }

    /* Static mode */
    .announcement-static {
        display: flex;
        justify-content: center;
        gap: 32px;
        padding: 8px 16px;
    }
</style>

@if ($announcement?->announcement_enabled && $activeItems->count())
    <div id="announcement-bar"
        style="background: {{ $config['bg_color'] ?? '#000' }};
                color: {{ $config['text_color'] ?? '#fff' }};">

        @if (!empty($config['marquee']))
            <div class="announcement-marquee marquee-{{ $dir }}" dir="{{ $dir }}">
                <div class="marquee-track" id="marqueeTrack">
                    @foreach ($activeItems as $item)
                        <span class="announcement-item" dir="{{ $dir }}">
                            <span>{!! $item['text'] !!}</span>
                        </span>
                    @endforeach
                </div>
            </div>
        @else
            <div class="announcement-static" dir="{{ $dir }}">
                @foreach ($activeItems as $item)
                    <span class="announcement-item">
                        <span>{!! $item['text'] !!}</span>
                    </span>
                @endforeach
            </div>
        @endif

    </div>
@endif

<script>
    "use strict";
    document.addEventListener('DOMContentLoaded', function() {
        const track = document.getElementById('marqueeTrack');
        if (!track) return;

        const container = track.parentElement;
        let contentWidth = track.scrollWidth;
        const containerWidth = container.offsetWidth;

        // Duplicate content until it fills at least 2x container width
        while (contentWidth < containerWidth * 2) {
            track.innerHTML += track.innerHTML;
            contentWidth = track.scrollWidth;
        }

        // Speed control (px per second)
        const speed = {{ $config['marquee_speed'] ?? 150 }};
        const duration = contentWidth / speed;

        track.style.animationDuration = duration + 's';
    });
</script>
