{{-- Footer Component --}}
<footer class="store-luxury-footer">
    <div class="footer-inner-box">
        <!-- Top row: Branding & Social Icons -->
        <div class="footer-meta-row">
            <div class="footer-copy-info">
                <p class="footer-brand-title">{{ $card_details->title }}</p>
                <p class="footer-copy-text">
                    {{ __('Copyright') }} &copy; {{ date('Y') }}
                    <a href="{{ $storeUrl }}" class="footer-inline-link">{{ $card_details->title }}</a>.
                    {{ __('All rights reserved.') }}
                </p>
                @if (!isset($plan_details['hide_branding']) || $plan_details['hide_branding'] != 1)
                    <p class="footer-maker-note">
                        {{ __('Made with') }} <i class="ti ti-heart text-danger"></i>
                        <a href="{{ config('app.url') }}" target="_blank"
                            class="footer-maker-link">{{ config('app.name') }}</a>
                    </p>
                @endif
            </div>

            <div class="footer-social-row">
                @if (!empty($shareComponent['facebook']))
                    <a href="{{ $shareComponent['facebook'] }}" target="_blank" class="footer-social-circle"
                        aria-label="Facebook">
                        <i class="ti ti-brand-facebook-filled"></i>
                    </a>
                @endif
                @if (!empty($shareComponent['twitter']))
                    <a href="{{ $shareComponent['twitter'] }}" target="_blank" class="footer-social-circle"
                        aria-label="Twitter">
                        <i class="ti ti-brand-twitter-filled"></i>
                    </a>
                @endif
                @if (!empty($shareComponent['linkedin']))
                    <a href="{{ $shareComponent['linkedin'] }}" target="_blank" class="footer-social-circle"
                        aria-label="LinkedIn">
                        <i class="ti ti-brand-linkedin"></i>
                    </a>
                @endif
                @if (!empty($shareComponent['telegram']))
                    <a href="{{ $shareComponent['telegram'] }}" target="_blank" class="footer-social-circle"
                        aria-label="Telegram">
                        <i class="ti ti-brand-telegram"></i>
                    </a>
                @endif
                @if (!empty($shareComponent['whatsapp']))
                    <a href="{{ $shareComponent['whatsapp'] }}" target="_blank" class="footer-social-circle"
                        aria-label="WhatsApp">
                        <i class="ti ti-brand-whatsapp"></i>
                    </a>
                @endif
            </div>
        </div>

        <hr class="footer-divider" />

        <!-- Bottom row: Complete 6 Legal Policies -->
        <div class="footer-policies-row">
            <ul class="footer-policy-list">
                @if (!empty($business_card_details->privacy_policy))
                    <li><a href="{{ route('store.privacy.policy', $business_card_details->card_url) }}"
                            class="footer-policy-link">{{ __('Privacy Policy') }}</a></li>
                @endif
                @if (!empty($business_card_details->terms_and_conditions))
                    <li><a href="{{ route('store.terms.and.conditions', $business_card_details->card_url) }}"
                            class="footer-policy-link">{{ __('Terms & Conditions') }}</a></li>
                @endif
                @if (!empty($business_card_details->refund_policy))
                    <li><a href="{{ route('store.refund.policy', $business_card_details->card_url) }}"
                            class="footer-policy-link">{{ __('Return/Refund Policy') }}</a></li>
                @endif
                @if (!empty($business_card_details->shipping_policy))
                    <li><a href="{{ route('store.shipping.policy', $business_card_details->card_url) }}"
                            class="footer-policy-link">{{ __('Shipping Policy') }}</a></li>
                @endif
                @if (!empty($business_card_details->cookie_policy))
                    <li><a href="{{ route('store.cookie.policy', $business_card_details->card_url) }}"
                            class="footer-policy-link">{{ __('Cookie Policy') }}</a></li>
                @endif
                @if (!empty($business_card_details->customer_support_policy))
                    <li><a href="{{ route('store.contact.information', $business_card_details->card_url) }}"
                            class="footer-policy-link">{{ __('Contact Us') }}</a></li>
                @endif
            </ul>
        </div>
    </div>
</footer>

<style>
    .store-luxury-footer {
        background: #1e1910;
        color: #f5eedf;
        padding-top: 45px;
        padding-bottom: 45px;
        border-top: 1px solid rgba(212, 175, 55, 0.25);
    }

    @media (max-width: 991px) {
        .store-luxury-footer {
            padding-bottom: 105px !important;
            /* Mobile bottom clearance */
        }
    }

    .footer-inner-box {
        max-width: 1240px;
        margin: 0 auto;
        padding: 0 20px;
    }

    .footer-meta-row {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        align-items: center;
        gap: 20px;
    }

    .footer-brand-title {
        font-family: 'Gloock', serif;
        font-size: 20px;
        color: #d4af37;
        margin: 0 0 6px 0;
    }

    .footer-copy-text {
        font-size: 13px;
        color: #c9bc9e;
        margin: 0;
    }

    .footer-inline-link {
        color: #d4af37 !important;
        font-weight: 600;
    }

    .footer-maker-note {
        font-size: 12px;
        color: #9c8e71;
        margin: 4px 0 0 0;
    }

    .footer-maker-link {
        color: #d4af37 !important;
    }

    .footer-social-row {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .footer-social-circle {
        width: 38px;
        height: 38px;
        border-radius: 50% !important;
        background: rgba(212, 175, 55, 0.15);
        border: 1px solid rgba(212, 175, 55, 0.35);
        color: #d4af37 !important;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        transition: all 0.25s ease;
    }

    .footer-social-circle:hover {
        background: #d4af37;
        color: #1e1910 !important;
        transform: translateY(-2px);
    }

    .footer-divider {
        margin: 28px 0 20px 0;
        border-color: rgba(212, 175, 55, 0.15);
    }

    .footer-policies-row {
        display: flex;
        justify-content: center;
    }

    .footer-policy-list {
        list-style: none;
        padding: 0;
        margin: 0;
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 16px 24px;
    }

    .footer-policy-link {
        font-size: 13px;
        color: #c9bc9e !important;
        transition: color 0.2s;
    }

    .footer-policy-link:hover {
        color: #d4af37 !important;
    }
</style>
