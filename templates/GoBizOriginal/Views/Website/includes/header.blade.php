@php
    // Settings
    use App\Services\GoBizCommonService;

    $setting = GoBizCommonService::settings();
    $pages = GoBizCommonService::templatePageContentAll('GoBizOriginal');
    $homePage = GoBizCommonService::templatePageContentGet('home', 'GoBizOriginal');

    // Check if the website is down for maintenance
    $isDown = app()->isDownForMaintenance();

    // Check user role
    $user = Auth::user();

    $modernDashboardEnabled =
        Schema::hasTable('modern_dashboard_settings') && is_dir(base_path('plugins/ModernDashboard'))
            ? (int) DB::table('modern_dashboard_settings')->value('modern_dashboard_enabled')
            : 0;

    $dashboardUrl =
        $user && $user->role_id != 2
            ? route('admin.dashboard')
            : route(
                $modernDashboardEnabled && $user && $user->role_id == 2 ? 'user.dashboard.overview' : 'user.dashboard',
            );
@endphp

<style>
    /* Prevent background page scroll when mobile menu is active */
    .no-scroll {
        overflow: hidden !important;
        height: 100vh !important;
        touch-action: none !important;
    }

    /* Default: Hide the menu */
    .mobile-menu {
        display: none;
    }

    /* Hide scrollbar but allow scrolling */
    .scrollbar-hidden {
        overflow-y: scroll;
        scrollbar-width: none;
        -ms-overflow-style: none;
    }

    /* For WebKit-based browsers */
    .scrollbar-hidden::-webkit-scrollbar {
        display: none;
    }

    .w-50 {
        width: 500px !important;
    }

    .x-overflow-hidden {
        overflow-x: hidden;
    }

    .y-overflow-scroll {
        overflow-y: scroll;
    }

    .h-auto {
        height: auto;
        max-height: 15rem;
    }

    /* Show the mobile menu between 1024px and 1435px */
    @media screen and (min-width: 1024px) and (max-width: 1435px) {
        .mobile-menu {
            display: block;
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            width: 66.67%;
            max-width: 320px;
            z-index: 50;
            background: white;
        }

        .mobile-menu-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(31, 41, 55, 0.8);
            z-index: 49;
        }
    }

    /* Adjust link spacing on laptop screens without shrinking logo space */
    @media screen and (min-width: 1280px) and (max-width: 1440px) {
        .demo-nav-link {
            margin-right: 1.75rem !important;
        }
    }

    /* RTL Language Dropdown Alignment & Width Fix */
    html[dir="rtl"] #languageDropdown,
    [dir="rtl"] #languageDropdown,
    html[dir="rtl"] .journal-scroll {
        left: 0 !important;
        right: auto !important;
        margin-left: 0 !important;
        margin-right: 0 !important;
    }

    /* Prevent clipping of long language names */
    #languageDropdown,
    .journal-scroll {
        min-width: 12rem !important;
        width: max-content !important;
        max-width: 90vw !important;
        white-space: nowrap !important;
    }

    /* RTL Logo Margin Fix: Remove mr-14 in RTL mode */
    html[dir="rtl"] .logo-wrapper,
    [dir="rtl"] .logo-wrapper {
        margin-right: 0 !important;
    }

    /* Apply margin-left in RTL mode only for desktop */
    @media screen and (min-width: 1280px) {

        html[dir="rtl"] .logo-wrapper,
        [dir="rtl"] .logo-wrapper {
            margin-left: 4rem !important;
            margin-right: 0 !important;
        }
    }

    /* Dropdown vertical offset & floating effect */
    #languageDropdown,
    #dropdown {
        margin-top: 0.75rem !important;
        border-radius: 0.75rem !important;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.05) !important;
    }
</style>

<section
    class="bg-{{ request()->is('/') ? 'gradient-' . $template_config->template_color . '2' : 'white' }} lg:px-20 background-animate">
    <section>
        <div class="flex items-center justify-between px-8 py-5">
            {{-- Website menu --}}
            <div class="w-auto">
                <div class="flex flex-nowrap items-center">
                    <!-- Adjusted logo spacing to mr-14 lg:mr-16 2xl:mr-20 to match demo gap perfectly -->
                    <div class="logo-wrapper demo-logo-wrapper mr-14 lg:mr-16 2xl:mr-20 flex-shrink-0">
                        <a href="/">
                            <img src="{{ asset($settings->site_logo) }}" alt="{{ config('app.name') }}"
                                class="site-logo">
                        </a>
                    </div>
                    @if ($config[38]->config_value != 'no')
                        <div class="w-auto">
                            <div class="flex flex-wrap items-center">
                                <div class="w-auto hidden xl:block">
                                    <ul class="flex items-center mr-10">
                                        @if ($pages[204]->page_name == 'about' && $pages[204]->status == 'active')
                                            <li
                                                class="font-heading demo-nav-link mr-9 text-gray-900 hover:text-gray-700 {{ App::isLocale('ru') ? 'text-md' : 'text-lg' }} whitespace-nowrap">
                                                <a href="{{ route('about') }}">{{ __('About') }}</a>
                                            </li>
                                        @endif
                                        <li
                                            class="font-heading demo-nav-link mr-9 text-gray-900 hover:text-gray-700 {{ App::isLocale('ru') ? 'text-md' : 'text-lg' }} whitespace-nowrap truncate">
                                            <a
                                                href="{{ route('home-locale') }}#how-it-works">{{ __('How it works?') }}</a>
                                        </li>
                                        <li
                                            class="font-heading demo-nav-link mr-9 text-gray-900 hover:text-gray-700 {{ App::isLocale('ru') ? 'text-md' : 'text-lg' }} whitespace-nowrap">
                                            <a href="{{ route('home-locale') }}#features">{{ __('Features') }}</a>
                                        </li>
                                        <li
                                            class="font-heading demo-nav-link mr-9 text-gray-900 hover:text-gray-700 {{ App::isLocale('ru') ? 'text-md' : 'text-lg' }} whitespace-nowrap">
                                            <a href="{{ route('home-locale') }}#pricing">{{ __('Pricing') }}</a>
                                        </li>
                                        @if ($pages[195]->page_name == 'contact' && $pages[195]->status == 'active')
                                            <li
                                                class="font-heading demo-nav-link mr-9 text-gray-900 hover:text-gray-700 {{ App::isLocale('ru') ? 'text-md' : 'text-lg' }} whitespace-nowrap">
                                                <a href="{{ route('contact') }}">{{ __('Contact') }}</a>
                                            </li>
                                        @endif

                                        {{-- Check webtools --}}
                                        @if ($config[88]->config_value == '1')
                                            <li class="relative">
                                                <button
                                                    class="font-heading demo-nav-link mr-9 text-gray-900 {{ App::isLocale('ru') ? 'text-md' : 'text-lg' }} text-center inline-flex items-center whitespace-nowrap truncate"
                                                    type="button" data-dropdown-toggle="dropdown"
                                                    data-dropdown-offset-distance="12">
                                                    {{ __('Web Tools') }}
                                                    <svg class="w-4 h-4 ml-2 rtl:mr-2 rtl:ml-0" fill="none"
                                                        stroke="currentColor" viewBox="0 0 24 24"
                                                        xmlns="http://www.w3.org/2000/svg">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                                    </svg>
                                                </button>

                                                <div class="hidden bg-white text-base z-50 list-none divide-y divide-gray-100 rounded-xl shadow-xl my-4 w-44 h-60 scrollbar-hidden"
                                                    id="dropdown">
                                                    <ul class="py-1" aria-labelledby="dropdown">
                                                        <li>
                                                            <a class="block px-4 py-2 mt-2 text-base capitalize font-semi-bold bg-transparent rounded-sm hover:bg-gray-100"
                                                                href="{{ route('web.html.beautifier') }}">{{ __('HTML Beautifier') }}</a>
                                                        </li>
                                                        <li>
                                                            <a class="block px-4 py-2 mt-2 text-base capitalize font-semi-bold bg-transparent rounded-sm hover:bg-gray-100"
                                                                href="{{ route('web.html.minifier') }}">{{ __('HTML Minifier') }}</a>
                                                        </li>
                                                        <li>
                                                            <a class="block px-4 py-2 mt-2 text-base capitalize font-semi-bold bg-transparent rounded-sm hover:bg-gray-100"
                                                                href="{{ route('web.css.beautifier') }}">{{ __('CSS Beautifier') }}</a>
                                                        </li>
                                                        <li>
                                                            <a class="block px-4 py-2 mt-2 text-base capitalize font-semi-bold bg-transparent rounded-sm hover:bg-gray-100"
                                                                href="{{ route('web.css.minifier') }}">{{ __('CSS Minifier') }}</a>
                                                        </li>
                                                        <li>
                                                            <a class="block px-4 py-2 mt-2 text-base capitalize font-semi-bold bg-transparent rounded-sm hover:bg-gray-100"
                                                                href="{{ route('web.js.beautifier') }}">{{ __('JS Beautifier') }}</a>
                                                        </li>
                                                        <li>
                                                            <a class="block px-4 py-2 mt-2 text-base capitalize font-semi-bold bg-transparent rounded-sm hover:bg-gray-100"
                                                                href="{{ route('web.js.minifier') }}">{{ __('JS Minifier') }}</a>
                                                        </li>
                                                        <li>
                                                            <a class="block px-4 py-2 mt-2 text-base capitalize font-semi-bold bg-transparent rounded-sm hover:bg-gray-100"
                                                                href="{{ route('web.random.password.generator') }}">{{ __('Random Password Generator') }}</a>
                                                        </li>
                                                        <li>
                                                            <a class="block px-4 py-2 mt-2 text-base capitalize font-semi-bold bg-transparent rounded-sm hover:bg-gray-100"
                                                                href="{{ route('web.bcrypt.password.generator') }}">{{ __('Bcrypt Password Generator') }}</a>
                                                        </li>
                                                        <li>
                                                            <a class="block px-4 py-2 mt-2 text-base capitalize font-semi-bold bg-transparent rounded-sm hover:bg-gray-100"
                                                                href="{{ route('web.md5.password.generator') }}">{{ __('MD5 Password Generator') }}</a>
                                                        </li>
                                                        <li>
                                                            <a class="block px-4 py-2 mt-2 text-base capitalize font-semi-bold bg-transparent rounded-sm hover:bg-gray-100"
                                                                href="{{ route('web.random.word.generator') }}">{{ __('Random Word Generator') }}</a>
                                                        </li>
                                                        <li>
                                                            <a class="block px-4 py-2 mt-2 text-base capitalize font-semi-bold bg-transparent rounded-sm hover:bg-gray-100"
                                                                href="{{ route('web.text.counter') }}">{{ __('Text Counter') }}</a>
                                                        </li>
                                                        <li>
                                                            <a class="block px-4 py-2 mt-2 text-base capitalize font-semi-bold bg-transparent rounded-sm hover:bg-gray-100"
                                                                href="{{ route('web.lorem.generator') }}">{{ __('Lorem Generator') }}</a>
                                                        </li>
                                                        <li>
                                                            <a class="block px-4 py-2 mt-2 text-base capitalize font-semi-bold hover:bg-gray-100"
                                                                href="{{ route('web.emojies') }}">{{ __('Emojies') }}</a>
                                                        </li>
                                                        <li>
                                                            <a class="block px-4 py-2 mt-2 text-base capitalize font-semi-bold hover:bg-gray-100"
                                                                href="{{ route('web.dns.lookup') }}">{{ __('DNS Lookup') }}</a>
                                                        </li>
                                                        <li>
                                                            <a class="block px-4 py-2 mt-2 text-base capitalize font-semi-bold hover:bg-gray-100"
                                                                href="{{ route('web.ip.lookup') }}">{{ __('IP Address Lookup') }}</a>
                                                        </li>
                                                        <li>
                                                            <a class="block px-4 py-2 mt-2 text-base capitalize font-semi-bold hover:bg-gray-100"
                                                                href="{{ route('web.whois.lookup') }}">{{ __('WHOIS Lookup') }}</a>
                                                        </li>
                                                        <li>
                                                            <a class="block px-4 py-2 mt-2 text-base capitalize font-semi-bold hover:bg-gray-100"
                                                                href="{{ route('web.qrcode') }}">{{ __('QR Code Maker') }}</a>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </li>
                                        @endif
                                    </ul>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Right side buttons --}}
            <div class="w-auto flex-shrink-0">
                <div class="flex items-center">
                    <div class="w-auto hidden xl:block">
                        <div class="flex flex-nowrap items-center">
                            {{-- Check maintenance mode --}}
                            @if (!$isDown)
                                {{-- Languages --}}
                                <div class="w-auto">
                                    @if (count(config('app.languages')) > 1)
                                        <div class="relative">
                                            <button
                                                class="font-heading mr-4 rtl:ml-4 rtl:mr-0 text-gray-900 {{ App::isLocale('ru') ? 'text-md' : 'text-lg' }} text-center inline-flex items-center whitespace-nowrap"
                                                type="button" data-dropdown-toggle="languageDropdown"
                                                data-dropdown-offset-distance="12">
                                                {{ strtoupper(app()->getLocale()) }}
                                                <svg class="w-4 h-4 ml-1 rtl:mr-1 rtl:ml-0" fill="none"
                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M19 9l-7 7-7-7" />
                                                </svg>
                                            </button>

                                            <!-- Dropdown with floating offset -->
                                            <div id="languageDropdown"
                                                class="hidden bg-white text-base z-50 list-none divide-y divide-gray-100 rounded-xl shadow-xl my-4 min-w-[12rem] w-auto h-auto max-h-80 scrollbar-hidden ltr:right-0 rtl:left-0 py-2">
                                                <ul class="py-1">
                                                    @foreach (config('app.languages') as $langLocale => $langName)
                                                        <li>
                                                            <a class="block px-4 py-2 text-base capitalize font-medium text-gray-700 hover:bg-gray-100 hover:text-gray-900 whitespace-nowrap transition duration-150"
                                                                href="{{ url()->current() }}?change_language={{ $langLocale }}">
                                                                {{ strtoupper($langName) }}
                                                            </a>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @endif

                            {{-- Guest user --}}
                            @if ($config[38]->config_value != 'no')
                                @guest
                                    <div class="w-auto">
                                        <a class="font-heading block py-2 px-5 mr-5 rtl:ml-5 rtl:mr-0 {{ App::isLocale('ru') ? 'text-md' : 'text-lg' }} text-gray-900 rounded-xl whitespace-nowrap"
                                            href="{{ route('login') }}">{{ __('Sign In') }}</a>
                                    </div>

                                    @if (Route::has('register'))
                                        <div class="w-auto">
                                            <a class="group relative font-heading block py-2 px-5 {{ App::isLocale('ru') ? 'text-md' : 'text-lg' }} text-gray-900 border-2 border-gray-900 overflow-hidden rounded-xl whitespace-nowrap flex-shrink-0"
                                                href="{{ route('register') }}">
                                                <div
                                                    class="absolute top-0 left-0 transform -translate-y-full group-hover:-translate-y-0 h-full w-full bg-gray-900 transition ease-in-out duration-500">
                                                </div>
                                                <p class="relative z-10 group-hover:text-white">{{ __('Sign Up') }}</p>
                                            </a>
                                        </div>
                                    @endif
                                @else
                                    {{-- Check maintenance mode --}}
                                    @if (!$isDown)
                                        <div class="w-auto">
                                            <a class="group relative font-heading block py-2 px-5 {{ App::isLocale('ru') ? 'text-md' : 'text-lg' }} text-gray-900 border-2 border-gray-900 overflow-hidden rounded-xl whitespace-nowrap flex-shrink-0"
                                                href="{{ $dashboardUrl }}">
                                                <div
                                                    class="absolute top-0 left-0 transform -translate-y-full group-hover:-translate-y-0 h-full w-full bg-gray-900 transition ease-in-out duration-500">
                                                </div>
                                                <p class="relative z-10 group-hover:text-white">{{ __('Dashboard') }}</p>
                                            </a>
                                        </div>
                                    @endif
                                @endguest
                            @endif
                        </div>
                    </div>
                    <div class="w-auto xl:hidden">
                        @if ($config[38]->config_value != 'no')
                            <a href="#" class="navbar-burger-open inline-block">
                                <svg class="navbar-burger text-gray-800" width="42" height="42"
                                    viewbox="0 0 56 56" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <rect width="56" height="56" rx="28" fill="currentColor"></rect>
                                    <path d="M37 32H19M37 24H19" stroke="white" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round"></path>
                                </svg>
                            </a>
                        @else
                            {{-- Check maintenance mode --}}
                            @if (!$isDown)
                                @if (count(config('app.languages')) > 1)
                                    <div @click.away="open = false" class="cursor-pointer inline-block py-2 relative"
                                        x-data="{ open: false }">
                                        <a @click="open = !open" href="#"
                                            class="font-heading mr-2 rtl:ml-2 rtl:mr-0 text-gray-900 hover:text-gray-700 text-lg">
                                            <span>{{ strtoupper(app()->getLocale()) }}</span>
                                            <svg fill="currentColor" viewBox="0 0 20 20"
                                                :class="{ 'rotate-180': open, 'rotate-0': !open }"
                                                class="inline w-4 h-4 ml-1 rtl:mr-1 rtl:ml-0 transition-transform duration-200 transform">
                                                <path fill-rule="evenodd"
                                                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                    clip-rule="evenodd"></path>
                                            </svg>
                                        </a>
                                        <div x-show="open" x-transition
                                            class="journal-scroll absolute max-h-80 overflow-y-scroll mt-3 rounded-xl shadow-xl z-50 bg-white min-w-[12rem] w-auto ltr:right-0 rtl:left-0">
                                            <div class="px-2 py-2 capitalize rounded-sm shadow">
                                                @foreach (config('app.languages') as $langLocale => $langName)
                                                    <a class="block px-4 py-2.5 text-sm capitalize font-medium hover:bg-gray-100 whitespace-nowrap"
                                                        href="{{ url()->current() }}?change_language={{ $langLocale }}">{{ strtoupper($langName) }}</a>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Mobile menu --}}
        <div class="hidden navbar-menu fixed top-0 left-0 bottom-0 w-4/6 sm:max-w-xs z-50">
            <div class="navbar-backdrop fixed inset-0 bg-gray-800 opacity-80"></div>
            <nav class="relative z-10 px-9 py-8 bg-white h-full x-overflow-hidden y-overflow-scroll">
                <div class="flex flex-wrap justify-between h-full">
                    <div class="w-full">
                        <div class="flex items-center justify-between -m-2">
                            <div class="w-auto p-2">
                                <a class="inline-block" href="/">
                                    <img src="{{ asset($settings->site_logo) }}" alt="{{ config('app.name') }}"
                                        class="site-logo">
                                </a>
                            </div>
                            <div class="w-auto p-2">
                                <a class="navbar-close" href="#">
                                    <svg width="24" height="24" viewbox="0 0 24 24" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path d="M6 18L18 6M6 6L18 18" stroke="#111827" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round"></path>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="flex flex-col justify-center py-8 w-full">
                        <ul>
                            @if ($pages[204]->page_name == 'about' && $pages[204]->status == 'active')
                                <li class="mb-6"><a
                                        class="font-heading font-medium text-base text-gray-900 hover:text-gray-700"
                                        href="{{ route('about') }}">{{ __('About') }}</a></li>
                            @endif
                            <li class="mb-6"><a
                                    class="font-heading font-medium text-base text-gray-900 hover:text-gray-700"
                                    href="{{ route('home-locale') }}#how-it-works">{{ __('How it works?') }}</a></li>
                            <li class="mb-6"><a
                                    class="font-heading font-medium text-base text-gray-900 hover:text-gray-700"
                                    href="{{ route('home-locale') }}#features">{{ __('Features') }}</a></li>
                            <li class="mb-6"><a
                                    class="font-heading font-medium text-base text-gray-900 hover:text-gray-700"
                                    href="{{ route('home-locale') }}#pricing">{{ __('Pricing') }}</a></li>
                            @if ($pages[195]->page_name == 'contact' && $pages[195]->status == 'active')
                                <li class="mb-6"><a
                                        class="font-heading font-medium text-base text-gray-900 hover:text-gray-700"
                                        href="{{ route('contact') }}">{{ __('Contact') }}</a></li>
                            @endif

                            {{-- Custom pages --}}
                            @if ($pages)
                                @foreach ($pages as $page)
                                    @if ($page->page_name == 'Custom Page' && $page->status == 'active')
                                        <li
                                            class="mb-6 {{ request()->is('p/' . $page->section_title) ? 'font-bold' : '' }}">
                                            <a class="font-heading font-medium text-base text-gray-900 hover:text-gray-700"
                                                href="{{ route('custom.page', $page->section_title) }}">{{ __($page->section_name) }}</a>
                                        </li>
                                    @endif
                                @endforeach
                            @endif

                            {{-- Check webtools --}}
                            @if ($config[88]->config_value == '1')
                                <li @click.away="open = false" class="mb-6 relative" x-data="{ open: false }">
                                    <a @click="open = !open" href="#"
                                        class="font-heading font-medium text-base text-gray-900 hover:text-gray-700">
                                        <span>{{ __('Web Tools') }}</span>
                                        <svg fill="currentColor" viewBox="0 0 20 20"
                                            :class="{ 'rotate-180': open, 'rotate-0': !open }"
                                            class="inline w-4 h-4 ml-1 rtl:mr-1 rtl:ml-0 transition-transform duration-200 transform">
                                            <path fill-rule="evenodd"
                                                d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                clip-rule="evenodd"></path>
                                        </svg>
                                    </a>
                                    <div x-show="open" x-transition
                                        class="journal-scroll absolute w-full h-40 overflow-y-scroll mt-2 rounded-lg shadow-lg md:w-40 z-50 bg-white">
                                        <div class="px-2 py-2 capitalize rounded-sm shadow">
                                            <a class="block px-4 py-2 text-sm capitalize font-semi-bold hover:bg-gray-100 whitespace-nowrap"
                                                href="{{ route('web.html.beautifier') }}">{{ __('HTML Beautifier') }}</a>
                                            <a class="block px-4 py-2 text-sm capitalize font-semi-bold hover:bg-gray-100 whitespace-nowrap"
                                                href="{{ route('web.html.minifier') }}">{{ __('HTML Minifier') }}</a>
                                            <a class="block px-4 py-2 text-sm capitalize font-semi-bold hover:bg-gray-100 whitespace-nowrap"
                                                href="{{ route('web.css.beautifier') }}">{{ __('CSS Beautifier') }}</a>
                                            <a class="block px-4 py-2 text-sm capitalize font-semi-bold hover:bg-gray-100 whitespace-nowrap"
                                                href="{{ route('web.css.minifier') }}">{{ __('CSS Minifier') }}</a>
                                            <a class="block px-4 py-2 text-sm capitalize font-semi-bold hover:bg-gray-100 whitespace-nowrap"
                                                href="{{ route('web.js.beautifier') }}">{{ __('JS Beautifier') }}</a>
                                            <a class="block px-4 py-2 text-sm capitalize font-semi-bold hover:bg-gray-100 whitespace-nowrap"
                                                href="{{ route('web.js.minifier') }}">{{ __('JS Minifier') }}</a>
                                            <a class="block px-4 py-2 text-sm capitalize font-semi-bold hover:bg-gray-100 whitespace-nowrap"
                                                href="{{ route('web.random.password.generator') }}">{{ __('Random Password Generator') }}</a>
                                            <a class="block px-4 py-2 text-sm capitalize font-semi-bold hover:bg-gray-100 whitespace-nowrap"
                                                href="{{ route('web.bcrypt.password.generator') }}">{{ __('Bcrypt Password Generator') }}</a>
                                            <a class="block px-4 py-2 text-sm capitalize font-semi-bold hover:bg-gray-100 whitespace-nowrap"
                                                href="{{ route('web.md5.password.generator') }}">{{ __('MD5 Password Generator') }}</a>
                                            <a class="block px-4 py-2 text-sm capitalize font-semi-bold hover:bg-gray-100 whitespace-nowrap"
                                                href="{{ route('web.random.word.generator') }}">{{ __('Random Word Generator') }}</a>
                                            <a class="block px-4 py-2 text-sm capitalize font-semi-bold hover:bg-gray-100 whitespace-nowrap"
                                                href="{{ route('web.text.counter') }}">{{ __('Text Counter') }}</a>
                                            <a class="block px-4 py-2 text-sm capitalize font-semi-bold hover:bg-gray-100 whitespace-nowrap"
                                                href="{{ route('web.lorem.generator') }}">{{ __('Lorem Generator') }}</a>
                                            <a class="block px-4 py-2 text-sm capitalize font-semi-bold hover:bg-gray-100"
                                                href="{{ route('web.emojies') }}">{{ __('Emojies') }}</a>
                                            <a class="block px-4 py-2 text-sm capitalize font-semi-bold hover:bg-gray-100"
                                                href="{{ route('web.dns.lookup') }}">{{ __('DNS Lookup') }}</a>
                                            <a class="block px-4 py-2 text-sm capitalize font-semi-bold hover:bg-gray-100"
                                                href="{{ route('web.ip.lookup') }}">{{ __('IP Address Lookup') }}</a>
                                            <a class="block px-4 py-2 text-sm capitalize font-semi-bold hover:bg-gray-100"
                                                href="{{ route('web.whois.lookup') }}">{{ __('WHOIS Lookup') }}</a>
                                            <a class="block px-4 py-2 text-sm capitalize font-semi-bold hover:bg-gray-100"
                                                href="{{ route('web.qrcode') }}">{{ __('QR Code Maker') }}</a>
                                        </div>
                                    </div>
                                </li>
                            @endif

                            {{-- Languages --}}
                            @if (count(config('app.languages')) > 1)
                                <li @click.away="open = false" class="mb-6 relative" x-data="{ open: false }">
                                    <a @click="open = !open" href="#"
                                        class="font-heading font-medium text-base text-gray-900 hover:text-gray-700">
                                        <span>{{ strtoupper(app()->getLocale()) }}</span>
                                        <svg fill="currentColor" viewBox="0 0 20 20"
                                            :class="{ 'rotate-180': open, 'rotate-0': !open }"
                                            class="inline w-4 h-4 ml-1 rtl:mr-1 rtl:ml-0 transition-transform duration-200 transform">
                                            <path fill-rule="evenodd"
                                                d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                clip-rule="evenodd"></path>
                                        </svg>
                                    </a>
                                    <div x-show="open" x-transition
                                        class="journal-scroll absolute w-full h-40 overflow-y-scroll mt-2 rounded-lg shadow-lg md:w-40 z-50 bg-white">
                                        <div class="px-2 py-2 capitalize rounded-sm shadow">
                                            @foreach (config('app.languages') as $langLocale => $langName)
                                                <a class="block px-4 py-2 text-sm capitalize font-semi-bold hover:bg-gray-100 whitespace-nowrap"
                                                    href="{{ url()->current() }}?change_language={{ $langLocale }}">{{ strtoupper($langName) }}</a>
                                            @endforeach
                                        </div>
                                    </div>
                                </li>
                            @endif
                        </ul>
                    </div>
                    <div class="flex flex-col justify-end w-full">
                        <div class="flex flex-wrap">
                            {{-- Guest user --}}
                            @guest
                                <div class="w-full">
                                    <a class="p-0.5 font-heading block w-full text-base text-gray-900 font-medium rounded-xl"
                                        href="{{ route('login') }}">
                                        <div class="py-2 px-5 rounded-xl">
                                            <p>{{ __('Login') }}</p>
                                        </div>
                                    </a>
                                </div>

                                @if (Route::has('register'))
                                    <div class="w-full">
                                        <a href="{{ route('register') }}"
                                            class="group relative p-0.5 font-heading block w-full text-base text-gray-900 font-medium bg-gradient-cyan overflow-hidden rounded-xl">
                                            <div
                                                class="absolute top-0 left-0 transform -translate-y-full group-hover:-translate-y-0 h-full w-full bg-gradient-cyan transition ease-in-out duration-500">
                                            </div>
                                            <div class="py-2 px-5 bg-white rounded-lg">
                                                <p class="relative z-10">{{ __('Try now') }}</p>
                                            </div>
                                        </a>
                                    </div>
                                @endif
                            @else
                                {{-- Check maintenance mode --}}
                                @if (!$isDown)
                                    <div class="w-full">
                                        <a href="{{ $dashboardUrl }}"
                                            class="group relative p-0.5 font-heading block w-full text-base text-gray-900 font-medium bg-gradient-cyan overflow-hidden rounded-xl">
                                            <div
                                                class="absolute top-0 left-0 transform -translate-y-full group-hover:-translate-y-0 h-full w-full bg-gradient-cyan transition ease-in-out duration-500">
                                            </div>
                                            <div class="py-2 px-5 bg-white rounded-lg">
                                                <p class="relative z-10">{{ __('Dashboard') }}</p>
                                            </div>
                                        </a>
                                    </div>
                                @endif
                            @endguest
                        </div>
                    </div>
                </div>
            </nav>
        </div>
    </section>

    {{-- Hero slider --}}
    @if (isset($banner) && $banner)
        <div class="container mx-auto px-4">
            <div class="flex flex-wrap items-center -m-4 lg:pt-24 pt-10 pb-36">
                <div class="w-full lg:w-1/2 p-6">
                    <div class="lg:max-w-xl">
                        <h1 class="mb-6 font-heading text-5xl md:text-7xl xl:text-7xl text-gray-900 font-bold">
                            {{ __($homePage[0]->section_content) }}</h1>
                        <p class="mb-9 text-gray-600 text-lg">{{ __($homePage[1]->section_content) }}</p>
                        <button
                            class="group relative font-heading px-10 py-5 my-2 w-full lg:w-auto uppercase text-white text-xs font-semibold tracking-px bg-{{ $template_config->template_color }}-800 overflow-hidden rounded-md">
                            <a href="{{ url($homePage[5]->section_content) }}" target="_blank">
                                <div
                                    class="absolute top-0 left-0 transform -translate-x-full group-hover:-translate-x-0 h-full w-full transition ease-in-out duration-500 bg-gray-800">
                                </div>
                                <p class="relative z-10">{{ __($homePage[4]->section_content) }}</p>
                            </a>
                        </button>
                        <button
                            class="group relative font-heading px-10 py-5 w-full lg:w-auto uppercase text-white text-xs font-semibold tracking-px bg-gray-900 overflow-hidden rounded-md">
                            <a href="{{ url($homePage[3]->section_content) }}" target="_blank">
                                <div
                                    class="absolute top-0 left-0 transform -translate-x-full group-hover:-translate-x-0 h-full w-full transition ease-in-out duration-500 bg-gray-800">
                                </div>
                                <p class="relative z-10">{{ __($homePage[2]->section_content) }}</p>
                            </a>
                        </button>
                    </div>
                </div>
                <div class="w-full lg:w-1/2 p-6 hidden md:block mobile-banner">
                    <img class="block mx-auto w-50" src="{{ asset($template_config->banner_image) }}"
                        alt="{{ config('app.name') }}">
                </div>
            </div>
        </div>
    @endif

</section>

<!-- Mobile Navigation and Scroll Control Script -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const openBurger = document.querySelector('.navbar-burger-open');
        const closeBurger = document.querySelector('.navbar-close');
        const menu = document.querySelector('.navbar-menu');
        const backdrop = document.querySelector('.navbar-backdrop');
        const menuLinks = document.querySelectorAll('.navbar-menu nav a');

        function openMenu(e) {
            if (e) e.preventDefault();
            if (menu) {
                menu.classList.remove('hidden');
                document.body.classList.add('no-scroll');
            }
        }

        function closeMenu() {
            if (menu) {
                menu.classList.add('hidden');
                document.body.classList.remove('no-scroll');
            }
        }

        if (openBurger) openBurger.addEventListener('click', openMenu);
        if (closeBurger) {
            closeBurger.addEventListener('click', function(e) {
                e.preventDefault();
                closeMenu();
            });
        }
        if (backdrop) backdrop.addEventListener('click', closeMenu);

        if (menuLinks.length) {
            menuLinks.forEach(link => {
                link.addEventListener('click', function() {
                    const isDropdownToggle = link.getAttribute('href') === '#' && (link
                        .hasAttribute('@click') || link.closest('.navbar-close'));

                    if (!isDropdownToggle) {
                        closeMenu();
                    }
                });
            });
        }
    });
</script>
