@extends('user.layouts.index', ['header' => true, 'nav' => true, 'demo' => true, 'settings' => $settings])

{{-- Custom CSS for Enhanced Typography & Modern Tabler Design --}}
@section('css')
    {{-- Advanced Settings CSS --}}
    <link rel="stylesheet" href="{{ asset('css/advanced-settings.css') }}">
    
    {{-- CodeMirror CSS Assets --}}
    <link rel="stylesheet" href="{{ asset('plugins/codemirror/css/codemirror.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/codemirror/css/dracula.min.css') }}">
@endsection

@section('content')
    <div class="page-wrapper">
        <!-- Page Header -->
        <div class="page-header d-print-none">
            <div class="container-fluid">
                <div class="row g-2 align-items-center">
                    <nav aria-label="breadcrumb" class="mb-1">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="{{ route('user.dashboard') }}" class="text-secondary text-decoration-none">
                                    {{ __('Dashboard') }}
                                </a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="{{ route('user.cards') }}" class="text-secondary text-decoration-none">
                                    {{ __('Digital Business Cards') }}
                                </a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">
                                {{ __('Advanced Settings') }}
                            </li>
                        </ol>
                    </nav>
                    <div class="col">
                        <h2 class="page-title text-dark fw-bold">
                            {{ __('Advanced Settings') }}
                        </h2>
                        <div class="text-secondary mt-1">
                            {{ __('Configure search engine metadata, password protection, progressive web app settings, and custom CSS and JavaScript.') }}
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
                    <div class="alert alert-important alert-danger alert-dismissible mb-3 border-0 shadow-sm"
                        role="alert">
                        <div class="d-flex">
                            <div>{{ Session::get('failed') }}</div>
                        </div>
                        <a class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="close"></a>
                    </div>
                @endif

                {{-- Success Alert --}}
                @if (Session::has('success'))
                    <div class="alert alert-important alert-success alert-dismissible mb-3 border-0 shadow-sm"
                        role="alert">
                        <div class="d-flex">
                            <div>{{ Session::get('success') }}</div>
                        </div>
                        <a class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="close"></a>
                    </div>
                @endif

                <div class="card border-0 shadow-sm">
                    <div class="row g-0">
                        <!-- Sidebar Navigation -->
                        <div class="col-12 col-md-3 col-lg-2 border-end">
                            <div class="card-body">
                                <h4 class="subheader mb-3 text-uppercase fw-bold text-muted">{{ __('Card Settings') }}</h4>
                                <div class="list-group list-group-transparent">
                                    @include('user.pages.edit-cards.includes.nav-link', [
                                        'link' => 'advanced',
                                    ])
                                </div>
                            </div>
                        </div>

                        <!-- Main Form Area -->
                        <div class="col-12 col-md-9 col-lg-10 d-flex flex-column">
                            <form action="{{ route('user.update.advanced.setting', Request::segment(3)) }}" method="post"
                                id="myForm" enctype="multipart/form-data">
                                @csrf
                                <div class="card-body p-3 p-lg-4">
                                    <div class="mb-4">
                                        <h3 class="card-title m-0 text-dark fw-bold">
                                            {{ __('Advanced Card Configuration') }}</h3>
                                        <p class="text-muted small mb-0 mt-1">
                                            {{ __('Customize privacy parameters, search discoverability, developer code injections, and SEO assets.') }}
                                        </p>
                                    </div>

                                    {{-- Section 1: Feature Toggles Grid --}}
                                    <div class="row g-3 mb-4">
                                        {{-- Public Directory Toggle --}}
                                        @if (plugin_active('Directory'))
                                            @php
                                                $directory_settings = DB::table('directory_settings')->first();
                                            @endphp

                                            @if ($directory_settings && $directory_settings->directory == 1)
                                                <div
                                                    class="col-12 col-sm-6 col-xl-3 {{ $directory_settings->default_enable_directory_customers == 1 ? 'd-none' : '' }}">
                                                    <div
                                                        class="settings-switch-card d-flex flex-column justify-content-between">
                                                        <div>
                                                            <div
                                                                class="d-flex align-items-center justify-content-between mb-2">
                                                                <div class="text-primary">
                                                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon"
                                                                        width="24" height="24" viewBox="0 0 24 24"
                                                                        stroke-width="2" stroke="currentColor"
                                                                        fill="none" stroke-linecap="round"
                                                                        stroke-linejoin="round">
                                                                        <path stroke="none" d="M0 0h24v24H0z"
                                                                            fill="none" />
                                                                        <path d="M14 3v4a1 1 0 0 0 1 1h4" />
                                                                        <path
                                                                            d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z" />
                                                                        <path d="M9 14l2 2l4 -4" />
                                                                    </svg>
                                                                </div>
                                                                <div class="form-check form-switch m-0">
                                                                    <input type="checkbox" class="form-check-input"
                                                                        name="directory_listing" id="directory"
                                                                        {{ $directory_settings->default_enable_directory_customers == 1 || $business_card->directory_listing == 1 ? 'checked' : '' }}>
                                                                </div>
                                                            </div>
                                                            <h5 class="fw-bold text-dark mb-1">
                                                                {{ __('Directory Listing') }}</h5>
                                                            <p class="text-muted small mb-0">
                                                                {{ __('Show this card publicly in the directory.') }}</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        @endif

                                        {{-- Password Protection Toggle --}}
                                        @if ($plan_details->password_protected == 1)
                                            <div class="col-12 col-sm-6 col-xl-3">
                                                <div
                                                    class="settings-switch-card d-flex flex-column justify-content-between">
                                                    <div>
                                                        <div class="d-flex align-items-center justify-content-between mb-2">
                                                            <div class="text-primary">
                                                                <svg xmlns="http://www.w3.org/2000/svg" class="icon"
                                                                    width="24" height="24" viewBox="0 0 24 24"
                                                                    stroke-width="2" stroke="currentColor" fill="none"
                                                                    stroke-linecap="round" stroke-linejoin="round">
                                                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                                    <path
                                                                        d="M5 13a2 2 0 0 1 2 -2h10a2 2 0 0 1 2 2v6a2 2 0 0 1 -2 2h-10a2 2 0 0 1 -2 -2v-6z" />
                                                                    <path d="M11 16a1 1 0 1 0 2 0a1 1 0 0 0 -2 0" />
                                                                    <path d="M8 11v-4a4 4 0 1 1 8 0v4" />
                                                                </svg>
                                                            </div>
                                                            <div class="form-check form-switch m-0">
                                                                <input class="form-check-input" type="checkbox"
                                                                    onchange="displayPasswordProtected()"
                                                                    {{ $business_card->password == null ? 'checked' : '' }}
                                                                    name="password_protected" id="password-protected">
                                                            </div>
                                                        </div>
                                                        <h5 class="fw-bold text-dark mb-1">
                                                            {{ __('Disable Password Protection') }}</h5>
                                                        <p class="text-muted small mb-0">
                                                            {{ __('Turn off password requirement for card visitors.') }}
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif

                                        {{-- PWA Toggle --}}
                                        @if ($plan_details->advanced_settings == 1)
                                            <div class="col-12 col-sm-6 col-xl-3">
                                                <div
                                                    class="settings-switch-card d-flex flex-column justify-content-between">
                                                    <div>
                                                        <div
                                                            class="d-flex align-items-center justify-content-between mb-2">
                                                            <div class="text-primary">
                                                                <svg xmlns="http://www.w3.org/2000/svg" class="icon"
                                                                    width="24" height="24" viewBox="0 0 24 24"
                                                                    stroke-width="2" stroke="currentColor" fill="none"
                                                                    stroke-linecap="round" stroke-linejoin="round">
                                                                    <path stroke="none" d="M0 0h24v24H0z"
                                                                        fill="none" />
                                                                    <path
                                                                        d="M6 5a2 2 0 0 1 2 -2h8a2 2 0 0 1 2 2v14a2 2 0 0 1 -2 2h-8a2 2 0 0 1 -2 -2v-14z" />
                                                                    <path d="M11 4h2" />
                                                                    <path d="M12 17v.01" />
                                                                </svg>
                                                            </div>
                                                            <div class="form-check form-switch m-0">
                                                                <input class="form-check-input" type="checkbox"
                                                                    {{ $business_card->is_enable_pwa == 1 ? 'checked' : '' }}
                                                                    name="is_enable_pwa">
                                                            </div>
                                                        </div>
                                                        <h5 class="fw-bold text-dark mb-1">{{ __('PWA') }}</h5>
                                                        <p class="text-muted small mb-0">
                                                            {{ __('Allow card installation as an App on mobile devices.') }}
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- Language Switcher Toggle --}}
                                            <div class="col-12 col-sm-6 col-xl-3">
                                                <div
                                                    class="settings-switch-card d-flex flex-column justify-content-between">
                                                    <div>
                                                        <div
                                                            class="d-flex align-items-center justify-content-between mb-2">
                                                            <div class="text-primary">
                                                                <svg xmlns="http://www.w3.org/2000/svg" class="icon"
                                                                    width="24" height="24" viewBox="0 0 24 24"
                                                                    stroke-width="2" stroke="currentColor" fill="none"
                                                                    stroke-linecap="round" stroke-linejoin="round">
                                                                    <path stroke="none" d="M0 0h24v24H0z"
                                                                        fill="none" />
                                                                    <path d="M4 5h7" />
                                                                    <path d="M9 3v2c0 4.418 -2.239 8 -5 8" />
                                                                    <path d="M5 9c0 2.144 2.952 3.908 6.7 4" />
                                                                    <path d="M12 20l4 -9l4 9" />
                                                                    <path d="M19.1 18h-6.2" />
                                                                </svg>
                                                            </div>
                                                            <div class="form-check form-switch m-0">
                                                                <input class="form-check-input" type="checkbox"
                                                                    {{ $business_card->is_enable_language_switcher == 1 ? 'checked' : '' }}
                                                                    name="is_enable_language_switcher">
                                                            </div>
                                                        </div>
                                                        <h5 class="fw-bold text-dark mb-1">
                                                            {{ __('Language Switcher') }}</h5>
                                                        <p class="text-muted small mb-0">
                                                            {{ __('Allow visitors to toggle between multi-language choices.') }}
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>

                                    {{-- Section 2: Password Configuration (Conditional) --}}
                                    <div class="{{ $business_card->password == null ? 'd-none' : '' }}"
                                        id="passwordProtectedForm">
                                        <div class="advanced-section-card">
                                            <div class="mb-3">
                                                <h4 class="fw-bold text-dark mb-1">
                                                    {{ __('Password Protection Configuration') }}</h4>
                                                <p class="text-muted small mb-0">
                                                    {{ __('Specify a security passcode required to view this digital business card.') }}
                                                </p>
                                            </div>

                                            <div class="row">
                                                <div class="col-12 col-md-6">
                                                    <label class="form-label required"
                                                        for="password">{{ __('Passcode') }}</label>
                                                    <div class="input-group">
                                                        <input type="password" class="form-control" name="password"
                                                            id="password" value="{{ $business_card->password }}"
                                                            minlength="3" maxlength="20"
                                                            placeholder="{{ __('Enter password') }}">
                                                        <button class="btn btn-outline-secondary input-group-text"
                                                            type="button" onclick="showPassword()">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="icon"
                                                                width="20" height="20" viewBox="0 0 24 24"
                                                                stroke-width="2" stroke="currentColor" fill="none"
                                                                stroke-linecap="round" stroke-linejoin="round">
                                                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                                <path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" />
                                                                <path
                                                                    d="M21 12c-2.4 4 -5.4 6 -9 6c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c3.6 0 6.6 2 9 6" />
                                                            </svg>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Section 3: Custom CSS / JS Code Injections --}}
                                    @if ($plan_details->advanced_settings == 1)
                                        <div class="advanced-section-card">
                                            <div class="mb-3">
                                                <h4 class="fw-bold text-dark mb-1">
                                                    {{ __('Custom CSS & JavaScript Injections') }}</h4>
                                                <p class="text-muted small mb-0">
                                                    {{ __('Add custom stylesheet rules or tracking scripts to enhance functionality.') }}
                                                </p>
                                            </div>

                                            <div class="row g-3">
                                                {{-- Custom CSS Editor --}}
                                                <div class="col-12 col-lg-6">
                                                    <label
                                                        class="form-label d-flex align-items-center justify-content-between"
                                                        for="custom_css">
                                                        <span>{{ __('Custom CSS') }}</span>
                                                        <span
                                                            class="badge bg-blue-lt text-uppercase fw-bold">{{ __('CSS') }}</span>
                                                    </label>
                                                    <textarea id="custom_css" class="form-control" name="custom_css" rows="5" maxlength="25000"
                                                        placeholder="{{ __('Enter custom CSS') }}">{{ $business_card->custom_css }}</textarea>
                                                </div>

                                                {{-- Custom JS Editor --}}
                                                <div class="col-12 col-lg-6">
                                                    <label
                                                        class="form-label d-flex align-items-center justify-content-between"
                                                        for="custom_js">
                                                        <span>{{ __('Custom JS') }}</span>
                                                        <span
                                                            class="badge bg-yellow-lt text-uppercase fw-bold">{{ __('JavaScript') }}</span>
                                                    </label>
                                                    <textarea id="custom_js" class="form-control" name="custom_js" rows="5" maxlength="25000"
                                                        placeholder="{{ __('Enter custom JS') }}">{{ $business_card->custom_js }}</textarea>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Section 4: SEO Metadata Configuration --}}
                                        <div class="advanced-section-card mb-0">
                                            <div class="mb-3">
                                                <h4 class="fw-bold text-dark mb-1">
                                                    {{ __('Search Engine Optimization (SEO) Settings') }}</h4>
                                                <p class="text-muted small mb-0">
                                                    {{ __('Optimize search visibility, social media link shares, and search index metadata.') }}
                                                </p>
                                            </div>

                                            @php
                                                $seoConfig = isset($business_card->seo_configurations)
                                                    ? json_decode($business_card->seo_configurations)
                                                    : null;
                                            @endphp

                                            <div class="row g-3">
                                                {{-- Favicon image --}}
                                                <div class="col-12 col-md-6">
                                                    <label class="form-label"
                                                        for="favicon">{{ __('Favicon Icon') }}</label>
                                                    <input type="file" class="form-control" name="favicon"
                                                        placeholder="{{ __('Choose favicon image') }}" id="favicon"
                                                        accept="image/*">
                                                    <small
                                                        class="text-muted mt-1 d-block">{{ __('Recommended size: 32x32 px or 64x64 px (.png / .ico).') }}</small>
                                                </div>

                                                {{-- Meta title --}}
                                                <div class="col-12 col-md-6">
                                                    <label class="form-label"
                                                        for="meta_title">{{ __('Meta Title') }}</label>
                                                    <input type="text" class="form-control" name="meta_title"
                                                        id="meta_title" value="{{ $seoConfig->meta_title ?? '' }}"
                                                        placeholder="{{ __('Enter meta title') }}">
                                                </div>

                                                {{-- Meta description --}}
                                                <div class="col-12 col-md-6">
                                                    <label class="form-label"
                                                        for="meta_description">{{ __('Meta Description') }}</label>
                                                    <textarea class="form-control" rows="3" name="meta_description" id="meta_description"
                                                        placeholder="{{ __('Enter meta description') }}">{{ $seoConfig->meta_description ?? '' }}</textarea>
                                                </div>

                                                {{-- Meta keywords --}}
                                                <div class="col-12 col-md-6">
                                                    <label class="form-label"
                                                        for="meta_keywords">{{ __('Meta Keywords') }}</label>
                                                    <textarea class="form-control" rows="3" name="meta_keywords" id="meta_keywords"
                                                        placeholder="{{ __('Enter meta keywords separated by commas') }}">{{ $seoConfig->meta_keywords ?? '' }}</textarea>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                <!-- Form Footer -->
                                <div class="card-footer border-top d-flex align-items-center justify-content-between py-3">
                                    <a href="{{ route('user.cards') }}"
                                        class="btn btn-danger text-decoration-none fw-semibold">
                                        {{ __('Cancel') }}
                                    </a>
                                    <button type="submit"
                                        class="btn btn-primary d-inline-flex align-items-center shadow-sm px-4 fw-semibold">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon me-2" width="20"
                                            height="20" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                            fill="none" stroke-linecap="round" stroke-linejoin="round">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                            <path
                                                d="M6 4h10l4 4v10a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2" />
                                            <path d="M12 14m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" />
                                            <path d="M14 4l0 4l-6 0l0 -4" />
                                        </svg>
                                        {{ __('Save') }}
                                    </button>
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

@push('custom-js')
    {{-- CodeMirror Core & Mode Scripts --}}
    <script src="{{ asset('plugins/codemirror/js/codemirror.min.js') }}"></script>
    <script src="{{ asset('plugins/codemirror/js/css.min.js') }}"></script>
    <script src="{{ asset('plugins/codemirror/js/javascript.min.js') }}"></script>

    <script>
        "use strict";

        document.addEventListener("DOMContentLoaded", function() {
            // Initialize CodeMirror for Custom CSS
            var cssEl = document.getElementById("custom_css");
            if (cssEl) {
                var cssEditor = CodeMirror.fromTextArea(cssEl, {
                    mode: "css",
                    theme: "dracula",
                    lineNumbers: true,
                    lineWrapping: true,
                    indentUnit: 2,
                    tabSize: 2
                });

                cssEditor.on("change", function() {
                    cssEditor.save();
                });
            }

            // Initialize CodeMirror for Custom JS
            var jsEl = document.getElementById("custom_js");
            if (jsEl) {
                var jsEditor = CodeMirror.fromTextArea(jsEl, {
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

        function displayPasswordProtected() {
            var isChecked = document.getElementById('password-protected').checked;
            var formSection = document.getElementById("passwordProtectedForm");
            var passwordInput = document.getElementById('password');

            if (!isChecked) {
                formSection.classList.remove('d-none');
                passwordInput.setAttribute('required', 'required');
            } else {
                formSection.classList.add('d-none');
                passwordInput.removeAttribute('required');
            }
        }

        function showPassword() {
            var password = document.getElementById("password");
            if (password.type === "password") {
                password.type = "text";
            } else {
                password.type = "password";
            }
        }
    </script>
@endpush
