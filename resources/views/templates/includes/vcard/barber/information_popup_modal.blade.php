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
    <div id="customInfoOverlay" class="fixed inset-0 bb-modal-overlay flex items-center justify-center z-50 p-4"
        onclick="closeInfoModal()">
        <div id="customInfoBox" class="bb-panel rounded-2xl w-full max-w-md relative overflow-hidden"
            onclick="event.stopPropagation()">
            <button type="button" class="bb-icon-btn absolute w-10 h-10 rounded-full z-10" style="right: 1rem; top: 1rem;"
                onclick="closeInfoModal()" aria-label="{{ __('Close') }}"><i class="fas fa-times"></i></button>
            @if (!empty($info_img)) 
                <img src="{{ url($info_img) }}" alt="{{ $info_title }}" class="w-full h-48 object-cover opacity-70">
            @endif
            <div class="p-6 text-center">
                @if (empty($info_img))
                    <div class="flex items-center justify-center w-16 h-16 rounded-full mx-auto mb-4"
                        style="border: 1px solid var(--bb-gold); color: var(--bb-gold);"><i
                            class="fas fa-shield-alt text-2xl"></i></div>
                @endif
                <p class="bb-eyebrow mb-2">{{ __('The straight story') }}</p>
                <h3 class="bb-display text-3xl bb-gold-text">{{ $info_title }}</h3>
                <div class="bb-divider my-4"><span class="bb-divider-mark"></span></div>
                <div class="bb-text-muted text-sm leading-relaxed bb-clamp-3 mb-4">{!! $info_desc !!}</div>
                <a href="{{ $info_btn_url }}" target="_blank" rel="noopener"
                    class="bb-btn-primary inline-flex items-center justify-center gap-2 px-5 py-3 rounded-lg mt-5">{{ $info_btn_text }}</a>
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
                }, () => '#' + Math.floor(Math.random() * 16777215).toString(16).padStart(6, '0'));
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

<script>
    "use strict";
    window.closeInfoModal = function() {
        document.getElementById('customInfoOverlay')?.classList.add('hidden');
    };
    window.openInfoModal = function() {
        document.getElementById('customInfoOverlay')?.classList.remove('hidden');
        if (typeof triggerInfoConfetti === 'function') setTimeout(triggerInfoConfetti, 300);
    };
</script>
