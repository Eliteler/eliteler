@extends('user.layouts.index', ['header' => true, 'nav' => true, 'demo' => true, 'settings' => $settings])

@section('css')
    <link href="{{ asset('css/dropzone.min.css') }}" rel="stylesheet">
    <script src="{{ asset('js/dropzone.min.js') }}"></script>

    <style>
        [data-bs-theme="dark"] .dz-message {
            color: #667382;
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
                            {{ __('Add Media') }}
                        </h2>
                    </div>
                </div>
            </div>
        </div>
        <div class="page-body">
            <div class="container-fluid">
                {{-- Failed --}}
                <div class="alert alert-important alert-danger alert-dismissible" id="failed" role="alert">
                    <div class="d-flex">
                        <div id="failedMessage"></div>
                    </div>
                    <a class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="close"></a>
                </div>

                {{-- Success --}}
                <div class="alert alert-important alert-success alert-dismissible" id="success" role="alert">
                    <div class="d-flex">
                        <div id="successMessage"></div>
                    </div>
                    <a class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="close"></a>
                </div>

                <div class="row row-cards">
                    <div class="col-sm-12 col-lg-12">
                        <div id="dropzone">
                            <form action="{{ route('user.upload.media') }}" class="dropzone" id="dropzone"
                                enctype="multipart/form-data">
                                @csrf
                                <div class="dz-message">
                                    {{ __('Drag and Drop Single/Multiple Files Here') }} <br>
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

@section('scripts')
    <script type="text/javascript">
        // Default
        $('#success').hide();
        $('#failed').hide();

        Dropzone.options.dropzone = {
            maxFilesize: {{ env('SIZE_LIMIT') / 1024 }},
            acceptedFiles: ".jpeg,.jpg,.png,.gif",
            timeout: 180000,
            init: function() {
                var myDropzone = this;

                myDropzone.on("success", function(file, response) {
                    if (response.status == 'success') {
                        // Store success status temporarily, actual alert after all uploads
                        file.uploadSuccess = true;
                        file.serverMessage = response.message;
                    } else {
                        file.uploadSuccess = false;
                        file.serverMessage = response.message;
                    }
                });

                myDropzone.on("queuecomplete", function() {
                    let allSuccess = true;
                    let firstError = null;

                    myDropzone.files.forEach(function(file) {
                        if (!file.uploadSuccess) {
                            allSuccess = false;
                            if (!firstError) {
                                firstError = file.serverMessage || 'Upload failed.';
                            }
                        }
                    });

                    if (allSuccess) {
                        $('#successMessage').html(`<span>All files uploaded successfully.</span>`);
                        $('#success').show();
                        // Optional: redirect after short delay
                        setTimeout(() => {
                            window.location.href = `{{ route('user.media') }}`;
                        }, 1500);
                    } else {
                        $('#failedMessage').html(`<span>` + firstError + `</span>`);
                        $('#failed').show();
                    }
                });
            }
        };
    </script>
@endsection
@endsection
