{{--
    Digital products theme scripts, at the end of <body>: the theme toast, light/dark toggle,
    opening and closing of the cart and checkout layers (no Bootstrap in this theme), the WhatsApp
    enquiry button, then the shared storefront (cart, product options, WhatsApp checkout).
--}}

<!-- Bulletproof Store Toast (Inline styles guarantee positioning below sticky header) -->
<div id="storeDirectToast" role="status" aria-live="polite"
    class="text-white text-xs font-semibold px-4 py-3 rounded-2xl shadow-2xl flex items-center gap-2.5 border"
    style="position: fixed !important; top: 85px !important; right: 20px !important; z-index: 999999 !important; background-color: #059669; border-color: rgba(16, 185, 129, 0.4); opacity: 0; pointer-events: none; transform: translateY(-20px); transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);">
    <i class="ti ti-circle-check text-base shrink-0" id="storeToastIcon"></i>
    <span id="storeToastMessage"></span>
</div>

<script src="{{ url('js/swiper-bundle.min.js') }}"></script>

<script>
    // Bulletproof Theme Toast with zero compilation dependency
    window.showStoreToast = function(message, type = 'success') {
        const toast = document.getElementById('storeDirectToast');
        if (!toast) return;

        const msgEl = document.getElementById('storeToastMessage');
        const iconEl = document.getElementById('storeToastIcon');

        if (msgEl) msgEl.textContent = message;
        if (iconEl) {
            iconEl.className = 'ti text-base shrink-0 ' + (type === 'error' ? 'ti-alert-circle' :
            'ti-circle-check');
        }

        // Apply colors directly
        if (type === 'error') {
            toast.style.backgroundColor = '#e11d48'; // Rose color for errors
            toast.style.borderColor = 'rgba(244, 63, 94, 0.4)';
        } else {
            toast.style.backgroundColor = '#059669'; // Emerald color for success
            toast.style.borderColor = 'rgba(16, 185, 129, 0.4)';
        }

        // Slide down and reveal 85px below the header
        toast.style.pointerEvents = 'auto';
        toast.style.opacity = '1';
        toast.style.transform = 'translateY(0)';

        // Auto hide after 2.6 seconds
        clearTimeout(window.storeToastTimer);
        window.storeToastTimer = setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateY(-20px)';
            toast.style.pointerEvents = 'none';
        }, 2600);
    };

    // Light / dark switch in the header
    function toggleTheme() {
        const dark = document.documentElement.classList.toggle('dark');
        try {
            localStorage.setItem('theme', dark ? 'dark' : 'light');
        } catch (error) {}
    }

    // Cart and checkout layers controller
    const storeLayers = {
        open(layer) {
            if (!layer) return;
            layer.classList.remove('hidden');
            document.getElementById('pwaModal')?.classList.replace('show', 'hide');
            if (typeof gsap !== 'undefined') {
                gsap.fromTo(layer, {
                    opacity: 0
                }, {
                    opacity: 1,
                    duration: 0.25
                });
                gsap.fromTo(layer.firstElementChild, {
                    scale: 0.9,
                    opacity: 0
                }, {
                    scale: 1,
                    opacity: 1,
                    duration: 0.3,
                    ease: 'back.out(1.5)'
                });
            }
        },
        close(layer) {
            if (!layer || layer.classList.contains('hidden')) return;
            if (typeof gsap === 'undefined') return layer.classList.add('hidden');
            gsap.to(layer, {
                opacity: 0,
                duration: 0.2,
                onComplete: () => layer.classList.add('hidden')
            });
        },
    };

    document.addEventListener('click', function(event) {
        const layer = event.target.closest('[data-cart-drawer], [data-order-modal]');
        if (!layer) return;
        // Close button inside a layer, or a click on the dark backdrop around it
        if (event.target.closest('[data-layer-close]') || event.target === layer) storeLayers.close(layer);
    });

    // "Enquiry" buttons on WhatsApp
    function enquireViaWhatsApp(button) {
        const product = button.closest('[data-product]');
        if (!product || !window.GoBizStore) return;

        const qty = parseInt(product.querySelector('[data-qty-value]')?.textContent || '1', 10) || 1;
        const shownPrice = product.querySelector('[data-product-price]')?.textContent.trim();
        const price = shownPrice || GoBizStore.formatMoney(Number(product.dataset.price) * qty);
        const option = product.querySelector('[data-option-label]')?.textContent.trim();
        const name = product.dataset.name + (option ? ' (' + option + ')' : '');
        const message = @json(__('Hello! I have an enquiry regarding')) + ' "' + name + '" (' + @json(__('Quantity')) + ': ' + qty +
            ', ' +
            @json(__('Price')) + ': ' + price + '). ' + @json(__('Can you please share more details?'));

        window.open(GoBizStore.whatsappUrl(message), '_blank');
    }
</script>

@include('templates.stores.shared.scripts', [
    'hooks' => '{
    notify: (message, type) => window.showStoreToast(message, type),
    openCart: () => storeLayers.open(document.querySelector("[data-cart-drawer]")),
    closeCart: () => storeLayers.close(document.querySelector("[data-cart-drawer]")),
    openCheckout: () => storeLayers.open(document.querySelector("[data-order-modal]")),
    closeCheckout: () => storeLayers.close(document.querySelector("[data-order-modal]")),
}',
])
