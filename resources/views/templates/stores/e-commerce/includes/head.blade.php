{{--
    E-commerce theme <head>: shared meta/SEO/PWA, then this theme's fonts and base styles.
    Pages add their own <style> after it, then @include('templates.stores.shared.custom-code').
--}}
@include('templates.stores.shared.head', ['themeColor' => '#206bc4'])

<link href="{{ url('css/tabler.min.css') }}" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
<link href="{{ url('app/css/store.css') }}" rel="stylesheet">
<link rel="stylesheet" href="{{ url('css/swiper-bundle.min.css') }}">
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700&family=Fustat:wght@400;500;600;700&display=swap" rel="stylesheet">

<style>
    body {
        font-family: 'Fustat', sans-serif;
        letter-spacing: -0.3px;
    }

    .head {
        font-family: 'Outfit', sans-serif;
        letter-spacing: -0.1px;
    }

    .store-bg-wrapper {
        background-color: #FBFBFB;
        min-height: 100vh;
    }

    .store-main-container {
        min-height: 100vh;
    }

    .btn {
        border-radius: 0px !important;
        border: none;
    }

    /* Shimmer on dark action buttons */
    .btn-effect-loop {
        position: relative;
        overflow: hidden;
    }

    .btn-effect-loop::before {
        content: "";
        position: absolute;
        top: 0;
        left: -150%;
        width: 300%;
        height: 100%;
        background: linear-gradient(120deg,
                    rgba(255, 255, 255, 0) 0%,
                    rgba(255, 255, 255, 0.12) 35%,
                    rgba(255, 255, 255, 0.25) 50%,
                    rgba(255, 255, 255, 0.12) 65%,
                    rgba(255, 255, 255, 0) 100%);
        animation: loopShimmer 2.5s linear infinite;
        z-index: 1;
        pointer-events: none;
    }

    .btn-effect-loop>* {
        position: relative;
        z-index: 2;
    }

    @keyframes loopShimmer {
        0% {
            left: -150%;
        }

        100% {
            left: 100%;
        }
    }

    /* Theme toast (includes/scripts.blade.php) */
    .store-direct-toast-pill {
        display: none;
        position: fixed !important;
        top: 25px !important;
        left: 50% !important;
        transform: translateX(-50%) !important;
        z-index: 99999999 !important;
        background-color: #10b981 !important;
        color: #ffffff !important;
        padding: 10px 22px !important;
        border-radius: 50px !important;
        font-weight: 700 !important;
        font-size: 14.5px !important;
        box-shadow: 0 12px 35px rgba(0, 0, 0, 0.35) !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 8px !important;
        white-space: nowrap !important;
        pointer-events: none !important;
        width: auto !important;
        max-width: 90vw !important;
        line-height: 1 !important;
    }

    .store-direct-toast-pill span {
        white-space: nowrap !important;
        line-height: 1 !important;
    }

    .store-direct-toast-pill.is-visible {
        display: inline-flex !important;
    }

    .store-direct-toast-pill.is-error {
        background-color: #ef4444 !important;
    }
</style>
