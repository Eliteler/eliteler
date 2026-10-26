{{-- Ultra-Luxury Popups Component with Guaranteed Direct Blur & Dark Backdrop --}}
@php
    $cardId = $business_card_details->card_id ?? '';
    $hasNewsletter =
        !empty($business_card_details->is_newsletter_pop_active) &&
        $business_card_details->is_newsletter_pop_active == 1 &&
        !request()->cookie('newsletter_' . $cardId);
    $hasInfoPop = !empty($business_card_details->is_info_pop_active) && $business_card_details->is_info_pop_active == 1;

    $infoPopup = $hasInfoPop ? \App\InformationPop::where('card_id', $cardId)->first() : null;
@endphp

{{-- 1. Newsletter Popup --}}
@if ($hasNewsletter)
    <div class="modal fade luxury-popup-modal" id="newsletterModal" tabindex="-1" aria-hidden="true"
        data-bs-backdrop="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content luxury-popup-card">
                <!-- Floating Circular Close Button -->
                <button type="button" class="popup-floating-close-btn" data-bs-dismiss="modal" aria-label="Close">
                    <i class="ti ti-x"></i>
                </button>

                <div class="modal-body p-4 p-md-5 text-center">
                    <!-- Glowing Icon Pod -->
                    <div class="popup-glowing-pod">
                        <i class="ti ti-mail-heart popup-core-icon"></i>
                    </div>

                    <!-- VIP Privilege Pill Badge -->
                    <div class="popup-badge-wrap">
                        <span class="popup-vip-pill">
                            <i class="ti ti-sparkles"></i> {{ __('VIP PRIVILEGE') }}
                        </span>
                    </div>

                    <!-- Headings -->
                    <h3 class="popup-hero-title">{{ __('Subscribe to our newsletter') }}</h3>
                    <p class="popup-hero-desc">
                        {{ __('Get exclusive deals, new collection arrivals, and VIP privileges right in your inbox.') }}
                    </p>

                    <!-- Subscription Form -->
                    <form action="{{ url('subscribe/store/newsletter') }}" method="POST" class="mt-4">
                        @csrf
                        <input type="hidden" name="card_id" value="{{ $cardId }}">

                        <div class="newsletter-input-group">
                            <div class="newsletter-field-box">
                                <i class="ti ti-mail newsletter-field-icon"></i>
                                <input type="email" name="email" class="newsletter-luxury-input"
                                    placeholder="{{ __('Enter your email address') }}" required />
                            </div>
                            <button type="submit" class="btn btn-gold newsletter-luxury-submit-btn">
                                <span>{{ __('Subscribe Now') }}</span>
                                <i class="ti ti-arrow-narrow-right"></i>
                            </button>
                        </div>
                    </form>

                    <!-- Trust Note -->
                    <p class="popup-trust-note">
                        <i class="ti ti-lock"></i> {{ __('We respect your privacy. No spam ever.') }}
                    </p>
                </div>
            </div>
        </div>
    </div>
@endif

{{-- 2. VIP Information / Announcement Promo Popup --}}
@if ($hasInfoPop && $infoPopup)
    <div class="modal fade luxury-popup-modal" id="infoPromoModal" tabindex="-1" aria-hidden="true"
        data-bs-backdrop="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content luxury-popup-card">
                <!-- Floating Circular Close Button -->
                <button type="button" class="popup-floating-close-btn" data-bs-dismiss="modal" aria-label="Close">
                    <i class="ti ti-x"></i>
                </button>

                <div class="modal-body p-4 p-md-5 text-center">
                    @if (!empty($infoPopup->info_pop_image))
                        <div class="info-banner-container mb-4">
                            <img src="{{ url($infoPopup->info_pop_image) }}" alt="Announcement"
                                class="info-banner-img" />
                        </div>
                    @else
                        <div class="popup-glowing-pod">
                            <i class="ti ti-bell-ringing popup-core-icon"></i>
                        </div>
                    @endif

                    <div class="popup-badge-wrap">
                        <span class="popup-vip-pill">
                            <i class="ti ti-sparkles"></i> {{ __('SPECIAL ANNOUNCEMENT') }}
                        </span>
                    </div>

                    <h3 class="popup-hero-title">{{ __($infoPopup->info_pop_title ?? '') }}</h3>
                    <p class="popup-hero-desc">{{ __($infoPopup->info_pop_desc ?? '') }}</p>

                    @if (!empty($infoPopup->info_pop_button_text))
                        <div class="mt-4">
                            <a href="{{ $infoPopup->info_pop_button_url ?? '#' }}" target="_blank"
                                class="btn btn-gold w-100 popup-cta-pill-btn">
                                <span>{{ __($infoPopup->info_pop_button_text) }}</span>
                                <i class="ti ti-arrow-up-right"></i>
                            </a>
                        </div>
                    @endif

                    <button type="button" class="btn-dismiss-text" data-bs-dismiss="modal">
                        {{ __('No thanks, continue browsing') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    @if (!empty($infoPopup->confetti_effect) && $infoPopup->confetti_effect == 1)
        <script src="{{ asset('js/confetti.browser.min.js') }}"></script>
    @endif
@endif

<style>
    /* Direct Dark Backdrop on Popups */
    .luxury-popup-modal {
        background-color: rgba(15, 23, 42, 0.75) !important;
        z-index: 1060 !important;
    }

    /* Force Blur on the entire store behind popups */
    body.popup-modal-active .store-main-wrapper,
    body.popup-modal-active .luxury-bottom-bar,
    body.popup-modal-active .luxury-wa-floater-root,
    body:has(#newsletterModal.show) .store-main-wrapper,
    body:has(#newsletterModal.show) .luxury-bottom-bar,
    body:has(#newsletterModal.show) .luxury-wa-floater-root,
    body:has(#infoPromoModal.show) .store-main-wrapper,
    body:has(#infoPromoModal.show) .luxury-bottom-bar,
    body:has(#infoPromoModal.show) .luxury-wa-floater-root {
        filter: blur(6px) !important;
        -webkit-filter: blur(6px) !important;
        transition: filter 0.25s ease-in-out;
        pointer-events: none;
    }

    /* Popup Card: 100% Solid White, Sharp, and Elevated */
    .luxury-popup-card {
        border-radius: 28px !important;
        border: 1.5px solid rgba(212, 175, 55, 0.35) !important;
        box-shadow: 0 25px 65px rgba(0, 0, 0, 0.6) !important;
        background-color: #ffffff !important;
        position: relative;
        overflow: hidden;
        z-index: 1070 !important;
    }

    /* Floating Circular Close Button */
    .popup-floating-close-btn {
        position: absolute;
        top: 16px;
        right: 16px;
        width: 36px;
        height: 36px;
        border-radius: 50% !important;
        background: #fbf7ee;
        border: 1px solid rgba(212, 175, 55, 0.35);
        color: #55411d;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-size: 16px;
        z-index: 10;
        transition: all 0.25s ease;
    }

    .popup-floating-close-btn:hover {
        background: #d4af37;
        color: #ffffff;
        border-color: #d4af37;
        transform: rotate(90deg);
    }

    /* Glowing Icon Pod */
    .popup-glowing-pod {
        width: 76px;
        height: 76px;
        border-radius: 50% !important;
        background: linear-gradient(135deg, #fcf8ee 0%, #f6edd9 100%);
        border: 2px solid rgba(212, 175, 55, 0.45);
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 16px auto;
        box-shadow: 0 8px 24px rgba(212, 175, 55, 0.2);
    }

    .popup-core-icon {
        font-size: 34px;
        color: #b89028;
    }

    /* VIP Tag Pill */
    .popup-badge-wrap {
        margin-bottom: 12px;
    }

    .popup-vip-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: #fbf7ee;
        color: #8c733e;
        border: 1px solid rgba(212, 175, 55, 0.4);
        padding: 5px 16px;
        border-radius: 50px !important;
        font-size: 11px;
        font-weight: 800;
        letter-spacing: 1px;
        text-transform: uppercase;
    }

    /* Typography */
    .popup-hero-title {
        font-family: 'Gloock', serif;
        font-size: 24px;
        color: #2b2316;
        margin-bottom: 10px;
        line-height: 1.3;
    }

    .popup-hero-desc {
        font-size: 14px;
        color: #716248;
        line-height: 1.6;
        max-width: 380px;
        margin: 0 auto;
    }

    /* Newsletter Form Group */
    .newsletter-input-group {
        display: flex;
        flex-direction: column;
        gap: 12px;
        max-width: 400px;
        margin: 0 auto;
    }

    .newsletter-field-box {
        position: relative;
        width: 100%;
    }

    .newsletter-field-icon {
        position: absolute;
        left: 18px;
        top: 50%;
        transform: translateY(-50%);
        color: #b89028;
        font-size: 18px;
    }

    .newsletter-luxury-input {
        width: 100%;
        height: 50px;
        border-radius: 50px !important;
        border: 1.5px solid rgba(212, 175, 55, 0.4) !important;
        padding: 0 20px 0 46px;
        font-size: 14px;
        background: #ffffff;
        color: #2b2316;
        outline: none;
        transition: all 0.25s ease;
    }

    .newsletter-luxury-input:focus {
        border-color: #d4af37 !important;
        box-shadow: 0 0 0 4px rgba(212, 175, 55, 0.18) !important;
    }

    .newsletter-luxury-submit-btn,
    .popup-cta-pill-btn {
        height: 50px;
        border-radius: 50px !important;
        background: #d4af37 !important;
        color: #ffffff !important;
        font-weight: 700;
        font-size: 14px;
        border: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        box-shadow: 0 6px 20px rgba(212, 175, 55, 0.35);
        transition: all 0.25s ease;
        cursor: pointer;
    }

    .newsletter-luxury-submit-btn:hover,
    .popup-cta-pill-btn:hover {
        background: #b89028 !important;
        box-shadow: 0 8px 25px rgba(212, 175, 55, 0.45);
        transform: translateY(-2px);
    }

    /* Trust & Dismiss Notes */
    .popup-trust-note {
        font-size: 11px;
        color: #9c8e71;
        margin: 14px 0 0 0;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
    }

    .btn-dismiss-text {
        background: transparent;
        border: none;
        color: #9c8e71;
        font-size: 12px;
        font-weight: 600;
        margin-top: 14px;
        cursor: pointer;
        transition: color 0.2s;
    }

    .btn-dismiss-text:hover {
        color: #2b2316;
        text-decoration: underline !important;
    }

    /* Banner Box for Announcement */
    .info-banner-container {
        border-radius: 20px !important;
        overflow: hidden;
        border: 1px solid rgba(212, 175, 55, 0.25);
        max-height: 220px;
    }

    .info-banner-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
</style>

{{-- Intelligent Sequential Popup Controller Script with Blur Bridge --}}
<script>
    document.addEventListener("DOMContentLoaded", function() {
        "use strict";

        const newsletterEl = document.getElementById('newsletterModal');
        const infoPromoEl = document.getElementById('infoPromoModal');

        // Helper to bind guaranteed blur to any popup
        function bindPopupBlur(modalEl) {
            if (!modalEl) return;

            modalEl.addEventListener('show.bs.modal', function() {
                document.body.classList.add('modal-open', 'popup-modal-active');
            });
            modalEl.addEventListener('shown.bs.modal', function() {
                document.body.classList.add('modal-open', 'popup-modal-active');
            });
            modalEl.addEventListener('hidden.bs.modal', function() {
                document.body.classList.remove('popup-modal-active');
            });

            // MutationObserver fallback to catch any class/display change instantly
            const observer = new MutationObserver(function() {
                const isShown = modalEl.classList.contains('show') || modalEl.style.display === 'block';
                if (isShown) {
                    document.body.classList.add('modal-open', 'popup-modal-active');
                } else {
                    document.body.classList.remove('popup-modal-active');
                }
            });
            observer.observe(modalEl, {
                attributes: true,
                attributeFilter: ['class', 'style']
            });

            // Close when clicking directly on dark background
            modalEl.addEventListener('click', function(e) {
                if (e.target === modalEl) {
                    const instance = bootstrap.Modal.getInstance(modalEl);
                    if (instance) instance.hide();
                }
            });
        }

        bindPopupBlur(newsletterEl);
        bindPopupBlur(infoPromoEl);

        function launchInfoPromoModal() {
            if (!infoPromoEl) return;
            const promoModal = bootstrap.Modal.getOrCreateInstance(infoPromoEl, {
                keyboard: false,
                backdrop: true
            });
            promoModal.show();

            @if (!empty($infoPopup->confetti_effect) && $infoPopup->confetti_effect == 1)
                setTimeout(function() {
                    if (typeof confetti === 'function') {
                        confetti({
                            particleCount: 140,
                            spread: 110,
                            origin: {
                                y: 0.6
                            }
                        });
                    }
                }, 300);
            @endif
        }

        if (newsletterEl) {
            setTimeout(function() {
                const newsModal = bootstrap.Modal.getOrCreateInstance(newsletterEl, {
                    keyboard: false,
                    backdrop: true
                });
                newsModal.show();

                newsletterEl.addEventListener('hidden.bs.modal', function() {
                    setTimeout(launchInfoPromoModal, 1200);
                }, {
                    once: true
                });
            }, 3000);
        } else if (infoPromoEl) {
            setTimeout(launchInfoPromoModal, 3500);
        }
    });
</script>
