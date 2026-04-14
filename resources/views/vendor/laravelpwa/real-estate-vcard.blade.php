{{-- Start PWA Install Prompt Modal — Real Estate Design --}}
<div id="pwaModal">
    {{-- Backdrop — clicking this closes the modal --}}
    <div class="pwa-backdrop" onclick="hidePwaPrompt()"></div>

    <div class="pwa-box">

        {{-- Close Button --}}
        <button class="pwa-close" id="closePwaModal"><i class="fas fa-times"></i></button>

        {{-- Drag Handle --}}
        <div class="pwa-handle"></div>

        {{-- Icon --}}
        <div class="pwa-icon-wrap">
            <i class="fas fa-home"></i>
        </div>

        {{-- Title --}}
        <h3>{{ __('Add to Home Screen') }}</h3>

        {{-- Description --}}
        <p class="pwa-desc">
            {{ __('Install this app on your device for instant access to property listings, tours, and more.') }}
        </p>

        {{-- Buttons --}}
        <div class="pwa-btn-row">
            <button class="pwa-btn-cancel" id="closeModal">{{ __('Cancel') }}</button>
            <button class="pwa-btn-install" id="addToHomeScreenButton">
                <i class="fas fa-download"></i> {{ __('Install App') }}
            </button>
        </div>
    </div>
</div>

<script>
    "use strict";

    document.addEventListener('DOMContentLoaded', () => {
        const modal = document.getElementById('pwaModal');
        const closeBtn = document.getElementById('closePwaModal');
        const cancelBtn = document.getElementById('closeModal');

        if (!modal) return;

        // Show modal
        const showPwaPrompt = () => {
            modal.classList.add('show');
            modal.classList.remove('hidden', 'fadeOut');
        };

        // Hide modal
        const hidePwaPrompt = () => {
            modal.classList.add('fadeOut');

            // Wait for animation before hiding
            modal.classList.add('hidden');
            modal.classList.remove('show');
        };

        // Attach events safely
        closeBtn?.addEventListener('click', hidePwaPrompt);
        cancelBtn?.addEventListener('click', hidePwaPrompt);

        // Optional: expose globally if needed
        window.showPwaPrompt = showPwaPrompt;
    });
</script>
{{-- End PWA Install Prompt Modal --}}
