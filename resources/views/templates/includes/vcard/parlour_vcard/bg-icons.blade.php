{{-- Women's Salon vCard — 3D Floating Women's Salon & Beauty Items Canvas --}}
<div class="salon-3d-bg fixed inset-0 w-screen h-screen pointer-events-none z-0 overflow-hidden" aria-hidden="true">
 
    {{-- SVG Gradients & Glow Filters --}}
    <svg class="absolute w-0 h-0" aria-hidden="true" focusable="false">
        <defs>
            <linearGradient id="roseGoldGrad" x1="0%" y1="0%" x2="100%" y2="100%"> 
                <stop offset="0%" stop-color="#F7CAD0" />
                <stop offset="50%" stop-color="#E29A88" />
                <stop offset="100%" stop-color="#721B29" />
            </linearGradient>
            <filter id="softRoseGlow">
                <feDropShadow dx="0" dy="10" stdDeviation="12" flood-color="#721B29"
                    flood-opacity="0.18" />
            </filter>
        </defs>
    </svg>

    {{-- Ambient Light Aura --}}
    <div class="salon-light-aura"></div>

    {{-- 1. Floating Hairdryer / Blow Dryer (Top Left) --}}
    <div class="salon-3d-item salon-pos-1">
        <svg viewBox="0 0 100 100" class="salon-3d-svg" filter="url(#softRoseGlow)">
            <g fill="url(#roseGoldGrad)">
                <path
                    d="M20,30 L55,30 C62,30 68,36 68,43 C68,50 62,56 55,56 L45,56 L48,82 C48,85 45,88 42,88 L36,88 C33,88 31,85 31,82 L33,56 L20,56 Z" />
                <rect x="68" y="38" width="12" height="10" rx="3" />
                <circle cx="20" cy="43" r="10" fill="#721B29" opacity="0.3" />
            </g>
        </svg>
    </div>

    {{-- 2. Floating 3D Styling Shears (Top Right) --}}
    <div class="salon-3d-item salon-pos-2">
        <svg viewBox="0 0 100 100" class="salon-3d-svg" filter="url(#softRoseGlow)">
            <g fill="url(#roseGoldGrad)">
                <path class="salon-blade-left"
                    d="M30 80 C 20 80, 15 70, 25 60 C 35 50, 48 48, 50 45 L 75 15 C 77 12, 80 15, 78 18 L 52 48 Z M 25 75 C 20 75, 20 65, 25 65 C 30 65, 30 75, 25 75 Z" />
                <path class="salon-blade-right"
                    d="M70 80 C 80 80, 85 70, 75 60 C 65 50, 52 48, 50 45 L 25 15 C 23 12, 20 15, 22 18 L 48 48 Z M 75 75 C 80 75, 80 65, 75 65 C 70 65, 70 75, 75 75 Z" />
                <circle cx="50" cy="46" r="3.5" fill="#fdf7f4" stroke="#e29a88" stroke-width="1.5" />
            </g>
        </svg>
    </div>

    {{-- 3. Floating Lipstick / Beauty Wand (Middle Left) --}}
    <div class="salon-3d-item salon-pos-3">
        <svg viewBox="0 0 100 100" class="salon-3d-svg" filter="url(#softRoseGlow)">
            <g fill="url(#roseGoldGrad)">
                <rect x="38" y="55" width="24" height="35" rx="3" />
                <rect x="41" y="42" width="18" height="13" rx="1" fill="#fdf7f4" opacity="0.8" />
                <path d="M43,42 L43,22 C43,18 57,12 57,18 L57,42 Z" />
            </g>
        </svg>
    </div>

    {{-- 4. Floating Luxury Perfume Bottle (Middle Right) --}}
    <div class="salon-3d-item salon-pos-4">
        <svg viewBox="0 0 100 100" class="salon-3d-svg" filter="url(#softRoseGlow)">
            <g fill="url(#roseGoldGrad)">
                <rect x="30" y="40" width="40" height="48" rx="8" />
                <rect x="42" y="28" width="16" height="12" rx="2" />
                <rect x="38" y="18" width="24" height="10" rx="3" fill="#fdf7f4" opacity="0.9" />
                <path d="M30,55 L70,55" stroke="#ffffff" stroke-width="2" opacity="0.6" />
            </g>
        </svg>
    </div>

    {{-- 5. Floating Styling Hairbrush (Bottom Left) --}}
    <div class="salon-3d-item salon-pos-5">
        <svg viewBox="0 0 100 100" class="salon-3d-svg" filter="url(#softRoseGlow)">
            <g fill="url(#roseGoldGrad)">
                <rect x="25" y="20" width="50" height="35" rx="12" />
                <path d="M46,55 L46,88 C46,91 54,91 54,88 L54,55 Z" />
                <!-- Bristles -->
                <line x1="32" y1="20" x2="32" y2="12" stroke="#721B29" stroke-width="2" />
                <line x1="41" y1="20" x2="41" y2="12" stroke="#721B29" stroke-width="2" />
                <line x1="50" y1="20" x2="50" y2="12" stroke="#721B29"
                    stroke-width="2" />
                <line x1="59" y1="20" x2="59" y2="12" stroke="#721B29"
                    stroke-width="2" />
                <line x1="68" y1="20" x2="68" y2="12" stroke="#721B29"
                    stroke-width="2" />
            </g>
        </svg>
    </div>

    {{-- 6. Floating Hand Mirror (Bottom Right) --}}
    <div class="salon-3d-item salon-pos-6">
        <svg viewBox="0 0 100 100" class="salon-3d-svg" filter="url(#softRoseGlow)">
            <g fill="url(#roseGoldGrad)">
                <circle cx="50" cy="38" r="22" stroke="#e29a88" stroke-width="3" fill="none" />
                <circle cx="50" cy="38" r="18" fill="#ffffff" opacity="0.7" />
                <path d="M47 60 L 53 60 L 51 90 C 51 93, 49 93, 49 90 Z" />
            </g>
        </svg>
    </div>

</div>
