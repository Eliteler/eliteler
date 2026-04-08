<div class="relative">
    <h2 class="text-3xl lg:text-4xl font-bold py-12 text-center relative custom-head">
        {{ __('Contact Us') }}
    </h2>
    <img src="{{ url('img/templates/modern/leaf-8.png') }}" alt=""
        class="w-28 absolute top-2 -right-12 rotate-12 animate-move-y" />
    {{-- Message Alert --}}
    @if (Session::has('message'))
        <div class="px-6 py-4 bg-green-800 shadow-md mb-6">
            <div class="flex items-start">
                <div class="mr-4">
                    <svg class="w-6 h-6 text-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                        fill="currentColor">
                        <path
                            d="M2.93 17.07A10 10 0 1 1 17.07 2.93 10 10 0 0 1 2.93 17.07zm12.73-1.41A8 8 0 1 0 4.34 4.34a8 8 0 0 0 11.32 11.32zM9 11V9h2v6H9v-4zm0-6h2v2H9V5z" />
                    </svg>
                </div>
                <div>
                    <p class="font-medium text-white">
                        {{ Session::get('message') }}</p>
                    <p class="text-sm text-white">
                        {{ __('Please wait for the reply to be sent.') }}</p>
                </div>
            </div>
        </div>
    @endif

    {{-- Contact Form --}}
    <form class="w-full bg-green-100 border border-green-600 p-6 rounded-2xl"
        action="{{ config('app.url') }}/sent-enquiry" method="POST">
        @csrf
        <input type="hidden" name="card_id" value="{{ $business_card_details->card_id }}" />
        <div class="flex flex-col lg:flex-row gap-4 mb-2">
            <div class="flex flex-col w-full lg:w-1/2">
                <label for="name" class="text-gray-800 font-medium mb-2">{{ __('Name') }}</label>
                <input type="text" name="name" placeholder="{{ __('Your Name') }}"
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-2xl focus:outline-none focus:ring-2 focus:ring-green-800 focus:ring-opacity-80"
                    required />
            </div>
            <div class="flex flex-col w-full lg:w-1/2">
                <label for="email" class="text-gray-800 font-medium mb-2">{{ __('Email') }}</label>
                <input type="email" name="email" placeholder="{{ __('Your Email') }}"
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-2xl focus:outline-none focus:ring-2 focus:ring-green-800 focus:ring-opacity-80"
                    required />
            </div>
        </div>
        <div class="flex flex-col mb-2">
            <label for="phone" class="text-gray-800 font-medium mb-2">{{ __('Mobile Number') }}</label>
            <input type="tel" name="phone" placeholder="{{ __('Your Mobile Number') }}"
                class="w-full px-4 py-2.5 border border-gray-300 rounded-2xl focus:outline-none focus:ring-2 focus:ring-green-800 focus:ring-opacity-80"
                required />
        </div>
        <div class="flex flex-col mb-4">
            <label for="message" class="text-gray-800 font-medium mb-2">{{ __('Message') }}</label>
            <textarea name="message" placeholder="{{ __('Your Message') }}" rows="5"
                class="w-full px-4 py-2.5 border border-gray-300 focus:outline-none rounded-2xl focus:ring-2 focus:ring-green-800 focus:ring-opacity-80"
                required></textarea>
        </div>

        {{-- ReCaptcha --}}
        @include('templates.includes.recaptcha', [
            'recaptchaId' => 'recaptcha-one',
        ])

        <div class="flex flex-col ">
            <button type="submit"
                class="w-full px-4 py-2.5 bg-green-800 text-white text-lg font-medium focus:outline-none rounded-2xl">
                {{ __('Send') }}
            </button>
        </div>
    </form>
</div>
