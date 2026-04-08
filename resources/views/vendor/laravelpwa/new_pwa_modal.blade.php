<div id="pwaModal" class="fixed inset-x-0 bottom-0 z-50 p-3 overflow-auto hidden bg-black bg-opacity-50">
    <div class="flex items-end justify-center min-h-screen">
        <div class="bg-white rounded-[32px] p-6 lg:w-1/4">
            <!-- Modal content -->
            <div class="mb-4">
                <img src="{{ url( $img) }}" alt="" class="w-16 h-16 mb-4 rounded-xl shadow-xl">
                <h1 class="text-xl font-semibold mb-3">{{ __('Add to Home Screen') }}</h1>
                <p class="text-gray-700 text-sm">
                    {{ __('This website can be installed on your device. Add it to your home screen for a better experience.') }}
                </p>
            </div>
            <div class="flex flex-col justify-end">
                @if($primary_color == 'black')
                <button id="addToHomeScreenButton"
                    class="mb-2.5 px-4 py-2 text-white bg-[#121212] rounded-xl focus:outline-none">{{ __('Install') }}</button> 
                @else
                <button id="addToHomeScreenButton"
                    class="mb-2.5 px-4 py-2 text-{{ $primary_color }}-600 bg-{{ $primary_color }}-100 hover:bg-{{ $primary_color }}-200 rounded-xl focus:outline-none">{{ __('Install') }}</button> 
                @endif
                <button id="closeModal" 
                class="px-4 py-2 text-black bg-gray-200 rounded-xl hover:bg-gray-300 focus:outline-none focus:bg-gray-300">{{ __('Cancel') }}</button>                              
            </div>
        </div>
    </div>
</div>
 