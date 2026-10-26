<!DOCTYPE html>
<html @class(['dark' => ($appearance ?? 'system') == 'dark'])>

@php
    $settings = DB::table('settings')->first();
@endphp

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    {{-- <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests"> --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name') }}</title>

    <link rel="icon" href="{{ asset($settings->favicon) }}" sizes="96x96" type="image/png" />

    {{-- Inline script to detect system dark mode preference and apply it immediately --}}
    <script>
        (function() {
            const appearance = '{{ $appearance ?? 'system' }}';

            if (appearance === 'system') {
                const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

                if (prefersDark) {
                    document.documentElement.classList.add('dark');
                }
            }
        })();
    </script>

    <title inertia>{{ config('app.name') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    {{-- Same font as the admin panel --}}
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300..700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/all.css') }}" />

    @if (isset($settings) && $settings)
        {{-- Check Google Analytics is "enabled" --}}
        @if (
            !empty($settings->google_analytics_id) &&
                (Cookie::get('laravel_cookie_consent') === '1' || env('COOKIE_CONSENT_ENABLED') == ''))
            <!-- Global site tag (gtag.js) - Google Analytics -->
            <script async src="https://www.googletagmanager.com/gtag/js?id={{ $settings->google_analytics_id }}"></script>
            <script>
                window.dataLayer = window.dataLayer || [];

                function gtag() {
                    dataLayer.push(arguments);
                }
                gtag('js', new Date());

                gtag('config', '{{ $settings->google_analytics_id }}');
            </script>
        @endif

        @if (
            $settings->google_adsense_code != 'DISABLE_ADSENSE_ONLY' &&
                (Cookie::get('laravel_cookie_consent') === '1' || env('COOKIE_CONSENT_ENABLED') == ''))
            {{-- AdSense code --}}
            <script async
                src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client={{ $settings->google_adsense_code }}"
                crossorigin="anonymous"></script>
        @endif
    @endif

    @viteReactRefresh
    @routes
    @vite('plugins/ModernDashboard/Views/js/app.tsx')
    @inertiaHead
</head>

<body class="antialiased">
    <div id="boot-loader" class="boot-loader">
        <div class="boot-spinner"></div>
    </div>
    @inertia
</body>

</html>
