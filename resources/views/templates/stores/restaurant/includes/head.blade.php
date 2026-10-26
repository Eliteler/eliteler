{{--
    Restaurant <head>: shared meta/SEO/PWA, Tabler, fonts and the theme's base styles.
    Pages add their own <style> after it, then @include('templates.stores.shared.custom-code').
--}}
@include('templates.stores.shared.head', ['themeColor' => '#ff3d00'])

<link href="{{ url('css/tabler.min.css') }}" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
<link href="{{ url('app/css/store.css') }}" rel="stylesheet">
<link rel="stylesheet" href="{{ url('css/swiper-bundle.min.css') }}">
<link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Rethink+Sans:wght@400;500;600&display=swap" rel="stylesheet" />

{{-- jQuery: the language switcher in the header uses it as soon as it renders --}}
<script src="{{ url('js/jquery.min.js') }}"></script>

<style>
    body {
        font-family: 'Rethink Sans', sans-serif;
        letter-spacing: -0.4px;
    }

    .head {
        font-family: 'Pacifico', sans-serif;
        letter-spacing: 0.1px;
    }

    .navbar {
        left: 0;
        position: absolute;
        top: 0;
        width: 100%;
    }

    .card {
        box-shadow: 0 0 6px rgba(252, 142, 142, 0.493) !important;
        border: rgba(252, 94, 94, 0.452) !important;
    }

    .card,
    .rounded-restaurant,
    .alert,
    input,
    select {
        border-radius: 25px;
    }

    .btn {
        border-radius: 25px !important;
        color: #ffffff;
        border: none;
    }

    .blur-img {
        width: 110px;
        height: 110px;
        right: -60px;
        opacity: 0.5;
        top: 55px;
        filter: blur(1px);
    }

    @media (max-width: 768px) {
        .blur-img {
            width: 110px;
            height: 110px;
            right: -50px;
            opacity: 0.5;
            top: 105px;
            filter: blur(1px);
        }
    }

    .image-wrapper {
        width: 100px;
        height: 100px;
        overflow: hidden;
    }

    .category-img {
        width: 100%;
        height: 100%;
        object-fit: contain;
        display: block;
    }

    @keyframes spin {
        0% {
            transform: rotate(0deg);
        }

        100% {
            transform: rotate(360deg);
        }
    }

    .rotate-loop {
        animation: spin 25s linear infinite;
    }

    @keyframes floatUpDown {
        0% {
            transform: translateY(0);
        }

        50% {
            transform: translateY(-10px);
        }

        100% {
            transform: translateY(0);
        }
    }

    .float-loop {
        animation: floatUpDown 10s ease-in-out infinite;
    }

    nav.fixed-bottom {
        z-index: 999 !important;
    }

    .modal-backdrop,
    .offcanvas-backdrop {
        z-index: 1000 !important;
    }
</style>
