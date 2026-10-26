{{-- Luxury Brand Preloader with Logo & Pulse Glow --}}
<style>
    .site-preloader {
        position: fixed !important;
        inset: 0 !important;
        width: 100vw !important;
        height: 100vh !important;
        background-color: #ffffff !important;
        z-index: 9999999 !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        flex-direction: column !important;
        transition: opacity 0.4s ease, visibility 0.4s ease !important;
    }

    .preloader-logo-ring {
        position: relative;
        width: 78px;
        height: 78px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    /* Spinning Rose Gradient Ring */
    .preloader-spinner-circle {
        position: absolute;
        inset: 0;
        border-radius: 50%;
        border: 2.5px solid transparent;
        border-top-color: #e91e63;
        border-right-color: #f43f5e;
        animation: preloaderSpin 0.9s linear infinite;
    }

    .preloader-avatar-img {
        width: 58px;
        height: 58px;
        border-radius: 50%;
        object-fit: cover;
        box-shadow: 0 4px 15px rgba(233, 30, 99, 0.15);
    }

    .preloader-brand-title {
        font-family: 'Merriweather', serif;
        font-size: 14px;
        font-weight: 700;
        color: #0f172a;
        letter-spacing: 0.5px;
    }

    .preloader-dots-pulse {
        display: inline-flex;
        gap: 4px;
    }

    .preloader-dot {
        width: 5px;
        height: 5px;
        background-color: #e91e63;
        border-radius: 50%;
        animation: dotPulse 1.2s infinite ease-in-out;
    }

    .preloader-dot:nth-child(2) {
        animation-delay: 0.2s;
    }

    .preloader-dot:nth-child(3) {
        animation-delay: 0.4s;
    }

    @keyframes preloaderSpin {
        0% {
            transform: rotate(0deg);
        }

        100% {
            transform: rotate(360deg);
        }
    }

    @keyframes dotPulse {

        0%,
        80%,
        100% {
            transform: scale(0.6);
            opacity: 0.4;
        }

        40% {
            transform: scale(1.2);
            opacity: 1;
        }
    }

    .preloader-fade-out {
        opacity: 0 !important;
        visibility: hidden !important;
        pointer-events: none !important;
    }
</style>

<div class="site-preloader" id="sitePreloader">
    <div class="preloader-logo-ring mb-3">
        <div class="preloader-spinner-circle"></div>
        <img src="{{ url($business_card_details->profile) }}" alt="{{ $card_details->title }}" class="preloader-avatar-img"
            onerror="this.onerror=null; this.src='{{ asset('img/templates/all-categories.png') }}';">
    </div>

    <!-- Store Brand Name & Loading Dots -->
    <div class="text-center">
        <span class="preloader-brand-title d-block mb-1">{{ $card_details->title }}</span>
        <div class="preloader-dots-pulse">
            <span class="preloader-dot"></span>
            <span class="preloader-dot"></span>
            <span class="preloader-dot"></span>
        </div>
    </div>
</div>

<script>
    (function() {
        "use strict";

        function hideSitePreloader() {
            const preloader = document.getElementById('sitePreloader');
            if (preloader) {
                preloader.classList.add('preloader-fade-out');
                setTimeout(() => {
                    preloader.style.display = 'none';
                }, 400);
            }
        }

        // Hide when DOM is ready
        window.addEventListener('load', hideSitePreloader);

        // Fail-safe timeout (Guaranteed hide after 1.5s even if something stalls)
        setTimeout(hideSitePreloader, 1500);
    })();
</script>
