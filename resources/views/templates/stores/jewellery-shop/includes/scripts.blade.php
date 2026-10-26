{{--
    Jewellery theme scripts, at the end of <body>: the theme toast, Bootstrap/Tabler and sliders,
    then the shared storefront (cart, product options, WhatsApp checkout) using the theme toast.
--}}
<div id="storeDirectToast" role="status" aria-live="polite">
    <i id="storeToastIcon" class="ti ti-circle-check"></i>
    <span id="storeToastMessage"></span>
</div>

<script src="{{ url('js/jquery.min.js') }}"></script>
<script src="{{ url('js/tabler.min.js') }}"></script>
<script src="{{ url('js/swiper-element-bundle.min.js') }}"></script>

<script>
    // Theme toast (also used by includes/share.blade.php)
    window.showStoreToast = function(message, type = 'success') {
        const toast = document.getElementById('storeDirectToast');
        const icon = document.getElementById('storeToastIcon');
        if (!toast) return;

        document.getElementById('storeToastMessage').textContent = message;
        icon.className = type === 'success' ? 'ti ti-circle-check' : 'ti ti-alert-circle';
        icon.style.color = type === 'success' ? '#d4af37' : '#ef4444';
        toast.classList.add('toast-visible', 'show');

        clearTimeout(window.storeToastTimer);
        window.storeToastTimer = setTimeout(() => toast.classList.remove('toast-visible', 'show'), 3200);
    };

    // Opening the checkout from the cart drawer stacks two Bootstrap layers: tidy up the
    // backdrops so the page never stays dimmed or locked after both are closed
    ['hidden.bs.modal', 'hidden.bs.offcanvas'].forEach(function(eventName) {
        document.addEventListener(eventName, function() {
            const openModals = document.querySelectorAll('.modal.show').length;
            const openDrawers = document.querySelectorAll('.offcanvas.show').length;

            if (openModals === 0 && openDrawers === 0) {
                document.body.classList.remove('modal-open');
                document.querySelectorAll('.modal-backdrop, .offcanvas-backdrop').forEach((backdrop) => backdrop.remove());
            }
        });
    });
</script>

@include('templates.stores.shared.scripts', ['hooks' => '{ notify: (message, type) => window.showStoreToast(message, type) }'])
