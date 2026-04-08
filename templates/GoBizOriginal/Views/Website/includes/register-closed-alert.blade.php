@if (Session::has('failed'))
    <div id="failed-alert"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 backdrop-blur-md">
        <div class="bg-white border border-gray-200 rounded-lg shadow-xl max-w-sm w-full mx-4 p-6">
            <div class="flex items-center">
                <!-- Text Content -->
                <div class="text-left">
                    <h3 class="text-lg font-semibold text-gray-900">
                        {{ __('Customer Registration Closed') }}
                    </h3>
                    <p class="text-sm text-gray-600 mt-2">
                        {{ Session::get('failed') }}
                    </p>
                </div>
            </div>
            <!-- Close Button -->
            <div class="mt-4 text-center">
                <button onclick="closeModal()"
                    class="bg-red-500 text-white px-4 py-2 rounded-md shadow hover:bg-red-600 focus:outline-none">
                    {{ __('Close') }}
                </button>
            </div>
        </div>
    </div>
@endif
