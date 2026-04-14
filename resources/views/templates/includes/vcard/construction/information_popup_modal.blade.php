@php
    use App\InformationPop;
    $information_pop = InformationPop::where('card_id', $business_card_details->card_id)->first();
    if ($information_pop) {
        $confetti_effect = $information_pop->confetti_effect ?? 0;
        $info_img = $information_pop->info_pop_image ?? null;
        $info_title = $information_pop->info_pop_title ?? __('Important Info');
        $info_desc =
            $information_pop->info_pop_desc ??
            __('We pride ourselves on ethical sourcing and a clean, safe environment.');
        $info_btn_text = $information_pop->info_pop_button_text ?? __('Learn More');
        $info_btn_url = $information_pop->info_pop_button_url ?? '#';
    }
@endphp

@if (isset($information_pop))

    <div id="customInfoOverlay" class="info-overlay" onclick="closeInfoModal()">
        <div id="customInfoBox" class="info-box" onclick="event.stopPropagation()">

            {{-- Corner accents --}}
            <span class="info-corner info-corner--tl"></span>
            <span class="info-corner info-corner--tr"></span>
            <span class="info-corner info-corner--bl"></span>
            <span class="info-corner info-corner--br"></span>

            {{-- Close --}}
            <button class="info-close" onclick="closeInfoModal()">
                <i class="fas fa-times"></i>
            </button>

            {{-- Cover image --}}
            @if (!empty($info_img))
                <div class="info-img-wrap">
                    <img src="{{ url($info_img) }}" alt="{{ $info_title }}" class="info-img">
                    <div class="info-img-fade"></div>
                </div>
            @endif

            {{-- Body --}}
            <div class="info-body {{ !empty($info_img) ? 'info-body--with-img' : '' }}">

                {{-- Icon (no image only) --}}
                @if (empty($info_img))
                    <div class="info-icon-wrap">
                        <i class="fas fa-shield-alt info-icon"></i>
                    </div>
                @endif

                {{-- Title --}}
                <h3 class="info-title">{{ $info_title }}</h3>

                {{-- Divider --}}
                <div class="info-divider">
                    <span class="info-divider__line"></span>
                    <span class="info-divider__diamond"></span>
                    <span class="info-divider__line"></span>
                </div>

                {{-- Description --}}
                <div class="info-desc-wrapper">
                    <div class="info-desc">{!! $info_desc !!}</div>
                </div>

                {{-- CTA --}}
                <a href="{{ $info_btn_url }}" target="_blank" rel="noopener" class="info-btn">
                    {{ $info_btn_text }}
                    <i class="fas fa-arrow-right"></i>
                </a>

            </div>
        </div>
    </div>

    @if ($confetti_effect == 1 && $introScreen == null)
        <script src="{{ asset('js/confetti.browser.min.js') }}"></script>
        <script>
            "use strict";

            function triggerInfoConfetti() {
                const colors = Array.from({
                        length: 7
                    }, () =>
                    '#' + Math.floor(Math.random() * 16777215).toString(16).padStart(6, '0')
                );
                confetti({
                    particleCount: window.innerWidth > 768 ? 200 : 100,
                    spread: window.innerWidth > 768 ? 120 : 100,
                    colors: colors,
                    origin: {
                        x: 0.5,
                        y: window.innerWidth > 768 ? 0.75 : 0.72
                    }
                });
            }
        </script>
    @endif

@endif
