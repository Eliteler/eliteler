{{--
    Gift shop theme <head>: shared meta/SEO/PWA, then this theme's fonts, colours and base styles.
    Pages add their own <style> after it, then @include('templates.stores.shared.custom-code').
--}}
@include('templates.stores.shared.head', ['themeColor' => '#dc2626'])

<link href="{{ url('css/tabler.min.css') }}" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
<link rel="stylesheet" href="{{ url('css/swiper-bundle.min.css') }}">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Carattere&family=Lora:ital,wght@0,400..700;1,400..700&display=swap" rel="stylesheet">

<style>
    :root {
        --store-primary: #dc2626;
        --store-primary-hover: #b91c1c;
        --store-primary-light: #fef2f2;
        --store-dark: #1c1917;
        --store-body-bg: #f8fafc;
        --store-card-bg: #ffffff;
        --store-text-main: #292524;
        --store-text-muted: #78716c;
        --store-border: #f1f5f9;
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
        font-family: 'Lora', serif !important;
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

    .font-carattere {
        font-family: 'Carattere', cursive !important;
    }

    .store-top-greeting-bar {
        background: #1c1917;
        color: #f5f5f4;
        padding: 9px 16px;
        font-size: 0.84rem;
        font-weight: 600;
        letter-spacing: 0.4px;
        text-align: center;
        border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        position: relative;
        z-index: 1025;
    }

    .greeting-sparkle-icon {
        color: #dc2626;
        font-size: 1rem;
    }

    .heading-divider-line {
        flex: 1;
        height: 1.5px;
        background: linear-gradient(90deg, transparent, var(--store-primary));
    }

    .heading-divider-line.right {
        background: linear-gradient(90deg, var(--store-primary), transparent);
    }

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
