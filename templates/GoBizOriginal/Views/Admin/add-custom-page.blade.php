@extends('admin.layouts.index', ['header' => true, 'nav' => true, 'demo' => true])

@section('content')
    <div class="page-wrapper">
        <!-- Page Title Header Wrapper (Wraps the inner container-fluid) -->
        <div class="page-header d-print-none">
            <div class="container-fluid">
                <div class="row g-2 align-items-center">
                    <div class="col">
                        <!-- Hierarchical Breadcrumbs -->
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-2">
                                <li class="breadcrumb-item">
                                    <a href="{{ route('admin.dashboard') }}" class="text-secondary text-decoration-none">
                                        {{ __('Dashboard') }}
                                    </a>
                                </li>
                                <li class="breadcrumb-item">
                                    <a href="{{ route('admin.web-templates') }}" class="text-secondary text-decoration-none">
                                        {{ __('Web Templates') }}
                                    </a>
                                </li>
                                <li class="breadcrumb-item">
                                    <a href="{{ route('admin.web-template.gobiz-original') }}" class="text-secondary text-decoration-none">
                                        {{ __('GoBiz Original') }}
                                    </a>
                                </li>
                                <li class="breadcrumb-item active" aria-current="page">
                                    {{ __('Create Page') }}
                                </li>
                            </ol>
                        </nav>

                        <h2 class="page-title">
                            {{ __('Create Custom Page') }}
                        </h2>

                        <!-- Helper Subtitle -->
                        <div class="text-muted mt-1">
                            {{ __('Deploy new localized custom pages on your front-end template with customized search engine optimizations.') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Page Body -->
        <div class="page-body">
            <div class="container-fluid">

                {{-- Client-Side Required Validation Fail Alert --}}
                <div class="alert alert-important alert-danger alert-dismissible d-none mb-3" role="alert" id="fillError">
                    <div class="d-flex">
                        <div>
                            {{ __('Please ensure all required fields and editor contents are completely filled.') }}
                        </div>
                    </div>
                    <a class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="close"></a>
                </div>

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
                    <div class="col-12">
                        <!-- Upgraded to Borderless Shadowed Card Wrapper -->
                        <div class="card border-0 shadow-sm">
                            <form action="{{ route('admin.web-template.gobiz-original.save-custom-page') }}" method="post"
                                enctype="multipart/form-data" id="customPageForm">
                                @csrf
                                <div class="card-body">
                                    <div class="row g-3">
                                        {{-- Name (Hidden Parameter Mapping) --}}
                                        <input type="hidden" name="name" value="Custom Page" required />

                                        {{-- Page Name --}}
                                        <div class="col-12 col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label required"
                                                    for="page_name">{{ __('Page Name') }}</label>
                                                <input type="text" class="form-control" name="page_name" id="page_name"
                                                    value="{{ old('page_name') }}"
                                                    placeholder="{{ __('Enter page name') }}" required />
                                            </div>
                                        </div>

                                        {{-- Slug --}}
                                        <div class="col-12 col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label required"
                                                    for="slug">{{ __('Slug') }}</label>
                                                <input type="text" class="form-control" name="slug" id="slug"
                                                    value="{{ old('slug') }}" placeholder="{{ __('e.g., about-us') }}"
                                                    required />
                                            </div>
                                        </div>

                                        {{-- Body Textarea --}}
                                        <div class="col-12">
                                            <div class="mb-3">
                                                <label class="form-label required"
                                                    for="body">{{ __('Body') }}</label>
                                                <textarea name="body" id="body" cols="30" rows="5" class="form-control"
                                                    placeholder="{{ __('Enter page body content...') }}">{{ old('body') }}</textarea>
                                            </div>
                                        </div>

                                        {{-- SEO Segment Header Break --}}
                                        <div class="col-12 border-top pt-4 mt-3">
                                            <h3 class="card-title fw-bold m-0 mb-3">{{ __('SEO Configurations') }}</h3>
                                        </div>

                                        {{-- Title --}}
                                        <div class="col-12 col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label required"
                                                    for="title">{{ __('Meta Title') }}</label>
                                                <textarea class="form-control" id="title" name="title" rows="2"
                                                    placeholder="{{ __('Enter search snippet title tag') }}" required>{{ old('title') }}</textarea>
                                            </div>
                                        </div>

                                        {{-- Meta Description --}}
                                        <div class="col-12 col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label required"
                                                    for="description">{{ __('Meta Description') }}</label>
                                                <textarea name="description" id="description" rows="2" class="form-control"
                                                    placeholder="{{ __('Enter meta description to summarize content inside index indices') }}" required>{{ old('description') }}</textarea>
                                            </div>
                                        </div>

                                        {{-- Meta Keywords --}}
                                        <div class="col-12">
                                            <div class="mb-3">
                                                <label class="form-label required"
                                                    for="keywords">{{ __('Meta Keywords') }}</label>
                                                <textarea name="keywords" id="keywords" rows="2" class="form-control"
                                                    placeholder="{{ __('Enter primary target keywords separated by commas (e.g., about, business, details)') }}"
                                                    required>{{ old('keywords') }}</textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Standardized Right-Aligned Form Footer (Omitted Cancel Button) -->
                                <div class="card-footer text-end">
                                    <button type="submit"
                                        class="btn btn-primary btn-md d-inline-flex align-items-center">
                                        <!-- Device Floppy Icon -->
                                        <svg xmlns="http://www.w3.org/2000/svg"
                                            class="icon icon-tabler icon-tabler-device-floppy me-2" width="24"
                                            height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                            fill="none" stroke-linecap="round" stroke-linejoin="round">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                            <path
                                                d="M6 4h10l4 4v10a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2" />
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
        </div>

        {{-- Footer --}}
        @include('admin.includes.footer')
    </div>
@endsection

{{-- Custom JS --}}
@section('scripts')
    {{-- TinyMCE Library loader cleanly imported inside correct section --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/7.0.1/tinymce.min.js"
        integrity="sha512-KGtsnWohFUg0oksKq7p7eDgA1Rw2nBfqhGJn463/rGhtUY825dBqGexj8eP04LwfnsSW6dNAHAlOqKJKquHsnw=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    <script>
        "use strict";

        // Convert page name to URL-friendly lowercase slug dynamically
        $('#page_name').on("change keyup paste click", function() {
            var Text = $(this).val();
            Text = Text.toLowerCase();
            Text = Text.replace(/[^a-zA-Z0-9]+/g, '-');
            $('#slug').val(Text);
        });

        // Initialize TinyMCE taking system theme configuration context into account
        const isDark = document.body.getAttribute('data-bs-theme') === 'dark';

        tinymce.init({
            selector: 'textarea#body',
            plugins: 'code preview importcss searchreplace autolink autosave save directionality visualblocks visualchars link charmap pagebreak nonbreaking anchor insertdatetime advlist lists wordcount quickbars emoticons',
            menubar: false,
            statusbar: false,
            skin: isDark ? 'oxide-dark' : 'oxide',
            content_css: isDark ? [
                'https://fonts.googleapis.com/css2?family=Figtree:wght@300;400;500;600;700;800&display=swap',
                'dark'
            ] : [
                'https://fonts.googleapis.com/css2?family=Figtree:wght@300;400;500;600;700;800&display=swap',
                'default'
            ],
            setup: function(editor) {
                editor.on('init', function() {
                    if (document.body.dataset.bsTheme === 'dark') {
                        editor.getBody().setAttribute('data-bs-theme', 'dark');
                    }
                });
            },
            content_style: isDark ? `
                body {
                    background-color: #151f2c !important;
                    font-family: "Figtree", sans-serif !important;
                }
                .mce-content-body[data-mce-placeholder]:not(.mce-visualblocks)::before {
                    color: rgba(34,47,62,.5) !important;
                    opacity: 1 !important;
                    font-family: "Figtree", sans-serif !important;
                    font-size: 0.85rem !important;
                    font-weight: 600 !important;
                }
            ` : `
                body {
                    font-family: "Figtree", sans-serif !important;
                }
                .mce-content-body[data-mce-placeholder]:not(.mce-visualblocks)::before {
                    opacity: 1 !important;
                    font-family: "Figtree", sans-serif !important;
                    font-size: 0.85rem !important;
                    font-weight: 600 !important;
                }
            `,
            mobile: {
                plugins: 'paste',
                toolbar: 'code undo redo | bold italic underline strikethrough | blocks | alignleft aligncenter alignright | numlist bullist | link emoticons | preview'
            }
        });

        // Dynamic validation: Check for genuine content strings inside the TinyMCE editor instance
        $('#customPageForm').on('submit', function(e) {
            var editorContent = tinymce.get('body').getContent({
                format: 'text'
            }).trim();
            if (editorContent === '') {
                $('#fillError').removeClass('d-none');
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
                e.preventDefault();
            }
        });
    </script>
@endsection
