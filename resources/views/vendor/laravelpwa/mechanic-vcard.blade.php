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
        let deferredPrompt = null;

        // Function to show modal
        window.showPwaPrompt = function() {
            const modal = document.getElementById('pwaModal');
            if (modal) {
                // Teleport to root body to prevent overflow/clipping issues
                if (modal.parentElement !== document.body) {
                    document.body.appendChild(modal);
                }
                modal.classList.add('show');
                // Force visible display
                modal.style.setProperty('display', 'flex', 'important');
            }
        };

        // Function to hide modal
        window.hidePwaPrompt = function() {
            const modal = document.getElementById('pwaModal');
            if (modal) {
                modal.classList.remove('show');
                // Force hide display
                modal.style.setProperty('display', 'none', 'important');
            }
        };

        // Close on backdrop click
        window.handlePwaBackdrop = function(e) {
            if (e.target.id === 'pwaModal') {
                window.hidePwaPrompt();
            }
        };

        // 1. Capture browser PWA prompt event
        window.addEventListener('beforeinstallprompt', function(e) {
            e.preventDefault();
            deferredPrompt = e;
            window.showPwaPrompt();
        });

        // 2. Automatically show modal after 1.5 seconds so you can see it
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                window.showPwaPrompt();
            }, 1500);
        });

        // 3. Trigger native install dialog
        window.installPwa = async function() {
            if (!deferredPrompt) {
                alert("PWA is already installed or not supported in this browser.");
                return;
            }

            deferredPrompt.prompt();
            const {
                outcome
            } = await deferredPrompt.userChoice;
            deferredPrompt = null;
            window.hidePwaPrompt();
        };

        // Hide after installation
        window.addEventListener('appinstalled', function() {
            window.hidePwaPrompt();
            deferredPrompt = null;
        });
    })();
</script>
{{-- End PWA Install Prompt Modal --}}
