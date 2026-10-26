@php
    use App\Support\Admin\AdminSharedProps;

    $settings = DB::table('settings')->first();
    $appearance = AdminSharedProps::appearance(Auth::user());
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ AdminSharedProps::isRtl() ? 'rtl' : 'ltr' }}"
    data-accent="{{ AdminSharedProps::ACCENT }}" @class(['dark' => $appearance === 'dark'])>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Accent colour picked in General settings is kept per browser; apply it before paint --}}
    <script>
        try {
            var accent = localStorage.getItem('gobiz.admin.accent');
            if (accent && /^[a-z]+$/.test(accent)) document.documentElement.dataset.accent = accent;
        } catch (e) {}
    </script>

    <title inertia>{{ config('app.name') }}</title>

    @if ($settings)
        <link rel="icon" href="{{ asset($settings->favicon) }}" sizes="96x96" type="image/png" />
    @endif

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300..700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flag-icons@6.6.6/css/flag-icons.min.css" />

    @routes
    {{ \Illuminate\Support\Facades\Vite::useHotFile(public_path('hot-admin'))->useBuildDirectory('build-admin')->reactRefresh() }}
    {{ \Illuminate\Support\Facades\Vite::useHotFile(public_path('hot-admin'))->useBuildDirectory('build-admin')->withEntryPoints(['resources/js/admin/app.tsx']) }}
    @inertiaHead

    {{-- CSS/JS from admin settings --}}
    @if ($settings)
        @php
            $custom_css = $settings->custom_css;
            $custom_js = $settings->custom_scripts;

            if ($custom_css && strpos($custom_css, '<style>') === false && strpos($custom_css, '</style>') === false) {
                $custom_css = '<style>' . $custom_css . '</style>';
            }

            if ($custom_js && strpos($custom_js, '<script>') === false && strpos($custom_js, '</script>') === false) {
                $custom_js = '<script>' . $custom_js . '</script>';
            }
        @endphp

        {!! $custom_css !!}
        {!! $custom_js !!}
    @endif
</head>

<body class="font-sans antialiased">
    @inertia
</body>

</html>
