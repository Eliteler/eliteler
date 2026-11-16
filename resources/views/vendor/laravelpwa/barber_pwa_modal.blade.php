{{-- Barbershop PWA install prompt --}}
<div id="pwaModal">
    <div class="bb-panel w-full max-w-md rounded-2xl p-6 relative" style="box-shadow: 0 20px 60px rgba(0,0,0,.75);">
        {{-- Close Button --}}
        <button type="button"
            class="bb-icon-btn absolute w-10 h-10 rounded-full flex items-center justify-center cursor-pointer"
            style="right: 1rem; top: 1rem;" id="closePwaModal" aria-label="{{ __('Close') }}">
            <i class="fas fa-times"></i>
        </button>

        {{-- Accent Bar --}}
        <div class="w-12 h-1 rounded-full mx-auto mb-6" style="background: var(--bb-line);"></div>

        {{-- Icon --}}
        <div class="flex items-center justify-center w-16 h-16 rounded-full mx-auto mb-4"
            style="border: 1px solid var(--bb-gold); color: var(--bb-gold);">
            <i class="fas fa-cut text-2xl"></i>
        </div>

        {{-- Title & Text --}}
        <h3 class="bb-display text-3xl bb-gold-text text-center">{{ __('Add to Home Screen') }}</h3>
        <p class="bb-text-muted text-center mt-3 mb-6 text-sm">
            {{ __('Add this digital card to your home screen for instant access.') }}
        </p>

        {{-- Action Buttons --}}
        <div class="grid grid-cols-2 gap-4">
            <button type="button" class="bb-btn-outline py-3 rounded-lg text-sm font-semibold cursor-pointer"
                id="closeModal">{{ __('Cancel') }}</button>
            <button type="button"
                class="bb-btn-primary py-3 rounded-lg text-sm font-semibold flex items-center justify-center cursor-pointer"
                id="addToHomeScreenButton">
                <i class="fas fa-download mr-2"></i>{{ __('Install App') }}
            </button>
        </div>
    </div>
</div>

<script>
    "use strict";

    (function() {
        // Variable to hold the native install prompt event
        let deferredPrompt = null;

        // Listen for the native install prompt event
        window.addEventListener('beforeinstallprompt', function(e) {
            e.preventDefault();
            deferredPrompt = e;
            window.showPwaPrompt();
        });

        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('pwaModal');
            const installBtn = document.getElementById('addToHomeScreenButton');
            const closeBtn = document.getElementById('closePwaModal');
            const cancelBtn = document.getElementById('closeModal');

            // Reliable hide function using direct style manipulation
            const hide = function() {
                if (modal) {
                    modal.classList.add('hidden');
                    // Force display none directly to override any external ID styling
                    modal.style.setProperty('display', 'none', 'important');
                }
            };

            // Global show function
            window.showPwaPrompt = function() {
                if (modal) {
                    modal.classList.remove('hidden');
                    // Remove inline display none if previously set
                    modal.style.removeProperty('display');
                }
            };

            window.hidePwaPrompt = hide;

            // Close button click listeners
            if (closeBtn) closeBtn.addEventListener('click', hide);
            if (cancelBtn) cancelBtn.addEventListener('click', hide);

            // Close when clicking modal backdrop
            if (modal) {
                modal.addEventListener('click', function(e) {
                    if (e.target === modal) hide();
                });
            }

            // Install button logic
            if (installBtn) {
                installBtn.addEventListener('click', async function() {
                    if (!deferredPrompt) {
                        return;
                    }

                    // Trigger native browser install dialog
                    deferredPrompt.prompt();

                    // Wait for the user action
                    const {
                        outcome
                    } = await deferredPrompt.userChoice;

                    // Clear event after execution
                    deferredPrompt = null;

                    // Close the modal
                    hide();
                });
            }

            // Hide automatically when app is installed
            window.addEventListener('appinstalled', function() {
                hide();
                deferredPrompt = null;
            });
        });
    })();
</script>
