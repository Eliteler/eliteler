@extends('admin.layouts.index', ['header' => true, 'nav' => true, 'demo' => true])

{{-- Custom CSS --}}
@section('css')
    <style>
        .badge {
            margin-bottom: 0 !important;
        }

        .pagination-btn {
            padding: 5px 12px !important;
            cursor: pointer;
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
                            <li class="breadcrumb-item">
                                <a href="{{ route('translation-manager.index') }}"
                                    class="text-secondary text-decoration-none">
                                    {{ __('Translations') }}
                                </a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">
                                <a href="#">{{ __('Update') }}</a>
                            </li>
                        </ol>
                    </div>
                    <div class="col">
                        <h2 class="page-title">
                            {{ __('Translation Editor') }}
                        </h2>
                        <div class="text-muted mt-1">
                            {{ __('Manage and customize translated values for each locale and category.') }}
                        </div>
                    </div>
                    <!-- Add New Word Trigger -->
                    <div class="col-auto ms-auto d-print-none">
                        <button type="button" class="btn btn-primary d-inline-flex align-items-center"
                            data-bs-toggle="modal" data-bs-target="#addKeyModal">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-plus m-0"
                                width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                fill="none" stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                <line x1="12" y1="5" x2="12" y2="19"></line>
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                            </svg>
                            <span class="d-none d-sm-inline">{{ __('Add') }}</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="page-body">
            <div class="container-fluid">

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

                <!-- Alert Wrapper Container -->
                <div id="alertContainer">
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
                </div>

                <div class="row row-deck row-cards">
                    <!-- Dropdown Selectors Card -->
                    <div class="col-sm-12 col-lg-12 mb-3">
                        <div class="card p-3 shadow-sm">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label
                                        class="form-label small text-muted text-uppercase">{{ __('Target Language') }}</label>
                                    <select id="localeSelect" class="form-select" onchange="filterTranslations()">
                                        @foreach ($languages as $lang)
                                            <option value="{{ $lang['code'] }}"
                                                {{ $locale === $lang['code'] ? 'selected' : '' }}>
                                                {{ __($lang['name']) }} ({{ $lang['code'] }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label
                                        class="form-label small text-muted text-uppercase">{{ __('Filter / Search') }}</label>
                                    <input type="text" id="keySearch" placeholder="{{ __('Type to search...') }}"
                                        value="{{ request('search') }}" class="form-control">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Key-Value Editing Grid Card -->
                    <div class="col-sm-12 col-lg-12">
                        <div class="card shadow-sm overflow-hidden" id="editorCardContainer">
                            <!-- Single Header Save Button -->
                            <div class="card-header d-flex align-items-center justify-content-between">
                                <div>
                                    <h4 class="card-title m-0">{{ __('Editing Language Pack:') }} <strong
                                            class="text-uppercase text-primary">{{ $locale }}</strong></h4>
                                    </p>
                                </div>
                                @if (!empty($paginatedSourceKeys))
                                    <button type="button" onclick="document.getElementById('translationsForm').submit();"
                                        class="btn btn-primary d-inline-flex align-items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg"
                                            class="icon icon-tabler icon-tabler-device-floppy m-0" width="24"
                                            height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                            fill="none" stroke-linecap="round" stroke-linejoin="round">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                            <path d="M6 4h10l4 4v10a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2">
                                            </path>
                                            <path d="M12 14m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"></path>
                                            <path d="M14 4l0 4l-4 0l0 -4"></path>
                                        </svg>
                                        <span class="d-none d-sm-inline">{{ __('Save Changes') }}</span>
                                    </button>
                                @endif
                            </div>

                            <form id="translationsForm" action="{{ route('translation-manager.update', $locale) }}"
                                method="POST">
                                @csrf
                                <input type="hidden" name="group" value="{{ $selectedGroup }}">
                                <input type="hidden" name="type" value="{{ $selectedType }}">
                                <input type="hidden" name="page" value="{{ $currentPage }}">
                                <input type="hidden" name="search" value="{{ $search ?? '' }}">

                                <div class="card-body p-0">
                                    <!-- Desktop Grid Headers (Hidden on Mobile) -->
                                    <div class="d-none d-md-flex p-3 font-weight-bold text-muted border-bottom row g-0"
                                        style="font-size: 11px; letter-spacing: 0.05em; text-transform: uppercase;">
                                        <div class="col-md-2">{{ __('Category') }}</div>
                                        <div class="col-md-3">{{ __('Value') }}</div>
                                        <div class="col-md-3">{{ __('Source') }} ({{ strtoupper($sourceLocale) }})</div>
                                        <div class="col-md-4">{{ strtoupper($locale) }}</div>
                                    </div>

                                    <!-- Container -->
                                    <div class="divide-y">
                                        @forelse($paginatedSourceKeys as $compoundKey => $sourceValue)
                                            @php
                                                $groupName = $selectedGroup;
                                                $cleanKey = $compoundKey;
                                                if (str_contains($compoundKey, '|||')) {
                                                    [$groupName, $type, $cleanKey] = explode('|||', $compoundKey, 3);
                                                }
                                            @endphp

                                            <!-- Grid alignment -->
                                            <div class="p-3 row g-0 align-items-md-center border-bottom translation-row"
                                                data-key="{{ strtolower($cleanKey) }}">

                                                <!-- Category Column -->
                                                <div class="col-12 col-md-2 text-muted text-uppercase small mb-2 mb-md-0">
                                                    <span class="d-md-none font-weight-bold">{{ __('Category') }}:</span>
                                                    <span class="badge d-md-none">{{ $groupName }}</span>
                                                    <span class="d-none d-md-inline">{{ $groupName }}</span>
                                                </div>

                                                <!-- Value Column -->
                                                <div class="col-12 col-md-3 text-dark break-all pe-md-3 mb-2 mb-md-0">
                                                    <span
                                                        class="d-md-none small text-muted font-weight-bold">{{ __('Value') }}:</span>
                                                    <span class="font-weight-semibold">{{ $cleanKey }}</span>
                                                </div>

                                                <!-- Source Template Column -->
                                                <div class="col-12 col-md-3 text-muted text-wrap pe-md-3 mb-2 mb-md-0">
                                                    <div class="d-md-none p-2 rounded mb-1 small">
                                                        <strong class="text-muted">{{ __('Source') }}
                                                            ({{ strtoupper($sourceLocale) }})
                                                            :</strong>
                                                        <div class="text-wrap text-dark mt-1">
                                                            {{ is_array($sourceValue) ? json_encode($sourceValue) : $sourceValue }}
                                                        </div>
                                                    </div>
                                                    <span class="d-none d-md-inline">
                                                        {{ is_array($sourceValue) ? json_encode($sourceValue) : $sourceValue }}
                                                    </span>
                                                </div>

                                                <!-- Textarea Input Column -->
                                                <div class="col-12 col-md-4 pe-md-1">
                                                    <label
                                                        class="form-label d-md-none small font-weight-bold text-dark mb-1">{{ strtoupper($locale) }}</label>
                                                    <textarea name="translations[{{ $compoundKey }}]" rows="2" class="form-control"
                                                        placeholder="{{ __('Enter translation...') }}">{{ $translations[$compoundKey] ?? '' }}</textarea>
                                                </div>
                                            </div>
                                        @empty
                                            <div class="text-center text-muted py-5">
                                                {{ __('No values found inside this selection filter.') }}
                                            </div>
                                        @endforelse
                                    </div>
                                </div>

                                <!-- Pagination Footer -->
                                <div
                                    class="card-footer d-flex flex-column flex-md-row align-items-center justify-content-between gap-3">
                                    <div class="d-none d-md-block">
                                        <p class="m-0 text-muted small">
                                            {{ __('Showing') }} <span>{{ ($currentPage - 1) * $perPage + 1 }}</span>
                                            {{ __('to') }}
                                            <span>{{ min($currentPage * $perPage, $totalItems) }}</span>
                                            {{ __('of') }} <span>{{ $totalItems }}</span> {{ __('entries') }}
                                        </p>
                                    </div>

                                    <div class="mx-auto me-md-0 ms-md-auto">
                                        @if ($totalPages > 1)
                                            @php
                                                $startPage = max(1, $currentPage - 1);
                                                $endPage = min($totalPages, $startPage + 2);
                                                if ($endPage - $startPage < 2) {
                                                    $startPage = max(1, $endPage - 2);
                                                }
                                            @endphp
                                            <div class="d-flex align-items-center gap-1">
                                                <!-- Prev Button -->
                                                <a class="btn btn-primary btn-sm pagination-btn {{ $currentPage <= 1 ? 'disabled' : '' }}"
                                                    href="{{ $currentPage <= 1 ? '#' : route('translation-manager.edit', ['locale' => $locale, 'group' => $selectedGroup, 'type' => $selectedType, 'page' => $currentPage - 1, 'search' => $search]) }}"
                                                    tabindex="-1"
                                                    @if ($currentPage <= 1) aria-disabled="true" @endif>
                                                    {{ __('Prev') }}
                                                </a>

                                                @for ($i = $startPage; $i <= $endPage; $i++)
                                                    <a class="btn btn-sm pagination-btn {{ $currentPage == $i ? 'btn-primary text-white fw-bold' : 'btn-ghost-primary' }}"
                                                        href="{{ route('translation-manager.edit', ['locale' => $locale, 'group' => $selectedGroup, 'type' => $selectedType, 'page' => $i, 'search' => $search]) }}">
                                                        {{ $i }}
                                                    </a>
                                                @endfor

                                                <!-- Go To Page Action Toggle Button -->
                                                <a class="pagination-btn px-2" data-bs-toggle="modal"
                                                    data-bs-target="#goToPageModal" title="{{ __('Go to page') }}"
                                                    style="cursor: pointer;">
                                                    <svg xmlns="http://www.w3.org/2000/svg"
                                                        class="icon icon-tabler icon-tabler-dots m-0" width="24"
                                                        height="24" viewBox="0 0 24 24" stroke-width="2"
                                                        stroke="currentColor" fill="none" stroke-linecap="round"
                                                        stroke-linejoin="round">
                                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                                        <path d="M5 12m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0"></path>
                                                        <path d="M12 12m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0"></path>
                                                        <path d="M19 12m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0"></path>
                                                    </svg>
                                                </a>

                                                <!-- Next Button -->
                                                <a class="btn btn-primary btn-sm pagination-btn {{ $currentPage >= $totalPages ? 'disabled' : '' }}"
                                                    href="{{ $currentPage >= $totalPages ? '#' : route('translation-manager.edit', ['locale' => $locale, 'group' => $selectedGroup, 'type' => $selectedType, 'page' => $currentPage + 1, 'search' => $search]) }}"
                                                    @if ($currentPage >= $totalPages) aria-disabled="true" @endif>
                                                    {{ __('Next') }}
                                                </a>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Word Modal -->
    <div class="modal modal-bg fade" id="addKeyModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <form action="{{ route('translation-manager.add-key') }}" method="POST">
                    @csrf
                    <input type="hidden" name="locale" value="{{ $locale }}">
                    <input type="hidden" name="group" value="single">

                    <div class="modal-header">
                        <h5 class="modal-title font-weight-bold">{{ __('Add New Translation Word') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-start">
                        <div class="mb-3">
                            <label class="form-label font-weight-semibold">{{ __('Source Word (English)') }}</label>
                            <textarea name="source_value" class="form-control" rows="2" placeholder="{{ __('Default English Word') }}"
                                required></textarea>
                        </div>
                        <div class="mb-3">
                            <label
                                class="form-label font-weight-semibold">{{ __('Localized Word (:locale)', ['locale' => strtoupper($locale)]) }}</label>
                            <textarea name="target_value" class="form-control" rows="2"
                                placeholder="{{ __('Local language translation (optional)') }}"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary ms-auto">{{ __('Save') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Go To Page Modal -->
    <div class="modal modal-bg fade" id="goToPageModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
            <div class="modal-content">
                <div class="modal-header py-2">
                    <h5 class="modal-title font-weight-bold">{{ __('Go to Page') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body py-3 text-start">
                    <div class="mb-2">
                        <label class="form-label font-weight-semibold"
                            for="targetPageInput">{{ __('Page Number') }}</label>
                        <input type="number" id="targetPageInput" class="form-control" min="1"
                            max="{{ $totalPages }}" value="{{ $currentPage }}" required>
                        <span
                            class="form-hint small text-muted mt-2 d-block">{{ __('Enter a page between 1 and :max', ['max' => $totalPages]) }}</span>
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" onclick="executeGoToPage()"
                        class="btn btn-sm btn-primary">{{ __('Go') }}</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Tom Select Assets and Client-side Javascript initialization -->
    <script type="text/javascript" src="{{ asset('js/tom-select.base.min.js') }}"></script>
    <script>
        let debounceTimer;

        function showEditorAlert(message, type = 'danger') {
            const container = document.getElementById('alertContainer');
            if (!container) return;

            const alertHtml = `
                <div class="alert alert-important alert-${type} alert-dismissible mb-2" role="alert">
                    <div class="d-flex">
                        <div>
                            ${message}
                        </div>
                    </div>
                    <a class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="close"></a>
                </div>
            `;
            container.innerHTML = alertHtml;
        }

        function loadTranslations(url) {
            const container = document.getElementById('editorCardContainer');
            if (!container) return;

            container.style.opacity = '0.5';

            fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');

                    container.innerHTML = doc.getElementById('editorCardContainer').innerHTML;
                    container.style.opacity = '1';

                    // Re-initialize Tom Select on newly loaded dynamic markup if select elements are present inside the updated container
                    initializeTomSelect();

                    // Scroll cleanly to the top of the window on successful pagination loads
                    window.scrollTo({
                        top: 0,
                        behavior: 'smooth'
                    });

                    history.pushState(null, '', url);
                })
                .catch(error => {
                    console.error('AJAX Load Error:', error);
                    container.style.opacity = '1';
                });
        }

        function filterTranslations() {
            const locale = document.getElementById('localeSelect').value;
            const group = 'all';
            const type = 'all';

            const searchVal = document.getElementById('keySearch').value.trim();
            const url =
                `{{ url('admin/translations') }}/${locale}/edit?group=${group}&type=${type}&search=${encodeURIComponent(searchVal)}&page=1`;
            loadTranslations(url);
        }

        function executeGoToPage() {
            const pageInput = document.getElementById('targetPageInput');
            if (!pageInput) return;

            const pageVal = parseInt(pageInput.value, 10);
            const maxPage = parseInt(pageInput.getAttribute('max'), 10);

            // Close the modal cleanly in both successful and failed navigation states
            const modalElement = document.getElementById('goToPageModal');
            const modalInstance = bootstrap.Modal.getInstance(modalElement);
            if (modalInstance) {
                modalInstance.hide();
            }

            if (isNaN(pageVal) || pageVal < 1 || pageVal > maxPage) {
                // Scroll cleanly to the top of the page so the alert message is visible
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });

                showEditorAlert('{{ __('Please enter a valid page number.') }}', 'danger');

                // Auto-hide the alert after 4 seconds
                setTimeout(() => {
                    const alertEl = document.querySelector('#alertContainer .alert');
                    if (alertEl) {
                        if (window.bootstrap && bootstrap.Alert) {
                            const bsAlert = bootstrap.Alert.getOrCreateInstance(alertEl);
                            bsAlert.close();
                        } else {
                            alertEl.remove();
                        }
                    }
                }, 4000);

                return;
            }

            // Generate target AJAX URL matching the current filter state
            const locale = '{{ $locale }}';
            const group = '{{ $selectedGroup }}';
            const type = '{{ $selectedType }}';
            const searchVal = document.getElementById('keySearch').value.trim();

            const url =
                `{{ url('admin/translations') }}/${locale}/edit?group=${group}&type=${type}&search=${encodeURIComponent(searchVal)}&page=${pageVal}`;

            loadTranslations(url);
        }

        function initializeTomSelect() {
            document.querySelectorAll('select').forEach((el) => {
                // Avoid re-initializing already converted element configurations
                if (el.tomselect) return;

                new TomSelect(el, {
                    create: false,
                    controlInput: null
                });
            });
        }

        document.addEventListener('DOMContentLoaded', () => {
            // Initializing Tom Select UI Components on startup
            initializeTomSelect();

            const searchInput = document.getElementById('keySearch');

            if (searchInput) {
                searchInput.addEventListener('input', () => {
                    clearTimeout(debounceTimer);
                    debounceTimer = setTimeout(() => {
                        filterTranslations();
                    }, 400);
                });
            }

            // Intercept standard click events on pagination links to route them via AJAX instead
            document.addEventListener('click', (e) => {
                const pageLink = e.target.closest('.pagination .page-link, .page-wrapper .pagination-btn');
                if (pageLink) {
                    e.preventDefault();
                    const url = pageLink.getAttribute('href');
                    if (url && url !== '#') {
                        loadTranslations(url);
                    }
                }
            });
        });

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
