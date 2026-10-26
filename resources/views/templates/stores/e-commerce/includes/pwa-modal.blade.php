@php
    $isStoreIndex = $storefront->onHome();
@endphp

{{-- Guaranteed Deep Dark Backdrop Bottom-Sheet PWA Modal --}}
@if ($isStoreIndex && $plan_details != null && $plan_details['pwa'] == 1 && $business_card_details->is_enable_pwa == 1)
    <div class="modal fade pwa-bottom-modal-container" id="pwaModal" tabindex="-1" aria-hidden="true"
        data-bs-backdrop="false" onclick="if(event.target === this) dismissPwaModal();">
        <div class="modal-dialog pwa-bottom-modal-dialog" role="document">
            <div class="modal-content pwa-bottom-modal-card border-0 shadow-lg">

                <!-- Top Pull Indicator Bar -->
                <div class="pwa-drag-handle"></div>

                <!-- Main Content Row -->
                <div class="d-flex align-items-center gap-3 px-3 pt-1 pb-2">
                    <!-- App Logo Box -->
                    <div class="pwa-app-logo-box flex-shrink-0">
                        <img src="{{ url($business_card_details->profile) }}" alt="{{ $card_details->title }}"
                            class="pwa-app-logo-img"
                            onerror="this.onerror=null; this.src='{{ asset('img/templates/all-categories.png') }}';">
                    </div>

                    <!-- Store Title & Subtitle -->
                    <div class="flex-grow-1 min-w-0">
                        <div class="d-flex align-items-center gap-1 mb-0">
                            <h4 class="mb-0 text-dark fw-bold text-truncate pwa-app-title">
                                {{ $card_details->title }}
                            </h4>
                            <i class="ti ti-circle-check-filled text-e-commerce-blue pwa-verified-badge"></i>
                        </div>
                        <small class="text-muted d-block text-truncate pwa-app-desc">
                            {{ __('Install app on your phone for quick access') }}
                        </small>
                    </div>

                    <!-- Dismiss Close Button -->
                    <button type="button" class="btn-close pwa-close-btn flex-shrink-0" aria-label="Close"
                        onclick="dismissPwaModal()"></button>
                </div>

                <!-- Action Buttons -->
                <div class="d-flex gap-2 p-3 pt-1">
                    <button type="button" class="btn pwa-cancel-btn w-40" onclick="dismissPwaModal()">
                        {{ __('Not Now') }}
                    </button>
                    <button type="button" class="btn btn-e-commerce-blue pwa-install-btn w-60" id="btn-pwa-install">
                        <i class="ti ti-download fs-2 me-1"></i>
                        <span>{{ __('Install App') }}</span>
                    </button>
                </div>

            </div>
        </div>
    </div>

    <style>
        /* ========================================================= */
        /* Direct Guaranteed Deep Dark Backdrop on Active PWA Modal */
        /* ========================================================= */
        #pwaModal.show {
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            width: 100vw !important;
            height: 100vh !important;
            display: flex !important;
            align-items: flex-end !important;
            justify-content: center !important;
            background: rgba(15, 23, 42, 0.75) !important;
            backdrop-filter: blur(2px) !important;
            z-index: 1055 !important;
            padding: 0 16px 25px 16px !important;
        }

        #pwaModal:not(.show) {
            display: none !important;
            pointer-events: none !important;
            visibility: hidden !important;
        }

        .pwa-bottom-modal-dialog {
            margin: 0 auto !important;
            width: 100% !important;
            max-width: 400px !important;
            z-index: 1060 !important;
            pointer-events: auto !important;
            transform: none !important;
        }

        .pwa-bottom-modal-card {
            border-radius: 22px !important;
            background-color: #ffffff !important;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.45) !important;
            border: 1px solid #edf2f7 !important;
            overflow: hidden !important;
            position: relative !important;
        }

        .pwa-drag-handle {
            width: 38px;
            height: 4px;
            background-color: #cbd5e1;
            border-radius: 4px;
            margin: 8px auto 4px;
        }

        .pwa-app-logo-box {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid #edf2f7;
        }

        .pwa-app-logo-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .pwa-app-title {
            font-size: 1.05rem;
            letter-spacing: -0.2px;
        }

        .pwa-verified-badge {
            font-size: 1rem;
        }

        .pwa-app-desc {
            font-size: 0.8rem;
            line-height: 1.2;
        }

        .pwa-close-btn {
            font-size: 0.8rem;
            opacity: 0.55;
            cursor: pointer !important;
        }

        .pwa-cancel-btn {
            height: 42px !important;
            border-radius: 12px !important;
            background-color: #f1f5f9 !important;
            color: #475569 !important;
            font-weight: 600 !important;
            font-size: 0.88rem !important;
            border: none !important;
            cursor: pointer !important;
        }

        .pwa-cancel-btn:hover {
            background-color: #e2e8f0 !important;
            color: #1e293b !important;
        }

        .pwa-install-btn {
            height: 42px !important;
            border-radius: 12px !important;
            background-color: #206bc4 !important;
            color: #ffffff !important;
            font-weight: 700 !important;
            font-size: 0.92rem !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            box-shadow: 0 4px 14px rgba(32, 107, 196, 0.35) !important;
            border: none !important;
            cursor: pointer !important;
        }

        .w-40 {
            width: 38% !important;
        }

        .w-60 {
            width: 62% !important;
        }

        @media (max-width: 768px) {
            #pwaModal.show {
                padding-bottom: 20px !important;
            }
        }
    </style>

    <script>
        (function() {
            const storageDismissKey = "pwa_dismissed_{{ $business_card_details->card_id ?? 'store' }}";
            let pwaTimer = null;
            let pwaAlreadyShown = false;

            // Global scope helper for dismissing modal
            window.dismissPwaModal = function() {
                clearTimeout(pwaTimer);
                sessionStorage.setItem(storageDismissKey, '1');

                const modalEl = document.getElementById('pwaModal');
                if (modalEl) {
                    const inst = bootstrap.Modal.getInstance(modalEl);
                    if (inst) inst.hide();
                    modalEl.classList.remove('show');
                    modalEl.style.display = 'none';
                }

                document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
                document.body.classList.remove('modal-open');
                document.body.style.removeProperty('overflow');
                document.body.style.removeProperty('padding-right');
            };

            function triggerPwaModal() {
                if (pwaAlreadyShown || sessionStorage.getItem(storageDismissKey) === '1') {
                    return;
                }

                // If another popup is currently visible, wait until it is closed
                if (document.querySelector('#informationModal.show, #newsletterModal.show')) {
                    setTimeout(triggerPwaModal, 2000);
                    return;
                }

                const pwaModalEl = document.getElementById('pwaModal');
                if (pwaModalEl) {
                    pwaAlreadyShown = true;
                    const bsPwaModal = new bootstrap.Modal(pwaModalEl, {
                        backdrop: false,
                        keyboard: true
                    });
                    bsPwaModal.show();
                }
            }

            // Bind to window to prevent identifier redeclaration collision
            window.addEventListener('beforeinstallprompt', (e) => {
                e.preventDefault();
                window.deferredPrompt = e;
                clearTimeout(pwaTimer);
                pwaTimer = setTimeout(triggerPwaModal, 3000);
            });

            document.addEventListener('DOMContentLoaded', () => {
                document.getElementById('btn-pwa-install')?.addEventListener('click', async () => {
                    if (window.deferredPrompt) {
                        window.deferredPrompt.prompt();
                        const {
                            outcome
                        } = await window.deferredPrompt.userChoice;
                        window.deferredPrompt = null;
                        window.dismissPwaModal();
                    }
                });
            });
        })();
    </script>
@endif
