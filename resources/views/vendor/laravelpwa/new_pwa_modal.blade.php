{{-- Start PWA Install Prompt Modal --}}
<div id="pwaModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-black bg-opacity-50"
    onclick="window.handlePwaBackdrop(event)">
    <div class="flex items-end justify-center min-h-screen p-3">
        <div class="bg-white rounded-[32px] p-6 lg:w-1/4 w-full shadow-2xl" onclick="event.stopPropagation()">
            <!-- Modal content -->
            <div class="mb-4">
                <img src="{{ url($img) }}" alt="" class="w-16 h-16 mb-4 rounded-xl shadow-xl">
                <h1 class="text-xl font-semibold mb-3">{{ __('Add to Home Screen') }}</h1>
                <p class="text-gray-700 text-sm">
                    {{ __('This website can be installed on your device. Add it to your home screen for a better experience.') }}
                </p>
            </div>
            <div class="flex flex-col justify-end">
                @if ($primary_color == 'black')
                    <button type="button" id="addToHomeScreenButton" onclick="window.installPwa()"
                        class="mb-2.5 px-4 py-2 text-white bg-[#121212] rounded-xl focus:outline-none cursor-pointer">{{ __('Install') }}</button>
                @else
                    <button type="button" id="addToHomeScreenButton" onclick="window.installPwa()"
                        class="mb-2.5 px-4 py-2 text-{{ $primary_color }}-600 bg-{{ $primary_color }}-100 hover:bg-{{ $primary_color }}-200 rounded-xl focus:outline-none cursor-pointer">{{ __('Install') }}</button>
                @endif
                <button type="button" id="closeModal" onclick="window.hidePwaModal()"
                    class="px-4 py-2 text-black bg-gray-200 rounded-xl hover:bg-gray-300 focus:outline-none cursor-pointer">{{ __('Cancel') }}</button>
            </div>
        </div>
    </div>
</div>

<script>
    (function() {
        "use strict";

        // Store browser prompt event
        let deferredPrompt = null;

        // Show PWA Modal
        window.showPwaModal = function() {
            const modal = document.getElementById('pwaModal');
            if (modal) {
                // Ensure it is attached to body to prevent overflow issues
                if (modal.parentElement !== document.body) {
                    document.body.appendChild(modal);
                }
                modal.classList.remove('hidden');
            }
        };

        // Hide PWA Modal
        window.hidePwaModal = function() {
            const modal = document.getElementById('pwaModal');
            if (modal) {
                modal.classList.add('hidden');
            }
        };

        // Close when clicking outside modal box
        window.handlePwaBackdrop = function(event) {
            if (event.target.id === 'pwaModal') {
                window.hidePwaModal();
            }
        };

        // Listen for browser install prompt
        window.addEventListener('beforeinstallprompt', function(e) {
            // Prevent immediate mini-infobar on mobile
            e.preventDefault();
            // Stash event to trigger later
            deferredPrompt = e;
            // Display modal
            window.showPwaModal();
        });

        // Trigger installation on button click
        window.installPwa = async function() {
            if (!deferredPrompt) {
                return;
            }

            // Show native browser install prompt
            deferredPrompt.prompt();

            // Wait for user choice
            const {
                outcome
            } = await deferredPrompt.userChoice;

            // Reset prompt after action
            deferredPrompt = null;

            // Hide modal
            window.hidePwaModal();
        };

        // Automatically hide modal after successful install
        window.addEventListener('appinstalled', function() {
            window.hidePwaModal();
            deferredPrompt = null;
        });
    })();
</script>
{{-- End PWA Install Prompt Modal --}}
