@extends('admin.layouts.index', ['header' => true, 'nav' => true, 'demo' => true])

{{-- Custom CSS --}}
@section('css')
    <link href="{{ asset('plugins/bootstrap-icons/bootstrap-icons.css') }}" rel="stylesheet">
    <style>
        [data-bs-theme="dark"] text {
            fill: rgb(250, 250, 250);
        }

        [data-bs-theme="light"] text {
            fill: rgb(0, 0, 0);
        }
    </style>
@endsection

@section('content')
    <div class="page-wrapper">
        <div class="container-fluid">
            <!-- Page title -->
            <div class="page-header d-print-none">
                <div class="row align-items-center">
                    <div class="col">
                        <!-- Page pre-title -->
                        <div class="page-pretitle">
                            {{ __('Overview') }}
                        </div>
                        <h2 class="page-title">
                            {{ __('Dashboard') }}
                        </h2>
                    </div>
                </div>
            </div>
        </div>
        <div class="page-body">
            <div class="container-fluid">
                {{-- Message --}}
                @if (session()->has('message'))
                    <div class="alert alert-important alert-success alert-dismissible" role="alert">
                        <div class="d-flex">
                            <div>
                                <!-- Download SVG icon from http://tabler-icons.io/i/info-circle -->
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon alert-icon" width="24"
                                    height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                    <path d="M3 12a9 9 0 1 0 18 0a9 9 0 0 0 -18 0"></path>
                                    <path d="M12 9h.01"></path>
                                    <path d="M11 12h1v4h1"></path>
                                </svg>
                            </div>
                            <div>
                                {!! session('message') !!}
                            </div>
                        </div>
                        <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
                    </div>
                    @php
                        session()->forget('message');
                    @endphp
                @endif

                {{-- Support status message --}}
                @if (session()->has('support_status_message'))
                    <div class="alert alert-important alert-danger alert-dismissible" role="alert">
                        <div class="d-flex">
                            <div>
                                {!! session('support_status_message') !!}
                            </div>
                        </div>
                        <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
                    </div>
                    @php
                        session()->forget('support_status_message');
                    @endphp
                @endif

                {{-- Stock message --}}
                @if (session('stock_message'))
                    <div class="alert alert-important alert-danger alert-dismissible" role="alert">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                {!! session('stock_message') !!}
                            </div>
                        </div>
                        <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
                    </div>
                @endif

                {{-- Failed --}}
                @if (Session::has('failed'))
                    <div class="alert alert-important alert-danger alert-dismissible mb-2" role="alert">
                        <div class="d-flex">
                            <div>
                                {{ Session::get('failed') }}
                            </div>
                        </div>
                        <a class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="close"></a>
                    </div>
                @endif

                {{-- Success --}}
                @if (Session::has('success'))
                    <div class="alert alert-important alert-success alert-dismissible mb-2" role="alert">
                        <div class="d-flex">
                            <div>
                                {{ Session::get('success') }}
                            </div>
                        </div>
                        <a class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="close"></a>
                    </div>
                @endif

                <div class="row row-deck row-cards mb-5">
                    {{-- Check role_id --}}
                    @if (Auth::user()->role_id != 3)
                        {{-- This Month Income --}}
                        <div class="col-sm-3 col-lg-3">
                            <div class="card bg-custom1">
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="subheader text-custom">{{ __('This Month Income') }}</div>
                                    </div>
                                    <div class="h1 text-custom">{{ formatCurrency($thisMonthIncome) }}</div>
                                </div>
                            </div>
                        </div>

                        {{-- Today Income --}}
                        <div class="col-sm-3 col-lg-3">
                            <div class="card bg-custom2">
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="subheader text-custom">{{ __('Today Income') }}</div>
                                    </div>
                                    <div class="h1 text-custom">{{ formatCurrency($today_income) }}</div>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Overall Users --}}
                    <div class="{{ Auth::user()->role_id != 3 ? 'col-sm-3 col-lg-3' : 'col-sm-6 col-lg-6' }}">
                        <div class="card bg-custom3">
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="subheader text-custom">{{ __('Overall Customers') }}</div>
                                </div>
                                <div class="h1 text-custom">{{ $overall_users }}</div>
                            </div>
                        </div>
                    </div>

                    {{-- Today User --}}
                    <div class="{{ Auth::user()->role_id != 3 ? 'col-sm-3 col-lg-3' : 'col-sm-6 col-lg-6' }}">
                        <div class="card bg-custom4">
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="subheader text-custom">{{ __('Today Customers') }}</div>
                                </div>
                                <div class="h1 text-custom">{{ $today_users }}</div>
                            </div>
                        </div>
                    </div>

                    {{-- Total Earnings, vCard creation and Store creation --}}
                    <div class="col-md-8 col-xl-8">
                        <div class="card shadow-sm">
                            <div class="card-body">

                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <div>
                                        <div class="subheader text-uppercase">{{ __('Overview') }}</div>
                                        <div class="text-muted small" id="overviewDateRangeText">
                                            {{ __('Last 2 weeks') }}
                                        </div>
                                    </div>

                                    <div class="position-relative">
                                        <i class="bi bi-calendar3 date-icon"></i>
                                        <input type="text" id="overviewDateRange"
                                            class="form-control form-control-sm overview-daterange" readonly>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col">
                                        <div id="overview"></div>
                                    </div>

                                    <div class="col-md-auto">
                                        <div class="divide-y divide-y-fill">
                                            <div class="px-3">
                                                <div><span class="status-dot bg-red"></span> {{ __('Earnings') }}</div>
                                                <div class="h2" id="totalEarnings">
                                                    {{ formatCurrency($totalEarnings) }}
                                                </div>
                                            </div>
                                            <div class="px-3">
                                                <div><span class="status-dot bg-orange"></span> {{ __('vCards') }}</div>
                                                <div class="h2" id="totalvCards">{{ $totalvCards }}</div>
                                            </div>
                                            <div class="px-3">
                                                <div><span class="status-dot bg-green"></span> {{ __('Stores') }}</div>
                                                <div class="h2" id="totalStores">{{ $totalStores }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                    {{-- Current Week Sales --}}
                    <div class="col-md-4 col-xl-4">
                        <div class="card">
                            <div class="card-body">
                                {{-- Title --}}
                                <div class="d-flex align-items-center mb-3">
                                    <div class="subheader mb-2">{{ __('Current Week Sales') }}</div>
                                </div>
                                <div id="current-week-sales"></div>
                            </div>
                        </div>
                    </div>

                    {{-- Sales Overwise --}}
                    <div class="col-md-8 col-lg-12 col-xl-8">
                        <div class="card shadow-sm">
                            <div class="card-body">

                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <div>
                                        <div class="subheader text-uppercase">{{ __('Sales Overview') }}</div>
                                        <div class="text-muted small" id="overviewSalesDateRange">
                                            {{ __('Last 2 weeks') }}
                                        </div>
                                    </div>

                                    <div class="position-relative">
                                        <i class="bi bi-calendar3 date-icon"></i>
                                        <input type="text" id="salesDateRange"
                                            class="form-control form-control-sm overview-daterange" readonly>
                                    </div>
                                </div>

                                <div id="sales" class="chart-sm"></div>

                            </div>
                        </div>
                    </div>

                    {{-- Users Overwise --}}
                    <div class="col-md-4 col-lg-12 col-xl-4">
                        <div class="card shadow-sm">
                            <div class="card-body">

                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <div>
                                        <div class="subheader text-uppercase">{{ __('Users Overview') }}</div>
                                        <div class="text-muted small" id="overviewUsersDateRange">
                                            {{ __('Last 2 weeks') }}
                                        </div>
                                    </div>

                                    <div class="position-relative">
                                        <i class="bi bi-calendar3 date-icon"></i>
                                        <input type="text" id="usersDateRange"
                                            class="form-control form-control-sm overview-daterange" readonly>
                                    </div>
                                </div>

                                <div id="users" class="chart-sm"></div>

                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
        @include('admin.includes.footer')
    </div>

    {{-- Custom JS --}}
@section('scripts')
    <script src="{{ asset('js/apexcharts.min.js') }}"></script>
    <link rel="stylesheet" href="{{ asset('plugins/daterangepicker/daterangepicker.css') }}">
    <script src="{{ asset('plugins/moment/moment.min.js') }}"></script>
    <script src="{{ asset('plugins/daterangepicker/daterangepicker.min.js') }}"></script>

    <script>
        "use strict";

        // Sales Overview
        let salesChart;

        document.addEventListener("DOMContentLoaded", function() {

            // Last week
            const startOfLastWeek = moment().subtract(1, 'week').startOf('week');

            // Current week
            const endOfCurrentWeek = moment().endOf('week');

            // Date range picker (Jan 1 → Today)
            $('#salesDateRange').daterangepicker({
                startDate: startOfLastWeek,
                endDate: endOfCurrentWeek,
                opens: 'left',
                autoUpdateInput: true,
                showDropdowns: true,
                alwaysShowCalendars: true,
                ranges: {
                    'Today': [moment(), moment()],
                    'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                    'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                    'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                    'This Month': [moment().startOf('month'), moment().endOf('month')],
                    'This Year': [moment().startOf('year'), moment()]
                },
                locale: {
                    format: 'DD MMM YYYY'
                }
            });

            // On date apply
            $('#salesDateRange').on('apply.daterangepicker', function(ev, picker) {
                // Update date range text
                document.getElementById('overviewSalesDateRange').innerHTML =
                    `From ${picker.startDate.format('DD MMM YYYY')} to ${picker.endDate.format('DD MMM YYYY')}`;

                loadSalesData(
                    picker.startDate.format('YYYY-MM-DD'),
                    picker.endDate.format('YYYY-MM-DD')
                );
            });

            // ApexCharts init
            salesChart = new ApexCharts(document.getElementById('sales'), {
                chart: {
                    type: "area",
                    height: 280,
                    toolbar: {
                        show: false
                    },
                    parentHeightOffset: 0
                },
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    curve: "smooth",
                    width: 3
                },
                fill: {
                    type: "gradient",
                    gradient: {
                        opacityFrom: 0.45,
                        opacityTo: 0.05
                    }
                },
                grid: {
                    padding: {
                        left: 12,
                        right: 12,
                        top: 10,
                        bottom: 0
                    },
                    strokeDashArray: 4
                },
                series: [{
                    name: "Sales",
                    data: []
                }],
                xaxis: {
                    categories: [],
                    labels: {
                        style: {
                            fontSize: '12px',
                            colors: '#6c757d'
                        }
                    }
                },
                yaxis: {
                    labels: {
                        style: {
                            fontSize: '12px',
                            colors: '#6c757d'
                        },
                        formatter: val => val.toFixed(2)
                    }
                },
                tooltip: {
                    theme: 'dark',
                    x: {
                        formatter: val => moment(val, 'DD MMM').format('DD MMM YYYY')
                    },
                    y: {
                        formatter: val => val.toFixed(2)
                    }
                },
                colors: ["#4263eb"]
            });

            salesChart.render();

            // Auto load Jan 1 → Today
            loadSalesData(
                startOfLastWeek.format('YYYY-MM-DD'),
                endOfCurrentWeek.format('YYYY-MM-DD')
            );
        });

        // Load data via AJAX
        function loadSalesData(startDate, endDate) {
            $.ajax({
                url: "{{ route('admin.sales.filter') }}",
                method: "GET",
                data: {
                    start_date: startDate,
                    end_date: endDate
                },
                success: function(res) {

                    if (!res.data || !res.data.length) {
                        salesChart.updateOptions({
                            xaxis: {
                                categories: []
                            },
                            series: [{
                                name: "Sales",
                                data: []
                            }]
                        });
                        return;
                    }

                    salesChart.updateOptions({
                        xaxis: {
                            categories: res.labels
                        },
                        series: [{
                            name: "Sales",
                            data: res.data
                        }]
                    });
                }
            });
        }

        // Users overview
        let usersChart;

        document.addEventListener("DOMContentLoaded", function() {

            // Last week
            const startOfLastWeek = moment().subtract(1, 'week').startOf('week');

            // Current week
            const endOfCurrentWeek = moment().endOf('week');

            // Date range picker (Jan 1 → Today)
            $('#usersDateRange').daterangepicker({
                startDate: startOfLastWeek,
                endDate: endOfCurrentWeek,
                opens: 'left',
                autoUpdateInput: true,
                showDropdowns: true,
                alwaysShowCalendars: true,
                ranges: {
                    'Today': [moment(), moment()],
                    'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                    'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                    'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                    'This Month': [moment().startOf('month'), moment().endOf('month')],
                    'This Year': [moment().startOf('year'), moment()]
                },
                locale: {
                    format: 'DD MMM YYYY'
                }
            });

            // On date change
            $('#usersDateRange').on('apply.daterangepicker', function(ev, picker) {
                // Update date range text
                document.getElementById('overviewUsersDateRange').innerHTML =
                    `From ${picker.startDate.format('DD MMM YYYY')} to ${picker.endDate.format('DD MMM YYYY')}`;

                loadUsersData(
                    picker.startDate.format('YYYY-MM-DD'),
                    picker.endDate.format('YYYY-MM-DD')
                );
            });

            // ApexCharts init
            usersChart = new ApexCharts(document.getElementById('users'), {
                chart: {
                    type: "bar",
                    height: 310,
                    toolbar: {
                        show: false
                    },
                    parentHeightOffset: 0
                },
                plotOptions: {
                    bar: {
                        columnWidth: '45%',
                        borderRadius: 4
                    }
                },
                dataLabels: {
                    enabled: false
                },
                fill: {
                    opacity: 1
                },
                grid: {
                    padding: {
                        left: 8,
                        right: 8,
                        top: 10,
                        bottom: 0
                    },
                    strokeDashArray: 4
                },
                series: [{
                    name: "{{ __('New Users') }}",
                    data: []
                }],
                xaxis: {
                    categories: [],
                    labels: {
                        style: {
                            fontSize: '12px',
                            colors: '#6c757d'
                        }
                    },
                    axisBorder: {
                        show: false
                    }
                },
                yaxis: {
                    labels: {
                        style: {
                            fontSize: '12px',
                            colors: '#6c757d'
                        }
                    }
                },
                tooltip: {
                    theme: 'dark',
                    x: {
                        formatter: val => moment(val, 'DD MMM').format('DD MMM YYYY')
                    }
                },
                colors: ['#035AC4'],
                legend: {
                    show: false
                }
            });

            usersChart.render();

            // Auto load Jan 1 → Today
            loadUsersData(
                startOfLastWeek.format('YYYY-MM-DD'),
                endOfCurrentWeek.format('YYYY-MM-DD')
            );
        });

        // Load users via AJAX
        function loadUsersData(startDate, endDate) {
            $.ajax({
                url: "{{ route('admin.users.filter') }}",
                method: "GET",
                data: {
                    start_date: startDate,
                    end_date: endDate
                },
                success: function(res) {

                    if (!res.data || !res.data.length) {
                        usersChart.updateOptions({
                            xaxis: {
                                categories: []
                            },
                            series: [{
                                name: "{{ __('New Users') }}",
                                data: []
                            }]
                        });
                        return;
                    }

                    usersChart.updateOptions({
                        xaxis: {
                            categories: res.labels
                        },
                        series: [{
                            name: "{{ __('New Users') }}",
                            data: res.data
                        }]
                    });
                }
            });
        }
        // @formatter:on

        // Overview
        // @formatter:off
        let overviewChart;

        document.addEventListener("DOMContentLoaded", function() {

            // Last week
            const startOfLastWeek = moment().subtract(1, 'week').startOf('week');

            // Current week
            const endOfCurrentWeek = moment().endOf('week');

            $('#overviewDateRange').daterangepicker({
                startDate: startOfLastWeek,
                endDate: endOfCurrentWeek,
                opens: 'left',
                autoUpdateInput: true,
                showDropdowns: true,
                alwaysShowCalendars: true,
                ranges: {
                    'Today': [moment(), moment()],
                    'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                    'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                    'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                    'This Month': [moment().startOf('month'), moment().endOf('month')],
                    'This Year': [moment().startOf('year'), moment()]
                },
                locale: {
                    format: 'DD MMM YYYY'
                }
            });

            $('#overviewDateRange').on('apply.daterangepicker', function(ev, picker) {
                // Update date range text
                document.getElementById('overviewDateRangeText').innerHTML =
                    `From ${picker.startDate.format('DD MMM YYYY')} to ${picker.endDate.format('DD MMM YYYY')}`;

                loadOverviewData(
                    picker.startDate.format('YYYY-MM-DD'),
                    picker.endDate.format('YYYY-MM-DD')
                );
            });

            overviewChart = new ApexCharts(document.getElementById('overview'), {
                chart: {
                    type: "line",
                    height: 310,
                    toolbar: {
                        show: false
                    },
                    parentHeightOffset: 0
                },
                stroke: {
                    curve: "smooth",
                    width: 3
                },
                dataLabels: {
                    enabled: false
                },
                grid: {
                    padding: {
                        left: 10,
                        right: 10,
                        top: 10,
                        bottom: 0
                    },
                    strokeDashArray: 4
                },
                series: [{
                        name: "Earnings",
                        data: []
                    },
                    {
                        name: "vCards",
                        data: []
                    },
                    {
                        name: "Stores",
                        data: []
                    }
                ],
                xaxis: {
                    categories: [],
                    labels: {
                        style: {
                            fontSize: '12px',
                            colors: '#6c757d'
                        }
                    }
                },
                tooltip: {
                    theme: 'dark',
                    x: {
                        formatter: val => moment(val, 'DD MMM').format('DD MMM YYYY')
                    }
                },
                colors: ["#D63939", "#F76707", "#2FB344"],
                legend: {
                    show: false
                }
            });

            overviewChart.render();

            loadOverviewData(
                startOfLastWeek.format('YYYY-MM-DD'),
                endOfCurrentWeek.format('YYYY-MM-DD')
            );
        });

        function loadOverviewData(startDate, endDate) {
            $.get("{{ route('admin.overview.filter') }}", {
                start_date: startDate,
                end_date: endDate
            }, function(res) {

                overviewChart.updateOptions({
                    xaxis: {
                        categories: res.labels
                    },
                    series: [{
                            name: "Earnings",
                            data: res.earnings
                        },
                        {
                            name: "vCards",
                            data: res.vcards
                        },
                        {
                            name: "Stores",
                            data: res.stores
                        }
                    ]
                });

                $('#totalEarnings').text(res.total_earnings);
                $('#totalvCards').text(res.total_vcards);
                $('#totalStores').text(res.total_stores);
            });
        }
        // @formatter:on

        // Current week sales
        document.addEventListener("DOMContentLoaded", function() {

            var totalSales = {{ array_sum($currentWeekSales) }};
            var container = document.getElementById('current-week-sales');

            // Proper empty state
            if (totalSales <= 0) {
                container.innerHTML = `
            <div class="d-flex flex-column align-items-center justify-content-center" style="height:310px;">
                <div style="font-size:14px;color:#6c757d;">{{ __('No sales recorded this week') }}</div>
            </div>
        `;
                return;
            }

            var options = {
                chart: {
                    type: "donut",
                    height: 310,
                    fontFamily: 'inherit',
                    animations: {
                        enabled: true
                    }
                },
                series: {!! json_encode($currentWeekSales) !!},
                labels: [
                    `{{ __('Monday') }}`,
                    `{{ __('Tuesday') }}`,
                    `{{ __('Wednesday') }}`,
                    `{{ __('Thursday') }}`,
                    `{{ __('Friday') }}`,
                    `{{ __('Saturday') }}`,
                    `{{ __('Sunday') }}`
                ],
                stroke: {
                    width: 2
                },
                dataLabels: {
                    enabled: false
                },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '72%',
                            labels: {
                                show: true,
                                total: {
                                    show: true,
                                    label: `{{ __('Total') }}`,
                                    formatter: function() {
                                        return "{{ formatCurrency(array_sum($currentWeekSales)) }}";
                                    }
                                }
                            }
                        }
                    }
                },
                legend: {
                    show: true,
                    position: 'bottom',
                    fontSize: '13px',
                    markers: {
                        width: 10,
                        height: 10,
                        radius: 100
                    }
                },
                tooltip: {
                    theme: 'dark'
                },
                colors: [
                    "#4c6ef5",
                    "#fd7e14",
                    "#495057",
                    "#15aabf",
                    "#fa5252",
                    "#2f9e44",
                    "#cc5de8"
                ],
                grid: {
                    strokeDashArray: 4
                }
            };

            new ApexCharts(container, options).render();
        });
    </script>
@endsection
@endsection
