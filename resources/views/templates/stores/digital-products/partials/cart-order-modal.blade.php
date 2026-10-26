{{--
    Digital products cart and checkout (Tailwind). Markup only: the shared storefront script
    (public/js/store/storefront.js) fills and runs it through the data-* hooks; the theme scripts
    (includes/scripts.blade.php) open and close these layers.
--}}

{{-- Cart --}}
<div id="cart-modal" class="fixed inset-0 z-50 bg-black/75 backdrop-blur-sm hidden flex items-center justify-center p-4" data-cart-drawer>
    <div class="bg-white dark:bg-[#111625] border border-slate-200 dark:border-slate-800 w-full max-w-lg rounded-3xl p-5 sm:p-6 shadow-2xl flex flex-col max-h-[90vh]">

        <div class="flex items-center justify-between pb-4 border-b border-slate-200 dark:border-slate-800">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-xl bg-indigo-600/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold text-sm">🛒</div>
                <h3 class="text-base font-bold text-slate-900 dark:text-white">{{ __('Your Shopping Cart') }}</h3>
            </div>
            <button type="button" data-layer-close aria-label="{{ __('Close') }}"
                class="text-slate-400 hover:text-slate-600 dark:hover:text-white p-1 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition cursor-pointer">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <div class="flex-1 overflow-y-auto py-4 space-y-3">
            <div class="space-y-3" data-cart-items></div>
            <div class="text-center py-12 text-slate-400 text-xs" data-cart-when-empty>
                <div class="text-4xl mb-2">🛒</div>
                {{ __('Your cart is empty.') }}
            </div>
        </div>

        <div class="pt-4 border-t border-slate-200 dark:border-slate-800 space-y-3" data-cart-when-filled hidden>
            <div class="flex items-center justify-between text-sm">
                <span class="text-slate-500 dark:text-slate-400 font-medium">{{ __('Subtotal') }}</span>
                <span class="text-lg font-black text-slate-900 dark:text-white" data-cart-total></span>
            </div>
            <button type="button" data-cart-checkout
                class="w-full bg-[#059669] hover:bg-[#047857] text-white font-bold py-3.5 px-4 rounded-2xl text-xs sm:text-sm flex items-center justify-center gap-2.5 transition shadow-lg shadow-emerald-600/25 cursor-pointer">
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

{{-- One cart line (copied by the storefront script for every item) --}}
<template data-cart-item>
    <div class="flex items-center justify-between gap-3 bg-slate-100/90 dark:bg-slate-900/70 border border-slate-200 dark:border-slate-800 p-3.5 rounded-2xl">
        <div class="flex items-center gap-3 min-w-0 flex-1 pr-2">
            <img class="w-12 h-12 rounded-xl object-cover shrink-0 border border-slate-200 dark:border-slate-700" data-cart-image
                data-fallback="{{ asset('img/templates/digital-products/placeholder.svg') }}" alt="">
            <div class="min-w-0 flex-1">
                <h5 class="text-xs font-bold text-slate-900 dark:text-white truncate" data-cart-name></h5>
                <div class="mt-1" data-cart-variant>
                    <span class="inline-flex items-center gap-1 text-[10px] font-bold text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-950/60 px-2 py-0.5 rounded-md border border-indigo-200 dark:border-indigo-800/60">
                        <i class="ti ti-color-swatch text-xs"></i>
                        <span data-cart-variant-name></span>
                    </span>
                </div>
                <div class="text-xs text-emerald-600 dark:text-emerald-400 font-extrabold mt-1" data-cart-unit-price></div>
            </div>
        </div>

        <div class="flex items-center gap-2 shrink-0">
            <div class="flex items-center border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 rounded-xl overflow-hidden p-0.5 shadow-sm">
                <button type="button" data-cart-decrease aria-label="{{ __('Decrease') }}" class="w-6 h-6 flex items-center justify-center text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 rounded transition font-bold text-xs">-</button>
                <span class="w-7 text-center text-xs font-bold text-slate-900 dark:text-white" data-cart-qty></span>
                <button type="button" data-cart-increase aria-label="{{ __('Increase') }}" class="w-6 h-6 flex items-center justify-center text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 rounded transition font-bold text-xs">+</button>
            </div>
            <button type="button" data-cart-remove title="{{ __('Delete') }}" aria-label="{{ __('Delete') }}" class="text-rose-500 hover:text-rose-400 p-1.5 rounded-lg hover:bg-rose-500/10 transition cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                </svg>
            </button>
        </div>
    </div>
</template>

{{-- Checkout --}}
<div id="orderModal" class="fixed inset-0 z-50 bg-black/75 backdrop-blur-sm hidden flex items-center justify-center p-4" data-order-modal>
    <form class="bg-white dark:bg-[#111625] border border-slate-200 dark:border-slate-800 w-full max-w-md rounded-3xl p-6 shadow-2xl space-y-5 max-h-[92vh] overflow-y-auto"
        data-order-form novalidate>

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
            <button type="button" data-layer-close aria-label="{{ __('Close') }}"
                class="text-slate-400 hover:text-slate-600 dark:hover:text-white p-1 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition cursor-pointer">
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
                    <input type="text" id="cus_name" name="customer_name" autocomplete="name"
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
                    <input type="tel" inputmode="tel" id="cus_mobile" name="customer_phone" autocomplete="tel"
                        class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl pl-10 pr-3.5 py-2.5 text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/10 transition"
                        placeholder="{{ __('Country code + mobile number (e.g. 919876543210)') }}" required />
                </div>
            </div>

            <div data-address-field>
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
                    <textarea id="cus_address" name="delivery_address" rows="3"
                        class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl pl-10 pr-3.5 py-2.5 text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/10 transition resize-none"
                        placeholder="{{ __('Delivery address or email for digital files') }}"></textarea>
                </div>
            </div>

            <div>
                <label
                    class="block font-semibold text-slate-700 dark:text-slate-300 mb-1.5">{{ __('Order Notes (Optional)') }}</label>
                <textarea id="cus_notes" name="delivery_note" rows="2"
                    class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl px-3.5 py-2.5 text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/10 transition"
                    placeholder="{{ __('Any specific instructions or requirements...') }}"></textarea>
            </div>
        </div>

        <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-100 dark:border-slate-800/80">
            <button type="button" data-layer-close
                class="px-4 py-2.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold rounded-2xl text-xs transition cursor-pointer">
                {{ __('Close') }}
            </button>
            <button type="submit"
                class="flex-1 bg-emerald-600 hover:bg-emerald-500 text-white font-bold py-2.5 px-4 rounded-2xl text-xs flex items-center justify-center gap-2 transition shadow-lg shadow-emerald-600/20 cursor-pointer">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    class="icon icon-tabler icons-tabler-outline icon-tabler-brand-whatsapp w-4 h-4">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                    <path d="M3 21l1.65 -3.8a9 9 0 1 1 3.4 2.9l-5.05 .9" />
                    <path
                        d="M9 10a.5 .5 0 0 0 1 0v-1a.5 .5 0 0 0 -1 0v1a5 5 0 0 0 5 5h1a.5 .5 0 0 0 0 -1h-1a.5 .5 0 0 0 0 1" />
                </svg>
                <span>{{ __('Confirm on WhatsApp') }}</span>
            </button>
        </div>
    </form>
</div>
