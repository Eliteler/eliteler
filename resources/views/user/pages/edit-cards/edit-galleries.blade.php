@extends('user.layouts.index', ['header' => true, 'nav' => true, 'demo' => true, 'settings' => $settings])

{{-- Custom CSS for Enhanced Typography, Readability & Modern Tabler Design --}}
@section('css')
    {{-- Galleries CSS --}}
    <link rel="stylesheet" href="{{ asset('css/galleries.css') }}" />

    {{-- Font Awesome CSS --}}
    <link rel="stylesheet" href="{{ asset('css/all.css') }}" />

    {{-- Dropzone CSS --}}
    <link rel="stylesheet" href="{{ asset('css/dropzone.min.css') }}">

    {{-- Dropzone JS --}}
    <script src="{{ asset('js/dropzone.min.js') }}"></script>

    {{-- Clipboard JS --}}
    <script src="{{ asset('js/clipboard.min.js') }}"></script>
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
                                {{ __('Galleries') }}
                            </li>
                        </ol>
                    </nav>
                    <div class="col">
                        <h2 class="page-title text-dark fw-bold">
                            {{ __('Galleries') }}
                        </h2>
                        <div class="text-secondary mt-1">
                            {{ __('Upload gallery images, write captions, and deploy dynamic photo showcases.') }}
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

                <div id="errorMessage" style="display:none;"
                    class="alert alert-important alert-danger alert-dismissible mb-3 border-0 shadow-sm"></div>

                <div class="card border-0 shadow-sm">
                    <div class="row g-0">
                        <!-- Sidebar Navigation -->
                        <div class="col-12 col-md-3 col-lg-2 border-end">
                            <div class="card-body">
                                <h4 class="subheader mb-3 text-uppercase fw-bold text-muted">{{ __('Card Settings') }}</h4>
                                <div class="list-group list-group-transparent">
                                    @include('user.pages.edit-cards.includes.nav-link', [
                                        'link' => 'galleries',
                                    ])
                                </div>
                            </div>
                        </div>

                        <!-- Main Form Area -->
                        <div class="col-12 col-md-9 col-lg-10 d-flex flex-column">
                            <form action="{{ route('user.update.galleries', Request::segment(3)) }}" method="post"
                                enctype="multipart/form-data" id="myForm">
                                @csrf
                                <div class="card-body p-3 p-lg-3">
                                    <div class="d-flex align-items-center justify-content-between mb-4">
                                        <div>
                                            <h3 class="card-title m-0 text-dark fw-bold">{{ __('Manage Galleries') }}</h3>
                                            <p class="text-muted small mb-0 mt-1">
                                                {{ __('Upload gallery images, write captions, and select dynamic media illustrations.') }}
                                            </p>
                                        </div>

                                        <button type="button" onclick="addGallery()"
                                            class="btn btn-primary btn-icon-only d-inline-flex align-items-center shadow-sm fw-semibold">
                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                class="icon icon-tabler icon-tabler-plus" width="20" height="20"
                                                viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                                                stroke-linecap="round" stroke-linejoin="round">
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                <line x1="12" y1="5" x2="12" y2="19" />
                                                <line x1="5" y1="12" x2="19" y2="12" />
                                            </svg>
                                            <span class="d-none d-sm-inline-block">{{ __('Add New') }}</span>
                                        </button>
                                    </div>

                                    {{-- Empty State when no galleries exist --}}
                                    <div id="no-galleries-empty"
                                        class="{{ count($galleries) > 0 ? 'd-none' : '' }} py-5 text-center">
                                        <div class="empty">
                                            <div class="empty-icon mb-3">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48"
                                                    viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                    class="icon icon-tabler icons-tabler-outline icon-tabler-library-photo">
                                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                    <path
                                                        d="M7 5.667a2.667 2.667 0 0 1 2.667 -2.667h8.666a2.667 2.667 0 0 1 2.667 2.667v8.666a2.667 2.667 0 0 1 -2.667 2.667h-8.666a2.667 2.667 0 0 1 -2.667 -2.667l0 -8.666" />
                                                    <path
                                                        d="M4.012 7.26a2.005 2.005 0 0 0 -1.012 1.737v10c0 1.1 .9 2 2 2h10c.75 0 1.158 -.385 1.5 -1" />
                                                    <path d="M17 7h.01" />
                                                    <path d="M7 13l3.644 -3.644a1.21 1.21 0 0 1 1.712 0l3.644 3.644" />
                                                    <path d="M15 12l1.644 -1.644a1.21 1.21 0 0 1 1.712 0l2.644 2.644" />
                                                </svg>
                                            </div>
                                            <p class="empty-title h4 font-weight-bold mb-2 text-dark">
                                                {{ __('No gallery images added yet') }}
                                            </p>
                                            <p class="empty-subtitle text-secondary mb-3">
                                                {{ __('Click the button below to upload your showcase images and write custom captions.') }}
                                            </p>
                                            <button type="button" onclick="addGallery()"
                                                class="btn btn-primary fw-semibold">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="icon me-1" width="20"
                                                    height="20" viewBox="0 0 24 24" stroke-width="2"
                                                    stroke="currentColor" fill="none" stroke-linecap="round"
                                                    stroke-linejoin="round">
                                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                    <line x1="12" y1="5" x2="12" y2="19" />
                                                    <line x1="5" y1="12" x2="19" y2="12" />
                                                </svg>
                                                {{ __('Add Gallery Image') }}
                                            </button>
                                        </div>
                                    </div>

                                    {{-- Existing Galleries Rows --}}
                                    <div id="galleries-container">
                                        @for ($i = 0; $i < count($galleries); $i++)
                                            <div class="gallery-item-row" id="{{ $i }}">
                                                <div class="row g-3 align-items-end">
                                                    {{-- Gallery Image Input --}}
                                                    <div class="col-md-6 col-lg-6">
                                                        <label
                                                            class="form-label required">{{ __('Gallery Image') }}</label>
                                                        <div class="input-group">
                                                            <input type="text"
                                                                class="image{{ $galleries[$i]->id }} media-model form-control gallery-img-input"
                                                                name="gallery_image[]"
                                                                placeholder="{{ __('No image chosen') }}"
                                                                value="{{ $galleries[$i]->gallery_image }}" required
                                                                readonly>
                                                            <button
                                                                class="btn btn-primary d-inline-flex align-items-center"
                                                                type="button"
                                                                onclick="openMedia({{ $galleries[$i]->id }})">
                                                                <svg xmlns="http://www.w3.org/2000/svg" width="18"
                                                                    height="18" viewBox="0 0 24 24" fill="none"
                                                                    stroke="currentColor" stroke-width="2"
                                                                    stroke-linecap="round" stroke-linejoin="round"
                                                                    class="icon me-1">
                                                                    <path stroke="none" d="M0 0h24v24H0z"
                                                                        fill="none" />
                                                                    <path
                                                                        d="M7 5.667a2.667 2.667 0 0 1 2.667 -2.667h8.666a2.667 2.667 0 0 1 2.667 2.667v8.666a2.667 2.667 0 0 1 -2.667 2.667h-8.666a2.667 2.667 0 0 1 -2.667 -2.667l0 -8.666" />
                                                                    <path
                                                                        d="M4.012 7.26a2.005 2.005 0 0 0 -1.012 1.737v10c0 1.1 .9 2 2 2h10c.75 0 1.158 -.385 1.5 -1" />
                                                                    <path d="M17 7h.01" />
                                                                    <path
                                                                        d="M7 13l3.644 -3.644a1.21 1.21 0 0 1 1.712 0l3.644 3.644" />
                                                                    <path
                                                                        d="M15 12l1.644 -1.644a1.21 1.21 0 0 1 1.712 0l2.644 2.644" />
                                                                </svg>
                                                                <span>{{ __('Choose Image') }}</span>
                                                            </button>
                                                        </div>
                                                    </div>

                                                    {{-- Image Caption Input --}}
                                                    <div class="col-md">
                                                        <label class="form-label">{{ __('Image Caption') }}</label>
                                                        <input type="text" class="form-control" name="caption[]"
                                                            placeholder="{{ __('Enter image caption') }}"
                                                            value="{{ $galleries[$i]->caption }}">
                                                    </div>

                                                    {{-- Solid Red Delete Button --}}
                                                    <div class="col-auto">
                                                        <button type="button"
                                                            class="btn btn-danger btn-icon-only btn-icon shadow-sm"
                                                            onclick="removeGallery({{ $i }})"
                                                            title="{{ __('Remove Option') }}">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="icon"
                                                                width="20" height="20" viewBox="0 0 24 24"
                                                                stroke-width="2" stroke="currentColor" fill="none"
                                                                stroke-linecap="round" stroke-linejoin="round">
                                                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                                <line x1="4" y1="7" x2="20"
                                                                    y2="7" />
                                                                <line x1="10" y1="11" x2="10"
                                                                    y2="17" />
                                                                <line x1="14" y1="11" x2="14"
                                                                    y2="17" />
                                                                <path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" />
                                                                <path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" />
                                                            </svg>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        @endfor

                                        {{-- Dynamic Appended Elements Target --}}
                                        <div id="more-gallery"></div>
                                    </div>
                                </div>

                                <!-- Form Footer -->
                                <div class="card-footer border-top d-flex align-items-center justify-content-end py-3">
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

        {{-- Media Library Modal --}}
        <div class="modal modal-bg fade" id="openMediaModel" tabindex="-1" aria-labelledby="openMediaModelLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; overflow: hidden;">

                    <!-- Modal Header (Matching Mobile Screenshot) -->
                    <div
                        class="modal-header border-bottom py-3 px-3 px-md-4 bg-surface d-flex align-items-center justify-content-between">
                        <!-- Left: Icon & Title -->
                        <div class="d-flex align-items-center gap-2 gap-md-3">
                            <div class="avatar avatar-md bg-primary-lt rounded-circle flex-shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg"
                                    class="icon icon-tabler icon-tabler-photo text-primary" width="22" height="22"
                                    viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                    <line x1="15" y1="8" x2="15.01" y2="8" />
                                    <rect x="4" y="4" width="16" height="16" rx="3" />
                                    <path d="M4 15l4 -4a3 5 0 0 1 3 0l5 5" />
                                    <path d="M14 14l1 -1a3 5 0 0 1 3 0l2 2" />
                                </svg>
                            </div>
                            <div>
                                <h4 class="modal-title fw-bold mb-0 text-reset fs-3" id="openMediaModelLabel">
                                    {{ __('Media Library') }}
                                </h4>
                                <div class="text-muted small" style="font-size: 0.75rem;">
                                    {{ __('Upload multiple images or select an uploaded image below') }}
                                </div>
                            </div>
                        </div>

                        <!-- Right: Tabs (Browse & Upload) -->
                        <div class="d-flex flex-column flex-md-row gap-1 bg-surface-secondary p-1 rounded-3"
                            role="tablist">
                            <button
                                class="nav-link active px-3 py-1 fw-semibold small d-inline-flex align-items-center gap-1 border-0 bg-transparent text-muted"
                                data-bs-toggle="tab" data-bs-target="#modal-tab-browse" type="button" role="tab">
                                <svg xmlns="http://www.w3.org/2000/svg"
                                    class="icon icon-tabler icon-tabler-folders text-primary" width="16"
                                    height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                    fill="none" stroke-linecap="round" stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                    <path
                                        d="M9 4h3l2 2h5a2 2 0 0 1 2 2v7a2 2 0 0 1 -2 2h-10a2 2 0 0 1 -2 -2v-9a2 2 0 0 1 2 -2" />
                                    <path d="M17 17v2a2 2 0 0 1 -2 2h-10a2 2 0 0 1 -2 -2v-9a2 2 0 0 1 2 -2h2" />
                                </svg>
                                <span class="text-primary fw-bold">{{ __('Browse') }}</span>
                            </button>
                            <button
                                class="nav-link px-3 py-1 fw-semibold small d-inline-flex align-items-center gap-1 border-0 bg-transparent text-muted"
                                data-bs-toggle="tab" data-bs-target="#modal-tab-upload" type="button" role="tab">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-cloud-upload"
                                    width="16" height="16" viewBox="0 0 24 24" stroke-width="2"
                                    stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                    <path d="M7 18a4.6 4.4 0 0 1 0 -9a5 4.5 0 0 1 11 2h1a3.5 3.5 0 0 1 0 7h-1" />
                                    <polyline points="9 15 12 12 15 15" />
                                    <line x1="12" y1="12" x2="12" y2="21" />
                                </svg>
                                <span>{{ __('Upload') }}</span>
                            </button>
                        </div>
                    </div>

                    <!-- Modal Body with Scrollable Area -->
                    <div class="modal-body p-3 p-md-4">
                        <div id="uploadMessage"></div>

                        <div class="tab-content">
                            {{-- Tab 1: Browse Media (List) --}}
                            <div class="tab-pane fade show active" id="modal-tab-browse" role="tabpanel">
                                @include('user.pages.cards.media.list')
                            </div>

                            {{-- Tab 2: Upload Files (Dropzone) --}}
                            <div class="tab-pane fade" id="modal-tab-upload" role="tabpanel">
                                @include('user.pages.cards.media.upload')
                            </div>
                        </div>

                        <div id="pagination" class="mt-2"></div>
                    </div>

                    <!-- Modal Footer (Full-width Primary Cancel Button on Mobile) -->
                    <div class="modal-footer border-top py-3 px-3 px-md-4">
                        <button type="button" class="btn btn-primary w-100 fw-bold shadow-sm" data-bs-dismiss="modal">
                            {{ __('Cancel') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('custom-js')
    <script>
        var count = {{ count($galleries) }};
        var currentSelection = 0;

        function checkEmptyState() {
            if (count === 0) {
                $('#no-galleries-empty').removeClass('d-none');
            } else {
                $('#no-galleries-empty').addClass('d-none');
            }
        }

        function addGallery() {
            "use strict";
            if (count >= {{ $plan_details->no_of_galleries }}) {
                new swal({
                    title: `{{ __('Oops!') }}`,
                    icon: 'warning',
                    text: `{{ __('You have reached your current plan limit.') }}`,
                    timer: 2000,
                    buttons: false,
                    showConfirmButton: false,
                });
            } else {
                count++;
                checkEmptyState();

                var id = getRandomInt();
                var gallery = `
                    <div class="gallery-item-row" id="${id}">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-6 col-lg-6">
                                <label class="form-label required">{{ __('Gallery Image') }}</label>
                                <div class="input-group">
                                    <input type="text" class="image${id} media-model form-control gallery-img-input" name="gallery_image[]" placeholder="{{ __('No image chosen') }}" required readonly>
                                    <button class="btn btn-primary d-inline-flex align-items-center" type="button" onclick="openMedia(${id})">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon me-1">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                            <path d="M7 5.667a2.667 2.667 0 0 1 2.667 -2.667h8.666a2.667 2.667 0 0 1 2.667 2.667v8.666a2.667 2.667 0 0 1 -2.667 2.667h-8.666a2.667 2.667 0 0 1 -2.667 -2.667l0 -8.666" />
                                            <path d="M4.012 7.26a2.005 2.005 0 0 0 -1.012 1.737v10c0 1.1 .9 2 2 2h10c.75 0 1.158 -.385 1.5 -1" />
                                            <path d="M17 7h.01" />
                                            <path d="M7 13l3.644 -3.644a1.21 1.21 0 0 1 1.712 0l3.644 3.644" />
                                            <path d="M15 12l1.644 -1.644a1.21 1.21 0 0 1 1.712 0l2.644 2.644" />
                                        </svg>
                                        <span>{{ __('Choose Image') }}</span>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md">
                                <label class="form-label">{{ __('Image Caption') }}</label>
                                <input type="text" class="form-control" name="caption[]" placeholder="{{ __('Enter image caption') }}">
                            </div>
                            <div class="col-auto">
                                <button type="button" class="btn btn-danger btn-icon-only btn-icon shadow-sm" onclick="removeGallery(${id})" title="{{ __('Remove Option') }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="20" height="20" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                        <line x1="4" y1="7" x2="20" y2="7" />
                                        <line x1="10" y1="11" x2="10" y2="17" />
                                        <line x1="14" y1="11" x2="14" y2="17" />
                                        <path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" />
                                        <path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                `;
                $("#more-gallery").append(gallery);
            }
        }

        function removeGallery(id) {
            "use strict";
            $("#" + id).remove();
            count--;
            checkEmptyState();
        }

        function getRandomInt() {
            var min = Math.ceil(0);
            var max = Math.floor(9999999999);
            return Math.floor(Math.random() * (max - min) + min);
        }

        function openMedia(id) {
            "use strict";
            currentSelection = id;
            showModalSkeleton();
            $('#openMediaModel').modal('show');
            loadMedia(1);
        }

        // Form Submit Validation for Gallery Image Required
        $('#myForm').on('submit', function(e) {
            let hasEmptyImage = false;
            $('.gallery-img-input').each(function() {
                if ($(this).val().trim() === '') {
                    hasEmptyImage = true;
                    $(this).focus();
                    return false;
                }
            });

            if (hasEmptyImage) {
                e.preventDefault();
                $('#errorMessage').text("{{ __('Gallery image is required.') }}").show();
                $('#errorMessage').delay(2000).fadeOut(200);
                return false;
            }
        });
    </script>

    {{-- Dropzone files upload config --}}
    <script type="text/javascript">
        Dropzone.options.dropzone = {
            maxFilesize: {{ env('SIZE_LIMIT') / 1024 }},
            acceptedFiles: ".jpeg,.jpg,.png,.gif",
            timeout: 180000,

            init: function() {
                $('#success').remove();
                $('#failed').remove();

                this.on("success", function(file, response) {
                    $('#uploadMessage').empty();

                    if (response.status === 'success') {
                        $('#uploadMessage').html(`
                            <div class="alert alert-important alert-success alert-dismissible mb-3 border-0 shadow-sm" role="alert">
                                ${response.message || '{{ __('Image uploaded successfully!') }}'}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        `);

                        this.removeFile(file);
                        loadMedia();

                        setTimeout(function() {
                            $('#uploadMessage .alert').fadeOut();
                        }, 3000);
                    } else {
                        $('#uploadMessage').html(`
                            <div class="alert alert-important alert-danger alert-dismissible mb-3 border-0 shadow-sm" role="alert">
                                ${response.message || '{{ __('Upload failed. Please try again.') }}'}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        `);
                    }
                });

                this.on("error", function(file, response) {
                    $('#uploadMessage').html(`
                        <div class="alert alert-important alert-danger alert-dismissible mb-3 border-0 shadow-sm" role="alert">
                            {{ __('Upload failed. Please try again.') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    `);
                    this.removeFile(file);
                });
            }
        };
    </script>

    {{-- Paginated Media listing implementations --}}
    <script>
        var currentPage = 1;
        var totalPages = 1;

        function loadPreviousPage() {
            "use strict";
            if (currentPage > 1) {
                currentPage--;
                loadMedia(currentPage);
            }
        }

        function loadNextPage() {
            "use strict";
            if (currentPage < totalPages) {
                currentPage++;
                loadMedia(currentPage);
            }
        }

        function showModalSkeleton() {
            let skeletonHtml = '';
            for (let i = 0; i < 8; i++) {
                skeletonHtml += `
                    <div class="col-6 col-md-4 col-lg-3">
                        <div class="modal-media-card placeholder-glow">
                            <div class="modal-media-thumb placeholder"></div>
                            <div class="modal-media-body">
                                <div class="placeholder col-8 rounded mb-2"></div>
                                <div class="placeholder rounded" style="width: 38px; height: 38px;"></div>
                            </div>
                        </div>
                    </div>
                `;
            }
            $('#mediaCardsContainer').html(skeletonHtml);
            $('#noImagesFound').hide();
            $('#showPagination').hide();
        }

        function loadMedia(page = 1) {
            showModalSkeleton();

            $.ajax({
                url: '{{ route('user.media') }}',
                method: 'GET',
                data: {
                    page: page
                },
                dataType: 'json',
                success: handleMediaResponse,
                error: function(xhr, status, error) {
                    console.error(error);
                }
            });
        }

        function handleMediaResponse(response) {
            "use strict";
            var mediaData = response.media.data;
            if (mediaData.length > 0) {
                $('#noImagesFound').hide();
                $('#showPagination').show();
                displayMediaCards(mediaData);
                updatePaginationInfo(response.media);
            } else {
                $('#noImagesFound').show();
                $('#showPagination').hide();
                $('#mediaCardsContainer').html('');
                updatePaginationInfo(response.media);
            }
        }

        function displayMediaCards(mediaData) {
            "use strict";
            var mediaCardsHtml = '';
            mediaData.forEach(function(media) {
                mediaCardsHtml += `
                    <div class="col-6 col-md-4 col-lg-3">
                        <div class="modal-media-card">
                            <div class="modal-media-thumb" style="background-image: url('${media.base_url}${media.media_url}');"></div>
                            <div class="modal-media-body">
                                <div class="modal-media-title" title="${media.media_name}">${media.media_name}</div>
                                <button type="button" class="btn btn-primary btn-modal-copy-icon copyBoard shadow-sm" data-clipboard-text="${media.media_url}" data-bs-toggle="tooltip" data-bs-placement="bottom" title="{{ __('Copy') }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-copy m-0" width="18" height="18" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                        <rect x="8" y="8" width="12" height="12" rx="2"></rect>
                                        <path d="M16 8v-2a2 2 0 0 0 -2 -2h-8a2 2 0 0 0 -2 2v8a2 2 0 0 0 2 2h2"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                `;
            });
            $('#mediaCardsContainer').html(mediaCardsHtml);
        }

        function updatePaginationInfo(media) {
            "use strict";
            $('#paginationStartIndex').text(media.from || 1);
            $('#paginationEndIndex').text(media.to || media.total);
            $('#paginationTotalCount').text(media.total || 0);
            currentPage = media.current_page;
            totalPages = media.last_page;

            $('#prevPageBtn').prop('disabled', currentPage <= 1).toggleClass('disabled', currentPage <= 1);
            $('#nextPageBtn').prop('disabled', currentPage >= totalPages).toggleClass('disabled', currentPage >=
                totalPages);
        }

        $(document).ready(function() {
            "use strict";
            loadMedia();
        });
    </script>

    {{-- Clipboard success handles --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            "use strict";
            var clipboard = new ClipboardJS('.copyBoard');

            clipboard.on('success', function(e) {
                "use strict";
                $('.image' + currentSelection).val(e.text);
                $('#openMediaModel').modal('hide');
            });

            clipboard.on('error', function(e) {
                "use strict";
                showErrorAlert('{{ __('Failed to copy text to clipboard. Please try again.') }}');
            });

            function showSuccessAlert(message) {
                "use strict";
                showAlert(message, 'success');
            }

            function showErrorAlert(message) {
                "use strict";
                showAlert(message, 'danger');
            }

            function showAlert(message, type) {
                "use strict";
                var alertDiv = document.createElement('div');
                alertDiv.classList.add('alert', 'alert-important', 'alert-' + type, 'alert-dismissible', 'mb-3');
                alertDiv.setAttribute('role', 'alert');

                var innerContent = '<div class="d-flex align-items-center gap-2">' +
                    '<div>' + message + '</div>' +
                    '</div>' +
                    '<a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>';

                alertDiv.innerHTML = innerContent;
                document.querySelector('#showAlert').appendChild(alertDiv);

                setTimeout(function() {
                    "use strict";
                    alertDiv.remove();
                }, 3000);
            }
        });
    </script>
@endpush
