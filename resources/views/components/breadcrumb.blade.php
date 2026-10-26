<div class="mb-1 ms-0">
    <ol class="breadcrumb" aria-label="breadcrumbs">
        @foreach ($items as $item)
            <li class="breadcrumb-item {{ $loop->last ? 'active' : '' }}"
                @if ($loop->last) aria-current="page" @endif>

                @if (!$loop->last)
                    <a href="{{ $item['url'] ?? route($item['route'], $item['params'] ?? []) }}"
                        class="text-secondary text-decoration-none">
                        {{ __($item['title']) }}
                    </a>
                @else
                    <a href="#">
                        {{ __($item['title']) }}
                    </a>
                @endif
            </li>
        @endforeach
    </ol>
</div>
