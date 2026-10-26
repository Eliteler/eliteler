{{-- ============================================================
     ELECTRICIAN VCARD — POWER GRID BACKGROUND
============================================================ --}}

{{-- ── Power Grid Layer ── --}}
<div class="vcard-bg-grid hidden lg:flex" aria-hidden="true">

    {{-- Glow halos behind towers --}}
    <div class="vcard-bg-halos">
        <div class="halo halo-1"></div>
        <div class="halo halo-2"></div>
        <div class="halo halo-3"></div>
        <div class="halo halo-4"></div>
        <div class="halo halo-5"></div>
        <div class="halo halo-6"></div>
    </div>

    {{-- SVG — towers + transmission lines ── --}}
    <svg class="vcard-bg-svg" viewBox="0 0 100 100" preserveAspectRatio="xMidYMid slice"
        xmlns="http://www.w3.org/2000/svg">

        {{-- ===== TOWER 1 — left side, upper ===== --}}
        {{-- Main mast --}}
        <line class="grid-tower" x1="8" y1="28" x2="8" y2="95" />
        {{-- Cross-arms --}}
        <line class="grid-tower" x1="4" y1="33" x2="12" y2="33" />
        <line class="grid-tower" x1="4.5" y1="38" x2="11.5" y2="38" />
        {{-- Diagonal braces --}}
        <line class="grid-tower" x1="8" y1="28" x2="4" y2="33" />
        <line class="grid-tower" x1="8" y1="28" x2="12" y2="33" />
        <line class="grid-tower" x1="4" y1="33" x2="6" y2="60" />
        <line class="grid-tower" x1="12" y1="33" x2="10" y2="60" />
        <line class="grid-tower" x1="6" y1="60" x2="5" y2="95" />
        <line class="grid-tower" x1="10" y1="60" x2="11" y2="95" />
        {{-- Horizontal brace mid --}}
        <line class="grid-tower" x1="5.5" y1="60" x2="10.5" y2="60" />
        <line class="grid-tower" x1="5.8" y1="75" x2="10.2" y2="75" />
        {{-- Insulator nodes at crossarm tips --}}
        <circle class="grid-node" cx="4" cy="33" r="0.6" opacity="0.7" />
        <circle class="grid-node" cx="12" cy="33" r="0.6" opacity="0.7" />
        <circle class="grid-node" cx="4.5" cy="38" r="0.5" opacity="0.7" />
        <circle class="grid-node" cx="11.5" cy="38" r="0.5" opacity="0.7" />
        {{-- Top peak light placeholder --}}
        <circle class="grid-node" cx="8" cy="28" r="0.8" opacity="0.9" />

        {{-- ===== TOWER 2 — centre, lower ===== --}}
        <line class="grid-tower" x1="32" y1="55" x2="32" y2="100" />
        <line class="grid-tower" x1="27" y1="60" x2="37" y2="60" />
        <line class="grid-tower" x1="27.5" y1="65" x2="36.5" y2="65" />
        <line class="grid-tower" x1="32" y1="55" x2="27" y2="60" />
        <line class="grid-tower" x1="32" y1="55" x2="37" y2="60" />
        <line class="grid-tower" x1="27" y1="60" x2="29" y2="82" />
        <line class="grid-tower" x1="37" y1="60" x2="35" y2="82" />
        <line class="grid-tower" x1="29" y1="82" x2="28.5" y2="100" />
        <line class="grid-tower" x1="35" y1="82" x2="35.5" y2="100" />
        <line class="grid-tower" x1="29" y1="82" x2="35" y2="82" />
        <circle class="grid-node" cx="27" cy="60" r="0.6" opacity="0.7" />
        <circle class="grid-node" cx="37" cy="60" r="0.6" opacity="0.7" />
        <circle class="grid-node" cx="27.5" cy="65" r="0.5" opacity="0.7" />
        <circle class="grid-node" cx="36.5" cy="65" r="0.5" opacity="0.7" />
        <circle class="grid-node" cx="32" cy="55" r="0.8" opacity="0.9" />

        {{-- ===== TOWER 3 — right side, upper ===== --}}
        <line class="grid-tower" x1="58" y1="28" x2="58" y2="95" />
        <line class="grid-tower" x1="54" y1="33" x2="62" y2="33" />
        <line class="grid-tower" x1="54.5" y1="38" x2="61.5" y2="38" />
        <line class="grid-tower" x1="58" y1="28" x2="54" y2="33" />
        <line class="grid-tower" x1="58" y1="28" x2="62" y2="33" />
        <line class="grid-tower" x1="54" y1="33" x2="56" y2="60" />
        <line class="grid-tower" x1="62" y1="33" x2="60" y2="60" />
        <line class="grid-tower" x1="56" y1="60" x2="55" y2="95" />
        <line class="grid-tower" x1="60" y1="60" x2="61" y2="95" />
        <line class="grid-tower" x1="55.5" y1="60" x2="60.5" y2="60" />
        <line class="grid-tower" x1="55.8" y1="75" x2="60.2" y2="75" />
        <circle class="grid-node" cx="54" cy="33" r="0.6" opacity="0.7" />
        <circle class="grid-node" cx="62" cy="33" r="0.6" opacity="0.7" />
        <circle class="grid-node" cx="54.5" cy="38" r="0.5" opacity="0.7" />
        <circle class="grid-node" cx="61.5" cy="38" r="0.5" opacity="0.7" />
        <circle class="grid-node" cx="58" cy="28" r="0.8" opacity="0.9" />

        {{-- ===== TOWER 4 — far right, lower ===== --}}
        <line class="grid-tower" x1="82" y1="55" x2="82" y2="100" />
        <line class="grid-tower" x1="77" y1="60" x2="87" y2="60" />
        <line class="grid-tower" x1="77.5" y1="65" x2="86.5" y2="65" />
        <line class="grid-tower" x1="82" y1="55" x2="77" y2="60" />
        <line class="grid-tower" x1="82" y1="55" x2="87" y2="60" />
        <line class="grid-tower" x1="77" y1="60" x2="79" y2="82" />
        <line class="grid-tower" x1="87" y1="60" x2="85" y2="82" />
        <line class="grid-tower" x1="79" y1="82" x2="78.5" y2="100" />
        <line class="grid-tower" x1="85" y1="82" x2="85.5" y2="100" />
        <line class="grid-tower" x1="79" y1="82" x2="85" y2="82" />
        <circle class="grid-node" cx="77" cy="60" r="0.6" opacity="0.7" />
        <circle class="grid-node" cx="87" cy="60" r="0.6" opacity="0.7" />
        <circle class="grid-node" cx="77.5" cy="65" r="0.5" opacity="0.7" />
        <circle class="grid-node" cx="86.5" cy="65" r="0.5" opacity="0.7" />
        <circle class="grid-node" cx="82" cy="55" r="0.8" opacity="0.9" />

        {{-- ===== TOWER 5 — bottom left ===== --}}
        <line class="grid-tower" x1="20" y1="78" x2="20" y2="105" />
        <line class="grid-tower" x1="16" y1="82" x2="24" y2="82" />
        <line class="grid-tower" x1="16.5" y1="86" x2="23.5" y2="86" />
        <line class="grid-tower" x1="20" y1="78" x2="16" y2="82" />
        <line class="grid-tower" x1="20" y1="78" x2="24" y2="82" />
        <line class="grid-tower" x1="16" y1="82" x2="17.5" y2="100" />
        <line class="grid-tower" x1="24" y1="82" x2="22.5" y2="100" />
        <line class="grid-tower" x1="17.5" y1="100" x2="22.5" y2="100" />
        <circle class="grid-node" cx="16" cy="82" r="0.5" opacity="0.7" />
        <circle class="grid-node" cx="24" cy="82" r="0.5" opacity="0.7" />
        <circle class="grid-node" cx="20" cy="78" r="0.7" opacity="0.9" />

        {{-- ===== TOWER 6 — bottom right ===== --}}
        <line class="grid-tower" x1="70" y1="78" x2="70" y2="105" />
        <line class="grid-tower" x1="66" y1="82" x2="74" y2="82" />
        <line class="grid-tower" x1="66.5" y1="86" x2="73.5" y2="86" />
        <line class="grid-tower" x1="70" y1="78" x2="66" y2="82" />
        <line class="grid-tower" x1="70" y1="78" x2="74" y2="82" />
        <line class="grid-tower" x1="66" y1="82" x2="67.5" y2="100" />
        <line class="grid-tower" x1="74" y1="82" x2="72.5" y2="100" />
        <line class="grid-tower" x1="67.5" y1="100" x2="72.5" y2="100" />
        <circle class="grid-node" cx="66" cy="82" r="0.5" opacity="0.7" />
        <circle class="grid-node" cx="74" cy="82" r="0.5" opacity="0.7" />
        <circle class="grid-node" cx="70" cy="78" r="0.7" opacity="0.9" />

        {{-- ===== TRANSMISSION LINES — top row (T1 → T3) ===== --}}
        {{-- Upper wire --}}
        <path class="grid-line" d="M4,33 Q31,28 54,33" />
        {{-- Lower wire --}}
        <path class="grid-line" d="M4.5,38 Q31,34 54.5,38" />
        {{-- Animated flow wire --}}
        <path class="grid-line--flow" d="M4,33 Q31,28 54,33" style="animation-duration:2.8s" />

        {{-- ===== TRANSMISSION LINES — diagonal T1 → T2 ===== --}}
        <path class="grid-line" d="M4,33 Q18,47 27,60" />
        <path class="grid-line" d="M12,33 Q22,47 37,60" />
        <path class="grid-line--flow-slow" d="M4,33 Q18,47 27,60" style="animation-duration:4.2s" />

        {{-- ===== TRANSMISSION LINES — diagonal T3 → T4 ===== --}}
        <path class="grid-line" d="M54,33 Q68,47 77,60" />
        <path class="grid-line" d="M62,33 Q72,47 87,60" />
        <path class="grid-line--flow-slow" d="M62,33 Q72,47 87,60" style="animation-duration:3.8s" />

        {{-- ===== TRANSMISSION LINES — lower row (T2 → T4) ===== --}}
        <path class="grid-line" d="M27,60 Q54,54 77,60" />
        <path class="grid-line" d="M27.5,65 Q54,60 86.5,65" />
        <path class="grid-line--flow" d="M27,60 Q54,54 77,60" style="animation-duration:3.5s; animation-delay:1s" />

        {{-- ===== TRANSMISSION LINES — T2 → T5 ===== --}}
        <path class="grid-line" d="M27,60 Q24,70 16,82" />
        <path class="grid-line--flow-slow" d="M27,60 Q24,70 16,82"
            style="animation-duration:5s; animation-delay:0.5s" />

        {{-- ===== TRANSMISSION LINES — T4 → T6 ===== --}}
        <path class="grid-line" d="M87,60 Q80,70 74,82" />
        <path class="grid-line--flow-slow" d="M87,60 Q80,70 74,82"
            style="animation-duration:4.5s; animation-delay:1.5s" />

        {{-- ===== TRANSMISSION LINES — T5 → T6 bottom ===== --}}
        <path class="grid-line" d="M16,82 Q43,76 66,82" />
        <path class="grid-line" d="M16.5,86 Q43,80 73.5,86" />
        <path class="grid-line--flow" d="M16,82 Q43,76 66,82" style="animation-duration:4.0s; animation-delay:2s" />

        {{-- Tiny ground lines from tower bases --}}
        <line class="grid-line" x1="5" y1="95" x2="3" y2="95" opacity="0.3" />
        <line class="grid-line" x1="11" y1="95" x2="13" y2="95" opacity="0.3" />
        <line class="grid-line" x1="28.5" y1="100" x2="26" y2="100" opacity="0.3" />
        <line class="grid-line" x1="35.5" y1="100" x2="38" y2="100" opacity="0.3" />

    </svg>

    {{-- Floating spark particles near tower tops --}}
    <div class="vcard-bg-sparks">
        <div class="spark spark-1"></div>
        <div class="spark spark-2"></div>
        <div class="spark spark-3"></div>
        <div class="spark spark-4"></div>
        <div class="spark spark-5"></div>
        <div class="spark spark-6"></div>
        <div class="spark spark-7"></div>
        <div class="spark spark-8"></div>
        <div class="spark spark-9"></div>
        <div class="spark spark-10"></div>
        <div class="spark spark-11"></div>
        <div class="spark spark-12"></div>
    </div>

    {{-- Aviation warning lights on tower tops --}}
    <div class="vcard-bg-lights">
        <div class="pylon-light pylon-light-1"></div>
        <div class="pylon-light pylon-light-2"></div>
        <div class="pylon-light pylon-light-3"></div>
        <div class="pylon-light pylon-light-4"></div>
        <div class="pylon-light pylon-light-5"></div>
        <div class="pylon-light pylon-light-6"></div>
    </div>

</div>

{{-- ── Vignette overlay ── --}}
<div class="vcard-bg-vignette hidden lg:flex" aria-hidden="true"></div>

{{-- ── Floating electrical icons ── --}}
<div class="vcard-bg-icons hidden lg:flex" aria-hidden="true">
    <i class="fas fa-bolt                bg-icon bg-i1"></i>
    <i class="fas fa-bolt                bg-icon bg-i2"></i>
    <i class="fas fa-bolt                bg-icon bg-i3"></i>
    <i class="fas fa-bolt                bg-icon bg-i4"></i>
    <i class="fas fa-bolt                bg-icon bg-i5"></i>
    <i class="fas fa-cog                 bg-icon bg-i6"></i>
    <i class="fas fa-cogs                bg-icon bg-i7"></i>
    <i class="fas fa-cog                 bg-icon bg-i8"></i>
    <i class="fas fa-plug                bg-icon bg-i9"></i>
    <i class="fas fa-plug                bg-icon bg-i10"></i>
    <i class="fas fa-charging-station    bg-icon bg-i11"></i>
    <i class="fas fa-screwdriver-wrench  bg-icon bg-i12"></i>
    <i class="fas fa-toolbox             bg-icon bg-i13"></i>
    <i class="fas fa-hard-hat            bg-icon bg-i14"></i>
    <i class="fas fa-triangle-exclamation bg-icon bg-i15"></i>
    <i class="fas fa-hard-hat            bg-icon bg-i16"></i>
    <i class="fas fa-shield-halved       bg-icon bg-i17"></i>
    <i class="fas fa-lightbulb           bg-icon bg-i18"></i>
    <i class="far fa-lightbulb           bg-icon bg-i19"></i>
    <i class="fas fa-clipboard-list      bg-icon bg-i20"></i>
    <i class="fas fa-gauge-high          bg-icon bg-i21"></i>
    <i class="fas fa-fire                bg-icon bg-i22"></i>
</div>
