{{-- 1. Information / Special Offer Popup Modal --}}
@if ($business_card_details != null)
    @if (!empty($business_card_details->is_info_pop_active) && $business_card_details->is_info_pop_active == 1)
        @php
            $information_pop = \App\InformationPop::where('card_id', $business_card_details->card_id)->first();
            $confetti_effect = $information_pop->confetti_effect ?? 0;
            $img = $information_pop->info_pop_image ?? '';
            $title = $information_pop->info_pop_title ?? '';
            $desc = $information_pop->info_pop_desc ?? '';
            $button_text = $information_pop->info_pop_button_text ?? '';
            $button_url = $information_pop->info_pop_button_url ?? '#';
        @endphp

        <!-- Pure Tailwind Modal Overlay (Hidden by Default) -->
        <div id="informationModal"
            class="fixed inset-0 z-50 bg-black/80 backdrop-blur-md hidden flex items-center justify-center p-4 select-none"
            onclick="if(event.target === this) closePopupModal('informationModal');">

            <!-- Modal Card Content -->
            <div
                class="relative w-full max-w-sm sm:max-w-md bg-white dark:bg-[#111625] border border-slate-200 dark:border-slate-800 rounded-3xl p-6 sm:p-8 shadow-2xl text-center overflow-hidden">

                <!-- Ambient Cyber Glow -->
                <div
                    class="absolute -top-10 left-1/2 -translate-x-1/2 w-48 h-48 bg-indigo-500/20 rounded-full blur-3xl pointer-events-none">
                </div>

                <!-- Close Button -->
                <button type="button" onclick="closePopupModal('informationModal')"
                    class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 dark:hover:text-white p-1 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 transition"
                    aria-label="{{ __('Close') }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2"
                            d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>

                <!-- Special Offer Pill Badge -->
                <div class="mb-4">
                    <span
                        class="inline-flex items-center gap-1.5 px-3.5 py-1 rounded-full text-[11px] font-bold tracking-wider uppercase bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-500/20">
                        <i class="ti ti-sparkles text-xs"></i>
                        <span>{{ __('Special Offer') }}</span>
                    </span>
                </div>

                <!-- Product / Offer Image -->
                @if (!empty($img))
                    <div class="flex justify-center mb-4">
                        <img src="{{ url($img) }}" alt="{{ $title }}"
                            class="max-h-24 max-w-[150px] object-contain rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800"
                            onerror="this.onerror=null; this.src='{{ asset('img/templates/all-categories.png') }}';" />
                    </div>
                @endif

                <!-- Offer Headings -->
                <h3 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white mb-2 leading-tight">
                    {{ __($title) }}
                </h3>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mb-4 leading-relaxed max-w-xs mx-auto">
                    {{ __($desc) }}
                </p>

                <!-- Action Button -->
                <a href="{{ $button_url }}" target="_blank" onclick="closePopupModal('informationModal')"
                    class="w-full h-12 bg-indigo-600 hover:bg-indigo-500 text-white font-bold rounded-2xl text-xs sm:text-sm flex items-center justify-center gap-2 transition shadow-lg shadow-indigo-600/30">
                    <span>{{ __($button_text) }}</span>
                    <i class="ti ti-arrow-right text-sm"></i>
                </a>

            </div>
        </div>

        @if ($confetti_effect == 1)
            <script src="{{ asset('js/confetti.browser.min.js') }}"></script>
        @endif
    @endif
@endif

{{-- 2. Newsletter Subscription Popup Modal --}}
@if ($business_card_details != null)
    @if (
        !empty($business_card_details->is_newsletter_pop_active) &&
            $business_card_details->is_newsletter_pop_active == 1 &&
            !request()->cookie('newsletter_' . $business_card_details->card_id))
        <!-- Pure Tailwind Modal Overlay (Hidden by Default) -->
        <div id="newsletterModal"
            class="fixed inset-0 z-50 bg-black/80 backdrop-blur-md hidden flex items-center justify-center p-4 select-none"
            onclick="if(event.target === this) closePopupModal('newsletterModal');">

            <!-- Modal Card Content -->
            <div
                class="relative w-full max-w-sm sm:max-w-md bg-white dark:bg-[#111625] border border-slate-200 dark:border-slate-800 rounded-3xl p-6 sm:p-8 shadow-2xl text-center overflow-hidden">

                <div
                    class="absolute -top-10 left-1/2 -translate-x-1/2 w-48 h-48 bg-indigo-500/20 rounded-full blur-3xl pointer-events-none">
                </div>

                <!-- Close Button -->
                <button type="button" onclick="closePopupModal('newsletterModal')"
                    class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 dark:hover:text-white p-1 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 transition"
                    aria-label="{{ __('Close') }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2"
                            d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>

                <!-- Mail Icon Badge -->
                <div
                    class="w-16 h-16 rounded-2xl bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-500/20 flex items-center justify-center mx-auto mb-4 shadow-sm text-2xl">
                    <i class="ti ti-mail-opened"></i>
                </div>

                <h3 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white mb-2 leading-tight">
                    {{ __('Subscribe to our newsletter') }}
                </h3>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mb-6 leading-relaxed max-w-xs mx-auto">
                    {{ __('Get exclusive deals, new arrivals, and special promotions delivered right to your inbox.') }}
                </p>

                <!-- Form -->
                <form action="{{ url('subscribe/store/newsletter') }}" method="post" class="space-y-3">
                    @csrf
                    <input type="hidden" id="card_id" name="card_id" value="{{ $business_card_details->card_id }}">

                    <!-- Fixed Email Input with Guaranteed Spacing -->
                    <div class="relative flex items-center">
                        <!-- Mail SVG Icon (Centered on Left) -->
                        <div
                            class="absolute left-0 top-0 bottom-0 w-12 flex items-center justify-center pointer-events-none text-slate-400">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
                                </path>
                            </svg>
                        </div>

                        <!-- Input with Explicit Left Padding to Prevent Overlap -->
                        <input type="email" name="email" id="newsletter_email" required
                            placeholder="{{ __('Enter your email address') }}"
                            class="w-full h-12 pr-4 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl text-xs sm:text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/10 transition"
                            style="padding-left: 48px !important;" />
                    </div>

                    <button type="submit"
                        class="w-full h-12 bg-indigo-600 hover:bg-indigo-500 text-white font-bold rounded-2xl text-xs sm:text-sm flex items-center justify-center gap-2 transition shadow-lg shadow-indigo-600/30">
                        <i class="ti ti-send text-sm"></i>
                        <span>{{ __('Subscribe Now') }}</span>
                    </button>
                </form>

            </div>
        </div>
    @endif
@endif

<!-- Pure Tailwind & GSAP Modal Controller -->
<script>
    // Open Modal Overlay with Smooth Animation
    function openPopupModal(modalId) {
        const modal = document.getElementById(modalId);
        if (!modal) return;

        modal.classList.remove('hidden');
        const card = modal.firstElementChild;

        if (typeof gsap !== 'undefined' && card) {
            gsap.fromTo(modal, {
                opacity: 0
            }, {
                opacity: 1,
                duration: 0.25
            });
            gsap.fromTo(card, {
                scale: 0.88,
                opacity: 0,
                y: 20
            }, {
                scale: 1,
                opacity: 1,
                y: 0,
                duration: 0.35,
                ease: "back.out(1.5)"
            });
        }
    }

    // Close Modal Overlay with Animation
    function closePopupModal(modalId) {
        const modal = document.getElementById(modalId);
        if (!modal) return;

        const card = modal.firstElementChild;
        if (typeof gsap !== 'undefined' && card) {
            gsap.to(card, {
                scale: 0.9,
                opacity: 0,
                y: 15,
                duration: 0.2,
                ease: "power2.in"
            });
            gsap.to(modal, {
                opacity: 0,
                duration: 0.25,
                onComplete: () => {
                    modal.classList.add('hidden');
                    // Queue next modal if closing Information modal
                    if (modalId === 'informationModal') {
                        const newsModal = document.getElementById('newsletterModal');
                        if (newsModal) setTimeout(() => openPopupModal('newsletterModal'), 500);
                    }
                }
            });
        } else {
            modal.classList.add('hidden');
        }
    }

    // Auto Trigger on Load
    document.addEventListener("DOMContentLoaded", function() {
        const infoModal = document.getElementById('informationModal');
        const newsModal = document.getElementById('newsletterModal');

        if (infoModal) {
            setTimeout(function() {
                openPopupModal('informationModal');
                if (typeof confetti === 'function') {
                    confetti({
                        particleCount: 120,
                        spread: 80,
                        origin: {
                            y: 0.6
                        }
                    });
                }
            }, 1500);
        } else if (newsModal) {
            setTimeout(function() {
                openPopupModal('newsletterModal');
            }, 2000);
        }
    });
</script>
