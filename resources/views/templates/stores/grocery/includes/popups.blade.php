{{-- Luxury Newsletter & VIP Promotion Popups with Dark Blur Backdrop --}}
@if ($business_card_details != null)
    {{-- Newsletter Popup --}}
    @if (
        !empty($business_card_details->is_newsletter_pop_active) &&
            $business_card_details->is_newsletter_pop_active == 1 &&
            !request()->cookie('newsletter_' . $business_card_details->card_id) &&
            empty(request()->getQueryString()))
        <div class="modal fade store-blur-modal" id="newsletterModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-md modal-dialog-centered">
                <div class="modal-content store-popup-modal-content">
                    <button type="button" class="btn store-popup-close-btn" data-bs-dismiss="modal">
                        <i class="ti ti-x"></i>
                    </button>
                    <div class="store-popup-body text-center">
                        <div class="store-popup-badge-icon">
                            <i class="ti ti-mail-heart"></i>
                        </div>
                        <h3 class="store-popup-heading">{{ __('Subscribe to our Newsletter') }}</h3>
                        <p class="store-popup-desc">
                            {{ __('Receive fresh updates, seasonal grocery discounts, and exclusive offers straight to your inbox.') }}
                        </p>

                        <form action="{{ url('subscribe/store/newsletter') }}" method="POST" class="store-popup-form">
                            @csrf
                            <input type="hidden" name="card_id" value="{{ $business_card_details->card_id }}">
                            <div class="store-popup-input-row">
                                <input type="email" name="email" class="form-control store-popup-input"
                                    placeholder="{{ __('Your email address') }}" required>
                                <button type="submit" class="btn store-popup-btn">{{ __('Subscribe') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <script>
            // Open Newsletter modal after 3.5 seconds
            document.addEventListener("DOMContentLoaded", function() {
                setTimeout(() => {
                    const modalEl = document.getElementById('newsletterModal');
                    if (modalEl) {
                        const modal = new bootstrap.Modal(modalEl);
                        modal.show();
                    }
                }, 3500);
            });
        </script>
    @endif

    {{-- VIP Information / Promo Popup --}}
    @if (
        !empty($business_card_details->is_info_pop_active) &&
            $business_card_details->is_info_pop_active == 1 &&
            empty(request()->getQueryString()))
        @php
            $infoPop = \App\InformationPop::where('card_id', $business_card_details->card_id)->first();
            $confetti = $infoPop->confetti_effect ?? 0;
        @endphp

        @if ($infoPop)
            <div class="modal fade store-blur-modal" id="infoPromoModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-md modal-dialog-centered">
                    <div class="modal-content store-popup-modal-content">
                        <button type="button" class="btn store-popup-close-btn" data-bs-dismiss="modal">
                            <i class="ti ti-x"></i>
                        </button>
                        <div class="store-popup-body text-center">
                            @if (!empty($infoPop->info_pop_image))
                                <div class="store-promo-img-wrapper">
                                    <img src="{{ url($infoPop->info_pop_image) }}" alt="{{ $infoPop->info_pop_title }}"
                                        class="store-promo-img">
                                </div>
                            @endif
                            <h3 class="store-popup-heading">{{ __($infoPop->info_pop_title ?? '') }}</h3>
                            <p class="store-popup-desc">{{ __($infoPop->info_pop_desc ?? '') }}</p>
                            @if (!empty($infoPop->info_pop_button_text))
                                <a href="{{ $infoPop->info_pop_button_url ?? '#' }}" target="_blank"
                                    class="btn store-popup-btn w-100">
                                    {{ __($infoPop->info_pop_button_text) }}
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            @if ($confetti == 1)
                <script src="{{ asset('js/confetti.browser.min.js') }}"></script>
            @endif

            <script>
                // Show VIP promotion modal
                document.addEventListener("DOMContentLoaded", function() {
                    setTimeout(() => {
                        const infoEl = document.getElementById('infoPromoModal');
                        if (infoEl) {
                            const modal = new bootstrap.Modal(infoEl);
                            modal.show();

                            @if ($confetti == 1)
                                if (typeof confetti === 'function') {
                                    confetti({
                                        particleCount: 120,
                                        spread: 80,
                                        origin: {
                                            y: 0.6
                                        }
                                    });
                                }
                            @endif
                        }
                    }, 5000);
                });
            </script>
        @endif
    @endif
@endif

<style>
    /* Dark Blur Modal Backdrop Engine */
    .modal-backdrop.show {
        background-color: rgba(15, 23, 42, 0.82) !important;
        backdrop-filter: blur(6px) !important;
        -webkit-backdrop-filter: blur(6px) !important;
        opacity: 1 !important;
    }

    /* Popups Common Luxury Styles */
    .store-popup-modal-content {
        border-radius: 28px !important;
        border: 1px solid #c8e6c9 !important;
        overflow: hidden;
        position: relative;
        padding: 30px 24px;
        background: #ffffff;
        box-shadow: 0 20px 45px rgba(0, 0, 0, 0.35) !important;
    }

    .store-popup-close-btn {
        position: absolute;
        top: 14px;
        right: 14px;
        width: 32px;
        height: 32px;
        border-radius: 50% !important;
        background-color: #f5f5f5 !important;
        color: #424242 !important;
        border: none !important;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0 !important;
    }

    [dir="rtl"] .store-popup-close-btn {
        right: auto;
        left: 14px;
    }

    .store-popup-badge-icon {
        width: 60px;
        height: 60px;
        border-radius: 50% !important;
        background-color: #e8f5e9;
        color: #2e7d32;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 28px;
        margin: 0 auto 16px auto;
    }

    .store-promo-img-wrapper {
        width: 100%;
        max-height: 180px;
        border-radius: 18px !important;
        overflow: hidden;
        margin-bottom: 16px;
    }

    .store-promo-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .store-popup-heading {
        font-size: 20px;
        font-weight: 700;
        color: #1b5e20;
        margin-bottom: 8px;
    }

    .store-popup-desc {
        font-size: 13px;
        color: #616161;
        line-height: 1.5;
        margin-bottom: 20px;
    }

    .store-popup-input-row {
        display: flex;
        gap: 8px;
    }

    .store-popup-input {
        height: 46px;
        border-radius: 50px !important;
        border: 1px solid #c8e6c9 !important;
        padding: 0 20px;
        font-size: 13px;
    }

    .store-popup-btn {
        height: 46px;
        border-radius: 50px !important;
        background-color: #2e7d32 !important;
        color: #ffffff !important;
        border: none !important;
        padding: 0 24px !important;
        font-weight: 600;
        font-size: 14px;
        white-space: nowrap;
    }

    .store-popup-btn:hover {
        background-color: #1b5e20 !important;
    }
</style>
