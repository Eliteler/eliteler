<style>
    /* Scoped Ultra-Luxury Pagination Engine */
    .store-pagination-wrapper {
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 2.5rem 0 1.5rem;
        width: 100%;
    }

    .store-pagination-container {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        list-style: none;
        padding: 6px 10px;
        margin: 0;
        background: #ffffff;
        border: 1.5px solid #f1f5f9;
        border-radius: 50px !important;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
    }

    .store-page-item {
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .store-page-btn {
        width: 40px;
        height: 40px;
        border-radius: 50% !important;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.9rem;
        font-weight: 700;
        color: #1c1917;
        background: #ffffff;
        border: 1px solid transparent;
        transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        text-decoration: none !important;
        cursor: pointer;
    }

    .store-page-btn:hover {
        background: #fef2f2;
        color: #dc2626;
        border-color: #fca5a5;
        transform: translateY(-2px);
    }

    .store-page-btn.active {
        background: #dc2626 !important;
        color: #ffffff !important;
        border-color: #dc2626 !important;
        box-shadow: 0 6px 18px rgba(220, 38, 38, 0.35);
        transform: scale(1.05);
        pointer-events: none;
    }

    .store-page-btn.disabled {
        opacity: 0.35;
        pointer-events: none;
        background: transparent;
        cursor: not-allowed;
    }

    .store-page-dots {
        width: 32px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.9rem;
        font-weight: 800;
        color: #94a3b8;
        letter-spacing: 2px;
        user-select: none;
    }
</style>

@if ($paginator->hasPages())
    <div class="store-pagination-wrapper">
        <ul class="store-pagination-container">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <li class="store-page-item">
                    <span class="store-page-btn disabled" aria-disabled="true">
                        <i class="ti ti-chevron-left"></i>
                    </span>
                </li>
            @else
                <li class="store-page-item">
                    <a href="{{ $paginator->previousPageUrl() }}" class="store-page-btn" rel="prev"
                        aria-label="{{ __('Previous') }}">
                        <i class="ti ti-chevron-left"></i>
                    </a>
                </li>
            @endif

            {{-- Pagination Elements --}}
            @foreach ($elements as $element)
                {{-- Three Dots Separator --}}
                @if (is_string($element))
                    <li class="store-page-item">
                        <span class="store-page-dots">{{ $element }}</span>
                    </li>
                @endif

                {{-- Array Of Links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li class="store-page-item">
                                <span class="store-page-btn active" aria-current="page">{{ $page }}</span>
                            </li>
                        @else
                            <li class="store-page-item">
                                <a href="{{ $url }}" class="store-page-btn">{{ $page }}</a>
                            </li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <li class="store-page-item">
                    <a href="{{ $paginator->nextPageUrl() }}" class="store-page-btn" rel="next"
                        aria-label="{{ __('Next') }}">
                        <i class="ti ti-chevron-right"></i>
                    </a>
                </li>
            @else
                <li class="store-page-item">
                    <span class="store-page-btn disabled" aria-disabled="true">
                        <i class="ti ti-chevron-right"></i>
                    </span>
                </li>
            @endif
        </ul>
    </div>
@endif
