
@if ($paginator->hasPages())
    <nav class="d-flex justify-items-center justify-content-between">
        <div class="flex-fill d-flex align-items-center justify-content-end">
            <div>
                <ul class="pagination">
                    {{-- Previous Page Link --}}
                    @if ($paginator->onFirstPage())
                        <li class="page-item disabled" aria-disabled="true" aria-label="@lang('pagination.previous')">
                            <span class="page-link" aria-hidden="true">&lsaquo;</span>
                        </li>
                    @else
                        <li class="page-item">
                            <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev"
                                aria-label="@lang('pagination.previous')">&lsaquo;</a>
                        </li>
                    @endif

                    {{-- Pagination Elements --}}
                    {{-- Page Numbers --}}
                    @php
                        $current = $paginator->currentPage();
                        $last = $paginator->lastPage();
                        $ellipsisRendered = false;
                    @endphp
                    @foreach ($elements as $element)
                        @if (is_array($element))
                            @foreach ($element as $page => $url)
                                @php
                                    $showPage = false;

                                    // Always show first 3
                                    if ($page <= 3) {
                                        $showPage = true;
                                    }

                                    // Always show last 3
                                    if ($page > $last - 3) {
                                        $showPage = true;
                                    }

                                    // Show nearby current page range (3 before and 3 after)
                                    if (abs($page - $current) <= 2) {
                                        $showPage = true;
                                    }
                                @endphp

                                @if ($showPage)
                                    @php $ellipsisRendered = false; @endphp
                                    @if ($page == $current)
                                        <li class="page-item active"><span class="page-link">{{ $page }}</span>
                                        </li>
                                    @else
                                        <li class="page-item"><a class="page-link"
                                                href="{{ $url }}">{{ $page }}</a></li>
                                    @endif
                                @elseif (!$ellipsisRendered)
                                    <li class="page-item disabled"><span class="page-link">...</span></li>
                                    @php $ellipsisRendered = true; @endphp
                                @endif
                            @endforeach
                        @endif
                    @endforeach

                    {{-- Next Page Link --}}
                    @if ($paginator->hasMorePages())
                        <li class="page-item">
                            <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next"
                                aria-label="@lang('pagination.next')">&rsaquo;</a>
                        </li>
                    @else
                        <li class="page-item disabled" aria-disabled="true" aria-label="@lang('pagination.next')">
                            <span class="page-link" aria-hidden="true">&rsaquo;</span>
                        </li>
                    @endif
                </ul>
            </div>
        </div>
    </nav>
@endif
