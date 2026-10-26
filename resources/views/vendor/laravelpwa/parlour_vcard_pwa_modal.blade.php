{{-- Women's Salon — PWA Install Prompt Modal --}}
<div id="pwaModal" class="hidden">
    <div class="sl-panel w-full max-w-md rounded-3xl p-6 mx-4 relative text-center space-y-4 shadow-2xl">

        {{-- Close Button --}}
        <button type="button" class="sl-modal-close" id="closePwaModal" aria-label="{{ __('Close') }}">
            <i class="fas fa-times"></i>
        </button>

        {{-- Mobile Handle Indicator --}}
        <div class="w-10 h-1 rounded-full bg-[#E29A88]/30 mx-auto mb-1"></div> 
 
        {{-- Salon Icon --}}
        <div
            class="w-14 h-14 rounded-full mx-auto flex items-center justify-center text-xl text-[#721B29] mb-2 bg-[#FDF7F4] border-2 border-[#E29A88]">
            <i class="fas fa-cut"></i>
        </div>

        {{-- Text --}}
        <div>
            <p class="text-xs font-bold uppercase tracking-widest text-[#E29A88] mb-1">{{ __('App Experience') }}</p>
            <h3 class="sl-display text-2xl sm:text-3xl font-bold text-[#721B29]">{{ __('Add to Home Screen') }}</h3>
            <p class="text-xs text-[#735D61] mt-2 leading-relaxed">
                {{ __('Add this digital card to your home screen for instant access."') }}
            </p>
        </div>

        {{-- Actions --}}
        <div class="grid grid-cols-2 gap-3 pt-2">
            <button type="button" class="sl-btn-outline py-3 rounded-xl text-xs font-bold uppercase tracking-wider"
                id="closePwaBtn">
                {{ __('Cancel') }}
            </button>
            <button type="button"
                class="sl-btn-primary py-3 rounded-xl text-xs font-bold uppercase tracking-wider flex items-center justify-center mt-0"
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
