{{--
    Furniture theme <head>: shared meta/SEO/PWA, then this theme's fonts, colours and base styles.
    Pages add their own <style> after it, then @include('templates.stores.shared.custom-code').
--}}
@include('templates.stores.shared.head', ['themeColor' => '#cca47a'])

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Albert+Sans:wght@400;500;600;700&family=DM+Serif+Display&display=swap" rel="stylesheet">
<link href="{{ url('css/tabler.min.css') }}" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
<link rel="stylesheet" href="{{ url('css/swiper-bundle.min.css') }}">
<link href="{{ url('templates/css/store/furniture.css') }}" rel="stylesheet">

<style>
    :root {
        --color-camel: #cca47a;
        --color-camel-dark: #b58c62;
        --color-camel-light: #f5ece1;
        --color-forest-teal: #1f4b48;
        --color-forest-teal-dark: #163836;
        --color-canvas-bg: #f9f7f2;
        --color-card-border: #ede8df;
        --color-text-main: #1e293b;
        --color-text-muted: #64748b;
    }

    a,
    a:hover,
    a:focus,
    a:active {
        text-decoration: none !important;
    }

    html,
    body {
        height: 100%;
        margin: 0;
        padding: 0;
    }

    body {
        font-family: 'Albert Sans', sans-serif;
        background-color: var(--color-canvas-bg);
        color: var(--color-text-main);
        letter-spacing: -0.2px;
        overflow-x: hidden;
        display: flex;
        flex-direction: column;
        min-height: 100vh;
    }

    .store-main-wrapper {
        max-width: 1280px;
        width: 100%;
        margin: 0 auto;
        background: #ffffff;
        min-height: 100vh;
        border-left: 1px solid var(--color-card-border);
        border-right: 1px solid var(--color-card-border);
        display: flex;
        flex-direction: column;
        flex: 1 0 auto;
    }

    .store-body-content-area {
        flex: 1 0 auto;
        width: 100%;
        display: flex;
        flex-direction: column;
    }

    /* Theme toast (includes/scripts.blade.php) */
    #storeDirectToast {
        position: fixed;
        top: 24px;
        left: 50%;
        transform: translateX(-50%) translateY(-100px);
        z-index: 99999999;
        background-color: #1f4b48;
        color: #ffffff;
        font-size: 14px;
        font-weight: 700;
        padding: 11px 26px;
        border-radius: 50px !important;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        white-space: nowrap !important;
        opacity: 0;
        pointer-events: none;
        transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.3s ease;
    }

    #storeDirectToast.toast-visible {
        transform: translateX(-50%) translateY(0);
        opacity: 1;
    }

    #storeDirectToast.toast-error {
        background-color: #dc2626;
    }
</style>
