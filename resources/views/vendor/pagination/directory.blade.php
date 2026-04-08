@php
    use Illuminate\Support\Facades\DB;

    $config = DB::table('config')->get();
    $template_config = DB::table('gobiz_original_config')->first();

    $colors = [
        'slate' => '100,116,139',
        'gray' => '107,114,128',
        'zinc' => '113,113,122',
        'neutral' => '115,115,115',
        'stone' => '120,113,108',

        'red' => '239,68,68',
        'orange' => '249,115,22',
        'amber' => '245,158,11',
        'yellow' => '234,179,8',
        'lime' => '132,204,22',
        'green' => '34,197,94',
        'emerald' => '16,185,129',
        'teal' => '20,184,166',
        'cyan' => '6,182,212',
        'sky' => '14,165,233',
        'blue' => '59,130,246',
        'indigo' => '99,102,241',
        'violet' => '139,92,246',
        'purple' => '168,85,247',
        'fuchsia' => '217,70,239',
        'pink' => '236,72,153',
        'rose' => '244,63,94',
    ];

    $rgb = $colors[$template_config->template_color] ?? '30,64,175';
@endphp

<style>
    .pagination-wrapper {
        display: flex;
        justify-content: center;
        padding: 40px 0;
    }

    .pagination {
        display: flex;
        align-items: center;
        gap: 6px;
        font-family: system-ui, sans-serif;
    }

    .page {
        padding: 8px 14px;
        border-radius: 8px;
        background: #f3f4f6;
        color: #374151;
        text-decoration: none;
        font-size: 14px;
        transition: all 0.2s ease;
    }

    .page:hover {
        background: #e5e7eb;
    }

    .page.active {
        background: rgba({{ $rgb }});
        /* change to your theme color */
        color: #fff;
        font-weight: 600;
    }

    .page.disabled {
        color: #9ca3af;
        background: #f3f4f6;
        cursor: not-allowed;
    }

    .dots {
        padding: 0 6px;
        color: #9ca3af;
        font-size: 14px;
    }
</style>

@if ($paginator->hasPages())
    <div class="pagination-wrapper">
        <nav class="pagination">

            {{-- Previous --}}
            @if ($paginator->onFirstPage())
                <span class="page disabled">{{ __('Prev') }}</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="page">{{ __('Prev') }}</a>
            @endif

            @php
                $current = $paginator->currentPage();
                $last = $paginator->lastPage();
            @endphp

            {{-- Pages --}}
            @for ($page = 1; $page <= $last; $page++)
                @if ($page == 1 || $page == $last || ($page >= $current - 1 && $page <= $current + 1))
                    @if ($page == $current)
                        <span class="page active">{{ $page }}</span>
                    @else
                        <a href="{{ $paginator->url($page) }}" class="page">{{ $page }}</a>
                    @endif
                @elseif (($page == 2 && $current > 3) || ($page == $last - 1 && $current < $last - 2))
                    <span class="dots">...</span>
                @endif
            @endfor

            {{-- Next --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="page">{{ __('Next') }}</a>
            @else
                <span class="page disabled">{{ __('Next') }}</span>
            @endif

        </nav>
    </div>
@endif
