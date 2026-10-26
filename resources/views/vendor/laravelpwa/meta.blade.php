<!-- Add to homescreen for Chrome on Android -->
<meta name="mobile-web-app-capable" content="{{ ($config['display'] ?? '') == 'standalone' ? 'yes' : 'no' }}">

<!-- Add to homescreen for Safari on iOS -->
<meta name="apple-mobile-web-app-capable" content="{{ ($config['display'] ?? '') == 'standalone' ? 'yes' : 'no' }}">
<meta name="apple-mobile-web-app-status-bar-style" content="{{ $config['status_bar'] ?? 'default' }}">

<script type="text/javascript">
    (function() {
        "use strict";

        // 1. Initialize Service Worker safely
        if ('serviceWorker' in navigator) {
            fetch('/generate-service-worker')
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    const swUrl = data?.serviceWorkerUrl;

                    // Validate that the URL is not empty and does not end with just '/.js'
                    if (swUrl && !swUrl.endsWith('/.js')) {
                        navigator.serviceWorker.register(swUrl)
                            .then(registration => {
                                console.log('Service Worker registered with scope:', registration.scope);
                            })
                            .catch(error => {
                                console.warn('Service Worker registration failed:', error);
                            });
                    } else {
                        console.warn('Invalid Service Worker URL received:', swUrl);
                    }
                })
                .catch(error => {
                    console.warn('Could not fetch service worker configuration:', error);
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
