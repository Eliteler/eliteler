{{--
    Cosmetics theme <head>: shared meta/SEO/PWA, then this theme's fonts and base styles.
    Pages add their own <style> after it, then @include('templates.stores.shared.custom-code').
--}}
@include('templates.stores.shared.head', ['themeColor' => '#e91e63'])

<link href="{{ url('css/tabler.min.css') }}" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
<link href="{{ url('app/css/store.css') }}" rel="stylesheet">
<link rel="stylesheet" href="{{ url('css/swiper-bundle.min.css') }}">
<link href="https://fonts.googleapis.com/css2?family=Figtree:wght@100;200;300;400;500;600;700;800;900&family=Merriweather:wght@300;400;700;900&display=swap"
    rel="stylesheet">

<script src="{{ url('js/jquery.min.js') }}"></script>
<script src="{{ url('js/main.js') }}"></script>
<script src="{{ url('js/sweetalert.all.js') }}"></script>

<style>
    body {
        font-family: 'Figtree', sans-serif;
        letter-spacing: -0.4px;
    }

    .head {
        font-family: 'Merriweather', sans-serif;
    }

    .navbar {
        left: 0;
        position: absolute;
        top: 0;
        width: 100%;
    }

    .card,
    input,
    select,
    .alert,
    .rounded-cosmetics {
        border-radius: 10px !important;
    }

    .btn {
        border-radius: 10px !important;
        color: #ffffff;
        border: none;
    }
</style>
