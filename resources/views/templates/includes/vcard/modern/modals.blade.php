<!-- Start Apointment Modal (By default hidden) -->
<div id="appointmentModal" class="fixed inset-0 bg-gray-800 bg-opacity-50 flex items-center justify-center z-50 hidden">
    <!-- Modal Content -->
    <div class="bg-white rounded-2xl w-full max-w-md p-6 mx-4 shadow-lg">
        <!-- Modal Header -->
        <div class="flex justify-center items-center mb-4">
            <h2 class="text-xl font-bold text-gray-800">{{ __('Book Appointment') }}</h2>
        </div>

        <!-- Appointment Form -->
        <form id="appointmentForm">
            <!-- Name Field -->
            <div class="mb-4">
                <label for="name" class="block text-sm font-medium text-gray-700">{{ __('Name') }}</label>
                <input type="text" id="name"
                    class="w-full mt-1 p-2.5 rounded-2xl text-black border border-gray-300 focus:outline-none focus:ring-2 focus:ring-green-800 focus:ring-opacity-80"
                    required>
            </div>

            <!-- Email Field -->
            <div class="mb-4">
                <label for="email" class="block text-sm font-medium text-gray-700">{{ __('Email') }}</label>
                <input type="email" id="email"
                    class="w-full mt-1 p-2.5 rounded-2xl text-black border border-gray-300 focus:outline-none focus:ring-2 focus:ring-green-800 focus:ring-opacity-80"
                    required>
            </div>

            <!-- Phone Field -->
            <div class="mb-4">
                <label for="phone" class="block text-sm font-medium text-gray-700">{{ __('Phone') }}</label>
                <input type="text" id="phone"
                    class="w-full mt-1 p-2.5 rounded-2xl text-black border border-gray-300 focus:outline-none focus:ring-2 focus:ring-green-800 focus:ring-opacity-80"
                    required>
            </div>

            <!-- Notes Field -->
            <div class="mb-4">
                <label for="notes" class="block text-sm font-medium text-gray-700">{{ __('Notes') }}</label>
                <textarea id="notes"
                    class="w-full mt-1 p-2.5 rounded-2xl text-black border border-gray-300 focus:outline-none focus:ring-2 focus:ring-green-800 focus:ring-opacity-80"
                    rows="3"></textarea>
            </div>

            <!-- Hidden Price Field -->
            <div class="mb-4 hidden">
                <label for="price" class="block text-sm font-medium text-gray-700">{{ __('Price') }}</label>
                <input type="text" id="price"
                    class="w-full mt-1 p-2.5 rounded-2xl text-black border border-gray-300 focus:outline-none focus:ring-2 focus:ring-green-800 focus:ring-opacity-80"
                    disabled>
            </div>

            {{-- ReCaptcha --}}
            @include('templates.includes.recaptcha', ['recaptchaId' => 'recaptcha-two'])

            <!-- Submit and Close Buttons -->
            <div class="flex justify-between">
                <button type="button" class="bg-gray-500 text-white px-4 py-2.5 rounded-2xl"
                    onclick="validateAndShowModal()">
                    {{ __('Close') }}
                </button>
                <button type="submit" id="bookAppointmentButton"
                    class="bg-green-800 text-white px-4 py-2.5 rounded-2xl">
                    {{ __('Submit') }}
                </button>
            </div>
        </form>
    </div>
</div>
{{-- End Appointment Modal --}}

<!-- Start Share Modal -->
<div id="shareModal" class="fixed inset-0 bg-gray-800 bg-opacity-50 flex items-center justify-center hidden z-50"
    onclick="shareToggleModal(false)">
    <!-- Modal content -->
    <div class="bg-white rounded-2xl w-full max-w-md p-6 mx-4 space-y-6" onclick="event.stopPropagation()">
        <!-- Modal header -->
        <div class="flex justify-center items-center">
            <h2 class="text-2xl text-center font-bold">{{ __('Share on') }}</h2>
        </div>

        <!-- QR Code Section -->
        <div class="flex justify-center">
            <canvas id="shareQrCode"></canvas>
        </div>

        <!-- Share via Social Media -->
        <div class="flex justify-around text-green-800">
            <a href="{{ $shareComponent['facebook'] }}" target="_blank">
                <i class="fab fa-facebook fa-2x"></i>
            </a>
            <a href="{{ $shareComponent['twitter'] }}" target="_blank">
                <i class="fab fa-twitter fa-2x"></i>
            </a>
            <a href="{{ $shareComponent['linkedin'] }}" target="_blank">
                <i class="fab fa-linkedin fa-2x"></i>
            </a>
            <a href="{{ $shareComponent['whatsapp'] }}" target="_blank">
                <i class="fab fa-whatsapp fa-2x"></i>
            </a>
            <a href="{{ $shareComponent['telegram'] }}" target="_blank">
                <i class="fab fa-telegram fa-2x"></i>
            </a>
        </div>

        <!-- Copy Link Section -->
        <div class="flex justify-center">
            <button onclick="copyLink()" class="bg-green-800 text-white font-medium py-2.5 px-4 rounded-2xl w-full">
                {{ __('Copy Link') }}
            </button>
        </div>
    </div>
</div>
{{-- End Share Modal --}}

<!-- Start WhatsApp Modal -->
<div id="whatsappModal" class="fixed inset-0 bg-gray-800 bg-opacity-60 flex items-center justify-center hidden z-50"
    onclick="toggleWhatsAppModal(false)">
    <!-- Modal content (stops propagation to prevent closing when clicking inside) -->
    <div class="rounded-2xl w-full max-w-md p-6 mx-4 space-y-4 bg-white" onclick="event.stopPropagation()">
        <!-- Input for WhatsApp number -->
        <div>
            <label for="whatsappNumber"
                class="block text-gray-700 font-medium">{{ __('Enter WhatsApp Number') }}:</label>
            <input type="text" id="whatsappNumber" placeholder="{{ __('e.g., +919876543210') }}"
                class="w-full mt-1 px-4 py-2.5 border border-gray-300 rounded-2xl focus:outline-none focus:ring-2 focus:ring-green-800 ring-opacity-80" />
        </div>

        <!-- Submit Button -->
        <div class="flex justify-center">
            <button onclick="sendMessage()"
                class="bg-green-800 text-white font-medium text-lg py-2.5 px-4 rounded-2xl w-full">
                {{ __('Send') }}
            </button>
        </div>
    </div>
</div>
<!-- End Whatsapp Modal -->

<!-- Start Scan QR Code Modal -->
<div id="scanModal"
    class="fixed inset-0 bg-gray-800 bg-opacity-50 flex items-center justify-center hidden z-50 qr-modal"
    onclick="toggleScanModal(false)">
    <!-- Modal content (stops propagation to prevent closing when clicking inside) -->
    <div class="rounded-2xl w-full max-w-md p-6 mx-4 space-y-6 bg-white qr-modal-overlay"
        onclick="event.stopPropagation()">
        <!-- Qr Code -->
        <div class="flex justify-center flex-col items-center">
            <div class="qr-code mb-2"></div>
        </div>

        <!-- Submit Button -->
        <div class="flex justify-center">
            <button id="download"
                onclick="downloadQr('{{ config('app.url') . route('dynamic.card', $business_card_details->card_id, false) }}', 500)"
                class="bg-green-100 border border-green-600 font-bold p-3 rounded-2xl">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                    stroke-linejoin="round"
                    class="icon icon-tabler icons-tabler-outline icon-tabler-download text-green-800 h-6 w-6">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                    <path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2 -2v-2" />
                    <path d="M7 11l5 5l5 -5" />
                    <path d="M12 4l0 12" />
                </svg>
            </button>
        </div>
    </div>
</div>
<!-- End Scan QR Code Modal -->

{{-- Start Check password protected Modal --}}
@if ($business_card_details->password != null && Session::get('password_protected') == false)
    <div class="p-4 flex items-center justify-center">
        <div x-data="{ showModal: true }">
            <!-- Modal -->
            <div x-show="showModal" class="fixed inset-0 flex items-center justify-center z-50 p-3">
                <div class="bg-white p-6 w-96 max-w-full shadow-lg transform transition-all duration-300 rounded-2xl"
                    x-show.transition.opacity="showModal">
                    <!-- Modal Header -->
                    <div class="flex justify-between items-center border-b-2 border-gray-200 pb-4">
                        <h2 class="text-2xl font-semibold">{{ __('Password Protected') }}</h2>
                    </div>

                    <!-- Modal Content -->
                    <div class="mt-4 space-y-4">
                        <form action="{{ config('app.url') }}/check-password/{{ $business_card_details->card_id }}"
                            method="post">
                            @csrf
                            <p class="text-lg text-gray-900">{{ __('Enter your vcard Password') }}</p>
                            <div class="flex">
                                <input type="password" name="password"
                                    class="w-full px-4 py-2.5 border border-gray-300 rounded-2xl focus:outline-none focus:ring-2 focus:ring-green-800 ring-opacity-80"
                                    placeholder="{{ __('Password') }}" required autofocus>
                            </div>

                            {{-- Message --}}
                            @if (Session::has('message'))
                                <div class="flex items-center p-4 my-4 text-sm bg-gray-100" role="alert">
                                    <svg class="flex-shrink-0 inline w-4 h-4 mr-3 text-red-500" aria-hidden="true"
                                        xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                                        <path
                                            d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z" />
                                    </svg>
                                    <span class="sr-only">{{ __('Failed') }}</span>
                                    <div>
                                        <span class="font-medium text-red-500">{{ Session::get('message') }}</span>
                                    </div>
                                </div>
                            @endif

                            <div class="flex flex-col space-y-4 mt-3">
                                <button type="submit"
                                    class="bg-green-800 text-white rounded-2xl px-4 py-2.5 mt-2 transition duration-300">{{ __('Submit') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@else   
    <!-- Include PWA modal -->
    @if ($plan_details != null)
        {{-- Check PWA --}}
        @if ($plan_details['pwa'] == 1 && $business_card_details->is_enable_pwa == 1)
            @include('vendor.laravelpwa.new_pwa_modal', [
                'primary_color' => 'green',
                'img' => $business_card_details->profile,
            ])
        @endif
    @endif

    {{-- Include Newsletter Modal --}}
    @if ($business_card_details != null)
        {{-- Check Newsletter --}}
        @if (!empty($business_card_details->is_newsletter_pop_active) && $business_card_details->is_newsletter_pop_active == 1)
            @include('templates.includes.old_theme_newsletter_modal', [
                'primary_color' => 'green',
            ])
        @endif
    @endif

    {{-- Include Information Popup Modal --}}
    @if ($business_card_details != null)
        {{-- Check Information Popup --}}
        @if (!empty($business_card_details->is_info_pop_active) && $business_card_details->is_info_pop_active == 1)
            @include('templates.includes.old_theme_information_popup_modal', [
                'primary_color' => 'green',
            ])
        @endif
    @endif
@endif
{{-- End Check password protected Modal --}}
