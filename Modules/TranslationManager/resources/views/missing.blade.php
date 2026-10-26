@extends('admin.layouts.index', ['header' => true, 'nav' => true, 'demo' => true])

@section('css')
    <style>
        .rotating {
            animation: spin 1.2s linear infinite;
        }

        @keyframes spin {
            from {
                transform: rotate(0deg);
            }

            to {
                transform: rotate(360deg);
            }
        }

        /* Remove card animation on load */
        .card {
            translate: none;
            rotate: none;
            scale: none;
            transform: translate(0px, 16px);
            opacity: 1 !important;
            visibility: visible !important;
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
                                <a href="#">{{ __('Missing Words') }}</a>
                            </li>
                        </ol>
                    </div>
                    <div class="col">
                        <h2 class="page-title">
                            {{ __('Missing Words Detection') }}
                        </h2>
                        <div class="text-muted mt-1">
                            {{ __('Comparing') }} <strong class="text-primary">{{ strtoupper($sourceLocale) }}</strong>
                            ({{ __('Source') }}) {{ __('with') }} <strong
                                class="text-primary">{{ strtoupper($locale) }}</strong> ({{ __('Target') }}).
                        </div>
                    </div>
                    <!-- Automatic Sync Action -->
                    @if (!empty($missingKeys))
                        <div class="col-auto ms-auto d-print-none">
                            <form action="{{ route('translation-manager.sync-missing', $locale) }}" method="POST"
                                id="sync-missing-form">
                                @csrf
                                <input type="hidden" name="source_locale" value="{{ $sourceLocale }}">
                                <button type="submit" id="sync-missing-btn"
                                    class="btn btn-primary btn-icon-only d-inline-flex align-items-center"
                                    {{ $syncInProgress ? 'disabled' : '' }}>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round"
                                        class="icon icon-tabler icons-tabler-outline icon-tabler-refresh {{ $syncInProgress ? 'rotating' : '' }}"
                                        id="sync-missing-icon">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                        <path d="M20 11a8.1 8.1 0 0 0 -15.5 -2m-.5 -4v4h4" />
                                        <path d="M4 13a8.1 8.1 0 0 0 15.5 2m.5 4v-4h-4" />
                                    </svg>
                                    <span class="d-none d-sm-inline" id="sync-missing-label">
                                        {{ $syncInProgress ? __('Syncing…') : __('Sync Missing Words') }}
                                    </span>
                                </button>
                            </form>
                        </div>
                    @endif
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

                <div class="row row-cards">
                    @forelse($missingKeys as $group => $details)
                        <div class="col-sm-12 col-lg-12 mb-3">
                            <div class="card shadow-sm">
                                <div class="card-header d-flex justify-content-between align-items-center py-2">
                                    <h3 class="card-title m-0 text-dark font-weight-bold">{{ $group }}</h3>
                                    <span class="badge bg-warning text-white font-weight-bold" style="border-radius: 4px;">
                                        {{ count($details['keys']) }} {{ __('missing words') }}
                                    </span>
                                </div>

                                <!-- Responsive CSS Adaptive Table Grid -->
                                <div class="card-body p-0">
                                    <!-- Desktop Table Headers (Hidden on Mobile) -->
                                    <div class="d-none d-md-flex p-3 font-weight-bold text-muted border-bottom"
                                        style="font-size: 11px; letter-spacing: 0.05em; text-transform: uppercase;">
                                        <div style="width: 50%">{{ __('Key Path') }}</div>
                                        <div style="width: 50%">{{ __('Default Value') }}
                                            ({{ strtoupper($sourceLocale) }})
                                        </div>
                                    </div>

                                    <!-- Rows Container -->
                                    <div class="divide-y">
                                        @foreach ($details['keys'] as $key => $val)
                                            <div
                                                class="p-3 d-flex flex-column d-md-flex flex-md-row align-items-md-center gap-1 gap-md-0 border-bottom">
                                                <!-- Key Path Column -->
                                                <div class="d-md-none small text-muted font-weight-bold mb-1">
                                                    {{ __('Word Path') }}:
                                                </div>
                                                <div class="font-monospace text-dark font-weight-bold break-all pe-3"
                                                    style="width: 50%; min-width: 50%;">
                                                    {{ $key }}
                                                </div>

                                                <!-- Default Value Column -->
                                                <div class="d-md-none p-2 rounded mb-1 small w-100 mt-1">
                                                    <strong class="text-muted">{{ __('Default Value') }}
                                                        ({{ strtoupper($sourceLocale) }})
                                                        :</strong>
                                                    <div class="text-wrap text-dark mt-1">{{ $val['source_value'] }}</div>
                                                </div>
                                                <div class="d-none d-md-block text-muted text-wrap pe-3" style="width: 50%">
                                                    {{ $val['source_value'] }}
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-sm-12 col-lg-12">
                            <div class="card text-center p-5 border-light">
                                <div class="card-body">
                                    <h3 class="text-dark">{{ __('No missing words found') }}</h3>
                                    <p class="text-muted m-0">
                                        {{ __('No missing words were detected between the selected language pairs.') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection

{{-- Custom JS --}}
@section('scripts')
    <script>
        // Sync button
        (function() {
            "use strict";

            const btn = document.getElementById('sync-missing-btn');
            const label = document.getElementById('sync-missing-label');
            const icon = document.getElementById('sync-missing-icon');

            if (!btn) return;

            const syncStatusUrl = @json(route('translation-manager.sync-status', $locale));
            const syncInProgress = @json($syncInProgress);

            let pollInterval = null;

            function setSyncing(isSyncing) {
                btn.disabled = isSyncing;
                label.textContent = isSyncing ? "{{ __('Syncing…') }}" : "{{ __('Sync Missing Words') }}";
                icon.classList.toggle('rotating', isSyncing);
            }

            function pollStatus() {
                fetch(syncStatusUrl, {
                        headers: {
                            'Accept': 'application/json'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        const state = data.state;

                        if (state === 'queued' || state === 'running') {
                            setSyncing(true);
                        } else {
                            setSyncing(false);
                            if (pollInterval) {
                                clearInterval(pollInterval);
                                pollInterval = null;
                            }
                            if (state === 'completed') {
                                location.reload();
                            }
                        }
                    })
                    .catch(() => {
                        // Network hiccup — don't flip the button state on a failed poll
                    });
            }

            if (syncInProgress) {
                pollInterval = setInterval(pollStatus, 3000);
            }

            document.getElementById('sync-missing-form').addEventListener('submit', function() {
                setSyncing(true);
                if (!pollInterval) {
                    pollInterval = setInterval(pollStatus, 3000);
                }
            });
        })();

        // Active sync status alert
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
