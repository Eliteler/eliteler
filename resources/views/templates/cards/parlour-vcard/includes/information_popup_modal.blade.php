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
    <div id="customInfoOverlay" class="fixed flex items-center justify-center z-50 p-4 hidden"
        onclick="closeInfoModal()">
        <div id="customInfoBox" class="sl-panel rounded-3xl w-full max-w-md relative overflow-hidden shadow-2xl"
            onclick="event.stopPropagation()">

            {{-- Close Button --}}
            <button type="button" class="sl-modal-close" onclick="closeInfoModal()" aria-label="{{ __('Close') }}">
                <i class="fas fa-times"></i>
            </button>

            {{-- Feature Image Banner --}}
            @if (!empty($info_img))
                <div class="relative h-48 w-full overflow-hidden">
                    <img src="{{ url($info_img) }}" alt="{{ $info_title }}" class="w-full h-full object-cover" />
                    <div class="absolute inset-0 bg-gradient-to-t from-[#ffffff] via-transparent to-transparent"></div>
                </div>
            @endif

            {{-- Content Body --}}
            <div class="p-6 text-center space-y-3">
                @if (empty($info_img))
                    <div
                        class="w-14 h-14 rounded-full mx-auto flex items-center justify-center text-xl text-[#E29A88] mb-3 bg-[#FDF7F4] border-2 border-[#E29A88]">
                        <i class="fas fa-sparkles"></i>
                    </div>
                @endif

                <p class="text-xs font-bold uppercase tracking-widest text-[#E29A88]">{{ __('Announcement') }}</p>

                <h3 class="sl-display text-3xl font-bold text-[#721B29]">{{ $info_title }}</h3>

                <div class="sl-divider my-3"><span class="sl-divider-mark"></span></div>

                <div class="text-xs text-[#735D61] leading-relaxed sl-clamp-3 mb-4">
                    {!! $info_desc !!}
                </div>

                @if (!empty($info_btn_url) && $info_btn_url !== '#')
                    <div class="pt-2">
                        <a href="{{ $info_btn_url }}" target="_blank" rel="noopener"
                            class="sl-btn-primary block w-full py-3.5 rounded-xl text-xs sm:text-sm font-bold text-center uppercase tracking-wider">
                            {{ $info_btn_text }}
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Confetti Effect --}}
    @if ($confetti_effect == 1 && $introScreen == null)
        <script src="{{ asset('js/confetti.browser.min.js') }}"></script>
        <script>
            "use strict";

            function triggerInfoConfetti() {
                const colors = ['#E29A88', '#721B29', '#F7CAD0', '#D4AF37', '#ffffff'];

                if (typeof confetti === 'function') {
                    confetti({
                        particleCount: window.innerWidth > 768 ? 180 : 90,
                        spread: window.innerWidth > 768 ? 110 : 90,
                        colors: colors,
                        origin: {
                            x: 0.5,
                            y: window.innerWidth > 768 ? 0.75 : 0.72
                        }
                    });
                }
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
        if (typeof triggerInfoConfetti === 'function') {
            setTimeout(triggerInfoConfetti, 300);
        }
    };

    // Auto open popup on load
    document.addEventListener('DOMContentLoaded', function() {
        const overlay = document.getElementById('customInfoOverlay');
        if (overlay) {
            setTimeout(window.openInfoModal, 600);
        }
    });
</script>
