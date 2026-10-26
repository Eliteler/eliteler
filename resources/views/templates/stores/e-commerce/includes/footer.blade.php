{{-- Eliteler: store map --}}
@include('templates.stores.shared.store-map')

<footer class="footer bg-dark d-print-none mb-7 mb-lg-0 container-xl store-footer-section">
    <div class="container-xl">
        <!-- Copyright & Social Icons Row -->
        <div class="row text-center text-lg-start align-items-center">
            <div class="col-lg-6 mb-2 mb-lg-0">
                <span class="text-white">
                    {{ __('Copyright') }} &copy; {{ date('Y') }}
                    {{-- Eliteler: copyright link --}}
                    @include('templates.stores.shared.copyright-link', ['copyrightLinkClass' => 'text-e-commerce-blue fw-semibold text-decoration-none']).
                    {{ __('All rights reserved.') }}
                    @if (!isset($plan_details['hide_branding']) || $plan_details['hide_branding'] != 1)
                        <br>
                        {{ __('Made with') }} <i class="ti ti-heart text-danger"></i>
                        {{ !empty($business_card_details->copyright) ? $business_card_details->copyright : parse_url(config('app.url'), PHP_URL_HOST) }}
                    @endif
                </span>
            </div>
            <div class="col-lg-6 text-lg-end">
                <ul class="list-inline list-inline-dots mb-0">
                    {{-- Eliteler: the store's social links if it has any, otherwise the share links --}}
                    @if (isset($storeSocialLinks) && $storeSocialLinks->where('type', '!=', 'map')->isNotEmpty())
                        @include('templates.stores.shared.social-links', [
                            'socialItemTag' => 'li',
                            'socialItemClass' => 'list-inline-item',
                            'socialLinkClass' => 'text-e-commerce-blue fs-2',
                        ])
                    @else
                    @if (!empty($shareComponent['facebook']))
                        <li class="list-inline-item">
                            <a href="{{ $shareComponent['facebook'] }}" target="_blank"
                                class="text-e-commerce-blue fs-2">
                                <i class="ti ti-brand-facebook-filled"></i>
                            </a>
                        </li>
                    @endif
                    @if (!empty($shareComponent['twitter']))
                        <li class="list-inline-item">
                            <a href="{{ $shareComponent['twitter'] }}" target="_blank"
                                class="text-e-commerce-blue fs-2">
                                <i class="ti ti-brand-twitter-filled"></i>
                            </a>
                        </li>
                    @endif
                    @if (!empty($shareComponent['linkedin']))
                        <li class="list-inline-item">
                            <a href="{{ $shareComponent['linkedin'] }}" target="_blank"
                                class="text-e-commerce-blue fs-2">
                                <i class="ti ti-brand-linkedin"></i>
                            </a>
                        </li>
                    @endif
                    @if (!empty($shareComponent['telegram']))
                        <li class="list-inline-item">
                            <a href="{{ $shareComponent['telegram'] }}" target="_blank"
                                class="text-e-commerce-blue fs-2">
                                <i class="ti ti-brand-telegram"></i>
                            </a>
                        </li>
                    @endif
                    @if (!empty($shareComponent['whatsapp']))
                        <li class="list-inline-item">
                            <a href="{{ $shareComponent['whatsapp'] }}" target="_blank"
                                class="text-e-commerce-blue fs-2">
                                <i class="ti ti-brand-whatsapp"></i>
                            </a>
                        </li>
                    @endif
                    @endif
                </ul>
            </div>
        </div>

        <!-- Store Policy Links Row -->
        <div class="row mt-3">
            <div class="col">
                <ul class="list-inline list-inline-dots text-center text-md-start text-lg-start mb-0">
                    @if ($business_card_details->privacy_policy)
                        <li class="list-inline-item">
                            <a href="{{ route('store.privacy.policy', $business_card_details->card_url) }}"
                                class="text-white text-decoration-none">
                                {{ __('Privacy Policy') }}
                            </a>
                        </li>
                    @endif
                    @if ($business_card_details->terms_and_conditions)
                        <li class="list-inline-item">
                            <a href="{{ route('store.terms.and.conditions', $business_card_details->card_url) }}"
                                class="text-white text-decoration-none">
                                {{ __('Terms & Conditions') }}
                            </a>
                        </li>
                    @endif
                    @if ($business_card_details->refund_policy)
                        <li class="list-inline-item">
                            <a href="{{ route('store.refund.policy', $business_card_details->card_url) }}"
                                class="text-white text-decoration-none">
                                {{ __('Return/Refund Policy') }}
                            </a>
                        </li>
                    @endif
                    @if ($business_card_details->shipping_policy)
                        <li class="list-inline-item">
                            <a href="{{ route('store.shipping.policy', $business_card_details->card_url) }}"
                                class="text-white text-decoration-none">
                                {{ __('Shipping Policy') }}
                            </a>
                        </li>
                    @endif
                    @if ($business_card_details->cookie_policy)
                        <li class="list-inline-item">
                            <a href="{{ route('store.cookie.policy', $business_card_details->card_url) }}"
                                class="text-white text-decoration-none">
                                {{ __('Cookie Policy') }}
                            </a>
                        </li>
                    @endif
                    @if ($business_card_details->customer_support_policy)
                        <li class="list-inline-item">
                            <a href="{{ route('store.contact.information', $business_card_details->card_url) }}"
                                class="text-white text-decoration-none">
                                {{ __('Contact Us') }}
                            </a>
                        </li>
                    @endif
                </ul>
            </div>
        </div>
    </div>
</footer>
