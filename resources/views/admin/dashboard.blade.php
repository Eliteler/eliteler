@extends('admin.layouts.index', ['header' => true, 'nav' => true, 'demo' => true])

{{-- Custom CSS --}}
@section('css')
    {{-- Admin Dashboard CSS --}}
    <link href="{{ asset('css/admin/dashboard.css') }}" rel="stylesheet">

    {{-- Bootstrap Icons --}}
    <link href="{{ asset('plugins/bootstrap-icons/bootstrap-icons.css') }}" rel="stylesheet">
@endsection

@section('content')
    <div class="page-wrapper">
        <!-- Page title header wrapper -->
        <div class="page-header d-print-none mb-2">
            <div class="container-fluid">
                <div class="row align-items-center">
                    <div class="col">
                        <h2 class="page-title text-dark fw-bold mb-0">
                            <span id="clientGreetingText"></span>
                        </h2>
                        <div class="text-muted mt-1">
                            {{ __('Here’s a quick overview of your business performance today.') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="page-body">
            <div class="container-fluid">
                {{-- Flash Message --}}
                @if (session()->has('message'))
                    <div class="alert alert-important alert-success alert-dismissible" role="alert">
                        <div class="d-flex align-items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon alert-icon" width="24" height="24"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                <path d="M3 12a9 9 0 1 0 18 0a9 9 0 0 0 -18 0"></path>
                                <path d="M12 9h.01"></path>
                                <path d="M11 12h1v4h1"></path>
                            </svg>
                            <div>{!! session('message') !!}</div>
                        </div>
                        <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
                    </div>
                    @php session()->forget('message'); @endphp
                @endif

                {{-- Support status message --}}
                @if (session()->has('support_status_message'))
                    <div class="alert alert-important alert-danger alert-dismissible" role="alert">
                        <div class="d-flex align-items-center gap-2">
                            <div>{!! session('support_status_message') !!}</div>
                        </div>
                        <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
                    </div>
                    @php session()->forget('support_status_message'); @endphp
                @endif

                {{-- Stock message --}}
                @if (session('stock_message'))
                    <div class="alert alert-important alert-danger alert-dismissible fade show pe-5" role="alert">
                        <a href="{{ session('stock_url') }}" class="text-white text-decoration-none">
                            {{ session('stock_message') }}
                        </a>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"
                            aria-label="Close"></button>
                    </div>
                @endif

                {{-- Failed Notification --}}
                @if (Session::has('failed'))
                    <div class="alert alert-important alert-danger alert-dismissible mb-2" role="alert">
                        <div class="d-flex align-items-center gap-2">
                            <div>{{ Session::get('failed') }}</div>
                        </div>
                        <a class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="close"></a>
                    </div>
                @endif

                {{-- Success Notification --}}
                @if (Session::has('success'))
                    <div class="alert alert-important alert-success alert-dismissible mb-2" role="alert">
                        <div class="d-flex align-items-center gap-2">
                            <div>{{ Session::get('success') }}</div>
                        </div>
                        <a class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="close"></a>
                    </div>
                @endif

                {{-- =========================================================================
                     ROW 1: WELCOME HERO (COL-5) | TOTAL USERS (COL-4) | ACTIVE USERS GAUGE (COL-3)
                     ========================================================================= --}}
                <div class="row g-3 mb-3">
                    {{-- 1. Hero Welcome Card --}}
                    <div class="col-12 col-xl-5">
                        <div class="analytics-card p-3 p-md-3">
                            <div class="row align-items-center">
                                <div class="col-7">
                                    <h3 class="fw-bold mb-1 text-reset">{{ __('Welcome back') }}, {{ Auth::user()->name }}
                                    </h3>
                                    <p class="text-muted small mb-3" style="font-size: 0.8rem;">
                                        {{ __('Here’s a quick overview of your business performance today.') }}
                                    </p>

                                    <div class="row g-3">
                                        <div class="col-6">
                                            <div class="metric-title" style="font-size: 0.65rem;">{{ __("TODAY'S SALES") }}
                                            </div>
                                            <div class="d-flex align-items-center gap-1 mt-1">
                                                <span
                                                    class="h3 fw-bold mb-0 text-reset">{{ formatCurrency($today_income) }}</span>
                                                <span class="badge-trend-up small" style="font-size: 0.72rem;">
                                                    {{ $currency->symbol ?? '₹' }}
                                                </span>
                                            </div>
                                            <div class="hero-progress-line bg-success"></div>
                                        </div>

                                        <div class="col-6">
                                            <div class="metric-title" style="font-size: 0.65rem;">{{ __('MONTH GROWTH') }}
                                            </div>
                                            <div class="d-flex align-items-center gap-1 mt-1">
                                                <span
                                                    class="h3 fw-bold mb-0 text-reset">{{ $revenueGrowthPercentage }}%</span>
                                                <span
                                                    class="{{ $revenueGrowthPercentage >= 0 ? 'badge-trend-up' : 'badge-trend-down' }} small"
                                                    style="font-size: 0.72rem;">
                                                    {{ $revenueGrowthPercentage >= 0 ? '↗' : '↘' }}
                                                </span>
                                            </div>
                                            <div
                                                class="hero-progress-line {{ $revenueGrowthPercentage >= 0 ? 'bg-success' : 'bg-danger' }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-5 text-center">
                                    <img src="{{ asset('images/goal-achievement.webp') }}" alt="Target Achievement"
                                        class="img-fluid" style="max-height: 125px; object-fit: contain;">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 2. Total Users Card with Month-over-Month Comparison --}}
                    <div class="col-12 col-md-6 col-xl-4">
                        <div class="analytics-card p-3 p-md-3">
                            <div>
                                <div class="metric-title mb-1">{{ __('TOTAL USERS') }}</div>
                                <div class="d-flex align-items-baseline gap-2">
                                    <span class="metric-value text-reset">{{ number_format($overall_users) }}</span>
                                    <span
                                        class="{{ $usersGrowthPercentage >= 0 ? 'badge-trend-up' : 'badge-trend-down' }}">
                                        {{ $usersGrowthPercentage >= 0 ? '+' : '' }}{{ $usersGrowthPercentage }}%
                                        {{ $usersGrowthPercentage >= 0 ? '↗' : '↘' }}
                                    </span>
                                </div>
                                <div class="text-muted small mt-1" style="font-size: 0.78rem;">
                                    {{ $thisMonthUsers }} {{ __('new this month') }} ({{ $lastMonthUsers }}
                                    {{ __('last month') }})
                                </div>
                            </div>

                            <div class="mt-auto" style="margin-bottom: -10px;">
                                <div id="sparkline-total-users"></div>
                            </div>
                        </div>
                    </div>

                    {{-- 3. Active Users Semi-Radial Gauge --}}
                    <div class="col-12 col-md-6 col-xl-3">
                        <div class="analytics-card p-3 p-md-3 text-center">
                            <div class="text-start">
                                <div class="metric-title mb-1">{{ __('ACTIVE CARDS') }}</div>
                                <div class="d-flex align-items-baseline gap-2">
                                    <span
                                        class="metric-value text-reset">{{ number_format($totalvCards + $totalStores) }}</span>
                                    <span class="badge-trend-up small">{{ $activeCardRatio }}% active</span>
                                </div>
                            </div>

                            <div class="radial-gauge-container">
                                <div id="radial-active-users" style="width: 100%;"></div>
                                <div class="gauge-center-text">
                                    <span class="gauge-number">{{ $activeCardRatio }}%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- =========================================================================
                     ROW 2: MONTH-OVER-MONTH METRIC CARDS (SALES CONV | REVENUE | CLIENTS | ACTIVE SUBS)
                     ========================================================================= --}}
                <div class="row g-3 mb-4">
                    {{-- 1. Conversion Rate --}}
                    <div class="col-12 col-sm-6 col-xl-3">
                        <div class="analytics-card p-3 p-md-3">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <div class="metric-title">{{ __('CONVERSION') }}</div>
                                <span
                                    class="badge bg-surface-secondary text-muted small fw-semibold">{{ __('Overall') }}</span>
                            </div>
                            <div class="metric-value text-reset mb-2">{{ $conversionRate }}%</div>

                            <div class="mt-auto">
                                <div class="d-flex align-items-center justify-content-between small text-muted mb-1"
                                    style="font-size: 0.78rem;">
                                    <span>{{ __('Target: 50%') }}</span>
                                    <span class="badge-trend-up">{{ $activeSubscriptions }} active</span>
                                </div>
                                <div class="progress" style="height: 5px;">
                                    <div class="progress-bar bg-primary"
                                        style="width: {{ min(100, $conversionRate * 2) }}%"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 2. Monthly Revenue (This Month vs Last Month) --}}
                    <div class="col-12 col-sm-6 col-xl-3">
                        <div class="analytics-card p-3 p-md-3">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <div class="metric-title">{{ __('MONTH REVENUE') }}</div>
                                <span
                                    class="badge bg-surface-secondary text-muted small fw-semibold">{{ __('vs Last Month') }}</span>
                            </div>
                            <div class="d-flex align-items-baseline gap-2 mb-1">
                                <span class="metric-value text-reset">{{ formatCurrency($thisMonthIncome) }}</span>
                                <span class="{{ $revenueGrowthPercentage >= 0 ? 'badge-trend-up' : 'badge-trend-down' }}">
                                    {{ $revenueGrowthPercentage >= 0 ? '+' : '' }}{{ $revenueGrowthPercentage }}%
                                    {{ $revenueGrowthPercentage >= 0 ? '↗' : '↘' }}
                                </span>
                            </div>

                            <div class="mt-auto" style="margin-bottom: -10px;">
                                <div id="sparkline-revenue"></div>
                            </div>
                        </div>
                    </div>

                    {{-- 3. New Clients (This Month vs Last Month) --}}
                    <div class="col-12 col-sm-6 col-xl-3">
                        <div class="analytics-card p-3 p-md-3">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <div class="metric-title">{{ __('NEW CLIENTS') }}</div>
                                <span
                                    class="badge bg-surface-secondary text-muted small fw-semibold">{{ __('vs Last Month') }}</span>
                            </div>
                            <div class="d-flex align-items-baseline gap-2 mb-1">
                                <span class="metric-value text-reset">{{ number_format($thisMonthUsers) }}</span>
                                <span class="{{ $usersGrowthPercentage >= 0 ? 'badge-trend-up' : 'badge-trend-down' }}">
                                    {{ $usersGrowthPercentage >= 0 ? '+' : '' }}{{ $usersGrowthPercentage }}%
                                    {{ $usersGrowthPercentage >= 0 ? '↗' : '↘' }}
                                </span>
                            </div>

                            <div class="mt-auto" style="margin-bottom: -10px;">
                                <div id="sparkline-new-clients"></div>
                            </div>
                        </div>
                    </div>

                    {{-- 4. Subscriptions (This Month vs Last Month) --}}
                    <div class="col-12 col-sm-6 col-xl-3">
                        <div class="analytics-card p-3 p-md-3">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <div class="metric-title">{{ __('SUBSCRIPTIONS') }}</div>
                                <span
                                    class="badge bg-surface-secondary text-muted small fw-semibold">{{ __('vs Last Month') }}</span>
                            </div>
                            <div class="d-flex align-items-baseline gap-2 mb-1">
                                <span class="metric-value text-reset">{{ number_format($thisMonthSubs) }}</span>
                                <span class="{{ $subsGrowthPercentage >= 0 ? 'badge-trend-up' : 'badge-trend-down' }}">
                                    {{ $subsGrowthPercentage >= 0 ? '+' : '' }}{{ $subsGrowthPercentage }}%
                                    {{ $subsGrowthPercentage >= 0 ? '↗' : '↘' }}
                                </span>
                            </div>

                            <div class="mt-auto" style="margin-bottom: -10px;">
                                <div id="sparkline-subscriptions"></div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- =========================================================================
                     ROW 3: COMPREHENSIVE OVERVIEW & WEEKLY BREAKDOWN
                     ========================================================================= --}}
                <div class="row row-deck row-cards mb-5 g-3">
                    {{-- Total Earnings, Digital Business Cards, Stores --}}
                    <div class="col-12 col-md-8 col-xl-8">
                        <div class="card border-custom1 border-0 shadow-sm">
                            <div class="card-body p-3 p-md-3">
                                {{-- Header --}}
                                <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
                                    <div>
                                        <div
                                            class="subheader text-uppercase fw-bold text-secondary overview-title metric-title">
                                            {{ __('Overview') }}
                                        </div>
                                        <div class="text-muted small mt-1" id="overviewDateRangeText">
                                            {{ __('Last 7 Days') }}
                                        </div>
                                    </div>

                                    <div class="position-relative">
                                        <i class="bi bi-calendar3 date-icon"></i>
                                        <input type="text" id="overviewDateRange"
                                            class="form-control form-control-sm overview-daterange" readonly>
                                    </div>
                                </div>

                                {{-- Chart + Responsive Stats Grid --}}
                                <div class="row align-items-center g-3">
                                    {{-- Chart --}}
                                    <div class="col-12 col-lg-8 col-xl-9">
                                        <div id="overview" class="overview-chart w-100"></div>
                                    </div>

                                    {{-- Responsive Stats Grid --}}
                                    <div class="col-12 col-lg-4 col-xl-3">
                                        <div class="row g-2">
                                            {{-- Earnings --}}
                                            <div class="col-4 col-lg-12">
                                                <div class="overview-stat overview-stat-danger p-2 p-md-3 rounded-2">
                                                    <div
                                                        class="stat-title text-danger small text-nowrap mb-1 metric-title">
                                                        <span class="status-dot bg-red"></span>
                                                        {{ __('Earnings') }}
                                                    </div>
                                                    <div class="stat-value text-dark-custom fw-bold fs-3 text-truncate"
                                                        id="totalEarnings">
                                                        {{ formatCurrency($totalEarnings) }}
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- Cards --}}
                                            <div class="col-4 col-lg-12">
                                                <div class="overview-stat overview-stat-warning p-2 p-md-3 rounded-2">
                                                    <div class="stat-title text-orange small text-nowrap mb-1">
                                                        <span class="status-dot bg-orange"></span>
                                                        {{ __('Cards') }}
                                                    </div>
                                                    <div class="stat-value text-dark-custom fw-bold fs-3 text-truncate"
                                                        id="totalvCards">
                                                        {{ $totalvCards }}
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- Stores --}}
                                            <div class="col-4 col-lg-12">
                                                <div class="overview-stat overview-stat-success p-2 p-md-3 rounded-2">
                                                    <div class="stat-title text-success small text-nowrap mb-1">
                                                        <span class="status-dot bg-green"></span>
                                                        {{ __('Stores') }}
                                                    </div>
                                                    <div class="stat-value text-dark-custom fw-bold fs-3 text-truncate"
                                                        id="totalStores">
                                                        {{ $totalStores }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Current Week Sales Card Component --}}
                    <div class="col-12 col-md-4 col-xl-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-3">
                                    <div>
                                        <div
                                            class="subheader text-secondary fw-bold text-uppercase tracking-wider mb-1 metric-title">
                                            {{ __('Current Week Sales') }}
                                        </div>
                                        <div class="text-muted small">
                                            {{ __('Distribution of revenue across the current week') }}
                                        </div>
                                    </div>
                                </div>
                                <div id="current-week-sales" style="min-height: 320px;" class="position-relative"></div>
                            </div>
                        </div>
                    </div>

                    {{-- Sales Overview --}}
                    <div class="col-12 col-md-8 col-xl-8">
                        <div class="card border-custom1 border-0 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
                                    <div>
                                        <div class="subheader text-uppercase metric-title">{{ __('Sales Overview') }}
                                        </div>
                                        <div class="text-muted small mt-1" id="overviewSalesDateRange">
                                            {{ __('Last 7 Days') }}
                                        </div>
                                    </div>

                                    <div class="position-relative">
                                        <i class="bi bi-calendar3 date-icon"></i>
                                        <input type="text" id="salesDateRange"
                                            class="form-control form-control-sm overview-daterange" readonly>
                                    </div>
                                </div>
                                <div id="sales" class="chart-sm w-100"></div>
                            </div>
                        </div>
                    </div>

                    {{-- Users Overview --}}
                    <div class="col-12 col-md-4 col-xl-4">
                        <div class="card border-custom1 border-0 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
                                    <div>
                                        <div class="subheader text-uppercase metric-title">{{ __('Users Overview') }}
                                        </div>
                                        <div class="text-muted small mt-1" id="overviewUsersDateRange">
                                            {{ __('Last 7 Days') }}
                                        </div>
                                    </div>

                                    <div class="position-relative">
                                        <i class="bi bi-calendar3 date-icon"></i>
                                        <input type="text" id="usersDateRange"
                                            class="form-control form-control-sm overview-daterange" readonly>
                                    </div>
                                </div>
                                <div id="users" class="chart-sm w-100"></div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
        @include('admin.includes.footer')
    </div>
@endsection

{{-- Custom JS --}}
@section('scripts')
    <script src="{{ asset('js/apexcharts.min.js') }}"></script>
    <link rel="stylesheet" href="{{ asset('plugins/daterangepicker/daterangepicker.css') }}">
    <script src="{{ asset('plugins/moment/moment.min.js') }}"></script>
    <script src="{{ asset('plugins/daterangepicker/daterangepicker.min.js') }}"></script>

    <script>
        "use strict";

        // Client-side Local Time Greeting Calculations
        document.addEventListener("DOMContentLoaded", function() {
            const hour = new Date().getHours();
            let greetingText = '';

            if (hour >= 5 && hour < 12) {
                greetingText = "{{ __('Good morning') }}";
            } else if (hour >= 12 && hour < 17) {
                greetingText = "{{ __('Good afternoon') }}";
            } else if (hour >= 17 && hour < 22) {
                greetingText = "{{ __('Good evening') }}";
            } else {
                greetingText = "{{ __('Good night') }}";
            }

            const targetText = document.getElementById('clientGreetingText');
            if (targetText) {
                targetText.innerHTML =
                    `${greetingText}, <span class="text-primary">{{ Auth::user()->name }}!</span> 👋`;
            }
        });

        // Sparklines & Single Radial Gauge Initialization
        document.addEventListener("DOMContentLoaded", function() {
            // 1. Total Users Sparkline
            const elTotalUsers = document.getElementById('sparkline-total-users');
            if (elTotalUsers) {
                elTotalUsers.innerHTML = '';
                new ApexCharts(elTotalUsers, {
                    chart: {
                        type: 'area',
                        height: 60,
                        sparkline: {
                            enabled: true
                        }
                    },
                    stroke: {
                        curve: 'smooth',
                        width: 2.5
                    },
                    fill: {
                        type: 'gradient',
                        gradient: {
                            opacityFrom: 0.35,
                            opacityTo: 0.0,
                            stops: [0, 90]
                        }
                    },
                    series: [{
                        name: 'Users',
                        data: {!! json_encode($usersSparkline) !!}
                    }],
                    colors: ['#0054a6']
                }).render();
            }

            // 2. Active Users Semi-Circle Gauge (Single Clean Render)
            const elActiveUsers = document.getElementById('radial-active-users');
            if (elActiveUsers) {
                elActiveUsers.innerHTML = '';
                const isDark = document.body.getAttribute('data-bs-theme') === 'dark';

                new ApexCharts(elActiveUsers, {
                    chart: {
                        type: 'radialBar',
                        height: 155,
                        offsetY: -5,
                        sparkline: {
                            enabled: true
                        }
                    },
                    plotOptions: {
                        radialBar: {
                            startAngle: -90,
                            endAngle: 90,
                            hollow: {
                                size: '68%'
                            },
                            track: {
                                background: isDark ? 'rgba(255, 255, 255, 0.08)' : '#e2e8f0',
                                strokeWidth: '100%',
                                margin: 0
                            },
                            dataLabels: {
                                show: false
                            }
                        }
                    },
                    series: [{{ $activeCardRatio }}],
                    colors: ['#0054a6']
                }).render();
            }

            // 3. Revenue Smooth Wave Sparkline
            const elRevenue = document.getElementById('sparkline-revenue');
            if (elRevenue) {
                elRevenue.innerHTML = '';
                new ApexCharts(elRevenue, {
                    chart: {
                        type: 'area',
                        height: 50,
                        sparkline: {
                            enabled: true
                        }
                    },
                    stroke: {
                        curve: 'smooth',
                        width: 2.5
                    },
                    fill: {
                        type: 'gradient',
                        gradient: {
                            opacityFrom: 0.25,
                            opacityTo: 0.0,
                            stops: [0, 90]
                        }
                    },
                    series: [{
                        name: 'Revenue',
                        data: {!! json_encode($revenueSparkline) !!}
                    }],
                    colors: ['#0054a6']
                }).render();
            }

            // 4. New Clients Double Curve Sparkline
            const elNewClients = document.getElementById('sparkline-new-clients');
            if (elNewClients) {
                elNewClients.innerHTML = '';
                new ApexCharts(elNewClients, {
                    chart: {
                        type: 'line',
                        height: 50,
                        sparkline: {
                            enabled: true
                        }
                    },
                    stroke: {
                        curve: 'smooth',
                        width: [2, 1.5],
                        dashArray: [0, 4]
                    },
                    series: [{
                            name: 'Current Month',
                            data: [4, 8, 6, 12, 9, 14, 11]
                        },
                        {
                            name: 'Last Month',
                            data: [3, 6, 5, 8, 7, 10, 9]
                        }
                    ],
                    colors: ['#0054a6', '#94a3b8']
                }).render();
            }

            // 5. Active Subscriptions Mini Column Bars
            const elSubs = document.getElementById('sparkline-subscriptions');
            if (elSubs) {
                elSubs.innerHTML = '';
                new ApexCharts(elSubs, {
                    chart: {
                        type: 'bar',
                        height: 50,
                        sparkline: {
                            enabled: true
                        }
                    },
                    plotOptions: {
                        bar: {
                            columnWidth: '40%',
                            borderRadius: 2
                        }
                    },
                    series: [{
                        name: 'Subscriptions',
                        data: {!! json_encode($subSparkline) !!}
                    }],
                    colors: ['#0054a6']
                }).render();
            }
        });

        // Sales Overview Chart
        let salesChart;

        document.addEventListener("DOMContentLoaded", function() {
            const salesEl = document.getElementById('sales');
            if (!salesEl) return;

            const defaultStartDate = moment().subtract(6, 'days');
            const defaultEndDate = moment();

            if ($('#salesDateRange').length) {
                $('#salesDateRange').daterangepicker({
                    startDate: defaultStartDate,
                    endDate: defaultEndDate,
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

                $('#salesDateRange').on('apply.daterangepicker', function(ev, picker) {
                    const dateTextEl = document.getElementById('overviewSalesDateRange');
                    if (dateTextEl) {
                        dateTextEl.innerHTML =
                            `From ${picker.startDate.format('DD MMM YYYY')} to ${picker.endDate.format('DD MMM YYYY')}`;
                    }

                    loadSalesData(
                        picker.startDate.format('YYYY-MM-DD'),
                        picker.endDate.format('YYYY-MM-DD')
                    );
                });
            }

            salesChart = new ApexCharts(salesEl, {
                chart: {
                    type: "area",
                    height: 280,
                    width: "100%",
                    toolbar: {
                        show: false
                    },
                    parentHeightOffset: 0,
                    dropShadow: {
                        enabled: true,
                        top: 6,
                        left: 0,
                        blur: 8,
                        opacity: 0.15,
                        color: '#4263eb'
                    }
                },
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    curve: "smooth",
                    width: 3.5,
                    lineCap: "round"
                },
                fill: {
                    type: "gradient",
                    gradient: {
                        opacityFrom: 0.22,
                        opacityTo: 0.01,
                        stops: [0, 95]
                    }
                },
                grid: {
                    padding: {
                        left: 5,
                        right: 5,
                        top: 10,
                        bottom: 0
                    },
                    strokeDashArray: 5,
                    borderColor: 'rgba(148, 163, 184, 0.06)'
                },
                series: [{
                    name: "Sales",
                    data: []
                }],
                xaxis: {
                    categories: [],
                    labels: {
                        style: {
                            fontSize: '11px',
                            colors: '#6c757d',
                            fontFamily: 'inherit',
                            fontWeight: '500'
                        }
                    },
                    axisBorder: {
                        show: false
                    },
                    axisTicks: {
                        show: false
                    }
                },
                yaxis: {
                    labels: {
                        align: 'right',
                        minWidth: 28,
                        maxWidth: 28,
                        style: {
                            fontSize: '11px',
                            colors: '#6c757d',
                            fontFamily: 'inherit',
                            fontWeight: '500'
                        },
                        formatter: val => (val % 1 === 0 ? val.toFixed(0) : val.toFixed(2))
                    }
                },
                tooltip: {
                    theme: 'dark',
                    x: {
                        formatter: val => moment(val, 'DD MMM').format('DD MMM YYYY')
                    },
                    y: {
                        formatter: val => (typeof val === 'number' ? val.toFixed(2) : val)
                    }
                },
                colors: ["#4263eb"]
            });

            if (salesChart) {
                salesChart.render();
                loadSalesData(
                    defaultStartDate.format('YYYY-MM-DD'),
                    defaultEndDate.format('YYYY-MM-DD')
                );
            }
        });

        function loadSalesData(startDate, endDate) {
            if (!salesChart) return;
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

        // Users Overview Chart
        let usersChart;

        document.addEventListener("DOMContentLoaded", function() {
            const usersEl = document.getElementById('users');
            if (!usersEl) return;

            const defaultStartDate = moment().subtract(6, 'days');
            const defaultEndDate = moment();

            if ($('#usersDateRange').length) {
                $('#usersDateRange').daterangepicker({
                    startDate: defaultStartDate,
                    endDate: defaultEndDate,
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

                $('#usersDateRange').on('apply.daterangepicker', function(ev, picker) {
                    const dateTextEl = document.getElementById('overviewUsersDateRange');
                    if (dateTextEl) {
                        dateTextEl.innerHTML =
                            `From ${picker.startDate.format('DD MMM YYYY')} to ${picker.endDate.format('DD MMM YYYY')}`;
                    }

                    loadUsersData(
                        picker.startDate.format('YYYY-MM-DD'),
                        picker.endDate.format('YYYY-MM-DD')
                    );
                });
            }

            usersChart = new ApexCharts(usersEl, {
                chart: {
                    type: "bar",
                    height: 310,
                    width: "100%",
                    toolbar: {
                        show: false
                    },
                    parentHeightOffset: 0
                },
                plotOptions: {
                    bar: {
                        columnWidth: '32%',
                        borderRadius: 5,
                        borderRadiusApplication: 'around'
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
                        left: 5,
                        right: 5,
                        top: 10,
                        bottom: 0
                    },
                    strokeDashArray: 5,
                    borderColor: 'rgba(148, 163, 184, 0.08)'
                },
                series: [{
                    name: "{{ __('New Users') }}",
                    data: []
                }],
                xaxis: {
                    categories: [],
                    labels: {
                        style: {
                            fontFamily: 'inherit',
                            fontSize: '11px',
                            colors: '#6c757d',
                            fontWeight: '500'
                        }
                    },
                    axisBorder: {
                        show: false
                    },
                    axisTicks: {
                        show: false
                    }
                },
                yaxis: {
                    labels: {
                        align: 'right',
                        minWidth: 28,
                        maxWidth: 28,
                        formatter: val => (val % 1 === 0 ? val.toFixed(0) : val.toFixed(2)),
                        style: {
                            fontFamily: 'inherit',
                            fontSize: '11px',
                            colors: '#6c757d',
                            fontWeight: '500'
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

            if (usersChart) {
                usersChart.render();
                loadUsersData(
                    defaultStartDate.format('YYYY-MM-DD'),
                    defaultEndDate.format('YYYY-MM-DD')
                );
            }
        });

        function loadUsersData(startDate, endDate) {
            if (!usersChart) return;
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

        // Overview Chart
        let overviewChart;

        document.addEventListener("DOMContentLoaded", function() {
            const overviewEl = document.getElementById('overview');
            if (!overviewEl) return;

            const defaultStartDate = moment().subtract(6, 'days');
            const defaultEndDate = moment();

            if ($('#overviewDateRange').length) {
                $('#overviewDateRange').daterangepicker({
                    startDate: defaultStartDate,
                    endDate: defaultEndDate,
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
                    const dateTextEl = document.getElementById('overviewDateRangeText');
                    if (dateTextEl) {
                        dateTextEl.innerHTML =
                            `From ${picker.startDate.format('DD MMM YYYY')} to ${picker.endDate.format('DD MMM YYYY')}`;
                    }

                    loadOverviewData(
                        picker.startDate.format('YYYY-MM-DD'),
                        picker.endDate.format('YYYY-MM-DD')
                    );
                });
            }

            overviewChart = new ApexCharts(overviewEl, {
                chart: {
                    type: "area",
                    height: 280,
                    width: "100%",
                    redrawOnParentResize: true,
                    toolbar: {
                        show: false
                    },
                    parentHeightOffset: 0,
                    dropShadow: {
                        enabled: true,
                        top: 4,
                        left: 0,
                        blur: 6,
                        opacity: 0.15,
                        color: ["#ef4444", "#f97316", "#10b981"]
                    }
                },
                stroke: {
                    curve: "smooth",
                    width: 3.5,
                    lineCap: "round"
                },
                fill: {
                    type: "gradient",
                    gradient: {
                        shadeIntensity: 1,
                        opacityFrom: 0.22,
                        opacityTo: 0.01,
                        stops: [0, 95, 100]
                    }
                },
                markers: {
                    size: 0,
                    hover: {
                        size: 6,
                        sizeOffset: 3
                    }
                },
                dataLabels: {
                    enabled: false
                },
                grid: {
                    padding: {
                        left: 5,
                        right: 5,
                        top: 10,
                        bottom: 0
                    },
                    strokeDashArray: 5,
                    borderColor: 'rgba(148, 163, 184, 0.08)'
                },
                series: [{
                        name: `{{ __('Earnings') }}`,
                        data: []
                    },
                    {
                        name: `{{ __('Digital Business Cards') }}`,
                        data: []
                    },
                    {
                        name: `{{ __('Stores') }}`,
                        data: []
                    }
                ],
                xaxis: {
                    categories: [],
                    labels: {
                        style: {
                            fontFamily: 'inherit',
                            fontSize: '11px',
                            colors: '#6c757d',
                            fontWeight: '500'
                        }
                    },
                    axisBorder: {
                        show: false
                    },
                    axisTicks: {
                        show: false
                    }
                },
                yaxis: {
                    labels: {
                        align: 'right',
                        minWidth: 28,
                        maxWidth: 28,
                        formatter: function(val) {
                            return val % 1 === 0 ? val.toFixed(0) : val.toFixed(2);
                        },
                        style: {
                            fontFamily: 'inherit',
                            fontSize: '11px',
                            colors: '#6c757d',
                            fontWeight: '500'
                        }
                    }
                },
                tooltip: {
                    theme: 'dark',
                    x: {
                        formatter: val => moment(val, 'DD MMM').format('DD MMM YYYY')
                    }
                },
                colors: ["#ef4444", "#f97316", "#10b981"],
                legend: {
                    show: false
                }
            });

            if (overviewChart) {
                overviewChart.render();
                loadOverviewData(
                    defaultStartDate.format('YYYY-MM-DD'),
                    defaultEndDate.format('YYYY-MM-DD')
                );
            }
        });

        function loadOverviewData(startDate, endDate) {
            if (!overviewChart) return;
            $.get("{{ route('admin.overview.filter') }}", {
                start_date: startDate,
                end_date: endDate
            }, function(res) {
                overviewChart.updateOptions({
                    xaxis: {
                        categories: res.labels
                    },
                    series: [{
                            name: `{{ __('Earnings') }}`,
                            data: res.earnings
                        },
                        {
                            name: `{{ __('Digital Business Cards') }}`,
                            data: res.vcards
                        },
                        {
                            name: `{{ __('Stores') }}`,
                            data: res.stores
                        }
                    ]
                });

                $('#totalEarnings').text(res.total_earnings);
                $('#totalvCards').text(res.total_vcards);
                $('#totalStores').text(res.total_stores);
            });
        }

        // Current Week Sales Donut Chart Initialization
        document.addEventListener("DOMContentLoaded", function() {
            var container = document.getElementById('current-week-sales');
            if (!container) return;

            var rawData = {!! json_encode($currentWeekSales) !!};
            var seriesData = [];
            if (Array.isArray(rawData)) {
                seriesData = rawData.map(function(val) {
                    return parseFloat(val) || 0;
                });
            } else if (typeof rawData === 'object' && rawData !== null) {
                seriesData = Object.values(rawData).map(function(val) {
                    return parseFloat(val) || 0;
                });
            }

            var totalSales = seriesData.reduce(function(a, b) {
                return a + b;
            }, 0);

            if (totalSales <= 0) {
                container.innerHTML = `
                    <div class="d-flex flex-column align-items-center justify-content-center text-center py-4" style="height: 320px;">
                        <div class="avatar avatar-xl rounded-circle bg-light text-secondary mb-3 d-inline-flex align-items-center justify-content-center shadow-sm border" style="width: 64px; height: 64px;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="0 0 24 24" fill="none"/>
                                <path d="M5 21v-16a2 2 0 0 1 2 -2h10a2 2 0 0 1 2 2v16l-3 -2l-2 2l-2 -2l-2 2l-2 -2l-3 2" />
                                <path d="M9 7h6" />
                                <path d="M9 11h6" />
                                <path d="M9 15h4" />
                            </svg>
                        </div>
                        <div class="fw-bold text-dark fs-3 mb-1">{{ __('No sales recorded this week') }}</div>
                        <div class="text-secondary small">{{ __('Check back later for updates.') }}</div>
                    </div>
                `;
                return;
            }

            var options = {
                chart: {
                    type: "donut",
                    height: 320,
                    width: "100%",
                    fontFamily: 'inherit'
                },
                series: seriesData,
                labels: [
                    `{{ __('Monday') }}`,
                    `{{ __('Tuesday') }}`,
                    `{{ __('Wednesday') }}`,
                    `{{ __('Thursday') }}`,
                    `{{ __('Friday') }}`,
                    `{{ __('Saturday') }}`,
                    `{{ __('Sunday') }}`
                ],
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    show: true,
                    width: 2,
                    colors: ['#ffffff']
                },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '75%',
                            labels: {
                                show: true,
                                name: {
                                    show: true,
                                    fontSize: '12px',
                                    fontWeight: '600',
                                    color: '#626976',
                                    offsetY: -10
                                },
                                value: {
                                    show: true,
                                    fontSize: '20px',
                                    fontWeight: '700',
                                    color: '#1e293b',
                                    offsetY: 4,
                                    formatter: function(val) {
                                        return "$" + parseFloat(val).toFixed(2);
                                    }
                                },
                                total: {
                                    show: true,
                                    label: `{{ __('Total') }}`,
                                    color: '#626976',
                                    fontSize: '12px',
                                    fontWeight: '600',
                                    formatter: function() {
                                        return "$" + totalSales.toFixed(2);
                                    }
                                }
                            }
                        }
                    }
                },
                legend: {
                    show: true,
                    position: 'bottom',
                    fontSize: '12px'
                },
                tooltip: {
                    enabled: true,
                    theme: 'dark',
                    fillSeriesColor: false,
                    y: {
                        formatter: function(value) {
                            return "$" + parseFloat(value).toFixed(2);
                        }
                    }
                },
                colors: ["#4263eb", "#0ea5e9", "#10b981", "#f59f00", "#ef4444", "#8b5cf6", "#ec4899"]
            };

            container.innerHTML = "";
            var chartInstance = new ApexCharts(container, options);
            chartInstance.render();
        });
    </script>
@endsection
