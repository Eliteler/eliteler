
<!-- Dark Mode Compatible Styles for Language Switcher -->
<style>
    /* Light Mode Language Switcher */
    .language-dropdown-wrapper select,
    .language-dropdown-wrapper .btn,
    .language-dropdown-wrapper .dropdown-toggle {
        background-color: #f1f5f9 !important;
        color: #0f172a !important;
        border: 1px solid #cbd5e1 !important;
        border-radius: 10px !important;
        font-size: 12px !important;
        font-weight: 700 !important;
        padding: 5px 10px !important;
        display: inline-flex !important;
        align-items: center !important;
        box-shadow: none !important;
        text-decoration: none !important;
    }

    /* Dark Mode Language Switcher Fix */
    .dark .language-dropdown-wrapper select,
    .dark .language-dropdown-wrapper .btn,
    .dark .language-dropdown-wrapper .dropdown-toggle {
        background-color: #1e293b !important;
        color: #f8fafc !important;
        border-color: #334155 !important;
    }

    .dark .language-dropdown-wrapper select option {
        background-color: #0f172a !important;
        color: #f8fafc !important;
    }

    /* Dropdown Menu styling in Dark Mode */
    .dark .language-dropdown-wrapper .dropdown-menu {
        background-color: #0f172a !important;
        border: 1px solid #334155 !important;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.5) !important;
    }

    .dark .language-dropdown-wrapper .dropdown-item {
        color: #cbd5e1 !important;
    }

    .dark .language-dropdown-wrapper .dropdown-item:hover,
    .dark .language-dropdown-wrapper .dropdown-item:focus {
        background-color: #1e293b !important;
        color: #ffffff !important;
    }
</style>

<!-- Top Announcement Bar -->
@if (!empty($business_card_details->sub_title) || !empty($business_card_details->title2) || !empty($business_card_details->subtitle2))
    <div
        class="bg-indigo-900/90 dark:bg-gradient-to-r dark:from-indigo-900/60 dark:via-purple-900/40 dark:to-slate-900 text-white dark:text-indigo-200 text-xs py-2 px-4 text-center border-b border-indigo-500/20">
        @include('templates.stores.shared.subtitle-lines')
        {{ $business_card_details->sub_title }}
    </div>
@endif

<!-- DigiVault Header / Navbar -->
<nav
    class="sticky top-0 z-40 bg-white/90 dark:bg-[#0b0f19]/90 backdrop-blur-xl border-b border-slate-200 dark:border-slate-800/80 px-4 py-3">
    <div class="max-w-6xl mx-auto flex items-center justify-between">

        <!-- Logo & Store Title -->
        <a href="{{ $storeUrl }}" class="flex items-center gap-2.5">
            <img src="{{ url($business_card_details->profile) }}" alt="{{ $business_card_details->title }}"
                class="w-9 h-9 rounded-xl object-cover shadow-md border border-slate-200 dark:border-slate-700">

            <span
                class="font-extrabold text-lg tracking-tight bg-gradient-to-r from-slate-900 via-indigo-900 to-indigo-600 dark:from-white dark:via-slate-200 dark:to-slate-400 bg-clip-text text-transparent">
                {{ $card_details->title ?? $business_card_details->title }}
            </span>
        </a>

        <!-- Controls Area -->
        <div class="flex items-center gap-2.5 sm:gap-3">

            {{-- Language Switcher Include --}}
            @if (
                $business_card_details->is_enable_language_switcher == 1 &&
                    is_array(config('app.languages')) &&
                    count(config('app.languages')) > 1)
                <div class="language-dropdown-wrapper">
                    @include('templates.stores.digital-products.includes.language-switcher', [
                        'rounded' => 'rounded-xl',
                    ])
                </div>
            @endif

            <!-- Dark / Light Theme Toggle -->
            <button type="button" onclick="toggleTheme()"
                class="p-2 rounded-xl bg-slate-100 dark:bg-slate-800/80 text-slate-700 dark:text-slate-300 hover:text-indigo-600 dark:hover:text-indigo-400 border border-slate-300 dark:border-slate-700 transition"
                aria-label="{{ __('Toggle Theme') }}">
                <svg id="theme-icon-sun" class="w-4 h-4 hidden dark:block" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 3v1m0 16v1m9-9h-1M4 9H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z">
                    </path>
                </svg>
                <svg id="theme-icon-moon" class="w-4 h-4 block dark:hidden" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z">
                    </path>
                </svg>
            </button>

            <!-- Cart Trigger Button -->
            <button type="button" data-cart-open
                class="relative bg-indigo-600 hover:bg-indigo-500 text-white p-2 sm:p-2.5 rounded-xl shadow-md transition"
                aria-label="{{ __('Open Cart') }}">
                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                </svg>
                <span data-cart-count data-cart-when-filled hidden
                    class="absolute -top-1 -right-1 bg-rose-500 text-white text-[10px] font-bold w-4 h-4 rounded-full flex items-center justify-center">0</span>
            </button>

        </div>
    </div>
</nav>

