{{-- Start PWA Install Prompt Modal — Luxury Golf Club Pass --}}
<div id="pwaModal" style="display: none;">
    <div class="pwa-box" onclick="event.stopPropagation()">

        {{-- Icon Badge --}}
        <div class="pwa-icon-wrap">
            <i class="fas fa-golf-ball"></i>
        </div>

        {{-- Title --}}
        <h3>{{ __('Add to Home Screen') }}</h3>

        {{-- Description --}}
        <p class="pwa-desc">
            {{ __('Add this clubhouse pass to your home screen for instant access.') }}
        </p>

        {{-- Buttons --}}
        <div class="pwa-btn-row">
            <button type="button" class="pwa-btn-cancel" id="closeModal"
                onclick="window.hidePwaPrompt()">{{ __('Cancel') }}</button>
            <button type="button" class="pwa-btn-install" id="addToHomeScreenButton" onclick="window.installPwa()">
                <i class="fas fa-download"></i> {{ __('Install App') }}
            </button>
        </div>

    </div>
</div>

<script>
    "use strict";

    (function() {
        // Variable to hold the native prompt event
        let deferredPrompt = null;

        // Show modal function
        window.showPwaPrompt = function() {
            const modal = document.getElementById('pwaModal');
            if (modal) {
                // Teleport to body to avoid being trapped in card wrapper
                if (modal.parentElement !== document.body) {
                    document.body.appendChild(modal);
                }
                modal.classList.add('show');
                // Ensure display is active
                modal.style.setProperty('display', 'flex', 'important');
            }
        };

        // Hide modal function
        window.hidePwaPrompt = function() {
            const modal = document.getElementById('pwaModal');
            if (modal) {
                modal.classList.remove('show');
                modal.style.setProperty('display', 'none', 'important');
            }
        };

        // Listen for the browser's install prompt event
        window.addEventListener('beforeinstallprompt', function(e) {
            // Prevent default mini banner
            e.preventDefault();
            // Save the event to trigger later
            deferredPrompt = e;
            // Trigger and display the modal automatically
            window.showPwaPrompt();
        });

        // Trigger PWA installation on button click
        window.installPwa = async function() {
            if (!deferredPrompt) {
                return;
            }

            // Show native install dialog
            deferredPrompt.prompt();

            // Wait for user choice
            const {
                outcome
            } = await deferredPrompt.userChoice;

            // Reset event
            deferredPrompt = null;

            // Close modal
            window.hidePwaPrompt();
        };

        // Close on backdrop click
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('pwaModal');
            if (modal) {
                modal.addEventListener('click', function(e) {
                    if (e.target === this) {
                        window.hidePwaPrompt();
                    }
                });
            }
        });

        // Close modal after app is installed successfully
        window.addEventListener('appinstalled', function() {
            window.hidePwaPrompt();
            deferredPrompt = null;
        });
    })();
</script>
{{-- End PWA Install Prompt Modal --}}
