@if ($paginator->hasPages())
    <nav class="d-flex justify-content-center my-4" aria-label="Page navigation">
        <ul class="store-pagination-list">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <li class="page-pill-item disabled">
                    <span class="page-pill-link"><i class="ti ti-chevron-left"></i></span>
                </li>
            @else
                <li class="page-pill-item">
                    <a class="page-pill-link" href="{{ $paginator->previousPageUrl() }}" rel="prev"><i
                            class="ti ti-chevron-left"></i></a>
                </li>
            @endif

            {{-- Pagination Elements --}}
            @foreach ($elements as $element)
                {{-- Three Dots Separator --}}
                @if (is_string($element))
                    <li class="page-pill-item disabled"><span class="page-pill-link">{{ $element }}</span></li>
                @endif

                {{-- Array Of Links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li class="page-pill-item active">
                                <span class="page-pill-link bg-e-commerce-blue text-white">{{ $page }}</span>
                            </li>
                        @else
                            <li class="page-pill-item">
                                <a class="page-pill-link" href="{{ $url }}">{{ $page }}</a>
                            </li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <li class="page-pill-item">
                    <a class="page-pill-link" href="{{ $paginator->nextPageUrl() }}" rel="next"><i
                            class="ti ti-chevron-right"></i></a>
                </li>
            @else
                <li class="page-pill-item disabled">
                    <span class="page-pill-link"><i class="ti ti-chevron-right"></i></span>
                </li>
            @endif
        </ul>
    </nav>
@endif

<style>
    .store-pagination-list {
        display: flex;
        list-style: none;
        padding: 0;
        margin: 0;
        gap: 6px;
        align-items: center;
    }

    .page-pill-item {
        display: inline-block;
    }

    .page-pill-link {
        min-width: 38px;
        height: 38px;
        padding: 0 12px;
        border-radius: 20px;
        border: 1px solid #dee2e6;
        background-color: #ffffff;
        color: #1e293b;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        font-weight: 600;
        font-size: 0.9rem;
        transition: all 0.2s ease;
    }

    .page-pill-link:hover {
        background-color: #f1f5f9;
        color: #0f172a;
    }

    .page-pill-item.active .page-pill-link {
        border-color: transparent;
    }

    .page-pill-item.disabled .page-pill-link {
        opacity: 0.45;
        cursor: not-allowed;
        background-color: #f8fafc;
    }
</style>
