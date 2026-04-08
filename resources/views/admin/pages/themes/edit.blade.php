@extends('admin.layouts.index', ['header' => true, 'nav' => true, 'demo' => true])

{{-- Custom CSS --}}
@section('css')
    <style>
        .cm-s-material-darker.CodeMirror {
            border-radius: 10px;
        }
    </style>
@endsection

@section('content')
    <div class="page-wrapper">
        <!-- Page title -->
        <div class="page-header d-print-none">
            <div class="container-fluid">
                <div class="row g-2 align-items-center">
                    <div class="col">
                        <div class="page-pretitle">
                            {{ __('Update') }}
                        </div>
                        <h2 class="page-title">
                            <span class="me-1">{{ __($theme_details->theme_name) }}</span>
                            {{ __('Details') }}
                        </h2>
                    </div>
                </div>
            </div>
        </div>

        <div class="page-body">
            <div class="container-fluid">
                {{-- Failed --}}
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

                {{-- Success --}}
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
                    <div class="col-sm-12 col-lg-12">
                        <form action="{{ route('admin.update.theme') }}" method="post" enctype="multipart/form-data"
                            class="card">
                            @csrf
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-xl-12">
                                        <div class="row">
                                            <input type="hidden" class="form-control" name="theme_id"
                                                value="{{ $theme_details->theme_id }}">

                                            <div class="col-md-6 col-xl-6">
                                                <div class="mb-3">
                                                    <div class="form-label">{{ __('Theme Thumbnail') }}</div>
                                                    <input type="file" class="form-control" name="theme_thumbnail"
                                                        placeholder="{{ __('Theme Thumbnail') }}"
                                                        accept=".jpeg,.jpg,.png,.gif,.svg" />
                                                </div>
                                            </div>
                                            <div class="col-md-6 col-xl-6">
                                                <div class="mb-3">
                                                    <label class="form-label required">{{ __('Theme Name') }}</label>
                                                    <input type="text" class="form-control" name="theme_name"
                                                        placeholder="{{ __('Theme Name') }}"
                                                        value="{{ $theme_details->theme_name }}" required>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card-footer text-end">
                                <button type="submit" class="btn btn-primary">
                                    {{ __('Save') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- Custom CSS --}}
                <div class="row row-deck row-cards mt-3">
                    <div class="col-sm-12 col-lg-12">
                        <form action="{{ route('admin.update.theme.css') }}" method="post" enctype="multipart/form-data"
                            class="card">
                            @csrf
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-xl-12">
                                        <div class="row">
                                            <input type="hidden" class="form-control" name="theme_id"
                                                value="{{ $theme_details->theme_id }}">

                                            <div class="col-md-12 col-xl-12">
                                                <div class="mb-3">
                                                    <label class="form-label required">{{ __('CSS Code') }}</label>
                                                    <textarea id="css-editor" name="css"
                                                        placeholder="{{ __('Example: body { background-color: #fff; color: #333; }') }}">{{ trim($theme_details->theme_css) }}</textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card-footer text-end">
                                <button type="submit" class="btn btn-primary">
                                    {{ __('Save') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- Custom JS --}}
                <div class="row row-deck row-cards mt-3">
                    <div class="col-sm-12 col-lg-12">
                        <form action="{{ route('admin.update.theme.js') }}" method="post" enctype="multipart/form-data"
                            class="card">
                            @csrf
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-xl-12">
                                        <div class="row">
                                            <input type="hidden" class="form-control" name="theme_id"
                                                value="{{ $theme_details->theme_id }}">

                                            <div class="col-md-12 col-xl-12">
                                                <div class="mb-3">
                                                    <label class="form-label required">{{ __('JS Code') }}</label>
                                                    <textarea id="js-editor" name="js"
                                                        placeholder="{{ __("Example: document.addEventListener('DOMContentLoaded', function() { console.log('Hello'); });") }}">{{ trim($theme_details->theme_js) }}</textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card-footer text-end">
                                <button type="submit" class="btn btn-primary">
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

    {{-- Custom JS --}}
@section('scripts')
    <!-- CodeMirror Core CSS/JS -->
    <link rel="stylesheet" href="{{ asset('plugins/codemirror/css/codemirror.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/codemirror/css/material-darker.min.css') }}">
    <script src="{{ asset('plugins/codemirror/js/codemirror.min.js') }}"></script>
    <script src="{{ asset('plugins/codemirror/js/css.min.js') }}"></script>
    <script src="{{ asset('plugins/codemirror/js/javascript.min.js') }}"></script>

    <!-- Addon: Placeholder -->
    <script src="{{ asset('plugins/codemirror/js/placeholder.min.js') }}"></script>

    <!-- Addons: Autocomplete -->
    <link rel="stylesheet" href="{{ asset('plugins/codemirror/css/show-hint.min.css') }}">
    <script src="{{ asset('plugins/codemirror/js/show-hint.min.js') }}"></script>
    <script src="{{ asset('plugins/codemirror/js/css-hint.min.js') }}"></script>
    <script src="{{ asset('plugins/codemirror/js/javascript-hint.min.js') }}"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            "use strict";

            var editor = CodeMirror.fromTextArea(document.getElementById("css-editor"), {
                mode: "css",
                theme: "material-darker",
                lineNumbers: true,
                autoCloseBrackets: true,
                matchBrackets: true,
                indentUnit: 4,
                tabSize: 4,
                lineWrapping: true,
                extraKeys: {
                    "Ctrl-Space": "autocomplete" // Trigger autocomplete
                }
            });

            // Set width and height
            editor.setSize("100%", "460px"); // width: 100%, height: 400px

            // Trigger autocomplete automatically when typing
            editor.on("inputRead", function(cm, change) {
                if (change.text[0].match(/[\w-]/)) {
                    cm.showHint({
                        completeSingle: false
                    });
                }
            });
        });
    </script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            "use strict";

            var editor = CodeMirror.fromTextArea(document.getElementById("js-editor"), {
                mode: "javascript",
                theme: "material-darker",
                lineNumbers: true,
                autoCloseBrackets: true,
                matchBrackets: true,
                indentUnit: 4,
                tabSize: 4,
                lineWrapping: true,
                extraKeys: {
                    "Ctrl-Space": "autocomplete" // Trigger autocomplete
                }
            });

            // Set width and height
            editor.setSize("100%", "460px"); // width: 100%, height: 400px

            // Trigger autocomplete automatically when typing
            editor.on("inputRead", function(cm, change) {
                if (change.text[0].match(/[\w-]/)) {
                    cm.showHint({
                        completeSingle: false
                    });
                }
            });
        });
    </script>
@endsection
@endsection
