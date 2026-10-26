{{-- Modern Luxury Standalone Pagination Component --}}
@if ($paginator->hasPages())
    <style>
        .fashion-pagination-wrap {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            list-style: none;
            padding: 0;
            margin: 32px 0 16px 0;
            user-select: none;
        }

        .fashion-page-node {
            display: inline-flex;
        }

        .fashion-page-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 40px;
            height: 40px;
            padding: 0 10px;
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 0px;
            color: #0f172a;
            font-size: 13.5px;
            font-weight: 700;
            text-decoration: none !important;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .fashion-page-btn svg,
        .fashion-page-btn i {
            font-size: 16px;
            width: 16px;
            height: 16px;
            line-height: 1;
        }

        /* Hover States */
        .fashion-page-btn:hover {
            background-color: #0f172a;
            color: #ffffff;
            border-color: #0f172a;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.15);
        }

        /* Active Page Pill */
        .fashion-page-node.active .fashion-page-btn {
            background-color: #0f172a;
            color: #ffffff;
            border-color: #0f172a;
            box-shadow: 0 4px 14px rgba(15, 23, 42, 0.2);
            pointer-events: none;
        }

        /* Disabled State */
        .fashion-page-node.disabled .fashion-page-btn {
            color: #cbd5e1;
            pointer-events: none;
            background-color: #f8fafc;
            border-color: #f1f5f9;
            box-shadow: none;
            transform: none;
        }

        @media (max-width: 576px) {
            .fashion-pagination-wrap {
                gap: 4px;
            }

            .fashion-page-btn {
                min-width: 36px;
                height: 36px;
                font-size: 12.5px;
                padding: 0 6px;
            }
        }
    </style>

    <ul class="fashion-pagination-wrap">
        {{-- Previous Page Navigation --}}
        @if ($paginator->onFirstPage())
            <li class="fashion-page-node disabled">
                <span class="fashion-page-btn" aria-disabled="true" aria-label="{{ __('Previous Page') }}">
                    <i class="ti ti-chevron-left"></i>
                </span>
            </li>
        @else
            <li class="fashion-page-node">
                <a class="fashion-page-btn" href="{{ $paginator->previousPageUrl() }}" rel="prev"
                    aria-label="{{ __('Previous Page') }}">
                    <i class="ti ti-chevron-left"></i>
                </a>
            </li>
        @endif

        {{-- Pagination Elements & Numbers --}}
        @foreach ($elements as $element)
            {{-- "Three Dots" Separator --}}
            @if (is_string($element))
                <li class="fashion-page-node disabled">
                    <span class="fashion-page-btn">{{ $element }}</span>
                </li>
            @endif

            {{-- Array Of Links --}}
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <li class="fashion-page-node active" aria-current="page">
                            <span class="fashion-page-btn">{{ $page }}</span>
                        </li>
                    @else
                        <li class="fashion-page-node">
                            <a class="fashion-page-btn" href="{{ $url }}">{{ $page }}</a>
                        </li>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Next Page Navigation --}}
        @if ($paginator->hasMorePages())
            <li class="fashion-page-node">
                <a class="fashion-page-btn" href="{{ $paginator->nextPageUrl() }}" rel="next"
                    aria-label="{{ __('Next Page') }}">
                    <i class="ti ti-chevron-right"></i>
                </a>
            </li>
        @else
            <li class="fashion-page-node disabled">
                <span class="fashion-page-btn" aria-disabled="true" aria-label="{{ __('Next Page') }}">
                    <i class="ti ti-chevron-right"></i>
                </span>
            </li>
        @endif
    </ul>
@endif
