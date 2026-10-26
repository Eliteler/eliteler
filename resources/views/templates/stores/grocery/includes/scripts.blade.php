{{--
    Grocery theme scripts, at the end of <body>: the theme toast, Bootstrap/Tabler and sliders,
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
        if (!toast) return;

        document.getElementById('storeToastMessage').textContent = message;
        document.getElementById('storeToastIcon').className = type === 'success' ? 'ti ti-circle-check' : 'ti ti-alert-circle';
        toast.className = (type === 'success' ? 'toast-success' : 'toast-error') + ' toast-visible';

        clearTimeout(window.storeToastTimer);
        window.storeToastTimer = setTimeout(() => toast.classList.remove('toast-visible'), 3000);
    };

    // Closing the checkout must never leave the page dimmed or locked
    document.addEventListener('hidden.bs.modal', function() {
        if (document.querySelectorAll('.modal.show').length === 0) {
            document.body.classList.remove('modal-open');
            document.querySelectorAll('.modal-backdrop').forEach((backdrop) => backdrop.remove());
        }
    });
</script>

@include('templates.stores.shared.scripts', ['hooks' => '{ notify: (message, type) => window.showStoreToast(message, type) }'])
