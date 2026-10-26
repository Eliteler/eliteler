{{-- Ultra-Luxury High-Fashion Preloader Component --}}
<style>
    /* Fullscreen High-End Stage */
    .fashion-luxury-preloader {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background: radial-gradient(circle at center, #1e293b 0%, #0f172a 100%);
        z-index: 99999999;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        transition: opacity 0.5s cubic-bezier(0.4, 0, 0.2, 1), visibility 0.5s ease;
    }

    .fashion-luxury-preloader.fade-out {
        opacity: 0;
        visibility: hidden;
    }

    /* Core Orbit Assembly */
    .fashion-orbit-box {
        position: relative;
        width: 100px;
        height: 100px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    /* Ambient Glow Behind Logo */
    .fashion-ambient-glow {
        position: absolute;
        width: 70px;
        height: 70px;
        background: rgba(217, 119, 6, 0.25);
        border-radius: 50%;
        filter: blur(18px);
        animation: fashionGlowPulse 2s ease-in-out infinite alternate;
    }

    /* Outer Counter-Rotating Rings */
    .fashion-ring-primary {
        position: absolute;
        inset: 0;
        border-radius: 50%;
        border: 1.5px solid transparent;
        border-top-color: #d97706;
        border-bottom-color: #d97706;
        animation: fashionSpinClockwise 1.6s cubic-bezier(0.68, -0.55, 0.27, 1.55) infinite;
    }

    .fashion-ring-secondary {
        position: absolute;
        inset: 6px;
        border-radius: 50%;
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-left-color: #ffffff;
        border-right-color: #ffffff;
        animation: fashionSpinAntiClockwise 1.2s linear infinite;
    }

    /* Center Brand Monogram / Avatar */
    .fashion-brand-logo {
        width: 64px;
        height: 64px;
        border-radius: 50%;
        object-fit: cover;
        z-index: 2;
        border: 2px solid rgba(255, 255, 255, 0.2);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.5);
        animation: fashionLogoFloat 2s ease-in-out infinite alternate;
    }

    /* Typography: Shimmering Title */
    .fashion-preload-title {
        font-family: 'Oswald', sans-serif;
        font-size: 15px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 4px;
        margin-top: 24px;
        margin-bottom: 0;
        background: linear-gradient(90deg, #94a3b8 0%, #ffffff 50%, #d97706 100%);
        background-size: 200% auto;
        color: transparent;
        -webkit-background-clip: text;
        background-clip: text;
        animation: fashionTextShimmer 2.5s linear infinite;
    }

    /* Minimal High-Fashion Loading Line */
    .fashion-precision-track {
        width: 80px;
        height: 2px;
        background-color: rgba(255, 255, 255, 0.08);
        margin-top: 14px;
        position: relative;
        overflow: hidden;
    }

    .fashion-precision-thumb {
        position: absolute;
        top: 0;
        left: 0;
        width: 40%;
        height: 100%;
        background: linear-gradient(90deg, #d97706, #fbbf24);
        animation: fashionTrackSlide 1.4s ease-in-out infinite;
    }

    /* Keyframes */
    @keyframes fashionSpinClockwise {
        0% {
            transform: rotate(0deg);
        }

        100% {
            transform: rotate(360deg);
        }
    }

    @keyframes fashionSpinAntiClockwise {
        0% {
            transform: rotate(360deg);
        }

        100% {
            transform: rotate(0deg);
        }
    }

    @keyframes fashionLogoFloat {
        0% {
            transform: scale(0.95);
        }

        100% {
            transform: scale(1.03);
        }
    }

    @keyframes fashionGlowPulse {
        0% {
            opacity: 0.3;
            transform: scale(0.8);
        }

        100% {
            opacity: 0.7;
            transform: scale(1.2);
        }
    }

    @keyframes fashionTextShimmer {
        0% {
            background-position: 0% center;
        }

        100% {
            background-position: 200% center;
        }
    }

    @keyframes fashionTrackSlide {
        0% {
            transform: translateX(-100%);
        }

        50% {
            transform: translateX(100%);
        }

        100% {
            transform: translateX(250%);
        }
    }
</style>

<div id="storePreloader" class="fashion-luxury-preloader">
    <div class="fashion-orbit-box">
        <div class="fashion-ambient-glow"></div>
        <div class="fashion-ring-primary"></div>
        <div class="fashion-ring-secondary"></div>
        <img src="{{ url($business_card_details->profile ?? 'images/default-avatar.png') }}"
            alt="{{ $card_details->title }}" class="fashion-brand-logo">
    </div>

    <h2 class="fashion-preload-title">{{ $card_details->title }}</h2>

    <div class="fashion-precision-track">
        <div class="fashion-precision-thumb"></div>
    </div>
</div>

<script>
    // Smooth Preloader Auto-Dismissal
    document.addEventListener("DOMContentLoaded", function() {
        const preloader = document.getElementById('storePreloader');
        if (preloader) {
            setTimeout(function() {
                preloader.classList.add('fade-out');
                setTimeout(function() {
                    preloader.remove();
                }, 500);
            }, 400);
        }
    });
</script>
