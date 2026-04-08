<div class="relative">
    {{-- Check appointment slots in the calendar --}}
    @if ($plan_details['appointment'] == 1)
        @if ($appointment_slots != null)
            <h2 class="text-3xl lg:text-4xl font-bold py-12 text-center relative custom-head">
                {{ __('Appointment') }}
            </h2>
            <img src="{{ url('img/templates/modern/leaf-5.png') }}" alt=""
                class="w-28 absolute -top-2 -left-12 -rotate-12 animate-move-y" />

            <div class="border border-green-600 p-6 bg-green-100 rounded-2xl">
                <!-- Error Message (hidden by default) -->
                <div id="errorMessage" class="text-red-800 text-md mb-2 hidden font-medium">
                    {{ __('Please select a valid date and time slot.') }}</div>

                {{-- Success Message (hidden by default) --}}
                <div id="successMessage" class="text-green-800 text-md mb-2 hidden font-medium">
                    {{ __('Appointment booked successfully!') }}</div>

                <!-- Error Message (hidden by default) -->
                <div id="errorSubmitMessage" class="text-red-800 text-md mb-2 hidden font-medium">
                    {{ __('Please fill all the fields.') }}</div>

                <div class="flex flex-col md:flex-row justify-between mb-6 space-y-2 md:space-y-0 md:gap-4">
                    <!-- flatpickr Calendar -->
                    <input type="text" id="appointment-date"
                        class="flatpickr-input md:w-1/2 rounded-2xl w-full px-4 py-2.5 text-black border border-gray-300 focus:outline-none focus:ring-2 focus:ring-green-800 focus:ring-opacity-80"
                        placeholder="{{ __('Select a date') }}" required />
                    <!-- Select time in dropdown -->
                    <select id="time-slot-select"
                        class="md:w-1/2 rounded-2xl w-full px-4 py-2.5 text-black border border-gray-300 focus:outline-none focus:ring-2 focus:ring-green-800 focus:ring-opacity-80"
                        required>
                        <option value="">{{ __('Select a time slot') }}
                        </option>
                    </select>
                </div>

                <!-- Booking button -->
                <div class="flex justify-center">
                    <button id="add-slot-button"
                        class="w-full p-2.5 bg-green-800 rounded-2xl text-white text-lg text-center font-medium"
                        onclick="validateAndShowModal()">
                        {{ __('Book Appointment') }}
                    </button>
                </div>
            </div>
        @endif
    @endif
</div>
