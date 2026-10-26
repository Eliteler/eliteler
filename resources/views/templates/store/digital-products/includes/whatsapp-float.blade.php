@php
    // Safe fallbacks matching your variables
    $chatImage = $businessImage ?? ($business_card_details->profile ?? '');
    $chatName = $businessName ?? ($card_details->title ?? ($business_card_details->title ?? 'Store'));
    $chatNumber = $whatsappNumber ?? ($enquiry_button ?? '919876543210');

    // Directly fetch store business hours from the store_business_hours table
    $storeId = $business_card_details->card_id ?? ($card_details->card_id ?? '');
    $storeBusinessHoursRow = \DB::table('store_business_hours')->where('store_id', $storeId)->first();

    $storeHoursData = null;
    $isHoursEnabled = false;

    // Check if record exists and status is 1 (Active)
    if ($storeBusinessHoursRow && (!isset($storeBusinessHoursRow->status) || $storeBusinessHoursRow->status == 1)) {
        $storeHoursData = json_decode($storeBusinessHoursRow->business_hours, true);
        $isHoursEnabled = true;
    }
@endphp

<!-- WhatsApp AI Chatbot Modal Widget -->
<div id="whatsapp-chatbot-widget"
    class="fixed right-4 sm:right-6 bottom-36 md:bottom-24 z-50 w-[92vw] sm:w-[370px] hidden origin-bottom-right print:hidden">
    <div
        class="bg-white dark:bg-[#111625] border border-slate-200 dark:border-slate-800 rounded-3xl shadow-2xl overflow-hidden flex flex-col">

        <!-- Header with Dynamic Business Hours Status -->
        <div class="bg-gradient-to-r from-emerald-600 to-teal-700 p-4 text-white flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="relative">
                    <img src="{{ url($chatImage) }}" alt="{{ __($chatName) }}"
                        class="w-10 h-10 rounded-full object-cover border-2 border-white/40 shadow">
                    <!-- Dynamic Status Indicator Dot -->
                    <span id="store-status-dot"
                        class="absolute bottom-0 right-0 w-3 h-3 bg-emerald-400 border-2 border-emerald-700 rounded-full"></span>
                </div>
                <div>
                    <div class="flex items-center gap-1.5">
                        <h4 class="font-bold text-sm leading-tight">{{ __($chatName) }}</h4>
                    </div>
                    <!-- Dynamic Online / Offline Status Text -->
                    <p id="store-status-text" class="text-[11px] text-emerald-100 flex items-center gap-1 mt-0.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-300 animate-pulse inline-block"></span>
                        {{ __('Online • Open for Orders') }}
                    </p>
                </div>
            </div>
            <button type="button" onclick="toggleWhatsAppChatbot()"
                class="text-white/80 hover:text-white p-1 rounded-lg hover:bg-white/10 transition"
                aria-label="{{ __('Close Chat') }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <div class="p-4 space-y-3 max-h-[310px] overflow-y-auto text-xs bg-slate-50 dark:bg-[#0b0f19]/70">
            <div class="text-center text-[10px] text-slate-400 font-medium">
                {{ __('Today • Direct WhatsApp Ordering') }}</div>

            <div class="flex items-start gap-2.5">
                <div
                    class="w-7 h-7 rounded-full bg-emerald-600 text-white flex items-center justify-center text-xs font-bold shrink-0">
                    {{ substr($chatName, 0, 2) }}
                </div>
                <div
                    class="bg-white dark:bg-[#1a2235] border border-slate-200 dark:border-slate-800 p-3 rounded-2xl rounded-tl-sm text-slate-700 dark:text-slate-200 shadow-sm leading-relaxed">
                    {{ __('Hello! 👋 Welcome to') }} <strong
                        class="text-emerald-600 dark:text-emerald-400">{{ __($chatName) }}</strong>.
                    {{ __('You can place your orders and make inquiries directly via WhatsApp.') }}
                </div>
            </div>

            <!-- Quick Action Prompts -->
            <div class="pt-2 space-y-1.5 pl-9">
                <p class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider">
                    {{ __('Quick Inquiries:') }}</p>
                <button type="button"
                    onclick="sendQuickPrompt('Hi! I want to place an order and need quick assistance.')"
                    class="w-full text-left bg-white dark:bg-[#151c2d] hover:bg-emerald-50 dark:hover:bg-emerald-950/40 border border-slate-200 dark:border-slate-800 hover:border-emerald-500/40 p-2 rounded-xl text-slate-700 dark:text-slate-300 transition flex items-center justify-between">
                    <span>⚡ {{ __('Place Order via WhatsApp') }}</span>
                    <span class="text-emerald-500">&rarr;</span>
                </button>
                <button type="button" onclick="sendQuickPrompt('Hi! Are there any bundle discounts available today?')"
                    class="w-full text-left bg-white dark:bg-[#151c2d] hover:bg-emerald-50 dark:hover:bg-emerald-950/40 border border-slate-200 dark:border-slate-800 hover:border-emerald-500/40 p-2 rounded-xl text-slate-700 dark:text-slate-300 transition flex items-center justify-between">
                    <span>🎁 {{ __('Bundle Deals & Discounts') }}</span>
                    <span class="text-emerald-500">&rarr;</span>
                </button>
                <button type="button"
                    onclick="sendQuickPrompt('Hi! I need to chat regarding product availability and payment.')"
                    class="w-full text-left bg-white dark:bg-[#151c2d] hover:bg-emerald-50 dark:hover:bg-emerald-950/40 border border-slate-200 dark:border-slate-800 hover:border-emerald-500/40 p-2 rounded-xl text-slate-700 dark:text-slate-300 transition flex items-center justify-between">
                    <span>💬 {{ __('Chat with Store Owner') }}</span>
                    <span class="text-emerald-500">&rarr;</span>
                </button>
            </div>
        </div>

        <!-- Custom Message Box -->
        <div
            class="p-3 border-t border-slate-200 dark:border-slate-800 bg-white dark:bg-[#111625] flex items-center gap-2">
            <input id="chatbot-user-input" type="text" placeholder="{{ __('Type message to WhatsApp...') }}"
                onkeydown="if(event.key==='Enter') sendChatbotCustomMessage()"
                class="flex-1 bg-slate-100 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-xs text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:border-emerald-500">
            <button type="button" onclick="sendChatbotCustomMessage()"
                class="bg-[#25D366] hover:bg-[#20ba5a] text-white p-2.5 rounded-xl transition shadow-md flex items-center justify-center shrink-0"
                aria-label="{{ __('Send Message') }}">
                <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                    <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z" />
                </svg>
            </button>
        </div>
    </div>
</div>

<!-- Floating Trigger Button -->
<button type="button" onclick="toggleWhatsAppChatbot()"
    class="fixed right-5 bottom-20 md:bottom-6 z-40 bg-[#25D366] hover:bg-[#20ba5a] text-white w-14 h-14 rounded-full shadow-2xl flex items-center justify-center transition-all duration-300 hover:scale-110 shadow-emerald-500/30 print:hidden"
    aria-label="{{ __('Open WhatsApp Chatbot') }}">
    <svg class="w-8 h-8 fill-current" viewBox="0 0 24 24">
        <path
            d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86s.274.072.376-.043c.101-.116.433-.506.549-.68.116-.173.231-.145.39-.087s1.011.477 1.184.564.289.13.332.202c.043.073.043.419-.101.824z" />
    </svg>
</button>

{{-- Custom JS from Backend --}}
@push('custom-js')
    <!-- JavaScript to accurately parse store_business_hours JSON -->
    <script>
        var rawStoreHours = @json($storeHoursData);
        var isHoursConfigured = @json($isHoursEnabled);

        function isStoreCurrentlyOnline() {
            // If business hours are disabled or not set, default to open
            if (!isHoursConfigured || !rawStoreHours || Object.keys(rawStoreHours).length === 0) {
                return true;
            }

            var now = new Date();
            var days = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
            var currentDay = days[now.getDay()]; // Gets 'monday', 'tuesday', etc.

            var schedule = rawStoreHours[currentDay];
            if (!schedule || !schedule.start || !schedule.end) {
                return false; // Day is not configured or closed
            }

            // Calculate total minutes from midnight
            var currentMinutes = now.getHours() * 60 + now.getMinutes();

            var startParts = schedule.start.split(':');
            var startMinutes = parseInt(startParts[0], 10) * 60 + parseInt(startParts[1], 10);

            var endParts = schedule.end.split(':');
            var endMinutes = parseInt(endParts[0], 10) * 60 + parseInt(endParts[1], 10);

            return (currentMinutes >= startMinutes && currentMinutes <= endMinutes);
        }

        // Update Status Badge on Load
        document.addEventListener("DOMContentLoaded", function() {
            var isOnline = isStoreCurrentlyOnline();
            var dot = document.getElementById('store-status-dot');
            var text = document.getElementById('store-status-text');

            if (dot && text) {
                if (isOnline) {
                    dot.className =
                        "absolute bottom-0 right-0 w-3 h-3 bg-emerald-400 border-2 border-emerald-700 rounded-full";
                    text.innerHTML =
                        '<span class="w-1.5 h-1.5 rounded-full bg-emerald-300 animate-pulse inline-block mr-1"></span> {{ __('Online • Open for Orders') }}';
                } else {
                    dot.className =
                        "absolute bottom-0 right-0 w-3 h-3 bg-rose-400 border-2 border-rose-700 rounded-full";
                    text.innerHTML =
                        '<span class="w-1.5 h-1.5 rounded-full bg-rose-300 inline-block mr-1"></span> {{ __('Offline • Currently Closed') }}';
                }
            }
        });

        function toggleWhatsAppChatbot() {
            var widget = document.getElementById('whatsapp-chatbot-widget');
            if (!widget) return;

            if (widget.classList.contains('hidden')) {
                widget.classList.remove('hidden');
                if (typeof gsap !== 'undefined') {
                    gsap.fromTo(widget, {
                        scale: 0.85,
                        opacity: 0,
                        y: 20
                    }, {
                        scale: 1,
                        opacity: 1,
                        y: 0,
                        duration: 0.35,
                        ease: "back.out(1.7)"
                    });
                }
            } else {
                if (typeof gsap !== 'undefined') {
                    gsap.to(widget, {
                        scale: 0.85,
                        opacity: 0,
                        y: 15,
                        duration: 0.25,
                        ease: "power2.in",
                        onComplete: () => widget.classList.add('hidden')
                    });
                } else {
                    widget.classList.add('hidden');
                }
            }
        }

        function sendQuickPrompt(promptText) {
            var phoneNumber = "{{ $chatNumber }}";
            var encodedText = encodeURIComponent(promptText);
            window.open(`https://wa.me/${phoneNumber}?text=${encodedText}`, '_blank');
        }

        function sendChatbotCustomMessage() {
            var input = document.getElementById('chatbot-user-input');
            if (!input) return;
            var message = input.value.trim();
            if (!message) return;

            var phoneNumber = "{{ $chatNumber }}";
            var encodedText = encodeURIComponent(message);
            window.open(`https://wa.me/${phoneNumber}?text=${encodedText}`, '_blank');
            input.value = '';
        }
    </script>
@endpush
