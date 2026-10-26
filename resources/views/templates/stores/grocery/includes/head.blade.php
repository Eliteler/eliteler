{{--
    Grocery theme <head>: shared meta/SEO/PWA, then this theme's fonts, colours and base styles.
    Pages add their own <style> after it, then @include('templates.stores.shared.custom-code').
--}}
@include('templates.stores.shared.head', ['themeColor' => '#2e7d32'])

<link href="{{ url('css/tabler.min.css') }}" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
<link rel="stylesheet" href="{{ url('css/swiper-bundle.min.css') }}">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<style>
    :root {
        --store-primary: #2e7d32;
        --store-primary-hover: #1b5e20;
        --store-primary-light: #e8f5e9;
        --store-dark: #1f2937;
        --store-body-bg: #f7faf7;
        --store-card-bg: #ffffff;
        --store-text-main: #1f2937;
        --store-text-muted: #6b7280;
        --store-border: #c8e6c9;
        --store-radius-card: 24px;
        --store-radius-pill: 50px;
    }

    *,
    *::before,
    *::after {
        box-sizing: border-box;
    }

    a,
    a:hover,
    a:focus,
    a:active {
        text-decoration: none !important;
    }

    /* Full-height page: the footer always sits at the bottom */
    body {
        min-height: 100vh;
        display: flex;
        flex-direction: column;
        background-color: var(--store-body-bg);
        color: var(--store-text-main);
        font-family: 'Plus Jakarta Sans', sans-serif !important;
        letter-spacing: -0.2px;
        margin: 0;
        padding: 0;
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

    /* Theme toast (includes/scripts.blade.php) */
    #storeDirectToast {
        position: fixed;
        bottom: 95px;
        left: 50%;
        transform: translateX(-50%) translateY(30px);
        background: #111827 !important;
        color: #ffffff !important;
        padding: 12px 24px;
        border-radius: var(--store-radius-pill) !important;
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.35);
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 0.92rem;
        font-weight: 600;
        z-index: 99999999;
        opacity: 0;
        pointer-events: none;
        white-space: nowrap !important;
        transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        border: 1px solid rgba(255, 255, 255, 0.1);
    }

    #storeDirectToast.toast-visible {
        opacity: 1;
        transform: translateX(-50%) translateY(0);
        pointer-events: auto;
    }

    #storeDirectToast.toast-success i {
        color: #10b981;
        font-size: 1.2rem;
    }

    #storeDirectToast.toast-error i {
        color: #ef4444;
        font-size: 1.2rem;
    }
</style>
