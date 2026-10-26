@if ($plan_details != null && $plan_details['pwa'] == 1 && $business_card_details->is_enable_pwa == 1)
    <style>
        /* Scoped True Bottom-Docked PWA Sheet */
        .pwa-modal-backdrop {
            background-color: rgba(15, 23, 42, 0.75) !important;
            backdrop-filter: blur(8px) !important;
            -webkit-backdrop-filter: blur(8px) !important;
        }

        /* Force Modal to Viewport Bottom */
        .pwa-bottom-docked-dialog {
            position: fixed !important;
            bottom: 0 !important;
            left: 0 !important;
            right: 0 !important;
            margin: 0 auto !important;
            max-width: 520px;
            width: 100%;
            transform: translateY(100%);
            transition: transform 0.35s cubic-bezier(0.16, 1, 0.3, 1) !important;
            pointer-events: none;
        }

        .modal.show .pwa-bottom-docked-dialog {
            transform: translateY(0) !important;
            pointer-events: auto;
        }

        .pwa-bottom-sheet-card {
            border-radius: 28px 28px 0 0 !important;
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-bottom: none;
            box-shadow: 0 -15px 40px rgba(0, 0, 0, 0.25);
            background: #ffffff;
            padding: 14px 22px 24px;
            overflow: hidden;
        }

        @media (min-width: 768px) {
            .pwa-bottom-sheet-card {
                border-radius: 28px !important;
                margin-bottom: 20px;
                border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            }
        }

        /* Top Drag Handle Indicator */
        .pwa-sheet-handle {
            width: 44px;
            height: 4.5px;
            border-radius: 50px !important;
            background: #e2e8f0;
            margin: 0 auto 16px;
        }

        .pwa-app-avatar {
            width: 58px;
            height: 58px;
            border-radius: 16px;
            object-fit: cover;
            background: #fef2f2;
            border: 1.5px solid #f1f5f9;
            box-shadow: 0 6px 16px rgba(220, 38, 38, 0.12);
            flex-shrink: 0;
        }

        .pwa-btn-dismiss {
            border-radius: 50px !important;
            background: #f8fafc;
            color: #64748b !important;
            border: 1px solid #e2e8f0;
            font-size: 0.9rem;
            font-weight: 700;
            padding: 11px 18px;
            transition: all 0.2s ease;
        }

        .pwa-btn-dismiss:hover {
            background: #e2e8f0;
            color: #1e293b !important;
        }

        .pwa-btn-install {
            border-radius: 50px !important;
            background: #dc2626;
            color: #ffffff !important;
            border: none;
            font-size: 0.9rem;
            font-weight: 700;
            padding: 11px 20px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            box-shadow: 0 6px 18px rgba(220, 38, 38, 0.3);
            transition: all 0.3s ease;
        }

        .pwa-btn-install:hover {
            background: #b91c1c;
            box-shadow: 0 8px 25px rgba(220, 38, 38, 0.45);
            transform: translateY(-2px);
        }
    </style>

    <div class="modal fade pwa-modal-backdrop" id="pwaModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog pwa-bottom-docked-dialog">
            <div class="modal-content pwa-bottom-sheet-card">
                {{-- Drag Handle --}}
                <div class="pwa-sheet-handle"></div>

                {{-- App Details --}}
                <div class="d-flex align-items-center gap-3 mb-3">
                    <img src="{{ url($business_card_details->profile) }}" alt="{{ $card_details->title }}"
                        class="pwa-app-avatar"
                        onerror="this.onerror=null;this.src='data:image/svg+xml;charset=UTF-8,%3Csvg%20width%3D%2260%22%20height%3D%2260%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%3E%3Crect%20width%3D%22100%25%22%20height%3D%22100%25%22%20fill%3D%22%23fef2f2%22%20rx%3D%2216%22%2F%3E%3Ctext%20x%3D%2250%25%22%20y%3D%2250%25%22%20fill%3D%22%23dc2626%22%20font-family%3D%22sans-serif%22%20font-size%3D%2214%22%20font-weight%3D%22bold%22%20text-anchor%3D%22middle%22%20dy%3D%22.35em%22%3EApp%3C%2Ftext%3E%3C%2Fsvg%3E';">
                    <div>
                        <h4 class="fs-2 fw-bold text-dark mb-1">{{ $card_details->title }}</h4>
                        <p class="text-muted fs-4 mb-0">{{ __('Install our app for quick access & instant shopping.') }}
                        </p>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="d-flex align-items-center gap-2 mt-2">
                    <button type="button" class="btn pwa-btn-dismiss w-50"
                        data-bs-dismiss="modal">{{ __('Maybe Later') }}</button>
                    <button type="button" id="pwaInstallBtn" class="btn pwa-btn-install w-50">
                        <i class="ti ti-download"></i>
                        <span>{{ __('Install App') }}</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Native PWA Install Prompt Engine
        (function() {
            "use strict";

            let deferredPrompt;
            const pwaModalEl = document.getElementById('pwaModal');
            const installBtn = document.getElementById('pwaInstallBtn');

            window.addEventListener('beforeinstallprompt', (e) => {
                e.preventDefault();
                deferredPrompt = e;

                // Show bottom sheet after 3 seconds on load
                setTimeout(() => {
                    if (pwaModalEl && typeof bootstrap !== 'undefined') {
                        const modal = new bootstrap.Modal(pwaModalEl);
                        modal.show();
                    }
                }, 3000);
            });

            if (installBtn) {
                installBtn.addEventListener('click', async () => {
                    if (deferredPrompt) {
                        deferredPrompt.prompt();
                        const {
                            outcome
                        } = await deferredPrompt.userChoice;
                        deferredPrompt = null;
                        if (outcome === 'accepted') {
                            const modal = bootstrap.Modal.getInstance(pwaModalEl);
                            if (modal) modal.hide();
                        }
                    } else {
                        if (window.showStoreToast) {
                            window.showStoreToast("{{ __('Tap share and choose Add to Home Screen') }}",
                                'success');
                        }
                        const modal = bootstrap.Modal.getInstance(pwaModalEl);
                        if (modal) modal.hide();
                    }
                });
            }
        })();
    </script>
@endif
