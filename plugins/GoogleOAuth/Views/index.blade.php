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
        <!-- Page Title Header Wrapper (Wraps the inner container-fluid) -->
        <div class="page-header d-print-none">
            <div class="container-fluid">
                <div class="row align-items-center">
                    <div class="col">
                        {{-- Breadcrumbs --}}
                        <x-breadcrumb :items="[
                            ['title' => 'Dashboard', 'route' => 'admin.dashboard'],
                            ['title' => 'Plugins', 'route' => 'admin.plugins'],
                            ['title' => 'Google Sign-In'],
                        ]" />

                        <h2 class="page-title">
                            {{ __('Google Sign-In') }}
                        </h2>

                        <!-- Helper Subtitle -->
                        <div class="text-muted mt-1">
                            {{ __('Configure your Google Sign-In secure credentials and manage Authorized Redirect URL parameters.') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Page Body -->
        <div class="page-body">
            <div class="container-fluid">
                {{-- Failed Alert --}}
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

                {{-- Success Alert --}}
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
                    <div class="col-12 col-md-6">
                        <!-- Upgraded to Borderless Shadowed Card Wrapper -->
                        <form action="{{ route('admin.google_oauth_settings.update') }}" method="post"
                            class="card border-0 shadow-sm">
                            @csrf
                            <div class="card-body">
                                <div class="row g-3">
                                    {{-- Enable Google Authentication --}}
                                    <div class="col-12 col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label required" for="google_auth_enable">
                                                {{ __('Enable Google Authentication') }}
                                            </label>
                                            <select class="form-select" id="google_auth_enable" name="google_auth_enable"
                                                required>
                                                <option value="" disabled>{{ __('Choose an option') }}</option>
                                                <option value="on"
                                                    {{ $google_configuration['GOOGLE_ENABLE'] == 'on' ? 'selected' : '' }}>
                                                    {{ __('On') }}</option>
                                                <option value="off"
                                                    {{ $google_configuration['GOOGLE_ENABLE'] == 'off' ? 'selected' : '' }}>
                                                    {{ __('Off') }}</option>
                                            </select>
                                        </div>
                                    </div>

                                    {{-- Google Client ID --}}
                                    <div class="col-12 col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label required">{{ __('Google Client ID') }}</label>
                                            <input type="text" class="form-control" name="google_client_id"
                                                value="{{ $google_configuration['GOOGLE_CLIENT_ID'] }}"
                                                placeholder="{{ __('Enter your Google OAuth Client ID') }}" required>
                                        </div>
                                    </div>

                                    {{-- Google Client Secret --}}
                                    <div class="col-12 col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label required">{{ __('Google Client Secret') }}</label>
                                            <input type="text" class="form-control" name="google_client_secret"
                                                value="{{ $google_configuration['GOOGLE_CLIENT_SECRET'] }}"
                                                placeholder="{{ __('Enter your Google OAuth Client Secret') }}" required>
                                        </div>
                                    </div>

                                    {{-- Google Redirect (Read-Only) --}}
                                    <div class="col-12 col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">{{ __('Authorized Redirect URL') }}</label>
                                            <input type="text" class="form-control" name="google_redirect"
                                                value="{{ url('/sign-in-with-google') }}" readonly>
                                        </div>
                                    </div>

                                    <div class="col-12 mt-2">
                                        <span class="small text-muted">
                                            {{ __('If you do not have a Google OAuth Client ID and Secret Key, you can follow') }}
                                            <a href="https://support.google.com/cloud/answer/6158849?hl=en#zippy=%2Cuser-consent%2Cpublic-and-internal-applications%2Cauthorized-domains/"
                                                target="_blank" class="text-decoration-none">
                                                {{ __('these setup steps.') }}
                                            </a>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Standardized Right-Aligned Form Footer (Omitted Cancel Button) -->
                            <div class="card-footer text-end">
                                <button type="submit" class="btn btn-primary btn-md d-inline-flex align-items-center">
                                    <!-- Device Floppy Icon -->
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

        {{-- Footer --}}
        @include('admin.includes.footer')
    </div>
@endsection

{{-- Custom JS --}}
@section('scripts')
    <!-- Script asset loaded inside correct scripts section -->
    <script type="text/javascript" src="{{ asset('js/tom-select.base.min.js') }}"></script>

    <script>
        "use strict";

        document.addEventListener("DOMContentLoaded", function() {
            var elementSelectors = ['google_auth_enable'];

            function initializeTomSelectWithRequired(el) {
                new TomSelect(el, {
                    copyClassesToDropdown: false,
                    dropdownClass: 'dropdown-menu ts-dropdown',
                    optionClass: 'dropdown-item',
                    controlInput: '<input>',
                    placeholder: "{{ __('Choose an option') }}",
                    maxOptions: null,
                });

                el.addEventListener('change', function() {
                    if (el.value) {
                        el.setCustomValidity('');
                    } else {
                        el.setCustomValidity('This field is required.');
                    }
                });

                el.dispatchEvent(new Event('change'));
            }

            elementSelectors.forEach(function(id) {
                var el = document.getElementById(id);
                if (el) {
                    initializeTomSelectWithRequired(el);
                }
            });
        });
    </script>
@endsection
