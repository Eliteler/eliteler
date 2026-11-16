{{-- Start PWA Install Prompt Modal --}}
<div id="pwaModal" onclick="handleBackdropClick(event)">
    <div class="pwa-box" onclick="event.stopPropagation()">

        {{-- App Icon Badge --}}
        <div class="pwa-icon-wrap">
            <i class="fas fa-mobile-alt"></i>
        </div>

        {{-- Title --}}
        <h3>{{ __('Install App') }}</h3>

        {{-- Description --}}
        <p class="pwa-desc">
            {{ __('Install this app on your home screen for quick and easy access anytime.') }}
        </p>

        {{-- Action Buttons --}}
        <div class="pwa-btn-row">
            {{-- Added inline onclick for guaranteed triggering --}}
            <button type="button" class="pwa-btn-cancel" id="closeModal"
                onclick="hidePwaPrompt()">{{ __('Cancel') }}</button>
            <button type="button" class="pwa-btn-install" id="addToHomeScreenButton">
                <i class="fas fa-download"></i> {{ __('Install App') }}
            </button>
        </div>

    </div>
</div>

<script>
    "use strict";

    let deferredPrompt = null;

    // Show modal function
    function showPwaPrompt() {
        const modal = document.getElementById('pwaModal');
        if (modal) {
            modal.classList.add('show');
            // Force display to flex in case CSS is overridden
            modal.style.setProperty('display', 'flex', 'important');
        }
    }

    // Hide modal function
    function hidePwaPrompt() {
        const modal = document.getElementById('pwaModal');
        if (modal) {
            modal.classList.remove('show');
            // Force modal to close directly
            modal.style.setProperty('display', 'none', 'important');
        }
    }

    // Handle background overlay click to close
    function handleBackdropClick(event) {
        if (event.target.id === 'pwaModal') {
            hidePwaPrompt();
        }
    }

    // Capture the PWA install prompt
    window.addEventListener('beforeinstallprompt', function(e) {
        // Prevent default browser install banner
        e.preventDefault();
        // Store event for triggering later
        deferredPrompt = e;
        // Show the prompt modal
        showPwaPrompt();
    });

    // Handle Install button click
    document.addEventListener('DOMContentLoaded', function() {
        const installBtn = document.getElementById('addToHomeScreenButton');

        if (installBtn) {
            installBtn.addEventListener('click', async function() {
                if (!deferredPrompt) {
                    return;
                }

                // Trigger browser prompt
                deferredPrompt.prompt();

                // Wait for user confirmation
                const {
                    outcome
                } = await deferredPrompt.userChoice;

                // Reset prompt
                deferredPrompt = null;

                // Close modal
                hidePwaPrompt();
            });
        }

        // Close modal after app is installed successfully
        window.addEventListener('appinstalled', function() {
            hidePwaPrompt();
            deferredPrompt = null;
        });
    });
</script>
{{-- End PWA Install Prompt Modal --}}
