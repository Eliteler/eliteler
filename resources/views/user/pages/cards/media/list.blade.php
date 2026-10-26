{{-- ==========================================================
     Media grid container
========================================================== --}}
<div class="row g-3 mb-3" id="mediaCardsContainer" data-route="{{ route('user.media') }}"></div>

{{-- ==========================================================
     Empty state
========================================================== --}}
<div class="text-center py-5" id="noImagesFound" style="display: none;">
    <div class="empty">
        <div class="empty-img mb-3">
            <img id="empty" src="{{ asset('img/empty.svg') }}" height="110" alt="{{ __('No images found') }}">
        </div>
        <p class="empty-title h4 mb-1">{{ __('Your media library is empty') }}</p>
        <p class="empty-subtitle text-secondary small mb-0">
            {{ __('Drag and drop images or click upload above to get started.') }}
        </p>
    </div>
</div>

{{-- ==========================================================
     Modern Clean Pagination UI
========================================================== --}}
<div id="showPagination" class="pagination-wrapper">
    <div class="d-flex flex-column flex-sm-row align-items-center justify-content-between gap-3" id="paginationLinks">

        {{-- Results Info Text --}}
        <div class="pagination-info text-secondary small">
            {{ __('Showing') }} <span class="fw-bold text-dark" id="paginationStartIndex">1</span>
            {{ __('to') }} <span class="fw-bold text-dark" id="paginationEndIndex">12</span>
            {{ __('of') }} <span class="fw-bold text-dark" id="paginationTotalCount">37</span>
            {{ __('results') }}
        </div>

        {{-- Previous & Next Action Buttons --}}
        <div class="pagination-actions d-flex align-items-center gap-2" aria-label="{{ __('Media pagination') }}">
            <button type="button" class="btn btn-pagination" id="prevPageBtn" onclick="loadPreviousPage()">
                <i class="ti ti-chevron-left me-1"></i> {{ __('Previous') }}
            </button>
            <button type="button" class="btn btn-pagination" id="nextPageBtn" onclick="loadNextPage()">
                {{ __('Next') }} <i class="ti ti-chevron-right ms-1"></i>
            </button>
        </div>

    </div>
</div>

<style>
    /* 1. Force Remove Outer Card / Box Borders */
    #showPagination,
    #showPagination .card,
    #showPagination .card-footer {
        background: transparent !important;
        border: none !important;
        box-shadow: none !important;
        padding: 0 !important;
        margin: 0 !important;
    }

    /* 2. Modern Border Top Divider */
    .pagination-wrapper {
        border-top: 1px solid #e2e8f0 !important;
        padding-top: 1rem !important;
        margin-top: 1.25rem !important;
    }

    /* 3. Modern Sleek Action Buttons */
    .btn-pagination {
        background-color: #ffffff !important;
        border: 1px solid #cbd5e1 !important;
        color: #334155 !important;
        font-size: 0.8125rem !important;
        font-weight: 500 !important;
        padding: 0.4rem 0.85rem !important;
        border-radius: 6px !important;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05) !important;
        display: inline-flex !important;
        align-items: center !important;
        transition: all 0.15s ease-in-out !important;
    }

    .btn-pagination:hover:not(:disabled) {
        background-color: #f8fafc !important;
        border-color: #94a3b8 !important;
        color: #0f172a !important;
    }

    /* 4. Disabled State */
    .btn-pagination:disabled,
    .btn-pagination.disabled,
    #showPagination .page-item.disabled .btn-pagination {
        opacity: 0.5 !important;
        cursor: not-allowed !important;
        background-color: #f1f5f9 !important;
        border-color: #e2e8f0 !important;
        box-shadow: none !important;
    }
</style>
