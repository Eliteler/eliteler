@extends('user.layouts.index', ['header' => true, 'nav' => true, 'demo' => true, 'settings' => $settings])

@php
    $isStore = $card->card_type == 'store';
    $kpis = $summary['kpis'];
    $breakdowns = $summary['breakdowns'];
    $periodLabels = [
        '7d' => __('Last 7 days'),
        '30d' => __('Last 30 days'),
        '90d' => __('Last 90 days'),
        '12m' => __('Last 12 months'),
    ];
    $kpiCards = [
        ['label' => __('Views'), 'value' => $kpis['views'], 'change' => $kpis['views_change']],
        ['label' => __('Unique visitors'), 'value' => $kpis['unique'], 'change' => $kpis['unique_change']],
        ['label' => __('Countries'), 'value' => $kpis['countries'], 'change' => null],
        ['label' => __('Today'), 'value' => $kpis['today'], 'change' => null],
    ];
    $breakdownCards = [
        'sources' => __('Traffic sources'),
        'countries' => __('Countries'),
        'cities' => __('Cities'),
        'device_types' => __('Devices'),
        'browsers' => __('Browsers'),
        'platforms' => __('Operating systems'),
        'referrers' => __('Referring sites'),
        'languages' => __('Languages'),
    ];
@endphp

{{-- Custom CSS (Forced responsiveness for horizontal scrolling and visible mobile navigation) --}}
@section('css')
    <style>
        .analytics-kpi .h1 {
            font-variant-numeric: tabular-nums;
        }

        @media (max-width: 767.98px) {

            /* Force standard DataTables elements to remain visible and center-aligned on mobile */
            .dataTables_wrapper .dataTables_paginate {
                display: flex !important;
                justify-content: center !important;
                float: none !important;
                margin-top: 1rem !important;
                width: 100% !important;
                padding: 0 0.5rem !important;
            }

            /* Resolve pagination overflow by enabling clean flex-wrapping and compact scale on small screens */
            .dataTables_wrapper .pagination {
                display: flex !important;
                flex-wrap: wrap !important;
                justify-content: center !important;
                padding-left: 0 !important;
                list-style: none !important;
                gap: 4px !important;
            }

            .dataTables_wrapper .pagination .page-item .page-link {
                padding: 4px 8px !important;
                font-size: 11px !important;
                border-radius: 4px !important;
            }

            .dataTables_wrapper .dataTables_info {
                display: block !important;
                float: none !important;
                text-align: center !important;
                margin-bottom: 0.5rem !important;
                font-size: 11px !important;
            }

            /* Smooth touch scrolling momentum specifically for the table */
            .table-responsive {
                overflow-x: auto !important;
                -webkit-overflow-scrolling: touch !important;
                border-bottom: 1px solid rgba(101, 109, 119, 0.16);
            }
        }
    </style>
@endsection

@section('content')
    <div class="page-wrapper">
        <!-- Page title header (Wraps the inner container-fluid) -->
        <div class="page-header d-print-none">
            <div class="container-fluid">
                <div class="row g-2 align-items-center">
                    <!-- Hierarchical Breadcrumb Trail -->
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="{{ route('user.dashboard') }}" class="text-secondary text-decoration-none">
                                    {{ __('Dashboard') }}
                                </a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="{{ route($back) }}" class="text-secondary text-decoration-none">
                                    {{ $isStore ? __('Stores') : __('Digital Business Cards') }}
                                </a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">
                                {{ __('Analytics') }}
                            </li>
                        </ol>
                    </nav>
                    <div class="col">
                        <h2 class="page-title">
                            {{ __('Analytics') }}: {{ $card->title }}
                        </h2>

                        <!-- Descriptive Help Subtitle -->
                        <div class="text-muted mt-1">
                            {{ __('Where your visitors come from, what they use, and how they found you.') }}
                        </div>
                    </div>

                    <!-- Period switcher -->
                    <div class="col-12 col-md-auto">
                        <div class="btn-group w-100" role="group" aria-label="{{ __('Period') }}">
                            @foreach ($periodLabels as $key => $label)
                                <a href="{{ route('user.visitors', ['id' => $card->card_id, 'period' => $key]) }}"
                                    class="btn {{ $summary['period'] == $key ? 'btn-primary' : '' }}"
                                    @if ($summary['period'] == $key) aria-current="page" @endif>{{ $label }}</a>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="page-body">
            <div class="container-fluid">
                {{-- Failed Alert --}}
                @if (Session::has('failed'))
                    <div class="alert alert-important alert-danger alert-dismissible mb-3 border-0 shadow-sm"
                        role="alert">
                        <div class="d-flex">
                            <div>{{ Session::get('failed') }}</div>
                        </div>
                        <a class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="close"></a>
                    </div>
                @endif

                {{-- KPIs --}}
                <div class="row row-cards mb-3">
                    @foreach ($kpiCards as $kpi)
                        <div class="col-6 col-lg-3">
                            <div class="card border-0 shadow-sm analytics-kpi h-100">
                                <div class="card-body">
                                    <div class="subheader">{{ $kpi['label'] }}</div>
                                    <div class="d-flex align-items-baseline flex-wrap gap-2">
                                        <div class="h1 mb-0">{{ number_format($kpi['value']) }}</div>
                                        @if ($kpi['change'] !== null)
                                            <span
                                                class="badge {{ $kpi['change'] >= 0 ? 'bg-success-lt' : 'bg-danger-lt' }}"
                                                title="{{ __('Compared with the previous period') }}">
                                                {{ $kpi['change'] >= 0 ? '+' : '' }}{{ $kpi['change'] }}%
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Visits over time --}}
                <div class="row row-cards mb-3">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header border-0 pb-0">
                                <h3 class="card-title">{{ __('Visits over time') }}</h3>
                                <div class="card-actions text-muted small">
                                    {{ __(':count all time', ['count' => number_format($kpis['all_time'])]) }}
                                </div>
                            </div>
                            <div class="card-body">
                                <div id="visitsChart" style="min-height: 280px;"></div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Breakdowns: ranked bars (magnitude), one hue --}}
                <div class="row row-cards mb-3">
                    @foreach ($breakdownCards as $key => $breakdownTitle)
                        <div class="col-md-6 col-xl-4">
                            @include('user.pages.cards.includes.analytics-breakdown', ['title' => $breakdownTitle, 'rows' => $breakdowns[$key], 'flags' => $key == 'countries'])
                        </div>
                    @endforeach
                </div>

                {{-- Recent visits --}}
                <div class="row row-deck row-cards">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm p-3">
                            <h3 class="card-title px-2 pt-1">{{ __('Recent visits') }}</h3>
                            <table class="table card-table table-vcenter text-nowrap" id="visitorsTable">
                                <thead>
                                    <tr>
                                        <th>{{ __('#') }}</th>
                                        <th>{{ __('Date') }}</th>
                                        <th>{{ __('Location') }}</th>
                                        <th>{{ __('Source') }}</th>
                                        <th>{{ __('Browser') }}</th>
                                        <th>{{ __('Platform') }}</th>
                                        <th>{{ __('Device') }}</th>
                                        <th>{{ __('Language') }}</th>
                                        <th>{{ __('IP') }}</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @include('user.includes.footer')
    </div>
@endsection

@section('scripts')
    <script src="{{ asset('js/apexcharts.min.js') }}"></script>
    <script type="text/javascript">
        "use strict";

        const analytics = @json($summary);
        // Views / unique visitors, checked for colour-blind separation and contrast on each theme's card surface
        const dark = document.body.getAttribute('data-bs-theme') === 'dark' || document.body.classList.contains('theme-dark');
        const seriesColors = dark ? ["#427ff7", "#00aa72"] : ["#206bc4", "#1e9438"];

        document.addEventListener("DOMContentLoaded", function() {
            const monthly = analytics.period === '12m';
            const label = (date) => new Date(monthly ? `${date}-01T00:00:00` : `${date}T00:00:00`)
                .toLocaleDateString(undefined, monthly ? {
                    month: 'short',
                    year: '2-digit'
                } : {
                    month: 'short',
                    day: 'numeric'
                });

            window.ApexCharts && new ApexCharts(document.getElementById('visitsChart'), {
                chart: {
                    type: "line",
                    fontFamily: 'inherit',
                    height: 280,
                    background: 'transparent',
                    parentHeightOffset: 0,
                    toolbar: {
                        show: false
                    },
                },
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    width: 2,
                    curve: "smooth"
                },
                fill: {
                    type: ["gradient", "solid"],
                    gradient: {
                        opacityFrom: 0.25,
                        opacityTo: 0.02
                    }
                },
                // Views as an area, unique visitors as a line, on one shared axis
                series: [{
                    name: `{{ __('Views') }}`,
                    type: 'area',
                    data: analytics.series.map((point) => point.views)
                }, {
                    name: `{{ __('Unique visitors') }}`,
                    type: 'line',
                    data: analytics.series.map((point) => point.unique)
                }],
                xaxis: {
                    categories: analytics.series.map((point) => label(point.date)),
                    tickAmount: Math.min(analytics.series.length, 10),
                    labels: {
                        rotate: 0,
                        hideOverlappingLabels: true
                    },
                    axisBorder: {
                        show: false
                    },
                },
                yaxis: {
                    min: 0,
                    forceNiceScale: true,
                    labels: {
                        formatter: (value) => Math.round(value)
                    }
                },
                grid: {
                    strokeDashArray: 4
                },
                colors: seriesColors,
                theme: {
                    mode: dark ? 'dark' : 'light'
                },
                tooltip: {
                    theme: dark ? 'dark' : 'light'
                },
                legend: {
                    position: 'top',
                    horizontalAlign: 'right'
                },
            }).render();

        });

        $(document).ready(function() {
            const totalColumns = 9;
            const placeholders = () => {
                let rows = '';
                for (let i = 0; i < 5; i++) {
                    rows += '<tr>' + '<td class="text-center"><div class="placeholder placeholder-xs col-12"></div></td>'
                        .repeat(totalColumns) + '</tr>';
                }
                $('#visitorsTable tbody').html(rows);
            };

            const table = $('#visitorsTable').DataTable({
                processing: false,
                serverSide: true,
                order: [],
                ajax: "{{ route('user.visitors.data', $card->card_id) }}",
                language: {
                    "sProcessing": `{{ __('Processing...') }}`,
                    "sLengthMenu": `{{ __('Show _MENU_ entries') }}`,
                    "sSearch": ``,
                    "sSearchPlaceholder": `{{ __('Search') }}`,
                    "oPaginate": {
                        "sNext": `{{ __('Next') }}`,
                        "sPrevious": `{{ __('Previous') }}`
                    },
                    "sInfo": `{{ __('Showing _START_ to _END_ of _TOTAL_ entries') }}`,
                    "sInfoEmpty": `{{ __('Showing 0 to 0 of 0 entries') }}`,
                    "sInfoFiltered": `{{ __('(filtered from _MAX_ total entries)') }}`,
                    loadingRecords: `{{ __('Please wait - loading...') }}`,
                    emptyTable: `{{ __('No data available in the table') }}`
                },
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'created_at',
                        name: 'created_at',
                        searchable: false
                    },
                    {
                        data: 'location',
                        name: 'location',
                        orderable: false
                    },
                    {
                        data: 'source',
                        name: 'source'
                    },
                    {
                        data: 'browser',
                        name: 'browser'
                    },
                    {
                        data: 'platform',
                        name: 'platform'
                    },
                    {
                        data: 'device_type',
                        name: 'device_type'
                    },
                    {
                        data: 'language',
                        name: 'language'
                    },
                    {
                        data: 'ip_address',
                        name: 'ip_address'
                    }
                ],
                preDrawCallback: function() {
                    $('#visitorsTable_wrapper').addClass('placeholder-glow');
                    placeholders();
                },
                drawCallback: function() {
                    $('#visitorsTable_wrapper').removeClass('placeholder-glow');

                    // Aligns column dimensions correctly for empty results
                    $('#visitorsTable tbody tr td.dataTables_empty').attr('colspan', totalColumns);
                }
            });

            // Wrap ONLY the table element in the responsive wrapper container dynamically
            $('#visitorsTable').wrap('<div class="table-responsive px-0 py-2"></div>');

            table.on('processing.dt', function(e, settings, processing) {
                if (processing) {
                    $('#visitorsTable_wrapper').addClass('placeholder-glow');
                    placeholders();
                } else {
                    $('#visitorsTable_wrapper').removeClass('placeholder-glow');
                }
            });
        });
    </script>
@endsection
