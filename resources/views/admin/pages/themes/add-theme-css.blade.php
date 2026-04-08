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
                        {{ __('Overview') }}
                    </div>
                    <h2 class="page-title">
                        {{ __('Update Theme CSS') }}
                    </h2>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-fluid">
            {{-- Failed --}}
            @if(Session::has("failed"))
            <div class="alert alert-important alert-danger alert-dismissible mb-2" role="alert">
                <div class="d-flex">
                    <div>
                        {{Session::get('failed')}}
                    </div>
                </div>
                <a class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="close"></a>
            </div>
            @endif

            {{-- Success --}}
            @if(Session::has("success"))
            <div class="alert alert-important alert-success alert-dismissible mb-2" role="alert">
                <div class="d-flex">
                    <div>
                        {{Session::get('success')}}
                    </div>
                </div>
                <a class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="close"></a>
            </div>
            @endif
            
            <div class="row row-deck row-cards">
                <div class="col-sm-12 col-lg-12">
                    <form action="{{ route('admin.update.theme.css') }}" method="post" enctype="multipart/form-data"
                        class="card">
                        @csrf
                        <div class="card-body">
                            <div class="row">
                                <div class="col-xl-12">
                                    <div class="row">
                                        <input type="hidden" class="form-control" name="theme_id" value="{{ $themeDetails->theme_id }}">

                                        <div class="col-md-12 col-xl-12">
                                            <div class="mb-3">
                                                <label class="form-label required">{{ __('CSS Code') }}</label>
                                                <textarea id="css-editor" name="css" placeholder="{{ __('Example: body { background-color: #fff; color: #333; }') }}">{{ trim($themeDetails->theme_css) }}</textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
 
                        <div class="card-footer text-end">
                            <button type="submit" class="btn btn-primary">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-edit"
                                    width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                    fill="none" stroke-linecap="round" stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                    <path d="M9 7h-3a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-3">
                                    </path>
                                    <path d="M9 15h3l8.5 -8.5a1.5 1.5 0 0 0 -3 -3l-8.5 8.5v3">
                                    </path>
                                    <line x1="16" y1="5" x2="19" y2="8"></line>
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

{{-- Custom JS --}}
@section('scripts')
<!-- CodeMirror Core CSS/JS -->
<link rel="stylesheet" href="{{ asset('plugins/codemirror/css/codemirror.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/codemirror/css/material-darker.min.css') }}">
<script src="{{ asset('plugins/codemirror/js/codemirror.min.js') }}"></script>
<script src="{{ asset('plugins/codemirror/js/css.min.js') }}"></script>

<!-- Addon: Placeholder -->
<script src="{{ asset('plugins/codemirror/js/placeholder.min.js') }}"></script>

<!-- Addons: Autocomplete -->
<link rel="stylesheet" href="{{ asset('plugins/codemirror/css/show-hint.min.css') }}">
<script src="{{ asset('plugins/codemirror/js/show-hint.min.js') }}"></script>
<script src="{{ asset('plugins/codemirror/js/css-hint.min.js') }}"></script>

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
            "Ctrl-Space": "autocomplete"  // Trigger autocomplete
        }
    });

    // Set width and height
    editor.setSize("100%", "460px"); // width: 100%, height: 400px

    // Trigger autocomplete automatically when typing
    editor.on("inputRead", function(cm, change) {
        if (change.text[0].match(/[\w-]/)) {
            cm.showHint({completeSingle: false});
        }
    });
});
</script>
@endsection
@endsection