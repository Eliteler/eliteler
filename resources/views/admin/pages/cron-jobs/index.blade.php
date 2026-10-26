@extends('admin.layouts.index', ['header' => true, 'nav' => true, 'demo' => true])

{{-- Custom CSS --}}
@section('css')
    <style>
        .ts-control>input {
            display: contents !important;
        }

        .ts-control {
            line-height: 1.7 !important;
        }
    </style>
@endsection

@section('content')
    <div class="page-wrapper">
        <!-- Page title header -->
        <div class="page-header d-print-none">
            <div class="container-fluid">
                <div class="row g-2 align-items-center">
                    <!-- Breadcrumbs (Replaces Overview pretitle) -->
                    <div class="ms-0">
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
                                <a href="#">{{ __('Cron Jobs') }}</a>
                            </li>
                        </ol>
                    </div>
                    <div class="col">
                        <h2 class="page-title">
                            {{ __('Cron Jobs') }}
                        </h2>
                        <!-- Added helper subtitle under page heading -->
                        <div class="text-muted mt-1">
                            {{ __('Configure scheduled tasks and generate the CRON command for your server.') }}
                        </div>
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

                <div class="row g-3 row-deck row-cards">
                    {{-- Form 1: Expiration Reminder Intervals --}}
                    <div class="col-12">
                        <form action="{{ route('admin.update.cron.jobs') }}" method="post" class="card border-0 shadow-sm">
                            @csrf
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-xl-12">
                                        <div class="row">
                                            {{-- Dates --}}
                                            <div class="col-12 col-lg-8">
                                                <div class="mb-3">
                                                    <label for="dates_in_array"
                                                        class="form-label required">{{ __('Dates') }}</label>
                                                    <input type="text" class="form-control" name="dates_in_array"
                                                        id="dates_in_array"
                                                        value="{{ old('dates_in_array') ?? $config[60]->config_value }}"
                                                        placeholder="{{ __('Dates') }}" autocomplete="dates_in_array"
                                                        autofocus required>
                                                    {{-- Notes --}}
                                                    <div class="form-text text-secondary mt-2">
                                                        {{ __('Dates format: -30,1,3,5,10,366') }}
                                                        <br>
                                                        {{ __('Min: -30') }} | {{ __('Max: 366') }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Double Button Footer Layout --}}
                            <div class="card-footer text-end">
                                <div class="d-flex align-items-center">
                                    <a href="{{ route('admin.test.reminder') }}"
                                        class="btn btn-outline-primary btn-md d-inline-flex align-items-center">{{ __('Send Test Reminder') }}</a>
                                    <button type="submit"
                                        class="btn btn-primary btn-md ms-auto d-inline-flex align-items-center">
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
                                        {{ __('Update') }}
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    {{-- Form 2: Set Cronjob Time --}}
                    <div class="col-12">
                        <form action="{{ route('admin.set.cronjob.time') }}" method="post"
                            class="card border-0 shadow-sm">
                            @csrf
                            <div class="card-header">
                                <h4 class="card-title">{{ __('Set Cronjob time') }}</h4>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-xl-12">
                                        <div class="row g-3">
                                            {{-- 24 hours select list (Labeled as Run Time) --}}
                                            <div class="col-md-6 col-lg-4">
                                                <div class="mb-3">
                                                    <label for="cron-hour"
                                                        class="form-label required">{{ __('Run Time') }}</label>
                                                    <select id="cron-hour" name="cron_hour" class="form-select"
                                                        onchange="generateCronCommand()" required>
                                                        @for ($i = 0; $i < 24; $i++)
                                                            <option value="{{ $i }}"
                                                                {{ $i == $config[61]->config_value ? 'selected' : '' }}>
                                                                {{ $i <= 9 ? 0 : '' }}{{ $i }}:00</option>
                                                        @endfor
                                                    </select>
                                                </div>
                                            </div>

                                            {{-- Command preview inside dynamic input group --}}
                                            <div class="col-md-6 col-lg-8">
                                                <div class="mb-3">
                                                    <label for="cron-command"
                                                        class="form-label required">{{ __('Your CRON Command:') }}</label>
                                                    <div class="input-group">
                                                        <input type="text" id="cron-command"
                                                            class="form-control font-monospace text-secondary select-all"
                                                            value="" readonly>
                                                        <button class="btn btn-primary btn-icon flex-shrink-0"
                                                            type="button" id="copy-button" onclick="copyToClipboard()"
                                                            title="{{ __('Copy to Clipboard') }}">
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="24"
                                                                height="24" viewBox="0 0 24 24" fill="none"
                                                                stroke="currentColor" stroke-width="2"
                                                                stroke-linecap="round" stroke-linejoin="round"
                                                                class="icon icon-tabler icons-tabler-outline icon-tabler-copy">
                                                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                                <path
                                                                    d="M7 7m0 2.667a2.667 2.667 0 0 1 2.667 -2.667h8.666a2.667 2.667 0 0 1 2.667 2.667v8.666a2.667 2.667 0 0 1 -2.667 2.667h-8.666a2.667 2.667 0 0 1 -2.667 -2.667z" />
                                                                <path
                                                                    d="M4.012 16.737a2.005 2.005 0 0 1 -1.012 -1.737v-10c0 -1.1 .9 -2 2 -2h10c.75 0 1.158 .385 1.5 1" />
                                                            </svg>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Double Button Footer Layout --}}
                            <div class="card-footer text-end">
                                <div class="d-flex align-items-center">
                                    <a href="{{ route('admin.set.cronjob') }}"
                                        class="btn btn-outline-primary btn-md d-inline-flex align-items-center">{{ __('Run Cronjob') }}</a>
                                    <button type="submit"
                                        class="btn btn-primary btn-md ms-auto d-inline-flex align-items-center">
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
                                        {{ __('Save') }}
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    {{-- Form 3: Queue Worker Cron Command --}}
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header">
                                <h4 class="card-title">{{ __('Queue Worker Cron Command') }}</h4>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-xl-12">
                                        <div class="text-muted mb-3">
                                            {{ __('Background jobs (like translation sync) require a queue worker running continuously. On shared hosting, add this cron job to run every minute - it processes queued jobs then exits automatically, so it never lingers as a stuck process.') }}
                                        </div>
                                        <div class="row g-3">
                                            <div class="col-12">
                                                <div class="mb-3">
                                                    <label for="queue-worker-command"
                                                        class="form-label required">{{ __('Your Queue Worker CRON Command:') }}</label>
                                                    <div class="input-group">
                                                        <input type="text" id="queue-worker-command"
                                                            class="form-control font-monospace text-secondary select-all"
                                                            value="" readonly>
                                                        <button class="btn btn-primary btn-icon flex-shrink-0"
                                                            type="button" id="copy-queue-button"
                                                            onclick="copyQueueCommandToClipboard()"
                                                            title="{{ __('Copy to Clipboard') }}">
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="24"
                                                                height="24" viewBox="0 0 24 24" fill="none"
                                                                stroke="currentColor" stroke-width="2"
                                                                stroke-linecap="round" stroke-linejoin="round"
                                                                class="icon icon-tabler icons-tabler-outline icon-tabler-copy">
                                                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                                <path
                                                                    d="M7 7m0 2.667a2.667 2.667 0 0 1 2.667 -2.667h8.666a2.667 2.667 0 0 1 2.667 2.667v8.666a2.667 2.667 0 0 1 -2.667 2.667h-8.666a2.667 2.667 0 0 1 -2.667 -2.667z" />
                                                                <path
                                                                    d="M4.012 16.737a2.005 2.005 0 0 1 -1.012 -1.737v-10c0 -1.1 .9 -2 2 -2h10c.75 0 1.158 .385 1.5 1" />
                                                            </svg>
                                                        </button>
                                                    </div>
                                                    <div class="form-text text-secondary mt-2">
                                                        {{ __('Add this as a separate cron entry running every minute (* * * * *). If your host provides Supervisor/SSH access instead, run "php artisan queue:work" as a persistent Supervisor-managed process rather than via cron.') }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Footer --}}
        @include('admin.includes.footer')
    </div>
@endsection

{{-- Custom JS --}}
@section('scripts')
    {{-- Tom Select --}}
    <script type="text/javascript" src="{{ asset('js/tom-select.base.min.js') }}"></script>
    {{-- Clipboard --}}
    <script src="{{ asset('js/clipboard.min.js') }}"></script>
    <script>
        // Call generateCronCommand function to page onload
        generateCronCommand();

        // Generate cronjob command
        function generateCronCommand() {
            "use strict";

            const hour = document.getElementById('cron-hour').value;
            const projectPath = "{{ str_replace('\\', '/', base_path()) }}";
            const cronCommand = `0 ${hour} * * * php ${projectPath}/artisan schedule:run >> /dev/null 2>&1`;
            document.getElementById('cron-command').value = cronCommand;
        }

        // Copy to clipboard
        function copyToClipboard() {
            "use strict";
            var copyText = document.getElementById("cron-command");
            copyText.select();
            copyText.setSelectionRange(0, 99999);
            document.execCommand("copy");

            // Change the button text to "Copied!"
            document.getElementById("copy-button").innerHTML =
                `<svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-clipboard-check"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 5h-2a2 2 0 0 0 -2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-12a2 2 0 0 0 -2 -2h-2" /><path d="M9 3m0 2a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v0a2 2 0 0 1 -2 2h-2a2 2 0 0 1 -2 -2z" /><path d="M9 14l2 2l4 -4" /></svg>`;
            // Change the button color to green
            document.getElementById("copy-button").style.backgroundColor = "#4CAF50";

            // Remove the "Copied!" text after 2 seconds
            setTimeout(function() {
                document.getElementById("copy-button").innerHTML =
                    `<svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-copy"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M7 7m0 2.667a2.667 2.667 0 0 1 2.667 -2.667h8.666a2.667 2.667 0 0 1 2.667 2.667v8.666a2.667 2.667 0 0 1 -2.667 2.667h-8.666a2.667 2.667 0 0 1 -2.667 -2.667z" /><path d="M4.012 16.737a2.005 2.005 0 0 1 -1.012 -1.737v-10c0 -1.1 .9 -2 2 -2h10c.75 0 1.158 .385 1.5 1" /></svg>`;
                // Change the button color back to original (add class)
                document.getElementById("copy-button").style.backgroundColor = "#066fd1";
            }, 2000);
        }
    </script>
    <script>
        // Array of element IDs
        var elementSelectors = ['cron-hour'];

        // Function to initialize TomSelect and enforce the "required" attribute
        function initializeTomSelectWithRequired(id) {
            var el = document.getElementById(id);
            if (!el) return;

            new TomSelect(el, {
                copyClassesToDropdown: false,
                dropdownClass: 'dropdown-menu ts-dropdown',
                optionClass: 'dropdown-item',
                controlInput: '<input>',
                maxOptions: null,
                render: {
                    item: function(data, escape) {
                        if (data.customProperties) {
                            return '<div><span class="dropdown-item-indicator">' + data.customProperties +
                                '</span>' + escape(data.text) + '</div>';
                        }
                        return '<div>' + escape(data.text) + '</div>';
                    },
                    option: function(data, escape) {
                        if (data.customProperties) {
                            return '<div><span class="dropdown-item-indicator">' + data.customProperties +
                                '</span>' + escape(data.text) + '</div>';
                        }
                        return '<div>' + escape(data.text) + '</div>';
                    },
                },
            });

            // Ensure the "required" attribute is enforced
            el.addEventListener('change', function() {
                if (el.value) {
                    el.setCustomValidity('');
                } else {
                    el.setCustomValidity('This field is required');
                }
            });

            // Trigger validation on load
            el.dispatchEvent(new Event('change'));
        }

        // Loop through each element ID
        elementSelectors.forEach(function(id) {
            initializeTomSelectWithRequired(id);
        });
    </script>
    <script>
        // Call on page load, alongside the existing generateCronCommand()
        generateQueueWorkerCommand();

        // Generate the queue worker cron command
        function generateQueueWorkerCommand() {
            "use strict";

            const projectPath = "{{ str_replace('\\', '/', base_path()) }}";
            const queueCommand =
                `* * * * * php ${projectPath}/artisan queue:work --stop-when-empty >> /dev/null 2>&1`;
            document.getElementById('queue-worker-command').value = queueCommand;
        }

        // Copy queue worker command to clipboard
        function copyQueueCommandToClipboard() {
            "use strict";
            var copyText = document.getElementById("queue-worker-command");
            copyText.select();
            copyText.setSelectionRange(0, 99999);
            document.execCommand("copy");

            document.getElementById("copy-queue-button").innerHTML =
                `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-clipboard-check"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 5h-2a2 2 0 0 0 -2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-12a2 2 0 0 0 -2 -2h-2" /><path d="M9 3m0 2a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v0a2 2 0 0 1 -2 2h-2a2 2 0 0 1 -2 -2z" /><path d="M9 14l2 2l4 -4" /></svg>`;
            document.getElementById("copy-queue-button").style.backgroundColor = "#4CAF50";

            setTimeout(function() {
                document.getElementById("copy-queue-button").innerHTML =
                    `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-copy"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M7 7m0 2.667a2.667 2.667 0 0 1 2.667 -2.667h8.666a2.667 2.667 0 0 1 2.667 2.667v8.666a2.667 2.667 0 0 1 -2.667 2.667h-8.666a2.667 2.667 0 0 1 -2.667 -2.667z" /><path d="M4.012 16.737a2.005 2.005 0 0 1 -1.012 -1.737v-10c0 -1.1 .9 -2 2 -2h10c.75 0 1.158 .385 1.5 1" /></svg>`;
                document.getElementById("copy-queue-button").style.backgroundColor = "#066fd1";
            }, 2000);
        }
    </script>
@endsection
