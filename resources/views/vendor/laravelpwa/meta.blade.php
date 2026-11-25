<!-- Add to homescreen for Chrome on Android -->
<meta name="mobile-web-app-capable" content="{{ ($config['display'] ?? '') == 'standalone' ? 'yes' : 'no' }}">

<!-- Add to homescreen for Safari on iOS -->
<meta name="apple-mobile-web-app-capable" content="{{ ($config['display'] ?? '') == 'standalone' ? 'yes' : 'no' }}">
<meta name="apple-mobile-web-app-status-bar-style" content="{{ $config['status_bar'] ?? 'default' }}">

<script type="text/javascript">
    (function() {
        "use strict";

        // 1. Initialize Service Worker safely with full root scope
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('/serviceworker.js', {
                    scope: '/'
                }).then(function(registration) {
                    console.log('PWA: Service Worker registered with scope:', registration.scope);
                }).catch(function(error) {
                    console.warn('PWA: Service Worker registration failed:', error);
                });
            });
        }

        // 2. Handle PWA Installation using Bootstrap Offcanvas
        let deferredPrompt = null;
        const pwaModalEl = document.getElementById('pwaModal');
        const installBtn = document.getElementById('addToHomeScreenButton');

        // Check if already in standalone mode
        const isStandalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone;
        if (isStandalone) {
            return;
        }

        window.addEventListener('beforeinstallprompt', function(event) {
            event.preventDefault();
            deferredPrompt = event;
            window.deferredPwaPrompt = event;
            window.deferredPrompt = event;

            // Trigger Bootstrap Offcanvas smoothly
            if (pwaModalEl && window.bootstrap && window.bootstrap.Offcanvas) {
                const modalInstance = window.bootstrap.Offcanvas.getOrCreateInstance(pwaModalEl);
                modalInstance.show();
            }
        });

        // Handle install button click
        if (installBtn) {
            installBtn.addEventListener('click', async function() {
                if (!deferredPrompt) {
                    return;
                }

                deferredPrompt.prompt();
                await deferredPrompt.userChoice;
                deferredPrompt = null;

                // Hide offcanvas modal
                if (pwaModalEl && window.bootstrap && window.bootstrap.Offcanvas) {
                    const modalInstance = window.bootstrap.Offcanvas.getInstance(pwaModalEl);
                    if (modalInstance) {
                        modalInstance.hide();
                    }
                }
            });
        }
    })();
</script>
