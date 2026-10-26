@php
    $isHomeActive = $storefront->onHome();
    $isCategoriesActive = $storefront->onCategories();
@endphp

<!-- 1. 2026 DigiVault Footer Section -->
<footer
    class="border-t border-slate-200 dark:border-slate-800/80 mt-20 bg-white dark:bg-[#070b12] pt-12 pb-24 md:pb-12 transition-colors">
    <div class="max-w-6xl mx-auto px-4 space-y-8">

        {{-- Eliteler: store map --}}
        @include('templates.stores.shared.store-map', [
            'mapWrapperClass' => '',
            'mapCardClass' => 'p-2 rounded-2xl bg-slate-100 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700/60 shadow-sm overflow-hidden',
        ])

        <!-- Brand Profile Logo & Socials -->
        <div class="flex flex-col items-center text-center space-y-3">
            <img src="{{ url($business_card_details->profile) }}" alt="{{ $card_details->title }}"
                class="w-11 h-11 rounded-2xl object-cover shadow-sm border border-slate-200 dark:border-slate-700">

            <div class="mb-2">
                <div class="flex items-center justify-center gap-2 mb-2">
                    <span
                        class="font-extrabold text-base tracking-tight text-slate-900 dark:text-white">{{ $card_details->title }}</span>
                </div>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                    {{ __(json_decode($business_card_details->description)->whatsapp_msg ?? 'Instant Digital Delivery via Automated WhatsApp Engine') }}
                </p>
            </div>

            {{-- Social Share Icons --}}
            <div class="flex flex-wrap items-center justify-center gap-2 pt-1">
                {{-- Eliteler: the store's social links if it has any, otherwise the share links --}}
                @if (isset($storeSocialLinks) && $storeSocialLinks->where('type', '!=', 'map')->isNotEmpty())
                    @include('templates.stores.shared.social-links', [
                        'socialLinkClass' => 'w-9 h-9 rounded-xl bg-slate-100 dark:bg-slate-800/80 hover:bg-indigo-600 hover:text-white text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700/60 flex items-center justify-center transition shadow-sm',
                        'socialIconClass' => 'text-sm',
                    ])
                @else
                @if (!empty($shareComponent['facebook']))
                    <a href="{{ $shareComponent['facebook'] }}" target="_blank"
                        class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-slate-800/80 hover:bg-indigo-600 hover:text-white text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700/60 flex items-center justify-center transition shadow-sm"
                        aria-label="{{ __('Facebook') }}">
                        <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                            <path
                                d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z" />
                        </svg>
                    </a>
                @endif
                @if (!empty($shareComponent['twitter']))
                    <a href="{{ $shareComponent['twitter'] }}" target="_blank"
                        class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-slate-800/80 hover:bg-indigo-600 hover:text-white text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700/60 flex items-center justify-center transition shadow-sm"
                        aria-label="{{ __('Twitter') }}">
                        <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24">
                            <path
                                d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z" />
                        </svg>
                    </a>
                @endif
                @if (!empty($shareComponent['linkedin']))
                    <a href="{{ $shareComponent['linkedin'] }}" target="_blank"
                        class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-slate-800/80 hover:bg-indigo-600 hover:text-white text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700/60 flex items-center justify-center transition shadow-sm"
                        aria-label="{{ __('LinkedIn') }}">
                        <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                            <path
                                d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z" />
                        </svg>
                    </a>
                @endif
                @if (!empty($shareComponent['telegram']))
                    <a href="{{ $shareComponent['telegram'] }}" target="_blank"
                        class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-slate-800/80 hover:bg-indigo-600 hover:text-white text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700/60 flex items-center justify-center transition shadow-sm"
                        aria-label="{{ __('Telegram') }}">
                        <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                            <path
                                d="M12 0c-6.627 0-12 5.373-12 12s5.373 12 12 12 12-5.373 12-12-5.373-12-12-12zm5.894 8.221l-1.97 9.28c-.145.658-.537.818-1.084.508l-3-2.21-1.446 1.394c-.14.18-.357.295-.6.295-.002 0-.003 0-.005 0l.213-3.054 5.56-5.022c.24-.213-.054-.334-.373-.121l-6.869 4.326-2.96-.924c-.643-.204-.657-.643.136-.953l11.57-4.461c.537-.197 1.006.128.832.941z" />
                        </svg>
                    </a>
                @endif
                @if (!empty($shareComponent['whatsapp']))
                    <a href="{{ $shareComponent['whatsapp'] }}" target="_blank"
                        class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-slate-800/80 hover:bg-indigo-600 hover:text-white text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700/60 flex items-center justify-center transition shadow-sm"
                        aria-label="{{ __('WhatsApp') }}">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round"
                            class="icon icon-tabler icons-tabler-outline icon-tabler-brand-whatsapp w-4 h-4">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                            <path d="M3 21l1.65 -3.8a9 9 0 1 1 3.4 2.9l-5.05 .9" />
                            <path
                                d="M9 10a.5 .5 0 0 0 1 0v-1a.5 .5 0 0 0 -1 0v1a5 5 0 0 0 5 5h1a.5 .5 0 0 0 0 -1h-1a.5 .5 0 0 0 0 1" />
                        </svg>
                    </a>
                @endif
                @endif
            </div>
        </div>

        <!-- Policy Links -->
        <div class="border-t border-slate-200/80 dark:border-slate-800/80 pt-6">
            <div
                class="grid grid-cols-2 sm:flex sm:flex-wrap items-center justify-center gap-x-6 gap-y-3 text-xs font-medium text-slate-500 dark:text-slate-400 text-center">
                @if ($business_card_details->privacy_policy)
                    <a href="{{ route('store.privacy.policy', $business_card_details->card_url) }}"
                        class="hover:text-indigo-600 dark:hover:text-indigo-400 transition py-1">{{ __('Privacy Policy') }}</a>
                @endif
                @if ($business_card_details->terms_and_conditions)
                    <a href="{{ route('store.terms.and.conditions', $business_card_details->card_url) }}"
                        class="hover:text-indigo-600 dark:hover:text-indigo-400 transition py-1">{{ __('Terms & Conditions') }}</a>
                @endif
                @if ($business_card_details->refund_policy)
                    <a href="{{ route('store.refund.policy', $business_card_details->card_url) }}"
                        class="hover:text-indigo-600 dark:hover:text-indigo-400 transition py-1">{{ __('Return/Refund Policy') }}</a>
                @endif
                @if ($business_card_details->shipping_policy)
                    <a href="{{ route('store.shipping.policy', $business_card_details->card_url) }}"
                        class="hover:text-indigo-600 dark:hover:text-indigo-400 transition py-1">{{ __('Shipping Policy') }}</a>
                @endif
                @if ($business_card_details->cookie_policy)
                    <a href="{{ route('store.cookie.policy', $business_card_details->card_url) }}"
                        class="hover:text-indigo-600 dark:hover:text-indigo-400 transition py-1">{{ __('Cookie Policy') }}</a>
                @endif
                @if ($business_card_details->customer_support_policy)
                    <a href="{{ route('store.contact.information', $business_card_details->card_url) }}"
                        class="hover:text-indigo-600 dark:hover:text-indigo-400 transition py-1">{{ __('Contact Us') }}</a>
                @endif
            </div>
        </div>

        <!-- Copyright & Security Note -->
        <div class="border-t border-slate-200/60 dark:border-slate-800/50 pt-5 text-center space-y-2">
            <p class="text-xs text-slate-500 dark:text-slate-400">
                {{ __('Copyright ©') }} {{ date('Y') }}
                {{-- Eliteler: copyright link --}}
                @include('templates.stores.shared.copyright-link', ['copyrightLinkClass' => 'font-semibold text-slate-700 dark:text-slate-200 underline']).
                {{ __('All rights reserved.') }}
            </p>
            <div class="flex items-center justify-center gap-2 text-[11px] text-slate-400 dark:text-slate-500">
                <span>🔒 {{ __('SSL 256-Bit Encrypted') }}</span>
            </div>
        </div>

    </div>
</footer>

<!-- 2. Mobile Responsive Bottom Navigation Bar -->
<nav
    class="fixed md:hidden bottom-0 left-0 right-0 z-40 bg-white/95 dark:bg-[#0b0f19]/95 backdrop-blur-xl border-t border-slate-200 dark:border-slate-800 py-2.5 px-6 flex justify-around items-center shadow-2xl d-print-none">

    {{-- Home Link --}}
    <a href="{{ $storeUrl }}"
        class="flex flex-col items-center gap-1 transition {{ $isHomeActive ? 'text-indigo-600 dark:text-indigo-400 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400' }}">
        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none"
            stroke="currentColor" stroke-width="{{ $isHomeActive ? '2.4' : '2' }}" stroke-linecap="round"
            stroke-linejoin="round">
            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
            <path d="M5 12l-2 0l9 -9l9 9l-2 0" />
            <path d="M5 12v7a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-7" />
            <path d="M9 21v-6a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v6" />
        </svg>
        <span class="text-[10px] font-semibold">{{ __('Home') }}</span>
    </a>

    {{-- Categories Link --}}
    <a href="{{ $storefront->categoriesUrl }}"
        class="flex flex-col items-center gap-1 transition {{ $isCategoriesActive ? 'text-indigo-600 dark:text-indigo-400 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400' }}">
        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none"
            stroke="currentColor" stroke-width="{{ $isCategoriesActive ? '2.4' : '2' }}" stroke-linecap="round"
            stroke-linejoin="round">
            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
            <path d="M4 4h6v6h-6z" />
            <path d="M14 4h6v6h-6z" />
            <path d="M4 14h6v6h-6z" />
            <path d="M17 17m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0" />
        </svg>
        <span class="text-[10px] font-semibold">{{ __('Categories') }}</span>
    </a>

    {{-- Cart Trigger Link --}}
    <button type="button" data-cart-open
        class="flex flex-col items-center gap-1 text-slate-600 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition relative focus:outline-none">

        <div class="relative">
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24"
                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                stroke-linejoin="round">
                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                <path
                    d="M6.331 8h11.339a2 2 0 0 1 1.977 2.304l-1.255 8.152a3 3 0 0 1 -2.966 2.544h-6.852a3 3 0 0 1 -2.965 -2.544l-1.255 -8.152a2 2 0 0 1 1.977 -2.304z" />
                <path d="M9 11v-5a3 3 0 0 1 6 0v5" />
            </svg>

            <!-- Dynamic Cart Counter Badge (Compatible with data-cart-count and custom scripts) -->
            <span data-cart-count data-cart-when-filled id="mobile-cart-counter"
                class="absolute -top-1.5 -right-2.5 bg-rose-500 text-white text-[9px] font-bold min-w-[16px] h-4 px-1 rounded-full flex items-center justify-center ring-2 ring-white dark:ring-[#0b0f19] leading-none shrink-0">
                0
            </span>
        </div>
        <span class="text-[10px] font-semibold">{{ __('Cart') }}</span>
    </button>

</nav>
