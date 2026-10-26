{{--
    Furniture theme scripts, at the end of <body>: the theme toast, Bootstrap/Tabler and sliders,
    then the shared storefront (cart, product options, WhatsApp checkout) using the theme toast.
--}}
<div id="storeDirectToast" role="status" aria-live="polite"></div>

<script src="{{ url('js/jquery.min.js') }}"></script>
<script src="{{ url('js/tabler.min.js') }}"></script>
<script src="{{ url('js/swiper-element-bundle.min.js') }}"></script>

<script>
    // Theme toast (also used by includes/share.blade.php and includes/pwa-modal.blade.php)
    window.showStoreToast = function(message, type = 'success') {
        const toast = document.getElementById('storeDirectToast');
        if (!toast) return;

        toast.textContent = message;
        toast.className = 'toast-visible' + (type === 'error' ? ' toast-error' : '');

        clearTimeout(window.storeToastTimer);
        window.storeToastTimer = setTimeout(() => (toast.className = ''), 3000);
    };
</script>

@include('templates.stores.shared.scripts', ['hooks' => '{ notify: (message, type) => window.showStoreToast(message, type) }'])
