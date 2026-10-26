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
                            ['title' => 'Google reCAPTCHA'],
                        ]" />

                        <h2 class="page-title">
                            {{ __('Google reCAPTCHA') }}
                        </h2>

                        <!-- Helper Subtitle -->
                        <div class="text-muted mt-1">
                            {{ __('Configure your Google reCAPTCHA (v2 Checkbox) credentials to protect forms against spam and automated submissions.') }}
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
                        <form action="{{ route('admin.google_recaptcha_settings.update') }}" method="post"
                            class="card border-0 shadow-sm">
                            @csrf
                            <div class="card-body">
                                <div class="row g-3">
                                    {{-- Enable Google reCAPTCHA --}}
                                    <div class="col-12">
                                        <div class="mb-3">
                                            <label class="form-label required" for="recaptcha_enable">
                                                {{ __('Enable Google reCAPTCHA') }}
                                            </label>
                                            <select class="form-select" id="recaptcha_enable" name="recaptcha_enable"
                                                required>
                                                <option value="" disabled>{{ __('Choose an option') }}</option>
                                                <option value="on"
                                                    {{ $recaptcha_configuration['RECAPTCHA_ENABLE'] == 'on' ? 'selected' : '' }}>
                                                    {{ __('On') }}</option>
                                                <option value="off"
                                                    {{ $recaptcha_configuration['RECAPTCHA_ENABLE'] == 'off' ? 'selected' : '' }}>
                                                    {{ __('Off') }}</option>
                                            </select>
                                        </div>
                                        <span class="small text-muted d-block mt-1">
                                            {{ __('If you do not have Google reCAPTCHA v2 credentials, you can') }}
                                            <a href="https://www.google.com/recaptcha/about/" target="_blank"
                                                class="text-decoration-none">{{ __('register a new site.') }}</a>
                                        </span>
                                    </div>

                                    {{-- reCAPTCHA Site Key --}}
                                    <div class="col-12 col-md-6 col-xl-6">
                                        <div class="mb-3">
                                            <label class="form-label required">{{ __('reCAPTCHA Site Key') }}</label>
                                            <input type="text" class="form-control" name="recaptcha_site_key"
                                                value="{{ $recaptcha_configuration['RECAPTCHA_SITE_KEY'] }}"
                                                placeholder="{{ __('Enter your reCAPTCHA site key') }}" required>
                                        </div>
                                    </div>

                                    {{-- reCAPTCHA Secret Key --}}
                                    <div class="col-12 col-md-6 col-xl-6">
                                        <div class="mb-3">
                                            <label class="form-label required">{{ __('reCAPTCHA Secret Key') }}</label>
                                            <input type="text" class="form-control" name="recaptcha_secret_key"
                                                value="{{ $recaptcha_configuration['RECAPTCHA_SECRET_KEY'] }}"
                                                placeholder="{{ __('Enter your reCAPTCHA secret key') }}" required>
                                        </div>
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
            var elementSelectors = ['recaptcha_enable'];

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
