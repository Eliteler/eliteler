@php
    use App\Newsletter;
    $newsletter_pop = Newsletter::where('card_id', $business_card_details->card_id)->first();
    $nl_title = $newsletter_pop->title ?? __('Subscribe to Our Newsletter');
    $nl_desc =
        $newsletter_pop->description ??
        __('Join our exclusive newsletter to receive the latest updates, special offers, and insider news directly in your inbox.');
    $nl_button_text = $newsletter_pop->button_text ?? __('Join Now');
@endphp

<!-- Overlay Wrapper -->
<div id="newsletterModal" class="dark-modal-overlay" data-csrf="{{ csrf_token() }}"
    data-email-error="{{ __('Please enter your email.') }}"
    data-vaild-email-error="{{ __('Please enter valid email.') }}">

    <!-- New Dark Design Box -->
    <div id="customNewsBox" class="vault-panel modal-vault-box">

        {{-- Close Button --}}
        <button class="modal-close-btn" onclick="closeNewsModal()">
            <i class="fas fa-times"></i>
        </button>

        {{-- Top Icon (Centered for Newsletter) --}}
        <div class="panel-head" style="justify-content: center; border-bottom: none; padding-top: 10px;">
            <div class="panel-icon" style="margin: 0 auto 15px;"><i class="far fa-envelope"></i></div>
        </div>

        <div style="text-align: center; padding: 0 24px 28px;">

            {{-- Title --}}
            <h3
                style="font-family: 'Barlow Condensed', sans-serif; font-size: 26px; color: var(--gold); text-transform: uppercase; margin-bottom: 12px; letter-spacing: 1px;">
                {{ $nl_title }}
            </h3>

            {{-- Description --}}
            <p style="font-size: 13px; color: var(--text-muted); line-height: 1.6; margin-bottom: 24px;">
                {!! $nl_desc !!}
            </p>

            {{-- Alert Messages (Using old JS IDs, but new dark styling) --}}
            <div id="errorNewsMessage" class="alert-box alert-error hidden"
                style="margin-bottom: 15px; font-size: 13px; text-align: left;"></div>
            <div id="successNewsMessage" class="alert-box alert-success hidden"
                style="margin-bottom: 15px; font-size: 13px; text-align: left;"></div>

            {{-- Hidden card id --}}
            <input type="hidden" id="card_id" name="card_id" value="{{ $business_card_details->card_id }}">

            {{-- Email Input --}}
            <div style="margin-bottom: 16px; text-align: left;">
                <input type="email" id="newsletter_email" name="email" class="f-input"
                    placeholder="{{ __('Email Address') }}" required style="width: 100%;">
            </div>

            {{-- Subscribe Button --}}
            <button id="subscribeButton" class="btn-gold" style="width: 100%;">
                {{ $nl_button_text }}
            </button>

            {{-- Privacy Note --}}
            <p
                style="font-size: 11px; color: var(--slate-light); margin-top: 18px; text-transform: uppercase; letter-spacing: 1px; font-weight: 600;">
                <i class="fas fa-lock"
                    style="color: var(--gold); margin-right: 6px;"></i>{{ __('No spam. Unsubscribe at any time.') }}
            </p>

        </div>
    </div>
</div>
