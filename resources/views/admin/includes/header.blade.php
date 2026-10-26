<!-- Navbar -->
<header class="navbar navbar-expand-md d-none d-lg-flex d-print-none bg-body">
    <!-- Desktop Search -->
    <div class="mx-3 my-2">
        <div class="input-icon">
            <input type="text" id="adminPageSearchTrigger" class="form-control" placeholder="{{ __('Search pages…') }}"
                readonly style="cursor: pointer;">
            <span class="input-icon-addon">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24"
                    stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                    <circle cx="10" cy="10" r="7" />
                    <line x1="21" y1="21" x2="15" y2="15" />
                </svg>
            </span>
        </div>
    </div>

    <div class="container-{{ App::isLocale('ar') || App::isLocale('ur') || App::isLocale('he') ? 'xl' : 'fluid' }}">
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbar-menu"
            aria-controls="navbar-menu" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="navbar-nav flex-row order-md-last">
            <div class="nav-item dropdown">
                <a href="#" class="nav-link d-flex lh-1 text-reset p-0" data-bs-toggle="dropdown"
                    aria-label="Open user menu">
                    <span class="img-rounded">
                        <img class="img-rounded"
                            src="{{ asset(auth::user()->profile_image == null ? 'profile.png' : auth::user()->profile_image) }}"
                            alt="{{ auth::user()->name }}">
                    </span>
                    <div class="d-none d-xl-block ps-2">
                        <div>{{ Auth::user()->name }}</div>
                        <div class="mt-1 small text-muted">
                            {{ Auth::user()->role_id == 4 ? __('Manager') : __('Administrator') }}</div>
                    </div>
                </a>
                <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                    <a href="{{ route('admin.account') }}" class="dropdown-item">{{ __('Profile & account') }}</a>
                    {{-- Language Switcher --}}
                    @if (count(config('app.languages')) > 1)
                        @php
                            $flagMap = config('app.flag-icons');
                        @endphp

                        <a class="dropdown-item d-flex align-items-center gap-2 py-2 lang-trigger"
                            data-bs-toggle="modal" data-bs-target="#languageModal">
                            <span class="fi fi-{{ $flagMap[app()->getLocale()] ?? 'us' }}"></span>
                            <span class="fw-semibold text-uppercase">
                                {{ app()->getLocale() }}
                            </span>
                            <span class="ms-auto text-muted small">
                                {{ __('Change') }}
                            </span>
                        </a>
                    @endif

                    {{-- Light / Dark Mode --}}
                    <a href="{{ route('admin.change.theme', 'dark') }}" class="dropdown-item hide-theme-dark"
                        data-bs-placement="bottom">
                        {{ __('Dark mode') }}
                    </a>
                    <a href="{{ route('admin.change.theme', 'light') }}" class="dropdown-item hide-theme-light"
                        data-bs-placement="bottom">
                        {{ __('Light mode') }}
                    </a>
                    <a href="{{ route('logout') }}" class="dropdown-item"
                        onclick="event.preventDefault(); document.getElementById('logout-form').submit();">{{ __('Logout') }}</a>
                    <form class="logout" id="logout-form" action="{{ route('logout') }}" method="POST">
                        @csrf
                    </form>
                </div>
            </div>
        </div>
        <div class="collapse navbar-collapse" id="navbar-menu"></div>
    </div>
</header>

{{-- Modern Spotlight Search Modal --}}
<div class="modal" id="pageSearchModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content search-modal">

            <!-- Search input with inner left aligned icon to solve alignment glitches -->
            <div class="search-header position-relative">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon text-muted" width="24" height="24"
                    viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round"
                    stroke-linejoin="round"
                    style="position: absolute; left: 34px; top: 50%; transform: translateY(-50%); z-index: 10; pointer-events: none;">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                    <circle cx="10" cy="10" r="7" />
                    <line x1="21" y1="21" x2="15" y2="15" />
                </svg>
                <input type="text" id="adminPageSearch" class="form-control form-control-lg border-0 shadow-none"
                    placeholder="{{ __('Search pages…') }}" autocomplete="off">
            </div>

            <!-- Spotlight Results & Default Recommendations panel (d-none explicitly removed to prevent mobile blank capsule bug) -->
            <div id="adminPageResults" class="search-results">
                {{-- Dynamic result lists are injected and toggled here via Javascript --}}
            </div>
        </div>
    </div>
</div>

{{-- Modern Centered Language Modal --}}
<div class="modal" id="languageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content search-modal">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-dark fs-2">
                    {{ __('Select Language') }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body pt-3 pb-4">
                <!-- Dropdown scrollable height container -->
                <div class="lang-scroll-area">
                    <div class="row g-2">
                        @foreach (config('app.languages') as $locale => $name)
                            <div class="col-12">
                                <a href="{{ request()->fullUrlWithQuery(['change_language' => $locale]) }}"
                                    class="lang-card {{ app()->getLocale() === $locale ? 'active' : '' }}">
                                    <div class="d-flex align-items-center gap-2 min-w-0">
                                        <span class="fi fi-{{ $flagMap[$locale] ?? 'us' }} flex-shrink-0"
                                            style="width: 20px; height: 20px; object-fit: cover;"></span>
                                        <span class="fw-bold text-uppercase small text-muted flex-shrink-0"
                                            style="font-size: 11px;">{{ $locale }}</span>
                                        <span class="fw-semibold text-truncate"
                                            style="font-size: 13px;">{{ __($name) }}</span>
                                    </div>
                                    @if (app()->getLocale() === $locale)
                                        <span class="text-primary d-flex align-items-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="3" stroke-linecap="round" stroke-linejoin="round"
                                                class="icon icon-tabler icon-tabler-check">
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                <path d="M5 12l5 5l10 -10" />
                                            </svg>
                                        </span>
                                    @endif
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
