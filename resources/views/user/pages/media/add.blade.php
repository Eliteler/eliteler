@extends('user.layouts.index', ['header' => true, 'nav' => true, 'demo' => true, 'settings' => $settings])

{{-- Scoped CSS --}}
@section('css')
    {{-- Upload Media CSS --}}
    <link href="{{ asset('css/upload-media.css') }}" rel="stylesheet">

    {{-- Dropzone CSS --}}
    <link href="{{ asset('css/dropzone.min.css') }}" rel="stylesheet">
@endsection

@section('content')
    <div class="page-wrapper">
        <!-- Page Header -->
        <div class="page-header d-print-none">
            <div class="container-fluid">
                <div class="row g-2 align-items-center">
                    <div class="col-12">
                        <!-- Hierarchical Breadcrumb Navigation -->
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb breadcrumb-arrows">
                                <li class="breadcrumb-item">
                                    <a href="{{ route('user.dashboard') }}" class="text-dark text-decoration-none">
                                        {{ __('Dashboard') }}
                                    </a>
                                </li>
                                <li class="breadcrumb-item">
                                    <a href="{{ route('user.media') }}" class="text-dark text-decoration-none">
                                        {{ __('Media Library') }}
                                    </a>
                                </li>
                                <li class="breadcrumb-item active fw-semibold" aria-current="page">
                                    {{ __('Upload Media') }}
                                </li>
                            </ol>
                        </nav>
                    </div>

                    <!-- Title & Subtitle -->
                    <div class="col">
                        <h2 class="page-title text-reset fw-bold">
                            {{ __('Upload Media') }}
                        </h2>
                        <div class="text-muted mt-1">
                            {{ __('Upload single or multiple images directly to your persistent store media assets.') }}
                        </div>
                    </div>

                    <!-- Return to Library Button -->
                    <div class="col-auto ms-auto d-print-none">
                        <a href="{{ route('user.media') }}"
                            class="btn btn-primary btn-icon-only d-inline-flex align-items-center gap-1 shadow-sm px-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-arrow-left"
                                width="18" height="18" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                fill="none" stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                <line x1="5" y1="12" x2="19" y2="12" />
                                <line x1="5" y1="12" x2="11" y2="18" />
                                <line x1="5" y1="12" x2="11" y2="6" />
                            </svg>
                            <span class="d-none d-sm-inline">{{ __('Back to Library') }}</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Page Body -->
        <div class="page-body">
            <div class="container-fluid">
                {{-- Failed Alert --}}
                <div class="alert alert-important alert-danger alert-dismissible border-0 shadow-sm mb-3" id="failed"
                    style="display: none;" role="alert">
                    <div class="d-flex align-items-center gap-2">
                        <div id="failedMessage"></div>
                    </div>
                    <a class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="close"></a>
                </div>

                {{-- Success Alert --}}
                <div class="alert alert-important alert-success alert-dismissible border-0 shadow-sm mb-3" id="success"
                    style="display: none;" role="alert">
                    <div class="d-flex align-items-center gap-2">
                        <div id="successMessage"></div>
                    </div>
                    <a class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="close"></a>
                </div>

                <div class="row row-cards g-4">
                    <!-- Main Dropzone Container -->
                    <div class="col-lg-8">
                        <div class="upload-card-wrapper shadow-sm p-4">
                            <form action="{{ route('user.upload.media') }}" class="dropzone dropzone-modern" id="dropzone"
                                enctype="multipart/form-data">
                                @csrf
                                <div class="dz-message text-center m-0">
                                    <div class="dz-icon-box">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36"
                                            viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                                            stroke-linecap="round" stroke-linejoin="round"
                                            class="icon icon-tabler icon-tabler-cloud-upload">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                            <path d="M7 18a4.6 4.4 0 0 1 0 -9a5 4.5 0 0 1 11 2h1a3.5 3.5 0 0 1 0 7h-1" />
                                            <polyline points="9 15 12 12 15 15" />
                                            <line x1="12" y1="12" x2="12" y2="21" />
                                        </svg>
                                    </div>
                                    <h3 class="fw-bold mb-1 text-reset">{{ __('Drag & Drop Your Files Here') }}</h3>
                                    <p class="text-muted small mb-3">{{ __('or browse directly from your computer') }}</p>
                                    <span class="btn btn-primary btn-sm px-3 shadow-sm">{{ __('Browse Files') }}</span>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Right Column: Guidelines & Storage Tips -->
                    <div class="col-lg-4">
                        <div class="upload-guideline-card shadow-sm p-4">
                            <h4 class="fw-bold mb-3 d-flex align-items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg"
                                    class="icon icon-tabler icon-tabler-info-circle text-primary" width="20"
                                    height="20" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                    fill="none" stroke-linecap="round" stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                    <circle cx="12" cy="12" r="9" />
                                    <line x1="12" y1="8" x2="12.01" y2="8" />
                                    <polyline points="11 12 12 12 12 16 13 16" />
                                </svg>
                                {{ __('Upload Specifications') }}
                            </h4>

                            <div class="d-flex flex-column gap-3">
                                <div class="guideline-item">
                                    <div class="guideline-icon bg-primary-lt text-primary">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-photo"
                                            width="18" height="18" viewBox="0 0 24 24" stroke-width="2"
                                            stroke="currentColor" fill="none" stroke-linecap="round"
                                            stroke-linejoin="round">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                            <line x1="15" y1="8" x2="15.01" y2="8" />
                                            <rect x="4" y="4" width="16" height="16" rx="3" />
                                            <path d="M4 15l4 -4a3 5 0 0 1 3 0l5 5" />
                                            <path d="M14 14l1 -1a3 5 0 0 1 3 0l2 2" />
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="fw-semibold small">{{ __('Supported Formats') }}</div>
                                        <div class="text-muted extra-small" style="font-size: 0.78rem;">JPEG, JPG, PNG,
                                            GIF, WEBP</div>
                                    </div>
                                </div>

                                <div class="guideline-item">
                                    <div class="guideline-icon bg-primary-lt text-primary">
                                        <svg xmlns="http://www.w3.org/2000/svg"
                                            class="icon icon-tabler icon-tabler-file-analytics" width="18"
                                            height="18" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                            fill="none" stroke-linecap="round" stroke-linejoin="round">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                            <path d="M14 3v4a1 1 0 0 0 1 1h4" />
                                            <path
                                                d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z" />
                                            <line x1="9" y1="17" x2="9" y2="14" />
                                            <line x1="12" y1="17" x2="12" y2="11" />
                                            <line x1="15" y1="17" x2="15" y2="16" />
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="fw-semibold small">{{ __('Max File Size') }}</div>
                                        <div class="text-muted extra-small" style="font-size: 0.78rem;">
                                            {{ round(env('SIZE_LIMIT') / 1024, 0) }} MB {{ __('per individual file') }}
                                        </div>
                                    </div>
                                </div>

                                <div class="guideline-item">
                                    <div class="guideline-icon bg-success-lt text-success">
                                        <svg xmlns="http://www.w3.org/2000/svg"
                                            class="icon icon-tabler icon-tabler-layers-linked" width="18"
                                            height="18" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                            fill="none" stroke-linecap="round" stroke-linejoin="round">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                            <path
                                                d="M19 8.268a2 2 0 0 1 1 1.732v8a2 2 0 0 1 -2 2h-8a2 2 0 0 1 -2 -2v-8a2 2 0 0 1 2 -2h3" />
                                            <path d="M5 15.734a2 2 0 0 1 -1 -1.734v-8a2 2 0 0 1 2 -2h8a2 2 0 0 1 2 2v2" />
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="fw-semibold small">{{ __('Batch Processing') }}</div>
                                        <div class="text-muted extra-small" style="font-size: 0.78rem;">
                                            {{ __('Multiple file uploads are simultaneously queued.') }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @include('user.includes.footer')
    </div>
@endsection

@push('custom-js')
    <script src="{{ asset('js/dropzone.min.js') }}"></script>
    <script type="text/javascript">
        "use strict";

        Dropzone.options.dropzone = {
            maxFilesize: {{ env('SIZE_LIMIT') / 1024 }},
            acceptedFiles: ".jpeg,.jpg,.png,.gif,.webp",
            timeout: 180000,
            init: function() {
                var myDropzone = this;

                myDropzone.on("success", function(file, response) {
                    if (response.status === 'success') {
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
                                firstError = file.serverMessage || `{{ __('Upload failed.') }}`;
                            }
                        }
                    });

                    if (allSuccess) {
                        $('#successMessage').html(
                            `<span>{{ __('All files uploaded successfully. Redirecting...') }}</span>`);
                        $('#success').show();

                        setTimeout(() => {
                            window.location.href = `{{ route('user.media') }}`;
                        }, 1200);
                    } else {
                        $('#failedMessage').html(`<span>${firstError}</span>`);
                        $('#failed').show();
                    }
                });
            }
        };
    </script>
@endpush
