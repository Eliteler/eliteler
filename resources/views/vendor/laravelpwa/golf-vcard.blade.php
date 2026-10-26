{{-- Start PWA Install Prompt Modal — Luxury Golf Club Pass --}}
<div id="pwaModal">
    <div class="pwa-box" onclick="event.stopPropagation()">

        {{-- Icon Badge --}}
        <div class="pwa-icon-wrap">
            <i class="fas fa-golf-ball"></i>
        </div>
x
        {{-- Title --}}
        <h3>{{ __('Add to Home Screen') }}</h3>

        {{-- Description --}}
        <p class="pwa-desc">
            {{ __('Add this clubhouse pass to your home screen for instant access.') }}
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

    // Close on Cancel
    document.getElementById('closeModal').addEventListener('click', hidePwaPrompt);

    // Close on backdrop click
    document.getElementById('pwaModal').addEventListener('click', function(e) {
        if (e.target === this) hidePwaPrompt();
    });
</script>
{{-- End PWA Install Prompt Modal --}}
