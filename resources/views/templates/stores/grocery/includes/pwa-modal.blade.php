{{-- Luxury Bottom-Sheet PWA Install Modal with Complete Auto-Trigger Engine --}}
@php
    $isPwaActive =
        ($plan_details != null &&
            ($plan_details['pwa'] ?? 0) == 1 &&
            ($business_card_details->is_enable_pwa ?? 0) == 1) ||
        (!empty($business_card_details->is_enable_pwa) && $business_card_details->is_enable_pwa == 1);
@endphp

@if ($isPwaActive)
    <div class="modal fade store-pwa-modal" id="pwaInstallModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="false">
        <div class="modal-dialog modal-dialog-centered modal-sm pwa-modal-dialog">
            <div class="modal-content pwa-modal-content">
                <div class="pwa-modal-body">
                    <div class="pwa-icon-wrapper">
                        <img src="{{ !empty($business_card_details->profile) ? url($business_card_details->profile) : '' }}"
                            alt="{{ $card_details->title }}" class="pwa-app-icon"
                            onerror="this.onerror=null;this.src='data:image/svg+xml;charset=UTF-8,%3Csvg%20width%3D%2280%22%20height%3D%2280%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%3E%3Crect%20width%3D%22100%25%22%20height%3D%22100%25%22%20fill%3D%22%232e7d32%22%2F%3E%3Ctext%20x%3D%2250%25%22%20y%3D%2250%25%22%20fill%3D%22%23ffffff%22%20font-family%3D%22sans-serif%22%20font-size%3D%2224%22%20text-anchor%3D%22middle%22%20dy%3D%22.3em%22%3EApp%3C%2Ftext%3E%3C%2Fsvg%3E';">
                    </div>
                    <h3 class="pwa-app-title">{{ __('Install') }} {{ $card_details->title }}</h3>
                    <p class="pwa-app-desc">
                        {{ __('Install our progressive web app for quick, offline access and a faster grocery shopping experience.') }}
                    </p>

                    <div class="pwa-btn-actions">
                        <button type="button" class="btn pwa-btn-install" id="pwaInstallAppBtn">
                            <i class="ti ti-download me-1"></i> {{ __('Install App') }}
                        </button>
                        <button type="button" class="btn pwa-btn-dismiss" id="pwaDismissBtn" data-bs-dismiss="modal">
                            {{ __('Maybe Later') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* Dark Blur Modal Backdrop Engine */
        .store-pwa-modal {
            background-color: rgba(15, 23, 42, 0.82) !important;
            backdrop-filter: blur(6px) !important;
            -webkit-backdrop-filter: blur(6px) !important;
            z-index: 1070 !important;
        }

        /* Scoped PWA Modal Content */
        .pwa-modal-content {
            border-radius: 28px !important;
            border: 1px solid #c8e6c9 !important;
            overflow: hidden;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.4) !important;
            background: #ffffff;
        }

        .pwa-modal-body {
            padding: 28px 24px;
            text-align: center;
        }

        .pwa-icon-wrapper {
            width: 72px;
            height: 72px;
            margin: 0 auto 16px auto;
            border-radius: 20px !important;
            overflow: hidden;
            box-shadow: 0 6px 16px rgba(46, 125, 50, 0.2);
            border: 2px solid #c8e6c9;
        }

        .pwa-app-icon {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .pwa-app-title {
            font-size: 19px;
            font-weight: 800;
            color: #1b5e20;
            margin-bottom: 8px;
        }

        .pwa-app-desc {
            font-size: 13px;
            color: #616161;
            margin-bottom: 22px;
            line-height: 1.5;
        }

        .pwa-btn-actions {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .pwa-btn-install {
            width: 100%;
            height: 44px;
            border-radius: 50px !important;
            background-color: #2e7d32 !important;
            color: #ffffff !important;
            border: none !important;
            font-weight: 700;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 12px rgba(46, 125, 50, 0.25);
            transition: all 0.2s ease;
        }

        .pwa-btn-install:hover {
            background-color: #1b5e20 !important;
            transform: translateY(-2px);
        }

        .pwa-btn-dismiss {
            width: 100%;
            height: 40px;
            border-radius: 50px !important;
            background-color: #f5f5f5 !important;
            color: #616161 !important;
            border: none !important;
            font-weight: 600;
            font-size: 13px;
        }

        .pwa-btn-dismiss:hover {
            background-color: #eeeeee !important;
            color: #212121 !important;
        }
    </style>

    <script>
        // Complete Standalone PWA Engine
        document.addEventListener('DOMContentLoaded', function() {
            let deferredPrompt = null;
            const pwaModalEl = document.getElementById('pwaInstallModal');
            let pwaModalInstance = null;

            if (pwaModalEl) {
                pwaModalInstance = new bootstrap.Modal(pwaModalEl, {
                    backdrop: false,
                    keyboard: true
                });
            }

            // Function to trigger the PWA modal if not dismissed in this session
            function showPwaPrompt() {
                const isDismissed = sessionStorage.getItem(
                    'pwa_modal_dismissed_{{ $business_card_details->card_id ?? '' }}');
                const isStandalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator
                    .standalone;

                // Only show if not already installed and not dismissed in current session
                if (!isDismissed && !isStandalone && pwaModalInstance) {
                    pwaModalInstance.show();
                }
            }

            // Capture native browser beforeinstallprompt event (Chrome, Android, Edge)
            window.addEventListener('beforeinstallprompt', function(e) {
                e.preventDefault();
                deferredPrompt = e;
                // Show modal slightly after page ready
                setTimeout(showPwaPrompt, 3000);
            });

            // Fallback timer: Show modal after 4.5 seconds on desktop/mobile testing
            setTimeout(function() {
                showPwaPrompt();
            }, 4500);

            // Handle Install Button Click
            const installBtn = document.getElementById('pwaInstallAppBtn');
            if (installBtn) {
                installBtn.addEventListener('click', async function() {
                    if (deferredPrompt) {
                        deferredPrompt.prompt();
                        const {
                            outcome
                        } = await deferredPrompt.userChoice;
                        deferredPrompt = null;
                    }
                    if (pwaModalInstance) {
                        pwaModalInstance.hide();
                    }
                    sessionStorage.setItem(
                        'pwa_modal_dismissed_{{ $business_card_details->card_id ?? '' }}', 'true');
                });
            }

            // Handle Dismiss Button Click
            const dismissBtn = document.getElementById('pwaDismissBtn');
            if (dismissBtn) {
                dismissBtn.addEventListener('click', function() {
                    sessionStorage.setItem(
                        'pwa_modal_dismissed_{{ $business_card_details->card_id ?? '' }}', 'true');
                });
            }
        });
    </script>
@endif
