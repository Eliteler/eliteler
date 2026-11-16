{{-- ============================================================
     PWA INSTALL PROMPT MODAL
============================================================ --}}

{{-- Added style="display: none;" so it does not block the vCard content on initial load --}}
<div id="pwaModal" style="display: none;">

    {{-- Backdrop — clicking closes modal --}}
    <div class="pwa-backdrop" onclick="window.hidePwaPrompt()"></div>

    <div class="pwa-box" onclick="event.stopPropagation()">

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
        // Variable to hold the browser prompt event
        let deferredPrompt = null;

        // Show modal function
        window.showPwaPrompt = function() {
            const modal = document.getElementById('pwaModal');
            if (modal) {
                // Attach to body to prevent stacking context or layout issues
                if (modal.parentElement !== document.body) {
                    document.body.appendChild(modal);
                }
                modal.classList.add('show');
                modal.classList.remove('hidden', 'fadeOut');
                // Make modal visible explicitly
                modal.style.setProperty('display', 'flex', 'important');
            }
        };

        // Hide modal function
        window.hidePwaPrompt = function() {
            const modal = document.getElementById('pwaModal');
            if (modal) {
                modal.classList.add('fadeOut');
                setTimeout(() => {
                    modal.classList.remove('show', 'fadeOut');
                    modal.classList.add('hidden');
                    // Hide completely so vCard is fully interactable
                    modal.style.setProperty('display', 'none', 'important');
                }, 260);
            }
        };

        // Capture browser install prompt
        window.addEventListener('beforeinstallprompt', function(e) {
            e.preventDefault();
            deferredPrompt = e;
            // Only show modal when the browser is actually ready
            window.showPwaPrompt();
        });

        // Trigger native install
        window.installPwa = async function() {
            if (!deferredPrompt) {
                return;
            }

            deferredPrompt.prompt();
            const {
                outcome
            } = await deferredPrompt.userChoice;
            deferredPrompt = null;
            window.hidePwaPrompt();
        };

        // Hide automatically on successful installation
        window.addEventListener('appinstalled', function() {
            window.hidePwaPrompt();
            deferredPrompt = null;
        });
    })();
</script>
{{-- End PWA Install Prompt Modal --}}
