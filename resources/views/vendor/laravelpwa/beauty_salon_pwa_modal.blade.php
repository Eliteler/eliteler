{{-- Botanical Parlour — PWA Install Prompt Modal --}}
<div id="pwaModal" class="hidden">
    <div class="sl-panel w-full max-w-md rounded-xl p-6 mx-4 relative text-center space-y-4 shadow-2xl">

        {{-- Close Button --}}
        <button type="button" class="sl-modal-close" id="closePwaModal" aria-label="{{ __('Close') }}">
            <i class="fas fa-times"></i>
        </button>

        {{-- Mobile Handle Indicator --}}
        <div class="w-10 h-1 rounded bg-[#5F6F52]/30 mx-auto mb-1"></div>

        {{-- Botanical Profile Logo --}}
        <div class="w-14 h-14 rounded-lg mx-auto overflow-hidden mb-2 border-2 border-[#5F6F52] shadow-sm">
            <img src="{{ url($business_card_details->profile) }}" alt="Logo" class="w-full h-full object-cover" />
        </div>

        {{-- Text --}}
        <div>
            <p class="text-xs font-bold uppercase tracking-widest text-[#5F6F52] mb-1">{{ __('App Experience') }}</p>
            <h3 class="sl-display text-2xl sm:text-3xl font-bold text-[#2E251A]">{{ __('Add to Home Screen') }}</h3>
            <p class="text-xs text-[#736756] mt-2 leading-relaxed">
                {{ __('Add this digital card to your home screen for instant access."') }}
            </p>
        </div>

        {{-- Actions --}}
        <div class="grid grid-cols-2 gap-3 pt-2">
            <button type="button" class="sl-btn-outline py-3 rounded text-xs font-bold uppercase tracking-wider"
                id="closePwaBtn">
                {{ __('Cancel') }}
            </button>
            <button type="button"
                class="sl-btn-primary py-3 rounded text-xs font-bold uppercase tracking-wider flex items-center justify-center mt-0"
                id="addToHomeScreenButton">
                <i class="fas fa-download mr-1.5"></i> {{ __('Install App') }}
            </button>
        </div>

    </div>
</div>

<script>
    "use strict";
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('pwaModal');
        const hide = function() {
            if (modal) modal.classList.add('hidden');
        };

        window.showPwaPrompt = function() {
            if (modal) modal.classList.remove('hidden');
        };
        window.hidePwaPrompt = hide;

        document.getElementById('closePwaModal')?.addEventListener('click', hide);
        document.getElementById('closePwaBtn')?.addEventListener('click', hide);

        modal?.addEventListener('click', function(e) {
            if (e.target === modal) hide();
        });
    });
</script>
