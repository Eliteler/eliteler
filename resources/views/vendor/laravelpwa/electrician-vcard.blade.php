{{-- ============================================================
     PWA INSTALL PROMPT MODAL
============================================================ --}}

<div id="pwaModal">

    {{-- Backdrop — clicking closes modal --}}
    <div class="pwa-backdrop" onclick="hidePwaPrompt()"></div>

    <div class="pwa-box">

        {{-- Drag Handle --}}
        <div class="pwa-handle"></div>

        {{-- Icon Badge --}}
        <div class="pwa-icon-wrap">
            <i class="fas fa-bolt"></i>
        </div>

        {{-- Title --}}
        <h3>{{ __('Add to Home Screen') }}</h3>

        {{-- Description --}}
        <p class="pwa-desc">
            {{ __('Install this app for instant access to services, bookings, and emergency dispatch — right from your home screen.') }}
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
        window.showPwaPrompt = () => {
            modal.classList.add('show');
            modal.classList.remove('hidden', 'fadeOut');
        };

        // Hide modal — slide down animation then hide
        window.hidePwaPrompt = () => {
            modal.classList.add('fadeOut');
            setTimeout(() => {
                modal.classList.remove('show', 'fadeOut');
            }, 260);
        };

        // Attach close events
        closeBtn?.addEventListener('click', window.hidePwaPrompt);
        cancelBtn?.addEventListener('click', window.hidePwaPrompt);
    });
</script>
{{-- End PWA Install Prompt Modal --}}
