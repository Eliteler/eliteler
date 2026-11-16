{{-- Start PWA Install Prompt Modal — Real Estate Design --}}
<div id="pwaModal">
    {{-- Backdrop — clicking this closes the modal --}}
    <div class="pwa-backdrop" onclick="window.hidePwaPrompt()"></div>

    <div class="pwa-box" onclick="event.stopPropagation()">

        {{-- Close Button --}}
        <button type="button" class="pwa-close" id="closePwaModal" onclick="window.hidePwaPrompt()"><i
                class="fas fa-times"></i></button>

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
            {{ __('Add this digital card to your home screen for instant access.') }}
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
                // Move to body to prevent stacking context or overflow hidden issues
                if (modal.parentElement !== document.body) {
                    document.body.appendChild(modal);
                }
                modal.classList.add('show');
                modal.classList.remove('hidden', 'fadeOut');
                modal.style.setProperty('display', 'flex', 'important');
            }
        };

        // Hide modal function
        window.hidePwaPrompt = function() {
            const modal = document.getElementById('pwaModal');
            if (modal) {
                modal.classList.add('fadeOut');
                modal.classList.add('hidden');
                modal.classList.remove('show');
                modal.style.setProperty('display', 'none', 'important');
            }
        };

        // Listen for the browser's install prompt event
        window.addEventListener('beforeinstallprompt', function(e) {
            // Prevent default browser install banner
            e.preventDefault();
            // Stash event to trigger manually
            deferredPrompt = e;
            // Now trigger and display the modal
            window.showPwaPrompt();
        });

        // Trigger PWA installation
        window.installPwa = async function() {
            if (!deferredPrompt) {
                return;
            }

            // Show native browser install prompt
            deferredPrompt.prompt();

            // Wait for user choice
            const {
                outcome
            } = await deferredPrompt.userChoice;

            // Clear prompt
            deferredPrompt = null;

            // Close modal
            window.hidePwaPrompt();
        };

        // Close modal when successfully installed
        window.addEventListener('appinstalled', function() {
            window.hidePwaPrompt();
            deferredPrompt = null;
        });

        // Setup after DOM is ready
        document.addEventListener('DOMContentLoaded', function() {
            const closeBtn = document.getElementById('closePwaModal');
            const cancelBtn = document.getElementById('closeModal');
            const installBtn = document.getElementById('addToHomeScreenButton');

            if (closeBtn) closeBtn.addEventListener('click', window.hidePwaPrompt);
            if (cancelBtn) cancelBtn.addEventListener('click', window.hidePwaPrompt);
            if (installBtn) installBtn.addEventListener('click', window.installPwa);
        });
    })();
</script>
{{-- End PWA Install Prompt Modal --}}
