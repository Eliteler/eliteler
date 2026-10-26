{{--
    Modern store <head> (light and dark, 8 colours): shared meta/SEO/PWA in the theme colour,
    Tabler, fonts and base styles. Pages add their own <style> after it, then
    @include('templates.stores.shared.custom-code').
--}}
@include('templates.stores.shared.head', ['themeColor' => $storeTheme->hex])

<link href="{{ url('css/tabler.min.css') }}" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
<link href="{{ url('app/css/store.css') }}" rel="stylesheet">
<link rel="stylesheet" href="{{ url('css/swiper-bundle.min.css') }}">
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">

{{-- jQuery: the language switcher in the header uses it as soon as it renders --}}
<script src="{{ url('js/jquery.min.js') }}"></script>

<style>
    body {
        font-family: 'Outfit', sans-serif;
        letter-spacing: -0.4px;
    }

    /* Pill buttons and inputs */
    .btn {
        border-radius: 50px !important;
        padding-left: 20px !important;
        padding-right: 20px !important;
    }

    .form-control,
    .form-select {
        border-radius: 50px !important;
        padding-left: 16px !important;
        padding-right: 16px !important;
    }

    textarea.form-control {
        border-radius: 16px !important;
    }

    .modal-content {
        border-radius: 20px !important;
        border: none;
        overflow: hidden;
        box-shadow: 0 15px 40px rgba(0, 0, 0, {{ $storeTheme->isDark() ? '0.5' : '0.3' }}) !important;
    }

    /* Dark backdrop behind the cart drawer and dialogs */
    .modal-backdrop,
    .offcanvas-backdrop {
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        width: 100vw !important;
        height: 100vh !important;
        background-color: #000000 !important;
        z-index: 1040 !important;
    }

    .modal-backdrop.show,
    .offcanvas-backdrop.show {
        opacity: {{ $storeTheme->isDark() ? '0.8' : '0.75' }} !important;
    }

    .offcanvas {
        z-index: 1050 !important;
    }

    .modal {
        z-index: 1060 !important;
    }

    @if ($storeTheme->isDark())
        .navbar {
            background-color: rgba(21, 31, 44, 0.6) !important;
        }

        .border-top {
            border-top: var(--tblr-border-width) var(--tblr-border-style) rgb(179 189 204 / 13%) !important;
        }

        .offcanvas-header {
            border-bottom: var(--tblr-border-width) var(--tblr-border-style) rgb(179 189 204 / 13%) !important;
        }

        .badge-custom {
            background-color: #343d51 !important;
            color: #ccc !important;
        }
    @endif
</style>
