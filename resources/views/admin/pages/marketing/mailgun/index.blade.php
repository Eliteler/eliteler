@extends('admin.layouts.index', ['header' => true, 'nav' => true, 'demo' => true])

{{-- Custom CSS --}}
@section('css')
    <style>
        .ts-control>input {
            display: contents !important;
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
                    <div class="mb-1 ms-0">
                        <ol class="breadcrumb" aria-label="breadcrumbs">
                            <li class="breadcrumb-item">
                                <a href="{{ route('admin.dashboard') }}" class="text-secondary text-decoration-none">
                                    {{ __('Dashboard') }}
                                </a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="{{ route('admin.marketing.campaigns') }}" class="text-secondary text-decoration-none">
                                    {{ __('Email Marketing') }}
                                </a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">
                                <a href="#">{{ __('Mailgun Configuration') }}</a>
                            </li>
                        </ol>
                    </div>
                    <div class="col">
                        <h2 class="page-title">
                            {{ __('Mailgun Configuration') }}
                        </h2>
                        <!-- Merged the documentation link and helper text into a clean sub-heading description -->
                        <div class="text-muted mt-1">
                            {{ __('Configure your Mailgun API keys and domain regions to enable bulk transactional emailing.') }}
                            <span class="ms-1">{{ __('For step-by-step setup instructions,') }}
                                {!! __(
                                    '<a href="https://nativecode.in/docs/gobiz/setup/where-can-I-find-my-API-keys-and-SMTP-credentials.html" target="_blank" class="link-primary text-decoration-none fw-bold">click here</a>',
                                ) !!}.</span>
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

                <div class="row row-deck row-cards">
                    <div class="col-md-6 col-sm-12">
                        <form action="{{ route('admin.marketing.mailgun.update') }}" method="post"
                            class="card border-0 shadow-sm">
                            @csrf
                            <div class="card-header">
                                <h4 class="page-title">{{ __('Configuration') }}</h4>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    {{-- Mailgun Region --}}
                                    <div class="col-md-12 col-xl-6">
                                        <div class="mb-3">
                                            <div class="form-label required">{{ __('Mailgun Region') }}</div>
                                            <select class="form-control" id="mailgun_region" name="mailgun_region">
                                                <option value="us"
                                                    {{ $config[95]->config_value == 'us' ? 'selected' : '' }}>
                                                    {{ __('US') }}</option>
                                                <option value="eu"
                                                    {{ $config[95]->config_value == 'eu' ? 'selected' : '' }}>
                                                    {{ __('EU') }}</option>
                                            </select>
                                        </div>
                                    </div>

                                    {{-- Mailgun API Key --}}
                                    <div class="col-md-12 col-xl-6">
                                        <div class="mb-3">
                                            <div class="form-label required">{{ __('Mailgun API Key') }}</div>
                                            <input type="text" class="form-control" id="mailgun_api_key"
                                                name="mailgun_api_key" value="{{ $config[58]->config_value }}"
                                                placeholder="{{ __('Enter Mailgun API Key') }}" required>
                                        </div>
                                    </div>

                                    {{-- Mailgun From Email --}}
                                    <div class="col-md-12 col-xl-6">
                                        <div class="mb-3">
                                            <div class="form-label required">{{ __('Mailgun From Email') }}</div>
                                            <input type="text" class="form-control" id="mailgun_from_email"
                                                name="mailgun_from_email" value="{{ $config[59]->config_value }}"
                                                placeholder="{{ __('Enter Mailgun From Email') }}" required>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer text-end">
                                <button type="submit" class="btn btn-primary btn-md d-inline-flex align-items-center">
                                    <!-- Replaced plain save text button with custom dual layout & Floppy Disk icon -->
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                        class="icon icon-tabler icon-tabler-device-floppy me-2" width="24"
                                        height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                        fill="none" stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                        <path d="M6 4h10l4 4v10a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2" />
                                        <path d="M12 14m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" />
                                        <path d="M14 4l0 4h-6l0 -4" />
                                    </svg>
                                    {{ __('Save') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        @include('admin.includes.footer')
    </div>
@endsection

{{-- Custom JS --}}
@section('scripts')
    <script type="text/javascript" src="{{ asset('js/tom-select.base.min.js') }}"></script>
    <script>
        // Array of element IDs
        var elementSelectors = ['mailgun_region'];

        // Function to initialize TomSelect and enforce the "required" attribute
        function initializeTomSelectWithRequired(el) {
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
            // Check if the element exists
            var el = document.getElementById(id);
            if (el) {
                // Apply TomSelect and enforce the "required" attribute
                initializeTomSelectWithRequired(el);
            }
        });
    </script>
@endsection
