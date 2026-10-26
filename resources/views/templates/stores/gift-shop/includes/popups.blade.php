<style>
    /* Scoped High-End Popup Modal Styling */
    .store-popup-overlay {
        background-color: rgba(15, 23, 42, 0.8) !important;
        backdrop-filter: blur(8px) !important;
        -webkit-backdrop-filter: blur(8px) !important;
    }

    .store-popup-card {
        border-radius: 28px !important;
        border: none;
        overflow: hidden;
        background: #ffffff;
        box-shadow: 0 25px 60px rgba(0, 0, 0, 0.25);
        padding: 32px 26px 28px;
        position: relative;
    }

    .popup-close-circle {
        position: absolute;
        top: 16px;
        right: 16px;
        width: 36px;
        height: 36px;
        border-radius: 50% !important;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        color: #78716c;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        cursor: pointer;
        transition: all 0.25s ease;
        z-index: 10;
    }

    .popup-close-circle:hover {
        background: #fee2e2;
        color: #dc2626;
        border-color: #fca5a5;
        transform: rotate(90deg);
    }

    .popup-icon-pod {
        width: 84px;
        height: 84px;
        border-radius: 50% !important;
        background: #fef2f2;
        color: #dc2626;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.8rem;
        margin: 0 auto 1.25rem;
        box-shadow: 0 10px 25px rgba(220, 38, 38, 0.12);
    }

    .popup-promo-img-box {
        width: 100%;
        max-height: 200px;
        border-radius: 20px;
        overflow: hidden;
        margin-bottom: 1.25rem;
        background: #fef2f2;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.06);
    }

    .popup-promo-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .popup-pill-input {
        border-radius: 50px !important;
        border: 1.5px solid #e2e8f0;
        background: #f8fafc;
        padding: 12px 20px;
        font-size: 0.95rem;
        color: #1c1917;
        text-align: center;
        transition: all 0.2s ease;
    }

    .popup-pill-input:focus {
        border-color: #dc2626;
        background: #ffffff;
        box-shadow: 0 0 0 4px rgba(220, 38, 38, 0.12);
        outline: none;
    }

    .btn-popup-submit {
        border-radius: 50px !important;
        background: #dc2626;
        color: #ffffff !important;
        border: none;
        padding: 12px 24px;
        font-size: 0.95rem;
        font-weight: 700;
        letter-spacing: 0.3px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        transition: all 0.3s ease;
        box-shadow: 0 6px 18px rgba(220, 38, 38, 0.3);
        width: 100%;
        cursor: pointer;
    }

    .btn-popup-submit:hover {
        background: #b91c1c;
        box-shadow: 0 10px 25px rgba(220, 38, 38, 0.4);
        transform: translateY(-2px);
    }

    .btn-popup-promo {
        border-radius: 50px !important;
        background: #1c1917;
        color: #ffffff !important;
        border: none;
        padding: 12px 24px;
        font-size: 0.95rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        transition: all 0.3s ease;
        width: 100%;
        box-shadow: 0 6px 18px rgba(0, 0, 0, 0.2);
    }

    .btn-popup-promo:hover {
        background: #dc2626;
        box-shadow: 0 10px 25px rgba(220, 38, 38, 0.35);
        transform: translateY(-2px);
    }
</style>

{{-- 1. VIP Newsletter Subscription Modal --}}
@if (
    !empty($business_card_details->is_newsletter_pop_active) &&
        $business_card_details->is_newsletter_pop_active == 1 &&
        !request()->cookie('newsletter_' . $business_card_details->card_id) &&
        empty(request()->getQueryString()))
    <div class="modal fade store-popup-overlay" id="newsletterModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content store-popup-card text-center">
                {{-- Circular Close Button --}}
                <button type="button" class="popup-close-circle" data-bs-dismiss="modal" aria-label="Close">
                    <i class="ti ti-x"></i>
                </button>

                {{-- Pulsing Icon Pod --}}
                <div class="popup-icon-pod">
                    <i class="ti ti-mail-heart"></i>
                </div>

                <h3 class="fs-2 fw-bold text-dark mb-2">{{ __('Subscribe to VIP Offers') }}</h3>
                <p class="text-muted fs-4 mb-4">
                    {{ __('Join our exclusive club for gift ideas, special discount vouchers, and festive updates.') }}
                </p>

                <form action="{{ url('subscribe/store/newsletter') }}" method="POST">
                    @csrf
                    <input type="hidden" name="card_id" value="{{ $business_card_details->card_id }}">
                    <div class="mb-3">
                        <input type="email" name="email" class="form-control popup-pill-input w-100"
                            placeholder="{{ __('Enter your email address') }}" required>
                    </div>
                    <button type="submit" class="btn-popup-submit mb-3">
                        <i class="ti ti-send"></i>
                        <span>{{ __('Subscribe Now') }}</span>
                    </button>
                    <small class="text-muted fs-5 d-block">
                        <i class="ti ti-lock me-1"></i>{{ __('We respect your privacy. No spam ever.') }}
                    </small>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            "use strict";
            setTimeout(() => {
                const modalEl = document.getElementById('newsletterModal');
                if (modalEl) {
                    new bootstrap.Modal(modalEl, {
                        keyboard: false
                    }).show();
                }
            }, 3000);
        });
    </script>
@endif

{{-- 2. Information Special Promo Modal --}}
@if (
    !empty($business_card_details->is_info_pop_active) &&
        $business_card_details->is_info_pop_active == 1 &&
        empty(request()->getQueryString()))
    @php
        $infoPop = \App\InformationPop::where('card_id', $business_card_details->card_id)->first();
        $confettiEffect = $infoPop->confetti_effect ?? 0;
    @endphp

    @if ($infoPop)
        <div class="modal fade store-popup-overlay" id="infoPromoModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content store-popup-card text-center">
                    {{-- Circular Close Button --}}
                    <button type="button" class="popup-close-circle" data-bs-dismiss="modal" aria-label="Close">
                        <i class="ti ti-x"></i>
                    </button>

                    {{-- Promotion Media Banner --}}
                    @if (!empty($infoPop->info_pop_image))
                        <div class="popup-promo-img-box">
                            <img src="{{ url($infoPop->info_pop_image) }}"
                                alt="{{ $infoPop->info_pop_title ?? 'Offer' }}" class="popup-promo-img">
                        </div>
                    @else
                        <div class="popup-icon-pod">
                            <i class="ti ti-gift"></i>
                        </div>
                    @endif

                    <h3 class="fs-2 fw-bold text-dark mb-2">{{ __($infoPop->info_pop_title ?? 'Special Announcement') }}
                    </h3>
                    <p class="text-muted fs-4 mb-4">{{ __($infoPop->info_pop_desc ?? '') }}</p>

                    @if (!empty($infoPop->info_pop_button_text))
                        <a href="{{ $infoPop->info_pop_button_url ?? '#' }}" target="_blank" class="btn-popup-promo">
                            <i class="ti ti-sparkles"></i>
                            <span>{{ __($infoPop->info_pop_button_text) }}</span>
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <script>
            document.addEventListener("DOMContentLoaded", function() {
                "use strict";
                setTimeout(() => {
                    const modalEl = document.getElementById('infoPromoModal');
                    if (modalEl) {
                        new bootstrap.Modal(modalEl, {
                            keyboard: false
                        }).show();
                    }
                }, 4500);
            });
        </script>

        {{-- Confetti Particle Trigger if Enabled --}}
        @if ($confettiEffect == 1)
            <script src="{{ asset('js/confetti.browser.min.js') }}"></script>
            <script>
                setTimeout(() => {
                    "use strict";
                    if (typeof confetti === 'function') {
                        confetti({
                            particleCount: window.innerWidth > 768 ? 150 : 80,
                            spread: 100,
                            origin: {
                                y: 0.6
                            }
                        });
                    }
                }, 4800);
            </script>
        @endif
    @endif
@endif
