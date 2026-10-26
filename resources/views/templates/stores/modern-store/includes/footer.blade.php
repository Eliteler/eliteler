{{--
    Modern store footer: copyright, share links and the store's policy pages.
--}}
<footer class="footer footer-transparent d-print-none mb-7 mb-lg-0">
    <div class="container-xl">
        <div class="row text-center text-lg-start align-items-center">
            <div class="col-lg-6 mb-2 mb-lg-0">
                <span class="{{ $storeTheme->isDark() ? 'text-white' : 'text-muted' }}">
                    {{ __('Copyright') }} &copy;
                    {{ date('Y') }}
                    <a href="{{ $storeUrl }}" class="{{ $storeTheme->isDark() ? 'text-white' : 'text-secondary' }} fw-semibold">
                        {{ $card_details->title }}
                    </a>.
                    {{ __('All rights reserved.') }} <br>
                    @if (!isset($plan_details['hide_branding']) || $plan_details['hide_branding'] != 1)
                        {{ __('Made with') }} <i class="ti ti-heart text-danger"></i> <a
                            href="{{ config('app.url') }}">{{ config('app.name') }}</a>.
                    @endif
                </span>
            </div>
            <div class="col-lg-6 text-lg-end">
                <ul class="list-inline list-inline-dots mb-0">
                    <li class="list-inline-item">
                        <a href="{{ $shareComponent['facebook'] }}" target="_blank" class="link-light">
                            <i class="ti ti-brand-facebook-filled text-{{ $storeTheme->color }}"></i>
                        </a>
                    </li>
                    <li class="list-inline-item">
                        <a href="{{ $shareComponent['twitter'] }}" target="_blank" class="link-light">
                            <i class="ti ti-brand-twitter-filled text-{{ $storeTheme->color }}"></i>
                        </a>
                    </li>
                    <li class="list-inline-item">
                        <a href="{{ $shareComponent['linkedin'] }}" target="_blank" class="link-light">
                            <i class="ti ti-brand-linkedin text-{{ $storeTheme->color }}"></i>
                        </a>
                    </li>
                    <li class="list-inline-item">
                        <a href="{{ $shareComponent['telegram'] }}" target="_blank" class="link-light">
                            <i class="ti ti-brand-telegram text-{{ $storeTheme->color }}"></i>
                        </a>
                    </li>
                    <li class="list-inline-item">
                        <a href="{{ $shareComponent['whatsapp'] }}" target="_blank" class="link-light">
                            <i class="ti ti-brand-whatsapp text-{{ $storeTheme->color }}"></i>
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <div class="row mt-3">
            <div class="col">
                <ul class="list-inline list-inline-dots text-center text-md-start text-lg-start mb-0">
                    @if ($business_card_details->privacy_policy)
                        <li class="list-inline-item">
                            <a href="{{ route('store.privacy.policy', $business_card_details->card_url) }}"
                                class="link-secondary">{{ __('Privacy Policy') }}</a>
                        </li>
                    @endif

                    @if ($business_card_details->terms_and_conditions)
                        <li class="list-inline-item">
                            <a href="{{ route('store.terms.and.conditions', $business_card_details->card_url) }}"
                                class="link-secondary">{{ __('Terms & Conditions') }}</a>
                        </li>
                    @endif

                    @if ($business_card_details->refund_policy)
                        <li class="list-inline-item">
                            <a href="{{ route('store.refund.policy', $business_card_details->card_url) }}"
                                class="link-secondary">{{ __('Return/Refund Policy') }}</a>
                        </li>
                    @endif

                    @if ($business_card_details->shipping_policy)
                        <li class="list-inline-item">
                            <a href="{{ route('store.shipping.policy', $business_card_details->card_url) }}"
                                class="link-secondary">{{ __('Shipping Policy') }}</a>
                        </li>
                    @endif

                    @if ($business_card_details->cookie_policy)
                        <li class="list-inline-item">
                            <a href="{{ route('store.cookie.policy', $business_card_details->card_url) }}"
                                class="link-secondary">{{ __('Cookie Policy') }}</a>
                        </li>
                    @endif

                    @if ($business_card_details->customer_support_policy)
                        <li class="list-inline-item">
                            <a href="{{ route('store.contact.information', $business_card_details->card_url) }}"
                                class="link-secondary">{{ __('Contact Us') }}</a>
                        </li>
                    @endif
                </ul>
            </div>
        </div>
    </div>
</footer>
