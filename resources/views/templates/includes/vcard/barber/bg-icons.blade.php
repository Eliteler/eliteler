{{-- Barbershop vCard — Full Width 3D Background Canvas Partial --}}
<div class="bb-3d-bg-canvas fixed inset-0 w-screen h-screen pointer-events-none z-0 overflow-hidden" aria-hidden="true">
 
    {{-- Shared SVG Gradients --}}
    <svg class="absolute w-0 h-0" aria-hidden="true" focusable="false">
        <defs>
            <linearGradient id="bbGoldGrad" x1="0%" y1="0%" x2="100%" y2="100%"> 
                <stop offset="0%" stop-color="#f5e0a3" />
                <stop offset="50%" stop-color="#c9a24b" />
                <stop offset="100%" stop-color="#73561a" />
            </linearGradient>
            <linearGradient id="bbRedGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                <stop offset="0%" stop-color="#e2484e" />
                <stop offset="50%" stop-color="#b7282e" />
                <stop offset="100%" stop-color="#4a0f12" />
            </linearGradient>
            <filter id="bb3dGlow">
                <feDropShadow dx="0" dy="15" stdDeviation="12" flood-color="#000000"
                    flood-opacity="0.75" />
            </filter>
        </defs> 
    </svg>

    {{-- 1. Full-Width Background Ambient Barber Pole Sweep Overlay --}}
    <div class="bb-fullwidth-pole-bg"></div>

    {{-- 2. Left Floating 3D Scissors --}}
    <div class="bb-3d-item bb-pos-left-top">
        <svg viewBox="0 0 100 100" class="bb-3d-svg" filter="url(#bb3dGlow)">
            <g fill="url(#bbGoldGrad)">
                <path class="bb-blade-left"
                    d="M30 80 C 20 80, 15 70, 25 60 C 35 50, 48 48, 50 45 L 75 15 C 77 12, 80 15, 78 18 L 52 48 Z M 25 75 C 20 75, 20 65, 25 65 C 30 65, 30 75, 25 75 Z" />
                <path class="bb-blade-right"
                    d="M70 80 C 80 80, 85 70, 75 60 C 65 50, 52 48, 50 45 L 25 15 C 23 12, 20 15, 22 18 L 48 48 Z M 75 75 C 80 75, 80 65, 75 65 C 70 65, 70 75, 75 75 Z" />
                <circle cx="50" cy="46" r="3.5" fill="#14100D" stroke="#c9a24b" stroke-width="1.5" />
            </g>
        </svg>
    </div>

    {{-- 3. Center-Left Floating 3D Razor --}}
    <div class="bb-3d-item bb-pos-center-left">
        <svg viewBox="0 0 120 120" class="bb-3d-svg" filter="url(#bb3dGlow)">
            <g>
                <path d="M15 85 C 30 95, 80 90, 95 65 C 98 60, 92 58, 88 62 C 75 80, 32 82, 18 75 Z"
                    fill="url(#bbRedGrad)" />
                <path d="M22 72 L 85 25 C 90 20, 100 28, 95 34 L 38 78 Z" fill="url(#bbGoldGrad)" />
                <circle cx="22" cy="74" r="3" fill="#c9a24b" />
            </g>
        </svg>
    </div>

    {{-- 4. Top Center Floating Mini Barber Pole --}}
    <div class="bb-3d-item bb-pos-top-center">
        <div class="bb-3d-pole-glass">
            <div class="bb-3d-pole-cap-top"></div>
            <div class="bb-3d-pole-stripes"></div>
            <div class="bb-3d-pole-cap-bottom"></div>
        </div>
    </div>

    {{-- 5. Center-Right Floating Mustache & Comb --}}
    <div class="bb-3d-item bb-pos-center-right">
        <svg viewBox="0 0 100 100" class="bb-3d-svg" filter="url(#bb3dGlow)">
            <g fill="url(#bbGoldGrad)">
                <path
                    d="M50 55 C 40 40, 15 42, 5 58 C 15 68, 38 65, 50 58 C 62 65, 85 68, 95 58 C 85 42, 60 40, 50 55 Z" />
                <rect x="10" y="25" width="80" height="8" rx="3" />
                <rect x="15" y="33" width="3" height="12" />
                <rect x="25" y="33" width="3" height="12" />
                <rect x="35" y="33" width="3" height="12" />
                <rect x="45" y="33" width="3" height="12" />
                <rect x="55" y="33" width="3" height="12" />
                <rect x="65" y="33" width="3" height="12" />
                <rect x="75" y="33" width="3" height="12" />
                <rect x="85" y="33" width="3" height="12" />
            </g>
        </svg>
    </div>

    {{-- 6. Far-Right Floating Hair Clipper / Trimmer --}}
    <div class="bb-3d-item bb-pos-far-right">
        <svg viewBox="0 0 100 100" class="bb-3d-svg" filter="url(#bb3dGlow)">
            <g fill="url(#bbGoldGrad)">
                <!-- Clipper Blade Teeth -->
                <rect x="30" y="10" width="40" height="6" rx="1" fill="#ede4d3" />
                <rect x="32" y="16" width="36" height="4" fill="#b7282e" />
                <!-- Body -->
                <path d="M34 20 L 66 20 L 60 85 C 58 92, 42 92, 40 85 Z" />
                <circle cx="50" cy="50" r="4" fill="#14100D" />
                <rect x="48" y="65" width="4" height="12" rx="2" fill="#ede4d3" />
            </g>
        </svg>
    </div>

</div>

{{-- 3D Styles & Full-Width Motion Animations --}}
<style>
    /* Full-Width Canvas Stage */
    .bb-3d-bg-canvas {
        perspective: 1400px;
        perspective-origin: 50% 50%;
        width: 100vw;
        height: 100vh;
    }

    /* Ambient Full-Width Barber Pole Background Stripe Gradient Sweep */
    .bb-fullwidth-pole-bg {
        position: absolute;
        inset: 0;
        width: 100vw;
        height: 100vh;
        z-index: -1;
        opacity: 0.035;
        background: repeating-linear-gradient(-45deg,
                var(--bb-gold) 0 40px,
                transparent 40px 80px,
                var(--bb-red) 80px 120px,
                transparent 120px 160px);
        background-size: 200% 200%;
        animation: bbFullWidthStripeSweep 30s linear infinite;
    }

    /* Base 3D Element */
    .bb-3d-item {
        position: absolute;
        transform-style: preserve-3d;
        will-change: transform;
        opacity: 0.16;
        transition: opacity 0.5s ease;
    }

    .bb-3d-svg {
        width: 100%;
        height: 100%;
        display: block;
    }

    /* Distributed Full-Width Positions & Sizes */
    .bb-pos-left-top {
        top: 8%;
        left: 2%;
        width: 16rem;
        height: 16rem;
        animation: bbFloatWide1 16s ease-in-out infinite alternate;
    }

    .bb-pos-center-left {
        top: 55%;
        left: 12%;
        width: 18rem;
        height: 18rem;
        animation: bbFloatWide2 18s ease-in-out infinite alternate;
    }

    .bb-pos-top-center {
        top: 4%;
        left: 48%;
        width: 4.5rem;
        height: 16rem;
        animation: bbFloatWide3 15s ease-in-out infinite alternate;
    }

    .bb-pos-center-right {
        top: 60%;
        right: 14%;
        width: 17rem;
        height: 17rem;
        animation: bbFloatWide4 19s ease-in-out infinite alternate;
    }

    .bb-pos-far-right {
        top: 12%;
        right: 3%;
        width: 16rem;
        height: 16rem;
        animation: bbFloatWide5 17s ease-in-out infinite alternate;
    }

    /* 3D Mini Barber Pole Styling */
    .bb-3d-pole-glass {
        width: 100%;
        height: 100%;
        position: relative;
        border-radius: 999px;
        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.8);
    }

    .bb-3d-pole-cap-top,
    .bb-3d-pole-cap-bottom {
        width: 125%;
        height: 1.4rem;
        background: linear-gradient(135deg, #f5e0a3, #c9a24b, #543e14);
        border-radius: 8px;
        position: absolute;
        left: -12.5%;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.5);
    }

    .bb-3d-pole-cap-top {
        top: 0;
    }

    .bb-3d-pole-cap-bottom {
        bottom: 0;
    }

    .bb-3d-pole-stripes {
        width: 100%;
        height: 100%;
        border-radius: 999px;
        background: repeating-linear-gradient(-45deg,
                #b7282e 0 16px,
                #ede4d3 16px 32px,
                #1f4e5f 32px 48px,
                #ede4d3 48px 64px);
        background-size: 100% 200%;
        animation: bbPoleSpinFast 2.5s linear infinite;
    }

    /* Full-Width Drifting Keyframe Animations */
    @keyframes bbFloatWide1 {
        0% {
            transform: translate3d(0, 0, 0) rotateX(20deg) rotateY(-25deg) rotateZ(-12deg);
        }

        50% {
            transform: translate3d(80px, -60px, 120px) rotateX(-15deg) rotateY(15deg) rotateZ(20deg);
        }

        100% {
            transform: translate3d(-40px, 40px, -60px) rotateX(30deg) rotateY(-35deg) rotateZ(-25deg);
        }
    }

    @keyframes bbFloatWide2 {
        0% {
            transform: translate3d(0, 0, 0) rotateX(-25deg) rotateY(30deg) rotateZ(18deg);
        }

        50% {
            transform: translate3d(-90px, 70px, 140px) rotateX(20deg) rotateY(-20deg) rotateZ(-15deg);
        }

        100% {
            transform: translate3d(60px, -50px, -80px) rotateX(-35deg) rotateY(40deg) rotateZ(25deg);
        }
    }

    @keyframes bbFloatWide3 {
        0% {
            transform: translate3d(0, 0, 0) rotateX(15deg) rotateY(20deg) rotateZ(-8deg);
        }

        50% {
            transform: translate3d(-70px, 60px, 90px) rotateX(-20deg) rotateY(-25deg) rotateZ(15deg);
        }

        100% {
            transform: translate3d(50px, -40px, -50px) rotateX(25deg) rotateY(20deg) rotateZ(-18deg);
        }
    }

    @keyframes bbFloatWide4 {
        0% {
            transform: translate3d(0, 0, 0) rotateX(-20deg) rotateY(-30deg) rotateZ(15deg);
        }

        50% {
            transform: translate3d(100px, -80px, 130px) rotateX(25deg) rotateY(20deg) rotateZ(-25deg);
        }

        100% {
            transform: translate3d(-60px, 50px, -70px) rotateX(-30deg) rotateY(-35deg) rotateZ(20deg);
        }
    }

    @keyframes bbFloatWide5 {
        0% {
            transform: translate3d(0, 0, 0) rotateX(25deg) rotateY(15deg) rotateZ(-20deg);
        }

        50% {
            transform: translate3d(-110px, 80px, 110px) rotateX(-15deg) rotateY(-20deg) rotateZ(15deg);
        }

        100% {
            transform: translate3d(70px, -60px, -90px) rotateX(30deg) rotateY(25deg) rotateZ(-30deg);
        }
    }

    @keyframes bbFullWidthStripeSweep {
        0% {
            background-position: 0 0;
        }

        100% {
            background-position: 100% 100%;
        }
    }

    @keyframes bbPoleSpinFast {
        0% {
            background-position-y: 0%;
        }

        100% {
            background-position-y: -200%;
        }
    }

    /* Scissor Snip Motion */
    .bb-blade-left {
        transform-origin: 50px 46px;
        animation: bbSnipLeftWide 1.8s ease-in-out infinite;
    }

    .bb-blade-right {
        transform-origin: 50px 46px;
        animation: bbSnipRightWide 1.8s ease-in-out infinite;
    }

    @keyframes bbSnipLeftWide {

        0%,
        100% {
            transform: rotate(0deg);
        }

        30% {
            transform: rotate(-15deg);
        }

        60% {
            transform: rotate(0deg);
        }
    }

    @keyframes bbSnipRightWide {

        0%,
        100% {
            transform: rotate(0deg);
        }

        30% {
            transform: rotate(15deg);
        }

        60% {
            transform: rotate(0deg);
        }
    }

    /* Mobile Viewport Sizing Adjustments */
    @media (max-width: 768px) {
        .bb-pos-left-top {
            width: 10rem;
            height: 10rem;
            left: 1%;
            top: 5%;
        }

        .bb-pos-center-left {
            width: 11rem;
            height: 11rem;
            left: 3%;
            top: 50%;
        }

        .bb-pos-top-center {
            width: 2.8rem;
            height: 10rem;
            left: 45%;
            top: 2%;
        }

        .bb-pos-center-right {
            width: 10rem;
            height: 10rem;
            right: 2%;
            top: 58%;
        }

        .bb-pos-far-right {
            width: 10rem;
            height: 10rem;
            right: 1%;
            top: 10%;
        }
    }
</style>
