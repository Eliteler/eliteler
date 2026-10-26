{{-- Luxury 40px Circular Pagination Engine --}}
@if ($paginator->hasPages())
    <nav class="store-pagination-wrapper">
        <ul class="store-pagination-list">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <li class="store-page-item disabled" aria-disabled="true">
                    <span class="store-page-btn"><i class="ti ti-chevron-left"></i></span>
                </li>
            @else
                <li class="store-page-item">
                    <a class="store-page-btn" href="{{ $paginator->previousPageUrl() }}" rel="prev"><i
                            class="ti ti-chevron-left"></i></a>
                </li>
            @endif

            {{-- Pagination Elements --}}
            @foreach ($elements as $element)
                {{-- Array of links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li class="store-page-item active" aria-current="page">
                                <span class="store-page-btn">{{ $page }}</span>
                            </li>
                        @else
                            <li class="store-page-item">
                                <a class="store-page-btn" href="{{ $url }}">{{ $page }}</a>
                            </li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <li class="store-page-item">
                    <a class="store-page-btn" href="{{ $paginator->nextPageUrl() }}" rel="next"><i
                            class="ti ti-chevron-right"></i></a>
                </li>
            @else
                <li class="store-page-item disabled" aria-disabled="true">
                    <span class="store-page-btn"><i class="ti ti-chevron-right"></i></span>
                </li>
            @endif
        </ul>
    </nav>

    <style>
        /* Scoped Pagination Styles */
        .store-pagination-wrapper {
            display: flex;
            justify-content: center;
            margin: 35px 0 15px 0;
            width: 100%;
        }

        .store-pagination-list {
            display: inline-flex;
            align-items: center;
            gap: 8px; 
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .store-page-btn {
            width: 40px;
            height: 40px;
            border-radius: 50% !important;
            background-color: #ffffff;
            border: 1px solid #c8e6c9;
            color: #1b5e20;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.2s ease;
        }

        .store-page-item.active .store-page-btn {
            background-color: #2e7d32 !important;
            color: #ffffff !important;
            border-color: #2e7d32 !important;
            box-shadow: 0 4px 10px rgba(46, 125, 50, 0.25);
        }

        .store-page-item:not(.active):not(.disabled) .store-page-btn:hover {
            background-color: #e8f5e9;
            color: #0a3d0c;
        }

        .store-page-item.disabled .store-page-btn {
            opacity: 0.4;
            pointer-events: none;
        }
    </style>
@endif
