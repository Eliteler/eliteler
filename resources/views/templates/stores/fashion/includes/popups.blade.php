{{-- Popups Component (Luxury Redesigned Newsletter & Promotion) --}}
<style>
    /* Backdrop & Modal Layering */
    .fashion-modal-backdrop {
        background: rgba(15, 23, 42, 0.75) !important;
        backdrop-filter: blur(4px) !important;
        z-index: 1055 !important;
    }

    .fashion-luxury-modal {
        background-color: #ffffff;
        border-radius: 0px !important;
        border: 1px solid rgba(0, 0, 0, 0.1);
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.35);
        overflow: hidden;
        position: relative;
    }

    /* Minimal Close Button */
    .fashion-popup-close {
        position: absolute;
        top: 16px;
        right: 16px;
        width: 34px;
        height: 34px;
        background-color: #f1f5f9;
        border: none;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #0f172a;
        cursor: pointer;
        z-index: 10;
        transition: all 0.2s ease;
    }

    .fashion-popup-close:hover {
        background-color: #0f172a;
        color: #ffffff;
    }

    /* Luxury Badge */
    .fashion-pop-badge {
        display: inline-block;
        background-color: #0f172a;
        color: #ffffff;
        font-size: 10px;
        font-weight: 800;
        letter-spacing: 1.5px;
        text-transform: uppercase;
        padding: 4px 10px;
        margin-bottom: 12px;
    }

    /* Newsletter Inputs & Buttons */
    .fashion-pop-input {
        height: 48px;
        border-radius: 0px !important;
        border: 1px solid #cbd5e1;
        padding: 0 16px;
        font-size: 14px;
        font-weight: 500;
        transition: border-color 0.2s ease;
    }

    .fashion-pop-input:focus {
        border-color: #0f172a;
        box-shadow: 0 0 0 2px rgba(15, 23, 42, 0.1);
    }

    .fashion-pop-btn {
        height: 48px;
        background-color: #0f172a;
        color: #ffffff;
        border: none;
        border-radius: 0px !important;
        font-size: 13px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        cursor: pointer;
        transition: background-color 0.2s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        text-decoration: none !important;
    }

    .fashion-pop-btn:hover {
        background-color: #1e293b;
        color: #ffffff;
    }

    /* Promo Image Display */
    .fashion-promo-img-wrap {
        width: 110px;
        height: 110px;
        margin: 0 auto 16px auto;
        border: 2px solid #f1f5f9;
        padding: 6px;
        background-color: #ffffff;
    }

    .fashion-promo-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .fashion-pop-title {
        font-family: 'Oswald', sans-serif;
        font-size: 24px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #0f172a;
        margin-bottom: 8px;
    }

    .fashion-pop-desc {
        font-size: 14px;
        color: #64748b;
        line-height: 1.5;
        margin-bottom: 20px;
    }

    .fashion-pop-microtext {
        font-size: 11px;
        color: #94a3b8;
        margin-top: 10px;
        display: block;
    }
</style>

{{-- Newsletter Modal --}}
@if (
    !empty($business_card_details->is_newsletter_pop_active) &&
        $business_card_details->is_newsletter_pop_active == 1 &&
        !request()->cookie('newsletter_' . $business_card_details->card_id))
    <div class="modal fade fashion-modal-backdrop" id="newsletterModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content fashion-luxury-modal p-4 p-md-5 text-center">
                <!-- Dismiss Button -->
                <button type="button" class="fashion-popup-close" data-bs-dismiss="modal" aria-label="Close">
                    <i class="ti ti-x fs-3"></i>
                </button>

                <div>
                    <span class="fashion-pop-badge">{{ __('Exclusive Access') }}</span>
                    <h3 class="fashion-pop-title">{{ __('Subscribe to VIP List') }}</h3>
                    <p class="fashion-pop-desc">
                        {{ __('Get private sales, trend alerts, and secret promo codes delivered directly to your inbox.') }}
                    </p>

                    <form action="{{ url('subscribe/store/newsletter') }}" method="POST">
                        @csrf
                        <input type="hidden" name="card_id" value="{{ $business_card_details->card_id }}">

                        <div class="mb-3">
                            <input type="email" class="form-control fashion-pop-input text-center" name="email"
                                placeholder="{{ __('Enter your email address') }}" required />
                        </div>

                        <button type="submit" class="fashion-pop-btn w-100">
                            <i class="ti ti-mail-fast fs-2"></i>
                            <span>{{ __('Subscribe Now') }}</span>
                        </button>
                    </form>

                    <span class="fashion-pop-microtext">{{ __('No spam. Unsubscribe anytime.') }}</span>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Newsletter Auto Show with Cleanup
        document.addEventListener("DOMContentLoaded", function() {
            setTimeout(() => {
                const el = document.getElementById('newsletterModal');
                if (el && typeof bootstrap !== 'undefined') {
                    const modal = new bootstrap.Modal(el);
                    modal.show();

                    // Cleanup backdrop on hidden
                    el.addEventListener('hidden.bs.modal', function() {
                        document.body.classList.remove('modal-open');
                        document.querySelectorAll('.modal-backdrop').forEach(b => b.remove());
                    });
                }
            }, 3500);
        });
    </script>
@endif

{{-- Information Promo Popup --}}
@if (!empty($business_card_details->is_info_pop_active) && $business_card_details->is_info_pop_active == 1)
    @php
        $information_pop = \App\InformationPop::where('card_id', $business_card_details->card_id)->first();
        $confetti_effect = $information_pop->confetti_effect ?? 0;
    @endphp

    @if ($information_pop)
        <div class="modal fade fashion-modal-backdrop" id="informationModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content fashion-luxury-modal p-4 p-md-5 text-center">
                    <!-- Dismiss Button -->
                    <button type="button" class="fashion-popup-close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="ti ti-x fs-3"></i>
                    </button>

                    @if (!empty($information_pop->info_pop_image))
                        <div class="fashion-promo-img-wrap">
                            <img src="{{ url($information_pop->info_pop_image) }}" class="fashion-promo-img"
                                alt="Special Offer">
                        </div>
                    @endif

                    <span class="fashion-pop-badge">{{ __('Special Announcement') }}</span>
                    <h3 class="fashion-pop-title">{{ $information_pop->info_pop_title ?? '' }}</h3>
                    <p class="fashion-pop-desc">{{ $information_pop->info_pop_desc ?? '' }}</p>

                    @if (!empty($information_pop->info_pop_button_text))
                        <a href="{{ $information_pop->info_pop_button_url ?? '#' }}" class="fashion-pop-btn w-100"
                            target="_blank">
                            <span>{{ $information_pop->info_pop_button_text }}</span>
                            <i class="ti ti-arrow-right fs-2"></i>
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <script>
            // Promo Popup Auto Show
            document.addEventListener("DOMContentLoaded", function() {
                setTimeout(() => {
                    const infoModalEl = document.getElementById('informationModal');
                    if (infoModalEl && typeof bootstrap !== 'undefined') {
                        const modal = new bootstrap.Modal(infoModalEl);
                        modal.show();

                        // Cleanup backdrop on hidden
                        infoModalEl.addEventListener('hidden.bs.modal', function() {
                            document.body.classList.remove('modal-open');
                            document.querySelectorAll('.modal-backdrop').forEach(b => b.remove());
                        });
                    }
                }, 5000);
            });
        </script>

        {{-- Confetti Particle Animation --}}
        @if ($confetti_effect == 1)
            <script src="{{ asset('js/confetti.browser.min.js') }}"></script>
            <script>
                setTimeout(() => {
                    "use strict";
                    if (typeof confetti === 'function') {
                        const confettiCanvas = document.createElement('canvas');
                        confettiCanvas.id = 'confetti-overlay';
                        confettiCanvas.style.cssText =
                            'position:fixed;top:0;left:0;width:100%;height:100%;pointer-events:none;z-index:1065;';
                        document.body.appendChild(confettiCanvas);

                        const myConfetti = confetti.create(confettiCanvas, {
                            resize: true,
                            useWorker: true
                        });
                        myConfetti({
                            particleCount: window.innerWidth > 768 ? 160 : 80,
                            spread: 100,
                            origin: {
                                x: 0.5,
                                y: 0.7
                            }
                        });

                        setTimeout(() => {
                            confettiCanvas.remove();
                        }, 6000);
                    }
                }, 5200);
            </script>
        @endif
    @endif
@endif
