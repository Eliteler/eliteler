<!-- ==========================================================================
     Preloader
     ========================================================================== -->
<div id="site-preloader"
    class="fixed inset-0 z-[9999] bg-[#070b14] flex flex-col items-center justify-center select-none overflow-hidden transition-colors">

    <!-- Background Ambient Cyber Glow & Digital Grid -->
    <div
        class="absolute inset-0 bg-[radial-gradient(#1e293b_1px,transparent_1px)] [background-size:24px_24px] opacity-25 pointer-events-none">
    </div>
    <div class="absolute w-96 h-96 bg-indigo-600/15 rounded-full blur-3xl pointer-events-none animate-pulse"></div>

    <div class="preloader-card relative flex flex-col items-center gap-6 p-8 max-w-sm w-full mx-auto z-10 text-center">

        <!-- Logo Frame with Digital Badges -->
        <div class="relative">
            <div
                class="absolute -inset-3 bg-gradient-to-tr from-indigo-500 via-cyan-400 to-emerald-400 rounded-3xl blur-xl opacity-50 animate-pulse">
            </div>

            <img src="{{ url($business_card_details->profile) }}" alt="{{ $card_details->title }}"
                class="relative w-20 h-20 rounded-3xl object-cover border-2 border-white/20 shadow-2xl bg-slate-900">

            <span
                class="absolute -top-2 -right-2 bg-indigo-600 text-white text-[9px] font-black px-2 py-0.5 rounded-full shadow-lg border border-indigo-400/30 tracking-wider">
                .ZIP
            </span>
            <span
                class="absolute -bottom-2 -left-2 bg-emerald-600 text-white text-[9px] font-black px-2 py-0.5 rounded-full shadow-lg border border-emerald-400/30 tracking-wider">
                .PDF
            </span>
        </div>

        <!-- Store Typography -->
        <div class="space-y-1.5">
            <div
                class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-white/5 border border-white/10 text-[10px] font-bold text-indigo-300 tracking-wider uppercase">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-ping"></span>
                {{ __('Digital Asset Storefront') }}
            </div>
            <h2 class="text-2xl font-black tracking-tight text-white leading-tight">
                {{ $card_details->title }}
            </h2>
            <p id="preloader-status-text" class="text-xs text-slate-400 font-medium h-4 transition-all">
                {{ __('Connecting to Cloud Vault...') }}
            </p>
        </div>

        <!-- High-Tech Progress Bar (Enlarged & Reliable Container) -->
        <div class="w-full max-w-xs space-y-2 pt-1">
            <div
                class="h-2 w-full bg-slate-800/90 rounded-full overflow-hidden border border-slate-700/60 shadow-inner">
                <div id="preloader-progress-bar"
                    class="h-full bg-gradient-to-r from-indigo-500 via-cyan-400 to-emerald-400 rounded-full shadow-[0_0_12px_rgba(99,102,241,0.8)]"
                    style="width: 0%;">
                </div>
            </div>

            <div class="flex justify-between items-center text-[10px] text-slate-500 font-mono font-semibold">
                <span class="flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                    {{ __('ASSETS VERIFIED') }}
                </span>
                <span id="preloader-counter" class="text-indigo-400 font-bold text-xs">0%</span>
            </div>
        </div>

    </div>
</div>

{{-- Custom JS --}}
@push('custom-js')
    <!-- GSAP Script to animate both Bar Width and Number synchronously -->
    <script>
        window.addEventListener('load', () => {
            const progress = {
                value: 0
            };
            const percentEl = document.getElementById('preloader-counter');
            const barEl = document.getElementById('preloader-progress-bar');
            const preloader = document.getElementById('site-preloader');
            const statusEl = document.getElementById('preloader-status-text');

            const statusSteps = [
                "{{ __('Connecting to Cloud Vault...') }}",
                "{{ __('Verifying Digital Licenses...') }}",
                "{{ __('Caching Templates & Assets...') }}",
                "{{ __('Digital Storefront Ready') }}"
            ];

            if (typeof gsap !== 'undefined') {
                // Animate progress bar width directly with GSAP to ensure it fills 100%
                if (barEl) {
                    gsap.to(barEl, {
                        width: "100%",
                        duration: 1.2,
                        ease: "power2.out"
                    });
                }

                // Animate percentage counter and text steps synchronously
                gsap.to(progress, {
                    value: 100,
                    duration: 1.2,
                    ease: "power2.out",
                    onUpdate: () => {
                        const current = Math.floor(progress.value);
                        if (percentEl) percentEl.innerText = `${current}%`;

                        if (statusEl) {
                            if (current < 30) statusEl.innerText = statusSteps[0];
                            else if (current < 65) statusEl.innerText = statusSteps[1];
                            else if (current < 90) statusEl.innerText = statusSteps[2];
                            else statusEl.innerText = statusSteps[3];
                        }
                    },
                    onComplete: () => {
                        // Curtain lift exit animation revealing the page
                        const tl = gsap.timeline();
                        tl.to(".preloader-card", {
                                scale: 0.9,
                                opacity: 0,
                                duration: 0.25,
                                ease: "power2.in"
                            })
                            .to(preloader, {
                                yPercent: -100,
                                duration: 0.65,
                                ease: "power4.inOut",
                                onComplete: () => {
                                    preloader.style.display = "none";
                                }
                            })
                            .from("#hero-section", {
                                y: 30,
                                opacity: 0,
                                duration: 0.7,
                                ease: "power3.out"
                            }, "-=0.3")
                            .from("nav", {
                                y: -20,
                                opacity: 0,
                                duration: 0.5,
                                ease: "power2.out"
                            }, "-=0.5");
                    }
                });
            } else {
                if (preloader) preloader.style.display = "none";
            }
        });
    </script>
@endpush
