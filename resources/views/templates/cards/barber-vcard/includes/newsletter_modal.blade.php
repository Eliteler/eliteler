@php
    use App\Newsletter;

    $newsletter_pop = Newsletter::where('card_id', $business_card_details->card_id)->first();
    
    $nl_title = $newsletter_pop->title ?? __('Subscribe to Our Newsletter');
    $nl_desc = 
        $newsletter_pop->description ??
        __(
            'Join our exclusive newsletter to receive the latest updates, special offers, and insider news directly in your inbox.',
        );
    $nl_button_text = $newsletter_pop->button_text ?? __('Join Now');
@endphp

<div id="newsletterModal" class="fixed inset-0 bb-modal-overlay hidden items-center justify-center z-50 p-4"
    data-csrf="{{ csrf_token() }}" data-email-error="{{ __('Please enter your email.') }}"
    data-vaild-email-error="{{ __('Please enter valid email.') }}">
    <div id="customNewsBox" class="bb-panel rounded-2xl w-full max-w-md relative overflow-hidden">
        <button type="button" class="bb-icon-btn absolute w-10 h-10 rounded-full z-10" style="right: 1rem; top: 1rem;"
            onclick="closeNewsModal()" aria-label="{{ __('Close') }}"><i class="fas fa-times"></i></button>
        <div class="p-6 text-center">
            <div class="flex items-center justify-center w-16 h-16 rounded-full mx-auto mb-4"
                style="border: 1px solid var(--bb-gold); color: var(--bb-gold);"><i
                    class="far fa-envelope text-2xl"></i></div>
            <p class="bb-eyebrow mb-2">{{ __('Stay in the chair') }}</p>
            <h3 class="bb-display text-3xl bb-gold-text">{{ $nl_title }}</h3>
            <div class="bb-text-muted text-sm leading-relaxed mt-3 mb-3 bb-clamp-3">{!! $nl_desc !!}</div>

            <div id="errorNewsMessage" class="hidden my-4 p-3 rounded-lg text-sm"
                style="background: rgba(183,40,46,.18); color: #f3b0b3;"></div>
            <div id="successNewsMessage" class="hidden mt-4 p-3 rounded-lg text-sm"
                style="background: rgba(201,162,75,.16); color: var(--bb-cream);"></div>
            <input type="hidden" id="card_id" name="card_id" value="{{ $business_card_details->card_id }}">

            <div class="mt-5 text-left">
                <label for="newsletter_email" class="sr-only">{{ __('Email Address') }}</label>
                <input type="email" id="newsletter_email" name="email"
                    class="bb-input w-full px-4 py-3 rounded-lg focus:outline-none" 
                    placeholder="{{ __('Email Address') }}" required>
            </div>
            <button type="button" id="subscribeButton"
                class="bb-btn-primary w-full py-3 rounded-lg mt-3">{{ $nl_button_text }}</button>
            <p class="bb-text-muted text-xs mt-4"><i
                    class="fas fa-lock bb-gold-text mr-2"></i>{{ __('No spam. Unsubscribe at any time.') }}</p>
        </div>
    </div>
</div>

<script>
    "use strict";
    window.closeNewsModal = function() {
        document.getElementById('newsletterModal')?.classList.add('hidden');
    };
    window.openNewsModal = function() {
        document.getElementById('newsletterModal')?.classList.remove('hidden');
    };
</script>
