{{-- ============================================================
     PWA INSTALL PROMPT MODAL
============================================================ --}}

<div id="pwaModal">
    <div class="pwa-box" onclick="event.stopPropagation()">

        {{-- Icon Badge --}}
        <div class="pwa-icon-wrap">
            <i class="fas fa-wrench"></i>
        </div>

        {{-- Title --}}
        <h3>{{ __('Add to Home Screen') }}</h3>

        {{-- Description --}}
        <p class="pwa-desc">
            {{ __('Install this app for instant access to services, bookings, and more, right from your home screen.') }}
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

    // Show modal
    function showPwaPrompt() {
        document.getElementById('pwaModal').classList.add('show');
    }

    // Hide modal
    function hidePwaPrompt() {
        document.getElementById('pwaModal').classList.remove('show');
    }

    // Close on X and Cancel
    document.getElementById('closePwaModal').addEventListener('click', hidePwaPrompt);
    document.getElementById('closeModal').addEventListener('click', hidePwaPrompt);

    // Close on backdrop click
    document.getElementById('pwaModal').addEventListener('click', function(e) {
        if (e.target === this) hidePwaPrompt();
    });
</script>
{{-- End PWA Install Prompt Modal --}}
