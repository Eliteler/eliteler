<style>
    /* Redesigned Luxury Popups Styling */
    .popup-modal-card {
        border-radius: 24px !important;
        border: 1px solid #f1f5f9 !important;
        overflow: hidden !important;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25) !important;
        pointer-events: auto !important;
        z-index: 10000005 !important;
        position: relative !important;
    }

    .popup-icon-badge {
        width: 72px;
        height: 72px;
        border-radius: 50%;
        background-color: #fff1f4;
        color: #e91e63;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1rem auto;
        border: 4px solid #ffffff;
        box-shadow: 0 4px 15px rgba(233, 30, 99, 0.12);
    }

    .popup-logo-img {
        max-height: 90px;
        max-width: 140px;
        object-fit: contain;
        border-radius: 12px;
        margin-bottom: 1rem;
    }

    .popup-btn-primary {
        height: 46px !important;
        border-radius: 12px !important;
        background-color: #e91e63 !important;
        color: #ffffff !important;
        font-weight: 700 !important;
        font-size: 14px !important;
        box-shadow: 0 4px 15px rgba(233, 30, 99, 0.3) !important;
        transition: all 0.25s ease !important;
        border: none !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 6px !important;
        width: 100% !important;
        text-decoration: none !important;
        cursor: pointer !important;
        pointer-events: auto !important;
    }

    .popup-btn-primary:hover {
        background-color: #d81b60 !important;
        transform: translateY(-1px);
        color: #ffffff !important;
    }

    .popup-input-field {
        height: 46px !important;
        border-radius: 12px !important;
        font-size: 13px !important;
        border: 1.5px solid #e2e8f0 !important;
        padding-left: 42px !important;
        pointer-events: auto !important;
    }

    /* Special Offer Pill Badge - Pixel Perfect Center Alignment */
    .special-offer-pill {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 6px !important;
        line-height: 1 !important;
        padding: 6px 14px !important;
        font-size: 12px !important;
        font-weight: 700 !important;
        border-radius: 9999px !important;
        background-color: #fff1f4 !important;
        color: #e91e63 !important;
        border: 1px solid #fbcfe8 !important;
        box-shadow: 0 1px 3px rgba(233, 30, 99, 0.08) !important;
    }

    .special-offer-pill i {
        font-size: 14px !important;
        line-height: 1 !important;
        display: inline-flex !important;
        align-items: center !important;
    }

    .special-offer-pill span {
        line-height: 1 !important;
        display: inline-block !important;
    }

    /* Modal Clickability & Z-Index */
    .modal {
        z-index: 10000000 !important;
        pointer-events: auto !important;
    }

    .modal-dialog {
        z-index: 10000002 !important;
        position: relative !important;
        pointer-events: auto !important;
    }
</style>

{{-- 1. Information / Offer Popup --}}
@if ($business_card_details != null)
    @if (!empty($business_card_details->is_info_pop_active) && $business_card_details->is_info_pop_active == 1)
        @php
            $information_pop = \App\InformationPop::where('card_id', $business_card_details->card_id)->first();
            $confetti_effect = $information_pop->confetti_effect ?? 0;
            $img = $information_pop->info_pop_image ?? '';
            $title = $information_pop->info_pop_title ?? '';
            $desc = $information_pop->info_pop_desc ?? '';
            $button_text = $information_pop->info_pop_button_text ?? '';
            $button_url = $information_pop->info_pop_button_url ?? '#';
        @endphp

        <div class="modal fade" id="informationModal" tabindex="-1" data-bs-focus="false" aria-hidden="true"
            onclick="if(event.target === this) closeModalDirectly('informationModal');">
            <div class="modal-dialog modal-md modal-dialog-centered" role="document">
                <div class="modal-content popup-modal-card bg-white position-relative p-4 p-md-5 text-center">

                    <!-- Direct Clickable Close Button -->
                    <button type="button" class="btn-close position-absolute top-0 end-0 m-3 shadow-none"
                        onclick="closeModalDirectly('informationModal')" aria-label="Close"></button>

                    <!-- Special Offer Badge -->
                    <div class="mb-3 text-center">
                        <span class="special-offer-pill">
                            <i class="ti ti-sparkles"></i>
                            <span>{{ __('Special Offer') }}</span>
                        </span>
                    </div>

                    <!-- Brand Image -->
                    @if (!empty($img))
                        <div class="d-flex justify-content-center">
                            <img src="{{ url($img) }}" alt="{{ $title }}" class="img-fluid popup-logo-img"
                                onerror="this.onerror=null; this.src='{{ asset('img/templates/all-categories.png') }}';" />
                        </div>
                    @endif

                    <!-- Offer Headings -->
                    <h2 class="fs-1 fw-bold text-dark mb-1">{{ __($title) }}</h2>
                    <p class="text-secondary fs-4 max-w-sm mx-auto mb-4">{{ __($desc) }}</p>

                    <!-- Action Link -->
                    <a href="{{ $button_url }}" target="_blank" onclick="closeModalDirectly('informationModal')"
                        class="popup-btn-primary">
                        <span>{{ __($button_text) }}</span>
                        <i class="ti ti-arrow-right fs-4"></i>
                    </a>

                </div>
            </div>
        </div>

        {{-- Confetti Library --}}
        @if ($confetti_effect == 1)
            <script src="{{ asset('js/confetti.browser.min.js') }}"></script>
        @endif
    @endif
@endif

{{-- 2. Newsletter Popup --}}
@if ($business_card_details != null)
    @if (
        !empty($business_card_details->is_newsletter_pop_active) &&
            $business_card_details->is_newsletter_pop_active == 1 &&
            !request()->cookie('newsletter_' . $business_card_details->card_id))
        <div class="modal fade" id="newsletterModal" tabindex="-1" data-bs-focus="false" aria-hidden="true"
            onclick="if(event.target === this) closeModalDirectly('newsletterModal');">
            <div class="modal-dialog modal-md modal-dialog-centered" role="document">
                <div class="modal-content popup-modal-card bg-white position-relative p-4 p-md-5 text-center">

                    <!-- Direct Clickable Close Button -->
                    <button type="button" class="btn-close position-absolute top-0 end-0 m-3 shadow-none"
                        onclick="closeModalDirectly('newsletterModal')" aria-label="Close"></button>

                    <!-- Mail Icon Badge -->
                    <div class="popup-icon-badge">
                        <i class="ti ti-mail-opened fs-1"></i>
                    </div>

                    <h2 class="fs-1 fw-bold text-dark mb-1">{{ __('Subscribe to our newsletter') }}</h2>
                    <p class="text-secondary fs-4 max-w-sm mx-auto mb-4">
                        {{ __('Get exclusive deals, new arrivals, and special promotions delivered right to your inbox.') }}
                    </p>

                    <!-- Form -->
                    <form action="{{ url('subscribe/store/newsletter') }}" method="post">
                        @csrf
                        <input type="hidden" id="card_id" name="card_id"
                            value="{{ $business_card_details->card_id }}">

                        <div class="mb-3 position-relative text-start">
                            <i class="ti ti-mail text-muted fs-3 position-absolute top-50 translate-middle-y ps-3"></i>
                            <input type="email" class="form-control popup-input-field w-100" id="newsletter_email"
                                placeholder="{{ __('Enter your email address') }}" name="email" required />
                        </div>

                        <button type="submit" class="popup-btn-primary">
                            <i class="ti ti-send"></i>
                            <span>{{ __('Subscribe Now') }}</span>
                        </button>
                    </form>

                </div>
            </div>
        </div>
    @endif
@endif

{{-- 3. Click-Guaranteed Modal Controller --}}
<script>
    // Direct modal dismiss function (Bypasses Bootstrap event locks)
    window.closeModalDirectly = function(modalId) {
        const el = document.getElementById(modalId);
        if (!el) return;

        const instance = bootstrap.Modal.getInstance(el);
        if (instance) {
            instance.hide();
        } else {
            el.classList.remove('show');
            el.style.display = 'none';
        }

        // Clean up backdrops & unlock scroll
        document.querySelectorAll('.modal-backdrop').forEach(b => b.remove());
        document.body.classList.remove('modal-open');
        document.body.style.overflow = '';
        document.body.style.paddingRight = '';

        // Open next modal in queue if closing Information modal
        if (modalId === 'informationModal') {
            const newsEl = document.getElementById('newsletterModal');
            if (newsEl) {
                setTimeout(function() {
                    const newsInstance = new bootstrap.Modal(newsEl, {
                        backdrop: true,
                        keyboard: true,
                        focus: false
                    });
                    newsInstance.show();
                }, 500);
            }
        }
    };

    document.addEventListener("DOMContentLoaded", function() {
        "use strict";

        const infoModalEl = document.getElementById('informationModal');
        const newsModalEl = document.getElementById('newsletterModal');

        // Extract outside wrappers directly to <body>
        if (infoModalEl) document.body.appendChild(infoModalEl);
        if (newsModalEl) document.body.appendChild(newsModalEl);

        // Open Offer Popup after 1.5 seconds with focus: false to prevent click lock
        if (infoModalEl) {
            setTimeout(function() {
                const infoInstance = new bootstrap.Modal(infoModalEl, {
                    backdrop: true,
                    keyboard: true,
                    focus: false // CRITICAL: Disables focus trap that froze clicks
                });
                infoInstance.show();

                if (typeof confetti === 'function') {
                    confetti({
                        particleCount: 120,
                        spread: 80,
                        origin: {
                            y: 0.6
                        }
                    });
                }
            }, 1500);
        } else if (newsModalEl) {
            setTimeout(function() {
                const newsInstance = new bootstrap.Modal(newsModalEl, {
                    backdrop: true,
                    keyboard: true,
                    focus: false
                });
                newsInstance.show();
            }, 2000);
        }
    });
</script>
