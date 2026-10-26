<style>
    /* Ultra-Luxury Fullscreen Canvas */
    .store-luxury-preloader {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background-color: #faf8f5;
        z-index: 99999999;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        transition: opacity 0.5s cubic-bezier(0.16, 1, 0.3, 1), visibility 0.5s ease;
        pointer-events: none;
    }

    .preloader-hidden {
        opacity: 0 !important;
        visibility: hidden !important;
    }

    /* Central Orbit Pod */
    .preloader-core-pod {
        position: relative;
        width: 86px;
        height: 86px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 16px;
    }

    /* Outer Clockwise Camel Ring (#cca47a) */
    .preloader-orbit-outer {
        position: absolute;
        width: 100%;
        height: 100%;
        border-radius: 50%;
        border: 2.5px solid transparent;
        border-top-color: #cca47a;
        border-right-color: rgba(204, 164, 122, 0.4);
        animation: orbitSpinClockwise 1.4s cubic-bezier(0.68, -0.55, 0.27, 1.55) infinite;
    }

    /* Inner Counter-Clockwise Teal Ring (#1f4b48) */
    .preloader-orbit-inner {
        position: absolute;
        width: 68px;
        height: 68px;
        border-radius: 50%;
        border: 2px dashed transparent;
        border-bottom-color: #1f4b48;
        border-left-color: rgba(31, 75, 72, 0.5);
        animation: orbitSpinCounter 1.1s linear infinite;
    }

    /* Floating Brand Logo with Pulse & Ambient Shadow */
    .preloader-brand-logo {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        object-fit: cover;
        background-color: #ffffff;
        border: 2px solid #ffffff;
        box-shadow: 0 8px 24px rgba(204, 164, 122, 0.35);
        animation: brandBreathingPulse 2s ease-in-out infinite;
        z-index: 2;
    }

    /* Ambient Brand Label */
    .preloader-brand-label {
        font-family: 'DM Serif Display', serif;
        font-size: 16px;
        color: #1e293b;
        letter-spacing: 0.8px;
        animation: textFadePulse 1.8s ease-in-out infinite;
    }

    @keyframes orbitSpinClockwise {
        0% {
            transform: rotate(0deg);
        }

        100% {
            transform: rotate(360deg);
        }
    }

    @keyframes orbitSpinCounter {
        0% {
            transform: rotate(0deg);
        }

        100% {
            transform: rotate(-360deg);
        }
    }

    @keyframes brandBreathingPulse {

        0%,
        100% {
            transform: scale(0.92);
        }

        50% {
            transform: scale(1.05);
        }
    }

    @keyframes textFadePulse {

        0%,
        100% {
            opacity: 0.6;
        }

        50% {
            opacity: 1;
        }
    }
</style>

<div class="store-luxury-preloader" id="storePreloader">
    <div class="preloader-core-pod">
        <div class="preloader-orbit-outer"></div>
        <div class="preloader-orbit-inner"></div>
        <img src="{{ !empty($business_card_details->profile) ? url($business_card_details->profile) : 'data:image/svg+xml;charset=UTF-8,%3Csvg%20width%3D%2248%22%20height%3D%2248%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%3E%3Ccircle%20cx%3D%2224%22%20cy%3D%2224%22%20r%3D%2224%22%20fill%3D%22%23cca47a%22%2F%3E%3C%2Fsvg%3E' }}"
            alt="Loading..." class="preloader-brand-logo">
    </div>
    <div class="preloader-brand-label">
        {{ $card_details->title }}
    </div>
</div>

<script>
    // High-Performance Auto-Dismiss Preloader Engine
    (function() {
        "use strict";

        function hideLuxuryPreloader() {
            const preloader = document.getElementById('storePreloader');
            if (preloader && !preloader.classList.contains('preloader-hidden')) {
                preloader.classList.add('preloader-hidden');
                setTimeout(() => {
                    if (preloader.parentNode) preloader.parentNode.removeChild(preloader);
                }, 550);
            }
        }

        // Fast dismiss when document is interactive
        if (document.readyState === 'complete' || document.readyState === 'interactive') {
            setTimeout(hideLuxuryPreloader, 350);
        } else {
            document.addEventListener('DOMContentLoaded', function() {
                setTimeout(hideLuxuryPreloader, 450);
            });
        }

        // Strict fail-safe timeout
        window.addEventListener('load', hideLuxuryPreloader);
        setTimeout(hideLuxuryPreloader, 1500);
    })();
</script>
