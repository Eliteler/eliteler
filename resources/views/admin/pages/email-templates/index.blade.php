@extends('admin.layouts.index', ['header' => true, 'nav' => true, 'demo' => true])

{{-- Custom CSS --}}
@section('css')
    {{-- Email Template CSS --}}
    <link rel="stylesheet" href="{{ asset('css/email-template.css') }}">
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
                                <a href="{{ route('admin.settings') }}" class="text-secondary text-decoration-none">
                                    {{ __('System Utilities') }}
                                </a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">
                                <a href="#">{{ __('Email Templates') }}</a>
                            </li>
                        </ol>
                    </div>
                    <div class="col">
                        <h2 class="page-title">
                            {{ __('Email Templates') }}
                        </h2>
                        <!-- Added helper subtitle under page heading -->
                        <div class="text-muted mt-1">
                            {{ __('Manage email templates used for system notifications.') }}
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

                @php
                    $shortcodes = [
                        'Customer' => [
                            ':customername',
                            ':customeremail',
                            ':receivername',
                            ':receiveremail',
                            ':receiverphone',
                            ':receivermessage',
                            ':registeredname',
                            ':registeredemail',
                        ],
                        'Appointments' => [
                            ':status',
                            ':appointmentdate',
                            ':appointmenttime',
                            ':appointmentpageurl',
                            ':googlecalendarurl',
                            ':checkindate',
                            ':checkintime',
                            ':checkoutdate',
                            ':checkouttime',
                            ':servicebookingpageurl',
                        ],
                        'Orders' => [
                            ':orderid',
                            ':cardname',
                            ':cardprice',
                            ':paymentstatus',
                            ':deliverystatus',
                            ':quantity',
                            ':trackingnumber',
                            ':courierpartner',
                            ':orderpageurl',
                            ':totalprice',
                        ],
                        'Plans' => [':planname', ':plancode', ':planprice', ':expirydate'],
                        'System' => [
                            ':hyperlink',
                            ':vcardname',
                            ':appname',
                            ':previousdomain',
                            ':currentdomain',
                            ':otpnumber',
                            ':supportemail',
                            ':supportphone',
                            ':actionlink',
                        ],
                    ];
                @endphp

                <div class="workspace-card">
                    <div class="row g-4">
                        {{-- SIDEBAR --}}
                        <div class="col-xl-3">
                            <div class="templates-sidebar card border-0 shadow-sm p-4">
                                <h4 class="subheader mb-4">
                                    {{ __('Templates') }}
                                </h4>
                                <div class="list-group list-group-transparent">
                                    @include('admin.pages.email-templates.includes.navlinks', [
                                        'link' => $email_templates->email_template_id,
                                    ])
                                </div>
                            </div>
                        </div>

                        {{-- MAIN WORKSPACE --}}
                        <div class="col-xl-9">
                            <form action="{{ route('admin.update.email.template.content') }}" method="POST">
                                @csrf
                                <input type="hidden" name="email_template_id"
                                    value="{{ $email_templates->email_template_id }}">

                                {{-- CONFIGURATION SETTINGS CARD --}}
                                <div class="card border-0 shadow-sm mb-4">
                                    <div class="card-header d-block">
                                        <h3 class="card-title mb-1">{{ __('Template Settings') }}</h3>
                                        <p class="text-muted small mb-0">{{ __('Manage email configuration') }}</p>
                                    </div>

                                    <div class="card-body p-4">
                                        <div class="row align-items-end g-3">
                                            {{-- Email Subject --}}
                                            <div class="col-xl-6">
                                                <label class="form-label required"
                                                    for="email_template_subject">{{ __('Email Template Subject') }}</label>
                                                <input type="text" class="form-control" id="email_template_subject"
                                                    name="email_template_subject"
                                                    value="{{ $email_templates->email_template_subject }}" required>
                                            </div>

                                            {{-- Enabled Switch --}}
                                            <div class="col-xl-3">
                                                <label class="form-label"
                                                    for="is_enabled">{{ __('Send Email Notifications to the customer / admin?') }}</label>
                                                <label class="form-check form-switch mt-2">
                                                    <input class="form-check-input" type="checkbox" name="is_enabled"
                                                        id="is_enabled"
                                                        {{ $email_templates->is_enabled ? 'checked' : '' }}>
                                                </label>
                                            </div>

                                            {{-- Save Action trigger --}}
                                            <div class="col-xl-3">
                                                <button type="submit"
                                                    class="btn btn-primary w-100 d-inline-flex align-items-center justify-content-center">
                                                    <svg xmlns="http://www.w3.org/2000/svg"
                                                        class="icon icon-tabler icon-tabler-device-floppy me-2"
                                                        width="24" height="24" viewBox="0 0 24 24" stroke-width="2"
                                                        stroke="currentColor" fill="none" stroke-linecap="round"
                                                        stroke-linejoin="round">
                                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                        <path
                                                            d="M6 4h10l4 4v10a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2" />
                                                        <path d="M12 14m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" />
                                                        <path d="M14 4l0 4h-6l0 -4" />
                                                    </svg>
                                                    {{ __('Save') }}
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- VISUAL BUILDER CARD --}}
                                <div class="card border-0 shadow-sm editor-card">
                                    <div class="card-header d-flex align-items-center justify-content-between">
                                        <div>
                                            <h3 class="card-title mb-1">{{ __('Email Builder') }}</h3>
                                            <p class="text-muted small mb-0">{{ __('Design modern email templates') }}</p>
                                        </div>
                                        <div class="d-flex align-items-center gap-2">
                                            {{-- DESKTOP --}}
                                            <button type="button" class="btn btn-light btn-icon preview-mode active"
                                                data-mode="desktop">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                    viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                    class="icon icon-tabler icons-tabler-outline icon-tabler-device-desktop">
                                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                    <path
                                                        d="M3 5a1 1 0 0 1 1 -1h16a1 1 0 0 1 1 1v10a1 1 0 0 1 -1 1h-16a1 1 0 0 1 -1 -1v-10" />
                                                    <path d="M7 20h10" />
                                                    <path d="M9 16v4" />
                                                    <path d="M15 16v4" />
                                                </svg>
                                            </button>

                                            {{-- MOBILE --}}
                                            <button type="button" class="btn btn-light btn-icon preview-mode"
                                                data-mode="mobile">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                    viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                    class="icon icon-tabler icons-tabler-outline icon-tabler-device-mobile">
                                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                    <path
                                                        d="M6 5a2 2 0 0 1 2 -2h8a2 2 0 0 1 2 2v14a2 2 0 0 1 -2 2h-8a2 2 0 0 1 -2 -2v-14" />
                                                    <path d="M11 4h2" />
                                                    <path d="M12 17v.01" />
                                                </svg>
                                            </button>

                                            {{-- PREVIEW --}}
                                            <button type="button" class="btn btn-primary btn-icon preview-modal-btn"
                                                data-bs-toggle="modal" data-bs-target="#emailPreviewModal">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                    viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                    class="icon icon-tabler icons-tabler-outline icon-tabler-scan-eye">
                                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                    <path d="M7 12q 5 -7 10 0" />
                                                    <path d="M7 12q 5 7 10 0" />
                                                    <path d="M12 12h-.01" />
                                                    <path d="M3 7v-2a2 2 0 0 1 2 -2h2" />
                                                    <path d="M3 17v2a2 2 0 0 0 2 2h2" />
                                                    <path d="M17 3h2a2 2 0 0 1 2 2v2" />
                                                    <path d="M17 21h2a2 2 0 0 0 2 -2v-2" />
                                                </svg>
                                            </button>
                                        </div>
                                    </div>

                                    <div class="card-body p-4">

                                        {{-- SHORTCODE TOOLBAR --}}
                                        <div class="shortcode-toolbar">
                                            <div class="d-flex align-items-center justify-content-between flex-wrap mb-3">
                                                <div>
                                                    <h4 class="mb-1">{{ __('Quick Shortcodes') }}</h4>
                                                    <div class="text-secondary">{{ __('Click shortcode to insert') }}
                                                    </div>
                                                </div>

                                                <div class="shortcode-search">
                                                    <input type="text" id="shortcodeSearch" class="form-control"
                                                        placeholder="{{ __('Search shortcode...') }}">
                                                </div>
                                            </div>

                                            <div class="shortcode-chips">
                                                @foreach ($shortcodes as $category => $codes)
                                                    @foreach ($codes as $code)
                                                        <div class="shortcode-chip" data-code="{{ $code }}">
                                                            {{ $code }}
                                                        </div>
                                                    @endforeach
                                                @endforeach
                                            </div>
                                        </div>

                                        {{-- Email Content TextArea Editor --}}
                                        <div class="mt-4">
                                            <label class="form-label required mb-2"
                                                for="email_template_content">{{ __('Email Template Content') }}</label>
                                            <textarea id="email_template_content" name="email_template_content">{{ $email_templates->email_template_content }}</textarea>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- FOOTER --}}
        @include('admin.includes.footer')
    </div>


    <!-- =====================================
                                            PREVIEW MODAL
                                        ===================================== -->
    <div class="modal fade custom-modal" id="emailPreviewModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog custom-modal-dialog modal-dialog-centered">
            <div class="modal-content custom-modal-content">

                <!-- HEADER -->
                <div class="modal-header custom-modal-header">
                    <div>
                        <h3 class="custom-modal-title">
                            {{ __('Email Preview') }}
                        </h3>
                        <div class="custom-modal-subtitle">
                            {{ __('Real-time Safari preview') }}
                        </div>
                    </div>

                    <button type="button" class="btn-close custom-modal-close" data-bs-dismiss="modal"
                        aria-label="{{ __('Close') }}"></button>
                </div>

                <!-- BODY -->
                <div class="modal-body custom-modal-body">
                    <div class="safari-browser">

                        <!-- TOOLBAR -->
                        <div class="safari-toolbar">

                            <!-- LEFT -->
                            <div class="safari-actions">
                                <span class="safari-dot red"></span>
                                <span class="safari-dot yellow"></span>
                                <span class="safari-dot green"></span>
                            </div>

                            <!-- ADDRESS BAR -->
                            <div class="safari-address-bar">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round"
                                    class="icon icon-tabler icons-tabler-outline icon-tabler-lock-access safari-lock">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                    <path d="M4 8v-2a2 2 0 0 1 2 -2h2" />
                                    <path d="M4 16v2a2 2 0 0 0 2 2h2" />
                                    <path d="M16 4h2a2 2 0 0 1 2 2v2" />
                                    <path d="M16 20h2a2 2 0 0 0 2 -2v-2" />
                                    <path
                                        d="M8 12a1 1 0 0 1 1 -1h6a1 1 0 0 1 1 1v3a1 1 0 0 1 -1 1h-6a1 1 0 0 1 -1 -1l0 -3" />
                                    <path d="M10 11v-2a2 2 0 1 1 4 0v2" />
                                </svg>
                                <span>
                                    {{ request()->getHost() }}
                                </span>
                            </div>

                            <!-- RIGHT -->
                            <div class="safari-toolbar-actions">
                                {{-- Refresh --}}
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round"
                                    class="icon icon-tabler icons-tabler-outline icon-tabler-refresh">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                    <path d="M20 11a8.1 8.1 0 0 0 -15.5 -2m-.5 -4v4h4" />
                                    <path d="M4 13a8.1 8.1 0 0 0 15.5 2m.5 4v-4h-4" />
                                </svg>
                                {{-- Share --}}
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round"
                                    class="icon icon-tabler icons-tabler-outline icon-tabler-share-3">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                    <path
                                        d="M13 4v4c-6.575 1.028 -9.02 6.788 -10 12c-.037 .206 5.384 -5.962 10 -6v4l8 -7l-8 -7" />
                                </svg>
                            </div>
                        </div>

                        <!-- CONTENT -->
                        <div class="safari-content">
                            <div class="preview-subject-label">
                                {{ __('Subject') }}
                            </div>

                            <div class="preview-subject" id="preview-subject-text">
                                {{ $email_templates->email_template_subject }}
                            </div>

                            <div id="preview-area" class="email-preview-content">
                                {!! $email_templates->email_template_content !!}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- FOOTER -->
                <div class="modal-footer custom-modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">
                        {{ __('Close') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    @php
        $emailCss = asset('css/email.css');
        $emailCss1 = asset('css/email1.css');
    @endphp
@endsection

@section('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/7.0.1/tinymce.min.js"></script>

    <script>
        "use strict";

        document.addEventListener('DOMContentLoaded', function() {

            const previewArea = document.getElementById('preview-area');
            const previewAreaModal = document.getElementById('preview-area-modal');
            const previewSubject = document.getElementById('preview-subject-text');
            const previewSubjectModal = document.getElementById('preview-subject-text-modal');
            const subjectInput = document.getElementById('email_template_subject');
            const previewWrapper = document.getElementById('previewWrapper');

            // =========================
            // SUBJECT LIVE UPDATE
            // =========================
            subjectInput.addEventListener('keyup', function() {
                previewSubject.innerText = this.value;
                if (previewSubjectModal) {
                    previewSubjectModal.innerText = this.value;
                }
            });

            // =========================
            // PREVIEW MODE SWITCH
            // =========================
            document.querySelectorAll('.preview-mode')
                .forEach(button => {
                    button.addEventListener('click', function() {
                        document.querySelectorAll('.preview-mode')
                            .forEach(btn => {
                                btn.classList.remove('active');
                            });

                        this.classList.add('active');
                        const mode = this.dataset.mode;

                        if (mode === 'mobile') {
                            previewWrapper.classList.remove(
                                'desktop-preview'
                            );

                            previewWrapper.classList.add(
                                'mobile-preview'
                            );
                        } else {
                            previewWrapper.classList.remove(
                                'mobile-preview'
                            );
                            previewWrapper.classList.add(
                                'desktop-preview'
                            );
                        }
                    });
                });

            // =========================
            // UPDATE PREVIEW
            // =========================
            function updatePreview() {
                if (!tinymce.get('email_template_content')) {
                    return;
                }

                const content = tinymce.get('email_template_content').getContent();

                // MAIN PREVIEW
                if (previewArea) {
                    previewArea.innerHTML = content;
                }

                // MODAL PREVIEW
                if (previewAreaModal) {
                    previewAreaModal.innerHTML = content;
                }
            }

            // =========================
            // SHORTCODE INSERT
            // =========================
            document.querySelectorAll('.shortcode-chip')
                .forEach(item => {
                    item.addEventListener('click', function() {
                        const shortcode = this.dataset.code;
                        tinymce.activeEditor.execCommand(
                            'mceInsertContent',
                            false,
                            shortcode
                        );

                        // Update preview
                        updatePreview();
                    });
                });

            // =========================
            // SHORTCODE SEARCH
            // =========================
            const shortcodeSearch = document.getElementById('shortcodeSearch');

            if (shortcodeSearch) {
                shortcodeSearch.addEventListener(
                    'keyup',
                    function() {
                        const value = this.value.toLowerCase();

                        document.querySelectorAll('.shortcode-chip').forEach(item => {
                            item.style.display =
                                item.innerText
                                .toLowerCase()
                                .includes(value) ?
                                'inline-flex' :
                                'none';
                        });
                    }
                );
            }

            // =========================
            // MODAL PREVIEW UPDATE
            // =========================
            const previewModal = document.getElementById('emailPreviewModal');

            if (previewModal) {
                previewModal.addEventListener(
                    'show.bs.modal',
                    function() {
                        // Update preview
                        updatePreview();

                        if (previewSubjectModal) {
                            previewSubjectModal.innerText = subjectInput.value;
                        }
                    }
                );
            }

            // Dark Mode
            const isDark = document.body.getAttribute('data-bs-theme') === 'dark';

            // =========================
            // TINYMCE INIT
            // =========================
            tinymce.init({
                selector: '#email_template_content',
                height: 700,
                menubar: false,
                statusbar: false,
                branding: false,
                plugins: [
                    'code',
                    'preview',
                    'importcss',
                    'searchreplace',
                    'autolink',
                    'autosave',
                    'save',
                    'directionality',
                    'visualblocks',
                    'visualchars',
                    'link',
                    'charmap',
                    'pagebreak',
                    'nonbreaking',
                    'anchor',
                    'insertdatetime',
                    'advlist',
                    'lists',
                    'wordcount',
                    'quickbars',
                    'emoticons'
                ],
                toolbar: 'undo redo | ' +
                    'blocks fontfamily fontsize | ' +
                    'bold italic underline strikethrough | ' +
                    'forecolor backcolor | ' +
                    'alignleft aligncenter alignright alignjustify | ' +
                    'bullist numlist | ' +
                    'link emoticons charmap | ' +
                    'code preview',
                skin: isDark ? 'oxide-dark' : 'oxide',
                content_css: isDark ? [
                    'https://fonts.googleapis.com/css2?family=Figtree:wght@300;400;500;600;700;800&display=swap',
                    'dark',
                    '{{ $emailCss }}',
                    '{{ $emailCss1 }}'
                ] : [
                    'https://fonts.googleapis.com/css2?family=Figtree:wght@300;400;500;600;700;800&display=swap',
                    'default',
                    '{{ $emailCss }}',
                    '{{ $emailCss1 }}'
                ],
                setup: function(editor) {
                    editor.on('init', function() {
                        if (document.body.dataset.bsTheme === 'dark') {
                            editor.getBody().setAttribute('data-bs-theme', 'dark');
                        }
                    });
                    editor.on(
                        'keyup change input',
                        function() {
                            // Update preview
                            updatePreview();
                        }
                    );
                    editor.on(
                        'init',
                        function() {
                            updatePreview();
                        }
                    );
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
            });
        });
    </script>
@endsection
