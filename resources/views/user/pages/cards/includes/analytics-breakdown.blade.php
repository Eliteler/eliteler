{{-- Top values with a share bar. $title, $rows (label, value, share, optional code), $flags (bool) --}}
<div class="card border-0 shadow-sm h-100">
    <div class="card-header border-0 pb-0">
        <h3 class="card-title">{{ $title }}</h3>
    </div>
    <div class="card-body">
        @forelse ($rows as $row)
            <div class="mb-3">
                <div class="d-flex justify-content-between small mb-1">
                    <span class="text-truncate me-2">
                        @if (!empty($flags) && !empty($row['code']))
                            <span class="me-1">{{ country_flag($row['code']) }}</span>
                        @endif
                        {{ __($row['label']) }}
                    </span>
                    <span class="text-muted text-nowrap">{{ number_format($row['value']) }}
                        <span class="ms-1">({{ $row['share'] }}%)</span></span>
                </div>
                <div class="progress progress-sm">
                    <div class="progress-bar bg-primary" style="width: {{ $row['share'] }}%" role="progressbar"
                        aria-valuenow="{{ $row['share'] }}" aria-valuemin="0" aria-valuemax="100"
                        aria-label="{{ $row['share'] }}%"></div>
                </div>
            </div>
        @empty
            <div class="text-muted small">{{ __('No visits in this period.') }}</div>
        @endforelse
    </div>
</div>
