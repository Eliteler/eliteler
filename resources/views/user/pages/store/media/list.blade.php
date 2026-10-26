{{-- Alert message --}}
<div id="showAlert"></div>

{{-- Media images collection container --}}
<div class="row g-3" id="mediaCardsContainer"></div>

{{-- Empty media illustration placeholder --}}
<div class="row" id="noImagesFound" style="display: none;">
    <div class="col-12">
        <div class="card border-0 shadow-sm py-5 text-center">
            <div class="empty">
                <div class="empty-img mb-3">
                    <img id="empty" src="{{ asset('img/empty.svg') }}" height="128"
                        alt="{{ __('No images found') }}">
                </div>
                <p class="empty-title h3">{{ __('No images found') }}</p>
                <p class="empty-subtitle text-secondary">
                    {{ __('Your media directory is currently empty. Try uploading some files to get started.') }}
                </p>
            </div>
        </div>
    </div>
</div>

{{-- Upgraded Pagination Layout Card --}}
<div class="card border-0 shadow-sm mb-4" id="showPagination" style="display: none;">
    <div class="card-footer d-flex align-items-center bg-transparent" id="paginationLinks">
        <p class="m-0 text-muted small">
            <span id="paginationStartIndex"></span> {{ __('to') }}
            <span id="paginationEndIndex"></span> {{ __('of') }}
            <span id="paginationTotalCount"></span> {{ __('results') }}
        </p>
        <nav class="ms-auto">
            <ul class="pagination mb-0">
                <li class="page-item me-2" id="prevPageBtn">
                    <button type="button" class="btn btn-sm btn-primary d-inline-flex align-items-center"
                        onclick="loadPreviousPage()">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-chevron-left me-1"
                            width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                            fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                            <path d="M15 6l-6 6l6 6" />
                        </svg>
                        {{ __('Previous') }}
                    </button>
                </li>
                <li class="page-item" id="nextPageBtn">
                    <button type="button" class="btn btn-sm btn-primary d-inline-flex align-items-center"
                        onclick="loadNextPage()">
                        {{ __('Next') }}
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-chevron-right ms-1"
                            width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                            fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                            <path d="M9 6l6 6l-6 6" />
                        </svg>
                    </button>
                </li>
            </ul>
        </nav>
    </div>
</div>

{{-- Delete confirmation modal --}}
<div class="modal modal-bg fade" id="delete-modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-sm">
            <div class="modal-status"></div>
            <div class="modal-body text-center py-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon mb-2 text-danger icon-lg" width="24"
                    height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                    <path d="M12 9v2m0 4v.01" />
                    <path
                        d="M5 19h14a2 2 0 0 0 1.84 -2.75l-7.1 -12.25a2 2 0 0 0 -3.5 0l-7.1 12.25a2 2 0 0 0 1.75 2.75" />
                </svg>
                <h3>{{ __('Are you sure?') }}</h3>
                <div id="delete_status" class="text-secondary small"></div>
            </div>
            <div class="modal-footer">
                <div class="w-100">
                    <div class="row">
                        <div class="col">
                            <button type="button" class="btn w-100" data-bs-dismiss="modal">
                                {{ __('Cancel') }}
                            </button>
                        </div>
                        <div class="col">
                            <a class="btn btn-danger w-100" id="delete_id">
                                {{ __('Yes, proceed') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
