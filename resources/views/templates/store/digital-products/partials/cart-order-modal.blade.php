<!-- ==========================================================================
     Reusable Cart & Customer Order Modals
     ========================================================================== -->

<!-- 1. Shopping Cart Modal -->
<div id="cart-modal" class="fixed inset-0 z-50 bg-black/75 backdrop-blur-sm hidden flex items-center justify-center p-4">
    <div id="cart-modal-content"
        class="bg-white dark:bg-[#111625] border border-slate-200 dark:border-slate-800 w-full max-w-lg rounded-3xl p-5 sm:p-6 shadow-2xl flex flex-col max-h-[90vh]">

        <div class="flex items-center justify-between pb-4 border-b border-slate-200 dark:border-slate-800">
            <div class="flex items-center gap-2.5">
                <div
                    class="w-8 h-8 rounded-xl bg-indigo-600/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold text-sm">
                    🛒
                </div>
                <h3 class="text-base font-bold text-slate-900 dark:text-white">{{ __('Your Shopping Cart') }}</h3>
            </div>
            <button onclick="closeCartModal()"
                class="text-slate-400 hover:text-slate-600 dark:hover:text-white p-1 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                    </path>
                </svg>
            </button>
        </div>

        <!-- Dynamic Cart Items Container -->
        <div id="cart-items-container" class="flex-1 overflow-y-auto py-4 space-y-3"></div>

        <div class="pt-4 border-t border-slate-200 dark:border-slate-800 space-y-3">
            <div class="flex items-center justify-between text-sm">
                <span class="text-slate-500 dark:text-slate-400 font-medium">{{ __('Subtotal') }}</span>
                <span id="cart-total-price" class="text-lg font-black text-slate-900 dark:text-white">0</span>
            </div>
            <button onclick="placeOrder()"
                class="w-full bg-[#059669] hover:bg-[#047857] text-white font-bold py-3.5 px-4 rounded-2xl text-xs sm:text-sm flex items-center justify-center gap-2.5 transition shadow-lg shadow-emerald-600/25">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    class="icon icon-tabler icons-tabler-outline icon-tabler-brand-whatsapp w-5 h-5">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                    <path d="M3 21l1.65 -3.8a9 9 0 1 1 3.4 2.9l-5.05 .9" />
                    <path
                        d="M9 10a.5 .5 0 0 0 1 0v-1a.5 .5 0 0 0 -1 0v1a5 5 0 0 0 5 5h1a.5 .5 0 0 0 0 -1h-1a.5 .5 0 0 0 0 1" />
                </svg>
                <span>{{ __('Place WhatsApp Order') }}</span>
            </button>
        </div>
    </div>
</div>

<!-- 2. Customer Order Details Modal -->
<div id="orderModal"
    class="fixed inset-0 z-50 bg-black/75 backdrop-blur-sm hidden flex items-center justify-center p-4">
    <div id="orderModalContent"
        class="bg-white dark:bg-[#111625] border border-slate-200 dark:border-slate-800 w-full max-w-md rounded-3xl p-6 shadow-2xl space-y-5 max-h-[92vh] overflow-y-auto">

        <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800/80">
            <div class="flex items-center gap-2.5">
                <div
                    class="w-8 h-8 rounded-xl bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold text-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                </div>
                <div>
                    <h4 class="font-bold text-slate-900 dark:text-white text-base leading-tight">
                        {{ __('Customer Details') }}</h4>
                    <p class="text-[11px] text-slate-400 mt-0.5">{{ __('Enter your info to complete WhatsApp order') }}
                    </p>
                </div>
            </div>
            <button onclick="closeModal('orderModal')"
                class="text-slate-400 hover:text-slate-600 dark:hover:text-white p-1 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                    </path>
                </svg>
            </button>
        </div>

        <div class="space-y-3.5 text-xs">
            <div>
                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1.5 flex items-center gap-1">
                    <span>{{ __('Full Name') }}</span>
                    <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </div>
                    <input type="text" id="cus_name"
                        class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl pl-10 pr-3.5 py-2.5 text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/10 transition"
                        placeholder="{{ __('Enter your full name') }}" required />
                </div>
            </div>

            <div>
                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1.5 flex items-center gap-1">
                    <span>{{ __('Mobile Number') }}</span>
                    <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z">
                            </path>
                        </svg>
                    </div>
                    <input type="tel" id="cus_mobile"
                        class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl pl-10 pr-3.5 py-2.5 text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/10 transition"
                        placeholder="{{ __('Country code + mobile number (e.g. 919876543210)') }}" required />
                </div>
            </div>

            <div>
                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1.5 flex items-center gap-1">
                    <span>{{ __('Delivery address / Email') }}</span>
                    <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                    <div class="absolute top-3 left-0 pl-3.5 flex items-start pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
                            </path>
                        </svg>
                    </div>
                    <textarea id="cus_address" rows="3"
                        class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl pl-10 pr-3.5 py-2.5 text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/10 transition resize-none"
                        placeholder="{{ __('Delivery address or email for digital files') }}" required></textarea>
                </div>
            </div>

            <div>
                <label
                    class="block font-semibold text-slate-700 dark:text-slate-300 mb-1.5">{{ __('Order Notes (Optional)') }}</label>
                <textarea id="cus_notes" rows="2"
                    class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl px-3.5 py-2.5 text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/10 transition"
                    placeholder="{{ __('Any specific instructions or requirements...') }}"></textarea>
            </div>
        </div>

        <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-100 dark:border-slate-800/80">
            <button onclick="closeModal('orderModal')"
                class="px-4 py-2.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold rounded-2xl text-xs transition">
                {{ __('Close') }}
            </button>
            <button onclick="confirmOrder()"
                class="flex-1 bg-emerald-600 hover:bg-emerald-500 text-white font-bold py-2.5 px-4 rounded-2xl text-xs flex items-center justify-center gap-2 transition shadow-lg shadow-emerald-600/20">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                    stroke-linejoin="round"
                    class="icon icon-tabler icons-tabler-outline icon-tabler-brand-whatsapp w-4 h-4">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                    <path d="M3 21l1.65 -3.8a9 9 0 1 1 3.4 2.9l-5.05 .9" />
                    <path
                        d="M9 10a.5 .5 0 0 0 1 0v-1a.5 .5 0 0 0 -1 0v1a5 5 0 0 0 5 5h1a.5 .5 0 0 0 0 -1h-1a.5 .5 0 0 0 0 1" />
                </svg>
                <span>{{ __('Confirm on WhatsApp') }}</span>
            </button>
        </div>
    </div>
</div>

<!-- 3. Floating Alert Containers -->
<div id="errorAlertContainer"
    class="fixed bottom-6 right-6 z-50 bg-rose-600 text-white text-xs font-semibold px-4 py-3 rounded-2xl shadow-2xl hidden items-center gap-2">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
    </svg>
    <div id="errorAlertMessage"></div>
</div>
<div id="successAlertContainer"
    class="fixed bottom-6 right-6 z-50 bg-emerald-600 text-white text-xs font-semibold px-4 py-3 rounded-2xl shadow-2xl hidden items-center gap-2">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
    </svg>
    <div id="successAlertMessage"></div>
</div>

{{-- Custom JS from Backend --}}
@push('custom-js')
    <!-- 4. Global Cart & Order Processing Script (Controller Compatible) -->
    <script>
        if (typeof window.cart === 'undefined') {
            window.cart = [];
        }
        if (typeof window.productQuantities === 'undefined') {
            window.productQuantities = {};
        }
        window.businessPhoneNumber = "{{ $enquiry_button ?? '' }}";
        window.whatsAppMessage = `{!! $whatsapp_msg ?? '' !!}`;

        var currencyConfig = {
            code: @json($currency ?? '$'),
            formatType: @json($config[55]->config_value ?? '1,234,567.89'),
            decimals: @json((int) ($config[56]->config_value ?? 2))
        };

        var availableCurrencies = {!! json_encode(App\Currency::where('status', 1)->select('iso_code', 'symbol', 'symbol_first')->get()) !!};
        var activeCurrencySymbol = '{{ $currency ?? "$" }}';
        var isSymbolFirst = true;

        for (var i = 0; i < availableCurrencies.length; i++) {
            if (availableCurrencies[i].symbol === currencyConfig.code || availableCurrencies[i].iso_code === currencyConfig
                .code) {
                activeCurrencySymbol = availableCurrencies[i].symbol;
                isSymbolFirst = availableCurrencies[i].symbol_first !== false && availableCurrencies[i].symbol_first !==
                    "false";
                break;
            }
        }

        function jsFormatCurrency(amount) {
            var num = Number(amount) || 0;
            var formattedNum = num.toLocaleString('en-US', {
                minimumFractionDigits: currencyConfig.decimals,
                maximumFractionDigits: currencyConfig.decimals
            });
            return isSymbolFirst ? (activeCurrencySymbol + ' ' + formattedNum) : (formattedNum + ' ' +
                activeCurrencySymbol);
        }

        function openModal(id) {
            var el = document.getElementById(id);
            if (el) {
                el.classList.remove('hidden');
                var content = el.firstElementChild;
                if (typeof gsap !== 'undefined' && content) {
                    gsap.fromTo(el, {
                        opacity: 0
                    }, {
                        opacity: 1,
                        duration: 0.25
                    });
                    gsap.fromTo(content, {
                        scale: 0.9,
                        opacity: 0
                    }, {
                        scale: 1,
                        opacity: 1,
                        duration: 0.3,
                        ease: "back.out(1.5)"
                    });
                }
            }
        }

        function closeModal(id) {
            var el = document.getElementById(id);
            if (el) {
                if (typeof gsap !== 'undefined') {
                    gsap.to(el, {
                        opacity: 0,
                        duration: 0.2,
                        onComplete: () => el.classList.add('hidden')
                    });
                } else {
                    el.classList.add('hidden');
                }
            }
        }

        function openCartModal() {
            var modal = document.getElementById('cart-modal');
            var content = document.getElementById('cart-modal-content');
            renderCartItems();

            if (modal) {
                modal.classList.remove('hidden');
                if (typeof gsap !== 'undefined') {
                    gsap.fromTo(modal, {
                        opacity: 0
                    }, {
                        opacity: 1,
                        duration: 0.25
                    });
                    gsap.fromTo(content, {
                        scale: 0.9,
                        opacity: 0
                    }, {
                        scale: 1,
                        opacity: 1,
                        duration: 0.3,
                        ease: "back.out(1.5)"
                    });
                }
            }
        }

        function closeCartModal() {
            var modal = document.getElementById('cart-modal');
            if (modal) {
                if (typeof gsap !== 'undefined') {
                    gsap.to(modal, {
                        opacity: 0,
                        duration: 0.2,
                        onComplete: () => modal.classList.add('hidden')
                    });
                } else {
                    modal.classList.add('hidden');
                }
            }
        }

        function updateCartItemQty(index, delta) {
            window.cart[index].quantity += delta;
            if (window.cart[index].quantity <= 0) {
                window.cart.splice(index, 1);
            }
            updateCartBadge();
            saveCartStorage();
            renderCartItems();
        }

        function updateCartBadge() {
            var totalCount = window.cart.reduce((sum, item) => sum + item.quantity, 0);
            var desktopCounter = document.getElementById('cart-counter');
            if (desktopCounter) desktopCounter.innerText = totalCount;

            var mobileCounter = document.getElementById('mobile-cart-counter');
            if (mobileCounter) mobileCounter.innerText = totalCount;
        }

        function renderCartItems() {
            var container = document.getElementById('cart-items-container');
            var totalEl = document.getElementById('cart-total-price');
            if (!container || !totalEl) return;

            if (window.cart.length === 0) {
                container.innerHTML = `
                <div class="text-center py-12 text-slate-400 text-xs">
                    <div class="text-4xl mb-2">🛒</div>
                    {{ __('Your cart is empty.') }}
                </div>
            `;
                totalEl.innerText = jsFormatCurrency(0);
                return;
            }

            var total = 0;
            container.innerHTML = window.cart.map((item, idx) => {
                total += item.price * item.quantity;
                return `
                <div class="flex items-center justify-between gap-3 bg-slate-100/90 dark:bg-slate-900/70 border border-slate-200 dark:border-slate-800 p-3.5 rounded-2xl">
                    <div class="flex items-center gap-3 min-w-0 flex-1 pr-2">
                        <img src="${item.img}" alt="${item.title}" class="w-12 h-12 rounded-xl object-cover shrink-0 border border-slate-200 dark:border-slate-700">
                        <div class="min-w-0 flex-1">
                            <h5 class="text-xs font-bold text-slate-900 dark:text-white truncate" title="${item.title}">${item.title}</h5>
                            <div class="text-xs text-emerald-600 dark:text-emerald-400 font-extrabold mt-0.5">${jsFormatCurrency(item.price)}</div>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 shrink-0">
                        <div class="flex items-center border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 rounded-xl overflow-hidden p-0.5 shadow-sm">
                            <button type="button" onclick="updateCartItemQty(${idx}, -1)" class="w-6 h-6 flex items-center justify-center text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 rounded transition font-bold text-xs">-</button>
                            <span class="w-7 text-center text-xs font-bold text-slate-900 dark:text-white">${item.quantity}</span>
                            <button type="button" onclick="updateCartItemQty(${idx}, 1)" class="w-6 h-6 flex items-center justify-center text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 rounded transition font-bold text-xs">+</button>
                        </div>
                        <button type="button" onclick="removeFromCartLive(${idx})" class="text-rose-500 hover:text-rose-400 p-1.5 rounded-lg hover:bg-rose-500/10 transition" title="{{ __('Delete') }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            `;
            }).join('');

            totalEl.innerText = jsFormatCurrency(total);
        }

        function removeFromCartLive(index) {
            window.cart.splice(index, 1);
            updateCartBadge();
            saveCartStorage();
            renderCartItems();
        }

        var businessHours = @json(json_decode($businessHours->business_hours ?? '{}', true));

        function isWithinBusinessHours() {
            "use strict";
            var now = new Date();
            var days = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
            var day = days[now.getDay()];
            var currentTime = now.getHours().toString().padStart(2, '0') + ':' + now.getMinutes().toString().padStart(2,
                '0');
            var hours = businessHours ? businessHours[day] : null;
            if (!hours || !hours.start || !hours.end) return true;
            return (currentTime >= hours.start && currentTime <= hours.end);
        }

        function placeOrder() {
            if (window.cart.length === 0) {
                showToastAlert('errorAlertContainer', 'errorAlertMessage', '{{ __('Your cart is empty.') }}');
                return;
            }

            if (isWithinBusinessHours()) {
                closeCartModal();
                openModal('orderModal');
            } else {
                showToastAlert('errorAlertContainer', 'errorAlertMessage', '{{ __('Sorry, we are currently closed.') }}');
            }
        }

        function confirmOrder() {
            var cusName = document.getElementById('cus_name').value.trim();
            var cusMobile = document.getElementById('cus_mobile').value.trim();
            var cusAddress = document.getElementById('cus_address').value.trim();
            var cusNotes = document.getElementById('cus_notes').value.trim();

            if (!cusName || !cusMobile || !cusAddress) {
                showToastAlert('errorAlertContainer', 'errorAlertMessage',
                    '{{ __('Please fill out all required fields.') }}');
                return false;
            }

            createWhatsAppLink([cusName, cusMobile, cusAddress, cusNotes]);
            closeModal('orderModal');
        }

        function createWhatsAppLink(cusDetails) {
            if (cusDetails[0].length >= 2 && cusDetails[1].length >= 4) {
                // Modern Clean Table Format for WhatsApp
                var productsList = `📦 *{{ __('ORDER SUMMARY') }}*\n`;
                productsList += `------------------------------------\n`;
                var grandTotal = 0;

                var formattedOrderItems = window.cart.map((item, index) => {
                    var itemCost = Number(item.quantity) * Number(item.price);
                    grandTotal += itemCost;

                    // Formatted Table Line: 1. Product Name * Qty = Total
                    productsList += `${index + 1}. *${item.title}*\n`;
                    productsList +=
                        `   └ ${jsFormatCurrency(item.price)} × ${item.quantity} = *${jsFormatCurrency(itemCost)}*\n`;

                    return {
                        product_image: item.img,
                        product_name: item.title,
                        price: item.price,
                        qty: item.quantity
                    };
                });

                productsList += `------------------------------------\n`;

                $.ajax({
                    url: "{{ url('order/place') }}",
                    method: "POST",
                    data: {
                        _token: '{{ csrf_token() }}',
                        store_id: "{{ $business_card_details->card_id ?? '' }}",
                        customer_name: cusDetails[0],
                        customer_phone: cusDetails[1],
                        delivery_address: cusDetails[2],
                        delivery_note: cusDetails[3],
                        delivery_method: 'order for delivery',
                        order_items: formattedOrderItems,
                        total_price: grandTotal,
                    },
                    success: function(data) {
                        if (data.status == "success") {
                            var waShareContent = `🛍️ *{{ __('NEW ORDER RECEIVED') }}* \n\n` +
                                `👤 *{{ __('Customer Details:') }}* \n` +
                                `• {{ __('Name') }}: ${cusDetails[0]}\n` +
                                `• {{ __('Phone') }}: ${cusDetails[1]}\n` +
                                `• {{ __('Address / Email') }}: ${cusDetails[2]}\n` +
                                (cusDetails[3] ? `• {{ __('Notes') }}: ${cusDetails[3]}\n` : ``) +
                                `\n` + productsList +
                                `\n💰 *{{ __('Grand Total:') }}* *${jsFormatCurrency(grandTotal)}*\n\n` +
                                `*${window.whatsAppMessage}*`;

                            window.open(
                                `https://api.whatsapp.com/send/?phone=${window.businessPhoneNumber}&text=${encodeURI(waShareContent)}`,
                                '_blank');

                            window.cart = [];
                            saveCartStorage();
                            updateCartBadge();
                            showToastAlert('successAlertContainer', 'successAlertMessage',
                                '{{ __('Order Placed on WhatsApp!') }}');
                        } else {
                            showToastAlert('errorAlertContainer', 'errorAlertMessage', data.message);
                        }
                    },
                    error: function(xhr) {
                        showToastAlert('errorAlertContainer', 'errorAlertMessage',
                            '{{ __('Something went wrong. Please try again.') }}');
                    }
                });
            }
        }

        function showToastAlert(containerId, messageId, message) {
            var container = document.getElementById(containerId);
            var msgEl = document.getElementById(messageId);
            if (container && msgEl) {
                msgEl.innerText = message;
                container.classList.remove('hidden');
                container.classList.add('flex');
                setTimeout(() => {
                    container.classList.remove('flex');
                    container.classList.add('hidden');
                }, 3500);
            }
        }

        function saveCartStorage() {
            localStorage.setItem("cart_" + "{{ $business_card_details->card_id ?? 'store' }}", JSON.stringify(window
                .cart));
        }

        function loadCartStorage() {
            var key = "cart_" + "{{ $business_card_details->card_id ?? 'store' }}";
            window.cart = localStorage.getItem(key) ? JSON.parse(localStorage.getItem(key)) : [];
            updateCartBadge();
        }

        document.addEventListener("DOMContentLoaded", function() {
            loadCartStorage();
        });
    </script>
@endpush
