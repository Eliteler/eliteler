{{-- Women's Salon — PWA Install Prompt Modal --}}
<div id="pwaModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black bg-opacity-70"
    onclick="window.handlePwaBackdrop(event)">
    <div class="sl-panel w-full max-w-md rounded-3xl p-6 mx-4 relative text-center space-y-4 shadow-2xl bg-white"
        onclick="event.stopPropagation()">

        {{-- Close Button --}}
        <button type="button" class="sl-modal-close cursor-pointer" id="closePwaModal" onclick="window.hidePwaPrompt()"
            aria-label="{{ __('Close') }}">
            <i class="fas fa-times"></i>
        </button>

        {{-- Mobile Handle Indicator --}}
        <div class="w-10 h-1 rounded-full bg-[#E29A88]/30 mx-auto mb-1"></div>

        {{-- Salon Icon --}}
        <div
            class="w-14 h-14 rounded-full mx-auto flex items-center justify-center text-xl text-[#721B29] mb-2 bg-[#FDF7F4] border-2 border-[#E29A88]">
            <i class="fas fa-cut"></i>
        </div>

        {{-- Text --}}
        <div>
            <p class="text-xs font-bold uppercase tracking-widest text-[#E29A88] mb-1">{{ __('App Experience') }}</p>
            <h3 class="sl-display text-2xl sm:text-3xl font-bold text-[#721B29]">{{ __('Add to Home Screen') }}</h3>
            <p class="text-xs text-[#735D61] mt-2 leading-relaxed">
                {{ __('Add this digital card to your home screen for instant access.') }}
            </p>
        </div>

        {{-- Actions --}}
        <div class="grid grid-cols-2 gap-3 pt-2">
            <button type="button"
                class="sl-btn-outline py-3 rounded-xl text-xs font-bold uppercase tracking-wider cursor-pointer"
                id="closePwaBtn" onclick="window.hidePwaPrompt()">
                {{ __('Cancel') }}
            </button>
            <button type="button"
                class="sl-btn-primary py-3 rounded-xl text-xs font-bold uppercase tracking-wider flex items-center justify-center mt-0 cursor-pointer"
                id="addToHomeScreenButton" onclick="window.installPwa()">
                <i class="fas fa-download mr-1.5"></i> {{ __('Install App') }}
            </button>
        </div>

    </div>
</div>

<script>
    "use strict";

    (function() {
        let deferredPrompt = null;

        // Function to show modal
        window.showPwaPrompt = function() {
            const modal = document.getElementById('pwaModal');
            if (modal) {
                // Ensure modal is attached to document body directly
                if (modal.parentElement !== document.body) {
                    document.body.appendChild(modal);
                }
                modal.classList.remove('hidden');
                modal.style.setProperty('display', 'flex', 'important');
            }
        };

        // Function to hide modal
        window.hidePwaPrompt = function() {
            const modal = document.getElementById('pwaModal');
            if (modal) {
                modal.classList.add('hidden');
                modal.style.setProperty('display', 'none', 'important');
            }
        };

        // Close on backdrop click
        window.handlePwaBackdrop = function(e) {
            if (e.target.id === 'pwaModal') {
                window.hidePwaPrompt();
            }
        };

        // 1. Capture browser PWA prompt event
        window.addEventListener('beforeinstallprompt', function(e) {
            e.preventDefault();
            deferredPrompt = e;
            window.showPwaPrompt();
        });

        // 2. Fallback: Automatically show modal after 1.5 seconds so you can see it
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
