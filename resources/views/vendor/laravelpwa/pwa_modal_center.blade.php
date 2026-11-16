<div id="pwaModal"
    class="hidden fixed inset-x-0 lg:bottom-0 bottom-16 z-50 p-3 overflow-auto bg-black bg-opacity-50"
    onclick="window.handlePwaBackdrop(event)">
    <div class="flex items-end justify-center min-h-screen">
        <div class="bg-white rounded-lg p-6 lg:w-2/5 w-full shadow-2xl" onclick="event.stopPropagation()">
            <!-- Modal content -->
            <div class="mb-4">
                <h1 class="text-xl font-semibold mb-3">{{ __('Add to Home Screen') }}</h1>
                <p class="text-gray-700 text-sm">
                    {{ __('This website can be installed on your device. Add it to your home screen for a better experience.') }}
                </p>
            </div>
            <div class="flex justify-end">
                <button type="button" id="addToHomeScreenButton" onclick="window.installPwa()"
                    class="px-4 py-2 text-white bg-gray-800 rounded hover:bg-gray-600 focus:outline-none focus:bg-gray-600 cursor-pointer">
                    {{ __('Install') }}
                </button>
                <button type="button" id="closeModal" onclick="window.hidePwaModal()"
                    class="ml-2 px-4 py-2 text-white bg-gray-500 rounded hover:bg-gray-500 focus:outline-none focus:bg-gray-500 cursor-pointer">
                    {{ __('Cancel') }}
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    "use strict";

    (function() {
        let deferredPrompt = null;

        // Function to show modal
        window.showPwaModal = function() {
            const modal = document.getElementById('pwaModal');
            if (modal) {
                // Ensure modal is attached to document body directly
                if (modal.parentElement !== document.body) {
                    document.body.appendChild(modal);
                }
                modal.classList.remove('hidden');
                modal.style.setProperty('display', 'block', 'important');
            }
        };

        // Function to hide modal
        window.hidePwaModal = function() {
            const modal = document.getElementById('pwaModal');
            if (modal) {
                modal.classList.add('hidden');
                modal.style.setProperty('display', 'none', 'important');
            }
        };

        // Close on backdrop click
        window.handlePwaBackdrop = function(e) {
            if (e.target.id === 'pwaModal') {
                window.hidePwaModal();
            }
        };

        // 1. Capture browser PWA prompt event
        window.addEventListener('beforeinstallprompt', function(e) {
            e.preventDefault();
            deferredPrompt = e;
            window.showPwaModal();
        });

        // 2. Fallback: Automatically show modal after 1.5 seconds so you can preview it
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                window.showPwaModal();
            }, 1500);
        });

        // 3. Trigger native install dialog
        window.installPwa = async function() {
            if (!deferredPrompt) {
                alert("PWA is already installed or your browser does not support it.");
                return;
            }

            deferredPrompt.prompt();
            const {
                outcome
            } = await deferredPrompt.userChoice;
            deferredPrompt = null;
            window.hidePwaModal();
        };

        // Hide after installation
        window.addEventListener('appinstalled', function() {
            window.hidePwaModal();
            deferredPrompt = null;
        });
    })();
</script>
