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

    <div id="customInfoOverlay" class="hidden" onclick="closeInfoModal()">
        <div id="customInfoBox" onclick="event.stopPropagation()">

            {{-- Close button --}}
            <button class="custom-info-close" onclick="closeInfoModal()">
                <i class="fas fa-times"></i>
            </button>

            {{-- Image (if provided) --}}
            @if (!empty($info_img))
                <div class="custom-info-img-wrap">
                    <img src="{{ url($info_img) }}" alt="{{ $info_title }}" class="custom-info-img">
                </div>
            @endif

            {{-- Content body --}}
            <div class="custom-info-body {{ !empty($info_img) ? 'custom-info-body--with-img' : '' }}">

                {{-- Icon badge (only when no image) --}}
                @if (empty($info_img))
                    <div class="custom-info-icon-wrap">
                        <i class="fas fa-info-circle custom-info-icon"></i>
                    </div>
                @endif

                {{-- Title --}}
                <h3 class="custom-info-title">{{ $info_title }}</h3>

                {{-- Gold divider --}}
                <div class="custom-info-divider">
                    <span class="custom-info-divider__line"></span>
                    <span class="custom-info-divider__diamond">◆</span>
                    <span class="custom-info-divider__line"></span>
                </div>

                {{-- Description --}}
                <div class="custom-info-desc-wrapper">
                    <div class="custom-info-desc">{!! $info_desc !!}</div>
                </div>

                {{-- Action button --}}
                <a href="{{ $info_btn_url }}" target="_blank" class="custom-info-btn">
                    {{ $info_btn_text }}
                </a>

            </div>
        </div>
    </div>

    {{-- Confetti effect (if enabled) --}}
    @if ($confetti_effect == 1 && $introScreen == null)
        <script src="{{ asset('js/confetti.browser.min.js') }}"></script>
        <script>
            function getRandomColor() {
                return '#' + Math.floor(Math.random() * 16777215).toString(16);
            }
            const randomColors = Array.from({
                length: 7
            }, getRandomColor);

            function triggerInfoConfetti() {
                confetti({
                    particleCount: window.innerWidth > 768 ? 200 : 100,
                    spread: window.innerWidth > 768 ? 120 : 100,
                    colors: randomColors,
                    origin: {
                        x: 0.5,
                        y: window.innerWidth > 768 ? 0.75 : 0.72
                    }
                });
            }
        </script>
    @endif

@endif
