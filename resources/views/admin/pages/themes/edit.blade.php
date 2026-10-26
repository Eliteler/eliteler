@extends('admin.layouts.index', ['header' => true, 'nav' => true, 'demo' => true])

{{-- Custom CSS & CodeMirror Assets --}}
@section('css')
    <link rel="stylesheet" href="{{ asset('plugins/codemirror/css/codemirror.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/codemirror/css/dracula.min.css') }}">

    <style>
        /* CodeMirror Custom Height & Container Styling */
        .CodeMirror {
            height: 220px;
            border-radius: 8px;
            font-family: 'Fira Code', 'Courier New', monospace;
            font-size: 13px;
            padding: 4px 0;
            border: 1px solid var(--tblr-border-color, #e6e8ec);
            box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.05);
        }

        .CodeMirror-gutters {
            background-color: #21252b !important;
            border-right: 1px solid #2c313a !important;
            border-top-left-radius: 7px;
            border-bottom-left-radius: 7px;
        }
    </style>
@endsection

@section('content')
    <div class="page-wrapper">
        <!-- Page title header wrapper -->
        <div class="page-header d-print-none">
            <div class="container-fluid">
                <div class="row align-items-center">
                    <!-- Breadcrumbs -->
                    <div class="mb-1 ms-0">
                        <ol class="breadcrumb" aria-label="breadcrumbs">
                            <li class="breadcrumb-item">
                                <a href="{{ route('admin.dashboard') }}" class="text-secondary text-decoration-none">
                                    {{ __('Dashboard') }}
                                </a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="{{ route('admin.themes', ['status' => 'all']) }}"
                                    class="text-secondary text-decoration-none">
                                    {{ __('Themes') }}
                                </a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">
                                <a href="#">{{ __('Update') }}</a>
                            </li>
                        </ol>
                    </div>
                    <div class="col">
                        <h2 class="page-title">
                            <span class="me-1">{{ __($theme_details->theme_name) }}</span>
                            {{ __('Details') }}
                        </h2>
                        <div class="text-muted mt-1">
                            {{ __('Edit theme properties, configure custom style modifications, and define script extensions.') }}
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
                            <div>{{ Session::get('failed') }}</div>
                        </div>
                        <a class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="close"></a>
                    </div>
                @endif

                {{-- Success alert --}}
                @if (Session::has('success'))
                    <div class="alert alert-important alert-success alert-dismissible mb-2" role="alert">
                        <div class="d-flex">
                            <div>{{ Session::get('success') }}</div>
                        </div>
                        <a class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="close"></a>
                    </div>
                @endif

                {{-- Unified Form Wrapper --}}
                <form action="{{ route('admin.update.theme') }}" method="post" enctype="multipart/form-data"
                    class="card border-0 shadow-sm">
                    @csrf
                    <input type="hidden" name="theme_id" value="{{ $theme_details->theme_id }}">

                    <div class="card-body">
                        {{-- Section 1: Basic Theme Details --}}
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label required">{{ __('Theme Name') }}</label>
                                    <input type="text" class="form-control" name="theme_name"
                                        placeholder="{{ __('Theme Name') }}" value="{{ $theme_details->theme_name }}"
                                        required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <div class="form-label">{{ __('Theme Thumbnail') }}</div>
                                    <input type="file" class="form-control" name="theme_thumbnail"
                                        placeholder="{{ __('Theme Thumbnail') }}" accept=".jpeg,.jpg,.png,.gif,.svg" />
                                </div>
                            </div>
                        </div>

                        {{-- Section 2 Title: Custom Code Modifications --}}
                        <div class="pt-3 mb-3">
                            <h3 class="card-title d-flex align-items-center">
                                {{ __('Custom Styling & JS Extensions') }}
                            </h3>
                        </div>

                        {{-- Code Editors Section (Side-by-Side) --}}
                        <div class="row g-3">
                            {{-- Custom CSS Editor --}}
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label d-flex align-items-center justify-content-between"
                                        for="custom_css">
                                        <span>{{ __('Custom CSS') }}</span>
                                        <span class="badge bg-blue-lt text-uppercase">{{ __('CSS') }}</span>
                                    </label>
                                    <textarea id="custom_css" class="form-control" name="css" rows="4" maxlength="25000"
                                        placeholder="{{ __('Enter custom CSS') }}">{{ trim($theme_details->theme_css) }}</textarea>
                                </div>
                            </div>

                            {{-- Custom JS Editor --}}
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label d-flex align-items-center justify-content-between"
                                        for="custom_js">
                                        <span>{{ __('Custom JS') }}</span>
                                        <span class="badge bg-yellow-lt text-uppercase">{{ __('JavaScript') }}</span>
                                    </label>
                                    <textarea id="custom_js" class="form-control" name="js" rows="4" maxlength="25000"
                                        placeholder="{{ __('Enter custom JS') }}">{{ trim($theme_details->theme_js) }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Single Save Button Footer -->
                    <div class="card-footer text-end">
                        <button type="submit" class="btn btn-primary btn-md d-inline-flex align-items-center">
                            <svg xmlns="http://www.w3.org/2000/svg"
                                class="icon icon-tabler icon-tabler-device-floppy me-2" width="24" height="24"
                                viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                                stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
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

        {{-- Footer --}}
        @include('admin.includes.footer')
    </div>
@endsection

{{-- CodeMirror JS Assets & Script Initialization --}}
@section('scripts')
    <script src="{{ asset('plugins/codemirror/js/codemirror.min.js') }}"></script>
    <script src="{{ asset('plugins/codemirror/js/css.min.js') }}"></script>
    <script src="{{ asset('plugins/codemirror/js/javascript.min.js') }}"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            "use strict";

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

                // Sync CodeMirror content back to textarea for form submit
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

                // Sync CodeMirror content back to textarea for form submit
                jsEditor.on("change", function() {
                    jsEditor.save();
                });
            }
        });
    </script>
@endsection
