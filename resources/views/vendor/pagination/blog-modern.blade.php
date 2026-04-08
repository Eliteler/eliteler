@if ($paginator->hasPages())
    <div class="pagination">

        {{-- Previous --}}
        @if ($paginator->onFirstPage())
            <a class="page-link disabled" href="#">
                <span>{{ __('Previous') }}</span>
            </a>
        @else
            <a class="page-link" href="{{ $paginator->previousPageUrl() }}">
                {{ __('Previous') }}
            </a>
        @endif

        @php
            $current = $paginator->currentPage();
            $last = $paginator->lastPage();
            $start = max(1, $current - 1);
            $end = min($last, $current + 1);
        @endphp

        {{-- Pages: prev current next --}}
        @for ($page = $start; $page <= $end; $page++)
            @if ($page == $current)
                <a class="page-link active">{{ $page }}</a>
            @else
                <a class="page-link" href="{{ $paginator->url($page) }}">{{ $page }}</a>
            @endif
        @endfor

        {{-- Next --}}
        @if ($paginator->hasMorePages())
            <a class="page-link" href="{{ $paginator->nextPageUrl() }}">
                {{ __('Next') }}
            </a>
        @else
            <a class="page-link disabled" href="#">
                <span>{{ __('Next') }}</span>
            </a>
        @endif

    </div>
@endif
