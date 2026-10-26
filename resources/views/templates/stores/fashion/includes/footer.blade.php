{{-- Footer Component --}}
<style>
    .fashion-footer {
        background-color: #ffffff;
        border-top: 1px solid #e5e7eb;
        padding: 40px 0 90px 0;
        color: #4b5563;
        font-size: 14px;
    }

    .fashion-social-link {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 38px;
        height: 38px;
        background-color: #f3f4f6;
        color: #111827;
        margin-right: 6px;
        transition: all 0.2s ease;
        text-decoration: none;
    }

    .fashion-social-link:hover {
        background-color: #111827;
        color: #ffffff;
    }

    .fashion-legal-link {
        color: #6b7280;
        text-decoration: none;
        margin: 0 10px;
        transition: color 0.2s ease;
    }

    .fashion-legal-link:hover {
        color: #111827;
        text-decoration: underline;
    }

    .fashion-brand-guard {
        color: #111827;
        font-weight: 600;
        text-decoration: none;
    }

    @media (min-width: 992px) {
        .fashion-footer {
            padding-bottom: 40px;
        }
    }
</style>

{{-- Eliteler: store map --}}
@include('templates.stores.shared.store-map', [
    'mapCardClass' => 'p-2 bg-white border',
])

<footer class="fashion-footer d-print-none">
    <div class="container-xl">
        <div class="row align-items-center gy-3 text-center text-lg-start">
            <div class="col-lg-6">
                <p class="mb-1">
                    {{ __('Copyright') }} &copy; <span id="yearCurrent">{{ date('Y') }}</span>
                    {{-- Eliteler: copyright link --}}
                    @include('templates.stores.shared.copyright-link', ['copyrightLinkClass' => 'fashion-brand-guard']).
                    {{ __('All rights reserved.') }}
                </p>
                @if (!isset($plan_details['hide_branding']) || $plan_details['hide_branding'] != 1)
                    <p class="mb-0 text-muted">
                        {{ __('Made with') }} <span class="text-danger">&hearts;</span>
                        {{ !empty($business_card_details->copyright) ? $business_card_details->copyright : parse_url(config('app.url'), PHP_URL_HOST) }}
                    </p>
                @endif
            </div>

            <div class="col-lg-6 text-center text-lg-end">
                {{-- Eliteler: the store's social links if it has any, otherwise the share links --}}
                @if (isset($storeSocialLinks) && $storeSocialLinks->where('type', '!=', 'map')->isNotEmpty())
                    <div class="d-inline-flex flex-wrap justify-content-center">
                        @include('templates.stores.shared.social-links', ['socialLinkClass' => 'fashion-social-link'])
                    </div>
                @elseif (isset($shareComponent))
                    <div class="d-inline-flex">
                        @if (!empty($shareComponent['facebook']))
                            <a href="{{ $shareComponent['facebook'] }}" target="_blank" class="fashion-social-link"
                                aria-label="Facebook">
                                <i class="ti ti-brand-facebook-filled"></i>
                            </a>
                        @endif
                        @if (!empty($shareComponent['twitter']))
                            <a href="{{ $shareComponent['twitter'] }}" target="_blank" class="fashion-social-link"
                                aria-label="Twitter">
                                <i class="ti ti-brand-twitter-filled"></i>
                            </a>
                        @endif
                        @if (!empty($shareComponent['linkedin']))
                            <a href="{{ $shareComponent['linkedin'] }}" target="_blank" class="fashion-social-link"
                                aria-label="LinkedIn">
                                <i class="ti ti-brand-linkedin"></i>
                            </a>
                        @endif
                        @if (!empty($shareComponent['whatsapp']))
                            <a href="{{ $shareComponent['whatsapp'] }}" target="_blank" class="fashion-social-link"
                                aria-label="WhatsApp">
                                <i class="ti ti-brand-whatsapp"></i>
                            </a>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        {{-- Legal Policies --}}
        <div class="row mt-4 pt-3 border-top">
            <div class="col text-center">
                @if (!empty($business_card_details->privacy_policy))
                    <a href="{{ route('store.privacy.policy', $business_card_details->card_url) }}"
                        class="fashion-legal-link">{{ __('Privacy Policy') }}</a>
                @endif
                @if (!empty($business_card_details->terms_and_conditions))
                    <a href="{{ route('store.terms.and.conditions', $business_card_details->card_url) }}"
                        class="fashion-legal-link">{{ __('Terms & Conditions') }}</a>
                @endif
                @if (!empty($business_card_details->refund_policy))
                    <a href="{{ route('store.refund.policy', $business_card_details->card_url) }}"
                        class="fashion-legal-link">{{ __('Return Policy') }}</a>
                @endif
                @if (!empty($business_card_details->shipping_policy))
                    <a href="{{ route('store.shipping.policy', $business_card_details->card_url) }}"
                        class="fashion-legal-link">{{ __('Shipping Policy') }}</a>
                @endif
                @if (!empty($business_card_details->cookie_policy))
                    <a href="{{ route('store.cookie.policy', $business_card_details->card_url) }}"
                        class="fashion-legal-link">{{ __('Cookie Policy') }}</a>
                @endif
                @if (!empty($business_card_details->customer_support_policy))
                    <a href="{{ route('store.contact.information', $business_card_details->card_url) }}"
                        class="fashion-legal-link">{{ __('Contact Us') }}</a>
                @endif
            </div>
        </div>
    </div>
</footer>
