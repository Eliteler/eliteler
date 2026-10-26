@php
    use App\Newsletter;

    // Fetch newsletter popup details for the current business card
    $newsletter_pop = Newsletter::where('card_id', $business_card_details->card_id)->first();

    $nl_title = $newsletter_pop->title ?? __('The Clubhouse Newsletter');
    $nl_desc =
        $newsletter_pop->description ??
        __('Subscribe for exclusive tee-time discounts, pro-coaching clinics, and club tournament invites.');
    $nl_button_text = $newsletter_pop->button_text ?? __('Join Clubhouse');
@endphp

<div id="newsletterModal" data-csrf="{{ csrf_token() }}" data-email-error="{{ __('Please enter your email.') }}"
    data-vaild-email-error="{{ __('Please enter valid email.') }}" class="hidden">

    <div id="customNewsBox">

        {{-- Close Button --}}
        <button class="custom-news-close" type="button" onclick="window.closeNewsModal()">
            <i class="fas fa-times"></i>
        </button>

        {{-- Circular Icon Badge --}}
        <div class="custom-news-icon-wrap">
            <i class="fas fa-envelope-open-text custom-news-icon"></i>
        </div>

        {{-- Content Body --}}
        <div class="custom-news-body">

            {{-- Title --}}
            <h3 class="custom-news-title">{{ $nl_title }}</h3>

            {{-- Gold Golf Divider --}}
            <div class="custom-news-divider">
                <span class="custom-news-divider__line"></span>
                <span class="custom-news-divider__golf"><i class="fas fa-golf-ball"></i></span>
                <span class="custom-news-divider__line"></span>
            </div>

            {{-- Description Wrapper --}}
            <div class="custom-news-desc-wrapper">
                <div class="custom-news-desc">{!! $nl_desc !!}</div>
            </div>

            {{-- Messages --}}
            <div id="errorNewsMessage" class="news-message news-error"></div>
            <div id="successNewsMessage" class="news-message news-success"></div>

            {{-- Hidden Card ID --}}
            <input type="hidden" id="card_id" name="card_id" value="{{ $business_card_details->card_id }}">

            {{-- Email Input Field --}}
            <input type="email" id="newsletter_email" name="email" class="custom-news-input"
                placeholder="{{ __('Email Address') }}" required>

            {{-- Subscribe Button --}}
            <button id="subscribeButton" class="custom-news-btn">
                {{ $nl_button_text }}
            </button>

            {{-- Privacy Note --}}
            <p class="custom-news-privacy">
                <i class="fas fa-lock"></i>{{ __('No spam. Unsubscribe at any time.') }}
            </p>

        </div>
    </div>
</div>

<script>
    (function() {
        "use strict";

        // State variable to track newsletter modal visibility
        window.isNewsModalVisible = false;

        // Global function to show Newsletter Modal
        window.openNewsModal = function() {
            const modal = document.getElementById('newsletterModal');
            const box = document.getElementById('customNewsBox');

            if (modal) {
                // Teleport to root body to avoid getting trapped inside parent div
                if (modal.parentElement !== document.body) {
                    document.body.appendChild(modal);
                }

                // Force layout styles to appear on top
                modal.classList.remove('hidden');
                modal.style.setProperty('display', 'flex', 'important');
                modal.style.setProperty('opacity', '1', 'important');
                modal.style.setProperty('visibility', 'visible', 'important');
                modal.style.setProperty('z-index', '2147483647', 'important');
                modal.classList.add('is-active');
                window.isNewsModalVisible = true;

                // GSAP smooth pop-in animation if available
                if (box && typeof gsap !== 'undefined') {
                    gsap.fromTo(box, {
                        scale: 0.8,
                        y: 30,
                        opacity: 0
                    }, {
                        scale: 1,
                        y: 0,
                        opacity: 1,
                        duration: 0.5,
                        ease: "back.out(1.5)"
                    });
                }
            }
        };

        // Global function to close Newsletter Modal
        window.closeNewsModal = function() {
            const modal = document.getElementById('newsletterModal');
            if (modal) {
                modal.style.setProperty('display', 'none', 'important');
                modal.style.setProperty('opacity', '0', 'important');
                modal.classList.add('hidden');
                modal.classList.remove('is-active');
                window.isNewsModalVisible = false;
            }
        };

        // ── Gesture Tracker: Swipe Top to Show, Swipe Bottom to Hide ──
        document.addEventListener('DOMContentLoaded', function() {
            let touchStartY = 0;
            let touchStartX = 0;

            window.addEventListener('touchstart', function(e) {
                if (e.touches && e.touches.length > 0) {
                    touchStartY = e.touches[0].clientY;
                    touchStartX = e.touches[0].clientX;
                }
            }, {
                capture: true,
                passive: true
            });

            window.addEventListener('touchend', function(e) {
                if (!e.changedTouches || e.changedTouches.length === 0) return;

                const touchEndY = e.changedTouches[0].clientY;
                const touchEndX = e.changedTouches[0].clientX;
                const deltaY = touchEndY - touchStartY;
                const deltaX = Math.abs(touchEndX - touchStartX);

                // Validate vertical swipe gesture (> 40px)
                if (Math.abs(deltaY) > 40 && Math.abs(deltaY) > deltaX) {
                    // 1. Swipe Top (Upward movement): Show Newsletter Modal
                    if (deltaY < -40 && !window.isNewsModalVisible) {
                        window.openNewsModal();
                    }
                    // 2. Swipe Bottom (Downward movement): Hide Newsletter Modal
                    if (deltaY > 40 && window.isNewsModalVisible) {
                        window.closeNewsModal();
                    }
                }
            }, {
                capture: true,
                passive: true
            });
        });
    })();
</script>
