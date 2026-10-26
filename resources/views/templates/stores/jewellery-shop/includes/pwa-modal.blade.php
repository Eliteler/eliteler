{{-- Guaranteed Working PWA Sheet with Direct Blur & Dark Backdrop --}}
@if (
    !empty($plan_details) &&
        $plan_details['pwa'] == 1 &&
        !empty($business_card_details->is_enable_pwa) &&
        $business_card_details->is_enable_pwa == 1)
    <div class="modal fade pwa-luxury-modal" id="pwaModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog pwa-bottom-sheet-dialog">
            <div class="modal-content pwa-sheet-card">
                <!-- Floating Circular Close Button -->
                <button type="button" class="pwa-floating-close-btn" onclick="closePwaModal()" data-bs-dismiss="modal"
                    data-dismiss="modal" aria-label="Close">
                    <i class="ti ti-x"></i>
                </button>

                <div class="modal-body p-4 p-md-5 text-center">
                    <!-- App Avatar -->
                    <div class="pwa-app-icon-wrap">
                        <div class="pwa-app-avatar-box">
                            <img src="{{ !empty($business_card_details->profile) ? url($business_card_details->profile) : '' }}"
                                alt="{{ $card_details->title }}" class="pwa-avatar-img"
                                onerror="this.onerror=null;this.src='data:image/svg+xml;charset=UTF-8,%3Csvg%20width%3D%2280%22%20height%3D%2280%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%3E%3Crect%20width%3D%22100%25%22%20height%3D%22100%25%22%20fill%3D%22%23d4af37%22%2F%3E%3Ctext%20x%3D%2250%25%22%20y%3D%2250%25%22%20fill%3D%22%23ffffff%22%20font-size%3D%2220%22%20text-anchor%3D%22middle%22%20dy%3D%22.3em%22%3EPWA%3C%2Ftext%3E%3C%2Fsvg%3E';" />
                        </div>
                    </div>

                    <!-- Tag -->
                    <div class="pwa-badge-wrap">
                        <span class="pwa-vip-pill">
                            <i class="ti ti-device-mobile"></i> {{ __('OFFICIAL STORE APP') }}
                        </span>
                    </div>

                    <!-- Headings -->
                    <h3 class="pwa-hero-title">{{ __('Install') }} {{ $card_details->title }}</h3>
                    <p class="pwa-hero-desc">
                        {{ __('Add our store to your home screen for ultra-fast browsing, instant WhatsApp orders, and offline access.') }}
                    </p>

                    <!-- Value Props -->
                    <div class="pwa-features-row">
                        <span class="pwa-feature-chip">
                            <i class="ti ti-bolt"></i> {{ __('1-Tap Access') }}
                        </span>
                        <span class="pwa-feature-chip">
                            <i class="ti ti-shopping-bag"></i> {{ __('Faster Orders') }}
                        </span>
                        <span class="pwa-feature-chip">
                            <i class="ti ti-shield-check"></i> {{ __('100% Secure') }}
                        </span>
                    </div>

                    <!-- Action Buttons -->
                    <div class="pwa-actions-grid">
                        <button type="button" class="btn pwa-btn-cancel" onclick="closePwaModal()"
                            data-bs-dismiss="modal" data-dismiss="modal">
                            {{ __('Maybe Later') }}
                        </button>
                        <button type="button" class="btn btn-gold pwa-btn-install" id="pwaInstallAppBtn">
                            <i class="ti ti-download"></i>
                            <span>{{ __('Install App') }}</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* Direct Dark Backdrop on PWA Modal */
        #pwaModal {
            background-color: rgba(15, 23, 42, 0.75) !important;
            z-index: 1060 !important;
        }

        /* Force blur on the entire store behind PWA modal via multiple selectors */
        body.pwa-modal-active .store-main-wrapper,
        body.pwa-modal-active .luxury-bottom-bar,
        body.pwa-modal-active .luxury-wa-floater-root,
        body:has(#pwaModal.show) .store-main-wrapper,
        body:has(#pwaModal.show) .luxury-bottom-bar,
        body:has(#pwaModal.show) .luxury-wa-floater-root {
            filter: blur(6px) !important;
            -webkit-filter: blur(6px) !important;
            transition: filter 0.25s ease-in-out;
            pointer-events: none;
        }

        .pwa-bottom-sheet-dialog {
            max-width: 480px;
            margin: auto;
            display: flex;
            align-items: flex-end;
            min-height: calc(100% - 1rem);
            z-index: 1070 !important;
        }

        @media (min-width: 576px) {
            .pwa-bottom-sheet-dialog {
                align-items: center;
            }
        }

        .pwa-sheet-card {
            border-radius: 28px !important;
            border: 1.5px solid rgba(212, 175, 55, 0.35) !important;
            background: #ffffff !important;
            box-shadow: 0 25px 65px rgba(0, 0, 0, 0.6) !important;
            position: relative;
            overflow: hidden;
            width: 100%;
            margin-bottom: 12px;
        }

        .pwa-floating-close-btn {
            position: absolute;
            top: 16px;
            right: 16px;
            width: 36px;
            height: 36px;
            border-radius: 50% !important;
            background: #fbf7ee;
            border: 1px solid rgba(212, 175, 55, 0.35);
            color: #55411d;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 16px;
            z-index: 10;
            transition: all 0.25s ease;
        }

        .pwa-floating-close-btn:hover {
            background: #d4af37;
            color: #ffffff;
            border-color: #d4af37;
            transform: rotate(90deg);
        }

        .pwa-app-icon-wrap {
            width: 76px;
            height: 76px;
            margin: 0 auto 16px auto;
        }

        .pwa-app-avatar-box {
            width: 100%;
            height: 100%;
            border-radius: 20px !important;
            overflow: hidden;
            background: #fbf7ee;
            border: 2px solid #d4af37;
        }

        .pwa-avatar-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .pwa-badge-wrap {
            margin-bottom: 10px;
        }

        .pwa-vip-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #fbf7ee;
            color: #8c733e;
            border: 1px solid rgba(212, 175, 55, 0.4);
            padding: 5px 16px;
            border-radius: 50px !important;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .pwa-hero-title {
            font-family: 'Gloock', serif;
            font-size: 22px;
            color: #2b2316;
            margin-bottom: 8px;
            line-height: 1.3;
        }

        .pwa-hero-desc {
            font-size: 13.5px;
            color: #716248;
            line-height: 1.6;
            max-width: 380px;
            margin: 0 auto 18px auto;
        }

        .pwa-features-row {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            flex-wrap: wrap;
            margin-bottom: 24px;
        }

        .pwa-feature-chip {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            background: #ffffff;
            border: 1px solid rgba(212, 175, 55, 0.25);
            color: #55411d;
            padding: 4px 12px;
            border-radius: 50px !important;
            font-size: 11.5px;
            font-weight: 700;
        }

        .pwa-feature-chip i {
            color: #b89028;
        }

        .pwa-actions-grid {
            display: grid;
            grid-template-columns: 1fr 1.3fr;
            gap: 12px;
        }

        .pwa-btn-cancel {
            height: 48px;
            border-radius: 50px !important;
            background: #fbf7ee !important;
            border: 1px solid rgba(212, 175, 55, 0.35) !important;
            color: #55411d !important;
            font-weight: 700;
            font-size: 13.5px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.25s ease;
        }

        .pwa-btn-cancel:hover {
            background: #f6edd9 !important;
            border-color: #d4af37 !important;
        }

        .pwa-btn-install {
            height: 48px;
            border-radius: 50px !important;
            background: #d4af37 !important;
            color: #ffffff !important;
            font-weight: 700;
            font-size: 13.5px;
            border: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            box-shadow: 0 6px 20px rgba(212, 175, 55, 0.35);
            transition: all 0.25s ease;
            cursor: pointer;
        }

        .pwa-btn-install:hover {
            background: #b89028 !important;
        }
    </style>

    <script>
        // Universal Safe Dismiss Function
        function closePwaModal() {
            const pwaEl = document.getElementById('pwaModal');
            if (!pwaEl) return;

            // Try Bootstrap 5
            try {
                const bsModal = bootstrap.Modal.getInstance(pwaEl) || bootstrap.Modal.getOrCreateInstance(pwaEl);
                if (bsModal) bsModal.hide();
            } catch (e) {}

            // Try jQuery
            try {
                if (window.jQuery && $(pwaEl).modal) $(pwaEl).modal('hide');
            } catch (e) {}

            // Force cleanup
            pwaEl.classList.remove('show');
            pwaEl.style.display = 'none';
            pwaEl.setAttribute('aria-hidden', 'true');
            document.body.classList.remove('modal-open', 'pwa-modal-active');
            document.body.style.removeProperty('overflow');
            document.body.style.removeProperty('padding-right');

            sessionStorage.setItem('pwa_dismissed', 'true');
        }

        document.addEventListener("DOMContentLoaded", function() {
            "use strict";

            let deferredPwaPrompt = null;
            const pwaModalEl = document.getElementById('pwaModal');
            const installBtn = document.getElementById('pwaInstallAppBtn');

            // Automatically activate blur whenever PWA modal enters the screen
            if (pwaModalEl) {
                pwaModalEl.addEventListener('show.bs.modal', function() {
                    document.body.classList.add('modal-open', 'pwa-modal-active');
                });
                pwaModalEl.addEventListener('shown.bs.modal', function() {
                    document.body.classList.add('modal-open', 'pwa-modal-active');
                });
                pwaModalEl.addEventListener('hidden.bs.modal', function() {
                    document.body.classList.remove('pwa-modal-active');
                });

                // MutationObserver: Guarantees blur even if triggered by external scripts
                const observer = new MutationObserver(function() {
                    const isShown = pwaModalEl.classList.contains('show') || pwaModalEl.style.display ===
                        'block';
                    if (isShown) {
                        document.body.classList.add('modal-open', 'pwa-modal-active');
                    } else {
                        document.body.classList.remove('pwa-modal-active');
                    }
                });
                observer.observe(pwaModalEl, {
                    attributes: true,
                    attributeFilter: ['class', 'style']
                });

                // Close when clicking directly on the dark outer background
                pwaModalEl.addEventListener('click', function(e) {
                    if (e.target === pwaModalEl) {
                        closePwaModal();
                    }
                });
            }

            window.addEventListener('beforeinstallprompt', function(e) {
                e.preventDefault();
                deferredPwaPrompt = e;
            });

            if (installBtn) {
                installBtn.addEventListener('click', function() {
                    if (deferredPwaPrompt) {
                        deferredPwaPrompt.prompt();
                        deferredPwaPrompt.userChoice.then(function() {
                            deferredPwaPrompt = null;
                            closePwaModal();
                        });
                    } else {
                        closePwaModal();
                    }
                });
            }
        });
    </script>
@endif
