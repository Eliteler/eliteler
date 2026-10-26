{{-- Botanical Parlour — 3D Floating Botanical Elements Canvas --}}
<div class="botanical-3d-bg fixed inset-0 w-screen h-screen pointer-events-none z-0 overflow-hidden" aria-hidden="true">
 
    {{-- SVG Gradients & Soft Glow Filters --}}
    <svg class="absolute w-0 h-0" aria-hidden="true" focusable="false">
        <defs>
            <linearGradient id="sageLeafGrad" x1="0%" y1="0%" x2="100%" y2="100%"> 
                <stop offset="0%" stop-color="#F5F2EB" />
                <stop offset="55%" stop-color="#9CAF88" />
                <stop offset="100%" stop-color="#5F6F52" />
            </linearGradient>
            <linearGradient id="blushPetalGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                <stop offset="0%" stop-color="#FEFBF6" />
                <stop offset="60%" stop-color="#E29A88" />
                <stop offset="100%" stop-color="#C97B65" />
            </linearGradient>
            <filter id="botanicalGlow">
                <feDropShadow dx="0" dy="8" stdDeviation="10" flood-color="#5F6F52"
                    flood-opacity="0.16" />
            </filter>
        </defs>
    </svg>

    {{-- Ambient Light Aura --}}
    <div class="botanical-light-aura"></div>

    {{-- 1. Floating Eucalyptus Sprig (Top Left) --}}
    <div class="botanical-3d-item botanical-pos-1">
        <svg viewBox="0 0 100 100" class="botanical-3d-svg" filter="url(#botanicalGlow)">
            <g fill="url(#sageLeafGrad)">
                <line x1="50" y1="10" x2="50" y2="90" stroke="url(#sageLeafGrad)"
                    stroke-width="2" />
                <ellipse cx="38" cy="28" rx="12" ry="7" transform="rotate(-35 38 28)" />
                <ellipse cx="62" cy="28" rx="12" ry="7" transform="rotate(35 62 28)" />
                <ellipse cx="34" cy="48" rx="13" ry="7" transform="rotate(-30 34 48)" />
                <ellipse cx="66" cy="48" rx="13" ry="7" transform="rotate(30 66 48)" />
                <ellipse cx="38" cy="68" rx="12" ry="6.5" transform="rotate(-25 38 68)" />
                <ellipse cx="62" cy="68" rx="12" ry="6.5" transform="rotate(25 62 68)" />
            </g>
        </svg>
    </div>

    {{-- 2. Floating Single Blossom (Top Right) --}}
    <div class="botanical-3d-item botanical-pos-2">
        <svg viewBox="0 0 100 100" class="botanical-3d-svg" filter="url(#botanicalGlow)">
            <g fill="url(#blushPetalGrad)">
                <ellipse cx="50" cy="34" rx="12" ry="18" />
                <ellipse cx="50" cy="34" rx="12" ry="18" transform="rotate(72 50 50)" />
                <ellipse cx="50" cy="34" rx="12" ry="18" transform="rotate(144 50 50)" />
                <ellipse cx="50" cy="34" rx="12" ry="18" transform="rotate(216 50 50)" />
                <ellipse cx="50" cy="34" rx="12" ry="18"
                    transform="rotate(288 50 50)" />
                <circle cx="50" cy="50" r="8" fill="#FEFBF6" opacity="0.85" />
            </g>
        </svg>
    </div>

    {{-- 3. Floating Fern Frond (Middle Left) --}}
    <div class="botanical-3d-item botanical-pos-3">
        <svg viewBox="0 0 100 100" class="botanical-3d-svg" filter="url(#botanicalGlow)">
            <g fill="url(#sageLeafGrad)">
                <path d="M50,8 C48,35 48,65 50,92" stroke="url(#sageLeafGrad)" stroke-width="2.5" fill="none" />
                <path d="M50,18 L30,10 M50,18 L70,10" stroke="url(#sageLeafGrad)" stroke-width="2" />
                <path d="M50,30 L26,24 M50,30 L74,24" stroke="url(#sageLeafGrad)" stroke-width="2.2" />
                <path d="M50,44 L24,40 M50,44 L76,40" stroke="url(#sageLeafGrad)" stroke-width="2.4" />
                <path d="M50,58 L26,56 M50,58 L74,56" stroke="url(#sageLeafGrad)" stroke-width="2.2" />
                <path d="M50,72 L30,72 M50,72 L70,72" stroke="url(#sageLeafGrad)" stroke-width="2" />
                <path d="M50,84 L36,86 M50,84 L64,86" stroke="url(#sageLeafGrad)" stroke-width="1.6" />
            </g>
        </svg>
    </div>

    {{-- 4. Floating Water Droplet / Dew (Middle Right) --}}
    <div class="botanical-3d-item botanical-pos-4">
        <svg viewBox="0 0 100 100" class="botanical-3d-svg" filter="url(#botanicalGlow)">
            <g fill="url(#blushPetalGrad)">
                <path d="M50,15 C62,38 74,52 74,68 C74,84 63,92 50,92 C37,92 26,84 26,68 C26,52 38,38 50,15 Z"
                    opacity="0.85" />
                <ellipse cx="42" cy="62" rx="6" ry="9" fill="#FEFBF6"
                    opacity="0.6" />
            </g>
        </svg>
    </div>

    {{-- 5. Floating Botanical Sprig with Berries (Bottom Left) --}}
    <div class="botanical-3d-item botanical-pos-5">
        <svg viewBox="0 0 100 100" class="botanical-3d-svg" filter="url(#botanicalGlow)">
            <g>
                <path d="M20,80 Q50,60 80,25" stroke="url(#sageLeafGrad)" stroke-width="2.5" fill="none" />
                <ellipse cx="38" cy="66" rx="10" ry="5.5" transform="rotate(-30 38 66)"
                    fill="url(#sageLeafGrad)" />
                <ellipse cx="55" cy="50" rx="10" ry="5.5" transform="rotate(-30 55 50)"
                    fill="url(#sageLeafGrad)" />
                <ellipse cx="70" cy="36" rx="9" ry="5" transform="rotate(-30 70 36)"
                    fill="url(#sageLeafGrad)" />
                <circle cx="30" cy="76" r="4.5" fill="url(#blushPetalGrad)" />
                <circle cx="40" cy="82" r="4" fill="url(#blushPetalGrad)" />
                <circle cx="24" cy="86" r="3.5" fill="url(#blushPetalGrad)" />
            </g>
        </svg>
    </div>

    {{-- 6. Floating Lotus Bloom (Bottom Right) --}}
    <div class="botanical-3d-item botanical-pos-6">
        <svg viewBox="0 0 100 100" class="botanical-3d-svg" filter="url(#botanicalGlow)">
            <g fill="url(#blushPetalGrad)">
                <ellipse cx="50" cy="70" rx="16" ry="10" opacity="0.5"
                    fill="url(#sageLeafGrad)" />
                <path d="M50,88 C40,70 38,50 50,32 C62,50 60,70 50,88 Z" />
                <path d="M50,88 C34,74 28,56 34,38 C50,50 54,68 50,88 Z" opacity="0.85" />
                <path d="M50,88 C66,74 72,56 66,38 C50,50 46,68 50,88 Z" opacity="0.85" />
            </g>
        </svg>
    </div>

    {{-- 7. Floating Palm Leaf (Center Left, upper-mid) --}}
    <div class="botanical-3d-item botanical-pos-7">
        <svg viewBox="0 0 100 100" class="botanical-3d-svg" filter="url(#botanicalGlow)">
            <g fill="url(#sageLeafGrad)">
                <path d="M50,90 C50,60 50,30 50,10 C70,20 82,40 78,58 C74,76 62,86 50,90 Z" />
                <path d="M50,90 C50,60 50,30 50,10 C30,20 18,40 22,58 C26,76 38,86 50,90 Z" opacity="0.85" />
                <line x1="50" y1="15" x2="50" y2="88" stroke="#FEFBF6"
                    stroke-width="1.5" opacity="0.5" />
            </g>
        </svg>
    </div>

    {{-- 8. Floating Small Orchid Petal Cluster (Center Right, upper-mid) --}}
    <div class="botanical-3d-item botanical-pos-8">
        <svg viewBox="0 0 100 100" class="botanical-3d-svg" filter="url(#botanicalGlow)">
            <g fill="url(#blushPetalGrad)">
                <ellipse cx="35" cy="40" rx="14" ry="9"
                    transform="rotate(-20 35 40)" />
                <ellipse cx="65" cy="40" rx="14" ry="9"
                    transform="rotate(20 65 40)" />
                <ellipse cx="50" cy="60" rx="10" ry="14" />
                <circle cx="50" cy="45" r="6" fill="#FEFBF6" opacity="0.8" />
            </g>
        </svg>
    </div>

    {{-- 9. Floating Vine Curl with Leaves (Center Left, lower-mid) --}}
    <div class="botanical-3d-item botanical-pos-9">
        <svg viewBox="0 0 100 100" class="botanical-3d-svg" filter="url(#botanicalGlow)">
            <g fill="none" stroke="url(#sageLeafGrad)">
                <path d="M20,20 C60,20 20,50 55,55 C90,60 55,85 80,85" stroke-width="2.5" />
                <ellipse cx="35" cy="22" rx="9" ry="5" transform="rotate(10 35 22)"
                    fill="url(#sageLeafGrad)" stroke="none" />
                <ellipse cx="60" cy="48" rx="9" ry="5" transform="rotate(-15 60 48)"
                    fill="url(#sageLeafGrad)" stroke="none" />
                <ellipse cx="68" cy="82" rx="9" ry="5" transform="rotate(10 68 82)"
                    fill="url(#sageLeafGrad)" stroke="none" />
            </g>
        </svg>
    </div>

    {{-- 10. Floating Seed Pod Duo (Center Right, lower-mid) --}}
    <div class="botanical-3d-item botanical-pos-10">
        <svg viewBox="0 0 100 100" class="botanical-3d-svg" filter="url(#botanicalGlow)">
            <g fill="url(#sageLeafGrad)">
                <path d="M38,20 C50,20 52,45 46,68 C42,82 34,84 30,72 C26,54 30,20 38,20 Z" />
                <path d="M64,32 C74,32 76,52 71,70 C68,80 61,82 58,72 C55,58 58,32 64,32 Z" opacity="0.85" />
                <line x1="38" y1="18" x2="38" y2="8" stroke="url(#sageLeafGrad)"
                    stroke-width="2" />
                <line x1="64" y1="30" x2="64" y2="20" stroke="url(#sageLeafGrad)"
                    stroke-width="2" />
            </g>
        </svg>
    </div>

    {{-- 11. Floating Dew Drop Trio (Far Top Center) --}}
    <div class="botanical-3d-item botanical-pos-11">
        <svg viewBox="0 0 100 100" class="botanical-3d-svg" filter="url(#botanicalGlow)">
            <g fill="url(#blushPetalGrad)">
                <path d="M30,30 C36,42 42,50 42,58 C42,66 36,70 30,70 C24,70 18,66 18,58 C18,50 24,42 30,30 Z"
                    opacity="0.9" />
                <path d="M62,45 C68,57 74,65 74,73 C74,81 68,85 62,85 C56,85 50,81 50,73 C50,65 56,57 62,45 Z"
                    opacity="0.7" />
                <path d="M46,15 C50,23 54,29 54,34 C54,39 50,42 46,42 C42,42 38,39 38,34 C38,29 42,23 46,15 Z"
                    opacity="0.6" />
            </g>
        </svg>
    </div>

    {{-- 12. Floating Small Leaf Cluster (Far Bottom Center) --}}
    <div class="botanical-3d-item botanical-pos-12">
        <svg viewBox="0 0 100 100" class="botanical-3d-svg" filter="url(#botanicalGlow)">
            <g fill="url(#sageLeafGrad)">
                <ellipse cx="34" cy="50" rx="16" ry="9"
                    transform="rotate(-15 34 50)" />
                <ellipse cx="66" cy="50" rx="16" ry="9"
                    transform="rotate(15 66 50)" />
                <ellipse cx="50" cy="30" rx="14" ry="8" transform="rotate(0 50 30)" />
                <circle cx="50" cy="50" r="5" fill="#FEFBF6" opacity="0.75" />
            </g>
        </svg>
    </div>

</div>
