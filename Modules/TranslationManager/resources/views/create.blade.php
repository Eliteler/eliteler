@extends('admin.layouts.index', ['header' => true, 'nav' => true, 'demo' => true])

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
                                <a href="#">{{ __('Create') }}</a>
                            </li>
                        </ol>
                    </div>
                    <div class="col">
                        <h2 class="page-title">
                            {{ __('Create Language') }}
                        </h2>
                        <div class="text-muted mt-1">
                            {{ __('Initialize a new language folder structured for translation files and copy basic defaults.') }}
                        </div>
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

                <div class="row">
                    <div class="col-sm-12 col-md-6 col-lg-4 mb-3">
                        <div class="card">
                            <div class="card-body">
                                <form action="{{ route('translation-manager.store') }}" method="POST">
                                    @csrf
                                    <div class="mb-3">
                                        <label for="name" class="form-label required">{{ __('Language Name') }}</label>
                                        <input type="text" name="name" id="name" placeholder="e.g., Tamil"
                                            class="form-control" required>
                                    </div>

                                    <div class="mb-3">
                                        <label for="code" class="form-label required">{{ __('Language Code') }}</label>
                                        <input type="text" name="code" id="code" placeholder="e.g., ta"
                                            class="form-control" required>
                                    </div>

                                    <div class="mb-4">
                                        <label for="copy_from"
                                            class="form-label">{{ __('Copy Base Content From') }}</label>
                                        <select name="copy_from" id="copy_from" class="form-select">
                                            @foreach ($languages as $lang)
                                                <option value="{{ $lang['code'] }}"
                                                    {{ $lang['code'] === 'en' ? 'selected' : '' }}>
                                                    {{ __($lang['name']) }} ({{ $lang['code'] }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="d-flex justify-content-end gap-2 border-top pt-3">
                                        <button type="submit" class="btn btn-primary">
                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                class="icon icon-tabler icon-tabler-device-floppy me-2" width="24"
                                                height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                                fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                <path
                                                    d="M6 4h10l4 4v10a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2" />
                                                <path d="M12 14m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" />
                                                <path d="M14 4l0 4h-6l0 -4" />
                                            </svg>
                                            {{ __('Save Language') }}
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tom Select Assets and Client-side Javascript initialization -->
    <script type="text/javascript" src="{{ asset('js/tom-select.base.min.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('select').forEach((el) => {
                if (el.tomselect) return;

                new TomSelect(el, {
                    create: false,
                    controlInput: null
                });
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
