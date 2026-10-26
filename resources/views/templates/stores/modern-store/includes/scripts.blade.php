{{--
    Modern store scripts, at the end of <body>: floating alerts, install prompt, WhatsApp chat button,
    Tabler and sliders, then the shared storefront (cart, product options, WhatsApp checkout).
    Params: $lightbox (bool) — load the image lightbox (product page)
--}}
<div class="alert alert-important alert-danger alert-float" id="errorAlertContainer" role="alert">
    <div id="errorAlertMessage"></div>
</div>
<div class="alert alert-important alert-success alert-float" id="successAlertContainer" role="status">
    <div id="successAlertMessage"></div>
</div>

@if (!empty($plan_details) && $plan_details['pwa'] == 1 && $business_card_details->is_enable_pwa == 1)
    @include('vendor.laravelpwa.bs_pwa_modal')
@endif

@include('templates.stores.includes.whatsapp-float', [
    'businessImage' => $business_card_details->profile,
    'businessName' => $card_details->title,
    'whatsappNumber' => $enquiry_button,
])

<script src="{{ url('js/tabler.min.js') }}"></script>
<script src="{{ url('js/swiper-element-bundle.min.js') }}"></script>
@if ($lightbox ?? false)
    <script src="{{ url('js/fslightbox.js') }}"></script>
@endif

<script>
    // Theme toast: the floating success / error alert (also used by the share box)
    window.showStoreToast = function(message, type = 'success') {
        const alertBox = document.getElementById(type === 'error' ? 'errorAlertContainer' : 'successAlertContainer');
        if (!alertBox) return;

        alertBox.querySelector('div').textContent = message;
        Object.assign(alertBox.style, { display: 'block', maxWidth: '500px', margin: '0 auto', width: '75%', textAlign: 'center' });
        alertBox.classList.add('show');

        clearTimeout(alertBox.hideTimer);
        alertBox.hideTimer = setTimeout(() => {
            alertBox.classList.remove('show');
            setTimeout(() => (alertBox.style.display = 'none'), 1000);
        }, 3000);
    };

    // Dialogs and the cart drawer sit directly in <body> so no parent clips or stacks them wrongly;
    // opening the cart closes the install prompt
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.modal, .offcanvas').forEach((element) => document.body.appendChild(element));

        document.getElementById('offcanvasEnd')?.addEventListener('show.bs.offcanvas', function() {
            document.getElementById('pwaModal')?.classList.replace('show', 'hide');
        });
    });
</script>

@include('templates.stores.shared.scripts', ['hooks' => '{ notify: (message, type) => window.showStoreToast(message, type) }'])
