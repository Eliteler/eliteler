@extends('admin.layouts.index', ['header' => true, 'nav' => true, 'demo' => true])

{{-- Custom CSS --}}
@section('css')
    <style>
        /* .table-responsive {
                                border-radius: 15px !important;
                            } */
        .table-responsive {
            overflow-x: visible !important;
        }
    </style>
@endsection

@section('content')
    <div class="page-wrapper">
        <div class="container-fluid">
            <!-- Page title -->
            <div class="page-header d-print-none">
                <div class="row align-items-center">
                    <!-- Standard Tabler Breadcrumbs (Dashboard › Translations) -->
                    <div class="mb-1">
                        <ol class="breadcrumb" aria-label="breadcrumbs">
                            <li class="breadcrumb-item">
                                <a href="{{ route('admin.dashboard') }}" class="text-secondary text-decoration-none">
                                    {{ __('Dashboard') }}
                                </a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="{{ route('admin.settings') }}" class="text-secondary text-decoration-none">
                                    {{ __('System Utilities') }}
                                </a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">
                                <a href="#">{{ __('Translations') }}</a>
                            </li>
                        </ol>
                    </div>
                    <div class="col">
                        <h2 class="page-title">
                            {{ __('System Translations') }}
                        </h2>
                        <div class="text-muted mt-1">
                            {{ __('Manage translation languages and locales for your platform.') }}
                        </div>
                    </div>
                    <!-- Action Controls -->
                    <div class="col-auto ms-auto d-print-none d-flex gap-2">
                        <!-- Trigger Import Modal -->
                        <button type="button" class="btn btn-primary d-inline-flex align-items-center"
                            data-bs-toggle="modal" data-bs-target="#importPackModal">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-upload m-0"
                                width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                fill="none" stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                <path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2 -2v-2"></path>
                                <polyline points="7 9 12 4 17 9"></polyline>
                                <line x1="12" y1="4" x2="12" y2="16"></line>
                            </svg>
                            <span class="d-none d-sm-inline">{{ __('Import') }}</span>
                        </button>

                        <!-- Add Language -->
                        <a href="{{ route('translation-manager.create') }}"
                            class="btn btn-primary d-inline-flex align-items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-plus m-0"
                                width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                fill="none" stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                <line x1="12" y1="5" x2="12" y2="19"></line>
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                            </svg>
                            <span class="d-none d-sm-inline">{{ __('Create New') }}</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="page-body">
            <div class="container-fluid">

                {{-- Failed alert --}}
                @if (Session::has('failed'))
                    <div class="alert alert-important alert-danger alert-dismissible mb-2" role="alert">
                        <div class="d-flex">
                            <div>
                                {{ Session::get('failed') }}
                            </div>
                        </div>
                        <a class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="close"></a>
                    </div>
                @endif

                {{-- Success alert --}}
                @if (Session::has('success'))
                    <div class="alert alert-important alert-success alert-dismissible mb-2" role="alert">
                        <div class="d-flex">
                            <div>
                                {{ Session::get('success') }}
                            </div>
                        </div>
                        <a class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="close"></a>
                    </div>
                @endif

                {{-- Info alert --}}
                <div id="sync-info-alert-wrapper">
                    @if (Session::has('info'))
                        <div class="alert alert-important alert-primary alert-dismissible mb-2" role="alert"
                            id="sync-info-alert">
                            <div class="d-flex">
                                <div id="sync-info-alert-text">
                                    {{ __(Session::get('info')) }}
                                </div>
                            </div>
                            <a class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="close"></a>
                        </div>
                    @endif
                </div>

                <!-- Live Search Bar -->
                <div class="row mb-3">
                    <div class="col-12 col-md-4 col-lg-6">
                        <input type="text" id="langSearch" placeholder="{{ __('Search languages by name or code...') }}"
                            class="form-control">
                    </div>
                </div>

                <div class="row row-cards">
                    <!-- 1. DESKTOP VIEW: Clean Table Layout with Dynamic Pagination Footer -->
                    <div class="col-sm-12 col-lg-12 mb-4 d-none d-md-block">
                        <div class="card border shadow-sm">
                            <div class="table-responsive rounded-top-3">
                                <table class="table card-table table-vcenter text-nowrap responsive-card-table">
                                    <thead>
                                        <tr>
                                            <th style="width: 70%;">{{ __('Name') }}</th>
                                            <th style="width: 20%;">{{ __('Locale') }}</th>
                                            <th style="width: 10%;" class="text-end">{{ __('Action') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($languages as $lang)
                                            <tr class="lang-row-item" data-name="{{ strtolower($lang['name']) }}"
                                                data-code="{{ strtolower($lang['code']) }}">
                                                <td>
                                                    <div class="font-weight-medium text-dark">{{ __($lang['name']) }}
                                                    </div>
                                                </td>
                                                <td>
                                                    <a href="{{ route('translation-manager.edit', $lang['code']) }}"
                                                        class="text-primary font-weight-semibold">
                                                        {{ $lang['code'] }}
                                                    </a>
                                                </td>
                                                <td class="text-end">
                                                    <div class="dropdown">
                                                        <a class="btn-action" href="javascript:void(0);" role="button"
                                                            data-bs-toggle="dropdown" aria-expanded="false">
                                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                                class="icon icon-tabler icon-tabler-dots-vertical m-0"
                                                                width="24" height="24" viewBox="0 0 24 24"
                                                                stroke-width="2" stroke="currentColor" fill="none"
                                                                stroke-linecap="round" stroke-linejoin="round">
                                                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                                <circle cx="12" cy="12" r="1" />
                                                                <circle cx="12" cy="19" r="1" />
                                                                <circle cx="12" cy="5" r="1" />
                                                            </svg>
                                                        </a>
                                                        <div class="dropdown-menu dropdown-menu-end">
                                                            <a class="dropdown-item"
                                                                href="{{ route('translation-manager.edit', $lang['code']) }}">
                                                                {{ __('Edit') }}
                                                            </a>
                                                            <a class="dropdown-item"
                                                                href="{{ route('translation-manager.missing', $lang['code']) }}">
                                                                {{ __('Check Missing') }}
                                                            </a>
                                                            <a class="dropdown-item"
                                                                href="{{ route('translation-manager.export', $lang['code']) }}">
                                                                {{ __('Export') }}
                                                            </a>
                                                            @if ($lang['code'] !== config('translation-manager.default_locale', 'en'))
                                                                <button type="button"
                                                                    class="dropdown-item text-danger w-100 text-start border-0 bg-transparent py-2"
                                                                    onclick="deleteLanguage('{{ $lang['code'] }}')">
                                                                    {{ __('Delete') }}
                                                                </button>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            {{-- Desktop Custom Pagination Footer --}}
                            <div id="desktop-pagination"
                                class="d-none justify-content-between align-items-center px-4 py-3 border-top rounded-bottom">
                                <span id="desktop-page-info" class="text-secondary small font-weight-medium"></span>
                                <div class="btn-list">
                                    <button id="desktop-prev-btn" class="btn-link btn-sm"
                                        type="button">{{ __('Previous') }}</button>
                                    <button id="desktop-next-btn" class="btn-link btn-sm ms-2"
                                        type="button">{{ __('Next') }}</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 2. MOBILE VIEW: Stacked Row Cards (Visible on Mobile) -->
                    <div class="col-12 d-md-none">
                        <div class="row g-2" id="mobileLangContainer">
                            @foreach ($languages as $lang)
                                <div class="col-12 lang-card-item" data-name="{{ strtolower($lang['name']) }}"
                                    data-code="{{ strtolower($lang['code']) }}">
                                    <div class="card shadow-sm w-100">
                                        <div class="card-body p-3 d-flex align-items-center justify-content-between">
                                            <a href="{{ route('translation-manager.edit', $lang['code']) }}">
                                                <div class="d-flex align-items-center gap-3">
                                                    <span
                                                        class="avatar avatar-md bg-primary-lt text-primary font-weight-bold text-uppercase"
                                                        style="border-radius: 6px;">{{ $lang['code'] }}</span>
                                                    <div>
                                                        <h4 class="m-0 font-weight-semibold text-dark">
                                                            {{ __($lang['name']) }}
                                                        </h4>
                                                        <span class="text-muted small uppercase"
                                                            style="font-size: 10px;">{{ $lang['type'] }}</span>
                                                    </div>
                                                </div>
                                            </a>

                                            <div class="dropdown">
                                                <a class="text-muted d-inline-block" href="#"
                                                    data-bs-toggle="dropdown" aria-expanded="false">
                                                    <svg xmlns="http://www.w3.org/2000/svg"
                                                        class="icon icon-tabler icon-tabler-dots-vertical m-0"
                                                        width="24" height="24" viewBox="0 0 24 24"
                                                        stroke-width="2" stroke="currentColor" fill="none"
                                                        stroke-linecap="round" stroke-linejoin="round">
                                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                        <circle cx="12" cy="12" r="1" />
                                                        <circle cx="12" cy="19" r="1" />
                                                        <circle cx="12" cy="5" r="1" />
                                                    </svg>
                                                </a>
                                                <div class="dropdown-menu dropdown-menu-end">
                                                    <a class="dropdown-item"
                                                        href="{{ route('translation-manager.edit', $lang['code']) }}">
                                                        {{ __('Edit') }}
                                                    </a>
                                                    <a class="dropdown-item"
                                                        href="{{ route('translation-manager.missing', $lang['code']) }}">
                                                        {{ __('Check Missing') }}
                                                    </a>
                                                    <a class="dropdown-item"
                                                        href="{{ route('translation-manager.export', $lang['code']) }}">
                                                        {{ __('Export') }}
                                                    </a>
                                                    @if ($lang['code'] !== config('translation-manager.default_locale', 'en'))
                                                        <button type="button"
                                                            class="dropdown-item text-danger w-100 text-start border-0 bg-transparent py-2"
                                                            onclick="deleteLanguage('{{ $lang['code'] }}')">
                                                            {{ __('Delete') }}
                                                        </button>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Mobile Custom Pagination Footer --}}
                    <div id="custom-mobile-pagination"
                        class="d-flex d-md-none justify-content-between align-items-center px-3 py-3 border-top">
                        <button id="mobile-prev-btn" class="btn btn-primary btn-sm" type="button">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-chevron-left me-1"
                                width="24" height="24" viewBox="0 0 24 24" stroke-width="2"
                                stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                <path d="M15 6l-6 6l6 6"></path>
                            </svg>
                            {{ __('Previous') }}
                        </button>
                        <span id="mobile-page-info" class="text-secondary small font-weight-medium"></span>
                        <button id="mobile-next-btn" class="btn btn-primary btn-sm" type="button">
                            {{ __('Next') }}
                            <svg xmlns="http://www.w3.org/2000/svg"
                                class="icon icon-tabler icon-tabler-chevron-right ms-1" width="24" height="24"
                                viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                                stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                <path d="M9 6l6 6l-6 6"></path>
                            </svg>
                        </button>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- Import Language Pack Modal (Cancel button omitted from footer) -->
    <div class="modal modal-bg modal-sm fade" id="importPackModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <form action="{{ route('translation-manager.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title font-weight-bold text-dark">{{ __('Import Translation Pack') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-start">
                        <p class="text-muted small mb-3">
                            {{ __('Upload a ZIP pack containing standard locale folder structures.') }}
                        </p>
                        <div class="row g-3">
                            <div class="col-12">
                                <label
                                    class="form-label small text-muted text-uppercase required">{{ __('Language Code') }}</label>
                                <input type="text" name="locale" placeholder="e.g., ta" class="form-control"
                                    required>
                            </div>
                            <div class="col-12">
                                <label
                                    class="form-label small text-muted text-uppercase required">{{ __('Zip File') }}</label>
                                <input type="file" name="zip_file" accept=".zip" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer text-end">
                        <button type="submit" class="btn btn-primary ms-auto">{{ __('Upload Pack') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Delete Language Modal --}}
    <div class="modal modal-bg fade" id="deleteLanguageModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-status"></div>
                <div class="modal-body text-center py-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon mb-2 text-danger icon-lg" width="24"
                        height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                        <path d="M12 9v2m0 4v.01" />
                        <path
                            d="M5 19h14a2 2 0 0 0 1.84 -2.75l-7.1 -12.25a2 2 0 0 0 -3.5 0l-7.1 12.25a2 2 0 0 0 1.75 2.75" />
                    </svg>
                    <h3>{{ __('Delete Language') }}</h3>
                    <div class="text-muted">
                        {{ __('Are you sure you want to delete this language?') }}
                    </div>
                </div>

                <div class="modal-footer">
                    <div class="w-100">
                        <div class="row">
                            <div class="col">
                                <button type="button" class="btn w-100" data-bs-dismiss="modal">
                                    {{ __('Cancel') }}
                                </button>
                            </div>

                            <div class="col">
                                <form id="deleteLanguageForm" method="POST">
                                    @csrf
                                    @method('DELETE')

                                    <button type="submit" class="btn btn-danger w-100">
                                        {{ __('Yes, delete') }}
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Client-side Instant Filter & Paginator script -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const searchInput = document.getElementById('langSearch');
            const tableRows = document.querySelectorAll('.lang-row-item');
            const mobileCards = document.querySelectorAll('.lang-card-item');

            let currentPage = 1;
            const pageSize = 10;

            // ── Pagination logic ──────────────────────────────────
            function updatePagination() {
                const term = searchInput.value.toLowerCase().trim();

                // 1. Filter Desktop elements
                const matchedRows = [];
                tableRows.forEach(row => {
                    const name = row.getAttribute('data-name') || '';
                    const code = row.getAttribute('data-code') || '';
                    if (name.includes(term) || code.includes(term)) {
                        matchedRows.push(row);
                    } else {
                        row.style.setProperty('display', 'none', 'important');
                    }
                });

                // 2. Filter Mobile elements
                const matchedCards = [];
                mobileCards.forEach(card => {
                    const name = card.getAttribute('data-name') || '';
                    const code = card.getAttribute('data-code') || '';
                    if (name.includes(term) || code.includes(term)) {
                        matchedCards.push(card);
                    } else {
                        card.style.setProperty('display', 'none', 'important');
                    }
                });

                // 3. Compute dynamic indices
                const totalItems = matchedRows.length;
                const totalPages = Math.ceil(totalItems / pageSize) || 1;

                if (currentPage > totalPages) {
                    currentPage = totalPages;
                }

                const startIndex = (currentPage - 1) * pageSize;
                const endIndex = startIndex + pageSize;

                // 4. Toggle visibility based on page limits
                tableRows.forEach(row => {
                    if (matchedRows.includes(row)) {
                        const idx = matchedRows.indexOf(row);
                        if (idx >= startIndex && idx < endIndex) {
                            row.style.setProperty('display', '', 'important');
                        } else {
                            row.style.setProperty('display', 'none', 'important');
                        }
                    }
                });

                mobileCards.forEach(card => {
                    if (matchedCards.includes(card)) {
                        const idx = matchedCards.indexOf(card);
                        if (idx >= startIndex && idx < endIndex) {
                            card.style.setProperty('display', '', 'important');
                        } else {
                            card.style.setProperty('display', 'none', 'important');
                        }
                    }
                });

                // 5. Update Desktop Pagination UI
                const desktopPagination = document.getElementById('desktop-pagination');
                const desktopPageInfo = document.getElementById('desktop-page-info');
                const desktopPrevBtn = document.getElementById('desktop-prev-btn');
                const desktopNextBtn = document.getElementById('desktop-next-btn');

                if (desktopPagination) {
                    if (totalPages <= 1) {
                        desktopPagination.classList.add('d-none');
                    } else {
                        desktopPagination.classList.remove('d-none');
                        desktopPagination.classList.add('d-flex');
                        desktopPageInfo.textContent = `{{ __('Page :current of :total') }}`
                            .replace(':current', currentPage)
                            .replace(':total', totalPages);
                        desktopPrevBtn.disabled = (currentPage === 1);
                        desktopNextBtn.disabled = (currentPage === totalPages);
                    }
                }

                // 6. Update Mobile Pagination UI
                const mobilePagination = document.getElementById('custom-mobile-pagination');
                const mobilePageInfo = document.getElementById('mobile-page-info');
                const mobilePrevBtn = document.getElementById('mobile-prev-btn');
                const mobileNextBtn = document.getElementById('mobile-next-btn');

                if (mobilePagination) {
                    if (totalPages <= 1) {
                        mobilePagination.classList.add('d-none');
                    } else {
                        mobilePagination.classList.remove('d-none');
                        mobilePagination.classList.add('d-flex');
                        mobilePageInfo.textContent = `{{ __('Page :current of :total') }}`
                            .replace(':current', currentPage)
                            .replace(':total', totalPages);
                        mobilePrevBtn.disabled = (currentPage === 1);
                        mobileNextBtn.disabled = (currentPage === totalPages);
                    }
                }
            }

            // ── Event Listeners ───────────────────────────────────
            if (searchInput) {
                searchInput.addEventListener('input', () => {
                    currentPage = 1; // Reset to page 1 on search
                    updatePagination();
                });
            }

            // Desktop Pagination triggers
            document.getElementById('desktop-prev-btn').addEventListener('click', () => {
                if (currentPage > 1) {
                    currentPage--;
                    updatePagination();
                }
            });

            document.getElementById('desktop-next-btn').addEventListener('click', () => {
                const term = searchInput.value.toLowerCase().trim();
                const totalItems = [...tableRows].filter(row => {
                    const name = row.getAttribute('data-name') || '';
                    const code = row.getAttribute('data-code') || '';
                    return name.includes(term) || code.includes(term);
                }).length;
                const totalPages = Math.ceil(totalItems / pageSize) || 1;

                if (currentPage < totalPages) {
                    currentPage++;
                    updatePagination();
                }
            });

            // Mobile Pagination triggers
            document.getElementById('mobile-prev-btn').addEventListener('click', () => {
                if (currentPage > 1) {
                    currentPage--;
                    updatePagination();
                }
            });

            document.getElementById('mobile-next-btn').addEventListener('click', () => {
                const term = searchInput.value.toLowerCase().trim();
                const totalItems = [...mobileCards].filter(card => {
                    const name = card.getAttribute('data-name') || '';
                    const code = card.getAttribute('data-code') || '';
                    return name.includes(term) || code.includes(term);
                }).length;
                const totalPages = Math.ceil(totalItems / pageSize) || 1;

                if (currentPage < totalPages) {
                    currentPage++;
                    updatePagination();
                }
            });

            // Initialize Page on load
            updatePagination();
        });

        // Delete Language
        function deleteLanguage(langCode) {
            "use strict";

            const form = document.getElementById("deleteLanguageForm");
            form.action = "{{ url('admin/translations') }}/" + encodeURIComponent(langCode);

            $("#deleteLanguageModal").modal("show");
        }

        (function() {
            "use strict";

            const activeStatusUrl = @json(route('translation-manager.sync-status.active'));
            const wrapper = document.getElementById('sync-info-alert-wrapper');

            if (!wrapper) return;

            function renderAlert(message) {
                wrapper.innerHTML = `
            <div class="alert alert-important alert-primary alert-dismissible mb-2" role="alert" id="sync-info-alert">
                <div class="d-flex">
                    <div id="sync-info-alert-text">${message}</div>
                </div>
                <a class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="close"></a>
            </div>
        `;
            }

            function clearAlert() {
                // Only clear if the currently-shown alert was ours (a sync message),
                // so we don't wipe out an unrelated flash message shown once.
                const existingText = document.getElementById('sync-info-alert-text');
                if (existingText) {
                    wrapper.innerHTML = '';
                }
            }

            function pollActiveSync() {
                fetch(activeStatusUrl, {
                        headers: {
                            'Accept': 'application/json'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.active) {
                            renderAlert(data.message);
                        } else {
                            clearAlert();
                        }
                    })
                    .catch(() => {
                        // Silent fail — don't disrupt the page on a network hiccup
                    });
            }

            // Poll every 5 seconds, site-wide, as long as this layout is loaded
            setInterval(pollActiveSync, 5000);

            // Run once immediately so it doesn't wait 5s on first load
            pollActiveSync();
        })();
    </script>
@endsection
