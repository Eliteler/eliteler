{{--
    E-commerce theme scripts, at the end of <body>: the theme toast, Bootstrap/Tabler and sliders,
    then the shared storefront (cart, product options, WhatsApp checkout) using the theme toast.
--}}
<div id="storeDirectToast" class="store-direct-toast-pill" role="status" aria-live="polite">
    <i class="ti ti-circle-check fs-2" id="storeDirectToastIcon"></i>
    <span id="storeDirectToastMsg"></span>
</div>

<script src="{{ url('js/jquery.min.js') }}"></script>
<script src="{{ url('js/tabler.min.js') }}"></script>
<script src="{{ url('js/swiper-element-bundle.min.js') }}"></script>

<script>
    // Theme toast (also used by includes/share.blade.php)
    window.showStoreToast = function(message, type = 'success') {
        const toast = document.getElementById('storeDirectToast');
        if (!toast) return;

        document.getElementById('storeDirectToastMsg').textContent = message;
        document.getElementById('storeDirectToastIcon').className = 'ti fs-2 ' + (type === 'error' ? 'ti-alert-circle' : 'ti-circle-check');
        toast.className = 'store-direct-toast-pill is-visible' + (type === 'error' ? ' is-error' : '');

        clearTimeout(window.storeToastTimer);
        window.storeToastTimer = setTimeout(() => toast.classList.remove('is-visible'), 2200);
    };
</script>

@include('templates.stores.shared.scripts', ['hooks' => '{ notify: (message, type) => window.showStoreToast(message, type) }'])
