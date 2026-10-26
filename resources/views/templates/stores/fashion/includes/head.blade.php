{{--
    Fashion theme <head>: shared meta/SEO/PWA, then this theme's fonts and base styles.
    Pages add their own <style> after it, then @include('templates.stores.shared.custom-code').
--}}
@include('templates.stores.shared.head', ['themeColor' => '#0f172a'])

<link href="{{ url('css/tabler.min.css') }}" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
<link rel="stylesheet" href="{{ url('css/swiper-bundle.min.css') }}">
<link href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

<style>
    a,
    a:hover,
    a:focus,
    a:active {
        text-decoration: none !important;
    }

    body {
        font-family: 'Plus Jakarta Sans', sans-serif;
        background-color: #f8fafc;
        color: #1e293b;
        margin: 0;
        padding: 0;
    }

    .head {
        font-family: 'Oswald', sans-serif;
        text-transform: uppercase;
    }

    /* Theme toast (includes/scripts.blade.php) */
    #storeDirectToast {
        position: fixed;
        top: 25px;
        left: 50%;
        transform: translateX(-50%);
        z-index: 99999999;
        background-color: #0f172a;
        color: #ffffff;
        padding: 12px 24px;
        border-radius: 0px;
        font-size: 13.5px;
        font-weight: 700;
        letter-spacing: 0.5px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
        display: none;
        white-space: nowrap !important;
        width: auto !important;
    }

    #storeDirectToast.toast-error {
        background-color: #dc2626;
    }

    #storeDirectToast.show {
        display: block;
        animation: toastFade 0.3s ease;
    }

    @keyframes toastFade {
        from {
            opacity: 0;
            transform: translate(-50%, -10px);
        }

        to {
            opacity: 1;
            transform: translate(-50%, 0);
        }
    }
</style>
