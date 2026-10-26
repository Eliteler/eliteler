<style>
    /* Ultra-Luxury Gift Shop Preloader Engine */
    .gift-preloader-overlay {
        position: fixed;
        inset: 0;
        background: #ffffff;
        z-index: 99999999;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-direction: column;
        transition: opacity 0.6s cubic-bezier(0.16, 1, 0.3, 1), visibility 0.6s cubic-bezier(0.16, 1, 0.3, 1);
        user-select: none;
    }

    .gift-preloader-container {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 16px;
    }

    .gift-orbit-box {
        position: relative;
        width: 82px;
        height: 82px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    /* Outer Track */
    .orbit-track {
        position: absolute;
        inset: 0;
        border-radius: 50%;
        border: 2px solid #fef2f2;
    }

    /* Outer Red Orbit Ring */
    .orbit-ring-outer {
        position: absolute;
        inset: 0;
        border-radius: 50%;
        border: 3px solid transparent;
        border-top-color: #dc2626;
        border-right-color: #dc2626;
        animation: giftOrbitSpin 1.3s cubic-bezier(0.68, -0.55, 0.27, 1.55) infinite;
    }

    /* Inner Dark Counter Orbit Ring */
    .orbit-ring-inner {
        position: absolute;
        inset: 8px;
        border-radius: 50%;
        border: 2.5px solid transparent;
        border-bottom-color: #1c1917;
        border-left-color: #1c1917;
        animation: giftOrbitCounter 1.1s linear infinite;
    }

    /* Center Breathing Avatar Pod */
    .preloader-center-pod {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        background: #fef2f2;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        box-shadow: 0 4px 15px rgba(220, 38, 38, 0.2);
        animation: giftBreathingPod 2s ease-in-out infinite;
    }

    .preloader-avatar-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .preloader-fallback-icon {
        color: #dc2626;
        font-size: 1.4rem;
    }

    /* Branding Typography */
    .preloader-brand-title {
        font-size: 1.15rem;
        font-weight: 700;
        color: #1c1917;
        margin: 0;
        letter-spacing: 0.3px;
        text-align: center;
    }

    .preloader-loading-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        padding: 4px 14px;
        border-radius: 50px !important;
        font-size: 0.72rem;
        font-weight: 700;
        color: #78716c;
        letter-spacing: 0.5px;
        text-transform: uppercase;
    }

    .preloader-pulsing-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: #dc2626;
        animation: giftDotBlink 1s infinite alternate;
    }

    @keyframes giftOrbitSpin {
        0% {
            transform: rotate(0deg);
        }

        100% {
            transform: rotate(360deg);
        }
    }

    @keyframes giftOrbitCounter {
        0% {
            transform: rotate(360deg);
        }

        100% {
            transform: rotate(0deg);
        }
    }

    @keyframes giftBreathingPod {

        0%,
        100% {
            transform: scale(0.92);
            box-shadow: 0 4px 12px rgba(220, 38, 38, 0.15);
        }

        50% {
            transform: scale(1.06);
            box-shadow: 0 8px 25px rgba(220, 38, 38, 0.3);
        }
    }

    @keyframes giftDotBlink {
        0% {
            opacity: 0.2;
            transform: scale(0.8);
        }

        100% {
            opacity: 1;
            transform: scale(1.2);
        }
    }
</style>

<div id="giftShopPreloader" class="gift-preloader-overlay">
    <div class="gift-preloader-container">
        {{-- Dual Orbit Spinner with Center Pod --}}
        <div class="gift-orbit-box">
            <div class="orbit-track"></div>
            <div class="orbit-ring-outer"></div>
            <div class="orbit-ring-inner"></div>
            <div class="preloader-center-pod">
                @if (!empty($business_card_details->profile))
                    <img src="{{ url($business_card_details->profile) }}" alt="Loading" class="preloader-avatar-img"
                        onerror="this.style.display='none'; document.getElementById('preloaderIconFallback').style.display='block';">
                    <i id="preloaderIconFallback" class="ti ti-gift preloader-fallback-icon" style="display: none;"></i>
                @else
                    <i class="ti ti-gift preloader-fallback-icon"></i>
                @endif
            </div>
        </div>

        {{-- Store Title --}}
        <h3 class="preloader-brand-title">{{ $card_details->title }}</h3>

        {{-- Loading Status Pill --}}
        <div class="preloader-loading-pill">
            <span class="preloader-pulsing-dot"></span>
            <span>{{ __('Loading Store') }}</span>
        </div>
    </div>
</div>

<script>
    // Smooth Preloader Dismissal
    (function() {
        "use strict";

        function dismissGiftPreloader() {
            const preloader = document.getElementById('giftShopPreloader');
            if (preloader) {
                preloader.style.opacity = '0';
                preloader.style.visibility = 'hidden';
                setTimeout(() => {
                    if (preloader && preloader.parentNode) {
                        preloader.parentNode.removeChild(preloader);
                    }
                }, 600);
            }
        }

        // Dismiss when window load finishes
        window.addEventListener('load', dismissGiftPreloader);

        // Fallback auto-dismiss timer (Max 2.2 seconds)
        setTimeout(dismissGiftPreloader, 2200);
    })();
</script>
