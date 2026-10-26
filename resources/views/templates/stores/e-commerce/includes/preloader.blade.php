{{-- Luxury Branded Pure CSS Auto-Dismiss Preloader --}}
<div class="preloader-overlay" id="store-preloader">
    <div class="preloader-inner text-center">
        <!-- Glowing Branded Orbit Container -->
        <div class="preloader-brand-wrapper">
            <div class="preloader-orbit-ring"></div>
            <div class="preloader-orbit-ring-pulse"></div>

            <img src="{{ url($business_card_details->profile) }}" alt="{{ $card_details->title }}"
                class="preloader-brand-logo rounded-circle shadow-sm"
                onerror="this.onerror=null; this.src='{{ asset('img/templates/all-categories.png') }}';">
        </div>

        <!-- Store Name with Soft Fade-up -->
        <h4 class="preloader-store-name text-dark mt-3 mb-2">
            {{ $card_details->title }}
        </h4>

        <!-- Ultra-Sleek Indeterminate Progress Line -->
        <div class="preloader-progress-track">
            <div class="preloader-progress-bar"></div>
        </div>
    </div>
</div>

<style>
    /* Fail-Safe Auto-Dismiss Keyframe: Guarantees page reveals even if JS stalls */
    @keyframes autoDismissPreloader {

        0%,
        75% {
            opacity: 1;
            visibility: visible;
        }

        100% {
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
            display: none !important;
        }
    }

    /* Primary Container */
    .preloader-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background-color: #ffffff;
        z-index: 999999;
        display: flex;
        align-items: center;
        justify-content: center;
        animation: autoDismissPreloader 1.25s cubic-bezier(0.4, 0, 0.2, 1) forwards !important;
        user-select: none;
    }

    .preloader-inner {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }

    /* Branded Orbit Wrapper */
    .preloader-brand-wrapper {
        position: relative;
        width: 84px;
        height: 84px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .preloader-brand-logo {
        width: 58px;
        height: 58px;
        object-fit: cover;
        z-index: 3;
        animation: logoBreathingPulse 2s ease-in-out infinite;
    }

    /* Outer Orbit Spinner Ring */
    .preloader-orbit-ring {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        border-radius: 50%;
        border: 2.5px solid transparent;
        border-top-color: #206bc4;
        border-right-color: #206bc4;
        animation: spinOrbit 0.9s cubic-bezier(0.6, 0.2, 0.4, 0.8) infinite;
        z-index: 2;
    }

    /* Glowing Pulsing Ring Behind Logo */
    .preloader-orbit-ring-pulse {
        position: absolute;
        top: 4px;
        left: 4px;
        width: calc(100% - 8px);
        height: calc(100% - 8px);
        border-radius: 50%;
        background-color: rgba(32, 107, 196, 0.08);
        animation: ringPulseEffect 1.8s ease-out infinite;
        z-index: 1;
    }

    /* Store Title */
    .preloader-store-name {
        font-family: 'Outfit', sans-serif;
        font-size: 1.15rem;
        font-weight: 700;
        letter-spacing: -0.2px;
    }

    /* Progress Track & Bar */
    .preloader-progress-track {
        width: 130px;
        height: 3px;
        background-color: #f1f5f9;
        border-radius: 10px;
        overflow: hidden;
        position: relative;
    }

    .preloader-progress-bar {
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, #206bc4 0%, #60a5fa 100%);
        border-radius: 10px;
        animation: indeterminateProgressBar 1.2s ease-in-out infinite;
    }

    /* Keyframe Animations */
    @keyframes spinOrbit {
        0% {
            transform: rotate(0deg);
        }

        100% {
            transform: rotate(360deg);
        }
    }

    @keyframes logoBreathingPulse {

        0%,
        100% {
            transform: scale(1);
        }

        50% {
            transform: scale(1.05);
        }
    }

    @keyframes ringPulseEffect {
        0% {
            transform: scale(0.9);
            opacity: 0.8;
        }

        100% {
            transform: scale(1.35);
            opacity: 0;
        }
    }

    @keyframes indeterminateProgressBar {
        0% {
            left: -100%;
            width: 40%;
        }

        50% {
            left: 25%;
            width: 60%;
        }

        100% {
            left: 100%;
            width: 100%;
        }
    }
</style>

<script>
    // Graceful JS Dismiss on DOMContentLoaded with zero stalling
    document.addEventListener("DOMContentLoaded", function() {
        var loader = document.getElementById("store-preloader");
        if (loader) {
            setTimeout(function() {
                loader.style.transition = "opacity 0.35s ease, visibility 0.35s ease";
                loader.style.opacity = "0";
                loader.style.visibility = "hidden";
                setTimeout(function() {
                    loader.style.display = "none";
                }, 350);
            }, 600);
        }
    });
</script>
