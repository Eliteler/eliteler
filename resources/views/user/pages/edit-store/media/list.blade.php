{{-- ==========================================================
     Media grid
     <div class="col-6 col-sm-4 col-lg-3">
       <div class="card ml-media-card border-0 shadow-sm">
         <div class="ratio ratio-1x1 ml-media-thumb">
           <img src="{{ '{{ $url }}' }}" alt="{{ '{{ $name }}' }}" class="card-img-top object-cover" loading="lazy">
           <div class="ml-media-overlay">
             <button type="button" class="btn btn-icon btn-sm btn-white" title="{{ __('Copy URL') }}"
                     onclick="copyImageUrl('{{ '{{ $url }}' }}')">
               <i class="ti ti-copy"></i>
             </button>
             <button type="button" class="btn btn-icon btn-sm btn-white text-danger" title="{{ __('Delete') }}"
                     onclick="deleteImage({{ '{{ $id }}' }})">
               <i class="ti ti-trash"></i>
             </button>
           </div>
         </div>
         <div class="card-body py-2 px-2 text-truncate small text-muted">{{ '{{ $name }}' }}</div>
       </div>
     </div>
========================================================== --}}
<div class="row g-3" id="mediaCardsContainer" data-route="{{ route('user.media') }}"></div>

{{-- ==========================================================
     Empty state
========================================================== --}}
<div class="row" id="noImagesFound" style="display: none;">
    <div class="col-12">
        <div class="card border-0 shadow-sm py-5">
            <div class="empty">
                <div class="empty-img mb-3">
                    <img id="empty" src="{{ asset('img/empty.svg') }}" height="128"
                        alt="{{ __('No images found') }}">
                </div>
                <p class="empty-title h3 mb-1">{{ __('Your media library is empty') }}</p>
                <p class="empty-subtitle text-secondary">
                    {{ __('Drag some images into the box above to get started.') }}
                </p>
            </div>
        </div>
    </div>
</div>

{{-- ==========================================================
     Pagination
========================================================== --}}
<div class="card border-0 shadow-sm mb-4" id="showPagination">
    <div class="card-footer d-flex flex-column flex-sm-row align-items-center justify-content-sm-between gap-2 bg-transparent text-center text-sm-start"
        id="paginationLinks">
        <p class="m-0 text-muted small order-2 order-sm-1">
            {{ __('Showing') }} <span class="fw-medium text-body" id="paginationStartIndex"></span>
            {{ __('to') }} <span class="fw-medium text-body" id="paginationEndIndex"></span>
            {{ __('of') }} <span class="fw-medium text-body" id="paginationTotalCount"></span>
            {{ __('results') }}
        </p>
        <nav class="order-1 order-sm-2" aria-label="{{ __('Media pagination') }}">
            <ul class="pagination mb-0 justify-content-center">
                <li class="page-item" id="prevPageBtn">
                    <button type="button" class="page-link d-inline-flex align-items-center"
                        onclick="loadPreviousPage()">
                        <i class="ti ti-chevron-left me-1"></i> {{ __('Previous') }}
                    </button>
                </li>
                <li class="page-item" id="nextPageBtn">
                    <button type="button" class="page-link d-inline-flex align-items-center" onclick="loadNextPage()">
                        {{ __('Next') }} <i class="ti ti-chevron-right ms-1"></i>
                    </button>
                </li>
            </ul>
        </nav>
    </div>
</div>

<style>
    .ml-media-card {
        overflow: hidden;
        transition: transform .15s ease, box-shadow .15s ease;
    }

    .ml-media-card:hover {
        transform: translateY(-3px);
        box-shadow: var(--tblr-shadow-lg) !important;
    }

    .ml-media-thumb {
        background: var(--tblr-bg-surface-secondary);
    }

    .object-cover {
        object-fit: cover;
    }

    /* ---- Skeleton glow (shown while a page is loading) ---- */
    .ml-skeleton-card {
        pointer-events: none;
    }

    .ml-skeleton-glow {
        position: relative;
        overflow: hidden;
        background: var(--tblr-bg-surface-secondary);
        border-radius: 4px;
    }

    .ml-skeleton-line {
        height: .8rem;
    }

    .ml-skeleton-glow::after {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(100deg,
                transparent 20%,
                color-mix(in srgb, var(--tblr-primary) 18%, transparent) 50%,
                transparent 80%);
        transform: translateX(-100%);
        animation: ml-glow-sweep 1.4s ease-in-out infinite;
    }

    @keyframes ml-glow-sweep {
        to {
            transform: translateX(100%);
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .ml-skeleton-glow::after {
            animation: none;
        }
    }

    .ml-media-overlay {
        position: absolute;
        inset: 0;
        display: flex;
        align-items: flex-start;
        justify-content: flex-end;
        gap: .375rem;
        padding: .5rem;
        background: linear-gradient(180deg, rgba(0, 0, 0, .35) 0%, rgba(0, 0, 0, 0) 35%);
        opacity: 0;
        transition: opacity .15s ease;
    }

    .ml-media-card:hover .ml-media-overlay {
        opacity: 1;
    }

    #showPagination .page-item.disabled .page-link,
    #showPagination .page-link:disabled {
        opacity: .5;
        pointer-events: none;
    }
</style>
