<div class="relative">
    <h2 class="text-3xl lg:text-4xl font-bold py-12 text-center relative custom-head">
        {{ __('Service Booking') }}
    </h2>

    {{-- Service Booking Form --}}
    <div class="bg-green-100 border border-green-600 rounded-2xl p-6">
        <!-- Error Message (hidden by default) -->
        <div id="errorMessage1" class="bg-red-800 text-sm my-2 hidden p-3 text-white rounded-2xl"></div>

        {{-- Success Message (hidden by default) --}}
        <div id="successMessage1" class="bg-green-800 text-sm my-2 hidden p-3 text-white rounded-2xl"></div>

        <div class="flex flex-col lg:flex-row lg:gap-4 mb-4">
            {{-- Name --}}
            <div class="flex flex-col w-full lg:w-1/2 mb-4 lg:mb-0">
                <label for="customer_name" class="text-gray-800 font-medium mb-2">{{ __('Name') }}</label>
                <input type="text" name="customer_name" id="customer_name" placeholder="{{ __('Your Name') }}"
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-2xl focus:outline-none focus:ring-2 focus:ring-green-800 focus:ring-opacity-80" />
            </div>
            {{-- Email --}}
            <div class="flex flex-col w-full lg:w-1/2">
                <label for="customer_email" class="text-gray-800 font-medium mb-2">{{ __('Email') }}</label>
                <input type="email" name="customer_email" id="customer_email" placeholder="{{ __('Your Email') }}"
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-2xl focus:outline-none focus:ring-2 focus:ring-green-800 focus:ring-opacity-80" />
            </div>
        </div>
        <div class="flex flex-col lg:flex-row lg:gap-4 mb-4">
            {{-- Mobile Number --}}
            <div class="flex flex-col w-full lg:w-1/2 mb-4 lg:mb-0">
                <label for="customer_phone" class="text-gray-800 font-medium mb-2">{{ __('Mobile Number') }}</label>
                <input type="tel" name="customer_phone" id="customer_phone"
                    placeholder="{{ __('Your Mobile Number') }}"
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-2xl focus:outline-none focus:ring-2 focus:ring-green-800 focus:ring-opacity-80" />
            </div>
            {{-- No. of Person(s) --}}
            <div class="flex flex-col w-full lg:w-1/2">
                <label for="no_of_persons" class="text-gray-800 font-medium mb-2">{{ __('No. of Person(s)') }}</label>
                <input type="number" name="no_of_persons" id="no_of_persons" value="1" step="1"
                    placeholder="{{ __('No. of Person(s)') }}"
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-2xl focus:outline-none focus:ring-2 focus:ring-green-800 focus:ring-opacity-80" />
            </div>
        </div>
        {{-- Address --}}
        <div class="flex flex-col mb-4">
            <label for="customer_address" class="text-gray-800 font-medium mb-2">{{ __('Address') }}</label>
            <textarea name="customer_address" id="customer_address" placeholder="{{ __('Your Address') }}" rows="3"
                class="w-full px-4 py-2.5 border border-gray-300 rounded-2xl focus:outline-none focus:ring-2 focus:ring-green-800 focus:ring-opacity-80"></textarea>
        </div>
        {{-- Notes --}}
        <div class="flex flex-col mb-4">
            <label for="customer_message" class="text-gray-800 font-medium mb-2">{{ __('Notes') }}</label>
            <textarea name="customer_notes" id="customer_notes" placeholder="{{ __('Your Message') }}" rows="3"
                class="w-full px-4 py-2.5 border border-gray-300 rounded-2xl focus:outline-none focus:ring-2 focus:ring-green-800 focus:ring-opacity-80"></textarea>
        </div>
        {{-- Service Start Datetime --}}
        <div class="flex flex-col mb-4">
            {{-- Date --}}
            <label for="service_start_date"
                class="text-gray-800 font-medium mb-2">{{ __('Service Start DateTime') }}</label>
            <div class="flex flex-row gap-4">
                <div class="flex flex-col w-1/2">
                    <input type="text" id="service_start_date" name="service_start_date"
                        value="{{ $service_booking_details->service_booking_start_date ?? '' }}"
                        placeholder="{{ __('Service Start Date') }}"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-2xl focus:outline-none focus:ring-2 focus:ring-green-800 focus:ring-opacity-80" />
                </div>
                {{-- Time --}}
                <div class="flex flex-col w-1/2">
                    <input type="time" name="service_start_time" id="service_start_time"
                        value="{{ $service_booking_details->service_booking_start_time ?? '' }}"
                        placeholder="{{ __('Service Start Time') }}"
                        class="timepicker w-full px-4 py-2.5 border border-gray-300 rounded-2xl focus:outline-none focus:ring-2 focus:ring-green-800 focus:ring-opacity-80" />
                </div>
            </div>
        </div>
        {{-- Service End Datetime --}}
        <div class="flex flex-col mb-4">
            {{-- Date --}}
            <label for="service_end_date"
                class="text-gray-800 font-medium mb-2">{{ __('Service End DateTime') }}</label>
            <div class="flex flex-row gap-4">
                <div class="flex flex-col w-1/2">
                    <input type="date" id="service_end_date" name="service_end_date"
                        placeholder="{{ __('Service End Date') }}"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-2xl focus:outline-none focus:ring-2 focus:ring-green-800 focus:ring-opacity-80" />
                </div>
                {{-- Time --}}
                <div class="flex flex-col w-1/2">
                    <input type="time" name="service_end_time" id="service_end_time"
                        placeholder="{{ __('Service End Time') }}"
                        class="timepicker w-full px-4 py-2.5 border border-gray-300 rounded-2xl focus:outline-none focus:ring-2 focus:ring-green-800 focus:ring-opacity-80" />
                </div>
            </div>
        </div>
        <div class="flex flex-col">
            <button onclick="submitServiceBooking()"
                class="w-full px-4 py-3 bg-green-800 text-white text-xl font-medium focus:outline-none rounded-2xl">
                {{ __('Submit') }}
            </button>
        </div>
    </div>
</div>
