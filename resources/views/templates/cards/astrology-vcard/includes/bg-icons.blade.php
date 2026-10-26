{{-- Background Full Width Floating 3D Celestial & Zodiac Icons --}}
<div class="vcard-bg-icons hidden lg:flex" aria-hidden="true">
    @php
        $celestialIcons = [
            ['type' => 'moon', 'top' => '3%', 'left' => '4%', 'anim' => 'packFloat1', 'dur' => '8.5s'],
            ['type' => 'sun', 'top' => '6%', 'left' => '88%', 'anim' => 'packFloat2', 'dur' => '9.5s'],
            ['type' => 'tarot', 'top' => '15%', 'left' => '16%', 'anim' => 'packFloat2', 'dur' => '10s'],
            ['type' => 'planet', 'top' => '19%', 'left' => '78%', 'anim' => 'packFloat1', 'dur' => '8.8s'],
            ['type' => 'star', 'top' => '28%', 'left' => '8%', 'anim' => 'packFloat1', 'dur' => '9.2s'],
            ['type' => 'crystal', 'top' => '33%', 'left' => '85%', 'anim' => 'packFloat2', 'dur' => '9.8s'],
            ['type' => 'astrolabe', 'top' => '44%', 'left' => '12%', 'anim' => 'packFloat2', 'dur' => '11s'],
            ['type' => 'moon', 'top' => '49%', 'left' => '82%', 'anim' => 'packFloat1', 'dur' => '9.4s'],
            ['type' => 'planet', 'top' => '58%', 'left' => '6%', 'anim' => 'packFloat1', 'dur' => '8.6s'],
            ['type' => 'tarot', 'top' => '63%', 'left' => '87%', 'anim' => 'packFloat2', 'dur' => '9.3s'],
            ['type' => 'sun', 'top' => '72%', 'left' => '18%', 'anim' => 'packFloat2', 'dur' => '10.2s'],
            ['type' => 'crystal', 'top' => '77%', 'left' => '76%', 'anim' => 'packFloat1', 'dur' => '8.9s'],
            ['type' => 'star', 'top' => '86%', 'left' => '9%', 'anim' => 'packFloat1', 'dur' => '9.5s'],
            ['type' => 'astrolabe', 'top' => '90%', 'left' => '80%', 'anim' => 'packFloat2', 'dur' => '9.1s'],
        ];
    @endphp

    @foreach ($celestialIcons as $index => $icon)
        <div class="bg-icon"
            style="top: {{ $icon['top'] }}; left: {{ $icon['left'] }}; animation: {{ $icon['anim'] }} {{ $icon['dur'] }} ease-in-out infinite;">

            {{-- 1. Crescent Moon --}}
            @if ($icon['type'] == 'moon')
                <svg width="105" height="115" viewBox="0 0 100 110" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M72 15C52 18 36 34 36 56C36 78 52 94 72 97C40 98 15 74 15 46C15 24 32 15 72 15Z"
                        fill="url(#moonGrad_{{ $index }})"
                        filter="drop-shadow(0 8px 16px rgba(224, 169, 109, 0.25))" />
                    <path d="M68 38L71 46L79 49L71 52L68 60L65 52L57 49L65 46L68 38Z" fill="#FFD700" opacity="0.85" />
                    <circle cx="82" cy="28" r="2.5" fill="#E0E1DD" />
                    <circle cx="78" cy="74" r="2" fill="#E0E1DD" />
                    <defs>
                        <linearGradient id="moonGrad_{{ $index }}" x1="15" y1="15" x2="75"
                            y2="95" gradientUnits="userSpaceOnUse">
                            <stop offset="0%" stop-color="#FFD700" />
                            <stop offset="50%" stop-color="#E0A96D" />
                            <stop offset="100%" stop-color="#240046" />
                        </linearGradient>
                    </defs>
                </svg>

                {{-- 2. Radiant Sun --}}
            @elseif ($icon['type'] == 'sun')
                <svg width="115" height="115" viewBox="0 0 110 110" fill="none"
                    xmlns="http://www.w3.org/2000/svg">
                    <circle cx="55" cy="55" r="24" fill="url(#sunCore_{{ $index }})"
                        filter="drop-shadow(0 0 12px rgba(255, 215, 0, 0.4))" />
                    <circle cx="55" cy="55" r="30" stroke="url(#sunRing_{{ $index }})"
                        stroke-width="1.5" stroke-dasharray="3 3" />
                    {{-- Sun Rays --}}
                    @for ($r = 0; $r < 8; $r++)
                        <line x1="55" y1="14" x2="55" y2="24" stroke="#E0A96D"
                            stroke-width="3" stroke-linecap="round" transform="rotate({{ $r * 45 }} 55 55)" />
                    @endfor
                    <defs>
                        <linearGradient id="sunCore_{{ $index }}" x1="35" y1="35" x2="75"
                            y2="75" gradientUnits="userSpaceOnUse">
                            <stop offset="0%" stop-color="#FFF3B0" />
                            <stop offset="45%" stop-color="#FFD700" />
                            <stop offset="100%" stop-color="#B38048" />
                        </linearGradient>
                        <linearGradient id="sunRing_{{ $index }}" x1="25" y1="25" x2="85"
                            y2="85" gradientUnits="userSpaceOnUse">
                            <stop offset="0%" stop-color="#FFD700" />
                            <stop offset="100%" stop-color="#3C096C" />
                        </linearGradient>
                    </defs>
                </svg>

                {{-- 3. Tarot Card --}}
            @elseif ($icon['type'] == 'tarot')
                <svg width="95" height="130" viewBox="0 0 90 120" fill="none"
                    xmlns="http://www.w3.org/2000/svg">
                    <rect x="10" y="8" width="70" height="104" rx="8"
                        fill="url(#tarotCard_{{ $index }})" stroke="url(#tarotBorder_{{ $index }})"
                        stroke-width="2" />
                    <rect x="16" y="14" width="58" height="92" rx="5" fill="none" stroke="#E0A96D"
                        stroke-width="1" opacity="0.6" />
                    {{-- Central Star Motif --}}
                    <path d="M45 42L48 55L61 58L48 61L45 74L42 61L29 58L42 55L45 42Z" fill="#FFD700" />
                    <circle cx="45" cy="58" r="3" fill="#0B132B" />
                    <circle cx="45" cy="26" r="3" fill="#E0A96D" />
                    <circle cx="45" cy="90" r="3" fill="#E0A96D" />
                    <defs>
                        <linearGradient id="tarotCard_{{ $index }}" x1="10" y1="8"
                            x2="80" y2="112" gradientUnits="userSpaceOnUse">
                            <stop offset="0%" stop-color="#1C2541" />
                            <stop offset="70%" stop-color="#0B132B" />
                            <stop offset="100%" stop-color="#240046" />
                        </linearGradient>
                        <linearGradient id="tarotBorder_{{ $index }}" x1="10" y1="8"
                            x2="80" y2="112" gradientUnits="userSpaceOnUse">
                            <stop offset="0%" stop-color="#FFD700" />
                            <stop offset="100%" stop-color="#B38048" />
                        </linearGradient>
                    </defs>
                </svg>

                {{-- 4. Ringed Planet (Saturn) --}}
            @elseif ($icon['type'] == 'planet')
                <svg width="125" height="95" viewBox="0 0 120 90" fill="none"
                    xmlns="http://www.w3.org/2000/svg">
                    <ellipse cx="60" cy="45" rx="52" ry="18" fill="none"
                        stroke="url(#saturnRing_{{ $index }})" stroke-width="4.5"
                        transform="rotate(-20 60 45)" />
                    <circle cx="60" cy="45" r="25" fill="url(#saturnBody_{{ $index }})" />
                    <path d="M35 45A25 25 0 0 0 85 45" stroke="#E0A96D" stroke-width="1.5" opacity="0.5" />
                    <defs>
                        <linearGradient id="saturnBody_{{ $index }}" x1="40" y1="25"
                            x2="80" y2="65" gradientUnits="userSpaceOnUse">
                            <stop offset="0%" stop-color="#3C096C" />
                            <stop offset="60%" stop-color="#1C2541" />
                            <stop offset="100%" stop-color="#0B132B" />
                        </linearGradient>
                        <linearGradient id="saturnRing_{{ $index }}" x1="15" y1="45"
                            x2="105" y2="45" gradientUnits="userSpaceOnUse">
                            <stop offset="0%" stop-color="#FFD700" />
                            <stop offset="50%" stop-color="#E0A96D" />
                            <stop offset="100%" stop-color="#7B2CBF" />
                        </linearGradient>
                    </defs>
                </svg>

                {{-- 5. Crystal Ball --}}
            @elseif ($icon['type'] == 'crystal')
                <svg width="105" height="115" viewBox="0 0 100 110" fill="none"
                    xmlns="http://www.w3.org/2000/svg">
                    {{-- Pedestal --}}
                    <path d="M30 92H70L62 76H38L30 92Z" fill="url(#pedestalGrad_{{ $index }})" />
                    <rect x="25" y="92" width="50" height="7" rx="3.5" fill="#B38048" />
                    {{-- Crystal Orb --}}
                    <circle cx="50" cy="48" r="34" fill="url(#orbGrad_{{ $index }})"
                        stroke="url(#orbBorder_{{ $index }})" stroke-width="1.5" />
                    <ellipse cx="40" cy="34" rx="10" ry="6" fill="#FFFFFF"
                        opacity="0.45" transform="rotate(-30 40 34)" />
                    <defs>
                        <radialGradient id="orbGrad_{{ $index }}" cx="38%" cy="36%" r="65%">
                            <stop offset="0%" stop-color="#FAFBFD" />
                            <stop offset="40%" stop-color="#7B2CBF" />
                            <stop offset="85%" stop-color="#240046" />
                            <stop offset="100%" stop-color="#0B132B" />
                        </radialGradient>
                        <linearGradient id="orbBorder_{{ $index }}" x1="20" y1="20"
                            x2="80" y2="80" gradientUnits="userSpaceOnUse">
                            <stop offset="0%" stop-color="#FFD700" />
                            <stop offset="100%" stop-color="#E0A96D" />
                        </linearGradient>
                        <linearGradient id="pedestalGrad_{{ $index }}" x1="30" y1="76"
                            x2="70" y2="92" gradientUnits="userSpaceOnUse">
                            <stop offset="0%" stop-color="#FFD700" />
                            <stop offset="100%" stop-color="#805423" />
                        </linearGradient>
                    </defs>
                </svg>

                {{-- 6. 8-Pointed Celestial Star (Polaris) --}}
            @elseif ($icon['type'] == 'star')
                <svg width="85" height="85" viewBox="0 0 90 90" fill="none"
                    xmlns="http://www.w3.org/2000/svg">
                    <path d="M45 5L51 34L80 40L51 46L45 75L39 46L10 40L39 34L45 5Z"
                        fill="url(#starGrad_{{ $index }})"
                        filter="drop-shadow(0 0 10px rgba(255, 215, 0, 0.45))" />
                    <path d="M45 20L48 38L66 42L48 46L45 64L42 46L24 42L42 38L45 20Z" fill="#FFFFFF" opacity="0.75" />
                    <circle cx="45" cy="42" r="3.5" fill="#0B132B" />
                    <defs>
                        <linearGradient id="starGrad_{{ $index }}" x1="10" y1="5"
                            x2="80" y2="75" gradientUnits="userSpaceOnUse">
                            <stop offset="0%" stop-color="#FFF3B0" />
                            <stop offset="40%" stop-color="#FFD700" />
                            <stop offset="100%" stop-color="#B38048" />
                        </linearGradient>
                    </defs>
                </svg>

                {{-- 7. Astrolabe / Zodiac Wheel --}}
            @elseif ($icon['type'] == 'astrolabe')
                <svg width="115" height="115" viewBox="0 0 110 110" fill="none"
                    xmlns="http://www.w3.org/2000/svg">
                    <circle cx="55" cy="55" r="46" stroke="url(#astroRing_{{ $index }})"
                        stroke-width="2.5" />
                    <circle cx="55" cy="55" r="36" stroke="#E0A96D" stroke-width="1.5"
                        stroke-dasharray="4 4" opacity="0.7" />
                    <circle cx="55" cy="55" r="18" fill="url(#astroCenter_{{ $index }})" />
                    {{-- 12 Zodiac Axis Lines --}}
                    @for ($a = 0; $a < 6; $a++)
                        <line x1="19" y1="55" x2="91" y2="55" stroke="#E0A96D"
                            stroke-width="1" opacity="0.4" transform="rotate({{ $a * 30 }} 55 55)" />
                    @endfor
                    <circle cx="55" cy="55" r="4" fill="#FFD700" />
                    <defs>
                        <linearGradient id="astroRing_{{ $index }}" x1="10" y1="10"
                            x2="100" y2="100" gradientUnits="userSpaceOnUse">
                            <stop offset="0%" stop-color="#FFD700" />
                            <stop offset="50%" stop-color="#E0A96D" />
                            <stop offset="100%" stop-color="#3C096C" />
                        </linearGradient>
                        <radialGradient id="astroCenter_{{ $index }}" cx="50%" cy="50%" r="50%">
                            <stop offset="0%" stop-color="#3C096C" />
                            <stop offset="100%" stop-color="#0B132B" />
                        </radialGradient>
                    </defs>
                </svg>
            @endif

        </div>
    @endforeach
</div>
