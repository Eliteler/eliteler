@php
    use App\Setting;

    $setting = Setting::where('status', 1)->first();
@endphp

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>{{ __($title) }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    {{-- Tailwind CSS --}}
    <link rel="stylesheet" href="{{ asset('css/tailwind.min.css') }}">
    {{-- Favicon --}}
    <link rel="shortcut icon" href="{{ asset($setting->favicon) }}" type="image/png">
    {{-- Tabler Icons --}}
    <link rel="stylesheet" href="https://unpkg.com/@tabler/icons/iconfont/tabler-icons.css">
</head>

<body class="bg-gray-100 flex items-center justify-center min-h-screen">

    <div class="bg-white rounded-lg shadow-lg p-8 w-full max-w-2xl text-center mx-4 lg:mx-auto">
        <!-- Success Icon -->
        <div class="flex justify-center mb-6">
            <div class="bg-green-100 rounded-full p-4">
                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    class="icon icon-tabler icons-tabler-outline icon-tabler-circle-dashed-check text-green-600">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                    <path d="M8.56 3.69a9 9 0 0 0 -2.92 1.95" />
                    <path d="M3.69 8.56a9 9 0 0 0 -.69 3.44" />
                    <path d="M3.69 15.44a9 9 0 0 0 1.95 2.92" />
                    <path d="M8.56 20.31a9 9 0 0 0 3.44 .69" />
                    <path d="M15.44 20.31a9 9 0 0 0 2.92 -1.95" />
                    <path d="M20.31 15.44a9 9 0 0 0 .69 -3.44" />
                    <path d="M20.31 8.56a9 9 0 0 0 -1.95 -2.92" />
                    <path d="M15.44 3.69a9 9 0 0 0 -3.44 -.69" />
                    <path d="M9 12l2 2l4 -4" />
                </svg>
            </div>
        </div>

        <!-- Messages -->
        <h2 class="text-2xl font-semibold text-gray-800 mb-2">{{ __($messageOne) }}</h2>
        <p class="text-gray-600 text-sm mb-6">{{ __($messageTwo) }}</p>

        {{-- Timer --}}
        <div class="flex items-center justify-center mb-6">
            <div class="text-red-500 text-sm font-semibold">
                {{ __('You will be redirected in') }}
                <span id="countdown" class="font-semibold">5</span>
                {{ __('seconds') }}
            </div>
        </div>

        <!-- WhatsApp Button -->
        <button id="sendWhatsapp"
            class="px-6 py-2 bg-green-500 hover:bg-green-600 text-white font-medium rounded-md transition duration-300">
            {{ __($messageThree) }}
        </button>
        {{-- Back to Home --}}
        <a href="{{ back()->getTargetUrl() }}"
            class="mt-4 inline-block px-6 py-2 bg-gray-200 text-gray-800 text-md font-medium rounded-md hover:bg-gray-300 transition duration-200">
            &larr; {{ __('Back to Vcard') }}
        </a>
    </div>

    <script>
        document.getElementById('sendWhatsapp')?.addEventListener('click', function() {
            window.open(@json($whatsapp_url), '_blank');
        });

        // 10 seconds timer to auto change the message
        let countDown = 4;

        const timer = setInterval(function() {
            if (countDown <= 0) {
                clearInterval(timer);
                button.click(); // Auto-click the button when countdown ends
                return;
            }

            countdown.innerHTML = countDown;
            countDown--;
        }, 1000);

        // Optional: Close current tab or redirect back after a few seconds
        setTimeout(() => {
            window.location.href = document.referrer || '/';
        }, 5000);
    </script>
</body>

</html>
