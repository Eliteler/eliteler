@extends('user.layouts.index', ['header' => true, 'nav' => true, 'demo' => true, 'settings' => $settings])

{{-- Custom CSS --}}
@section('css')
    {{-- Store CSS File --}}
    <link href="{{ asset('css/store-advanced-settings.css') }}" rel="stylesheet">

    {{-- CodeMirror Core & Dracula Theme CSS --}}
    <link rel="stylesheet" href="{{ asset('plugins/codemirror/css/codemirror.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/codemirror/css/dracula.min.css') }}">
@endsection

@section('content')
    <div class="page-wrapper">
        <!-- Page Header -->
        <div class="page-header d-print-none">
            <div class="container-fluid">
                <div class="row g-2 align-items-center">
                    <!-- Hierarchical Breadcrumb Navigation -->
                    <div class="col-12">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb breadcrumb-arrows">
                                <li class="breadcrumb-item">
                                    <a href="{{ route('user.dashboard') }}"
                                        class="text-muted d-inline-flex align-items-center gap-1">
                                        {{ __('Dashboard') }}
                                    </a>
                                </li>
                                <li class="breadcrumb-item">
                                    <a href="{{ route('user.stores') }}" class="text-muted">{{ __('Stores') }}</a>
                                </li>
                                <li class="breadcrumb-item active fw-semibold" aria-current="page">
                                    {{ __('Advanced Settings') }}
                                </li>
                            </ol>
                        </nav>
                    </div>

                    <!-- Title and Subtitle -->
                    <div class="col">
                        <h2 class="page-title text-reset fw-bold">
                            {{ __('Advanced Configurations') }}
                        </h2>
                        <div class="text-muted mt-1">
                            {{ __('Customize search visibility, PWA mobile options, localization settings, and custom stylesheet/script modifications.') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Page Body -->
        <div class="page-body">
            <div class="container-fluid">
                {{-- Flash Notifications --}}
                @if (Session::has('failed'))
                    <div class="alert alert-important alert-danger alert-dismissible mb-3 border-0 shadow-sm"
                        role="alert">
                        <div class="d-flex align-items-center gap-2">
                            <div>{{ Session::get('failed') }}</div>
                        </div>
                        <a class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="close"></a>
                    </div>
                @endif

                @if (Session::has('success'))
                    <div class="alert alert-important alert-success alert-dismissible mb-3 border-0 shadow-sm"
                        role="alert">
                        <div class="d-flex align-items-center gap-2">
                            <div>{{ Session::get('success') }}</div>
                        </div>
                        <a class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="close"></a>
                    </div>
                @endif

                <!-- Unified Settings Card -->
                <div class="card border-0 shadow-sm">
                    <div class="row g-0">
                        {{-- Left Navigation Sidebar --}}
                        <div class="col-12 col-md-3 col-lg-2 border-end">
                            <div class="card-body">
                                <h4 class="subheader mb-3 text-muted fw-bold">{{ __('Store Settings') }}</h4>
                                <div class="list-group list-group-transparent mb-0">
                                    @include('user.pages.edit-store.include.nav-link', [
                                        'link' => 'advanced-settings',
                                    ])
                                </div>
                            </div>
                        </div>

                        {{-- Right Main Form Pane --}}
                        <div class="col-12 col-md-9 col-lg-10">
                            <form action="{{ route('user.update.store.advanced.setting') }}" method="POST"
                                id="advancedSettingsForm">
                                @csrf
                                <input type="hidden" name="store_id" value="{{ $business_card->card_id }}">

                                <div class="card-body p-4">
                                    {{-- Section 1: Feature Toggles --}}
                                    <div class="mb-4">
                                        <div class="d-flex align-items-center justify-content-between mb-3">
                                            <div>
                                                <h4 class="card-title fw-bold mb-0">{{ __('Store Discovery & Features') }}
                                                </h4>
                                                <small
                                                    class="text-muted">{{ __('Enable or disable platform visibility and progressive web app capabilities.') }}</small>
                                            </div>
                                        </div>

                                        <div class="row g-3">
                                            {{-- Directory Listing Toggle --}}
                                            @if (plugin_active('Directory'))
                                                @php
                                                    $directory_settings = DB::table('directory_settings')->first();
                                                @endphp

                                                @if ($directory_settings && $directory_settings->directory == 1)
                                                    <div
                                                        class="col-12 {{ $directory_settings->default_enable_directory_customers == 1 ? 'd-none' : '' }}">
                                                        <div class="settings-switch-card">
                                                            <div class="d-flex align-items-center justify-content-between">
                                                                <div class="d-flex align-items-center gap-3">
                                                                    <span class="settings-switch-icon">
                                                                        <svg xmlns="http://www.w3.org/2000/svg"
                                                                            class="icon icon-tabler icon-tabler-world"
                                                                            width="22" height="22"
                                                                            viewBox="0 0 24 24" stroke-width="2"
                                                                            stroke="currentColor" fill="none"
                                                                            stroke-linecap="round" stroke-linejoin="round">
                                                                            <path stroke="none" d="M0 0h24v24H0z"
                                                                                fill="none" />
                                                                            <path
                                                                                d="M3 12a9 9 0 1 0 18 0a9 9 0 0 0 -18 0" />
                                                                            <path d="M3.6 9h16.8" />
                                                                            <path d="M3.6 15h16.8" />
                                                                            <path d="M11.5 3a17 17 0 0 0 0 18" />
                                                                            <path d="M12.5 3a17 17 0 0 1 0 18" />
                                                                        </svg>
                                                                    </span>
                                                                    <div>
                                                                        <div class="fw-bold text-reset">
                                                                            {{ __('Public Directory Listing') }}</div>
                                                                        <p class="text-muted small mb-0">
                                                                            {{ __('Display and feature your store publicly on the platform global business directory.') }}
                                                                        </p>
                                                                    </div>
                                                                </div>
                                                                <div class="form-check form-switch mb-0">
                                                                    <input type="checkbox" class="form-check-input"
                                                                        name="directory_listing" id="directory"
                                                                        {{ $directory_settings->default_enable_directory_customers == 1 || $business_card->directory_listing == 1 ? 'checked' : '' }}>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                            @endif

                                            @if ($plan_details->advanced_settings == 1)
                                                {{-- Enable PWA Switch --}}
                                                <div class="col-12">
                                                    <div class="settings-switch-card">
                                                        <div class="d-flex align-items-center justify-content-between">
                                                            <div class="d-flex align-items-center gap-3">
                                                                <span class="settings-switch-icon">
                                                                    <svg xmlns="http://www.w3.org/2000/svg"
                                                                        class="icon icon-tabler icon-tabler-device-mobile"
                                                                        width="22" height="22" viewBox="0 0 24 24"
                                                                        stroke-width="2" stroke="currentColor"
                                                                        fill="none" stroke-linecap="round"
                                                                        stroke-linejoin="round">
                                                                        <path stroke="none" d="M0 0h24v24H0z"
                                                                            fill="none" />
                                                                        <path
                                                                            d="M6 5a2 2 0 0 1 2 -2h8a2 2 0 0 1 2 2v14a2 2 0 0 1 -2 2h-8a2 2 0 0 1 -2 -2v-14z" />
                                                                        <path d="M11 4h2" />
                                                                        <path d="M12 17v.01" />
                                                                    </svg>
                                                                </span>
                                                                <div>
                                                                    <div class="fw-bold text-reset">
                                                                        {{ __('Progressive Web App (PWA)') }}</div>
                                                                    <p class="text-muted small mb-0">
                                                                        {{ __('Allow customers to install your store directly onto their smartphone home screen.') }}
                                                                    </p>
                                                                </div>
                                                            </div>
                                                            <div class="form-check form-switch mb-0">
                                                                <input class="form-check-input" type="checkbox"
                                                                    name="is_enable_pwa" value="1"
                                                                    {{ $business_card->is_enable_pwa == 1 ? 'checked' : '' }}>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                {{-- Enable Language Switcher Switch --}}
                                                <div class="col-12">
                                                    <div class="settings-switch-card">
                                                        <div class="d-flex align-items-center justify-content-between">
                                                            <div class="d-flex align-items-center gap-3">
                                                                <span class="settings-switch-icon">
                                                                    <svg xmlns="http://www.w3.org/2000/svg"
                                                                        class="icon icon-tabler icon-tabler-language"
                                                                        width="22" height="22" viewBox="0 0 24 24"
                                                                        stroke-width="2" stroke="currentColor"
                                                                        fill="none" stroke-linecap="round"
                                                                        stroke-linejoin="round">
                                                                        <path stroke="none" d="M0 0h24v24H0z"
                                                                            fill="none" />
                                                                        <path d="M4 5h7" />
                                                                        <path d="M9 3v2c0 4.418 -2.239 8 -5 8" />
                                                                        <path d="M5 9c0 2.144 2.952 3.908 6.7 4" />
                                                                        <path d="M12 20l4 -9l4 9" />
                                                                        <path d="M19.1 18h-6.2" />
                                                                    </svg>
                                                                </span>
                                                                <div>
                                                                    <div class="fw-bold text-reset">
                                                                        {{ __('Multi-Language Switcher') }}</div>
                                                                    <p class="text-muted small mb-0">
                                                                        {{ __('Display language selector dropdown on your storefront for international customers.') }}
                                                                    </p>
                                                                </div>
                                                            </div>
                                                            <div class="form-check form-switch mb-0">
                                                                <input class="form-check-input" type="checkbox"
                                                                    name="is_enable_language_switcher" value="1"
                                                                    {{ $business_card->is_enable_language_switcher == 1 ? 'checked' : '' }}>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- Section 2: Code Injections (CodeMirror) --}}
                                    @if ($plan_details->advanced_settings == 1)
                                        <div class="mb-2">
                                            <div class="mb-3">
                                                <h4 class="card-title fw-bold mb-0">{{ __('Custom Code Injections') }}
                                                </h4>
                                                <small
                                                    class="text-muted">{{ __('Add custom styling rules or third-party tracking scripts directly to your storefront header.') }}</small>
                                            </div>

                                            <div class="row g-4">
                                                {{-- Custom CSS Editor --}}
                                                <div class="col-lg-6">
                                                    <div class="store-section-card h-100 mb-0">
                                                        <div class="store-section-header">
                                                            <span
                                                                class="fw-bold small">{{ __('Custom CSS Stylesheet') }}</span>
                                                            <span
                                                                class="badge bg-primary-lt font-monospace text-uppercase">{{ __('CSS') }}</span>
                                                        </div>
                                                        <div class="p-2 bg-surface">
                                                            <textarea class="form-control" id="customCss" name="custom_css" rows="6" maxlength="25000"
                                                                placeholder="{{ __('/* Custom CSS Styles */') }}">{{ $business_card->custom_css }}</textarea>
                                                        </div>
                                                    </div>
                                                </div>

                                                {{-- Custom JS Editor --}}
                                                <div class="col-lg-6">
                                                    <div class="store-section-card h-100 mb-0">
                                                        <div class="store-section-header">
                                                            <span
                                                                class="fw-bold small">{{ __('Custom JavaScript Code') }}</span>
                                                            <span
                                                                class="badge bg-warning-lt font-monospace text-uppercase">{{ __('JavaScript') }}</span>
                                                        </div>
                                                        <div class="p-2 bg-surface">
                                                            <textarea class="form-control" id="customJs" name="custom_js" rows="6" maxlength="25000"
                                                                placeholder="{{ __('// Custom JavaScript Code') }}">{{ $business_card->custom_js }}</textarea>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                <!-- Standardized Symmetrical Action Footer -->
                                <div class="card-footer bg-transparent border-top p-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <a href="{{ route('user.stores') }}"
                                            class="btn btn-danger d-inline-flex align-items-center gap-1">
                                            {{ __('Cancel') }}
                                        </a>

                                        <button type="submit"
                                            class="btn btn-primary d-inline-flex align-items-center px-4 shadow-sm gap-2">
                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                class="icon icon-tabler icon-tabler-device-floppy" width="18"
                                                height="18" viewBox="0 0 24 24" stroke-width="2"
                                                stroke="currentColor" fill="none" stroke-linecap="round"
                                                stroke-linejoin="round">
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                <path
                                                    d="M6 4h10l4 4v10a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2" />
                                                <path d="M12 14m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" />
                                                <path d="M14 4l0 4l-4 0l0 -4" />
                                            </svg>
                                            {{ __('Save') }}
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Footer --}}
        @include('user.includes.footer')
    </div>
@endsection

{{-- Script pushes --}}
@push('custom-js')
    <script src="{{ asset('plugins/codemirror/js/codemirror.min.js') }}"></script>
    <script src="{{ asset('plugins/codemirror/js/css.min.js') }}"></script>
    <script src="{{ asset('plugins/codemirror/js/javascript.min.js') }}"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            "use strict";

            // Initialize CodeMirror for Custom CSS
            const cssElement = document.getElementById("customCss");
            if (cssElement) {
                const cssEditor = CodeMirror.fromTextArea(cssElement, {
                    mode: "css",
                    theme: "dracula",
                    lineNumbers: true,
                    lineWrapping: true,
                    indentUnit: 2,
                    tabSize: 2,
                });

                cssEditor.on("change", function() {
                    cssEditor.save();
                });
            }

            // Initialize CodeMirror for Custom JS
            const jsElement = document.getElementById("customJs");
            if (jsElement) {
                const jsEditor = CodeMirror.fromTextArea(jsElement, {
                    mode: "javascript",
                    theme: "dracula",
                    lineNumbers: true,
                    lineWrapping: true,
                    indentUnit: 2,
                    tabSize: 2
                });

                jsEditor.on("change", function() {
                    jsEditor.save();
                });
            }
        });
    </script>
@endpush
