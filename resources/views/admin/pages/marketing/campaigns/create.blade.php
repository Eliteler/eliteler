@extends('admin.layouts.index', ['header' => true, 'nav' => true, 'demo' => true])

{{-- Custom CSS --}}
@section('css')
    <style>
        .ts-control>input {
            display: contents !important;
        }
    </style>
@endsection

@section('content')
    <div class="page-wrapper">
        <!-- Page title header -->
        <div class="page-header d-print-none">
            <div class="container-fluid">
                <div class="row g-2 align-items-center">
                    <!-- Breadcrumbs (Replaces Overview pretitle) -->
                    <div class="mb-1 ms-0">
                        <ol class="breadcrumb" aria-label="breadcrumbs">
                            <li class="breadcrumb-item">
                                <a href="{{ route('admin.dashboard') }}" class="text-secondary text-decoration-none">
                                    {{ __('Dashboard') }}
                                </a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="{{ route('admin.marketing.campaigns') }}" class="text-secondary text-decoration-none">
                                    {{ __('Email Marketing') }}
                                </a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="{{ route('admin.marketing.campaigns') }}"
                                    class="text-secondary text-decoration-none">
                                    {{ __('Campaigns') }}
                                </a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">
                                <a href="#">{{ __('Create') }}</a>
                            </li>
                        </ol>
                    </div>
                    <div class="col">
                        <h2 class="page-title">
                            {{ __('Create Campaign') }}
                        </h2>
                        <!-- Updated helper text under heading -->
                        <div class="text-muted mt-1">
                            {{ __('Create an email campaign and send it to selected customer') }}
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
                    <div class="col-sm-12 col-lg-12">
                        <!-- Added id="campaignForm" for validation sync -->
                        <form id="campaignForm" action="{{ route('admin.marketing.campaigns.save') }}" method="post"
                            class="card border-0 shadow-sm">
                            @csrf
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-xl-12">
                                        <div class="row">
                                            {{-- Campaign Name --}}
                                            <div class="col-md-6 col-xl-4">
                                                <div class="mb-3">
                                                    <div class="form-label required">{{ __('Campaign Name') }}</div>
                                                    <!-- Updated placeholder -->
                                                    <input type="text" class="form-control" name="campaign_name"
                                                        value="{{ old('campaign_name') }}"
                                                        placeholder="{{ __('Enter the campaign name') }}" required>
                                                </div>
                                            </div>

                                            {{-- Campaign Description --}}
                                            <div class="col-md-6 col-xl-4">
                                                <div class="mb-3">
                                                    <div class="form-label required">{{ __('Description') }}</div>
                                                    <!-- Updated placeholder -->
                                                    <input type="text" class="form-control" name="campaign_description"
                                                        value="{{ old('campaign_description') }}"
                                                        placeholder="{{ __('Enter a short description') }}" required>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card-header">
                                <h4 class="page-title">{{ __('Email Notification Details') }}</h4>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-xl-12">
                                        <div class="row">
                                            {{-- Available Groups --}}
                                            @if (count($groups) > 0)
                                                {{-- Campaign Groups --}}
                                                <div class="col-md-12 col-xl-6">
                                                    <div class="mb-3">
                                                        <div class="form-label required">{{ __('Groups') }}</div>
                                                        <select class="form-control" id="group" name="group">
                                                            @foreach ($groups as $group)
                                                                <option value="{{ $group->group_id }}">
                                                                    {{ $group->group_name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                            @else
                                                <div class="col-md-12 col-xl-6">
                                                    <div class="mb-3">
                                                        {{-- Heading --}}
                                                        <div class="form-label required">{{ __('Recipient Groups') }}</div>
                                                        <!-- Updated empty text state -->
                                                        <div class="form-text fw-bold h4">
                                                            {{ __('No recipient groups available.') }}</div>
                                                    </div>
                                                </div>
                                            @endif

                                            {{-- Email Subject --}}
                                            <div class="col-md-12 col-xl-6">
                                                <div class="mb-3">
                                                    <div class="form-label required">{{ __('Email Subject') }}</div>
                                                    <!-- Updated placeholder -->
                                                    <input type="text" class="form-control" name="email_subject"
                                                        value="{{ old('email_subject') }}"
                                                        placeholder="{{ __('Enter the email subject') }}" required>
                                                </div>
                                            </div>

                                            {{-- Email Content --}}
                                            <div class="col-md-12 col-xl-12">
                                                <div class="mb-3">
                                                    <!-- Updated label to "Email Content" -->
                                                    <div class="form-label required">{{ __('Email Content') }}</div>
                                                    <!-- Added placeholder attribute to textarea -->
                                                    <textarea class="form-control" name="email_body" id="email_body" rows="10"
                                                        placeholder="{{ __('Write your email message here...') }}">{{ old('email_body') }}</textarea>
                                                </div>
                                                <!-- Updated helper subtext to insert the recipient's name -->
                                                <span
                                                    class="fw-bold">{{ __('Use #name to insert the recipient\'s name.') }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer text-end">
                                <!-- Updated submit button text to "Send Campaign" -->
                                <button type="submit"
                                    class="btn btn-primary btn-md d-inline-flex align-items-center {{ count($groups) > 0 ? '' : 'disabled' }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-send me-2"
                                        width="24" height="24" viewBox="0 0 24 24" stroke-width="2"
                                        stroke="currentColor" fill="none" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                        <path d="M10 14l11 -11" />
                                        <path
                                            d="M21 3l-6.5 18a.55 .55 0 0 1 -1 0l-3.5 -7l-7 -3.5a.55 .55 0 0 1 0 -1l18 -6.5" />
                                    </svg>
                                    {{ __('Send Campaign') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        @include('admin.includes.footer')
    </div>
@endsection

{{-- Custom JS --}}
@section('scripts')
    {{-- TinyMCE CDN --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/7.0.1/tinymce.min.js"
        integrity="sha512-KGtsnWohFUg0oksKq7p7eDgA1Rw2nBfqhGJn463/rGhtUY825dBqGexj8eP04LwfnsSW6dNAHAlOqKJKquHsnw=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    {{-- Tom Select --}}
    <script type="text/javascript" src="{{ asset('js/tom-select.base.min.js') }}"></script>
    <script>
        // Array of element IDs
        var elementSelectors = ['group'];

        // Function to initialize TomSelect and enforce the "required" attribute
        function initializeTomSelectWithRequired(el) {
            new TomSelect(el, {
                copyClassesToDropdown: false,
                dropdownClass: 'dropdown-menu ts-dropdown',
                optionClass: 'dropdown-item',
                controlInput: '<input>',
                maxOptions: null,
                render: {
                    item: function(data, escape) {
                        if (data.customProperties) {
                            return '<div><span class="dropdown-item-indicator">' + data.customProperties +
                                '</span>' + escape(data.text) + '</div>';
                        }
                        return '<div>' + escape(data.text) + '</div>';
                    },
                    option: function(data, escape) {
                        if (data.customProperties) {
                            return '<div><span class="dropdown-item-indicator">' + data.customProperties +
                                '</span>' + escape(data.text) + '</div>';
                        }
                        return '<div>' + escape(data.text) + '</div>';
                    },
                },
            });

            // Ensure the "required" attribute is enforced
            el.addEventListener('change', function() {
                if (el.value) {
                    el.setCustomValidity('');
                } else {
                    el.setCustomValidity('This field is required');
                }
            });

            // Trigger validation on load
            el.dispatchEvent(new Event('change'));
        }

        // Loop through each element ID
        elementSelectors.forEach(function(id) {
            // Check if the element exists
            var el = document.getElementById(id);
            if (el) {
                // Apply TomSelect and enforce the "required" attribute
                initializeTomSelectWithRequired(el);
            }
        });
    </script>
    {{-- TinyMCE --}}
    <script>
        // Dark Mode
        const isDark = document.body.getAttribute('data-bs-theme') === 'dark';

        tinymce.init({
            selector: 'textarea#email_body',
            placeholder: 'Write your email message here...', // Sync placeholder inside editor area
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

                .mce-content-body[data-mce-placeholder]:not(.mce-visualblocks):::before {
                    color: rgba(34,47,62,.5) !important;
                    opacity: 1 !important;
                    font-family: "Figtree", sans-serif !important;
                    font-size: 0.85rem !important;
                    font-weight: 600 !important;
                }
            ` : `body {
                    font-family: "Figtree", sans-serif !important;
                }

                .mce-content-body[data-mce-placeholder]:not(.mce-visualblocks):::before {
                    opacity: 1 !important;
                    font-family: "Figtree", sans-serif !important;
                    font-size: 0.85rem !important;
                    font-weight: 600 !important;
                }
            `,
            // Correct mobile settings (limited fields allowed)
            mobile: {
                plugins: 'paste',
                toolbar: 'code undo redo | bold italic underline strikethrough | fontfamily fontsize blocks | alignleft aligncenter alignright alignjustify | outdent indent | numlist bullist | forecolor backcolor removeformat | pagebreak | charmap emoticons | preview save print | insertfile link anchor | ltr rtl'
            }
        });

        // Handle Form Validation for TinyMCE Email Content
        document.getElementById('campaignForm').addEventListener('submit', function(e) {
            "use strict";

            // Save TinyMCE editor state back to original textarea
            tinymce.triggerSave();

            // Fetch the content as plain text (ignores raw HTML tags)
            var editorContent = tinymce.get('email_body').getContent({
                format: 'text'
            }).trim();

            if (editorContent.length === 0) {
                e.preventDefault(); // Stop form submission

                // Remove any existing dynamic alerts
                const dynamicAlerts = document.querySelectorAll('.dynamic-alert');
                dynamicAlerts.forEach(alert => alert.remove());

                // Build Tabler Alert HTML
                var errorAlert = document.createElement('div');
                errorAlert.className = "alert alert-important alert-danger alert-dismissible mb-2 dynamic-alert";
                errorAlert.setAttribute('role', 'alert');
                errorAlert.innerHTML = `
            <div class="d-flex">
                <div>
                    ${"{{ __('Email Content is required.') }}"}
                </div>
            </div>
            <a class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="close"></a>
        `;

                // Prepend alert inside the body container
                const pageBodyContainer = document.querySelector('.page-body .container-fluid');
                if (pageBodyContainer) {
                    pageBodyContainer.insertBefore(errorAlert, pageBodyContainer.firstChild);
                }

                // Scroll smoothly to top of the page to view the alert
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            }
        });
    </script>
@endsection
