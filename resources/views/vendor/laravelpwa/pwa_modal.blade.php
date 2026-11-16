<div id="pwaModal" class="fixed inset-x-0 bottom-0 z-50 p-3 overflow-auto bg-black bg-opacity-50">
    <div class="flex items-end justify-center min-h-screen">
        <div class="bg-white rounded-lg p-6 lg:w-2/5">
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
    (function() {
        "use strict";

        // Global variable to hold browser install prompt
        let deferredPrompt = null;

        // Function to show modal
        window.showPwaModal = function() {
            const modal = document.getElementById('pwaModal');
            if (modal) {
                // Attach to body directly to ensure top overlay
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

        // Backdrop click handler
        window.handlePwaBackdrop = function(e) {
            if (e.target.id === 'pwaModal') {
                window.hidePwaModal();
            }
        };

        // Listen for the browser's install event
        window.addEventListener('beforeinstallprompt', function(e) {
            // Prevent browser's mini-infobar
            e.preventDefault();
            // Stash event
            deferredPrompt = e;
            // Display modal
            window.showPwaModal();
        });

        // Trigger PWA installation
        window.installPwa = async function() {
            if (!deferredPrompt) {
                alert("PWA is either already installed or not supported in this browser.");
                return;
            }

            // Show prompt to user
            deferredPrompt.prompt();

            // Wait for user choice
            const {
                outcome
            } = await deferredPrompt.userChoice;

            // Clear prompt reference
            deferredPrompt = null;

            // Hide modal
            window.hidePwaModal();
        };

        // Automatically hide on install complete
        window.addEventListener('appinstalled', function() {
            window.hidePwaModal();
            deferredPrompt = null;
        });
    })();
</script>
