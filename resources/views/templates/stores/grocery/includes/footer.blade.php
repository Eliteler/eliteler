{{-- Luxury Grocery Store Footer with Full Height Clearance --}}
<footer class="store-luxury-footer">
    <div class="store-footer-container">
        <!-- Policy & Legal Links Navigation Grid -->
        <div class="store-footer-policies">
            @if (!empty($business_card_details->privacy_policy))
                <a href="{{ route('store.privacy.policy', $business_card_details->card_url) }}"
                    class="store-policy-link">{{ __('Privacy Policy') }}</a>
            @endif

            @if (!empty($business_card_details->terms_and_conditions))
                <a href="{{ route('store.terms.and.conditions', $business_card_details->card_url) }}"
                    class="store-policy-link">{{ __('Terms & Conditions') }}</a>
            @endif

            @if (!empty($business_card_details->refund_policy))
                <a href="{{ route('store.refund.policy', $business_card_details->card_url) }}"
                    class="store-policy-link">{{ __('Return/Refund Policy') }}</a>
            @endif

            @if (!empty($business_card_details->shipping_policy))
                <a href="{{ route('store.shipping.policy', $business_card_details->card_url) }}"
                    class="store-policy-link">{{ __('Shipping Policy') }}</a>
            @endif

            @if (!empty($business_card_details->cookie_policy))
                <a href="{{ route('store.cookie.policy', $business_card_details->card_url) }}"
                    class="store-policy-link">{{ __('Cookie Policy') }}</a>
            @endif

            @if (!empty($business_card_details->customer_support_policy))
                <a href="{{ route('store.contact.information', $business_card_details->card_url) }}"
                    class="store-policy-link">{{ __('Contact Us') }}</a>
            @endif
        </div>

        <!-- Social Media Icons -->
        @if (isset($shareComponent))
            <div class="store-footer-social-row">
                @if (isset($shareComponent['facebook']))
                    <a href="{{ $shareComponent['facebook'] }}" target="_blank" class="store-social-btn"
                        aria-label="Facebook"><i class="ti ti-brand-facebook"></i></a>
                @endif
                @if (isset($shareComponent['twitter']))
                    <a href="{{ $shareComponent['twitter'] }}" target="_blank" class="store-social-btn"
                        aria-label="Twitter"><i class="ti ti-brand-twitter"></i></a>
                @endif
                @if (isset($shareComponent['linkedin']))
                    <a href="{{ $shareComponent['linkedin'] }}" target="_blank" class="store-social-btn"
                        aria-label="LinkedIn"><i class="ti ti-brand-linkedin"></i></a>
                @endif
                @if (isset($shareComponent['telegram']))
                    <a href="{{ $shareComponent['telegram'] }}" target="_blank" class="store-social-btn"
                        aria-label="Telegram"><i class="ti ti-brand-telegram"></i></a>
                @endif
                @if (isset($shareComponent['whatsapp']))
                    <a href="{{ $shareComponent['whatsapp'] }}" target="_blank" class="store-social-btn"
                        aria-label="WhatsApp"><i class="ti ti-brand-whatsapp"></i></a>
                @endif
            </div>
        @endif

        <!-- Copyright & SaaS Branding Guard -->
        <div class="store-footer-copyright">
            <p class="store-copy-text">
                {{ __('Copyright') }} &copy; {{ date('Y') }}
                <a href="{{ $storeUrl }}" class="store-copy-brand">{{ $card_details->title }}</a>.
                {{ __('All rights reserved.') }}
            </p>
            @if (!isset($plan_details['hide_branding']) || $plan_details['hide_branding'] != 1)
                <p class="store-branding-text">
                    {{ __('Made with') }} <i class="ti ti-heart-filled text-danger"></i>
                    <a href="{{ config('app.url') }}" target="_blank">{{ config('app.name') }}</a>
                </p>
            @endif
        </div>
    </div>
</footer>

<style>
    /* Footer Scoped Styles with 105px Mobile Bottom Clearance */
    .store-luxury-footer {
        width: 100%;
        background-color: #ffffff;
        border-top: 1px solid #c8e6c9;
        padding-top: 40px;
        padding-bottom: 40px;
        margin-top: auto !important;
    }

    @media (max-width: 991.98px) {
        .store-luxury-footer {
            padding-bottom: 105px !important;
            /* Mobile bottom clearance */
        }
    }

    .store-footer-container {
        max-width: 1240px;
        margin: 0 auto;
        padding: 0 20px;
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        gap: 22px;
    }

    .store-footer-policies {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: center;
        gap: 12px 24px;
    }

    .store-policy-link {
        font-size: 13px;
        font-weight: 500;
        color: #424242;
        transition: color 0.2s ease;
    }

    .store-policy-link:hover {
        color: #2e7d32;
    }

    .store-footer-social-row {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .store-social-btn {
        width: 40px;
        height: 40px;
        border-radius: 50% !important;
        background-color: #e8f5e9;
        color: #2e7d32;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 19px;
        transition: all 0.2s ease;
    }

    .store-social-btn:hover {
        background-color: #2e7d32;
        color: #ffffff;
        transform: translateY(-2px);
    }

    .store-footer-copyright {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .store-copy-text,
    .store-branding-text {
        font-size: 13px;
        color: #757575;
        margin: 0;
    }

    .store-copy-brand,
    .store-branding-text a {
        color: #1b5e20;
        font-weight: 600;
    }
</style>
