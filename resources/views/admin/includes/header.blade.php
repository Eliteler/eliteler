{{-- Custom CSS --}}
<style>
    /* Language */
    .modal-dialog-top {
        margin: 1rem auto;
    }

    /* Make modal usable on small screens */
    @media (max-height: 700px) {
        .modal-content {
            max-height: calc(100vh - 2rem);
        }
    }

    /* Scrollable language list */
    .lang-dropdown-scroll {
        max-height: 300px;
        overflow-y: auto;
        scrollbar-width: thin;
        border-radius: 12px;
    }

    /* First item */
    .lang-dropdown-scroll .dropdown-item:first-child {
        border-top-left-radius: 8px;
        border-top-right-radius: 8px;
    }

    /* Last item */
    .lang-dropdown-scroll .dropdown-item:last-child {
        border-bottom-left-radius: 8px;
        border-bottom-right-radius: 8px;
    }

    .lang-dropdown-scroll .dropdown-item:hover {
        background-color: rgba(0, 0, 0, 0.04);
    }

    .lang-dropdown-scroll .dropdown-item.active {
        background-color: rgba(0, 0, 0, 0.06);
    }

    @media (max-width: 768px) {
        #languageModal {
            margin-top: 100px;
        }
    }

    /* Search */
    .input-icon {
        width: 120% !important;
    }

    /* Result items */
    #adminPageResults a {
        border-radius: 8px;
        margin-bottom: 6px;
        padding: 10px 12px;
    }

    #adminPageResults a:hover {
        background-color: #f1f5f9;
    }

    /* Mobile modal input spacing */
    @media (max-width: 768px) {
        #adminPageSearch {
            font-size: 16px;
            padding: 12px 14px;
        }
    }

    /* Modal container */
    .search-modal {
        border-radius: 14px;
        border: 1px solid #e5e7eb;
        box-shadow: 0 30px 80px rgba(0, 0, 0, 0.15);
        overflow: hidden;
    }

    /* Search input header */
    .search-header {
        padding: 16px;
        border-bottom: 1px solid #e5e7eb;
    }

    .search-header input {
        border-radius: 12px;
        font-size: 16px;
    }

    /* Results container */
    .search-results {
        max-height: 420px;
        overflow-y: auto;
        padding: 12px;
    }

    /* Result item */
    .search-results a {
        display: block;
        padding: 12px 14px;
        margin-bottom: 8px;
        border-radius: 10px;

        background: #ffffff;
        border: 1px solid #e5e7eb;

        color: #111827;
        text-decoration: none;
        font-weight: 500;

        transition: all 0.15s ease;
    }

    .search-results a:hover {
        background: #f8fafc;
        border-color: #c7d2fe;
        color: #1d4ed8;
    }

    /* Empty state */
    .search-results .empty {
        padding: 16px;
        color: #6b7280;
        text-align: center;
    }

    /* Mobile adjustments */
    @media (max-width: 768px) {
        .modal-dialog {
            margin: 10px;
        }

        .search-results {
            max-height: 60vh;
        }

        .search-header input {
            font-size: 15px;
            padding: 12px 14px;
        }
    }
</style>

<!-- Navbar -->
<header class="navbar navbar-expand-md d-none d-lg-flex d-print-none bg-body-tertiary">
    <!-- Desktop search -->
    <div class="mx-2 my-2">
        <div class="input-icon">
            <input type="text" id="adminPageSearchTrigger" class="form-control"
                placeholder="{{ __('Search pages… (Ctrl + K)') }}" autocomplete="off">
            <span class="input-icon-addon">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    class="icon icon-1">
                    <path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0" />
                    <path d="M21 21l-6 -6" />
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

{{-- Search Modal --}}
<div class="modal fade" id="pageSearchModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content search-modal">

            <!-- Search input -->
            <div class="search-header">
                <input type="text" id="adminPageSearch" class="form-control form-control-lg"
                    placeholder="{{ __('Search pages…') }}" autocomplete="off">
            </div>

            <!-- Results -->
            <div id="adminPageResults" class="search-results d-none"></div>

        </div>
    </div>
</div>

{{-- Language Modal --}}
<div class="modal fade" id="languageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-top modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-semibold">
                    {{ __('Language') }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body pt-0">
                <div class="dropdown w-100">
                    <button
                        class="btn btn-light w-100 d-flex align-items-center justify-content-between lang-select-btn"
                        data-bs-toggle="dropdown">
                        <span class="d-flex align-items-center gap-2">
                            <span class="fi fi-{{ $flagMap[app()->getLocale()] ?? 'us' }}"></span>
                            <span class="fw-semibold text-uppercase">
                                {{ app()->getLocale() }}
                            </span>
                        </span>
                        <i class="bi bi-chevron-down small"></i>
                    </button>

                    <div class="dropdown-menu w-100 border-0 shadow-sm mt-2 lang-dropdown-scroll">
                        @foreach (config('app.languages') as $locale => $name)
                            <a href="{{ request()->fullUrlWithQuery(['change_language' => $locale]) }}"
                                class="dropdown-item d-flex align-items-center gap-3 px-3 py-2
               {{ app()->getLocale() === $locale ? 'active fw-semibold' : '' }}">
                                <span class="fi fi-{{ $flagMap[$locale] ?? 'us' }}"></span>
                                <span class="flex-grow-1">{{ $name }}</span>

                                @if (app()->getLocale() === $locale)
                                    <span class="small text-muted">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                            class="icon icon-tabler icons-tabler-outline icon-tabler-check">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                            <path d="M5 12l5 5l10 -10" />
                                        </svg>
                                    </span>
                                @endif
                            </a>
                        @endforeach
                    </div>
                </div>

            </div>

            <!-- Close button -->
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-primary btn-5 ms-auto" data-bs-dismiss="modal">
                    {{ __('Close') }}
                </button>
            </div>
        </div>
    </div>
</div>
