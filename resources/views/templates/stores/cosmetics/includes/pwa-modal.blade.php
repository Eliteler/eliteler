{{-- PWA Installation Modal Component --}}
@if (
    $storefront->onHome() &&
        !empty($plan_details['pwa']) &&
        $plan_details['pwa'] == 1 &&
        !empty($business_card_details->is_enable_pwa) &&
        $business_card_details->is_enable_pwa == 1)
    <style>
        /* Modern Bottom Floating PWA Sheet */
        #pwaInstallModal {
            position: fixed !important;
            inset: 0 !important;
            width: 100vw !important;
            height: 100vh !important;
            z-index: 999999 !important;
            display: flex !important;
            flex-direction: column !important;
            justify-content: flex-end !important;
            /* Pulls card strictly to the bottom */
            align-items: center !important;
            background-color: rgba(0, 0, 0, 0.5) !important;
            backdrop-filter: blur(4px) !important;
            -webkit-backdrop-filter: blur(4px) !important;
            padding: 12px !important;
            padding-bottom: 20px !important;
            /* Sits precisely above the mobile bottom bar */

            opacity: 0;
            visibility: hidden;
            pointer-events: none;
            transition: opacity 0.35s ease, visibility 0.35s ease;
        }

        #pwaInstallModal.show-pwa {
            opacity: 1 !important;
            visibility: visible !important;
            pointer-events: auto !important;
        }

        /* Compact Rounded Card Positioned at Bottom */
        #pwaInstallModal .pwa-card {
            margin: 0 !important;
            /* Removes auto margin that centered the card */
            margin-bottom: 0 !important;
            max-width: 390px !important;
            width: 100% !important;
            border-radius: 20px !important;
            background-color: #ffffff !important;
            border: 1px solid #f1f5f9 !important;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.25) !important;
            transform: translateY(25px) scale(0.96);
            transition: transform 0.35s cubic-bezier(0.16, 1, 0.3, 1);
        }

        #pwaInstallModal.show-pwa .pwa-card {
            transform: translateY(0) scale(1);
        }

        /* Desktop Adjustment */
        @media (min-width: 992px) {
            #pwaInstallModal {
                padding-bottom: 25px !important;
            }
        }
    </style>

    <!-- PWA Install Modal Container -->
    <div id="pwaInstallModal" onclick="closePwaModal(event)">
        <div class="card pwa-card p-4 text-center border-0 shadow-lg" onclick="event.stopPropagation()">

            <!-- App Store Profile Icon -->
            <div class="mb-3 position-relative d-inline-block mx-auto">
                <img src="{{ url($business_card_details->profile) }}" alt="{{ $card_details->title }}"
                    class="rounded-circle shadow-sm"
                    style="width: 64px; height: 64px; object-fit: cover; border: 3px solid #fbcfe8;"
                    onerror="this.onerror=null; this.src='{{ asset('img/templates/all-categories.png') }}';">
            </div>

            <!-- Title & Information -->
            <div class="mb-3">
                <h3 class="fs-2 fw-bold text-dark mb-1">
                    {{ __('Install') }} {{ $card_details->title }} {{ __('App') }}
                </h3>
                <p class="text-muted fs-4 mb-0 px-2 leading-relaxed">
                    {{ __('Install our store to your home screen for faster shopping, offline access, and an app-like experience.') }}
                </p>
            </div>

            <!-- Action Buttons -->
            <div class="d-grid gap-2 pt-1">
                <button type="button" id="btnPwaInstall"
                    class="btn btn-rose rounded-cosmetics py-2 fs-3 fw-bold d-flex align-items-center justify-content-center gap-2 shadow-sm">
                    <i class="ti ti-download fs-2"></i>
                    <span>{{ __('Install App') }}</span>
                </button>

                <button type="button" onclick="closePwaModal(null, true)"
                    class="btn btn-link text-muted text-decoration-none fs-4 py-1">
                    {{ __('Cancel') }}
                </button>
            </div>

        </div>
    </div>

    <!-- PWA Installation Script (Shows on page load without timer lock) -->
    <script>
        (function() {
            "use strict";

            let pwaPromptEvent = null;

            // Intercept beforeinstallprompt event
            window.addEventListener('beforeinstallprompt', function(e) {
                e.preventDefault();
                pwaPromptEvent = e;
                displayPwaModal();
            });

            // Display PWA Modal function
            function displayPwaModal() {
                setTimeout(function() {
                    const modal = document.getElementById('pwaInstallModal');
                    if (modal) {
                        modal.classList.add('show-pwa');
                    }
                }, 1500); // Displays smoothly 1.5 seconds after load
            }

            // Close Modal function
            window.closePwaModal = function(event, forceClose) {
                if (forceClose || (event && event.target && event.target.id === 'pwaInstallModal')) {
                    const modal = document.getElementById('pwaInstallModal');
                    if (modal) {
                        modal.classList.remove('show-pwa');
                    }
                }
            };

            // Install Button Click Listener
            document.addEventListener('DOMContentLoaded', function() {
                const installBtn = document.getElementById('btnPwaInstall');
                if (installBtn) {
                    installBtn.addEventListener('click', async function() {
                        if (pwaPromptEvent) {
                            pwaPromptEvent.prompt();
                            const {
                                outcome
                            } = await pwaPromptEvent.userChoice;
                            pwaPromptEvent = null;
                            closePwaModal(null, true);
                        } else {
                            // Safari / iOS Fallback instruction
                            alert(
                                "{{ __('To install, tap the Share or Menu icon in your browser and select Add to Home Screen.') }}");
                            closePwaModal(null, true);
                        }
                    });
                }

                // Initial trigger on load
                displayPwaModal();
            });

            // Auto close upon installation
            window.addEventListener('appinstalled', function() {
                const modal = document.getElementById('pwaInstallModal');
                if (modal) {
                    modal.classList.remove('show-pwa');
                }
            });
        })();
    </script>
@endif
