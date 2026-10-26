@if ($paginator->hasPages())
    <style>
        /* Luxury Pill Pagination Container */
        .luxury-pagination {
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 8px !important;
            list-style: none !important;
            padding: 0 !important;
            margin: 0 !important;
        }

        /* Pagination Item Base (True Pill/Circle Geometry) */
        .luxury-page-item a,
        .luxury-page-item span {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            min-width: 40px !important;
            height: 40px !important;
            padding: 0 14px !important;
            border-radius: 9999px !important;
            font-size: 13px !important;
            font-weight: 700 !important;
            color: #1e293b !important;
            background-color: #ffffff !important;
            border: 1.5px solid #ede8df !important;
            text-decoration: none !important;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.03) !important;
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1) !important;
        }

        /* Hover State */
        .luxury-page-item a:hover {
            border-color: #cca47a !important;
            background-color: #f5ece1 !important;
            color: #1f4b48 !important;
            transform: translateY(-2px) !important;
            box-shadow: 0 4px 12px rgba(204, 164, 122, 0.25) !important;
        }

        /* Active Page Pill (Deep Forest Teal) */
        .luxury-page-item.active span {
            background-color: #1f4b48 !important;
            border-color: #1f4b48 !important;
            color: #ffffff !important;
            box-shadow: 0 4px 14px rgba(31, 75, 72, 0.35) !important;
            cursor: default !important;
        }

        /* Disabled Chevron/Ellipsis */
        .luxury-page-item.disabled span {
            color: #cbd5e1 !important;
            background-color: #f8fafc !important;
            border-color: #f1f5f9 !important;
            box-shadow: none !important;
            cursor: not-allowed !important;
        }

        .luxury-page-item i {
            font-size: 16px !important;
            line-height: 1 !important;
        }
    </style>

    <ul class="luxury-pagination" role="navigation" aria-label="Pagination Navigation">
        {{-- Previous Page Link --}}
        @if ($paginator->onFirstPage())
            <li class="luxury-page-item disabled" aria-disabled="true" aria-label="{{ __('Previous') }}">
                <span><i class="ti ti-chevron-left"></i></span>
            </li>
        @else
            <li class="luxury-page-item">
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="{{ __('Previous') }}">
                    <i class="ti ti-chevron-left"></i>
                </a>
            </li>
        @endif

        {{-- Pagination Elements Array --}}
        @foreach ($elements as $element)
            {{-- "Three Dots" Separator --}}
            @if (is_string($element))
                <li class="luxury-page-item disabled" aria-disabled="true">
                    <span>{{ $element }}</span>
                </li>
            @endif

            {{-- Array of Links --}}
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <li class="luxury-page-item active" aria-current="page">
                            <span>{{ $page }}</span>
                        </li>
                    @else
                        <li class="luxury-page-item">
                            <a href="{{ $url }}">{{ $page }}</a>
                        </li>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Next Page Link --}}
        @if ($paginator->hasMorePages())
            <li class="luxury-page-item">
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="{{ __('Next') }}">
                    <i class="ti ti-chevron-right"></i>
                </a>
            </li>
        @else
            <li class="luxury-page-item disabled" aria-disabled="true" aria-label="{{ __('Next') }}">
                <span><i class="ti ti-chevron-right"></i></span>
            </li>
        @endif
    </ul>
@endif
