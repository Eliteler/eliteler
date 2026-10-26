<style>
    .store-luxury-footer {
        background: #1c1917;
        color: #f5f5f4;
        padding: 3rem 0 calc(105px + 1rem);
        /* 105px clearance on mobile */
        border-top: 4px solid #dc2626;
    }

    @media (min-width: 992px) {
        .store-luxury-footer {
            padding: 3.5rem 0 2rem;
        }
    }

    .footer-social-circle {
        width: 38px;
        height: 38px;
        border-radius: 50% !important;
        background: rgba(255, 255, 255, 0.08);
        color: #ffffff !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        transition: all 0.3s ease;
    }

    .footer-social-circle:hover {
        background: #dc2626;
        transform: translateY(-3px);
    }

    .footer-legal-link {
        color: #a8a29e !important;
        font-size: 0.85rem;
        font-weight: 500;
        transition: color 0.2s ease;
    }

    .footer-legal-link:hover {
        color: #ffffff !important;
    }
</style>

<footer class="store-luxury-footer">
    <div class="container-xl px-3 px-lg-4">
        {{-- Eliteler: store map (inside the footer, which is pushed to the bottom of the page) --}}
        @include('templates.stores.shared.store-map', [
            'mapWrapperClass' => 'mb-4',
            'mapCardClass' => 'p-2 rounded-3',
            'mapCardStyle' => 'background: rgba(255, 255, 255, 0.08);',
        ])

        {{-- Row 1: Copyright & Socials --}}
        <div class="row align-items-center gy-3 text-center text-md-start">
            <div class="col-md-6">
                <p class="mb-0 text-white-50 fs-4">
                    {{ __('Copyright') }} &copy; {{ date('Y') }}
                    {{-- Eliteler: copyright link --}}
                    @include('templates.stores.shared.copyright-link', ['copyrightLinkClass' => 'text-white fw-bold']).
                    {{ __('All rights reserved.') }}
                </p>
            </div>
            <div class="col-md-6 text-md-end">
                <div class="d-flex align-items-center justify-content-center justify-content-md-end gap-2">
                    {{-- Eliteler: the store's social links if it has any, otherwise the share links --}}
                    @if (isset($storeSocialLinks) && $storeSocialLinks->where('type', '!=', 'map')->isNotEmpty())
                        @include('templates.stores.shared.social-links', ['socialLinkClass' => 'footer-social-circle'])
                    @else
                    @if (!empty($shareComponent['facebook']))
                        <a href="{{ $shareComponent['facebook'] }}" target="_blank" class="footer-social-circle"><i
                                class="ti ti-brand-facebook"></i></a>
                    @endif
                    @if (!empty($shareComponent['twitter']))
                        <a href="{{ $shareComponent['twitter'] }}" target="_blank" class="footer-social-circle"><i
                                class="ti ti-brand-x"></i></a>
                    @endif
                    @if (!empty($shareComponent['linkedin']))
                        <a href="{{ $shareComponent['linkedin'] }}" target="_blank" class="footer-social-circle"><i
                                class="ti ti-brand-linkedin"></i></a>
                    @endif
                    @if (!empty($shareComponent['whatsapp']))
                        <a href="{{ $shareComponent['whatsapp'] }}" target="_blank" class="footer-social-circle"><i
                                class="ti ti-brand-whatsapp"></i></a>
                    @endif
                    @endif
                </div>
            </div>
        </div>

        <hr class="border-secondary opacity-25 my-4">

        {{-- Row 2: All 6 Legal Policies Guard --}}
        <div class="row">
            <div class="col-12">
                <ul
                    class="list-inline text-center text-md-start mb-0 d-flex flex-wrap justify-content-center justify-content-md-start gap-3">
                    @if ($business_card_details->privacy_policy)
                        <li class="list-inline-item m-0">
                            <a href="{{ route('store.privacy.policy', $business_card_details->card_url) }}"
                                class="footer-legal-link">{{ __('Privacy Policy') }}</a>
                        </li>
                    @endif
                    @if ($business_card_details->terms_and_conditions)
                        <li class="list-inline-item m-0">
                            <a href="{{ route('store.terms.and.conditions', $business_card_details->card_url) }}"
                                class="footer-legal-link">{{ __('Terms & Conditions') }}</a>
                        </li>
                    @endif
                    @if ($business_card_details->refund_policy)
                        <li class="list-inline-item m-0">
                            <a href="{{ route('store.refund.policy', $business_card_details->card_url) }}"
                                class="footer-legal-link">{{ __('Return/Refund Policy') }}</a>
                        </li>
                    @endif
                    @if ($business_card_details->shipping_policy)
                        <li class="list-inline-item m-0">
                            <a href="{{ route('store.shipping.policy', $business_card_details->card_url) }}"
                                class="footer-legal-link">{{ __('Shipping Policy') }}</a>
                        </li>
                    @endif
                    @if ($business_card_details->cookie_policy)
                        <li class="list-inline-item m-0">
                            <a href="{{ route('store.cookie.policy', $business_card_details->card_url) }}"
                                class="footer-legal-link">{{ __('Cookie Policy') }}</a>
                        </li>
                    @endif
                    @if ($business_card_details->customer_support_policy)
                        <li class="list-inline-item m-0">
                            <a href="{{ route('store.contact.information', $business_card_details->card_url) }}"
                                class="footer-legal-link">{{ __('Contact Us') }}</a>
                        </li>
                    @endif
                </ul>
            </div>
        </div>
    </div>
</footer>
