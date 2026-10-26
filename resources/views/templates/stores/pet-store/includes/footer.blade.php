{{-- Pet store footer: wave, copyright, share links and the store's policy pages --}}
<div class="footer-wave">
    <svg viewBox="0 0 1440 100" preserveAspectRatio="none">
        <path d="M0,0 C360,200 1080,-100 1440,100 L1440,0 L0,0 Z" fill="#E96629" />
    </svg>
</div>
<footer class="footer footer-transparent mb-7 mb-lg-0">
    <div class="container-xl">
        <!-- First Row: Copyright and Social Links -->
        <div class="row text-center text-lg-start align-items-center">
            <div class="col-lg-6 mb-2 mb-lg-0">
                <span class="text-white">
                    {{ __('Copyright') }} &copy;
                    {{ date('Y') }}
                    <a href="{{ $storeUrl }}" class="link-light fw-semibold">
                        {{ $card_details->title }}
                    </a>.
                    {{ __('All rights reserved.') }} <br>
                    @if (!isset($plan_details['hide_branding']) || $plan_details['hide_branding'] != 1)
                        {{ __('Made with') }} <i class="ti ti-heart text-white"></i> <a
                            href="{{ url('/') }}">{{ config('app.name') }}</a>.
                    @endif
                </span>
            </div>
            <div class="col-lg-6 text-lg-end">
                <ul class="list-inline list-inline-dots mb-0">
                    <li class="list-inline-item">
                        <a href="{{ $shareComponent['facebook'] }}" target="_blank"
                            class="link-light">
                            <i class="ti ti-brand-facebook-filled text-white"></i>
                        </a>
                    </li>
                    <li class="list-inline-item">
                        <a href="{{ $shareComponent['twitter'] }}" target="_blank"
                            class="link-light">
                            <i class="ti ti-brand-twitter-filled text-white"></i>
                        </a>
                    </li>
                    <li class="list-inline-item">
                        <a href="{{ $shareComponent['linkedin'] }}" target="_blank"
                            class="link-light">
                            <i class="ti ti-brand-linkedin text-white"></i>
                        </a>
                    </li>
                    <li class="list-inline-item">
                        <a href="{{ $shareComponent['telegram'] }}" target="_blank"
                            class="link-light">
                            <i class="ti ti-brand-telegram text-white"></i>
                        </a>
                    </li>
                    <li class="list-inline-item">
                        <a href="{{ $shareComponent['whatsapp'] }}" target="_blank"
                            class="link-light">
                            <i class="ti ti-brand-whatsapp text-white"></i>
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Second Row: Policy Links -->
        <div class="row mt-3">
            <div class="col">
                <ul
                    class="list-inline list-inline-dots text-center text-md-start text-lg-start mb-0">
                    {{-- Privacy Policy --}}
                    @if ($business_card_details->privacy_policy)
                        <li class="list-inline-item">
                            <a href="{{ route('store.privacy.policy', $business_card_details->card_url) }}"
                                class="text-white">{{ __('Privacy Policy') }}</a>
                        </li>
                    @endif

                    {{-- Terms and Conditions --}}
                    @if ($business_card_details->terms_and_conditions)
                        <li class="list-inline-item">
                            <a href="{{ route('store.terms.and.conditions', $business_card_details->card_url) }}"
                                class="text-white">{{ __('Terms & Conditions') }}</a>
                        </li>
                    @endif

                    {{-- Return/Refund Policy --}}
                    @if ($business_card_details->refund_policy)
                        <li class="list-inline-item">
                            <a href="{{ route('store.refund.policy', $business_card_details->card_url) }}"
                                class="text-white">{{ __('Return/Refund Policy') }}</a>
                        </li>
                    @endif

                    {{-- Shipping Policy --}}
                    @if ($business_card_details->shipping_policy)
                        <li class="list-inline-item">
                            <a href="{{ route('store.shipping.policy', $business_card_details->card_url) }}"
                                class="text-white">{{ __('Shipping Policy') }}</a>
                        </li>
                    @endif

                    {{-- Cookie Policy --}}
                    @if ($business_card_details->cookie_policy)
                        <li class="list-inline-item">
                            <a href="{{ route('store.cookie.policy', $business_card_details->card_url) }}"
                                class="text-white">{{ __('Cookie Policy') }}</a>
                        </li>
                    @endif

                    {{-- Contact Information / Customer Support Policy --}}
                    @if ($business_card_details->customer_support_policy)
                        <li class="list-inline-item">
                            <a href="{{ route('store.contact.information', $business_card_details->card_url) }}"
                                class="text-white">{{ __('Contact Us') }}</a>
                        </li>
                    @endif
                </ul>
            </div>
        </div>
    </div>
</footer>
