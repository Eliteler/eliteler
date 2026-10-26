{{-- Preloader Component --}}
<div id="storePreloader" class="luxury-preloader-backdrop">
    <div class="preloader-content-pod">
        <div class="orbit-spinner">
            <div class="orbit-ring orbit-ring-outer"></div>
            <div class="orbit-ring orbit-ring-inner"></div>
            <div class="preloader-brand-avatar">
                @if (!empty($business_card_details->profile))
                    <img src="{{ url($business_card_details->profile) }}" alt="{{ $card_details->title ?? 'Store Logo' }}"
                        onerror="this.onerror=null;this.src='data:image/svg+xml;charset=UTF-8,%3Csvg%20width%3D%2260%22%20height%3D%2260%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%3E%3Crect%20width%3D%22100%25%22%20height%3D%22100%25%22%20fill%3D%22%23d4af37%22%2F%3E%3Ctext%20x%3D%2250%25%22%20y%3D%2250%25%22%20fill%3D%22%23ffffff%22%20font-size%3D%2218%22%20text-anchor%3D%22middle%22%20dy%3D%22.3em%22%3EG%3C%2Ftext%3E%3C%2Fsvg%3E';" />
                @else
                    <span class="preloader-fallback-glyph">✨</span>
                @endif
            </div>
        </div>
        <p class="preloader-caption">{{ __('Loading Store...') }}</p>
    </div>
</div>

<style>
    .luxury-preloader-backdrop {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background: #fffcf8;
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 9999999;
        transition: opacity 0.4s ease, visibility 0.4s ease;
    }

    .preloader-content-pod {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 16px;
    }

    .orbit-spinner {
        position: relative;
        width: 80px;
        height: 80px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .orbit-ring {
        position: absolute;
        border-radius: 50% !important;
        border: 2px solid transparent;
    }

    .orbit-ring-outer {
        width: 80px;
        height: 80px;
        border-top-color: #d4af37;
        border-bottom-color: #d4af37;
        animation: preloaderOrbitOuter 1.4s cubic-bezier(0.5, 0, 0.5, 1) infinite;
    }

    .orbit-ring-inner {
        width: 62px;
        height: 62px;
        border-left-color: #996515;
        border-right-color: #996515;
        animation: preloaderOrbitInner 1s linear infinite reverse;
    }

    .preloader-brand-avatar {
        width: 44px;
        height: 44px;
        border-radius: 50% !important;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #fbf7ee;
        box-shadow: 0 4px 12px rgba(212, 175, 55, 0.2);
    }

    .preloader-brand-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .preloader-fallback-glyph {
        font-size: 20px;
    }

    .preloader-caption {
        font-size: 13px;
        font-weight: 600;
        letter-spacing: 0.5px;
        text-transform: uppercase;
        color: #8c733e;
        margin: 0;
    }

    @keyframes preloaderOrbitOuter {
        0% {
            transform: rotate(0deg);
        }

        100% {
            transform: rotate(360deg);
        }
    }

    @keyframes preloaderOrbitInner {
        0% {
            transform: rotate(0deg);
        }

        100% {
            transform: rotate(-360deg);
        }
    }
</style>

<script>
    // Dismiss preloader safely
    window.addEventListener('load', function() {
        const preloader = document.getElementById('storePreloader');
        if (preloader) {
            preloader.style.opacity = '0';
            setTimeout(() => {
                preloader.style.display = 'none';
            }, 400);
        }
    });
    // Fallback timer if load event hangs
    setTimeout(() => {
        const preloader = document.getElementById('storePreloader');
        if (preloader && preloader.style.display !== 'none') {
            preloader.style.opacity = '0';
            setTimeout(() => {
                preloader.style.display = 'none';
            }, 400);
        }
    }, 4000);
</script>
