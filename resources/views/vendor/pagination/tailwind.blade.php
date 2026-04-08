<style>
    .page-link:focus {
        color: var(--tblr-pagination-focus-color);
        background-color: transparent !important;
        box-shadow: none !important;
        text-decoration: underline !important;
    }
</style>

@if ($paginator->hasPages())

    <div class="card" id="showPagination">
        <div class="card-footer d-flex align-items-center" id="paginationLinks">
            <p class="m-0 text-secondary">{{ __('Showing') }} <span>{{ $paginator->firstItem() }}</span>
                {{ __('to') }} <span>{{ $paginator->lastItem() }}</span> {{ __('of') }}
                <span>{{ $paginator->total() }}</span> {{ __('results') }}
            </p>
            <ul class="pagination m-0 ms-auto">
                {{-- Previous Page Link --}}
                @if ($paginator->onFirstPage())
                    <li class="page-item disabled" aria-disabled="true">
                        <a class="page-link" href="#">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="icon">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                <path d="M15 6l-6 6l6 6"></path>
                            </svg>
                            {{ __('Previous') }}
                        </a>
                    </li>
                @else
                    <li class="page-item">
                        <a class="page-link" href="{{ $paginator->previousPageUrl() }}">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="icon">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                <path d="M15 6l-6 6l6 6"></path>
                            </svg>
                            {{ __('Previous') }}
                        </a>
                    </li>
                @endif

                {{-- Next Page Link --}}
                @if ($paginator->hasMorePages())
                    <li class="page-item">
                        <a class="page-link" href="{{ $paginator->nextPageUrl() }}">
                            {{ __('Next') }}
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="icon">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                <path d="M9 6l6 6l-6 6"></path>
                            </svg>
                        </a>
                    </li>
                @else
                    <li class="page-item disabled" aria-disabled="true">
                        <a class="page-link" href="#">
                            {{ __('Next') }}
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="icon">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                <path d="M9 6l6 6l-6 6"></path>
                            </svg>
                        </a>
                    </li>
                @endif
            </ul>
        </div>
    </div>
@endif
