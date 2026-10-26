{{-- Background Full Width Packed 3D Golf Icons --}}
<div class="vcard-bg-icons hidden lg:flex" aria-hidden="true">
    @php
        $golfIcons = [
            ['type' => 'bag', 'top' => '3%', 'left' => '4%', 'anim' => 'packFloat1', 'dur' => '8s'],
            ['type' => 'cart', 'top' => '6%', 'left' => '90%', 'anim' => 'packFloat2', 'dur' => '9s'],
            ['type' => 'flag', 'top' => '14%', 'left' => '18%', 'anim' => 'packFloat2', 'dur' => '10s'],
            ['type' => 'trophy', 'top' => '18%', 'left' => '78%', 'anim' => 'packFloat1', 'dur' => '8.5s'],
            ['type' => 'cap', 'top' => '28%', 'left' => '8%', 'anim' => 'packFloat1', 'dur' => '9.5s'],
            ['type' => 'driver', 'top' => '32%', 'left' => '85%', 'anim' => 'packFloat2', 'dur' => '8.8s'],
            ['type' => 'ball', 'top' => '44%', 'left' => '14%', 'anim' => 'packFloat2', 'dur' => '10.5s'],
            ['type' => 'glove', 'top' => '48%', 'left' => '82%', 'anim' => 'packFloat1', 'dur' => '9.2s'],
            ['type' => 'cart', 'top' => '58%', 'left' => '6%', 'anim' => 'packFloat1', 'dur' => '8.2s'],
            ['type' => 'bag', 'top' => '62%', 'left' => '88%', 'anim' => 'packFloat2', 'dur' => '9.1s'],
            ['type' => 'flag', 'top' => '72%', 'left' => '20%', 'anim' => 'packFloat2', 'dur' => '10.1s'],
            ['type' => 'trophy', 'top' => '76%', 'left' => '75%', 'anim' => 'packFloat1', 'dur' => '8.7s'],
            ['type' => 'driver', 'top' => '86%', 'left' => '10%', 'anim' => 'packFloat1', 'dur' => '9.3s'],
            ['type' => 'cap', 'top' => '90%', 'left' => '80%', 'anim' => 'packFloat2', 'dur' => '8.9s'],
        ];
    @endphp

    @foreach ($golfIcons as $index => $icon)
        <div class="bg-icon"
            style="top: {{ $icon['top'] }}; left: {{ $icon['left'] }}; animation: {{ $icon['anim'] }} {{ $icon['dur'] }} ease-in-out infinite;">
            @if ($icon['type'] == 'bag')
                <svg width="105" height="125" viewBox="0 0 100 120" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M30 40C30 35 35 30 45 30H55C65 30 70 35 70 40V105C70 112 65 115 50 115C35 115 30 112 30 105V40Z"
                        fill="url(#bagGrad_{{ $index }})" />
                    <path d="M33 60H67V95C67 98 62 102 50 102C38 102 33 98 33 95V60Z"
                        fill="url(#pocketGrad_{{ $index }})" />
                    <path d="M40 30L28 4M50 30L50 1M60 30L72 4" stroke="url(#clubGrad_{{ $index }})"
                        stroke-width="4.5" stroke-linecap="round" />
                    <circle cx="28" cy="4" r="4.5" fill="#FFFFFF" />
                    <circle cx="50" cy="1" r="4.5" fill="#FFFFFF" />
                    <circle cx="72" cy="4" r="4.5" fill="#FFFFFF" />
                    <defs>
                        <linearGradient id="bagGrad_{{ $index }}" x1="20" y1="20" x2="80"
                            y2="120" gradientUnits="userSpaceOnUse">
                            <stop offset="0%" stop-color="#2E7D32" />
                            <stop offset="100%" stop-color="#04150E" />
                        </linearGradient>
                        <linearGradient id="pocketGrad_{{ $index }}" x1="33" y1="60" x2="67"
                            y2="102" gradientUnits="userSpaceOnUse">
                            <stop offset="0%" stop-color="#38A169" />
                            <stop offset="100%" stop-color="#0A2C22" />
                        </linearGradient>
                        <linearGradient id="clubGrad_{{ $index }}" x1="28" y1="4" x2="72"
                            y2="30" gradientUnits="userSpaceOnUse">
                            <stop offset="0%" stop-color="#FFFFFF" />
                            <stop offset="100%" stop-color="#475569" />
                        </linearGradient>
                    </defs>
                </svg>
            @elseif ($icon['type'] == 'cart')
                <svg width="115" height="95" viewBox="0 0 120 100" fill="none"
                    xmlns="http://www.w3.org/2000/svg">
                    <path d="M15 50H70L85 70H100V80H15V50Z" fill="url(#cartGrad_{{ $index }})" />
                    <path d="M40 50H70V30H40V50Z" fill="url(#roofGrad_{{ $index }})" />
                    <line x1="45" y1="30" x2="30" y2="50" stroke="#CBD5E1"
                        stroke-width="4.5" stroke-linecap="round" />
                    <line x1="65" y1="30" x2="80" y2="50" stroke="#CBD5E1"
                        stroke-width="4.5" stroke-linecap="round" />
                    <circle cx="35" cy="85" r="14" fill="#0F172A" stroke="#94A3B8" stroke-width="5" />
                    <circle cx="35" cy="85" r="5" fill="#CBD5E1" />
                    <circle cx="90" cy="85" r="14" fill="#0F172A" stroke="#94A3B8" stroke-width="5" />
                    <circle cx="90" cy="85" r="5" fill="#CBD5E1" />
                    <defs>
                        <linearGradient id="cartGrad_{{ $index }}" x1="15" y1="50" x2="100"
                            y2="80" gradientUnits="userSpaceOnUse">
                            <stop offset="0%" stop-color="#4CAF50" />
                            <stop offset="100%" stop-color="#1B4D3E" />
                        </linearGradient>
                        <linearGradient id="roofGrad_{{ $index }}" x1="40" y1="30"
                            x2="70" y2="50" gradientUnits="userSpaceOnUse">
                            <stop offset="0%" stop-color="#FFFFFF" />
                            <stop offset="100%" stop-color="#94A3B8" />
                        </linearGradient>
                    </defs>
                </svg>
            @elseif ($icon['type'] == 'flag')
                <svg width="100" height="110" viewBox="0 0 100 110" fill="none"
                    xmlns="http://www.w3.org/2000/svg">
                    <path d="M30 105L30 10" stroke="#CBD5E1" stroke-width="5" stroke-linecap="round" />
                    <path d="M32 15L90 38L32 61Z" fill="url(#flagGrad_{{ $index }})" />
                    <ellipse cx="30" cy="105" rx="16" ry="6" fill="#1E293B" />
                    <defs>
                        <linearGradient id="flagGrad_{{ $index }}" x1="32" y1="15"
                            x2="90" y2="38" gradientUnits="userSpaceOnUse">
                            <stop offset="0%" stop-color="#2E7D32" />
                            <stop offset="100%" stop-color="#0A2C22" />
                        </linearGradient>
                    </defs>
                </svg>
            @elseif ($icon['type'] == 'trophy')
                <svg width="105" height="115" viewBox="0 0 100 100" fill="none"
                    xmlns="http://www.w3.org/2000/svg">
                    <path d="M35 75H65L60 90H40L35 75Z" fill="#334155" />
                    <rect x="45" y="55" width="10" height="20" fill="#334155" />
                    <path d="M25 18C25 18 25 55 50 55C75 55 75 18 75 18H25Z"
                        fill="url(#trophyGrad_{{ $index }})" />
                    <path d="M25 25C14 25 14 45 25 45" stroke="url(#trophyGrad_{{ $index }})" stroke-width="5"
                        stroke-linecap="round" />
                    <path d="M75 25C86 25 86 45 75 45" stroke="url(#trophyGrad_{{ $index }})" stroke-width="5"
                        stroke-linecap="round" />
                    <defs>
                        <linearGradient id="trophyGrad_{{ $index }}" x1="25" y1="18"
                            x2="75" y2="55" gradientUnits="userSpaceOnUse">
                            <stop offset="0%" stop-color="#E5C378" />
                            <stop offset="100%" stop-color="#C5A059" />
                        </linearGradient>
                    </defs>
                </svg>
            @elseif ($icon['type'] == 'cap')
                <svg width="110" height="85" viewBox="0 0 110 80" fill="none"
                    xmlns="http://www.w3.org/2000/svg">
                    <path d="M20 40C20 20 40 10 65 10C90 10 100 25 100 40H20Z"
                        fill="url(#capGrad_{{ $index }})" />
                    <path d="M15 45C30 45 70 55 95 45C95 45 70 65 40 65C20 65 15 55 15 45Z"
                        fill="url(#brimGrad_{{ $index }})" />
                    <defs>
                        <linearGradient id="capGrad_{{ $index }}" x1="20" y1="10"
                            x2="100" y2="40" gradientUnits="userSpaceOnUse">
                            <stop offset="0%" stop-color="#2E7D32" />
                            <stop offset="100%" stop-color="#04150E" />
                        </linearGradient>
                        <linearGradient id="brimGrad_{{ $index }}" x1="15" y1="45"
                            x2="95" y2="65" gradientUnits="userSpaceOnUse">
                            <stop offset="0%" stop-color="#1B4D3E" />
                            <stop offset="100%" stop-color="#062318" />
                        </linearGradient>
                    </defs>
                </svg>
            @elseif ($icon['type'] == 'driver')
                <svg width="100" height="120" viewBox="0 0 90 130" fill="none"
                    xmlns="http://www.w3.org/2000/svg">
                    <path d="M60 10L28 112" stroke="url(#shaftGrad_{{ $index }})" stroke-width="5.5"
                        stroke-linecap="round" />
                    <path d="M18 110C12 110 8 116 10 124C12 130 22 125 35 120C50 116 52 110 40 110H18Z"
                        fill="url(#headGrad_{{ $index }})" />
                    <defs>
                        <linearGradient id="shaftGrad_{{ $index }}" x1="60" y1="10"
                            x2="28" y2="112" gradientUnits="userSpaceOnUse">
                            <stop offset="0%" stop-color="#FFFFFF" />
                            <stop offset="100%" stop-color="#475569" />
                        </linearGradient>
                        <linearGradient id="headGrad_{{ $index }}" x1="10" y1="110"
                            x2="52" y2="128" gradientUnits="userSpaceOnUse">
                            <stop offset="0%" stop-color="#475569" />
                            <stop offset="100%" stop-color="#0F172A" />
                        </linearGradient>
                    </defs>
                </svg>
            @elseif ($icon['type'] == 'ball')
                <svg width="75" height="75" viewBox="0 0 100 100" fill="none"
                    xmlns="http://www.w3.org/2000/svg">
                    <circle cx="50" cy="50" r="45" fill="url(#ballGrad_{{ $index }})" />
                    <defs>
                        <radialGradient id="ballGrad_{{ $index }}" cx="30%" cy="30%" r="70%">
                            <stop offset="0%" stop-color="#FFFFFF" />
                            <stop offset="100%" stop-color="#64748B" />
                        </radialGradient>
                    </defs>
                </svg>
            @elseif ($icon['type'] == 'glove')
                <svg width="90" height="100" viewBox="0 0 90 100" fill="none"
                    xmlns="http://www.w3.org/2000/svg">
                    <path d="M30 40C30 30 40 25 50 25C60 25 65 35 65 45V80C65 88 55 95 45 95C35 95 30 88 30 80V40Z"
                        fill="url(#gloveGrad_{{ $index }})" />
                    <path d="M35 55H60" stroke="#1B4D3E" stroke-width="3" />
                    <defs>
                        <linearGradient id="gloveGrad_{{ $index }}" x1="30" y1="25"
                            x2="65" y2="95" gradientUnits="userSpaceOnUse">
                            <stop offset="0%" stop-color="#FFFFFF" />
                            <stop offset="100%" stop-color="#CBD5E1" />
                        </linearGradient>
                    </defs>
                </svg>
            @endif
        </div>
    @endforeach
</div>
