{{-- Preloader Component with Breathing Orbit Effect --}}
<div id="store-preloader" class="store-preloader-wrapper">
    <div class="preloader-orbit-container">
        <div class="preloader-orbit-ring ring-outer"></div>
        <div class="preloader-orbit-ring ring-inner"></div>
        <div class="preloader-logo-pulse">
            <svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="#2e7d32" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round">
                <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                <line x1="3" y1="6" x2="21" y2="6"></line>
                <path d="M16 10a4 4 0 0 1-8 0"></path>
            </svg>
        </div>
    </div>
</div>

<style>
    /* Preloader Styles */
    .store-preloader-wrapper {
        position: fixed;
        inset: 0;
        background-color: #f7faf7;
        z-index: 999999;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: opacity 0.4s ease, visibility 0.4s ease;
    }

    .store-preloader-wrapper.loaded {
        opacity: 0;
        visibility: hidden;
        pointer-events: none;
    }

    .preloader-orbit-container {
        position: relative;
        width: 80px;
        height: 80px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .preloader-orbit-ring {
        position: absolute;
        border-radius: 50% !important;
        border: 3px solid transparent;
    }

    .ring-outer {
        inset: 0;
        border-top-color: #2e7d32;
        border-bottom-color: #a5d6a7;
        animation: preloader-spin 1.2s cubic-bezier(0.68, -0.55, 0.27, 1.55) infinite;
    }

    .ring-inner {
        inset: 10px;
        border-left-color: #1b5e20;
        border-right-color: #81c784;
        animation: preloader-spin-reverse 0.9s linear infinite;
    }

    .preloader-logo-pulse {
        animation: preloader-pulse 1.4s ease-in-out infinite;
    }

    @keyframes preloader-spin {
        100% {
            transform: rotate(360deg);
        }
    }

    @keyframes preloader-spin-reverse {
        100% {
            transform: rotate(-360deg);
        }
    }

    @keyframes preloader-pulse {

        0%,
        100% {
            transform: scale(0.9);
            opacity: 0.8;
        }

        50% {
            transform: scale(1.15);
            opacity: 1;
        }
    }
</style>

<script>
    // Dismiss preloader safely once window finishes loading
    window.addEventListener('load', function() {
        const loader = document.getElementById('store-preloader');
        if (loader) {
            setTimeout(() => {
                loader.classList.add('loaded');
                setTimeout(() => loader.remove(), 400);
            }, 300);
        }
    });

    // Fallback safety timeout
    setTimeout(() => {
        const loader = document.getElementById('store-preloader');
        if (loader && !loader.classList.contains('loaded')) {
            loader.classList.add('loaded');
            setTimeout(() => loader.remove(), 400);
        }
    }, 3000);
</script>
