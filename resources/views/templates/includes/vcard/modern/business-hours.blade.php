<div class="relative">
    <!-- Section Header -->
    <h2 class="text-3xl lg:text-4xl font-bold py-12 text-center relative custom-head">

        {{ __('Business Hours') }}
    </h2>
    <!-- Business Hours Card -->
    <div class="bg-green-100 border border-green-600 rounded-2xl p-6">
        @if ($business_hours->is_always_open != 'Opening')
            <!-- Days and Hours List -->
            <div class="grid grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach (['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as $day)
                    <div class="flex items-center space-x-2">
                        <!-- Day Icon -->
                        <div class="flex items-center justify-center w-10 h-10 bg-green-800 text-white rounded-2xl">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round"
                                class="icon icon-tabler icons-tabler-outline icon-tabler-calendar-clock text-white">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                <path d="M10.5 21h-4.5a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v3" />
                                <path d="M16 3v4" />
                                <path d="M8 3v4" />
                                <path d="M4 11h10" />
                                <path d="M18 18m-4 0a4 4 0 1 0 8 0a4 4 0 1 0 -8 0" />
                                <path d="M18 16.5v1.5l.5 .5" />
                            </svg>
                        </div>
                        <!-- Day and Hours -->
                        <div>
                            <p class="text-base font-medium text-green-800 capitalize">
                                {{ __($day) }}</p>
                            <p class="text-sm text-gray-500">
                                {{ __($business_hours->$day ?: __('Closed')) }}
                            </p>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <!-- Always Open -->
            <div class="flex items-start space-x-2">
                <!-- Animated Icon -->
                <div
                    class="flex items-center justify-center w-12 h-12 bg-green-200 text-green-800 rounded-full transform hover:scale-110 transition-transform duration-300 ease-in-out">
                    <svg class="w-6 h-6 animate-pulse" fill="currentColor" xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 24 24">
                        <path d="M12 2a10 10 0 100 20 10 10 0 000-20zM10 16l6-4-6-4v8z" />
                    </svg>
                </div>
                <!-- Text -->
                <div>
                    <p class="text-xl font-bold text-green-800">
                        {{ __('Always Open') }}</p>
                    <p class="text-sm text-gray-500">
                        {{ __('We’re available 24/7 to serve you!') }}</p>
                </div>
            </div>
        @endif
    </div>
</div>
