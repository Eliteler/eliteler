@extends('admin.layouts.index', ['header' => true, 'nav' => true, 'demo' => true])

{{-- Custom CSS --}}
@section('css')
    <script type="text/javascript" src="{{ asset('js/tom-select.base.min.js') }}"></script>
    <style>
        .category-card {
            cursor: pointer;
            border: 2px solid transparent;
            transition: all 0.2s ease;
            user-select: none;
        }

        .category-card:hover {
            border-color: var(--tblr-primary);
            background-color: var(--tblr-primary-lt);
        }

        .category-card.selected {
            border-color: var(--tblr-primary);
            background-color: var(--tblr-primary-lt);
        }

        .category-card .category-icon {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .category-card .form-check-input {
            pointer-events: none;
        }

        @keyframes pulse-icon {

            0%,
            100% {
                transform: scale(1);
                opacity: 1;
            }

            50% {
                transform: scale(1.12);
                opacity: 0.75;
            }
        }

        .icon-pulse {
            animation: pulse-icon 1.6s ease-in-out infinite;
        }

        .sitemap-info-box {
            background: linear-gradient(135deg, var(--tblr-primary-lt) 0%, rgba(var(--tblr-primary-rgb), 0.05) 100%);
            border-radius: 8px;
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
                                <a href="{{ route('admin.settings') }}" class="text-secondary text-decoration-none">
                                    {{ __('System Utilities') }}
                                </a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">
                                <a href="#">{{ __('Generate Sitemap') }}</a>
                            </li>
                        </ol>
                    </div>
                    <div class="col">
                        <h2 class="page-title">
                            {{ __('Generate Sitemap') }}
                        </h2>
                        <!-- Added helper subtitle under page heading -->
                        <div class="text-muted mt-1">
                            {{ __('Configure and build search engine friendly XML maps to optimize platform indexing speed.') }}
                        </div>
                    </div>

                    {{-- View current sitemap --}}
                    <div class="col-auto ms-auto">
                        <a href="{{ url('sitemap.xml') }}" target="_blank"
                            class="btn btn-dark d-inline-flex align-items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon me-1" width="16" height="16"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                <path d="M12 6h-6a2 2 0 0 0 -2 2v10a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-6" />
                                <path d="M11 13l9 -9" />
                                <path d="M15 4h5v5" />
                            </svg>
                            {{ __('View Sitemap') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Page Body --}}
        <div class="page-body">
            <div class="container-fluid">

                {{-- Alerts --}}
                @if (Session::has('failed'))
                    <div class="alert alert-important alert-danger alert-dismissible mb-3" role="alert">
                        <div class="d-flex">
                            <div>
                                {{ Session::get('failed') }}
                            </div>
                        </div>
                        <a class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="close"></a>
                    </div>
                @endif

                @if (Session::has('success'))
                    <div class="alert alert-important alert-success alert-dismissible mb-3" role="alert">
                        <div class="d-flex">
                            <div>
                                {{ Session::get('success') }}
                            </div>
                        </div>
                        <a class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="close"></a>
                    </div>
                @endif

                <div class="row g-3">

                    {{-- Left: Form Configuration Card --}}
                    <div class="col-lg-8">
                        <form id="sitemapForm" action="{{ route('admin.generate.sitemap') }}" method="POST">
                            @csrf

                            {{-- Info Box --}}
                            <div class="sitemap-info-box p-3 mb-3 d-flex align-items-start gap-3">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon text-primary mt-1 flex-shrink-0"
                                    width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                    <path d="M3 12a9 9 0 1 0 18 0a9 9 0 0 0 -18 0" />
                                    <path d="M12 9h.01" />
                                    <path d="M11 12h1v4h1" />
                                </svg>
                                <div>
                                    <div class="fw-semibold text-primary">{{ __('How it works') }}</div>
                                    <div class="text-muted medium mt-1">
                                        {{ __('Select the content categories you want to include in your sitemap.xml. The file will be saved to your public directory and submitted to search engines automatically.') }}
                                    </div>
                                </div>
                            </div>

                            <!-- Upgraded Selection Board Card to borderless with shadowed styles -->
                            <div class="card border-0 shadow-sm">
                                <div class="card-header">
                                    <h3 class="card-title">{{ __('Select Categories') }}</h3>
                                    <div class="card-options">
                                        <a href="#" id="selectAllBtn" class="btn btn-sm btn-primary">
                                            {{ __('Select All') }}
                                        </a>
                                        <a href="#" id="clearAllBtn" class="btn btn-sm btn-dark ms-1">
                                            {{ __('Clear') }}
                                        </a>
                                    </div>
                                </div>

                                <div class="card-body">
                                    {{-- Hidden select for form submission --}}
                                    <select name="categories[]" id="categories" multiple class="d-none" required>
                                        <option value="all">{{ __('All') }}</option>
                                        <option value="pages">{{ __('Website Pages') }}</option>
                                        <option value="blog">{{ __('Blogs') }}</option>
                                        <option value="vcards">{{ __('Digital Business Cards') }}</option>
                                        <option value="store">{{ __('Stores') }}</option>
                                        <option value="webtools">{{ __('Web Tools') }}</option>
                                        @if (is_dir(base_path('Plugins/OrderNFCSystem')))
                                            <option value="plugin_nfc">{{ __('NFC Cards') }}</option>
                                        @endif
                                        @if (is_dir(base_path('Plugins/Directory')))
                                            <option value="plugin_directory">{{ __('Directory') }}</option>
                                            <option value="plugin_directory_vcards_stores">
                                                {{ __('Directory - Digital Business Cards & Stores') }}
                                            </option>
                                        @endif
                                    </select>

                                    {{-- Visual Category Cards Panel --}}
                                    <div class="row g-2">
                                        {{-- All --}}
                                        <div class="col-6 col-sm-4">
                                            <div class="card card-sm border cursor-pointer category-card"
                                                data-category="all">
                                                <div class="card-body d-flex align-items-center">
                                                    <span class="avatar bg-blue-lt text-blue rounded-3 me-2">ALL</span>
                                                    <div class="text-truncate fw-semibold text-dark">{{ __('All') }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Pages --}}
                                        <div class="col-6 col-sm-4">
                                            <div class="card card-sm border cursor-pointer category-card"
                                                data-category="pages">
                                                <div class="card-body d-flex align-items-center">
                                                    <span class="avatar bg-purple-lt text-purple rounded-3 me-2">PAG</span>
                                                    <div class="text-truncate fw-semibold text-dark">
                                                        {{ __('Website Pages') }}</div>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Blogs --}}
                                        <div class="col-6 col-sm-4">
                                            <div class="card card-sm border cursor-pointer category-card"
                                                data-category="blog">
                                                <div class="card-body d-flex align-items-center">
                                                    <span class="avatar bg-orange-lt text-orange rounded-3 me-2">BLG</span>
                                                    <div class="text-truncate fw-semibold text-dark">{{ __('Blogs') }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Digital Business Cards --}}
                                        <div class="col-6 col-sm-4">
                                            <div class="card card-sm border cursor-pointer category-card"
                                                data-category="vcards">
                                                <div class="card-body d-flex align-items-center">
                                                    <span class="avatar bg-green-lt text-green rounded-3 me-2">VCD</span>
                                                    <div class="text-truncate fw-semibold text-dark">
                                                        {{ __('Digital Business Cards') }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Store --}}
                                        <div class="col-6 col-sm-4">
                                            <div class="card card-sm border cursor-pointer category-card"
                                                data-category="store">
                                                <div class="card-body d-flex align-items-center">
                                                    <span class="avatar bg-yellow-lt text-yellow rounded-3 me-2">STR</span>
                                                    <div class="text-truncate fw-semibold text-dark">{{ __('Stores') }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Web Tools --}}
                                        <div class="col-6 col-sm-4">
                                            <div class="card card-sm border cursor-pointer category-card"
                                                data-category="webtools">
                                                <div class="card-body d-flex align-items-center">
                                                    <span class="avatar bg-cyan-lt text-cyan rounded-3 me-2">TLS</span>
                                                    <div class="text-truncate fw-semibold medium">{{ __('Web Tools') }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Simplified Footer with standard device-floppy save button (No Cancel Button) -->
                                <div class="card-footer text-end">
                                    <button type="submit" class="btn btn-primary btn-md d-inline-flex align-items-center"
                                        id="generateBtn">
                                        <svg xmlns="http://www.w3.org/2000/svg"
                                            class="icon icon-tabler icon-tabler-device-floppy me-2" width="24"
                                            height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                            fill="none" stroke-linecap="round" stroke-linejoin="round">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                            <path
                                                d="M6 4h10l4 4v10a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2" />
                                            <path d="M12 14m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" />
                                            <path d="M14 4l0 4h-6l0 -4" />
                                        </svg>
                                        <span id="btnIconSpinner" class="spinner-border spinner-border-sm me-2 d-none"
                                            role="status"></span>
                                        {{ __('Generate') }}
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    {{-- Right: Status & Info Sidebar Panel --}}
                    <div class="col-lg-4">

                        {{-- Sitemap Status Info panel (Upgraded to borderless with shadowed styles) --}}
                        <div class="card mb-3 border-0 shadow-sm">
                            <div class="card-header">
                                <h3 class="card-title">{{ __('Sitemap Status') }}</h3>
                            </div>
                            <div class="card-body">
                                @php
                                    $sitemapPath = public_path('sitemap.xml');
                                    $exists = file_exists($sitemapPath);
                                    $lastUpdated = $exists
                                        ? \Carbon\Carbon::createFromTimestamp(filemtime($sitemapPath))->diffForHumans()
                                        : null;
                                    $fileSize = $exists ? round(filesize($sitemapPath) / 1024, 1) . ' KB' : null;
                                @endphp

                                @if ($exists)
                                    <div class="d-flex align-items-center gap-3 mb-3">
                                        <span class="status-dot status-dot-animated bg-green"></span>
                                        <span class="fw-semibold text-green">{{ __('Sitemap exists') }}</span>
                                    </div>
                                    <div class="list-group list-group-flush text-secondary medium">
                                        <div class="list-group-item px-0">
                                            <div class="row align-items-center">
                                                <div class="col text-muted medium">{{ __('Last generated') }}</div>
                                                <div class="col-auto fw-semibold text-dark">{{ $lastUpdated }}</div>
                                            </div>
                                        </div>
                                        <div class="list-group-item px-0">
                                            <div class="row align-items-center">
                                                <div class="col text-muted medium">{{ __('File size') }}</div>
                                                <div class="col-auto fw-semibold text-dark">{{ $fileSize }}</div>
                                            </div>
                                        </div>
                                        <div class="list-group-item px-0">
                                            <div class="row align-items-center">
                                                <div class="col text-muted medium">{{ __('Location') }}</div>
                                                <div class="col-auto">
                                                    <code
                                                        class="medium text-danger bg-light p-1 rounded">/sitemap.xml</code>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <div class="d-flex align-items-center gap-3">
                                        <span class="status-dot bg-yellow"></span>
                                        <span class="text-muted medium">{{ __('No sitemap generated yet.') }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Tips Panel (Upgraded to borderless with shadowed styles) --}}
                        <div class="card border-0 shadow-sm">
                            <div class="card-header">
                                <h3 class="card-title">{{ __('Tips') }}</h3>
                            </div>
                            <div class="card-body p-0">
                                <div class="list-group list-group-flush text-secondary medium">
                                    <div class="list-group-item">
                                        <div class="d-flex flex-column gap-2">
                                            <p class="medium text-muted mb-0">
                                                {{ __('1. Regenerate your sitemap whenever you add new pages, blogs, or cards.') }}
                                            </p>
                                            <p class="medium text-muted mb-0">
                                                {{ __('2. Submit your sitemap URL to Google Search Console to improve search indexing.') }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Footer --}}
        @include('admin.includes.footer')
    </div>

    <!-- Confirmation Modal -->
    <div class="modal modal-bg fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmDeleteModalLabel">{{ __('Confirm Delete') }}</h5>
                </div>
                <div class="modal-body">
                    {{ __('Are you sure you want to remove this template? This action cannot be undone.') }}</div>
                <div class="modal-footer d-flex justify-content-between">
                    <button type="button" class="btn btn-secondary"
                        data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="button" class="btn btn-danger" onclick="confirmDelete(this)"
                        id="confirmDeleteBtn">{{ __('Remove') }}</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Progress Modal --}}
    <div class="modal modal-bg fade" id="sitemapModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content" style="border-radius: 12px; overflow: hidden;">
                <div class="modal-body text-center p-4">
                    <div class="mb-3">
                        <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-primary-lt"
                            style="width: 64px; height: 64px;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="text-primary icon-pulse">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                <path d="M3 3m0 2a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v2a2 2 0 0 1 -2 2h-2a2 2 0 0 1 -2 -2z" />
                                <path d="M15 15m0 2a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v2a2 2 0 0 1 -2 2h-2a2 2 0 0 1 -2 -2z" />
                                <path d="M7 9l0 3.5a1.5 1.5 0 0 0 1.5 1.5h5" />
                                <path d="M13.5 12l0 -4.5a1.5 1.5 0 0 1 1.5 -1.5" />
                                <path d="M13.5 12l0 4.5a1.5 1.5 0 0 0 1.5 1.5" />
                            </svg>
                        </div>
                    </div>
                    <h3>{{ __('Installing Template...') }}</h3>
                    <div class="text-muted medium">
                        {{ __('Please do not close this page.') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

{{-- Custom JS --}}
@section('scripts')
    <script type="text/javascript">
        $(document).ready(function() {
            "use strict";

            // Category card click
            $('.category-card').on('click', function(e) {
                e.preventDefault();

                $(this).toggleClass('border-primary bg-primary-lt');

                let cat = $(this).data('category');
                $('#categories option[value="' + cat + '"]').prop(
                    'selected',
                    !$('#categories option[value="' + cat + '"]').prop('selected')
                );

                updateSelectionCount();
            });

            // Select All
            $('#selectAllBtn').on('click', function(e) {
                e.preventDefault();

                $('.category-card').addClass('border-primary bg-primary-lt');
                $('#categories option').prop('selected', true);

                updateSelectionCount();
            });

            // Clear All
            $('#clearAllBtn').on('click', function(e) {
                e.preventDefault();

                $('.category-card').removeClass('border-primary bg-primary-lt');
                $('#categories option').prop('selected', false);

                updateSelectionCount();
            });

            // Update selected count
            function updateSelectionCount() {
                let count = $('#categories option:selected').length;
                $('#selectedCount').text(count);
            }

            // Initial count
            updateSelectionCount();

        });
    </script>
@endsection
