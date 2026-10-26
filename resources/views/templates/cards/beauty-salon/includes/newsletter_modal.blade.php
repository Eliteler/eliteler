{{-- Botanical Parlour — Newsletter Popup Modal --}}
<div id="newsletterModal" class="hidden"> 
    <div class="sl-newsletter-card rounded-xl w-full max-w-md p-6 mx-4 relative text-center space-y-4 shadow-2xl">

        {{-- Close Button --}}
        <button type="button" class="sl-modal-close" onclick="closeNewsletterModal()" aria-label="{{ __('Close') }}">
            <i class="fas fa-times"></i>
        </button>

        {{-- Icon Header --}} 
        <div
            class="w-14 h-14 rounded-lg mx-auto flex items-center justify-center text-xl text-[#5F6F52] mb-3 bg-[#FEFBF6] border-2 border-[#5F6F52] shadow-sm">
            <i class="fas fa-paper-plane"></i>
        </div>

        {{-- Titles --}}
        <div>
            <p class="text-xs font-bold uppercase tracking-widest text-[#5F6F52] mb-1">{{ __('Stay Glamorous') }}</p>
            <h3 class="sl-display text-3xl font-bold text-[#2E251A]">{{ __('Subscribe Newsletter') }}</h3>
            <p class="text-xs text-[#736756] mt-2 leading-relaxed">
                {{ __('Join our VIP list for exclusive hair styling offers, beauty tips, and special discounts.') }}
            </p>
        </div>

        {{-- Alert Notification Box --}}
        <div id="newsletterAlert" class="hidden p-3 rounded-xl text-xs font-semibold text-center my-2 border"></div>

        {{-- Newsletter Form --}}
        <form id="newsletterForm" onsubmit="submitNewsletterForm(event)" class="space-y-3 pt-2">
            <input type="email" id="newsletter_email" name="email"
                placeholder="{{ __('Enter your email address') }}"
                class="sl-input w-full p-3.5 rounded-lg text-xs sm:text-sm" required />

            <button type="submit" id="btn-subscribe-newsletter"
                class="sl-btn-primary w-full py-3.5 rounded-lg text-xs sm:text-sm uppercase tracking-wider font-bold flex items-center justify-center">
                <i class="fas fa-sparkles mr-2"></i> {{ __('Subscribe Now') }}
            </button>
        </form>

    </div>
</div>

<script>
    "use strict";

    function closeNewsletterModal() {
        const modal = document.getElementById('newsletterModal');
        if (modal) modal.classList.add('hidden');
    }

    function submitNewsletterForm(e) {
        e.preventDefault();
        const btn = document.getElementById('btn-subscribe-newsletter');
        const alertBox = document.getElementById('newsletterAlert');
        const emailInput = document.getElementById('newsletter_email');
        const email = emailInput ? emailInput.value.trim() : '';

        if (!email) return;

        if (typeof setBtnLoading === 'function') setBtnLoading(btn, '{{ __('Submitting...') }}');

        const subscribeUrl =
            `{{ config('app.url') }}/subscribe-newsletter?email=${encodeURIComponent(email)}&card_id={{ $business_card_details->card_id ?? '' }}`;

        fetch(subscribeUrl, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(async res => {
                const data = await res.json();
                if (alertBox) {
                    alertBox.classList.remove('hidden', 'bg-red-100', 'text-red-800', 'border-red-300',
                        'bg-green-100', 'text-green-800', 'border-green-300');

                    if (res.ok && (data.success || data.status)) {
                        alertBox.classList.add('bg-green-100', 'text-green-800', 'border-green-300');
                        alertBox.innerHTML = '<i class="fas fa-check-circle mr-1"></i> ' + (data.message ||
                            '{{ __('Thank you for subscribing!') }}');
                        if (emailInput) emailInput.value = '';
                        setTimeout(closeNewsletterModal, 2500);
                    } else {
                        alertBox.classList.add('bg-red-100', 'text-red-800', 'border-red-300');
                        alertBox.innerHTML = '<i class="fas fa-exclamation-circle mr-1"></i> ' + (data
                            .message || '{{ __('Subscription failed.') }}');
                    }
                }
            })
            .catch(() => {
                if (alertBox) {
                    alertBox.classList.remove('hidden');
                    alertBox.classList.add('bg-green-100', 'text-green-800', 'border-green-300');
                    alertBox.innerHTML =
                        '<i class="fas fa-check-circle mr-1"></i> {{ __('Thank you for subscribing!') }}';
                    if (emailInput) emailInput.value = '';
                    setTimeout(closeNewsletterModal, 2500);
                }
            })
            .finally(() => {
                if (typeof resetBtnLoading === 'function') resetBtnLoading(btn);
            });
    }
</script>
