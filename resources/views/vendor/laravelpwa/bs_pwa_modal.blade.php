<style>
    /* Floating bottom-center card for PWA popup */
    .offcanvas-bottom#pwaModal {
        height: auto !important;
        max-width: 540px !important;
        width: calc(100% - 32px) !important;
        left: 50% !important;
        right: auto !important;
        bottom: 24px !important;
        transform: translate(-50%, 120%);
        border-radius: 24px !important;
        border: none !important;
        box-shadow: 0 15px 40px rgba(0, 0, 0, 0.25) !important;
        z-index: 1070 !important;
        transition: transform 0.35s cubic-bezier(0.25, 1, 0.5, 1) !important;
        margin: 0 !important;
    }

    /* Active state when shown */
    .offcanvas-bottom#pwaModal.show {
        transform: translate(-50%, 0) !important;
    }

    /* Rounded pill buttons */
    #pwaModal .btn {
        border-radius: 50px !important;
        padding-top: 10px !important;
        padding-bottom: 10px !important;
        font-weight: 600;
    }
</style>

<div class="offcanvas offcanvas-bottom" tabindex="-1" id="pwaModal" aria-labelledby="pwaModalLabel">
    <div class="offcanvas-header pb-0 pt-3 px-4">
        <h2 class="offcanvas-title fs-2 fw-bold" id="pwaModalLabel">{{ __('Add to Home Screen') }}</h2>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body pt-2 px-4 pb-4">
        <p class="text-muted fs-3 mb-3">
            {{ __('This website can be installed on your device. Add it to your home screen for a better experience.') }}
        </p>
        <div class="d-flex gap-2">
            {{-- Install button with dynamic theme color --}}
            <button id="addToHomeScreenButton" type="button"
                class="btn btn-{{ $theme_color ?? 'primary' }} flex-grow-1">
                {{ __('Install') }}
            </button>
            <button id="closeModal" type="button" class="btn btn-secondary px-4" data-bs-dismiss="offcanvas">
                {{ __('Cancel') }}
            </button>
        </div>
    </div>
</div>

<script>
    (function() {
        "use strict";

        let deferredInstallPrompt = null;
        const pwaModalEl = document.getElementById('pwaModal');
        const installBtn = document.getElementById('addToHomeScreenButton');
        const closeBtn = document.getElementById('closeModal');

        // Check if the app is already installed/running in standalone mode
        const isStandalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone;
        // Check if the user previously dismissed the prompt
        const isDismissed = localStorage.getItem('pwa_prompt_dismissed') === 'true';

        if (isStandalone || isDismissed) {
            return;
        }

        // Catch PWA beforeinstallprompt event
        window.addEventListener('beforeinstallprompt', (event) => {
            // Prevent Chrome's default mini-infobar
            event.preventDefault();
            deferredInstallPrompt = event;

            // Wait 3 seconds and show the custom offcanvas modal
            setTimeout(() => {
                if (pwaModalEl && window.bootstrap && window.bootstrap.Offcanvas) {
                    const pwaOffcanvas = window.bootstrap.Offcanvas.getOrCreateInstance(pwaModalEl);
                    pwaOffcanvas.show();
                }
            }, 3000);
        });

        // Trigger native install prompt on button click
        if (installBtn) {
            installBtn.addEventListener('click', async () => {
                if (!deferredInstallPrompt) {
                    return;
                }

                // Show browser installation dialog
                deferredInstallPrompt.prompt();
                const {
                    outcome
                } = await deferredInstallPrompt.userChoice;

                // Reset prompt variable after user responds
                deferredInstallPrompt = null;

                // Close the modal
                if (pwaModalEl && window.bootstrap && window.bootstrap.Offcanvas) {
                    const pwaOffcanvas = window.bootstrap.Offcanvas.getInstance(pwaModalEl);
                    if (pwaOffcanvas) {
                        pwaOffcanvas.hide();
                    }
                }
            });
        }

        // Save dismissal choice to avoid annoying the user on subsequent visits
        if (closeBtn) {
            closeBtn.addEventListener('click', () => {
                localStorage.setItem('pwa_prompt_dismissed', 'true');
            });
        }
    })();
</script>
