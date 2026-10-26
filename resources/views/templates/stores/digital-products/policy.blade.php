<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth dark" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">

<head>
    @include('templates.stores.digital-products.includes.head')

    <style>
        /* Policy Article Typography */
        .policy-content {
            line-height: 1.8;
            font-size: 0.925rem;
        }

        .policy-content h1,
        .policy-content h2,
        .policy-content h3,
        .policy-content h4 {
            font-weight: 800;
            margin-top: 1.5rem;
            margin-bottom: 0.75rem;
            letter-spacing: -0.02em;
        }

        .policy-content p {
            margin-bottom: 1.25rem;
        }

        .policy-content ul,
        .policy-content ol {
            margin-left: 1.5rem;
            margin-bottom: 1.25rem;
        }

        .policy-content li {
            margin-bottom: 0.5rem;
        }

        .policy-content a {
            color: #6366f1;
            text-decoration: underline;
        }
    </style>

    @include('templates.stores.shared.custom-code')
    @stack('page-css')
</head>

<body
    class="bg-slate-50 dark:bg-[#0b0f19] text-slate-800 dark:text-slate-100 min-h-screen antialiased transition-colors duration-300 relative pb-14 md:pb-0"
    dir="{{ $isRtl ? 'rtl' : 'ltr' }}">

    <!-- Fullscreen Preloader -->
    @include('templates.stores.digital-products.includes.preloader')


    <!-- Header Include -->
    @include('templates.stores.digital-products.includes.header')

    <!-- Main Content -->
    <main class="max-w-4xl mx-auto px-4 py-6 sm:py-8 space-y-6">

        <!-- Top Row: Breadcrumbs & Back Button (Overflow Protected) -->
        <div class="flex items-center justify-between gap-3 border-b border-slate-200/80 dark:border-slate-800/80 pb-4">
            
            {{-- Left: Breadcrumbs with min-w-0 to allow proper truncation --}}
            <div class="flex items-center gap-1.5 text-xs font-semibold text-slate-500 dark:text-slate-400 min-w-0 flex-1">
                <a href="{{ $storeUrl }}"
                    class="hover:text-indigo-600 dark:hover:text-indigo-400 transition shrink-0">{{ __('Home') }}</a>
                <span class="text-slate-300 dark:text-slate-600 shrink-0">&rsaquo;</span>
                <span class="text-indigo-600 dark:text-indigo-400 font-bold truncate" title="{{ __($pageTitle) }}">
                    {{ __($pageTitle) }}
                </span>
            </div>

            {{-- Right: Back to Store Button --}}
            <a href="{{ $storeUrl }}"
                class="inline-flex items-center gap-1.5 bg-white dark:bg-slate-800/90 hover:bg-indigo-600 hover:text-white text-slate-700 dark:text-slate-200 font-semibold px-3 py-1.5 sm:px-3.5 sm:py-2 rounded-xl text-xs border border-slate-200 dark:border-slate-700/80 transition shadow-xs shrink-0 whitespace-nowrap">
                <i class="ti ti-arrow-left text-xs"></i>
                <span>{{ __('Back to Store') }}</span>
            </a>

        </div>

        <!-- Policy Card -->
        <div
            class="bg-white dark:bg-[#111625] border border-slate-200 dark:border-slate-800/80 rounded-3xl p-6 sm:p-10 shadow-xl space-y-6">

            <!-- Heading with Icon -->
            <div class="border-b border-slate-100 dark:border-slate-800/80 pb-6 flex items-center gap-4">
                <div
                    class="w-12 h-12 rounded-2xl bg-indigo-50 dark:bg-indigo-950/50 border border-indigo-200 dark:border-indigo-800/50 text-indigo-600 dark:text-indigo-400 flex items-center justify-center flex-shrink-0 shadow-sm">
                    <i class="ti ti-shield-check text-2xl"></i>
                </div>
                <div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight m-0">
                        {{ __($pageTitle) }}
                    </h1>
                    <p class="text-xs text-slate-400 dark:text-slate-500 mt-1 m-0">
                        {{ __('Official store legal documentation and user privacy guidelines.') }}
                    </p>
                </div>
            </div>

            <!-- Policy Dynamic Body -->
            <div class="policy-content text-slate-600 dark:text-slate-300">
                {!! $pageContent ?? '' !!}
            </div>

        </div>

    </main>

    <!-- Bottom Bar Component Include (Footer + Mobile Navigation) -->
    @include('templates.stores.digital-products.includes.bottom-bar')

    <!-- WhatsApp Float Component Include -->
    @include('templates.stores.digital-products.includes.whatsapp-float', [
        'businessImage' => $business_card_details->profile,
        'businessName' => $card_details->title,
        'whatsappNumber' => $enquiry_button,
    ])

    {{-- Cart, checkout and the shared storefront script --}}
    @include('templates.stores.digital-products.partials.cart-order-modal')
    @include('templates.stores.digital-products.includes.scripts')

    @stack('custom-js')
</body>

</html>
