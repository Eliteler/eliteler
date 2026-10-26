@extends('user.layouts.index', ['header' => true, 'nav' => true, 'demo' => true, 'settings' => $settings])

@section('css')
    {{-- File Manager CSS --}}
    <link href="{{ asset('css/file-manager.css') }}" rel="stylesheet">
@endsection

@section('content')
    <div class="page-wrapper">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <!-- Page Header -->
        <div class="page-header d-print-none">
            <div class="container-fluid">
                <div class="row g-2 align-items-center">
                    <div class="col-12">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb breadcrumb-arrows">
                                <li class="breadcrumb-item">
                                    <a href="{{ route('user.dashboard') }}" class="text-secondary text-decoration-none">
                                        {{ __('Dashboard') }}
                                    </a>
                                </li>
                                <li class="breadcrumb-item active fw-semibold" aria-current="page">
                                    {{ __('Media Library') }}
                                </li>
                            </ol>
                        </nav>
                    </div>

                    <div class="col">
                        <h2 class="page-title text-reset fw-bold">
                            {{ __('Media Library') }}
                        </h2>
                        <div class="text-muted mt-1">
                            {{ __('Browse, inspect, filter, and organize your uploaded digital assets.') }}
                        </div>
                    </div>

                    <!-- Right Header: Storage Overview Pill & Upload -->
                    <div class="col-auto ms-auto d-flex align-items-center gap-3">
                        <div class="storage-pill d-none d-md-flex">
                            <svg xmlns="http://www.w3.org/2000/svg"
                                class="icon icon-tabler icon-tabler-database text-primary" width="16" height="16"
                                viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                                stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                <ellipse cx="12" cy="6" rx="8" ry="3"></ellipse>
                                <path d="M4 6v6a8 3 0 0 0 16 0v-6" />
                                <path d="M4 12v6a8 3 0 0 0 16 0v-6" />
                            </svg>
                            <span>{{ __('Used:') }} <strong>{{ $usedStorage }} MB</strong> /
                                {{ $active_plan->storage == '999' ? __('Unlimited') : $totalStorage . ' MB' }}</span>
                            <div class="progress" style="width: 60px; height: 6px;">
                                <div class="progress-bar bg-primary" style="width: {{ $usedStoragePercentage }}%"></div>
                            </div>
                        </div>

                        <a href="{{ route('user.add.media') }}"
                            class="btn btn-primary btn-icon-only d-inline-flex align-items-center shadow-sm px-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-upload"
                                width="18" height="18" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                fill="none" stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                <path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2 -2v-2" />
                                <path d="M7 9l5 -5l5 5" />
                                <path d="M12 4l0 12" />
                            </svg>
                            <span class="d-none d-sm-inline">{{ __('Upload Files') }}</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Page Body -->
        <div class="page-body">
            <div class="container-fluid">
                <div id="showAlert"></div>

                <!-- Main File Manager Box -->
                <div class="fm-workspace-card shadow-sm">

                    <!-- Modern Unified Toolbar Controls -->
                    <div class="fm-toolbar">
                        <!-- Left: Select All & Floating Delete Pill -->
                        <div class="fm-selection-cluster">
                            <label class="form-check m-0 cursor-pointer user-select-none">
                                <input class="form-check-input" type="checkbox" id="selectAllCheckbox"
                                    onchange="toggleSelectAll(this)">
                                <span class="form-check-label fw-bold text-reset small">{{ __('Select All') }}</span>
                            </label>

                            <button type="button"
                                class="btn btn-danger btn-sm fm-compact-delete-btn d-none align-items-center gap-1 shadow-sm"
                                id="bulkDeleteBtn" onclick="confirmBulkDelete()">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-trash"
                                    width="14" height="14" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                    fill="none" stroke-linecap="round" stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                    <path d="M4 7l16 0" />
                                    <path d="M10 11l0 6" />
                                    <path d="M14 11l0 6" />
                                    <path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" />
                                    <path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" />
                                </svg>
                                <span>{{ __('Delete') }}</span>
                                <span class="badge-count" id="bulkDeleteCount">0</span>
                            </button>
                        </div>

                        <!-- Right: Search Input & View Mode Switcher -->
                        <div class="fm-toolbar-controls">
                            <div class="fm-search-wrapper">
                                <svg xmlns="http://www.w3.org/2000/svg"
                                    class="icon icon-tabler icon-tabler-search position-absolute text-muted"
                                    style="top: 9px; left: 10px;" width="16" height="16" viewBox="0 0 24 24"
                                    stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                    <circle cx="10" cy="10" r="7" />
                                    <line x1="21" y1="21" x2="15" y2="15" />
                                </svg>
                                <input type="text" class="form-control fm-search-box" id="fmSearchInput"
                                    placeholder="{{ __('Search files...') }}" oninput="debounceSearch(this.value)">
                            </div>

                            <div class="btn-group gap-2 fm-view-switcher" role="group">
                                <button type="button" class="btn btn-icon btn-primary shadow-sm" id="btnViewGrid"
                                    onclick="switchViewMode('grid')" title="{{ __('Grid View') }}">
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                        class="icon icon-tabler icon-tabler-layout-grid" width="18" height="18"
                                        viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                                        stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                        <rect x="4" y="4" width="6" height="6" rx="1" />
                                        <rect x="14" y="4" width="6" height="6" rx="1" />
                                        <rect x="4" y="14" width="6" height="6" rx="1" />
                                        <rect x="14" y="14" width="6" height="6" rx="1" />
                                    </svg>
                                </button>
                                <button type="button" class="btn btn-icon btn-primary-lt" id="btnViewList"
                                    onclick="switchViewMode('list')" title="{{ __('List View') }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-list"
                                        width="18" height="18" viewBox="0 0 24 24" stroke-width="2"
                                        stroke="currentColor" fill="none" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                        <line x1="9" y1="6" x2="20" y2="6" />
                                        <line x1="9" y1="12" x2="20" y2="12" />
                                        <line x1="9" y1="18" x2="20" y2="18" />
                                        <line x1="5" y1="6" x2="5" y2="6.01" />
                                        <line x1="5" y1="12" x2="5" y2="12.01" />
                                        <line x1="5" y1="18" x2="5" y2="18.01" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Main Split Layout -->
                    <div class="fm-main-body">

                        <!-- Content Area -->
                        <div class="fm-files-zone" id="fmContentPane">

                            {{-- Grid View Cards --}}
                            <div class="row g-3" id="fmGridView"></div>

                            {{-- List View Table --}}
                            <div class="table-responsive d-none" id="fmListView">
                                <table class="table table-vcenter text-nowrap fm-table-view">
                                    <thead>
                                        <tr>
                                            <th class="w-1"></th>
                                            <th>{{ __('Name') }}</th>
                                            <th>{{ __('Type') }}</th>
                                            <th>{{ __('Size') }}</th>
                                            <th>{{ __('Date') }}</th>
                                            <th class="w-1 text-end">{{ __('Action') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody id="fmListTbody"></tbody>
                                </table>
                            </div>

                            {{-- Empty State Placeholder --}}
                            <div class="text-center py-5 d-none" id="fmEmptyState">
                                <div class="avatar avatar-xl bg-primary-lt rounded-circle mb-3">
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                        class="icon icon-tabler icon-tabler-folders text-primary" width="32"
                                        height="32" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                        fill="none" stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                        <path
                                            d="M9 4h3l2 2h5a2 2 0 0 1 2 2v7a2 2 0 0 1 -2 2h-10a2 2 0 0 1 -2 -2v-9a2 2 0 0 1 2 -2" />
                                        <path d="M17 17v2a2 2 0 0 1 -2 2h-10a2 2 0 0 1 -2 -2v-9a2 2 0 0 1 2 -2h2" />
                                    </svg>
                                </div>
                                <h4 class="fw-bold mb-1">{{ __('No Files Found') }}</h4>
                                <p class="text-muted small">
                                    {{ __('Upload images or assets to start managing them here.') }}</p>
                            </div>
                        </div>

                        <!-- Right Side Inspector Panel -->
                        <div class="fm-inspector-panel" id="fmInspector">
                            <div id="inspectorEmpty" class="text-center my-auto text-muted small py-5">
                                <svg xmlns="http://www.w3.org/2000/svg"
                                    class="icon icon-tabler icon-tabler-info-circle mb-2 text-primary" width="32"
                                    height="32" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                    fill="none" stroke-linecap="round" stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                    <circle cx="12" cy="12" r="9" />
                                    <line x1="12" y1="8" x2="12.01" y2="8" />
                                    <polyline points="11 12 12 12 12 16 13 16" />
                                </svg>
                                <div>{{ __('Select any file to inspect details, copy URL, or delete.') }}</div>
                            </div>

                            <div id="inspectorDetails" class="d-none">
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <h4 class="fw-bold mb-0">{{ __('File Details') }}</h4>
                                    <span class="badge bg-primary text-white" id="inspectorExt">JPG</span>
                                </div>

                                <div class="inspector-hero-img" id="inspectorThumb"></div>

                                <div class="mb-3">
                                    <label class="form-label text-muted small mb-1">{{ __('File URL') }}</label>
                                    <div class="input-group input-group-sm">
                                        <input type="text" class="form-control" id="inspectorUrl" readonly>
                                        <button class="btn btn-primary" type="button"
                                            onclick="copyInspectorUrl()">{{ __('Copy') }}</button>
                                    </div>
                                </div>

                                <div class="card bg-surface-secondary border-0 mb-3">
                                    <div class="card-body p-2">
                                        <table class="table table-sm table-borderless small mb-0">
                                            <tr>
                                                <td class="text-muted ps-1">{{ __('File Size:') }}</td>
                                                <td class="text-end fw-semibold" id="inspectorSize">-</td>
                                            </tr>
                                            <tr>
                                                <td class="text-muted ps-1">{{ __('Uploaded:') }}</td>
                                                <td class="text-end fw-semibold" id="inspectorDate">-</td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>

                                <div class="d-flex gap-2">
                                    <a href="#" target="_blank" class="btn btn-primary w-100"
                                        id="inspectorPreviewBtn">
                                        {{ __('Open Original') }}
                                    </a>
                                    <button class="btn btn-danger px-3" id="inspectorDeleteBtn">
                                        {{ __('Delete') }}
                                    </button>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Bottom App-like Pagination Bar -->
                    <div class="fm-toolbar fm-pagination-bar border-top d-flex align-items-center justify-content-between">
                        <button class="btn btn-primary btn-sm d-inline-flex align-items-center gap-1 shadow-sm"
                            id="prevPageBtn" type="button" onclick="loadPreviousPage()">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-chevron-left"
                                width="16" height="16" viewBox="0 0 24 24" stroke-width="2"
                                stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                <polyline points="15 6 9 12 15 18" />
                            </svg>
                            <span>{{ __('Previous') }}</span>
                        </button>

                        <div class="text-muted small fw-semibold pagination-info-text">
                            {{ __('Page') }} <span id="paginationCurrentPage" class="text-body fw-bold">1</span>
                            {{ __('of') }} <span id="paginationTotalPages" class="text-body fw-bold">1</span>
                        </div>

                        <button class="btn btn-primary btn-sm d-inline-flex align-items-center gap-1 shadow-sm"
                            id="nextPageBtn" type="button" onclick="loadNextPage()">
                            <span>{{ __('Next') }}</span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-chevron-right"
                                width="16" height="16" viewBox="0 0 24 24" stroke-width="2"
                                stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                <polyline points="9 6 15 12 9 18" />
                            </svg>
                        </button>
                    </div>

                </div>
            </div>
        </div>

        @include('user.includes.footer')
    </div>

    {{-- Single Delete Confirmation Modal --}}
    <div class="modal modal-bg fade" id="delete-modal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-body text-center py-4">
                    <div class="avatar avatar-lg bg-danger-lt mb-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-trash text-danger"
                            width="28" height="28" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                            fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                            <path d="M4 7l16 0" />
                            <path d="M10 11l0 6" />
                            <path d="M14 11l0 6" />
                            <path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" />
                            <path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" />
                        </svg>
                    </div>
                    <h3 class="fw-bold">{{ __('Are you sure?') }}</h3>
                    <div class="text-muted small">{{ __('This file will be permanently removed from your storage.') }}
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <div class="w-100">
                        <div class="row g-2">
                            <div class="col"><button type="button" class="btn btn-primary-lt w-100"
                                    data-bs-dismiss="modal">{{ __('Cancel') }}</button></div>
                            <div class="col"><a data-method="post" class="btn btn-danger w-100"
                                    id="delete_id">{{ __('Yes, Delete') }}</a></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Bulk Delete Confirmation Modal --}}
    <div class="modal modal-bg fade" id="bulk-delete-modal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-body text-center py-4">
                    <div class="avatar avatar-lg bg-danger-lt mb-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-trash text-danger"
                            width="28" height="28" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                            fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                            <path d="M4 7l16 0" />
                            <path d="M10 11l0 6" />
                            <path d="M14 11l0 6" />
                            <path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" />
                            <path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" />
                        </svg>
                    </div>
                    <h3 class="fw-bold">{{ __('Delete Selected Files?') }}</h3>
                    <div class="text-muted small" id="bulk_delete_status">
                        {{ __('Selected files will be deleted permanently.') }}</div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <div class="w-100">
                        <div class="row g-2">
                            <div class="col"><button type="button" class="btn btn-primary-lt w-100"
                                    data-bs-dismiss="modal">{{ __('Cancel') }}</button></div>
                            <div class="col"><button type="button" class="btn btn-danger w-100"
                                    id="confirmBulkDeleteBtn"
                                    onclick="executeBulkDelete()">{{ __('Yes, Delete') }}</button></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('custom-js')
    <script>
        "use strict";

        let allMediaData = [];
        let activeMedia = null;
        let selectedMediaIds = [];
        let currentPage = 1;
        let totalPages = 1;
        let viewMode = 'grid';
        let searchTimer = null;
        let currentSearchQuery = '';

        function debounceSearch(value) {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(() => {
                currentSearchQuery = value.trim();
                loadMedia(1);
            }, 300);
        }

        function showMediaSkeleton() {
            let gridSkeleton = '';
            for (let i = 0; i < 12; i++) {
                gridSkeleton += `
                    <div class="col-6 col-md-4 col-xl-3">
                        <div class="file-item-card placeholder-glow">
                            <div class="file-thumbnail-frame placeholder"></div>
                            <div class="mt-2">
                                <div class="placeholder col-8 rounded mb-1"></div>
                                <div class="d-flex justify-content-between">
                                    <span class="placeholder col-4 rounded"></span>
                                    <span class="placeholder col-3 rounded"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            }
            $('#fmGridView').html(gridSkeleton);

            let listSkeleton = '';
            for (let i = 0; i < 8; i++) {
                listSkeleton += `
                    <tr class="placeholder-glow">
                        <td><div class="placeholder col-12 rounded"></div></td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="fm-list-preview placeholder"></div>
                                <div class="placeholder col-7 rounded"></div>
                            </div>
                        </td>
                        <td><div class="placeholder col-4 rounded"></div></td>
                        <td><div class="placeholder col-6 rounded"></div></td>
                        <td><div class="placeholder col-8 rounded"></div></td>
                        <td class="text-end"><div class="placeholder col-6 rounded"></div></td>
                    </tr>
                `;
            }
            $('#fmListTbody').html(listSkeleton);
            $('#fmEmptyState').addClass('d-none');
        }

        function loadMedia(page = 1) {
            showMediaSkeleton();
            selectedMediaIds = [];
            updateBulkToolbar();

            $.ajax({
                url: '{{ route('user.media') }}',
                method: 'GET',
                data: {
                    page: page,
                    search: currentSearchQuery
                },
                dataType: 'json',
                success: function(response) {
                    allMediaData = response.media.data || [];
                    currentPage = response.media.current_page;
                    totalPages = response.media.last_page;

                    updatePaginationInfo();
                    renderFileManager(allMediaData);
                },
                error: function() {
                    $('#fmGridView').html('');
                    $('#fmListTbody').html('');
                    $('#fmEmptyState').removeClass('d-none');
                }
            });
        }

        function renderFileManager(items) {
            if (!items || items.length === 0) {
                $('#fmEmptyState').removeClass('d-none');
                $('#fmGridView').html('');
                $('#fmListTbody').html('');
                resetInspector();
                return;
            }

            $('#fmEmptyState').addClass('d-none');
            renderGridView(items);
            renderListView(items);

            // Maintain active inspection or select first item
            if (items.length > 0) {
                if (activeMedia) {
                    const found = items.find(m => m.media_id === activeMedia.media_id);
                    if (found) inspectMedia(found);
                    else inspectMedia(items[0]);
                } else {
                    inspectMedia(items[0]);
                }
            }
        }

        function renderGridView(items) {
            let html = '';
            items.forEach(media => {
                const isSelected = selectedMediaIds.includes(media.media_id);
                const isActive = activeMedia && activeMedia.media_id === media.media_id;
                const fullUrl = media.full_url;

                html += `
                    <div class="col-6 col-md-4 col-xl-3">
                        <div class="file-item-card ${isActive ? 'selected' : ''}" onclick="inspectMediaById('${media.media_id}')">
                            <input type="checkbox" class="form-check-input file-select-checkbox" 
                                value="${media.media_id}" 
                                ${isSelected ? 'checked' : ''} 
                                onclick="event.stopPropagation(); toggleMediaSelection('${media.media_id}', this)">
                            
                            <div class="file-thumbnail-frame" style="background-image: url('${fullUrl}');">
                                <span class="file-ext-tag">${media.extension}</span>
                            </div>
                            <div class="mt-2">
                                <div class="text-truncate fw-semibold small text-body" title="${media.file_name}">${media.file_name}</div>
                                <div class="d-flex justify-content-between text-muted mt-1" style="font-size: 0.75rem;">
                                    <span>${media.size_formatted}</span>
                                    <span>${media.formatted_created_at}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            });
            $('#fmGridView').html(html);
        }

        function renderListView(items) {
            let html = '';
            items.forEach(media => {
                const isSelected = selectedMediaIds.includes(media.media_id);
                const isActive = activeMedia && activeMedia.media_id === media.media_id;
                const fullUrl = media.full_url;

                html += `
                    <tr class="${isActive ? 'selected' : ''}" onclick="inspectMediaById('${media.media_id}')">
                        <td onclick="event.stopPropagation();">
                            <input type="checkbox" class="form-check-input" value="${media.media_id}" 
                                ${isSelected ? 'checked' : ''} 
                                onchange="toggleMediaSelection('${media.media_id}', this)">
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="fm-list-preview" style="background-image: url('${fullUrl}');"></div>
                                <div class="text-truncate fw-semibold" style="max-width: 250px;" title="${media.file_name}">${media.file_name}</div>
                            </div>
                        </td>
                        <td><span class="badge bg-primary-lt">${media.extension}</span></td>
                        <td class="text-muted small">${media.size_formatted}</td>
                        <td class="text-muted small">${media.formatted_created_at}</td>
                        <td class="text-end" onclick="event.stopPropagation();">
                            <button class="btn btn-icon btn-danger shadow-sm" onclick="deleteMedia('${media.media_id}')" title="{{ __('Delete') }}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-trash" width="14" height="14" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 7l16 0"/><path d="M10 11l0 6"/><path d="M14 11l0 6"/><path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12"/><path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3"/></svg>
                            </button>
                        </td>
                    </tr>
                `;
            });
            $('#fmListTbody').html(html);
        }

        function switchViewMode(mode) {
            viewMode = mode;
            if (mode === 'grid') {
                $('#btnViewGrid').removeClass('btn-primary-lt').addClass('btn-primary');
                $('#btnViewList').removeClass('btn-primary').addClass('btn-primary-lt');
                $('#fmGridView').removeClass('d-none');
                $('#fmListView').addClass('d-none');
            } else {
                $('#btnViewList').removeClass('btn-primary-lt').addClass('btn-primary');
                $('#btnViewGrid').removeClass('btn-primary').addClass('btn-primary-lt');
                $('#fmListView').removeClass('d-none');
                $('#fmGridView').addClass('d-none');
            }
        }

        function inspectMediaById(id) {
            const media = allMediaData.find(item => item.media_id == id);
            if (media) inspectMedia(media);
        }

        function inspectMedia(media) {
            activeMedia = media;
            const fullUrl = media.full_url;

            $('#inspectorEmpty').addClass('d-none');
            $('#inspectorDetails').removeClass('d-none');

            // Add placeholder glow while image loads
            $('#inspectorThumb').addClass('placeholder-glow');
            const img = new Image();
            img.src = fullUrl;
            img.onload = function() {
                $('#inspectorThumb').removeClass('placeholder-glow').css('background-image', `url('${fullUrl}')`);
            };

            $('#inspectorExt').text(media.extension);
            $('#inspectorUrl').val(media.media_url);
            $('#inspectorSize').text(media.size_formatted);
            $('#inspectorDate').text(media.full_date || media.formatted_created_at);
            $('#inspectorPreviewBtn').attr('href', fullUrl);
            $('#inspectorDeleteBtn').attr('onclick', `deleteMedia('${media.media_id}')`);

            // Highlight state
            $('.file-item-card').removeClass('selected');
            $('.fm-table-view tbody tr').removeClass('selected');
        }

        function resetInspector() {
            activeMedia = null;
            $('#inspectorEmpty').removeClass('d-none');
            $('#inspectorDetails').addClass('d-none');
        }

        function copyInspectorUrl() {
            const val = $('#inspectorUrl').val();
            navigator.clipboard.writeText(val);
            showNotificationAlert(`{{ __('Path copied to clipboard') }}`, 'success');
        }

        function toggleMediaSelection(id, checkbox) {
            if (checkbox.checked) {
                if (!selectedMediaIds.includes(id)) selectedMediaIds.push(id);
            } else {
                selectedMediaIds = selectedMediaIds.filter(item => item !== id);
            }
            updateBulkToolbar();
        }

        function toggleSelectAll(selectAllCheckbox) {
            const isChecked = selectAllCheckbox.checked;
            selectedMediaIds = isChecked ? allMediaData.map(m => m.media_id) : [];
            $('.file-select-checkbox, .fm-table-view .form-check-input').prop('checked', isChecked);
            updateBulkToolbar();
        }

        function updateBulkToolbar() {
            const count = selectedMediaIds.length;
            $('#bulkDeleteCount').text(count);
            if (count > 0) {
                $('#bulkDeleteBtn').removeClass('d-none').addClass('d-inline-flex');
            } else {
                $('#bulkDeleteBtn').addClass('d-none').removeClass('d-inline-flex');
            }
        }

        function confirmBulkDelete() {
            if (selectedMediaIds.length === 0) return;
            $('#bulk-delete-modal').modal('show');
        }

        function executeBulkDelete() {
            $.ajax({
                url: '{{ route('user.media.delete.multiple') }}',
                method: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    ids: selectedMediaIds
                },
                dataType: 'json',
                success: function(response) {
                    $('#bulk-delete-modal').modal('hide');
                    showNotificationAlert(response.message, 'success');
                    selectedMediaIds = [];
                    loadMedia(currentPage);
                }
            });
        }

        function deleteMedia(id) {
            $("#delete-modal").modal("show");
            var delete_link = document.getElementById("delete_id");
            delete_link.setAttribute("href", "{{ route('user.media.delete') }}?id=" + id);
        }

        function showNotificationAlert(message, type = 'success') {
            const alertDiv = `
                <div class="alert alert-important alert-${type} alert-dismissible border-0 shadow-sm mb-3" role="alert">
                    <div class="d-flex align-items-center gap-2"><div>${message}</div></div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            `;
            $('#showAlert').html(alertDiv);
            setTimeout(() => {
                $('#showAlert .alert').fadeOut();
            }, 4000);
        }

        function loadPreviousPage() {
            if (currentPage > 1) {
                currentPage--;
                loadMedia(currentPage);
            }
        }

        function loadNextPage() {
            if (currentPage < totalPages) {
                currentPage++;
                loadMedia(currentPage);
            }
        }

        function updatePaginationInfo() {
            $('#paginationCurrentPage').text(currentPage || 1);
            $('#paginationTotalPages').text(totalPages || 1);
            $('#prevPageBtn').prop('disabled', currentPage <= 1).toggleClass('disabled', currentPage <= 1);
            $('#nextPageBtn').prop('disabled', currentPage >= totalPages || totalPages === 0).toggleClass('disabled',
                currentPage >= totalPages || totalPages === 0);
        }

        $(document).ready(function() {
            loadMedia();
        });
    </script>
@endpush
