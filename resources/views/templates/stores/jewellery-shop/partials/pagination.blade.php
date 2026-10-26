{{-- Ultra-Luxury Redesigned Pagination Component --}}
@if ($paginator->hasPages())
    <nav class="luxury-pagination-nav" role="navigation" aria-label="Pagination Navigation">
        <!-- Floating Dock Container -->
        <div class="luxury-pagination-dock">
            <ul class="luxury-pagination-list">
                {{-- Previous Page Link --}}
                @if ($paginator->onFirstPage())
                    <li class="page-step disabled" aria-disabled="true" aria-label="{{ __('Previous') }}">
                        <span class="page-circle-btn page-step-nav-btn">
                            <i class="ti ti-chevron-left"></i>
                        </span>
                    </li>
                @else
                    <li class="page-step">
                        <a href="{{ $paginator->previousPageUrl() }}" rel="prev"
                            class="page-circle-btn page-step-nav-btn page-nav-prev" aria-label="{{ __('Previous') }}">
                            <i class="ti ti-chevron-left"></i>
                        </a>
                    </li>
                @endif

                {{-- Pagination Elements --}}
                @foreach ($elements as $element)
                    {{-- Three Dots Separator --}}
                    @if (is_string($element))
                        <li class="page-step disabled" aria-disabled="true">
                            <span class="page-circle-btn page-dots-btn">{{ $element }}</span>
                        </li>
                    @endif

                    {{-- Array Of Page Numbers --}}
                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <li class="page-step active" aria-current="page">
                                    <span class="page-circle-btn page-num-btn">{{ $page }}</span>
                                </li>
                            @else
                                <li class="page-step">
                                    <a href="{{ $url }}" class="page-circle-btn page-num-btn">
                                        {{ $page }}
                                    </a>
                                </li>
                            @endif
                        @endforeach
                    @endif
                @endforeach

                {{-- Next Page Link --}}
                @if ($paginator->hasMorePages())
                    <li class="page-step">
                        <a href="{{ $paginator->nextPageUrl() }}" rel="next"
                            class="page-circle-btn page-step-nav-btn page-nav-next" aria-label="{{ __('Next') }}">
                            <i class="ti ti-chevron-right"></i>
                        </a>
                    </li>
                @else
                    <li class="page-step disabled" aria-disabled="true" aria-label="{{ __('Next') }}">
                        <span class="page-circle-btn page-step-nav-btn">
                            <i class="ti ti-chevron-right"></i>
                        </span>
                    </li>
                @endif
            </ul>
        </div>
    </nav>

    <style>
        /* Base Navigation Alignment */
        .luxury-pagination-nav {
            margin: 40px 0 20px 0;
            display: flex;
            justify-content: center;
            align-items: center;
            width: 100%;
        }

        /* Floating Luxury Dock Wrapper */
        .luxury-pagination-dock {
            background: #ffffff;
            border: 1.5px solid rgba(212, 175, 55, 0.35);
            border-radius: 50px !important;
            padding: 6px 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.04), 0 2px 12px rgba(212, 175, 55, 0.1);
            display: inline-flex;
            align-items: center;
            transition: all 0.3s ease;
        }

        .luxury-pagination-dock:hover {
            box-shadow: 0 14px 35px rgba(0, 0, 0, 0.06), 0 4px 18px rgba(212, 175, 55, 0.18);
            border-color: rgba(212, 175, 55, 0.5);
        }

        /* Horizontal List Container */
        .luxury-pagination-list {
            display: flex;
            align-items: center;
            gap: 6px;
            list-style: none;
            padding: 0;
            margin: 0;
        }

        /* Standard 40px Circular Button */
        .page-circle-btn {
            width: 40px;
            height: 40px;
            border-radius: 50% !important;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 13.5px;
            font-weight: 700;
            color: #4b3e2b !important;
            background: transparent;
            border: 1px solid transparent;
            text-decoration: none !important;
            transition: all 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);
            cursor: pointer;
            user-select: none;
            line-height: 1;
        }

        /* Hover States for Inactive Numbers */
        .page-step:not(.disabled):not(.active) .page-circle-btn:hover {
            background: #fbf7ee;
            color: #b89028 !important;
            border-color: rgba(212, 175, 55, 0.4);
            transform: translateY(-2px) scale(1.05);
        }

        /* Chevrons Hover Movement */
        .page-step:not(.disabled) .page-nav-prev:hover i {
            transform: translateX(-2px);
        }

        .page-step:not(.disabled) .page-nav-next:hover i {
            transform: translateX(2px);
        }

        .page-step-nav-btn i {
            font-size: 16px;
            transition: transform 0.2s ease;
        }

        /* Active Glowing Page Number */
        .page-step.active .page-circle-btn {
            background: linear-gradient(135deg, #d4af37 0%, #b89028 100%) !important;
            color: #ffffff !important;
            border-color: #d4af37 !important;
            box-shadow: 0 4px 16px rgba(212, 175, 55, 0.45);
            transform: scale(1.06);
            cursor: default;
        }

        /* Three Dots Button */
        .page-dots-btn {
            color: #8c733e !important;
            font-weight: 800;
            letter-spacing: 1px;
            cursor: default;
        }

        /* Disabled Chevron States */
        .page-step.disabled .page-circle-btn {
            opacity: 0.35;
            cursor: not-allowed;
            color: #9c8e71 !important;
        }

        /* Mobile Screen Optimization */
        @media (max-width: 576px) {
            .luxury-pagination-dock {
                padding: 4px 6px;
            }

            .page-circle-btn {
                width: 34px;
                height: 34px;
                font-size: 12px;
            }

            .luxury-pagination-list {
                gap: 4px;
            }
        }
    </style>
@endif
