<style>
    /* 1. Global Dark Blur Backdrop */
    .modal-backdrop,
    .modal-backdrop.show {
        background-color: rgba(15, 23, 42, 0.8) !important;
        opacity: 1 !important;
        backdrop-filter: blur(5px) !important;
        -webkit-backdrop-filter: blur(5px) !important;
        z-index: 1050 !important;
    }

    #informationModal,
    #newsletterModal {
        z-index: 1055 !important;
    }

    /* 2. Information Modal Luxury Card */
    .info-modal-dialog {
        max-width: 480px !important;
        margin: 1.5rem auto !important;
    }

    .info-modal-card {
        background: linear-gradient(180deg, #fdfbf8 0%, #ffffff 100%) !important;
        border-radius: 28px !important;
        border: 1px solid rgba(204, 164, 122, 0.3) !important;
        box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.5) !important;
        padding: 36px 28px 30px !important;
        text-align: center !important;
        position: relative !important;
        overflow: hidden !important;
    }

    /* Floating Close Button */
    .info-close-trigger {
        position: absolute !important;
        top: 16px !important;
        right: 16px !important;
        width: 36px !important;
        height: 36px !important;
        border-radius: 50% !important;
        background-color: #f1ede4 !important;
        color: #1e293b !important;
        border: none !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        cursor: pointer !important;
        transition: transform 0.2s ease, background-color 0.2s ease !important;
        z-index: 15 !important;
    }

    .info-close-trigger:hover {
        background-color: #e2dcd0 !important;
        transform: rotate(90deg);
    }

    /* Top Special Offer Tag */
    .info-promo-badge {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 6px !important;
        padding: 6px 18px !important;
        font-size: 11px !important;
        font-weight: 800 !important;
        letter-spacing: 1px !important;
        text-transform: uppercase !important;
        border-radius: 9999px !important;
        background-color: #f5ece1 !important;
        color: #cca47a !important;
        border: 1px solid rgba(204, 164, 122, 0.4) !important;
        margin-bottom: 20px !important;
    }

    /* Showcase Image Container */
    .info-image-stage {
        width: 100% !important;
        max-height: 180px !important;
        border-radius: 18px !important;
        overflow: hidden !important;
        background-color: #f8fafc !important;
        border: 1px solid #ede8df !important;
        margin-bottom: 20px !important;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.04) !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
    }

    .info-image-stage img {
        width: 100% !important;
        height: 100% !important;
        object-fit: cover !important;
        transition: transform 0.35s ease !important;
    }

    .info-modal-card:hover .info-image-stage img {
        transform: scale(1.03);
    }

    /* Modal Typography */
    .info-modal-title {
        font-family: 'DM Serif Display', serif !important;
        font-size: 26px !important;
        color: #1e293b !important;
        line-height: 1.2 !important;
        margin-bottom: 8px !important;
    }

    .info-modal-desc {
        font-size: 14px !important;
        color: #64748b !important;
        line-height: 1.5 !important;
        margin-bottom: 24px !important;
        max-width: 90% !important;
        margin-left: auto !important;
        margin-right: auto !important;
    }

    /* True 9999px Pill Action Button */
    .info-cta-btn {
        height: 50px !important;
        border-radius: 9999px !important;
        background-color: #1f4b48 !important;
        color: #ffffff !important;
        font-weight: 700 !important;
        font-size: 15px !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 8px !important;
        width: 100% !important;
        border: none !important;
        box-shadow: 0 6px 20px rgba(31, 75, 72, 0.3) !important;
        transition: all 0.25s ease !important;
        text-decoration: none !important;
        cursor: pointer !important;
    }

    .info-cta-btn:hover {
        background-color: #163836 !important;
        color: #ffffff !important;
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(31, 75, 72, 0.4) !important;
    }

    /* Secondary Dismiss Link */
    .info-dismiss-link {
        display: inline-block !important;
        margin-top: 14px !important;
        font-size: 13px !important;
        font-weight: 600 !important;
        color: #94a3b8 !important;
        cursor: pointer !important;
        transition: color 0.2s ease !important;
    }

    .info-dismiss-link:hover {
        color: #1f4b48 !important;
    }

    /* Newsletter Popup Styling */
    .newsletter-modal-card {
        border-radius: 28px !important;
        border: none !important;
        overflow: hidden !important;
        background-color: #ffffff !important;
        box-shadow: 0 25px 60px rgba(0, 0, 0, 0.45) !important;
        padding: 36px 24px !important;
        text-align: center !important;
        position: relative !important;
    }

    .newsletter-circle-icon {
        width: 72px !important;
        height: 72px !important;
        border-radius: 50% !important;
        background-color: #f5ece1 !important;
        color: #cca47a !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        margin: 0 auto 16px auto !important;
    }

    .newsletter-pill-input {
        height: 48px !important;
        border-radius: 9999px !important;
        border: 1.5px solid #cbd5e1 !important;
        padding-left: 46px !important;
        padding-right: 20px !important;
        font-size: 14px !important;
        outline: none !important;
        width: 100% !important;
    }

    .newsletter-pill-input:focus {
        border-color: #1f4b48 !important;
        box-shadow: 0 0 0 3px rgba(31, 75, 72, 0.15) !important;
    }
</style>

@php
    $svgFallback =
        'data:image/svg+xml;charset=UTF-8,%3Csvg%20width%3D%22400%22%20height%3D%22200%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%3E%3Crect%20width%3D%22100%25%22%20height%3D%22100%25%22%20fill%3D%22%23f8fafc%22%2F%3E%3Ctext%20x%3D%2250%25%22%20y%3D%2250%25%22%20fill%3D%22%23cca47a%22%20font-family%3D%22sans-serif%22%20font-size%3D%2214%22%20font-weight%3D%22bold%22%20text-anchor%3D%22middle%22%20dy%3D%22.3em%22%3EEXCLUSIVE%20PROMO%3C%2Ftext%3E%3C%2Fsvg%3E';
@endphp

{{-- 1. Redesigned Luxury Information Modal --}}
@if ($business_card_details != null)
    @if (
        !empty($business_card_details->is_info_pop_active) &&
            $business_card_details->is_info_pop_active == 1 &&
            empty(request()->getQueryString()))
        @php
            $information_pop = \App\InformationPop::where('card_id', $business_card_details->card_id)->first();
            $confetti_effect = $information_pop->confetti_effect ?? 0;
            $img = $information_pop->info_pop_image ?? '';
            $title = $information_pop->info_pop_title ?? 'Exclusive Collection';
            $desc =
                $information_pop->info_pop_desc ??
                'Discover our curated furniture pieces designed for timeless spaces.';
            $button_text = $information_pop->info_pop_button_text ?? 'Shop Now';
            $button_url = $information_pop->info_pop_button_url ?? '#';
        @endphp

        <div class="modal fade" id="informationModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
            <div class="modal-dialog modal-dialog-centered info-modal-dialog" role="document">
                <div class="modal-content info-modal-card">

                    {{-- Floating Close Trigger --}}
                    <button type="button" class="info-close-trigger" onclick="closeModalSequential('informationModal')"
                        aria-label="Close">
                        <i class="ti ti-x fs-2"></i>
                    </button>

                    {{-- Top Promo Badge --}}
                    <div class="text-center">
                        <span class="info-promo-badge">
                            <i class="ti ti-sparkles fs-3"></i>
                            <span>{{ __('Special Offer') }}</span>
                        </span>
                    </div>

                    {{-- Promo Image Showcase Stage --}}
                    @if (!empty($img))
                        <div class="info-image-stage">
                            <img src="{{ url($img) }}" alt="{{ $title }}"
                                onerror="this.onerror=null; this.src='{{ $svgFallback }}';" />
                        </div>
                    @endif

                    {{-- Title & Description --}}
                    <h2 class="info-modal-title">{{ __($title) }}</h2>
                    <p class="info-modal-desc">{{ __($desc) }}</p>

                    {{-- Primary Action CTA --}}
                    <a href="{{ $button_url }}" target="_blank" onclick="closeModalSequential('informationModal')"
                        class="info-cta-btn">
                        <span>{{ __($button_text) }}</span>
                        <i class="ti ti-arrow-right fs-3"></i>
                    </a>

                    {{-- Secondary Dismiss Link --}}
                    <div>
                        <span class="info-dismiss-link" onclick="closeModalSequential('informationModal')">
                            {{ __('No thanks, continue browsing') }}
                        </span>
                    </div>

                </div>
            </div>
        </div>

        @if ($confetti_effect == 1)
            <script src="{{ asset('js/confetti.browser.min.js') }}"></script>
        @endif
    @endif
@endif

{{-- 2. Newsletter Modal --}}
@if ($business_card_details != null)
    @if (
        !empty($business_card_details->is_newsletter_pop_active) &&
            $business_card_details->is_newsletter_pop_active == 1 &&
            !request()->cookie('newsletter_' . $business_card_details->card_id) &&
            empty(request()->getQueryString()))
        <div class="modal fade" id="newsletterModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
            <div class="modal-dialog modal-dialog-centered info-modal-dialog" role="document">
                <div class="modal-content newsletter-modal-card">

                    <button type="button" class="info-close-trigger" onclick="closeModalSequential('newsletterModal')"
                        aria-label="Close">
                        <i class="ti ti-x fs-2"></i>
                    </button>

                    <div class="newsletter-circle-icon">
                        <i class="ti ti-mail-opened fs-1"></i>
                    </div>

                    <h2 class="info-modal-title mb-2">{{ __('Subscribe to our newsletter') }}</h2>
                    <p class="info-modal-desc">
                        {{ __('Get exclusive deals, new arrivals, and special promotions delivered right to your inbox.') }}
                    </p>

                    <form action="{{ url('subscribe/store/newsletter') }}" method="post">
                        @csrf
                        <input type="hidden" id="card_id" name="card_id"
                            value="{{ $business_card_details->card_id }}">

                        <div class="mb-3 position-relative text-start">
                            <i class="ti ti-mail text-muted fs-3 position-absolute top-50 translate-middle-y ps-3"></i>
                            <input type="email" class="form-control newsletter-pill-input" id="newsletter_email"
                                placeholder="{{ __('Enter your email address') }}" name="email" required />
                        </div>

                        <button type="submit" class="info-cta-btn">
                            <i class="ti ti-send fs-3"></i>
                            <span>{{ __('Subscribe Now') }}</span>
                        </button>
                    </form>

                </div>
            </div>
        </div>
    @endif
@endif

{{-- 3. Modal Queue & Transition Controller --}}
<script>
    window.closeModalSequential = function(modalId) {
        const modalEl = document.getElementById(modalId);
        if (modalEl) {
            const instance = bootstrap.Modal.getInstance(modalEl);
            if (instance) instance.hide();
        }

        // Seamless queue transition to newsletter modal if information modal was closed
        if (modalId === 'informationModal') {
            const newsEl = document.getElementById('newsletterModal');
            if (newsEl) {
                setTimeout(function() {
                    const newsInstance = new bootstrap.Modal(newsEl, {
                        backdrop: 'static',
                        keyboard: true
                    });
                    newsInstance.show();
                }, 350);
            }
        }
    };

    document.addEventListener("DOMContentLoaded", function() {
        "use strict";

        const infoModalEl = document.getElementById('informationModal');
        const newsModalEl = document.getElementById('newsletterModal');

        if (infoModalEl) {
            setTimeout(function() {
                const infoInstance = new bootstrap.Modal(infoModalEl, {
                    backdrop: 'static',
                    keyboard: true
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
            }, 1200);
        } else if (newsModalEl) {
            setTimeout(function() {
                const newsInstance = new bootstrap.Modal(newsModalEl, {
                    backdrop: 'static',
                    keyboard: true
                });
                newsInstance.show();
            }, 1500);
        }
    });
</script>
