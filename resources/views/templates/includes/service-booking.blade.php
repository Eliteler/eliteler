@php
    if (isset($service_booking_details) && $service_booking_details->service_booking == 1) {
        $service_booking_available_days = json_decode($service_booking_details->service_booking_available_days);
    }
@endphp

<div class="py-5 lg:py-8 border-b">

    @php
        switch ($business_card_details->theme_id) {
            case '588969111094':
            case '588969111095':
                $head = '<div class="bg-custom w-full mt-4 mb-4 flex justify-center align-middle py-2">
                            <p class="heading font-black text-white text-xl px-4 py-2">' . __($service_booking_details->title) . '</p>
                        </div>';
                break;

            case '588969111086':
            case '588969111087':
            case '588969111088':
            case '588969111089':
            case '588969111090':
            case '588969111091':
            case '588969111092':
            case '588969111093':
                $head = '<div class="' . $head_style . '">
                            <p class="heading font-black text-white text-xl px-4 py-2">' . __($service_booking_details->title) . '</p>
                        </div>';
                break;

            case '588969110990':
                $head = '<h2 class="' . $head_style . '">'
                            . __($service_booking_details->title) .
                        '</h2>';
                break;
            
            default:
                $head = '<h2 class="' . $head_style . '">'
                            . __($service_booking_details->title) .
                        '</h2>';
                break;
        }
    @endphp

    {{-- Heading --}}
    {!! $head !!}

    {{-- Service Booking Form --}}
    <div class="{{ in_array($business_card_details->theme_id, [
            '588969111147',
            '588969110990',
            '588969110991',
            '588969110992',
            '588969110993',
            '588969110994',
            '588969110995',
            '588969110996',
            '588969110997',
            '588969110998',
            '588969110999',
            '588969111000',
            '588969111001',
            '588969111002',
            '588969111003',
            '588969111004',
            '588969111005',
            '588969111006',
            '588969111007',
            '588969111008',
            '588969111009',
            '588969111010',
            '588969111011',
            '588969111012',
            '588969111013'
        ]) ? '' : 'px-4' }} pt-4">
        
        <!-- Error Message (hidden by default) -->
        <div id="errorMessage1" class="bg-red-500 text-sm mt-1 mb-4 hidden p-3 text-white rounded"></div>

        {{-- Success Message (hidden by default) --}}
        <div id="successMessage1" class="bg-green-500 text-sm mb-5 hidden p-3 text-white rounded"></div>

        <div class="flex flex-col lg:flex-row lg:gap-4 mb-4">
            {{-- Name --}}
            <div class="flex flex-col w-full lg:w-1/2 mb-4 lg:mb-0">
                <label for="customer_name" class="{{ $text_color }} font-medium mb-2">{{ __('Name') }}</label>
                <input type="text" name="customer_name" id="customer_name" placeholder="{{ __('Your Name') }}"
                    class="{{ $input_style }}" />
            </div>
            {{-- Email --}}
            <div class="flex flex-col w-full lg:w-1/2">
                <label for="customer_email" class="{{ $text_color }} font-medium mb-2">{{ __('Email') }}</label>
                <input type="email" name="customer_email" id="customer_email" placeholder="{{ __('Your Email') }}"
                    class="{{ $input_style }}" />
            </div>
        </div>
        <div class="flex flex-col lg:flex-row lg:gap-4 mb-4">
            {{-- Mobile Number --}}
            <div class="flex flex-col w-full lg:w-1/2 mb-4 lg:mb-0">
                <label for="customer_phone" class="{{ $text_color }} font-medium mb-2">{{ __('Mobile Number') }}</label>
                <input type="tel" name="customer_phone" id="customer_phone"
                    placeholder="{{ __('Your Mobile Number') }}"
                    class="{{ $input_style }}" />
            </div>
            {{-- No. of Person(s) --}}
            <div class="flex flex-col w-full lg:w-1/2">
                <label for="no_of_persons" class="{{ $text_color }} font-medium mb-2">{{ __('No. of Person(s)') }}</label>
                <input type="number" name="no_of_persons" id="no_of_persons" value="1" step="1"
                    placeholder="{{ __('No. of Person(s)') }}"
                    class="{{ $input_style }}" />
            </div>
        </div>
        {{-- Address --}}
        <div class="flex flex-col mb-4">
            <label for="customer_address" class="{{ $text_color }} font-medium mb-2">{{ __('Address') }}</label>
            <textarea name="customer_address" id="customer_address" placeholder="{{ __('Your Address') }}" rows="3"
                class="{{ $input_style }}"></textarea>
        </div>
        {{-- Notes --}}
        <div class="flex flex-col mb-4">
            <label for="customer_message" class="{{ $text_color }} font-medium mb-2">{{ __('Notes') }}</label>
            <textarea name="customer_notes" id="customer_notes" placeholder="{{ __('Your Message') }}" rows="3"
                class="{{ $input_style }}"></textarea>
        </div>
        {{-- Service Start Datetime --}}
        <div class="flex flex-col mb-4">
            {{-- Date --}}
            <label for="service_start_date"
                class="{{ $text_color }} font-medium mb-2">{{ __('Service Start DateTime') }}</label>
            <div class="flex flex-row gap-4">
                <div class="flex flex-col w-1/2">
                    <input type="text" id="service_start_date" name="service_start_date"
                        value="{{ $service_booking_details->service_booking_start_date ?? '' }}"
                        placeholder="{{ __('Service Start Date') }}"
                        class="{{ $input_style }}" />
                </div>
                {{-- Time --}}
                <div class="flex flex-col w-1/2">
                    <input type="time" name="service_start_time" id="service_start_time"
                        value="{{ $service_booking_details->service_booking_start_time ?? '' }}"
                        placeholder="{{ __('Service Start Time') }}"
                        class="{{ $input_style }} timepicker" />
                </div>
            </div>
        </div>
        {{-- Service End Datetime --}}
        <div class="flex flex-col mb-4">
            {{-- Date --}}
            <label for="service_end_date"
                class="{{ $text_color }} font-medium mb-2">{{ __('Service End DateTime') }}</label>
            <div class="flex flex-row gap-4">
                <div class="flex flex-col w-1/2">
                    <input type="date" id="service_end_date" name="service_end_date"
                        placeholder="{{ __('Service End Date') }}"
                        class="{{ $input_style }}" />
                </div>
                {{-- Time --}}
                <div class="flex flex-col w-1/2">
                    <input type="time" name="service_end_time" id="service_end_time"
                        placeholder="{{ __('Service End Time') }}"
                        class="{{ $input_style }} timepicker" />
                </div>
            </div>
        </div>
        <div class="flex flex-col">
            <button onclick="submitServiceBooking()"
                class="{{ $btn_style }}">
                {{ __('Submit') }}
            </button>
        </div>
    </div>
</div>  
