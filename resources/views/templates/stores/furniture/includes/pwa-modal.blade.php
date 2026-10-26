@if ($plan_details != null && $plan_details['pwa'] == 1 && $business_card_details->is_enable_pwa == 1)
    <style>
        /* 1. Fullscreen Dark Blur Backdrop Wrapper (Snug Bottom Alignment) */
        .pwa-backdrop-wrapper {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background-color: rgba(15, 23, 42, 0.78);
            backdrop-filter: blur(5px);
            -webkit-backdrop-filter: blur(5px);
            z-index: 1045;
            display: flex;
            align-items: flex-end;
            justify-content: center;
            padding-bottom: 14px;
            /* Snug, low bottom alignment */
            transition: opacity 0.3s ease, visibility 0.3s ease;
        }

        @media (min-width: 768px) {
            .pwa-backdrop-wrapper {
                padding-bottom: 24px;
            }
        }

        /* 2. Low-Profile Luxury PWA Bottom Sheet Card */
        .pwa-luxury-sheet {
            width: calc(100% - 24px);
            max-width: 440px;
            margin: 0 auto;
            background: linear-gradient(180deg, #fdfbf8 0%, #ffffff 100%);
            border-radius: 22px !important;
            padding: 18px 16px;
            box-shadow: 0 20px 45px -10px rgba(0, 0, 0, 0.5);
            border: 1px solid rgba(204, 164, 122, 0.35);
            position: relative;
            animation: pwaSlideUp 0.35s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes pwaSlideUp {
            from {
                transform: translateY(40px) scale(0.97);
                opacity: 0;
            }

            to {
                transform: translateY(0) scale(1);
                opacity: 1;
            }
        }

        /* App Logo */
        .pwa-app-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            object-fit: cover;
            background-color: #ffffff;
            border: 2px solid #f5ece1;
            box-shadow: 0 4px 10px rgba(204, 164, 122, 0.2);
            flex-shrink: 0;
        }

        /* Circular Close Button */
        .pwa-close-btn {
            position: absolute;
            top: 12px;
            right: 12px;
            width: 32px;
            height: 32px;
            border-radius: 50% !important;
            background-color: #f1ede4;
            color: #1e293b;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: transform 0.2s ease, background-color 0.2s ease;
            padding: 0;
            z-index: 10;
        }

        .pwa-close-btn:hover {
            background-color: #e2dcd0;
            transform: rotate(90deg);
        }

        /* True Pill Action Buttons */
        .pwa-btn-later {
            height: 42px;
            border-radius: 9999px !important;
            background-color: #f1f5f9;
            color: #475569 !important;
            font-weight: 700;
            font-size: 13px;
            border: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 40%;
            cursor: pointer;
            transition: background-color 0.2s ease;
        }

        .pwa-btn-later:hover {
            background-color: #e2e8f0;
            color: #0f172a !important;
        }

        .pwa-btn-install {
            height: 42px;
            border-radius: 9999px !important;
            background-color: #1f4b48;
            color: #ffffff !important;
            font-weight: 700;
            font-size: 13px;
            border: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            width: 58%;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(31, 75, 72, 0.3);
            transition: all 0.2s ease;
        }

        .pwa-btn-install:hover {
            background-color: #163836;
            color: #ffffff !important;
            transform: translateY(-1px);
            box-shadow: 0 6px 18px rgba(31, 75, 72, 0.4);
        }
    </style>

    {{-- Fullscreen Dark Backdrop Wrapper (Hugging the very bottom) --}}
    <div class="pwa-backdrop-wrapper" id="pwaBackdropWrapper" onclick="if(event.target === this) dismissPwaSheet();">
        {{-- Main Luxury Bottom Sheet Card --}}
        <div class="pwa-luxury-sheet" id="pwaBottomSheet">
            {{-- Circular Close Trigger --}}
            <button type="button" class="pwa-close-btn" onclick="dismissPwaSheet()" aria-label="Close">
                <i class="ti ti-x fs-3"></i>
            </button>

            <div class="d-flex align-items-center gap-3 pe-4">
                <img src="{{ !empty($business_card_details->profile) ? url($business_card_details->profile) : 'data:image/svg+xml;charset=UTF-8,%3Csvg%20width%3D%2248%22%20height%3D%2248%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%3E%3Ccircle%20cx%3D%2224%22%20cy%3D%2224%22%20r%3D%2224%22%20fill%3D%22%23cca47a%22%2F%3E%3C%2Fsvg%3E' }}"
                    alt="{{ $card_details->title }}" class="pwa-app-icon"
                    onerror="this.onerror=null;this.src='data:image/svg+xml;charset=UTF-8,%3Csvg%20width%3D%2248%22%20height%3D%2248%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%3E%3Ccircle%20cx%3D%2224%22%20cy%3D%2224%22%20r%3D%2224%22%20fill%3D%22%23cca47a%22%2F%3E%3C%2Fsvg%3E';">

                <div class="flex-grow-1 overflow-hidden">
                    <h4 class="mb-0 fs-3 fw-bold text-dark text-truncate"
                        style="font-family: 'DM Serif Display', serif;">{{ $card_details->title }}</h4>
                    <small
                        class="text-muted d-block fs-5 text-truncate">{{ __('Install app for faster shopping') }}</small>
                </div>
            </div>

            <div class="d-flex gap-2 mt-3 justify-content-between">
                <button type="button" class="pwa-btn-later" onclick="dismissPwaSheet()">
                    {{ __('Later') }}
                </button>

                <button type="button" class="pwa-btn-install" id="pwaInstallBtn">
                    <i class="ti ti-download fs-3"></i>
                    <span>{{ __('Install App') }}</span>
                </button>
            </div>
        </div>
    </div>

    <script>
        (function() {
            "use strict";

            let deferredPrompt = null;
            const wrapper = document.getElementById('pwaBackdropWrapper');

            window.addEventListener('beforeinstallprompt', (e) => {
                e.preventDefault();
                deferredPrompt = e;
            });

            const installBtn = document.getElementById('pwaInstallBtn');
            if (installBtn) {
                installBtn.addEventListener('click', async () => {
                    if (deferredPrompt) {
                        deferredPrompt.prompt();
                        const {
                            outcome
                        } = await deferredPrompt.userChoice;
                        deferredPrompt = null;
                        dismissPwaSheet();
                    } else {
                        if (window.showStoreToast) {
                            window.showStoreToast(
                                "{{ __('To install: Tap Share icon and select Add to Home Screen') }}",
                                "success");
                        }
                        dismissPwaSheet();
                    }
                });
            }

            window.dismissPwaSheet = function() {
                if (wrapper) {
                    wrapper.style.opacity = '0';
                    wrapper.style.visibility = 'hidden';
                    wrapper.style.pointerEvents = 'none';
                    setTimeout(() => {
                        if (wrapper.parentNode) wrapper.parentNode.removeChild(wrapper);
                    }, 300);
                }
            };
        })();
    </script>
@endif
