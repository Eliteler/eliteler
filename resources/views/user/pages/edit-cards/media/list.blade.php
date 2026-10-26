{{-- Dynamic Alert Notifications --}}
<div id="showAlert"></div>

{{-- Scoped Workspace Styling --}}
<style>
    .fm-modal-box {
        background: var(--tblr-bg-surface);
        border: 1px solid var(--tblr-border-color-translucent);
        border-radius: var(--tblr-border-radius-lg);
        overflow: hidden;
        display: flex;
        flex-direction: column;
    }

    /* Modal Toolbar */
    .fm-modal-toolbar {
        padding: 0.75rem 1rem;
        border-bottom: 1px solid var(--tblr-border-color-translucent);
        background: var(--tblr-bg-surface);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
    }

    .fm-modal-search {
        min-width: 200px;
        border: 1.5px solid var(--tblr-border-color);
        border-radius: 8px;
        padding: 0.4rem 0.65rem 0.4rem 2rem;
        font-size: 0.85rem;
        background: var(--tblr-bg-forms);
        color: var(--tblr-body-color);
    }

    .fm-modal-search:focus {
        border-color: var(--tblr-primary) !important;
        box-shadow: 0 0 0 0.25rem rgba(var(--tblr-primary-rgb), 0.12) !important;
        outline: 0;
    }

    /* Split Area */
    .fm-modal-split {
        display: flex;
        min-height: 400px;
        max-height: 480px;
        position: relative;
    }

    .fm-modal-content-pane {
        flex: 1;
        padding: 0.85rem;
        overflow-y: auto;
    }

    /* Side Inspector Panel */
    .fm-modal-inspector {
        width: 280px;
        border-left: 1px solid var(--tblr-border-color-translucent);
        background: var(--tblr-bg-surface-secondary);
        padding: 1rem;
        display: flex;
        flex-direction: column;
        overflow-y: auto;
    }

    @media (max-width: 991.98px) {
        .fm-modal-inspector {
            display: none !important;
        }
    }

    /* Grid Cards */
    .fm-card-item {
        background-color: var(--tblr-bg-surface);
        border: 1.5px solid var(--tblr-border-color-translucent);
        border-radius: 10px;
        padding: 0.5rem;
        cursor: pointer;
        transition: all 0.18s ease;
        position: relative;
    }

    .fm-card-item:hover {
        border-color: var(--tblr-primary);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        transform: translateY(-2px);
    }

    .fm-card-item.selected {
        border-color: var(--tblr-primary) !important;
        background-color: var(--tblr-primary-lt) !important;
    }

    /* Proportional Thumbnail Frame (Fixed Ratio) */
    .fm-card-thumb {
        width: 100%;
        height: 125px;
        border-radius: 6px;
        background-size: cover;
        background-position: center;
        background-color: var(--tblr-bg-surface-secondary);
        position: relative;
        overflow: hidden;
    }

    /* Shimmer Glow Animation */
    .fm-card-thumb::before,
    .fm-hero-preview::before {
        content: "";
        position: absolute;
        inset: 0;
        transform: translateX(-100%);
        background-image: linear-gradient(90deg, rgba(var(--tblr-body-color-rgb), 0) 0, rgba(var(--tblr-body-color-rgb), 0.05) 20%, rgba(var(--tblr-body-color-rgb), 0.1) 60%, rgba(var(--tblr-body-color-rgb), 0));
        animation: fmShimmer 2s infinite ease-in-out;
    }

    @keyframes fmShimmer {
        100% {
            transform: translateX(100%);
        }
    }

    .fm-hero-preview {
        width: 100%;
        height: 150px;
        border-radius: 8px;
        background-size: contain;
        background-repeat: no-repeat;
        background-position: center;
        background-color: #0b0f19;
        margin-bottom: 1rem;
        position: relative;
        overflow: hidden;
    }

    /* List View Table */
    .fm-modal-table tbody tr {
        cursor: pointer;
        transition: background 0.15s ease;
    }

    .fm-modal-table tbody tr.selected {
        background-color: var(--tblr-primary-lt) !important;
    }

    .fm-list-thumb {
        width: 36px;
        height: 36px;
        border-radius: 6px;
        background-size: cover;
        background-position: center;
        flex-shrink: 0;
    }

    /* Mobile Adaptations */
    @media (max-width: 767.98px) {
        .fm-modal-toolbar {
            padding: 0.65rem !important;
        }

        .fm-modal-search {
            width: 100% !important;
            min-width: 0 !important;
        }

        .fm-card-thumb {
            height: 110px !important;
        }
    }
</style>

<!-- Main File Manager Box -->
<div class="fm-modal-box shadow-sm mb-3">
    <!-- Top Filter / Search Bar -->
    <div class="fm-modal-toolbar">
        <div class="position-relative flex-fill me-2">
            <svg xmlns="http://www.w3.org/2000/svg"
                class="icon icon-tabler icon-tabler-search position-absolute text-muted" style="top: 8px; left: 8px;"
                width="15" height="15" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                stroke-linecap="round" stroke-linejoin="round">
                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                <circle cx="10" cy="10" r="7" />
                <line x1="21" y1="21" x2="15" y2="15" />
            </svg>
            <input type="text" class="form-control fm-modal-search" id="modalSearchInput"
                placeholder="{{ __('Search files...') }}" oninput="debounceModalSearch(this.value)">
        </div>

        <div class="btn-group gap-1 flex-shrink-0" role="group">
            <button type="button" class="btn btn-icon btn-sm btn-primary shadow-sm" id="btnModalViewGrid"
                onclick="switchModalView('grid')" title="{{ __('Grid View') }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-layout-grid" width="16"
                    height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                    <rect x="4" y="4" width="6" height="6" rx="1" />
                    <rect x="14" y="4" width="6" height="6" rx="1" />
                    <rect x="4" y="14" width="6" height="6" rx="1" />
                    <rect x="14" y="14" width="6" height="6" rx="1" />
                </svg>
            </button>
            <button type="button" class="btn btn-icon btn-sm btn-primary-lt" id="btnModalViewList"
                onclick="switchModalView('list')" title="{{ __('List View') }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-list" width="16"
                    height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                    stroke-linecap="round" stroke-linejoin="round">
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

    <!-- Split Area -->
    <div class="fm-modal-split">
        <!-- Content Area -->
        <div class="fm-modal-content-pane" id="modalFilesPane">
            {{-- Grid View Cards Container --}}
            <div class="row g-2" id="mediaCardsContainer"></div>

            {{-- List View Table Container --}}
            <div class="table-responsive d-none" id="modalListView">
                <table class="table table-vcenter text-nowrap fm-modal-table">
                    <thead>
                        <tr>
                            <th>{{ __('Name') }}</th>
                            <th>{{ __('Size') }}</th>
                            <th class="w-1 text-end">{{ __('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody id="modalListTbody"></tbody>
                </table>
            </div>

            {{-- Rich Empty State Indicator --}}
            <div class="text-center py-5" id="noImagesFound" style="display: none;">
                <div class="avatar avatar-lg bg-primary-lt rounded-circle mb-3">
                    <svg xmlns="http://www.w3.org/2000/svg"
                        class="icon icon-tabler icon-tabler-photo-off text-primary" width="28" height="28"
                        viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                        <line x1="3" y1="3" x2="21" y2="21" />
                        <line x1="15" y1="8" x2="15.01" y2="8" />
                        <path
                            d="M19.121 19.122a3 3 0 0 1 -2.121 .878h-10a3 3 0 0 1 -3 -3v-10c0 -.833 .34 -1.587 .888 -2.131m3.112 -.869h9a3 3 0 0 1 3 3v9" />
                        <path d="M4 15l4 -4c.928 -.893 2.072 -.893 3 0l5 5" />
                        <path d="M14 14l1 -1a3.03 3.03 0 0 1 2.378 -.982" />
                    </svg>
                </div>
                <h4 class="fw-bold mb-1">{{ __('No images found') }}</h4>
                <p class="text-muted small">
                    {{ __('There are no uploaded image assets found inside your media library.') }}</p>
            </div>
        </div>

        <!-- Right Side File Inspector Panel -->
        <div class="fm-modal-inspector" id="modalInspector">
            <div id="modalInspectorEmpty" class="text-center my-auto text-muted small py-4">
                <svg xmlns="http://www.w3.org/2000/svg"
                    class="icon icon-tabler icon-tabler-info-circle mb-2 text-primary" width="28" height="28"
                    viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                    <circle cx="12" cy="12" r="9" />
                    <line x1="12" y1="8" x2="12.01" y2="8" />
                    <polyline points="11 12 12 12 12 16 13 16" />
                </svg>
                <div>{{ __('Select any file to inspect details and copy URL.') }}</div>
            </div>

            <div id="modalInspectorDetails" class="d-none">
                <h5 class="fw-bold mb-2">{{ __('File Details') }}</h5>
                <div class="fm-hero-preview" id="modalInspectorThumb"></div>

                <div class="mb-3">
                    <label class="form-label text-muted small mb-1">{{ __('File URL') }}</label>
                    <div class="input-group input-group-sm">
                        <input type="text" class="form-control" id="modalInspectorUrl" readonly>
                        <button class="btn btn-primary" type="button"
                            onclick="copyModalUrl()">{{ __('Copy') }}</button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-sm table-borderless small mb-3">
                        <tr>
                            <td class="text-muted ps-0">{{ __('Size:') }}</td>
                            <td class="text-end fw-semibold text-body" id="modalInspectorSize">-</td>
                        </tr>
                        <tr>
                            <td class="text-muted ps-0">{{ __('Uploaded:') }}</td>
                            <td class="text-end fw-semibold text-body" id="modalInspectorDate">-</td>
                        </tr>
                    </table>
                </div>

                <div class="d-flex gap-2">
                    <a href="#" target="_blank" class="btn btn-primary btn-sm w-100"
                        id="modalInspectorPreviewBtn">{{ __('Open') }}</a>
                    <button class="btn btn-danger btn-sm" id="modalInspectorDeleteBtn">{{ __('Delete') }}</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Responsive Horizontal Bottom Pagination -->
    <div class="fm-modal-toolbar border-top d-flex align-items-center justify-content-between" id="showPagination"
        style="display: none;">
        <button type="button" class="btn btn-primary btn-sm d-inline-flex align-items-center gap-1 shadow-sm"
            id="prevPageBtn" onclick="loadPreviousPage()">
            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-chevron-left" width="16"
                height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                stroke-linecap="round" stroke-linejoin="round">
                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                <polyline points="15 6 9 12 15 18" />
            </svg>
            <span>{{ __('Previous') }}</span>
        </button>

        <span class="text-muted small fw-semibold">
            <span id="paginationStartIndex">1</span> - <span id="paginationEndIndex">12</span> {{ __('of') }}
            <span id="paginationTotalCount">0</span> {{ __('results') }}
        </span>

        <button type="button" class="btn btn-primary btn-sm d-inline-flex align-items-center gap-1 shadow-sm"
            id="nextPageBtn" onclick="loadNextPage()">
            <span>{{ __('Next') }}</span>
            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-chevron-right" width="16"
                height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                stroke-linecap="round" stroke-linejoin="round">
                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                <polyline points="9 6 15 12 9 18" />
            </svg>
        </button>
    </div>
</div>

{{-- Single Delete Confirmation Modal --}}
<div class="modal modal-blur fade" id="delete-modal" tabindex="-1" role="dialog" aria-hidden="true">
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
                <div id="delete_status" class="text-muted small"></div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <div class="w-100">
                    <div class="row g-2">
                        <div class="col"><button type="button" class="btn btn-primary-lt w-100"
                                data-bs-dismiss="modal">{{ __('Cancel') }}</button></div>
                        <div class="col"><a class="btn btn-danger w-100"
                                id="delete_id">{{ __('Yes, Proceed') }}</a></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>