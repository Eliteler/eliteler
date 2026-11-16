{{-- Start PWA Install Prompt Modal — Real Estate Design --}}
<div id="pwaModal">
    {{-- Backdrop — clicking this closes the modal --}}
    <div class="pwa-backdrop" onclick="window.hidePwaPrompt()"></div>

    <div class="pwa-box" onclick="event.stopPropagation()">

        {{-- Close Button --}}
        <button type="button" class="pwa-close" id="closePwaModal" onclick="window.hidePwaPrompt()">
            <i class="fas fa-times"></i>
        </button>

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
        let deferredPrompt = null;

        // Show modal function
        window.showPwaPrompt = function() {
            const modal = document.getElementById('pwaModal');
            if (modal) {
                // Ensure modal is attached directly to body
                if (modal.parentElement !== document.body) {
                    document.body.appendChild(modal);
                }
                modal.classList.add('show');
                modal.classList.remove('hidden', 'fadeOut');
                // Force display flex directly
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
                // Force display none directly
                modal.style.setProperty('display', 'none', 'important');
            }
        };

        // 1. Capture browser PWA prompt event
        window.addEventListener('beforeinstallprompt', function(e) {
            e.preventDefault();
            deferredPrompt = e;
            window.showPwaPrompt();
        });

        // 2. Fallback: Automatically show modal after 1.5 seconds so you can see it
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
