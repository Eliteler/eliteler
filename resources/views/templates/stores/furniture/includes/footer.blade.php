<style>
    /* Luxury Camel Sticky Footer (Firmly Anchored to Bottom) */
    .store-luxury-footer {
        background-color: #cca47a;
        color: #ffffff;
        padding: 36px 20px 28px;
        border-top: 1px solid #b58c62;
        margin-top: auto !important;
        /* Forces footer to the absolute bottom */
        width: 100% !important;
    }

    /* 100px Mobile Bottom Clearance for Fixed 75px Bottom Bar */
    @media (max-width: 768px) {
        .store-luxury-footer {
            padding-bottom: 105px !important;
        }
    }

    .footer-brand-heading {
        font-family: 'DM Serif Display', serif;
        font-size: 24px;
        color: #ffffff;
        letter-spacing: 0.5px;
    }

    .footer-social-icon-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 38px;
        height: 38px;
        border-radius: 50%;
        background-color: rgba(255, 255, 255, 0.2);
        color: #ffffff;
        margin: 0 4px;
        transition: all 0.2s ease;
    }

    .footer-social-icon-btn:hover {
        background-color: #1f4b48;
        color: #ffffff;
        transform: translateY(-2px);
    }

    .footer-legal-links-list {
        display: flex;
        flex-wrap: wrap;
        gap: 16px;
        list-style: none;
        padding: 0;
        margin: 16px 0 0 0;
    }

    .footer-legal-nav-link {
        color: rgba(255, 255, 255, 0.9);
        font-size: 13px;
        font-weight: 500;
        transition: color 0.2s ease;
    }

    .footer-legal-nav-link:hover {
        color: #1f4b48;
    }
</style>

<footer class="store-luxury-footer">
    {{-- Eliteler: store map (inside the footer, which is pushed to the bottom of the page) --}}
    @include('templates.stores.shared.store-map', [
        'mapWrapperClass' => 'mb-4',
        'mapCardClass' => 'p-2 bg-white rounded-3 shadow-sm',
    ])

    <div class="row align-items-center text-center text-md-start">
        <div class="col-md-6 mb-3 mb-md-0">
            <h3 class="footer-brand-heading mb-1">{{ $card_details->title }}</h3>
            <p class="mb-0 fs-4 text-white-50">
                &copy; <span id="footerYear"></span>
                {{-- Eliteler: copyright link --}}
                @include('templates.stores.shared.copyright-link', ['copyrightLinkClass' => 'text-white fw-bold']).
                {{ __('All Rights Reserved.') }}
            </p>
            @if (!isset($plan_details['hide_branding']) || $plan_details['hide_branding'] != 1)
                <small class="text-white-50 d-block mt-1">
                    {{ __('Powered by') }}
                    <span class="text-white fw-bold">{{ !empty($business_card_details->copyright) ? $business_card_details->copyright : parse_url(config('app.url'), PHP_URL_HOST) }}</span>
                </small>
            @endif
        </div>

        {{-- Social Media Share Channels --}}
        <div class="col-md-6 text-center text-md-end">
            {{-- Eliteler: the store's social links if it has any, otherwise the share links --}}
            @if (isset($storeSocialLinks) && $storeSocialLinks->where('type', '!=', 'map')->isNotEmpty())
                <div>
                    @include('templates.stores.shared.social-links', [
                        'socialLinkClass' => 'footer-social-icon-btn',
                        'socialIconClass' => 'fs-3',
                    ])
                </div>
            @elseif (isset($shareComponent))
                <div>
                    @if (!empty($shareComponent['facebook']))
                        <a href="{{ $shareComponent['facebook'] }}" target="_blank" class="footer-social-icon-btn"
                            aria-label="Facebook"><i class="ti ti-brand-facebook fs-2"></i></a>
                    @endif
                    @if (!empty($shareComponent['twitter']))
                        <a href="{{ $shareComponent['twitter'] }}" target="_blank" class="footer-social-icon-btn"
                            aria-label="Twitter"><i class="ti ti-brand-twitter fs-2"></i></a>
                    @endif
                    @if (!empty($shareComponent['whatsapp']))
                        <a href="{{ $shareComponent['whatsapp'] }}" target="_blank" class="footer-social-icon-btn"
                            aria-label="WhatsApp"><i class="ti ti-brand-whatsapp fs-2"></i></a>
                    @endif
                </div>
            @endif
        </div>
    </div>

    {{-- Legal Pages Policy Bar --}}
    <div class="border-top border-white-50 pt-3 mt-4">
        <ul class="footer-legal-links-list justify-content-center justify-content-md-start">
            {{-- 1. Privacy Policy --}}
            @if ($business_card_details->privacy_policy)
                <li><a href="{{ route('store.privacy.policy', $business_card_details->card_url) }}"
                        class="footer-legal-nav-link">{{ __('Privacy Policy') }}</a></li>
            @endif

            {{-- 2. Terms and Conditions --}}
            @if ($business_card_details->terms_and_conditions)
                <li><a href="{{ route('store.terms.and.conditions', $business_card_details->card_url) }}"
                        class="footer-legal-nav-link">{{ __('Terms & Conditions') }}</a></li>
            @endif

            {{-- 3. Return / Refund Policy --}}
            @if ($business_card_details->refund_policy)
                <li><a href="{{ route('store.refund.policy', $business_card_details->card_url) }}"
                        class="footer-legal-nav-link">{{ __('Refund Policy') }}</a></li>
            @endif

            {{-- 4. Shipping Policy --}}
            @if ($business_card_details->shipping_policy)
                <li><a href="{{ route('store.shipping.policy', $business_card_details->card_url) }}"
                        class="footer-legal-nav-link">{{ __('Shipping Policy') }}</a></li>
            @endif

            {{-- 5. Cookie Policy --}}
            @if ($business_card_details->cookie_policy)
                <li><a href="{{ route('store.cookie.policy', $business_card_details->card_url) }}"
                        class="footer-legal-nav-link">{{ __('Cookie Policy') }}</a></li>
            @endif

            {{-- 6. Contact Information / Support Policy --}}
            @if ($business_card_details->customer_support_policy)
                <li><a href="{{ route('store.contact.information', $business_card_details->card_url) }}"
                        class="footer-legal-nav-link">{{ __('Contact Us') }}</a></li>
            @endif
        </ul>
    </div>
</footer>

<script>
    const yrEl = document.getElementById('footerYear');
    if (yrEl) yrEl.textContent = new Date().getFullYear();
</script>
