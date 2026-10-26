@extends('admin.layouts.index', ['header' => true, 'nav' => true, 'demo' => true])

{{-- Custom CSS --}}
@section('css')
    <!-- Bootstrap Tags Input CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-tagsinput/0.8.0/bootstrap-tagsinput.css">
    {{-- Tiny MCE --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/7.0.1/tinymce.min.js"
        integrity="sha512-KGtsnWohFUg0oksKq7p7eDgA1Rw2nBfqhGJn463/rGhtUY825dBqGexj8eP04LwfnsSW6dNAHAlOqKJKquHsnw=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    <style>
        .bootstrap-tagsinput .tag {
            margin-right: 2px;
            color: #ffffff;
            background: #206bc4;
            border-radius: 5px;
        }

        .cover-preview {
            width: 50% !important;
        }

        /* Forces the tags input container to be full-width on all screen sizes */
        .bootstrap-tagsinput {
            width: 100% !important;
            display: block !important;
            padding: 9px 6px;
            border: var(--tblr-border-width) solid var(--tblr-border-color);
            border-radius: 8px !important;
        }

        /* Hides the remove button on the right side of the tags input */
        .bootstrap-tagsinput .tag [data-role="remove"] {
            margin-left: auto !important;
        }

        /* Scoped Google Search Preview custom styles */
        .google-preview-card {
            border-radius: 8px;
            background-color: var(--tblr-bg-surface-secondary, #f6f8fb);
        }

        .google-preview-title {
            font-size: 16px;
            font-weight: 600;
        }

        .google-search-container {
            border-color: #e6e8eb !important;
        }

        .google-logo-text {
            font-family: 'Product Sans', Arial, sans-serif;
            letter-spacing: -0.5px;
        }

        .google-search-bar {
            max-width: 100%;
            font-size: 13px;
            box-shadow: 0 1px 4px rgba(32, 33, 36, 0.15);
            border: 1px solid #e6e8eb;
            border-radius: 10px;
        }

        .google-search-bar input {
            font-size: 12px;
            height: auto;
            padding: 8px 12px !important;
        }

        .google-search-bar .icon {
            width: 14px;
            height: 14px;
        }

        .google-nav-tabs {
            font-size: 11px;
            font-family: arial, sans-serif;
        }

        .google-nav-tab-active {
            border-width: 2px !important;
            margin-bottom: -9px;
        }

        .google-result-snippet {
            border-color: #dadce0 !important;
        }

        .google-result-header {
            font-family: arial, sans-serif;
            font-size: 12px;
            color: #202124;
        }

        .google-result-favicon {
            width: 16px;
            height: 16px;
            font-size: 9px;
            font-weight: bold;
            color: #4285F4;
        }

        .google-result-title {
            color: #1a0dab !important;
            font-family: arial, sans-serif;
            font-size: 19px;
            font-weight: normal;
            line-height: 1.3;
            text-decoration: none;
        }

        .google-result-description {
            color: #4d5156;
            font-family: arial, sans-serif;
            font-size: 13px;
            line-height: 1.58;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            height: 41px;
        }

        .google-result-crumbs {
            font-size: 11px;
            color: #70757a !important;
        }

        .google-info-alert {
            font-size: 11px;
        }

        .google-info-icon {
            width: 14px;
            height: 14px;
        }
    </style>
@endsection

@section('content')
    <div class="page-wrapper">
        <!-- Page title header -->
        <div class="page-header d-print-none">
            <div class="container-fluid">
                <div class="row align-items-center">
                    <!-- Breadcrumbs (Replaces Overview pretitle) -->
                    <div class="mb-1 ms-0">
                        <ol class="breadcrumb" aria-label="breadcrumbs">
                            <li class="breadcrumb-item">
                                <a href="{{ route('admin.dashboard') }}" class="text-secondary text-decoration-none">
                                    {{ __('Dashboard') }}
                                </a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="{{ route('admin.web-templates') }}" class="text-secondary text-decoration-none">
                                    {{ __('Website Content') }}
                                </a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="{{ route('admin.blogs') }}" class="text-secondary text-decoration-none">
                                    {{ __('Blogs') }}
                                </a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">
                                <a href="#">{{ __('Create') }}</a>
                            </li>
                        </ol>
                    </div>
                    <div class="col">
                        <h2 class="page-title">
                            {{ __('Create Blog') }}
                        </h2>
                        <!-- Added helper subtitle under page heading -->
                        <div class="text-muted mt-1">
                            {{ __('Create a new blog post by adding a title, cover image, and content.') }}
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
                            <div>
                                {{ Session::get('failed') }}
                            </div>
                        </div>
                        <a class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="close"></a>
                    </div>
                @endif

                {{-- Success alert --}}
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
                    {{-- Save Blog --}}
                    <div class="col-sm-12 col-lg-12">
                        <form id="blogForm" action="{{ route('admin.publish.blog') }}" method="post"
                            enctype="multipart/form-data" class="card border-0 shadow-sm">
                            @csrf
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-xl-12">
                                        <div class="row">

                                            {{-- Blog Cover --}}
                                            <div class="col-md-6 col-xl-6">
                                                <div class="mb-3">
                                                    <!-- Label "Upload Cover Image" -->
                                                    <div class="form-label required">{{ __('Upload Cover Image') }}</div>
                                                    <input type="file" class="form-control" name="blog_cover"
                                                        id="cover" accept=".jpeg,.jpg,.png,.webp" required />
                                                </div>
                                            </div>

                                            {{-- Cover Preview --}}
                                            <div class="col-md-6 col-xl-6 mb-3">
                                                <div class="d-none" id="coverSection">
                                                    <div class="form-label">{{ __('Cover Image Preview') }}</div>
                                                    <a target="_blank" id="divCoverImg">
                                                        <img id="coverPreview" class="card-img-top cover-preview">
                                                    </a>
                                                </div>
                                            </div>

                                            {{-- Blog Name --}}
                                            <div class="col-md-6 col-xl-6">
                                                <div class="mb-3">
                                                    <!-- Label to "Blog Title" and placeholder to "Enter the blog title" -->
                                                    <label class="form-label required">{{ __('Blog Title') }}</label>
                                                    <input type="text" class="form-control" name="blog_name"
                                                        id="blog_name" maxlength="200"
                                                        placeholder="{{ __('Enter the blog title') }}" required>
                                                </div>
                                            </div>

                                            {{-- Blog Slug --}}
                                            <div class="col-md-6 col-xl-6">
                                                <div class="mb-3">
                                                    <!-- Tabler.io native help icon with tooltip and placeholder to "Eg: my-first-blog" -->
                                                    <label class="form-label required">
                                                        {{ __('Slug') }}
                                                        <span class="form-help" data-bs-toggle="tooltip"
                                                            data-bs-placement="top"
                                                            title="{{ __('Used in the blog URL') }}">?</span>
                                                    </label>
                                                    <input type="text" class="form-control" name="blog_slug"
                                                        id="blog_slug" maxlength="200"
                                                        placeholder="{{ __('Eg: my-first-blog') }}" required>
                                                </div>
                                            </div>

                                            {{-- Short description --}}
                                            <div class="col-md-12 col-xl-12">
                                                <div class="mb-3">
                                                    <!-- Placeholder to "Enter a short description." -->
                                                    <label
                                                        class="form-label required">{{ __('Short description') }}</label>
                                                    <textarea class="form-control" name="short_description" id="short_description"
                                                        placeholder="{{ __('Enter a short description.') }}" cols="30" rows="3" required></textarea>
                                                </div>
                                            </div>

                                            {{-- Long description (Blog Content) --}}
                                            <div class="col-md-12 col-xl-12">
                                                <div class="mb-3">
                                                    <!-- Label to "Blog Content" and placeholder to "Write your blog content here..." -->
                                                    <div class="form-label required">{{ __('Blog Content') }}</div>
                                                    <textarea name="long_description" id="long_description" cols="30" rows="5" class="form-control"
                                                        placeholder="{{ __('Write your blog content here...') }}"></textarea>
                                                </div>
                                            </div>

                                            {{-- Categories --}}
                                            <div class="col-md-6 col-xl-6">
                                                <div class="mb-3">
                                                    <label class="form-label required">{{ __('Categories') }}</label>
                                                    <select class="form-select"
                                                        placeholder="{{ __('Choose a category') }}" name="category_id"
                                                        id="category_id" required>
                                                        <option value="" selected disabled>
                                                            {{ __('Choose a category') }}</option>
                                                        @foreach ($blogsCategories as $blogsCategory)
                                                            <option
                                                                value="{{ $blogsCategory->blog_category_id }}"{{ $blogsCategory->blog_category_id == '66087535342' ? 'selected' : '' }}>
                                                                {{ __($blogsCategory->blog_category_title) }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>

                                            {{-- Tags --}}
                                            <div class="col-md-6 col-xl-6">
                                                <div class="mb-3">
                                                    <label class="form-label required">{{ __('Tags') }}</label>
                                                    <input type="text" class="form-control" name="tags"
                                                        id="tags" placeholder="{{ __('tag 1, tag 2') }}" required>
                                                    <!-- d-block mt-1 to guarantee proper line-breaking -->
                                                    <small
                                                        class="form-text text-muted d-block mt-1">{{ __('Press Enter to add a tag.') }}</small>
                                                </div>
                                                <div id="tagsContainer"></div>
                                            </div>

                                            <!-- SEO Settings Heading Section -->
                                            <div class="col-12 mt-4 mb-2">
                                                <h2 class="page-title mb-0">
                                                    {{ __('SEO Settings') }}
                                                </h2>
                                                <div class="text-muted small">
                                                    {{ __('Optimize your blog for search engines and improve visibility.') }}
                                                </div>
                                            </div>

                                            {{-- 2-Column Responsive Layout --}}
                                            <div class="col-md-12 col-xl-12">
                                                <div class="row g-4">
                                                    {{-- Left Column: Form Inputs with Live Character Counters --}}
                                                    <div class="col-lg-6">
                                                        {{-- Title --}}
                                                        <div class="mb-3">
                                                            <label
                                                                class="form-label required">{{ __('SEO Title') }}</label>
                                                            <textarea name="seo_title" id="seo_title" cols="30" rows="1" class="form-control"
                                                                placeholder="{{ __('Enter SEO title') }}" maxlength="60" required></textarea>
                                                            <div
                                                                class="d-flex justify-content-between small text-muted mt-1">
                                                                <span>{{ __('This title will appear in search engine results and browser tabs.') }}</span>
                                                                <span id="seo_title_counter">0 / 60</span>
                                                            </div>
                                                        </div>

                                                        {{-- Description --}}
                                                        <div class="mb-3">
                                                            <label
                                                                class="form-label required">{{ __('SEO Description') }}</label>
                                                            <textarea name="seo_description" id="seo_description" cols="30" rows="5" class="form-control"
                                                                placeholder="{{ __('Enter SEO description') }}" maxlength="160" required></textarea>
                                                            <div
                                                                class="d-flex justify-content-between small text-muted mt-1">
                                                                <span>{{ __('Summarize your blog content to help users understand what it\'s about.') }}</span>
                                                                <span id="seo_desc_counter">0 / 160</span>
                                                            </div>
                                                        </div>

                                                        {{-- Keywords --}}
                                                        <div class="mb-3">
                                                            <label
                                                                class="form-label required">{{ __('SEO Keywords') }}</label>
                                                            <input type="text" class="form-control"
                                                                name="seo_keywords" id="seo_keywords"
                                                                placeholder="{{ __('Enter keywords separated by commas') }}"
                                                                maxlength="200" required />
                                                            <div
                                                                class="d-flex justify-content-between small text-muted mt-1">
                                                                <div>
                                                                    <div>
                                                                        {{ __('Add relevant keywords to describe your blog content. Example: digital business card, networking, nfc card') }}
                                                                    </div>
                                                                </div>
                                                                <div class="ms-auto shrink-0" id="seo_keywords_counter">0
                                                                    / 200
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Actions (Positioned inline in the header column) -->
                                                    <div class="col-md-6 col-xl-6">
                                                        <div class="card border-0 shadow-sm p-3 bg-light-lt"
                                                            style="background-color: var(--tblr-bg-surface-secondary, #f6f8fb);">
                                                            <div class="subheader mb-2">{{ __('Google Search Preview') }}
                                                            </div>
                                                            <div class="text-secondary small mb-3">
                                                                {{ __('This is how your blog post may appear in Google search results.') }}
                                                            </div>

                                                            <div class="card border-0 shadow-sm mb-3">
                                                                <div class="card-body p-3">
                                                                    <div class="d-flex align-items-center mb-3">
                                                                        <img src="https://upload.wikimedia.org/wikipedia/commons/2/2f/Google_2015_logo.svg"
                                                                            alt="Google" style="height: 18px;"
                                                                            class="me-2">
                                                                        <div class="input-group google-search-bar">
                                                                            <input type="text"
                                                                                class="form-control border-0 p-0"
                                                                                value="digital business card benefits"
                                                                                readonly>
                                                                            <span
                                                                                class="input-group-text bg-transparent border-0 p-0 pe-2">
                                                                                <svg xmlns="http://www.w3.org/2000/svg"
                                                                                    class="icon text-primary"
                                                                                    width="24" height="24"
                                                                                    viewBox="0 0 24 24" stroke-width="2"
                                                                                    stroke="currentColor" fill="none"
                                                                                    stroke-linecap="round"
                                                                                    stroke-linejoin="round">
                                                                                    <path stroke="none" d="M0 0h24v24H0z"
                                                                                        fill="none" />
                                                                                    <path
                                                                                        d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0" />
                                                                                    <path d="M12 9v4" />
                                                                                    <path d="M12 16v.01" />
                                                                                </svg>
                                                                            </span>
                                                                        </div>
                                                                    </div>

                                                                    <div
                                                                        class="d-flex text-muted border-bottom pb-2 mb-3 gap-3 google-nav-tabs">
                                                                        <span
                                                                            class="text-primary border-bottom border-primary pb-2 fw-bold google-nav-tab-active">{{ __('All') }}</span>
                                                                        <span>{{ __('Images') }}</span>
                                                                        <span>{{ __('Videos') }}</span>
                                                                        <span>{{ __('News') }}</span>
                                                                        <span>{{ __('Maps') }}</span>
                                                                        <span>{{ __('More') }}</span>
                                                                    </div>

                                                                    <!-- Snippet Result card block -->
                                                                    <div
                                                                        class="border rounded-3 p-3 bg-white mb-3 google-result-snippet">
                                                                        <div
                                                                            class="d-flex align-items-center mb-1 text-truncate google-result-header">
                                                                            <span
                                                                                class="avatar avatar-xs rounded-circle bg-light me-2 google-result-favicon">G</span>
                                                                            <span id="preview_seo_url_breadcrumb"
                                                                                class="text-truncate">https://yourdomain.com/blog/your-blog-url</span>
                                                                        </div>

                                                                        <h3 id="preview_seo_title"
                                                                            class="mb-1 text-truncate google-result-title">
                                                                            {{ __('Your SEO Title Will Appear Here') }}
                                                                        </h3>

                                                                        <div id="preview_seo_description"
                                                                            class="google-result-description">
                                                                            {{ __('Your SEO description will appear here. It should be short, clear, and include the main point of your blog post to attract more clicks.') }}
                                                                        </div>

                                                                        <div class="small mt-2 text-truncate google-result-crumbs"
                                                                            id="preview_seo_domain_crumbs">
                                                                            {{ __('yourdomain.com › blog › your-blog-url') }}
                                                                        </div>
                                                                    </div>

                                                                    <!-- Bottom warning alert notice -->
                                                                    <div
                                                                        class="d-flex align-items-start p-2 bg-blue-lt rounded-3 border-0 google-info-alert">
                                                                        <span class="me-2 text-blue">
                                                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                                                class="icon google-info-icon"
                                                                                width="24" height="24"
                                                                                viewBox="0 0 24 24" stroke-width="2"
                                                                                stroke="currentColor" fill="none"
                                                                                stroke-linecap="round"
                                                                                stroke-linejoin="round">
                                                                                <path stroke="none" d="M0 0h24v24H0z"
                                                                                    fill="none" />
                                                                                <path d="M12 9v2m0 4v.01" />
                                                                                <path
                                                                                    d="M5 19h14a2 2 0 0 0 1.84 -2.75l-7.1 -12.25a2 2 0 0 0 -3.5 0l-7.1 12.25a2 2 0 0 0 1.75 2.75" />
                                                                            </svg>
                                                                        </span>
                                                                        <div class="text-blue-dark">
                                                                            {{ __('The preview shows an example of how your page may appear. Actual results may vary.') }}
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer text-end">
                                <div class="d-flex">
                                    <a href="{{ route('admin.blogs') }}"
                                        class="btn btn-outline-primary btn-md d-inline-flex align-items-center">{{ __('Cancel') }}</a>
                                    <button type="submit"
                                        class="btn btn-primary btn-md ms-auto d-inline-flex align-items-center">
                                        <!-- Replaced standard Publish button markup with custom Dual Layout & Floppy Disk icon -->
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
                                        {{ __('Publish') }}
                                    </button>
                                </div>
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
    <script src="{{ asset('js/lightgallery.min.js') }}"></script>
    <!-- Bootstrap Tags Input JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-tagsinput/0.8.0/bootstrap-tagsinput.min.js"></script>
    <script>
        // Preview Cover
        $(document).ready(() => {
            "use strict";

            const coverInp = $("#cover");
            let imgURL;

            coverInp.change(function(e) {
                // Remove d-none class from cover section
                $('#coverSection').removeClass('d-none');

                // Get image URL
                imgURL = URL.createObjectURL(e.target.files[0]);

                var coverImg = document.getElementById("coverPreview");
                coverImg.getAttribute("src");
                coverImg.setAttribute("src", imgURL);

                // New blank
                var divCoverImg = document.getElementById("divCoverImg");
                divCoverImg.getAttribute("href");
                divCoverImg.setAttribute("href", imgURL);
            });
        });

        // Convert slug dynamically
        $('#blog_name').on("change keyup paste click", function() {
            "use strict";
            var Text = $(this).val();
            Text = Text.toLowerCase();
            Text = Text.replace(/[^a-zA-Z0-9]+/g, '-');

            // Set slug input value
            $('#blog_slug').val(Text);
        });

        // Dark Mode
        const isDark = document.body.getAttribute('data-bs-theme') === 'dark';

        // HTML Editor
        tinymce.init({
            selector: '#long_description',
            plugins: 'code preview importcss searchreplace autolink autosave save directionality visualblocks visualchars link charmap pagebreak nonbreaking anchor insertdatetime advlist lists wordcount charmap quickbars emoticons',
            menubar: 'file edit view insert format tools',
            toolbar: 'code undo redo | bold italic underline strikethrough | fontfamily fontsize blocks | alignleft aligncenter alignright alignjustify | outdent indent |  numlist bullist | forecolor backcolor removeformat | pagebreak | charmap emoticons | preview save print | insertfile link anchor | ltr rtl',
            content_style: 'body { font-family:Times New Roman,Arial,sans-serif; font-size:16px }',
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
            ` : `body {
                    font-family: "Figtree", sans-serif !important;
                }

                .mce-content-body[data-mce-placeholder]:not(.mce-visualblocks)::before {
                    opacity: 1 !important;
                    font-family: "Figtree", sans-serif !important;
                    font-size: 0.85rem !important;
                    font-weight: 600 !important;
                }
            `,
            // Correct mobile settings (limited fields allowed)
            mobile: {
                toolbar: 'code undo redo | bold italic underline strikethrough | fontfamily fontsize blocks | alignleft aligncenter alignright alignjustify | outdent indent | numlist bullist | forecolor backcolor removeformat | pagebreak | charmap emoticons | preview save print | insertfile link anchor | ltr rtl'
            }
        });

        // Tags
        $(document).ready(function() {
            "use strict";

            $('#tags').tagsinput({
                confirmKeys: [13, 188] // Allows pressing Enter or comma to add a tag
            });

            $(".bootstrap-tagsinput input").addClass('form-control d-inline');
            $(".bootstrap-tagsinput").addClass('col-md-12 col-xl-12');

            // Initialize Bootstrap Tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });

            // Dynamic Live Google Search Preview & Counter Sync logic
            const currentDomain = window.location.origin.replace(/(^\w+:|^)\/\//,
                ''); // Strips protocol for breadcrumb
            const previewUrlBreadcrumb = $('#preview_seo_url_breadcrumb');
            const previewDomainCrumbs = $('#preview_seo_domain_crumbs');

            function updateSeoBreadcrumbs() {
                const slug = $('#blog_slug').val().trim() || 'your-blog-url';
                if (previewUrlBreadcrumb.length) {
                    previewUrlBreadcrumb.text(`https://${currentDomain}/blog/${slug}`);
                }
                if (previewDomainCrumbs.length) {
                    previewDomainCrumbs.text(`${currentDomain} › blog › ${slug}`);
                }
            }

            // Sync dynamic Slug input updates
            $('#blog_slug').on('input change keyup paste', function() {
                updateSeoBreadcrumbs();
            });
            updateSeoBreadcrumbs(); // Run initial breadcrumb calculation

            // Sync SEO Title input, update counter, update Google Snippet preview title
            $('#seo_title').on('input propertychange', function() {
                const len = this.value.length;
                $('#seo_title_counter').text(`${len} / 60`);

                const previewTitle = $('#preview_seo_title');
                if (previewTitle.length) {
                    previewTitle.text(this.value.trim() || "{{ __('Your SEO Title Will Appear Here') }}");
                }
            });

            // Sync SEO Description input, update counter, update Google Snippet preview description
            $('#seo_description').on('input propertychange', function() {
                const len = this.value.length;
                $('#seo_desc_counter').text(`${len} / 160`);

                const previewDesc = $('#preview_seo_description');
                if (previewDesc.length) {
                    previewDesc.text(this.value.trim() ||
                        "Your SEO description will appear here. It should be short, clear, and include the main point of your blog post to attract more clicks."
                    );
                }
            });

            // Sync SEO Keywords input and update counter
            $('#seo_keywords').on('input propertychange', function() {
                const len = this.value.length;
                $('#seo_keywords_counter').text(`${len} / 200`);
            });
        });

        // Handle Form Validation for TinyMCE
        $('#blogForm').on('submit', function(e) {
            "use strict";

            // Save TinyMCE editor state back to original textarea
            tinymce.triggerSave();

            // Fetch the content as plain text (ignores raw HTML tags like `<p><br></p>`)
            var editorContent = tinymce.get('long_description').getContent({
                format: 'text'
            }).trim();

            if (editorContent.length === 0) {
                e.preventDefault(); // Stop form submission

                // Remove any existing dynamic alerts
                $('.dynamic-alert').remove();

                // Build Tabler Alert HTML
                var errorAlert = `
                    <div class="alert alert-important alert-danger alert-dismissible mb-2" role="alert">
                        <div class="d-flex">
                            <div>
                                ${"{{ __('Blog Content is required.') }}"}
                            </div>
                        </div>
                        <a class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="close"></a>
                    </div>
                `;

                // Prepend alert inside the body container
                $('.page-body .container-fluid').first().prepend(errorAlert);

                // Scroll smoothly to top of the page to view the alert
                $('html, body').animate({
                    scrollTop: $('.page-wrapper').offset().top
                }, 150);
            }
        });
    </script>
@endsection
