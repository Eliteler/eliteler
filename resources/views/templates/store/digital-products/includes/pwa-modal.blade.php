<style>
    /* Force Center Overlay */
    #pwaModal {
        position: fixed !important;
        inset: 0 !important;
        width: 100vw !important;
        height: 100vh !important;
        z-index: 999999 !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        background-color: rgba(0, 0, 0, 0.75) !important;
        backdrop-filter: blur(8px) !important;
        -webkit-backdrop-filter: blur(8px) !important;
        padding: 1rem !important;

        /* Smooth visibility transition */
        opacity: 0;
        visibility: hidden;
        pointer-events: none;
        transition: opacity 0.35s ease, visibility 0.35s ease;
    }

    #pwaModal.pwa-visible {
        opacity: 1 !important;
        visibility: visible !important;
        pointer-events: auto !important;
    }

    #pwaModal .pwa-modal-card {
        margin: auto !important;
        max-width: 360px !important;
        width: 100% !important;
        transform: scale(0.92);
        transition: transform 0.35s cubic-bezier(0.16, 1, 0.3, 1);
    }

    #pwaModal.pwa-visible .pwa-modal-card {
        transform: scale(1);
    }
</style>

<!-- PWA Install Modal -->
<div id="pwaModal" onclick="handlePwaBackdropClick(event)">
    <div class="pwa-modal-card bg-white dark:bg-[#111625] border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-2xl flex flex-col items-center text-center space-y-4 relative"
        onclick="event.stopPropagation()">

        <!-- App Icon with Glow -->
        <div class="relative mt-2">
            <div
                class="absolute -inset-2 bg-gradient-to-r from-indigo-500 to-emerald-500 rounded-3xl blur-md opacity-50 animate-pulse">
            </div>
            <img src="{{ url($business_card_details->profile) }}" alt="{{ $card_details->title }}"
                class="relative w-16 h-16 rounded-2xl object-cover border-2 border-white dark:border-slate-700 shadow-xl mx-auto" />
        </div>

        <!-- Details -->
        <div class="space-y-1.5">
            <h3 class="text-base font-extrabold text-slate-900 dark:text-white tracking-tight">
                {{ __('Install') }} {{ $card_details->title }} {{ __('App') }}
            </h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed px-2">
                {{ __('Install our digital store to your home screen for instant access, offline downloads, and a fast app experience.') }}
            </p>
        </div>

        <!-- Buttons -->
        <div class="w-full space-y-2 pt-2">
            <button type="button" id="pwaInstallBtn"
                class="w-full py-3 px-4 bg-indigo-600 hover:bg-indigo-500 text-white font-bold rounded-xl text-xs sm:text-sm flex items-center justify-center gap-2 shadow-lg shadow-indigo-600/30 transition-all duration-200">
                <svg class="w-4 h-4 fill-none stroke-current" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                <span>{{ __('Install App') }}</span>
            </button>

            <button type="button" onclick="window.closePwaModal()"
                class="w-full py-2.5 px-4 text-xs font-semibold text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200 rounded-xl transition cursor-pointer">
                {{ __('Maybe Later') }}
            </button>
        </div>

    </div>
</div>

{{-- Custom JS from Backend --}}
@push('custom-js')
    <script>
        let deferredPwaPrompt = null;
        window._pwaShownThisSession = false;

        // Global show function with duplicate prevention
        window.showPwaModal = function() {
            if (window._pwaShownThisSession) return;

            // Check if 5-minute cooldown is active
            const dismissUntil = localStorage.getItem('pwa_dismiss_until');
            if (dismissUntil && Date.now() < Number(dismissUntil)) {
                return;
            }

            const modal = document.getElementById('pwaModal');
            if (modal && !modal.classList.contains('pwa-visible')) {
                modal.classList.add('pwa-visible');
                window._pwaShownThisSession = true;
            }
        };

        // Global close function with 5-minute timer set
        window.closePwaModal = function() {
            const modal = document.getElementById('pwaModal');
            if (modal) {
                modal.classList.remove('pwa-visible');

                // Set 5 minutes cooldown (5 minutes * 60 seconds * 1000 ms)
                const cooldownTime = Date.now() + (5 * 60 * 1000);
                localStorage.setItem('pwa_dismiss_until', cooldownTime);
            }
        };

        function handlePwaBackdropClick(e) {
            if (e.target && e.target.id === 'pwaModal') {
                window.closePwaModal();
            }
        }

        // Capture install prompt event if supported
        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferredPwaPrompt = e;
        });

        // Guaranteed Show after Preloader finishes (2.5 seconds)
        function schedulePwaLaunch() {
            if (window._pwaShownThisSession) return;
            setTimeout(() => {
                window.showPwaModal();
            }, 2500);
        }

        // Trigger after page load & preloader exit
        window.addEventListener('load', () => {
            const preloader = document.getElementById('site-preloader');
            if (!preloader || preloader.style.display === 'none') {
                schedulePwaLaunch();
            } else {
                const observer = new MutationObserver(() => {
                    if (preloader.style.display === 'none' || preloader.classList.contains('hidden')) {
                        observer.disconnect();
                        schedulePwaLaunch();
                    }
                });
                observer.observe(preloader, {
                    attributes: true,
                    attributeFilter: ['style', 'class']
                });

                // Fallback timer
                setTimeout(() => {
                    schedulePwaLaunch();
                }, 4000);
            }
        });

        // Handle Install Click
        document.addEventListener("DOMContentLoaded", function() {
            const pwaInstallBtn = document.getElementById('pwaInstallBtn');
            if (pwaInstallBtn) {
                pwaInstallBtn.addEventListener('click', async () => {
                    if (deferredPwaPrompt) {
                        deferredPwaPrompt.prompt();
                        const {
                            outcome
                        } = await deferredPwaPrompt.userChoice;
                        deferredPwaPrompt = null;
                        window.closePwaModal();
                    } else {
                        alert(
                            "{{ __('To install, tap the Share or Menu icon in your browser and select Add to Home Screen.') }}"
                        );
                        window.closePwaModal();
                    }
                });
            }
        });

        window.addEventListener('appinstalled', () => {
            window.closePwaModal();
            deferredPwaPrompt = null;
        });
    </script>
@endpush
