{{-- Start Custom PWA Install Prompt Modal --}}
<style>
    #pwaModal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(31, 41, 55, 0.7);
        backdrop-filter: blur(4px);
        z-index: 999999;
        /* Increased z-index to stay on top */
        display: none;
    }

    #pwaModal.show {
        display: block !important;
    }

    #pwaModal .pwa-box {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        background: #ffffff;
        width: 100%;
        max-width: 500px;
        margin: 0 auto;
        padding: 28px 24px 24px;
        border-radius: 24px 24px 0 0;
        box-shadow: 0 -8px 30px rgba(0, 0, 0, 0.12);
        text-align: center;
        animation: pwaSlideUp 0.35s ease;
    }

    @keyframes pwaSlideUp {
        from {
            transform: translateY(100%);
        }

        to {
            transform: translateY(0);
        }
    }

    @keyframes pwaPop {
        from {
            transform: scale(0.85);
            opacity: 0;
        }

        to {
            transform: scale(1);
        }
    }

    /* Icon badge */
    #pwaModal .pwa-icon-wrap {
        width: 60px;
        height: 60px;
        background: rgba(76, 175, 80, 0.1);
        border-radius: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 14px;
    }

    #pwaModal .pwa-icon-wrap i {
        font-size: 28px;
        color: var(--primary, #4CAF50);
    }

    /* Title */
    #pwaModal h3 {
        font-family: "Outfit", sans-serif;
        font-size: 20px;
        font-weight: 700;
        color: #1f2937;
        margin-bottom: 8px;
    }

    /* Description */
    #pwaModal p {
        font-size: 13px;
        color: #6b7280;
        line-height: 1.6;
        max-width: 280px;
        margin: 0 auto 22px;
    }

    /* Button row */
    #pwaModal .pwa-btn-row {
        display: flex;
        gap: 10px;
    }

    /* Cancel button */
    #pwaModal .pwa-btn-cancel {
        flex: 1;
        padding: 13px;
        background: #f4f7f4;
        color: #1f2937;
        border: none;
        border-radius: 14px;
        font-family: "Outfit", sans-serif;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.2s;
    }

    #pwaModal .pwa-btn-cancel:hover {
        background: #e5e7eb;
    }

    /* Install / primary button */
    #pwaModal .pwa-btn-install {
        flex: 1;
        padding: 13px;
        background: var(--primary, #4CAF50);
        color: #ffffff;
        border: none;
        border-radius: 14px;
        font-family: "Outfit", sans-serif;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: opacity 0.2s;
    }

    #pwaModal .pwa-btn-install:hover {
        opacity: 0.9;
    }
</style>

<div id="pwaModal" onclick="window.hidePwaPrompt()">
    <div class="pwa-box" onclick="event.stopPropagation()">

        <div class="pwa-icon-wrap">
            <i class="fas fa-mobile-alt"></i>
        </div>

        <h3>{{ __('Add to Home Screen') }}</h3>
        <p>{{ __('This website can be installed on your device. Add it to your home screen for a better experience.') }}
        </p>

        <div class="pwa-btn-row">
            <button type="button" class="pwa-btn-cancel" id="closeModal"
                onclick="window.hidePwaPrompt()">{{ __('Cancel') }}</button>
            <button type="button" class="pwa-btn-install" id="addToHomeScreenButton"
                onclick="window.installPwa()">{{ __('Install') }}</button>
        </div>

    </div>
</div>

<script>
    "use strict";

    (function() {
        let deferredPrompt = null;

        // Show modal function
        window.showPwaPrompt = function() {
            const modal = document.getElementById('pwaModal');
            if (modal) {
                // Attach directly to body to avoid overflow issues
                if (modal.parentElement !== document.body) {
                    document.body.appendChild(modal);
                }
                modal.classList.add('show');
            }
        };

        // Hide modal function
        window.hidePwaPrompt = function() {
            const modal = document.getElementById('pwaModal');
            if (modal) {
                modal.classList.remove('show');
            }
        };

        // 1. Capture browser PWA prompt event
        window.addEventListener('beforeinstallprompt', function(e) {
            e.preventDefault();
            deferredPrompt = e;
            window.showPwaPrompt();
        });

        // 2. Fallback: Automatically show modal after 1.5 seconds so you can preview it
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                window.showPwaPrompt();
            }, 1500);
        });

        // 3. Trigger native install dialog
        window.installPwa = async function() {
            if (!deferredPrompt) {
                alert("PWA is already installed or not supported in this browser.");
                return;
            }

            deferredPrompt.prompt();
            const {
                outcome
            } = await deferredPrompt.userChoice;
            deferredPrompt = null;
            window.hidePwaPrompt();
        };

        // Hide after installation
        window.addEventListener('appinstalled', function() {
            window.hidePwaPrompt();
            deferredPrompt = null;
        });
    })();
</script>
{{-- End Custom PWA Install Prompt Modal --}}
