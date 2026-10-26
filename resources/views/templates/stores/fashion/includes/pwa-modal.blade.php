{{-- Modern Luxury PWA Bottom-Sheet Modal Component (Null-Safe) --}}
<style>
    /* Dark Backdrop Overlay */
    .fashion-pwa-backdrop {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background: rgba(15, 23, 42, 0.75);
        backdrop-filter: blur(3px);
        z-index: 1044;
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.35s ease;
    }

    .fashion-pwa-backdrop.show {
        opacity: 1;
        pointer-events: auto;
    }

    /* PWA Floating Bottom-Sheet Container */
    .fashion-pwa-sheet {
        position: fixed;
        bottom: 85px;
        left: 50%;
        transform: translateX(-50%);
        width: calc(100% - 28px);
        max-width: 440px;
        background-color: #0f172a;
        color: #ffffff;
        border: 1px solid rgba(255, 255, 255, 0.15);
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.7);
        z-index: 1045;
        padding: 16px 18px;
        display: none;
    }

    .fashion-pwa-sheet.show {
        display: block;
        animation: pwaSlideUpFluid 0.35s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }

    @keyframes pwaSlideUpFluid {
        from {
            opacity: 0;
            transform: translate(-50%, 35px);
        }

        to {
            opacity: 1;
            transform: translate(-50%, 0);
        }
    }

    .fashion-pwa-icon-box {
        position: relative;
        flex-shrink: 0;
    }

    .fashion-pwa-icon {
        width: 48px;
        height: 48px;
        object-fit: cover;
        border-radius: 0px;
        border: 1.5px solid rgba(255, 255, 255, 0.25);
    }

    .fashion-pwa-mini-badge {
        position: absolute;
        bottom: -4px;
        right: -4px;
        background-color: #d97706;
        color: #ffffff;
        width: 16px;
        height: 16px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 9px;
        border: 1.5px solid #0f172a;
    }

    .fashion-pwa-tag {
        font-size: 10px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: #94a3b8;
        display: block;
        margin-bottom: 2px;
    }

    .fashion-pwa-title {
        font-family: 'Oswald', sans-serif;
        font-size: 16px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #ffffff;
        margin: 0;
        line-height: 1.2;
    }

    .fashion-pwa-desc {
        font-size: 12px;
        color: #94a3b8;
        margin: 0;
        line-height: 1.3;
    }

    .fashion-pwa-btn-install {
        background-color: #ffffff;
        color: #0f172a;
        border: none;
        border-radius: 0px;
        padding: 9px 16px;
        font-size: 12px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.2s ease;
    }

    .fashion-pwa-btn-install:hover {
        background-color: #f1f5f9;
        color: #000000;
    }

    .fashion-pwa-btn-close {
        background-color: transparent;
        color: #94a3b8;
        border: none;
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: color 0.2s ease;
    }

    .fashion-pwa-btn-close:hover {
        color: #ffffff;
    }

    @media (min-width: 992px) {
        .fashion-pwa-sheet {
            bottom: 24px;
            right: 24px;
            left: auto;
            transform: none;
        }

        @keyframes pwaSlideUpFluid {
            from {
                opacity: 0;
                transform: translateY(35px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    }
</style>

<!-- Fullscreen Dark Overlay Backdrop -->
<div id="pwaBackdropOverlay" class="fashion-pwa-backdrop" onclick="dismissPwaModal()"></div>

<!-- PWA Card Component -->
<div id="pwaBottomSheet" class="fashion-pwa-sheet">
    <div class="d-flex align-items-center justify-content-between mb-2">
        <span class="fashion-pwa-tag">{{ __('Fast & Secure App') }}</span>
        <button type="button" class="fashion-pwa-btn-close" onclick="dismissPwaModal()" aria-label="Close">
            <i class="ti ti-x fs-3"></i>
        </button>
    </div>

    <div class="d-flex align-items-center gap-3 mb-3">
        <div class="fashion-pwa-icon-box">
            <img src="{{ url($business_card_details->profile ?? 'images/default-avatar.png') }}"
                class="fashion-pwa-icon" alt="{{ $card_details->title }}">
            <div class="fashion-pwa-mini-badge">
                <i class="ti ti-bolt"></i>
            </div>
        </div>
        <div class="flex-grow-1 min-w-0">
            <h5 class="fashion-pwa-title text-truncate">{{ $card_details->title }}</h5>
            <p class="fashion-pwa-desc">{{ __('Install on home screen for 1-tap shopping') }}</p>
        </div>
    </div>

    <div class="d-flex align-items-center justify-content-end gap-2 pt-2 border-top border-secondary border-opacity-25">
        <button type="button" class="btn btn-link text-white-50 p-0 text-decoration-none fs-4 me-3"
            onclick="dismissPwaModal()">
            {{ __('Not Now') }}
        </button>
        <button type="button" class="fashion-pwa-btn-install" id="pwaInstallTriggerBtn">
            <i class="ti ti-download fs-3"></i>
            <span>{{ __('Install App') }}</span>
        </button>
    </div>
</div>

<script>
    // PWA Bottom Sheet Engine with Strict Null Safety
    (function() {
        "use strict";

        let deferredPwaPrompt;
        const storeCardId = "{{ $business_card_details->card_id ?? 'store' }}";
        const pwaDismissKey = 'pwa_dismissed_' + storeCardId;

        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferredPwaPrompt = e;

            try {
                if (!sessionStorage.getItem(pwaDismissKey)) {
                    setTimeout(() => {
                        const sheet = document.getElementById('pwaBottomSheet');
                        const backdrop = document.getElementById('pwaBackdropOverlay');
                        if (sheet && sheet.classList) sheet.classList.add('show');
                        if (backdrop && backdrop.classList) backdrop.classList.add('show');
                    }, 4000);
                }
            } catch (err) {
                console.warn("PWA storage check skipped:", err);
            }
        });

        document.addEventListener("DOMContentLoaded", function() {
            const installBtn = document.getElementById('pwaInstallTriggerBtn');
            if (installBtn) {
                installBtn.addEventListener('click', async () => {
                    window.dismissPwaModal();
                    if (deferredPwaPrompt) {
                        deferredPwaPrompt.prompt();
                        await deferredPwaPrompt.userChoice;
                        deferredPwaPrompt = null;
                    }
                });
            }
        });

        window.dismissPwaModal = function() {
            try {
                sessionStorage.setItem(pwaDismissKey, '1');
            } catch (e) {}

            const sheet = document.getElementById('pwaBottomSheet');
            const backdrop = document.getElementById('pwaBackdropOverlay');
            if (sheet && sheet.classList) sheet.classList.remove('show');
            if (backdrop && backdrop.classList) backdrop.classList.remove('show');
        };
    })();
</script>
