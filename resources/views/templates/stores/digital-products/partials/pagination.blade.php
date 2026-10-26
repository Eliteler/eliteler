{{-- Responsive Pagination Partial --}}
@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}"
        class="flex items-center justify-center gap-1.5 sm:gap-2 flex-wrap select-none">

        {{-- Previous Page Link --}}
        @if ($paginator->onFirstPage())
            <span
                class="h-10 px-3.5 sm:px-4 rounded-xl text-xs font-semibold inline-flex items-center gap-1.5 shrink-0 whitespace-nowrap border border-slate-200/50 dark:border-slate-800/60 text-slate-400/40 opacity-40 cursor-not-allowed bg-transparent">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M15 19l-7-7 7-7"></path>
                </svg>
                <span class="hidden sm:inline">{{ __('Previous') }}</span>
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev"
                class="h-10 px-3.5 sm:px-4 rounded-xl text-xs font-semibold inline-flex items-center gap-1.5 shrink-0 whitespace-nowrap border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-200 bg-white dark:bg-[#111625] hover:bg-slate-50 dark:hover:bg-slate-800 hover:border-indigo-500/50 shadow-sm transition">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M15 19l-7-7 7-7"></path>
                </svg>
                <span class="hidden sm:inline">{{ __('Previous') }}</span>
            </a>
        @endif

        {{-- Pagination Elements --}}
        @foreach ($elements as $element)
            {{-- "Three Dots" Separator --}}
            @if (is_string($element))
                <span
                    class="h-10 min-w-[40px] px-2 rounded-xl text-xs font-bold inline-flex items-center justify-center text-slate-400 dark:text-slate-600">
                    {{ $element }}
                </span>
            @endif

            {{-- Array Of Page Number Links --}}
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        {{-- Active Page Button --}}
                        <span aria-current="page"
                            class="h-10 min-w-[40px] px-3 rounded-xl text-xs font-bold inline-flex items-center justify-center border border-indigo-600 bg-indigo-600 text-white shadow-md shadow-indigo-600/30">
                            {{ $page }}
                        </span>
                    @else
                        {{-- Inactive Page Button --}}
                        <a href="{{ $url }}"
                            class="h-10 min-w-[40px] px-3 rounded-xl text-xs font-bold inline-flex items-center justify-center border border-slate-200 dark:border-slate-800 bg-white dark:bg-[#111625] text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 hover:border-indigo-500/50 hover:text-slate-900 dark:hover:text-white shadow-sm transition">
                            {{ $page }}
                        </a>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Next Page Link --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next"
                class="h-10 px-3.5 sm:px-4 rounded-xl text-xs font-semibold inline-flex items-center gap-1.5 shrink-0 whitespace-nowrap border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-200 bg-white dark:bg-[#111625] hover:bg-slate-50 dark:hover:bg-slate-800 hover:border-indigo-500/50 shadow-sm transition">
                <span class="hidden sm:inline">{{ __('Next') }}</span>
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M9 5l7 7-7 7"></path>
                </svg>
            </a>
        @else
            <span
                class="h-10 px-3.5 sm:px-4 rounded-xl text-xs font-semibold inline-flex items-center gap-1.5 shrink-0 whitespace-nowrap border border-slate-200/50 dark:border-slate-800/60 text-slate-400/40 opacity-40 cursor-not-allowed bg-transparent">
                <span class="hidden sm:inline">{{ __('Next') }}</span>
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M9 5l7 7-7 7"></path>
                </svg>
            </span>
        @endif

    </nav>
@endif
