{{--
    Jewellery theme <head>: shared meta/SEO/PWA, then this theme's fonts, colours and base styles.
    Pages add their own <style> after it, then @include('templates.stores.shared.custom-code').
--}}
@include('templates.stores.shared.head', ['themeColor' => '#d4af37'])

<link href="{{ url('css/tabler.min.css') }}" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
<link rel="stylesheet" href="{{ url('css/swiper-bundle.min.css') }}">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Gloock&family=Merriweather:ital,wght@0,300;0,400;0,700;1,300&display=swap"
    rel="stylesheet">

<style>
    :root {
        --store-primary: #d4af37;
        --store-primary-hover: #b89028;
        --store-primary-light: #fcf8ee;
        --store-primary-dark: #8c733e;
        --store-dark: #2b2316;
        --store-body-bg: #fffaf5;
        --store-card-bg: #ffffff;
        --store-text-main: #2b2316;
        --store-text-muted: #8c8273;
        --store-border: rgba(212, 175, 55, 0.28);
        --store-radius-card: 22px;
        --store-radius-pill: 50px;
    }

    html,
    body {
        min-height: 100vh;
        display: flex;
        flex-direction: column;
        background-color: var(--store-body-bg);
        color: var(--store-text-main);
        font-family: 'Merriweather', serif !important;
        letter-spacing: -0.2px;
        margin: 0;
        padding: 0;
    }

    a,
    a:hover,
    a:focus,
    a:active {
        text-decoration: none !important;
    }

    .store-main-wrapper {
        min-height: 100vh;
        display: flex;
        flex-direction: column;
        flex: 1 0 auto;
        width: 100%;
    }

    .store-body-content-area {
        flex: 1 0 auto;
        width: 100%;
        display: flex;
        flex-direction: column;
    }

    .store-luxury-footer {
        margin-top: auto !important;
        width: 100%;
    }

    #storeDirectToast {
        position: fixed;
        bottom: 95px;
        left: 50%;
        transform: translateX(-50%) translateY(40px);
        background: #1e1910 !important;
        color: #ffffff !important;
        padding: 12px 26px;
        border-radius: var(--store-radius-pill) !important;
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.45);
        display: flex !important;
        align-items: center !important;
        gap: 12px !important;
        font-size: 0.95rem;
        font-weight: 600;
        z-index: 99999999 !important;
        opacity: 0;
        visibility: hidden;
        pointer-events: none;
        white-space: nowrap !important;
        transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        border: 1.5px solid var(--store-primary) !important;
    }

    #storeDirectToast.toast-visible,
    #storeDirectToast.show {
        opacity: 1 !important;
        visibility: visible !important;
        transform: translateX(-50%) translateY(0) !important;
        pointer-events: auto !important;
    }

    #storeDirectToast i {
        font-size: 1.35rem !important;
        color: var(--store-primary) !important;
    }
</style>
