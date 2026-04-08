@php
    // Page content
    use Illuminate\Support\Facades\DB;
    use App\Services\GoBizCommonService;

    $config = GoBizCommonService::config();
    $supportPage = GoBizCommonService::templatePageContentGet('contact', 'GoBizOriginal');

    // Default
    $navbar = true;
    $footer = true;

    if ($config[38]->config_value == 'no') {
        $footer = false;
    }
@endphp

@extends('GoBizOriginal::Website.layouts.index', ['nav' => $navbar, 'banner' => false, 'footer' => $footer, 'cookie' => false, 'setting' => true, 'title' => true, 'title' => 'Two Factor Authentication'])

@section('custom-script')
    <link rel="icon" href="{{ asset($settings->favicon) }}" sizes="96x96" type="image/png" />
    <style>
        /* Hide spinners for number inputs */
        input[type="number"]::-webkit-inner-spin-button,
        input[type="number"]::-webkit-outer-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        /* For Firefox */
        input[type="number"] {
            -moz-appearance: textfield;
        }
    </style>
@endsection

@section('content')
    {{-- Confirm page --}}
    <section class="pt-12 lg:pb-20 overflow-hidden">
        <div class="container mx-auto px-4">
            <div class="flex flex-wrap items-center -m-6">
                <div class="w-full md:w-1/2 p-6 lg:block hidden">
                    <div class="p-1 mx-auto max-w-max overflow-hidden rounded-full">
                        <img class="object-cover rounded-full" src="{{ asset($config[13]->config_value) }}"
                            alt="{{ $config[0]->config_value }}">
                    </div>
                </div>
                <div class="w-full md:w-1/2 p-6">
                    <div class="md:max-w-md">
                        <h2 class="mb-8 font-heading font-bold text-6xl sm:text-7xl">{{ __('Two Factor Authentication') }}
                        </h2>
                        <p class="mb-4 text-lg">{{ __('Please enter the OTP that received in your email.') }}</p>
                        @if (session('success'))
                            <p class="invalid-feedback mb-4 text-green-500" role="alert">
                                <strong>{{ session('success') }}</strong>
                            </p>
                        @endif
                        @if (session('message'))
                            <p class="invalid-feedback mb-4 text-red-500" role="alert">
                                <strong>{{ session('message') }}</strong>
                            </p>
                        @endif
                        <form method="POST" action="{{ route('verify.two-factor-authentication') }}">
                            @csrf
                            <div class="flex flex-wrap -m-2 mb-6">
                                <div class="w-full p-2">
                                    <p class="mb-2.5 font-medium text-base">{{ __('OTP (One Time Password)') }}</p>
                                    <div class="flex space-x-2">
                                        <input type="number" min="0" max="9" name="number_1"
                                            class="w-12 h-12 text-center text-xl font-bold border-2 border-{{ $config[11]->config_value }}-500 rounded focus:outline-none focus:ring-2 focus:ring-{{ $config[11]->config_value }}-400"
                                            oninput="moveNext(this)" onkeydown="moveBack(event, this)" aria-label="Digit 1">
                                        <input type="number" min="0" max="9" name="number_2"
                                            class="w-12 h-12 text-center text-xl font-bold border-2 border-{{ $config[11]->config_value }}-500 rounded focus:outline-none focus:ring-2 focus:ring-{{ $config[11]->config_value }}-400"
                                            oninput="moveNext(this)" onkeydown="moveBack(event, this)" aria-label="Digit 2">
                                        <input type="number" min="0" max="9" name="number_3"
                                            class="w-12 h-12 text-center text-xl font-bold border-2 border-{{ $config[11]->config_value }}-500 rounded focus:outline-none focus:ring-2 focus:ring-{{ $config[11]->config_value }}-400"
                                            oninput="moveNext(this)" onkeydown="moveBack(event, this)" aria-label="Digit 3">
                                        <input type="number" min="0" max="9" name="number_4"
                                            class="w-12 h-12 text-center text-xl font-bold border-2 border-{{ $config[11]->config_value }}-500 rounded focus:outline-none focus:ring-2 focus:ring-{{ $config[11]->config_value }}-400"
                                            oninput="moveNext(this)" onkeydown="moveBack(event, this)" aria-label="Digit 4">
                                        <input type="number" min="0" max="9" name="number_5"
                                            class="w-12 h-12 text-center text-xl font-bold border-2 border-{{ $config[11]->config_value }}-500 rounded focus:outline-none focus:ring-2 focus:ring-{{ $config[11]->config_value }}-400"
                                            oninput="moveNext(this)" onkeydown="moveBack(event, this)" aria-label="Digit 5">
                                        <input type="number" min="0" max="9" name="number_6"
                                            class="w-12 h-12 text-center text-xl font-bold border-2 border-{{ $config[11]->config_value }}-500 rounded focus:outline-none focus:ring-2 focus:ring-{{ $config[11]->config_value }}-400"
                                            oninput="moveNext(this)" onkeydown="moveBack(event, this)" aria-label="Digit 6">
                                    </div>
                                    @if (session('error'))
                                        <span class="invalid-feedback mx-2 text-red-500" role="alert">
                                            <strong>{{ session('error') }}</strong>
                                        </span>
                                    @endif
                                </div>
                                <div class="w-full p-2">
                                    <p id="resend">{{ __('Resend OTP') }}<span class="px-2 text-red-500" id="timer">00:30</span>
                                    </p>
                                </div>
                            </div>


                            <div class="group relative md:max-w-max mb-5">
                                <div
                                    class="absolute top-0 left-0 w-full h-full bg-gradient-to-r from-{{ $config[11]->config_value }}-400 to-{{ $config[11]->config_value }}-500 opacity-0 group-hover:opacity-50 rounded-full transition ease-out duration-300">
                                </div>
                                <button
                                    class="p-1 w-full font-heading font-semibold text-xs text-white uppercase tracking-px overflow-hidden rounded-full">
                                    <div
                                        class="relative py-5 px-14 bg-gradient-to-r from-{{ $config[11]->config_value }}-400 to-{{ $config[11]->config_value }}-500 overflow-hidden rounded-full">
                                        <div
                                            class="absolute top-0 left-0 transform -translate-y-full group-hover:-translate-y-0 h-full w-full bg-white transition ease-in-out duration-500">
                                        </div>
                                        <p class="relative z-10 group-hover:text-gray-900">{{ __('Confirm OTP') }}</p>
                                    </div>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

@section('custom-js')
    <script>
        let timeLeft = 30;
        const timerElement = document.getElementById("timer");
        const resendElement = document.getElementById("resend");

        function updateTimer() {
            if (timeLeft > 0) {
                timeLeft--;
                timerElement.firstChild.textContent = `00:${timeLeft < 10 ? "0" + timeLeft : timeLeft} `;
                setTimeout(updateTimer, 1000);
            } else {
                resendElement.innerHTML = `<a href="{{ route('send.otp') }}" class="text-blue-500">Resend OTP</a>`;
            }
        }

        updateTimer();

        function moveNext(input) {
            if (input.value.length > 1) {
                input.value = input.value.slice(0, 1); // Restrict to one digit
            }
            if (input.value.length === 1) {
                let next = input.nextElementSibling;
                if (next) next.focus();
            }
        }

        function moveBack(event, input) {
            if (event.key === "Backspace" && input.value === "") {
                let prev = input.previousElementSibling;
                if (prev) prev.focus();
            }
        }
    </script>
@endsection
@endsection
