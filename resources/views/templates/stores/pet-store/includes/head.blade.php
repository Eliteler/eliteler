{{--
    Pet store <head>: shared meta/SEO/PWA, Tabler, fonts and the theme's base styles.
    Pages add their own <style> after it, then @include('templates.stores.shared.custom-code').
--}}
@include('templates.stores.shared.head', ['themeColor' => '#f59f00'])

<link href="{{ url('css/tabler.min.css') }}" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
<link href="{{ url('app/css/store.css') }}" rel="stylesheet">
<link rel="stylesheet" href="{{ url('css/swiper-bundle.min.css') }}">
<link href="https://fonts.googleapis.com/css2?family=Fustat:wght@400;500;600;700&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Gloock:wght@400;500;600;700&display=swap" rel="stylesheet">

{{-- jQuery: the language switcher in the header uses it as soon as it renders --}}
<script src="{{ url('js/jquery.min.js') }}"></script>

<style>
    body {
        font-family: 'Fustat', sans-serif;
        letter-spacing: -0.4px;
    }

    .head {
        font-family: 'Gloock', sans-serif;
        letter-spacing: 1px;
    }

    .navbar {
        left: 0;
        position: absolute;
        top: 0;
        width: 100%;
    }

    .rounded-pet-store,
    .rounded-btn,
    .alert,
    textarea,
    input,
    select {
        border-radius: 10px !important;
    }

    .card {
        border-radius: 10px;
        box-shadow: 0 0 6px rgba(248, 197, 130, 0.582) !important;
        border: #F4A261 !important;
    }

    .btn {
        border-radius: 10px !important;
        color: #ffffff;
        border: none;
    }

    .blur-img {
        width: 75px;
        height: 75px;
        top: 65px;
        right: -35px;
        opacity: 0.8;
        filter: blur(0.7px);
    }

    @media (max-width: 768px) {
        .blur-img {
            width: 75px;
            height: 75px;
            top: 110px;
            right: -25px;
            opacity: 0.8;
            filter: blur(0.7px);
        }
    }

    .footer-wave {
        aspect-ratio: 1440 / 100;
        overflow: hidden;
        line-height: 0;
        position: relative;
        margin-bottom: -1px;
    }

    .footer-wave svg {
        display: block;
        width: 100%;
        height: 100%;
        transform: scaleY(-1);
    }

    footer.footer {
        background-color: #E96629;
        color: white;
        position: relative;
        z-index: 1;
    }

    .modal-backdrop, .offcanvas-backdrop {
        z-index: 1000 !important;
    }
</style>
